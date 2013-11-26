<?php if (get_option('w2dc_show_what_search') || get_option('w2dc_show_where_search')): ?>
<script>
jQuery(document).ready(function($) {
	$("[placeholder]").focus(function() {
		var input = $(this);
		if (input.val() == input.attr("placeholder")) {
			input.val("''");
			input.removeClass("placeholder");
		}
	}).blur(function() {
		var input = $(this);
		if (input.val() == "''" || input.val() == input.attr("placeholder")) {
			input.addClass("placeholder");
			input.val(input.attr("placeholder"));
		}
	}).blur();
	$("[placeholder]").parents("form").submit(function() {
		$(this).find("[placeholder]").each(function() {
			var input = $(this);
			if (input.val() == input.attr("placeholder"))
				input.val("''");
			})
	});

	<?php if (get_option('w2dc_show_where_search')): ?>
	var cache = {};
	$("#where_search").autocomplete({
		minLength: 2,
		source: function(request, response) {
			var term = request.term;
			if (term in cache) {
				response(cache[term]);
				return;
			}
			$.ajax({
				type: "POST",
				url: js_objects.ajaxurl,
				data: {'action': 'w2dc_address_autocomplete', 'term': term},
				dataType: 'json',
				success: function(response_from_the_action_function){
					cache[term] = response_from_the_action_function;
					response(response_from_the_action_function);
				}
			});
		}
	});
	<?php endif; ?>
});
</script>

<div id="search_form">
	<form action="<?php echo $w2dc_instance->index_page_url; ?>">
		<input type="hidden" name="action" value="search" />

		<?php if (get_option('w2dc_show_what_search')): ?>
		<div class="search_label"><?php _e('What search', 'W2DC'); ?></div>
		<div class="search_section">
			<?php do_action('pre_search_what_form_html'); ?>
			<div class="search_option">
				<input type="text" name="what_search" size="38" placeholder="<?php _e('Enter keywords', 'W2DC'); ?>" value="<?php if (isset($_GET['what_search'])) echo esc_attr(stripslashes($_GET['what_search'])); ?>" />
			</div>
			<?php do_action('post_search_what_form_html'); ?>
			<div class="clear_float"></div>
		</div>
		<?php endif; ?>

		<?php if (get_option('w2dc_show_where_search')): ?>
		<div class="search_label"><?php _e('Where search', 'W2DC'); ?></div>
		<div class="search_section">
			<?php do_action('pre_search_where_form_html'); ?>
			<div class="search_option">
				<?php
				if (isset($_GET['search_location']) && is_numeric($_GET['search_location']))
					$term_id = $_GET['search_location'];
				else
					$term_id = 0; 
				w2dc_tax_dropdowns_init(W2DC_LOCATIONS_TAX, 'search_location', $term_id, true, array(), $w2dc_instance->locations_levels->getSelectionsArray()); ?>
			</div>
			<div class="search_option">
				<input type="text" name="where_search" id="where_search" size="38" placeholder="<?php _e('Enter address or zip code', 'W2DC'); ?>" value="<?php if (isset($_GET['where_search'])) echo esc_attr(stripslashes($_GET['where_search'])); ?>" />
			</div>
			<?php do_action('post_search_where_form_html'); ?>
			<div class="clear_float"></div>
		</div>
		<?php endif; ?>
		
		<?php do_action('post_search_form_html'); ?>

		<div class="search_option">
			<input type="submit" name="submit" value="<?php _e('Search', 'W2DC'); ?>" />
		</div>
		<div class="clear_float"></div>
	</form>
</div>
<?php endif; ?>