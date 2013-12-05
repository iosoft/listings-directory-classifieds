<?php 

class w2dc_listing {
	public $post;
	public $level;
	public $expiration_date;
	public $order_date;
	public $listing_created = false;
	public $status; // active, expired, stopped
	public $categories = array();
	public $locations = array();
	public $content_fields = array();
	public $logo_file;
	public $map_zoom = 11;
	public $logo_image;
	public $images = array();
	public $videos = array();
	public $map;

	public function __construct($level_id = null) {
		if ($level_id) {
			// New listing
			$this->setLevelByID($level_id);
		}
	}
	
	// Load existed listing
	public function loadListingFromPost($post) {
		if (is_object($post))
			$this->post = $post;
		elseif (is_numeric($post))
			if (!($this->post = get_post($post)))
				return false;

		if ($this->setLevelByPostId()) {
			$this->setMetaInformation();
			$this->setLocations();
			$this->setContentFields();
			$this->setMapZoom();
			$this->setMedia();
			
			apply_filters('w2dc_listing_loading', $this);
		}
		return true;
	}

	public function setLevelByID($level_id) {
		global $w2dc_instance;

		$levels = $w2dc_instance->levels;
		$this->level = $levels->getLevelById($level_id);
	}
	
	public function setMetaInformation() {
		if (!$this->level->eternal_active_period)
			$this->expiration_date = get_post_meta($this->post->ID, '_expiration_date', true);

		$this->order_date = get_post_meta($this->post->ID, '_order_date', true);

		$this->status = get_post_meta($this->post->ID, '_listing_status', true);

		$this->listing_created = get_post_meta($this->post->ID, '_listing_created', true);

		return $this->expiration_date;
	}

	public function setLevelByPostId($post_id = null) {
		global $w2dc_instance, $wpdb;

		if (!$post_id)
			$post_id = $this->post->ID;

		$levels = $w2dc_instance->levels;
		if ($level_id = $wpdb->get_var("SELECT level_id FROM `wp_w2dc_levels_relationships` WHERE post_id=" . $post_id))
			return $this->level = $levels->levels_array[$level_id];
		return $this->level;
	}

	public function setLocations() {
		global $wpdb;

		$results = $wpdb->get_results('SELECT * FROM wp_w2dc_locations_relationships WHERE post_id='.$this->post->ID, ARRAY_A);
		
		foreach ($results AS $row) {
			if ($row['location_id'] || $row['map_coords_1'] != '0.000000' || $row['map_coords_2'] != '0.000000' || $row['address_line_1'] || $row['zip_or_postal_index']) {
				$location = new w2dc_location($this->post->ID);
				$location_settings = array(
						'selected_location' => $row['location_id'],
						'address_line_1' => $row['address_line_1'],
						'address_line_2' => $row['address_line_2'],
						'zip_or_postal_index' => $row['zip_or_postal_index'],
				);
				if ($this->level->google_map) {
					$location_settings['manual_coords'] = w2dc_getValue($row, 'manual_coords');
					$location_settings['map_coords_1'] = w2dc_getValue($row, 'map_coords_1');
					$location_settings['map_coords_2'] = w2dc_getValue($row, 'map_coords_2');
					if ($this->level->google_map_markers)
						$location_settings['map_icon_file'] = w2dc_getValue($row, 'map_icon_file');
				}
				$location->createLocationFromArray($location_settings);
				
				$this->locations[] = $location;
			}
		}
	}

	public function setMapZoom() {
		$this->map_zoom = get_post_meta($this->post->ID, '_map_zoom', true);
	}

	public function setContentFields() {
		global $w2dc_instance;

		$post_categories_ids = wp_get_post_terms($this->post->ID, W2DC_CATEGORIES_TAX, array('fields' => 'ids'));
		$this->content_fields = $w2dc_instance->content_fields->loadValues($this->post->ID, $post_categories_ids);
	}
	
	public function setMedia() {
		if ($this->level->images_number) {
			if ($images = get_children(array(
					'post_parent' => $this->post->ID,
					'post_type' => 'attachment',
					'post_mime_type' => 'image'
			), ARRAY_A)) {
				$this->images = array_reverse($images, true);
				
				if (($logo_id = (int)get_post_meta($this->post->ID, '_attached_image_as_logo', true)) && in_array($logo_id, array_keys($this->images)))
					$this->logo_image = $logo_id;
				else
					$this->logo_image = array_shift(array_keys($images));
			} else
				$this->images = array();
		}
		
		if ($this->level->videos_number) {
			if ($videos = get_post_meta($this->post->ID, '_attached_video_id')) {
				$videos_captions = get_post_meta($this->post->ID, '_attached_video_caption');
				foreach ($videos AS $key=>$video) {
					if (isset($videos_captions[$key]))
						$caption = $videos_captions[$key];
					else
						$caption = '';
					$this->videos[] = array('caption' => $caption, 'id' => $video);
				}
			}
		}
	}
	
	public function getContentField($field_id) {
		if (isset($this->content_fields[$field_id]))
			return $this->content_fields[$field_id];
	}

	public function display() {
		w2dc_renderTemplate('frontend/listing.tpl.php', array('listing' => $this, 'current_user' => wp_get_current_user()));
	}
	
	public function renderContentFields($is_single = true) {
		foreach ($this->content_fields AS $content_field) {
			if ((!$is_single && $content_field->on_exerpt_page) || ($is_single && $content_field->on_listing_page))
				$content_field->renderOutput($this);
		}
	}
	
	public function isMap() {
		$this->map = new google_maps;
		return $this->map->collectLocations($this);
	}
	
	public function renderMap($show_directions = true) {
		$this->map->display($show_directions);
	}
	
	public function title() {
		return get_the_title($this->post);
	}

	public function processRaiseUp() {
		if ($this->level->raiseup_enabled)
			return update_post_meta($this->post->ID, '_order_date', mktime());
	}

	public function processRenew() {
		if (!$this->level->eternal_active_period) {
			$expiration_date = w2dc_sumDates(mktime(), $this->level->active_days, $this->level->active_months, $this->level->active_years);
			update_post_meta($this->post->ID, '_expiration_date', $expiration_date);
		}
		update_post_meta($this->post->ID, '_order_date', mktime());
		update_post_meta($this->post->ID, '_listing_status', 'active');

		return wp_update_post(array('ID' => $this->post->ID, 'post_status' => 'publish'));
	}
}

?>