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

                        $zip_result = $zip_instance->zip_upload($path , $extract_path);
                        if($zip_result == 'UnSupported File Format'){
                            $zip_response['success'] = false;
                            $zip_response['message'] = "UnSupported File Format Inside Zip";
                        }
                        else{
                            $zip_response['success'] = true;
                            $zip_response['filename'] = $file_name;
                            $zip_response['file_type'] = 'zip'; 
                            $zip_response['info'] = $zip_result; 
                        }
                    }else{
                        $zip_response['success'] = false;
                        $zip_response['message'] = "Cannot download zip file";
                    }   
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
}