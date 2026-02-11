//V3 Google Maps API CJG 20201028
<script type="text/javascript">

var latitude = Number(document.getElementById('GuideLat').value);
var longitude = Number(document.getElementById('GuideLong').value);

function initMap() {
  /**
   *Passing the value of variable received from text box
   **/
  var uluru = {
    lat: latitude,
    lng: longitude
  };
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 14,
    center: uluru
  });
  marker = new google.maps.Marker({
    map: map,
    draggable: true,
    animation: google.maps.Animation.DROP,
    position: uluru
  });
  google.maps.event.addListener(marker, 'dragend',
    function(marker) {
      var latLng = marker.latLng;
      document.getElementById("GuideLat").value = latLng.lat().toFixed(5);
	 document.getElementById("GuideLong").value = latLng.lng().toFixed(5);
    }
  );
	
// Create the search box and link it to the UI element.
  const input = document.getElementById("pac-input");
  const searchBox = new google.maps.places.SearchBox(input);
  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
  // Bias the SearchBox results towards current map's viewport.
  map.addListener("bounds_changed", () => {
    searchBox.setBounds(map.getBounds());
  });
  let markers = [];
  // Listen for the event fired when the user selects a prediction and retrieve
  // more details for that place.
  searchBox.addListener("places_changed", () => {
    const places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }
    // Clear out the old markers.
    markers.forEach((marker) => {
      marker.setMap(null);
    });
    markers = [];
    // For each place, get the icon, name and location.
    const bounds = new google.maps.LatLngBounds();
    places.forEach((place) => {
      if (!place.geometry) {
        console.log("Returned place contains no geometry");
        return;
      }
      const icon = {
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25),
      };
      // Create a marker for each place.
      
		marker = new google.maps.Marker({
			map: map,
			draggable: true,
			animation: google.maps.Animation.DROP,
			position: place.geometry.location,
		});
		
		var latLng = marker.position;
		  document.getElementById("GuideLat").value = latLng.lat().toFixed(5);
		 document.getElementById("GuideLong").value = latLng.lng().toFixed(5);
		
		
		google.maps.event.addListener(marker, 'dragend',
		function(marker) {
		  var latLng = marker.latLng;
		  document.getElementById("GuideLat").value = latLng.lat().toFixed(5);
		 document.getElementById("GuideLong").value = latLng.lng().toFixed(5);
		}
	  	);	
		
      if (place.geometry.viewport) {
        // Only geocodes have viewport.
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });
    map.fitBounds(bounds);
  });

	
	
	
}
</script>
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

<script defer
	src="//maps.googleapis.com/maps/api/js?key=<?php echo $cParams->get('google_maps_api_key', ''); ?>&libraries=places&callback=initMap">
</script>
 