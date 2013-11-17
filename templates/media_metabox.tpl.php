<?php if ($listing->level->images_number): ?>
<?php
$img_width = get_option('thumbnail_size_w'); 
$img_height = get_option('thumbnail_size_h'); 
?>
<script>
	var images_number = <?php echo $listing->level->images_number; ?>;

	function addImageDiv(attachment_url, attachment_id) {
		jQuery('<div class="w2dc_attached_item"><div class="w2dc_delete_attached_item delete_item" title="<?php _e('remove image', 'W2DC'); ?>"></div><input type="hidden" name="attached_image_id[]" value="' + attachment_id + '" /><div class="w2dc_img_div_border" style="width: <?php echo $img_width; ?>px; height: <?php echo $img_height; ?>px"><span class="w2dc_img_div_helper"></span><img src="' + attachment_url + '" style="max-width: <?php echo $img_width; ?>px; max-height: <?php echo $img_height; ?>px" /></div><input type="text" name="attached_image_title[]" size="37" /><br /><?php if ($listing->level->logo_enabled): ?><label><input type="radio" name="attached_image_as_logo" value="' + attachment_id + '"> <?php _e('set this image as logo', 'W2DC'); ?></label><?php endif; ?></div>').appendTo("#images_wrapper");

		if (images_number <= jQuery("#images_wrapper .w2dc_attached_item").length)
			jQuery("#upload_functions").hide();
	}

	jQuery(document).ready(function($) {
		$("#images_wrapper .delete_item").live('click', function() {
			$(this).parent().remove();

			if (images_number > $("#images_wrapper .w2dc_attached_item").length)
				$("#upload_functions").show();
		});
	});
</script>

<div id="upload_wrapper">
	<h2>
		<?php _e('Listing images', 'W2DC'); ?>
	</h2>

	<div id="images_wrapper">
	<?php foreach ($listing->images AS $attachment_id=>$attachment): ?>
		<?php $src = wp_get_attachment_image_src($attachment_id, 'thumbnail'); ?>
		<?php $src_full = wp_get_attachment_image_src($attachment_id, 'full'); ?>
		<div class="w2dc_attached_item">
			<div class="w2dc_delete_attached_item delete_item" title="<?php _e('remove image', 'W2DC'); ?>"></div>
			<input type="hidden" name="attached_image_id[]" value="<?php echo $attachment_id; ?>" />
			<div class="w2dc_img_div_border" style="width: <?php echo $img_width; ?>px; height: <?php echo $img_height; ?>px">
				<span class="w2dc_img_div_helper"></span><a href="<?php echo $src_full[0]; ?>" data-lightbox="listing_images"><img src="<?php echo $src[0]; ?>" style="max-width: <?php echo $img_width; ?>px; max-height: <?php echo $img_height; ?>px" /></a>
			</div>
			<input type="text" name="attached_image_title[]" size="37" value="<?php echo esc_attr($attachment['post_title']); ?>" /><br />
			<?php if ($listing->level->logo_enabled): ?>
			<label><input type="radio" name="attached_image_as_logo" value="<?php echo $attachment_id; ?>" <?php checked($listing->logo_image, $attachment_id); ?>> <?php _e('set this image as logo', 'W2DC'); ?></label>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	</div>
	<div class="clear_float"></div>

	<div id="upload_functions" <?php if (count($listing->images) >= $listing->level->images_number): ?>style="display: none;"<?php endif; ?>>
		<input id="browse_file" name="browse_file" type="file" size="45" />
		<br />
		<br />
		<label><input type="checkbox" id="crop_image" value="1" /> <?php _e('Crop thumbnail to exact dimensions (normally thumbnails are proportional)'); ?></label>
		<br />
		<br />
		<input
			type="button"
			class="button button-primary"
			onclick="return ajaxImageFileUploadToGallery(
				jQuery('#crop_image').is(':checked'),
				'<?php echo admin_url('admin-ajax.php?action=upload_image&post_id='.$listing->post->ID.'&_wpnonce='.wp_create_nonce('upload_images')); ?>',
				'<?php _e('Choose image to upload first!', 'W2DC'); ?>'
			);"
			value="<?php _e('Upload image', 'W2DC'); ?>" />
	</div>
