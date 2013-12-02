<?php 

class w2dc_levels {
	public $levels_array = array();

	public function __construct() {
		$this->getLevelsFromDB();
	}
	
	public function saveOrder($order_input) {
		global $wpdb;

		if ($order_ids = explode(',', trim($order_input))) {
			$i = 1;
			foreach ($order_ids AS $id) {
				$wpdb->update('wp_w2dc_levels', array('order_num' => $i), array('id' => $id));
				$i++;
			}
		}
		$this->getLevelsFromDB();
		return true;
	}
	
	public function getLevelsFromDB() {
		global $wpdb;
		$this->levels_array = array();
		
		$array = $wpdb->get_results("SELECT * FROM wp_w2dc_levels ORDER BY order_num", ARRAY_A);
		foreach ($array AS $row) {
			$level = new w2dc_level;
			$level->buildLevelFromArray($row);
			$this->levels_array[$row['id']] = $level;
		}
	}
	
	public function getLevelById($level_id) {
		if (isset($this->levels_array[$level_id]))
			return $this->levels_array[$level_id];
	}

	public function createLevelFromArray($array) {
		global $wpdb;
		
		$insert_update_args = array(
				'name' => $array['name'],
				'description' => $array['description'],
				'active_years' => $array['active_years'],
				'active_months' => $array['active_months'],
				'active_days' => $array['active_days'],
				'raiseup_enabled' => $array['raiseup_enabled'],
				'sticky' => $array['sticky'],
				'featured' => $array['featured'],
				'categories_number' => $array['categories_number'],
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'unlimited_categories' => $array['unlimited_categories'],
				'google_map' => $array['google_map'],
				'logo_enabled' => $array['logo_enabled'],
				'logo_size' => $array['logo_size'],
				'images_number' => $array['images_number'],
				'videos_number' => $array['videos_number'],
				'categories' => serialize($array['categories_list']),
		);
		$insert_update_args = apply_filters('w2dc_level_create_edit_args', $insert_update_args, $array);
	
		return $wpdb->insert('wp_w2dc_levels', $insert_update_args);
	}
	
	public function saveLevelFromArray($level_id, $array) {
		global $wpdb;

		// update listings from eternal active period to numeric 
		$old_level = $this->getLevelById($level_id);
		if ($old_level->eternal_active_period && ($array['active_years'] || $array['active_months'] || $array['active_days'])) {
			$expiration_date = w2dc_sumDates(mktime(), $array['active_days'], $array['active_months'], $array['active_years']);
			$postids = $this->getPostIdsByLevelId($level_id);
			foreach ($postids AS $post_id) {
				delete_post_meta($post_id, '_expiration_date');
				update_post_meta($post_id, '_expiration_date', $expiration_date);
			}
		} elseif (!$old_level->eternal_active_period && $array['active_years'] == 0 && $array['active_months'] == 0 && $array['active_days'] == 0) {
			$postids = $this->getPostIdsByLevelId($level_id);
			foreach ($postids AS $post_id)
				delete_post_meta($post_id, '_expiration_date');
		}
		
		$insert_update_args = array(
				'name' => $array['name'],
				'description' => $array['description'],
				'active_years' => $array['active_years'],
				'active_months' => $array['active_months'],
				'active_days' => $array['active_days'],
				'sticky' => $array['sticky'],
				'raiseup_enabled' => $array['raiseup_enabled'],
				'featured' => $array['featured'],
				'categories_number' => $array['categories_number'],
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'unlimited_categories' => $array['unlimited_categories'],
				'google_map' => $array['google_map'],
				'logo_enabled' => $array['logo_enabled'],
				'logo_size' => $array['logo_size'],
				'images_number' => $array['images_number'],
				'videos_number' => $array['videos_number'],
				'categories' => serialize($array['categories_list']),
		);
		$insert_update_args = apply_filters('w2dc_level_create_edit_args', $insert_update_args, $array);
	
		return $wpdb->update('wp_w2dc_levels', $insert_update_args,	array('id' => $level_id), null, array('%d')) !== false;
	}
	
