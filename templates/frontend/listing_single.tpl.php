<?php
global $w2dc_instance;
$frontend_controller = $w2dc_instance->frontend_controller; 
?>

<?php get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
			<div class="entry-content">
			
			<?php w2dc_renderMessages(); ?>
			
			<?php if ($frontend_controller->listings): ?>
			<?php while ($frontend_controller->query->have_posts()): ?>
				<?php if ($frontend_controller->getPageTitle()): ?>
				<header class="entry-header">
					<?php if ($frontend_controller->breadcrumbs): ?>
					<div class="breadcrumbs">
						<?php echo $frontend_controller->getBreadCrumbs(); ?>
					</div>
					<?php endif; ?>

					<h1 class="entry-title"><?php echo $frontend_controller->getPageTitle(); ?></h1>
					<div class="w2dc_edit_listing_link"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/page_edit.png" class="w2dc_field_icon" /><?php edit_post_link(__('Edit listing', 'W2DC')); ?></div>
				</heder>
				<?php endif; ?>

				<?php $frontend_controller->query->the_post(); ?>
				<article id="post-<?php the_ID(); ?>" class="">
					<?php $frontend_controller->listings[get_the_ID()]->display(); ?>
				</article>
			<?php endwhile; endif; ?>

			</div>
		</div>
	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>