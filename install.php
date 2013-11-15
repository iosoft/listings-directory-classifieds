<?php 

function w2dc_install_directory() {
	global $wpdb;
	
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
	
	$wpdb->query("INSERT INTO `wp_w2dc_locations_levels` (`name`, `in_widget`, `in_address_line`) VALUES ('Country', 1, 1), ('State', 1, 1), ('City', 1, 1);");

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
	update_option('w2dc_is_home_page', '0');
	update_option('w2dc_directory_title', 'Directory & Classifieds');
	update_option('w2dc_categories_nesting_level', '1');
	update_option('w2dc_images_on_tab', '0');
	update_option('w2dc_show_directions', '1');
	update_option('w2dc_send_expiration_notification_days', '1');
	update_option('w2dc_preexpiration_notification', 'Your listing "[listing]" will expiry in [days] days.');
	update_option('w2dc_expiration_notification', 'Your listing "[listing]" had expired. You can renew it here [link]');

	update_option('w2dc_installed_directory', true);
}
?>