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

// prueft, ob der User die notwendigen Rechte hat, neue User anzulegen
if($gCurrentUser->editUsers() == false)
{
$roles = array(2,37,38,39,40,41,42,43,44,45);
$access = array();
foreach ($roles as $getRoleId)
{
// Rollenobjekt erzeugen
$role = new TableRoles($gDb, $getRoleId);

//Testen ob Recht zur Listeneinsicht besteht
if($role->viewRole() == false)
{
}
else
{
  $access[] = $getRoleId;
  if ($getRoleId == 2)
  {
    $access = array(2);
    break;
  }
}
}
if (count($access) == 0)
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}
}
else
{
  $access = array(2);
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
<title>Piratenkarte</title>
<link rel="stylesheet" type="text/css" href="map.css">
<link href="bootstrap/dist/css/bootstrap.css" rel="stylesheet">
</head><body>
  <div id="mapdiv"></div>
  <script src="openlayers/lib/OpenLayers.js"></script>
  <script>
    map = new OpenLayers.Map("mapdiv", { controls: [] });
    map.addLayer(new OpenLayers.Layer.OSM());
map.addControl(new OpenLayers.Control.PanZoomBar());
map.addControl(new OpenLayers.Control.Permalink());
map.addControl(new OpenLayers.Control.Permalink('permalink'));
map.addControl(new OpenLayers.Control.Attribution());
map.addControl(new OpenLayers.Control.KeyboardDefaults()); 
map.addControl(new OpenLayers.Control.Navigation());
    var pois = new OpenLayers.Layer.Text( "Piraten",
                    { location:"./csv.php",
                      projection: map.displayProjection
                    });
    map.addLayer(pois);
 
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
