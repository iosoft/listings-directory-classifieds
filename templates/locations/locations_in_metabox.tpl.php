		<div class="location_in_metabox">
	
			<?php
				$uID = rand(1, 10000);
				w2dc_tax_dropdowns_init(
					W2DC_LOCATIONS_TAX,
					null,
					$location->selected_location,
					false,
					$locations_levels->getNamesArray(),
					$locations_levels->getSelectionsArray(),
					$uID
				);
			?>

			<label>
				<?php _e('Address line 1', 'W2DC'); ?>
			</label>
			<input type="text" name="address_line_1[<?php echo $uID;?>]" class="address_line_1" value="<?php echo $location->address_line_1; ?>" size="30" />
			
			<br />

			<label>
				<?php _e('Address line 2', 'W2DC'); ?>
			</label>
			<input type="text" name="address_line_2[<?php echo $uID;?>]" class="address_line_2" value="<?php echo $location->address_line_2; ?>" size="30" />
			
			<br />

			<label>
				<?php _e('Zip or postal index', 'W2DC'); ?>
			</label>
			<input type="text" name="zip_or_postal_index[<?php echo $uID;?>]" class="zip_or_postal_index" value="<?php echo $location->zip_or_postal_index; ?>" size="8" />
			
			<br />

			<?php if ($listing->level->google_map): ?>
			<!-- manual_coords - required in google_maps.js -->
			<img src="<?php echo W2DC_RESOURCES_URL; ?>images/map_edit.png" /> <input type="checkbox" name="manual_coords[<?php echo $uID;?>]" value="1" class="manual_coords" <?php if ($location->manual_coords) echo 'checked'; ?> /> <?php _e('Enter coordinates manually', 'W2DC'); ?>
			
			<br />

			<!-- manual_coords_block - position required for jquery selector -->
			<div class="manual_coords_block" <?php if (!$location->manual_coords) echo 'style="display: none;"'; ?>>
				<label>
					<?php _e('Latitude', 'W2DC'); ?>
				</label>
				<!-- map_coords_1 - required in google_maps.js -->
				<input type="text" name="map_coords_1[<?php echo $uID;?>]" class="map_coords_1" value="<?php echo $location->map_coords_1; ?>">
				
				<br />

				<label>
					<?php _e('Longitude', 'W2DC'); ?>
				</label>
				<!-- map_coords_2 - required in google_maps.js -->
				<input type="text" name="map_coords_2[<?php echo $uID;?>]" class="map_coords_2" value="<?php echo $location->map_coords_2; ?>">
			</div>
			<?php endif; ?>
		</div>