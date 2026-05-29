<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class CommentsImport {
    private static $comments_instance = null;

    public static function getInstance() {
		
		if (CommentsImport::$comments_instance == null) {
			CommentsImport::$comments_instance = new CommentsImport;
			return CommentsImport::$comments_instance;
		}
		return CommentsImport::$comments_instance;
    }

    public function comments_import_function($data_array, $mode, $unikey_value, $unikey_name, $line_number, $type, $check = '', $update_based_on = 'normal', $duplicate_action = 'skip') {

		global $wpdb;
		$core_instance = CoreFieldsImport::getInstance();
		global $core_instance;
		$helpers_instance = ImportHelpers::getInstance();
		$log_table_name = $wpdb->prefix ."import_detail_log";
		$returnArr = array();

		$update_based_on = in_array($update_based_on, array('normal', 'skip'), true) ? $update_based_on : 'normal';
		$duplicate_action = in_array($duplicate_action, array('skip', 'update', 'create'), true) ? $duplicate_action : 'skip';
		$comment_match_fields = array('comment_ID');

		$updated_row_counts = $helpers_instance->update_count($unikey_value,$unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];

		//To avoid invalid statements and scripts
		// if(isset($data_array['comment_content'])){
		// 	$data_array['comment_content'] = esc_textarea($data_array['comment_content']);
		// }
		$allowed_html = ['div' => ['class' => true, 'id' => true, 'style' => true, ], 
		'a' => ['id' => true, 'href' => true, 'title' => true, 'target' => true, 'class' => true, 'style' => true, 'onclick' => true,], 
		'strong' => [], 
		'i' => ['id' => true, 'onclick' => true, 'style' => true, 'class' => true, 'aria-hidden' => true, 'title' => true ], 
		'p' => ['style' => true, 'name' => true, 'id' => true, ], 
		'img' => ['id' => true, 'style' => true, 'class' => true, 'src' => true, 'align' => true, 'src' => true, 'width' => true, 'height' => true, 'border' => true, ], 
		'table' => ['id' => true, 'class' => true, 'style' => true, 'height' => true, 'cellspacing' => true, 'cellpadding' => true, 'border' => true, 'width' => true, 'align' => true, 'background' => true, 'frame' => true, 'rules' => true, ], 
		'tbody' => [], 
		'br' => ['bogus' => true, ], 
		'tr' => ['id' => true, 'class' => true, 'style' => true, ], 
		'th' => ['id' => true, 'class' => true, 'style' => true, ], 
		'hr' => ['id' => true, 'class' => true, 'style' => true,], 
		'h3' => ['style' => true, ], 
		'td' => ['style' => true, 'id' => true, 'align' => true, 'width' => true, 'valign' => true, 'class' => true, 'colspan' => true, ], 
		'span' => ['style' => true, 'class' => true, ], 
		'h1' => ['style' => true, ], 
		'thead' => [], 
		'tfoot' => ['id' => true, 'style' => true, ], 
		'figcaption' => ['id' => true, 'style' => true, ], 
		'h4' => ['id' => true, 'align' => true, 'style' => true, ],
		'h2' => ['id' => true, 'align' => true, 'style' => true, 'class' => true],
		'select' => ['id' => true, 'name' => true, 'class' => true, 'data-size' =>true, 'data-live-search' =>true],
		'option' => ['value' => true, 'selected' => true],
		'label' =>['id' => true, 'class' =>true],
		'input' => ['type' => true, 'value' => true, 'id' => true, 'name' => true, 'class' => true],
		'form' => ['method' => true, 'name' => true, 'id' => true, 'action' => true]];

		if(isset($data_array['comment_content'])){
			$content = preg_replace('/<script>.+?<\/script>/i',"",$data_array['comment_content']);
			$data_array['comment_content'] = wp_kses($content,$allowed_html);
		}
			
		$commentid = '';
		$post_id = isset($data_array['comment_post_ID']) ? $data_array['comment_post_ID'] :'';
		$post_exists = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}posts WHERE id = '" . $post_id . "' and post_status in ('publish','draft','future','private','pending')", ARRAY_A);
		$valid_status = array('1', '0', 'spam');
		if(empty($data_array['comment_approved'])) {
			$data_array['comment_approved'] = 0;
		}
		if(!in_array($data_array['comment_approved'], $valid_status)) {
			$data_array['comment_approved'] = 0;
		}
		$data_array['comment_approved'] = trim($data_array['comment_approved']);
		if(!empty($data_array['user_id'])){
			$user_login=$data_array['user_id'];
				$u_id =  $wpdb->get_results("SELECT ID FROM {$wpdb->prefix}users WHERE  user_login = '$user_login'");		
			foreach($u_id as $user_id){
				$users=$user_id->ID;
				$data_array['user_id']=$users;
			}
			}
			if($type == 'WooCommerce Reviews'){
				$data_array['comment_type'] = 'review';
			}
			if ($post_exists) {
			$existing_id = ($type === 'Comments') ? $this->find_existing_comment_id($data_array, $check) : 0;
			$has_match = $existing_id > 0;
			$duplicate_handling_active = (
				$type === 'Comments'
				&& $update_based_on === 'normal'
				&& !empty($check)
				&& in_array($check, $comment_match_fields, true)
			);

			if ($type === 'Comments' && $update_based_on === 'skip' && !empty($check) && in_array($check, $comment_match_fields, true) && !$has_match) {
				$core_instance->detailed_log[$line_number]['Message'] = 'Skipped. No matching record found.';
				$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
				$returnArr['MODE'] = $mode;
				return $returnArr;
			}

			if ($duplicate_handling_active && $has_match && $duplicate_action === 'skip') {
				$core_instance->detailed_log[$line_number]['Message'] = 'Skipped, Due to duplicate Comment found!.';
				$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
				$core_instance->detailed_log[$line_number]['id'] = $existing_id;
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
				$returnArr['MODE'] = $mode;
				$returnArr['ID'] = $existing_id;
				return $returnArr;
			}

			$retID = 0;
			$mode_of_affect = '';

			if ($duplicate_handling_active && $has_match && $duplicate_action === 'update') {
				$result = $this->update_existing_comment($data_array, $existing_id, $line_number, $log_table_name, $unikey_name, $unikey_value, $updated_count);
				if ($result === false) {
					$core_instance->detailed_log[$line_number]['Message'] = 'Skipped, Due to duplicate Comment update failed!.';
					$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
					$returnArr['MODE'] = $mode;
					return $returnArr;
				}
				$retID = $result['ID'];
				$mode_of_affect = $result['MODE'];
			} elseif ($duplicate_handling_active && $has_match && $duplicate_action === 'create') {
				$result = $this->insert_new_comment($data_array, $line_number, $log_table_name, $unikey_name, $unikey_value, $created_count, $skipped_count);
				if ($result === false) {
					$returnArr['MODE'] = $mode;
					return $returnArr;
				}
				$retID = $result['ID'];
				$mode_of_affect = $result['MODE'];
			} elseif ($mode === 'Update' && $has_match) {
				$result = $this->update_existing_comment($data_array, $existing_id, $line_number, $log_table_name, $unikey_name, $unikey_value, $updated_count);
				if ($result === false) {
					$core_instance->detailed_log[$line_number]['Message'] = 'Skipped, Due to duplicate Comment update failed!.';
					$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
					$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
					$returnArr['MODE'] = $mode;
					return $returnArr;
				}
				$retID = $result['ID'];
				$mode_of_affect = $result['MODE'];
			} elseif ($mode === 'Update' && !$has_match && $update_based_on === 'skip') {
				$core_instance->detailed_log[$line_number]['Message'] = 'Skipped. No matching record found.';
				$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
				$returnArr['MODE'] = $mode;
				return $returnArr;
			} elseif ($mode === 'Update' && !$has_match && empty($check)) {
				$ID_result = $wpdb->get_results("SELECT comment_ID FROM {$wpdb->prefix}comments WHERE comment_post_ID = $post_id order by comment_ID DESC ");
				if (is_array($ID_result) && !empty($ID_result)) {
					$result = $this->update_existing_comment($data_array, $ID_result[0]->comment_ID, $line_number, $log_table_name, $unikey_name, $unikey_value, $updated_count);
					if ($result === false) {
						$returnArr['MODE'] = $mode;
						return $returnArr;
					}
					$retID = $result['ID'];
					$mode_of_affect = $result['MODE'];
				} else {
					$result = $this->insert_new_comment($data_array, $line_number, $log_table_name, $unikey_name, $unikey_value, $created_count, $skipped_count);
					if ($result === false) {
						$returnArr['MODE'] = $mode;
						return $returnArr;
					}
					$retID = $result['ID'];
					$mode_of_affect = $result['MODE'];
				}
			} elseif ($mode === 'Update' && !$has_match) {
				$result = $this->insert_new_comment($data_array, $line_number, $log_table_name, $unikey_name, $unikey_value, $created_count, $skipped_count);
				if ($result === false) {
					$returnArr['MODE'] = $mode;
					return $returnArr;
				}
				$retID = $result['ID'];
				$mode_of_affect = $result['MODE'];
			} else {
				$result = $this->insert_new_comment($data_array, $line_number, $log_table_name, $unikey_name, $unikey_value, $created_count, $skipped_count);
				if ($result === false) {
					$returnArr['MODE'] = $mode;
					return $returnArr;
				}
				$retID = $result['ID'];
				$mode_of_affect = $result['MODE'];
			}
			if(isset($data_array['comment_rating'])){
				$rating_range = range(1,5);
				if(in_array($data_array['comment_rating'], $rating_range)){
					update_comment_meta($retID ,'rating', $data_array['comment_rating']);
				}
			}
		}else {
			$retID = $commentid;
			$core_instance->detailed_log[$line_number]['Message'] = "Skipped, Due to unknown post ID.";
			$fields = $wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
		}
			
		$returnArr['ID'] = $retID;
		$returnArr['MODE'] = isset($mode_of_affect) ? $mode_of_affect :'';
		return $returnArr;
		}

	private function find_existing_comment_id($data_array, $check) {
		if ($check !== 'comment_ID') {
			return 0;
		}
		$comment_id = isset($data_array['comment_ID']) ? trim((string) $data_array['comment_ID']) : '';
		if ($comment_id === '' || !is_numeric($comment_id)) {
			return 0;
		}
		$comment_id = absint($comment_id);
		if ($comment_id <= 0) {
			return 0;
		}
		$comment = get_comment($comment_id);
		return $comment ? (int) $comment->comment_ID : 0;
	}

	private function prepare_comment_date(&$data_array) {
		if (empty($data_array['comment_date'])) {
			$data_array['comment_date'] = current_time('mysql', 0);
		} else {
			$timestamp = strtotime($data_array['comment_date']);
			$data_array['comment_date'] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : current_time('mysql', 0);
		}
	}

	private function resolve_comment_user_id(&$data_array) {
		global $wpdb;
		if (empty($data_array['user_id'])) {
			return;
		}
		if (is_numeric($data_array['user_id'])) {
			$data_array['user_id'] = absint($data_array['user_id']);
			return;
		}
		$user_login = $data_array['user_id'];
		$user_row = $wpdb->get_row($wpdb->prepare(
			"SELECT ID FROM {$wpdb->prefix}users WHERE user_login = %s LIMIT 1",
			$user_login
		));
		if ($user_row) {
			$data_array['user_id'] = (int) $user_row->ID;
		} else {
			unset($data_array['user_id']);
		}
	}

	private function prepare_comment_payload($data_array, $comment_id = 0) {
		$this->resolve_comment_user_id($data_array);
		$this->prepare_comment_date($data_array);
		$allowed = array(
			'comment_post_ID',
			'comment_author',
			'comment_author_email',
			'comment_author_url',
			'comment_content',
			'comment_author_IP',
			'comment_date',
			'comment_approved',
			'comment_parent',
			'user_id',
			'comment_type',
		);
		$payload = array();
		foreach ($allowed as $field) {
			if (array_key_exists($field, $data_array)) {
				$payload[$field] = $data_array[$field];
			}
		}
		if ($comment_id > 0) {
			$payload['comment_ID'] = $comment_id;
		}
		return $payload;
	}

	private function insert_new_comment($data_array, $line_number, $log_table_name, $unikey_name, $unikey_value, $created_count, $skipped_count) {
		global $wpdb, $core_instance;
		$payload = $this->prepare_comment_payload($data_array, 0);
		$retID = wp_insert_comment($payload);
		if (is_wp_error($retID) || $retID == '') {
			$core_instance->detailed_log[$line_number]['Message'] = 'Skipped, Due to unknown post ID.';
			$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
			$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
			return false;
		}
		$core_instance->detailed_log[$line_number]['Message'] = 'Inserted Comment ID: ' . $retID;
		$core_instance->detailed_log[$line_number]['id'] = $retID;
		$core_instance->detailed_log[$line_number]['state'] = 'Inserted';
		$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey_value'");
		return array('ID' => $retID, 'MODE' => 'Inserted');
	}

	private function update_existing_comment($data_array, $comment_id, $line_number, $log_table_name, $unikey_name, $unikey_value, $updated_count) {
		global $wpdb, $core_instance;
		$payload = $this->prepare_comment_payload($data_array, $comment_id);
		$updated = wp_update_comment($payload);
		// wp_update_comment returns 1 when rows changed, 0 when unchanged — both are success.
		if ($updated === false || is_wp_error($updated)) {
			return false;
		}
		$core_instance->detailed_log[$line_number]['Message'] = 'Updated Comment ID: ' . $comment_id;
		$core_instance->detailed_log[$line_number]['id'] = $comment_id;
		$core_instance->detailed_log[$line_number]['state'] = 'Updated';
		$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey_value'");
		return array('ID' => $comment_id, 'MODE' => 'Updated');
	}
		
		public function menu_import_function($data_array , $mode , $unikey_value,$unikey_name , $line_number){
			global $wpdb;
		
			$menu_title = $data_array['menu_title'];
			$check_term_exists = term_exists($menu_title, 'nav_menu');

			if(is_array($check_term_exists)){
				$insert_term_id = $check_term_exists['term_id'];
				$insert_taxo_id = $check_term_exists['term_taxonomy_id'];
			}
			else{
				$insert_term = wp_insert_term($menu_title, 'nav_menu');
				$insert_term_id = $insert_term['term_id'];
				$insert_taxo_id = $insert_term['term_taxonomy_id'];
			}
			
			$menu_item_types = explode(',', $data_array['_menu_item_type']);
			$menu_item_objects = explode(',', $data_array['_menu_item_object']);
			$menu_item_objects_ids = explode(',', $data_array['_menu_item_object_id']);
			$menu_item_urls = explode(',', $data_array['_menu_item_url']);

			$temp = 0;
			foreach($menu_item_types as $menu_types){

				$menu_object_titles = $menu_item_objects_ids[$temp];
				$menu_objects = $menu_item_objects[$temp];
	
				if($menu_types == 'custom'){
					$post_title = $menu_object_titles;
				}
				else{
					$post_title = '';
				}

				// posts table entry
				$nav_post_arr = array(
							'post_title' => $post_title,
							'post_status' => 'publish',
							'post_type' => 'nav_menu_item',
							'menu_order' => $temp + 1
						);

				$inserted_post_id = wp_insert_post($nav_post_arr);

				if($menu_types == 'post_type'){
					$post_title_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE post_title = '$menu_object_titles' AND post_type = '$menu_objects' AND  post_status = 'publish' ");
				}
				elseif($menu_types == 'taxonomy'){
					$get_menu_term_id = get_term_by('name', $menu_object_titles, $menu_objects);
					$post_title_id = $get_menu_term_id->term_id;
				}
				else{
					$post_title_id = $inserted_post_id;
				}

				// postmeta table entry
				update_post_meta($inserted_post_id, '_menu_item_type', $menu_types);
				update_post_meta($inserted_post_id, '_menu_item_menu_item_parent', 0);
				update_post_meta($inserted_post_id, '_menu_item_object_id', $post_title_id);
				update_post_meta($inserted_post_id, '_menu_item_object', $menu_objects);
				update_post_meta($inserted_post_id, '_menu_item_target', '');
				update_post_meta($inserted_post_id, '_menu_item_classes', 'a:1:{i:0;s:0:"";}');
				update_post_meta($inserted_post_id, '_menu_item_xfn', '');
				update_post_meta($inserted_post_id, '_menu_item_url', $menu_item_urls[$temp]);

				// terms relationship table entry
				$wpdb->insert($wpdb->prefix.'term_relationships',
						array('object_id' => $inserted_post_id,
									'term_taxonomy_id' => $insert_taxo_id
						),
						array('%d','%d')
				);

				$temp++;
			}

			$menu_auto_add = $data_array['menu_auto_add'];
		
			$get_auto_add = get_option("nav_menu_options");
        foreach($get_auto_add as $auto_key => $auto_value){
            if($auto_key == 'auto_add'){
                if(empty($auto_value)){
									if($menu_auto_add == 'yes'){
										$get_auto_add['auto_add'] = array($insert_term_id);
										update_option("nav_menu_options", $get_auto_add);
									}
                }
                else{
									if(!in_array($insert_term_id , $auto_value) && $menu_auto_add == 'yes'){
                    array_push($auto_value, $insert_term_id);
										$get_auto_add['auto_add'] = $auto_value;
										update_option("nav_menu_options", $get_auto_add);
									}
                }
            }
				}
				
			$data_array_copy = $data_array;
			$exclude_keys = array('menu_title', '_menu_item_type', '_menu_item_object', '_menu_item_object_id', '_menu_item_url', 'menu_auto_add');
			foreach($exclude_keys as $exclude_key){
				unset($data_array_copy[$exclude_key]);
			}

			foreach($data_array_copy as $data_key => $data_value){
				if($data_value == 'yes'){
					$locations = get_theme_mod( 'nav_menu_locations' );
					$locations[$data_key] = $insert_term_id;
					set_theme_mod ( 'nav_menu_locations', $locations );
				}
			}
		}


		public function widget_import_function($post_values , $mode ,$unikey_value,$unikey_name , $line_number){
			
				foreach($post_values as $post_widget_key => $post_widget_value){
					if(!empty($post_widget_value)){
						$get_widget_id = explode('widget_', $post_widget_key);
						$get_total_posts = explode('|', $post_widget_value);
						foreach($get_total_posts as $per_post){

							$get_post_footer = explode('->', $per_post);
							$post_footer_number = $get_post_footer[1];
							$sidebar = 'sidebar-'.$post_footer_number;

							$get_post_details = explode(',', $get_post_footer[0]);
							
							$widget_data = [];

							if($post_widget_key == 'widget_recent-posts'){
								$widget_data['title'] = $get_post_details[0];
								$widget_data['number'] = $get_post_details[1];
								$widget_data['show_date'] = $get_post_details[2];
							}
							elseif($post_widget_key == 'widget_pages'){
								$widget_data['title'] = $get_post_details[0];
								$widget_data['sortby'] = $get_post_details[1];

								$exclude_ids = str_replace('/', ',', $get_post_details[2]);
								$widget_data['exclude'] = $exclude_ids;
							}
							elseif($post_widget_key == 'widget_recent-comments'){
								$widget_data['title'] = $get_post_details[0];
								$widget_data['number'] = $get_post_details[1];
							}
							elseif($post_widget_key == 'widget_archives'){
								$widget_data['title'] = $get_post_details[0];
								$widget_data['count'] = $get_post_details[1];
								$widget_data['dropdown'] = $get_post_details[2];
							}
							elseif($post_widget_key == 'widget_categories'){
								$widget_data['title'] = $get_post_details[0];
								$widget_data['count'] = $get_post_details[1];
								$widget_data['hierarchical'] = $get_post_details[2];
								$widget_data['dropdown'] = $get_post_details[3];
							}

							$this->insert_widget_in_sidebar( $get_widget_id[1], $widget_data, $sidebar );
						}	
					}
				}
		}

		public function insert_widget_in_sidebar( $widget_id, $widget_data, $sidebar ) {
			// Retrieve sidebars, widgets and their instances
			$sidebars_widgets = get_option( 'sidebars_widgets', array() );
			$widget_instances = get_option( 'widget_' . $widget_id, array() );
		
			// Retrieve the key of the next widget instance
			$numeric_keys = array_filter( array_keys( $widget_instances ), 'is_int' );
			//$next_key = $numeric_keys ? max( $numeric_keys ) + 1 : 2;
		
			if((count($numeric_keys) == 1) && (empty($widget_instances[$numeric_keys[0]]['title']))){
				$next_key = $numeric_keys[0];
			}else{
				$next_key = max( $numeric_keys ) + 1;
			}
			
		
			// Add this widget to the sidebar
			if ( ! isset( $sidebars_widgets[ $sidebar ] ) ) {
				$sidebars_widgets[ $sidebar ] = array();
			}

			$sidebar_key_id = $widget_id . '-' . $next_key;
			if(!in_array($sidebar_key_id, $sidebars_widgets[ $sidebar ])){
				$sidebars_widgets[ $sidebar ][] = $sidebar_key_id;
			}
		
			// Add the new widget instance
			$widget_instances[ $next_key ] = $widget_data;
		
			// Store updated sidebars, widgets and their instances
			update_option( 'sidebars_widgets', $sidebars_widgets );
			update_option( 'widget_' . $widget_id, $widget_instances );
		}
}