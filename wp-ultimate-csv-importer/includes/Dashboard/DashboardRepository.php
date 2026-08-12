<?php
/**
 * Data access layer for the WP Ultimate CSV Importer dashboard.
 *
 * Centralizes all SQL used by dashboard widgets so views and controllers
 * stay free of raw queries.
 *
 * @package Smackcoders\UCI\Core\Dashboard
 */

namespace Smackcoders\UCI\Core\Dashboard;

use Smackcoders\UCI\Core\ExtensionHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Executes optimized, prepared database queries for dashboard statistics.
 */
class DashboardRepository {

	/** @var \wpdb */
	private $wpdb;

	/** @var string */
	private $events_table;

	/** @var string */
	private $detail_log_table;

	/** @var string */
	private $cli_template_table;

	/** @var string */
	private $templates_table;

	/** @var ExtensionHandler */
	private $extension_handler;

	/**
	 * @param \wpdb|null $wpdb Optional wpdb instance (for testing).
	 */
	public function __construct( $wpdb_instance = null ) {
		global $wpdb;

		$this->wpdb               = $wpdb_instance ? $wpdb_instance : $wpdb;
		$this->events_table       = $this->wpdb->prefix . 'smackuci_events';
		$this->detail_log_table   = $this->wpdb->prefix . 'import_detail_log';
		$this->cli_template_table = $this->wpdb->prefix . 'cli_csv_template';
		$this->templates_table    = $this->wpdb->prefix . 'ultimate_csv_importer_mappingtemplate';
		$this->extension_handler  = new ExtensionHandler();
	}

	/**
	 * Returns true when a database table exists in the current site.
	 *
	 * @param string $table_name Fully-qualified table name.
	 * @return bool
	 */
	public function table_exists( $table_name ) {
		$found = $this->wpdb->get_var(
			$this->wpdb->prepare( 'SHOW TABLES LIKE %s', $this->wpdb->esc_like( $table_name ) )
		);
		return $found === $table_name;
	}