</div>
<?php endif; ?>


<?php if ($listing->level->videos_number): ?>
<script>
	var videos_number = <?php echo $listing->level->videos_number; ?>;

	function attachYoutubeVideo() {
		if (jQuery("#attach_video_input").val()) {
			if (matches = jQuery("#attach_video_input").val().match(/https?:\/\/(?:[a-zA_Z]{2,3}.)?(?:youtube\.com\/watch\?)((?:[\w\d\-\_\=]+&amp;(?:amp;)?)*v(?:&lt;[A-Z]+&gt;)?=([0-9a-zA-Z\-\_]+))/i)) {
				var video_id = matches[2];
				jQuery.getJSON('//gdata.youtube.com/feeds/api/videos/'+video_id+'?v=2&alt=jsonc', function(data, status, xhr) {
				    jQuery('<div class="w2dc_attached_item"><div class="w2dc_delete_attached_item delete_item" title="<?php _e('remove video', 'W2DC'); ?>"></div><input type="hidden" name="attached_video_id[]" value="' + video_id + '" /><div class="w2dc_img_div_border" style="width: 120px; height: 90px"><span class="w2dc_img_div_helper"></span><img src="' + data.data.thumbnail.sqDefault + '" style="max-width: 120px; max-height: 90px" /></div><input type="text" name="attached_video_title[]" value="" size="37" /></div>').appendTo("#videos_wrapper");
				    jQuery("input[name=attached_video_title\\[\\]]:last").val(data.data.title);

				    if (videos_number <= jQuery("#videos_wrapper .w2dc_attached_item").length)
						jQuery("#attach_videos_functions").hide();
				});
			} else
				alert("<?php _e('Wrong URL or this videos unavailable', 'W2DC'); ?>");
		}
	}

	jQuery(document).ready(function($) {
		$("#videos_wrapper .delete_item").live('click', function() {
			$(this).parent().remove();

			if (videos_number > $("#videos_wrapper .w2dc_attached_item").length)
				$("#attach_videos_functions").show();
		});
	});
</script>

<div id="videos_attach_wrapper">
	<h2>
		<?php _e('Listing videos', 'W2DC'); ?>
	</h2>
	
	<div id="videos_wrapper">
	<?php foreach ($listing->videos AS $video): ?>
		<div class="w2dc_attached_item">
			<div class="w2dc_delete_attached_item delete_item" title="<?php _e('remove video', 'W2DC'); ?>"></div>
			<input type="hidden" name="attached_video_id[]" value="<?php echo esc_attr($video['id']); ?>" />
			<div class="w2dc_img_div_border" style="width: 120px; height: 90px">
				<span class="w2dc_img_div_helper"></span><img src="http://i.ytimg.com/vi/<?php echo $video['id']; ?>/default.jpg" style="max-width: 120px; max-height: 90px" />
			</div>
			<input type="text" name="attached_video_title[]" value="<?php echo esc_attr($video['caption']); ?>" size="37" />
		</div>
	<?php endforeach; ?>
	</div>
	<div class="clear_float"></div>

	<div id="attach_videos_functions" <?php if (count($listing->videos) >= $listing->level->videos_number): ?>style="display: none;"<?php endif; ?>>
		<?php _e('Enter full YouTube video link', 'W2DC'); ?>
		<br />
		<input type="text" id="attach_video_input" size="50" />
		<br />
		<br />
		<input
			type="button"
			class="button button-primary"
			onclick="return attachYoutubeVideo(); "
			value="<?php _e('Attach video', 'W2DC'); ?>" />
	</div>
</div>
<?php endif; ?>