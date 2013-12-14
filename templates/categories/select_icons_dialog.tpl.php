		<input type="button" id="reset_icon" class="button button-primary button-large" value="<?php _e('Reset icon image', 'W2DC'); ?>" />

		<div class="theme_block">
		<?php foreach ($categories_icons AS $icon): ?>
			<div class="icon" icon_file="<?php echo $icon; ?>"><img src="<?php echo W2DC_CATEGORIES_ICONS_URL . $icon; ?>" title="<?php echo $icon; ?>" /></div>
		<?php endforeach;?>
		</div>
		<div class="clear_float"></div>