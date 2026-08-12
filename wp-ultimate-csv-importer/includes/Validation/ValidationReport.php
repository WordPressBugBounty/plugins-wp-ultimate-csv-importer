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
 * Aggregated validation scan results.
 */
class ValidationReport implements \JsonSerializable {

	/** @var int */
	private $total_rows = 0;

	/** @var int */
	private $scanned_rows = 0;

	/** @var int */
	private $warning_count = 0;

	/** @var int */
	private $critical_count = 0;

	/** @var int */
	private $health_score = 100;

	/** @var ValidationIssue[] */
	private $issues = array();

	public function set_total_rows( $total_rows ) {
		$this->total_rows = max( 0, (int) $total_rows );
	}

	public function get_total_rows() {
		return $this->total_rows;
	}

	public function set_scanned_rows( $scanned_rows ) {
		$this->scanned_rows = max( 0, (int) $scanned_rows );
	}

	public function get_scanned_rows() {
		return $this->scanned_rows;
	}

	public function set_health_score( $health_score ) {
		$this->health_score = max( 0, min( 100, (int) $health_score ) );
	}

	public function get_health_score() {
		return $this->health_score;
	}

	public function get_warning_count() {
		return $this->warning_count;
	}

	public function get_critical_count() {
		return $this->critical_count;
	}

	/**
	 * @return ValidationIssue[]
	 */
	public function get_issues() {
		return $this->issues;
	}

	public function add_issue( ValidationIssue $issue ) {
		$this->issues[] = $issue;
		if ( $issue->is_critical() ) {
			$this->critical_count++;
		} elseif ( $issue->is_warning() ) {
			$this->warning_count++;
		}
	}

	public function recalculate_counts() {
		$this->warning_count  = 0;
		$this->critical_count = 0;
		foreach ( $this->issues as $issue ) {
			if ( $issue->is_critical() ) {
				$this->critical_count++;
			} elseif ( $issue->is_warning() ) {
				$this->warning_count++;
			}
		}
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return array(
			'total_rows'     => $this->total_rows,
			'scanned_rows'   => $this->scanned_rows,
			'warning_count'  => $this->warning_count,
			'critical_count' => $this->critical_count,
			'health_score'   => $this->health_score,
			'issues'         => $this->issues,
		);
	}
}
