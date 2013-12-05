<?php
/*
Plugin Name: Directory & Classifieds plugin
Plugin URI: http://www.salephpscripts.com/wordpress_directory/
Description: Provides an ability to build any kind of directory site: classifieds, events directory, cars, bikes, boats and other vehicles dealers site, pets, real estate portal on your WordPress powered site. In other words - whatever you want.
Version: 1.1.4
Author: Mihail Chepovskiy
Author URI: http://www.salephpscripts.com
License: GPLv2 or any later version
*/

define('W2DC_VERSION', '1.1.4');

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
include_once W2DC_PATH . 'classes/ajax_controller.php';
include_once W2DC_PATH . 'classes/settings_manager.php';
include_once W2DC_PATH . 'classes/search_form.php';
include_once W2DC_PATH . 'classes/google_maps.php';
include_once W2DC_PATH . 'functions.php';
include_once W2DC_PATH . 'functions_ui.php';

global $w2dc_instance;
global $w2dc_messages;

class w2dc_plugin {
	public $admin;
	public $listings_manager;
	public $locations_manager;
	public $locations_levels_manager;
	public $categories_manager;
	public $content_fields_manager;
	public $media_manager;
	public $settings_manager;
	public $levels_manager;

	public $current_listing; // this is object of listing under edition right now
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

		$_GET = stripslashes_deep($_GET);
		if (isset($_REQUEST['action']))
			$this->action = $_REQUEST['action'];

		add_action('plugins_loaded', array($this, 'load_textdomain'), 0);
		
		add_action('sheduled_events', array($this, 'suspend_expired_listings'));

		add_shortcode('webdirectory', array($this, 'renderDirectory'));

		add_action('init', array($this, 'register_post_type'), 0);
		add_action('init', array($this, 'getIndexPage'), 0);
		// use 'get_header' hook instead of 'wp' hook
		add_action('get_header', array($this, 'loadFrontendController'));

		$w2dc_instance->levels = new w2dc_levels;
		$w2dc_instance->locations_levels = new w2dc_locations_levels;
		$w2dc_instance->content_fields = new w2dc_content_fields;

		$w2dc_instance->ajax_controller = new w2dc_ajax_controller;

		$this->admin = new w2dc_admin();

