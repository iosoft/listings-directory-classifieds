<?php 

class w2dc_content_field_email extends w2dc_content_field {
	protected $can_be_ordered = false;

	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/email_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors) {
		$field_index = 'w2dc_field_input_' . $this->id;

		$validation = new form_validation();
		$rules = 'valid_email';
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
		w2dc_renderTemplate('content_fields/fields/email_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
}
?>