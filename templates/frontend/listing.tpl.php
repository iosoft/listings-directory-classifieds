<?php $is_single = $w2dc_instance->frontend_controller->is_single; ?>

			<?php if (!$is_single): ?>
			<header class="entry-header">
				<h1 class="entry-title">
					<?php if (!get_option('w2dc_listings_own_page')): ?>
					<?php the_title(); ?>
					<?php else: ?>
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
					<?php endif; ?>
	
					<?php if ($listing->level->sticky): ?>
					<div class="w2dc_sticky_icon" title="<?php _e('sticky listing', 'W2DC'); ?>"></div>
					<div class="clear_float"></div>
					<?php endif; ?>
				</h1>
			</header>
			<?php endif; ?>

			<?php if ($listing->logo_image): ?>
			<div class="listing_logo_wrap">
				<div id="listing_logo">
					<?php $src = wp_get_attachment_image_src($listing->logo_image, $listing->level->logo_size); ?>
					<?php $src_full = wp_get_attachment_image_src($listing->logo_image, 'full'); ?>
					<?php if ($is_single && get_option('w2dc_images_on_tab')): ?>
					<img src="<?php echo $src[0]; ?>" itemprop="image" />
					<?php elseif ($is_single): ?>
					<a href="<?php echo $src_full[0]; ?>" data-lightbox="listing_images">
						<img src="<?php echo $src[0]; ?>" itemprop="image" />
					</a>
					<?php else: ?>
					<a href="<?php the_permalink(); ?>">
						<img src="<?php echo $src[0]; ?>" itemprop="image" />
					</a>
					<?php endif; ?>
				</div>
				<div class="clear_float"></div>
				<?php if ($is_single && !get_option('w2dc_images_on_tab') && count($listing->images) > 1): ?>
				<?php w2dc_renderTemplate('frontend/images_gallery_js.tpl.php', array('listing' => $listing)); ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="listing_text_content_wrap entry-content">
				<?php if (!$is_single && comments_open()): ?>
				<div>
					<img src="<?php echo W2DC_RESOURCES_URL; ?>images/comments.png" class="w2dc_field_icon" />
					<?php echo sprintf(_n('%d reply', '%d replies', $listing->post->comment_count, 'W2DC'), $listing->post->comment_count);?>
				</div>
				<?php endif; ?>
				
				<em class="w2dc_listing_date"><?php echo get_the_date(); ?> <?php echo get_the_time(); ?></em>
			
				<?php $listing->renderContentFields($is_single); ?>
			</div>
			<div class="clear_float"></div>

			<?php if ($is_single): ?>
			<script>
				jQuery(document).ready(function($) {
					var $tabs = $("#tabs").tabs({
						activate: function(event, ui){
							if ($(ui.newPanel).attr('id') == 'addresses-tab')
								for (var key in maps)
									if (typeof maps[key] != 'undefined') {
										var zoom = maps[key].getZoom();
										var center = maps[key].getCenter();
										google.maps.event.trigger(maps[key], 'resize');
										maps[key].setZoom(zoom);
										maps[key].setCenter(center);
									}
						}
					});
		
					var hash = window.location.hash.substring(1);
					if (hash == 'respond' || hash.indexOf('comment-', 0) >= 0)
						$("#tabs").tabs("option", "active", $("#tabs>div").index($("#comments-tab")));
					if (hash == 'contact')
						$("#tabs").tabs("option", "active", $("#tabs>div").index($("#contact-tab")));
				});
			</script>

			<?php if (
					   ($listing->level->google_map && $listing->isMap() && $listing->locations)
					|| (comments_open())
					|| ($listing->level->images_number && count($listing->images) > 1 && get_option('w2dc_images_on_tab'))
					|| ($listing->level->videos_number && $listing->videos)
					|| (get_option('w2dc_listing_contact_form'))
					): ?>
			<div id="tabs">
				<ul>
					<?php if ($listing->level->google_map && $listing->isMap() && $listing->locations): ?>
					<li><a href="#addresses-tab"><?php _e('Map', 'W2DC'); ?></a></li>
					<?php endif; ?>
					<?php if (comments_open()): ?>
					<li><a href="#comments-tab"><?php _e('Comments', 'W2DC'); ?> (<?php echo $listing->post->comment_count; ?>)</a></li>
					<?php endif; ?>
					<?php if ($listing->level->images_number && count($listing->images) > 1 && get_option('w2dc_images_on_tab')): ?>
					<li><a href="#images-tab"><?php _e('Images', 'W2DC'); ?> (<?php echo count($listing->images); ?>)</a></li>
					<?php endif; ?>
					<?php if ($listing->level->videos_number && $listing->videos): ?>
					<li><a href="#videos-tab"><?php _e('Videos','W2DC'); ?> (<?php echo count($listing->videos); ?>)</a></li>
					<?php endif; ?>
					<?php if (get_option('w2dc_listing_contact_form')): ?>
					<li><a href="#contact-tab"><?php _e('Contact', 'W2DC'); ?></a></li>
					<?php endif; ?>
				</ul>

				<?php if ($listing->level->google_map && $listing->isMap() && $listing->locations): ?>
				<div id="addresses-tab">
					<?php $listing->renderMap(get_option('w2dc_show_directions')); ?>
				</div>
				<?php endif; ?>

				<?php if (comments_open()): ?>
				<div id="comments-tab">
					<?php comments_template('', true); ?>
				</div>
				<?php endif; ?>

				<?php if ($listing->level->images_number && count($listing->images) > 1 && get_option('w2dc_images_on_tab')): ?>
				<div id="images-tab">
					<?php w2dc_renderTemplate('frontend/images_gallery_carousel.tpl.php', array('listing' => $listing)); ?>
				</div>
				<?php endif; ?>

				<?php if ($listing->level->videos_number && $listing->videos): ?>
				<div id="videos-tab">
				<?php foreach ($listing->videos AS $video): ?>
					<span><?php echo $video['caption']; ?></span>
					<object width="100%" height="400" data="http://www.youtube.com/v/<?php echo $video['id']; ?>" type="application/x-shockwave-flash"><param name="src" value="http://www.youtube.com/v/<?php echo $video['id']; ?>" /></object>
				<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php if (get_option('w2dc_listing_contact_form')): ?>
				<div id="contact-tab">
					<?php w2dc_renderTemplate('frontend/contact_form.tpl.php', array('listing' => $listing, 'current_user' => wp_get_current_user())); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			<?php endif; ?>