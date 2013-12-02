<?php 

class w2dc_content_field_website extends w2dc_content_field {
	public $is_blank = false;
	public $is_nofollow = false;
	
	protected $can_be_ordered = false;
	protected $is_configuration_page = true;

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('is_blank', __('Open link in new window', 'W2DC'), 'is_checked');
			$validation->set_rules('is_nofollow', __('Add nofollow attribute', 'W2DC'), 'is_checked');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update('wp_w2dc_content_fields', array('options' => serialize(array('is_blank' => $result['is_blank'], 'is_nofollow' => $result['is_nofollow']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->is_blank = $validation->result_array('is_blank');
				$this->is_nofollow = $validation->result_array('is_nofollow');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/website_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/website_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['is_blank']))
			$this->is_blank = $this->options['is_blank'];

		if (isset($this->options['is_nofollow']))
			$this->is_nofollow = $this->options['is_nofollow'];
		
	}
	
	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/website_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors) {
		$field_index_url = 'w2dc_field_input_url_' . $this->id;
		$field_index_text = 'w2dc_field_input_text_' . $this->id;

		$validation = new form_validation();
		$rules = '';
		if ($this->canBeRequired() && $this->is_required)
			$rules .= '|required';
		$validation->set_rules($field_index_url, $this->name, $rules);
		$validation->set_rules($field_index_text, $this->name);
		if (!$validation->run())
			$errors[] = $validation->error_string();

		return array('url' => $validation->result_array($field_index_url), 'text' => $validation->result_array($field_index_text));
	}
	
	public function saveValue($post_id, $validation_results) {
		if ($validation_results && is_array($validation_results))
			$value = serialize($validation_results);
		else
			$value = '';
		return update_post_meta($post_id, '_content_field_' . $this->id, $value);
	}
	
	public function loadValue($post_id) {
		if ($value = get_post_meta($post_id, '_content_field_' . $this->id, true)) {
			$unserialized_value = unserialize($value);
			if (count($unserialized_value) > 1 || $unserialized_value != array(''))
				$this->value = $unserialized_value;
			else
				$this->value = array('url' => '', 'text' => '');
		} else
			$this->value = array('url' => '', 'text' => '');
	}
	
	public function renderOutput($listing) {
		w2dc_renderTemplate('content_fields/fields/website_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
}
?>