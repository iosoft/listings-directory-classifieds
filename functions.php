<?php 

function w2dc_getValue($target, $key, $default = false) {
	$target = is_object($target) ? (array) $target : $target;

	if (is_array($target) && isset($target[$key]))
		return $target[$key];

	return $default;
}

function w2dc_addMessage($message, $type = 'updated') {
	global $w2dc_messages;

	if (!isset($w2dc_messages[$type]) || (isset($w2dc_messages[$type]) && !in_array($message, $w2dc_messages[$type])))
		$w2dc_messages[$type][] = $message;

	if (session_id() == '')
		@session_start();

	if (!isset($_SESSION['w2dc_messages'][$type]) || (isset($_SESSION['w2dc_messages'][$type]) && !in_array($message, $_SESSION['w2dc_messages'][$type])))
		$_SESSION['w2dc_messages'][$type][] = $message;
}

function w2dc_renderMessages() {
	global $w2dc_messages;

	$messages = array();
	if (isset($w2dc_messages) && is_array($w2dc_messages) && $w2dc_messages)
		$messages = $w2dc_messages;

	if (session_id() == '')
		@session_start();
	if (isset($_SESSION['w2dc_messages']))
		$messages = array_merge($messages, $_SESSION['w2dc_messages']);

	$messages = w2dc_superUnique($messages);

	foreach ($messages AS $type=>$messages) {
		echo '<div class="' . $type . '">';
		foreach ($messages AS $message)
			echo '<p>' . $message . '</p>';
		echo '</div>';
	}
	
	$w2dc_messages = array();
	unset($_SESSION['w2dc_messages']);
}
function w2dc_superUnique($array) {
	$result = array_map("unserialize", array_unique(array_map("serialize", $array)));
	foreach ($result as $key => $value)
		if (is_array($value))
			$result[$key] = w2dc_superUnique($value);
	return $result;
}

function w2dc_sumDates($date, $active_days, $active_months, $active_years)
{
	$date = strtotime('+'.$active_days.' day', $date);
	$date = strtotime('+'.$active_months.' month', $date);
	$date = strtotime('+'.$active_years.' year', $date);
	return $date;
}

function w2dc_renderTemplate($template, $args = array(), $return = false) {
	global $w2dc_instance;

	if ($args)
		extract($args);
	
	$core_template_path = W2DC_PATH . 'templates' . DIRECTORY_SEPARATOR . $template;
	if (!is_file($template))
		if (!is_file($core_template_path))
			return false;
		else
			$template = $core_template_path;

	$custom_template = str_replace('.tpl.php', '', $template) . '-custom.tpl.php';
	if (is_file($custom_template))
		$template = $custom_template;

	if ($return)
		ob_start();

	include($template);
	
	if ($return) {
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}

function w2dc_getCurrentListingInAdmin() {
	global $w2dc_instance;
	
	return $w2dc_instance->current_listing;
}

function w2dc_getIndexPage() {
	global $wpdb, $wp_rewrite;

	if (!($index_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE post_content LIKE '%[" . W2DC_MAIN_SHORTCODE . "]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)))
		$index_page = array('slug' => '', 'id' => 0);

	if ($wp_rewrite->using_permalinks())
		// If this is not WP homepage - add webdirectory page's slug
		if (get_option('page_on_front') != $index_page['id'])
			$index_page['url'] = home_url($index_page['slug']) . '/';
		else
			$index_page['url'] = home_url() . '/';
	else
			$index_page['url'] = add_query_arg('page_id', $index_page['id'], home_url());

	return $index_page;
}

function w2dc_get_term_parents($id, $tax, $link = false, $return_array = false, $separator = '/', &$chain = array()) {
	$parent = &get_term($id, $tax);
	if (is_wp_error($parent))
		return $parent;

	$name = $parent->name;
	
	if ($parent->parent && ($parent->parent != $parent->term_id))
		w2dc_get_term_parents($parent->parent, $tax, $link, $return_array, $separator, $chain);
	
	if ($link)
		$chain[] = '<a href="' . get_term_link($parent->slug, $tax) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $parent->name ) ) . '">'.$name.'</a>';
	else
		$chain[] = $name;
	
	if ($return_array)
		return $chain;
	else
		return implode($separator, $chain);
}

function checkQuickList($is_listing_id = null)
{
	if (isset($_COOKIE['favourites']))
		$favourites = explode('*', $_COOKIE['favourites']);
	else
		$favourites = array();
	$favourites = array_values(array_filter($favourites));

	if ($is_listing_id)
		if (in_array($is_listing_id, $favourites))
			return true;
		else 
			return false;

	$favourites_array = array();
	foreach ($favourites AS $listing_id)
		if (is_numeric($listing_id))
		$favourites_array[] = $listing_id;
	return $favourites_array;
}

?>