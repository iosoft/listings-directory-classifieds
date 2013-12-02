<?php 

class w2dc_content_field_textarea extends w2dc_content_field {
	public $max_length = 500;

	protected $can_be_ordered = false;
	protected $is_configuration_page = true;
	protected $can_be_searched = true;
	protected $is_search_configuration_page = true;

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('max_length', __('Max length', 'W2DC'), 'required|is_natural_no_zero');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update('wp_w2dc_content_fields', array('options' => serialize(array('max_length' => $result['max_length']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->max_length = $validation->result_array('max_length');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/textarea_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/textarea_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['max_length']))
			$this->max_length = $this->options['max_length'];
	}
	
	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/textarea_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors) {
		$field_index = 'w2dc_field_input_' . $this->id;
	
		$validation = new form_validation();
		$rules = 'max_length[' . $this->max_length . ']';
		if ($this->canBeRequired() && $this->is_required)
			$rules .= '|required';
		$validation->set_rules($field_index, $this->name, $rules);
		if (!$validation->run())
			$errors[] = $validation->error_string();

		return $validation->result_array($field_index);
	}
	
	public function saveValue($post_id, $validation_results) {
		return update_post_meta($post_id, '_content_field_' . $this->id, $validation_results);
	}
	
	public function loadValue($post_id) {
		$this->value = get_post_meta($post_id, '_content_field_' . $this->id, true);
	}
	
	public function renderOutput($listing) {
		w2dc_renderTemplate('content_fields/fields/textarea_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
}
?>