		if (!is_admin()) {
			add_filter('template_include', array($this, 'printlisting_template'));

			add_action('get_header', array($this, 'configure_seo_filters'));
			add_action('wp_loaded', array($this, 'wp_loaded'));
			add_filter('query_vars', array($this, 'add_query_vars'));
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
	
	public function add_query_vars($vars) {
		$vars[] = 'listing';
		$vars[] = 'category';
		$vars[] = 'tag';
		
		$key = array_search('order', $vars);
		unset($vars[$key]);

		return $vars;
	}
	
	public function renderDirectory() {
		$output =  w2dc_renderTemplate($this->frontend_controller->template, array('frontend_controller' => $this->frontend_controller), true);
		// this is reset is really required after the loop ends
		wp_reset_postdata();
		return $output;
	}

	public function loadFrontendController() {
		if (is_page($this->index_page_id))
			$this->frontend_controller = new w2dc_frontend_controller();
	}

	public function getIndexPage() {
		if ($array = w2dc_getIndexPage()) {
			$this->index_page_id = $array['id'];
			$this->index_page_slug = $array['slug'];
			$this->index_page_url = $array['url'];
		}
		
		if ($this->index_page_id === 0)
			w2dc_addMessage(sprintf(__('<b>Directory & Classifieds plugin</b>: sorry, but there isn\'t any page with [webdirectory] shortcode. Create <a href="%s">this special page</a> for you?', 'W2DC'), admin_url('admin.php?page=w2dc_admin&action=directory_page_installation')));
	}
	
	public function configure_seo_filters() {
		if (isset($this->frontend_controller) && $this->frontend_controller->query) {
			add_filter('wp_title', array($this, 'page_title'), 10, 2);
			if (defined('WPSEO_VERSION')) {
				global $wpseo_front;
				remove_filter('wp_title', array(&$wpseo_front, 'title'), 15, 3);
				remove_action('wp_head', array(&$wpseo_front, 'head'), 1, 1);
				
				add_action('wp_head', array( $this, 'page_meta'));
			}
		}
	}
	
	public function page_meta() {
		global $wpseo_front;
		if ($this->frontend_controller->is_single) {
			global $post;
			$saved_page = $post;
			$post = get_post($this->frontend_controller->listing->post->ID);

			$wpseo_front->metadesc();
			$wpseo_front->metakeywords();

			$post = $saved_page;
		} elseif ($this->frontend_controller->is_category) {
			$metadesc = wpseo_get_term_meta($this->frontend_controller->category, $this->frontend_controller->category->taxonomy, 'desc');
			if (!$metadesc && isset($wpseo_front->options['metadesc-' . $this->frontend_controller->category->taxonomy]))
				$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-' . $this->frontend_controller->category->taxonomy], (array) $this->frontend_controller->category );
			$metadesc = apply_filters('wpseo_metadesc', trim($metadesc));
			echo '<meta name="description" content="' . esc_attr(strip_tags(stripslashes($metadesc))) . '"/>' . "\n";
		} elseif ($this->frontend_controller->is_tag) {
			$metadesc = wpseo_get_term_meta($this->frontend_controller->tag, $this->frontend_controller->tag->taxonomy, 'desc');
			if (!$metadesc && isset($wpseo_front->options['metadesc-' . $this->frontend_controller->tag->taxonomy]))
				$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-' . $this->frontend_controller->tag->taxonomy], (array) $this->frontend_controller->tag );
			$metadesc = apply_filters('wpseo_metadesc', trim($metadesc));
			echo '<meta name="description" content="' . esc_attr(strip_tags(stripslashes($metadesc))) . '"/>' . "\n";
		} elseif ($this->frontend_controller->is_home) {
			$wpseo_front->metadesc();
			$wpseo_front->metakeywords();
		}
	}

	public function page_title($title, $separator) {
		if (defined('WPSEO_VERSION')) {
			global $wpseo_front;
			if ($this->frontend_controller->is_single) {
				$title = $wpseo_front->get_content_title(get_post($this->frontend_controller->listing->post->ID));
				return esc_html(strip_tags(stripslashes(apply_filters('wpseo_title', $title))));
			} elseif ($this->frontend_controller->is_search) {
				return $wpseo_front->get_title_from_options('title-search');
			} elseif ($this->frontend_controller->is_category) {
				$title = trim(wpseo_get_term_meta($this->frontend_controller->category, $this->frontend_controller->category->taxonomy, 'title'));
				if (!empty($title))
					return wpseo_replace_vars($title, (array)$this->frontend_controller->category);
				return $wpseo_front->get_title_from_options('title-' . $this->frontend_controller->category->taxonomy, $this->frontend_controller->category);
			} elseif ($this->frontend_controller->is_tag) {
				$title = trim(wpseo_get_term_meta($this->frontend_controller->tag, $this->frontend_controller->tag->taxonomy, 'title'));
				if (!empty($title))
					return wpseo_replace_vars($title, (array)$this->frontend_controller->tag);
				return $wpseo_front->get_title_from_options('title-' . $this->frontend_controller->tag->taxonomy, $this->frontend_controller->tag);
			} elseif ($this->frontend_controller->is_home) {
				$page = get_post($this->index_page_id);
				return $wpseo_front->get_title_from_options('title-' . W2DC_POST_TYPE, (array) $page);
			}
		} else {
			if ($this->frontend_controller->getPageTitle()) {
				if (get_option('w2dc_directory_title'))
					if ($this->frontend_controller->getPageTitle() == get_option('w2dc_directory_title'))
						$directory_title = '';
					else
						$directory_title = get_option('w2dc_directory_title');
				else
					$directory_title = '';
				
				if ($title != __('Directory listings', 'W2DC') . ' ' . $separator . ' ')
					return $this->frontend_controller->getPageTitle() . ' ' . $separator . ' ' . $directory_title;
				else
					return $directory_title;
			}
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
		
		$rules['(' . $page_url . ')/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&paged=$matches[2]';
		$rules['(' . $page_url . ')/?$'] = 'index.php?page_id=' .  $this->index_page_id;
		
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_category_slug') . '/([^\/.]+)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&category=$matches[2]&paged=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_category_slug') . '/([^\/.]+)/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&category=$matches[2]';
		
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_tag_slug') . '/([^\/.]+)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&tag=$matches[2]&paged=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_tag_slug') . '/([^\/.]+)/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&tag=$matches[2]';

		$rules[$page_url . '/([^\/.]+)/?$'] = 'index.php?page_id=' . $this->index_page_id . '&listing=$matches[1]';
		$rules['([0-9]{1,})/([^\/.]+)/?$'] = 'index.php?page_id=' . $this->index_page_id . '&listing=$matches[2]';

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

	public function listing_permalink($permalink, $post) {
		if ($post->post_type == W2DC_POST_TYPE) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks())
				if (get_option('page_on_front') == $this->index_page_id)
					return $this->index_page_url . $post->ID . '/' . $post->post_name;
				else
					return $this->index_page_url . $post->post_name;
			else
				return add_query_arg('listing', $post->post_name, $this->index_page_url);
		}
		return $permalink;
	}

	public function category_permalink($permalink, $category, $tax) {
		if ($tax == W2DC_CATEGORIES_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks())
				return $this->index_page_url . get_option('w2dc_category_slug') . '/' . $category->slug;
			else
				return add_query_arg('category', $category->slug, $this->index_page_url);
		}
		return $permalink;
	}

	public function tag_permalink($permalink, $tag, $tax) {
		if ($tax == W2DC_TAGS_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks())
				return $this->index_page_url . get_option('w2dc_tag_slug') . '/' . $tag->slug;
			else
				return add_query_arg('tag', $tag->slug, $this->index_page_url);
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
		
		if (!get_option('w2dc_installed_directory') || get_option('w2dc_installed_directory_version') != W2DC_VERSION)
			w2dc_install_directory();
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

	/**
	 * Special template for listings printing functionality
	 */
	public function printlisting_template($template) {
		if (is_page($this->index_page_id) && $this->action == 'printlisting') {
			if (is_file(W2DC_PATH . 'templates/frontend/listing_print-custom.tpl.php'))
				$template = W2DC_PATH . 'templates/frontend/listing_print-custom.tpl.php';
			else
				$template = W2DC_PATH . 'templates/frontend/listing_print.tpl.php';
		}
		return $template;
	}

	public function enqueue_scripts_styles() {
		if (!is_null($this->frontend_controller)) {
			wp_enqueue_style('w2dc_frontend', W2DC_RESOURCES_URL . 'css/frontend.css');
			if (is_file(W2DC_RESOURCES_PATH . 'css/frontend-custom.css'))
				wp_enqueue_style('w2dc_frontend-custom', W2DC_RESOURCES_URL . 'css/frontend-custom.css');

			wp_enqueue_script('js_functions', W2DC_RESOURCES_URL . 'js/js_functions.js', array('jquery'));
			wp_localize_script(
					'js_functions',
					'js_objects',
					array(
							'ajaxurl' => admin_url('admin-ajax.php'),
							'ajax_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-loader.gif',
							'in_favourites_icon' => W2DC_RESOURCES_URL . 'images/folder_star.png',
							'not_in_favourites_icon' => W2DC_RESOURCES_URL . 'images/folder_star_grscl.png',
							'in_favourites_msg' => __('Put in favourites list', 'W2DC'),
							'not_in_favourites_msg' => __('Out of favourites list', 'W2DC'),
					)
			);
			
			if ($this->frontend_controller->is_single) {
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
			// this jQuery UI version 1.10.3 is for WP v3.7.1
			wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css');
			
			wp_enqueue_script('google_maps', '//maps.google.com/maps/api/js?v=3.14&sensor=false&language=en', array('jquery'));
			wp_enqueue_script('google_maps_view', W2DC_RESOURCES_URL . 'js/google_maps_view.js', array('jquery'));
			
			wp_enqueue_script('jquery_cookie', W2DC_RESOURCES_URL . 'js/jquery.coo_kie.js', array('jquery'));
		}
	}
}

$w2dc_instance = new w2dc_plugin();
$w2dc_instance->init();

?>
