<?php 

class w2dc_settings_manager {
	public function __construct() {
		add_action('admin_menu', array($this, 'menu'));
		add_action('admin_init', array($this, 'register_settings'));
	}

	public function menu() {
		add_submenu_page('w2dc_admin',
				__('Directory settings', 'W2DC'),
				__('Directory settings', 'W2DC'),
				'administrator',
				'w2dc_settings',
				array($this, 'w2dc_settings_page')
		);
	}
	
	public function register_settings() {
		// General settings page /////////////////////////////////////////////////////////////////////////
		add_settings_section(
				'w2dc_general_section',
				__('General settings', 'W2DC'),
				null,
				'w2dc_settings_page'
		);
		add_settings_field(
				'w2dc_directory_title',
				__('Directory title', 'W2DC'),
				array($this, 'w2dc_directory_title_callback'),
				'w2dc_settings_page',
				'w2dc_general_section'
		);
		register_setting('w2dc_settings_page', 'w2dc_directory_title');
		add_settings_field(
				'w2dc_category_slug',
				__('Category slug', 'W2DC'),
				array($this, 'w2dc_category_slug_callback'),
				'w2dc_settings_page',
				'w2dc_general_section'
		);
		register_setting('w2dc_settings_page', 'w2dc_category_slug');
		add_settings_field(
				'w2dc_tag_slug',
				__('Tag slug', 'W2DC'),
				array($this, 'w2dc_tag_slug_callback'),
				'w2dc_settings_page',
				'w2dc_general_section'
		);
		register_setting('w2dc_settings_page', 'w2dc_tag_slug');

		// Recaptcha settings page /////////////////////////////////////////////////////////////////////////
		add_settings_section(
				'w2dc_recaptcha_section',
				__('reCaptcha settings', 'W2DC'),
				null,
				'w2dc_settings_page'
		);
		add_settings_field(
				'w2dc_enable_recaptcha',
				__('Enable reCaptcha', 'W2DC'),
				array($this, 'w2dc_enable_recaptcha_callback'),
				'w2dc_settings_page',
				'w2dc_recaptcha_section'
		);
		register_setting('w2dc_settings_page', 'w2dc_enable_recaptcha');
		add_settings_field(
				'w2dc_recaptcha_public_key',
				__('reCaptcha public key', 'W2DC'),
				array($this, 'w2dc_recaptcha_public_key_callback'),
				'w2dc_settings_page',
				'w2dc_recaptcha_section'
		);
		register_setting('w2dc_settings_page', 'w2dc_recaptcha_public_key');
		add_settings_field(
				'w2dc_recaptcha_private_key',
				__('reCaptcha private key', 'W2DC'),
				array($this, 'w2dc_recaptcha_private_key_callback'),
				'w2dc_settings_page',
				'w2dc_recaptcha_section'
		);
		register_setting('w2dc_settings_page', 'w2dc_recaptcha_private_key');


		// Pages & Views settings page /////////////////////////////////////////////////////////////////////////
		add_settings_section(
				'w2dc_categories_section',
				__('Categories settings', 'W2DC'),
				null,
				'w2dc_pagesviews_settings_page'
		);
		add_settings_field(
				'w2dc_show_categories_index',
				__('Show categories list on index and excerpt pages?', 'W2DC'),
				array($this, 'w2dc_show_categories_index_callback'),
				'w2dc_pagesviews_settings_page',
				'w2dc_categories_section'
		);
		register_setting('w2dc_pagesviews_settings_page', 'w2dc_show_categories_index');
		add_settings_field(
				'w2dc_categories_nesting_level',
				__('Categories nesting level', 'W2DC'),
				array($this, 'w2dc_categories_nesting_level_callback'),
				'w2dc_pagesviews_settings_page',
				'w2dc_categories_section'
		);
		register_setting('w2dc_pagesviews_settings_page', 'w2dc_categories_nesting_level');
		add_settings_field(
				'w2dc_show_category_count',
				__('Show category posts count?', 'W2DC'),
				array($this, 'w2dc_show_category_count_callback'),
				'w2dc_pagesviews_settings_page',
				'w2dc_categories_section'
		);
		register_setting('w2dc_pagesviews_settings_page', 'w2dc_show_category_count');

		// Search settings /////////////////////////////////////////////////////////////////////////
		add_settings_section(
				'w2dc_search_section',
				__('Search settings', 'W2DC'),
				null,
				'w2dc_pagesviews_settings_page'
		);
		add_settings_field(
				'w2dc_show_what_search',
				__('Show "What search" section?', 'W2DC'),
				array($this, 'w2dc_show_what_search_callback'),
				'w2dc_pagesviews_settings_page',
				'w2dc_search_section'
		);
		register_setting('w2dc_pagesviews_settings_page', 'w2dc_show_what_search');
		add_settings_field(
				'w2dc_show_where_search',
				__('Show "Where search" section?', 'W2DC'),
				array($this, 'w2dc_show_where_search_callback'),
				'w2dc_pagesviews_settings_page',
				'w2dc_search_section'
		);
		register_setting('w2dc_pagesviews_settings_page', 'w2dc_show_where_search');

		// Listings settings page /////////////////////////////////////////////////////////////////////////
		add_settings_section(
				'w2dc_listings_section',
				__('Listings settings', 'W2DC'),
				null,
				'w2dc_listings_settings_page'
		);
		add_settings_field(
				'w2dc_listings_on_index',
				__('Show listings on index', 'W2DC'),
				array($this, 'w2dc_listings_on_index_callback'),
				'w2dc_listings_settings_page',
				'w2dc_listings_section'
		);
		register_setting('w2dc_listings_settings_page', 'w2dc_listings_on_index');
		add_settings_field(
				'w2dc_listings_number_index',
				__('Number of listings on index page', 'W2DC'),
				array($this, 'w2dc_listings_number_index_callback'),
				'w2dc_listings_settings_page',
				'w2dc_listings_section'
		);
		register_setting('w2dc_listings_settings_page', 'w2dc_listings_number_index');
		add_settings_field(
				'w2dc_listings_number_excerpt',
				__('Number of listings on excerpt page', 'W2DC'),
				array($this, 'w2dc_listings_number_excerpt_callback'),
				'w2dc_listings_settings_page',
				'w2dc_listings_section'
		);
		register_setting('w2dc_listings_settings_page', 'w2dc_listings_number_excerpt');
		add_settings_field(
				'w2dc_listings_own_page',
				__('Do listings have own pages?', 'W2DC'),
				array($this, 'w2dc_listings_own_page_callback'),
				'w2dc_listings_settings_page',
				'w2dc_listings_section'
		);
		register_setting('w2dc_listings_settings_page', 'w2dc_listings_own_page');
		add_settings_field(
				'w2dc_images_on_tab',
				__('Place listing images gallery on separate tab?', 'W2DC'),
				array($this, 'w2dc_images_on_tab_callback'),
				'w2dc_listings_settings_page',
				'w2dc_listings_section'
		);
		register_setting('w2dc_listings_settings_page', 'w2dc_images_on_tab');
		add_settings_field(
				'w2dc_listing_contact_form',
				__('Enable contact form on listing page', 'W2DC'),
				array($this, 'w2dc_listing_contact_form_callback'),
				'w2dc_listings_settings_page',
				'w2dc_listings_section'
		);
		register_setting('w2dc_listings_settings_page', 'w2dc_listing_contact_form');


		// Maps settings page /////////////////////////////////////////////////////////////////////////
		add_settings_section(
				'w2dc_maps_section',
				__('Maps settings', 'W2DC'),
				null,
				'w2dc_maps_settings_page'
		);
		add_settings_field(
				'w2dc_map_on_index',
				__('Show map on index page?', 'W2DC'),
				array($this, 'w2dc_map_on_index_callback'),
				'w2dc_maps_settings_page',
				'w2dc_maps_section'
		);
		register_setting('w2dc_maps_settings_page', 'w2dc_map_on_index');
		add_settings_field(
				'w2dc_map_on_excerpt',
				__('Show map on excerpt page?', 'W2DC'),
				array($this, 'w2dc_map_on_excerpt_callback'),
				'w2dc_maps_settings_page',
				'w2dc_maps_section'
		);
		register_setting('w2dc_maps_settings_page', 'w2dc_map_on_excerpt');
		add_settings_field(
				'w2dc_show_directions',
				__('Show directions panel for the listing map?', 'W2DC'),
				array($this, 'w2dc_show_directions_callback'),
				'w2dc_maps_settings_page',
				'w2dc_maps_section'
		);
		register_setting('w2dc_maps_settings_page', 'w2dc_show_directions');

		// Notifications settings page /////////////////////////////////////////////////////////////////////////
		add_settings_section(
				'w2dc_notifications_section',
				__('Email notifications settings', 'W2DC'),
				null,
				'w2dc_notifications_settings_page'
		);
		add_settings_field(
				'w2dc_send_expiration_notification_days',
				__('Days before pre-expiration notification will be sent', 'W2DC'),
				array($this, 'w2dc_send_expiration_notification_days_callback'),
				'w2dc_notifications_settings_page',
				'w2dc_notifications_section'
		);
		register_setting('w2dc_notifications_settings_page', 'w2dc_send_expiration_notification_days');
		add_settings_field(
				'w2dc_preexpiration_notification',
				__('Pre-expiration notification', 'W2DC'),
				array($this, 'w2dc_preexpiration_notification_callback'),
				'w2dc_notifications_settings_page',
				'w2dc_notifications_section'
		);
		register_setting('w2dc_notifications_settings_page', 'w2dc_preexpiration_notification');
		add_settings_field(
				'w2dc_expiration_notification',
				__('Expiration notification', 'W2DC'),
				array($this, 'w2dc_expiration_notification_callback'),
				'w2dc_notifications_settings_page',
				'w2dc_notifications_section'
		);
		register_setting('w2dc_notifications_settings_page', 'w2dc_expiration_notification');
	}

