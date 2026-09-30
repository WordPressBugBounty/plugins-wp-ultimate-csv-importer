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
	 * Whether the current user may run an import (capability, super admin, or administrator role).
	 *
	 * @return bool
	 */
	public static function current_user_can_import() {
		if ( function_exists( 'is_super_admin' ) && is_super_admin() ) {
			return true;
		}
		if ( self::check_capability( self::can_import() ) ) {
			return true;
		}
		$user = wp_get_current_user();
		return is_array( $user->roles ) && in_array( 'administrator', $user->roles, true );
	}

	/**
	 * True when $path resolves inside $directory (realpath prefix check).
	 *
	 * @param string $path
	 * @param string $directory
	 * @return bool
	 */
	public static function is_path_inside_directory( $path, $directory ) {
		$real_dir = realpath( $directory );
		if ( false === $real_dir ) {
			return false;
		}
		$real_path = realpath( $path );
		if ( false === $real_path ) {
			$real_path = realpath( dirname( $path ) );
			if ( false === $real_path ) {
				return false;
			}
			$real_path = $real_path . DIRECTORY_SEPARATOR . basename( $path );
		}
		$real_path = wp_normalize_path( $real_path );
		$real_dir  = wp_normalize_path( trailingslashit( $real_dir ) );
		return strpos( $real_path, $real_dir ) === 0;
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
		$result = @unserialize( $data, array( 'allowed_classes' => false ) );
		if ( is_object( $result ) ) {
			return false;
		}
		return $result;
	}

	/**
	 * Generate a cryptographically secure opaque event/import key.
	 *
	 * Replaces predictable HMAC(filename + time, 'secret') naming for storage paths.
	 * Return value is hex-only so it is safe with sanitize_key() and filesystem paths.
	 *
	 * @param int $bytes Number of random bytes (default 16 => 32 hex chars).
	 * @return string
	 */
	public static function generate_secure_event_key( $bytes = 16 ) {
		$bytes = max( 16, \absint( $bytes ) );
		try {
			return bin2hex( random_bytes( $bytes ) );
		} catch ( \Exception $e ) {
			// Extremely unlikely fallback; still avoid filename/time/hardcoded secrets.
			return bin2hex( openssl_random_pseudo_bytes( $bytes ) );
		}
	}

	/**
	 * Whether an IP address is safe as an outbound FTP/HTTP destination.
	 *
	 * Rejects loopback, private, link-local, multicast, reserved, unique-local,
	 * IPv4-mapped IPv6, and other special-use ranges. Fail closed.
	 *
	 * @param string $ip IPv4 or IPv6 address.
	 * @return bool
	 */
	public static function is_public_ip_address( $ip ) {
		$ip = strtolower( trim( (string) $ip ) );
		if ( $ip === '' ) {
			return false;
		}

		// Strip IPv4-mapped IPv6 prefix and validate the embedded address.
		if ( preg_match( '/^::ffff:(\d{1,3}(?:\.\d{1,3}){3})$/', $ip, $m ) ) {
			return self::is_public_ip_address( $m[1] );
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		// PHP flags cover most private/reserved ranges; add explicit extras.
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
			return false;
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$long = ip2long( $ip );
			if ( false === $long ) {
				return false;
			}
			// 0.0.0.0/8, 100.64.0.0/10 (CGNAT), 198.18.0.0/15 (benchmarking).
			if ( ( $long & 0xff000000 ) === 0x00000000 ) {
				return false;
			}
			if ( ( $long & 0xffc00000 ) === 0x64400000 ) {
				return false;
			}
			if ( ( $long & 0xfffe0000 ) === 0xc6120000 ) {
				return false;
			}
			return true;
		}

		// IPv6: reject unique-local, link-local, multicast, unspecified, documentation.
		if ( preg_match( '/^(fc|fd)/i', $ip ) || strpos( $ip, 'fe80:' ) === 0 ) {
			return false;
		}
		if ( strpos( $ip, 'ff' ) === 0 || $ip === '::' || $ip === '::1' ) {
			return false;
		}
		if ( strpos( $ip, '2001:db8:' ) === 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Resolve a hostname (or literal IP) and ensure every address is public.
	 *
	 * @param string $host Hostname or IP.
	 * @return array|false { host, ips[], connect_ip } or false if blocked/unresolvable.
	 */
	public static function resolve_public_host( $host ) {
		$host = strtolower( trim( (string) $host ) );
		$host = preg_replace( '/^\[|\]$/', '', $host );
		$host = preg_replace( '/\.$/', '', $host );

		if ( $host === '' || strlen( $host ) > 253 ) {
			return false;
		}

		// Block obvious local/metadata hostnames before DNS.
		$blocked_names = array(
			'localhost',
			'localhost.localdomain',
			'metadata.google.internal',
			'metadata',
			'kubernetes.default',
			'kubernetes.default.svc',
		);
		if ( in_array( $host, $blocked_names, true ) ) {
			return false;
		}

		// Reject decimal/hex oddities by requiring valid DNS label or IP form.
		if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
			if ( ! self::is_public_ip_address( $host ) ) {
				return false;
			}
			return array(
				'host'       => $host,
				'ips'        => array( $host ),
				'connect_ip' => $host,
			);
		}

		if ( ! preg_match( '/^(?=.{1,253}$)([a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?)(\.[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?)*$/i', $host ) ) {
			return false;
		}

		$ips = array();

		if ( function_exists( 'dns_get_record' ) ) {
			$records = @dns_get_record( $host, DNS_A + DNS_AAAA );
			if ( is_array( $records ) ) {
				foreach ( $records as $record ) {
					if ( ! empty( $record['ip'] ) ) {
						$ips[] = $record['ip'];
					}
					if ( ! empty( $record['ipv6'] ) ) {
						$ips[] = $record['ipv6'];
					}
				}
			}
		}

		if ( empty( $ips ) && function_exists( 'gethostbynamel' ) ) {
			$v4 = @gethostbynamel( $host );
			if ( is_array( $v4 ) ) {
				$ips = array_merge( $ips, $v4 );
			}
		}

		if ( empty( $ips ) ) {
			$fallback = gethostbyname( $host );
			if ( $fallback && $fallback !== $host && filter_var( $fallback, FILTER_VALIDATE_IP ) ) {
				$ips[] = $fallback;
			}
		}

		$ips = array_values( array_unique( array_filter( $ips ) ) );
		/**
		 * Filter resolved IPs before public-address validation (tests / advanced hardening).
		 *
		 * @param string[] $ips  Resolved addresses.
		 * @param string   $host Hostname being resolved.
		 */
		$ips = apply_filters( 'smack_uci_resolved_ips', $ips, $host );
		if ( ! is_array( $ips ) || empty( $ips ) ) {
			return false;
		}
		$ips = array_values( array_unique( array_filter( $ips ) ) );

		foreach ( $ips as $ip ) {
			if ( ! self::is_public_ip_address( $ip ) ) {
				return false;
			}
		}

		// Prefer IPv4 for PHP ftp_connect compatibility.
		$connect_ip = $ips[0];
		foreach ( $ips as $ip ) {
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				$connect_ip = $ip;
				break;
			}
		}

		return array(
			'host'       => $host,
			'ips'        => $ips,
			'connect_ip' => $connect_ip,
		);
	}

	/**
	 * Validate FTP host + port for outbound connections (SSRF protection).
	 *
	 * Custom FTP ports are allowed (product requirement) but only to public IPs.
	 * Returns a connect_ip so callers can avoid a second DNS lookup (DNS rebinding).
	 *
	 * @param string     $host Hostname or IP.
	 * @param int|string $port TCP port.
	 * @return array|false { host, port, ips[], connect_ip } or false.
	 */
	public static function validate_ftp_destination( $host, $port ) {
		if ( ! is_numeric( $port ) ) {
			return false;
		}
		$port = (int) $port;
		if ( $port < 1 || $port > 65535 ) {
			return false;
		}

		$resolved = self::resolve_public_host( $host );
		if ( ! $resolved ) {
			return false;
		}

		$resolved['port'] = $port;
		return $resolved;
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

		$host = isset( $parsed['host'] ) ? (string) $parsed['host'] : '';
		$port = isset( $parsed['port'] ) ? (int) $parsed['port'] : ( strtolower( $parsed['scheme'] ) === 'https' ? 443 : 80 );

		if ( ! self::validate_ftp_destination( $host, $port ) ) {
			return false;
		}

		return $url;
	}

	/**
	 * Write web-server deny rules so sensitive upload payloads are not directly downloadable.
	 *
	 * Apache honors .htaccess; IIS honors web.config. Nginx does not — authenticated
	 * download handlers remain the primary control there.
	 *
	 * @param string $directory Absolute directory path.
	 * @param string $marker    Unique marker string used to detect/update existing rules.
	 * @return void
	 */
	public static function protect_sensitive_directory( $directory, $marker = 'deny direct download of import payloads' ) {
		$directory = trailingslashit( wp_normalize_path( (string) $directory ) );
		if ( $directory === '/' || ! is_dir( $directory ) ) {
			return;
		}

		$extensions = self::PAYLOAD_EXTENSION_PATTERN;
		$htaccess   = $directory . '.htaccess';
		$rules      = <<<HTACCESS
# WP Ultimate CSV Importer — {$marker}.
Options -Indexes
<IfModule mod_authz_core.c>
	<FilesMatch "(?i)^([a-f0-9]+|(.*\\.({$extensions})))$">
		Require all denied
	</FilesMatch>
</IfModule>
<IfModule !mod_authz_core.c>
	<FilesMatch "(?i)^([a-f0-9]+|(.*\\.({$extensions})))$">
		Order deny,allow
		Deny from all
	</FilesMatch>
</IfModule>
HTACCESS;
		$updated = null;
		if ( ! file_exists( $htaccess ) ) {
			$updated = $rules;
		} else {
			$existing = (string) file_get_contents( $htaccess );
			if ( strpos( $existing, $marker ) === false ) {
				$updated = $rules;
			} elseif ( strpos( $existing, '(' . $extensions . ')' ) === false ) {
				// Rules written by earlier versions left HTML event logs and SVG downloadable.
				$updated = str_replace( '(' . self::LEGACY_PAYLOAD_EXTENSION_PATTERN . ')', '(' . $extensions . ')', $existing );
				if ( $updated === $existing ) {
					$updated = $rules . "\n\n" . $existing;
				}
			}
		}
		if ( null !== $updated ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $htaccess, $updated );
		}

		$web_config = $directory . 'web.config';
		$refresh    = ! file_exists( $web_config );
		if ( ! $refresh ) {
			$existing_config = (string) file_get_contents( $web_config );
			$refresh         = false !== strpos( $existing_config, 'fileExtension=".csv" allowed="false"' )
				&& false === strpos( $existing_config, 'fileExtension=".html" allowed="false"' );
		}
		if ( $refresh ) {
			$config = <<<'WEBCONFIG'
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<system.webServer>
		<security>
			<requestFiltering>
				<fileExtensions>
					<add fileExtension=".csv" allowed="false" />
					<add fileExtension=".xml" allowed="false" />
					<add fileExtension=".xls" allowed="false" />
					<add fileExtension=".xlsx" allowed="false" />
					<add fileExtension=".tsv" allowed="false" />
					<add fileExtension=".zip" allowed="false" />
					<add fileExtension=".json" allowed="false" />
					<add fileExtension=".txt" allowed="false" />
					<add fileExtension=".html" allowed="false" />
					<add fileExtension=".htm" allowed="false" />
					<add fileExtension=".xhtml" allowed="false" />
					<add fileExtension=".svg" allowed="false" />
					<add fileExtension=".svgz" allowed="false" />
				</fileExtensions>
			</requestFiltering>
		</security>
	</system.webServer>
</configuration>
WEBCONFIG;
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $web_config, $config );
		}

		$index = $directory . 'index.php';
		if ( ! file_exists( $index ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}

	/**
	 * Write web-server deny rules so import payloads are not directly downloadable.
	 *
	 * @param string $directory Absolute directory path.
	 * @return void
	 */
	public static function protect_import_directory( $directory ) {
		self::protect_sensitive_directory( $directory, 'deny direct download of import payloads' );
	}

	/**
	 * Protect the Single Import/Export CSV directory from direct web access.
	 *
	 * @param string $directory Absolute directory path.
	 * @return void
	 */
	public static function protect_single_export_directory( $directory ) {
		self::protect_sensitive_directory( $directory, 'deny direct download of single-export payloads' );
	}

	/**
	 * Directory name that roots every plugin-generated upload artifact.
	 */
	const UPLOAD_ROOT_SEGMENT = 'smack_uci_uploads';

	/**
	 * File extensions denied by the import-directory rules.
	 */
	const PAYLOAD_EXTENSION_PATTERN = 'csv|xml|xls|xlsx|tsv|zip|json|txt|html?|xhtml|svgz?';

	/**
	 * Extension list written by versions that did not deny HTML event logs or SVG.
	 */
	const LEGACY_PAYLOAD_EXTENSION_PATTERN = 'csv|xml|xls|xlsx|tsv|zip|json|txt';

	/**
	 * Set once every pre-existing log directory has been denied over HTTP.
	 */
	const LOG_DIRECTORIES_PROTECTED_OPTION = 'smack_uci_log_directories_protected_v1';

	/**
	 * Log directories (relative to the smack_uci_uploads root) whose per-event subfolders hold logs.
	 */
	const LOG_PARENT_DIRECTORIES = array( 'summary_logs', 'media_log', 'failed_media_logs', 'imports/import_logs', 'imports/media_logs', 'imports/failed_media_logs' );

	/**
	 * Mode for plugin-created directories. Never world-writable.
	 *
	 * @return int
	 */
	public static function get_directory_permissions() {
		$perms = (int) apply_filters( 'smack_uci_directory_permissions', 0755 );
		return ( $perms & 0775 ) | 0700;
	}

	/**
	 * Mode for plugin-created files. Never world-writable or executable.
	 *
	 * @return int
	 */
	public static function get_file_permissions() {
		$perms = (int) apply_filters( 'smack_uci_file_permissions', 0644 );
		return ( $perms & 0664 ) | 0600;
	}

	/**
	 * Apply the plugin's permission policy to a path owned by the PHP process.
	 *
	 * Symlinks are skipped because chmod() follows them. Paths owned by another
	 * user cannot be chmod()ed and are left untouched rather than raising warnings.
	 *
	 * @param string    $path   Absolute path.
	 * @param bool|null $is_dir Force directory/file mode; auto-detected when null.
	 * @return bool True when the path ends up with the expected mode.
	 */
	public static function apply_secure_permissions( $path, $is_dir = null ) {
		$path = (string) $path;
		if ( '' === $path || is_link( $path ) || ! file_exists( $path ) ) {
			return false;
		}
		if ( null === $is_dir ) {
			$is_dir = is_dir( $path );
		}
		$perms = $is_dir ? self::get_directory_permissions() : self::get_file_permissions();
		clearstatcache( true, $path );
		if ( ( fileperms( $path ) & 07777 ) === $perms ) {
			return true;
		}
		if ( function_exists( 'posix_geteuid' ) && fileowner( $path ) !== posix_geteuid() ) {
			return false;
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod
		return @chmod( $path, $perms );
	}

	/**
	 * Apply the file permission policy to a plugin-written file.
	 *
	 * @param string $file Absolute file path.
	 * @return bool
	 */
	public static function secure_file( $file ) {
		return self::apply_secure_permissions( $file, false );
	}

	/**
	 * Return the smack_uci_uploads root that contains $path, or '' if none.
	 *
	 * @param string $path Absolute path.
	 * @return string Root path without trailing slash.
	 */
	public static function get_upload_root_for_path( $path ) {
		$path   = untrailingslashit( wp_normalize_path( (string) $path ) );
		$needle = '/' . self::UPLOAD_ROOT_SEGMENT . '/';
		$pos    = strpos( $path . '/', $needle );
		if ( false === $pos ) {
			return '';
		}
		return substr( $path, 0, $pos + strlen( $needle ) - 1 );
	}

	/**
	 * Create a plugin data directory without world-writable permissions.
	 *
	 * wp_mkdir_p() copies the parent directory's mode onto new directories, so every
	 * level from the smack_uci_uploads root down to $directory is tightened both
	 * before and after creation. The root also receives script-execution protection.
	 *
	 * @param string $directory Absolute directory path.
	 * @return bool True if the directory exists afterwards.
	 */
	public static function ensure_secure_directory( $directory ) {
		$directory = untrailingslashit( wp_normalize_path( (string) $directory ) );
		if ( '' === $directory ) {
			return false;
		}
		$root = self::get_upload_root_for_path( $directory );

		if ( '' !== $root ) {
			self::secure_directory_chain( $directory, $root );
		}
		if ( ! is_dir( $directory ) && ! wp_mkdir_p( $directory ) && ! is_dir( $directory ) ) {
			return false;
		}
		if ( '' === $root ) {
			self::apply_secure_permissions( $directory, true );
			return true;
		}
		self::secure_directory_chain( $directory, $root );
		self::protect_upload_root( $root );
		return true;
	}

	/**
	 * Tighten each existing directory between $root and $directory (inclusive).
	 *
	 * @param string $directory Normalized path without trailing slash.
	 * @param string $root      Normalized root path without trailing slash.
	 * @return void
	 */
	private static function secure_directory_chain( $directory, $root ) {
		$current = $directory;
		while ( strlen( $current ) >= strlen( $root ) ) {
			if ( is_dir( $current ) ) {
				self::apply_secure_permissions( $current, true );
			}
			if ( $current === $root ) {
				break;
			}
			$current = dirname( $current );
		}
	}

	/**
	 * Block script execution and directory listing under the smack_uci_uploads root.
	 *
	 * Data files (CSV/XML/ZIP exports, logs, images) stay downloadable because bulk
	 * exports and log links are served by direct URL. Only executable types are denied.
	 * Nginx ignores these files; see SECURITY-0777-WEBROOT-AUDIT.md for the server rule.
	 *
	 * @param string $root Absolute path to a smack_uci_uploads directory.
	 * @return void
	 */
	public static function protect_upload_root( $root ) {
		static $protected = array();

		$root = trailingslashit( wp_normalize_path( (string) $root ) );
		if ( isset( $protected[ $root ] ) || '/' === $root || ! is_dir( $root ) ) {
			return;
		}
		$protected[ $root ] = true;

		foreach ( array( '.htaccess', 'web.config', 'index.php' ) as $root_file ) {
			self::quarantine_untrusted_file( $root . $root_file );
		}

		$begin = '# BEGIN WP Ultimate CSV Importer script protection';
		$end   = '# END WP Ultimate CSV Importer script protection';
		$rules = <<<'HTACCESS'
Options -Indexes -ExecCGI
<IfModule mod_authz_core.c>
	<FilesMatch "(?i)(\.(php[0-9]*|pht|phtml|phar|phps)(\.|$)|\.(cgi|pl|py|sh|shtml|asp|aspx|jsp)$|^\.ht|\.uci-quarantine-)">
		Require all denied
	</FilesMatch>
</IfModule>
<IfModule !mod_authz_core.c>
	<FilesMatch "(?i)(\.(php[0-9]*|pht|phtml|phar|phps)(\.|$)|\.(cgi|pl|py|sh|shtml|asp|aspx|jsp)$|^\.ht|\.uci-quarantine-)">
		Order allow,deny
		Deny from all
	</FilesMatch>
</IfModule>
<IfModule mod_php.c>
	php_flag engine off
</IfModule>
<IfModule mod_php7.c>
	php_flag engine off
</IfModule>
<IfModule mod_php5.c>
	php_flag engine off
</IfModule>
HTACCESS;
		/**
		 * Filter the Apache rules written to the smack_uci_uploads root .htaccess.
		 *
		 * @param string $rules Rules placed between the BEGIN/END markers.
		 * @param string $root  Absolute root path.
		 */
		$rules = (string) apply_filters( 'smack_uci_upload_root_htaccess_rules', $rules, $root );
		$block = $begin . "\n" . trim( $rules ) . "\n" . $end . "\n";

		$htaccess = $root . '.htaccess';
		$existing = is_file( $htaccess ) ? (string) file_get_contents( $htaccess ) : '';
		if ( false === strpos( $existing, $block ) ) {
			$pattern = '/' . preg_quote( $begin, '/' ) . '.*?' . preg_quote( $end, '/' ) . '\n?/s';
			if ( preg_match( $pattern, $existing ) ) {
				$updated = preg_replace_callback(
					$pattern,
					function () use ( $block ) {
						return $block;
					},
					$existing,
					1
				);
			} else {
				$updated = ( '' === trim( $existing ) ) ? $block : rtrim( $existing ) . "\n\n" . $block;
			}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			if ( false === @file_put_contents( $htaccess, $updated, LOCK_EX ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'WP Ultimate CSV Importer: unable to write script protection to ' . $htaccess );
			}
		}
		self::secure_file( $htaccess );

		$web_config = $root . 'web.config';
		if ( ! file_exists( $web_config ) ) {
			$config = <<<'WEBCONFIG'
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<system.webServer>
		<directoryBrowse enabled="false" />
		<security>
			<requestFiltering>
				<fileExtensions>
					<add fileExtension=".php" allowed="false" />
					<add fileExtension=".php3" allowed="false" />
					<add fileExtension=".php4" allowed="false" />
					<add fileExtension=".php5" allowed="false" />
					<add fileExtension=".php7" allowed="false" />
					<add fileExtension=".php8" allowed="false" />
					<add fileExtension=".phtml" allowed="false" />
					<add fileExtension=".pht" allowed="false" />
					<add fileExtension=".phar" allowed="false" />
					<add fileExtension=".phps" allowed="false" />
					<add fileExtension=".cgi" allowed="false" />
					<add fileExtension=".pl" allowed="false" />
					<add fileExtension=".py" allowed="false" />
					<add fileExtension=".asp" allowed="false" />
					<add fileExtension=".aspx" allowed="false" />
					<add fileExtension=".jsp" allowed="false" />
				</fileExtensions>
			</requestFiltering>
		</security>
	</system.webServer>
</configuration>
WEBCONFIG;
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $web_config, $config );
			self::secure_file( $web_config );
		}

		$index = $root . 'index.php';
		if ( ! file_exists( $index ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $index, "<?php\n// Silence is golden.\n" );
			self::secure_file( $index );
		}
	}

	/**
	 * Candidate smack_uci_uploads roots for this site.
	 *
	 * Some writers build paths from WP_CONTENT_DIR/ABSPATH instead of wp_upload_dir(),
	 * which differs on multisite and custom UPLOADS setups, so both are included.
	 *
	 * @return string[]
	 */
	public static function get_upload_roots() {
		$candidates = array();
		$upload     = wp_upload_dir( null, false );
		if ( ! empty( $upload['basedir'] ) ) {
			$candidates[] = trailingslashit( $upload['basedir'] ) . self::UPLOAD_ROOT_SEGMENT;
		}
		$candidates[] = WP_CONTENT_DIR . '/uploads/' . self::UPLOAD_ROOT_SEGMENT;
		$candidates[] = ABSPATH . 'wp-content/uploads/' . self::UPLOAD_ROOT_SEGMENT;

		$roots = array();
		foreach ( $candidates as $candidate ) {
			$real = realpath( $candidate );
			if ( false !== $real && is_dir( $real ) ) {
				$roots[ wp_normalize_path( $real ) ] = true;
			}
		}
		return array_keys( $roots );
	}

	/**
	 * Rename an executable or server-config file that another local user could have planted or edited.
	 *
	 * Apache and IIS honor per-directory .htaccess/web.config files, so one left in a
	 * formerly world-writable directory can override the root protection. Only files
	 * inside a directory owned by the PHP process are considered; the renamed copy is
	 * kept for forensic review and is ignored by the web server.
	 *
	 * @param string $path Absolute file path.
	 * @return bool True when the file was quarantined.
	 */
	public static function quarantine_untrusted_file( $path ) {
		$path = (string) $path;
		if ( '' === $path || is_link( $path ) || ! is_file( $path ) ) {
			return false;
		}
		$name = strtolower( basename( $path ) );
		if ( false !== strpos( $name, '.uci-quarantine-' ) ) {
			return false;
		}
		if ( 'web.config' !== $name && ! self::is_executable_filename( $name ) ) {
			return false;
		}
		clearstatcache( true, $path );
		$untrusted = (bool) ( fileperms( $path ) & 0002 );
		if ( function_exists( 'posix_geteuid' ) ) {
			$euid = posix_geteuid();
			if ( fileowner( dirname( $path ) ) !== $euid ) {
				return false;
			}
			$owner     = fileowner( $path );
			$untrusted = $untrusted || ( $owner !== $euid && 0 !== $owner );
		}
		if ( ! $untrusted ) {
			return false;
		}
		$target = $path . '.uci-quarantine-' . time();
		// phpcs:ignore WordPress.WP.AlternativeFunctions.rename_rename
		if ( ! @rename( $path, $target ) ) {
			return false;
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'WP Ultimate CSV Importer: quarantined untrusted file ' . $path . ' as ' . basename( $target ) );
		return true;
	}

	/**
	 * Remove world-writable bits left on existing plugin directories and files.
	 *
	 * Symlinks are neither followed nor modified. Executable or server-config files
	 * that another user could have planted are quarantined.
	 *
	 * @param string $root Absolute smack_uci_uploads path.
	 * @return array{fixed:int,skipped:int,quarantined:int,foreign_dirs:int} Counts of
	 *         repaired, un-repairable, quarantined entries and directories owned by another user.
	 */
	public static function harden_upload_tree( $root ) {
		$result = array(
			'fixed'        => 0,
			'skipped'      => 0,
			'quarantined'  => 0,
			'foreign_dirs' => 0,
		);
		$root = untrailingslashit( wp_normalize_path( (string) $root ) );
		if ( '' === $root || is_link( $root ) || ! is_dir( $root ) ) {
			return $result;
		}

		$entries = array( new \SplFileInfo( $root ) );
		try {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $root, \FilesystemIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::SELF_FIRST,
				\RecursiveIteratorIterator::CATCH_GET_CHILD
			);
			foreach ( $iterator as $entry ) {
				$entries[] = $entry;
			}
		} catch ( \Exception $e ) {
			// Unreadable subtree; repair what was collected.
			unset( $e );
		}

		foreach ( $entries as $entry ) {
			if ( $entry->isLink() ) {
				continue;
			}
			$path = $entry->getPathname();
			clearstatcache( true, $path );
			if ( ! $entry->isDir() && self::quarantine_untrusted_file( $path ) ) {
				++$result['quarantined'];
				continue;
			}
			if ( $entry->isDir() && function_exists( 'posix_geteuid' ) && fileowner( $path ) !== posix_geteuid() ) {
				++$result['foreign_dirs'];
			}
			if ( 0 === ( fileperms( $path ) & 0002 ) ) {
				continue;
			}
			if ( self::apply_secure_permissions( $path, $entry->isDir() ) ) {
				++$result['fixed'];
			} else {
				++$result['skipped'];
			}
		}
		return $result;
	}

	/**
	 * One-time repair of directories created as 0777 by earlier plugin versions.
	 *
	 * @return void
	 */
	public static function maybe_harden_existing_upload_tree() {
		$option = 'smack_uci_upload_permissions_hardened';
		if ( get_option( $option ) ) {
			return;
		}
		// add_option() fails if a concurrent request already claimed the run.
		if ( ! add_option( $option, time() ) ) {
			return;
		}
		foreach ( self::get_upload_roots() as $root ) {
			self::protect_upload_root( $root );
			$result = self::harden_upload_tree( $root );
			if ( is_dir( $root . '/imports' ) ) {
				self::protect_import_directory( $root . '/imports' );
			}
			if ( is_dir( $root . '/single_exports' ) ) {
				self::protect_single_export_directory( $root . '/single_exports' );
			}
			if ( $result['skipped'] > 0 ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( sprintf( 'WP Ultimate CSV Importer: %d world-writable entries under %s are owned by another user and could not be repaired.', $result['skipped'], $root ) );
			}
			if ( $result['foreign_dirs'] > 0 ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( sprintf( 'WP Ultimate CSV Importer: %d directories under %s are owned by another user; review them for planted .htaccess or script files.', $result['foreign_dirs'], $root ) );
			}
		}
	}

	/**
	 * Whether a filename could be executed by a web server that maps handlers by extension.
	 *
	 * Checks every dot-separated segment (e.g. "x.php.jpg") and rejects dotfiles
	 * such as .htaccess and .user.ini.
	 *
	 * @param string $filename File name or path.
	 * @return bool
	 */
	public static function is_executable_filename( $filename ) {
		$name = basename( wp_normalize_path( (string) $filename ) );
		if ( '' === $name || '.' === $name[0] ) {
			return true;
		}
		return (bool) preg_match( '/\.(php[0-9]*|pht|phtml|phar|phps|cgi|pl|py|sh|shtml|asp|aspx|jsp)(\.|$)/i', $name );
	}

	/**
	 * Extensions the importer may write into the public media library.
	 *
	 * SVG, HTML, XHTML and XML are excluded: browsers run script in them on the site origin.
	 *
	 * @return string[]
	 */
	public static function get_safe_media_extensions() {
		return array( 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp', 'bmp', 'avif', 'ico', 'tif', 'tiff', 'heic', 'pdf', 'mp4', 'm4v', 'mov', 'webm', 'mp3', 'm4a', 'wav', 'ogg', 'zip' );
	}

	/**
	 * Whether a media file name has an allowed, non-executable extension.
	 *
	 * @param string $filename File name or path.
	 * @return bool
	 */
	public static function is_safe_media_filename( $filename ) {
		$name = basename( wp_normalize_path( (string) $filename ) );
		if ( self::is_executable_filename( $name ) ) {
			return false;
		}
		$ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		return '' !== $ext && in_array( $ext, self::get_safe_media_extensions(), true );
	}

	/**
	 * Whether file content is a markup document a browser could render with script (SVG, HTML, XML).
	 *
	 * Every allowed media format is binary or starts with a signature; markup starts with "<".
	 *
	 * @param string $content File content or its first bytes.
	 * @return bool
	 */
	public static function is_active_media_content( $content ) {
		$sample = substr( (string) $content, 0, 8192 );
		if ( '' === $sample ) {
			return false;
		}
		$start = ltrim( preg_replace( '/^(\xEF\xBB\xBF|\xFE\xFF|\xFF\xFE)/', '', $sample ) );
		if ( '' !== $start && '<' === $start[0] ) {
			return true;
		}
		if ( function_exists( 'finfo_open' ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			if ( $finfo ) {
				$mime = (string) finfo_buffer( $finfo, $sample );
				finfo_close( $finfo );
				$active = array( 'image/svg+xml', 'image/svg', 'text/html', 'application/xhtml+xml', 'text/xml', 'application/xml', 'text/javascript', 'application/javascript' );
				if ( in_array( strtolower( $mime ), $active, true ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * File name under which downloaded media may be saved publicly, or '' to refuse it.
	 *
	 * The requested name (from a URL, CSV column or media setting) keeps its base name; an
	 * extension outside the allow-list is replaced by one derived from the content.
	 *
	 * @param string $filename Requested destination file name.
	 * @param string $content  File content or its first bytes.
	 * @return string
	 */
	public static function get_safe_media_filename( $filename, $content ) {
		$name = basename( wp_normalize_path( (string) $filename ) );
		if ( self::is_active_media_content( $content ) ) {
			return '';
		}
		if ( self::is_safe_media_filename( $name ) ) {
			return $name;
		}

		$mime = '';
		if ( function_exists( 'finfo_open' ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			if ( $finfo ) {
				$mime = strtolower( (string) finfo_buffer( $finfo, substr( (string) $content, 0, 8192 ) ) );
				finfo_close( $finfo );
			}
		}
		$mime_extensions = array(
			'image/jpeg'               => 'jpg',
			'image/pjpeg'              => 'jpg',
			'image/png'                => 'png',
			'image/gif'                => 'gif',
			'image/webp'               => 'webp',
			'image/bmp'                => 'bmp',
			'image/x-ms-bmp'           => 'bmp',
			'image/avif'               => 'avif',
			'image/x-icon'             => 'ico',
			'image/vnd.microsoft.icon' => 'ico',
			'image/tiff'               => 'tif',
			'image/heic'               => 'heic',
			'application/pdf'          => 'pdf',
			'video/mp4'                => 'mp4',
			'video/quicktime'          => 'mov',
			'video/webm'               => 'webm',
			'audio/mpeg'               => 'mp3',
			'audio/mp4'                => 'm4a',
			'audio/x-wav'              => 'wav',
			'audio/wav'                => 'wav',
			'audio/ogg'                => 'ogg',
			'application/zip'          => 'zip',
		);
		if ( ! isset( $mime_extensions[ $mime ] ) ) {
			return '';
		}

		$base = preg_replace( '/[^a-zA-Z0-9_\-]/', '-', (string) pathinfo( $name, PATHINFO_FILENAME ) );
		$base = trim( (string) $base, '-' );
		if ( '' === $base ) {
			$base = 'media-' . substr( md5( (string) $filename ), 0, 8 );
		}
		return $base . '.' . $mime_extensions[ $mime ];
	}

	/**
	 * Deny direct HTTP access to every existing log directory, including ones created by
	 * versions that stored logs under predictable names without protection.
	 *
	 * @return bool True when every existing log directory carries the deny rules.
	 */
	public static function protect_existing_log_directories() {
		$complete = true;
		foreach ( self::get_upload_roots() as $root ) {
			foreach ( self::LOG_PARENT_DIRECTORIES as $relative ) {
				$parent = $root . '/' . $relative;
				if ( ! is_dir( $parent ) ) {
					continue;
				}
				self::protect_log_directory( $parent );
				$htaccess = $parent . '/.htaccess';
				if ( ! is_file( $htaccess ) || false === strpos( (string) file_get_contents( $htaccess ), 'deny direct download of import logs' ) ) {
					$complete = false;
				}
			}
			if ( is_dir( $root . '/imports' ) ) {
				self::protect_import_directory( $root . '/imports' );
				$htaccess = $root . '/imports/.htaccess';
				if ( ! is_file( $htaccess ) || false === strpos( (string) file_get_contents( $htaccess ), '(' . self::PAYLOAD_EXTENSION_PATTERN . ')' ) ) {
					$complete = false;
				}
			}
		}
		return $complete;
	}

	/**
	 * Run protect_existing_log_directories() once after an update; retried until the rules are in place.
	 *
	 * @return void
	 */
	public static function maybe_protect_existing_log_directories() {
		if ( get_option( self::LOG_DIRECTORIES_PROTECTED_OPTION ) ) {
			return;
		}
		if ( self::protect_existing_log_directories() ) {
			update_option( self::LOG_DIRECTORIES_PROTECTED_OPTION, time() );
		}
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

	/**
	 * Deny all direct HTTP access to import/summary/media log directories.
	 *
	 * Stronger than FilesMatch-based rules: logs must only be served through
	 * authenticated admin-ajax handlers (Nginx ignores .htaccess).
	 *
	 * @param string $directory Absolute directory path.
	 * @return void
	 */
	public static function protect_log_directory( $directory ) {
		$directory = trailingslashit( wp_normalize_path( (string) $directory ) );
		if ( $directory === '/' || $directory === '' ) {
			return;
		}
		if ( ! self::ensure_secure_directory( untrailingslashit( $directory ) ) ) {
			return;
		}

		$marker   = 'deny direct download of import logs';
		$htaccess = $directory . '.htaccess';
		$rules    = <<<HTACCESS
# WP Ultimate CSV Importer — {$marker}.
Options -Indexes
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>
HTACCESS;
		$needs_htaccess = ! file_exists( $htaccess );
		if ( ! $needs_htaccess ) {
			$existing       = (string) file_get_contents( $htaccess );
			$needs_htaccess = ( strpos( $existing, $marker ) === false );
		}
		if ( $needs_htaccess ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $htaccess, $rules );
			self::secure_file( $htaccess );
		}

		$web_config = $directory . 'web.config';
		if ( ! file_exists( $web_config ) ) {
			$config = <<<'WEBCONFIG'
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
	<system.webServer>
		<security>
			<authorization>
				<deny users="*" />
			</authorization>
		</security>
	</system.webServer>
</configuration>
WEBCONFIG;
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $web_config, $config );
			self::secure_file( $web_config );
		}

		$index = $directory . 'index.php';
		if ( ! file_exists( $index ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $index, "<?php\n// Silence is golden.\n" );
			self::secure_file( $index );
		}
	}
}
