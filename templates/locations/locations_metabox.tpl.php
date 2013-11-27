<script>
	var global_map_icons_path = '<?php echo $w2dc_instance->map_markers_url; ?>';

	jQuery(document).ready(function($) {
        $(".manual_coords").live('click', function() {
        	if ($(this).is(":checked"))
        		$(this).parent().find(".manual_coords_block").show(200);
        	else
        		$(this).parent().find(".manual_coords_block").hide(200);
        });
	});
</script>

<div class="locations_metabox">
	<div id="locations_wrapper">
		<?php
		if ($listing->locations)
			w2dc_renderTemplate('locations/locations_in_metabox.tpl.php', array('listing' => $listing, 'location' => $listing->locations[0], 'locations_levels' => $locations_levels));
		else
			w2dc_renderTemplate('locations/locations_in_metabox.tpl.php', array('listing' => $listing, 'location' => new w2dc_location, 'locations_levels' => $locations_levels));
		?>
	</div>

	<?php if ($listing->level->google_map): ?>
	<br />
	<br />
	<input type="hidden" name="map_zoom" class="map_zoom" value="<?php echo $listing->map_zoom; ?>" />
	<input type="button" class="button button-primary" onClick="generateMap(); return false;" value="<?php _e('Generate on google map', 'W2DC'); ?>" />
	<br />
	<br />
	<div class="maps_canvas" id="maps_canvas" style="width: auto; height: 350px;"></div>
	<?php endif;?>
</div>