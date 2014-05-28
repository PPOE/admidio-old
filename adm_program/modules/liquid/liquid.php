<?php
header("Content-Type: text/plain");
require_once('../../system/common.php');
require_once('../../system/classes/list_configuration.php');
require_once('../../system/classes/table_roles.php');
include "../../../adm_api/config.php";
//Verwaltung der Session
$_SESSION['navigation']->clear();
$_SESSION['navigation']->addUrl(CURRENT_URL);

// Html-Kopf ausgeben
$gLayout['title']  = 'Liquid Wählerverzeichnis';

if(!$gCurrentUser || !$gValidLogin)
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

include "config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$query = mysql_query("SELECT * FROM (select G1.usr_id,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end >= curdate() AND G3.mem_begin <= curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO,
(SELECT nuts FROM ppoe_mitglieder.adm_user_data G3 LEFT JOIN ppoe_mitglieder.nutsplz ON usd_value = plz WHERE usd_usf_id IN (4,39) AND G1.usr_id = G3.usd_usr_id GROUP BY nuts ORDER BY nuts ASC LIMIT 1 OFFSET 0) AS RO1,
(SELECT nuts FROM ppoe_mitglieder.adm_user_data G3 LEFT JOIN ppoe_mitglieder.nutsplz ON usd_value = plz WHERE usd_usf_id IN (4,39) AND G1.usr_id = G3.usd_usr_id GROUP BY nuts ORDER BY nuts ASC LIMIT 1 OFFSET 1) AS RO2,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 35 AND G2.usd_value <= curdate()) as AkkDate,
(select G2.usd_value + interval 30 day from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 35 AND G2.usd_value + interval 30 day >= curdate()) as Probemonat,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26 AND G2.usd_value + interval 14 day >= curdate()) as Paid
from `adm_users` G1
WHERE (select G1.usr_id IN (select T3.usr_id from `adm_users` T3 INNER JOIN `adm_members` T4 WHERE T3.usr_id = T4.mem_usr_id AND T3.usr_valid = 1 AND T4.mem_rol_id = 2 AND T4.mem_begin <= curdate() AND T4.mem_end >= curdate()))) A WHERE AkkDate < NOW() AND (Paid >= NOW() OR Probemonat >= NOW()) ORDER BY md5(usr_id)");

$lines = "";
$i = 0;
while ($row = mysql_fetch_assoc($query)) {
  $i++;
  $row['usr_id'] = md5(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $row['usr_id'], MCRYPT_MODE_CBC, md5(md5($key)))));
  $lines .= "$i\t".$row['usr_id']."\n";
}
echo $lines;
mysql_close($link);

?>
