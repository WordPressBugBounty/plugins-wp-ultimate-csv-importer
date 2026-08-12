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
 * Single validation finding for a CSV row or file-level check.
 */
class ValidationIssue implements \JsonSerializable {

	const SEVERITY_WARNING  = 'warning';
	const SEVERITY_CRITICAL = 'critical';

	/** @var int */
	private $row_number;

	/** @var string */
	private $column_name;

	/** @var string */
	private $field_key;

	/** @var string */
	private $severity;

	/** @var string */
	private $error_code;

	/** @var string */
	private $message;

	public function __construct(
		$row_number,
		$column_name,
		$field_key,
		$severity,
		$error_code,
		$message
	) {
		$this->row_number  = (int) $row_number;
		$this->column_name = (string) $column_name;
		$this->field_key   = (string) $field_key;
		$this->severity    = (string) $severity;
		$this->error_code  = (string) $error_code;
		$this->message     = (string) $message;
	}

	public function get_row_number() {
		return $this->row_number;
	}

	public function get_column_name() {
		return $this->column_name;
	}

	public function get_field_key() {
		return $this->field_key;
	}

	public function get_severity() {
		return $this->severity;
	}

	public function get_error_code() {
		return $this->error_code;
	}

	public function get_message() {
		return $this->message;
	}

	public function is_critical() {
		return self::SEVERITY_CRITICAL === $this->severity;
	}

	public function is_warning() {
		return self::SEVERITY_WARNING === $this->severity;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return array(
			'row_number'  => $this->row_number,
			'column_name' => $this->column_name,
			'field_key'   => $this->field_key,
			'severity'    => $this->severity,
			'error_code'  => $this->error_code,
			'message'     => $this->message,
		);
	}
}
