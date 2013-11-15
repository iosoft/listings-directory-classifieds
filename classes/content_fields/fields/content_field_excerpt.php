<?php 

class w2dc_content_field_excerpt extends w2dc_content_field {
	protected $can_be_ordered = false;
	protected $is_categories = false;
	protected $is_slug = false;

	public function validateValues(&$errors) {
		$listing = w2dc_getCurrentListingInAdmin();
		if ($this->is_required && (!isset($listing->post->post_excerpt) || $listing->post->post_excerpt == ''))
			$errors[] = __('Listing excerpt is required', 'W2DC');
		else
			return $listing->post->post_excerpt;
	}
	
	public function renderOutput($listing) {
		w2dc_renderTemplate('content_fields/fields/excerpt_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
}
?>