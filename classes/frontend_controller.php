<?php 

class w2dc_frontend_controller {
	public $query;
	public $page_title;
	public $listings = array();
	public $google_map;
	public $paginator;
	public $breadcrumbs = array();
	public $base_url;
	public $messages = array();

	public function __construct() {
		global $w2dc_instance;

		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

		if (is_single() && get_post_type() == W2DC_POST_TYPE) {
			global $wp_query;
			$this->query = $wp_query;

			add_filter('single_template', array($w2dc_instance, '_listing_template'));
			add_action('wp_head', array($w2dc_instance, 'highlight_menu_item'));

			$this->listings[get_the_ID()] = new w2dc_listing;
			if ($this->listings[get_the_ID()]->loadListingFromPost(get_post())) {
				$this->page_title = $this->listings[get_the_ID()]->title();
				$this->breadcrumbs = array(
						'<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>',
						$this->listings[get_the_ID()]->title()
				);
				if ($w2dc_instance->action == 'contact')
					$this->contactOwnerAction(get_post());
			} else {
				status_header(404);
				nocache_headers();
				include(get_404_template());
				exit;
			}
		} elseif ($w2dc_instance->action == 'search' && is_search()) {
			
			$this->search_form = new search_form();
			$order_args = apply_filters('w2dc_order_args', array());
			
			add_filter('search_template', array($w2dc_instance, '_search_template'));
			add_action('wp_head', array($w2dc_instance, 'highlight_menu_item'));
			
			add_filter('posts_join', array($this, 'join_levels'));
			add_filter('posts_orderby', array($this, 'orderby_levels'), 1);
			$args = array(
					'post_type' => W2DC_POST_TYPE,
					'post_status' => 'publish',
					'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
					'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
					'paged' => $paged,
			);
			$args = array_merge($args, $order_args);
			$args = apply_filters('w2dc_search_args', $args);
			$base_url_args = apply_filters('w2dc_base_url_args', array('action' => 'search'));
			
			$this->query = new WP_Query($args);
			$this->processQuery(get_option('w2dc_map_on_excerpt'));

			//var_dump($this->query->request);
			
			$this->page_title = __('Search results', 'W2DC');
			$this->breadcrumbs = array(
					'<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>',
					__('Search results', 'W2DC')
			);
			$this->base_url = add_query_arg($base_url_args, $w2dc_instance->index_page_url);
		} elseif (is_tax() && get_query_var('taxonomy') == W2DC_CATEGORIES_TAX) {
			$category_object = get_queried_object();
			$this->search_form = new search_form();
			$order_args = apply_filters('w2dc_order_args', array());

			add_filter('taxonomy_template', array($w2dc_instance, '_category_template'));
			add_action('wp_head', array($w2dc_instance, 'highlight_menu_item'));
			add_filter('posts_join', array($this, 'join_levels'));
			add_filter('posts_orderby', array($this, 'orderby_levels'), 1);
			$args = array(
					'tax_query' => array(
							array(
								'taxonomy' => W2DC_CATEGORIES_TAX,
								'field' => 'slug',
								'terms' => $category_object->slug,
							)
					),
					'post_type' => W2DC_POST_TYPE,
					'post_status' => 'publish',
					'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
					'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
					'paged' => $paged,
			);
			$args = array_merge($args, $order_args);

			$this->query = new WP_Query($args);
			$this->processQuery(get_option('w2dc_map_on_excerpt'));

			$this->page_title = $category_object->name;
			$this->breadcrumbs = array_merge(
					array('<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>'),
					w2dc_get_term_parents($category_object, W2DC_CATEGORIES_TAX, true, true)
			);
			$this->base_url = get_term_link($category_object, W2DC_CATEGORIES_TAX);
		} elseif (is_tax() && get_query_var('taxonomy') == W2DC_TAGS_TAX) {
			$tag_object = get_queried_object();
			$this->search_form = new search_form();
			$order_args = apply_filters('w2dc_order_args', array());

			add_filter('taxonomy_template', array($w2dc_instance, '_tag_template'));
			add_action('wp_head', array($w2dc_instance, 'highlight_menu_item'));
			add_filter('posts_join', array($this, 'join_levels'));
			add_filter('posts_orderby', array($this, 'orderby_levels'), 1);
			$args = array(
					'tax_query' => array(
							array(
									'taxonomy' => W2DC_TAGS_TAX,
									'field' => 'slug',
									'terms' => $tag_object->slug,
							)
					),
					'post_type' => W2DC_POST_TYPE,
					'post_status' => 'publish',
					'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
					'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
					'paged' => $paged,
			);
			$args = array_merge($args, $order_args);

			$this->query = new WP_Query($args);
			$this->processQuery(get_option('w2dc_map_on_excerpt'));

			$this->page_title = $tag_object->name;
			$this->breadcrumbs = array_merge(
					array('<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>'),
					w2dc_get_term_parents($tag_object, W2DC_TAGS_TAX, true, true)
			);
			$this->base_url = get_term_link($tag_object, W2DC_TAGS_TAX);
		} elseif (get_query_var('post_type') == W2DC_POST_TYPE || (!get_option('w2dc_is_home_page') && $w2dc_instance->index_page_id && is_page($w2dc_instance->index_page_id))) {
			$this->search_form = new search_form();
			$order_args = apply_filters('w2dc_order_args', array());

			add_filter('template_include', array($w2dc_instance, '_index_template'));
			add_action('wp_head', array($w2dc_instance, 'highlight_menu_item'));

			add_filter('posts_join', array($this, 'join_levels'));
			add_filter('posts_orderby', array($this, 'orderby_levels'), 1);
			$args = array(
					'post_type' => W2DC_POST_TYPE,
					'post_status' => 'publish',
					'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
					'posts_per_page' => get_option('w2dc_listings_number_index'),
					'paged' => $paged,
			);
			$args = array_merge($args, $order_args);

			$this->query = new WP_Query($args);
			$this->processQuery(get_option('w2dc_map_on_index'));
			$this->page_title = get_option('w2dc_directory_title');
			$this->base_url = $w2dc_instance->index_page_url;
		}
	}

