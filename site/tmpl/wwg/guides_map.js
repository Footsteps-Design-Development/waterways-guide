<script type="text/javascript">

	//V3 Google Maps API CJG 20201028
	var html1 = "";
	var curWaterway = "";
	var curCountry = "";
	var startLat = "47.6392";
	var firstlat="0";
	var startLng = "2.73623";
	var startzoom = 10;
	var displaySummary="";

	var customIcon= ["","/media/com_waterways_guide/images/marker1.png","/media/com_waterways_guide/images/marker2.png","/media/com_waterways_guide/images/marker3.png","/media/com_waterways_guide/images/marker4.png"];

	function initMap() {
	var bounds = new google.maps.LatLngBounds;
	var gmarkers = [];
	var idmarkers = [];
	var idmarkers = [];
	var Openitnow = "";

	var map = new google.maps.Map(document.getElementById('map'), {
		center: new google.maps.LatLng(startLat,startLng),
	zoom: startzoom,
	});
	var infoWindow = new google.maps.InfoWindow;



	// Change this depending on the name of your PHP or XML file
	console.log("/components/com_waterways_guide/tmpl/wwg/guides_map_xml.php<?php echo($mapvars); ?>");

	downloadUrl("/components/com_waterways_guide/tmpl/wwg/guides_map_xml.php<?php echo($mapvars); ?>", function(data) {
		
		var waterwaysummary = [];

	var xml = data.responseXML;
	var markers = xml.getElementsByTagName('marker');
	var sidebar = document.getElementById('sidebar');
	sidebar.innerHTML = '';
	var header = document.getElementById('header');
	var markers = xml.documentElement.getElementsByTagName('marker');
	Array.prototype.forEach.call(markers, function(markerElem) {
			
			var ID = markerElem.getAttribute('ID');
	var Openit = markerElem.getAttribute('Openit');
	var Country = markerElem.getAttribute('Country');
	var Waterway = markerElem.getAttribute('Waterway');
	var Name = markerElem.getAttribute('Name');
	var Rating = markerElem.getAttribute('Rating');
	var LatLng = markerElem.getAttribute('LatLng');
	var Reference = markerElem.getAttribute('Reference');
	var Location = markerElem.getAttribute('Location');
	var Mooring = markerElem.getAttribute('Mooring');
	var Codes = markerElem.getAttribute('Codes');
	var Facilities = markerElem.getAttribute('Facilities');
	var Costs = markerElem.getAttribute('Costs');
	var Amenities = markerElem.getAttribute('Amenities');
	var Summary = markerElem.getAttribute('Summary');
	var Contributors = markerElem.getAttribute('Contributors');
	var Remarks = markerElem.getAttribute('Remarks');
	var Update = markerElem.getAttribute('Update');
	var Cat = markerElem.getAttribute('Cat');


	if(!Rating || Rating==0){
		Rating = 1;
			}
	if(Summary){
				if(!displaySummary){
		displaySummary = Summary;
				}else{
		displaySummary += " " + Summary;
				}
			}

	var thislat=parseFloat(markerElem.getAttribute('Lat'));
	var thislng=parseFloat(markerElem.getAttribute('Lng'))
			if(thislat>0){
				//valid lat lng
				if(firstlat!=startLat){
		//store first valid point for start map centre
		startLat = thislat;
	startLng=thislng;
	firstlat=startLat;
				}
	var point = new google.maps.LatLng(
	parseFloat(thislat),
	parseFloat(thislng));
	bounds.extend(point);
	map.fitBounds(bounds);

	var html1 = "<div>";
		if(Name){
					if(Cat=='1'){
			html1 += "<b>Mooring: " + Name + "</b>";
					}else if(Cat=='2'){
			html1 += "<b>Hazard " + Name + "</b>";
					}else{
			html1 += "<b>Name: " + Name + "</b>";
					}		
				}

		//work out Rating
		var i=1;
		var RatingIcon="";
		for (var m = 0; m < Rating; m++) {
					if(Cat==2){
			RatingIcon += "<img src='/Image/common/hazard_small.gif' alt='Hazard level' title='Hazard level' width='16' height='16' border='0'>";				
					}else{
			RatingIcon += "<img src='/Image/common/star.gif' alt='Rating' title='Rating' width='16' height='16' border='0'>";
					}
				}


		if(Rating){
			html1 += "<br /><b>Rating:</b> " + RatingIcon;
				}
		if(point){
			html1 += "<br /><b>Position:</b> " + LatLng;
				}
		if(Reference){
			html1 += "<br /><b>Reference:</b> " + Reference;
				}
		if(Location){
			html1 += "<br /><b>Location:</b> " + Location;
				}
		if(Mooring){
			html1 += "<br /><b>Mooring:</b> " + Mooring;
				}
		if(Codes){
			html1 += "<br /><b>Essentials:</b> " + Codes;
				}
		if(Facilities){
			html1 += "<br /><b>Facilities:</b> " + Facilities;
				}

		if(Costs){
			html1 += "<br /><b>Costs:</b> " + Costs;
				}
		if(Amenities){
			html1 += "<br /><b>Amenities:</b> " + Amenities;
				}
		if(Contributors){
			html1 += "<br /><b>Contributors:</b> " + Contributors;
				}
		if(Remarks){
			html1 += "<br /><b>Remarks:</b> " + Remarks;
				}
		if(Update){
			html1 += "<br /><b>Update:</b> " + Update;
				}


		html1 += "<br /><b>Submit update:</b> Click <a href='?guideaction=memberedit&infoid="+ID+"'>here to update this mooring <img src='/Image/common/open.gif' width='18' height='18' border='0' title='Submit an update to this entry' alt='Submit an update to this entry'></a>";
		html1 += "</div>";
	html1 += "<div class='pinpop_copyright'>Copyright &copy; <?php echo(date("Y ").$sitename); ?></div>";


	var infowincontent = document.createElement('div');
	var text = document.createElement('text');
	text.textContent = html1;
	infowincontent.appendChild(text);


	var marker = new google.maps.Marker({
		map: map,
	position: point,
	icon: customIcon[Rating]
				});
	marker.addListener('click', function() {
		infoWindow.setContent(html1);
	infoWindow.open(map, marker);
				});

	// save the info we need to find this marker
	if(ID==Openit){
		OpenitnowID = gmarkers.length;
	gmarkers.push(marker);
	idmarkers[Openit.toLowerCase()] = marker;
	Openitnow=Openit;
				}

	var sidebarEntry = createSidebarEntry(marker,ID,Name,Country,Waterway);
	sidebar.appendChild(sidebarEntry);
			}
		});
		
		
		if(markers.length>0){
			if (Openitnow) {
				if (idmarkers[Openitnow]) {
		google.maps.event.addListenerOnce(map, 'idle', function () {
			map.setCenter(idmarkers[Openitnow].getPosition());
			map.setZoom(12);
			google.maps.event.trigger(idmarkers[Openitnow], 'click');
		});
				} 
			}else{
		map.fitBounds(bounds)
	}

	var key="<div class='Key'><b>Ratings:</b> <img src='"+customIcon[1]+"' alt='rating' width='14' height='22' border='0'> *  <img src='"+customIcon[2]+"' alt='rating' width='14' height='22' border='0'> **  <img src='"+customIcon[3]+"' alt='rating' width='14' height='22' border='0'> *** <img src='"+customIcon[4]+"' alt='hazard' width='14' height='22' border='0'> Hazard</div>";

		header.innerHTML = "Click on a location in the left hand window or on a pin on the map for details."+key;
		}else{
			header.innerHTML = "Sorry, there are no location positions available for the chosen search";

		}
	});
		
}


		function downloadUrl(url, callback) {
	var request = window.ActiveXObject ?
		new ActiveXObject('Microsoft.XMLHTTP') :
		new XMLHttpRequest;

		request.onreadystatechange = function() {
	  if (request.readyState == 4) {
			request.onreadystatechange = doNothing;
		callback(request, request.status);
	  }
	};

		request.open('GET', url, true);
		request.send(null);
}

		function doNothing() { }

		function createSidebarEntry(marker,ID,Name,Country,Waterway) {
		var div = document.createElement('div');
		if(curCountry!=Country.toUpperCase()){
				//new country Country heading
				var html = '<div class=Country>' + Country.toUpperCase() + '</div>';
		curCountry=Country.toUpperCase();
			} else {
				var html = '';
			}
		if(curWaterway!=Waterway){
			//new Waterway heading
			html += '<div class=Waterway>' + Waterway + '</div>' + Name + '';
		curWaterway=Waterway;
	}else{
			html += '' + Name + '';
	}
		div.innerHTML = html;
		div.style.cursor = 'pointer';
		div.style.marginBottom = '3px';

		google.maps.event.addDomListener(div, 'click', function() {
			google.maps.event.trigger(marker, 'click');
		div.style.backgroundColor = '#eee';
	});
		google.maps.event.addDomListener(div, 'mouseover', function() {
			div.style.backgroundColor = '#eee';
	});
		google.maps.event.addDomListener(div, 'mouseout', function() {
			div.style.backgroundColor = '#fff';
	});
		return div;
}


	</script>

		<script defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $cParams->get('google_maps_api_key', ''); ?>&callback=initMap&libraries=maps,marker">
		</script>
