<?php
/*
Plugin Name: Directory & Classifieds plugin
Plugin URI: http://www.salephpscripts.com/wordpress_directory/
Description: Provides an ability to build any kind of directory site: classifieds, events directory, cars, bikes, boats and other vehicles dealers site, pets, real estate portal on your WordPress powered site. In other words - whatever you want.
Version: 1.0.0
Author: Mihail Chepovskiy
Author URI: http://www.salephpscripts.com
License: GPLv2 or any later version
*/

define('W2DC_VERSION', '1.0.0');

define('W2DC_PATH', plugin_dir_path(__FILE__));
define('W2DC_URL', plugins_url('/', __FILE__));
define('W2DC_RESOURCES_PATH', W2DC_PATH . 'resources/');
define('W2DC_RESOURCES_URL', W2DC_URL . 'resources/');

// Content fields icons constant
define('W2DC_FIELDS_ICONS_PATH', W2DC_PATH . '/resources/images/content_fields_icons/');
define('W2DC_FIELDS_ICONS_URL', W2DC_RESOURCES_URL . 'images/content_fields_icons/');

define('W2DC_POST_TYPE', 'w2dc_listing');
define('W2DC_CATEGORIES_TAX', 'w2dc-category');
define('W2DC_LOCATIONS_TAX', 'w2dc-location');
define('W2DC_TAGS_TAX', 'w2dc-tag');

define('W2DC_MAIN_SHORTCODE', 'webdirectory');

include_once W2DC_PATH . 'install.php';
include_once W2DC_PATH . 'classes/admin.php';
include_once W2DC_PATH . 'classes/form_validation.php';
include_once W2DC_PATH . 'classes/listings/listings_manager.php';
include_once W2DC_PATH . 'classes/listings/listing.php';
include_once W2DC_PATH . 'classes/categories_manager.php';
include_once W2DC_PATH . 'classes/media_manager.php';
include_once W2DC_PATH . 'classes/content_fields/content_fields_manager.php';
include_once W2DC_PATH . 'classes/content_fields/content_fields.php';
include_once W2DC_PATH . 'classes/locations/locations_manager.php';
include_once W2DC_PATH . 'classes/locations/locations_levels_manager.php';
include_once W2DC_PATH . 'classes/locations/locations_levels.php';
include_once W2DC_PATH . 'classes/locations/location.php';
include_once W2DC_PATH . 'classes/levels/levels_manager.php';
include_once W2DC_PATH . 'classes/levels/levels.php';
include_once W2DC_PATH . 'classes/frontend_controller.php';
include_once W2DC_PATH . 'classes/settings_manager.php';
include_once W2DC_PATH . 'classes/search_form.php';
include_once W2DC_PATH . 'classes/google_maps.php';
include_once W2DC_PATH . 'functions.php';
include_once W2DC_PATH . 'functions_ui.php';

global $w2dc_instance;
global $w2dc_messages;

class w2dc_plugin {
	public $admin;
	public $levels;
	public $index_page_id;
	public $index_page_slug;
	public $index_page_url;
	public $frontend_controller;
	public $action;
	public $map_markers_url = '';

	public function __construct() {
		register_activation_hook(__FILE__, array($this, 'activation'));
		register_deactivation_hook(__FILE__, array($this, 'deactivation'));
	}
	
	public function activation() {
		global $wp_version;

		if (version_compare($wp_version, '3.6', '<')) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			wp_die("Sorry, but you can't run this plugin on current WordPress version, it requires WordPress v3.6 or higher.");
		}
		flush_rewrite_rules();
		
