<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\UCI\Core\Validation;

use Smackcoders\UCI\Core\SecurityHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSV pre-flight validation engine.
 */
class ValidationEngine {

	const ERROR_EMPTY_CSV           = 'EMPTY_CSV';
	const ERROR_CORRUPT_CSV         = 'CORRUPT_CSV';
	const ERROR_INVALID_ENCODING    = 'INVALID_ENCODING';
	const ERROR_MISSING_REQUIRED    = 'MISSING_REQUIRED_FIELD';
	const ERROR_EMPTY_TITLE         = 'EMPTY_TITLE';
	const ERROR_INVALID_DATE        = 'INVALID_DATE';
	const ERROR_INVALID_STATUS      = 'INVALID_POST_STATUS';
	const ERROR_INVALID_NUMERIC     = 'INVALID_NUMERIC';
	const ERROR_BROKEN_ROW_LENGTH   = 'BROKEN_ROW_LENGTH';
	const ERROR_MISSING_MAPPING     = 'MISSING_MAPPED_FIELD';

	const ALLOWED_POST_STATUSES = array(
		'publish',
		'draft',
		'pending',
		'private',
		'future',
		'trash',
	);

	const DATE_FIELDS = array( 'post_date' );

	const NUMERIC_FIELDS = array(
		'ID',
		'menu_order',
		'post_parent',
		'comment_ID',
		'ORDERID',
		'TERMID',
	);

	private static $instance = null;

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param string $file_path
	 * @param array  $config {
	 *     @type string $scan_mode quick_10|quick_50|full
	 *     @type array  $mapping   Serialized mapping array from DB/context
	 *     @type string $import_type Module/import type label
	 *     @type bool   $allow_import_with_critical_errors
	 * }
	 * @return ValidationResult
	 */
	public function validateFile( $file_path, array $config = array() ) {
		$scan_mode = isset( $config['scan_mode'] ) ? sanitize_key( $config['scan_mode'] ) : CsvPreflightReader::SCAN_QUICK_10;
		if ( ! in_array( $scan_mode, array( CsvPreflightReader::SCAN_QUICK_10, CsvPreflightReader::SCAN_QUICK_50, CsvPreflightReader::SCAN_FULL ), true ) ) {
			$scan_mode = CsvPreflightReader::SCAN_QUICK_10;
		}

		$mapping      = isset( $config['mapping'] ) && is_array( $config['mapping'] ) ? $config['mapping'] : array();
		$import_type  = isset( $config['import_type'] ) ? sanitize_text_field( $config['import_type'] ) : '';
		$allow_critical = $this->resolve_allow_critical_errors( $config );

		$report = new ValidationReport();
		$result = new ValidationResult( $report );
		$result->set_scan_mode( $scan_mode );

		$reader = new CsvPreflightReader();
		$opened = $reader->open( $file_path );

		if ( empty( $opened['success'] ) ) {
			$message = isset( $opened['message'] ) ? $opened['message'] : __( 'CSV validation failed.', 'wp-ultimate-csv-importer' );
			$code    = self::ERROR_EMPTY_CSV;
			if ( false !== strpos( strtolower( $message ), 'utf-8' ) ) {
				$code = self::ERROR_INVALID_ENCODING;
			}

			$report->add_issue(
				new ValidationIssue(
					0,
					'',
					'',
					ValidationIssue::SEVERITY_CRITICAL,
					$code,
					$message
				)
			);
			$report->set_total_rows( 0 );
			$report->set_scanned_rows( 0 );
			$report->set_health_score( $this->calculateHealthScore( $report ) );
			$result->set_success( false );
			$result->set_can_import( false );
			$result->set_message( $message );
			return $this->finalize_result( $result, $allow_critical );
		}

		$header_result = $reader->read_header();
		if ( empty( $header_result['success'] ) ) {
			$message = isset( $header_result['message'] ) ? $header_result['message'] : __( 'CSV header is invalid.', 'wp-ultimate-csv-importer' );
			$report->add_issue(
				new ValidationIssue(
					0,
					'',
					'',
					ValidationIssue::SEVERITY_CRITICAL,
					self::ERROR_EMPTY_CSV,
					$message
				)
			);
			$report->set_health_score( $this->calculateHealthScore( $report ) );
			$result->set_success( false );
			$result->set_can_import( false );
			$result->set_message( $message );
			$reader->close();
			return $this->finalize_result( $result, $allow_critical );
		}

		$headers    = $this->normalize_headers( $reader->get_headers() );
		$total_rows = $reader->count_data_rows();
		$reader->rewind_data();

		$mapping = $this->normalize_mapping_columns( $mapping );

		$report->set_total_rows( $total_rows );

		if ( 0 === $total_rows ) {
			$report->add_issue(
				new ValidationIssue(
					0,
					'',
					'',
					ValidationIssue::SEVERITY_CRITICAL,
					self::ERROR_EMPTY_CSV,
					__( 'CSV file contains no data rows.', 'wp-ultimate-csv-importer' )
				)
			);
			$report->set_scanned_rows( 0 );
			$report->set_health_score( $this->calculateHealthScore( $report ) );
			$result->set_success( false );
			$result->set_can_import( false );
			$reader->close();
			return $this->finalize_result( $result, $allow_critical );
		}

		$this->validate_mapping_requirements( $report, $mapping, $import_type );

		$scan_limit = CsvPreflightReader::scan_limit_for_mode( $scan_mode, $total_rows );
		$scanned    = 0;
		$row_number = 1;

		while ( $scanned < $scan_limit ) {
			$row = $reader->read_data_row();

			if ( null === $row ) {
				break;
			}

			if ( false === $row ) {
				$report->add_issue(
					new ValidationIssue(
						$row_number,
						'',
						'',
						ValidationIssue::SEVERITY_CRITICAL,
						self::ERROR_CORRUPT_CSV,
						sprintf(
							/* translators: %d: row number */
							__( 'CSV row %d is corrupt or malformed.', 'wp-ultimate-csv-importer' ),
							$row_number
						)
					)
				);
				break;
			}

			$row_issues = $this->validateRow( $row_number, $row, $headers, $mapping, $import_type );
			foreach ( $row_issues as $issue ) {
				$report->add_issue( $issue );
			}

			$scanned++;
			$row_number++;
		}

		$report->set_scanned_rows( $scanned );
		$report->set_health_score( $this->calculateHealthScore( $report ) );
		$reader->close();

		return $this->finalize_result( $result, $allow_critical );
	}

