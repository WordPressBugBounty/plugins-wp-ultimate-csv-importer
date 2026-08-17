<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class MappingExtension {
	private static $instance = null,$extension_handler ;
	private static $extension = [];
	private static $validatefile;

	private function __construct(){
		$plugin_pages = ['com.smackcoders.csvimporternew.menu'];
		global $plugin_ajax_hooks;

		$request_page = isset($_REQUEST['page']) ?sanitize_text_field($_REQUEST['page']) : '';
		$request_action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
		if (in_array($request_page, $plugin_pages) || in_array($request_action, $plugin_ajax_hooks)) {
			add_action('wp_ajax_mappingfields',array($this,'mapping_field_function'));
			add_action('wp_ajax_templateinfo',array($this,'get_template_info'));
			add_action('wp_ajax_search_template',array($this,'search_template'));
		}
	}

	public static function getInstance() {
		if (MappingExtension::$instance == null) {
			MappingExtension::$instance = new MappingExtension;
			MappingExtension::$validatefile = new ValidateFile;
	
			foreach(get_declared_classes() as $class){
				if(is_subclass_of($class, 'Smackcoders\UCI\Core\ExtensionHandler')){ 
					array_push(MappingExtension::$extension ,$class::getInstance() );
				}
			}
			return MappingExtension::$instance;
		}
		return MappingExtension::$instance;
	}

	/**
	 * Ajax Call 
	 * Provides all Widget Fields for Mapping Section
	 * @return array - mapping fields
	 */
	public function mapping_field_function(){	
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		$import_type = sanitize_text_field($_POST['Types']);
		if(isset($_POST['MediaType'])){
			$media_type = sanitize_key($_POST['MediaType']);
		}
		$hash_key = sanitize_key($_POST['HashKey']);
		$ai_status = ConnectorsHelper::get_status();
		$get_key  = $ai_status['configured']; // Backward compat for frontend (get_key / setting).
		$mode = sanitize_text_field($_POST['Mode']);
		global $wpdb;

		$response = [];
		$current_user = wp_get_current_user();
		$current_user_role = $current_user->roles[0];
		$response['currentuser']   = $current_user_role;
		$response['configured']   = $ai_status['configured'];
		$response['settings_url'] = $ai_status['settings_url'];
		$response['CustomPostCheck'] = $this->is_bulk_update_eligible_type($import_type);
		$details = [];
		$info = [];
		$filename = '';
		$table_name = $wpdb->prefix."smackcsv_file_events";
		$fields = $wpdb->query( "UPDATE $table_name SET mode ='$mode' WHERE hash_key = '$hash_key'");

		$get_result = $wpdb->get_results("SELECT file_name, total_rows FROM $table_name WHERE hash_key = '$hash_key' ");
		$filename = $get_result[0]->file_name;
		$total_rows = $get_result[0]->total_rows;
		$file_extension = pathinfo($filename, PATHINFO_EXTENSION);
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		if($file_extension == 'xlsx'  || $file_extension == 'xls'){
			$file_extension = 'csv';                    
		}
		$template_table_name = $wpdb->prefix."ultimate_csv_importer_mappingtemplate";
		$get_template_result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, templatename, createdtime, module, csvname, mapping FROM $template_table_name WHERE templatename != '' AND (module = %s OR ((module IS NULL OR module = '') AND csvname = %s)) ORDER BY id DESC",
				$import_type,
				$filename
			)
		);
		if(!empty($get_template_result)) {
			foreach($get_template_result as $value){
				$details = [];
				$template_name = $value->templatename;
				$mapping = isset($value->mapping) ? $value->mapping : '';
				$mapped_elements = array();
				$mapping_fields = maybe_unserialize($mapping);
				if (!is_array($mapping_fields)) {
					$mapping_fields = [];
				}
                foreach ($mapping_fields as $key => $mapping_value) {
					if (!is_array($mapping_value)) {
						continue;
					}

					if($key == "ATTRMETA"){
						$mapped_elements[$key] =array();
					}
					foreach($mapping_value as $map_key=>$map_value){
						if (is_int($map_key)) {
							if($key == "ATTRMETA"){
								if (is_int($map_key)) {
									$mapped_elements[$key] = array_merge($mapped_elements[$key], $map_value);
								}
								else{
									$mapped_elements[$key][$map_key]=$map_value;
								}

							}
							else{
								unset($mapping_value[$map_key]);
							}
							
						}else{
							$mapped_elements[$key][$map_key]=$map_value;
						}
                        
                	}    
           		}
				$matched_count = $this->get_matched_count($mapped_elements, $template_name);	
				$created_time = $value->createdtime;
				$module = $this->resolve_template_module($value, $import_type);
				$details['template_name'] = $template_name;
				$details['created_time'] = $created_time;
				$details['module'] = $module;
				$details['module_label'] = $this->get_module_label($module);
				$details['count'] = $matched_count;
				array_push($info , $details);
			}
				
			$response['success'] = true;
			$response['show_template'] = true;
			$response['info'] = $info;
			$response['currentuser']=$current_user_role;
			$response['total_records'] = (int)$total_rows;
			echo wp_json_encode($response);
			wp_die();
		}
		$smackcsv_instance = UCICore::getInstance();
		$upload_dir = $smackcsv_instance->create_upload_dir();
		if($file_extension == 'csv' || $file_extension == 'txt'){
		
			if (version_compare(PHP_VERSION, '8.1.0', '<')) {  // Only do this if PHP version is less than 8.1.0
				if (!ini_get("auto_detect_line_endings")) {
					ini_set("auto_detect_line_endings", true);
				}
			}
			$info = [];
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				// Convert each line into the local $data variable

				$delimiters = array( ',','\t',';','|',':','&nbsp');
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = MappingExtension::$validatefile->getFileDelimiter($file_path, 5);
				$array_index = array_search($delimiter,$delimiters);
				if($array_index == 5){
					
					$delimiters[$array_index] = ' ';
				}
				if($delimiter == '\t'){
				
					$delimiter ='~';
					 $temp=$file_path.'temp';
					 if (($handles = fopen($temp, 'r')) !== FALSE){
						while (($data = fgetcsv($handles, 0, $delimiter, '"', '\\')) !== FALSE)
						{
							$trimmed_array = ImportHelpers::getInstance()->normalize_header_array( $data );
							array_push($info , $trimmed_array);	
							$exp_line = $info[0];
							$response['success'] = true;
							$response['show_template'] = false;
							$response['csv_fields'] = $exp_line;
							$response['currentuser']=$current_user_role;
							if(!empty($media_type) && $import_type == 'Media'){
								$value = $this->media_mapping_fields($import_type,$mode,$media_type);
							}else{
								$value = $this->mapping_fields_with_csv_headers( $import_type, $exp_line, null );
							}
							$response['fields'] = $value;					
							echo wp_json_encode($response);
							wp_die();  			  			
						}
					}

					fclose($handles);
				}
				else{
					
					while (($data = fgetcsv($h, 0, $delimiters[$array_index], '"', '\\')) !== FALSE) 
					{	
						
						// Read the data from a single line
						$trimmed_info = ImportHelpers::getInstance()->normalize_header_array( $data );
						array_push($info , $trimmed_info);
						$exp_line = $info[0];

						$response['success'] = true;
						$response['get_key'] = $get_key;
						$response['show_template'] = false;
						$response['csv_fields'] = $exp_line;
						if(!empty($media_type) && $import_type == 'Media'){
							$value = $this->media_mapping_fields($import_type,$mode,$media_type);
						}else{
							$value = $this->mapping_fields_with_csv_headers( $import_type, $exp_line, null );
						}
						$response['fields'] = $value;
						$response['total_records'] = (int)$total_rows;
						echo wp_json_encode($response);
						wp_die();  			
					}	
					// Close the file
					fclose($h);
				}
			}
		}
		if($file_extension == 'tsv'){
			if (version_compare(PHP_VERSION, '8.1.0', '<')) {  // Only do this if PHP version is less than 8.1.0
				if (!ini_get("auto_detect_line_endings")) {
					ini_set("auto_detect_line_endings", true);
				}
			}
			$info = [];
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = MappingExtension::$validatefile->getFileDelimiter($file_path, 5);
				if($delimiter == '\t'){
					$hs = $upload_dir . $hash_key . '/' . $hash_key;
					$line =file($hs, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
					// Read the data from a single line
					$data = explode("\t", $line[0]); // Split by tab
					$trimmed_info = ImportHelpers::getInstance()->normalize_header_array( $data );
						array_push($info , $trimmed_info);
						$exp_line = $info[0];

						$response['success'] = true;
						$response['get_key'] = $get_key;
						$response['show_template'] = false;
						$response['csv_fields'] = $exp_line;
						if(!empty($media_type) && $import_type == 'Media'){
							$value = $this->media_mapping_fields($import_type,$mode,$media_type);
						}else{
							$value = $this->mapping_fields_with_csv_headers( $import_type, $exp_line, null );
						}
						
						$response['fields'] = $value;
						$response['total_records'] = (int)$total_rows;
						echo wp_json_encode($response);
						wp_die();
				}	
			}
		}
		if($file_extension == 'xml'){
			$xml_class = new XmlHandler();
			$upload_dir_path = $upload_dir. $hash_key;
			if (!is_dir($upload_dir_path)) {
				wp_mkdir_p( $upload_dir_path);
			}
			chmod($upload_dir_path, 0777);   
			$path = $upload_dir . $hash_key . '/' . $hash_key;   

			$xml = simplexml_load_file($path);
			$xml_arr = json_decode( json_encode($xml) , 1);

			foreach($xml->children() as $child){   
				$child_name = $child->getName();    
			}
			$parse_xml = $xml_class->parse_xmls($hash_key);
			$i = 0;
			$headers=[];
			foreach($parse_xml as $xml_key => $xml_value){
				if(is_array($xml_value)){
					foreach ($xml_value as $e_key => $e_value){
						$headers[$i] = $e_value['name'];
						$i++;
					}
				}
			}
			$response['success'] = true;
			$response['show_template'] = false;
			$response['csv_fields'] = $headers;
			if(!empty($media_type) && $import_type == 'Media'){
				$value = $this->media_mapping_fields($import_type,$mode,$media_type);
			}else{
				$value = $this->mapping_fields_with_csv_headers( $import_type, $headers, null );
			}
			$response['fields'] = $value;
			$response['total_records'] = (int)$total_rows;
			echo wp_json_encode($response);
			wp_die();  			
		}
	}

	/**
	 * Provides active plugins
	 * @return array - active plugins
	 */
	public function get_active_plugins() {
		$active_plugins = get_option('active_plugins');
		return $active_plugins;
	}

	/**
	 * Provides all Widget Fields for Export Section
	 * @return array - mapping fields
	 */
	public function get_fields($module){ 
		$import_type = $module;
		$response = [];
		$value = $this->mapping_fields($import_type,'Export');
		$response['fields'] = $value;
		return $response;
	}

	public function mapping_fields($import_type,$process_type = null){
		return $this->mapping_fields_with_csv_headers( $import_type, array(), $process_type );
	}

	/**
	 * Build mapping fields with optional CSV header context (attribute slots, etc.).
	 *
	 * @param string $import_type   Import module.
	 * @param array  $csv_headers   CSV header row.
	 * @param string $process_type  Insert/Update/Export.
	 * @return array
	 */
	public function mapping_fields_with_csv_headers( $import_type, $csv_headers = array(), $process_type = null ) {
		$csv_headers = is_array( $csv_headers ) ? ImportHelpers::getInstance()->normalize_header_array( $csv_headers ) : array();
		add_filter( 'sm_uci_free_product_attr_csv_headers', static function () use ( $csv_headers ) {
			return $csv_headers;
		} );

		$support_instance = [];
		$value = [];
		for($i = 0 ; $i < count(MappingExtension::$extension) ; $i++){
			$extension_instance = MappingExtension::$extension[$i];
			if($extension_instance->extensionSupportedImportType($import_type)){
				array_push($support_instance , $extension_instance);		
			}	
		}		
		
		for($i = 0 ;$i < count($support_instance) ; $i++){	
			$supporting_instance = $support_instance[$i];
			$fields = $supporting_instance->processExtension($import_type,$process_type);
			array_push($value , $fields);			
		}

		remove_all_filters( 'sm_uci_free_product_attr_csv_headers' );
		return $value;
	}

	public function media_mapping_fields($import_type,$mode =null,$media_type=null){
		MappingExtension::$extension_handler = new ExtensionHandler();
		$support_instance = [];
		if($import_type == 'Media') {
			if($media_type == 'local'){
				$wordpressfields = array(
					'File Name' => 'file_name',
					'Caption' => 'caption',
					'Alt text' => 'alt_text',
					'Desctiption' => 'description',
					'Title' => 'title',
					'Media ID' => 'media_id',
				);
				if(trim($mode) == 'Insert'){
					unset($wordpressfields['Media ID']);
				}
			}else{
				$wordpressfields = array(
					'Post ID' => 'post_id',
					'Media ID' => 'media_id',
					'Actual URL' => 'actual_url',
					'File Name' => 'file_name',
					'Title' => 'title',
					'Caption' => 'caption',
					'Alt text' => 'alt_text',
					'Desctiption' => 'description'		
				);
				if(trim($mode) == 'Insert'){
					unset($wordpressfields['Post ID']);
					unset($wordpressfields['Media ID']);
				}
			}
			
		}
		$wordpress_value = MappingExtension::$extension_handler->convert_static_fields_to_array($wordpressfields);
		$response[]['core_fields'] = $wordpress_value ;
		return $response;
	}

	/**
	 * Whether import type supports bulk update / duplicate handling (Posts, Pages, CPT).
	 *
	 * @param string $import_type Selected type from importer UI.
	 * @return bool
	 */
	public function is_bulk_update_eligible_type($import_type)
	{
		if ($import_type === 'WooCommerce Product' && $this->is_woocommerce_bulk_update_addon_active()) {
			return true;
		}
		if ($import_type === 'Users' && $this->is_users_bulk_update_addon_active()) {
			return true;
		}
		if ($import_type === 'WooCommerce Customer' && $this->is_woocommerce_bulk_update_addon_active()) {
			return true;
		}
		if ($import_type === 'WooCommerce Orders' && $this->is_woocommerce_bulk_update_addon_active()) {
			return true;
		}
		if ($import_type === 'Comments') {
			return true;
		}
		if ($this->is_taxonomy_bulk_update_eligible($import_type)) {
			return true;
		}
		$handler = new ExtensionHandler();
		$resolved = $handler->import_name_as($import_type);
		return in_array($resolved, array('Posts', 'Pages', 'CustomPosts'), true);
	}

	/**
	 * WooCommerce product bulk update requires WooCommerce + import-woocommerce addons.
	 *
	 * @return bool
	 */
	private function is_woocommerce_bulk_update_addon_active()
	{
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active('woocommerce/woocommerce.php')
			&& is_plugin_active('import-woocommerce/import-woocommerce.php');
	}

	/**
	 * @return bool
	 */
	private function is_users_bulk_update_addon_active()
	{
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active('import-users/import-users.php');
	}

	/**
	 * @param string $import_type
	 * @return bool
	 */
	private function is_taxonomy_bulk_update_eligible($import_type)
	{
		$slugs = array('category', 'post_tag', 'product_cat', 'product_brand', 'product_tag');
		return in_array($import_type, array('Categories', 'Tags', 'Taxonomies'), true)
			|| in_array($import_type, $slugs, true);
	}

	public function get_template_info(){
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $wpdb;

		$template_name = isset($_POST['TemplateName']) ? sanitize_text_field($_POST['TemplateName']) : '';
		$import_type = sanitize_text_field($_POST['Types']);
		$hash_key = sanitize_key($_POST['HashKey']);
		$requested_hash_key = $hash_key;
		$ai_status = ConnectorsHelper::get_status();
		$response = [];
		$file_name = '';
		$total_rows = 0;
		$template_event_key = '';
		$get_mapping = '';
		$get_mapping_filter = '';
		$mapping_type = '';
		$file_type = '';
		$result = [];
		$mapping_filter = [];
		$mode = isset($_POST['Mode']) ? sanitize_text_field($_POST['Mode']) : '';
		$media_type = '';
        if (isset($_POST['MediaType'])) {
            $media_type = sanitize_key($_POST['MediaType']);
        }
		$template_table_name = $wpdb->prefix . "ultimate_csv_importer_mappingtemplate";
		$table_name = $wpdb->prefix."smackcsv_file_events";
		$response['success'] = true;
		$response['get_key'] = $ai_status['configured'] ? 'configured' : '';
		$response['ai_status'] = $ai_status;
		if (!empty($hash_key)) {
			$total_rows = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT total_rows FROM $table_name WHERE hash_key = %s",
					$hash_key
				)
			);
		}
		$administratorRole = wp_get_current_user();
        $roles = $administratorRole->roles;
		$current_user_role = '';
		if (in_array('administrator', $roles)) {
			$current_user_role = 'administrator';
		}
		if(!empty($template_name)){
			$get_detail = $this->get_template_detail($template_table_name, $template_name, $import_type);
			if(!empty($get_detail)){
				$get_mapping = isset($get_detail[0]->mapping) ? $get_detail[0]->mapping : null;
				$get_mapping_filter = isset($get_detail[0]->mapping_filter) ? $get_detail[0]->mapping_filter : null;
				$mapping_type = $this->normalize_template_mapping_type(isset($get_detail[0]->mapping_type) ? $get_detail[0]->mapping_type : '');
				$file_name = isset($get_detail[0]->csvname) ? $get_detail[0]->csvname : null;
				$template_event_key = isset($get_detail[0]->eventKey) ? sanitize_key($get_detail[0]->eventKey) : '';
				$file_type = $file_name ? pathinfo($file_name, PATHINFO_EXTENSION) : null;
}

			if(empty($template_event_key) && !empty($template_name) && !empty($requested_hash_key)){
				$wpdb->update($template_table_name,array('eventKey' => $requested_hash_key),array('templatename' => trim($template_name)));
				$template_event_key = $requested_hash_key;
			}
			if(empty($hash_key)){
				if(!empty($file_name)){
					$hash_key = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT hash_key FROM $table_name WHERE file_name = %s ORDER BY id DESC LIMIT 1",
							$file_name
						)
					);
				}
				if(empty($hash_key)){
					$hash_key = $template_event_key;
				}
				if (!empty($hash_key)) {
					$total_rows = (int) $wpdb->get_var(
						$wpdb->prepare(
							"SELECT total_rows FROM $table_name WHERE hash_key = %s",
							$hash_key
						)
					);
				}
			}
			$result = maybe_unserialize($get_mapping);
			$result = is_array($result) ? $result : [];
			$mapping_filter = maybe_unserialize($get_mapping_filter);
			foreach($result as $result_key => $result_val){
				if($result_key == 'ATTRMETA' && is_array($result_val)){
					foreach($result_val as $res => $rest){
						if(is_int($res)){
							$result[$result_key] =array();
						}
					}
					foreach($result_val as $res_key=>$res_value){
						if (is_int($res_key) && $result_key =="ATTRMETA") {
							//if($key == "ATTRMETA"){
								$result[$result_key] = array_merge($result[$result_key], $res_value);

							//}
						}
					}
					
				}
			}

			$response['already_mapped'] = $result;
			$response['currentuser']=$current_user_role;
			$response['mapping_type'] = $mapping_type;
			$response['mapping_filter'] = $mapping_filter;
			$response['file_type'] = $file_type;
			$response['hash_key'] = $hash_key;
		}
		if(empty($hash_key)){
			if($import_type == 'Media'){
				$hash_key = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT eventKey FROM $template_table_name WHERE templatename = %s AND module = %s",
						$template_name,
						'Media'
					)
				);
			}else{
				$hash_key = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT eventKey FROM $template_table_name WHERE templatename = %s",
						$template_name
					)
				);
			}
		}
		$get_result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT file_name, total_rows FROM $table_name WHERE hash_key = %s",
				$hash_key
			)
		);
		$filename = isset($get_result[0]->file_name) ? $get_result[0]->file_name : $file_name;
		if (isset($get_result[0]->total_rows)) {
			$total_rows = (int) $get_result[0]->total_rows;
		}
		
		if(empty($filename)){
			$filename = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT csvname FROM $template_table_name WHERE eventKey = %s",
					$hash_key
				)
			);
		}
		$file_extension = pathinfo($filename, PATHINFO_EXTENSION);
		if($file_extension == 'xlsx' ||  $file_extension == 'xls' || $file_extension == 'json'){
			$file_extension = 'csv';
		}
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		$response['file_type'] = $file_extension;
		
		$smackcsv_instance = UCICore::getInstance();
		$upload_dir = $smackcsv_instance->create_upload_dir();

		if($file_extension == 'csv' || $file_extension == 'txt'){
			if (version_compare(PHP_VERSION, '8.1.0', '<')) {  // Only do this if PHP version is less than 8.1.0
				if (!ini_get("auto_detect_line_endings")) {  // If auto_detect_line_endings is not enabled
					ini_set("auto_detect_line_endings", true);  // Enable it to handle different line endings
				}
			}
			$info = [];
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
			// Convert each line into the local $data variable
			$delimiters = array( ',','\t',';','|',':','&nbsp');
			$file_path = $upload_dir . $hash_key . '/' . $hash_key;
			$delimiter = MappingExtension::$validatefile->getFileDelimiter($file_path, 5);
			$array_index = array_search($delimiter,$delimiters);
			if($array_index == 5){
				$delimiters[$array_index] = ' ';
			}
			if($delimiter == '\t'){
				$delimiter ='~';
				 $temp=$file_path.'temp';
				 if (($handles = fopen($temp, 'r')) !== FALSE){
					while (($data = fgetcsv($handles, 0, $delimiter, '"', '\\')) !== FALSE)
					{
						// Read the data from a single line
						$trimmed_array = ImportHelpers::getInstance()->normalize_header_array( $data );
						array_push($info , $trimmed_array);
						$exp_line = $info[0];									
						
						$response['csv_fields'] = $exp_line;					
						//$value = $this->mapping_fields($import_type);
						
						if(!empty($media_type) && $import_type == 'Media'){
							$value = $this->media_mapping_fields($import_type,$mode,$media_type);
						}else{
							$value = $this->mapping_fields_with_csv_headers( $import_type, $exp_line, null );
						}


						$response['fields'] = $value;	
						$response['total_records'] = (int)$total_rows;				
						echo wp_json_encode($response);
						wp_die();  	
					}
				}
			}
			else{
				while (($data = fgetcsv($h, 0, $delimiters[$array_index], '"', '\\')) !== FALSE) 
				{		
					// Read the data from a single line
					$trimmed_array = ImportHelpers::getInstance()->normalize_header_array( $data );
					array_push($info , $trimmed_array);
					$exp_line = $info[0];									
					
					$response['csv_fields'] = $exp_line;					
					if(!empty($media_type) && $import_type == 'Media'){
						$value = $this->media_mapping_fields($import_type,$mode,$media_type);
					}else{
						$value = $this->mapping_fields_with_csv_headers( $import_type, $exp_line, null );
					}

					$response['fields'] = $value;	
					$response['total_records'] = (int)$total_rows;				
					echo wp_json_encode($response);
					wp_die();  			
				}	
				// Close the file
				fclose($h);
			}
			}
			
		}
		if($file_extension == 'tsv'){
			if (version_compare(PHP_VERSION, '8.1.0', '<')) {  // Only do this if PHP version is less than 8.1.0
				if (!ini_get("auto_detect_line_endings")) {  // If auto_detect_line_endings is not enabled
					ini_set("auto_detect_line_endings", true);  // Enable it to handle different line endings
				}
			}
			$info = [];
			if (($h = fopen($upload_dir.$hash_key.'/'.$hash_key, "r")) !== FALSE) 
			{
				$file_path = $upload_dir . $hash_key . '/' . $hash_key;
				$delimiter = MappingExtension::$validatefile->getFileDelimiter($file_path, 5);
				if($delimiter == '\t'){
					$hs = $upload_dir . $hash_key . '/' . $hash_key;
					$line =file($hs, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
					$data = explode("\t", $line[0]); 
					$trimmed_array = ImportHelpers::getInstance()->normalize_header_array( $data );
					array_push($info , $trimmed_array);
					$exp_line = $info[0];		
					$response['csv_fields'] = $exp_line;					
					if(!empty($media_type) && $import_type == 'Media'){
						$value = $this->media_mapping_fields($import_type,$mode,$media_type);
					}else{
						$value = $this->mapping_fields_with_csv_headers( $import_type, $exp_line, null );
					}

					$response['fields'] = $value;	
					$response['total_records'] = (int)$total_rows;				
					echo wp_json_encode($response);
					wp_die();
						
				}
			}
		}

		if($file_extension == 'xml'){
			$xml_class = new XmlHandler();
			
			$upload_dir_path = $upload_dir. $hash_key;
			if (!is_dir($upload_dir_path)) {
				wp_mkdir_p( $upload_dir_path);
			}
			chmod($upload_dir_path, 0777);   
			$path = $upload_dir . $hash_key . '/' . $hash_key; 
			$xml = simplexml_load_file($path);
			$xml_arr = json_decode( json_encode($xml) , 1);	
			foreach($xml->children() as $child){   
				$child_name = $child->getName();    
			}
			$parse_xml = $xml_class->parse_xmls($hash_key);
			$i = 0;
			$headers = [];
			foreach($parse_xml as $xml_key => $xml_value){
				if(is_array($xml_value)){
					foreach ($xml_value as $e_key => $e_value){
						$headers[$i] = $e_value['name'];
						$i++;
					}
				}
			}
			$response['show_template'] = false;
			$response['csv_fields'] = $headers;
			if(!empty($media_type) && $import_type == 'Media'){
				$value = $this->media_mapping_fields($import_type,$mode,$media_type);
			}else{
				$value = $this->mapping_fields_with_csv_headers( $import_type, $headers, null );
			}
			$response['fields'] = $value;
			$response['total_records'] = (int)$total_rows;
			echo wp_json_encode($response);
			
			wp_die();  			
		}

		$response['show_template'] = false;
		$response['csv_fields'] = $this->extract_csv_fields_from_mapping($result);
		if(!empty($media_type) && $import_type == 'Media'){
			$value = $this->media_mapping_fields($import_type,$mode,$media_type);
		}else{
			$value = $this->mapping_fields_with_csv_headers( $import_type, $response['csv_fields'], null );
		}
		$response['fields'] = $value;
		$response['total_records'] = (int)$total_rows;
		echo wp_json_encode($response);
		wp_die();
	}

	private function get_template_detail($template_table_name, $template_name, $import_type){
		global $wpdb;

		if($import_type == 'Media'){
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT mapping, mapping_filter, csvname, mapping_type, eventKey, module FROM $template_table_name WHERE templatename = %s AND module = %s ORDER BY id DESC LIMIT 1",
					$template_name,
					'Media'
				)
			);
		}

		$get_detail = [];
		if(!empty($import_type)){
			$get_detail = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT mapping, mapping_filter, csvname, mapping_type, eventKey, module FROM $template_table_name WHERE templatename = %s AND module = %s ORDER BY id DESC LIMIT 1",
					$template_name,
					$import_type
				)
			);
		}

		if(empty($get_detail)){
			$get_detail = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT mapping, mapping_filter, csvname, mapping_type, eventKey, module FROM $template_table_name WHERE templatename = %s ORDER BY id DESC LIMIT 1",
					$template_name
				)
			);
		}

		return $get_detail;
	}

	private function normalize_template_mapping_type($mapping_type){
		$mapping_type = trim((string) $mapping_type);
		if($mapping_type === 'normal' || $mapping_type === '' || $mapping_type === 'mapping_section'){
			return 'mapping-section';
		}
		if($mapping_type === 'advanced' || $mapping_type === 'dragdrop_section' || $mapping_type === 'drag_and_drop_section'){
			return 'dragdrop-section';
		}
		return $mapping_type;
	}

	private function extract_csv_fields_from_mapping($mapping){
		$csv_fields = [];
		$this->collect_csv_fields_from_mapping($mapping, $csv_fields);
		return array_values(array_unique($csv_fields));
	}

	private function collect_csv_fields_from_mapping($mapping, &$csv_fields){
		if(!is_array($mapping)){
			return;
		}

		foreach($mapping as $key => $value){
			if(is_array($value)){
				$this->collect_csv_fields_from_mapping($value, $csv_fields);
				continue;
			}

			if(!is_scalar($value)){
				continue;
			}

			$value = trim((string) $value);
			if($value === '' || $value === 'Header Manipulation'){
				continue;
			}

			if(strpos((string) $key, '->') !== false){
				continue;
			}

			$csv_fields[] = $value;
		}
	}

	private function resolve_template_module($template, $fallback_module = ''){
		$module = isset($template->module) ? trim((string) $template->module) : '';
		if (!empty($module)) {
			return sanitize_text_field($module);
		}

		$fallback_module = trim((string) $fallback_module);
		if (!empty($fallback_module)) {
			return sanitize_text_field($fallback_module);
		}

		return $this->infer_module_from_mapping(isset($template->mapping) ? $template->mapping : '');
	}

	private function infer_module_from_mapping($mapping){
		$mapping_data = maybe_unserialize($mapping);
		if (!is_array($mapping_data)) {
			return '';
		}

		$registered_modules = array_merge(get_post_types([], 'names'), get_taxonomies([], 'names'));
		$mapped_values = $this->flatten_mapping_values($mapping_data);

		foreach ($mapped_values as $mapped_value) {
			$mapped_value = is_scalar($mapped_value) ? trim((string) $mapped_value) : '';
			if (!empty($mapped_value) && in_array($mapped_value, $registered_modules, true)) {
				return sanitize_text_field($mapped_value);
			}
		}

		return '';
	}

	private function flatten_mapping_values($mapping_data){
		$values = [];
		foreach ($mapping_data as $value) {
			if (is_array($value)) {
				$values = array_merge($values, $this->flatten_mapping_values($value));
			} else {
				$values[] = $value;
			}
		}

		return $values;
	}

	private function get_module_label($module){
		$module = trim((string) $module);
		if (empty($module)) {
			return __( 'Uncategorized', 'wp-ultimate-csv-importer' );
		}

		if (class_exists(__NAMESPACE__ . '\ExtensionHandler')) {
			$extension_handler = ExtensionHandler::getInstance();
			$import_modules = $extension_handler->get_import_post_types();
			foreach ($import_modules as $label => $value) {
				if ($module === $value || $module === $label) {
					return sanitize_text_field($label);
				}
			}
		}

		$post_type = get_post_type_object($module);
		if (!empty($post_type->labels->name)) {
			return sanitize_text_field($post_type->labels->name);
		}

		$taxonomy = get_taxonomy($module);
		if (!empty($taxonomy->labels->name)) {
			return sanitize_text_field($taxonomy->labels->name);
		}

		return ucwords(str_replace(['_', '-'], ' ', sanitize_text_field($module)));
	}

	/**
	* Provides mapped fields count from template
	* @param array $mappingList
	* @return int - count
	*/
	public function get_matched_count($mappingList, $templateName = null){
		$count = 0;

		//added
		$plugins_array = array(
			'ACF' => 'advanced-custom-fields/acf.php',
			'GF' => 'advanced-custom-fields-pro/acf.php',
			'RF' => 'advanced-custom-fields-pro/acf.php',
			'FC' => 'advanced-custom-fields-pro/acf.php',
			'ACFIMAGEMETA' => 'advanced-custom-fields-pro/acf.php',
			'TYPES' => 'types/wpcf.php',
			'TYPESIMAGEMETA' => 'types/wpcf.php',
			'PODS' => 'pods/init.php',
			'PODSIMAGEMETA' => 'pods/init.php',
			'CFS' => 'custom-field-suite/cfs.php',
			'AIOSEO' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'YOASTSEO' => 'wordpress-seo/wp-seo.php',
			'RANKMATH' => 'seo-by-rank-math/rank-math.php',
			'WPMEMBERS' => 'wp-members/wp-members.php',
			'ECOMMETA' => 'woocommerce/woocommerce.php',
			'BUNDLEMETA' => 'woocommerce-product-bundles/woocommerce-product-bundles.php',
			'LISTINGMETA' => 'woocommerce-product-bundles/woocommerce-product-bundles.php',
			'PRODUCTIMAGEMETA' => 'woocommerce/woocommerce.php',
			'ORDERMETA' => 'woocommerce/woocommerce.php',
			'COUPONMETA' => 'woocommerce/woocommerce.php',
			'REFUNDMETA' => 'woocommerce/woocommerce.php',
			'WPECOMMETA' => 'wp-e-commerce-custom-fields/custom-fields.php',
			'EVENTS' => 'events-manager/events-manager.php',
			'NEXTGEN' => 'nextgen-gallery/nggallery.php',
			'WPML' => 'wpml-multilingual-cms/sitepress.php',
			'CMB2' => 'cmb2/init.php',
			'JE' => 'jet-engine/jet-engine.php',
			'JERF' => 'jet-engine/jet-engine.php',
			'JECPT' => 'jet-engine/jet-engine.php',
			'JECPTRF' => 'jet-engine/jet-engine.php',
			'JECCT' => 'jet-engine/jet-engine.php',
			'JECCTRF' => 'jet-engine/jet-engine.php',
			'JETAX' => 'jet-engine/jet-engine.php',
			'JETAXRF' => 'jet-engine/jet-engine.php',
			'JEREL' => 'jet-engine/jet-engine.php',
			'LPCOURSE' => 'learnpress/learnpress.php',
			'LPCURRICULUM' => 'learnpress/learnpress.php',
			'LPLESSON' => 'learnpress/learnpress.php',
			'LPQUIZ' => 'learnpress/learnpress.php',
			'LPQUESTION' => 'learnpress/learnpress.php',
			'LPORDER' => 'learnpress/learnpress.php',
			'LIFTERLESSON' => 'lifterlms/lifterlms.php',
			'LIFTERCOURSE' => 'lifterlms/lifterlms.php',
			'LIFTERCOUPON' => 'lifterlms/lifterlms.php',
			'LIFTERQUIZ' => 'lifterlms/lifterlms.php',
			'STMCOURSE' => 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php',
			'STMCURRICULUM' => 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php',
			'STMLESSON' => 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php',
			'STMQUIZ' => 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php',
			'STMQUESTION' => 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php',
			'STMORDER' => 'masterstudy-lms-learning-management-system/masterstudy-lms-learning-management-system.php',
			'FORUM' => 'bbpress/bbpress.php',
			'TOPIC' => 'bbpress/bbpress.php',
			'REPLY' => 'bbpress/bbpress.php',
			'POLYLANG' => 'polylang/polylang.php',
		);
		if(is_plugin_active('advanced-custom-fields-pro/acf.php'))
		{
			$plugins_array['GF'] = 'advanced-custom-fields-pro/acf.php';
		}
		if(is_plugin_active('advanced-custom-fields/acf.php'))
		{
			$plugins_array['GF'] = 'advanced-custom-fields/acf.php';
		}
		else{
			if(is_plugin_active('secure-custom-fields/secure-custom-fields.php')){
				$plugins_array['GF'] = 'secure-custom-fields/secure-custom-fields.php';	
			}
		}
		foreach ($mappingList as $templatename => $group) {				
			//added condition to check whether mapped fields plugin is active or not, if not remove it from mapping
			if(array_key_exists($templatename, $plugins_array)){
				if($templatename == 'WPML'){
					if(!is_plugin_active($plugins_array[$templatename]) && !is_plugin_active('sitepress-multilingual-cms/sitepress.php')){
						unset($mappingList[$templatename]);
						continue;
					}
				}
				elseif($templatename == 'POLYLANG'){
					if(!is_plugin_active($plugins_array[$templatename]) && !is_plugin_active('polylang-pro/polylang.php')){						
						unset($mappingList[$templatename]);
						continue;
					}
				}
				elseif($templatename == 'RF' ){					
					if(!is_plugin_active($plugins_array[$templatename]) && (!is_plugin_active('advanced-custom-fields/acf.php') || !is_plugin_active('secure-custom-fields/secure-custom-fields.php'))){						
						unset($mappingList[$templatename]);
						continue;
					}
					elseif((is_plugin_active('advanced-custom-fields/acf.php') || is_plugin_active('secure-custom-fields/secure-custom-fields.php')) && !is_plugin_active('acf-repeater/acf-repeater.php')) {						
						unset($mappingList[$templatename]);
						continue;
					}
				}
				elseif($templatename == 'ACF'){
					if((!is_plugin_active($plugins_array[$templatename]) && !is_plugin_active('secure-custom-fields/secure-custom-fields.php'))&& !is_plugin_active('advanced-custom-fields-pro/acf.php')){
						unset($mappingList[$templatename]);
						continue;
					}
				}
				elseif($templatename == 'AIOSEO'){
					if(!is_plugin_active($plugins_array[$templatename]) && !is_plugin_active('all-in-one-seo-pack-pro/all_in_one_seo_pack.php')){
						unset($mappingList[$templatename]);
						continue;
					}
				}
				elseif($templatename == 'RANKMATH'){
					if(!is_plugin_active($plugins_array[$templatename]) && !is_plugin_active('seo-by-rank-math-pro/rank-math-pro.php')){
						unset($mappingList[$templatename]);
						continue;
					}
				}
				elseif($templatename == 'YOASTSEO'){
					if(!is_plugin_active($plugins_array[$templatename]) && !is_plugin_active('wordpress-seo-premium/wp-seo-premium.php')){
						unset($mappingList[$templatename]);
						continue;
					}
				}
				elseif(!is_plugin_active($plugins_array[$templatename])){
					unset($mappingList[$templatename]);
					continue;
				}				
			}

			$count += count(array_filter($group));
		}
	
		//added - updated mapping in template table
		if(!empty($templateName)){
			global $wpdb;
			$template_table_name = $wpdb->prefix."ultimate_csv_importer_mappingtemplate";
			$wpdb->update(
				$template_table_name,
				array('mapping' => serialize($mappingList)),
				array('templatename' => $templateName),
				array('%s'),
				array('%s')
			);
		}

		return $count;	
	}
	
	

	/**
	* Ajax Call 
	* Searches Templates based on Template Name and Dates
	* @return array - Template Details
	*/
	public function search_template(){
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_import())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $wpdb;
		$template_name = isset($_POST['TemplateName']) ? sanitize_text_field($_POST['TemplateName']) : '';
		$start_date = isset($_POST['FromDate']) ? sanitize_text_field($_POST['FromDate']) : '';
		$end_date = isset($_POST['ToDate']) ? sanitize_text_field($_POST['ToDate']) : '';
		$filename = isset($_POST['filename']) ? sanitize_text_field($_POST['filename']) : '';
		$module = isset($_POST['module']) ? sanitize_text_field($_POST['module']) : '';
		$info = [];
		$details = [];
		$where = ["templatename != ''"];
		$params = [];

		if (!empty($module)) {
			$where[] = "(module = %s OR ((module IS NULL OR module = '') AND csvname = %s))";
			$params[] = $module;
			$params[] = $filename;
		} elseif (!empty($filename)) {
			$where[] = "csvname = %s";
			$params[] = $filename;
		}

		if ($start_date !== 'Invalid date' && !empty($start_date)) {
			$where[] = 'createdtime >= %s';
			$params[] = $start_date . ' 00:00:00';
		}

		if ($end_date !== 'Invalid date' && !empty($end_date)) {
			$where[] = 'createdtime <= %s';
			$params[] = $end_date . ' 23:59:59';
		}

		if (!empty($template_name)) {
			$where[] = 'templatename LIKE %s';
			$params[] = '%' . $wpdb->esc_like($template_name) . '%';
		}

		$query = "SELECT id, templatename, createdtime, module, mapping, csvname FROM {$wpdb->prefix}ultimate_csv_importer_mappingtemplate WHERE " . implode(' AND ', $where) . " ORDER BY id DESC";
		$templateList = !empty($params) ? $wpdb->get_results($wpdb->prepare($query, $params)) : $wpdb->get_results($query);
		
		if(!empty($templateList)){
			foreach($templateList as $value){
				$details = [];
				$templateName = $value->templatename;
		
				if(!empty($templateName)){					
					$module_name = $this->resolve_template_module($value, $module);
					$details['template_name'] = $templateName;
					$details['module'] = $module_name;
					$details['module_label'] = $this->get_module_label($module_name);
					$details['created_time'] = $value->createdtime;
					$mapping = $value->mapping;
					$map = maybe_unserialize($mapping);
					$map = is_array($map) ? $map : [];
					$count = $this->get_matched_count($map);
					$details['count'] = $count;	
					array_push($info , $details);
				}	
			}
			$response['success'] = true;
			$response['info'] = $info;
		}else{
			$response['success'] = false;
			$response['message'] = "Templates not found";
		}
		echo wp_json_encode($response);
		wp_die(); 	
	}
}		
