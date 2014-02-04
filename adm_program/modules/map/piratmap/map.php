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

require_once('../../../system/common.php');

require_once('../../../system/classes/list_configuration.php');
require_once('../../../system/classes/table_roles.php');

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

// Html ausgeben
echo<<<END
<!DOCTYPE html>

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

<title>PiratMap</title>

<link rel="stylesheet" href="normalize.css" />
<link rel="stylesheet" href="main.css" />
<link rel="stylesheet" href="leaflet/leaflet.css" />
<link rel="stylesheet" href="L.Control.Zoomslider/L.Control.Zoomslider.css" />
<link rel="stylesheet" href="L.Control.Locate/L.Control.Locate.css" />
<link rel="stylesheet" href="L.Control.MousePosition/L.Control.MousePosition.css" />
<!--<link rel="stylesheet" href="L.Awesome-Markers/L.Awesome-Markers.css" />
<link rel="stylesheet" href="L.Awesome-Markers/Font-Awesome/css/font-awesome.min.css" />-->

<!--[if lt IE 9]>
	<link rel="stylesheet" href="L.Control.Zoomslider/L.Control.Zoomslider.ie.css" />
	<link rel="stylesheet" href="L.Control.Locate/L.Control.Locate.ie.css" />
<![endif]-->

<script src="jquery-2.0.3.min.js"></script>
<script src="browser-update.js"></script>
<script src="leaflet/leaflet.js"></script>
<script src="L.Control.Zoomslider/L.Control.Zoomslider.js"></script>
<script src="L.Control.Locate/L.Control.Locate.js"></script>
<script src="L.Control.MousePosition/L.Control.MousePosition.js"></script>
<!--<script src="L.Awesome-Markers/L.Awesome-Markers.min.js"></script>-->
<script src="L.TileLayer.Providers/L.TileLayer.Providers.js"></script>
<!--<script src="L.MarkerCluster/L.MarkerCluster.js"></script>
<script src="extends.js"></script>-->
<script src="json.php"></script>
<script src="init.js"></script>

</head>

<body>

<div id="map"></div>

</body>

</html>
END;
// ENDE CONTENT

?>

