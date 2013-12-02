<?php 

class w2dc_levels_manager {
	public function __construct() {
		add_action('admin_menu', array($this, 'menu'));
	}

	public function menu() {
		add_submenu_page('w2dc_admin',
			__('Listings levels', 'W2DC'),
			__('Listings levels', 'W2DC'),
			'administrator',
			'w2dc_levels',
			array($this, 'w2dc_manage_levels_page')
		);
	}

	public function w2dc_manage_levels_page() {

		if (isset($_GET['action']) && $_GET['action'] == 'add') {
			$this->addOrEditLevel();
		} elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['level_id'])) {
			$this->addOrEditLevel($_GET['level_id']);
		} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['level_id'])) {
			$this->deleteLevel($_GET['level_id']);
		} else {
			$this->showLevelsTable();
		}
	}
	
	public function showLevelsTable() {
		wp_enqueue_script('jquery-ui-sortable' /* , '/wp-includes/js/jquery/ui/jquery.ui.sortable.min.js' */);
		$levels = new w2dc_levels;
		
		if (isset($_POST['levels_order']) && $_POST['levels_order']) {
			if ($levels->saveOrder($_POST['levels_order']))
				w2dc_addMessage(__('Levels order was updated!', 'W2DC'), 'updated');
		}
		
		$levels_table = new w2dc_manage_levels_table();
		$levels_table->prepareItems($levels);
		
		//wp_nounce();
		w2dc_renderTemplate('levels/levels_table.tpl.php', array('levels_table' => $levels_table));
	}
	
	public function addOrEditLevel($level_id = null) {
		global $w2dc_instance;

		$levels = $w2dc_instance->levels;
		
		if (!$level = $levels->getLevelById($level_id))
			$level = new w2dc_level();

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_levels_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('name', __('Level name', 'W2DC'), 'required');
			$validation->set_rules('active_years', __('years', 'W2DC'), 'is_natural');
			$validation->set_rules('active_months', __('months', 'W2DC'), 'is_natural');
			$validation->set_rules('active_days', __('days', 'W2DC'), 'is_natural');
			$validation->set_rules('description', __('Level description', 'W2DC'));
			$validation->set_rules('raiseup_enabled', __('Ability to raise up listings', 'W2DC'), 'is_checked');
			$validation->set_rules('sticky', __('Sticky listings', 'W2DC'), 'is_checked');
			$validation->set_rules('featured', __('Featured listings', 'W2DC'), 'is_checked');
			$validation->set_rules('categories_number', __('Categories number available', 'W2DC'), 'is_natural');
			$validation->set_rules('unlimited_categories', __('Unlimited categories number', 'W2DC'), 'is_checked');
			$validation->set_rules('google_map', __('Enable google map', 'W2DC'), 'is_checked');
			$validation->set_rules('logo_enabled', __('Enable listing logo', 'W2DC'), 'is_checked');
			$validation->set_rules('logo_size', __('Listing logo size', 'W2DC'), 'alpha_dash');
			$validation->set_rules('images_number', __('Images number available', 'W2DC'), 'is_natural');
			$validation->set_rules('videos_number', __('Videos number available', 'W2DC'), 'is_natural');
			$validation->set_rules('categories_list', __('Assigned categories', 'W2DC'));
			apply_filters('w2dc_level_validation', $validation);
		
			if ($validation->run()) {
				if ($level->id) {
					if ($levels->saveLevelFromArray($level_id, $validation->result_array())) {
						w2dc_addMessage(__('Level was updated successfully!', 'W2DC'));
					}
				} else {
					if ($levels->createLevelFromArray($validation->result_array())) {
						w2dc_addMessage(__('Level was created succcessfully!', 'W2DC'));
					}
				}
				$this->showLevelsTable();
			} else {
				$level->buildLevelFromArray($validation->result_array());
				w2dc_addMessage($validation->error_string(), 'error');
		
				w2dc_renderTemplate('levels/add_edit_level.tpl.php', array('level' => $level, 'level_id' => $level_id));
			}
		} else {
			w2dc_renderTemplate('levels/add_edit_level.tpl.php', array('level' => $level, 'level_id' => $level_id));
		}
	}
	
	public function deleteLevel($level_id) {
		global $w2dc_instance;

		$levels = $w2dc_instance->levels;
		if ($level = $levels->getLevelById($level_id)) {
			if (w2dc_getValue($_POST, 'submit')) {
				if ($levels->deleteLevel($level_id))
					w2dc_addMessage(__('Level was deleted successfully!', 'W2DC'));

				$this->showLevelsTable();
			} else
				w2dc_renderTemplate('delete_question.tpl.php', array('heading' => __('Delete level', 'W2DC'), 'question' => sprintf(__('Are you sure you want delete "%s" level with all listings inside?', 'level', 'W2DC'), $level->name), 'item_name' => $level->name));
		} else 
			$this->showLevelsTable();
	}
	
	public function displayChooseLevelTable() {
		global $w2dc_instance;

		$levels = $w2dc_instance->levels;

		$levels_table = new w2dc_choose_levels_table();
		$levels_table->prepareItems($levels);
		
		$levels_count = count($w2dc_instance->levels->levels_array);

		w2dc_renderTemplate('levels/choose_levels_table.tpl.php', array('levels_table' => $levels_table, 'levels_count' => $levels_count));
	}
}

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class w2dc_manage_levels_table extends WP_List_Table {

	public function __construct() {
		parent::__construct(array(
				'singular' => __('level', 'W2DC'),
				'plural' => __('levels', 'W2DC'),
				'ajax' => false
		));
	}

	public function getColumns($levels) {
		$columns = array(
				'level_name' => __('Name', 'W2DC'),
				'active_period' => __('Active period', 'W2DC'),
				'sticky' => __('Sticky', 'W2DC'),
				'featured' => __('Featured', 'W2DC'),
				'categories_number' => __('Categories number', 'W2DC'),
				'google_map' => __('Google map', 'W2DC'),
		);
		$columns = apply_filters('w2dc_level_table_header', $columns, $levels);

		return $columns;
	}
	
	public function getItems($levels) {
		$items_array = array();
		foreach ($levels->levels_array as $id=>$level) {
			$items_array[$id] = array(
					'id' => $level->id,
					'level_name' => $level->name,
					'active_period' => $level->getActivePeriodString(),
					'sticky' => $level->sticky,
					'featured' => $level->featured,
					'categories_number' => $level->categories_number,
					'unlimited_categories' => $level->unlimited_categories,
					'google_map' => $level->google_map,
			);
			if ($level->unlimited_categories)
				$items_array[$id]['categories_number'] = __('Unlimited', 'W2DC');

			$items_array[$id] = apply_filters('w2dc_level_table_row', $items_array[$id], $level);
		}
		return $items_array;
	}

	public function prepareItems($levels) {
		$this->_column_headers = array($this->getColumns($levels), array(), array());
		
		$this->items = $this->getItems($levels);
	}
	
	public function column_level_name($item) {
		$actions = array(
				'edit' => sprintf('<a href="?page=%s&action=%s&level_id=%d">' . __('Edit', 'W2DC') . '</a>', $_GET['page'], 'edit', $item['id']),
				'delete' => sprintf('<a href="?page=%s&action=%s&level_id=%d">' . __('Delete', 'W2DC') . '</a>', $_GET['page'], 'delete', $item['id']),
				);
		return sprintf('%1$s %2$s', sprintf('<a href="?page=%s&action=%s&level_id=%d">' . $item['level_name'] . '</a><input type="hidden" class="level_weight_id" value="%d" />', $_GET['page'], 'edit', $item['id'], $item['id']), $this->row_actions($actions));
	}
	
	public function column_sticky($item) {
		if ($item['sticky'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	}

	public function column_featured($item) {
		if ($item['featured'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	}

	public function column_google_map($item) {
		if ($item['google_map'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	}
	
	public function column_categories_number($item) {
		if ($item['unlimited_categories'])
			return __('Unlimited', 'W2DC');
		else
			return $item['categories_number'];
	}
	
	public function column_default($item, $column_name) {
		switch($column_name) {
			default:
				return $item[$column_name];
		}
	}
	
	function no_items() {
		__('No levels found.', 'W2DC');
	}
}

class w2dc_choose_levels_table extends WP_List_Table {

	public function __construct() {
		parent::__construct(array(
				'singular' => __('level', 'W2DC'),
				'plural' => __('levels', 'W2DC'),
				'ajax' => false
		));
	}

	public function getColumns($levels) {
		$columns = array(
				'level_name' => __('Name', 'W2DC'),
				'active_period' => __('Active period', 'W2DC'),
				'sticky' => __('Sticky', 'W2DC'),
				'featured' => __('Featured', 'W2DC'),
				'categories_number' => __('Categories number', 'W2DC'),
				'google_map' => __('Google map', 'W2DC'),
		);
		$columns = apply_filters('w2dc_level_table_header', $columns, $levels);
		
		$columns = array_merge($columns, array('create' => ''));
		
		return $columns;
	}

	public function getItems($levels) {
		$items_array = array();
		foreach ($levels->levels_array as $id=>$level) {
			$items_array[$id] = array(
					'id' => $level->id,
					'level_name' => $level->name,
					'level_description' => $level->description,
					'active_period' => $level->getActivePeriodString(),
					'sticky' => $level->sticky,
					'featured' => $level->featured,
					'categories_number' => $level->categories_number,
					'unlimited_categories' => $level->unlimited_categories,
					'google_map' => $level->google_map,
			);
			if ($level->unlimited_categories)
				$items_array[$id]['categories_number'] = __('Unlimited', 'W2DC');

			$items_array[$id] = apply_filters('w2dc_level_table_row', $items_array[$id], $level);

			$items_array[$id] = array_merge($items_array[$id], array('create' => __('Create listing in this level', 'W2DC')));
		}
		return $items_array;
	}

	public function prepareItems($levels) {
		$this->_column_headers = array($this->getColumns($levels), array(), array());

		$this->items = $this->getItems($levels);
	}
	
	public function column_level_name($item) {
		if ($item['level_description'])
			return $item['level_name'] . '<div class="hint_icon"></div><div class="hint_msg">' . nl2br($item['level_description']) . '</div>';
		else 
			return $item['level_name'];
	}

	public function column_create($item) {
		return sprintf('<a href="post-new.php?post_type=w2dc_listing&level_id=%d">' . $item['create'] . '</a>', $item['id']);
	}

	public function column_sticky($item) {
		if ($item['sticky'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	}

	public function column_featured($item) {
		if ($item['featured'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	}

	public function column_google_map($item) {
		if ($item['google_map'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	}

	public function column_categories_number($item) {
		if ($item['unlimited_categories'])
			return __('Unlimited', 'W2DC');
		else
			return $item['categories_number'];
	}

	public function column_default($item, $column_name) {
		switch($column_name) {
			default:
				return $item[$column_name];
		}
	}
	
	function no_items() {
		__('No levels found. Can\'t create new listings.', 'W2DC');
	}
}

?>