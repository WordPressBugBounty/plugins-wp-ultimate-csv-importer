<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\UCI\Core\Validation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outcome of a validation scan including import eligibility.
 */
class ValidationResult implements \JsonSerializable {

	/** @var bool */
	private $success = true;

	/** @var string */
	private $scan_mode = 'quick_10';

	/** @var ValidationReport */
	private $report;

	/** @var bool */
	private $can_import = true;

	/** @var string */
	private $message = '';

	public function __construct( ValidationReport $report = null ) {
		$this->report = $report ? $report : new ValidationReport();
	}

	public function set_success( $success ) {
		$this->success = (bool) $success;
	}

	public function is_success() {
		return $this->success;
	}

	public function set_scan_mode( $scan_mode ) {
		$this->scan_mode = (string) $scan_mode;
	}

	public function get_scan_mode() {
		return $this->scan_mode;
	}

	public function get_report() {
		return $this->report;
	}

	public function set_can_import( $can_import ) {
		$this->can_import = (bool) $can_import;
	}

	public function can_import() {
		return $this->can_import;
	}

	public function set_message( $message ) {
		$this->message = (string) $message;
	}

	public function get_message() {
		return $this->message;
	}

	/**
	 * Flat array for AJAX responses.
	 *
	 * @return array
	 */
	public function to_response_array() {
		$report = $this->report;

		return array(
			'success'         => $this->success,
			'scan_mode'       => $this->scan_mode,
			'total_rows'      => $report->get_total_rows(),
			'scanned_rows'    => $report->get_scanned_rows(),
			'health_score'    => $report->get_health_score(),
			'warning_count'   => $report->get_warning_count(),
			'critical_count'  => $report->get_critical_count(),
			'can_import'      => $this->can_import,
			'message'         => $this->message,
			'issues'          => $report->get_issues(),
		);
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->to_response_array();
	}
}
