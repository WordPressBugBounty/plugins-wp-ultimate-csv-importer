<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

class TermsandTaxonomiesImport {
	private static $terms_taxo_instance = null;

    public static function getInstance() {
		
		if (TermsandTaxonomiesImport::$terms_taxo_instance == null) {
			TermsandTaxonomiesImport::$terms_taxo_instance = new TermsandTaxonomiesImport;
			return TermsandTaxonomiesImport::$terms_taxo_instance;
		}
		return TermsandTaxonomiesImport::$terms_taxo_instance;
    }
    function set_terms_taxo_values($header_array ,$value_array , $map, $post_id , $type, $mode , $line_number,$poly_array = null){
		$post_values = [];
		$helpers_instance = ImportHelpers::getInstance();
		$post_values = $helpers_instance->get_header_values($map , $header_array , $value_array);
		$poly_values = $helpers_instance->get_header_values($poly_array , $header_array , $value_array);
		$this->terms_taxo_import_function($post_values,$type, $post_id , $mode , $line_number,$poly_values);
	
    }

    public function terms_taxo_import_function ($data_array, $type ,$pID , $mode , $line_number,$poly_values) {
		$core_instance = CoreFieldsImport::getInstance();
		$helpers_instance = ImportHelpers::getInstance();
		global $core_instance;
		if ($type == 'WooCommerce Product') {
			$raw_cat = $data_array['product_category'] ?? '';

			// IMPORTANT: Do not force Uncategorized here; WooCommerce will default it when no categories are assigned.
			// For Update mode, if user didn't map category, preserve existing instead of forcing Uncategorized.
			if ($mode == 'Update' && empty($raw_cat)) {
				$existing_names = wp_get_post_terms($pID, 'product_cat', array('fields' => 'names'));
				$data_array['product_category'] = !empty($existing_names) ? implode(', ', $existing_names) : '';
			}

			if (empty($data_array['product_category'])) {
				// Leave empty to let WooCommerce default behavior apply.
			}
		}
		unset($data_array['post_format']);
		unset($data_array['product_type']);
		$categories = $tags = array();
		foreach ($data_array as $termKey => $termVal) {
			$smack_taxonomy = array();
			switch ($termKey) {
				case 'post_category' :
					$categories [$termKey] = $data_array [$termKey];

					if(preg_match("(Can't|Skipped|Duplicate)", $core_instance->detailed_log[$line_number]['Message']) === 0) {  
						$core_instance->detailed_log[$line_number]['Categories'] = $data_array[$termKey];
					}

                    $category_name = 'category';

                    if($mode == 'Update'){
                        $categories_before = wp_get_object_terms($pID, 'category');
                    
                        foreach($categories_before as $category_before){
                            wp_remove_object_terms($pID, $category_before->name , 'category');
                            
                        }
                    }

					// Create / Assign categories to the post types
					if(isset($categories[$termKey]) && $categories[$termKey] != '')
						$this->assignTermsAndTaxonomies($categories, $category_name, $pID,$poly_values);
					//Get Default Category id
                    $default_category_id = get_option('default_category');
                   
					//Get Default Category Name
                    $default_category_details = get_term_by('id', $default_category_id , 'category');
                    
					//Remove Default Category
                    $categories = wp_get_object_terms($pID, 'category');					
            
					if (count($categories) > 1) {
						foreach ($categories as $key => $category) {							
							if ((!empty($category) && !empty($default_category_details)) && $category->name == $default_category_details->name ) {
								wp_remove_object_terms($pID, $default_category_details->name , 'category');
							}
						}
					}
					break;
				case 'post_tag' :
					$tags [$termKey] = $data_array [$termKey];
					
					if(preg_match("(Can't|Skipped|Duplicate)", $core_instance->detailed_log[$line_number]['Message']) === 0){
						$core_instance->detailed_log[$line_number]['Tags'] = $data_array[$termKey];
					}
					$tag_name = 'post_tag';
					break;
				case 'product_tag':
					$tags [$termKey] = $data_array [$termKey];

					if(preg_match("(Can't|Skipped|Duplicate)", $core_instance->detailed_log[$line_number]['Message']) === 0){
						$core_instance->detailed_log[$line_number]['Tags'] = $data_array[$termKey];
					}	
					$tag_name = 'product_tag';
					break;
				case 'product_category':
					if($type == 'WooCommerce Product')
						$category_name = 'product_cat';
					if($type == 'WPeCommerce Products')
						$category_name = 'wpsc_product_category';
						else
					$category_name = 'product_cat';
					$categories [$termKey] = $data_array [$termKey];

					if(preg_match("(Can't|Skipped|Duplicate)", $core_instance->detailed_log[$line_number]['Message']) === 0){
						$core_instance->detailed_log[$line_number]['Categories'] = $data_array[$termKey];
					}

					// Create / Assign categories to the post types
					if(isset($categories[$termKey]) && $categories[$termKey] != '') {
						$assigned_term_ids = $this->assignTermsAndTaxonomies($categories, $category_name, $pID,$poly_values);

						// If we successfully assigned any non-default categories, remove default category term.
						if (!empty($assigned_term_ids)) {
							$default_candidates = [];
							$default_product_cat_id = (int) get_option('default_product_cat');
							if (!empty($default_product_cat_id)) {
								$default_candidates[] = $default_product_cat_id;
							}

							$uncat_slug_term = get_term_by('slug', 'uncategorized', 'product_cat');
							if ($uncat_slug_term && !is_wp_error($uncat_slug_term)) {
								$default_candidates[] = (int) $uncat_slug_term->term_id;
							}

							$uncat_name_term = get_term_by('name', 'Uncategorized', 'product_cat');
							if ($uncat_name_term && !is_wp_error($uncat_name_term)) {
								$default_candidates[] = (int) $uncat_name_term->term_id;
							}

							$default_candidates = array_values(array_unique(array_filter(array_map('intval', $default_candidates))));
							if (!empty($default_candidates)) {
								wp_remove_object_terms($pID, $default_candidates, 'product_cat');
							}
						}
					}
					break;
				case 'event_tags':
					$eventtags [$termKey] = $data_array [$termKey];
					if(!empty($eventtags)){

						if(preg_match("(Can't|Skipped|Duplicate)", $core_instance->detailed_log[$line_number]['Message']) === 0){
							$core_instance->detailed_log[$line_number]['Tags'] = $data_array[$termKey];
						}
						
						foreach($eventtags as $e_key => $e_value){
							if(!empty($e_value)){
								if (strpos($e_value, ',') !== false) {
									$split_etag = explode(',', $e_value);
								
								} else {
									$split_etag = $e_value;
								}
								if(is_array($split_etag)) {
									foreach($split_etag as $item) {
										$etagData[] = (string)$item;
									}
								} else {
									$etagData = (string)$split_etag;
								}
								wp_set_object_terms($pID, $etagData,'event-tags');
							}
						}
					}
					break;
				case 'event_categories':
					$event_categories [$termKey] = $data_array [$termKey];
					if(!empty($event_categories)) {
						
						if(preg_match("(Can't|Skipped|Duplicate)", $core_instance->detailed_log[$line_number]['Message']) === 0){
							$core_instance->detailed_log[$line_number]['Categories'] = $data_array[$termKey];
						}

						foreach($event_categories as $ec_key => $ec_value){
							if(!empty($ec_value)) {
								if (strpos($ec_value, ',') !== false) {
									$split_ecat = explode(',', $ec_value);
								
								} else {
									$split_ecat = $ec_value;
								}
								if(is_array($split_ecat)) {
									foreach($split_ecat as $item) {
										$ecatData[] = (string)$item;
									}
								} else {
									$ecatData = (string)$split_ecat;
								}
								wp_set_object_terms($pID, $ecatData,'event-categories');
							}
						}
					}
					break;
				default :
					$smack_taxonomy[$termKey] = $data_array[$termKey];

					if($termKey != 'post_format')
					$term_space = '&nbsp'.$termKey;
					$core_instance->detailed_log[$line_number][$term_space] =  $data_array[$termKey]  ;

					$taxonomy_name = $termKey;
					// Create / Assign taxonomies to the post types
					if(isset($smack_taxonomy[$termKey]) && $smack_taxonomy[$termKey] != '')
						$this->assignTermsAndTaxonomies($smack_taxonomy, $taxonomy_name, $pID,$poly_values);
					break;
			}
		}

		// Create / Assign tags to the post types
		if (!empty ($tags)) {
			foreach ($tags as $tag_key => $tag_value) {
				if (!empty($tag_value)) {
					if (strpos($tag_value, ',') !== false) {
						$split_tag = explode(',', $tag_value); 
					} else {
						$split_tag = $tag_value;
					}
					if(is_array($split_tag)) {
						foreach($split_tag as $item) {
							$tag_list[] = $item;
						}
					} else {
						$tag_list = $split_tag;
					}
					wp_set_object_terms($pID, $tag_list, $tag_name);
				}
			}
		}
    }
    
