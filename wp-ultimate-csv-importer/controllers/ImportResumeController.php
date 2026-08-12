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
 * AJAX endpoints for import auto-recovery and resume.
 */
class ImportResumeController {

	private static $instance = null;

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_ajax_get_interrupted_imports', array( $this, 'get_interrupted_imports' ) );
		add_action( 'wp_ajax_resume_import_session', array( $this, 'resume_import_session' ) );
		add_action( 'wp_ajax_discard_import_session', array( $this, 'discard_import_session' ) );
		add_action( 'admin_init', array( $this, 'maybe_detect_stale_on_admin' ) );
	}

	public function maybe_detect_stale_on_admin() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_GET['page'] ) || 'com.smackcoders.csvimporternew.menu' !== $_GET['page'] ) {
			return;
		}
		ImportResumeService::getInstance()->detect_stale_imports();
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
		if ( ! empty( $_POST['import_hash'] ) ) {
			return sanitize_key( wp_unslash( $_POST['import_hash'] ) );
		}
		return '';
	}

	public function get_interrupted_imports() {
		$this->verify_request();
		$imports = ImportResumeService::getInstance()->get_resumable_imports();

		wp_send_json(
			array(
				'success' => true,
				'imports' => $imports,
			)
		);
	}

	public function resume_import_session() {
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

		$result = ImportResumeService::getInstance()->prepare_resume( $hash_key );
		if ( empty( $result['success'] ) ) {
			wp_send_json(
				array(
					'success' => false,
					'message' => isset( $result['message'] ) ? $result['message'] : __( 'Unable to resume import.', 'wp-ultimate-csv-importer' ),
				)
			);
		}

		wp_send_json(
			array(
				'success'      => true,
				'resume_state' => true,
				'session'      => $result['data'],
			)
		);
	}

	public function discard_import_session() {
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

		$delete_upload = ! empty( $_POST['delete_upload'] ) && ( 'true' === $_POST['delete_upload'] || '1' === $_POST['delete_upload'] );
		$result        = ImportResumeService::getInstance()->discard( $hash_key, $delete_upload );

		wp_send_json( $result );
	}
}