	public function join_levels($join = '') {
		global $wpdb;
	
		$join .= " LEFT JOIN `wp_w2dc_levels_relationships` AS w2dc_lr ON w2dc_lr.post_id = $wpdb->posts.ID ";
		$join .= " LEFT JOIN `wp_w2dc_levels` AS w2dc_levels ON w2dc_levels.id = w2dc_lr.level_id ";
	
		return $join;
	}
	
	public function orderby_levels($orderby = '') {
		$orderby = " w2dc_levels.sticky DESC, w2dc_levels.featured DESC, " . $orderby;
		return $orderby;
	}
	
	public function processQuery($load_map = true) {
		global $w2dc_instance;

		$this->google_map = new google_maps;
		while ($this->query->have_posts()) {
			$this->query->the_post();
		
			$listing = new w2dc_listing;
			$listing->loadListingFromPost($this->query->post);

			if ($load_map)
				$this->google_map->collectLocations($listing);
			
			$this->listings[get_the_ID()] = $listing;
		}

		wp_reset_postdata();
	}
	
	public function getQuery() {
		return $this->query;
	}
	
	public function getPageTitle() {
		return $this->page_title;
	}

	public function getBreadCrumbs() {
		return implode(' » ', $this->breadcrumbs);
	}

	public function getBaseUrl() {
		return $this->base_url;
	}
	
	public function renderPaginator() {
		// adapted for WP-PageNavi
		if (function_exists('wp_pagenavi'))
			wp_pagenavi(array('query' => $this->getQuery()));
		else {
			$big = 999999999;
			$this->paginator =  paginate_links( array(
					'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
					'format' => '?paged=%#%',
					'current' => max(1, get_query_var('paged')),
					'total' => $this->query->max_num_pages,
					'end_size' => 1,
					'mid_size' => 5,
			));

			echo $this->paginator;
		}
	}

	/*
	 * there is a serious problem with global variables outside WP shortcode callback,
	 * so we have to especially collect and then set all these messages inside callback
	 */
	/* public function setMessages() {
		foreach ($this->messages AS $type=>$messages_array)
			foreach ($messages_array AS $message)
				w2dc_addMessage($message, $type);
	} */
	
	public function contactOwnerAction($post) {
		$validation = new form_validation;
		if (!($current_user = wp_get_current_user())) {
			$validation->set_rules('contact_name', __('Contact name', 'W2DC'), 'required');
			$validation->set_rules('contact_email', __('Contact email', 'W2DC'), 'required|valid_email');
		}
		$validation->set_rules('contact_message', __('Your message', 'W2DC'), 'required|max_length[1500]');
		if ($validation->run()) {
			if (!$current_user) {
				$contact_name = $validation->result_array('contact_name');
				$contact_email = $validation->result_array('contact_email');
			} else {
				$contact_name = $current_user->user_login;
				$contact_email = $current_user->user_email;
			}
			$contact_message = $validation->result_array('contact_message');

			if (w2dc_is_recaptcha_passed()) {
				$listing_owner = get_userdata($post->post_author);

				$headers =  "MIME-Version: 1.0\n" .
						"From: $contact_name <$contact_email>\n" .
						"Reply-To: $contact_email\n" .
						"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

				global $w2dc_instance;
				$subject = "[" . get_option('blogname') . "] " . sprintf(__('%s contacted you about your listing', 'W2DC'), $contact_name);

				$body = w2dc_renderTemplate('emails/contact_form.tpl.php',
						array(
								'contact_name' => $contact_name,
								'contact_email' => $contact_email,
								'contact_message' => $contact_message,
								'listing_title' => get_the_title($post),
								'listing_url' => get_permalink($post->ID)
						), true);

				if (wp_mail($listing_owner->user_email, $subject, $body, $headers))
					//$this->messages['updated'][] = __('You message was sent successfully!', 'W2DC');
					w2dc_addMessage(__('You message was sent successfully!', 'W2DC'), 'error');
				else
					//$this->messages['error'][] =  __('An error occurred and your message wasn\t sent', 'W2DC');
					w2dc_addMessage(__('An error occurred and your message wasn\t sent', 'W2DC'), 'error');
			} else {
				//$this->messages['error'][] =  __('Verification code wasn\'t entered correctly', 'W2DC');
				w2dc_addMessage(__('Verification code wasn\'t entered correctly', 'W2DC'), 'error');
			}
		} else {
			//$this->messages['error'][] = $validation->error_string();
			w2dc_addMessage($validation->error_string(), 'error');
		}
	}
}
?>