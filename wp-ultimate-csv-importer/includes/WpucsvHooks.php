<?php
/**
 * Developer API hook dispatcher for WP Ultimate CSV Importer.
 *
 * @package Smackcoders\UCI\Core
 * @since   7.42.0
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central registry for wpucsv_* import lifecycle hooks (Issue #395).
 *
 * Wire orchestration only (SaveMapping, ImportHelpers, MediaHandling) — not per-adapter files.
 */
final class WpucsvHooks {

	/**
	 * Developer API version (semver for hook signatures).
	 *
	 * @var string
	 */
	const API_VERSION = '1.0.0';

	/** @var string Import session started (batch or full run). */
	const HOOK_BEFORE_IMPORT = 'wpucsv_before_import';

	/** @var string Import batch or file finished. */
	const HOOK_AFTER_IMPORT = 'wpucsv_after_import';

	/** @var string Before a single CSV/XML row is processed. */
	const HOOK_BEFORE_ROW = 'wpucsv_before_row';

	/** @var string After a single row finishes (success or handled skip). */
	const HOOK_AFTER_ROW = 'wpucsv_after_row';

	/** @var string Row failed, skipped, or duplicate per importer log rules. */
	const HOOK_ON_ROW_ERROR = 'wpucsv_on_row_error';

	/** @var string Before remote/local image download and attachment create. */
	const HOOK_BEFORE_MEDIA_UPLOAD = 'wpucsv_before_media_upload';

	/** @var string After media handling attempt for one URL/file. */
	const HOOK_AFTER_MEDIA_UPLOAD = 'wpucsv_after_media_upload';

	/** @var string Filter CSV header + value arrays before CORE. */
	const HOOK_MODIFY_ROW_DATA = 'wpucsv_modify_row_data';

	/** @var string Return true to skip the current row. */
	const HOOK_SKIP_ROW = 'wpucsv_skip_row';

	/** @var string Override a single mapped field value (null = default). */
	const HOOK_OVERRIDE_FIELD_VALUE = 'wpucsv_override_field_value';

	/** @var string Filter import type dropdown (post types list). */
	const HOOK_CUSTOM_POST_TYPE_SUPPORT = 'wpucsv_custom_post_type_support';

	/** @var string Filter SSL verify flag for image downloads. */
	const HOOK_MEDIA_DOWNLOAD_SSLVERIFY = 'wpucsv_media_download_sslverify';

	/** @var string Filter parsed user field array (Users import; legacy alias target). */
	const HOOK_MODIFY_USER_DATA = 'wpucsv_modify_user_data';

	/** @var string Active row context during main_import_process. */
	private static $current_context = null;

	/**
	 * Register backward-compatible filter aliases.
	 *
	 * @since 7.42.0
	 */
	public static function register_legacy_hooks() {
		add_filter( 'smack_csv_importer_image_download_sslverify', array( __CLASS__, 'legacy_media_sslverify' ), 10, 2 );
		add_filter( 'smack_csv_modify_userdata_filter', array( __CLASS__, 'legacy_modify_userdata_filter' ), 10, 1 );
	}

	/**
	 * Proxy legacy Users filter to wpucsv_modify_user_data.
	 *
	 * @since 7.42.0
	 *
	 * @param array $data_array Parsed user fields from Users import.
	 * @return array
	 */
	public static function legacy_modify_userdata_filter( $data_array ) {
		if ( function_exists( '_deprecated_hook' ) ) {
			_deprecated_hook(
				'smack_csv_modify_userdata_filter',
				'7.42.0',
				self::HOOK_MODIFY_USER_DATA . ' (or wpucsv_modify_row_data for CSV row data)'
			);
		}
		if ( ! is_array( $data_array ) ) {
			return $data_array;
		}
		/**
		 * Filter parsed user data during Users import (after header mapping).
		 *
		 * @since 7.42.0
		 *
		 * @param array $data_array User field key => value.
		 */
		return apply_filters( self::HOOK_MODIFY_USER_DATA, $data_array );
	}

	/**
	 * Proxy legacy SSL filter to wpucsv_media_download_sslverify.
	 *
	 * @since 7.42.0
	 *
	 * @param bool   $sslverify Whether to verify SSL certificates.
	 * @param string $url       Image URL or path being fetched.
	 * @return bool
	 */
	public static function legacy_media_sslverify( $sslverify, $url ) {
		if ( function_exists( '_deprecated_hook' ) ) {
			_deprecated_hook(
				'smack_csv_importer_image_download_sslverify',
				'7.42.0',
				self::HOOK_MEDIA_DOWNLOAD_SSLVERIFY
			);
		}
		return (bool) apply_filters( self::HOOK_MEDIA_DOWNLOAD_SSLVERIFY, $sslverify, $url );
	}