		wp_schedule_event(current_time('timestamp'), 'hourly', 'sheduled_events');
	}

	public function deactivation() {
		flush_rewrite_rules();

		wp_clear_scheduled_hook('sheduled_events');
	}
	
	public function init() {
		global $w2dc_instance;
		
		if (!get_option('w2dc_installed_directory'))
			w2dc_install_directory();

		$_GET = stripslashes_deep($_GET);
		if (isset($_REQUEST['action']))
			$this->action = $_REQUEST['action'];
		
		add_action('plugins_loaded', array($this, 'load_textdomain'), 0);
		
		add_action('sheduled_events', array($this, 'suspend_expired_listings'));

		add_action('init', array($this, 'register_post_type'), 0);
		add_action('init', array($this, 'getIndexPage'), 0);
		add_action('wp', array($this, 'loadFrontendController'), 0);
		add_action('pre_get_posts', array($this, 'rewrite_query_vars'));

		$w2dc_instance->levels = new w2dc_levels;
		$w2dc_instance->locations_levels = new w2dc_locations_levels;
		$w2dc_instance->content_fields = new w2dc_content_fields;

		if (is_admin()) {
			$this->admin = new w2dc_admin();
		} else {
			add_filter('wp_title', array($this, 'page_title'), 10, 2);
			add_action('wp_loaded', array($this, 'wp_loaded'));
			add_filter('rewrite_rules_array', array($this, 'rewrite_rules'));
			add_filter('post_type_link', array($this, 'listing_permalink'), 10, 2);
			add_filter('term_link', array($this, 'category_permalink'), 10, 3);
			add_filter('term_link', array($this, 'tag_permalink'), 10, 3);
		}

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
	}
	
	public function load_textdomain() {
		load_plugin_textdomain('W2DC', '', dirname(plugin_basename( __FILE__ )) . '/languages');
	}

	public function loadFrontendController() {
		$this->frontend_controller = new w2dc_frontend_controller();
	}
	
	public function rewrite_query_vars($query) {
		if (!is_admin() && $query->is_main_query()) {
			if ($this->action == 'search')
				// set up search flag
				$query->is_search = true;

			if (get_option('w2dc_is_home_page') && $query->is_home())
				$query->set('post_type', W2DC_POST_TYPE);

			if ($query->is_tax() && get_query_var('taxonomy') == W2DC_CATEGORIES_TAX) {
				// categories/tags pages
				$query->query_vars['posts_per_page'] = get_option('w2dc_listings_number_excerpt');
			} elseif (!$query->is_single() && get_query_var('post_type') == W2DC_POST_TYPE) {
				// index page
				$query->query_vars['posts_per_page'] = get_option('w2dc_listings_number_index');
			}
		}
	}

	public function getIndexPage() {
		if ($array = w2dc_getIndexPage()) {
			$this->index_page_id = $array['id'];
			$this->index_page_slug = $array['slug'];
			$this->index_page_url = $array['url'];
		}
		
		if (!get_option('w2dc_is_home_page') && $this->index_page_id === 0)
			w2dc_addMessage(sprintf(__('<b>Directory & Classifieds plugin</b>: sorry, but there isn\'t any page with [webdirectory] shortcode. Create <a href="%s">this special page</a> for you? Or you wish to set up <a href="%s">directory at home page</a>?', 'W2DC'), admin_url('admin.php?page=w2dc_admin&action=directory_page_installation'), admin_url('admin.php?page=w2dc_settings')));
	}

	public function page_title($title, $separator) {
		if ($this->frontend_controller->getPageTitle()) {
			if (get_option('w2dc_directory_title'))
				if ($this->frontend_controller->getPageTitle() == get_option('w2dc_directory_title'))
					$directory_title = '';
				else
					$directory_title = get_option('w2dc_directory_title') . ' ' . $separator . ' ';
			else
				$directory_title = '';
			
			if ($title != __('Directory listings', 'W2DC') . ' ' . $separator . ' ')
				return $this->frontend_controller->getPageTitle() . ' ' . $separator . ' ' . $directory_title;
			else
				return $directory_title;
		}

		return $title;
	}
	
	public function rewrite_rules($rules) {
		return $this->w2dc_addRules() + $rules;
	}

	public function w2dc_addRules() {
		global $wp_rewrite;
		//var_dump($wp_rewrite);
		
		//var_dump($wp_rewrite->rewrite_rules());
/* 		foreach (get_option('rewrite_rules') AS $key=>$rule)
			echo $key . '
' . $rule . '


'; */
		
		$page_url = $this->index_page_slug;

		$rules['(' . $page_url . ')/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?post_type=' . W2DC_POST_TYPE . '&paged=$matches[2]';
		$rules['(' . $page_url . ')/?$'] = 'index.php?post_type=' . W2DC_POST_TYPE;

		if ($page_url)
			$rules[$page_url . '/([^\/.]+)/?$'] = 'index.php?' . W2DC_POST_TYPE . '=$matches[1]';
		else
			$rules['([0-9]{1,})/(.+)/?$'] = 'index.php?' . W2DC_POST_TYPE . '=$matches[2]';

		$rules['(' . $page_url . ')?/?' . get_option('w2dc_category_slug') . '/([^\/.]+)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?term=$matches[2]&taxonomy=' . W2DC_CATEGORIES_TAX . '&paged=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_category_slug') . '/([^\/.]+)/?$'] = 'index.php?term=$matches[2]&taxonomy=' . W2DC_CATEGORIES_TAX;

		$rules['(' . $page_url . ')?/?' . get_option('w2dc_tag_slug') . '/([^\/.]+)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?term=$matches[2]&taxonomy=' . W2DC_TAGS_TAX . '&paged=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_tag_slug') . '/([^\/.]+)/?$'] = 'index.php?term=$matches[2]&taxonomy=' . W2DC_TAGS_TAX;

		return $rules;
	}
	
	public function wp_loaded() {
		if ($rules = get_option('rewrite_rules'))
			foreach ($this->w2dc_addRules() as $key=>$value)
				if (!isset($rules[$key]) || $rules[$key] != $value) {
					global $wp_rewrite;
					$wp_rewrite->flush_rules();
					return;
				}
	}

	public function _listing_template($template) {
		if (is_single() && get_post_type() == W2DC_POST_TYPE) {
			if (is_file(W2DC_PATH . 'templates/frontend/listing_single-custom.tpl.php'))
				$template = W2DC_PATH . 'templates/frontend/listing_single-custom.tpl.php';
			else
				$template = W2DC_PATH . 'templates/frontend/listing_single.tpl.php';
		}

		return $template;
	}
	public function _index_template($template) {
		if (get_post_type() == W2DC_POST_TYPE || (!get_option('w2dc_is_home_page') && is_page($this->index_page_id))) {
			if (is_file(W2DC_PATH . 'templates/frontend/index-custom.tpl.php'))
				$template = W2DC_PATH . 'templates/frontend/index-custom.tpl.php';
			else
				$template = W2DC_PATH . 'templates/frontend/index.tpl.php';
		}

		return $template;
	}
	public function _search_template($template) {
		if (is_file(W2DC_PATH . 'templates/frontend/search-custom.tpl.php'))
			$template = W2DC_PATH . 'templates/frontend/search-custom.tpl.php';
		else
			$template = W2DC_PATH . 'templates/frontend/search.tpl.php';

		return $template;
	}
	public function _category_template($template) {
		if (is_file(W2DC_PATH . 'templates/frontend/category-custom.tpl.php'))
			$template = W2DC_PATH . 'templates/frontend/category-custom.tpl.php';
		else
			$template = W2DC_PATH . 'templates/frontend/category.tpl.php';

		return $template;
	}
	public function _tag_template($template) {
		if (is_file(W2DC_PATH . 'templates/frontend/tag-custom.tpl.php'))
			$template = W2DC_PATH . 'templates/frontend/tag-custom.tpl.php';
		else
			$template = W2DC_PATH . 'templates/frontend/tag.tpl.php';

		return $template;
	}
	
	public function listing_permalink($permalink, $post) {
		if ($post->post_type == W2DC_POST_TYPE) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks())
				if (get_option('w2dc_is_home_page') || !$this->index_page_id)
					return $this->index_page_url . $post->ID . '/' . $post->post_name;
				else
					return $this->index_page_url . $post->post_name;
			else
				return add_query_arg(W2DC_POST_TYPE, $post->post_name, $this->index_page_url);
		}
		return $permalink;
	}

	public function category_permalink($permalink, $category, $tax) {
		if ($tax == W2DC_CATEGORIES_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks())
				return $this->index_page_url . get_option('w2dc_category_slug') . '/' . $category->slug;
			else
				return add_query_arg(W2DC_CATEGORIES_TAX, $category->slug, $this->index_page_url);
		}
		return $permalink;
	}

	public function tag_permalink($permalink, $tag, $tax) {
		if ($tax == W2DC_TAGS_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks())
				return $this->index_page_url . get_option('w2dc_tag_slug') . '/' . $tag->slug;
			else
				return add_query_arg(W2DC_TAGS_TAX, $tag->slug, $this->index_page_url);
		}
		return $permalink;
	}

	public function register_post_type() {
		$args = array(
			'labels' => array(
				'name' => __('Directory listings', 'W2DC'),
				'singular_name' => __('Directory listing', 'W2DC'),
				'add_new' => __('Create new listing', 'W2DC'),
				'add_new_item' => __('Create new listing', 'W2DC'),
				'edit_item' => __('Edit listing', 'W2DC'),
				'new_item' => __('New listing', 'W2DC'),
				'view_item' => __('View listing', 'W2DC'),
				'search_items' => __('Search listings', 'W2DC'),
				'not_found' =>  __('No listings found', 'W2DC'),
				'not_found_in_trash' => __('No listings found in trash', 'W2DC')
			),
			'has_archive' => true,
			'description' => __('Directory listings', 'W2DC'),
			'public' => true,
			'supports' => array('title', 'editor', 'author', 'categories', 'tags', 'excerpt', 'comments'),
			'menu_icon' => W2DC_URL . 'resources/images/menuicon.png',
		);
		register_post_type(W2DC_POST_TYPE, $args);
		
		register_taxonomy(W2DC_CATEGORIES_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => true,
				'has_archive' => true,
				'labels' => array(
					'name' =>  __('Listing categories', 'W2DC'),
					'menu_name' =>  __('Directory categories', 'W2DC'),
					'singular_name' => __('Category', 'W2DC'),
					'add_new_item' => __('Create category', 'W2DC'),
					'new_item_name' => __('New category', 'W2DC'),
					'edit_item' => __('Edit category', 'W2DC'),
					'view_item' => __('View category', 'W2DC'),
					'update_item' => __('Update category', 'W2DC'),
					'search_items' => __('Search categories', 'W2DC'),
				),
			)
		);
		register_taxonomy(W2DC_LOCATIONS_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => true,
				'labels' => array(
					'name' =>  __('Listing locations', 'W2DC'),
					'menu_name' =>  __('Directory locations', 'W2DC'),
					'singular_name' => __('Location', 'W2DC'),
					'add_new_item' => __('Create location', 'W2DC'),
					'new_item_name' => __('New location', 'W2DC'),
					'edit_item' => __('Edit location', 'W2DC'),
					'view_item' => __('View location', 'W2DC'),
					'update_item' => __('Update location', 'W2DC'),
					'search_items' => __('Search locations', 'W2DC'),
					
				),
			)
		);
		register_taxonomy(W2DC_TAGS_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => false,
				'labels' => array(
					'name' =>  __('Listing tags', 'W2DC'),
					'menu_name' =>  __('Directory tags', 'W2DC'),
					'singular_name' => __('Tag', 'W2DC'),
					'add_new_item' => __('Create tag', 'W2DC'),
					'new_item_name' => __('New tag', 'W2DC'),
					'edit_item' => __('Edit tag', 'W2DC'),
					'view_item' => __('View tag', 'W2DC'),
					'update_item' => __('Update tag', 'W2DC'),
					'search_items' => __('Search tags', 'W2DC'),
				),
			)
		);
	}
	
	public function highlight_menu_item() {
		if (!get_option('w2dc_is_home_page'))
			echo "<script>
				jQuery(document).ready(function($) {
					$('.page-item-" . $this->index_page_id . "').addClass('current_page_item');
				});
			</script>
			";
	}
	
	public function suspend_expired_listings() {
		global $wpdb;

		$posts_ids = $wpdb->get_col($wpdb->prepare("
				SELECT
					wp_pm1.post_id
				FROM
					{$wpdb->postmeta} AS wp_pm1
				LEFT JOIN
					{$wpdb->postmeta} AS wp_pm2 ON wp_pm1.post_id=wp_pm2.post_id
				WHERE
					wp_pm1.meta_key = '_expiration_date' AND
					wp_pm1.meta_value < %d AND
					wp_pm2.meta_key = '_listing_status' AND
					(wp_pm2.meta_value = 'active' OR wp_pm2.meta_value = 'stopped')
			", mktime()));
		foreach ($posts_ids AS $post_id) {
			if (!get_post_meta($this->current_listing->post->ID, '_expiration_notification_sent', true)) {
				$post = get_post($post_id);
				$listing_owner = get_userdata($post->post_author);
			
				$headers =  "MIME-Version: 1.0\n" .
						"From: get_option('blogname' <" . get_option('admin_email') . ">\n" .
						"Reply-To: get_option('admin_email')\n" .
						"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";
			
				$subject = "[" . get_option('blogname') . "] " . __('Expiration notification', 'W2DC');
			
				$body = str_replace('[listing]', $post->post_title, str_replace('[link]', admin_url('options.php?page=w2dc_renew&listing_id=' . $post->ID), get_option('w2dc_expiration_notification')));
			
				if (wp_mail($listing_owner->user_email, $subject, $body, $headers))
					add_post_meta($post_id, '_expiration_notification_sent', true);
				
				update_post_meta($post_id, '_listing_status', 'expired');
				wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));
			}
		}

		$posts_ids = $wpdb->get_col($wpdb->prepare("
				SELECT
					wp_pm1.post_id
				FROM
					{$wpdb->postmeta} AS wp_pm1
				LEFT JOIN
					{$wpdb->postmeta} AS wp_pm2 ON wp_pm1.post_id=wp_pm2.post_id
				WHERE
					wp_pm1.meta_key = '_expiration_date' AND
					wp_pm1.meta_value < %d AND
					wp_pm2.meta_key = '_listing_status' AND
					(wp_pm2.meta_value = 'active' OR wp_pm2.meta_value = 'stopped')
			", mktime()+(get_option('w2dc_send_expiration_notification_days')*86400)));
		foreach ($posts_ids AS $post_id) {
			if (!get_post_meta($post_id, '_preexpiration_notification_sent', true)) {
				$post = get_post($post_id);
				$listing_owner = get_userdata($post->post_author);
				
				$headers =  "MIME-Version: 1.0\n" .
						"From: get_option('blogname' <" . get_option('admin_email') . ">\n" .
						"Reply-To: get_option('admin_email')\n" .
						"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

				$subject = "[" . get_option('blogname') . "] " . __('Expiration notification', 'W2DC');
				
				$body = str_replace('[listing]', $post->post_title, str_replace('[days]', get_option('w2dc_send_expiration_notification_days'), get_option('w2dc_preexpiration_notification')));
				
				if (wp_mail($listing_owner->user_email, $subject, $body, $headers))
					add_post_meta($post_id, '_preexpiration_notification_sent', true);
			}
		}
	}

	public function enqueue_scripts_styles() {
		if (!is_null($this->frontend_controller->page_title)) {
			if (is_file(W2DC_RESOURCES_PATH . 'css/frontend-custom.css'))
				$css_file = W2DC_RESOURCES_URL . 'css/frontend-custom.css';
			elseif (is_file(W2DC_RESOURCES_PATH . 'css/frontend.css'))
				$css_file = W2DC_RESOURCES_URL . 'css/frontend.css';
			wp_enqueue_style('w2dc_frontend', $css_file);

			wp_enqueue_script('js_functions', W2DC_RESOURCES_URL . 'js/js_functions.js', array('jquery'));
			wp_localize_script(
					'js_functions',
					'js_objects',
					array(
							'ajaxurl' => admin_url('admin-ajax.php'),
							'ajax_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-loader.gif',
					)
			);
			
			if (is_single()) {
				wp_enqueue_style('media_styles', W2DC_RESOURCES_URL . 'lightbox/css/lightbox.css');
				wp_enqueue_script('media_scripts', W2DC_RESOURCES_URL . 'lightbox/js/lightbox-2.6.min.js', array('jquery'));
				
				if (get_option('w2dc_images_on_tab')) {
					wp_enqueue_style('carousel_style', W2DC_RESOURCES_URL . 'css/anythingslider/css/anythingslider.css');
					wp_enqueue_style('carousel_theme', W2DC_RESOURCES_URL . 'css/anythingslider/css/theme-minimalist-round.css');
					wp_enqueue_script('carousel_scripts', W2DC_RESOURCES_URL . 'js/jquery.anythingslider.min.js', array('jquery'));
				}
			}

			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_script('jquery-ui-autocomplete');
			// this jQuery UI version 1.10.3 is for WP v3.6.0
			wp_enqueue_style('jquery-ui-style', W2DC_RESOURCES_URL . 'css/jquery-ui.css');
			
			wp_enqueue_script('google_maps', 'http://maps.google.com/maps/api/js?v=3.14&sensor=false&language=en', array('jquery'));
			wp_enqueue_script('google_maps_view', W2DC_RESOURCES_URL . 'js/google_maps_view.js', array('jquery'));
		}
	}
}

$w2dc_instance = new w2dc_plugin();
$w2dc_instance->init();

?>
