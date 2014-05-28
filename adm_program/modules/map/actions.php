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

if (!$gCurrentUser)
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}
//Verwaltung der Session
$_SESSION['navigation']->clear();
$_SESSION['navigation']->addUrl(CURRENT_URL);

// Html-Kopf ausgeben
$gLayout['title']  = 'Piratenkarte';

// CONTENT AB HIER

echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
$icon = "../../../adm_themes/ppoe/icons/exclamation.png";
$iconSize = "16,16";
$iconOffset = "-8,-8";
$title = "Aktion";
$query = mysql_query("SELECT A.*,usr_login_name AS owner_name FROM map_actions A LEFT JOIN adm_users ON usr_id = owner ORDER BY id ASC;");
while ($query && ($row = mysql_fetch_assoc($query))) {
  $id = $row['id'];
  if ($row['owner_name'])
    $title = "Aktion $id von <a href=\"https://mitglieder.piratenpartei.at/adm_program/modules/mail/mail.php?usr_id=".$row['owner']."\" target=\"_blank\">".$row['owner_name']."</a><br />";
  else
    $title = "Aktion $id von <a href=\"https://mitglieder.piratenpartei.at/adm_program/modules/mail/mail.php?usr_id=".$row['owner']."\" target=\"_blank\">Mitglied ".$row['owner']."</a><br />";
  if ($row['owner'] == $gCurrentUser->getValue('usr_id'))
    $row['content'] .= "<br /><br /><a id=\"delA$id\" href=\"javascript:deleteAction($id);\">Aktion löschen</a>";
  echo $row['lat']."\t".$row['lon']."\t".$title."\t".$row['content']."\t$icon\t$iconSize\t$iconOffset\n";
  $description = "";
}

// ENDE CONTENT

?>