	/**
	 * Build standard hook context for actions and filters.
	 *
	 * @since 7.42.0
	 *
	 * @param array $args Context keys (see ISSUE-395 plan §8.1).
	 * @return array<string, mixed>
	 */
	public static function build_context( array $args = array() ) {
		$defaults = array(
			'hash_key'         => '',
			'selected_type'    => '',
			'resolved_type'    => '',
			'mode'             => '',
			'line_number'      => 0,
			'check'            => '',
			'duplicate_action' => 'skip',
			'update_based_on'  => 'normal',
			'gmode'            => null,
			'templatekey'      => null,
			'header_array'     => array(),
			'value_array'      => array(),
			'map'              => array(),
			'post_id'          => null,
			'record_type'      => '',
			'record_id'        => null,
			'log'              => null,
			'media_type'       => null,
			'total_rows'       => null,
			'file_name'        => null,
			'phase'            => null,
		);

		$context = array_merge( $defaults, $args );

		if ( $context['resolved_type'] === '' && $context['selected_type'] !== '' ) {
			$handler = new ExtensionHandler();
			$context['resolved_type'] = $handler->import_name_as( $context['selected_type'] );
		}

		if ( $context['record_type'] === '' && $context['selected_type'] !== '' ) {
			$context['record_type'] = self::resolve_record_type(
				$context['selected_type'],
				$context['post_id']
			);
		}

		if ( $context['record_id'] === null && $context['post_id'] !== null && $context['post_id'] !== '' ) {
			$context['record_id'] = $context['post_id'];
		}

		return $context;
	}

	/**
	 * Resolve record_type for hook context from UI module and optional primary ID.
	 *
	 * @since 7.42.0
	 *
	 * @param string                    $selected_type Import module from UI.
	 * @param int|string|null           $record_id   Post/user/term/attachment ID when known.
	 * @return string post|user|term|product|shop_order|comment|attachment|...
	 */
	public static function resolve_record_type( $selected_type, $record_id = null ) {
		$selected_type = trim( (string) $selected_type );

		$map = array(
			'Posts'                   => 'post',
			'Pages'                   => 'post',
			'Users'                   => 'user',
			'WooCommerce Product'     => 'product',
			'WooCommerce Orders'      => 'shop_order',
			'WooCommerce Customer'    => 'user',
			'WooCommerce Coupons'     => 'shop_coupon',
			'WooCommerce Refunds'     => 'shop_order_refund',
			'WooCommerce Product Variations' => 'product_variation',
			'Comments'                => 'comment',
			'WooCommerce Reviews'     => 'comment',
			'Media'                   => 'attachment',
			'Categories'              => 'term',
			'Tags'                    => 'term',
			'Taxonomies'              => 'term',
			'JetBooking'              => 'jet_booking',
			'JetReviews'              => 'jet_reviews',
		);

		if ( isset( $map[ $selected_type ] ) ) {
			return $map[ $selected_type ];
		}

		$taxonomies = get_taxonomies();
		if ( in_array( $selected_type, $taxonomies, true ) ) {
			return 'term';
		}

		if ( $record_id && is_numeric( $record_id ) ) {
			$post_type = get_post_type( (int) $record_id );
			if ( $post_type ) {
				return $post_type;
			}
		}

		$handler = new ExtensionHandler();
		$resolved = $handler->import_name_as( $selected_type );
		if ( $resolved === 'CustomPosts' || $resolved === 'Posts' || $resolved === 'Pages' ) {
			return 'post';
		}

		return sanitize_key( str_replace( ' ', '_', strtolower( $selected_type ) ) );
	}

	/**
	 * Whether detailed_log message indicates row should not continue to extension pass.
	 *
	 * @since 7.42.0
	 *
	 * @param string $message Log message.
	 * @return bool
	 */
	public static function is_row_blocked( $message ) {
		return (bool) preg_match( "/(Can't|Skipped|Duplicate)/", (string) $message );
	}

	/**
	 * Set context for the current row (used by get_header_values / media).
	 *
	 * @since 7.42.0
	 *
	 * @param array|null $context Context array or null to clear.
	 */
	public static function set_current_context( $context ) {
		self::$current_context = $context;
	}

