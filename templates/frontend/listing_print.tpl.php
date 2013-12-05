<?php
global $w2dc_instance;
$frontend_controller = new w2dc_frontend_controller(); 
$w2dc_instance->frontend_controller = $frontend_controller;

?>

<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<?php wp_head(); ?>
	
	<style type="text/css">
	.w2dc_print_buttons {
		margin: 10px;
	}
	@media print {
		.w2dc_print_buttons {
			display: none;
		}
	}
	</style>
</head>

<body <?php body_class(); ?>>
	<div id="page" class="hfeed site">
		<div id="main" class="wrapper">
			<div class="entry-content">
				<div class="w2dc_print_buttons">
					<input type="button" onclick="window.print();" value="<?php _e('Print listing', 'W2DC'); ?>">&nbsp;&nbsp;&nbsp;<input type="button" onclick="window.close();" value="<?php _e('Close window', 'W2DC'); ?>">
				</div>

			<?php while ($frontend_controller->query->have_posts()): ?>
				<?php $frontend_controller->query->the_post(); ?>
				<?php $listing = $frontend_controller->listings[get_the_ID()]; ?>
				<?php if (get_the_title()): ?>
				<header class="entry-header">
					<h2><?php the_title(); ?></h2>
				</header>
				<?php endif;?>

				<?php if ($listing->logo_image): ?>
				<div class="listing_logo_wrap">
					<div id="listing_logo">
						<?php $src = wp_get_attachment_image_src($listing->logo_image, $listing->level->logo_size); ?>
						<img src="<?php echo $src[0]; ?>" />
					</div>
					<div class="clear_float"></div>
				</div>
				<?php endif; ?>

				<div class="listing_text_content_wrap entry-content">
					<em class="w2dc_listing_date"><?php echo get_the_date(); ?> <?php echo get_the_time(); ?></em>

					<?php $listing->renderContentFields(true); ?>
				</div>
				<div class="clear_float"></div>

				<?php if ($listing->level->google_map && $listing->isMap() && $listing->locations): ?>
				<h2><?php _e('Map', 'W2DC'); ?></h2>
				<?php $listing->renderMap(false); ?>
				<?php endif; ?>
				
				<?php if (count($listing->images) > 1): ?>
				<h2><?php _e('Images', 'W2DC'); ?> (<?php echo count($listing->images); ?>)</h2>
				<?php foreach ($listing->images AS $attachment_id=>$image): ?>
					<?php $src_thumbnail = wp_get_attachment_image_src($attachment_id,'large'); ?>
					<div style="margin: 10px">
						<img src="<?php echo $src_thumbnail[0]; ?>"/>
					</div>
				<?php endforeach; ?>
				<?php endif; ?>

				<?php if (get_comments_number()): ?>
				<h2 class="comments-title">
					<?php
						printf(_n('One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'W2DC'),
							number_format_i18n(get_comments_number()), '<span>' . get_the_title() . '</span>');
					?>
				</h2>
				<ol class="commentlist">
				<?php wp_list_comments(array('reply_text' => '', 'login_text' => '', 'style' => 'ol'), get_comments(array('post_id' => $listing->post->ID))); ?>
				</ol>
				<?php endif; ?>
			<?php endwhile; ?>

				<div class="w2dc_print_buttons">
					<input type="button" onclick="window.print();" value="<?php _e('Print listing', 'W2DC'); ?>">&nbsp;&nbsp;&nbsp;<input type="button" onclick="window.close();" value="<?php _e('Close window', 'W2DC'); ?>">
				</div>
			</div>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>