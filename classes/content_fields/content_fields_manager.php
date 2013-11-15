<?php

class w2dc_content_fields_manager {
	public function __construct() {
		global $pagenow;

		if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') {
			add_action('add_meta_boxes', array($this, 'addContentFieldsMetabox'));
		}
		
		add_action('admin_menu', array($this, 'menu'));
		
		add_action('wp_ajax_select_field_icon', array($this, 'select_field_icon'));
	}
	
	public function menu() {
		add_submenu_page('w2dc_admin',
			__('Content fields', 'W2DC'),
			__('Content fields', 'W2DC'),
			'administrator',
			'w2dc_content_fields',
			array($this, 'w2dc_content_fields')
		);
	}
	
	public function w2dc_content_fields() {
		if (isset($_GET['action']) && $_GET['action'] == 'add') {
			$this->addOrEditContentField();
		} elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['field_id'])) {
			$this->addOrEditContentField($_GET['field_id']);
		} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['field_id'])) {
			$this->deleteContentField($_GET['field_id']);
		} elseif (isset($_GET['action']) && $_GET['action'] == 'configure' && isset($_GET['field_id'])) {
			$this->configureContentField($_GET['field_id']);
		} elseif (!isset($_GET['action'])) {
			$this->showContentFieldsTable();
		}
	}
	
	public function showContentFieldsTable() {
		wp_enqueue_script('jquery-ui-sortable' /* , '/wp-includes/js/jquery/ui/jquery.ui.sortable.min.js' */);
		$content_fields = new w2dc_content_fields;
		
		if (isset($_POST['content_fields_order']) && $_POST['content_fields_order']) {
			if ($content_fields->saveOrder($_POST['content_fields_order']))
				w2dc_addMessage(__('Content fields order was updated!', 'W2DC'), 'updated');
		}
		
		$content_fields_table = new w2dc_manage_content_fields_table();
		$content_fields_table->prepareItems($content_fields);
		
		w2dc_renderTemplate('content_fields/content_fields_table.tpl.php', array('content_fields_table' => $content_fields_table, 'fields_types_names' => $content_fields->fields_types_names));
	}
	
	public function addOrEditContentField($field_id = null) {
		global $w2dc_instance;
	
		$content_fields = $w2dc_instance->content_fields;
	
		if (!$content_field = $content_fields->getContentFieldById($field_id)) {
			// this will be new field
			if (isset($_POST['type'])) {
				// load dummy content field by its type from $_POST
				$field_class_name = 'w2dc_content_field_' . $_POST['type'];
				if (class_exists($field_class_name)) {
					$content_field = new $field_class_name;
				} else {
					w2dc_addMessage('This type of content field does not exist!', 'error');
					w2dc_renderTemplate('content_fields/add_edit_content_field.tpl.php', array('content_fields' => $content_fields, 'content_field' => $content_field, 'field_id' => $field_id, 'fields_types_names' => $content_fields->fields_types_names));
				}
			} else 
				$content_field = new w2dc_content_field();
		}

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_content_fields_nonce'], W2DC_PATH)) {
			$validation = $content_field->validation();

			if ($validation->run()) {
				if ($content_field->id) {
					if ($content_fields->saveContentFieldFromArray($field_id, $validation->result_array())) {
						w2dc_addMessage(__('Content field was updated successfully!', 'W2DC'));
					}
				} else {
					if ($content_fields->createContentFieldFromArray($validation->result_array())) {
						w2dc_addMessage(__('Content field was created succcessfully!', 'W2DC'));
					}
				}
				$this->showContentFieldsTable();
			} else {
				$content_field->buildContentFieldFromArray($validation->result_array());
				w2dc_addMessage($validation->error_string(), 'error');
	
				w2dc_renderTemplate('content_fields/add_edit_content_field.tpl.php', array('content_fields' => $content_fields, 'content_field' => $content_field, 'field_id' => $field_id, 'fields_types_names' => $content_fields->fields_types_names));
			}
		} else {
			w2dc_renderTemplate('content_fields/add_edit_content_field.tpl.php', array('content_fields' => $content_fields, 'content_field' => $content_field, 'field_id' => $field_id, 'fields_types_names' => $content_fields->fields_types_names));
		}
	}

	public function configureContentField($field_id) {
		global $w2dc_instance;
	
		if (($content_field = $w2dc_instance->content_fields->getContentFieldById($field_id)) && $content_field->isConfigurationPage())
			$content_field->configure();
		else {
			w2dc_addMessage(__('This content field can\'t be configured', 'W2DC'), 'error');
			$this->showContentFieldsTable();
		}
	}

	public function deleteContentField($field_id) {
		global $w2dc_instance;
	
		$content_fields = $w2dc_instance->content_fields;
		// core fields can't be deleted
		if (($content_field = $content_fields->getContentFieldById($field_id)) && !$content_field->is_core_field) {
			if (w2dc_getValue($_POST, 'submit')) {
				if ($content_fields->deleteContentField($field_id))
					w2dc_addMessage(__('Content field was deleted successfully!', 'W2DC'));
	
				$this->showContentFieldsTable();
			} else
				w2dc_renderTemplate('delete_question.tpl.php', array('heading' => __('Delete conent field', 'W2DC'), 'question' => sprintf(__('Are you sure you want delete "%s" content field?', 'W2DC'), $content_field->name), 'item_name' => $content_field->name));
		} else
			$this->showContentFieldsTable();
	}
	
	public function select_field_icon() {
		$custom_fields_icons = array();
		
		$custom_fields_icons_files = scandir(W2DC_FIELDS_ICONS_PATH);
		foreach ($custom_fields_icons_files AS $file)
			if (is_file(W2DC_FIELDS_ICONS_PATH . $file) && $file != '.' && $file != '..')
				$custom_fields_icons[] = $file;

		w2dc_renderTemplate('content_fields/select_icons.tpl.php', array('custom_fields_icons' => $custom_fields_icons));
		die();
	}
	
	public function addContentFieldsMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE) {
			global $w2dc_instance;
			
			if ($w2dc_instance->content_fields->isNotCoreContentFields())
				add_meta_box('w2dc_content_fields',
						__('Content fields', 'W2DC'),
						array($this, 'contentFieldsMetabox'),
						W2DC_POST_TYPE,
						'normal',
						'high');
		}
	}
	
	public function contentFieldsMetabox($post) {
		global $w2dc_instance;

		if ($listing = w2dc_getCurrentListingInAdmin())
			$content_fields = $listing->content_fields + $w2dc_instance->content_fields->content_fields_array;
		else
			$content_fields = $w2dc_instance->content_fields->content_fields_array;
		w2dc_renderTemplate('content_fields/content_fields_metabox.tpl.php', array('content_fields' => $content_fields));
	}
}

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class w2dc_manage_content_fields_table extends WP_List_Table {

	public function __construct() {
		parent::__construct(array(
				'singular' => __('content field', 'WPBDM'),
				'plural' => __('content fields', 'WPBDM'),
				'ajax' => false
		));
	}

	public function getColumns() {
		$columns = array(
				'field_name' => __('Name', 'W2DC'),
				'field_type' => __('Field type', 'W2DC'),
				'required' => __('Required', 'W2DC'),
				'icon_image' => __('Icon image', 'W2DC'),
				'in_pages' => '',
		);
		$columns = apply_filters('w2dc_content_field_table_header', $columns);

		return $columns;
	}

	public function getItems($content_fields) {
		$items_array = array();
		foreach ($content_fields->content_fields_array as $id=>$content_field) {
			$items_array[$id] = array(
					'id' => $content_field->id,
					'is_core_field' => $content_field->is_core_field,
					'field_name' => $content_field->name,
					'field_type' => $content_field->type,
					'required' => $content_field->is_required,
					'can_be_required' => $content_field->canBeRequired(),
					'is_configuration_page' => $content_field->isConfigurationPage(),
					'is_search_configuration_page' => $content_field->isSearchConfigurationPage(),
					'icon_image' => $content_field->icon_image,
					'on_exerpt_page' => $content_field->on_exerpt_page,
					'on_listing_page' => $content_field->on_listing_page,
					'on_search_form' => $content_field->on_search_form,
			);
			$items_array[$id] = apply_filters('w2dc_content_field_table_row', $items_array[$id], $content_field);
		}
		return $items_array;
	}

	public function prepareItems($levels) {
		$this->_column_headers = array($this->getColumns(), array(), array());

		$this->items = $this->getItems($levels);
	}

	public function column_field_name($item) {
		$actions['edit'] = sprintf('<a href="?page=%s&action=%s&field_id=%d">' . __('Edit', 'W2DC') . '</a>', $_GET['page'], 'edit', $item['id']);
		if ($item['is_configuration_page'])
			$actions['configure'] = sprintf('<a href="?page=%s&action=%s&field_id=%d">' . __('Configure', 'W2DC') . '</a>', $_GET['page'], 'configure', $item['id']);

		$actions = apply_filters('w2dc_content_fields_column_options', $actions, $item);

		if (!$item['is_core_field'])
			$actions['delete'] = sprintf('<a href="?page=%s&action=%s&field_id=%d">' . __('Delete', 'W2DC') . '</a>', $_GET['page'], 'delete', $item['id']);
		return sprintf('%1$s %2$s', sprintf('<a href="?page=%s&action=%s&field_id=%d">' . $item['field_name'] . '</a><input type="hidden" class="content_field_weight_id" value="%d" />', $_GET['page'], 'edit', $item['id'], $item['id']), $this->row_actions($actions));
	}

	public function column_field_type($item) {
		global $w2dc_instance;

		return $w2dc_instance->content_fields->fields_types_names[$item['field_type']];
	}

	public function column_required($item) {
		if ($item['can_be_required'])
			if ($item['required'])
				return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
			else
				return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
		else
			return ' ';
	}

	public function column_icon_image($item) {
		if ($item['icon_image'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/content_fields_icons/' . $item['icon_image'] . '" />';
		else
			return ' ';
	}

	public function column_in_pages($item) {
		$html = array();
		if ($item['on_exerpt_page'])
			$html[] = __('On exerpt', 'W2DC');
		if ($item['on_listing_page'])
			$html[] = __('On listing', 'W2DC');
		
		$html = apply_filters('w2dc_content_fields_in_pages_options', $html, $item);
		
		if ($html)
			return implode('<br />', $html);
		else
			return ' ';
	}

	public function column_default($item, $column_name) {
		switch($column_name) {
			default:
				return $item[$column_name];
		}
	}

	function no_items() {
		__('No content fields found.', 'W2DC');
	}
}
?>