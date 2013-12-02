<?php 

class w2dc_frontend_controller {
	public $query;
	public $page_title;
	public $template;
	public $listings = array();
	public $google_map;
	public $paginator;
	public $breadcrumbs = array();
	public $base_url;
	public $messages = array();
	
	public $is_home = false;
	public $is_search = false;
	public $is_single = false;
	public $is_category = false;
	public $is_tag = false;

	public function __construct() {
		global $w2dc_instance;

		$paged = (get_query_var('page')) ? get_query_var('page') : 1;

		if (get_query_var('listing')) {
			$args = array(
					'post_type' => W2DC_POST_TYPE,
					'post_status' => 'publish',
					'name' => get_query_var('listing'),
					'posts_per_page' => 1,
			);
			$this->query = new WP_Query($args);
			$this->processQuery();

			if (count($this->listings) == 1) {
				$this->is_single = true;
				$this->template = 'frontend/listing_single.tpl.php';
				
				$listings_array = $this->listings;
				$listing = array_shift($listings_array);
				$this->listing = $listing;
				$this->page_title = $listing->title();
				$this->breadcrumbs = array(
						'<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>',
						$listing->title()
				);
				if (get_option('w2dc_listing_contact_form') && $w2dc_instance->action == 'contact')
					$this->contactOwnerAction($listing->post);
			} else {
				status_header(404);
				nocache_headers();
				include(get_404_template());
				exit;
			}
		} elseif ($w2dc_instance->action == 'search') {
			$this->is_search = true;
			$this->template = 'frontend/search.tpl.php';

			$this->search_form = new search_form();
			$order_args = apply_filters('w2dc_order_args', array());

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

			$this->page_title = __('Search results', 'W2DC');
			$this->breadcrumbs = array(
					'<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>',
					__('Search results', 'W2DC')
			);
			$this->base_url = add_query_arg($base_url_args, $w2dc_instance->index_page_url);
		} elseif (get_query_var('category')) {
			if ($category_object = get_term_by('slug', get_query_var('category'), W2DC_CATEGORIES_TAX)) {
				$this->is_category = true;
				$this->category = $category_object;
				$this->search_form = new search_form();
				$order_args = apply_filters('w2dc_order_args', array());

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

				$this->template = 'frontend/category.tpl.php';

				$this->page_title = $category_object->name;
				$this->breadcrumbs = array_merge(
						array('<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>'),
						w2dc_get_term_parents($category_object, W2DC_CATEGORIES_TAX, true, true)
				);
				$this->base_url = get_term_link($category_object, W2DC_CATEGORIES_TAX);
			} else {
				status_header(404);
				nocache_headers();
				include(get_404_template());
				exit;
			}
		} elseif (get_query_var('tag')) {
			if ($tag_object = get_term_by('slug', get_query_var('tag'), W2DC_TAGS_TAX)) {
				$this->is_tag = true;
				$this->tag = $tag_object;
				$this->search_form = new search_form();
				$order_args = apply_filters('w2dc_order_args', array());

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

				$this->template = 'frontend/tag.tpl.php';
	
				$this->page_title = $tag_object->name;
				$this->breadcrumbs = array_merge(
						array('<a href="' . $w2dc_instance->index_page_url . '">' . __('Home', 'W2DC') . '</a>'),
						w2dc_get_term_parents($tag_object, W2DC_TAGS_TAX, true, true)
				);
				$this->base_url = get_term_link($tag_object, W2DC_TAGS_TAX);
			} else {
				status_header(404);
				nocache_headers();
				include(get_404_template());
				exit;
			}
		} elseif (!$w2dc_instance->action) {
			$this->is_home = true;
			$this->search_form = new search_form();
			$order_args = apply_filters('w2dc_order_args', array());

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
			
			$this->template = 'frontend/index.tpl.php';

			$this->query = new WP_Query($args);
			$this->processQuery(get_option('w2dc_map_on_index'));
			$this->base_url = $w2dc_instance->index_page_url;
		}
		
		apply_filters('w2dc_frontend_controller_contruct', $this);
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
		$this->google_map = new google_maps;
		while ($this->query->have_posts()) {
			$this->query->the_post();

			$listing = new w2dc_listing;
			$listing->loadListingFromPost(get_post());

			if ($load_map)
				$this->google_map->collectLocations($listing);
			
			$this->listings[get_the_ID()] = $listing;
		}
		// this is reset is really required after the loop ends 
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
					w2dc_addMessage(__('You message was sent successfully!', 'W2DC'));
				else
					w2dc_addMessage(__('An error occurred and your message wasn\t sent!', 'W2DC'), 'error');
			} else {
				w2dc_addMessage(__('Verification code wasn\'t entered correctly!', 'W2DC'), 'error');
			}
		} else {
			w2dc_addMessage($validation->error_string(), 'error');
		}
	}
}
?>