<?php 

class w2dc_content_field_tags extends w2dc_content_field {
	protected $can_be_required = false;
	protected $can_be_ordered = false;
	protected $is_categories = false;
	protected $is_slug = false;

	public function renderOutput($listing) {
		$tags = get_the_terms($listing->post->ID, W2DC_TAGS_TAX);

		w2dc_renderTemplate('content_fields/fields/tags_output.tpl.php', array('content_field' => $this, 'tags' => $tags, 'listing' => $listing));
	}
}
?>