	/**
	 * Counts non-deleted import runs, optionally limited to a date range.
	 *
	 * @param string|null $from Inclusive start datetime (Y-m-d H:i:s).
	 * @param string|null $to   Inclusive end datetime (Y-m-d H:i:s).
	 * @return int
	 */
	public function count_import_runs( $from = null, $to = null ) {
		$sql    = "SELECT COUNT(*) FROM {$this->events_table} WHERE deletelog = 0";
		$params = array();

		if ( $from ) {
			$sql     .= ' AND last_activity >= %s';
			$params[] = $from;
		}
		if ( $to ) {
			$sql     .= ' AND last_activity <= %s';
			$params[] = $to;
		}

		if ( ! empty( $params ) ) {
			$sql = $this->wpdb->prepare( $sql, $params );
		}

		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Sums created / updated / skipped / failed counters for import history.
	 *
	 * @param string|null $from Optional inclusive start datetime.
	 * @param string|null $to   Optional inclusive end datetime.
	 * @return array{created:int,updated:int,skipped:int,failed:int}
	 */
	public function sum_record_counters( $from = null, $to = null ) {
		$sql    = "SELECT COALESCE(SUM(created),0) AS created, COALESCE(SUM(updated),0) AS updated,
			COALESCE(SUM(skipped),0) AS skipped, COALESCE(SUM(failed),0) AS failed
			FROM {$this->events_table} WHERE deletelog = 0";
		$params = array();

		if ( $from ) {
			$sql     .= ' AND last_activity >= %s';
			$params[] = $from;
		}
		if ( $to ) {
			$sql     .= ' AND last_activity <= %s';
			$params[] = $to;
		}

		if ( ! empty( $params ) ) {
			$sql = $this->wpdb->prepare( $sql, $params );
		}

		$row = $this->wpdb->get_row( $sql, ARRAY_A );
		return array(
			'created' => isset( $row['created'] ) ? (int) $row['created'] : 0,
			'updated' => isset( $row['updated'] ) ? (int) $row['updated'] : 0,
			'skipped' => isset( $row['skipped'] ) ? (int) $row['skipped'] : 0,
			'failed'  => isset( $row['failed'] ) ? (int) $row['failed'] : 0,
		);
	}

	/**
	 * Builds import-run counts for the requested period type.
	 *
	 * Uses last_activity with registered_on fallback so legacy rows with
	 * invalid month/year columns still chart correctly.
	 *
	 * @param string $period Period ('weekly', 'monthly', 'yearly').
	 * @return array<int,array{key:string,label:string,count:int}>
	 */
	public function get_import_volume_series( $period = 'yearly' ) {
		$period = strtolower( trim( $period ) );

		if ( 'weekly' === $period ) {
			$count       = 12; // 12 weeks
			$date_format = '%%x-%%v'; // Year-Week (ISO 8601)
			$start       = gmdate( 'Y-m-d 00:00:00', strtotime( '-11 weeks Monday' ) );
		} elseif ( 'monthly' === $period ) {
			$count       = 12; // 12 months
			$date_format = '%%Y-%%m'; // Year-Month
			$start       = gmdate( 'Y-m-01 00:00:00', strtotime( '-11 months' ) );
		} else {
			// yearly fallback
			$count       = 5; // 5 years
			$date_format = '%%Y'; // Year
			$start       = gmdate( 'Y-01-01 00:00:00', strtotime( '-4 years' ) );
		}

		$sql = $this->wpdb->prepare(
			"SELECT DATE_FORMAT(COALESCE(NULLIF(last_activity, '0000-00-00 00:00:00'), registered_on), '{$date_format}') AS period_key,
			COUNT(*) AS import_count
			FROM {$this->events_table}
			WHERE deletelog = 0
			AND COALESCE(NULLIF(last_activity, '0000-00-00 00:00:00'), registered_on) >= %s
			GROUP BY period_key
			ORDER BY period_key ASC",
			$start
		);

		$rows    = $this->wpdb->get_results( $sql, ARRAY_A );
		$indexed = array();
		foreach ( (array) $rows as $row ) {
			if ( ! empty( $row['period_key'] ) ) {
				$indexed[ $row['period_key'] ] = (int) $row['import_count'];
			}
		}

		$series = array();
		for ( $i = $count - 1; $i >= 0; $i-- ) {
			if ( 'weekly' === $period ) {
				$ts    = strtotime( "-{$i} weeks" );
				$key   = gmdate( 'o-W', $ts ); // ISO year and week
				$label = 'W' . gmdate( 'W', $ts );
			} elseif ( 'monthly' === $period ) {
				$ts    = strtotime( "-{$i} months" );
				$key   = gmdate( 'Y-m', $ts );
				$label = gmdate( 'M', $ts );
			} else {
				$ts    = strtotime( "-{$i} years" );
				$key   = gmdate( 'Y', $ts );
				$label = $key;
			}

			$series[] = array(
				'key'   => $key,
				'label' => $label,
				'count' => isset( $indexed[ $key ] ) ? $indexed[ $key ] : 0,
			);
		}

		return $series;
	}

	/**
	 * Returns per-import-type created totals for the content distribution panel.
	 *
	 * @return array<int,array{slug:string,label:string,count:int}>
	 */
	public function get_content_distribution() {
		$sql = "SELECT import_type AS slug, COALESCE(SUM(created),0) AS count
			FROM {$this->events_table}
			WHERE deletelog = 0
			GROUP BY import_type
			HAVING count > 0
			ORDER BY count DESC";

		$rows = $this->wpdb->get_results( $sql, ARRAY_A );
		return $this->normalize_distribution_rows( (array) $rows );
	}

	/**
	 * Maps raw import_type rows into grouped dashboard buckets.
	 *
	 * @param array<int,array{slug:string,count:int}> $rows Raw SQL rows.
	 * @return array<int,array{slug:string,label:string,count:int}>
	 */
	private function normalize_distribution_rows( array $rows ) {
		$buckets = array(
			'woocommerce' => array(
				'label' => __( 'WooCommerce Products', 'wp-ultimate-csv-importer' ),
				'count' => 0,
			),
			'posts'       => array(
				'label' => __( 'Posts', 'wp-ultimate-csv-importer' ),
				'count' => 0,
			),
			'pages'       => array(
				'label' => __( 'Pages', 'wp-ultimate-csv-importer' ),
				'count' => 0,
			),
			'users'       => array(
				'label' => __( 'Users / Customers', 'wp-ultimate-csv-importer' ),
				'count' => 0,
			),
			'media'       => array(
				'label' => __( 'Media', 'wp-ultimate-csv-importer' ),
				'count' => 0,
			),
			'custom'      => array(
				'label' => __( 'Custom Post Types', 'wp-ultimate-csv-importer' ),
				'count' => 0,
			),
		);

		foreach ( $rows as $row ) {
			$slug  = isset( $row['slug'] ) ? (string) $row['slug'] : '';
			$count = isset( $row['count'] ) ? (int) $row['count'] : 0;
			$key   = $this->map_import_type_to_bucket( $slug );

			if ( isset( $buckets[ $key ] ) ) {
				$buckets[ $key ]['count'] += $count;
			}
		}

		$output = array();
		foreach ( $buckets as $slug => $bucket ) {
			$output[] = array(
				'slug'  => $slug,
				'label' => $bucket['label'],
				'count' => $bucket['count'],
			);
		}

		return $output;
	}

	/**
	 * Resolves a raw import_type value to a distribution bucket key.
	 *
	 * @param string $import_type Database import_type value.
	 * @return string Bucket key.
	 */
	private function map_import_type_to_bucket( $import_type ) {
		$normalized = strtolower( trim( (string) $import_type ) );

		if ( in_array( $normalized, array( 'woocommerce', 'woocommerce product', 'product', 'wpecommerce' ), true ) ) {
			return 'woocommerce';
		}
		if ( in_array( $normalized, array( 'posts', 'post' ), true ) ) {
			return 'posts';
		}
		if ( in_array( $normalized, array( 'pages', 'page' ), true ) ) {
			return 'pages';
		}
		if ( in_array( $normalized, array( 'users', 'user', 'woocommercecustomer' ), true ) ) {
			return 'users';
		}
		if ( in_array( $normalized, array( 'media', 'attachment' ), true ) ) {
			return 'media';
		}
		if ( in_array( $normalized, array( 'customposts', 'comments', 'categories', 'tags', 'taxonomies' ), true ) ) {
			return 'custom';
		}

		$post_types = get_post_types( array( 'public' => true ), 'names' );
		if ( in_array( $normalized, array_map( 'strtolower', $post_types ), true ) ) {
			return 'custom';
		}

		return 'custom';
	}

	/**
	 * Fetches the most recent import log entries for the activity widget.
	 *
	 * @param int $limit Maximum rows to return.
	 * @return array<int,array<string,mixed>>
	 */
	public function get_recent_activity( $limit = 10 ) {
		$limit = max( 1, (int) $limit );

		$sql = $this->wpdb->prepare(
			"SELECT id, original_file_name, import_type, created, updated, skipped, failed,
			processing, last_activity, registered_on, revision
			FROM {$this->events_table}
			WHERE deletelog = 0
			ORDER BY COALESCE(NULLIF(last_activity, '0000-00-00 00:00:00'), registered_on) DESC
			LIMIT %d",
			$limit
		);

		$rows = $this->wpdb->get_results( $sql, ARRAY_A );
		$out  = array();

		foreach ( (array) $rows as $row ) {
			$created  = isset( $row['created'] ) ? (int) $row['created'] : 0;
			$updated  = isset( $row['updated'] ) ? (int) $row['updated'] : 0;
			$skipped  = isset( $row['skipped'] ) ? (int) $row['skipped'] : 0;
			$failed   = isset( $row['failed'] ) ? (int) $row['failed'] : 0;
			$records  = $created + $updated + $skipped;
			$activity = $this->resolve_activity_label( $row );
			$status   = $this->resolve_activity_status( $row );

			$date_raw = ! empty( $row['last_activity'] ) && '0000-00-00 00:00:00' !== $row['last_activity']
				? $row['last_activity']
				: ( isset( $row['registered_on'] ) ? $row['registered_on'] : '' );

			$out[] = array(
				'date'     => $date_raw,
				'activity' => $activity,
				'status'   => $status,
				'records'  => $records,
				'created'  => $created,
				'updated'  => $updated,
				'skipped'  => $skipped,
				'failed'   => $failed,
				'module'   => $this->resolve_import_label( isset( $row['import_type'] ) ? $row['import_type'] : '' ),
				'filename' => isset( $row['original_file_name'] ) ? $row['original_file_name'] : '',
				'revision' => isset( $row['revision'] ) ? (int) $row['revision'] : 0,
			);
		}

		return $out;
	}

	/**
	 * Builds human-readable activity text for a log row.
	 *
	 * @param array<string,mixed> $row Event row.
	 * @return string
	 */
	private function resolve_activity_label( array $row ) {
		$type = isset( $row['import_type'] ) ? strtolower( (string) $row['import_type'] ) : '';

		if ( 'media' === $type ) {
			return __( 'Media Imported', 'wp-ultimate-csv-importer' );
		}
		if ( ! empty( $row['failed'] ) && (int) $row['failed'] > 0 ) {
			return __( 'Import Failed', 'wp-ultimate-csv-importer' );
		}

		return __( 'Import Completed', 'wp-ultimate-csv-importer' );
	}

	/**
	 * Derives a compact status label for activity rows.
	 *
	 * @param array<string,mixed> $row Event row.
	 * @return string
	 */
	private function resolve_activity_status( array $row ) {
		if ( ! empty( $row['failed'] ) && (int) $row['failed'] > 0 ) {
			return __( 'Failed', 'wp-ultimate-csv-importer' );
		}
		return __( 'Completed', 'wp-ultimate-csv-importer' );
	}

	/**
	 * Converts internal import_type slugs to display labels.
	 *
	 * @param string $import_type Raw import type.
	 * @return string
	 */
	private function resolve_import_label( $import_type ) {
		$types = array_flip( $this->extension_handler->get_import_post_types() );
		if ( isset( $types[ $import_type ] ) ) {
			return $types[ $import_type ];
		}
		return (string) $import_type;
	}

	/**
	 * Counts saved mapping templates that are not soft-deleted.
	 *
	 * @return int
	 */
	public function count_saved_templates() {
		if ( ! $this->table_exists( $this->templates_table ) ) {
			return 0;
		}

		return (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->templates_table} WHERE deleted = 0"
		);
	}