	/**
	 * Get context for the row being imported.
	 *
	 * @since 7.42.0
	 *
	 * @return array<string, mixed>|null
	 */
	public static function get_current_context() {
		return self::$current_context;
	}

	/**
	 * Clear row context after import row completes.
	 *
	 * @since 7.42.0
	 */
	public static function clear_current_context() {
		self::$current_context = null;
	}

	/**
	 * Fires when an import session or batch starts.
	 *
	 * @since 7.42.0
	 *
	 * @param array $context Import session context.
	 */
	public static function before_import( array $context ) {
		/**
		 * Fires before an import session or AJAX batch processes rows.
		 *
		 * @since 7.42.0
		 *
		 * @param array $context Import session context (hash_key, selected_type, mode, total_rows, …).
		 */
		do_action( self::HOOK_BEFORE_IMPORT, $context );
	}

	/**
	 * Fires when an import session or batch ends.
	 *
	 * @since 7.42.0
	 *
	 * @param array  $context Import session context.
	 * @param string $phase   batch|complete
	 */
	public static function after_import( array $context, $phase = 'complete' ) {
		$context['phase'] = $phase;
		/**
		 * Fires after an import batch or full file import completes.
		 *
		 * @since 7.42.0
		 *
		 * @param array  $context Import session context; includes 'phase' => batch|complete.
		 * @param string $phase   batch|complete
		 */
		do_action( self::HOOK_AFTER_IMPORT, $context, $phase );
	}

	/**
	 * Fires before CORE + adapter processing for one row.
	 *
	 * @since 7.42.0
	 *
	 * @param array $context Row context.
	 */
	public static function before_row( array $context ) {
		self::set_current_context( $context );
		/**
		 * Fires before a single import row is processed (CORE + extensions).
		 *
		 * @since 7.42.0
		 *
		 * @param array $context Row context.
		 */
		do_action( self::HOOK_BEFORE_ROW, $context );
	}

	/**
	 * Fires after one row finishes processing.
	 *
	 * @since 7.42.0
	 *
	 * @param array $context Row context (post_id / record_id should be set when available).
	 */
	public static function after_row( array $context ) {
		/**
		 * Fires after a single import row completes.
		 *
		 * @since 7.42.0
		 *
		 * @param array $context Row context.
		 */
		do_action( self::HOOK_AFTER_ROW, $context );
		self::clear_current_context();
	}

	/**
	 * Fires when a row is skipped, duplicated, or failed.
	 *
	 * @since 7.42.0
	 *
	 * @param array  $context  Row context.
	 * @param string $message  Log or error message.
	 * @param string $severity error|warning|skipped
	 */
	public static function on_row_error( array $context, $message, $severity = 'error' ) {
		/**
		 * Fires when a row fails validation, is skipped, or hits a duplicate rule.
		 *
		 * @since 7.42.0
		 *
		 * @param array  $context  Row context.
		 * @param string $message  Human-readable message.
		 * @param string $severity error|warning|skipped
		 */
		do_action( self::HOOK_ON_ROW_ERROR, $context, $message, $severity );
	}

	/**
	 * Filter row header and value arrays before mapping filters and CORE.
	 *
	 * @since 7.42.0
	 *
	 * @param array $header_array CSV/XML headers.
	 * @param array $value_array  Row values.
	 * @param array $context      Row context.
	 * @return array{header: array, values: array}
	 */
	public static function modify_row_data( array $header_array, array $value_array, array $context ) {
		$payload = array(
			'header' => $header_array,
			'values' => $value_array,
		);

		/**
		 * Filter CSV/XML row data before UI filters and CORE import.
		 *
		 * @since 7.42.0
		 *
		 * @param array $payload  Keys: header (array), values (array).
		 * @param array $context  Row context.
		 */
		$filtered = apply_filters( self::HOOK_MODIFY_ROW_DATA, $payload, $context );

		if ( ! is_array( $filtered ) ) {
			return $payload;
		}

		return array(
			'header' => isset( $filtered['header'] ) && is_array( $filtered['header'] ) ? $filtered['header'] : $header_array,
			'values' => isset( $filtered['values'] ) && is_array( $filtered['values'] ) ? $filtered['values'] : $value_array,
		);
	}

	/**
	 * Whether the current row should be skipped via developer API.
	 *
	 * @since 7.42.0
	 *
	 * @param array $context Row context.
	 * @return bool
	 */
	public static function should_skip_row( array $context ) {
		/**
		 * Return true to skip the current row (logged as Skipped).
		 *
		 * @since 7.42.0
		 *
		 * @param bool  $skip    Default false.
		 * @param array $context Row context.
		 */
		return (bool) apply_filters( self::HOOK_SKIP_ROW, false, $context );
	}

