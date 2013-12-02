<?php 

class w2dc_content_field_datetime extends w2dc_content_field {
	public $is_time = true;
	
	protected $is_configuration_page = true;
	protected $can_be_searched = true;

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('is_time', __('Enable time in field', 'W2DC'), 'is_checked');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update('wp_w2dc_content_fields', array('options' => serialize(array('is_time' => $result['is_time']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->is_time = $validation->result_array('is_time');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/datetime_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/datetime_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['is_time']))
			$this->is_time = $this->options['is_time'];
	}
	
	public function delete() {
		global $wpdb;
	
		$wpdb->delete('wp_postmeta', array('meta_key' => '_content_field_' . $this->id . '_date'));
		$wpdb->delete('wp_postmeta', array('meta_key' => '_content_field_' . $this->id . '_hour'));
		$wpdb->delete('wp_postmeta', array('meta_key' => '_content_field_' . $this->id . '_minute'));
	
		$wpdb->delete('wp_w2dc_content_fields', array('id' => $this->id));
		return true;
	}
	
	public function renderInput() {
		wp_enqueue_script('jquery-ui-datepicker');
		
		$wp_date_format = get_option('date_format');
		$dpicker_format = str_replace(
				array('S',  'd', 'j',  'l',  'm', 'n',  'F',  'Y'),
				array('',  'dd', 'd', 'DD', 'mm', 'm', 'MM', 'yy'),
		$wp_date_format);

		w2dc_renderTemplate('content_fields/fields/datetime_input.tpl.php', array('content_field' => $this, 'dateformat' => $dpicker_format));
	}
	
	public function validateValues(&$errors) {
		$field_index_date = 'w2dc_field_input_' . $this->id;
		$field_index_hour = 'w2dc_field_input_hour_' . $this->id;
		$field_index_minute = 'w2dc_field_input_minute_' . $this->id;

		$validation = new form_validation();
		$rules = '';
		if ($this->canBeRequired() && $this->is_required)
			$rules .= 'required|is_natural_no_zero';
		$validation->set_rules($field_index_date, $this->name, $rules);
		$validation->set_rules($field_index_hour, $this->name);
		$validation->set_rules($field_index_minute, $this->name);
		if (!$validation->run())
			$errors[] = $validation->error_string();

		return array('date' => $validation->result_array($field_index_date), 'hour' => $validation->result_array($field_index_hour), 'minute' => $validation->result_array($field_index_minute));
	}
	
	public function saveValue($post_id, $validation_results) {
		if ($validation_results && is_array($validation_results)) {
			update_post_meta($post_id, '_content_field_' . $this->id . '_date', $validation_results['date']);
			update_post_meta($post_id, '_content_field_' . $this->id . '_hour', $validation_results['hour']);
			update_post_meta($post_id, '_content_field_' . $this->id . '_minute', $validation_results['minute']);
			return true;
		}
	}
	
	public function loadValue($post_id) {
		$this->value = array(
			'date' => get_post_meta($post_id, '_content_field_' . $this->id . '_date', true),
			'hour' => get_post_meta($post_id, '_content_field_' . $this->id . '_hour', true),
			'minute' => get_post_meta($post_id, '_content_field_' . $this->id . '_minute', true)
		);
			
	}
	
	public function renderOutput($listing) {
		if ($this->value['date']) {
			$formatted_date = mysql2date(get_option('date_format'), date('Y-m-d H:i:s', $this->value['date']));
	
			w2dc_renderTemplate('content_fields/fields/datetime_output.tpl.php', array('content_field' => $this, 'formatted_date' => $formatted_date, 'listing' => $listing));
		}
	}
	
	public function orderParams() {
		return array('orderby' => 'meta_value_num', 'meta_key' => '_content_field_' . $this->id . '_date');
	}
}
?>