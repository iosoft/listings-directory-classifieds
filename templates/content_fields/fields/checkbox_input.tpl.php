<?php if (count($content_field->selection_items)): ?>
<div class="w2dc_field w2dc_field_input_block w2dc_field_input_block_<?php echo $content_field->id; ?>">
	<label><?php echo $content_field->name; ?><?php if ($content_field->canBeRequired() && $content_field->is_required): ?><span class="red_asterisk">*</span><?php endif; ?></label>
	<div class="w2dc_field_input_div">
		<?php foreach ($content_field->selection_items AS $item): ?>
		<label><input type="checkbox" name="w2dc_field_input_<?php echo $content_field->id; ?>[]" class="w2dc_field_input_checkbox" value="<?php echo esc_attr($item); ?>" <?php if (in_array($item, $content_field->value)) echo 'checked'; ?> />&nbsp;&nbsp;<?php echo $item; ?></label><br />
		<?php endforeach; ?>
	</div>
	<div class="clear_float"></div>
	<?php if ($content_field->description): ?><p class="description"><?php echo $content_field->description; ?></p><?php endif; ?>
</div>
<?php endif; ?>