<?php 

class w2dc_content_field_address extends w2dc_content_field {
	protected $can_be_required = false;
	protected $can_be_ordered = false;
	protected $is_categories = false;
	protected $is_slug = false;

	public function renderOutput($listing) {
		w2dc_renderTemplate('content_fields/fields/address_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
}
?>