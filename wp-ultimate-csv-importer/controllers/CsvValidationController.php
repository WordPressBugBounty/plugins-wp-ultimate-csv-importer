<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\UCI\Core;

use Smackcoders\UCI\Core\Validation\CsvPreflightReader;
use Smackcoders\UCI\Core\Validation\ValidationEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX endpoints for CSV pre-flight validation.
 */
class CsvValidationController {

	const TRANSIENT_PREFIX = 'wpucsv_validation_';
	const TRANSIENT_TTL    = HOUR_IN_SECONDS;

	private static $instance = null;

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_validate_csv_preflight', array( $this, 'validate_csv_preflight' ) );
	}

	private function verify_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Permission denied.', 'wp-ultimate-csv-importer' ),
				)
			);
		}

		$nonce = isset( $_POST['securekey'] ) ? sanitize_text_field( wp_unslash( $_POST['securekey'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'smack-ultimate-csv-importer' ) ) {
			SecurityHelper::verify_ajax_nonce();
			if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}
		}
	}

	private function resolve_hash_key() {
		if ( ! empty( $_POST['HashKey'] ) ) {
			return sanitize_key( wp_unslash( $_POST['HashKey'] ) );
		}
		if ( ! empty( $_POST['hash_key'] ) ) {
			return sanitize_key( wp_unslash( $_POST['hash_key'] ) );
		}
		return '';
	}

	private function resolve_scan_mode() {
		$scan_mode = isset( $_POST['scan_mode'] ) ? sanitize_key( wp_unslash( $_POST['scan_mode'] ) ) : CsvPreflightReader::SCAN_QUICK_10;
		$allowed   = array(
			CsvPreflightReader::SCAN_QUICK_10,
			CsvPreflightReader::SCAN_QUICK_50,
			CsvPreflightReader::SCAN_FULL,
		);
		if ( ! in_array( $scan_mode, $allowed, true ) ) {
			return CsvPreflightReader::SCAN_QUICK_10;
		}
		return $scan_mode;
	}

	public function validate_csv_preflight() {
		$this->verify_request();

		$hash_key = $this->resolve_hash_key();
		if ( empty( $hash_key ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'Invalid import session.', 'wp-ultimate-csv-importer' ),
				)
			);
		}

		$scan_mode = $this->resolve_scan_mode();
		$allow     = isset( $_POST['allow_import_with_critical_errors'] )
			? filter_var( wp_unslash( $_POST['allow_import_with_critical_errors'] ), FILTER_VALIDATE_BOOLEAN )
			: null;

		$config = array(
			'scan_mode' => $scan_mode,
		);

		if ( null !== $allow ) {
			$config['allow_import_with_critical_errors'] = $allow;
		}

		if ( ! empty( $_POST['MappedFields'] ) ) {
			$mapped = json_decode( wp_unslash( $_POST['MappedFields'] ), true );
			if ( is_array( $mapped ) ) {
				$config['mapping'] = $mapped;
			}
		}

		if ( ! empty( $_POST['Types'] ) ) {
			$config['import_type'] = sanitize_text_field( wp_unslash( $_POST['Types'] ) );
		}

		$engine = ValidationEngine::getInstance();
		$result = $engine->validate_import_session( $hash_key, $config );

		$this->store_validation_transient( $hash_key, $result );

		wp_send_json( $result->to_response_array() );
	}

	/**
	 * @param string $hash_key
	 * @param \Smackcoders\UCI\Core\Validation\ValidationResult $result
	 */
	public function store_validation_transient( $hash_key, $result ) {
		$response = $result->to_response_array();
		set_transient(
			self::TRANSIENT_PREFIX . $hash_key,
			$response,
			self::TRANSIENT_TTL
		);

		if ( class_exists( '\Smackcoders\UCI\Core\Dashboard\DashboardService' ) ) {
			\Smackcoders\UCI\Core\Dashboard\DashboardService::record_validation_assists( $response );
		}
	}

	/**
	 * @param string $hash_key
	 * @return array|null
	 */
	public function get_stored_validation( $hash_key ) {
		$stored = get_transient( self::TRANSIENT_PREFIX . $hash_key );
		return is_array( $stored ) ? $stored : null;
	}

	/**
	 * Import gate used by SaveMapping.
	 *
	 * @param string $hash_key
	 * @param array  $config
	 * @return array|null Error response array or null if allowed.
	 */
	public static function import_gate_response( $hash_key, array $config = array() ) {
		$scan_mode = isset( $config['scan_mode'] )
			? $config['scan_mode']
			: apply_filters( 'wpucsv_validation_import_scan_mode', CsvPreflightReader::SCAN_FULL, $hash_key );

		$engine_config = array(
			'scan_mode' => $scan_mode,
		);

		if ( isset( $config['allow_import_with_critical_errors'] ) ) {
			$engine_config['allow_import_with_critical_errors'] = (bool) $config['allow_import_with_critical_errors'];
		}

		$engine = ValidationEngine::getInstance();
		$result = $engine->validate_import_session( $hash_key, $engine_config );

		self::getInstance()->store_validation_transient( $hash_key, $result );

		if ( $result->can_import() ) {
			return null;
		}

		$response               = $result->to_response_array();
		$response['success']    = false;
		$response['blocked']    = true;
		$response['message']    = $result->get_message();
		return $response;
	}
}
