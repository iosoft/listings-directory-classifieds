<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php
	if ($field_id)
		_e('Edit content field', 'W2DC');
	else
		_e('Create new content field', 'W2DC');
	?>
</h2>
<?php if ($content_field->is_core_field): ?>
<p class="description"><?php _e('You can\'t select assigned categories for core fields such as content, excerpt, categories, tags and addresses', 'W2DC'); ?></p>
<?php endif; ?>

<script language="JavaScript" type="text/javascript">
	jQuery(document).ready(function($) {
		$("#content_field_name").keyup(function() {
			$("#content_field_slug").val(make_slug($("#content_field_name").val()));
		});

		<?php if (!$content_field->is_core_field): ?>
		$("#type").change(function() {
			if (
				<? 
				foreach ($content_fields->fields_types_names AS $content_field_type=>$content_field_name){
					$field_class_name = 'w2dc_content_field_' . $content_field_type;
					if (class_exists($field_class_name)) {
						$_content_field = new $field_class_name;
						if (!$_content_field->canBeOrdered()) {
				?>
				$(this).val() == '<?echo $content_field_type; ?>' ||
				<?
						}
					}
				} ?>
			'x'=='y')
				$("#is_ordered_block").hide();
			else
				$("#is_ordered_block").show();
		});
		<? endif; ?>

		var field_icon_image_url = '<?php echo W2DC_FIELDS_ICONS_URL; ?>';

		<?php if ($content_field->icon_image): ?>
		$("#icon_image_tag").attr('src', field_icon_image_url+$("#icon_image").val());
		$("#icon_image_tag").show();
		<? else: ?>
		$("#icon_image_tag").hide();
		<? endif; ?>

		$(".select_icon_image").live('click', function() {
			var dialog = $('<div id="select_field_icon_dialog"></div>').dialog({
				width: 650,
				height: 520,
				modal: true,
				resizable: false,
				draggable: false,
				title: '<?php _e('Select content field icon', 'W2DC'); ?>',
				open: function() {
					ajax_loader_show();
					$.ajax({
						type: "POST",
						url: js_objects.ajaxurl,
						data: {'action': 'select_field_icon'},
						dataType: 'html',
						success: function(response_from_the_action_function){
							if (response_from_the_action_function != 0) {
								$('#select_field_icon_dialog').html(response_from_the_action_function);
								if ($("#icon_image").val())
									$(".icon[icon_file='"+$("#icon_image").val()+"']").addClass("selected_icon");
							}
						},
						complete: function() {
							ajax_loader_hide();
						}
					});
					$('.ui-widget-overlay').live('click', function() { $('#select_map_icon_dialog').remove(); });
				},
				close: function() {
					$('#select_field_icon_dialog').remove();
				}
			});
		});
		$(".icon").live('click', function() {
			$(".selected_icon").removeClass("selected_icon");
			$("#icon_image").val($(this).attr('icon_file'));
			$("#icon_image_tag").attr('src', field_icon_image_url+$(this).attr('icon_file'));
			$("#icon_image_tag").show();
			$(this).addClass("selected_icon");
			$('#select_field_icon_dialog').remove();
		});
		$("#reset_icon").live('click', function() {
			$(".selected_icon").removeClass("selected_icon");
			$("#icon_image_tag").attr('src', '');
			$("#icon_image_tag").hide();
			$("#icon_image").val('');
			$('#select_field_icon_dialog').remove();
		});
	});
</script>

