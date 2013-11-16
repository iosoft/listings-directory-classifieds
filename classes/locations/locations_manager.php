<?php 

class w2dc_locations_manager {
	
	public function __construct() {
		global $pagenow;

		if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') {
			add_action('add_meta_boxes', array($this, 'removeLocationsMetabox'));
			add_action('add_meta_boxes', array($this, 'addLocationsMetabox'), 300);
			
			add_action('wp_ajax_tax_dropdowns_hook', 'w2dc_tax_dropdowns_updateterms');
			add_action('wp_ajax_nopriv_tax_dropdowns_hook', 'w2dc_tax_dropdowns_updateterms');
		}
	}
	
	// remove native locations taxonomy metabox from sidebar
	public function removeLocationsMetabox() {
		remove_meta_box(W2DC_LOCATIONS_TAX . 'div', W2DC_POST_TYPE, 'side');
	}
	
	public function addLocationsMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE && ($level = w2dc_getCurrentListingInAdmin()->level) && $level->locations_number > 0) {
			if ($level->google_map) {
				wp_enqueue_script('google_maps', '//maps.google.com/maps/api/js?v=3.14&sensor=false&language=en', array('jquery'));
				wp_enqueue_script('google_maps_edit', W2DC_RESOURCES_URL . 'js/google_maps_edit.js', array('jquery'));
			}

			add_meta_box('w2dc_locations',
					__('Listing locations', 'W2DC'),
					array($this, 'listingLocationsMetabox'),
					W2DC_POST_TYPE,
					'normal',
					'high');
		}
	}
	
	public function listingLocationsMetabox($post) {
		global $w2dc_instance;
		
		$listing = w2dc_getCurrentListingInAdmin();
		$locations_levels = $w2dc_instance->locations_levels;
		w2dc_renderTemplate('locations/locations_metabox.tpl.php', array('listing' => $listing, 'locations_levels' => $locations_levels));
	}

	public function validateLocations(&$errors) {
		$validation = new form_validation();
		$validation->set_rules('selected_tax[]', __('Selected location', 'W2DC'), 'is_natural');
		$validation->set_rules('address_line_1[]', __('Address line 1', 'W2DC'));
		$validation->set_rules('address_line_2[]', __('Address line 2', 'W2DC'));
		$validation->set_rules('zip_or_postal_index[]', __('Zip or postal index', 'W2DC'));
		$validation->set_rules('manual_coords[]', __('Use manual coordinates', 'W2DC'), 'is_checked');
		$validation->set_rules('map_coords_1[]', __('Latitude', 'W2DC'), 'numeric');
		$validation->set_rules('map_coords_2[]', __('Longitude', 'W2DC'), 'numeric');
		$validation->set_rules('map_zoom', __('Map zoom', 'W2DC'), 'is_natural');

		if (!$validation->run())
			$errors[] = $validation->error_string();

		return $validation->result_array();
	}
	
	public function saveLocations($level, $post_id, $validation_results) {
		global $wpdb;
		
		$wpdb->query('DELETE FROM wp_w2dc_locations_relationships WHERE post_id='.$post_id);
		wp_delete_object_term_relationships($post_id, W2DC_LOCATIONS_TAX);
		delete_post_meta($post_id, '_map_zoom');
		
		if ($validation_results['selected_tax[]']) {
			// remove unauthorized locations
			$validation_results['selected_tax[]'] = array_slice($validation_results['selected_tax[]'], 0, $level->locations_number, true);
	
			foreach ($validation_results['selected_tax[]'] AS $key=>$value) {
				$insert_values = array(
						'post_id' => $post_id,
						'location_id' => $value,
						'address_line_1' => $validation_results['address_line_1[]'][$key],
						'address_line_2' => $validation_results['address_line_2[]'][$key],
						'zip_or_postal_index' => $validation_results['zip_or_postal_index[]'][$key],
				);
				if ($level->google_map) {
					if (is_array($validation_results['manual_coords[]'])) {
						if (in_array($key, array_keys($validation_results['manual_coords[]'])))
							$insert_values['manual_coords'] = 1;
						else
							$insert_values['manual_coords'] = 0;
					} else
						$insert_values['manual_coords'] = 0;
					$insert_values['map_coords_1'] = $validation_results['map_coords_1[]'][$key];
					$insert_values['map_coords_2'] = $validation_results['map_coords_2[]'][$key];
				}
				$keys = array_keys($insert_values);
				array_walk($keys, create_function('&$val', '$val = "`".$val."`";'));
				array_walk($insert_values, create_function('&$val', '$val = "\'".$val."\'";'));
				$wpdb->query('INSERT INTO wp_w2dc_locations_relationships (' . implode(', ', $keys) . ') VALUES (' . implode(', ', $insert_values) . ')');
			}
	
			array_walk($validation_results['selected_tax[]'], create_function('&$val', '$val = intval($val);'));
			wp_set_object_terms($post_id, $validation_results['selected_tax[]'], W2DC_LOCATIONS_TAX);
			
			add_post_meta($post_id, '_map_zoom', $validation_results['map_zoom']);
		}
	}

	public function deleteLocations($post_id) {
		global $wpdb;

		$wpdb->query('DELETE FROM wp_w2dc_locations_relationships WHERE post_id='.$post_id);
		wp_delete_object_term_relationships($post_id, W2DC_LOCATIONS_TAX);
		delete_post_meta($post_id, '_map_zoom');
	}
}

?>