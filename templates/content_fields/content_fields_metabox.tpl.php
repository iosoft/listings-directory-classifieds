<script>
	jQuery(document).ready(function($) {
		var fields_in_categories = new Array();
<?php
foreach ($content_fields AS $content_field): 
	if (!$content_field->is_core_field)
		if (!$content_field->isCategories() || $content_field->categories === array()) { ?>
			fields_in_categories[<? echo $content_field->id?>] = [];
	<? } else { ?>
			fields_in_categories[<? echo $content_field->id?>] = [<? echo implode(',', $content_field->categories); ?>];
	<? } ?>
<?php endforeach; ?>

		hideShowFields();

		$("input[name=tax_input\\[w2dc-category\\]\\[\\]]").change(function() {hideShowFields()});
		$("#w2dc-category-pop input[type=checkbox]").change(function() {hideShowFields()});

		function hideShowFields() {
			var selected_categories_ids = [];
			$.each($("input[name=tax_input\\[w2dc-category\\]\\[\\]]:checked"), function() {
				selected_categories_ids.push($(this).val());
			})

			$(".w2dc_field_input_block").hide();
			$.each(fields_in_categories, function(index, value) {
				if (value != undefined && (value == '' || intersect_arrays_safe(value, selected_categories_ids) != ''))
					if ($(".w2dc_field_input_block_"+index).length)
						$(".w2dc_field_input_block_"+index).show();
			});
		}
	});
</script>

<div class="content_fields_metabox">
	<p class="description_big"><?php _e('Content fields may be dependent on selected categories', 'W2DC'); ?></p>
	<?php
	foreach ($content_fields AS $content_field) {
		if (!$content_field->is_core_field)
			$content_field->renderInput();
	}
	?>
</div>