<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class Plugin{
    private static $instance = null;
        private static $string = 'com.smackcoders.smackcsv';

    public function getPluginSlug() {
        if ( is_plugin_active('wp-importer-custom-fields-basic-pro/wp-importer-custom-fields-basic-pro.php') ) {
            return 'wp-importer-custom-fields-basic-pro';
        }
        if ( is_plugin_active('wordpress-importer-wpml-pro/wordpress-importer-wpml-pro.php') ) {
            return 'wordpress-importer-wpml-pro';
        }
        if ( is_plugin_active('wp-ultimate-csv-importer-pro/wp-ultimate-csv-importer-pro.php') ) {
            return 'wp-ultimate-csv-importer-pro';
        }
        return self::$string;
    }


    public static function getInstance() {
        if (Plugin::$instance == null) {
            Plugin::$instance = new Plugin;
           
            return Plugin::$instance;
        }
        return Plugin::$instance;
    }

}