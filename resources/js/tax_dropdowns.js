jQuery(document).ready(function() {
	jQuery(document).on('change', '.tax_dropdowns_wrap select', function() {
		var select_box = jQuery(this).attr('id').split('_');
		var parent = jQuery(this).val();
		var current_level = select_box[1];
		var uID = select_box[2];

		var divclass = jQuery(this).parent('div').parent('div').attr('class').split(' ');
		var tax = divclass[0];
		var count = divclass[1];

		update_tax(parent, tax, current_level, count, uID);
	});

	function update_tax(parent, tax, current_level, count, uID){
		var current_level = parseInt(current_level);
		var next_level = current_level + 1;
		var prev_level = current_level - 1;
		var selects_length = jQuery('#tax_dropdowns_wrap_'+uID+' select').length;
		
		if (parent)
			jQuery('#selected_tax\\['+uID+'\\]').val(parent).trigger('change');
		else if (current_level > 1)
			jQuery('#selected_tax\\['+uID+'\\]').val(jQuery('#chainlist_'+prev_level+'_'+uID).val()).trigger('change');
		else
			jQuery('#selected_tax\\['+uID+'\\]').val(0).trigger('change');

		for (var i=next_level; i<=selects_length; i++)
			jQuery('#wrap_chainlist_'+i+'_'+uID).remove();
		
		if (parent) {
			// avoid errors after ajax dropdowns addition
			if (w2dc_tax_dropdowns_script[uID] == undefined)
				var labels_source = first(w2dc_tax_dropdowns_script);
			else
				var labels_source = w2dc_tax_dropdowns_script[uID];

			if (labels_source.labels[current_level] != undefined)
				var label = labels_source.labels[current_level];
			else
				var label = '';
			if (labels_source.titles[current_level] != undefined)
				var title = labels_source.titles[current_level];
			else
				var title = '';

			jQuery('#chainlist_'+current_level+'_'+uID).addClass('ajax_loading');
			jQuery.post(
				js_objects.ajaxurl,
				{'action': 'tax_dropdowns_hook', 'parentid': parent, 'next_level': next_level, 'tax': tax, 'count': count, 'label': label, 'title': title, 'uID': uID},
				function(response_from_the_action_function){
					if (response_from_the_action_function != 0)
						jQuery('#tax_dropdowns_wrap_'+uID).append(response_from_the_action_function);

					jQuery('#chainlist_'+current_level+'_'+uID).removeClass('ajax_loading');
				}
			);
		}
	}
	
	function first(p){for(var i in p)return p[i];}
});

