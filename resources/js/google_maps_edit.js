	var map = null;
    var geocoder = null;
    var infoWindow = null;
    var markersArray = [];
    var address = '';
    var glocation = (function(index, point, location, address_line_1, address_line_2, zip_or_postal_index, map_icon_file) {
    	this.index = index;
    	this.point = point;
    	this.location = location;
    	this.address_line_1 = address_line_1;
    	this.address_line_2 = address_line_2;
    	this.zip_or_postal_index = zip_or_postal_index;
    	this.map_icon_file = map_icon_file;
    	this.placeMarker = function() {
    		return placeMarker(this);
    	};
    	this.compileAddress = function() {
    		address = this.address_line_1;
    		if (this.address_line_2)
    			address += ", "+this.address_line_2;
    		if (this.location) {
    			if (this.address_line_1 || this.address_line_2)
    				address += " ";
    			address += this.location;
    		}
    		if (this.zip_or_postal_index)
    			address += " "+this.zip_or_postal_index;
    		return address;
    	};
    	this.compileHtmlAddress = function() {
    		address = this.address_line_1;
    		if (this.address_line_2)
    			address += ", "+this.address_line_2;
    		if (this.location) {
    			if (this.address_line_1 || this.address_line_2)
    				address += "<br />";
    			address += this.location;
    		}
    		if (this.zip_or_postal_index)
    			address += " "+this.zip_or_postal_index;
    		return address;
    	};
    	this.setPoint = function(point) {
    		this.point = point;
    	};
    });

    jQuery(document).ready(function() 
	{
		if (document.getElementById("maps_canvas")) {
		    var mapOptions = {
				zoom: 1,
				scrollwheel: false,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
		    map = new google.maps.Map(document.getElementById("maps_canvas"), mapOptions);

		    geocoder = new google.maps.Geocoder();
			    
		    var coords_array_1 = new Array();
   			var coords_array_2 = new Array();

		    if (jQuery(".map_coords_1[value!=''][value!='0.000000'][value!='0']").length != 0 || jQuery(".map_coords_2[value!=''][value!='0.000000'][value!='0']").length != 0) {
		    	generateMap();
		    } else {
		    	map.setCenter(new google.maps.LatLng(34, 0));
		    }

			google.maps.event.addListener(map, 'zoom_changed', function() {
				jQuery(".map_zoom").val(map.getZoom());
			});
		}
	});
	
	function setMapCenter(coords_array_1, coords_array_2) {
		var count = 0;
		var bounds = new google.maps.LatLngBounds();
		for (count == 0; count<coords_array_1.length; count++)  {
			bounds.extend(new google.maps.LatLng(coords_array_1[count], coords_array_2[count]));
		}
		if (count == 1) {
			if (jQuery(".map_zoom").val() == '' || jQuery(".map_zoom").val() == 0)
				var zoom_level = 1;
			else
				var zoom_level = parseInt(jQuery(".map_zoom").val());
		} else {
			map.fitBounds(bounds);
			var zoom_level = map.getZoom();
		}
		map.setCenter(bounds.getCenter());
		map.setZoom(zoom_level);

		ajax_loader_hide();
	}

	function generateMap() {
		ajax_loader_show("Locations targeting...");
		var coords_array_1 = new Array();
    	var coords_array_2 = new Array();
		clearOverlays();
		jQuery(".location_in_metabox").each(function(i, val) {
			var locations_drop_boxes = [];
			jQuery(this).find("select").each(function(j, val) {
				if (jQuery(this).val())
					locations_drop_boxes.push(jQuery(this).children(":selected").text());
			});

			var location_string = locations_drop_boxes.reverse().join(', ');

			if (jQuery(".manual_coords:eq("+i+")").is(":checked") && jQuery(".map_coords_1:eq("+i+")").val()!='' && jQuery(".map_coords_2:eq("+i+")").val()!='' && (jQuery(".map_coords_1:eq("+i+")").val()!=0 || jQuery(".map_coords_2:eq("+i+")").val()!=0)) {
				map_coords_1 = jQuery(".map_coords_1:eq("+i+")").val();
				map_coords_2 = jQuery(".map_coords_2:eq("+i+")").val();
				if (jQuery.isNumeric(map_coords_1) && jQuery.isNumeric(map_coords_2)) {
					point = new google.maps.LatLng(map_coords_1, map_coords_2);
					coords_array_1.push(map_coords_1);
					coords_array_2.push(map_coords_2);
	
					var location_obj = new glocation(i, point, 
						location_string,
						jQuery(".address_line_1:eq("+i+")").val(),
						jQuery(".address_line_2:eq("+i+")").val(),
						jQuery(".zip_or_postal_index:eq("+i+")").val(),
						jQuery(".map_icon_file:eq("+i+")").val()
					);
					location_obj.placeMarker();
					setMapCenter(coords_array_1, coords_array_2);
				}
			} else if(location_string) {
				var location_obj = new glocation(i, null, 
					location_string,
					jQuery(".address_line_1:eq("+i+")").val(),
					jQuery(".address_line_2:eq("+i+")").val(),
					jQuery(".zip_or_postal_index:eq("+i+")").val(),
					jQuery(".map_icon_file:eq("+i+")").val()
				);

				// Geocode by address
				geocoder.geocode( { 'address': location_obj.compileAddress()}, function(results, status) {
					if (status != google.maps.GeocoderStatus.OK) {
						alert("Sorry, we were unable to geocode that address (address #"+(i+1)+") for the following reason: " + status);
						ajax_loader_hide();
					} else {
						point = results[0].geometry.location;
						jQuery(".map_coords_1:eq("+i+")").val(point.lat());
						jQuery(".map_coords_2:eq("+i+")").val(point.lng());
						map_coords_1 = point.lat();
						map_coords_2 = point.lng();
						coords_array_1.push(map_coords_1);
						coords_array_2.push(map_coords_2);
						location_obj.setPoint(point);
						location_obj.placeMarker();
						setMapCenter(coords_array_1, coords_array_2);
					}
				});
			} else {
				ajax_loader_hide();
			}
		});
	}

	function placeMarker(glocation) {
		if (global_map_icons_path != '') {
			if (glocation.map_icon_file) {
				var customIcon = new google.maps.MarkerImage(
					//themes_path+glocation.map_icon_file,
					global_map_icons_path+'icons/'+glocation.map_icon_file,
					new google.maps.Size(32, 37),
					new google.maps.Point(0, 0),
					new google.maps.Point(16,37));
				var customShadow = new google.maps.MarkerImage(
					global_map_icons_path+'shadow-playground.png',
					new google.maps.Size(51, 37));
			} else {
				var customIcon = new google.maps.MarkerImage(
					global_map_icons_path+"blank.png",
					new google.maps.Size(27, 27),
					new google.maps.Point(0, 0),
					new google.maps.Point(14,27));
				var customShadow = new google.maps.MarkerImage(
					global_map_icons_path+'shadow-playground.png',
					new google.maps.Size(41, 27));
			}
			var marker = new google.maps.Marker({
				position: glocation.point,
				map: map,
				icon: customIcon,
				shadow: customShadow,
				draggable: true
			});
		} else 
			var marker = new google.maps.Marker({
				position: glocation.point,
				map: map,
				draggable: true
			});

		markersArray.push(marker);
		google.maps.event.addListener(marker, 'click', function() {
			showInfoWindow(glocation, marker);
		});
		
		google.maps.event.addListener(marker, 'dragend', function(event) {
			var point = marker.getPosition();
			if (point !== undefined) {
				var selected_location_num = glocation.index;
				jQuery(".manual_coords:eq("+glocation.index+")").attr("checked", true);
				jQuery(".manual_coords:eq("+glocation.index+")").parent().find(".manual_coords_block").show(200);

				jQuery(".map_coords_1:eq("+glocation.index+")").val(point.lat());
				jQuery(".map_coords_2:eq("+glocation.index+")").val(point.lng());
			}
		});
	}
	
	// This function builds info Window and shows it hiding another
	function showInfoWindow(glocation, marker) {
		address = glocation.compileHtmlAddress();
		index = glocation.index;
		//jQuery('#locations_accordion').accordion("activate", index);
		var windowHtml = '<div style="width:300px">';
		//if (global_logo != '')
			//windowHtml += '<img width="70px" class="map_window_logo" src="' + global_server_path + '/users_images/logos/' + global_logo + '" />';
		//windowHtml += '<b>' + global_title + '</b><br />' + address + '<div class="clear_float"></div>';
		windowHtml += address + '<div class="clear_float"></div>';
		windowHtml += '</div>';

		// we use global infoWindow, not to close/open it - just to set new content (in order to prevent blinking)
		if (!infoWindow)
			infoWindow = new google.maps.InfoWindow();

		infoWindow.setContent(windowHtml);
		infoWindow.open(map, marker);
	}
	
	function clearOverlays() {
		if (markersArray) {
			for(var i = 0; i<markersArray.length; i++){
				markersArray[i].setMap(null);
			}
		}
	}