<?php 

class w2dc_content_field_price extends w2dc_content_field {
	public $currency_symbol;
	public $currency_code;
	public $decimal_separator = ',';
	public $thousands_separator = ' ';

	protected $is_configuration_page = true;
	protected $is_search_configuration_page = true;
	protected $can_be_searched = true;

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('currency_symbol', __('Currency symbol', 'W2DC'), 'required');
			$validation->set_rules('currency_code', __('Currency code', 'W2DC'), 'required|alpha|exact_length[3]');
			$validation->set_rules('decimal_separator', __('Decimal separator', 'W2DC'), 'required|max_length[1]');
			$validation->set_rules('thousands_separator', __('Thousands separator', 'W2DC'), 'max_length[1]');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update('wp_w2dc_content_fields', array('options' => serialize(array('currency_symbol' => $result['currency_symbol'], 'currency_code' => $result['currency_code'], 'decimal_separator' => $result['decimal_separator'], 'thousands_separator' => $result['thousands_separator']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->admin->content_fields_manager->showContentFieldsTable();
			} else {
				$this->currency_symbol = $validation->result_array('currency_symbol');
				$this->currency_code = $validation->result_array('currency_code');
				$this->decimal_separator = $validation->result_array('decimal_separator');
				$this->thousands_separator = $validation->result_array('thousands_separator');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/price_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/price_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['currency_symbol']))
			$this->currency_symbol = $this->options['currency_symbol'];
		if (isset($this->options['currency_code']))
			$this->currency_code = $this->options['currency_code'];
		if (isset($this->options['decimal_separator']))
			$this->decimal_separator = $this->options['decimal_separator'];
		if (isset($this->options['thousands_separator']))
			$this->thousands_separator = $this->options['thousands_separator'];
	}
	
	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/price_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors) {
		$field_index = 'w2dc_field_input_' . $this->id;
	
		$validation = new form_validation();
		$rules = 'numeric';
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
		if (is_numeric($this->value)) {
			$formatted_price = number_format($this->value, 2, $this->decimal_separator, $this->thousands_separator);
	
			w2dc_renderTemplate('content_fields/fields/price_output.tpl.php', array('content_field' => $this, 'formatted_price' => $formatted_price, 'listing' => $listing));
		}
	}
	
	public function orderParams() {
		return array('orderby' => 'meta_value_num', 'meta_key' => '_content_field_' . $this->id);
	}
}
?>