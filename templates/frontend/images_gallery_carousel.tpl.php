<?php $slider_height = get_option('thumbnail_size_h')+30; ?>

<script language="JavaScript" type="text/javascript">
jQuery(document).ready(function($) {
	$('#images_slider').anythingSlider({
		resizeContents: true,
		theme: 'minimalist-round',
		buildNavigation: false,
		buildStartStop: false,
		showMultiple: 3,
		changeBy: 1,
		infiniteSlides: false,
		expand: true
	});
	$('#images_slider').show();
});
</script>

<div style="height: <? echo $slider_height; ?>px; padding-bottom: 20px">
	<ul id="images_slider" style="display: none;">
	<?php foreach ($listing->images AS $attachment_id=>$image): ?>
		<?php $src_thumbnail = wp_get_attachment_image_src($attachment_id,'thumbnail'); ?>
		<?php $src_full = wp_get_attachment_image_src($attachment_id, 'full'); ?>
		<li>
			<div style="margin: 10px">
				<a href="<?php echo $src_full[0]; ?>" data-lightbox="listing_images" title="<?php echo esc_attr($image['post_title']); ?>"><img src="<?php echo $src_thumbnail[0]; ?>"/></a>
			</div>
		</li>
	<?php endforeach; ?>
	</ul>
</div>
