<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 *
 * Backward-compatible facade; AJAX handlers live in DashboardController.
 *
 * @package Smackcoders\UCI\Core
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy dashboard bootstrap retained for backward compatibility.
 */
class Dashboard {

	/** @var Dashboard|null */
	private static $dashboard_instance = null;

	/**
	 * Initializes the dashboard controller singleton.
	 */
	private function __construct() {
		DashboardController::getInstance();
	}

	/**
	 * Returns the dashboard singleton and registers AJAX hooks once.
	 *
	 * @return Dashboard
	 */
	public static function getInstance() {
		if ( null === self::$dashboard_instance ) {
			self::$dashboard_instance = new self();
		}
		return self::$dashboard_instance;
	}

	/**
	 * Converts PHP ini size strings (e.g. 128M) to bytes.
	 *
	 * @param string $val Size string from php.ini.
	 * @return int|false
	 */
	public function get_config_bytes( $val ) {
		$val = trim( $val );
		if ( '' === $val ) {
			return false;
		}
		$last = strtolower( $val[ strlen( $val ) - 1 ] );
		switch ( $last ) {
			case 'g':
				$val *= 1024;
				// no break — fall through.
			case 'm':
				$val *= 1024;
				// no break — fall through.
			case 'k':
				$val *= 1024;
		}
		return (int) $val;
	}
}
