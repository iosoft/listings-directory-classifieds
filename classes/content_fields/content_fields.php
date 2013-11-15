<?php

include_once W2DC_PATH . 'classes/content_fields/fields/content_field_content.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_excerpt.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_address.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_categories.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_tags.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_string.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_textarea.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_number.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_select.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_checkbox.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_radio.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_website.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_email.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_datetime.php';
include_once W2DC_PATH . 'classes/content_fields/fields/content_field_price.php';

class w2dc_content_fields {
	public $content_fields_array = array();
	public $fields_types_names;
	
	public function __construct() {
		$this->fields_types_names = array(
				'excerpt' => __('Excerpt', 'W2DC'),
				'content' => __('Content', 'W2DC'),
				'categories' => __('Listing categories', 'W2DC'),
				'tags' => __('Listing tags', 'W2DC'),
				'address' => __('Listing addresses', 'W2DC'),
				'string' => __('Text string', 'W2DC'),
				'textarea' => __('Textarea', 'W2DC'),
				'number' => __('Number', 'W2DC'),
				'select' => __('Select list', 'W2DC'),
				'radio' => __('Radio buttons', 'W2DC'),
				'checkbox' => __('Checkboxes', 'W2DC'),
				'website' => __('Website URL', 'W2DC'),
				'email' => __('Email', 'W2DC'),
				'datetime' => __('Date-Time', 'W2DC'),
				'price' => __('Price', 'W2DC'),
		);

		$this->getContentFieldsFromDB();
	}
	
	public function saveOrder($order_input) {
		global $wpdb;
	
		if ($order_ids = explode(',', trim($order_input))) {
			$i = 1;
			foreach ($order_ids AS $id) {
				$wpdb->update('wp_w2dc_content_fields', array('order_num' => $i), array('id' => $id));
				$i++;
			}
		}
		$this->getContentFieldsFromDB();
		return true;
	}
	
	public function getContentFieldsFromDB() {
		global $wpdb;
		$this->content_fields_array = array();
	
		$array = $wpdb->get_results("SELECT * FROM wp_w2dc_content_fields ORDER BY order_num, is_core_field", ARRAY_A);
		foreach ($array AS $row) {
			$field_class_name = 'w2dc_content_field_' . $row['type'];
			if (class_exists($field_class_name)) {
				$content_field = new $field_class_name;
				$content_field->buildContentFieldFromArray($row);
				$content_field->convertCategories();
				$content_field->convertOptions();
				$this->content_fields_array[$row['id']] = $content_field;
			}
		}
	}
	
	public function getContentFieldById($field_id) {
		if (isset($this->content_fields_array[$field_id]))
			return $this->content_fields_array[$field_id];
	}
	
	public function createContentFieldFromArray($array) {
		if (isset($array['type'])) {
			$field_class_name = 'w2dc_content_field_' . $array['type'];
			if (class_exists($field_class_name)) {
				$content_field = new $field_class_name;
				return $content_field->create($array);
			}
		}
		return false;
	}
	
	public function saveContentFieldFromArray($field_id, $array) {
		if ($content_field = $this->getContentFieldById($field_id))
			return $content_field->save($array);

		return false;
	}
	
	public function deleteContentField($field_id) {
		if ($content_field = $this->getContentFieldById($field_id))
			return $content_field->delete();
		
		return false;
	}
	
	public function getOrderingContentFields() {
		$fields = array();
		foreach($this->content_fields_array AS $content_field) {
			if ($content_field->canBeOrdered() && $content_field->is_ordered)
				$fields[] = $content_field;
		}
		return $fields;
	}

	public function isNotCoreContentFields() {
		foreach($this->content_fields_array AS $content_field) {
			if (!$content_field->is_core_field)
				return true;
		}
	}
	
	public function getFieldsBycategoriesIds($categories_ids) {
		$result_fields = array();

		foreach($this->content_fields_array AS &$content_field) {
			if (!$content_field->isCategories() || $content_field->categories === array() || array_intersect($content_field->categories, $categories_ids))
				$result_fields[$content_field->id] = $content_field;
		}
		return $result_fields;
	}

	public function saveValues($post_id, $categories_ids, &$errors, $data) {
		$content_fields = $this->getFieldsBycategoriesIds($categories_ids);
		foreach($content_fields AS $content_field) {
			if (($validation_results = $content_field->validateValues($errors, $data)) !== false)
				$content_field->saveValue($post_id, $validation_results);
		}
	}