	// General settings page /////////////////////////////////////////////////////////////////////////
	public function w2dc_directory_title_callback() {
		echo '<input type="text" id="w2dc_directory_title" name="w2dc_directory_title" value="' . esc_attr(get_option('w2dc_directory_title')) . '" size="53" />';
		echo '<p class="description">' . __('This title will be used in HTML title tag of every directory page', 'W2DC') . '</p>';
	}
	public function w2dc_category_slug_callback() {
		echo '<input type="text" id="w2dc_category_slug" name="w2dc_category_slug" value="' . esc_attr(get_option('w2dc_category_slug')) . '" size="25" />';
	}
	public function w2dc_tag_slug_callback() {
		echo '<input type="text" id="w2dc_tag_slug" name="w2dc_tag_slug" value="' . esc_attr(get_option('w2dc_tag_slug')) . '" size="25" />';
	}

	// ReCaptcha settings page /////////////////////////////////////////////////////////////////////////
	public function w2dc_enable_recaptcha_callback() {
		echo '<input type="checkbox" id="w2dc_enable_recaptcha" name="w2dc_enable_recaptcha" value="1" ' . checked(get_option('w2dc_enable_recaptcha'), 1, false) .' />';
	}
	public function w2dc_recaptcha_public_key_callback() {
		echo '<p class="description">' . sprintf(__('get your reCAPTCHA API Keys <a href="%s" target="_blank">here</a>', 'W2DC'), 'http://www.google.com/recaptcha') . '</p>';
		echo '<input type="text" id="w2dc_recaptcha_public_key" name="w2dc_recaptcha_public_key" value="' . esc_attr(get_option('w2dc_recaptcha_public_key')) . '" size="53" />';
	}
	public function w2dc_recaptcha_private_key_callback() {
		echo '<input type="text" id="w2dc_recaptcha_private_key" name="w2dc_recaptcha_private_key" value="' . esc_attr(get_option('w2dc_recaptcha_private_key')) . '" size="53" />';
	}

