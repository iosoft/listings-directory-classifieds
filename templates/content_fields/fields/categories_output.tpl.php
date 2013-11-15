<?php if ($categories): ?>
<div class="w2dc_field w2dc_field_output_block w2dc_field_output_block_<? echo $content_field->id; ?>">
	<?php if ($content_field->icon_image): ?>
		<img class="w2dc_field_icon" src="<?php echo W2DC_RESOURCES_URL; ?>images/content_fields_icons/<?php echo $content_field->icon_image; ?>" />
	<?php endif; ?>
	<?php if (!$content_field->is_hide_name): ?>
		<span class="w2dc_field_name"><?php echo $content_field->name?>:</span>
	<?php endif; ?>
	<span class="w2dc_field_content">
	<?php foreach ($categories AS $category): ?>
		<a href="<?php echo get_term_link($category); ?>" title="<?php echo esc_attr($category->name); ?>"><?php echo $category->name; ?></a>
	<?php endforeach; ?>
	</span>
</div>
<?php endif; ?>