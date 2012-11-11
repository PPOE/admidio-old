<? 
include "config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$query = mysql_query("select G1.usr_id,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 12) as Email,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 33) as BackupEmail,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 35 AND G2.usd_value <= curdate()) as AkkDate
from `adm_users` G1 
where G1.usr_id IN (
select T1.usr_id
from `adm_users` T1 INNER JOIN `adm_user_data` T2
WHERE T1.usr_id = T2.usd_usr_id
AND   T2.usd_usf_id = 26
AND   T2.usd_value >= curdate()
)
and G1.usr_id IN (
select T3.usr_id
from `adm_users` T3 INNER JOIN `adm_members` T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T3.usr_valid = 1
AND   T4.mem_rol_id = 2
AND   T4.mem_end >= curdate()
)"); 

$lines = "";
$i = 0;
while ($row = mysql_fetch_array($query)) {
  $i++;
  $row[0] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $row[0], MCRYPT_MODE_CBC, md5(md5($key))));
  if ($row[1] == "mitglied@piratenpartei.at" && strlen($row[2]) > 4)
    $row[1] = $row[2];
  $row[4] = ($row[4] != null);
  $lines .= "$i\t".$row[0]."\t".$row[1]."\t".$row[3]."\t".$row[4]."\n";
}

$encrypted2 = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key2), $lines, MCRYPT_MODE_CBC, md5(md5($key2))));
echo $encrypted2;
mysql_close($link);
?>

