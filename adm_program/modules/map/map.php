<?php
/******************************************************************************
 * Show a list of all downloads
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Parameters:
 *
 * folder_id : akutelle OrdnerId
 *
 *****************************************************************************/

require_once('../../system/common.php');

require_once('../../system/classes/list_configuration.php');
require_once('../../system/classes/table_roles.php');

if(!$gCurrentUser || !$gValidLogin)
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}
elseif (!$gCurrentUser->getValue('PIRATENKARTE'))
{
    $gMessage->show('Du musst zuerst deine Präferenz für die Piratenkarte in deinem <a href="/adm_program/modules/profile/profile_new.php?user_id='.$gCurrentUser->getValue('usr_id').'">Profil</a> auswählen.');
}

//Verwaltung der Session
$_SESSION['navigation']->clear();
$_SESSION['navigation']->addUrl(CURRENT_URL);

// Html-Kopf ausgeben
$gLayout['title']  = 'Piratenkarte';
$gLayout['bodyfirst'] = '<div id="mapdiv"></div>';

$lat = '47.56';
$lon = '14.0';
$zoom = '8';

if (isset($_GET['plz']) && preg_match('/^\d+$/',$_GET['plz']) == 1)
{
  $query = mysql_query("SELECT lat,lon FROM plz2gps WHERE plz = '".$_GET['plz']."';");
if ($query && ($row = mysql_fetch_assoc($query))) {
  $lat = $row['lat'];
  $lon = $row['lon'];
  $zoom = '13';
}
}
echo<<<END
  <html><head>
<meta charset="UTF-8" />
<title>Piratenkarte</title>
<link rel="stylesheet" type="text/css" href="map.css">
<link href="bootstrap/dist/css/bootstrap.css" rel="stylesheet">
</head><body>
  <div id="mapdiv"></div>
  <table id="newMarkerPopup" class="olPopup" style="display: none;">
  <tr>
  <td style="padding-left: 15px">
  <h4>Aktion eintragen</h4>
  <form>
  <p>
  <textarea id="content" style="height: 250px; width: 270px;"></textarea>
  <input value="Speichern" type="button" onclick="saveCurrentMarker();"/>
  <input value="Abbrechen" type="button" onclick="undoCurrentMarker();"/>
  <iframe id="updateframe" style="display: none;" src="addaction.php"></iframe>
  </p>
  </form>
  </td>
  </tr>
  </table>
  <script src="openlayers/lib/OpenLayers.js"></script>
  <script>
    var currentMarker = null;
    function deleteAction(id)
    {
      document.getElementById('delA' + id).parentNode.parentNode.parentNode.parentNode.style.display = 'none';
      document.getElementById('updateframe').src = 'addaction.php?action=del&id=' + id;
      document.getElementById('content').value = '';
      document.getElementById('newMarkerPopup').style.display = 'none';
      map.getLayer('Markers').removeMarker(currentMarker);
      currentMarker = null;
      // TODO remove marker from screen immediately
    }
    function saveCurrentMarker()
    {
      if (currentMarker == null)
        return;
      var content = encodeURIComponent(document.getElementById('content').value);
      var lonlat = currentMarker.lonlat.transform(new OpenLayers.Projection("EPSG:900913"), new OpenLayers.Projection("EPSG:4326"));
      var lat = lonlat.lat;
      var lon = lonlat.lon;
      // TODO use jquery
      document.getElementById('updateframe').src = 'addaction.php?action=add&lat=' + lat + '&lon=' + lon + '&content=' + content;
      document.getElementById('content').value = '';
      document.getElementById('newMarkerPopup').style.display = 'none';
      map.getLayer('Markers').removeMarker(currentMarker);
      currentMarker = null;
      // TODO put the new marker on screen immediately
    }
    function undoCurrentMarker()
    {
      if (currentMarker == null)
        return;
      document.getElementById('newMarkerPopup').style.display = 'none';
      map.getLayer('Markers').removeMarker(currentMarker);
      currentMarker = null;
    }
    var map = new OpenLayers.Map("mapdiv", { controls: [] });
    map.addLayer(new OpenLayers.Layer.OSM());
map.addControl(new OpenLayers.Control.LayerSwitcher({'ascending':true}));
map.addControl(new OpenLayers.Control.PanZoomBar());
map.addControl(new OpenLayers.Control.Permalink());
map.addControl(new OpenLayers.Control.Permalink('permalink'));
map.addControl(new OpenLayers.Control.Attribution());
map.addControl(new OpenLayers.Control.KeyboardDefaults()); 
map.addControl(new OpenLayers.Control.Navigation());

map.baseLayer.displayInLayerSwitcher = false;

    var pois = new OpenLayers.Layer.Text( "Piraten",
                    { location:"./csv.php",
                      projection: map.displayProjection
                    });
    map.addLayer(pois);
 

    var actions = new OpenLayers.Layer.Text( "Aktionen",
                    { location:"./actions.php",
                      projection: map.displayProjection
                    });
    actions.id = "Aktionen";

map.addLayer(actions);

var markers = new OpenLayers.Layer.Markers( "Neue Aktion", {'displayInLayerSwitcher':false} );

markers.id = "Markers";
map.addLayer(markers);

map.events.register("click", map, function(e) {
      //var position = this.events.getMousePosition(e);
      var position = map.getLonLatFromPixel(e.xy);
      var size = new OpenLayers.Size(16,16);
   var offset = new OpenLayers.Pixel(-(size.w/2), -(size.h/2));
   var icon = new OpenLayers.Icon('/adm_themes/ppoe/icons/exclamation.png', size, offset);   
   var markerslayer = map.getLayer('Markers');
   if (currentMarker)
     undoCurrentMarker();
   currentMarker = new OpenLayers.Marker(position,icon);
   markerslayer.addMarker(currentMarker);
   
   document.getElementById("newMarkerPopup").style.display = "";

   });

/*    var voters = new OpenLayers.Layer.Text( "Haushalte",
                    { location:"./voters.php",
                      projection: map.displayProjection
                    });
    map.addLayer(voters);*/
    //Set start centrepoint and zoom    
    var lonLat = new OpenLayers.LonLat( $lon, $lat)
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
    var zoom=$zoom;
    map.setCenter (lonLat, zoom);  
 
  </script>
END;
// ENDE CONTENT

?>
