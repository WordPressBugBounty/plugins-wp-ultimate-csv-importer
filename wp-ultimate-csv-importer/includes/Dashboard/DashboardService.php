<?php
/**
 * Business logic and caching for dashboard statistics.
 *
 * @package Smackcoders\UCI\Core\Dashboard
 */

namespace Smackcoders\UCI\Core\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Aggregates repository data, applies growth calculations, and caches responses.
 */
class DashboardService {

	const CACHE_GROUP = 'wpucsv_dashboard';
	const CACHE_TTL   = 300;

	/** @var DashboardRepository */
	private $repository;

	/**
	 * @param DashboardRepository|null $repository Optional repository override.
	 */
	public function __construct( DashboardRepository $repository = null ) {
		$this->repository = $repository ? $repository : new DashboardRepository();
	}

	/**
	 * Clears all dashboard transients after imports or exports complete.
	 *
	 * @return void
	 */
	public static function bust_cache() {
		$keys = array(
			'kpi',
			'volume',
			'distribution',
			'activity',
			'quick_stats',
			'line_chart',
			'bar_chart',
		);

		foreach ( $keys as $key ) {
			delete_transient( self::transient_key( $key ) );
		}
	}

	/**
	 * Builds a namespaced transient key for dashboard payloads.
	 *
	 * @param string $suffix Cache segment name.
	 * @return string
	 */
	private static function transient_key( $suffix ) {
		return 'wpucsv_dashboard_' . sanitize_key( $suffix );
	}

	/**
	 * Reads a cached payload or executes the callback and stores the result.
	 *
	 * @param string   $key      Transient suffix.
	 * @param callable $callback Data producer.
	 * @return mixed
	 */
	private function remember( $key, callable $callback ) {
		$transient = self::transient_key( $key );
		$cached    = get_transient( $transient );

		if ( false !== $cached ) {
			return $cached;
		}

		$data = call_user_func( $callback );
		set_transient( $transient, $data, self::CACHE_TTL );
		return $data;
	}

	/**
	 * Returns KPI card statistics for the dashboard header.
	 *
	 * @return array<string,mixed>
	 */
	public function get_kpi_stats() {
		return $this->remember( 'kpi', function () {
			$now        = current_time( 'mysql' );
			$last_30    = gmdate( 'Y-m-d H:i:s', strtotime( $now . ' -30 days' ) );
			$prev_30_to = gmdate( 'Y-m-d H:i:s', strtotime( $now . ' -30 days' ) );
			$prev_30    = gmdate( 'Y-m-d H:i:s', strtotime( $now . ' -60 days' ) );

			$total_imports   = $this->repository->count_import_runs();
			$imports_30      = $this->repository->count_import_runs( $last_30, $now );
			$imports_prev_30 = $this->repository->count_import_runs( $prev_30, $prev_30_to );

			$records_all     = $this->repository->sum_record_counters();
			$records_30      = $this->repository->sum_record_counters( $last_30, $now );
			$records_prev_30 = $this->repository->sum_record_counters( $prev_30, $prev_30_to );

			$exports_total      = $this->repository->sum_exported_records();
			$exports_30         = $this->repository->count_exports_in_range( $last_30, $now );
			$exports_prev_30    = $this->repository->count_exports_in_range( $prev_30, $prev_30_to );
			$exports_growth     = $this->calculate_growth_percent( $exports_30, $exports_prev_30 );

			$templates_total   = $this->repository->count_saved_templates();
			$templates_30      = $this->repository->count_templates_in_range( $last_30, $now );
			$templates_prev_30 = $this->repository->count_templates_in_range( $prev_30, $prev_30_to );

			return array(
				'success' => true,
				'cards'   => array(
					'imports_run'      => array(
						'total'            => $total_imports,
						'recent'           => $imports_30,
						'growth_percent'   => $this->calculate_growth_percent( $imports_30, $imports_prev_30 ),
						'recent_label'     => sprintf(
							/* translators: %d: number of imports in the last 30 days */
							__( '+%d this month', 'wp-ultimate-csv-importer' ),
							$imports_30
						),
					),
					'records_created'  => array(
						'total'          => $records_all['created'],
						'growth_percent' => $this->calculate_growth_percent( $records_30['created'], $records_prev_30['created'] ),
					),
					'records_exported' => array(
						'total'          => $exports_total,
						'growth_percent' => $exports_growth,
					),
					'total_templates'  => array(
						'total'          => $templates_total,
						'recent'         => $templates_30,
						'growth_percent' => $this->calculate_growth_percent( $templates_30, $templates_prev_30 ),
						'recent_label'   => sprintf(
							/* translators: %d: number of templates created in the last 30 days */
							__( '+%d this month', 'wp-ultimate-csv-importer' ),
							$templates_30
						),
					),
				),
			);
		} );
	}

	/**
	 * Calculates percentage growth between two integer values.
	 *
	 * @param int|float $current  Current period value.
	 * @param int|float $previous Previous period value.
	 * @return float
	 */
	private function calculate_growth_percent( $current, $previous ) {
		$current  = (float) $current;
		$previous = (float) $previous;

		if ( $previous <= 0 ) {
			return $current > 0 ? 100.0 : 0.0;
		}

		return round( ( ( $current - $previous ) / $previous ) * 100, 1 );
	}