	/**
	 * Counts templates created within an optional date range.
	 *
	 * @param string|null $from Inclusive start datetime (Y-m-d H:i:s).
	 * @param string|null $to   Inclusive end datetime (Y-m-d H:i:s).
	 * @return int
	 */
	public function count_templates_in_range( $from = null, $to = null ) {
		if ( ! $this->table_exists( $this->templates_table ) ) {
			return 0;
		}

		$sql    = "SELECT COUNT(*) FROM {$this->templates_table} WHERE deleted = 0";
		$params = array();

		if ( $from ) {
			$sql     .= ' AND createdtime >= %s';
			$params[] = $from;
		}
		if ( $to ) {
			$sql     .= ' AND createdtime <= %s';
			$params[] = $to;
		}

		if ( ! empty( $params ) ) {
			$sql = $this->wpdb->prepare( $sql, $params );
		}

		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Counts scheduled import and export jobs across known addon tables.
	 *
	 * @return int
	 */
	public function count_scheduled_jobs() {
		$tables = array(
			$this->wpdb->prefix . 'ultimate_csv_importer_scheduled_import',
			$this->wpdb->prefix . 'sm_uci_addon_pro_scheduled_import',
			$this->wpdb->prefix . 'sm_uci_addon_pro_scheduled_export',
			$this->wpdb->prefix . 'smackuci_addon_custom_fields_pro_scheduled_import',
			$this->wpdb->prefix . 'smackuci_addon_custom_fields_pro_scheduled_export',
		);

		$total = 0;
		foreach ( $tables as $table ) {
			if ( ! $this->table_exists( $table ) ) {
				continue;
			}
			$total += (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		}

		return $total;
	}

	/**
	 * Counts failed import runs and failed scheduled jobs when available.
	 *
	 * @return int
	 */
	public function count_failed_jobs() {
		$failed = (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->events_table} WHERE deletelog = 0 AND failed > 0"
		);

		$export_tables = array(
			$this->wpdb->prefix . 'sm_uci_addon_pro_scheduled_export',
			$this->wpdb->prefix . 'smackuci_addon_custom_fields_pro_scheduled_export',
		);

		foreach ( $export_tables as $table ) {
			if ( ! $this->table_exists( $table ) ) {
				continue;
			}
			$failed += (int) $this->wpdb->get_var(
				"SELECT COUNT(*) FROM {$table} WHERE cron_status = 'failed'"
			);
		}

		return $failed;
	}

	/**
	 * Estimates exported record volume from completed scheduled export jobs.
	 *
	 * @return int
	 */
	public function sum_exported_records() {
		$tables = array(
			$this->wpdb->prefix . 'sm_uci_addon_pro_scheduled_export',
			$this->wpdb->prefix . 'smackuci_addon_custom_fields_pro_scheduled_export',
		);

		$total = 0;
		foreach ( $tables as $table ) {
			if ( ! $this->table_exists( $table ) ) {
				continue;
			}

			$sum = $this->wpdb->get_var(
				"SELECT COALESCE(SUM(end_limit),0) FROM {$table}
				WHERE cron_status = 'completed' OR (lastrun IS NOT NULL AND lastrun != '0000-00-00 00:00:00')"
			);
			$total += (int) $sum;
		}

		/**
		 * Allow Pro/addon exporters to contribute export totals.
		 *
		 * @param int $total Current exported record estimate.
		 */
		return (int) apply_filters( 'wpucsv_dashboard_exported_records', $total );
	}

	/**
	 * Counts completed export jobs within an optional date range.
	 *
	 * @param string|null $from Inclusive start datetime.
	 * @param string|null $to   Inclusive end datetime.
	 * @return int
	 */
	public function count_exports_in_range( $from = null, $to = null ) {
		$tables = array(
			$this->wpdb->prefix . 'sm_uci_addon_pro_scheduled_export',
			$this->wpdb->prefix . 'smackuci_addon_custom_fields_pro_scheduled_export',
		);

		$total = 0;
		foreach ( $tables as $table ) {
			if ( ! $this->table_exists( $table ) ) {
				continue;
			}

			$sql    = "SELECT COUNT(*) FROM {$table} WHERE cron_status = 'completed'";
			$params = array();

			if ( $from ) {
				$sql     .= ' AND lastrun >= %s';
				$params[] = $from;
			}
			if ( $to ) {
				$sql     .= ' AND lastrun <= %s';
				$params[] = $to;
			}

			if ( ! empty( $params ) ) {
				$sql = $this->wpdb->prepare( $sql, $params );
			}

			$total += (int) $this->wpdb->get_var( $sql );
		}

		return $total;
	}

	/**
	 * Counts completed export jobs across addon export schedule tables.
	 *
	 * @return int
	 */
	public function count_exports() {
		$tables = array(
			$this->wpdb->prefix . 'sm_uci_addon_pro_scheduled_export',
			$this->wpdb->prefix . 'smackuci_addon_custom_fields_pro_scheduled_export',
		);

		$total = 0;
		foreach ( $tables as $table ) {
			if ( ! $this->table_exists( $table ) ) {
				continue;
			}
			$total += (int) $this->wpdb->get_var(
				"SELECT COUNT(*) FROM {$table}
				WHERE cron_status = 'completed' OR (lastrun IS NOT NULL AND lastrun != '0000-00-00 00:00:00')"
			);
		}

		return (int) apply_filters( 'wpucsv_dashboard_export_count', $total );
	}

	/**
	 * Sums media records created through imports.
	 *
	 * @return int
	 */
	public function count_media_imported() {
		return (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(SUM(created),0) FROM {$this->events_table}
				WHERE deletelog = 0 AND import_type IN (%s, %s, %s)",
				'Media',
				'media',
				'attachment'
			)
		);
	}

