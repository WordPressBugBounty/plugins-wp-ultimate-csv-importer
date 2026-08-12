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
 * Auto recovery and resume checkpoints for live imports.
 */
class ImportResumeService {

	const STATE_PENDING      = 'PENDING';
	const STATE_RUNNING      = 'RUNNING';
	const STATE_PAUSED       = 'PAUSED';
	const STATE_INTERRUPTED  = 'INTERRUPTED';
	const STATE_FAILED       = 'FAILED';
	const STATE_COMPLETED    = 'COMPLETED';
	const STATE_DISCARDED    = 'DISCARDED';

	const ROW_INSERTED = 'Inserted';
	const ROW_UPDATED  = 'Updated';
	const ROW_SKIPPED  = 'Skipped';
	const ROW_FAILED   = 'Failed';

	private static $instance = null;

	public static function getInstance() {
		if ( null === self::$instance ) {
			self::ensure_tables();
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Create resume tables when missing (e.g. plugin updated without re-activation).
	 */
	public static function ensure_tables() {
		static $checked = false;
		if ( $checked ) {
			return;
		}
		$checked = true;

		global $wpdb;
		$checkpoint_table = self::checkpoint_table();
		$row_log_table      = self::row_log_table();

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $checkpoint_table ) ) ) {
			return;
		}


		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$checkpoint_table} (
			`resume_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`import_hash` varchar(64) NOT NULL,
			`import_type` varchar(32) NOT NULL DEFAULT 'csv',
			`import_mode` varchar(32) NOT NULL DEFAULT 'bulk',
			`batch_number` int(11) NOT NULL DEFAULT 1,
			`page_number` int(11) NOT NULL DEFAULT 1,
			`line_number` int(11) NOT NULL DEFAULT 1,
			`processed_count` bigint(20) NOT NULL DEFAULT 0,
			`failed_count` bigint(20) NOT NULL DEFAULT 0,
			`skipped_count` bigint(20) NOT NULL DEFAULT 0,
			`state` varchar(20) NOT NULL DEFAULT 'PENDING',
			`queue_snapshot` longtext,
			`media_snapshot` longtext,
			`schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
			`file_name` varchar(255) DEFAULT NULL,
			`last_heartbeat_at` datetime DEFAULT NULL,
			`created_at` datetime NOT NULL,
			`updated_at` datetime NOT NULL,
			PRIMARY KEY (`resume_id`),
			KEY `import_hash` (`import_hash`),
			KEY `state_heartbeat` (`state`, `last_heartbeat_at`)
			) ENGINE=InnoDB"
		);

		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$row_log_table} (
			`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`import_hash` varchar(64) NOT NULL,
			`row_number` int(11) NOT NULL,
			`status` varchar(20) NOT NULL,
			`post_id` bigint(20) UNSIGNED DEFAULT NULL,
			`completed_at` datetime NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `hash_row` (`import_hash`, `row_number`),
			KEY `import_hash` (`import_hash`)
			) ENGINE=InnoDB"
		);
	}

	public static function checkpoint_table() {
		global $wpdb;
		return $wpdb->prefix . 'sm_uci_import_checkpoint';
	}

	public static function row_log_table() {
		global $wpdb;
		return $wpdb->prefix . 'sm_uci_import_row_log';
	}

	/**
	 * Escaped column name — `row_number` is reserved in MySQL 8+ (window function).
	 */
	private static function row_log_row_col() {
		return '`row_number`';
	}

	public function get_heartbeat_ttl() {
		$ttl = (int) apply_filters( 'sm_uci_import_heartbeat_ttl', 120 );
		return max( 30, $ttl );
	}

	/**
	 * Seconds without heartbeat before a RUNNING import is shown in the resume banner.
	 */
	public function get_banner_stale_seconds() {
		$seconds = (int) apply_filters( 'sm_uci_import_banner_stale_seconds', 30 );
		return max( 10, $seconds );
	}

	/**
	 * Whether the upload dashboard should offer resume/discard for this checkpoint.
	 *
	 * @param array<string,mixed> $row Raw checkpoint row.
	 */
	public function is_banner_eligible( $row ) {
		$state = isset( $row['state'] ) ? (string) $row['state'] : '';

		if ( in_array( $state, array( self::STATE_INTERRUPTED, self::STATE_PAUSED ), true ) ) {
			return true;
		}

		if ( self::STATE_RUNNING !== $state ) {
			return false;
		}

		if ( $this->is_import_log_paused( $row['import_hash'] ?? '' ) ) {
			return true;
		}

		if ( empty( $row['last_heartbeat_at'] ) ) {
			return true;
		}

		$heartbeat_ts = strtotime( (string) $row['last_heartbeat_at'] . ' UTC' );
		if ( false === $heartbeat_ts ) {
			return true;
		}

		return ( time() - $heartbeat_ts ) >= $this->get_banner_stale_seconds();
	}

	/**
	 * @param string $import_hash
	 */
	private function is_import_log_paused( $import_hash ) {
		global $wpdb;
		$import_hash = sanitize_key( $import_hash );
		if ( empty( $import_hash ) ) {
			return false;
		}

		$running = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT running FROM {$wpdb->prefix}import_detail_log WHERE hash_key = %s ORDER BY id DESC LIMIT 1",
				$import_hash
			)
		);

		return null !== $running && 0 === (int) $running;
	}

	/**
	 * Mark stale RUNNING imports as INTERRUPTED.
	 */
	public function detect_stale_imports() {
		global $wpdb;
		$table = self::checkpoint_table();
		$ttl   = $this->get_heartbeat_ttl();
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - $ttl );

		$affected = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET state = %s, updated_at = %s
				WHERE state = %s AND last_heartbeat_at IS NOT NULL AND last_heartbeat_at < %s",
				self::STATE_INTERRUPTED,
				current_time( 'mysql', true ),
				self::STATE_RUNNING,
				$cutoff
			)
		);
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function get_resumable_imports() {
		global $wpdb;
		$this->detect_stale_imports();
		$table = self::checkpoint_table();

		$states = array(
			self::STATE_INTERRUPTED,
			self::STATE_PAUSED,
			self::STATE_RUNNING,
		);
		$placeholders = implode( ',', array_fill( 0, count( $states ), '%s' ) );
		$rows         = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE state IN ({$placeholders}) ORDER BY updated_at DESC LIMIT 10",
				...$states
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$out[] = $this->format_checkpoint_for_ui( $row );
		}

		return $out;
	}

	/**
	 * @param array<string,mixed> $row
	 * @return array<string,mixed>
	 */
	private function format_checkpoint_for_ui( $row ) {
		$snapshot = $this->decode_snapshot( $row['queue_snapshot'] ?? '' );
		$formatted = array(
			'import_hash'      => $row['import_hash'],
			'file_name'        => $row['file_name'],
			'import_type'      => $row['import_type'],
			'import_mode'      => $row['import_mode'],
			'state'            => $row['state'],
			'page_number'      => (int) $row['page_number'],
			'line_number'      => (int) $row['line_number'],
			'processed_count'  => (int) $row['processed_count'],
			'failed_count'     => (int) $row['failed_count'],
			'skipped_count'    => (int) $row['skipped_count'],
			'total_records'    => isset( $snapshot['total_records'] ) ? (int) $snapshot['total_records'] : 0,
			'last_heartbeat_at'=> $row['last_heartbeat_at'],
			'updated_at'       => $row['updated_at'],
			'module'           => isset( $snapshot['module'] ) ? $snapshot['module'] : '',
		);
		$formatted['banner_eligible'] = $this->is_banner_eligible( $row );
		return $formatted;
	}

	/**
	 * @param array<string,mixed> $args
	 */
	public function start_checkpoint( $import_hash, $args = array() ) {
		global $wpdb;
		$import_hash = sanitize_key( $import_hash );
		if ( empty( $import_hash ) ) {
			return false;
		}

		$existing = $this->get_checkpoint_row( $import_hash );
		if ( $existing && in_array( $existing['state'], array( self::STATE_RUNNING, self::STATE_PAUSED, self::STATE_INTERRUPTED ), true ) ) {
			$this->mark_running( $import_hash );
			$this->heartbeat( $import_hash );
			return true;
		}

		$now = current_time( 'mysql', true );
		$data = array(
			'import_hash'       => $import_hash,
			'import_type'       => sanitize_text_field( $args['import_type'] ?? 'csv' ),
			'import_mode'       => sanitize_text_field( $args['import_mode'] ?? 'bulk' ),
			'batch_number'      => max( 1, (int) ( $args['page_number'] ?? 1 ) ),
			'page_number'       => max( 1, (int) ( $args['page_number'] ?? 1 ) ),
			'line_number'       => max( 1, (int) ( $args['line_number'] ?? 1 ) ),
			'processed_count'   => max( 0, (int) ( $args['processed_count'] ?? 0 ) ),
			'failed_count'      => max( 0, (int) ( $args['failed_count'] ?? 0 ) ),
			'skipped_count'     => max( 0, (int) ( $args['skipped_count'] ?? 0 ) ),
			'state'             => self::STATE_RUNNING,
			'queue_snapshot'    => wp_json_encode( $this->build_queue_snapshot( $import_hash, $args ) ),
			'media_snapshot'    => wp_json_encode( $this->build_media_snapshot( $import_hash ) ),
			'schedule_id'       => isset( $args['schedule_id'] ) ? (int) $args['schedule_id'] : null,
			'file_name'         => sanitize_file_name( $args['file_name'] ?? '' ),
			'last_heartbeat_at' => $now,
			'created_at'        => $now,
			'updated_at'        => $now,
		);

		$inserted = $wpdb->insert( self::checkpoint_table(), $data );
		if ( false === $inserted || ! empty( $wpdb->last_error ) ) {
			return false;
		}

		do_action( 'sm_uci_import_checkpoint_saved', $import_hash, $data );
		return true;
	}

	/**
	 * @param array<string,mixed> $args
	 */
	public function update_checkpoint_progress( $import_hash, $args = array() ) {
		global $wpdb;
		$import_hash = sanitize_key( $import_hash );
		$row         = $this->get_checkpoint_row( $import_hash );
		if ( ! $row ) {
			return;
		}

		$snapshot = $this->decode_snapshot( $row['queue_snapshot'] );
		if ( ! empty( $args['queue_snapshot'] ) && is_array( $args['queue_snapshot'] ) ) {
			$snapshot = array_merge( $snapshot, $args['queue_snapshot'] );
		}

		$update = array(
			'updated_at'        => current_time( 'mysql', true ),
			'last_heartbeat_at' => current_time( 'mysql', true ),
			'media_snapshot'    => wp_json_encode( $this->build_media_snapshot( $import_hash ) ),
		);

		if ( isset( $args['page_number'] ) ) {
			$update['page_number']  = max( 1, (int) $args['page_number'] );
			$update['batch_number'] = $update['page_number'];
		}
		if ( isset( $args['line_number'] ) ) {
			$update['line_number'] = max( 1, (int) $args['line_number'] );
		}
		if ( isset( $args['processed_count'] ) ) {
			$update['processed_count'] = max( 0, (int) $args['processed_count'] );
		}
		if ( isset( $args['failed_count'] ) ) {
			$update['failed_count'] = max( 0, (int) $args['failed_count'] );
		}
		if ( isset( $args['skipped_count'] ) ) {
			$update['skipped_count'] = max( 0, (int) $args['skipped_count'] );
		}
		if ( ! empty( $snapshot ) ) {
			$update['queue_snapshot'] = wp_json_encode( $snapshot );
		}

		$wpdb->update( self::checkpoint_table(), $update, array( 'resume_id' => (int) $row['resume_id'] ) );
	}

	public function heartbeat( $import_hash ) {
		global $wpdb;
		$import_hash = sanitize_key( $import_hash );
		$now         = current_time( 'mysql', true );
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE " . self::checkpoint_table() . " SET last_heartbeat_at = %s, updated_at = %s
				WHERE import_hash = %s AND state IN (%s, %s, %s)",
				$now,
				$now,
				$import_hash,
				self::STATE_RUNNING,
				self::STATE_PAUSED,
				self::STATE_INTERRUPTED
			)
		);
	}

	public function mark_running( $import_hash ) {
		$this->set_state( $import_hash, self::STATE_RUNNING );
		$this->sync_log_running_flag( $import_hash, 1 );
	}

	public function mark_paused( $import_hash, $page_number = null ) {
		global $wpdb;
		$import_hash = sanitize_key( $import_hash );
		$checkpoint  = $this->get_checkpoint_row( $import_hash );
		if ( null !== $page_number ) {
			$wpdb->update(
				self::checkpoint_table(),
				array(
					'page_number'  => max( 1, (int) $page_number ),
					'batch_number' => max( 1, (int) $page_number ),
					'state'        => self::STATE_PAUSED,
					'updated_at'   => current_time( 'mysql', true ),
				),
				array( 'import_hash' => $import_hash )
			);
		} else {
			$this->set_state( $import_hash, self::STATE_PAUSED );
		}
		$this->sync_log_running_flag( $import_hash, 0 );
	}

	public function mark_completed( $import_hash ) {
		$this->set_state( $import_hash, self::STATE_COMPLETED );
		$this->sync_log_running_flag( $import_hash, 1 );
		delete_option( 'smack_csvpro_paused_record_' . $import_hash );
	}

	public function mark_interrupted( $import_hash ) {
		$this->set_state( $import_hash, self::STATE_INTERRUPTED );
		$this->sync_log_running_flag( $import_hash, 0 );
	}

	private function set_state( $import_hash, $state ) {
		global $wpdb;
		$wpdb->update(
			self::checkpoint_table(),
			array(
				'state'      => $state,
				'updated_at' => current_time( 'mysql', true ),
			),
			array( 'import_hash' => sanitize_key( $import_hash ) )
		);
	}

	public function get_page_number( $import_hash ) {
		$row = $this->get_checkpoint_row( $import_hash );
		if ( $row && ! empty( $row['page_number'] ) ) {
			return (int) $row['page_number'];
		}
		$legacy = get_option( 'sm_bulk_import_page_number' );
		return $legacy ? (int) $legacy : 1;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public function get_checkpoint_row( $import_hash ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::checkpoint_table() . ' WHERE import_hash = %s ORDER BY resume_id DESC LIMIT 1',
				sanitize_key( $import_hash )
			),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	public function is_row_completed( $import_hash, $row_number ) {
		global $wpdb;
		$row_number  = (int) $row_number;
		$import_hash = sanitize_key( $import_hash );
		if ( $row_number < 1 || empty( $import_hash ) ) {
			return false;
		}
		$row_col = self::row_log_row_col();
		$status  = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT status FROM " . self::row_log_table() . " WHERE import_hash = %s AND {$row_col} = %d LIMIT 1",
				$import_hash,
				$row_number
			)
		);
		if ( ! $status ) {
			return false;
		}
		return in_array( $status, array( self::ROW_INSERTED, self::ROW_UPDATED, self::ROW_SKIPPED ), true );
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public function get_row_log( $import_hash, $row_number ) {
		global $wpdb;
		$row_col = self::row_log_row_col();
		$row     = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . self::row_log_table() . " WHERE import_hash = %s AND {$row_col} = %d LIMIT 1",
				sanitize_key( $import_hash ),
				(int) $row_number
			),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param array<string,mixed> $detail_entry
	 */
	public function mark_row_from_detail( $import_hash, $row_number, $detail_entry, $post_id = 0 ) {
		$row_number  = (int) $row_number;
		$import_hash = sanitize_key( $import_hash );
		if ( $row_number < 1 || empty( $import_hash ) ) {
			return;
		}

		$state = '';
		if ( is_array( $detail_entry ) ) {
			$state   = isset( $detail_entry['state'] ) ? (string) $detail_entry['state'] : '';
			$post_id = ! empty( $detail_entry['id'] ) ? (int) $detail_entry['id'] : $post_id;
		}

		if ( ! in_array( $state, array( self::ROW_INSERTED, self::ROW_UPDATED, self::ROW_SKIPPED, self::ROW_FAILED ), true ) ) {
			return;
		}

		if ( self::ROW_FAILED === $state ) {
			return;
		}

		global $wpdb;
		$table = self::row_log_table();
		$now   = current_time( 'mysql', true );

		$data = array(
			'import_hash'  => $import_hash,
			'row_number'   => $row_number,
			'status'       => $state,
			'post_id'      => $post_id > 0 ? $post_id : null,
			'completed_at' => $now,
		);

		$wpdb->replace(
			$table,
			$data,
			array( '%s', '%d', '%s', '%d', '%s' )
		);

		$checkpoint = $this->get_checkpoint_row( $import_hash );
		if ( $checkpoint ) {
			$line = max( (int) $checkpoint['line_number'], $row_number + 1 );
			$this->update_checkpoint_progress(
				$import_hash,
				array(
					'line_number'     => $line,
					'processed_count' => $this->count_completed_rows( $import_hash ),
				)
			);
		}
	}

	public function count_completed_rows( $import_hash ) {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM " . self::row_log_table() . " WHERE import_hash = %s AND status IN (%s, %s, %s)",
				sanitize_key( $import_hash ),
				self::ROW_INSERTED,
				self::ROW_UPDATED,
				self::ROW_SKIPPED
			)
		);
	}

	/**
	 * @param array<string,mixed> $log_fields
	 */
	public function upsert_import_detail_log( $import_hash, $log_fields ) {
		global $wpdb;
		$table       = $wpdb->prefix . 'import_detail_log';
		$import_hash = sanitize_key( $import_hash );

		$existing_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE hash_key = %s ORDER BY id DESC LIMIT 1",
				$import_hash
			)
		);

		$log_fields['hash_key'] = $import_hash;

		if ( $existing_id ) {
			unset( $log_fields['hash_key'] );
			$wpdb->update( $table, $log_fields, array( 'id' => (int) $existing_id ) );
			return (int) $existing_id;
		}

		$wpdb->insert( $table, $log_fields );
		return (int) $wpdb->insert_id;
	}

	/**
	 * @return array{success:bool,message?:string,data?:array<string,mixed>}
	 */
	public function prepare_resume( $import_hash ) {
		global $wpdb;
		$import_hash = sanitize_key( $import_hash );
		$this->detect_stale_imports();

		$checkpoint = $this->get_checkpoint_row( $import_hash );
		if ( ! $checkpoint ) {
			return array(
				'success' => false,
				'message' => __( 'No resumable import session found.', 'wp-ultimate-csv-importer' ),
			);
		}

		if ( self::STATE_COMPLETED === $checkpoint['state'] ) {
			return array(
				'success' => false,
				'message' => __( 'This import is already completed.', 'wp-ultimate-csv-importer' ),
			);
		}

		$file_table = $wpdb->prefix . 'smackcsv_file_events';
		$file_row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$file_table} WHERE hash_key = %s LIMIT 1", $import_hash ),
			ARRAY_A
		);

		if ( empty( $file_row ) ) {
			return array(
				'success' => false,
				'message' => __( 'Import file metadata is missing. Cannot resume.', 'wp-ultimate-csv-importer' ),
			);
		}

		$template_row = $this->resolve_mapping_template( $import_hash, $file_row['file_name'] ?? '' );

		if ( empty( $template_row ) || ! $this->has_valid_mapping( $template_row['mapping'] ?? '' ) ) {
			$snapshot = $this->decode_snapshot( $checkpoint['queue_snapshot'] ?? '' );
			$from_snap = $this->mapping_from_snapshot( $snapshot );
			if ( ! empty( $from_snap['mapping'] ) ) {
				$template_row = $from_snap;
				$this->restore_mapping_template_row( $import_hash, $file_row['file_name'] ?? '', $from_snap );
			}
		}

		if ( empty( $template_row ) || ! $this->has_valid_mapping( $template_row['mapping'] ?? '' ) ) {
			return array(
				'success' => false,
				'message' => __( 'Mapping template is missing. Cannot resume.', 'wp-ultimate-csv-importer' ),
			);
		}

		$resolved_event_key = isset( $template_row['eventKey'] ) ? sanitize_key( (string) $template_row['eventKey'] ) : '';
		if ( $resolved_event_key !== $import_hash ) {
			$this->restore_mapping_template_row( $import_hash, $file_row['file_name'] ?? '', $template_row );
		}

		$upload_dir = UCICore::getInstance()->create_upload_dir();
		$file_path  = $upload_dir . $import_hash . '/' . $import_hash;
		if ( ! file_exists( $file_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Import file no longer exists on the server.', 'wp-ultimate-csv-importer' ),
			);
		}

		$snapshot = $this->decode_snapshot( $checkpoint['queue_snapshot'] );
		$this->mark_running( $import_hash );
		$this->heartbeat( $import_hash );

		$log_table = $wpdb->prefix . 'import_detail_log';
		$log_row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$log_table} WHERE hash_key = %s ORDER BY id DESC LIMIT 1",
				$import_hash
			),
			ARRAY_A
		);

		$page_number = (int) $checkpoint['page_number'];
		if ( ! empty( $checkpoint['line_number'] ) && ! empty( $snapshot['file_iteration'] ) ) {
			$iteration = max( 1, (int) $snapshot['file_iteration'] );
			$page_from_line = (int) ceil( (int) $checkpoint['line_number'] / $iteration );
			if ( $page_from_line > 0 ) {
				$page_number = max( 1, $page_from_line );
			}
		}

		update_option( 'sm_bulk_import_page_number', $page_number );

		if ( ! empty( $snapshot['file_iteration'] ) ) {
			update_option( 'sm_bulk_import_free_iteration_limit', (int) $snapshot['file_iteration'] );
		}

		return array(
			'success' => true,
			'data'    => array(
				'import_hash'        => $import_hash,
				'page_number'        => $page_number,
				'line_number'        => (int) $checkpoint['line_number'],
				'file_name'          => $file_row['file_name'],
				'mode'               => $file_row['mode'],
				'module'             => $template_row['module'],
				'total_rows'         => isset( $snapshot['total_records'] ) ? (int) $snapshot['total_records'] : (int) $file_row['total_rows'],
				'file_iteration'     => isset( $snapshot['file_iteration'] ) ? (int) $snapshot['file_iteration'] : (int) get_option( 'sm_bulk_import_free_iteration_limit', 5 ),
				'processing_records' => isset( $log_row['processing_records'] ) ? (int) $log_row['processing_records'] : (int) $checkpoint['processed_count'],
				'queue_snapshot'     => $snapshot,
				'state'              => $checkpoint['state'],
			),
		);
	}

	/**
	 * @return array{success:bool,message?:string}
	 */
	public function discard( $import_hash, $delete_upload = false ) {
		global $wpdb;
		$import_hash = sanitize_key( $import_hash );
		if ( empty( $import_hash ) ) {
			return array( 'success' => false, 'message' => __( 'Invalid import session.', 'wp-ultimate-csv-importer' ) );
		}

		$wpdb->delete( self::row_log_table(), array( 'import_hash' => $import_hash ) );
		$wpdb->delete( self::checkpoint_table(), array( 'import_hash' => $import_hash ) );

		delete_option( 'smack_csvpro_paused_record_' . $import_hash );
		delete_option( 'smack_operation_mode_' . $import_hash );
		delete_option( 'SMACK_IMAGE_INCLUDED_' . $import_hash );
		delete_option( 'smack_csv_delimiter_' . $import_hash );
		delete_option( 'smack_uci_ai_skip_condition_' . $import_hash );

		$log_table = $wpdb->prefix . 'import_detail_log';
		$wpdb->update(
			$log_table,
			array( 'status' => 'Discarded', 'running' => 0 ),
			array( 'hash_key' => $import_hash )
		);

		$events_table = $wpdb->prefix . 'smackuci_events';
		$wpdb->update(
			$events_table,
			array(
				'is_terminated' => 1,
				'processing'    => 0,
			),
			array( 'eventKey' => $import_hash )
		);

		if ( $delete_upload ) {
			$this->delete_import_upload_dir( $import_hash );
		}

		do_action( 'sm_uci_import_discarded', $import_hash );
		return array( 'success' => true );
	}

	/**
	 * @param array<string,mixed>|string|null $mapping_filter Serialized string or array.
	 */
	public function persist_mapping_snapshot( $import_hash, $module, $map, $mapping_filter = null ) {
		if ( empty( $import_hash ) || ! is_array( $map ) || empty( $map ) ) {
			return;
		}

		$filter_blob = '';
		if ( is_array( $mapping_filter ) ) {
			$filter_blob = base64_encode( maybe_serialize( $mapping_filter ) );
		} elseif ( is_string( $mapping_filter ) && '' !== $mapping_filter ) {
			$filter_blob = base64_encode( $mapping_filter );
		}

		$this->update_checkpoint_progress(
			$import_hash,
			array(
				'queue_snapshot' => array(
					'module'             => sanitize_text_field( (string) $module ),
					'mapping_b64'        => base64_encode( maybe_serialize( $map ) ),
					'mapping_filter_b64' => $filter_blob,
				),
			)
		);
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public function resolve_mapping_template( $import_hash, $file_name = '' ) {
		global $wpdb;

		$import_hash    = sanitize_key( $import_hash );
		$template_table = $wpdb->prefix . 'ultimate_csv_importer_mappingtemplate';
		$file_name      = sanitize_file_name( $file_name );

		$queries = array();

		$queries[] = array(
			$wpdb->prepare(
				"SELECT module, mapping, mapping_filter, eventKey FROM {$template_table} WHERE eventKey = %s ORDER BY id DESC LIMIT 1",
				$import_hash
			),
		);

		if ( ! empty( $file_name ) ) {
			$queries[] = array(
				$wpdb->prepare(
					"SELECT module, mapping, mapping_filter, eventKey FROM {$template_table} WHERE csvname = %s AND mapping IS NOT NULL AND mapping != '' ORDER BY id DESC LIMIT 1",
					$file_name
				),
			);
		}

		$events_row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT original_file_name FROM {$wpdb->prefix}smackuci_events WHERE eventKey = %s ORDER BY id DESC LIMIT 1",
				$import_hash
			),
			ARRAY_A
		);
		if ( ! empty( $events_row['original_file_name'] ) ) {
			$orig = sanitize_file_name( $events_row['original_file_name'] );
			$queries[] = array(
				$wpdb->prepare(
					"SELECT module, mapping, mapping_filter, eventKey FROM {$template_table} WHERE csvname = %s AND mapping IS NOT NULL AND mapping != '' ORDER BY id DESC LIMIT 1",
					$orig
				),
			);
		}

		$has_deleted = $wpdb->get_var( "SHOW COLUMNS FROM {$template_table} LIKE 'deleted'" );
		if ( $has_deleted ) {
			$queries[] = array(
				$wpdb->prepare(
					"SELECT module, mapping, mapping_filter, eventKey FROM {$template_table} WHERE (eventKey = %s OR csvname = %s) AND (deleted = 0 OR deleted IS NULL) AND mapping IS NOT NULL AND mapping != '' ORDER BY id DESC LIMIT 1",
					$import_hash,
					$file_name
				),
			);
		}

		foreach ( $queries as $query_pack ) {
			$sql = $query_pack[0];
			if ( empty( $sql ) ) {
				continue;
			}
			$row = $wpdb->get_row( $sql, ARRAY_A );
			if ( is_array( $row ) && $this->has_valid_mapping( $row['mapping'] ?? '' ) ) {
				return $row;
			}
		}

		return null;
	}

	/**
	 * @param array<string,mixed> $snapshot
	 * @return array<string,mixed>
	 */
	private function mapping_from_snapshot( $snapshot ) {
		if ( empty( $snapshot['mapping_b64'] ) ) {
			return array();
		}
		$decoded = base64_decode( $snapshot['mapping_b64'], true );
		if ( false === $decoded ) {
			return array();
		}
		$mapping = maybe_unserialize( $decoded );
		if ( ! is_array( $mapping ) || empty( $mapping ) ) {
			return array();
		}

		$filter = '';
		if ( ! empty( $snapshot['mapping_filter_b64'] ) ) {
			$filter_decoded = base64_decode( $snapshot['mapping_filter_b64'], true );
			if ( false !== $filter_decoded ) {
				$filter = $filter_decoded;
			}
		}

		return array(
			'module'         => isset( $snapshot['module'] ) ? $snapshot['module'] : '',
			'mapping'        => maybe_serialize( $mapping ),
			'mapping_filter' => $filter,
		);
	}

	/**
	 * @param array<string,mixed> $template_row
	 */
	private function restore_mapping_template_row( $import_hash, $file_name, $template_row ) {
		global $wpdb;

		$import_hash    = sanitize_key( $import_hash );
		$template_table = $wpdb->prefix . 'ultimate_csv_importer_mappingtemplate';
		$existing_id    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$template_table} WHERE eventKey = %s ORDER BY id DESC LIMIT 1",
				$import_hash
			)
		);

		$data = array(
			'mapping'        => $template_row['mapping'],
			'mapping_filter' => isset( $template_row['mapping_filter'] ) ? $template_row['mapping_filter'] : null,
			'module'         => isset( $template_row['module'] ) ? $template_row['module'] : '',
			'eventKey'       => $import_hash,
			'csvname'        => sanitize_file_name( $file_name ),
			'createdtime'    => current_time( 'mysql' ),
		);

		if ( $existing_id ) {
			unset( $data['createdtime'] );
			$wpdb->update( $template_table, $data, array( 'id' => (int) $existing_id ) );
			return;
		}

		$data['templatename'] = 'resume_' . substr( $import_hash, 0, 12 );
		$data['mapping_type'] = 'mapping-section';
		$wpdb->insert( $template_table, $data );
	}

	/**
	 * @param string $mapping_blob Serialized mapping from DB.
	 */
	private function has_valid_mapping( $mapping_blob ) {
		if ( empty( $mapping_blob ) ) {
			return false;
		}
		$map = maybe_unserialize( $mapping_blob );
		return is_array( $map ) && ! empty( $map );
	}

	/**
	 * @param array<string,mixed> $args
	 * @return array<string,mixed>
	 */
	private function build_queue_snapshot( $import_hash, $args = array() ) {
		global $wpdb;
		$file_table = $wpdb->prefix . 'smackcsv_file_events';
		$file_row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT file_name, mode, total_rows FROM {$file_table} WHERE hash_key = %s", sanitize_key( $import_hash ) ),
			ARRAY_A
		);

		$template_row = $this->resolve_mapping_template(
			$import_hash,
			$file_row ? (string) $file_row['file_name'] : ''
		);
		$module       = is_array( $template_row ) && ! empty( $template_row['module'] ) ? $template_row['module'] : '';

		return array_merge(
			array(
				'file_iteration'  => (int) get_option( 'sm_bulk_import_free_iteration_limit', 5 ),
				'total_records'   => $file_row ? (int) $file_row['total_rows'] : 0,
				'module'          => $module ? $module : '',
				'file_mode'       => $file_row ? $file_row['mode'] : '',
				'update_using'    => '',
				'rollback'        => false,
				'check'           => '',
			),
			is_array( $args['queue_snapshot'] ?? null ) ? $args['queue_snapshot'] : array(),
			array_filter(
				array(
					'update_using' => isset( $args['update_using'] ) ? sanitize_text_field( $args['update_using'] ) : '',
					'rollback'     => ! empty( $args['rollback'] ),
					'check'        => isset( $args['check'] ) ? sanitize_text_field( $args['check'] ) : '',
				)
			)
		);
	}

	private function build_media_snapshot( $import_hash ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ultimate_csv_importer_shortcode_manager';
		$hash  = sanitize_key( $import_hash );

		$pending = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE hash_key = %s AND status != 'completed'", $hash ) );
		$failed  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE hash_key = %s AND status = 'failed'", $hash ) );
		$done    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE hash_key = %s AND status = 'completed'", $hash ) );

		return array(
			'pending'   => $pending,
			'failed'    => $failed,
			'completed' => $done,
		);
	}

	private function sync_log_running_flag( $import_hash, $running ) {
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'import_detail_log',
			array( 'running' => (int) $running ),
			array( 'hash_key' => sanitize_key( $import_hash ) )
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function decode_snapshot( $json ) {
		if ( empty( $json ) ) {
			return array();
		}
		$decoded = json_decode( $json, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	private function delete_import_upload_dir( $import_hash ) {
		$upload_dir = UCICore::getInstance()->create_upload_dir();
		$dir        = trailingslashit( $upload_dir ) . sanitize_key( $import_hash );
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ( $iterator as $fileinfo ) {
			if ( $fileinfo->isDir() ) {
				rmdir( $fileinfo->getRealPath() );
			} else {
				unlink( $fileinfo->getRealPath() );
			}
		}
		rmdir( $dir );
	}

	public function sync_checkpoint_from_log( $import_hash ) {
		global $wpdb;
		$log = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT processing_records, failed, skipped FROM {$wpdb->prefix}import_detail_log WHERE hash_key = %s ORDER BY id DESC LIMIT 1",
				sanitize_key( $import_hash )
			),
			ARRAY_A
		);
		if ( ! $log ) {
			return;
		}
		$this->update_checkpoint_progress(
			$import_hash,
			array(
				'processed_count' => (int) $log['processing_records'],
				'failed_count'    => (int) $log['failed'],
				'skipped_count'   => (int) $log['skipped'],
			)
		);
	}
}
