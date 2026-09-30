<?php

/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\UCI\Core;


if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class SingleImportExport {
	private static $singlecsv_instance = null;

	/**
	 * Subdirectory under uploads for Single Import/Export artifacts.
	 * Kept separate from smack_uci_uploads/exports/ so bulk exporter public URLs stay intact.
	 */
	const SINGLE_EXPORT_SUBDIR = 'smack_uci_uploads/single_exports/';


	public function __construct() {

		add_action( 'wp_ajax_handle_export_csv', array( $this, 'export_single_post_as_csv' ) );
		add_action( 'wp_ajax_handle_import_csv', array( $this, 'import_single_post_as_csv' ) );
		add_action( 'wp_ajax_download_single_export_csv', array( $this, 'download_single_export_csv' ) );

		// Defer filesystem migration until pluggable APIs are fully available.
		add_action( 'init', array( __CLASS__, 'bootstrap_single_export_storage' ), 20 );

	}

	/**
	 * Ensure protected storage exists and migrate legacy public single-export CSVs.
	 *
	 * @return void
	 */
	public static function bootstrap_single_export_storage() {
		self::ensure_single_export_directory();
		self::migrate_legacy_public_single_exports();
	}

	public static function getInstance() {

		if ( SingleImportExport::$singlecsv_instance == null ) {
			SingleImportExport::$singlecsv_instance = new SingleImportExport;
			return SingleImportExport::$singlecsv_instance;
		}
		return SingleImportExport::$singlecsv_instance;
	}

	/**
	 * Absolute filesystem path for protected single-export CSVs.
	 *
	 * @return string Trailing-slash directory path.
	 */
	public static function get_single_export_dir() {
		$upload = wp_upload_dir();
		return trailingslashit( $upload['basedir'] ) . self::SINGLE_EXPORT_SUBDIR;
	}

	/**
	 * Create and harden the single-export directory.
	 *
	 * @return string Directory path.
	 */
	public static function ensure_single_export_directory() {
		$dir = self::get_single_export_dir();
		SecurityHelper::ensure_secure_directory( $dir );
		SecurityHelper::protect_single_export_directory( $dir );
		return $dir;
	}

	/**
	 * Build an unguessable single-export filename for a post ID.
	 *
	 * HMAC binding prevents ID-only enumeration on servers (e.g. Nginx) that ignore .htaccess.
	 *
	 * @param int $post_id Post ID.
	 * @return string|false Filename or false.
	 */
	public static function get_single_export_filename( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}
		$token = hash_hmac( 'sha256', 'uci-single-export|' . $post_id, \wp_salt( 'auth' ) );
		return $post_id . '-' . $token . '.csv';
	}

	/**
	 * Resolve a validated CSV path for a post ID inside the single-export directory.
	 *
	 * @param int $post_id Post ID.
	 * @return string|false Absolute path or false on failure.
	 */
	public static function resolve_single_export_path( $post_id ) {
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return false;
		}

		$dir      = self::ensure_single_export_directory();
		$filename = self::get_single_export_filename( $post_id );
		if ( false === $filename ) {
			return false;
		}

		// Strict allow-list: {post_id}-{64 hex hmac}.csv
		if ( $filename !== basename( $filename ) || ! preg_match( '/^\d+-[a-f0-9]{64}\.csv$/', $filename ) ) {
			return false;
		}

		$file_path = $dir . $filename;
		if ( ! SecurityHelper::is_path_inside_directory( $file_path, $dir ) ) {
			return false;
		}

		return $file_path;
	}

	/**
	 * Move legacy Single Import/Export CSVs out of the publicly readable exports/ directory.
	 *
	 * Only touches numeric post-id filenames whose header matches the single-export format.
	 * Does not delete bulk-exporter artifacts or named template CSVs.
	 *
	 * @return void
	 */
	public static function migrate_legacy_public_single_exports() {
		if ( get_option( 'smack_uci_single_export_legacy_migrated', '' ) === '1' ) {
			return;
		}

		$upload     = wp_upload_dir();
		$legacy_dir = trailingslashit( $upload['basedir'] ) . 'smack_uci_uploads/exports/';
		$protected  = self::ensure_single_export_directory();

		if ( is_dir( $legacy_dir ) ) {
			$files = glob( $legacy_dir . '[0-9]*.csv' );
			if ( is_array( $files ) ) {
				foreach ( $files as $legacy_file ) {
					$basename = basename( $legacy_file );
					if ( ! preg_match( '/^(\d+)\.csv$/', $basename, $matches ) ) {
						continue;
					}
					if ( ! self::is_single_export_csv( $legacy_file ) ) {
						continue;
					}

					$legacy_post_id = absint( $matches[1] );
					$dest_name      = self::get_single_export_filename( $legacy_post_id );
					if ( false === $dest_name ) {
						continue;
					}
					$dest = $protected . $dest_name;
					// Prefer moving; fall back to delete if move fails (file must not stay public).
					// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
					$moved = @rename( $legacy_file, $dest );
					if ( ! $moved ) {
						if ( @copy( $legacy_file, $dest ) ) {
							// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
							@unlink( $legacy_file );
						} else {
							// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
							@unlink( $legacy_file );
						}
					}
				}
			}
		}

		// Rename any already-migrated plain {id}.csv artifacts inside the protected directory.
		$plain_protected = glob( $protected . '[0-9]*.csv' );
		if ( is_array( $plain_protected ) ) {
			foreach ( $plain_protected as $plain_file ) {
				$basename = basename( $plain_file );
				if ( ! preg_match( '/^(\d+)\.csv$/', $basename, $matches ) ) {
					continue;
				}
				if ( ! self::is_single_export_csv( $plain_file ) ) {
					continue;
				}
				$dest_name = self::get_single_export_filename( absint( $matches[1] ) );
				if ( false === $dest_name ) {
					continue;
				}
				$dest = $protected . $dest_name;
				if ( $dest === $plain_file ) {
					continue;
				}
				// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
				if ( ! @rename( $plain_file, $dest ) ) {
					if ( @copy( $plain_file, $dest ) ) {
						// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
						@unlink( $plain_file );
					}
				}
			}
		}

		update_option( 'smack_uci_single_export_legacy_migrated', '1', false );
	}

	/**
	 * Detect Single Import/Export CSV by header signature.
	 *
	 * @param string $file_path Absolute path.
	 * @return bool
	 */
	private static function is_single_export_csv( $file_path ) {
		if ( ! is_readable( $file_path ) ) {
			return false;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$handle = fopen( $file_path, 'r' );
		if ( false === $handle ) {
			return false;
		}
		$header = fgets( $handle );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $handle );
		if ( ! is_string( $header ) ) {
			return false;
		}
		return ( strpos( $header, 'core_post_title' ) !== false && strpos( $header, 'terms_data' ) !== false );
	}

	/**
	 * Build an authenticated admin-ajax download URL for a single export.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_authenticated_download_url( $post_id ) {
		return add_query_arg(
			array(
				'action'  => 'download_single_export_csv',
				'post_id' => absint( $post_id ),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Authorize the current request for single-export operations.
	 *
	 * @return void Dies on failure.
	 */
	private static function authorize_single_export_request() {
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'wp-ultimate-csv-importer' ) ), 403 );
		}

		SecurityHelper::verify_ajax_nonce();
		if ( ! SecurityHelper::check_capability( SecurityHelper::can_export() ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have sufficient permissions to access this page.', 'wp-ultimate-csv-importer' ) ), 403 );
		}
	}


	public static function import_single_post_as_csv() {
		
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Unauthorized access.', 'wp-ultimate-csv-importer' ) ], 403 );
			return;
		}

		SecurityHelper::verify_ajax_nonce();
		if ( ! SecurityHelper::check_capability( SecurityHelper::can_import() ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		

		if ( empty( $_FILES['file'] ) || $_FILES['file']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( [ 'message' => __( 'File upload error.', 'wp-ultimate-csv-importer' ) ] );
			return;
		}

		$file_name = sanitize_file_name( $_FILES['file']['name'] );
		$file_tmp  = $_FILES['file']['tmp_name'];
		$file_info = wp_check_filetype_and_ext( $file_tmp, $file_name, [ 'csv' => 'text/csv' ] );
		if ( ! $file_info['ext'] || $file_info['ext'] !== 'csv' ) {
			wp_send_json_error( [ 'message' => __( 'Invalid file type. Only CSV files are allowed.', 'wp-ultimate-csv-importer' ) ] );
			return;
		}
		$upload = wp_upload_dir();
		$upload_dir = trailingslashit( $upload['basedir'] ) . 'smack_uci_uploads/imports/';
		SecurityHelper::ensure_secure_directory( $upload_dir );

		$index_php_file = $upload_dir . 'index.php';
		if ( ! file_exists( $index_php_file ) ) {
			file_put_contents( $index_php_file, "<?php\n// Silence is golden.\n" );
		}

		$upload_dir_path = $upload_dir . $file_name;
		SecurityHelper::ensure_secure_directory( $upload_dir_path );

		$csv_file = $upload_dir_path . '/' . $file_name;
		if ( move_uploaded_file( $_FILES['file']['tmp_name'], $csv_file ) ) {
			if ( ( $handle = fopen( $csv_file, 'r' ) ) !== false ) {

				$headers = fgetcsv( $handle, 1000, ',', '"', '\\' );
				$post_id = 0;
				while ( ( $row = fgetcsv( $handle, 1000, ',', '"', '\\' ) ) !== false ) {
					if ( ! is_array( $headers ) || ! is_array( $row ) ) {
						continue;
					}
					$data = array_combine( $headers, $row );
					if ( ! is_array( $data ) ) {
						continue;
					}
					$post_data = array();
					foreach ( $data as $key => $value ) {

						if ( strpos( $key, 'core_' ) === 0 ) {
							$post_data[ str_replace( 'core_', '', $key ) ] = $value;
						}

					}


					$post_id = wp_insert_post( $post_data );

					if ( ! is_wp_error( $post_id ) ) {
						// Set meta data
foreach ( $data as $key => $value ) {
    if ( strpos( $key, 'meta_' ) === 0 && $value !== '' ) {
        $meta_key = substr( $key, 5 ); 

        if ( $meta_key === '_elementor_data' ) {
            update_post_meta( $post_id, $meta_key, wp_slash( $value ) );
        } elseif ( in_array( $meta_key, [
            '_elementor_css',
            '_elementor_page_assets',
            '_elementor_controls_usage'
        ], true ) ) {
            // SECURITY FIX: Use JSON instead of unserialize to prevent PHP Object Injection
            // Try JSON decode first (modern Elementor format)
            $decoded_value = json_decode( $value, true );
            
            if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded_value ) ) {
                // Valid JSON
                update_post_meta( $post_id, $meta_key, $decoded_value );
            } elseif ( is_array( $value ) ) {
                update_post_meta( $post_id, $meta_key, $value );
            } else {
                // Store as array with single value (safest option)
                update_post_meta( $post_id, $meta_key, [ $value ] );
            }
        } elseif ( $meta_key === '_elementor_element_cache' ) {
            $decoded = json_decode( $value, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                update_post_meta( $post_id, $meta_key, $decoded );
            } else {
                update_post_meta( $post_id, $meta_key, $value );
            }
        } else {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $value ) );
        }
    }
}


						// Set terms
						if ( ! empty( $data['terms_data'] ) ) {
							$terms = json_decode( $data['terms_data'], true );
							foreach ( $terms as $term ) {
								$parent_id = 0;

								// Handle parent term if necessary
								if ( ! empty( $term['parent'] ) && is_taxonomy_hierarchical( $term['taxonomy'] ) ) {
									$parent_term = get_term( $term['parent'], $term['taxonomy'] );
									$parent_id = $parent_term ? $parent_term->term_id : 0;
								}


								if ( ! empty( $term['term_name'] ) ) {
									// Check if the term already exists
									$existing_term = get_term_by( 'name', $term['term_name'], $term['taxonomy'] );

									if ( $existing_term ) {
										// Assign the existing term to the post
										wp_set_object_terms( $post_id, [ $existing_term->term_id ], $term['taxonomy'], true );
									} else {
										// Insert the term if it doesn't exist
										$term_id = wp_insert_term( $term['term_name'], $term['taxonomy'], [
											'slug'   => $term['term_slug'],
											'parent' => $parent_id,
										] );

										if ( ! is_wp_error( $term_id ) ) {
											// Assign the newly created term to the post
											wp_set_object_terms( $post_id, [ $term_id['term_id'] ], $term['taxonomy'], true );
										}
									}
								}



							}
						}

						// Set featured image
						if ( ! empty( $data['core_featured_image'] ) ) {
							$attachment_id = attachment_url_to_postid( $data['core_featured_image'] );
							if ( $attachment_id ) {
								set_post_thumbnail( $post_id, $attachment_id );
							}
						}
					}
				}
				fclose( $handle );
			}
			$response['success'] = true;    
			$response['message'] = 'Inserted Post   ' . $post_id;
			$response['redirect_link'] = $post_id ? get_edit_post_link( $post_id, 'raw' ) : '';
			echo wp_json_encode( $response );
			wp_die();
		}

		wp_send_json_error( array( 'message' => __( 'Unable to save the uploaded file.', 'wp-ultimate-csv-importer' ) ) );
	}



	public static function export_single_post_as_csv() {

		self::authorize_single_export_request();

		global $wpdb;
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$post    = get_post( $post_id );

		if ( ! $post || ! $post_id || in_array( get_post_status( $post_id ), array( 'auto-draft', 'trash' ), true ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid post ID.', 'wp-ultimate-csv-importer' ) ] );
			wp_die();
		}

		$meta_keys = $wpdb->get_col(
			$wpdb->prepare( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d", $post_id )
		);

		// Fetch taxonomies
		$taxonomies = get_object_taxonomies( $post->post_type );

		// Headers
		$headers = array_merge(
			[ 'core_post_title', 'core_post_content', 'core_post_name', 'core_post_excerpt', 'core_post_status', 'core_post_type', 'core_featured_image', 'core_ping_status', 'core_comment_status', 'core_post_format', 'core_post_author', 'core_post_date', 'core_post_parent', 'core_menu_order', 'core_wp_page_template' ],
			array_map( fn( $meta ) => "meta_$meta", $meta_keys ),
			[ 'terms_data' ]
		);

		// Core data
		$featured_image = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
		$row = [
			'core_post_title'   => $post->post_title,
			'core_post_content' => $post->post_content,
			'core_post_name'    => $post->post_name,
			'core_post_excerpt'    => $post->post_excerpt,
			'core_post_status'  => $post->post_status,
			'core_post_type'    => $post->post_type,
			'core_featured_image' => $featured_image ?: '',	
			'core_ping_status'    => $post->ping_status,
			'core_comment_status'    => $post->comment_status,
			'core_post_format'    => $post->post_format,
			'core_post_author' => get_the_author_meta( 'display_name', $post->post_author ),
			'core_post_date' => $post->post_date,
			'core_post_parent' => $post->post_parent,
			'core_menu_order' => $post->menu_order,
			'core_wp_page_template' => $post->wp_page_template



		];

		// Meta data
foreach ( $meta_keys as $meta_key ) {
    $meta_value = get_post_meta( $post_id, $meta_key, true );

    if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
        $meta_value = wp_json_encode( $meta_value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    }

    $row[ "meta_$meta_key" ] = $meta_value;
}


		// Terms data
		$terms_data = [];
		foreach ( $taxonomies as $taxonomy ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy );
			foreach ( $terms as $term ) {
				$term_meta = get_term_meta( $term->term_id );
				$terms_data[] = [
					'taxonomy'   => $taxonomy,
					'term_name'  => $term->name,
					'term_slug'  => $term->slug,
					'parent'     => $term->parent,
					'term_meta'  => $term_meta,
				];
			}
		}
		$row['terms_data'] = json_encode( $terms_data );

		

		$file_path = self::resolve_single_export_path( $post_id );
		if ( false === $file_path ) {
			wp_send_json_error( array( 'message' => __( 'Unable to create export file.', 'wp-ultimate-csv-importer' ) ) );
			wp_die();
		}

		// Remove any leftover publicly accessible legacy copy for this post ID.
		$upload     = wp_upload_dir();
		$legacy_pub = trailingslashit( $upload['basedir'] ) . 'smack_uci_uploads/exports/' . $post_id . '.csv';
		if ( is_file( $legacy_pub ) && self::is_single_export_csv( $legacy_pub ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $legacy_pub );
		}

		// Write via temp file then rename to avoid serving a partial CSV.
		$temp_path = $file_path . '.tmp';
		$output    = fopen( $temp_path, 'w' );
		if ( false === $output ) {
			wp_send_json_error( array( 'message' => __( 'Unable to create export file.', 'wp-ultimate-csv-importer' ) ) );
			wp_die();
		}
		fputcsv( $output, $headers );
		fputcsv( $output, $row );
		fclose( $output );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
		if ( ! @rename( $temp_path, $file_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $temp_path );
			wp_send_json_error( array( 'message' => __( 'Unable to create export file.', 'wp-ultimate-csv-importer' ) ) );
			wp_die();
		}

		$response              = array();
		$response['success']   = true;
		$response['post_id']   = $post_id;
		$response['file_name'] = 'post-' . $post_id . '.csv';
		// Authenticated download endpoint — not a direct uploads URL.
		$response['file_path'] = self::get_authenticated_download_url( $post_id );
		echo wp_json_encode( $response );
		wp_die();


	}

	/**
	 * Stream a single-export CSV after login, capability, and path validation.
	 *
	 * @return void
	 */
	public static function download_single_export_csv() {
		if ( ! is_user_logged_in() ) {
			status_header( 403 );
			wp_die( esc_html__( 'Unauthorized access.', 'wp-ultimate-csv-importer' ), 403 );
		}

		SecurityHelper::verify_ajax_nonce();

		if ( ! current_user_can( 'manage_options' ) || ! SecurityHelper::check_capability( SecurityHelper::can_export() ) ) {
			status_header( 403 );
			wp_die( esc_html__( 'Unauthorized access.', 'wp-ultimate-csv-importer' ), 403 );
		}

		$post_id = isset( $_REQUEST['post_id'] ) ? absint( wp_unslash( $_REQUEST['post_id'] ) ) : 0;
		$file_path = self::resolve_single_export_path( $post_id );

		if ( false === $file_path || ! is_file( $file_path ) || ! is_readable( $file_path ) ) {
			status_header( 404 );
			wp_die( esc_html__( 'Export file not found.', 'wp-ultimate-csv-importer' ), 404 );
		}

		// Re-validate containment after realpath resolution.
		$dir = self::get_single_export_dir();
		if ( ! SecurityHelper::is_path_inside_directory( $file_path, $dir ) ) {
			status_header( 403 );
			wp_die( esc_html__( 'Unauthorized access.', 'wp-ultimate-csv-importer' ), 403 );
		}

		$filename = 'post-' . $post_id . '.csv';
		$filesize = filesize( $file_path );

		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		if ( false !== $filesize ) {
			header( 'Content-Length: ' . (int) $filesize );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $file_path );
		exit;
	}
}
