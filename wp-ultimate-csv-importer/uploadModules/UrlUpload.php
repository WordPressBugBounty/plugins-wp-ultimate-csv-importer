<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class UrlUpload implements Uploads{

	private static $instance = null;
	private static $smack_csv_instance = null;

    private function __construct(){
		add_action('wp_ajax_get_csv_url',array($this,'upload_function'));
    }

    public static function getInstance() {
		if (UrlUpload::$instance == null) {
			UrlUpload::$instance = new UrlUpload;
			UrlUpload::$smack_csv_instance = SmackCSV::getInstance();
			return UrlUpload::$instance;
		}
		return UrlUpload::$instance;
    }
	
	/**
	 * Upload file from URL.
	 */
    public function upload_function(){
		check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
		$file_url = esc_url_raw($_POST['url']);
		$file_url = wp_http_validate_url($file_url);
		$media_type = '';
        if (isset($_POST['MediaType'])) {
            $media_type = sanitize_key($_POST['MediaType']);
        }
		if(!$file_url){					
		$response['success'] = false;
		$response['message'] = 'Download Failed.URL is not valid.';
		echo wp_json_encode($response); 
		die();
		}
		$response = [];
		global $wpdb;
		$file_table_name = $wpdb->prefix ."smackcsv_file_events";			

			$pub = substr($file_url, strrpos($file_url, '/') + 1);
               /*Added support for google addon & dropbox*/
			if($pub=='pubhtml'){
				$spread_sheet=substr($file_url, 0, -7);
				$file_url = $spread_sheet.'pub?gid=0&single=true&output=csv';
			}elseif ($pub=='edit?usp=sharing') {
				$response['success'] = false;
				$response['message'] = 'Update your Google sheet as Public';
				echo wp_json_encode($response);				
			}
			
			if(!strstr($file_url, 'https://www.dropbox.com/')) {	
				$file_url   = $this->get_original_url($file_url);	
				$file_url = $file_url;
			}
			
			if(strstr($file_url, 'https://docs.google.com/')) {	
				$get_file_headers = get_headers($file_url, 1);
				if(isset($get_file_headers['Content-Disposition'])){
					$url_file_name = $this->get_filename_from_headers($get_file_headers['Content-Disposition']);
				}else{
					$get_file_id = explode('/', $file_url);
					$external_file = 'google-sheet-' . $get_file_id[count($get_file_id) - 2];
					$file_extension = explode('output=', $get_file_id[count($get_file_id) - 1]);
					$file_extension = $file_extension[1];
					$url_file_name = $external_file . '.' . $file_extension;
				}
			}elseif(strstr($file_url, 'https://www.dropbox.com/')) {
				$filename = basename($file_url);
				$get_local_filename = explode('?', $filename);
				$url_file_name = $get_local_filename[0];	
			}else { # Other URL's except google spreadsheets	
			
				$supported_file = array('csv' , 'xml' , 'zip' , 'txt','json');
				$has_extension = explode(".", basename($file_url));
				$has_file_extension = end($has_extension);
				if($has_extension && in_array($has_file_extension , $supported_file)){
					$url_file_name = basename($file_url);
				}
				else{

					$get_file_headers = get_headers($file_url, 1);
					if(isset($get_file_headers['Content-Disposition'])){
						$url_file_name = $this->get_filename_from_headers($get_file_headers['Content-Disposition']);
					}else{
						if(strpos($file_url, '&type=') !== false) {	
							$get_extension = substr($file_url, strpos($file_url, "&type=") + 6, 3);
							$url_file_name = basename($file_url) .'.'. $get_extension;
						}
						elseif((strpos($file_url, 'format=rss') !== false) || (strpos($file_url, '/rss') !== false)){	
							if(isset($get_file_headers['Content-Type'])){
								$url_extension = substr($get_file_headers['Content-Type'], strpos($get_file_headers['Content-Type'], 'text/') + strlen('text/'), 3);
								$url_file_name = basename($file_url) . '.' . $url_extension;
							}
							else{
								$url_file_name = basename($file_url) . '.xml';
							}
						}else{
							if(isset($get_file_headers['Content-Type'])){	
								if( strpos($get_file_headers['Content-Type'], 'text/') !== false) {	
									$url_extension = substr($get_file_headers['Content-Type'], strpos($get_file_headers['Content-Type'], 'text/') + strlen('text/'), 3);
								}
								else{
									$url_extension = substr($get_file_headers['Content-Type'], strpos($get_file_headers['Content-Type'], 'application/') + strlen('application/'), 3);	
								}
								$url_file_name = basename($file_url) . '.' . $url_extension;	
							}
							else{
								$url_file_name = basename($file_url);
							}
						}
					}
				}
			}
			
			$validate_instance = ValidateFile::getInstance();
			$zip_instance = ZipHandler::getInstance();
			$validate_format = $validate_instance->validate_file_format($url_file_name);
			
			if($validate_format == 'yes'){
				$upload_dir = UrlUpload::$smack_csv_instance->create_upload_dir();
				if($upload_dir){
					$url_file_name = str_replace('%20', ' ', $url_file_name);
					$event_key = UrlUpload::$smack_csv_instance->convert_string2hash_key($url_file_name);
					$file_extension = pathinfo($url_file_name, PATHINFO_EXTENSION);
					if(empty($file_extension)){
						$file_extension = 'xml';
					}
				

					$upload_dir_path = $upload_dir. $event_key;
                    if (!is_dir($upload_dir_path)) {
                        wp_mkdir_p( $upload_dir_path);
                    }
                    chmod($upload_dir_path, 0777);
					
					$wpdb->insert( $file_table_name , array( 'file_name' => $url_file_name , 'hash_key' => $event_key , 'status' => 'Downloading','lock' => true ) );
					$last_id = $wpdb->get_results("SELECT id FROM $file_table_name ORDER BY id DESC LIMIT 1",ARRAY_A);
					$lastid=$last_id[0]['id']; 
		
					$url_data = wp_safe_remote_get($file_url, array( 'timeout' => 30));							
					if(is_wp_error($url_data)){
						$response['success'] = false;
						$response['message'] = 'Download Failed.URL not valid.';
						echo wp_json_encode($response); 
						die();
					}
					$rawdata =  wp_remote_retrieve_body($url_data);
		
					$http_code = wp_remote_retrieve_response_code($url_data);
					if($http_code == 404){
						$response['success'] = false;
						$response['message'] = 'Download Failed';
						echo wp_json_encode($response); 
						$wpdb->get_results("UPDATE $file_table_name SET status='Download_Failed' WHERE id = '$lastid'");
					}
					else{
						$path = $upload_dir. $event_key .'/'.$event_key;
						$file = fopen($path , "w+");
						fputs($file, $rawdata);
						chmod($path, 0777);

						$validate_file = $validate_instance->file_validation($path , $file_extension );

						$file_size = filesize($path);
		                $filesize = $validate_instance->formatSizeUnits($file_size);
						
						if($validate_file == "yes"){
							$wpdb->get_results("UPDATE $file_table_name SET status='Downloaded',`lock`=false WHERE id = '$lastid'");
							fclose($file);
							$get_result = $validate_instance->import_record_function($event_key , $url_file_name);
							if(isset($media_type) && ($media_type == 'external' || $media_type == 'local')){
								$get_result['selected type'] = 'Media';
							}
							$response['success'] = true;
							$response['filename'] = $url_file_name;
							$response['hashkey'] = $event_key;
							$response['posttype'] = $get_result['Post Type'];
							$response['taxonomy'] = $get_result['Taxonomy'];
							$response['selectedtype'] = $get_result['selected type'];
							$response['file_type'] = $file_extension;
							$response['file_size'] = $filesize;
							$response['message'] = 'success';
							echo wp_json_encode($response); 
						}else{
							$response['success'] = false;
							$response['message'] = $validate_file;
							echo wp_json_encode($response); 
							unlink($path);
							$wpdb->get_results("UPDATE $file_table_name SET status='Download Failed',`lock`=true WHERE id = '$lastid'");
						}
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
	
	public static function get_original_url($url)
	{
		$url = str_replace(' ', '%20', $url);
		return $url;
	}

	public function get_filename_from_headers($file_full_name){	
		$arr = explode('filename=', $file_full_name);
		$url_full_file_name = explode('.', $arr[1]);
		$url_extension = substr($arr[1], strpos($arr[1], '.') + strlen('.'), 3);
		$file_name = $url_full_file_name[0] . '.' . $url_extension;
		$file_explode=explode('"',$file_name);
		return $file_explode[1];
	}



}
