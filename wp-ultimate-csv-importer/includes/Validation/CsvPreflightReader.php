<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\UCI\Core\Validation;

use Smackcoders\UCI\Core\ValidateFile;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lightweight CSV reader for pre-flight validation (reuses ValidateFile delimiter detection).
 */
class CsvPreflightReader {

	const SCAN_QUICK_10 = 'quick_10';
	const SCAN_QUICK_50 = 'quick_50';
	const SCAN_FULL     = 'full';

	/** @var resource|null */
	private $handle = null;

	/** @var string */
	private $delimiter = ',';

	/** @var string[] */
	private $headers = array();

	/** @var int */
	private $total_rows = 0;

	/** @var string */
	private $file_path = '';

	/** @var bool */
	private $is_open = false;

	/**
	 * @param string $file_path Absolute path to CSV file.
	 * @return array{success:bool,message?:string}
	 */
	public function open( $file_path ) {
		$this->close();
		$this->file_path = $file_path;

		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'CSV file is missing or not readable.', 'wp-ultimate-csv-importer' ),
			);
		}

		$file_size = filesize( $file_path );
		if ( false === $file_size || 0 === $file_size ) {
			return array(
				'success' => false,
				'message' => __( 'CSV file is empty.', 'wp-ultimate-csv-importer' ),
			);
		}

		$raw = file_get_contents( $file_path );
		if ( false === $raw ) {
			return array(
				'success' => false,
				'message' => __( 'Unable to read CSV file.', 'wp-ultimate-csv-importer' ),
			);
		}

		if ( function_exists( 'mb_check_encoding' ) && ! mb_check_encoding( $raw, 'UTF-8' ) ) {
			return array(
				'success' => false,
				'message' => __( 'CSV file is not valid UTF-8 encoding.', 'wp-ultimate-csv-importer' ),
			);
		}

		$validate_file     = ValidateFile::getInstance();
		$this->delimiter   = $this->resolve_delimiter( $validate_file->getFileDelimiter( $file_path, 5 ) );
		$this->handle      = fopen( $file_path, 'r' );
		$this->is_open     = ( false !== $this->handle );
		$this->total_rows  = 0;
		$this->headers     = array();

		if ( ! $this->is_open ) {
			return array(
				'success' => false,
				'message' => __( 'Unable to open CSV file.', 'wp-ultimate-csv-importer' ),
			);
		}

		ini_set( 'auto_detect_line_endings', '1' );

		return array( 'success' => true );
	}

	/**
	 * @return string[]
	 */
	public function get_headers() {
		return $this->headers;
	}

	public function get_total_rows() {
		return $this->total_rows;
	}

	public function get_delimiter() {
		return $this->delimiter;
	}

	/**
	 * Read header row.
	 *
	 * @return array{success:bool,row?:array,message?:string}
	 */
	public function read_header() {
		if ( ! $this->is_open ) {
			return array(
				'success' => false,
				'message' => __( 'CSV reader is not open.', 'wp-ultimate-csv-importer' ),
			);
		}

		$row = $this->read_csv_row();
		if ( false === $row ) {
			return array(
				'success' => false,
				'message' => __( 'CSV header row is missing or corrupt.', 'wp-ultimate-csv-importer' ),
			);
		}

		$this->headers = array_map( 'trim', $row );
		if ( $this->is_header_empty() ) {
			return array(
				'success' => false,
				'message' => __( 'CSV header row is empty.', 'wp-ultimate-csv-importer' ),
			);
		}

		return array(
			'success' => true,
			'row'     => $this->headers,
		);
	}

	/**
	 * Count remaining data rows (after header).
	 */
	public function count_data_rows() {
		if ( ! $this->is_open ) {
			return 0;
		}

		$count = 0;
		while ( true ) {
			$row = $this->read_csv_row();
			if ( null === $row ) {
				break;
			}
			if ( false === $row ) {
				break;
			}
			$count++;
		}

		$this->total_rows = $count;
		return $count;
	}

	/**
	 * Rewind to first data row (after header).
	 */
	public function rewind_data() {
		if ( ! $this->is_open ) {
			return;
		}
		rewind( $this->handle );
		$this->read_header();
	}

	/**
	 * @return array|false|null false = corrupt row, null = EOF
	 */
	public function read_data_row() {
		if ( ! $this->is_open ) {
			return null;
		}

		$row = $this->read_csv_row();
		if ( null === $row ) {
			return null;
		}
		if ( false === $row ) {
			return false;
		}

		return array_map( 'trim', $row );
	}

	public function close() {
		if ( $this->handle ) {
			fclose( $this->handle );
		}
		$this->handle     = null;
		$this->is_open    = false;
		$this->headers    = array();
		$this->total_rows = 0;
	}

	public function __destruct() {
		$this->close();
	}

	/**
	 * @param string $scan_mode
	 * @return int
	 */
	public static function scan_limit_for_mode( $scan_mode, $total_rows ) {
		switch ( $scan_mode ) {
			case self::SCAN_QUICK_10:
				return min( 10, $total_rows );
			case self::SCAN_QUICK_50:
				return min( 50, $total_rows );
			case self::SCAN_FULL:
			default:
				return $total_rows;
		}
	}

	/**
	 * @param mixed $delimiter
	 * @return string
	 */
	private function resolve_delimiter( $delimiter ) {
		if ( '\t' === $delimiter || "\t" === $delimiter ) {
			return "\t";
		}
		if ( is_string( $delimiter ) && strlen( $delimiter ) === 1 ) {
			return $delimiter;
		}
		return ',';
	}

	/**
	 * @return array|false|null
	 */
	private function read_csv_row() {
		if ( ! $this->handle ) {
			return null;
		}

		while ( true ) {
			if ( feof( $this->handle ) ) {
				return null;
			}

			$row = fgetcsv( $this->handle, 0, $this->delimiter, '"', '\\' );
			if ( false === $row ) {
				if ( feof( $this->handle ) ) {
					return null;
				}
				return false;
			}

			if ( $this->is_blank_row( $row ) ) {
				if ( feof( $this->handle ) ) {
					return null;
				}
				continue;
			}

			return $row;
		}
	}

	/**
	 * @param array $row
	 * @return bool
	 */
	private function is_blank_row( array $row ) {
		foreach ( $row as $cell ) {
			if ( '' !== trim( (string) $cell ) ) {
				return false;
			}
		}
		return true;
	}

	private function is_header_empty() {
		if ( empty( $this->headers ) ) {
			return true;
		}
		foreach ( $this->headers as $header ) {
			if ( '' !== trim( (string) $header ) ) {
				return false;
			}
		}
		return true;
	}
}
