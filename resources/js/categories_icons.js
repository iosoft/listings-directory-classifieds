	jQuery(document).ready(function($) {
		var category_icon_image_url = categories_icons.categories_icons_url;

		$(".select_icon_image").live('click', function() {
			category_icon_image_input = $(this).parent().find('.icon_image');

			var dialog = $('<div id="select_field_icon_dialog"></div>').dialog({
				width: 650,
				height: 520,
				modal: true,
				resizable: false,
				draggable: false,
				title: categories_icons.ajax_dialog_title,
				open: function() {
					ajax_loader_show();
					$.ajax({
						type: "POST",
						url: js_objects.ajaxurl,
						data: {'action': 'select_category_icon_dialog'},
						dataType: 'html',
						success: function(response_from_the_action_function){
							if (response_from_the_action_function != 0) {
								$('#select_field_icon_dialog').html(response_from_the_action_function);
								if (category_icon_image_input.val())
									$(".icon[icon_file='"+category_icon_image_input.val()+"']").addClass("selected_icon");
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
			var icon_file = $(this).attr('icon_file');
			ajax_loader_show();
			$.ajax({
				type: "POST",
				url: js_objects.ajaxurl,
				data: {'action': 'select_category_icon', 'icon_file': icon_file, 'category_id': category_icon_image_input.parent().find(".category_id").val()},
				dataType: 'html',
				success: function(response_from_the_action_function){
					if (response_from_the_action_function != 0) {
						if (category_icon_image_input) {
							category_icon_image_input.val(icon_file);
							category_icon_image_input.parent().find(".icon_image_tag").attr('src', category_icon_image_url+icon_file).show();
							category_icon_image_input = false;
						}
					}
				},
				complete: function() {
					$(this).addClass("selected_icon");
					$('#select_field_icon_dialog').remove();
					ajax_loader_hide();
				}
			});
		});
		$("#reset_icon").live('click', function() {
			$(".selected_icon").removeClass("selected_icon");
			ajax_loader_show();
			$.ajax({
				type: "POST",
				url: js_objects.ajaxurl,
				data: {'action': 'select_category_icon', 'category_id': category_icon_image_input.parent().find(".category_id").val()},
				dataType: 'html',
				success: function(response_from_the_action_function){
					if (category_icon_image_input) {
						category_icon_image_input.val('');
						category_icon_image_input.parent().find(".icon_image_tag").attr('src', '').hide();
						category_icon_image_input = false;
					}
				},
				complete: function() {
					$('#select_field_icon_dialog').remove();
					ajax_loader_hide();
				}
			});
		});
	});