<?php 

class w2dc_content_field_checkbox extends w2dc_content_field_select {
	public $value = array();
	protected $can_be_searched = true;

	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/checkbox_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors) {
		$field_index = 'w2dc_field_input_' . $this->id . '[]';

		$validation = new form_validation();
		$validation->set_rules($field_index, $this->name);
		if (!$validation->run())
			$errors[] = $validation->error_string();
		elseif ($selected_items_array = $validation->result_array($field_index)) {
			foreach ($selected_items_array AS $selected_item) {
				if (!in_array($selected_item, $this->selection_items))
					$errors[] = sprintf(__('This selection option "%s" doesn\'t exist', 'W2DC'), $selected_item);
			}
	
			return $selected_items_array;
		} elseif ($this->canBeRequired() && $this->is_required)
			$errors[] = sprintf(__('At least one option must be selected in "%s" content field', 'W2DC'), $this->name);
	}
	
	public function saveValue($post_id, $validation_results) {
		if ($validation_results && is_array($validation_results)) {
			delete_post_meta($post_id, '_content_field_' . $this->id);
			foreach ($validation_results AS $value)
				add_post_meta($post_id, '_content_field_' . $this->id, $value);
		}
		return true;
	}
	
	public function loadValue($post_id) {
		if (!($this->value = get_post_meta($post_id, '_content_field_' . $this->id)) || $this->value[0] == '')
			$this->value = array();
	}
	
	public function renderOutput($listing) {
		w2dc_renderTemplate('content_fields/fields/checkbox_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
}
?>