	/**
	 * Loads cumulative AI / validation assist counters for the KPI card.
	 *
	 * @return array<string,int>
	 */
	public function get_ai_assist_stats() {
		$defaults = array(
			'total'               => 0,
			'auto_fixes'          => 0,
			'import_corrections'  => 0,
			'validation_fixes'    => 0,
		);

		$stored = get_option( 'wpucsv_dashboard_ai_stats', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$stats = wp_parse_args( $stored, $defaults );

		/**
		 * Filter AI assist counters displayed on the dashboard.
		 *
		 * @param array<string,int> $stats AI assist counters.
		 */
		$stats = apply_filters( 'wpucsv_dashboard_ai_assists', $stats );
		$stats['total'] = (int) $stats['auto_fixes'] + (int) $stats['import_corrections'] + (int) $stats['validation_fixes'];

		return $stats;
	}

	/**
	 * Records validation-driven assist metrics when a CSV preflight scan completes.
	 *
	 * @param array<string,mixed> $validation_response ValidationEngine response array.
	 * @return void
	 */
	public static function record_validation_assists( array $validation_response ) {
		$warning_count  = isset( $validation_response['warning_count'] ) ? (int) $validation_response['warning_count'] : 0;
		$critical_count = isset( $validation_response['critical_count'] ) ? (int) $validation_response['critical_count'] : 0;

		if ( $warning_count <= 0 && $critical_count <= 0 ) {
			return;
		}

		$stored = get_option( 'wpucsv_dashboard_ai_stats', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array(
				'auto_fixes'         => 0,
				'import_corrections' => 0,
				'validation_fixes'   => 0,
			);
		}

		$stored['validation_fixes']   = isset( $stored['validation_fixes'] ) ? (int) $stored['validation_fixes'] : 0;
		$stored['import_corrections'] = isset( $stored['import_corrections'] ) ? (int) $stored['import_corrections'] : 0;
		$stored['auto_fixes']         = isset( $stored['auto_fixes'] ) ? (int) $stored['auto_fixes'] : 0;

		$stored['validation_fixes']   += $warning_count;
		$stored['import_corrections'] += $critical_count;

		update_option( 'wpucsv_dashboard_ai_stats', $stored, false );
		self::bust_cache();
	}

	/**
	 * Returns import volume chart data for the requested period.
	 *
	 * @param string $period 'weekly', 'monthly', or 'yearly'.
	 * @return array<string,mixed>
	 */
	public function get_import_volume( $period = 'yearly' ) {
		$cache_key = 'volume_' . strtolower( trim( $period ) );
		return $this->remember( $cache_key, function () use ( $period ) {
			$series = $this->repository->get_import_volume_series( $period );
			return array(
				'success' => true,
				'labels'  => wp_list_pluck( $series, 'label' ),
				'counts'  => wp_list_pluck( $series, 'count' ),
				'series'  => $series,
			);
		} );
	}

	/**
	 * Returns grouped content distribution statistics with progress percentages.
	 *
	 * @return array<string,mixed>
	 */
	public function get_content_distribution() {
		return $this->remember( 'distribution', function () {
			$rows = $this->repository->get_content_distribution();
			$max  = 0;

			foreach ( $rows as $row ) {
				$max = max( $max, (int) $row['count'] );
			}

			$items = array();
			foreach ( $rows as $row ) {
				$count = (int) $row['count'];
				$items[] = array(
					'label'      => $row['label'],
					'count'      => $count,
					'percent'    => $max > 0 ? round( ( $count / $max ) * 100, 1 ) : 0,
				);
			}

			return array(
				'success' => true,
				'items'   => $items,
				'max'     => $max,
			);
		} );
	}

	/**
	 * Returns recent import activity rows for the dashboard widget.
	 *
	 * @param int $limit Row limit.
	 * @return array<string,mixed>
	 */
	public function get_recent_activity( $limit = 10 ) {
		return $this->remember( 'activity', function () use ( $limit ) {
			return array(
				'success' => true,
				'items'   => $this->repository->get_recent_activity( $limit ),
			);
		} );
	}

	/**
	 * Returns the quick-stats summary row.
	 *
	 * @return array<string,mixed>
	 */
	public function get_quick_stats() {
		return $this->remember( 'quick_stats', function () {
			return array(
				'success' => true,
				'stats'   => array(
					'total_imports'   => $this->repository->count_import_runs(),
					'total_exports'   => $this->repository->count_exports(),
					'scheduled_jobs'  => $this->repository->count_scheduled_jobs(),
					'failed_jobs'     => $this->repository->count_failed_jobs(),
					'templates_saved' => $this->repository->count_saved_templates(),
					'media_imported'  => $this->repository->count_media_imported(),
				),
			);
		} );
	}

	/**
	 * Returns cached legacy line chart data.
	 *
	 * @return array<string,mixed>
	 */
	public function get_line_chart_data() {
		return $this->remember( 'line_chart', array( $this->repository, 'get_line_chart_data' ) );
	}

	/**
	 * Returns cached legacy bar chart data.
	 *
	 * @return array<string,mixed>
	 */
	public function get_bar_chart_data() {
		return $this->remember( 'bar_chart', array( $this->repository, 'get_bar_chart_data' ) );
	}
}
