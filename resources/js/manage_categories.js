jQuery(document).ready(function($) {
	$("input[name=tax_input\\[w2dc-category\\]\\[\\]]").change(function() {manageCategories($(this))});
	$("#w2dc-category-pop input[type=checkbox]").change(function() {manageCategories($(this))});
	
	function manageCategories(checked_object) {
		if (checked_object.is(":checked") && level_categories.level_categories_number != 'unlimited') {
			if ($("input[name=tax_input\\[w2dc-category\\]\\[\\]]:checked").length > level_categories.level_categories_number) {
				alert(level_categories.level_categories_notice_number);
				$("#in-w2dc-category-"+checked_object.val()).attr("checked", false);
				$("#in-popular-w2dc-category-"+checked_object.val()).attr("checked", false);
			}
		}

		if (checked_object.is(":checked") && level_categories.level_categories_array.length > 0) {
			var result = false;
			$.each(level_categories.level_categories_array, function(index, value) {
				if (value == checked_object.val())
					result = true;
			});
			if (!result) {
				alert(level_categories.level_categories_notice_disallowed);
				$("#in-w2dc-category-"+checked_object.val()).attr("checked", false);
				$("#in-popular-w2dc-category-"+checked_object.val()).attr("checked", false);
			} else
				return true;
		} else
			return true;
	}
});