<form method="POST" action="">
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_content_fields_nonce');?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('Field name', 'W2DC'); ?><span class="red_asterisk">*</span></label>
				</th>
				<td>
					<input
						name="name"
						id="content_field_name"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($content_field->name); ?>" />
				</td>
			</tr>
			<?php if ($content_field->isSlug()) :?>
			<tr>
				<th scope="row">
					<label><?php _e('Field slug', 'W2DC'); ?><span class="red_asterisk">*</span></label>
				</th>
				<td>
					<input
						name="slug"
						id="content_field_slug"
						type="text"
						class="regular-text"
						value="<?php echo esc_attr($content_field->slug); ?>" />
				</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th scope="row">
					<label><?php _e('Hide name', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="is_hide_name"
						type="checkbox"
						value="1"
						<?php checked($content_field->is_hide_name); ?> />
					<p class="description"><?php _e("Hide field name at the frontend?", 'W2DC'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Field description', 'W2DC'); ?></label>
				</th>
				<td>
					<textarea
						name="description"
						cols="60"
						rows="4" ><?php echo esc_textarea($content_field->description); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Icon image', 'W2DC'); ?></label>
				</th>
				<td>
					<img id="icon_image_tag" src="" />
					<input type="hidden" name="icon_image" id="icon_image" value="<?php echo esc_attr($content_field->icon_image); ?>">
					<div>
						<a class="select_icon_image" href="javascript: void(0);"><?php _e('Select field icon', 'W2DC'); ?></a>
					</div>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label><?php _e('Field type', 'W2DC'); ?><span class="red_asterisk">*</span></label>
				</th>
				<td>
					<select name="type" id="type" <?php disabled($content_field->is_core_field); ?>>
						<option value=""><?php _e('- Select field type -', 'W2DC'); ?></option>
						<?php if ($content_field->is_core_field) :?>
						<option value="excerpt" <?php selected($content_field->type, 'excerpt'); ?> ><?php echo $fields_types_names['excerpt']; ?></option>
						<option value="content" <?php selected($content_field->type, 'content'); ?> ><?php echo $fields_types_names['content']; ?></option>
						<option value="categories" <?php selected($content_field->type, 'categories'); ?> ><?php echo $fields_types_names['categories']; ?></option>
						<option value="tags" <?php selected($content_field->type, 'tags'); ?> ><?php echo $fields_types_names['tags']; ?></option>
						<option value="address" <?php selected($content_field->type, 'address'); ?> ><?php echo $fields_types_names['address']; ?></option>
						<?php endif; ?>
						<option value="string" <?php selected($content_field->type, 'string'); ?> ><?php echo $fields_types_names['string']; ?></option>
						<option value="textarea" <?php selected($content_field->type, 'textarea'); ?> ><?php echo $fields_types_names['textarea']; ?></option>
						<option value="number" <?php selected($content_field->type, 'number'); ?> ><?php echo $fields_types_names['number']; ?></option>
						<option value="select" <?php selected($content_field->type, 'select'); ?> ><?php echo $fields_types_names['select']; ?></option>
						<option value="radio" <?php selected($content_field->type, 'radio'); ?> ><?php echo $fields_types_names['radio']; ?></option>
						<option value="checkbox" <?php selected($content_field->type, 'checkbox'); ?> ><?php echo $fields_types_names['checkbox']; ?></option>
						<option value="website" <?php selected($content_field->type, 'website'); ?> ><?php echo $fields_types_names['website']; ?></option>
						<option value="email" <?php selected($content_field->type, 'email'); ?> ><?php echo $fields_types_names['email']; ?></option>
						<option value="datetime" <?php selected($content_field->type, 'datetime'); ?> ><?php echo $fields_types_names['datetime']; ?></option>
						<option value="price" <?php selected($content_field->type, 'price'); ?> ><?php echo $fields_types_names['price']; ?></option>
					</select>
					<?php if ($content_field->is_core_field): ?>
					<p class="description"><?php _e('You can\'t change the type of core fields', 'W2DC'); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			
			<?php if ($content_field->canBeRequired()): ?>
			<tr>
				<th scope="row">
					<label><?php _e('Is this field required?', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="is_required"
						type="checkbox"
						value="1"
						<?php checked($content_field->is_required); ?> />
				</td>
			</tr>
			<?php endif; ?>
			<tr id="is_ordered_block" <?php if (!$content_field->canBeOrdered()): ?>style="display: none;"<?php endif; ?>>
				<th scope="row">
					<label><?php _e('Order by field', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="is_ordered"
						type="checkbox"
						value="1"
						<?php checked($content_field->is_ordered); ?> />
					<p class="description"><?php _e("Is it possible to order listings by this field?", 'W2DC'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('On excerpt page', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="on_exerpt_page"
						type="checkbox"
						value="1"
						<?php checked($content_field->on_exerpt_page); ?> />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('On listing page', 'W2DC'); ?></label>
				</th>
				<td>
					<input
						name="on_listing_page"
						type="checkbox"
						value="1"
						<?php checked($content_field->on_listing_page); ?> />
				</td>
			</tr>
			
			<?php apply_filters('w2dc_content_field_html', $content_field); ?>
			
			<?php if ($content_field->isCategories()): ?>
			<tr>
				<th scope="row">
					<label><?php _e('Assigned categories', 'W2DC'); ?></label>
				</th>
				<td>
					<?php w2dc_termsSelectList('categories_list', W2DC_CATEGORIES_TAX, $content_field->categories); ?>
				</td>
			</tr>
			<?php endif; ?>
			
		</tbody>
	</table>
	
	<?php
	if ($field_id)
		submit_button(__('Save changes', 'W2DC'));
	else
		submit_button(__('Create content field', 'W2DC'));
	?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>