<?php

class google_maps {
	public $locations_array = array();
	public $locations_option_array = array();

	public function collectLocations($listing) {
		foreach ($listing->locations AS $location) {
			if ($location->map_coords_1 != '0.000000' || $location->map_coords_2 != '0.000000') {
				$this->locations_array[] = $location;
				$this->locations_option_array[] = array(
					$location->map_coords_1,
					$location->map_coords_2,
					$location->getSelectedLocationString(),
					$location->address_line_1,
					$location->address_line_2,
					$location->zip_or_postal_index,
					$location->map_icon_file,
					$listing->map_zoom,
					get_the_title(),
					$listing->logo_file,
					get_permalink(),
					$listing->post->ID,
				);
			}
		}
		if ($this->locations_option_array)
			return true;
		else
			return false;
	}
	
	public function display($show_directions = true) {
		if ($locations_options = json_encode($this->locations_option_array)) {
			//var_dump($locations_options);
			w2dc_renderTemplate('google_map.tpl.php', array('locations_options' => $locations_options, 'locations_array' => $this->locations_array, 'show_directions' => $show_directions, 'unique_map_id' => time()));
		}
	}
}
?>