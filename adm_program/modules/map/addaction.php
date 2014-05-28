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

function base64_url_decode($input) {
  return base64_decode(strtr($input, '-_,', '+/='));
}

if (isset($_GET['action']))
{
  switch ($_GET['action'])
  {
    case 'add':
      $owner = $gCurrentUser->getValue('usr_id');
      $lat = $gDb->escape_string($_GET['lat']);
      $lon = $gDb->escape_string($_GET['lon']);
      $content = $gDb->escape_string(str_replace('<','&lt;',$_GET['content']));
      $q = "INSERT INTO map_actions (owner,lat,lon,content) VALUES ($owner,'$lat','$lon','$content');";
      $gDb->query($q);
      break;
    case 'del':
      $id = intval($_GET['id']);
      $owner = $gCurrentUser->getValue('usr_id');
      $q = "DELETE FROM map_actions WHERE id = $id AND owner = $owner";
      $gDb->query($q);
      break;
    case 'edit':
      $id = intval($_GET['id']);
      $owner = $gCurrentUser->getValue('usr_id');
      $content = $gDb->escape_string($_GET['content']);
      $content = $gDb->escape_string(str_replace('<','&lt;',$_GET['content']));
      $q = "UPDATE map_actions SET content = '$content' WHERE id = $id AND owner = $owner;";
      $gDb->query($q);
      break;
  }
}

?>
