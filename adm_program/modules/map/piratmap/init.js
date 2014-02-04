var map,baseLayers,overlays;
var geoJSONfeatures = [];

$(function(){
	map = initMap();
	baseLayers = getBaseLayers();
	overlays = getOverlays();
	addLayers();
	addControls();
	getData();
});

function initMap(){
	var maxB = new L.LatLngBounds(new L.LatLng(53.03,1.52),new L.LatLng(42.37,25.17));
	var locations = [];
	for(a in JSONaddresses){
		locations.push(new L.LatLng(JSONaddresses[a].lat,JSONaddresses[a].lon));
	}
	var fitB = new L.LatLngBounds(locations); //new L.LatLng(49.03,9.52),new L.LatLng(46.37,17.17)); //Je nach Bundesland setzen
	
	return new L.Map('map',
	{
		minZoom: 7,
		maxZoom: 18,
		maxBounds: maxB,
		zoomAnimationThreshold: 8
	}).fitBounds(
		fitB
	).locate({
		//watch: true,
		setView: true,
		maxZoom: 12
	});
}

function getBaseLayers(){
	return	['OpenStreetMap.Mapnik','OpenStreetMap.DE','Thunderforest.Transport','Stamen.Watercolor','Esri.WorldImagery'];/*{
		+'OpenStreetMap_Mapnik': new L.TileLayer.Provider('OpenStreetMap.Mapnik'),
		+'OpenStreetMap_DE': new L.TileLayer.Provider('OpenStreetMap.DE'),
		'OpenStreetMap_BlackandWhite': new L.TileLayer.Provider('OpenStreetMap.BlackAndWhite'),
		'Thunderforest_OpenCycleMap': new L.TileLayer.Provider('Thunderforest.OpenCycleMap'),
		+'Thunderforest_Transport': new L.TileLayer.Provider('Thunderforest.Transport'),
		'Thunderforest_Landscape': new L.TileLayer.Provider('Thunderforest.Landscape'),
		'MapQuest_OSM': new L.TileLayer.Provider('MapQuestOpen.OSM'),
		'MapQuest_Aerial': new L.TileLayer.Provider('MapQuestOpen.Aerial'),
		'MapBox_Simple': new L.TileLayer.Provider('MapBox.Simple'),
		'MapBox_Streets': new L.TileLayer.Provider('MapBox.Streets'),
		'MapBox_Light': new L.TileLayer.Provider('MapBox.Light'),
		'MapBox_Lacquer': new L.TileLayer.Provider('MapBox.Lacquer'),
		'MapBox_Warden': new L.TileLayer.Provider('MapBox.Warden'),
		'Stamen_Toner': new L.TileLayer.Provider('Stamen.Toner'),
		'Stamen_Terrain': new L.TileLayer.Provider('Stamen.Terrain'),
		+'Stamen_Watercolor': new L.TileLayer.Provider('Stamen.Watercolor'),
		'Esri_WorldStreetMap': new L.TileLayer.Provider('Esri.WorldStreetMap'),
		'Esri_DeLorme': new L.TileLayer.Provider('Esri.DeLorme'),
		'Esri_WorldTopoMap': new L.TileLayer.Provider('Esri.WorldTopoMap'),
		+'Esri_WorldImagery': new L.TileLayer.Provider('Esri.WorldImagery'),
		'Esri_OceanBasemap': new L.TileLayer.Provider('Esri.OceanBasemap'),
		'Esri_NatGeoWorldMap': new L.TileLayer.Provider('Esri.NatGeoWorldMap')
	};*/
}

function getOverlays(){
	/*var mitgliederMarker = L.AwesomeMarkers.icon({
		icon: "user",
		markerColor: "darkpurple",
		prefix: "fa"
	});*/
	var mitgliederMarker = L.icon({
		iconUrl: "leaflet/images/marker-user.png",
		iconSize: [20, 26],
		iconAnchor: [10, 26],
		popupAnchor: [0, -22]
	});
	
	return {
		"Mitglieder": new L.GeoJSON("",{
			pointToLayer: function (feature, latlng) {
				return new L.Marker(latlng, {icon: mitgliederMarker});
			},
			onEachFeature: function (feature, layer) {
				layer.bindPopup('<h1>' + feature.properties.title + '</h1><ul>' + feature.properties.text + '</ul>');
			}
		})
	};
}

function addLayers(){
	//map.addLayer(baseLayers.OpenStreetMap_Mapnik);
	//Overlays
	map.addLayer(overlays.Mitglieder);
}

function addControls(){
	map.addControl(new L.Control.Layers.Provided(baseLayers,overlays));
	map.addControl(new L.Control.Locate({follow: true}));
	map.addControl(new L.Control.MousePosition());
	map.addControl(new L.Control.Scale({'imperial':false}));
}

function getData(){
	for(var f=0;f<JSONaddresses.length;f++){
		var user = JSONaddresses[f];
		geoJSONfeatures.push({'type':'Feature','properties':{'title':'PLZ ' + user.plz,'text':'<li><a href="https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=' + user.id + '">' + user.nickname + '</a></li>'},'geometry':{'type':'Point','coordinates':[user.lon,user.lat]}});
		//geocodingNominatim('AT',JSONaddresses[f].plz,JSONaddresses[f].city,JSONaddresses[f].street,f);
	}
	overlays.Mitglieder.addData({'type':'FeatureCollection','features': geoJSONfeatures});
}
/*
function geocodingNominatim(country,plz,city,street,user){
	var search_query = 'http://nominatim.openstreetmap.org/search.php?format=json&accept-language=de&limit=1&countrycodes=AT&country=' + country + '&postalcode=' + plz + '&city=' + city + '&street=' + street;
	
	$.getJSON(search_query).always(function(dataJSON){
		var coords = [parseFloat(dataJSON[0].lon),parseFloat(dataJSON[0].lat)];
		var feature = {'type':'Feature','properties':{'title':'PLZ ' + user.plz,'text':'<li><a href="https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=' + user.id + '">' + user.nickname + '</a></li>'},'geometry':{'type':'Point','coordinates':coords}};
		geoJSONfeatures.push(feature);
		
		if(geoJSONfeatures.length == JSONaddresses.length){
			var geoJSON = {'type':'FeatureCollection','features': geoJSONfeatures};
			overlays.Mitglieder.addData(geoJSON);
		}
	});
}
*/
