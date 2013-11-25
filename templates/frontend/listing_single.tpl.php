			<div class="entry-content">
			
			<?php w2dc_renderMessages(); ?>
			
			<?php if ($frontend_controller->listings): ?>
			<?php while ($frontend_controller->query->have_posts()): ?>
				<?php $frontend_controller->query->the_post(); ?>
				<?php if (get_the_title()): ?>
				<header class="entry-header">
					<h2><?php the_title(); ?></h2>

					<?php if ($frontend_controller->breadcrumbs): ?>
					<div class="breadcrumbs">
						<?php echo $frontend_controller->getBreadCrumbs(); ?>
					</div>
					<?php endif; ?>

					<div class="w2dc_edit_listing_link"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/page_edit.png" class="w2dc_field_icon" /><?php edit_post_link(__('Edit listing', 'W2DC')); ?></div>
				</header>
				<?php endif; ?>

				<article id="post-<?php the_ID(); ?>" class="">
					<?php $frontend_controller->listings[get_the_ID()]->display(); ?>
				</article>
			<?php endwhile; endif; ?>

			</div>