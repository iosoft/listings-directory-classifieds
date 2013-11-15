<?php if ($formatted_date): ?>
<div class="w2dc_field w2dc_field_output_block w2dc_field_output_block_<? echo $content_field->id; ?>">
	<?php if ($content_field->icon_image): ?>
		<img class="w2dc_field_icon" src="<?php echo W2DC_RESOURCES_URL; ?>images/content_fields_icons/<?php echo $content_field->icon_image; ?>" />
	<?php endif; ?>
	<?php if (!$content_field->is_hide_name): ?>
	<span class="w2dc_field_name"><?php echo $content_field->name?>:</span>
	<?php endif; ?>
	<span class="w2dc_field_content">
		<?php echo $formatted_date; ?> <?php if($content_field->is_time) echo $content_field->value['hour'] . ':' . $content_field->value['minute']; ?>
	</span>
</div>
<?php endif; ?>