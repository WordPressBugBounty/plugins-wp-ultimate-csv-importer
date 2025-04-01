<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;
require_once(__DIR__.'/../vendor/autoload.php');
use League\Csv\Writer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class DesktopUpload implements Uploads{

	private static $instance = null;
	private static $smack_csv_instance = null;

	private function __construct(){
		add_action('wp_ajax_get_desktop',array($this,'upload_function'));
		add_action('wp_ajax_oneClickUpload',array($this,'upload_function'));
	}

	public static function getInstance() {
		if (DesktopUpload::$instance == null) {
			DesktopUpload::$instance = new DesktopUpload;
			DesktopUpload::$smack_csv_instance = SmackCSV::getInstance();
			return DesktopUpload::$instance;
		}
		return DesktopUpload::$instance;
	}
	private function convertXlsxToCsv($upload_dir_path, $event_key)
	{
		$spreadsheet = IOFactory::load($_FILES['csvFile']['tmp_name']);
		$csv_file_path = $upload_dir_path . '/' . $event_key;
		$csv_writer = new Csv($spreadsheet);
		$csv_writer->setDelimiter(',');
		$csv_writer->setEnclosure('"');
		$csv_writer->setLineEnding("\r\n");
		$csv_writer->setIncludeSeparatorLine(false);
		$csv_writer->save($csv_file_path);
	}

	/**
	 * Upload file from desktop.
	 */
	public function upload_function(){

		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		global $wpdb;


		$validate_instance = ValidateFile::getInstance();
		$zip_instance = ZipHandler::getInstance();
		$media_type = '';
		if (isset($_POST['MediaType'])) {
			$media_type = strtolower(sanitize_key($_POST['MediaType']));
		}
		global $wpdb;
		$file_table_name = $wpdb->prefix ."smackcsv_file_events";

		$file_name = $_FILES['csvFile']['name'];    
		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		if(empty($file_extension)){
			$file_extension = 'xml';
		}
		$validate_format = $validate_instance->validate_file_format($file_name);

		$response =[];
		if (!extension_loaded('xml')) {
			$response['success'] = false;
			$response['message'] = 'The required PHP module xml is not installed. Please install it.';
			echo wp_json_encode($response);
			wp_die();
		}
		if($validate_format == 'yes'){

			$upload_dir = DesktopUpload::$smack_csv_instance->create_upload_dir();

			if($upload_dir){
				$event_key = DesktopUpload::$smack_csv_instance->convert_string2hash_key($file_name);

				if($file_extension == 'zip'){
					if(!function_exists('curl_version')){
						$response['success'] = false;
						$response['message'] = 'Curl is not exists.Kindly install it.';
						echo wp_json_encode($response); 
						wp_die();
					}
					$zip_response = [];    
					$path = $upload_dir . $event_key . '.zip';
					$extract_path = $upload_dir . $event_key;

					if(move_uploaded_file($_FILES['csvFile']['tmp_name'], $path)){
						chmod($path, 0777);

						$zip_result = $zip_instance->zip_upload($path , $extract_path,$event_key);
						if($zip_result == 'UnSupported File Format'){
							$zip_response['success'] = false;
							$zip_response['message'] = "UnSupported File Format Inside Zip";
						}
						else{
							$zip_response['success'] = true;
							$zip_response['filename'] = $file_name;
							$zip_response['file_type'] = 'zip'; 
							$zip_response['info'] = $zip_result; 
							$zip_response['info'] = $zip_result;
							$action = $_POST['action'];
                            
							if($action == 'oneClickUpload'){
								$zip_response['info'] = $zip_result;
								$exporter_adapter_list = array(
									"core_fields" => "CORE",
									"Comments" => "Comments",
									"acf_fields" => "ACF",
									"acf_group_fields" => "ACF",
									"acf_pro_fields" => "ACFPro",
									"acf_group_fields" => "ACFPro",
									"acf_repeater_fields" => "ACFPro",
									"acf_flexible_fields" => "ACFPro",
									"all_in_one_seo_fields" => "AllInOneSeo",
									"bp_fields" => "BBPress",
									"WooCommerceBSI" => "WooCommerce Billing Shipping Information", //TODO: To be named properly
									"CCTM" => "Custom Content Types Manager",
									"custom_fields_suite_fields" => "CFS",
									"cmb2_fields" => "CMB2",
									"CustomerReviews" => "Customer Reviews",
									"Elementor" => "Elementor",
									"events_manager_fields" =>"EventsManager",
									"jetengine_fields" => "JetEngine",
									"jetengine_rf_fields" => "JetEngine",
									"jetenginecct_fields" => "JetEngineCCT",
									"jetenginecct_rf_fields" => "JetEngineCCT",
									"jetenginecpt_fields" => "JetEngineCPT",
									"jetenginecpt_rf_fields" => "JetEngineCPT",
									"jetengine_rel_fields" => "JetEngineRelations",
									"jetenginetaxonomy_fields" => "JetEngineTaxonomy",
									"jetenginetaxonomy_rf_fields" => "JetEngineTaxonomy",
									"job_listing_fields" => "Job",
									"course_settings_fields" => "LearnPress",
									"curriculum_settings_fields" => "LearnPress",
									"lesson_settings_fields" => "LearnPress",
									"quiz_settings_fields" => "LearnPress",
									"question_settings_fields" => "LearnPress",
									"order_settings_fields" => "LearnPress",
									"metabox_fields" => "MetaBox",
									"metabox_group_fields" => "MetaBoxGroup",
									"metabox_relations_fields" => "MetaBoxRelation",
									"custom_fields_members" => "Users",
									"custom_ultimate_members" => "Users",
									"custom_fields_wp_members" => "Users",
									"NextgenGallery" => "Nextgen Gallery",
									"pods_fields" => "Pods",
									"Polylang_settings_fields" => "Polylang",
									"WooProductAttr" => "WooCommerce Product Attributes",
									"WooProductBundleMeta" => "WooCommerce Product Bundle Meta",
									"order_meta_fields" => "WooProductMeta",
									"coupon_meta_fields" => "WooProductMeta",
									"refund_meta_fields" => "WooProductMeta",
									"product_meta_fields" => "WooProductMeta",
									"rank_math_fields" => "RankMath",
									"rank_math_pro_fields" => "RankMath",
									"seopress_fields" => "SeoPress",
									"terms_and_taxonomies" => "TermsAndTaxonomies",
									"types_fields" => "ToolSet",
									"billing_and_shipping_information" => "Users",
									"WooCommerceMeta" => "WooCommerce Meta",
									"wordpress_custom_fields" => "WordpressCustom",
									"directory_pro_fields" => "WordpressCustom",
									"wp_ecom_custom_fields" => "WPeCommerce",
									"wpml_fields" => "WPML",
									"yoast_seo_fields" => "YoastSeo",
									"lifter_course_settings_fields" => "LifterLMS",
									"lifter_lesson_settings_fields" => "LifterLMS",
									"lifter_quiz_settings_fields" => "LifterLMS",
									"lifter_coupon_settings_fields" => "LifterLMS",
									"lifter_review_settings_fields" => "LifterLMS",
									"course_settings_fields_stm" => "MasterStudyLMS",
									"curriculum_settings_fields_stm" => "MasterStudyLMS",
									"lesson_settings_fields_stm" => "MasterStudyLMS",
									"quiz_settings_fields_stm" => "MasterStudyLMS",
									"question_settings_fields_stm" => "MasterStudyLMS",
									"order_settings_fields_stm" => "MasterStudyLMS",
									"forum_attributes_fields" => "BBPress",
									"topic_attributes_fields" => "BBPress",
									"reply_attributes_fields" => "BBPress"
								);
								foreach ($zip_result as $file) {

									if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'json') {

										$json_content = file_get_contents($file['path']);
										$decoded_json = json_decode($json_content, true);
										function mapFields($decoded_json, $exporter_adapter_list) {
											$mappedFields = [];

											foreach ($decoded_json as $key => $fields) {
												if (isset($exporter_adapter_list[$key])) {
													$mappedKey = $exporter_adapter_list[$key]; // Get mapped value from the list

													if (!isset($mappedFields[$mappedKey])) {
														$mappedFields[$mappedKey] = []; // Ensure category exists
													}

													foreach ($fields as $fieldKey => $fieldValue) {
														$mappedFields[$mappedKey][$fieldKey] = $fieldValue;
													}
												}
											}

											return json_encode($mappedFields, JSON_PRETTY_PRINT);
										}


										$formatted_data = [];

										foreach ($decoded_json['headers']['fields'] as $field_group) {
											foreach ($field_group as $key => $fields) {
												if (is_array($fields)) {
													foreach ($fields as $field) {
														if (isset($field['name'])) {
															// Assign key and value dynamically
															$formatted_data[$key][$field['name']] = $field['name'];
														}
													}
												}
											}
										}
										$json_content = json_encode($formatted_data, JSON_PRETTY_PRINT);



										$decoded_jsons = json_decode($json_content, true);
										$mappedJson = mapFields($decoded_jsons, $exporter_adapter_list);

										$this->save_mapping($mappedJson,$event_key,$_POST,$decoded_json);


										if (is_array($decoded_json)) {
											// Merge JSON key-value pairs into the response array
											$zip_response = array_merge($zip_response, $decoded_json);
										}
									}
								}
							}
							$zip_response['hashkey'] = $event_key;
						}
					}else{
						$zip_response['success'] = false;
						$zip_response['message'] = "Cannot download zip file";
					}   
					if (preg_match('/^smbundle_(.*)\.zip$/', $file_name, $matches)) {
						$path = $upload_dir . $event_key . '/' . $event_key;

						if (file_exists($path)) {
							// Get the MIME type of the file
							$mime_type = mime_content_type($path); // Alternative: finfo_open(FILES)
							
						
							// Get the extension based on MIME type
							$extension = $this->get_extension_from_mime($mime_type);
						}

						$file_name = $matches[1] .'.'.$extension ;
					} else {
						echo "Invalid file name format";
					}
					$wpdb->insert( $file_table_name , array('file_name' => $file_name ,'total_rows' => $decoded_json['total_rows'],'hash_key' => $event_key , 'status' => 'Downloading', 'lock' => true) );

					echo wp_json_encode($zip_response); 
					wp_die();
				}

				$upload_dir_path = $upload_dir. $event_key;
				if (!is_dir($upload_dir_path)) {
					wp_mkdir_p( $upload_dir_path);
				}
				chmod($upload_dir_path, 0777);	
				$wpdb->insert( $file_table_name , array('file_name' => $file_name , 'hash_key' => $event_key , 'status' => 'Downloading', 'lock' => true) );
				$last_id = $wpdb->get_results("SELECT id FROM $file_table_name ORDER BY id DESC LIMIT 1",ARRAY_A);
				$lastid = $last_id[0]['id'];

				switch($_FILES['csvFile']['error']){

				case UPLOAD_ERR_OK:
					$path = $upload_dir. $event_key. '/' . $event_key;
					if ($file_extension == 'xlsx' || $file_extension == 'xls') {
						$this->convertXlsxToCsv($upload_dir_path, $event_key);
						$file_extension = 'csv';
					}
					else{
						if (move_uploaded_file($_FILES['csvFile']['tmp_name'], $path)) {
							chmod($path, 0755);
						} else {
							$response['success'] = false;
							$response['message'] = "Cannot download the file";
							echo wp_json_encode($response); 
							$wpdb->get_results("UPDATE $file_table_name SET status='Download_Failed' WHERE id = '$lastid'");
						}
					}
					$validate_file = $validate_instance->file_validation($path , $file_extension);
					$file_size = filesize($path);
					$filesize = $validate_instance->formatSizeUnits($file_size);   
					$server_software = sanitize_text_field($_SERVER['SERVER_SOFTWARE']);
					if($validate_file == "yes"){
						$fields = $wpdb->get_results("UPDATE $file_table_name SET status='Downloaded',`lock`=false WHERE id = '$lastid'");

						$get_result = $validate_instance->import_record_function($event_key , $file_name);
						if(isset($media_type) && ($media_type == 'external' || $media_type == 'local')){
							$get_result['selected type'] = 'Media';
						}
						$response['success'] = true;
						$response['filename'] = $file_name;
						$response['hashkey'] = $event_key;
						$response['posttype'] = $get_result['Post Type'];
						$response['selectedtype'] = $get_result['selected type'];
						$response['taxonomy'] = $get_result['Taxonomy'];
						$response['file_type'] = $file_extension;
						$response['file_size'] = $filesize;
						$response['message'] = 'success';
						echo wp_json_encode($response); 

					}
					else{
						$response['success'] = false;
						$response['message'] = $validate_file;
						echo wp_json_encode($response); 
						unlink($path);
						$wpdb->get_results("UPDATE $file_table_name SET status='Download_Failed' WHERE id = '$lastid'");
					}
					break;

				case UPLOAD_ERR_INI_SIZE:
					$response['success'] = false;
					$response['message'] = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
					echo wp_json_encode($response); 
					$wpdb->get_results("UPDATE $file_table_name SET status='Download_Failed' WHERE id = '$lastid'");
					break;

				default:
					$response['success'] = false;
					$response['message'] = "Cannot download file";
					echo wp_json_encode($response); 
					$wpdb->get_results("UPDATE $file_table_name SET status='Download_Failed' WHERE id = '$lastid'");
					break;
				}
			}else{
				$response['success'] = false;
				$response['message'] = "Please create Upload folder with writable permission";
				echo wp_json_encode($response); 
			}

		}else{
			$response['success'] = false;
			$response['message'] = $validate_format;
			echo wp_json_encode($response); 
		}
		wp_die();

	}


	public function save_mapping($data,$hash_key,$post,$decoded_json)
	{

		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		$type          = $decoded_json['selectedtype'];
		$map_fields    = $data;

		$mapping_type = 'mapping-section';
		$counter = isset($counter) ? $counter : 0;
		$selected_mode = 'Advanced';
		global $wpdb;
		if ($selected_mode == 'simpleMode') {
			$fileiteration = 5;
			update_option('sm_bulk_import_free_iteration_limit', $fileiteration);
			$media_settings['media_handle_option'] = 'true';
			$media_settings['use_ExistingImage'] = 'true';
			$image_info = array(
				'media_settings'  => $media_settings
			);
			update_option('smack_image_options', $image_info);
		}
		$template_table_name = $wpdb->prefix . "ultimate_csv_importer_mappingtemplate";
		$file_table_name = $wpdb->prefix . "smackcsv_file_events";

		$mapping_filter = '';	

		$mapped_fields = json_decode(stripslashes($map_fields), true);
		$helpers_instance = ImportHelpers::getInstance();
		$response = [];
		$counter = 0;
		foreach ($mapped_fields as $maps) {
			foreach ($maps as $header_keys => $value) {
				if (strpos($header_keys, '->cus2') !== false) {
					if (!empty($value)) {
						$helpers_instance->write_to_customfile($value);
					}
				}
			}
		}
		foreach ($mapped_fields as $key => $value) {
			if ($key === 'ECOMMETA') {
				$map_data[$key] = $value;
				if ($has_bundlemeta) {
					$map_data['BUNDLEMETA'] = $mapped_fields['BUNDLEMETA'];
				}
			}
			if ($key !== 'BUNDLEMETA') {

				$map_data[$key] = $value;
			}
		}		
		$get_detail   = $wpdb->get_results("SELECT file_name FROM $file_table_name WHERE `hash_key` = '$hash_key'");
		$get_file_name = $get_detail[0]->file_name;
		$get_hash = $wpdb->get_results("SELECT eventKey FROM $template_table_name");

		$mapping_fields = serialize($map_data);
		$time = date('Y-m-d h:i:s');
		$get_file_name = $get_detail[0]->file_name;

		// Using prepare to safely insert the values
		if(!empty($get_hash)){
			foreach ($get_hash as $hash_values) {
				$inserted_hash_values[] = $hash_values->eventKey;
			}
			if (in_array($hash_key, $inserted_hash_values)) {
				$query = $wpdb->prepare(
					"UPDATE $template_table_name SET mapping = %s, mapping_filter = %s, createdtime = %s, module = %s, mapping_type = %s WHERE eventKey = %s",
					$mapping_fields,
					$mapping_filter,
					$time,
					$type,
					$mapping_type,
					$hash_key
				);
			} else {
				$query = $wpdb->prepare(
					"INSERT INTO $template_table_name (mapping, mapping_filter , createdtime, module, csvname, eventKey, mapping_type) 
					VALUES (%s, %s, %s, %s, %s, %s, %s)",
		$mapping_fields,
			$mapping_filter,
			$time,
			$type,
			$get_file_name,
			$hash_key,
			$mapping_type
				);
			}
		}else{
			$query = $wpdb->prepare(
				"INSERT INTO $template_table_name (mapping, mapping_filter , createdtime, module, csvname, eventKey, mapping_type) 
				VALUES (%s, %s, %s, %s, %s, %s, %s)",
		$mapping_fields,
			$mapping_filter,
			$time,
			$type,
			$get_file_name,
			$hash_key,
			$mapping_type
			);
		}

		$wpdb->query($query);

		$fileiteration = '5';
		update_option('sm_bulk_import_free_iteration_limit', $fileiteration);
		$response['success'] = true;
		$response['file_iteration'] = (int)$fileiteration;

	}

	public function get_extension_from_mime($mime_type) {
		$mime_map = [
			'text/csv'              => 'csv',
			'application/xml'       => 'xml',
			'text/xml'              => 'xml',
			'application/vnd.ms-excel'  => 'xls',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
			'text/tab-separated-values' => 'tsv',
		];
	
		return $mime_map[$mime_type] ?? null;
	}

}
