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

header("Content-Type: text/plain");
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

// Html-Kopf ausgeben
$gLayout['title']  = 'Piratenkarte';

// CONTENT AB HIER

$rows = array();
$query = mysql_query("SELECT adm_users.usr_id,A.usd_value AS postal,B.usd_value AS city,C.usd_value AS street,gps FROM adm_users LEFT JOIN adm_members ON usr_id = mem_usr_id AND mem_rol_id IN (".implode(',',$access).") AND mem_end > NOW() AND mem_begin < NOW() LEFT JOIN adm_user_data A ON A.usd_usr_id = usr_id AND A.usd_usf_id = 4 LEFT JOIN adm_user_data B ON B.usd_usr_id = usr_id AND B.usd_usf_id = 5 LEFT JOIN adm_user_data C ON C.usd_usr_id = usr_id AND C.usd_usf_id = 3 LEFT JOIN usergps ON adm_users.usr_id = usergps.usr_id WHERE mem_rol_id IN (".implode(',',$access).") AND usr_valid AND gps IS NOT NULL ORDER BY gps;");
while ($query && ($row = mysql_fetch_assoc($query))) {
$rows[] = $row;
}

$query = mysql_query("SELECT adm_users.usr_id,A.usd_value AS postal,B.usd_value AS city,C.usd_value AS street,lat,lon FROM adm_users LEFT JOIN adm_members ON usr_id = mem_usr_id AND mem_rol_id IN (".implode(',',$access).") AND mem_end > NOW() AND mem_begin < NOW() LEFT JOIN adm_user_data A ON A.usd_usr_id = usr_id AND A.usd_usf_id = 4 LEFT JOIN adm_user_data B ON B.usd_usr_id = usr_id AND B.usd_usf_id = 5 LEFT JOIN adm_user_data C ON C.usd_usr_id = usr_id AND C.usd_usf_id = 3 LEFT JOIN usergps ON adm_users.usr_id = usergps.usr_id LEFT JOIN plz2gps ON A.usd_value = plz WHERE usergps.gps IS NULL AND mem_rol_id IN (".implode(',',$access).") AND usr_valid AND lat IS NOT NULL ORDER BY lat,lon;");
while ($query && ($row = mysql_fetch_assoc($query))) {
$rows[] = $row;
}
echo "var JSONaddresses = [";
$postals = array();
$old_row = array();
$gps = array();
$entry = "";
foreach ($rows as $row) {
if (isset($row['gps']))
{
  $gps_r = explode(" ",$row['gps']);
  $row['lat'] = $gps_r[0];
  $row['lon'] = $gps_r[1];
}
//if ($row['lat'] != "" && $row['lon'] != "")
//{
//  $entry .= "{'id':'$id','nickname':'$id','plz':'$plz','city':'empty','street':'empty','lat':$lat,'lon':$lon},";
//}
$lat = $row['lat'];
$lon = $row['lon'];
$plz = $row['postal'];
$id = $row['usr_id'];

$entry .= "{\"id\":$id,\"nickname\":$id,\"plz\":\"$plz\",\"lat\":$lat,\"lon\":$lon},";
//$entry .= "{id:$id,nickname:$id,plz:\"$plz\",lat:$lat,lon:$lon},";
//$entry .= "{'id':'$id','nickname':'$id','plz':'$plz','city':'empty','street':'empty','lat':$lat,'lon':$lon},";

//$old_row = $row;
}
echo substr_replace($entry,"",-1);
echo "];";

// ENDE CONTENT

?>
