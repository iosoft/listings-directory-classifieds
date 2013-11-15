<?php 

class w2dc_location {
	public $post_id;
	public $selected_location = 0;
	public $address_line_1;
	public $address_line_2;
	public $zip_or_postal_index;
	public $manual_coords = false;
	public $map_coords_1;
	public $map_coords_2;
	public $map_icon_file;
	
	public function __construct($post_id = null) {
		$this->post_id = $post_id;
	}

	public function createLocationFromArray($array) {
		$this->selected_location = w2dc_getValue($array, 'selected_location');
		$this->address_line_1 = w2dc_getValue($array, 'address_line_1');
		$this->address_line_2 = w2dc_getValue($array, 'address_line_2');
		$this->zip_or_postal_index = w2dc_getValue($array, 'zip_or_postal_index');
		$this->manual_coords = w2dc_getValue($array, 'manual_coords');
		$this->map_coords_1 = w2dc_getValue($array, 'map_coords_1');
		$this->map_coords_2 = w2dc_getValue($array, 'map_coords_2');
		$this->map_icon_file = w2dc_getValue($array, 'map_icon_file');
	}
	
	public function getSelectedLocationString($glue = ', ', $reverse = false) {
		global $w2dc_instance;

		if ($this->selected_location != 0) {
			$chain = array();
			$parent_id = $this->selected_location;
			while ($parent_id != 0) {
				$term = get_term($parent_id, W2DC_LOCATIONS_TAX);
				$chain[] = $term->name;
				$parent_id = $term->parent;
			}

			// reverse locations for actual locations levels 
			$chain = array_reverse($chain);

			$locations_levels = $w2dc_instance->locations_levels;
			$locations_levels_array = array_values($locations_levels->levels_array);
			$result_chain = array();
			foreach ($chain AS $location_key=>$location) {
				if ($locations_levels_array[$location_key]->in_address_line)
					$result_chain[] = $location;
			}

			// do not reverse as it was reversed before
			if (!$reverse)
				$result_chain = array_reverse($result_chain);
			return implode($glue, $result_chain);
		}
	}
	
	public function compileAddress()
	{
		$address = '';
		if ($this->address_line_1)
			$address .= $this->address_line_1;
		if ($this->address_line_2)
			$address .= ", " . $this->address_line_2;
		if ($this->zip_or_postal_index)
			$address .= " " . $this->zip_or_postal_index;
		return $address;
	}
	
	public function getWholeAddress($glue = ', ', $reverse = false) {
		return $this->compileAddress() . ' ' . $this->getSelectedLocationString($glue, $reverse);
	}
}

?>