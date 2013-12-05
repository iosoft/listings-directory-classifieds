<?php

class search_form {
	
	public function __construct() {
		add_filter('w2dc_order_args', array($this, 'order_listings'));

		add_filter('w2dc_search_args', array($this, 'what_search'));
		add_filter('w2dc_search_args', array($this, 'where_search'));
		add_filter('w2dc_search_args', array($this, 'locations_search'));

		add_filter('w2dc_base_url_args', array($this, 'base_url_args'));
	}

	public function order_listings($order_args = array()) {
		global $w2dc_instance;

		if (isset($_GET['order']) && ($_GET['order'] == 'ASC' || $_GET['order'] == 'DESC'))
			$order_args['order'] = $_GET['order'];
		else
			$order_args['order'] = 'ASC';
		
		if (!isset($_GET['order_by']) || $_GET['order_by'] == 'post_date') {
			// First of all order by _order_date parameter
			$order_args['orderby'] = 'meta_value_num';
			$order_args['meta_key'] = '_order_date';
			if (isset($_GET['order']) && ($_GET['order'] == 'ASC' || $_GET['order'] == 'DESC'))
				$order_args['order'] = $_GET['order'];
			else
				$order_args['order'] = 'DESC';
		} elseif ($_GET['order_by'] == 'title') {
			$order_args['orderby'] = 'title';
		} else
			$order_args = array_merge($order_args, $w2dc_instance->content_fields->getOrderParams());

		return $order_args;
	}
	
	public function what_search($args) {
		if (isset($_GET['what_search']) && $_GET['what_search'])
			$args['s'] = $_GET['what_search'];
		
		return $args;
	}

	public function where_search($args) {
		if (isset($_GET['where_search']) && $_GET['where_search']) {
			$where_search = $_GET['where_search'];

			global $wpdb;
			$results = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM wp_w2dc_locations_relationships WHERE address_line_1 LIKE '%%%s%%' OR address_line_2 LIKE '%%%s%%' OR zip_or_postal_index LIKE '%%%s%%'", $where_search, $where_search, $where_search), ARRAY_A);
			$post_ids = array();
			foreach ($results AS $row)
				$post_ids[] = $row['post_id']; 
			if ($post_ids)
				$args['post__in'] = $post_ids;
		}
		
		return $args;
	}
	
	public function locations_search($args) {
		if (isset($_GET['search_location']) && $_GET['search_location'] && is_numeric($_GET['search_location'])) {
			$term_ids = get_terms(W2DC_LOCATIONS_TAX, array('child_of' => $_GET['search_location'], 'fields' => 'ids', 'hide_empty' => false));
			$term_ids[] = $_GET['search_location'];
			
			global $wpdb;
			$results = $wpdb->get_results('SELECT post_id FROM wp_w2dc_locations_relationships WHERE location_id IN (' . implode(', ', $term_ids) . ')', ARRAY_A);
			$post_ids = array();
			foreach ($results AS $row)
				$post_ids[] = $row['post_id'];

			if ($post_ids)
				$args['post__in'] = $post_ids;
			else
				// Do not show any listings
				$args['post__in'] = array(0);
		}
		return $args;
	}
	
	public function base_url_args($args) {
		if (isset($_GET['what_search']) && $_GET['what_search'])
			$args['what_search'] = $_GET['what_search'];
		if (isset($_GET['where_search']) && $_GET['where_search'])
			$args['where_search'] = $_GET['where_search'];
		if (isset($_GET['search_location']) && $_GET['search_location'] && is_numeric($_GET['search_location']))
			$args['search_location'] = $_GET['search_location'];
		
		return $args;
	}
	
	public function display() {
		w2dc_renderTemplate('search_form.tpl.php');
	}
}
?>