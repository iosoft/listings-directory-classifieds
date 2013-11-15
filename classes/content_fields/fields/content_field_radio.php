<?php 

class w2dc_content_field_radio extends w2dc_content_field_select {
	protected $can_be_searched = true;
	
	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/radio_input.tpl.php', array('content_field' => $this));
	}
}
?>