	public function loadValues($post_id, $categories_ids) {
		$content_fields = $this->getFieldsBycategoriesIds($categories_ids);
		$result_content_fields = array();
		foreach($content_fields AS $content_field) {
			$rcontent_field = clone $content_field;
			$rcontent_field->loadValue($post_id);
			$result_content_fields[$content_field->id] = $rcontent_field;
		}
		return $result_content_fields;
	}
	
	public function getOrderParams() {
		if (isset($_GET['orderby']))
			foreach($this->content_fields_array AS $content_field)
				if ($content_field->canBeOrdered() && $content_field->is_ordered && $content_field->slug == $_GET['orderby']) {
					return $content_field->orderParams();
					break;
				}
		return array();
	}
}

class w2dc_content_field {
	public $id;
	public $is_core_field = 0;
	public $order_num;
	public $name;
	public $slug;
	public $description;
	public $type;
	public $icon_image;
	public $is_required = 0;
	public $is_ordered;
	public $is_hide_name;
	public $on_exerpt_page;
	public $on_listing_page;
	public $categories = array();
	public $options;
	public $search_options;
	public $value;
	
	protected $can_be_required = true;
	protected $can_be_ordered = true;
	protected $is_categories = true;
	protected $is_slug = true;
	
	protected $is_configuration_page = false;

	protected $can_be_searched = false;
	protected $is_search_configuration_page = false;
	public $on_search_form = false;
	public $advanced_search_form = false;


	public function validation() {
		$validation = new form_validation();
		$validation->set_rules('name', __('Content field name', 'W2DC'), 'required');
		if ($this->isSlug())
			$validation->set_rules('slug', __('Content field slug', 'W2DC'), 'required|alpha_dash');
		$validation->set_rules('description', __('Content field description', 'W2DC'));
		$validation->set_rules('icon_image', __('Icon image', 'W2DC'));
		if ($this->canBeRequired())
			$validation->set_rules('is_required', __('Content field required', 'W2DC'), 'is_checked');
		if ($this->canBeOrdered())
			$validation->set_rules('is_ordered', __('Order by field', 'W2DC'), 'is_checked');
		$validation->set_rules('is_hide_name', __('Hide name', 'W2DC'), 'is_checked');
		$validation->set_rules('on_exerpt_page', __('On excerpt page', 'W2DC'), 'is_checked');
		$validation->set_rules('on_listing_page', __('On listing page', 'W2DC'), 'is_checked');
		// core fields can't change type
		if (!$this->is_core_field)
			$validation->set_rules('type', __('Content field type', 'W2DC'), 'required');
		if ($this->isCategories())
			$validation->set_rules('categories_list', __('Assigned categories', 'W2DC'));

		$validation = apply_filters('w2dc_content_field_validation', $validation, $this);

		if ($this->isSlug()) {
			global $wpdb;
			if ($wpdb->get_results($wpdb->prepare("SELECT * FROM wp_w2dc_content_fields WHERE slug=%s AND id!=%d", $_POST['slug'], $this->id), ARRAY_A)
				|| $_POST['slug'] == 'post_date'
				|| $_POST['slug'] == 'title'
				|| $_POST['slug'] == 'categories_list'
				|| $_POST['slug'] == 'address'
				|| $_POST['slug'] == 'content'
				|| $_POST['slug'] == 'excerpt'
				|| $_POST['slug'] == 'listing_tags'
				|| $_POST['slug'] == 'distance'
			)
				$validation->setError('slug', __('Can\'t use this slug', 'W2DC'));
		}

		return $validation;
	}
	
	public function create($array) {
		global $wpdb;

		$insert_update_args = array(
				'name' => $array['name'],
				'description' => $array['description'],
				'type' => $array['type'],
				'icon_image' => $array['icon_image'],
				'is_configuration_page' => $this->is_configuration_page,
				'is_search_configuration_page' => $this->is_search_configuration_page,
				'is_hide_name' => $array['is_hide_name'],
				'on_exerpt_page' => $array['on_exerpt_page'],
				'on_listing_page' => $array['on_listing_page'],
		);
		if ($this->isSlug())
			$insert_update_args['slug'] = $array['slug'];
		if ($this->canBeRequired())
			$insert_update_args['is_required'] = $array['is_required'];
		if ($this->canBeOrdered())
			$insert_update_args['is_ordered'] = $array['is_ordered'];
		if ($this->isCategories())
			$insert_update_args['categories'] = serialize($array['categories_list']);

		$insert_update_args = apply_filters('w2dc_content_field_create_edit_args', $insert_update_args, $this, $array);
		
		return $wpdb->insert('wp_w2dc_content_fields', $insert_update_args);
	}
	
