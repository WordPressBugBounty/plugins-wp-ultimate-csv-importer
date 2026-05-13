<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI content generation helper.
 *
 * Relies entirely on WordPress 7.0 Global AI Settings (Settings → Connectors).
 * No plugin-level API key storage or fallback.
 *
 * @since 8.2.0
 */
class OpenAIHelper {


	/**
	 * Generate text content from a prompt.
	 *
	 * Uses WordPress 7.0 wp_ai_client_prompt() when Connectors are configured.
	 *
	 * @param string $prompt         The prompt text.
	 * @param string|int $max_words  Unused (kept for backward compatibility).
	 * @return string|int|false Generated text, HTTP error code, or false on failure.
	 */
	public function generateContent( $prompt, $max_words = 0 ) {
		$prompt = is_string( $prompt ) ? trim( $prompt ) : '';
		if ( $prompt === '' ) {
			return false;
		}

		$wp_ai_available = function_exists( 'wp_ai_client_prompt' );
		$has_connector   = ConnectorsHelper::has_any_connector();

		if ( ! $wp_ai_available || ! $has_connector ) {
			return false;
		}
		// Send prompt directly. Do not modify or apply max token/word rules here.
		$result = $this->generate_content_via_wp_ai( $prompt );
		return $result;
	}

	/**
	 * Generate text using WordPress AI Client.
	 *
	 * @param string $prompt         The prompt text.
	 * @return string|int|false Generated text, HTTP error code, or false.
	 */
	private function generate_content_via_wp_ai( $prompt ) {

		$builder = wp_ai_client_prompt( $prompt );
		$result = $builder->generate_text();

		if ( is_wp_error( $result ) ) {
			$code = $result->get_error_code();
			return is_numeric( $code ) ? (int) $code : false;
		}

		if ( is_string( $result ) ) {
			return $result;
		}

		// Some WP AI implementations may return an object/array rather than a raw string.
		if ( is_object( $result ) ) {
			foreach ( array( 'getText', 'get_text', 'text', '__toString' ) as $m ) {
				if ( method_exists( $result, $m ) ) {
					$val = $result->$m();
					if ( is_string( $val ) && $val !== '' ) {
						return $val;
					}
				}
			}
		}
		if ( is_array( $result ) ) {
			if ( isset( $result['text'] ) && is_string( $result['text'] ) ) {
				return $result['text'];
			}
			if ( isset( $result['content'] ) && is_string( $result['content'] ) ) {
				return $result['content'];
			}
		}

		return false;
	}

		/**
	 * Generate image from a prompt.
	 *
	 * @param string $prompt The image prompt.
	 * @return string|int|false Image URL or data URI, HTTP error code, or false on failure.
	 */
	public function generateImage( $prompt ) {
		$prompt = is_string( $prompt ) ? trim( $prompt ) : '';
		if ( $prompt === '' ) {
			return false;
		}

		$wp_ai_available = function_exists( 'wp_ai_client_prompt' );
		$has_connector   = ConnectorsHelper::has_any_connector();

		if ( ! $wp_ai_available || ! $has_connector ) {
			return false;
		}
		$result = $this->generate_image_via_wp_ai( $prompt );
		return $result;
	}

	/**
	 * Generate image using WordPress AI Client.
	 *
	 * @param string $prompt The image prompt.
	 * @return string|int|false Image URL or data URI, HTTP error code, or false.
	 */
	private function generate_image_via_wp_ai( $prompt ) {
		$builder = wp_ai_client_prompt( $prompt );
		if ( ConnectorsHelper::is_configured( 'openai' ) && method_exists( $builder, 'using_provider' ) ) {
			$builder = $builder->using_provider( 'openai' );
		}

		$supported = method_exists( $builder, 'is_supported_for_image_generation' ) && $builder->is_supported_for_image_generation();

		if ( ! $supported && ! ConnectorsHelper::is_configured( 'openai' ) ) {
			return false;
		}

		$result = $builder->generate_image();
		if ( is_wp_error( $result ) ) {
			$code = $result->get_error_code();
			return is_numeric( $code ) ? (int) $code : false;
		}

		$url     = null;
		$data_uri = null;
		if ( is_object( $result ) && method_exists( $result, 'getUrl' ) ) {
			$url = $result->getUrl();
		}
		if ( is_object( $result ) && method_exists( $result, 'getDataUri' ) ) {
			$data_uri = $result->getDataUri();
		}
		if ( $url !== null && $url !== '' ) {
			return $url;
		}
		if ( $data_uri !== null && $data_uri !== '' ) {
			$file_url = $this->save_data_uri_to_upload( $data_uri );
			return $file_url !== null ? $file_url : $data_uri;
		}
		return false;
	}

	/**
	 * Save data URI (base64 image) to WordPress uploads and return the URL.
	 *
	 * MediaHandling expects http/https URLs; data URIs are not supported.
	 *
	 * @param string $data_uri Data URI like data:image/png;base64,iVBORw0...
	 * @return string|null File URL or null on failure.
	 */
	private function save_data_uri_to_upload( $data_uri ) {
		if ( ! is_string( $data_uri ) || strpos( $data_uri, 'data:image' ) !== 0 ) {
			return null;
		}
		if ( ! preg_match( '/^data:image\/(\w+);base64,(.+)$/', $data_uri, $m ) ) {
			return null;
		}
		$ext    = strtolower( $m[1] );
		$ext    = ( $ext === 'jpeg' ) ? 'jpg' : $ext;
		$binary = base64_decode( $m[2], true );
		if ( $binary === false || strlen( $binary ) < 100 ) {
			return null;
		}
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return null;
		}
		$subdir  = $upload['subdir'];
		$basedir = $upload['basedir'];
		$baseurl = $upload['baseurl'];
		$dir     = $basedir . $subdir;
		if ( ! wp_mkdir_p( $dir ) ) {
			return null;
		}
		$filename = 'ai-' . uniqid() . '.' . $ext;
		$filepath = $dir . '/' . $filename;
		if ( file_put_contents( $filepath, $binary ) === false ) {
			return null;
		}
		return $baseurl . $subdir . '/' . $filename;
	}
}
