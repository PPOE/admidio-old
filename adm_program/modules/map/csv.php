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
require_once('../../system/common.php');
require_once('../../system/classes/list_configuration.php');
require_once('../../system/classes/table_roles.php');

// prueft, ob der User die notwendigen Rechte hat, neue User anzulegen
if($gCurrentUser->editUsers() == false)
{
$roles = array(2,37,38,39,40,41,42,43,44,45);
$access = array(-1);
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
if(!$gCurrentUser || !$gValidLogin)
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}
elseif (!$gCurrentUser->getValue('PIRATENKARTE'))
{
    $gMessage->show('Du musst zuerst deine Präferenz für die Piratenkarte in deinem <a href="/adm_program/modules/profile/profile_new.php?user_id='.$gCurrentUser->getValue('usr_id').'">Profil</a> auswählen.');
}
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

$lat = null;
$lon = null;
$title = "";
$details = "";
$icon = "../../../adm_themes/ppoe/icons/profile.png";
$iconSize = "";
$iconOffset = "";

$rows = array();
$query = mysql_query("SELECT adm_users.usr_id,A.usd_value AS postal,B.usd_value AS city,C.usd_value AS street,gps,mem_rol_id IN (".implode(',',$access).") AS details FROM adm_users LEFT JOIN adm_members ON usr_id = mem_usr_id AND mem_rol_id IN (".implode(',',$access).") AND mem_end > NOW() AND mem_begin < NOW() LEFT JOIN adm_user_data A ON A.usd_usr_id = usr_id AND A.usd_usf_id = 4 LEFT JOIN adm_user_data B ON B.usd_usr_id = usr_id AND B.usd_usf_id = 5 LEFT JOIN adm_user_data C ON C.usd_usr_id = usr_id AND C.usd_usf_id = 3 LEFT JOIN usergps ON adm_users.usr_id = usergps.usr_id LEFT JOIN adm_user_data D ON D.usd_usr_id = adm_users.usr_id AND D.usd_usf_id = 41 WHERE (mem_rol_id IN (".implode(',',$access).") OR D.usd_value = 3) AND gps IS NOT NULL AND usr_valid ORDER BY gps;");
while ($query && ($row = mysql_fetch_assoc($query))) {
$rows[] = $row;
}

$query = mysql_query("SELECT adm_users.usr_id,A.usd_value AS postal,B.usd_value AS city,C.usd_value AS street,lat,lon,mem_rol_id IN (".implode(',',$access).") AS details FROM adm_users LEFT JOIN adm_members ON usr_id = mem_usr_id AND mem_rol_id IN (".implode(',',$access).") AND mem_end > NOW() AND mem_begin < NOW() LEFT JOIN adm_user_data A ON A.usd_usr_id = usr_id AND A.usd_usf_id = 4 LEFT JOIN adm_user_data B ON B.usd_usr_id = usr_id AND B.usd_usf_id = 5 LEFT JOIN adm_user_data C ON C.usd_usr_id = usr_id AND C.usd_usf_id = 3 LEFT JOIN usergps ON adm_users.usr_id = usergps.usr_id LEFT JOIN plz2gps ON A.usd_value = plz LEFT JOIN adm_user_data D ON D.usd_usr_id = adm_users.usr_id AND D.usd_usf_id = 41 WHERE (((mem_rol_id IN (".implode(',',$access).") OR D.usd_value = 3) AND gps IS NULL) OR (D.usd_value = 2)) AND lat IS NOT NULL AND usr_valid ORDER BY lat,lon;");
while ($query && ($row = mysql_fetch_assoc($query))) {
$rows[] = $row;
}
echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
$postals = array();
$old_row = array();
$gps = array();
foreach ($rows as $row) {
if (isset($row['gps']))
{
  $gps_r = explode(" ",$row['gps']);
  $row['lat'] = $gps_r[0];
  $row['lon'] = $gps_r[1];
}
if ($row['lat'] != $lat && $row['lon'] != $lon)
{
  echo "$lat\t$lon\t$title\t$description\t$icon\t$iconSize\t$iconOffset\n";
  $description = "";
  $w = 16.0;
}
$lat = $row['lat'];
$lon = $row['lon'];
$title = "PLZ " . $row['postal'];
$details = $row['details'];
if ($details)
$description .= "<a href=\"https://mitglieder.piratenpartei.at/adm_program/modules/mail/mail.php?usr_id=".$row['usr_id']."\" target=\"_blank\">Mitglied ".$row['usr_id']." eine Nachricht senden</a><br />";
else
$description .= "<a href=\"https://mitglieder.piratenpartei.at/adm_program/modules/mail/mail.php?usr_id=".$row['usr_id']."\" target=\"_blank\">Mitglied ".$row['usr_id']." eine Nachricht senden</a><br />";
$icon = "../../../adm_themes/ppoe/icons/profile.png";
$w += 2;
$iconSize = "$w,$w";
$o = $w / 2.0;
$iconOffset = "-$o,-$o";

$old_row = $row;
}
echo "$lat\t$lon\t$title\t$description\t$icon\t$iconSize\t$iconOffset\n";
// ENDE CONTENT

?>
