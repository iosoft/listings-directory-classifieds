<?php if (count($content_field->selection_items)): ?>
<div class="w2dc_field w2dc_field_input_block w2dc_field_input_block_<?php echo $content_field->id; ?>">
	<label><?php echo $content_field->name; ?><?php if ($content_field->canBeRequired() && $content_field->is_required): ?><span class="red_asterisk">*</span><?php endif; ?></label>
	<select name="w2dc_field_input_<?php echo $content_field->id; ?>" class="w2dc_field_input_select">
	<option value=""><?php _e('- Select item -', 'W2DC'); ?></option>
	<?php foreach ($content_field->selection_items AS $item): ?>
		<option value="<?php echo $item; ?>" <?php if ($content_field->value == $item) echo 'selected'; ?>><?php echo $item; ?></option>
	<?php endforeach; ?>
	</select>
	<?php if ($content_field->description): ?><p class="description"><?php echo $content_field->description; ?></p><?php endif; ?>
</div>
<?php endif; ?>