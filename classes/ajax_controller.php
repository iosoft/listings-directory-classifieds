<?php 

class w2dc_ajax_controller {

	public function __construct() {
		global $w2dc_instance;

		add_action('wp_ajax_w2dc_address_autocomplete', array($this, 'address_autocomplete'));
		add_action('wp_ajax_nopriv_w2dc_address_autocomplete', array($this, 'address_autocomplete'));
	}

	public function address_autocomplete() {
		if (isset($_POST['term']) && $_POST['term']) {
			$term = trim($_POST['term']);
			
			global $wpdb;
			$output = array();
			$results = $wpdb->get_results($wpdb->prepare("SELECT address_line_1 FROM wp_w2dc_locations_relationships WHERE address_line_1 LIKE '%%%s%%'", $term), ARRAY_A);
			foreach ($results AS $row) {
				$address = $row['address_line_1']; 
				$output[] = array('id' => $address, 'label' => $address, 'value' => $address);
			}
			$results = $wpdb->get_results($wpdb->prepare("SELECT address_line_2 FROM wp_w2dc_locations_relationships WHERE address_line_2 LIKE '%%%s%%'", $term), ARRAY_A);
			foreach ($results AS $row) {
				$address = $row['address_line_2']; 
				$output[] = array('id' => $address, 'label' => $address, 'value' => $address);
			}
			$results = $wpdb->get_results($wpdb->prepare("SELECT zip_or_postal_index FROM wp_w2dc_locations_relationships WHERE zip_or_postal_index LIKE '%%%s%%'", $term), ARRAY_A);
			foreach ($results AS $row) {
				$address = $row['zip_or_postal_index']; 
				$output[] = array('id' => $address, 'label' => $address, 'value' => $address);
			}
			echo json_encode($output);
		}
		die();
	}
}
?>