	public function deleteLevel($level_id) {
		global $wpdb;
		
		$postids = $this->getPostIdsByLevelId($level_id);
		foreach ($postids AS $post_id)
			wp_delete_post($post_id, true);
	
		$wpdb->delete('wp_w2dc_levels', array('id' => $level_id));
		return true;
	}
	
	public function getPostIdsByLevelId($level_id) {
		global $wpdb;

		return $postids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM wp_w2dc_levels_relationships WHERE level_id=%d", $level_id));
	}
}

class w2dc_level {
	public $id;
	public $order_num;
	public $name;
	public $description;
	public $active_years = 0;
	public $active_months = 0;
	public $active_days = 0;
	public $eternal_active_period;
	public $featured = 0;
	public $raiseup_enabled = 0;
	public $sticky = 0;
	public $categories_number = 0;
	public $unlimited_categories = 1;
	public $locations_number = 0;
	public $google_map = 0;
	public $google_map_markers = 0;
	//public $preapproved_mode;
	public $logo_enabled;
	public $logo_size;
	public $images_number = 0;
	public $videos_number = 0;
	public $categories;

	//public $files_count;

	/* public $maps_enabled;
	public $maps_size;
	public $option_print;
	public $option_pdf;
	public $option_quick_list;
	public $option_email_friend;
	public $option_email_owner;
	public $option_report;
	public $ratings_enabled;
	public $reviews_mode;
	public $reviews_richtext_enabled;
	public $social_bookmarks_enabled;
	public $titles_template;
	public $allow_to_edit_active_period;
	public $after_listings_expiration;
	public $after_listings_claim; */
	
	public function buildLevelFromArray($array) {
		$this->id = w2dc_getValue($array, 'id');
		$this->order_num = w2dc_getValue($array, 'order_num');
		$this->name = w2dc_getValue($array, 'name');
		$this->description = w2dc_getValue($array, 'description');
		$this->active_years = w2dc_getValue($array, 'active_years');
		$this->active_months = w2dc_getValue($array, 'active_months');
		$this->active_days = w2dc_getValue($array, 'active_days');
		if ($this->active_years == 0 && $this->active_months == 0 && $this->active_days == 0)
			$this->eternal_active_period = 1;
		else 
			$this->eternal_active_period = 0;
		
		$this->featured = w2dc_getValue($array, 'featured');
		$this->sticky = w2dc_getValue($array, 'sticky');
		$this->raiseup_enabled = w2dc_getValue($array, 'raiseup_enabled');
		$this->categories_number = w2dc_getValue($array, 'categories_number');
		$this->unlimited_categories = w2dc_getValue($array, 'unlimited_categories');
		$this->locations_number = w2dc_getValue($array, 'locations_number');
		$this->google_map = w2dc_getValue($array, 'google_map');
		$this->google_map_markers = w2dc_getValue($array, 'google_map_markers');
		$this->logo_enabled = w2dc_getValue($array, 'logo_enabled');
		$this->logo_size = w2dc_getValue($array, 'logo_size');
		$this->images_number = w2dc_getValue($array, 'images_number');
		$this->videos_number = w2dc_getValue($array, 'videos_number');
		$this->categories = w2dc_getValue($array, 'categories');
		
		$this->convertCategories();
	}
	
	public function convertCategories() {
		if ($this->categories) {
			$unserialized_categories = unserialize($this->categories);
			if (count($unserialized_categories) > 1 || $unserialized_categories != array(''))
				$this->categories = $unserialized_categories;
			else
				$this->categories = array();
		}
		return $this->categories;
	}
	
	public function getActivePeriodString() {
		if ($this->eternal_active_period)
			return __('Never expire', 'W2DC');
		else {
			$string_arr = array();
			if ($this->active_days > 0)
				$string_arr[] = $this->active_days . ' ' . _n('day', 'days', $this->active_days, 'W2DC');
			if ($this->active_months > 0)
				$string_arr[] = $this->active_months . ' ' . _n('month', 'months', $this->active_months, 'W2DC');
			if ($this->active_years > 0)
				$string_arr[] = $this->active_years . ' ' . _n('year', 'years', $this->active_years, 'W2DC');
			return implode(', ', $string_arr);
		}
	}
}

?>