	// Categories settings page /////////////////////////////////////////////////////////////////////////
	public function w2dc_show_categories_index_callback() {
		echo '<input type="checkbox" id="w2dc_show_categories_index" name="w2dc_show_categories_index" value="1" ' . checked(get_option('w2dc_show_categories_index'), 1, false) .' />';
	}
	public function w2dc_categories_nesting_level_callback() {
		echo '<input type="text" id="w2dc_categories_nesting_level" name="w2dc_categories_nesting_level" value="' . esc_attr(get_option('w2dc_categories_nesting_level')) .'" size="1" />';
	}
	public function w2dc_show_category_count_callback() {
		echo '<input type="checkbox" id="w2dc_show_category_count" name="w2dc_show_category_count" value="1" ' . checked(get_option('w2dc_show_category_count'), 1, false) .' />';
	}

	// Search settings page /////////////////////////////////////////////////////////////////////////
	public function w2dc_show_what_search_callback() {
		echo '<input type="checkbox" id="w2dc_show_what_search" name="w2dc_show_what_search" value="1" ' . checked(get_option('w2dc_show_what_search'), 1, false) .' />';
	}
	public function w2dc_show_where_search_callback() {
		echo '<input type="checkbox" id="w2dc_show_where_search" name="w2dc_show_where_search" value="1" ' . checked(get_option('w2dc_show_where_search'), 1, false) .' />';
	}

