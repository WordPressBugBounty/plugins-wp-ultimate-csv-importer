<?php
/**
 * AJAX controller for the WP Ultimate CSV Importer dashboard.
 *
 * @package Smackcoders\UCI\Core
 */

namespace Smackcoders\UCI\Core;

use Smackcoders\UCI\Core\Dashboard\DashboardService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers dashboard AJAX endpoints and returns JSON payloads.
 */
class DashboardController {

	/** @var DashboardController|null */
	private static $instance = null;

	/** @var DashboardService */
	private $service;

	/**
	 * Returns the singleton controller instance.
	 *
	 * @return DashboardController
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Registers WordPress AJAX actions for dashboard widgets.
	 */
	private function __construct() {
		$this->service = new DashboardService();

		add_action( 'wp_ajax_LineChart', array( $this, 'fetch_line_chart_data' ) );
		add_action( 'wp_ajax_BarChart', array( $this, 'fetch_bar_chart_data' ) );
		add_action( 'wp_ajax_dashboard_kpi_stats', array( $this, 'fetch_kpi_stats' ) );
		add_action( 'wp_ajax_dashboard_import_volume', array( $this, 'fetch_import_volume' ) );
		add_action( 'wp_ajax_dashboard_content_distribution', array( $this, 'fetch_content_distribution' ) );
		add_action( 'wp_ajax_dashboard_recent_activity', array( $this, 'fetch_recent_activity' ) );
		add_action( 'wp_ajax_dashboard_quick_stats', array( $this, 'fetch_quick_stats' ) );
	}

	/**
	 * Verifies nonce and administrator access for dashboard requests.
	 *
	 * @return void
	 */
	private function verify_request() {
		SecurityHelper::verify_ajax_nonce();
		if ( ! SecurityHelper::check_admin_access() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-ultimate-csv-importer' ) );
		}
	}

	/**
	 * Sends a JSON response and terminates the request.
	 *
	 * @param array<string,mixed> $payload Response body.
	 * @return void
	 */
	private function send_json( array $payload ) {
		echo wp_json_encode( $payload );
		wp_die();
	}

	/**
	 * Legacy Importers Activity line chart endpoint.
	 *
	 * @return void
	 */
	public function fetch_line_chart_data() {
		$this->verify_request();
		$this->send_json( $this->service->get_line_chart_data() );
	}

	/**
	 * Legacy Import Statistics bar chart endpoint.
	 *
	 * @return void
	 */
	public function fetch_bar_chart_data() {
		$this->verify_request();
		$this->send_json( $this->service->get_bar_chart_data() );
	}

	/**
	 * Returns KPI card metrics for the dashboard header.
	 *
	 * @return void
	 */
	public function fetch_kpi_stats() {
		$this->verify_request();
		$this->send_json( $this->service->get_kpi_stats() );
	}

	/**
	 * Returns twelve-month import volume chart data.
	 *
	 * @return void
	 */
	public function fetch_import_volume() {
		$this->verify_request();
		$period = isset( $_POST['period'] ) ? sanitize_text_field( wp_unslash( $_POST['period'] ) ) : 'yearly';
		$this->send_json( $this->service->get_import_volume( $period ) );
	}

	/**
	 * Returns grouped content distribution statistics.
	 *
	 * @return void
	 */
	public function fetch_content_distribution() {
		$this->verify_request();
		$this->send_json( $this->service->get_content_distribution() );
	}

	/**
	 * Returns the ten most recent import activities.
	 *
	 * @return void
	 */
	public function fetch_recent_activity() {
		$this->verify_request();
		$limit = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 10;
		$this->send_json( $this->service->get_recent_activity( $limit ) );
	}

	/**
	 * Returns the quick-stats summary metrics.
	 *
	 * @return void
	 */
	public function fetch_quick_stats() {
		$this->verify_request();
		$this->send_json( $this->service->get_quick_stats() );
	}
}
