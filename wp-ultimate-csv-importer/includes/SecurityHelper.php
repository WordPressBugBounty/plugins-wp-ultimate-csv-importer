<?php
/**
 * Security Framework for WP Ultimate CSV Importer
 *
 * Provides centralized security mechanisms for AJAX, Import, Export,
 * capabilities, and credential masking.
 *
 * @package Smackcoders\UCI\Core
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class SecurityHelper {

	/**
	 * Get the required capability for imports.
	 * Allow third-party add-ons to filter this capability.
	 *
	 * @return string Capability string.
	 */
	public static function can_import() {
		return apply_filters( 'smack_import_capability', 'manage_options' );
	}

	/**
	 * Get the required capability for exports.
	 *
	 * @return string Capability string.
	 */
	public static function can_export() {
		return apply_filters( 'smack_export_capability', 'manage_options' );
	}

	/**
	 * Get the required capability to manage settings.
	 *
	 * @return string Capability string.
	 */
	public static function can_manage_settings() {
		return apply_filters( 'smack_manage_settings_capability', 'manage_options' );
	}

	/**
	 * Get the required capability to download logs.
	 *
	 * @return string Capability string.
	 */
	public static function can_download_logs() {
		return apply_filters( 'smack_download_logs_capability', 'manage_options' );
	}

	/**
	 * Check if the current user has the required capability.
	 *
	 * @param string $capability The capability to check.
	 * @return bool True if they have capability, false otherwise.
	 */
	public static function check_capability( $capability ) {
		return current_user_can( $capability );
	}

	/**
	 * Check if user is an admin.
	 *
	 * @return bool
	 */
	public static function check_admin_access() {
		return self::check_capability( self::can_manage_settings() );
	}

	/**
	 * Verify standard WordPress nonce.
	 *
	 * @param string $nonce The nonce value.
	 * @param string $action The nonce action.
	 * @return false|int 1 if valid, 2 if valid and 24h old, false if invalid.
	 */
	public static function verify_nonce( $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Verify an AJAX nonce using check_ajax_referer().
	 * Dies if invalid.
	 *
	 * @param string $action The action/nonce name.
	 * @param string $query_arg The key in $_REQUEST. Default is 'securekey' for this plugin.
	 */
	public static function verify_ajax_nonce( $action = 'smack-ultimate-csv-importer', $query_arg = 'securekey' ) {
		check_ajax_referer( $action, $query_arg );
	}

	/**
	 * Verify REST API permissions (if any REST endpoints exist).
	 *
	 * @return bool
	 */
	public static function verify_rest_permission() {
		return self::check_capability( self::can_manage_settings() );
	}

	/**
	 * Validate if current user can download protected files.
	 * Dies if unauthorized.
	 */
	public static function validate_download_access() {
		if ( ! self::check_capability( self::can_download_logs() ) ) {
			wp_die( __( 'You do not have sufficient permissions to download this file.', 'wp-ultimate-csv-importer' ), 403 );
		}
	}

	/**
	 * Mask sensitive credentials to prevent exposure in logs or UI.
	 * Example: mypassword123 -> ********
	 *
	 * @param string $data The sensitive string.
	 * @return string Masked string.
	 */
	public static function mask_credentials( $data ) {
		if ( empty( $data ) ) {
			return $data;
		}
		// Mask everything except the first and last two characters if length > 6
		$length = strlen( $data );
		if ( $length <= 4 ) {
			return str_repeat( '*', $length );
		}
		if ( $length > 6 ) {
			return substr( $data, 0, 2 ) . str_repeat( '*', $length - 4 ) . substr( $data, -2 );
		}
		return str_repeat( '*', $length );
	}

	/**
	 * Deep sanitize request arrays (like $_POST).
	 *
	 * @param mixed $data The array or string to sanitize.
	 * @return mixed Sanitized data.
	 */
	public static function sanitize_request_data( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = self::sanitize_request_data( $value );
			}
			return $data;
		}
		return sanitize_text_field( $data );
	}

	/**
	 * Sanitize a filename.
	 *
	 * @param string $filename The filename to sanitize.
	 * @return string
	 */
	public static function sanitize_filename( $filename ) {
		return sanitize_file_name( $filename );
	}

	/**
	 * Validate that a file path is safe and does not contain path traversal.
	 *
	 * @param string $path The absolute or relative path to validate.
	 * @return bool True if valid, false if invalid/traversal detected.
	 */
	public static function validate_file_path( $path ) {
		if ( empty( $path ) ) {
			return false;
		}
		// Prevent path traversal
		if ( strpos( $path, '../' ) !== false || strpos( $path, '..\\' ) !== false ) {
			return false;
		}
		return true;
	}

	/**
	 * Required capability for plugin installation.
	 *
	 * @return bool
	 */
	public static function can_install_plugins() {
		return current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' );
	}

	/**
	 * Verify AJAX nonce and capability in one call.
	 *
	 * @param string $capability  Capability to require.
	 * @param string $nonce_action Nonce action name.
	 * @param string $query_arg   Request key holding the nonce.
	 */
	public static function enforce_ajax_request( $capability, $nonce_action = 'smack-ultimate-csv-importer', $query_arg = 'securekey' ) {
		self::verify_ajax_nonce( $nonce_action, $query_arg );
		if ( ! self::check_capability( $capability ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Unauthorized', 'wp-ultimate-csv-importer' ) ),
				403
			);
		}
	}

	/**
	 * Safe unserialize replacement — blocks object injection.
	 *
	 * @param string $data Serialized string.
	 * @return mixed
	 */
	public static function safe_unserialize( $data ) {
		if ( ! is_string( $data ) || $data === '' ) {
			return false;
		}
		return @unserialize( $data, array( 'allowed_classes' => false ) );
	}

	/**
	 * Validate a remote import URL (SSRF protection).
	 *
	 * @param string $url URL to validate.
	 * @return string|false Sanitized URL or false if blocked.
	 */
	public static function validate_remote_url( $url ) {
		$url = esc_url_raw( trim( (string) $url ) );
		if ( $url === '' ) {
			return false;
		}

		$parsed = wp_parse_url( $url );
		if ( empty( $parsed['scheme'] ) || ! in_array( strtolower( $parsed['scheme'] ), array( 'http', 'https', 'ftp', 'ftps' ), true ) ) {
			return false;
		}

		$host = strtolower( (string) ( $parsed['host'] ?? '' ) );
		if ( $host === '' ) {
			return false;
		}

		$blocked_hosts = array(
			'localhost',
			'127.0.0.1',
			'0.0.0.0',
			'::1',
			'metadata.google.internal',
		);
		if ( in_array( $host, $blocked_hosts, true ) ) {
			return false;
		}

		// Block link-local / metadata ranges.
		if ( preg_match( '/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.|169\.254\.|127\.)/', $host ) ) {
			return false;
		}

		$ip = gethostbyname( $host );
		if ( $ip !== $host && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
				return false;
			}
		}

		return $url;
	}

	/**
	 * Reject dangerous upload file extensions.
	 *
	 * @param string $filename Filename or path.
	 * @return bool True if extension is allowed.
	 */
	public static function is_allowed_import_extension( $filename ) {
		$ext = strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
		$blocked = array( 'php', 'phtml', 'phar', 'cgi', 'pl', 'exe', 'sh', 'bash', 'js', 'htaccess' );
		if ( in_array( $ext, $blocked, true ) ) {
			return false;
		}
		$allowed = array( 'csv', 'xml', 'json', 'xls', 'xlsx', 'txt', 'tsv', 'zip' );
		return in_array( $ext, $allowed, true );
	}
}