	/**
	 * @param int      $row_number 1-based data row number.
	 * @param string[] $row
	 * @param string[] $headers
	 * @param array    $mapping
	 * @param string   $import_type
	 * @return ValidationIssue[]
	 */
	public function validateRow( $row_number, array $row, array $headers, array $mapping, $import_type = '' ) {
		$issues      = array();
		$core_map    = isset( $mapping['CORE'] ) && is_array( $mapping['CORE'] ) ? $mapping['CORE'] : array();
		$header_count = count( $headers );
		$row_count    = count( $row );
		$original_row_count = $row_count;

		if ( $row_count < $header_count ) {
			$row = array_pad( $row, $header_count, '' );
			$row_count = $header_count;
		}

		$assoc = $this->combine_row( $headers, $row );

		if ( $header_count !== $original_row_count && $original_row_count > $header_count ) {
			$issues[] = new ValidationIssue(
				$row_number,
				'',
				'',
				ValidationIssue::SEVERITY_WARNING,
				self::ERROR_BROKEN_ROW_LENGTH,
				sprintf(
					/* translators: 1: row number, 2: expected columns, 3: actual columns */
					__( 'Row %1$d has %3$d columns but %2$d were expected.', 'wp-ultimate-csv-importer' ),
					$row_number,
					$header_count,
					$original_row_count
				)
			);
		}

		if ( $this->import_type_requires_title( $import_type ) ) {
			$title_column = isset( $core_map['post_title'] ) ? $core_map['post_title'] : '';
			if ( '' !== $title_column ) {
				$title_value = isset( $assoc[ $title_column ] ) ? $assoc[ $title_column ] : '';
				if ( '' === trim( (string) $title_value ) ) {
					$issues[] = new ValidationIssue(
						$row_number,
						$title_column,
						'post_title',
						ValidationIssue::SEVERITY_WARNING,
						self::ERROR_EMPTY_TITLE,
						sprintf(
							/* translators: %d: row number */
							__( 'Row %d has an empty post title.', 'wp-ultimate-csv-importer' ),
							$row_number
						)
					);
				}
			}
		}

		foreach ( $this->get_required_mapped_fields( $import_type ) as $field_key ) {
			if ( 'post_title' === $field_key ) {
				continue;
			}
			$column = isset( $core_map[ $field_key ] ) ? trim( (string) $core_map[ $field_key ] ) : '';
			if ( '' === $column ) {
				continue;
			}
			$value = isset( $assoc[ $column ] ) ? $assoc[ $column ] : '';
			if ( '' === trim( (string) $value ) ) {
				$issues[] = new ValidationIssue(
					$row_number,
					$column,
					$field_key,
					ValidationIssue::SEVERITY_CRITICAL,
					self::ERROR_MISSING_REQUIRED,
					sprintf(
						/* translators: 1: row number, 2: field key */
						__( 'Row %1$d is missing required value for %2$s.', 'wp-ultimate-csv-importer' ),
						$row_number,
						$field_key
					)
				);
			}
		}

		foreach ( $core_map as $field_key => $column_name ) {
			$column_name = trim( (string) $column_name );
			if ( '' === $column_name || ! isset( $assoc[ $column_name ] ) ) {
				continue;
			}
			$value = trim( (string) $assoc[ $column_name ] );
			if ( '' === $value ) {
				continue;
			}

			if ( in_array( $field_key, self::DATE_FIELDS, true ) ) {
				if ( ! $this->is_valid_date_value( $value ) ) {
					$issues[] = new ValidationIssue(
						$row_number,
						$column_name,
						$field_key,
						ValidationIssue::SEVERITY_WARNING,
						self::ERROR_INVALID_DATE,
						sprintf(
							/* translators: 1: row number, 2: value */
							__( 'Row %1$d has invalid date value "%2$s".', 'wp-ultimate-csv-importer' ),
							$row_number,
							$value
						)
					);
				}
			}

			if ( 'post_status' === $field_key && ! in_array( strtolower( $value ), self::ALLOWED_POST_STATUSES, true ) ) {
				$issues[] = new ValidationIssue(
					$row_number,
					$column_name,
					$field_key,
					ValidationIssue::SEVERITY_WARNING,
					self::ERROR_INVALID_STATUS,
					sprintf(
						/* translators: 1: row number, 2: status value */
						__( 'Row %1$d has invalid post status "%2$s".', 'wp-ultimate-csv-importer' ),
						$row_number,
						$value
					)
				);
			}

			if ( in_array( $field_key, self::NUMERIC_FIELDS, true ) && ! $this->is_valid_numeric_value( $value ) ) {
				$issues[] = new ValidationIssue(
					$row_number,
					$column_name,
					$field_key,
					ValidationIssue::SEVERITY_WARNING,
					self::ERROR_INVALID_NUMERIC,
					sprintf(
						/* translators: 1: row number, 2: field key, 3: value */
						__( 'Row %1$d has invalid numeric value for %2$s: "%3$s".', 'wp-ultimate-csv-importer' ),
						$row_number,
						$field_key,
						$value
					)
				);
			}
		}

		/**
		 * Allow Pro/add-ons to append row validation issues.
		 *
		 * @param ValidationIssue[] $issues
		 * @param int               $row_number
		 * @param array             $assoc
		 * @param array             $mapping
		 * @param string            $import_type
		 */
		$filtered = apply_filters( 'wpucsv_validation_row_issues', $issues, $row_number, $assoc, $mapping, $import_type );

		return is_array( $filtered ) ? $filtered : $issues;
	}

