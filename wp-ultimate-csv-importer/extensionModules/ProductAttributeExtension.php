<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class ProductAttributeExtension extends ExtensionHandler{
	private static $instance = null;
	private static $attr_slot_cache = array();

    public static function getInstance() {	
		if (ProductAttributeExtension::$instance == null) {
			ProductAttributeExtension::$instance = new ProductAttributeExtension;
		}
		return ProductAttributeExtension::$instance;
    }

	/**
	 * Max attribute index from CSV headers (product_attribute_name1, etc.).
	 *
	 * @param array $headers CSV header row.
	 * @return int
	 */
	public static function count_from_csv_headers( array $headers ) {
		$max = 0;
		foreach ( $headers as $header ) {
			$header = trim( (string) $header );
			if ( preg_match( '/^product_attribute_(?:name|value|visible|variation|taxonomy|position|default)(\d+)$/i', $header, $m ) ) {
				$max = max( $max, (int) $m[1] );
			}
		}
		return $max;
	}

	public function processExtension($data,$process_type=null) {
        $response = [];
		$import_type = $data;
		$import_type = $this->import_type_as($import_type);
		$importas = $this->import_post_types($import_type);	
		get_object_taxonomies( $importas, 'names' );

		$csv_headers = apply_filters( 'sm_uci_free_product_attr_csv_headers', array() );
		$csv_headers = is_array( $csv_headers ) ? $csv_headers : array();
		$csv_attr_max  = self::count_from_csv_headers( $csv_headers );
		$cache_key     = md5( wp_json_encode( $csv_headers ) . '|' . (string) $process_type );

		if ( isset( self::$attr_slot_cache[ $cache_key ] ) ) {
			$count = self::$attr_slot_cache[ $cache_key ];
		} else {
			global $wpdb;

			$global_attr_count = 0;
			$attr_table        = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			$table_exists      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $attr_table ) );
			if ( $table_exists === $attr_table ) {
				$global_attr_count = (int) $wpdb->get_var(
					"SELECT COUNT(*) FROM {$attr_table}"
				);
			}

			$sampled_max = 0;
			$sample_rows = $wpdb->get_col(
				"SELECT pm.meta_value
				 FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				 WHERE pm.meta_key = '_product_attributes'
				   AND p.post_type IN ('product', 'product_variation')
				   AND p.post_status IN ('publish','draft','future','private','pending')
				   AND pm.meta_value != ''
				   AND pm.meta_value != 'a:0:{}'
				 ORDER BY p.ID DESC
				 LIMIT 50"
			);

			foreach ( $sample_rows as $raw ) {
				$attrs = maybe_unserialize( $raw );
				if ( is_array( $attrs ) ) {
					$n = count( $attrs );
					if ( $n > $sampled_max ) {
						$sampled_max = $n;
					}
				}
			}

			if ( $csv_attr_max > 0 ) {
				$count = max( $csv_attr_max, $sampled_max, 1 );
			} else {
				$count = max( $global_attr_count, $sampled_max, 3 );
			}

			$count = max( $count, 1 );
			$count = min( $count, 100 );
			self::$attr_slot_cache[ $cache_key ] = $count;
		}

	if($process_type == 'Export'){
		$pro_attr_fields =array();
		for($i=1; $i<=$count;$i++){
			$pro_attr_fields += array(
				'Product Attribute Name' . $i => 'product_attribute_name' . $i,
				'Product Attribute Value' . $i => 'product_attribute_value' . $i,
				'Product Attribute Visible' . $i => 'product_attribute_visible' . $i
			);
		}
		$pro_attr_fields_line = $this->convert_static_fields_to_array($pro_attr_fields);
		$response['product_attr_fields'] = $pro_attr_fields_line; 
	}
	else{
		$pro_attr_fields = array();
		for ( $i = 1; $i <= $count; $i++ ) {
			$pro_attr_fields[] = array(
				'label' => $i,
				'name'  => $this->convert_static_fields_to_array(
					array(
						"Product Attribute Name$i"   => "product_attribute_name$i",
						"Product Attribute Value$i"  => "product_attribute_value$i",
						"Product Attribute Visible$i" => "product_attribute_visible$i",
					)
				),
			);
		}
		$response['product_attr_fields'] = $pro_attr_fields;
	}
		return $response;	
	}
	
	
	public function extensionSupportedImportType($import_type){
		if(is_plugin_active('woocommerce/woocommerce.php')){
			$import_type = $this->import_name_as($import_type);
			if($import_type == 'WooCommerce' || $import_type == 'WooCommerceVariations' ) { 
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
}
