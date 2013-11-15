<?php 

class w2dc_categories_manager {
	
	public function __construct() {
		global $pagenow;

		if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') {
			add_action('add_meta_boxes', array($this, 'removeCategoriesMetabox'));
			add_action('add_meta_boxes', array($this, 'addCategoriesMetabox'));
		} 
	}
	
	// remove native locations taxonomy metabox from sidebar
	public function removeCategoriesMetabox() {
		remove_meta_box(W2DC_CATEGORIES_TAX . 'div', W2DC_POST_TYPE, 'side');
	}

	public function addCategoriesMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE && ($level = w2dc_getCurrentListingInAdmin()->level) && ($level->categories_number > 0 || $level->unlimited_categories)) {
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles'));

			add_meta_box(W2DC_CATEGORIES_TAX,
					__('Listing categories', 'W2DC'),
					'post_categories_meta_box',
					W2DC_POST_TYPE,
					'normal',
					'high',
					array('taxonomy' => W2DC_CATEGORIES_TAX));
		}
	}
	
	public function validateCategories($level, &$postarr, &$errors) {
		if (isset($postarr['tax_input'][W2DC_CATEGORIES_TAX]) && is_array($postarr['tax_input'][W2DC_CATEGORIES_TAX])) {
			unset($postarr['tax_input'][W2DC_CATEGORIES_TAX][0]);

			if (!$level->unlimited_categories)
				// remove unauthorized categories
				$postarr['tax_input'][W2DC_CATEGORIES_TAX] = array_slice($postarr['tax_input'][W2DC_CATEGORIES_TAX], 0, $level->categories_number, true);

			if ($level->categories && array_diff($postarr['tax_input'][W2DC_CATEGORIES_TAX], $level->categories))
				$errors[] = __('Sorry, you can not choose some categries for this level!', 'W2DC');

			$post_categories_ids = $postarr['tax_input'][W2DC_CATEGORIES_TAX];
		} else
			$post_categories_ids = array();

		return $post_categories_ids;
	}
	
	public function admin_enqueue_scripts_styles() {
		wp_enqueue_script('categories_scripts', W2DC_RESOURCES_URL . 'js/manage_categories.js', array('jquery'));

		if ($listing = w2dc_getCurrentListingInAdmin()) {
			if ($listing->level->unlimited_categories)
				$categories_number = 'unlimited';
			else 
				$categories_number = $listing->level->categories_number;

			wp_localize_script(
					'categories_scripts',
					'level_categories',
					array(
							'level_categories_array' => $listing->level->categories,
							'level_categories_number' => $categories_number,
							'level_categories_notice_disallowed' => __('Sorry, you can not choose this category for this level!', 'W2DC'),
							'level_categories_notice_number' => sprintf(__('Sorry, you can not choose more than %d categories!', 'W2DC'), $categories_number)
					)
			);
		}
	}
}

?>