    public function assignTermsAndTaxonomies($categories, $category_name, $pID,$poly_values = '') {
		if(!empty($poly_values)){
			$lang_list = pll_languages_list();
		}
		
		$raw_input = '';
		foreach ($categories as $cat_value) {
			if (!empty($cat_value)) {
				$raw_input = $cat_value;
				break;
			}
		}

		$term_ids_to_assign = [];

		// Normalize and split: support ',', '|', and '>' (hierarchy).
		$raw_input = is_array($raw_input) ? implode('|', $raw_input) : (string) $raw_input;
		$raw_input = html_entity_decode($raw_input, ENT_QUOTES, get_bloginfo('charset'));
		$raw_input = trim((string) $raw_input);
		if ($raw_input === '') {
			return [];
		}

		// Split multiple categories by comma or pipe.
		$paths = preg_split('/\s*[,\|]\s*/', $raw_input, -1, PREG_SPLIT_NO_EMPTY);
		if (!is_array($paths)) {
			$paths = [$raw_input];
		}

		foreach ($paths as $path) {
			$path = trim((string) $path);
			if ($path === '') {
				continue;
			}

			// Split hierarchical chain by '>'.
			$parts = preg_split('/\s*>\s*/', $path, -1, PREG_SPLIT_NO_EMPTY);
			if (!is_array($parts) || empty($parts)) {
				$parts = [$path];
			}

			$parent_id = 0;
			$last_term_id = 0;
			foreach ($parts as $part_name) {
				$term_name = sanitize_text_field(trim((string) $part_name));
				if ($term_name === '') {
					continue;
				}

				$exists = term_exists($term_name, $category_name, $parent_id ?: 0);
				if (is_array($exists) && !empty($exists['term_id'])) {
					$last_term_id = (int) $exists['term_id'];
				} elseif (is_int($exists) && $exists > 0) {
					$last_term_id = (int) $exists;
				} else {
					$insert_args = [];
					if ($parent_id > 0) {
						$insert_args['parent'] = $parent_id;
					}
					$created = wp_insert_term($term_name, $category_name, $insert_args);
					if (is_wp_error($created) || empty($created['term_id'])) {
						// Skip this chain if we couldn't create the term.
						$last_term_id = 0;
						break;
					}
					$last_term_id = (int) $created['term_id'];
				}

				// Polylang term language (if provided).
				if (!empty($poly_values) && !empty($last_term_id) && function_exists('pll_set_term_language')) {
					$lang = $poly_values['language_code'] ?? '';
					if (empty($lang) || (isset($lang_list) && is_array($lang_list) && !in_array($lang, $lang_list, true))) {
						$lang = function_exists('pll_default_language') ? pll_default_language() : $lang;
					}
					if (!empty($lang)) {
						pll_set_term_language($last_term_id, $lang);
					}
				}

				$parent_id = $last_term_id;
			}

			if (!empty($last_term_id)) {
				$term_ids_to_assign[] = $last_term_id;
			}
		}

		$term_ids_to_assign = array_values(array_unique(array_map('intval', $term_ids_to_assign)));

		if (!empty($term_ids_to_assign)) {
			wp_set_object_terms($pID, $term_ids_to_assign, $category_name, true);
		}

		return $term_ids_to_assign;
	}
}
