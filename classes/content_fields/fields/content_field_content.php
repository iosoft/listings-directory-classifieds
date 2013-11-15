<?php 

class w2dc_content_field_content extends w2dc_content_field {
	protected $can_be_ordered = false;
	protected $is_categories = false;
	protected $is_slug = false;

	public function validateValues(&$errors, $data) {
		$listing = w2dc_getCurrentListingInAdmin();
		if ($this->is_required && (!isset($data['post_content']) || !$data['post_content']))
			$errors[] = __('Listing content is required', 'W2DC');
		else
			return $listing->post->post_content;
	}
	
	public function renderOutput($listing) {
		w2dc_renderTemplate('content_fields/fields/content_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
}
?>