	// Listings settings page /////////////////////////////////////////////////////////////////////////
	public function w2dc_listings_on_index_callback() {
		echo '<input type="checkbox" id="w2dc_listings_on_index" name="w2dc_listings_on_index" value="1" ' . checked(get_option('w2dc_listings_on_index'), 1, false) .' />';
	}
	public function w2dc_listings_number_index_callback() {
		echo '<input type="text" id="w2dc_listings_number_index" name="w2dc_listings_number_index" value="' . esc_attr(get_option('w2dc_listings_number_index')) .'" size="1" />';
	}
	public function w2dc_listings_number_excerpt_callback() {
		echo '<input type="text" id="w2dc_listings_number_excerpt" name="w2dc_listings_number_excerpt" value="' . esc_attr(get_option('w2dc_listings_number_excerpt')) .'" size="1" />';
	}
	public function w2dc_listings_own_page_callback() {
		echo '<input type="checkbox" id="w2dc_listings_own_page" name="w2dc_listings_own_page" value="1" ' . checked(get_option('w2dc_listings_own_page'), 1, false) .' />';
	}
	public function w2dc_images_on_tab_callback() {
		echo '<input type="checkbox" id="w2dc_images_on_tab" name="w2dc_images_on_tab" value="1" ' . checked(get_option('w2dc_images_on_tab'), 1, false) .' />';
	}
	public function w2dc_listing_contact_form_callback() {
		echo '<input type="checkbox" id="w2dc_listing_contact_form" name="w2dc_listing_contact_form" value="1" ' . checked(get_option('w2dc_listing_contact_form'), 1, false) .' />';
	}

	// Maps settings page /////////////////////////////////////////////////////////////////////////
	public function w2dc_map_on_index_callback() {
		echo '<input type="checkbox" id="w2dc_map_on_index" name="w2dc_map_on_index" value="1" ' . checked(get_option('w2dc_map_on_index'), 1, false) .' />';
	}
	public function w2dc_map_on_excerpt_callback() {
		echo '<input type="checkbox" id="w2dc_map_on_excerpt" name="w2dc_map_on_excerpt" value="1" ' . checked(get_option('w2dc_map_on_excerpt'), 1, false) .' />';
	}
	public function w2dc_show_directions_callback() {
		echo '<input type="checkbox" id="w2dc_show_directions" name="w2dc_show_directions" value="1" ' . checked(get_option('w2dc_show_directions'), 1, false) .' />';
	}

	// Notifications settings page /////////////////////////////////////////////////////////////////////////
	public function w2dc_send_expiration_notification_days_callback() {
		echo '<input type="text" id="w2dc_send_expiration_notification_days" name="w2dc_send_expiration_notification_days" value="' . esc_attr(get_option('w2dc_send_expiration_notification_days')) .'" size="1" />';
	}
	public function w2dc_preexpiration_notification_callback() {
		echo '<textarea id="w2dc_preexpiration_notification" name="w2dc_preexpiration_notification" cols="60" rows="3">' . esc_textarea(get_option('w2dc_preexpiration_notification')) . '</textarea>';
	}
	public function w2dc_expiration_notification_callback() {
		echo '<textarea id="w2dc_expiration_notification" name="w2dc_expiration_notification" cols="60" rows="3">' . esc_textarea(get_option('w2dc_expiration_notification')) . '</textarea>';
	}

	public function w2dc_settings_page() {
		if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true')
			w2dc_addMessage(__('Settings saved!', 'W2DC'));
		
		$section = 'w2dc_settings_page';
		if (isset($_GET['section']) && $_GET['section'])
			$section = $_GET['section'];

		w2dc_renderTemplate('settings_common.tpl.php', array('section' => $section));
	}
}

?>