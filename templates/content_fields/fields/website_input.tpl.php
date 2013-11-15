<div class="w2dc_field w2dc_field_input_block w2dc_field_input_block_<?php echo $content_field->id; ?>">
	<label><?php echo $content_field->name; ?><?php if ($content_field->canBeRequired() && $content_field->is_required): ?><span class="red_asterisk">*</span><?php endif; ?></label>
	<div class="w2dc_field_input_div">
		<?php _e('URL:', 'W2DC'); ?><br />
		<input type="text" name="w2dc_field_input_url_<?php echo $content_field->id; ?>" class="w2dc_field_input_url regular-text" value="<?php echo esc_url($content_field->value['url']); ?>" /><br />
		<?php _e('Link text:', 'W2DC'); ?><br />
		<input type="text" name="w2dc_field_input_text_<?php echo $content_field->id; ?>" class="w2dc_field_input_text regular-text" value="<?php echo esc_attr($content_field->value['text']); ?>" />
	</div>
	<div class="clear_float"></div>
	<?php if ($content_field->description): ?><p class="description"><?php echo $content_field->description; ?></p><?php endif; ?>
</div>