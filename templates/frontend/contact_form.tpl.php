<form method="POST" action="<?php the_permalink($listing->post->ID); ?>#contact">
	<input type="hidden" name="action" value="contact" />
	<input type="hidden" name="listing_id" value="<?php echo $listing->post->ID; ?>" />
	<h3><?php _e('Send message to listing owner', 'W2DC'); ?></h3>
	<div class="w2dc_contact_form">
		<?php if ($current_user): ?>
		<p>
			<?php echo sprintf(__('You are currently logged in as %s. Your message will be sent using your logged in name and email.', 'W2DC'), $current_user->user_login); ?>
		</p>
		<?php else: ?>
		<p>
			<label for="contact_name"><?php _e('Contact Name', 'W2DC'); ?><span class="red_asterisk">*</span></label>
			<input type="text" name="contact_name" value="<?php echo esc_attr(w2dc_getValue($_POST, 'contact_name')); ?>" size="35" />
		</p>
		<p>
			<label for="contact_email"><?php _e("Contact Email", "W2DC"); ?><span class="red_asterisk">*</span></label>
			<input type="text" name="contact_email" value="<?php echo esc_attr(w2dc_getValue($_POST, 'contact_email')); ?>" size="35" />
		</p>
		<?php endif; ?>
		<p>
			<label for="contact_message"><?php _e("Your message", "W2DC"); ?><span class="red_asterisk">*</span></label>
			<textarea name="contact_message" cols="50" rows="6"><?php echo esc_textarea(w2dc_getValue($_POST, 'contact_message')); ?></textarea>
		</p>
		
		<?php echo w2dc_recaptcha(); ?>
		
		<input type="submit" name="submit" value="<?php _e('Send', 'W2DC'); ?>" />
	</div>
</form>