<?php if ($tags): ?>
<div class="w2dc_field w2dc_field_output_block w2dc_field_output_block_<? echo $content_field->id; ?>">
	<?php if ($content_field->icon_image): ?>
		<img class="w2dc_field_icon" src="<?php echo W2DC_RESOURCES_URL; ?>images/content_fields_icons/<?php echo $content_field->icon_image; ?>" />
	<?php endif; ?>
	<?php if (!$content_field->is_hide_name): ?>
		<span class="w2dc_field_name"><?php echo $content_field->name?>:</span>
	<?php endif; ?>
	<span class="w2dc_field_content">
	<?php foreach ($tags AS $tag): ?>
		<a href="<?php echo get_term_link($tag); ?>" title="<?php echo esc_attr($tag->name); ?>"><?php echo $tag->name; ?></a>
	<?php endforeach; ?>
	</span>
</div>
<?php endif; ?>