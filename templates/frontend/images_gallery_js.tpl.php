<script>
	jQuery(document).ready(function($) {
		$(".img_small").click(function() {
			if (!$("#listing_logo").hasClass("ajax_loading")) {
				var img_small = $(this).clone();

				// path to full size image
				var full_href = $(this).parent().find(".hidden_imgs").attr("href");

				// Set the size of big image and place ajax loader there
				var big_image_width = $("#listing_logo").find("img").width();
				var big_image_height = $("#listing_logo").find("img").height();
				// place ajax loader
				$("#listing_logo").html("");
				$("#listing_logo").addClass("ajax_loading");
				$("#listing_logo").css("width", big_image_width);
				$("#listing_logo").css("height", big_image_height);
				$(".lightbox_image").css("position", "relative");
				$(".lightbox_image").css("top", (big_image_height/2)-20);
				$(".lightbox_image").css("left", (big_image_width/2)-20);

				// Remove thmb of logo from lighbox images
				$(".hidden_divs a").addClass("hidden_imgs").attr("data-lightbox", "listing_images");
				$(this).parent().find(".hidden_divs a").removeClass("hidden_imgs").removeAttr("data-lightbox");

				// Load new image into big image container
				var img = new Image();
				$(img).load(function () {
					$(this).hide();
					img_small.html(this);
					img_small.attr("href", full_href);
					img_small.removeClass("img_small").attr("data-lightbox", "listing_images");
					$("#listing_logo").removeClass("ajax_loading");
					$("#listing_logo").html(img_small);
					$("#listing_logo").css("width", img_small.find("img").width());
					$("#listing_logo").css("height", img_small.find("img").height());
					$(this).fadeIn();
				}).attr("src", img_small.attr("href"));
			}
			return false;
		});
	});
</script>

<?php if ($listing->level->logo_size == 'thumbnail'): ?>
<?php $columns_num = 3; ?>
<?php elseif ($listing->level->logo_size == 'medium'): ?>
<?php $columns_num = 4; ?>
<?php elseif ($listing->level->logo_size == 'large'): ?>
<?php $columns_num = 5; ?>
<?php endif; ?>
<div class="listing_images_gallery">
	<table>
		<tr>
		<?php $i = 0; ?>
		<?php foreach ($listing->images AS $attachment_id=>$image): ?>
			<?php $src_small = wp_get_attachment_image_src($attachment_id, array(60, 60)); ?>
			<?php $src_thumbnail = wp_get_attachment_image_src($attachment_id, $listing->level->logo_size); ?>
			<?php $src_full = wp_get_attachment_image_src($attachment_id, 'full'); ?>
			<?php $i++; ?>
			<td align="center" valign="middle" class="small_image_bg">
				<a href="<?php echo $src_thumbnail[0]; ?>" class="img_small" title="<?php echo esc_attr($image['post_title']); ?>"><img src="<?php echo $src_small[0]; ?>" width="<?php echo $src_small[1]; ?>" height="<?php echo $src_small[2]; ?>" /></a>
				<div class="hidden_divs" style="display:none"><a href="<?php echo $src_full[0]; ?>" <?php if ($attachment_id != $listing->logo_image): ?>data-lightbox="listing_images" class="hidden_imgs"<?php endif; ?>></a></div>
			</td>
		<?php if ($i >= $columns_num): ?>
		</tr><tr>
		<?php $i = 0; ?>
		<?php endif; ?>
		<?php endforeach; ?>
		</tr>
	</table>
</div>