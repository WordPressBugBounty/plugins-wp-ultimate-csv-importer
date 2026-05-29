<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly
    
class ImportConfiguration {
    private static $import_config_instance = null;

    private function __construct(){
		add_action('wp_ajax_updatefields',array($this,'get_update_fields'));
    }
    
    public static function getInstance() {
            
        if (ImportConfiguration::$import_config_instance == null) {
            ImportConfiguration::$import_config_instance = new ImportConfiguration;
            return ImportConfiguration::$import_config_instance;
        }
        return ImportConfiguration::$import_config_instance;
    }

    public function get_update_fields(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		$import_type = sanitize_text_field($_POST['Types']);	
		$mode = sanitize_text_field($_POST['Mode']);
		$hash_key = sanitize_key($_POST['HashKey']);
        $response = [];
		$taxonomies = get_taxonomies(); 
		if($mode == 'Update') {
			$fields = array( 'post_title', 'ID', 'post_name' , );
			if($import_type == 'WooCommerce Orders'){
				$fields = array('ORDERID');
			}	
			if(is_plugin_active('jet-booking/jet-booking.php')){
				if($import_type == 'JetBooking'){
					$fields = array('booking_id');
				}
			}
			if(is_plugin_active('jet-reviews/jet-reviews.php')) {
				if($import_type == 'JetReviews'){
					$fields = array('ID');
				}
			}
			if($import_type == 'WooCommerce Coupons' || $import_type =='WPeCommerce Coupons'){
				$fields =  array('COUPONID');
			}
			if($import_type == 'WooCommerce Refunds'){
				$fields = array('REFUNDID');
			}
			if($import_type == 'WooCommerce Product Variations'){
				$fields = array('VARIATIONSKU', 'VARIATIONID');
			}
			if($import_type == 'WooCommerce Product'){
				array_push($fields,"PRODUCTSKU");
            }
			if($import_type == 'Customer Reviews'){
				$fields = array('review_id');
			}
			elseif (in_array($import_type, $taxonomies)){
				$fields = array('TERMID', 'slug');
			}
			elseif($import_type == 'Users' || $import_type == 'WooCommerce Customer'){
				$fields = array('user_email','ID');
			}
			elseif($import_type == 'Comments'){
				$fields = array('comment_ID');
			}
		}
		else {
			if (in_array($import_type, $taxonomies)){
				$fields = array('TERMID', 'slug');
			}
			elseif($import_type == 'WooCommerce Product Variations'){
				if(is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('import-woocommerce/import-woocommerce.php')){
					$fields = array('VARIATIONSKU');
				}
			}
			elseif($import_type == 'Users' || $import_type == 'WooCommerce Customer'){
				$fields = array('ID', 'user_email');
			}
			elseif($import_type == 'WooCommerce Orders'){
				$fields = array('ORDERID');
			}
			elseif($import_type == 'Comments'){
				$fields = array('comment_ID');
			}
			else{
				$fields = array( 'ID', 'post_title', 'post_name' );
		    }
			if ($import_type === 'WooCommerce Product') {
				if (!isset($fields)) {
					$fields = array('ID', 'post_title', 'post_name');
				}
				if (!in_array('PRODUCTSKU', $fields, true)) {
					$fields[] = 'PRODUCTSKU';
				}
			}
		}
		global $wpdb;
		$file_table_name = $wpdb->prefix ."smackcsv_file_events";
		$get_id = $wpdb->get_results( "SELECT id , mode ,file_name , total_rows FROM $file_table_name WHERE `hash_key` = '$hash_key'");
		$total_rows = isset($get_id[0]->total_rows) ? $get_id[0]->total_rows : '';

		$response['total_records'] = $total_rows;
        $response['update_fields'] = $fields;
		$mapping_extension = MappingExtension::getInstance();
		$response['CustomPostCheck'] = $mapping_extension->is_bulk_update_eligible_type($import_type);
        echo wp_json_encode($response);
        wp_die();
		
    }

    public function get_active_plugins() {
	$active_plugins = get_option('active_plugins');
	return $active_plugins;
    }
}