	public function save($array) {
		global $wpdb;
		
		$insert_update_args = array(
				'name' => $array['name'],
				'description' => $array['description'],
				'icon_image' => $array['icon_image'],
				'is_hide_name' => $array['is_hide_name'],
				'on_exerpt_page' => $array['on_exerpt_page'],
				'on_listing_page' => $array['on_listing_page'],
		);
		// core fields can't change type
		if (!$this->is_core_field)
			$insert_update_args['type'] = $array['type'];
		if ($this->isSlug())
			$insert_update_args['slug'] = $array['slug'];
		if ($this->canBeRequired())
			$insert_update_args['is_required'] = $array['is_required'];
		if ($this->canBeOrdered())
			$insert_update_args['is_ordered'] = $array['is_ordered'];
		if ($this->isCategories())
			$insert_update_args['categories'] = serialize($array['categories_list']);

		$insert_update_args = apply_filters('w2dc_content_field_create_edit_args', $insert_update_args, $this, $array);
		
		return $wpdb->update('wp_w2dc_content_fields', $insert_update_args,	array('id' => $this->id), null, array('%d')) !== false;
	}
	
	public function delete() {
		global $wpdb;

		$wpdb->delete('wp_postmeta', array('meta_key' => '_content_field_' . $this->id));

		$wpdb->delete('wp_w2dc_content_fields', array('id' => $this->id));
		return true;
	}

	public function buildContentFieldFromArray($array) {
		$this->id = w2dc_getValue($array, 'id');
		$this->is_core_field = w2dc_getValue($array, 'is_core_field');
		$this->order_num = w2dc_getValue($array, 'order_num');
		$this->name = w2dc_getValue($array, 'name');
		$this->slug = w2dc_getValue($array, 'slug');
		$this->description = w2dc_getValue($array, 'description');
		$this->type = w2dc_getValue($array, 'type');
		$this->icon_image = w2dc_getValue($array, 'icon_image');
		$this->is_required = w2dc_getValue($array, 'is_required');
		$this->is_configuration_page = w2dc_getValue($array, 'is_configuration_page');
		$this->is_search_configuration_page = w2dc_getValue($array, 'is_search_configuration_page');
		$this->on_search_form = w2dc_getValue($array, 'on_search_form');
		$this->advanced_search_form = w2dc_getValue($array, 'advanced_search_form');
		$this->is_ordered = w2dc_getValue($array, 'is_ordered');
		$this->is_hide_name = w2dc_getValue($array, 'is_hide_name');
		$this->on_exerpt_page = w2dc_getValue($array, 'on_exerpt_page');
		$this->on_listing_page = w2dc_getValue($array, 'on_listing_page');
		$this->categories = w2dc_getValue($array, 'categories');
		$this->options = w2dc_getValue($array, 'options');
		$this->search_options = w2dc_getValue($array, 'search_options');
	}
	
	public function convertCategories() {
		if ($this->categories) {
			$unserialized_categories = unserialize($this->categories);
			if (count($unserialized_categories) > 1 || $unserialized_categories != array(''))
				$this->categories = $unserialized_categories;
			else
				$this->categories = array();
		}
		return $this->categories;
	}

	public function convertOptions() {
		if ($this->options) {
			$unserialized_options = unserialize($this->options);
			if (count($unserialized_options) > 1 || $unserialized_options != array('')) {
				$this->options = $unserialized_options;
				if (method_exists($this, 'buildOptions'))
					$this->buildOptions();
				return $this->options;
			}
		}
		return array();
	}
	
	public function canBeRequired() {
		return $this->can_be_required;
	}

	public function canBeOrdered() {
		return $this->can_be_ordered;
	}

	public function isSlug() {
		return $this->is_slug;
	}

	public function isCategories() {
		return $this->is_categories;
	}

	public function isConfigurationPage() {
		return $this->is_configuration_page;
	}

	public function isSearchConfigurationPage() {
		return $this->is_search_configuration_page;
	}

	public function canBeSearched() {
		return $this->can_be_searched;
	}
	
	public function validateValues(&$errors) {
		return true;
	}

	public function saveValue() {
		return true;
	}

	public function loadValue() {
		return true;
	}
	
	public function renderOutput() {
		return true;
	}
}

?>