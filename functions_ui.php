<?php

function w2dc_tax_dropdowns_init($tax = 'category', $field_name = null, $term_id = null, $count = true, $labels = array(), $titles = array(), $uID = null) {
	// unique ID need when we place some dropdowns groups on one page
	if (!$uID)
		$uID = rand(1, 10000);

	wp_enqueue_script('w2dc_tax_dropdowns_handle', W2DC_RESOURCES_URL . 'js/tax_dropdowns.js', array('jquery'));
	
	$localized_data[$uID] = array(
			'labels' => $labels,
			'titles' => $titles
	);

	// update w2dc_tax_dropdowns_script data instead of fully rewrite
	global $wp_scripts;
	$data = $wp_scripts->get_data('w2dc_tax_dropdowns_handle', 'data');
	if (empty($data)) {
		wp_localize_script(
				'w2dc_tax_dropdowns_handle',
				'w2dc_tax_dropdowns_script',
				$localized_data
		);
	} else {
		if(!is_array($data))
			$data = json_decode(str_replace('var w2dc_tax_dropdowns_script = ', '', substr($data, 0, -1)), true);
		foreach($data as $key => $value)
			$localized_data[$key] = $value;
		$wp_scripts->add_data('w2dc_tax_dropdowns_handle', 'data', '');
		wp_localize_script(
				'w2dc_tax_dropdowns_handle',
				'w2dc_tax_dropdowns_script',
				$localized_data
		);
	}
	

	if (!is_null($term_id) && $term_id != 0) {
		$chain = array();
		$parent_id = $term_id;
		while ($parent_id != 0) {
			if ($term = get_term($parent_id, $tax)) {
				$chain[] = $term->term_id;
				$parent_id = $term->parent;
			} else
				break;
		}
	}
	$chain[] = 0;
	$chain = array_reverse($chain);

	if (!$field_name)
		$field_name = 'selected_tax[' . $uID . ']';

	echo '<div id="tax_dropdowns_wrap_' . $uID . '" class="' . $tax . ' cs_count_' . (int)$count . ' tax_dropdowns_wrap">';
	echo '<input type="hidden" name="' . $field_name . '" id="selected_tax[' . $uID . ']" class="selected_tax_' . $tax . '" value="' . $term_id . '" />';
	foreach ($chain AS $key=>$term_id) {
		// there is a wp bug with pad_counts in get_terms function - so we use this construction
		if ($terms = wp_list_filter(get_categories(array('taxonomy' => $tax, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => $term_id))) {
			$level_num = $key + 1;
			echo '<div id="wrap_chainlist_' . $level_num . '_' .$uID . '" class="wrap_chainlist">';
			echo '<label for="chainlist_' . $level_num . '_' . $uID . '">' . ((isset($labels[$key])) ? $labels[$key] : '') . '</label>';

			echo '<select id="chainlist_' . $level_num . '_' . $uID . '">';
			echo '<option value="">- ' . ((isset($titles[$key])) ? $titles[$key] : __('Select term', 'W2DC')) . ' -</option>';
			foreach ($terms as $term) {
				if ($count)
					$term_count = " ($term->count)";
				else
					 $term_count = '';
				if (isset($chain[$key+1]) && $term->term_id == $chain[$key+1]) $selected = 'selected'; else $selected = '';
				echo '<option id="' . $term->slug . '" value="' . $term->term_id . '" ' . $selected . '>' . $term->name . $term_count . '</option>';
			}
			echo '</select>';
			echo '<div class="clear"></div>';
			echo '</div>';
		}
	}
	echo '</div>';
}

function w2dc_tax_dropdowns_updateterms() {
	$parentid = w2dc_getValue($_POST, 'parentid');
	$next_level = w2dc_getValue($_POST, 'next_level');
	$tax = w2dc_getValue($_POST, 'tax');
	$count = w2dc_getValue($_POST, 'count');
	if (!$label = w2dc_getValue($_POST, 'label'))
		$label = '';
	if (!$title = w2dc_getValue($_POST, 'title'))
		$title = __('Select term', 'W2DC');
	$uID = w2dc_getValue($_POST, 'uID');

	// there is a wp bug with pad_counts in get_terms function - so we use this construction
	$terms = wp_list_filter(get_categories(array('taxonomy' => $tax, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => $parentid));

	if (!empty($terms)) {
		echo '<div id="wrap_chainlist_' . $next_level . '_' . $uID . '">';

		echo '<label for="chainlist_' . $next_level . '_' . $uID . '">' . $label . '</label>';

		echo '<select id="chainlist_' . $next_level . '_' . $uID . '">';

		echo '<option value="">- ' . $title . ' -</option>';

		foreach ($terms as $term) {
			if ($count == 'cs_count_1') {
				$term_count = " ($term->count)";
			} else { $term_count = '';
			}
			echo '<option id="' . $term->slug . '" value="' . $term->term_id . '">' . $term->name . $term_count . '</option>';
		}

		echo '</select>';
		echo '</div>';

	}
	die();
}

function w2dc_renderOptionsTerms($tax, $parent, $selected_terms, $level = 0) {
	$terms = get_terms($tax, array('parent' => $parent, 'hide_empty' => false));

	foreach ($terms AS $term) {
		echo '<option value="' . $term->term_id . '" ' . (($selected_terms && in_array($term->term_id, $selected_terms)) ? 'selected' : '') . '>' . (str_repeat('&nbsp;&nbsp;&nbsp;', $level)) . $term->name . '</option>';
		w2dc_renderOptionsTerms($tax, $term->term_id, $selected_terms, $level+1);
	}
	return $terms;
}
function w2dc_termsSelectList($name, $tax = 'category', $selected_terms = array()) {
	echo '<select multiple="multiple" name="' . $name . '[]" class="selected_terms_list" style="height: 300px">';
	echo '<option value="" ' . ((!$selected_terms) ? 'selected' : '') . '>' . __('- Select All -', 'W2DC') . '</option>';

	w2dc_renderOptionsTerms($tax, 0, $selected_terms);

	echo '</select>';
}

function w2dc_recaptcha() {
	if (get_option('w2dc_enable_recaptcha') && get_option('w2dc_recaptcha_public_key') && get_option('w2dc_recaptcha_private_key')) {
		require_once(W2DC_PATH . 'recaptcha/recaptchalib.php');
		return '<p>' . recaptcha_get_html(get_option('w2dc_recaptcha_public_key')) . '</p>';
	}
}

function w2dc_is_recaptcha_passed() {
	if (get_option('w2dc_enable_recaptcha') && get_option('w2dc_recaptcha_public_key') && get_option('w2dc_recaptcha_private_key')) {
		if (isset($_POST["recaptcha_challenge_field"]) && isset($_POST["recaptcha_response_field"])) {
			require_once(W2DC_PATH . 'recaptcha/recaptchalib.php');
			$responce = recaptcha_check_answer(get_option('w2dc_recaptcha_private_key'),
					$_SERVER["REMOTE_ADDR"],
					$_POST["recaptcha_challenge_field"],
					$_POST["recaptcha_response_field"]);
			return $responce->is_valid;
		} else {
			return false;
		}
	} else
		return true;
}

function w2dc_orderLinks($base_url) {
	global $w2dc_instance;

	$ordering = array();
	$class = '';
	if (!isset($_GET['order_by']) || $_GET['order_by'] == 'post_date') {
		if (!isset($_GET['order']) || $_GET['order'] == 'DESC') {
			$class = 'descending';
			$url = add_query_arg('order', 'ASC', add_query_arg('order_by', 'post_date', $base_url));
		} elseif ($_GET['order'] == 'ASC') {
			$class = 'ascending';
			$url = add_query_arg('order_by', 'post_date', $base_url);
		}
	} else
		$url = add_query_arg('order_by', 'post_date', $base_url);
	$ordering['post_date'] = '<a class="' . $class . '" href="' . $url . '">' . __('Date', 'W2DC') . '</a>';
	
	$class = '';
	if (isset($_GET['order_by']) && $_GET['order_by'] == 'title') {
		if (!isset($_GET['order']) || $_GET['order'] == 'ASC') {
			$class = 'ascending';
			$url = add_query_arg('order', 'DESC', add_query_arg('order_by', 'title', $base_url));
		} elseif ($_GET['order'] == 'DESC') {
			$class = 'descending';
			$url = add_query_arg('order_by', 'title', $base_url);
		}
	} else
		$url = add_query_arg('order_by', 'title', $base_url);
	$ordering['title'] = '<a class="' . $class . '" href="' . $url . '">' . __('Title', 'W2DC') . '</a>';

	$content_fields = $w2dc_instance->content_fields->getOrderingContentFields();
	foreach ($content_fields AS $content_field) {
		$class = '';
		if (isset($_GET['order_by']) && $_GET['order_by'] == $content_field->slug) {
			if (!isset($_GET['order']) || $_GET['order'] == 'ASC') {
				$class = 'ascending';
				$url = add_query_arg('order', 'DESC', add_query_arg('order_by', $content_field->slug, $base_url));
			} elseif ($_GET['order'] == 'DESC') {
				$class = 'descending';
				$url = add_query_arg('order_by', $content_field->slug, $base_url);
			}
		} else
			$url = add_query_arg('order_by', $content_field->slug, $base_url);
		$ordering[$content_field->slug] = '<a class="' . $class . '" href="' . $url . '">' . $content_field->name . '</a>';
	}
	$ordering = apply_filters('w2dc_ordering_options', $ordering, $base_url);

	echo __('Order by: ', 'W2DC') . implode(' | ', $ordering);
}

function w2dc_renderSubCategories($parent_category_slug = '', $columns = 2, $count = false) {
	if ($parent_category_slug) {
		$parent_category = get_term_by('slug', $parent_category_slug, W2DC_CATEGORIES_TAX);
		$parent_category_id = $parent_category->term_id;
	} else
		$parent_category_id = 0;
	if ($terms = wp_list_filter(get_categories(array('taxonomy' => W2DC_CATEGORIES_TAX, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => $parent_category_id))) {
		$width = ((96-($columns*5))/$columns);
		$ccount = 0;
		$tcount = 0;
		echo '<h2>' . __('Subcategories:', 'W2DC') . '</h2>';
		echo '<ul>';
		foreach ($terms AS $term) {
			if ($count)
				$term_count = " ($term->count)";
			else
				$term_count = '';

			if ($icon_file = w2dc_getCategoryIcon($term->term_id))
				$icon_image = '<img class="w2dc_field_icon" src="' . W2DC_CATEGORIES_ICONS_URL . $icon_file . '" />';
			else
				$icon_image = '';

			echo '<li class="categories_list" style="width: ' . $width . '%"><a href="' . get_term_link($term) . '">' . $icon_image . $term->name . $term_count . '</a></li>';
			$ccount++;
			$tcount++;
			if ($ccount == $columns || $tcount == count($terms)) {
				$ccount = 0;
				echo '<div class="clear_float"></div>';
			}
		}
		echo '</ul>';
	}
}

function w2dc_terms_checklist($post_id) {
	if ($terms = wp_list_filter(get_categories(array('taxonomy' => W2DC_CATEGORIES_TAX, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => 0))) {
		$checked_categories_ids = array();
		$checked_categories = wp_get_object_terms($post_id, W2DC_CATEGORIES_TAX);
		foreach ($checked_categories AS $term)
			$checked_categories_ids[] = $term->term_id;

		echo '<ul class="categorychecklist">';
		foreach ($terms AS $term) {
			$checked = '';
			if (in_array($term->term_id, $checked_categories_ids))
				$checked = 'checked';
				
			echo '
<li id="' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '">';
			echo '<label class="selectit"><input type="checkbox" ' . $checked . ' id="in-' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '" name="tax_input[' . W2DC_CATEGORIES_TAX . '][]" value="' . $term->term_id . '"> ' . $term->name . '</label>';
			echo _w2dc_terms_checklist($term->term_id, $checked_categories_ids);
			echo '</li>';
		}
		echo '</ul>';
	}
}
function _w2dc_terms_checklist($parent = 0, $checked_categories_ids = array()) {
	$html = '';
	if ($terms = wp_list_filter(get_categories(array('taxonomy' => W2DC_CATEGORIES_TAX, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => $parent))) {
		$html .= '<ul class="children">';
		foreach ($terms AS $term) {
			$checked = '';
			if (in_array($term->term_id, $checked_categories_ids))
				$checked = 'checked';

			$html .= '
<li id="' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '">';
			$html .= '<label class="selectit"><input type="checkbox" ' . $checked . ' id="in-' . W2DC_CATEGORIES_TAX . '-' . $term->term_id . '" name="tax_input[' . W2DC_CATEGORIES_TAX . '][]" value="' . $term->term_id . '"> ' . $term->name . '</label>';
			$html .= _w2dc_terms_checklist($term->term_id);
			$html .= '</li>';
		}
		$html .= '</ul>';
	}
	return $html;
}

function w2dc_renderAllCategories($depth = 2, $columns = 2, $count = false) {
	if ($terms = wp_list_filter(get_categories(array('taxonomy' => W2DC_CATEGORIES_TAX, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => 0))) {
		$width = ((96-($columns*5))/$columns);
		$ccount = 0;
		$tcount = 0;
		echo '<ul>';
		foreach ($terms AS $term) {
			if ($count)
				$term_count = " ($term->count)";
			else
				$term_count = '';

			if ($icon_file = w2dc_getCategoryIcon($term->term_id))
				$icon_image = '<img class="w2dc_field_icon" src="' . W2DC_CATEGORIES_ICONS_URL . $icon_file . '" />';
			else
				$icon_image = '';
			
			echo '<li class="categories_list" style="width: ' . $width . '%"><a href="' . get_term_link($term) . '">' . $icon_image . $term->name . $term_count . '</a>' . _w2dc_renderAllCategories($term->term_id, $depth, 1, $count) . '</li>';
			$ccount++;
			$tcount++;
			if ($ccount == $columns || $tcount == count($terms)) {
				$ccount = 0;
				echo '<div class="clear_float"></div>';
			}
		}
		echo '</ul>';
	}
}
function _w2dc_renderAllCategories($parent = 0, $depth = 2, $level = 0, $count = false) {
	$html = '';
	if ($depth > $level && $terms = wp_list_filter(get_categories(array('taxonomy' => W2DC_CATEGORIES_TAX, 'pad_counts' => true, 'hide_empty' => false)), array('parent' => $parent))) {
		$level++;
		$html .= '<ul>';
		foreach ($terms AS $term) {
			if ($count)
				$term_count = " ($term->count)";
			else
				$term_count = '';

			if ($icon_file = w2dc_getCategoryIcon($term->term_id))
				$icon_image = '<img class="w2dc_field_icon" src="' . W2DC_CATEGORIES_ICONS_URL . $icon_file . '" />';
			else
				$icon_image = '';

			$html .= '<li class="subcategories_list"><a href="' . get_term_link($term) . '">' . $icon_image . $term->name . $term_count . '</a>' . _w2dc_renderAllCategories($term->term_id, $depth, $level, $count) . '</li>';
		}
		$html .= '</ul>';
	}
	return $html;
}

function w2dc_getCategoryIcon($term_id) {
	global $w2dc_instance;
	
	if ($icon_file = $w2dc_instance->categories_manager->getCategoryIconFile($term_id))
		return $icon_file;
}

function w2dc_show_404() {
	status_header(404);
	nocache_headers();
	include(get_404_template());
	exit;
}

?>