<?php
/**
 * Template management AJAX handlers.
 *
 * @package Smackcoders\UCI\Core
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateManager {

	private static $instance = null;

	public function __construct() {
		add_action( 'wp_ajax_displayTemplates', array( $this, 'display_templates' ) );
		add_action( 'wp_ajax_saveTemplate', array( $this, 'save_template' ) );
		add_action( 'wp_ajax_deleteTemplate', array( $this, 'delete_template' ) );
		add_action( 'wp_ajax_download_template_file', array( $this, 'download_template_file' ) );
	}

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Save template details.
	 */
	public function save_template() {
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $wpdb;

		$type              = isset( $_POST['Types'] ) ? sanitize_text_field( wp_unslash( $_POST['Types'] ) ) : '';
		$map_fields        = isset( $_POST['MappedFields'] ) ? wp_unslash( $_POST['MappedFields'] ) : '';
		$template_name     = isset( $_POST['TemplateName'] ) ? sanitize_text_field( wp_unslash( $_POST['TemplateName'] ) ) : '';
		$new_template_name = isset( $_POST['NewTemplate'] ) ? sanitize_text_field( wp_unslash( $_POST['NewTemplate'] ) ) : '';
		$mapping_type      = isset( $_POST['MappingType'] ) ? sanitize_text_field( wp_unslash( $_POST['MappingType'] ) ) : '';
		$mapping_filter    = null;
		$filters           = ! empty( $_POST['MappedFilter'] ) ? json_decode( stripslashes( wp_unslash( $_POST['MappedFilter'] ) ), true ) : '';
		if ( ! empty( $filters ) ) {
			$mapping_filter = serialize( $filters );
		}

		$template_table_name = $wpdb->prefix . 'ultimate_csv_importer_mappingtemplate';
		$template            = $this->get_template_for_save( $template_table_name, $template_name, $type );

		if ( empty( $template ) ) {
			wp_send_json_error( array( 'message' => __( 'Template not found', 'wp-ultimate-csv-importer' ) ) );
		}

		$mapdata = ImportHelpers::decode_mapping_payload( $map_fields );
		if ( ! is_array( $mapdata ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid mapping payload. Existing template mapping was not changed.', 'wp-ultimate-csv-importer' ) ) );
		}

		$mapping_fields      = serialize( $mapdata );
		$time                = gmdate( 'Y-m-d H:i:s' );
		$saved_template_name = ! empty( $new_template_name ) ? $new_template_name : $template_name;

		if ( $saved_template_name !== $template_name ) {
			$duplicate_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM $template_table_name WHERE templatename = %s AND id != %d LIMIT 1",
					$saved_template_name,
					(int) $template->id
				)
			);
			if ( ! empty( $duplicate_id ) ) {
				echo wp_json_encode(
					array(
						'success' => false,
						'message' => __( 'Template Name Already Exists', 'wp-ultimate-csv-importer' ),
					)
				);
				wp_die();
			}
		}

		$update_response     = $wpdb->update(
			$template_table_name,
			array(
				'templatename'   => $saved_template_name,
				'mapping'        => $mapping_fields,
				'mapping_filter' => $mapping_filter ?: '',
				'createdtime'    => $time,
				'module'         => $type,
				'mapping_type'   => $mapping_type,
			),
			array( 'id' => (int) $template->id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $update_response ) {
			wp_send_json_error( array( 'message' => __( 'Unable to save template mapping', 'wp-ultimate-csv-importer' ) ) );
		}

		wp_send_json( array( 'success' => true ) );
	}

	private function get_template_for_save( $template_table_name, $template_name, $type ) {
		global $wpdb;

		$template = null;
		if ( ! empty( $type ) ) {
			$template = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, eventKey FROM $template_table_name WHERE templatename = %s AND module = %s ORDER BY id DESC LIMIT 1",
					$template_name,
					$type
				)
			);
		}

		if ( empty( $template ) ) {
			$template = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, eventKey FROM $template_table_name WHERE templatename = %s ORDER BY id DESC LIMIT 1",
					$template_name
				)
			);
		}

		return $template;
	}

	/**
	 * Deletes Template.
	 */
	public function delete_template() {
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		$template_name  = isset( $_POST['TemplateName'] ) ? sanitize_text_field( wp_unslash( $_POST['TemplateName'] ) ) : '';
		$return_message = array();
		global $wpdb;

		$template_table_name = $wpdb->prefix . 'ultimate_csv_importer_mappingtemplate';
		$id                  = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM $template_table_name WHERE templatename = %s",
				$template_name
			)
		);

		if ( empty( $id ) ) {
			$return_message['message'] = 'Template not found';
			$return_message['success'] = false;
			echo wp_json_encode( $return_message );
			wp_die();
		}

		if ( $this->check_template_scheduled( $id ) ) {
			$return_message['message'] = 'Template assigned to Scheduler. So can not delete it.';
			$return_message['success'] = false;
			echo wp_json_encode( $return_message );
			wp_die();
		}

		$delete_response = $wpdb->delete( $template_table_name, array( 'id' => $id ) );
		if ( $delete_response ) {
			$return_message['message'] = 'Deleted Successfully';
			$return_message['success'] = true;
		} else {
			$return_message['message'] = 'Error occured while deleting';
			$return_message['success'] = false;
		}
		echo wp_json_encode( $return_message );
		wp_die();
	}

	/**
	 * Checks whether a template has been scheduled.
	 *
	 * @param int $id Template id.
	 * @return bool
	 */
	public function check_template_scheduled( $id ) {
		global $wpdb;

		$scheduled_tables = array(
			$wpdb->prefix . 'sm_uci_addon_pro_scheduled_import',
			$wpdb->prefix . 'ultimate_csv_importer_scheduled_import',
		);

		foreach ( $scheduled_tables as $table_name ) {
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) !== $table_name ) {
				continue;
			}

			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $table_name WHERE templateid = %d AND isrun = %d",
					$id,
					0
				)
			);
			if ( $count > 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves and display template details.
	 */
	public function display_templates() {
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $wpdb;

		$response              = array();
		$info                  = array();
		$template_table_name   = $wpdb->prefix . 'ultimate_csv_importer_mappingtemplate';
		$get_result            = $wpdb->get_results( "SELECT id, templatename, createdtime, module, csvname, eventKey, mapping FROM $template_table_name WHERE templatename != '' ORDER BY id DESC" );

		if ( ! empty( $get_result ) ) {
			foreach ( $get_result as $value ) {
				$module   = $this->resolve_template_module( $value );
				$details  = array(
					'template_id'   => isset( $value->id ) ? absint( $value->id ) : 0,
					'template_name' => $value->templatename,
					'module'        => $module,
					'module_label'  => $this->get_module_label( $module ),
					'created_time'  => $value->createdtime,
					'csv_name'      => isset( $value->csvname ) ? sanitize_text_field( $value->csvname ) : '',
				);
				$info[] = $details;
			}
			$response['success'] = true;
			$response['info']    = $info;
		} else {
			$response['success'] = false;
			$response['message'] = 'No Templates Found';
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Download template mapping as CSV.
	 */
	public function download_template_file() {
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $wpdb;

		$response     = array();
		$templatename = isset( $_POST['filename'] ) ? sanitize_text_field( wp_unslash( $_POST['filename'] ) ) : '';
		$upload       = wp_upload_dir();
		$upload_dir   = $upload['baseurl'];

		if ( ! file_exists( $upload['basedir'] . '/smack_uci_uploads/exports/' ) ) {
			wp_mkdir_p( $upload['basedir'] . '/smack_uci_uploads/exports/' );
		}

		$get_event_key = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ultimate_csv_importer_mappingtemplate WHERE templatename = %s",
				$templatename
			)
		);

		if ( empty( $get_event_key ) ) {
			$response['success'] = false;
			$response['message'] = 'Template not found';
		} else {
			$mapping_data = maybe_unserialize( $get_event_key[0]->mapping );
			if ( ! is_array( $mapping_data ) ) {
				$mapping_data = array();
			}

			$csv_headers = array( 'module', 'template_name', 'csv_name' );
			$csv_values  = array(
				$get_event_key[0]->module ?? '',
				$get_event_key[0]->templatename ?? '',
				$get_event_key[0]->csvname ?? '',
			);

			foreach ( $mapping_data as $section => $fields ) {
				if ( ! is_array( $fields ) ) {
					continue;
				}
				foreach ( $fields as $key => $value ) {
					$csv_headers[] = "{$section}->{$key}";
					$csv_values[]  = $value;
				}
			}

			$csv_filename  = $templatename . '.csv';
			$csv_file_path = $upload['basedir'] . '/smack_uci_uploads/exports/' . $csv_filename;
			$file          = fopen( $csv_file_path, 'w' );
			if ( false !== $file ) {
				fputcsv( $file, $csv_headers );
				fputcsv( $file, $csv_values );
				fclose( $file );
			}

			$response['success']   = true;
			$response['file_link'] = $upload_dir . '/smack_uci_uploads/exports/' . $csv_filename;
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	private function resolve_template_module( $template ) {
		global $wpdb;

		$module = isset( $template->module ) ? trim( (string) $template->module ) : '';
		if ( ! empty( $module ) ) {
			return sanitize_text_field( $module );
		}

		$template_id = isset( $template->id ) ? absint( $template->id ) : 0;
		if ( ! empty( $template_id ) ) {
			$scheduled_table = $wpdb->prefix . 'ultimate_csv_importer_scheduled_import';
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $scheduled_table ) ) ) === $scheduled_table ) {
				$scheduled_module = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT module FROM $scheduled_table WHERE templateid = %d AND module != '' ORDER BY id DESC LIMIT 1",
						$template_id
					)
				);
				if ( ! empty( $scheduled_module ) ) {
					return sanitize_text_field( $scheduled_module );
				}
			}
		}

		$event_key = isset( $template->eventKey ) ? sanitize_text_field( $template->eventKey ) : '';
		if ( ! empty( $event_key ) ) {
			$scheduled_table = $wpdb->prefix . 'ultimate_csv_importer_scheduled_import';
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $scheduled_table ) ) ) === $scheduled_table ) {
				$scheduled_module = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT module FROM $scheduled_table WHERE event_key = %s AND module != '' ORDER BY id DESC LIMIT 1",
						$event_key
					)
				);
				if ( ! empty( $scheduled_module ) ) {
					return sanitize_text_field( $scheduled_module );
				}
			}

			$events_table = $wpdb->prefix . 'smackuci_events';
			if ( $this->table_exists( $events_table ) ) {
				$event_module = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT import_type FROM $events_table WHERE eventKey = %s AND import_type != '' ORDER BY id DESC LIMIT 1",
						$event_key
					)
				);
				if ( ! empty( $event_module ) ) {
					return sanitize_text_field( $event_module );
				}
			}
		}

		return $this->infer_module_from_mapping( isset( $template->mapping ) ? $template->mapping : '' );
	}

	private function infer_module_from_mapping( $mapping ) {
		$mapping_data = maybe_unserialize( $mapping );
		if ( ! is_array( $mapping_data ) ) {
			return '';
		}

		$registered_modules = array_merge( get_post_types( array(), 'names' ), get_taxonomies( array(), 'names' ) );
		$mapped_values      = $this->flatten_mapping_values( $mapping_data );

		foreach ( $mapped_values as $mapped_value ) {
			$mapped_value = is_scalar( $mapped_value ) ? trim( (string) $mapped_value ) : '';
			if ( ! empty( $mapped_value ) && in_array( $mapped_value, $registered_modules, true ) ) {
				return sanitize_text_field( $mapped_value );
			}
		}

		return '';
	}

	private function flatten_mapping_values( $mapping_data ) {
		$values = array();
		foreach ( $mapping_data as $value ) {
			if ( is_array( $value ) ) {
				$values = array_merge( $values, $this->flatten_mapping_values( $value ) );
			} else {
				$values[] = $value;
			}
		}
		return $values;
	}

	private function get_module_label( $module ) {
		$module = trim( (string) $module );
		if ( empty( $module ) ) {
			return __( 'Uncategorized', 'wp-ultimate-csv-importer' );
		}

		if ( class_exists( __NAMESPACE__ . '\ExtensionHandler' ) ) {
			$extension_handler = ExtensionHandler::getInstance();
			$import_modules    = $extension_handler->get_import_post_types();
			foreach ( $import_modules as $label => $value ) {
				if ( $module === $value || $module === $label ) {
					return sanitize_text_field( $label );
				}
			}
		}

		$post_type = get_post_type_object( $module );
		if ( ! empty( $post_type->labels->name ) ) {
			return sanitize_text_field( $post_type->labels->name );
		}

		$taxonomy = get_taxonomy( $module );
		if ( ! empty( $taxonomy->labels->name ) ) {
			return sanitize_text_field( $taxonomy->labels->name );
		}

		return ucwords( str_replace( array( '_', '-' ), ' ', sanitize_text_field( $module ) ) );
	}

	private function table_exists( $table_name ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) === $table_name;
	}
}