	/**
	 * Allow extensions to override a single field value during get_header_values().
	 *
	 * @since 7.42.0
	 *
	 * @param mixed  $value      Default null = use core resolution.
	 * @param string $field_key  WordPress / mapping field key.
	 * @param string $csv_column Mapped CSV column name.
	 * @param mixed  $raw_value  Raw cell value.
	 * @param array  $context    Row context (falls back to current context).
	 * @return mixed Null to keep default; any other value is used.
	 */
	public static function override_field_value( $value, $field_key, $csv_column, $raw_value, array $context = array() ) {
		if ( empty( $context ) && self::$current_context !== null ) {
			$context = self::$current_context;
		}

		/**
		 * Override a single mapped field value for the current row.
		 *
		 * @since 7.42.0
		 *
		 * @param null  $value      Return non-null to override.
		 * @param string $field_key WordPress field key.
		 * @param string $csv_column CSV column header.
		 * @param mixed  $raw_value Cell value.
		 * @param array  $context   Row context.
		 */
		return apply_filters( self::HOOK_OVERRIDE_FIELD_VALUE, $value, $field_key, $csv_column, $raw_value, $context );
	}

	/**
	 * Filter available import types in the admin dropdown.
	 *
	 * @since 7.42.0
	 *
	 * @param array $importas slug => label list from ExtensionHandler.
	 * @return array
	 */
	public static function filter_post_types( array $importas ) {
		/**
		 * Add or remove import module types (including custom post types).
		 *
		 * @since 7.42.0
		 *
		 * @param array $importas Import type options.
		 */
		return apply_filters( self::HOOK_CUSTOM_POST_TYPE_SUPPORT, $importas );
	}

	/**
	 * Filter media arguments before download/attach.
	 *
	 * @since 7.42.0
	 *
	 * @param array $context    Row context (from current context if empty).
	 * @param array $media_args url, post_id, line_number, hash_key, featured, …
	 * @return array
	 */
	public static function before_media_upload( array $context, array $media_args ) {
		if ( empty( $context ) && self::$current_context !== null ) {
			$context = self::$current_context;
		}

		/**
		 * Filter media upload arguments before download/attach.
		 *
		 * @since 7.42.0
		 *
		 * @param array $media_args Media arguments.
		 * @param array $context    Row context.
		 */
		$filtered = apply_filters( self::HOOK_BEFORE_MEDIA_UPLOAD, $media_args, $context );

		return is_array( $filtered ) ? $filtered : $media_args;
	}

	/**
	 * Fires after media handling for one file/URL.
	 *
	 * @since 7.42.0
	 *
	 * @param array       $context       Row context.
	 * @param array       $media_args    Same array passed to before_media_upload.
	 * @param int|string  $attachment_id Attachment ID, 0, or empty on failure.
	 * @param string|null $error         Optional error message.
	 */
	public static function after_media_upload( array $context, array $media_args, $attachment_id, $error = null ) {
		if ( empty( $context ) && self::$current_context !== null ) {
			$context = self::$current_context;
		}

		/**
		 * Fires after media download/attach attempt for one URL or file.
		 *
		 * @since 7.42.0
		 *
		 * @param int|string  $attachment_id Attachment ID or empty.
		 * @param array       $media_args    Media arguments.
		 * @param array       $context       Row context.
		 * @param string|null $error         Error message if any.
		 */
		do_action( self::HOOK_AFTER_MEDIA_UPLOAD, $attachment_id, $media_args, $context, $error );
	}

	/**
	 * Filter whether to verify SSL when downloading images.
	 *
	 * @since 7.42.0
	 *
	 * @param bool   $sslverify Default true.
	 * @param string $url       Image URL.
	 * @return bool
	 */
	public static function media_download_sslverify( $sslverify, $url ) {
		/**
		 * Filter SSL certificate verification for remote image downloads.
		 *
		 * @since 7.42.0
		 *
		 * @param bool   $sslverify Whether to verify SSL.
		 * @param string $url       Image URL.
		 */
		return (bool) apply_filters( self::HOOK_MEDIA_DOWNLOAD_SSLVERIFY, $sslverify, $url );
	}
}

add_action( 'plugins_loaded', array( 'Smackcoders\\UCI\\Core\\WpucsvHooks', 'register_legacy_hooks' ), 20 );
