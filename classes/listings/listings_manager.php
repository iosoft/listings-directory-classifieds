<?php 

class w2dc_listings_manager {
	public $current_listing;
	
	public function __construct() {
		global $pagenow;
		
		if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') {
			add_action('add_meta_boxes', array($this, 'addListingInfoMetabox'));
		}

		add_action('admin_init', array($this, 'loadCurrentListing'));

		add_action('admin_init', array($this, 'initHooks'));
		
		add_filter('manage_'.W2DC_POST_TYPE.'_posts_columns', array($this, 'add_listings_table_columns'));
		add_filter('manage_'.W2DC_POST_TYPE.'_posts_custom_column', array($this, 'manage_listings_table_rows'), 10, 2);
		add_filter('post_row_actions', array($this, 'add_row_actions'), 10, 2);
		
		add_action('admin_menu', array($this, 'addRaiseUpPage'));
		add_action('admin_menu', array($this, 'addRenewPage'));

		if ((isset($_POST['publish']) || isset($_POST['save'])) && $_POST['post_type'] == W2DC_POST_TYPE) {
			add_filter('wp_insert_post_data', array($this, 'saveListing'), 99, 2);
			add_filter('wp_insert_post_empty_content', array($this, 'allowEmptyContent'), 99, 2);
			add_filter('redirect_post_location', array($this, 'redirectAfterSave'));
		}
	}
	
	public function addListingInfoMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE) {
			add_meta_box('w2dc_listing_ingo',
					__('Listing Info', 'W2DC'),
					array($this, 'listingInfoMetabox'),
					W2DC_POST_TYPE,
					'side',
					'high');
		}
	}
	
	public function listingInfoMetabox($post) {
		global $w2dc_instance;

		$listing = w2dc_getCurrentListingInAdmin();
		$levels = $w2dc_instance->levels;
		w2dc_renderTemplate('listings/info_metabox.tpl.php', array('listing' => $listing, 'levels' => $levels));
	}
	
	public function add_listings_table_columns($columns) {
		$w2dc_columns['level'] = __('Level', 'W2DC');
		$w2dc_columns['expiration_date'] = __('Expiration date', 'W2DC');

		return array_slice($columns, 0, 2, true) + $w2dc_columns + array_slice($columns, 2, count($columns)-2, true);
	}
	
	public function manage_listings_table_rows($column, $post_id) {
		switch ($column) {
			case "level":
				$listing = new w2dc_listing();
				$listing->loadListingFromPost($post_id);
				echo $listing->level->name;
				break;
			case "expiration_date":
				$listing = new w2dc_listing();
				$listing->loadListingFromPost($post_id);
				if ($listing->level->eternal_active_period)
					_e('Eternal active period', 'W2DC');
				else {
					echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($listing->expiration_date));
					if ($listing->status == 'expired')
						echo '<br /><a href="' . admin_url('options.php?page=w2dc_renew&listing_id=' . $post_id) . '"><img src="' . W2DC_RESOURCES_URL . 'images/page_refresh.png" class="w2dc_field_icon" />' . __('renew listing', 'W2DC') . '</a>';
				}
				break;
		}
	}
	
	public function add_row_actions($actions, $post) {
		if ($post->post_type ==W2DC_POST_TYPE){
			$listing = new w2dc_listing();
			$listing->loadListingFromPost($post);
			
			if ($listing->level->raiseup_enabled)
				$actions['raise_up'] = '<a href="' . admin_url('options.php?page=w2dc_raise_up&listing_id=' . $post->ID) . '"><img src="' . W2DC_RESOURCES_URL . 'images/raise_up.png" class="w2dc_field_icon" />' . __('raise up listing', 'W2DC') . '</a>';
			
		}
		return $actions;
	}

	public function addRaiseUpPage() {
		add_submenu_page('options.php',
				__('Raise up listing', 'W2DC'),
				__('Raise up listing', 'W2DC'),
				'publish_posts',
				'w2dc_raise_up',
				array($this, 'raiseUpListing')
		);
	}
	
	public function raiseUpListing() {
		if (isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
			$listing = new w2dc_listing();
			if ($listing->loadListingFromPost($_GET['listing_id'])) {
				$action = 'show';
				$referer = wp_get_referer();
				if (isset($_GET['raiseup_action']) && $_GET['raiseup_action'] == 'raiseup') {
					if ($listing->processRaiseUp())
						w2dc_addMessage(__('Listing was raised up successfully!', 'W2DC'));
					else
						w2dc_addMessage(__('An error has occurred and listing was not raised up', 'W2DC'), 'error');
					$action = $_GET['raiseup_action'];
					$referer = $_GET['referer'];
				}
				w2dc_renderTemplate('listings/raise_up.tpl.php', array('listing' => $listing, 'referer' => $referer, 'action' => $action));
			} else
				exit();
		} else
			exit();
	}

	public function addRenewPage() {
		add_submenu_page('options.php',
				__('Renew listing', 'W2DC'),
				__('Renew listing', 'W2DC'),
				'publish_posts',
				'w2dc_renew',
				array($this, 'renewListing')
		);
	}
	
	public function renewListing() {
		if (isset($_GET['listing_id']) && is_numeric($_GET['listing_id'])) {
			$listing = new w2dc_listing();
			if ($listing->loadListingFromPost($_GET['listing_id'])) {
				$action = 'show';
				$referer = wp_get_referer();
				if (isset($_GET['renew_action']) && $_GET['renew_action'] == 'raiseup') {
					if ($listing->processRenew())
						w2dc_addMessage(__('Listing was renewed successfully!', 'W2DC'));
					else
						w2dc_addMessage(__('An error has occurred and listing was not renewed', 'W2DC'), 'error');
					$action = $_GET['renew_action'];
					$referer = $_GET['referer'];
				}
				w2dc_renderTemplate('listings/renew.tpl.php', array('listing' => $listing, 'referer' => $referer, 'action' => $action));
			} else
				exit();
		} else
			exit();
	}
	
	public function loadCurrentListing() {
		global $w2dc_instance, $pagenow, $post;

		if ($pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE && isset($_GET['level_id']) && is_numeric($_GET['level_id'])) {
			// New post
			$level_id = $_GET['level_id'];
			$this->current_listing = new w2dc_listing($level_id);
			$w2dc_instance->current_listing = $this->current_listing;

			if ($this->current_listing->level) {
				// need to load draft post into current_listing property
				add_action('save_post', array($this, 'saveInitialDraft'), 10);
			} else {
				wp_redirect(admin_url('options.php?page=w2dc_choose_level'));
				exit;
			}
		} elseif (
			($pagenow == 'post.php' && isset($_GET['post']) && ($post = get_post($_GET['post'])) && $post->post_type == W2DC_POST_TYPE)
			||
			($pagenow == 'post.php' && isset($_POST['post_ID']) && ($post = get_post($_POST['post_ID'])) && $post->post_type == W2DC_POST_TYPE)
		) {
			// Existed post
			$listing = new w2dc_listing();
			$listing->loadListingFromPost($post);
			$this->current_listing = $listing;
			$w2dc_instance->current_listing = $listing;
		}
	}
	
	public function saveInitialDraft($post_id) {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		global $w2dc_instance, $wpdb;
		$this->current_listing->loadListingFromPost($post_id);
		$w2dc_instance->current_listing = $this->current_listing;

		return $wpdb->query($wpdb->prepare("INSERT INTO `wp_w2dc_levels_relationships` (post_id, level_id) VALUES(%d, %d) ON DUPLICATE KEY UPDATE level_id=%d", $this->current_listing->post->ID, $this->current_listing->level->id, $this->current_listing->level->id));
	}
	
	public function allowEmptyContent($maybe_empty, $postarr) {
		if ($postarr['post_type'] == W2DC_POST_TYPE)
			return false;
	}

	public function saveListing($data, $postarr) {
		global $w2dc_instance;

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		$errors = array();
		
		if (!isset($postarr['post_title']) || !$postarr['post_title'] || $postarr['post_title'] == __('Auto Draft'))
			$errors[] = __('Listing title field required', 'W2DC');

		$post_categories_ids = $w2dc_instance->categories_manager->validateCategories($this->current_listing->level, $postarr, $errors);

		$w2dc_instance->content_fields->saveValues($this->current_listing->post->ID, $post_categories_ids, $errors, $data);

		if ($this->current_listing->level->locations_number) {
			if ($validation_results = $w2dc_instance->locations_manager->validateLocations($errors)) {
				$w2dc_instance->locations_manager->saveLocations($this->current_listing->level, $this->current_listing->post->ID, $validation_results);
			}
		}

		if ($this->current_listing->level->images_number || $this->current_listing->level->videos_number) {
			if ($validation_results = $w2dc_instance->media_manager->validateAttachments($this->current_listing->level, $errors))
				$w2dc_instance->media_manager->saveAttachments($this->current_listing->level, $this->current_listing->post->ID, $validation_results);
		}

		
		if ($errors) {
			$data['post_status'] = 'draft';

			foreach ($errors AS $error)
				w2dc_addMessage($error, 'error');
		} else {
			if (!($listing_created = get_post_meta($this->current_listing->post->ID, '_listing_created', true))) {
				add_post_meta($this->current_listing->post->ID, '_listing_created', true);
				add_post_meta($this->current_listing->post->ID, '_order_date', mktime());
				add_post_meta($this->current_listing->post->ID, '_listing_status', 'active');

				if (!$this->current_listing->level->eternal_active_period) {
					$expiration_date = w2dc_sumDates(mktime(), $this->current_listing->level->active_days, $this->current_listing->level->active_months, $this->current_listing->level->active_years);
					add_post_meta($this->current_listing->post->ID, '_expiration_date', $expiration_date);
				}
			} else {
				if (!$this->current_listing->status == 'expired')
					update_post_meta($this->current_listing->post->ID, '_listing_status', 'active');
				elseif ($this->current_listing->status == 'expired' && $data['post_status'] == 'publish') {
					w2dc_addMessage(__('You can\'t publish listing until it has expired status!', 'W2DC'), 'error');
					return $data;
				}
			}

			w2dc_addMessage(__('Listing was saved successfully!', 'W2DC'));
		}

		return $data;
	}

	public function redirectAfterSave($location) {
		global $post;

		if ($post && $post->post_type == W2DC_POST_TYPE) {
			// Remove native success 'message'
			$uri = parse_url($location);
			$uri_array = wp_parse_args($uri['query']);
			if (isset($uri_array['message']))
				unset($uri_array['message']);
			$location = add_query_arg($uri_array, 'post.php');
		}

		return $location;
	}
	
	public function initHooks() {
		if (current_user_can('delete_posts'))
			add_action('delete_post', array($this, 'delete_listing_data'), 10);
	}
	
	public function delete_listing_data($post_id) {
		global $w2dc_instance;
		
		$w2dc_instance->locations_manager->deleteLocations($post_id);
	}
}

?>