	/**
	 * Legacy Importers Activity line-chart payload (backward compatible).
	 *
	 * @return array{success:bool,label:array<int,string>,data:array<int,array<int,int>>}
	 */
	public function get_line_chart_data() {
		$available_types = $this->get_available_type_labels();
		$periods         = $this->build_chart_periods( 12 );
		$import_types    = $this->get_distinct_import_types();

		$labels = array();
		$data   = array();

		foreach ( $import_types as $import_type ) {
			$series = array();
			foreach ( $periods as $period ) {
				$series[] = $this->sum_created_for_period( $import_type, $period['month'], $period['year'] );
			}

			$labels[] = isset( $available_types[ $import_type ] ) ? $available_types[ $import_type ] : $import_type;
			$data[]   = $series;
		}

		return array(
			'success' => true,
			'label'   => $labels,
			'data'    => $data,
		);
	}

	/**
	 * Legacy Import Statistics bar-chart payload (backward compatible).
	 *
	 * @return array<string,mixed>
	 */
	public function get_bar_chart_data() {
		$available_types = $this->get_available_type_labels();
		$import_types    = $this->get_distinct_import_types();
		$return          = array( 'success' => true );

		foreach ( $import_types as $import_type ) {
			$event_stats = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT COALESCE(SUM(created),0) AS created, COALESCE(SUM(updated),0) AS updated,
					COALESCE(SUM(skipped),0) AS skipped
					FROM {$this->events_table} WHERE import_type = %s AND deletelog = 0",
					$import_type
				),
				ARRAY_A
			);

