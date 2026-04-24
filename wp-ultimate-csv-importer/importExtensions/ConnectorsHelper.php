<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper for WordPress 7.0 Global AI Connectors.
 *
 * Fetches API credentials from Settings → Connectors instead of plugin-level storage.
 *
 * @since 8.2.0
 */
class ConnectorsHelper {

	/**
	 * Provider option names used by WordPress 7.0 Connectors.
	 *
	 * @var array<string, string>
	 */
	private static $option_names = array(
		'openai'    => 'connectors_ai_openai_api_key',
		'google'    => 'connectors_ai_google_api_key',
		'anthropic' => 'connectors_ai_anthropic_api_key',
	);

	/**
	 * Get WordPress Connectors API key for a provider.
	 *
	 * The option is masked when using get_option() directly. This method
	 * temporarily removes the mask filter to fetch the real key.
	 *
	 * @param string $provider One of: 'openai', 'google', 'anthropic'.
	 * @return string The API key, or empty string if not set.
	 */
	public static function get_api_key( $provider ) {
		$valid = array( 'openai', 'google', 'anthropic' );
		if ( ! in_array( $provider, $valid, true ) ) {
			return '';
		}

		$option_name   = self::$option_names[ $provider ];
		$mask_callback = '_wp_connectors_mask_api_key';

		if ( ! function_exists( $mask_callback ) ) {
			return (string) get_option( $option_name, '' );
		}

		remove_filter( "option_{$option_name}", $mask_callback );
		$value = get_option( $option_name, '' );
		add_filter( "option_{$option_name}", $mask_callback );

		return (string) $value;
	}

	/**
	 * Check if a provider has a valid (non-empty) key configured.
	 *
	 * @param string $provider One of: 'openai', 'google', 'anthropic'.
	 * @return bool True if configured.
	 */
	public static function is_configured( $provider ) {
		$key = self::get_api_key( $provider );
		return $key !== '' && strlen( $key ) > 4;
	}

	/**
	 * Check if any AI connector is configured in WordPress.
	 *
	 * @return bool True if at least one provider has a key.
	 */
	public static function has_any_connector() {
		foreach ( array_keys( self::$option_names ) as $provider ) {
			if ( self::is_configured( $provider ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if WordPress 7.0 AI Client is available.
	 *
	 * @return bool True if wp_ai_client_prompt exists.
	 */
	public static function is_wp_ai_available() {
		return function_exists( 'wp_ai_client_prompt' );
	}

	/**
	 * Get AI configuration status for frontend.
	 *
	 * Relies entirely on WordPress 7.0 Global AI Settings (Settings → Connectors).
	 *
	 * @return array{configured: bool, source: string, settings_url: string}
	 */
	public static function get_status() {
		$configured = self::is_wp_ai_available() && self::has_any_connector();

		return array(
			'configured'    => (bool) $configured,
			'source'       => $configured ? 'wordpress' : 'none',
			'settings_url' => admin_url( 'options-connectors.php' ),
		);
	}
}
