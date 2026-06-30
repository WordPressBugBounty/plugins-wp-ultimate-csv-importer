<?php
/**
 * Centralized AJAX authorization guard for WP Ultimate CSV Importer.
 *
 * Defense-in-depth: blocks low-privilege users before individual handlers run.
 *
 * @package Smackcoders\UCI\Core
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxAuthorization {

	/**
	 * Actions that require manage_options (administrator).
	 *
	 * @return string[]
	 */
	public static function admin_only_actions() {
		$actions = array(
			'displayTemplates',
			'saveTemplate',
			'deleteTemplate',
			'download_template_file',
			'saveTemplateFields',
			'saveMappedFields',
			'StartImport',
			'GetProgress',
			'ImportState',
			'ImportStop',
			'checkmain_mode',
			'close_notification_action',
			'bulk_file_import',
			'bulk_import',
			'PauseImport',
			'ResumeImport',
			'DeactivateMail',
			'smackuci_check_review_popup',
			'helperImport',
			'helperSearch',
			'settings_options',
			'get_options',
			'get_setting',
			'needHelper',
			'check_import',
			'get_parse_xml',
			'validate_csv_preflight',
			'get_interrupted_imports',
			'resume_import_session',
			'discard_import_session',
			'mappingfields',
			'templateinfo',
			'search_template',
			'display_log',
			'download_log',
			'download_media_log',
			'download_failed_log',
			'delete_log',
			'security_performance',
			'active_addons',
			'get_ftp_url',
			'get_ftp_details',
			'get_desktop',
			'oneClickUpload',
			'get_csv_delimiter',
			'install_plugins',
			'install_addon',
			'handle_export_csv',
			'handle_import_csv',
			'displayCSV',
			'LineChart',
			'BarChart',
			'dashboard_kpi_stats',
			'dashboard_import_volume',
			'dashboard_content_distribution',
			'dashboard_recent_activity',
			'dashboard_quick_stats',
			'updatefields',
			'total_records',
			'check_export',
			'get_csv_url',
			'support_mail',
			'toolset_state',
			'send_subscribe_email',
			'zip_upload',
			'image_options',
			'delete_image',
		);

		return apply_filters( 'smack_uci_admin_ajax_actions', $actions );
	}

	/**
	 * Actions that require install_plugins + activate_plugins.
	 *
	 * @return string[]
	 */
	public static function plugin_install_actions() {
		return array( 'install_plugins', 'install_addon' );
	}

	/**
	 * Register the guard on admin_init (after auth is ready for admin-ajax.php).
	 */
	public static function register() {
		static $registered = false;
		if ( $registered ) {
			return;
		}
		$registered = true;
		add_action( 'admin_init', array( __CLASS__, 'guard' ), 1 );
	}

	/**
	 * Block unauthorized AJAX requests early.
	 */
	public static function guard() {
		if ( ! wp_doing_ajax() ) {
			return;
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( $action === '' ) {
			return;
		}

		if ( in_array( $action, self::plugin_install_actions(), true ) ) {
			if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
				self::deny();
			}
			return;
		}

		if ( in_array( $action, self::admin_only_actions(), true ) ) {
			$capability = SecurityHelper::can_import();
			if ( in_array( $action, array( 'total_records', 'check_export', 'handle_export_csv' ), true ) ) {
				$capability = SecurityHelper::can_export();
			}
			if ( in_array( $action, array( 'settings_options', 'security_performance', 'active_addons', 'get_options', 'get_setting' ), true ) ) {
				$capability = SecurityHelper::can_manage_settings();
			}
			if ( in_array( $action, array( 'display_log', 'download_log', 'download_media_log', 'download_failed_log', 'delete_log' ), true ) ) {
				$capability = SecurityHelper::can_download_logs();
			}

			if ( ! current_user_can( $capability ) ) {
				self::deny();
			}
		}
	}

	/**
	 * Send 403 JSON response and exit.
	 */
	private static function deny() {
		status_header( 403 );
		wp_send_json_error(
			array( 'message' => __( 'Unauthorized', 'wp-ultimate-csv-importer' ) ),
			403
		);
	}
}
