			<?php w2dc_renderMessages(); ?>

			<?php if ($frontend_controller->listings): ?>
			<?php while ($frontend_controller->query->have_posts()): ?>
				<?php $frontend_controller->query->the_post(); ?>

				<div class="w2dc_directory_frontpanel">
					<?php do_action('w2dc_directory_frontpanel'); ?>
					<?php if (get_option('w2dc_favourites_list')): ?>
					<input type="button" class="w2dc_favourites_link" value="<?php _e('My favourites', 'W2DC'); ?>" onClick="window.location='<?php echo add_query_arg('action', 'myfavourites', $w2dc_instance->index_page_url); ?>';" />
					<?php endif; ?>
					<?php if (current_user_can('edit_posts', get_the_ID())): ?>
					<input type="button" class="w2dc_edit_listing_link" value="<?php _e('Edit listing', 'W2DC'); ?>" onClick="window.location='<?php echo get_edit_post_link(); ?>';" />
					<?php endif; ?>
					<?php if (get_option('w2dc_print_button')): ?>
					<script>
						var window_width = 860;
						var window_height = 800;
						var leftPosition, topPosition;
					   	leftPosition = (window.screen.width / 2) - ((window_width / 2) + 10);
					   	topPosition = (window.screen.height / 2) - ((window_height / 2) + 50);
					</script>
					<input type="button" class="w2dc_print_listing_link" value="<?php _e('Print listing', 'W2DC'); ?>" onClick="window.open('<?php echo add_query_arg('action', 'printlisting', get_permalink()); ?>', 'print_window', 'height='+window_height+',width='+window_width+',left='+leftPosition+',top='+topPosition+',menubar=yes,scrollbars=yes');" />
					<?php endif; ?>
					<?php if (get_option('w2dc_favourites_list')): ?>
					<input type="button" class="w2dc_save_listing_link add_to_favourites <?php if (checkQuickList(get_the_ID())) 'in_favourites_list'; else 'not_in_favourites_list'; ?>" value="<?php if (checkQuickList(get_the_ID())) _e('Out of favourites list', 'W2DC'); else _e('Put in favourites list', 'W2DC'); ?>" listingid="<?php the_ID(); ?>" />
					<?php endif; ?>
					<?php if (get_option('w2dc_pdf_button')): ?>
					<input type="button" class="w2dc_pdf_listing_link" value="<?php _e('Save listing in PDF', 'W2DC'); ?>" onClick="window.open('http://pdfmyurl.com/?url=<?php echo urlencode(get_permalink()); ?>');" />
					<?php endif; ?>
				</div>
	
				<div class="entry-content">
					<?php if (get_the_title()): ?>
					<header class="entry-header">
						<h2><?php the_title(); ?></h2>
	
						<?php if ($frontend_controller->breadcrumbs): ?>
						<div class="breadcrumbs">
							<?php echo $frontend_controller->getBreadCrumbs(); ?>
						</div>
						<?php endif; ?>
					</header>
					<?php endif; ?>
	
					<article id="post-<?php the_ID(); ?>" class="">
						<?php $frontend_controller->listings[get_the_ID()]->display(); ?>
					</article>
				</div>
			<?php endwhile; endif; ?>