<?php 

function w2dc_install_directory() {
	global $wpdb;
	
	if (!get_option('w2dc_installed_directory')) {
		$wpdb->query("CREATE TABLE IF NOT EXISTS `wp_w2dc_content_fields` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`is_core_field` tinyint(1) NOT NULL DEFAULT '0',
					`order_num` tinyint(1) NOT NULL,
					`name` varchar(255) NOT NULL,
					`slug` varchar(255) NOT NULL,
					`description` text NOT NULL,
					`type` varchar(255) NOT NULL,
					`icon_image` varchar(255) NOT NULL,
					`is_required` tinyint(1) NOT NULL DEFAULT '0',
					`is_configuration_page` tinyint(1) NOT NULL DEFAULT '0',
					`is_search_configuration_page` tinyint(1) NOT NULL DEFAULT '0',
					`is_ordered` tinyint(1) NOT NULL DEFAULT '0',
					`is_hide_name` tinyint(1) NOT NULL DEFAULT '0',
					`on_exerpt_page` tinyint(1) NOT NULL DEFAULT '0',
					`on_listing_page` tinyint(1) NOT NULL DEFAULT '0',
					`on_search_form` tinyint(1) NOT NULL DEFAULT '0',
					`advanced_search_form` tinyint(1) NOT NULL,
					`categories` text NOT NULL,
					`options` text NOT NULL,
					`search_options` text NOT NULL,
					PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_content_fields` WHERE slug = 'summary'"))
			$wpdb->query("INSERT INTO `wp_w2dc_content_fields` (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `advanced_search_form`, `categories`, `options`, `search_options`) VALUES(1, 1, 'Summary', 'summary', '', 'excerpt', '', 0, 0, 0, 0, 0, 1, 1, 0, 0, '', '', '');");
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_content_fields` WHERE slug = 'address'"))
			$wpdb->query("INSERT INTO `wp_w2dc_content_fields` (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `advanced_search_form`, `categories`, `options`, `search_options`) VALUES(1, 2, 'Address', 'address', '', 'address', '', 0, 0, 0, 0, 0, 1, 1, 0, 0, '', '', '');");
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_content_fields` WHERE slug = 'content'"))
			$wpdb->query("INSERT INTO `wp_w2dc_content_fields` (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `advanced_search_form`, `categories`, `options`, `search_options`) VALUES(1, 3, 'Description', 'content', '', 'content', '', 0, 0, 0, 0, 0, 1, 1, 0, 0, '', '', '');");
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_content_fields` WHERE slug = 'categories_list'"))
			$wpdb->query("INSERT INTO `wp_w2dc_content_fields` (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `advanced_search_form`, `categories`, `options`, `search_options`) VALUES(1, 4, 'Categories list', 'categories_list', '', 'categories', '', 0, 0, 0, 0, 0, 1, 1, 0, 0, '', '', '');");
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_content_fields` WHERE slug = 'listing_tags'"))
			$wpdb->query("INSERT INTO `wp_w2dc_content_fields` (`is_core_field`, `order_num`, `name`, `slug`, `description`, `type`, `icon_image`, `is_required`, `is_configuration_page`, `is_search_configuration_page`, `is_ordered`, `is_hide_name`, `on_exerpt_page`, `on_listing_page`, `on_search_form`, `advanced_search_form`, `categories`, `options`, `search_options`) VALUES(1, 5, 'Listing tags', 'listing_tags', '', 'tags', '', 0, 0, 0, 0, 0, 1, 1, 0, 0, '', '', '');");
	
		$wpdb->query("CREATE TABLE IF NOT EXISTS `wp_w2dc_levels` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`order_num` tinyint(1) NOT NULL,
					`name` varchar(255) NOT NULL,
					`description` text NOT NULL,
					`active_years` tinyint(1) NOT NULL,
					`active_months` tinyint(1) NOT NULL,
					`active_days` tinyint(1) NOT NULL,
					`raiseup_enabled` tinyint(1) NOT NULL,
					`sticky` tinyint(1) NOT NULL,
					`featured` tinyint(1) NOT NULL,
					`categories_number` tinyint(1) NOT NULL,
					`unlimited_categories` tinyint(1) NOT NULL,
					`locations_number` tinyint(1) NOT NULL,
					`google_map` tinyint(1) NOT NULL,
					`google_map_markers` tinyint(1) NOT NULL,
					`logo_enabled` tinyint(1) NOT NULL,
					`logo_size` varchar(255) NOT NULL DEFAULT 'thumbnail',
					`images_number` tinyint(1) NOT NULL,
					`videos_number` tinyint(1) NOT NULL,
					`categories` text NOT NULL,
					PRIMARY KEY (`id`),
					KEY `order_num` (`order_num`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_levels` WHERE name = 'Standart'"))
			$wpdb->query("INSERT INTO `wp_w2dc_levels` (`order_num`, `name`, `description`, `active_years`, `active_months`, `active_days`, `raiseup_enabled`, `sticky`, `featured`, `categories_number`, `unlimited_categories`, `locations_number`, `google_map`, `google_map_markers`, `logo_enabled`, `logo_size`, `images_number`, `videos_number`, `categories`) VALUES (1, 'Standart', '', 0, 0, 0, 1, 0, 0, 3, 0, 2, 1, 1, 1, 'thumbnail', 6, 3, '')");
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `wp_w2dc_levels_relationships` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`post_id` int(11) NOT NULL,
					`level_id` int(11) NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `post_id` (`post_id`,`level_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `wp_w2dc_locations_levels` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`in_widget` tinyint(1) NOT NULL,
					`in_address_line` tinyint(1) NOT NULL,
					PRIMARY KEY (`id`),
					KEY `in_select_widget` (`in_widget`,`in_address_line`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_locations_levels` WHERE name = 'Country'"))
			$wpdb->query("INSERT INTO `wp_w2dc_locations_levels` (`name`, `in_widget`, `in_address_line`) VALUES ('Country', 1, 1);");
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_locations_levels` WHERE name = 'State'"))
			$wpdb->query("INSERT INTO `wp_w2dc_locations_levels` (`name`, `in_widget`, `in_address_line`) VALUES ('State', 1, 1);");
		if (!$wpdb->get_var("SELECT id FROM `wp_w2dc_locations_levels` WHERE name = 'City'"))
			$wpdb->query("INSERT INTO `wp_w2dc_locations_levels` (`name`, `in_widget`, `in_address_line`) VALUES ('City', 1, 1);");
	
		$wpdb->query("CREATE TABLE IF NOT EXISTS `wp_w2dc_locations_relationships` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`post_id` int(11) NOT NULL,
					`location_id` int(11) NOT NULL,
					`address_line_1` varchar(255) NOT NULL,
					`address_line_2` varchar(255) NOT NULL,
					`zip_or_postal_index` varchar(25) NOT NULL,
					`manual_coords` tinyint(1) NOT NULL,
					`map_coords_1` float(10,6) NOT NULL,
					`map_coords_2` float(10,6) NOT NULL,
					`map_icon_file` varchar(255) NOT NULL,
					PRIMARY KEY (`id`),
					KEY `location_id` (`location_id`),
					KEY `post_id` (`post_id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	
		if (!is_array(get_terms(W2DC_LOCATIONS_TAX)) || !count(get_terms(W2DC_LOCATIONS_TAX))) {
			$parent_term = wp_insert_term('USA', W2DC_LOCATIONS_TAX);
			wp_insert_term('Alabama', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Alaska', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Arkansas', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('California', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Colorado', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Connecticut', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Delaware', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('District of Columbia', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Florida', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Georgia', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Hawaii', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Idaho', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Illinois', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Indiana', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Iowa', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Kansas', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Kentucky', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Lousiana', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Maine', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Maryland', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Massachusetts', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Michigan', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Minnesota', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Mississippi', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Missouri', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Montana', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Nebraska', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Nevada', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('New Hampshire', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('New Jersey', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('New Mexico', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('New York', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('North Carolina', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('North Dakota', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Ohio', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Oklahoma', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Oregon', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Pennsylvania', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Rhode Island', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('South Carolina', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('South Dakota', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Tennessee', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Texas', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Utah', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Vermont', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Virginia', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Washington', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('West Virginina', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Wisconsin', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
			wp_insert_term('Wyoming', W2DC_LOCATIONS_TAX, array('parent' => $parent_term['term_id']));
		}
	
		update_option('w2dc_category_slug', 'web-category');
		update_option('w2dc_tag_slug', 'web-tag');
		update_option('w2dc_enable_recaptcha', '0');
		update_option('w2dc_recaptcha_public_key', '');
		update_option('w2dc_recaptcha_private_key', '');
		update_option('w2dc_show_categories_index', '1');
		update_option('w2dc_show_category_count', '1');
		update_option('w2dc_listings_number_index', '6');
		update_option('w2dc_listings_number_excerpt', '6');
		update_option('w2dc_map_on_index', '1');
		update_option('w2dc_map_on_excerpt', '1');
		update_option('w2dc_listings_own_page', '1');
		update_option('w2dc_directory_title', 'Directory & Classifieds');
		update_option('w2dc_categories_nesting_level', '1');
		update_option('w2dc_images_on_tab', '0');
		update_option('w2dc_show_directions', '1');
		update_option('w2dc_send_expiration_notification_days', '1');
		update_option('w2dc_preexpiration_notification', 'Your listing "[listing]" will expiry in [days] days.');
		update_option('w2dc_expiration_notification', 'Your listing "[listing]" had expired. You can renew it here [link]');
		update_option('w2dc_show_what_search', '1');
		update_option('w2dc_show_where_search', '1');
	
		update_option('w2dc_installed_directory', true);
		update_option('w2dc_installed_directory_version', W2DC_VERSION);
	} elseif (get_option('w2dc_installed_directory_version') != W2DC_VERSION) {
		$upgrades_list = array(
				'1.0.6',
				'1.0.7',
				'1.1.0',
		);

		$old_version = get_option('w2dc_installed_directory_version');
		foreach ($upgrades_list AS $upgrade_version) {
			if (!$old_version || version_compare($old_version, $upgrade_version, '<')) {
				$upgrade_function_name = 'upgrade_to_' . str_replace('.', '_', $upgrade_version);
				if (function_exists($upgrade_function_name))
					$upgrade_function_name();
			}
		}
		update_option('w2dc_installed_directory_version', W2DC_VERSION);
	}
}

function upgrade_to_1_0_6() {
	update_option('w2dc_show_what_search', '1');
	update_option('w2dc_show_where_search', '1');
}

function upgrade_to_1_0_7() {
	update_option('w2dc_content_width', '60');
}

function upgrade_to_1_1_0() {
	delete_option('w2dc_is_home_page');
	delete_option('w2dc_content_width');
}

?>