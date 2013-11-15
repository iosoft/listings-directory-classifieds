<div class="w2dc_field w2dc_field_input_block w2dc_field_input_block_<?php echo $content_field->id; ?>">
	<label><?php echo $content_field->name; ?><?php if ($content_field->canBeRequired() && $content_field->is_required): ?><span class="red_asterisk">*</span><?php endif; ?></label>
	<input type="text" name="w2dc_field_input_<?php echo $content_field->id; ?>" class="w2dc_field_input_email regular-text" value="<?php echo esc_attr($content_field->value); ?>" />
	<?php if ($content_field->description): ?><p class="description"><?php echo $content_field->description; ?></p><?php endif; ?>
</div>