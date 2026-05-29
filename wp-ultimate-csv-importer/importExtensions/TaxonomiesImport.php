<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class TaxonomiesImport {
    private static $taxonomies_instance = null;

    public static function getInstance() {
        
        if (TaxonomiesImport::$taxonomies_instance == null) {
            TaxonomiesImport::$taxonomies_instance = new TaxonomiesImport;
            return TaxonomiesImport::$taxonomies_instance;
        }
        return TaxonomiesImport::$taxonomies_instance;
    }
    public function taxonomies_import_function ($data_array, $mode, $importType, $unmatched_row, $check, $unikey_value, $unikey_name, $line_number, $header_array, $value_array, $update_based_on = 'normal', $duplicate_action = 'skip') {
		$returnArr = array();
		$mode_of_affect = 'Inserted';
		$update_based_on = in_array($update_based_on, array('normal', 'skip'), true) ? $update_based_on : 'normal';
		$duplicate_action = in_array($duplicate_action, array('skip', 'update', 'create'), true) ? $duplicate_action : 'skip';
		$term_match_fields = array('TERMID', 'termid', 'slug');
		global $wpdb;
		$helpers_instance = ImportHelpers::getInstance();
		$core_instance = CoreFieldsImport::getInstance();
		$media_instance = MediaHandling::getInstance();
		global $core_instance;

		$log_table_name = $wpdb->prefix ."import_detail_log";
		$events_table = $wpdb->prefix."em_meta" ;

		$updated_row_counts = $helpers_instance->update_count($unikey_value , $unikey_name);
		$created_count = $updated_row_counts['created'];
		$updated_count = $updated_row_counts['updated'];
		$skipped_count = $updated_row_counts['skipped'];
		
		$terms_table = $wpdb->term_taxonomy;
        //$taxonomy = $importAs;
        $taxonomy = $importType;
		
		$term_children_options = get_option("$taxonomy" . "_children");
		$_name = isset($data_array['name']) ? $data_array['name'] : '';
		$_slug = isset($data_array['slug']) ? $data_array['slug'] : '';
		$_desc = isset($data_array['description']) ? $data_array['description'] : '';
		$_image = isset($data_array['image']) ? $data_array['image'] : '';
		$_parent = isset($data_array['parent']) ? $data_array['parent'] : '';
		$_display_type = isset($data_array['display_type']) ? $data_array['display_type'] : '';
		$_color = isset($data_array['color']) ? $data_array['color'] : '';
		$_top_content = isset($data_array['top_content']) ? $data_array['top_content'] : '';
		$_bottom_content = isset($data_array['bottom_content']) ? $data_array['bottom_content'] : '';

		$get_category_list = array();
		// if (strpos($_name, ',') !== false) {
		// 	$get_category_list = explode(',', $_name);
		// }
		 if (strpos($_name, '>') !== false) {
			$get_category_list = explode('>', $_name);
		} else {
			$get_category_list[] = trim($_name);
		}

		$parent_term_id = 0;
		$termID = '';
	
		if (count($get_category_list) == 1) {
			$_name = trim($get_category_list[0]);
			if($_parent){
				$get_parent = term_exists("$_parent", "$taxonomy");
				$parent_term_id = $get_parent['term_id'];
			}
			else{
				// $termid_value = $wpdb->get_results("SELECT term_id FROM {$wpdb->prefix}terms WHERE slug = '$_slug'");
				$termid_value = $wpdb->get_results($wpdb->prepare("SELECT term.term_id FROM {$wpdb->prefix}terms AS term INNER JOIN {$wpdb->prefix}term_taxonomy AS tax ON term.term_id = tax.term_id WHERE term.slug = %s AND tax.taxonomy = %s", $_slug, $taxonomy));
				if(isset($termid_value[0]->term_id)){
					$termid_val = $termid_value[0]->term_id;
					$term_parent_value = $wpdb->get_results("SELECT parent FROM {$wpdb->prefix}term_taxonomy WHERE term_id = '$termid_val'");
					$parent_term_id = $term_parent_value[0]->parent;
				}
			}
		} else {
			$count = count($get_category_list);
			$_name = trim($get_category_list[$count - 1]);
			$checkParent = trim($get_category_list[$count - 2]);
			$parent_term = term_exists("$checkParent", "$taxonomy");
			$parent_term_id = $parent_term['term_id'];
		}
		$existing_id = $this->find_existing_term_id($data_array, $check, $taxonomy);
		$has_match = $existing_id > 0;
		$duplicate_handling_active = (
			$update_based_on === 'normal'
			&& !empty($check)
			&& in_array($check, $term_match_fields, true)
		);

		if ($update_based_on === 'skip' && !empty($check) && in_array($check, $term_match_fields, true) && !$has_match) {
			$core_instance->detailed_log[$line_number]['Message'] = 'Skipped. No matching record found.';
			$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
			$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
			$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
			return array('MODE' => $mode);
		}

		if ($duplicate_handling_active && $has_match && $duplicate_action === 'skip') {
			$core_instance->detailed_log[$line_number]['Message'] = 'Skipped, Due to duplicate Term found!.';
			$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
			$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
			$core_instance->detailed_log[$line_number]['id'] = $existing_id;
			$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
			return array('MODE' => $mode, 'ID' => $existing_id);
		}

		$run_insert = false;
		$run_update = false;
		if ($duplicate_handling_active && $has_match && $duplicate_action === 'update') {
			$run_update = true;
		} elseif ($duplicate_handling_active && $has_match && $duplicate_action === 'create') {
			$run_insert = true;
		} elseif ($mode === 'Update' && $has_match) {
			$run_update = true;
		} elseif ($mode === 'Update' && !$has_match && $update_based_on === 'skip') {
			$core_instance->detailed_log[$line_number]['Message'] = 'Skipped. No matching record found.';
			$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
			$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
			$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
			return array('MODE' => $mode);
		} else {
			$run_insert = true;
		}

		$termID = '';
		if ($run_update) {
			$termID = $has_match ? $existing_id : 0;
			$result = $this->update_existing_term($termID, $_name, $_slug, $_desc, $parent_term_id, $taxonomy, $importType, $data_array, $_image, $_display_type, $line_number, $log_table_name, $unikey_name, $unikey_value, $updated_count, $header_array, $value_array, $media_instance);
			if ($result === false) {
				$core_instance->detailed_log[$line_number]['Message'] = 'Skipped, Due to duplicate Term update failed!.';
				$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
				$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
				$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
				return array('MODE' => $mode);
			}
			$termID = $result['ID'];
			$mode_of_affect = $result['MODE'];
			$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);
		} elseif ($run_insert) {
			$result = $this->insert_new_term($_name, $_slug, $_desc, $parent_term_id, $taxonomy, $importType, $data_array, $_image, $_display_type, $line_number, $log_table_name, $unikey_name, $unikey_value, $created_count, $skipped_count, $header_array, $value_array, $media_instance);
			if ($result === false) {
				return array('MODE' => $mode);
			}
			$termID = $result['ID'];
			$mode_of_affect = $result['MODE'];
			$returnArr = array('ID' => $termID, 'MODE' => $mode_of_affect);
		}

		if ($run_insert && $unmatched_row == 'true' && !empty($termID)) {
				global $wpdb;
				$post_entries_table = $wpdb->prefix ."post_entries_table";
				$file_table_name = $wpdb->prefix."smackcsv_file_events";
				$get_id  = $wpdb->get_results( "SELECT file_name  FROM $file_table_name WHERE $unikey_name = '$unikey_value'");	
				$file_name = $get_id[0]->file_name;
				$wpdb->get_results("INSERT INTO $post_entries_table (`ID`,`type`, `file_name`,`status`) VALUES ( '{$termID}','{$importType}', '{$file_name}','Inserted')");
			}

		if(!empty($termID) && !is_wp_error($termID)) {
			update_option("$taxonomy" . "_children", $term_children_options);
			delete_option($taxonomy . "_children");
		}
		return $returnArr;
    }

	private function find_existing_term_id($data_array, $check, $taxonomy) {
		if ($check === 'TERMID' || $check === 'termid') {
			$term_id = isset($data_array['TERMID']) ? trim((string) $data_array['TERMID']) : '';
			if ($term_id === '' || !is_numeric($term_id)) {
				return 0;
			}
			$term_id = absint($term_id);
			if ($term_id <= 0) {
				return 0;
			}
			$term = get_term($term_id, $taxonomy);
			return ($term && !is_wp_error($term)) ? (int) $term->term_id : 0;
		}
		if ($check === 'slug') {
			$slug = isset($data_array['slug']) ? trim((string) $data_array['slug']) : '';
			if ($slug === '') {
				return 0;
			}
			$term = get_term_by('slug', $slug, $taxonomy);
			return ($term && !is_wp_error($term)) ? (int) $term->term_id : 0;
		}
		return 0;
	}

	private function apply_term_meta($termID, $importType, $taxonomy, $data_array, $_image, $_display_type, $header_array, $value_array, $media_instance) {
		if (!empty($_image)) {
			$media_instance->store_image_ids($i = 1);
			$imageid = $media_instance->media_handling($_image, $termID, $data_array, '', '', '', $header_array, $value_array);
			if ($importType === 'product_cat' || $taxonomy === 'product_cat') {
				update_term_meta($termID, 'thumbnail_id', $imageid);
			}
		}
		if (!empty($_display_type)) {
			update_term_meta($termID, 'display_type', $_display_type);
		}
	}

	private function insert_new_term($_name, $_slug, $_desc, $parent_term_id, $taxonomy, $importType, $data_array, $_image, $_display_type, $line_number, $log_table_name, $unikey_name, $unikey_value, $created_count, $skipped_count, $header_array, $value_array, $media_instance) {
		global $wpdb, $core_instance;
		$terms_table = $wpdb->term_taxonomy;
		$taxoID = wp_insert_term($_name, $taxonomy, array('description' => $_desc, 'slug' => $_slug));
		if (is_wp_error($taxoID)) {
			$core_instance->detailed_log[$line_number]['Message'] = "Can't insert this " . $taxonomy . '. ' . $taxoID->get_error_message();
			$core_instance->detailed_log[$line_number]['state'] = 'Skipped';
			$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
			$wpdb->get_results("UPDATE $log_table_name SET skipped = $skipped_count WHERE $unikey_name = '$unikey_value'");
			return false;
		}
		$termID = $taxoID['term_id'];
		$this->apply_term_meta($termID, $importType, $taxonomy, $data_array, $_image, $_display_type, $header_array, $value_array, $media_instance);
		if (isset($parent_term_id)) {
			$wpdb->get_results("UPDATE $terms_table SET `parent` = $parent_term_id WHERE `term_id` = $termID ");
		}
		$core_instance->detailed_log[$line_number]['Message'] = 'Inserted ' . $taxonomy . ' ID: ' . $termID;
		$core_instance->detailed_log[$line_number]['state'] = 'Inserted';
		$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
		$core_instance->detailed_log[$line_number]['id'] = $termID;
		$wpdb->get_results("UPDATE $log_table_name SET created = $created_count WHERE $unikey_name = '$unikey_value'");
		return array('ID' => $termID, 'MODE' => 'Inserted');
	}

	private function update_existing_term($termID, $_name, $_slug, $_desc, $parent_term_id, $taxonomy, $importType, $data_array, $_image, $_display_type, $line_number, $log_table_name, $unikey_name, $unikey_value, $updated_count, $header_array, $value_array, $media_instance) {
		global $wpdb, $core_instance;
		$terms_table = $wpdb->term_taxonomy;
		if ($termID <= 0) {
			return false;
		}
		$args = array(
			'name' => $_name,
			'description' => $_desc,
		);
		if ($_slug !== '') {
			$args['slug'] = $_slug;
		}
		if (isset($parent_term_id)) {
			$args['parent'] = (int) $parent_term_id;
		}
		$updated = wp_update_term($termID, $taxonomy, $args);
		if (is_wp_error($updated)) {
			return false;
		}
		$this->apply_term_meta($termID, $importType, $taxonomy, $data_array, $_image, $_display_type, $header_array, $value_array, $media_instance);
		$core_instance->detailed_log[$line_number]['Message'] = 'Updated ' . $taxonomy . ' ID: ' . $termID;
		$core_instance->detailed_log[$line_number]['state'] = 'Updated';
		$core_instance->detailed_log[$line_number]['cat_name'] = $_name;
		$core_instance->detailed_log[$line_number]['id'] = $termID;
		$wpdb->get_results("UPDATE $log_table_name SET updated = $updated_count WHERE $unikey_name = '$unikey_value'");
		return array('ID' => $termID, 'MODE' => 'Updated');
	}
}
    