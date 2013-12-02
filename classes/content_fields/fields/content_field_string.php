<?php 

class w2dc_content_field_string extends w2dc_content_field {
	public $max_length = 255;
	public $input_size = 50;
	public $regex;
	
	protected $can_be_searched = true;
	protected $is_search_configuration_page = true;
	protected $is_configuration_page = true;

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('max_length', __('Max length', 'W2DC'), 'required|is_natural_no_zero');
			$validation->set_rules('input_size', __('Input HTML field size', 'W2DC'), 'required|is_natural_no_zero');
			$validation->set_rules('regex', __('PHP RegEx template', 'W2DC'));
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update('wp_w2dc_content_fields', array('options' => serialize(array('max_length' => $result['max_length'], 'input_size' => $result['input_size'], 'regex' => $result['regex']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->max_length = $validation->result_array('max_length');
				$this->input_size = $validation->result_array('input_size');
				$this->regex = $validation->result_array('regex');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/string_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/string_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['max_length']))
			$this->max_length = $this->options['max_length'];

		if (isset($this->options['input_size']))
			$this->input_size = $this->options['input_size'];

		if (isset($this->options['regex']))
			$this->regex = $this->options['regex'];
		
	}
	
	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/string_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors) {
		$field_index = 'w2dc_field_input_' . $this->id;
		
		if (isset($_POST[$field_index]) && $_POST[$field_index] && $this->regex)
			if (@!preg_match('/^' . $this->regex . '$/', $_POST[$field_index]))
				$errors[] = sprintf(__('Field %s doesn\'t match template!', 'W2DC'), $this->name);

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
		w2dc_renderTemplate('content_fields/fields/string_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
	
	public function orderParams() {
		return array('orderby' => 'meta_value_num', 'meta_key' => '_content_field_' . $this->id);
	}
}
?>