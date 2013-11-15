<?php if ($locations_options != '[]'): ?>
<script>
	map_markers_attrs_array.push(new map_markers_attrs(<?php echo $unique_map_id; ?>, eval(<?php echo $locations_options; ?>)));
	var global_map_icons_path = '<?php echo $w2dc_instance->map_markers_url; ?>';
	var view_listing_label = '<?php _e('View listing', 'W2DC'); ?>';
	var view_summary_label = '<?php _e('View listing summary', 'W2DC'); ?>';
</script>

<div id="maps_canvas_<?php echo $unique_map_id; ?>" class="maps_canvas" style="width: auto; height: 300px"></div>
<?php if ($show_directions): ?>
<div id="maps_direction_from">
	<?php _e('Get direction from:', 'W2DC'); ?> <input type="text" size="60" id="from_direction_<?php echo $unique_map_id; ?>" />
</div>
<div id="maps_direction_to">
	<?php _e('direction to:', 'W2DC'); ?><br />
	<?php $i = 1; ?>
	<?php foreach ($locations_array AS $location): ?>
	<input type="radio" name="select_direction" class="select_direction_<?php echo $unique_map_id; ?>" <?php if (count($locations_array) == 1) echo "style='display:none'"; ?> <?php checked($i, 1); ?> value="<?php echo $location->getWholeAddress(); ?>" /> <b><?php echo $location->getWholeAddress(); ?></b><br />
	<?php endforeach; ?>
</div>

<input type="button" class="direction_button front-btn" id="get_direction_button_<?php echo $unique_map_id; ?>" value="<?php _e('Get direction', 'W2DC'); ?>">

<div id="route_<?php echo $unique_map_id; ?>"></div>
<?php endif; ?>
<?php endif; ?>