	/**
	 * @param ValidationReport $report
	 * @return int
	 */
	public function calculateHealthScore( ValidationReport $report ) {
		$score = 100 - ( $report->get_critical_count() * 10 ) - ( $report->get_warning_count() * 2 );
		return max( 0, min( 100, $score ) );
	}

	/**
	 * @param ValidationResult $result
	 * @param bool             $allow_critical
	 * @return ValidationResult
	 */
	public function resolve_import_eligibility( ValidationResult $result, $allow_critical = false ) {
		$report = $result->get_report();
		$can    = true;

		if ( $report->get_critical_count() > 0 && ! $allow_critical ) {
			$can = false;
		}

		$result->set_can_import( $can );
		return $result;
	}

	/**
	 * Run validation for a stored import session hash key.
	 *
	 * @param string $hash_key
	 * @param array  $config
	 * @return ValidationResult
	 */
	public function validate_import_session( $hash_key, array $config = array() ) {
		global $wpdb;

		$hash_key = sanitize_key( $hash_key );
		$upload   = \Smackcoders\UCI\Core\UCICore::getInstance()->create_upload_dir();
		$path     = $upload . $hash_key . '/' . $hash_key;

		if ( empty( $config['mapping'] ) || empty( $config['import_type'] ) ) {
			$template_table = $wpdb->prefix . 'ultimate_csv_importer_mappingtemplate';
			$row            = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT mapping, module FROM {$template_table} WHERE eventKey = %s LIMIT 1",
					$hash_key
				),
				ARRAY_A
			);
			if ( $row ) {
				if ( empty( $config['mapping'] ) && ! empty( $row['mapping'] ) ) {
					$mapping = SecurityHelper::safe_unserialize( $row['mapping'] );
					if ( is_array( $mapping ) ) {
						$config['mapping'] = $mapping;
					}
				}
				if ( empty( $config['import_type'] ) && ! empty( $row['module'] ) ) {
					$config['import_type'] = $row['module'];
				}
			}
		}

		return $this->validateFile( $path, $config );
	}

	/**
	 * @param string $hash_key
	 * @param array  $config
	 * @return ValidationResult
	 */
	public function assert_import_allowed( $hash_key, array $config = array() ) {
		$result = $this->validate_import_session( $hash_key, $config );
		if ( ! $result->can_import() ) {
			return $result;
		}
		return $result;
	}

	private function finalize_result( ValidationResult $result, $allow_critical ) {
		$report = $result->get_report();
		$report->recalculate_counts();
		$report->set_health_score( $this->calculateHealthScore( $report ) );
		$this->resolve_import_eligibility( $result, $allow_critical );

		if ( ! $result->can_import() && '' === $result->get_message() ) {
			$result->set_message(
				__( 'Import is blocked due to critical validation errors.', 'wp-ultimate-csv-importer' )
			);
		}

		/**
		 * Filter final validation result (Pro extension point).
		 *
		 * @param ValidationResult $result
		 * @param bool             $allow_critical
		 */
		return apply_filters( 'wpucsv_validation_result', $result, $allow_critical );
	}

	private function validate_mapping_requirements( ValidationReport $report, array $mapping, $import_type ) {
		if ( ! $this->import_type_requires_title( $import_type ) ) {
			return;
		}

		$core_map = isset( $mapping['CORE'] ) && is_array( $mapping['CORE'] ) ? $mapping['CORE'] : array();
		if ( empty( $core_map['post_title'] ) || '' === trim( (string) $core_map['post_title'] ) ) {
			$report->add_issue(
				new ValidationIssue(
					0,
					'',
					'post_title',
					ValidationIssue::SEVERITY_CRITICAL,
					self::ERROR_MISSING_MAPPING,
					__( 'Post title is not mapped in WordPress Core Fields.', 'wp-ultimate-csv-importer' )
				)
			);
		}

		foreach ( $this->get_required_mapped_fields( $import_type ) as $field_key ) {
			if ( 'post_title' === $field_key ) {
				continue;
			}
			if ( empty( $core_map[ $field_key ] ) || '' === trim( (string) $core_map[ $field_key ] ) ) {
				$report->add_issue(
					new ValidationIssue(
						0,
						'',
						$field_key,
						ValidationIssue::SEVERITY_CRITICAL,
						self::ERROR_MISSING_MAPPING,
						sprintf(
							/* translators: %s: field key */
							__( 'Required field %s is not mapped.', 'wp-ultimate-csv-importer' ),
							$field_key
						)
					)
				);
			}
		}
	}

	/**
	 * @param string $import_type
	 * @return string[]
	 */
	private function get_required_mapped_fields( $import_type ) {
		$fields = array();
		if ( $this->import_type_requires_title( $import_type ) ) {
			$fields[] = 'post_title';
		}

		/**
		 * Filter required mapped CORE field keys per import type.
		 *
		 * @param string[] $fields
		 * @param string   $import_type
		 */
		return apply_filters( 'wpucsv_validation_required_fields', $fields, $import_type );
	}

	private function import_type_requires_title( $import_type ) {
		$types = array( 'Posts', 'Pages', 'CustomPosts' );
		if ( in_array( $import_type, $types, true ) ) {
			return true;
		}

		/**
		 * CPT slugs and other content types can opt in via filter.
		 *
		 * @param bool   $requires
		 * @param string $import_type
		 */
		return (bool) apply_filters( 'wpucsv_validation_requires_title', false, $import_type );
	}

	/**
	 * @param string[] $headers
	 * @param string[] $row
	 * @return array
	 */
	private function combine_row( array $headers, array $row ) {
		$assoc = array();
		$count = min( count( $headers ), count( $row ) );
		for ( $i = 0; $i < $count; $i++ ) {
			$key = $this->strip_utf8_bom( $headers[ $i ] );
			if ( '' === $key ) {
				continue;
			}
			$assoc[ $key ] = $row[ $i ];
		}
		return $assoc;
	}

	/**
	 * @param string[] $headers
	 * @return string[]
	 */
	private function normalize_headers( array $headers ) {
		$out = array();
		foreach ( $headers as $header ) {
			$out[] = $this->strip_utf8_bom( $header );
		}
		return $out;
	}

	/**
	 * Strip UTF-8 BOM from mapped CSV column names so Excel-exported files match.
	 *
	 * @param array $mapping
	 * @return array
	 */
	private function normalize_mapping_columns( array $mapping ) {
		foreach ( $mapping as $group => $fields ) {
			if ( ! is_array( $fields ) ) {
				continue;
			}
			foreach ( $fields as $field_key => $column ) {
				if ( is_string( $column ) ) {
					$mapping[ $group ][ $field_key ] = $this->strip_utf8_bom( $column );
				}
			}
		}
		return $mapping;
	}

	/**
	 * @param mixed $text
	 * @return string
	 */
	private function strip_utf8_bom( $text ) {
		$text = (string) $text;
		if ( strncmp( $text, "\xEF\xBB\xBF", 3 ) === 0 ) {
			$text = substr( $text, 3 );
		}
		return trim( $text );
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	private function is_valid_date_value( $value ) {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			list( $year, $month, $day ) = array_map( 'intval', explode( '-', $value ) );
			return checkdate( $month, $day, $year );
		}

		if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value ) ) {
			$date_part = substr( $value, 0, 10 );
			$time_part = substr( $value, 11 );
			list( $year, $month, $day ) = array_map( 'intval', explode( '-', $date_part ) );
			if ( ! checkdate( $month, $day, $year ) ) {
				return false;
			}
			list( $hour, $minute, $second ) = array_map( 'intval', explode( ':', $time_part ) );
			return $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59 && $second >= 0 && $second <= 59;
		}

		return false;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	private function is_valid_numeric_value( $value ) {
		if ( is_numeric( $value ) ) {
			return true;
		}
		return (bool) preg_match( '/^-?\d+$/', $value );
	}

	/**
	 * @param array $config
	 * @return bool
	 */
	private function resolve_allow_critical_errors( array $config ) {
		if ( isset( $config['allow_import_with_critical_errors'] ) ) {
			$allow = (bool) $config['allow_import_with_critical_errors'];
		} else {
			$settings = get_option( 'sm_uci_pro_settings', array() );
			$allow    = ! empty( $settings['allow_import_with_critical_errors'] );
		}

		/**
		 * Filter whether critical validation errors block import.
		 *
		 * @param bool  $allow
		 * @param array $config
		 */
		return (bool) apply_filters( 'wpucsv_validation_allow_import_with_critical_errors', $allow, $config );
	}
}
