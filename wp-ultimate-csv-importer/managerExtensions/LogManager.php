<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class LogManager {

    private static $instance = null,$saveMappingInstance;
	private static $smack_csv_instance = null;
	// declare log file and file pointer as private properties
	private $log_file, $fp;
	public $logArr;
	public function __construct(){
		add_action('wp_ajax_display_log',array($this,'display_log'));		
		add_action('wp_ajax_download_log',array($this,'download_log'));
		add_action('wp_ajax_download_media_log',array($this,'download_media_log'));
		add_action('wp_ajax_download_failed_log',array($this,'download_failed_log'));		
		add_action('wp_ajax_delete_log',array($this,'delete_log'));
		add_action('wp_ajax_stream_uci_log', array( $this, 'stream_uci_log' ));
	}

	/**
	 * Allowed log types for protected storage + authenticated streaming.
	 *
	 * @return array<string, array{subdir:string, filename:string}>
	 */
	public static function log_type_map() {
		return array(
			'summary'              => array(
				'subdir'   => 'smack_uci_uploads/summary_logs',
				'filename' => 'summary.csv',
			),
			'media'                => array(
				'subdir'   => 'smack_uci_uploads/media_log',
				'filename' => 'Media_log.csv',
			),
			'failed_media'         => array(
				'subdir'   => 'smack_uci_uploads/failed_media_logs',
				'filename' => 'FailedMedia.csv',
			),
			'import_summary'       => array(
				'subdir'   => 'smack_uci_uploads/imports/import_logs',
				'filename' => 'summary_log.csv',
			),
			'import_failed_media'  => array(
				'subdir'   => 'smack_uci_uploads/imports/failed_media_logs',
				'filename' => 'failed_media_log.csv',
			),
			'import_media'         => array(
				'subdir'   => 'smack_uci_uploads/imports/media_logs',
				'filename' => 'media_log.csv',
			),
		);
	}

	/**
	 * Create (if needed) and harden a log directory against direct HTTP access.
	 *
	 * @param string $directory Absolute directory path.
	 * @return string|false Directory path or false on failure.
	 */
	public static function ensure_protected_log_directory( $directory ) {
		$directory = trailingslashit( wp_normalize_path( (string) $directory ) );
		if ( $directory === '/' || $directory === '' ) {
			return false;
		}
		if ( ! SecurityHelper::ensure_secure_directory( untrailingslashit( $directory ) ) ) {
			return false;
		}
		SecurityHelper::protect_log_directory( $directory );
		// Sibling folders from earlier versions have no rules of their own.
		SecurityHelper::protect_log_directory( dirname( untrailingslashit( $directory ) ) );
		return $directory;
	}

	/**
	 * Resolve a validated absolute path for a log file.
	 *
	 * @param string $log_type  Key from log_type_map().
	 * @param string $event_key Opaque event / hash key.
	 * @return string|false
	 */
	public static function resolve_log_file_path( $log_type, $event_key ) {
		$map = self::log_type_map();
		if ( ! isset( $map[ $log_type ] ) ) {
			return false;
		}

		$event_key = sanitize_key( (string) $event_key );
		if ( $event_key === '' || ! preg_match( '/^[a-f0-9]+$/i', $event_key ) ) {
			return false;
		}

		$upload = wp_upload_dir();
		if ( empty( $upload['basedir'] ) ) {
			return false;
		}

		$base_dir = trailingslashit( $upload['basedir'] ) . trailingslashit( $map[ $log_type ]['subdir'] ) . $event_key;
		$dir      = self::ensure_protected_log_directory( $base_dir );
		if ( false === $dir ) {
			return false;
		}

		$file_path = trailingslashit( $dir ) . $map[ $log_type ]['filename'];
		if ( ! SecurityHelper::is_path_inside_directory( $file_path, $dir ) ) {
			return false;
		}

		return $file_path;
	}

	/**
	 * Build an authenticated admin-ajax URL that streams a protected log file.
	 *
	 * @param string $log_type  Key from log_type_map().
	 * @param string $event_key Opaque event / hash key.
	 * @return string
	 */
	public static function get_authenticated_log_url( $log_type, $event_key ) {
		return add_query_arg(
			array(
				'action'    => 'stream_uci_log',
				'log_type'  => sanitize_key( $log_type ),
				'event_key' => sanitize_key( $event_key ),
				'securekey' => wp_create_nonce( 'smack-ultimate-csv-importer' ),
			),
			admin_url( 'admin-ajax.php' )
		);
	}

	/**
	 * Stream a protected log CSV after login, capability, and path validation.
	 *
	 * @return void
	 */
	public function stream_uci_log() {
		if ( ! is_user_logged_in() ) {
			status_header( 403 );
			wp_die( esc_html__( 'Unauthorized access.', 'wp-ultimate-csv-importer' ), 403 );
		}

		SecurityHelper::verify_ajax_nonce();

		if ( ! SecurityHelper::check_capability( SecurityHelper::can_download_logs() ) ) {
			status_header( 403 );
			wp_die( esc_html__( 'You do not have sufficient permissions to download this file.', 'wp-ultimate-csv-importer' ), 403 );
		}

		$log_type  = isset( $_REQUEST['log_type'] ) ? sanitize_key( wp_unslash( $_REQUEST['log_type'] ) ) : '';
		$event_key = isset( $_REQUEST['event_key'] ) ? sanitize_key( wp_unslash( $_REQUEST['event_key'] ) ) : '';
		$file_path = self::resolve_log_file_path( $log_type, $event_key );

		if ( false === $file_path || ! is_file( $file_path ) || ! is_readable( $file_path ) ) {
			status_header( 404 );
			wp_die( esc_html__( 'Log file not found.', 'wp-ultimate-csv-importer' ), 404 );
		}

		$map      = self::log_type_map();
		$filename = isset( $map[ $log_type ]['filename'] ) ? $map[ $log_type ]['filename'] : 'log.csv';
		$filesize = filesize( $file_path );

		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		if ( false !== $filesize ) {
			header( 'Content-Length: ' . (int) $filesize );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $file_path );
		exit;
	}

    public static function getInstance() {
		if (LogManager::$instance == null) {
			LogManager::$instance = new LogManager;
			LogManager::$smack_csv_instance = UCICore::getInstance();
			LogManager::$saveMappingInstance = SaveMapping::getInstance();
			return LogManager::$instance;
		}
		return LogManager::$instance;
	}


	/**
	 * Writes event log in log file.
	 * @param  string $hash_key - file hash key
	 * @param  string $original_file_name - file name
	 * @param  string $fileType - file extension
	 * @param  string $mode - file mode (import or update)
	 * @param  int    $totalCount - Total number of records
	 * @param  string $importType - Post type
	 * @param  string $core_log - Event log
	 * @param  boolean $addHeader 
	 */
	public function get_event_log($hash_key , $original_file_name , $fileType , $mode , $totalCount , $importType , $core_log, $addHeader,$templatekey = null){
		$smack_instance = UCICore::getInstance();
		global $logArr;
		if (is_array($core_log)){
			$logArr = $core_log;
			$this->displayLogValue($logArr);
		}
	}
	public function displayLogValue(){
		global $logArr;
		return $logArr;
	}


	/**
	 * Retrieves and display the file events history.
	 */
	public function display_log(){	
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_download_logs())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $wpdb,$logArr;
		$response = [];
		$logInfo = [];
		$value = [];

		$logInformation = $wpdb->get_results("select * from {$wpdb->prefix}smackuci_events where deletelog = 0 order by id desc ");
		if(empty($logInformation)){
			$response['success'] = false;
			$response['message'] = "No logs Found";
		}else{
			foreach($logInformation as $logIndex => $logValue){

				$file_name = $logValue->original_file_name;
				$revision = $logValue->revision;
				$module = $logValue->import_type;
				$inserted = $logValue->created;
				$updated = $logValue->updated;
				$skipped = $logValue->skipped;
				$failed = $logValue->failed;

				$logInfo['filename'] = $file_name;
				$logInfo['revision'] = $revision;
				$logInfo['module'] = $module;
				$logInfo['inserted'] = $inserted ;
				$logInfo['updated'] = $updated;
				$logInfo['skipped'] = $skipped;
				$logInfo['failed'] = $failed;

				array_push($value , $logInfo);
			}
			$response['success'] = true;
			$response['info'] = $value;
			$response['importer_records']=$logArr;
		}
		echo wp_json_encode($response);
		wp_die();
	}

	/**
	 * Delete the Logs
	 */
	public function delete_directory($directoryPath) {
		if (file_exists($directoryPath) && is_dir($directoryPath)) {
			$files = glob($directoryPath . '*');
			foreach ($files as $file) {
				if (is_file($file)) {
					unlink($file);
				}
			}
			if (!rmdir($directoryPath)) {
				return false;
			}
		}
		return true;
	}
	public function delete_log(){
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_download_logs())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		global $wpdb;
		$smack_instance = UCICore::getInstance();
		$filename = sanitize_text_field($_POST['filename']);
		$revision = sanitize_text_field($_POST['revision']);
		$upload_path = $smack_instance->create_upload_dir();
		$get_details = $wpdb->get_results($wpdb->prepare("select id,eventKey from {$wpdb->prefix}smackuci_events where revision = %d and original_file_name = %s", $revision, $filename));
		if (!empty($get_details)) {
			foreach ($get_details as $records) {
				$eventKey = $records->eventKey;
				$directories = [
					$upload_path . 'import_logs/' . $eventKey . '/',
					$upload_path . 'failed_media_logs/' . $eventKey . '/',
					$upload_path . 'media_logs/' . $eventKey . '/',
				];

				foreach ($directories as $directoryPath) {
					if (!$this->delete_directory($directoryPath)) {
						$response['message'] = "File not available. Kindly refresh the page.";
						echo wp_json_encode($response);
						wp_die();
					}
				}

				$wpdb->update($wpdb->prefix . 'smackuci_events', ['deletelog' => true], ['id' => $records->id]);
			}
			if ( class_exists( '\Smackcoders\UCI\Core\Dashboard\DashboardService' ) ) {
				\Smackcoders\UCI\Core\Dashboard\DashboardService::bust_cache();
			}
			$response['message'] = "Deleted Successfully";
			echo wp_json_encode($response);
			wp_die();
		}
		else {
			$response['message'] = "Record not found";
		}

		echo wp_json_encode($response);
		wp_die();
	}

	/**
	 * Downloads download_media_log .
	 */
	// public function download_media_log(){
	// 	check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	// 	global $wpdb;		
	// 	$response = [];
	//     $filename = sanitize_file_name($_POST['filename']);
	//     $revision = sanitize_text_field($_POST['revision']);

	//     $upload = wp_upload_dir();
	//     $upload_dir = $upload['baseurl'];
	//     $upload_url = $upload_dir . '/smack_uci_uploads/imports/media_logs/';

	//     $upload_path = LogManager::$smack_csv_instance->create_upload_dir();
	// 	$get_event_key = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision , $filename));
	// 	if(empty($get_event_key)) {
	// 		$response['success'] = false;
	//         $response['message'] = 'Log not exists';
	// 	}
	// 	else {
	// 		$logPath = $upload_path.'media_logs'.'/'.$get_event_key[0]->eventKey .'/';
	// 		if (file_exists($logPath)) :
	// 			$loglink = $upload_url .$get_event_key[0]->eventKey .'/'.'media_log.csv';
	// 			$response['success'] = true;
	// 			$response['media_log_link'] = $loglink;

	// 		else :
	// 			$response['success'] = false;
	// 			$response['message'] = 'Log not exists';

	// 		endif;
	// 	}   
	//     echo wp_json_encode($response); 
	//     wp_die();  
	// }
	public function download_media_log(){
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_download_logs())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		global $wpdb;

		$response = [];
		$filename = sanitize_file_name($_POST['filename']);
		$revision = isset($_POST['revision']) ? sanitize_text_field($_POST['revision']) : '';
	$hash_key=isset($_POST['hashkey'] ) ? sanitize_text_field($_POST['hashkey']) : '';
		if(empty($hash_key)){
			$event_key_result = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision, $filename));
			$get_event_key = $event_key_result[0]->eventKey;

		}
		else{
			$get_event_key=$hash_key;
		}

		if (empty($get_event_key)) {
			$response['success'] = false;
			$response['message'] = 'Log not exists';
			echo wp_json_encode($response);
			wp_die();
		} else {

			$file_path = self::resolve_log_file_path( 'media', $get_event_key );
			if ( false === $file_path ) {
				$response['success'] = false;
				$response['message'] = 'Log not exists';
				echo wp_json_encode($response);
				wp_die();
			}
			$upload_dir = trailingslashit( dirname( $file_path ) );
			$file_url   = self::get_authenticated_log_url( 'media', $get_event_key );




			if (file_exists($file_path)) {
				// If the file already exists, return the authenticated download URL
				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();

			} else {


				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT  media_id, title, actual_url,file_url,file_name,caption,alt_text,description,status 
					 FROM " . $wpdb->prefix . "failed_media 
					 WHERE event_id = %s",
						$get_event_key,

					)
				);
				$json_posts = wp_json_encode($results);
				$posts_array = json_decode($json_posts, true);

				if (empty($posts_array)) {
					$response['success'] = false;
					$response['message'] = 'No posts found or failed to decode JSON.';
					echo wp_json_encode($response);
					wp_die();
				}

				$csv_file = fopen('php://temp', 'w');
				if (!empty($posts_array)) {
					fputcsv($csv_file, array_keys($posts_array[0]));
				}

				foreach ($posts_array as $post) {
					fputcsv($csv_file, $post);
				}
				rewind($csv_file);

				$csv_contents = stream_get_contents($csv_file);
				fclose($csv_file);

				// Save the CSV data to the file
				file_put_contents($file_path, $csv_contents);

				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();
			}
		}
	}

	/**
	 * Downloads failed event log.
	 */
	// public function download_failed_log(){
	// 	check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	// 	global $wpdb;		
	// 	$response = [];
	//     $filename = sanitize_file_name($_POST['filename']);
	//     $revision = sanitize_text_field($_POST['revision']);

	//     $upload = wp_upload_dir();
	//     $upload_dir = $upload['baseurl'];
	//     $upload_url = $upload_dir . '/smack_uci_uploads/imports/failed_media_logs/';

	//     $upload_path = LogManager::$smack_csv_instance->create_upload_dir();
	// 	$get_event_key = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision , $filename));
	// 	if(empty($get_event_key)) {
	// 		$response['success'] = false;
	//         $response['message'] = 'Log not exists';
	// 	}
	// 	else {
	// 		$logPath = $upload_path .'failed_media_logs'.'/'.$get_event_key[0]->eventKey .'/failed_media_log.csv';
	// 		if (file_exists($logPath)) :
	// 			$loglink = $upload_url .$get_event_key[0]->eventKey .'/'.'failed_media_log.csv';
	// 			$response['success'] = true;
	// 			$response['failed_log_link'] = $loglink;

	// 		else :
	// 			$response['success'] = false;
	// 			$response['message'] = 'Log not exists';

	// 		endif;
	// 	}   
	//     echo wp_json_encode($response); 
	//     wp_die();  
	// }


	/**
	 * Downloads file event log.
	 */
	// public function download_log(){
	// 	check_ajax_referer('smack-ultimate-csv-importer', 'securekey');
	// 	global $wpdb;

	//     $response = [];
	//     $filename = sanitize_file_name($_POST['filename']);
	//     $revision = sanitize_text_field($_POST['revision']);

	//     $upload = wp_upload_dir();
	//     $upload_dir = $upload['baseurl'];
	//     $upload_url = $upload_dir . '/smack_uci_uploads/imports/import_logs/';

	//     $upload_path = LogManager::$smack_csv_instance->create_upload_dir();
	// 	$get_event_key = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision , $filename));
	// 	if(empty($get_event_key)) {
	// 		$response['success'] = false;
	//         $response['message'] = 'Log not exists';
	// 	}
	// 	else {
	// 		$logPath = $upload_path.'import_logs'.'/'.$get_event_key[0]->eventKey .'/';

	// 		if (file_exists($logPath)) :
	// 			$loglink = $upload_url .$get_event_key[0]->eventKey .'/'.'summary_log.csv';
	// 			$response['success'] = true;
	// 			$response['log_link'] = $loglink;

	// 		else :
	// 			$response['success'] = false;
	// 			$response['message'] = 'Log not exists';

	// 		endif;
	// 	}   
	//     echo wp_json_encode($response); 
	//     wp_die();
	// }

	public function download_log() {
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_download_logs())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		global $wpdb;

		$response = [];
		$filename = sanitize_file_name($_POST['filename']);
		$revision = isset($_POST['revision']) ? sanitize_text_field($_POST['revision']) : '';
		$hash_key=isset($_POST['hashkey'] ) ? sanitize_text_field($_POST['hashkey']) : '';
		$module  = sanitize_text_field($_POST['type']);
		if(empty($hash_key)){
			$event_key_result = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision, $filename));
			$get_event_key = $event_key_result[0]->eventKey;

		}
		else{
			$get_event_key=$hash_key;
		}
		if (empty($get_event_key)  || $module == 'Media') {
			$response['success'] = false;
			$response['message'] = 'Log not exists';
			echo wp_json_encode($response);
			wp_die();
		} else {

			$file_path = self::resolve_log_file_path( 'summary', $get_event_key );
			if ( false === $file_path ) {
				$response['success'] = false;
				$response['message'] = 'Log not exists';
				echo wp_json_encode($response);
				wp_die();
			}
			$file_url = self::get_authenticated_log_url( 'summary', $get_event_key );

			if (file_exists($file_path)) {
				// If the file already exists, return the authenticated download URL
				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();

			} else {
				$cat_check = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));
				foreach ($cat_check as $item) {

					switch (strtolower($item->is_category)) {
						case '1':
							$found_category = true;
							break;
						case '2':
							$found_tag = true;
							break;

						case '3':
							$found_users = true;
							break;
						case '4':
							$found_comment = true;
							break;

					}

				}
				// category log
				if (isset($found_category) && $found_category){
					global $wpdb;

					$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

					if (!empty($cat_ids)) {

						$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));


						$query = "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id IN ($placeholders)";
						$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));
						foreach ($results as $result) {
							$term_id = $result->term_id;

							// Admin link
							$admin_link = admin_url("term.php?taxonomy=category&tag_ID={$term_id}");

							// Weblink
							$site_url = get_site_url();
								$term_link = trailingslashit($site_url) . 'index.php/'.'category/' . $result->slug . '/';

							$result->admin_link = $admin_link;
							$result->weblink = $term_link;
						}

					}
				}
				//tag log
				elseif(isset($found_tag) && $found_tag){
					global $wpdb;

					$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

					if (!empty($cat_ids)) {

						$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));


						$query = "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id IN ($placeholders)";
						$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));
						foreach ($results as $result) {
							$term_id = $result->term_id;

							// Admin link
							$admin_link = admin_url("term.php?taxonomy=post_tag&tag_ID={$term_id}");

							// Weblink
							$site_url = get_site_url();
								$term_link = trailingslashit($site_url) . 'index.php/'.'tag/' . $result->slug . '/';

							$result->admin_link = $admin_link;
							$result->weblink = $term_link;
						}

					}

				}
				//users log
				elseif (isset($found_users) && $found_users) {
					global $wpdb;

					$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

					if (!empty($cat_ids)) {
						$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));

						// Query to get user information, including the ID
						$query = "SELECT ID, user_login, user_nicename,display_name, user_email, user_url FROM " . $wpdb->prefix . "users WHERE ID IN ($placeholders)";
						$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));


					}

				}
				//comment logs
				elseif (isset($found_comment) && $found_comment) {
					global $wpdb;

					$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

					if (!empty($cat_ids)) {
						$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));

						// Query to get user information, including the ID
						$query = "SELECT comment_post_id, comment_author, comment_author_url,comment_content, comment_type FROM " . $wpdb->prefix . "comments WHERE comment_ID IN ($placeholders)";
						$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));


					}
				} else {
					global $wpdb;

					$cat_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM " . $wpdb->prefix . "summary WHERE event_id = %s", $get_event_key));

					if (!empty($cat_ids)) {
						$placeholders = implode(',', array_fill(0, count($cat_ids), '%d'));

						$query = "SELECT ID, post_type, post_title, post_content, post_excerpt, post_status, post_name, guid FROM " . $wpdb->prefix . "posts WHERE ID IN ($placeholders)";
						$results = $wpdb->get_results($wpdb->prepare($query, ...$cat_ids));

						$media_query = "SELECT post_id, associated_media, failed_media FROM " . $wpdb->prefix . "summary WHERE event_id = %s";
						$media_results = $wpdb->get_results($wpdb->prepare($media_query, $get_event_key), OBJECT_K);

						if ($media_results) {
							foreach ($results as $result) {
								if (isset($media_results[$result->ID])) {
									$result->associated_media = $media_results[$result->ID]->associated_media;
									$result->failed_media = $media_results[$result->ID]->failed_media;
								} else {
									$result->associated_media = null;
									$result->failed_media = null;
								}
							}
						} else {

							foreach ($results as $result) {
								$result->associated_media = null;
								$result->failed_media = null;
							}
						}
					} else {

						$results = [];
					}

				}


				$json_posts = wp_json_encode($results);
				$posts_array = json_decode($json_posts, true);

				if (empty($posts_array)) {
					$response['success'] = false;
					$response['message'] = 'No posts found or failed to decode JSON.';
					echo wp_json_encode($response);
					wp_die();
				}

				$csv_file = fopen('php://temp', 'w');
				if (!empty($posts_array)) {
					fputcsv($csv_file, array_keys($posts_array[0]));
				}

				foreach ($posts_array as $post) {
					fputcsv($csv_file, $post);
				}
				rewind($csv_file);

				$csv_contents = stream_get_contents($csv_file);
				fclose($csv_file);

				// Save the CSV data to the file
				file_put_contents($file_path, $csv_contents);

				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();
			}
		}
	}

	//failed media log download
	public function download_failed_log()
	{
		SecurityHelper::verify_ajax_nonce();
		if (!SecurityHelper::check_capability(SecurityHelper::can_download_logs())) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		global $wpdb;

		$response = [];
		$filename = sanitize_file_name($_POST['filename']);
		$revision = isset($_POST['revision']) ? sanitize_text_field($_POST['revision']) : '';
		$hash_key = isset($_POST['hashkey']) ? sanitize_text_field($_POST['hashkey']) : '';
		$module = sanitize_text_field($_POST['type']);
		if (empty($hash_key)) {
			$event_key_result = $wpdb->get_results($wpdb->prepare("SELECT eventKey FROM {$wpdb->prefix}smackuci_events WHERE revision = %d AND original_file_name = %s", $revision, $filename));
			$get_event_key = $event_key_result[0]->eventKey;

		} else {
			$get_event_key = $hash_key;
		}

		if (empty($get_event_key)) {
			$response['success'] = false;
			$response['message'] = 'Log not exists';
			echo wp_json_encode($response);
			wp_die();
		} else {

			$file_path = self::resolve_log_file_path( 'failed_media', $get_event_key );
			if ( false === $file_path ) {
				$response['success'] = false;
				$response['message'] = 'Log not exists';
				echo wp_json_encode($response);
				wp_die();
			}
			$file_url = self::get_authenticated_log_url( 'failed_media', $get_event_key );

			if (file_exists($file_path)) {
				// If the file already exists, return the authenticated download URL
				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();

			} else {

				if ($module == 'Media') {
					$results = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT  media_id, title,file_name, caption,description,alt_text,actual_url,status ,file_url
						 FROM " . $wpdb->prefix . "failed_media 
						 WHERE event_id = %s AND status = %s",
							$get_event_key,
							'failed'
						)
					);
				} else {
					$results = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT  media_id,title,post_id,actual_url,status
						 FROM " . $wpdb->prefix . "failed_media 
						 WHERE event_id = %s AND status = %s",
							$get_event_key,
							'failed'
						)
					);
				}

				$json_posts = wp_json_encode($results);
				$posts_array = json_decode($json_posts, true);

				if (empty($posts_array)) {
					$response['success'] = false;
					$response['message'] = 'No posts found or failed to decode JSON.';
					echo wp_json_encode($response);
					wp_die();
				}

				$csv_file = fopen('php://temp', 'w');
				if (!empty($posts_array)) {
					fputcsv($csv_file, array_keys($posts_array[0]));
				}

				foreach ($posts_array as $post) {
					fputcsv($csv_file, $post);
				}
				rewind($csv_file);

				$csv_contents = stream_get_contents($csv_file);
				fclose($csv_file);


				// Save the CSV data to the file
				file_put_contents($file_path, $csv_contents);

				$response['success'] = true;
				$response['file_url'] = $file_url;
				echo wp_json_encode($response);
				wp_die();
			}
		}
	}


	/**
	 * Saves event logs in database.
	 * @param  string $hash_key - File hash key
	 * @param  string $selected_type - Post type
	 * @param  string $file_name - File name
	 * @param  string $total_rows - Total rows in file
	 */
	public function manage_records($hash_key, $selected_type, $file_name, $total_rows)
	{
		global $wpdb;
		$file_name = sanitize_file_name($file_name);
		$log_table_name = $wpdb->prefix . "import_detail_log";

		$file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
		$file_extn = '.' . $file_extension;
		$get_local_filename = explode($file_extn, $file_name);
		$extension_object = new ExtensionHandler;
		$import_type = $extension_object->import_name_as($selected_type);

		$imported_on = date('Y-m-d h:i:s');
		$month = date("M", strtotime($imported_on));
		$year = date("Y", strtotime($imported_on));
		$file_path = '/smack_uci_uploads/imports/' . $hash_key . '/' . $hash_key;

		$get_name = $wpdb->get_results("SELECT original_file_name FROM {$wpdb->prefix}smackuci_events ");

		if (!empty($get_name)) {
			foreach ($get_name as $name_values) {
				$inserted_name_values[] = $name_values->original_file_name;
			}
			if (in_array($file_name, $inserted_name_values)) {
				$get_revision = $wpdb->get_results($wpdb->prepare("SELECT revision FROM {$wpdb->prefix}smackuci_events WHERE original_file_name = %s ", $file_name));
				foreach ($get_revision as $value) {
					$last_version_id = $value->revision;
				}
				$revision = $last_version_id + 1;
				$name = $get_local_filename[0] . '-' . $revision . $file_extn;
			} else {
				$name = $get_local_filename[0] . '-1' . $file_extn;
				$revision = 1;
			}
		} else {
			$name = $get_local_filename[0] . '-1' . $file_extn;
			$revision = 1;
		}

		$get_data = $wpdb->get_results($wpdb->prepare("SELECT skipped , created , updated,failed FROM $log_table_name WHERE hash_key = %s ", $hash_key));
		$skipped_count = $get_data[0]->skipped;
		$created_count = $get_data[0]->created;
		$updated_count = $get_data[0]->updated;
		$failed_count = $get_data[0]->failed;
		$processed = $created_count + $updated_count + $skipped_count;
		if ($processed > $total_rows)
			$processed = $created_count;

		$smack_uci_table = $wpdb->prefix . "smackuci_events";

		$getid = $wpdb->get_results($wpdb->prepare("SELECT distinct( id ) from {$wpdb->prefix}smackuci_events where import_type = %s and eventKey = %s", $import_type, $hash_key), ARRAY_A);
		if (!empty($getid)) {
			$wpdb->update(
				$smack_uci_table,
				array(
					'created' => "{$created_count}",
					'updated' => "{$updated_count}",
					'skipped' => "{$skipped_count}",
					'failed' => "{$failed_count}",
					'processed' => "{$processed}",
					'last_activity' => "{$imported_on}",
					'month' => $month,
					'year' => $year,
				),
				array('id' => $getid[0]['id'])
			);
		} else {
			$wpdb->insert(
				$smack_uci_table,
				array(
					'revision' => $revision,
					'name' => "{$name}",
					'original_file_name' => "{$file_name}",
					'import_type' => "{$import_type}",
					'filetype' => "{$file_extension}",
					'filepath' => "{$file_path}",
					'eventKey' => "{$hash_key}",
					'registered_on' => $imported_on,
					'processing' => 1,
					'count' => $total_rows,
					'processed' => $created_count,
					'created' => $created_count,
					'updated' => $updated_count,
					'skipped' => $skipped_count,
					'failed' => $failed_count,
					'last_activity' => $imported_on,
					'month' => $month,
					'year' => $year
				),
				array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s')
			);
		}

		if ( class_exists( '\Smackcoders\UCI\Core\Dashboard\DashboardService' ) ) {
			\Smackcoders\UCI\Core\Dashboard\DashboardService::bust_cache();
		}
	}
	/**
	 * insert log file log.
	 */
	public function Insert_log_details($data, $line_number, $hash_key)
	{
		if (empty($data)) {
			return null; // Exit function if data is empty
		}
		if (!isset($data[$line_number]) || !is_array($data[$line_number])) {
			$line_number = $line_number - 1;
			if (!isset($data[$line_number]) || !is_array($data[$line_number])) {
				return null;
			}
		}

		$upload_dir = WP_CONTENT_DIR . '/uploads/smack_uci_uploads/imports/import_logs/' . $hash_key . '/';

		if ( false === self::ensure_protected_log_directory( $upload_dir ) ) {
			return null;
		}

		$filePath = self::resolve_log_file_path( 'import_summary', $hash_key );
		if ( false === $filePath ) {
			return null;
		}
		$fileURL = self::get_authenticated_log_url( 'import_summary', $hash_key );

		$headers = array_keys($data[$line_number]);


		// Read existing CSV file and store lines
		$lines = [];
		if (file_exists($filePath)) {
			$file = fopen($filePath, 'r');
			while (($line = fgetcsv($file, 0, ',', '"', '\\')) !== FALSE) {
				$lines[] = $line;
			}
			fclose($file);
		} else {
			// If file does not exist, create header row
			$lines[] = $headers;
		}

		// Update the specific line numbers
		foreach ($data as $index => $row) {
			$new_line = [];
			foreach ($headers as $header) {
				$new_line[] = isset($row[$header]) ? $row[$header] : '';
			}
			$lines[$line_number + $index - 1] = $new_line;
		}

		// Write back the updated lines to the CSV file
		$file = fopen($filePath, 'w');
		if ($file) {
			foreach ($lines as $line) {
				fputcsv($file, $line);
			}
			fclose($file);
		}
		chmod($filePath, 0644);
		return isset($fileURL) ? $fileURL : null;
	}

	/**
	 * Starts the failed media download
	 */
	public function failedMediaExport($data, $line_number, $hash_key)
	{
		$file_path = self::resolve_log_file_path( 'import_failed_media', $hash_key );
		if ( false === $file_path ) {
			return null;
		}
		$upload_dir = trailingslashit( dirname( $file_path ) );
		$file_url   = self::get_authenticated_log_url( 'import_failed_media', $hash_key );
		$headers = ['post_id', 'title', 'media_id', 'actual_url'];

		if (empty($data)) {
			if (!file_exists($file_path) || !is_readable($file_path)) {
				return null;
			}

			$file_handle = fopen($file_path, 'r');
			if ($file_handle === false) {
				return null;
			}

			$data_found = false;
			while (($row = fgetcsv($file_handle, 0, ',', '"', '\\')) !== false) {
				if (!empty(array_filter($row))) {
					$data_found = true;
					break;
				}
			}
			fclose($file_handle);

			return $data_found ? $file_url : null;
		}

		if ( false === self::ensure_protected_log_directory( $upload_dir ) ) {
			return null;
		}

		$lines = [];
		if (file_exists($file_path)) {
			$file = fopen($file_path, 'r');
			while (($line = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
				$lines[] = $line;
			}
			fclose($file);
		} else {
			$lines[] = $headers;
		}

		foreach ($data as $index => $row) {
			$new_line = [];
			foreach ($headers as $header) {
				$new_line[] = isset($row[$header]) ? $row[$header] : '';
			}
			$lines[$line_number + $index - 1] = $new_line;
		}

		$file = fopen($file_path, 'w');
		foreach ($lines as $line) {
			fputcsv($file, $line);
		}
		fclose($file);

		chmod($file_path, 0644);

		return $file_url;
	}

	/**
	 * Starts the media download
	 */
	public function mediaExport($data, $line_number, $hash_key)
	{
		if (empty($data)) {
			return null; // Exit function if data is empty
		}
		if (!isset($data[$line_number]) || !is_array($data[$line_number])) {
			$line_number = $line_number - 1;
			if (!isset($data[$line_number]) || !is_array($data[$line_number])) {
				return null;
			}
		}
		$filePath = self::resolve_log_file_path( 'import_media', $hash_key );
		if ( false === $filePath ) {
			return null;
		}
		$fileURL = self::get_authenticated_log_url( 'import_media', $hash_key );
		$headers = array_keys($data[$line_number]);

		// Read existing CSV file and store lines
		$lines = [];
		if (file_exists($filePath)) {
			$file = fopen($filePath, 'r');
			while (($line = fgetcsv($file, 0, ',', '"', '\\')) !== FALSE) {
				$lines[] = $line;
			}
			fclose($file);
		} else {
			// If file does not exist, create header row
			$lines[] = $headers;
		}

		// Update the specific line numbers
		foreach ($data as $index => $row) {
			$new_line = [];
			foreach ($headers as $header) {
				$new_line[] = isset($row[$header]) ? $row[$header] : '';
			}
			$lines[$line_number + $index - 1] = $new_line;
		}

		// Write back the updated lines to the CSV file
		$file = fopen($filePath, 'w');
		foreach ($lines as $line) {
			fputcsv($file, $line);
		}
		fclose($file);

		chmod($filePath, 0644);
		return isset($fileURL) ? $fileURL : null;
	}

}
