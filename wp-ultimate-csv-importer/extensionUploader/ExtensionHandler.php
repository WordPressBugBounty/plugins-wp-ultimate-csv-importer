<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class ExtensionHandler{
	private static $instance=null;
	private static $validate_file = null;
	public static function getInstance() {

		if (ExtensionHandler::$instance == null) {
			ExtensionHandler::$validate_file = ValidateFile::getInstance();
			return ExtensionHandler::$instance;
		}
		return ExtensionHandler::$instance;
	}

	public function import_post_types($import_type) {	
		$import_type = trim($import_type);	
		$module = array('Posts' => 'post', 'Pages' => 'page', 'Users' => 'user', 'JetReviews' => 'jetreviews', 'Comments' => 'comments', 'Taxonomies' => $import_type, 'CustomerReviews' =>'wpcr3_review', 'Categories' => 'categories', 'Tags' => 'tags', 'WooCommerce' => 'product', 'WooCommerce Product' => 'product', 'WPeCommerce' => 'wpsc-product','WPeCommerceCoupons' => 'wpsc-product', 'WooCommerceOrders' => 'product', 'WooCommerceCoupons' => 'product', 'WooCommerceRefunds' => 'product', 'CustomPosts' => $import_type,'WooCommerceReviews' => 'reviews');
		foreach (get_taxonomies() as $key => $taxonomy) {
			$module[$taxonomy] = $taxonomy;
		}
		if(array_key_exists($import_type, $module)) {
			return $module[$import_type];
		}
		else {
			return $import_type;
		}
    }
 public function convert_fields_to_array($get_value){
		if(is_array($get_value)){
			foreach($get_value as $values){
				foreach($values as $in_values){
					$fields_getting[]=$in_values;
				}	
			}
		}
		$fields_getting=isset($fields_getting)?$fields_getting:'';
		return $fields_getting;
	}

	public function convert_static_fields_to_array($static_value){
        if (is_array($static_value) || is_object($static_value)){
            foreach($static_value as $key=>$values){
                $static_fields_getting[] = array('label' => $key,
                                                'name' => $values			
                );
            }
		}
		$static_fields_getting=isset($static_fields_getting)?$static_fields_getting:'';
		return $static_fields_getting;
    }

	public function get_active_plugins() {
		$active_plugins = get_option('active_plugins');
		return $active_plugins;
	}

	public function get_import_custom_post_types(){
		$custompost = array();
		$custom_array = array('post', 'page', 'wpsc-product', 'product_variation', 'shop_order', 'shop_coupon', 'shop_order_refund', 'mp_product_variation', 'ngg_pictures');
		$other_posttypes = array('attachment','revision','wpsc-product-file','mp_order','shop_webhook','llms_quiz','llms_question');
		$all_post_types = get_post_types();
		foreach($other_posttypes as $ptkey => $ptvalue) {
			if (in_array($ptvalue, $all_post_types)) {
				unset($all_post_types[$ptvalue]);
			}
		}
		foreach($all_post_types as $key => $value){
			if(!in_array($value,$custom_array)){
				$custompost[$value] = $value;
			}
		}
		return $custompost;
	}

	public function get_import_post_types(){
		global $wpdb;
		$custom_array = array('post', 'page', 'wpsc-product', 'product_variation', 'shop_order', 'shop_coupon', 'shop_order_refund','mp_product_variation');
		$other_posttypes = array('attachment','revision','wpsc-product-file','mp_order','shop_webhook','custom_css','customize_changeset','oembed_cache','user_request','_pods_template','wpmem_product','wp-types-group','wp-types-user-group','wp-types-term-group','gal_display_source','display_type','displayed_gallery','wpsc_log','lightbox_library','scheduled-action','cfs','_pods_pod','_pods_field','acf-field','acf-field-group','wp_block','ngg_album','ngg_gallery','nf_sub','wpcf7_contact_form','iv_payment','llms_quiz','llms_question','llms_membership','llms_engagement','llms_order','llms_transaction','llms_achievement','llms_my_achievement','llms_my_certificate','llms_email','llms_voucher','llms_access_plan','llms_form','section','llms_certificate','product','product-group');
		$importas = array(
			'Posts' => 'Posts',
			'Pages' => 'Pages',			
			'Comments' => 'Comments'
			
		);
		$all_post_types = get_post_types();
		array_push($all_post_types, 'widgets');

		// To avoid toolset repeater group fields from post types in dropdown

		foreach($other_posttypes as $ptkey => $ptvalue) {
			if (in_array($ptvalue, $all_post_types)) {
				unset($all_post_types[$ptvalue]);
			}
		}
		foreach($all_post_types as $key => $value) {
			if(!in_array($value, $custom_array)) {
				if(is_plugin_active('events-manager/events-manager.php') && $value == 'event') {
					$importas['Events'] = $value;
				} else {
					$importas[$value] = $value;

				}
				$custompost[$value] = $value;
			}
		}

		if(is_plugin_active('import-users/import-users.php') ) {
			$importas['Users'] = 'Users';
		}

		if(is_plugin_active('wp-customer-reviews/wp-customer-reviews-3.php') ||  is_plugin_active('wp-customer-reviews/wp-customer-reviews.php')) {
			$importas['Customer Reviews'] = 'CustomerReviews';
			if(isset($importas['wpcr3_review'])) {
				unset($importas['wpcr3_review']);
			}
		}
		if(is_plugin_active('wp-e-commerce/wp-shopping-cart.php')){
			$importas['WPeCommerce Products'] ='WPeCommerce';
			$importas['WPeCommerce Coupons'] = 'WPeCommerceCoupons';
		}
      // Add JetReviews if the JetReviews plugin is active
      if(is_plugin_active('jet-reviews/jet-reviews.php')) {
	  $importas['JetReviews'] = 'jetreviews';
	  }

		if(is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('import-woocommerce/import-woocommerce.php')){
			$importas['WooCommerce Product'] ='WooCommerce';
			//$importas['WooCommerce Product Variations'] ='WooCommerceVariations';
			$importas['WooCommerce Reviews'] ='WooCommerceReviews';
			$importas['WooCommerce Orders'] = 'WooCommerceOrders';
			$importas['WooCommerce Coupons'] = 'WooCommerceCoupons';
			$importas['WooCommerce Customer'] = 'WooCommerceCustomer';
		}

		if(array_key_exists('location' , $importas) && array_key_exists('event-recurring' , $importas)){
			unset($importas['location']);
			unset($importas['event-recurring']);
		}
		if(is_plugin_active('jet-engine/jet-engine.php')){
			$get_slug_name = $wpdb->get_results("SELECT slug FROM {$wpdb->prefix}jet_post_types WHERE status = 'content-type'");
		
			if(!empty($get_slug_name)){
				foreach($get_slug_name as $key => $get_slug){
					$value = $get_slug->slug;
					$importas[$value] = $value;
				}
			}
		}
		if(is_plugin_active('jet-booking/jet-booking.php')){
			$importas['JetBooking'] ='JetBooking';
		}
		return $importas;	
		
	}

	public function import_name_as($import_type){
		$taxonomies = get_taxonomies();
		$customposts = $this->get_import_custom_post_types();
		
		$import_type_as = $this->get_import_post_types();
		if (in_array($import_type, $taxonomies)) {
			if($import_type == 'category' || $import_type == 'product_category' || $import_type == 'product_cat' || $import_type == 'wpsc_product_category' || $import_type == 'event-categories'):
				$import_type = 'Categories';
			elseif($import_type == 'product_tag' || $import_type == 'event-tags' || $import_type == 'post_tag'):
				$import_type = 'Tags';
			elseif($import_type == 'comments'):
				$import_type = 'Comments';
			else:
				$import_type = 'Taxonomies';
			endif;
		}

		else if(array_key_exists($import_type , $import_type_as ) && !in_array($import_type, $customposts)){ 
			$import_type = $import_type_as[$import_type];
		}
		else if (in_array($import_type, $customposts)) { // else if added becuase "event" module cannot refer while used if condition
			$import_type = 'CustomPosts';
		}
		
		return $import_type;
	}

	public function import_type_as($import_type){
		$import_type_as = $this->get_import_post_types();

		if(array_key_exists(trim($import_type) , $import_type_as )){	
			$import_type = $import_type_as[trim($import_type)];
		}

		return $import_type;
	}

	public function set_post_types($hashkey , $filename) {

		$smackcsv_instance = SmackCSV::getInstance();
		$upload_dir = $smackcsv_instance->create_upload_dir();
		$file_extension = pathinfo($filename, PATHINFO_EXTENSION);
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		if($file_extension == 'xlsx'  || $file_extension == 'xls'){
			$file_extension = 'csv';                    
		}
		if($file_extension == 'csv' || $file_extension == 'txt'){
			if (version_compare(PHP_VERSION, '8.1.0', '<')) {  // Only do this if PHP version is less than 8.1.0
				if (!ini_get("auto_detect_line_endings")) {
					ini_set("auto_detect_line_endings", true);
				}
			}
			$info = [];
			if (($h = fopen($upload_dir.$hashkey.'/'.$hashkey, "r")) !== FALSE) 
			{
				$line_number = 0;
				$Headers = [];
				$values = [];
				// Convert each line into the local $data variable	
				$delimiters = array( ',','\t',';','|',':','&nbsp');
				$file_path = $upload_dir . $hashkey . '/' . $hashkey;
				ExtensionHandler::$validate_file = ValidateFile::getInstance();
				$delimiter = ExtensionHandler::$validate_file->getFileDelimiter($file_path, 5);
				$array_index = array_search($delimiter,$delimiters);
				if($array_index == 5){
					$delimiters[$array_index] = ' ';
				}
				if($delimiter == '\t'){
					$delimiter ='~';
					 $temp=$file_path.'temp';
					 if (($handles = fopen($temp, 'r')) !== FALSE){
						while (($data = fgetcsv($handles, 0, $delimiter)) !== FALSE)
						{
							$trimmed_info = array_map('trim', $data);
				
							array_push($info , $trimmed_info);

							if($line_number == 0){
								$Headers = $info[$line_number];
								$type = $this->select_import_type($Headers);	
							}
							else{
								$values = $info[$line_number];
							}
							$line_number ++; 			  			
						}
					}

					fclose($handles);
				}
				else{
				while (($data = fgetcsv($h, 0, $delimiters[$array_index])) !== FALSE)  
				{		
					// Read the data from a single line

					$trimmed_info = array_map('trim', $data);
				
					array_push($info , $trimmed_info);

					if($line_number == 0){
						$Headers = $info[$line_number];
						$type = $this->select_import_type($Headers);	
					}
					else{
						$values = $info[$line_number];
					}
					$line_number ++;		
				}	
				// Close the file
				fclose($h);
			}
			}
			$total_rows = $line_number - 1;
		}
		if($file_extension == 'xml'){
			$upload_dir_path = $upload_dir. $hashkey;
			if (!is_dir($upload_dir_path)) {
				wp_mkdir_p( $upload_dir_path);
			}
			chmod($upload_dir_path, 0777);   
			$path = $upload_dir . $hashkey . '/' . $hashkey;   

			$xml = simplexml_load_file($path);
			$xml_arr = json_decode( json_encode($xml) , 1);

			foreach($xml->children() as $child){   
				$child_name = $child->getName();    
			}
			$xml_class = new XmlHandler();
			$parse_xml = $xml_class->parse_xmls($hashkey);
			$i = 0;
			foreach($parse_xml as $xml_key => $xml_value){
				if(is_array($xml_value)){
					foreach ($xml_value as $e_key => $e_value){
						$Headers[$i] = $e_value['name'];
						$i++;
					}
				}
			}
			$type = $this->select_import_type($Headers);
			$total_rows = $this->get_xml_count($path , $child_name);
			if($total_rows == 0 || $child_name == 'channel' ){
				$sub_child = $this->get_child($child,$path);
				$child_name = $sub_child['child_name'];
				$total_rows = $sub_child['total_count'];
			}
		}
		if($file_extension == 'tsv'){
			if (version_compare(PHP_VERSION, '8.1.0', '<')) {  // Only do this if PHP version is less than 8.1.0
				if (!ini_get("auto_detect_line_endings")) {
					ini_set("auto_detect_line_endings", true);
				}
			}
			$info = [];
			if (($h = fopen($upload_dir.$hashkey.'/'.$hashkey, "r")) !== FALSE) 
			{
				$line_number = 0;
				$Headers = [];
				$values = [];
				$file_path = $upload_dir . $hashkey . '/' . $hashkey;
				ExtensionHandler::$validate_file = ValidateFile::getInstance();
				$delimiter = ExtensionHandler::$validate_file->getFileDelimiter($file_path, 5);
				if($delimiter == '\t'){
						$hs = $upload_dir.$hashkey.'/'.$hashkey;
						while (($data = fgetcsv($h, 0, "\t")) !== FALSE) {
						
						// Read the data from a single line
					
						$data = explode("\t", $line[0]); // Split by tab
						$trimmed_info = array_map('trim', $data);
					
						array_push($info , $trimmed_info);

						if($line_number == 0){
							$Headers = $info[$line_number];
							$type = $this->select_import_type($Headers);	
						}
						else{
							$values = $info[$line_number];
						}
						$line_number ++;		
					}
					// Close the file
				fclose($h);
				}
			}
			$total_rows = $line_number - 1;
		}
		global $wpdb;
		$table_name = $wpdb->prefix ."smackcsv_file_events";
		$fields = $wpdb->get_results("UPDATE $table_name SET total_rows=$total_rows WHERE hash_key = '$hashkey'");
		return $type;

	}

	public function get_child($child,$path){
		foreach($child->children() as $sub_child){
			$sub_child_name = $sub_child->getName();
		}
		$total_xml_count = $this->get_xml_count($path , $sub_child_name);
		if($total_xml_count == 0 || $sub_child_name == 'channel' ){
			$this->get_child($sub_child,$path);
		}
		else{
			$result['child_name'] = $sub_child_name;
			$result['total_count'] = $total_xml_count;
			return $result;
		}
	}
	public function select_import_type($Headers){
		$type = 'Posts';
		if(!empty($Headers)){
			if(in_array('check_in_date', $Headers) && in_array('check_out_date', $Headers)){
				$type = 'Booking';
			}
			if(in_array('wp_page_template', $Headers) && in_array('menu_order', $Headers)){
				$type = 'Pages';
			}
			elseif(in_array('user_login', $Headers) || in_array('role', $Headers) || in_array('user_email', $Headers) ){
				if(is_plugin_active('import-users/import-users.php')) {
					$type = 'Users';
				}else{
					$type = 'WooCommerce Customer';
				}
			}
			elseif(in_array('comment_author', $Headers) || in_array('comment_content', $Headers) ||  in_array('comment_approved', $Headers) ){
				$type = 'Comments';
			} elseif( in_array('reviewer_name', $Headers) || in_array('reviewer_email', $Headers)){
				$type = 'Customer Reviews';
			} elseif( in_array('event_start_date', $Headers) || in_array('event_end_date', $Headers)){
				$type = 'Events';
			}
			elseif( in_array('ticket_start_date', $Headers) || in_array('ticket_end_date', $Headers) && !in_array('event_start_date' , $Headers)){
				$type = 'Tickets';
			}
			elseif( in_array('location_name', $Headers) || in_array('location_address', $Headers)){
				$type = 'Event Locations';
			} elseif( in_array('recurrence_freq', $Headers) || in_array('recurrence_interval', $Headers) || in_array('recuurence_days', $Headers)){
				$type = 'Recurring Events';
			} elseif( in_array('name', $Headers) && in_array('slug', $Headers)){
				$type = 'category';  
			} elseif(is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('import-woocommerce/import-woocommerce.php')){
				if(in_array('PARENTSKU', $Headers) || in_array('VARIATIONSKU', $Headers) || in_array('PRODUCTID', $Headers) || in_array('VARIATIONID', $Headers)){
					$type = 'WooCommerce Product Variations';
				} elseif(in_array('coupon_code', $Headers) || in_array('COUPONID', $Headers) || in_array('coupon_amount', $Headers)){
					$type = 'WooCommerce Coupons';
				} elseif(in_array('ORDERID', $Headers) || in_array('payment_method', $Headers)){
					$type = 'WooCommerce Orders';
				} elseif(in_array('REFUNDID', $Headers)){
					$type = 'WooCommerce Refunds';
				} elseif(in_array('sku', $Headers)){
					$type = 'WooCommerce Product';
				}
			} elseif(is_plugin_active('wp-e-commerce/wp-shopping-cart.php')){
				if(in_array('coupon_code', $Headers) || in_array('COUPONID', $Headers)){
					$type = 'WPeCommerce Coupons';
				} elseif(in_array('sku', $Headers)){
					$type = 'WPeCommerce Products';
				}
			}	
			if(is_plugin_active('lifterlms/lifterlms.php')){
				if(in_array('_llms_instructors',$Headers) || in_array('_llms_course_opens_message',$Headers)){
					$type = 'course';
				}
				elseif(in_array('_llms_parent_course',$Headers) || in_array('_llms_free_lesson',$Headers)){
					$type = 'lesson';
				}
				elseif(in_array('_llms_coupon_courses',$Headers) || in_array('_llms_coupon_amount',$Headers)){
					$type = 'llms_coupon';
				}
			}
		}
		return $type;
	}

	public function get_xml_count($eventFile , $tagname){
		$doc = new \DOMDocument();
		$doc->load($eventFile);
		$nodes=$doc->getElementsByTagName($tagname);
		$total_row_count = $nodes->length;
		return $total_row_count;	
	}


	public function processExtension($data){
		return '';
	}
	public function extensionSupportedImportType($import_type){
		return boolean ;
	}

}