			$cli_stats = array( 'created' => 0, 'updated' => 0, 'skipped' => 0 );
			if ( $this->table_exists( $this->cli_template_table ) && 'event-recurring' !== $import_type ) {
				$cli_stats = $this->wpdb->get_row(
					$this->wpdb->prepare(
						"SELECT COALESCE(SUM(log.created),0) AS created, COALESCE(SUM(log.updated),0) AS updated,
						COALESCE(SUM(log.skipped),0) AS skipped
						FROM {$this->detail_log_table} AS log
						INNER JOIN {$this->cli_template_table} AS cli ON cli.templatekey = log.templatekey
						WHERE cli.type = %s",
						$import_type
					),
					ARRAY_A
				);
			}

			$label = isset( $available_types[ $import_type ] ) ? $available_types[ $import_type ] : $import_type;
			$return[ $label ] = array(
				'created' => (int) ( isset( $event_stats['created'] ) ? $event_stats['created'] : 0 ) + (int) ( isset( $cli_stats['created'] ) ? $cli_stats['created'] : 0 ),
				'updated' => (int) ( isset( $event_stats['updated'] ) ? $event_stats['updated'] : 0 ) + (int) ( isset( $cli_stats['updated'] ) ? $cli_stats['updated'] : 0 ),
				'skipped' => (int) ( isset( $event_stats['skipped'] ) ? $event_stats['skipped'] : 0 ) + (int) ( isset( $cli_stats['skipped'] ) ? $cli_stats['skipped'] : 0 ),
			);
		}

		return $return;
	}

	/**
	 * Returns distinct import types from events and CLI templates.
	 *
	 * @return array<int,string>
	 */
	private function get_distinct_import_types() {
		$types = $this->wpdb->get_col(
			"SELECT DISTINCT import_type FROM {$this->events_table} WHERE import_type IS NOT NULL AND import_type != ''"
		);

		if ( $this->table_exists( $this->cli_template_table ) ) {
			$cli_types = $this->wpdb->get_col(
				"SELECT DISTINCT type FROM {$this->cli_template_table} WHERE type IS NOT NULL AND type != ''"
			);
			$types = array_unique( array_merge( (array) $types, (array) $cli_types ) );
		}

		return array_values( array_filter( $types ) );
	}

	/**
	 * Flips import post-type map for label lookups.
	 *
	 * @return array<string,string>
	 */
	private function get_available_type_labels() {
		$available = array();
		foreach ( $this->extension_handler->get_import_post_types() as $name => $type ) {
			$available[ $type ] = $name;
		}
		foreach ( get_taxonomies() as $taxonomy_name ) {
			$available[ $taxonomy_name ] = $taxonomy_name;
		}
		return $available;
	}

	/**
	 * Builds month/year tuples for the trailing number of months.
	 *
	 * @param int $count Month count.
	 * @return array<int,array{month:string,year:string,key:string}>
	 */
	private function build_chart_periods( $count ) {
		$periods = array();
		$today   = current_time( 'mysql' );

		for ( $i = $count - 1; $i >= 0; $i-- ) {
			$ts        = strtotime( $today . " -{$i} months" );
			$periods[] = array(
				'month' => gmdate( 'M', $ts ),
				'year'  => gmdate( 'Y', $ts ),
				'key'   => gmdate( 'Y-m', $ts ),
			);
		}

		return $periods;
	}

	/**
	 * Sums created records for one import type and month, with legacy fallbacks.
	 *
	 * @param string $import_type Import type slug.
	 * @param string $month       Three-letter month abbreviation.
	 * @param string $year        Four-digit year.
	 * @return int
	 */
	private function sum_created_for_period( $import_type, $month, $year ) {
		$period_key = $year . '-' . gmdate( 'm', strtotime( "{$year}-{$month}-01" ) );

		$event_sum = (int) $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COALESCE(SUM(created),0) FROM {$this->events_table}
				WHERE deletelog = 0 AND import_type = %s
				AND (
					(month = %s AND year = %s)
					OR DATE_FORMAT(COALESCE(NULLIF(last_activity, '0000-00-00 00:00:00'), registered_on), '%%Y-%%m') = %s
				)",
				$import_type,
				$month,
				$year,
				$period_key
			)
		);

		$cli_sum = 0;
		if ( $this->table_exists( $this->cli_template_table ) && 'event-recurring' !== $import_type ) {
			$cli_sum = (int) $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT COALESCE(SUM(log.created),0) FROM {$this->detail_log_table} AS log
					INNER JOIN {$this->cli_template_table} AS cli ON cli.templatekey = log.templatekey
					WHERE cli.type = %s
					AND (
						(cli.month = %s AND cli.year = %s)
						OR (cli.month = %s AND cli.Year = %s)
					)",
					$import_type,
					$month,
					$year,
					$month,
					$year
				)
			);
		}

		return $event_sum + $cli_sum;
	}
}
