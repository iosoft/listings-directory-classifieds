<?php

class w2dc_admin {
	public $listings_manager;
	public $locations_manager;
	public $locations_levels_manager;
	public $categories_manager;
	public $content_fields_manager;
	public $media_manager;
	public $settings_manager;
	public $levels_manager;
	
	public function __construct() {
		add_action('admin_menu', array($this, 'menu'));

		$this->settings_manager = new w2dc_settings_manager;

		$this->levels_manager = new w2dc_levels_manager;

		$this->listings_manager = new w2dc_listings_manager;

		$this->locations_manager = new w2dc_locations_manager;

		$this->locations_levels_manager = new w2dc_locations_levels_manager;

		$this->categories_manager = new w2dc_categories_manager;

		$this->content_fields_manager = new w2dc_content_fields_manager;

		$this->media_manager = new w2dc_media_manager;

		add_action('admin_menu', array($this, 'addChooseLevelPage'));
		add_action('load-post-new.php', array($this, 'handleLevel'));

		// hide some meta-blocks when create/edit posts
		add_action('admin_init', array($this, 'hideMetaBlocks'));
		
		add_filter('post_row_actions', array($this, 'removeQuickEdit'), 10, 2);

		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles'));

		add_action('admin_notices', 'w2dc_renderMessages');
	}

	public function addChooseLevelPage() {
		add_submenu_page('options.php',
			__('Choose level of new listing', 'W2DC'),
			__('Choose level of new listing', 'W2DC'),
			'publish_posts',
			'w2dc_choose_level',
			array($this, 'chooseLevelsPage')
		);
	}

	// Special page to choose the level for new listing
	public function chooseLevelsPage() {
		$this->levels_manager->displayChooseLevelTable();
	}
	
	public function handleLevel() {
		global $w2dc_instance;

		if (isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE) {
			if (!isset($_GET['level_id'])) {
				if (count($w2dc_instance->levels->levels_array) != 1) {
					wp_redirect(admin_url('options.php?page=w2dc_choose_level'));
					exit;
				} else {
					$single_level = array_shift($w2dc_instance->levels->levels_array);
					wp_redirect(admin_url('post-new.php?post_type=w2dc_listing&level_id=' . $single_level->id));
					exit;
				}
			}
		}
	}

	public function menu() {
		add_menu_page(__("Directory Admin", "W2DC"),
			__('Directory Admin', 'W2DC'),
			'administrator',
			'w2dc_admin',
			array($this, 'w2dc_index_page'),
			W2DC_RESOURCES_URL . 'images/menuicon.png'
		);
	}
	
	public function hideMetaBlocks() {
		 global $post, $pagenow;

		if (($pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE) || ($pagenow == 'post.php' && $post && $post->post_type == W2DC_POST_TYPE)) {
			$user_id = get_current_user_id();
			update_user_meta($user_id, 'metaboxhidden_' . W2DC_POST_TYPE, array('authordiv', 'trackbacksdiv', 'commentstatusdiv', 'postcustom'));
		}
	}

	public function w2dc_index_page() {
		global $w2dc_instance;
		if ($w2dc_instance->index_page_id === 0 && isset($_GET['action']) && $_GET['action'] == 'directory_page_installation') {
			$page = array('post_status' => 'publish', 'post_title' => __('Directory & Classifieds', 'W2DC'), 'post_type' => 'page', 'post_content' => '[webdirectory]', 'comment_status' => 'closed');
			if (wp_insert_post($page))
				w2dc_addMessage(__('"Directory & Classifieds" page with [webdirectory] shortcode was successfully created, thank you!'));
		}
		w2dc_renderTemplate('admin_index.tpl.php');
	}
	
	public function removeQuickEdit($actions, $post) {
		if ($post->post_type == W2DC_POST_TYPE)
			unset($actions['inline hide-if-no-js']);
		return $actions;
	}
	
	public function admin_enqueue_scripts_styles() {
		wp_enqueue_style('w2dc_admin', W2DC_RESOURCES_URL . 'css/admin.css');
		wp_enqueue_script('js_functions', W2DC_RESOURCES_URL . 'js/js_functions.js', array('jquery'));
		wp_localize_script(
				'js_functions',
				'js_objects',
				array(
						'ajaxurl' => admin_url('admin-ajax.php'),
						'ajax_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-loader.gif',
				)
		);
		wp_enqueue_script('jquery-ui-dialog');
		// this jQuery UI version 1.10.3 is for WP v3.7.1
		wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css');
	}
}
?>