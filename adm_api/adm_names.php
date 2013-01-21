<? 
include "config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$query = mysql_query("select G1.usr_id,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 1) as LN,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 2) as FN,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_begin < curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO
from `adm_users` G1 
where G1.usr_id IN (
select T3.usr_id
from `adm_users` T3 INNER JOIN `adm_members` T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T3.usr_valid = 1
AND   T4.mem_rol_id = 2
AND   T4.mem_end >= curdate()
)"); 

$lines = "";
$i = 0;
while ($row = mysql_fetch_assoc($query)) {
  $i++;
  $lines .= "$i\t".$row['usr_id']."\t".$row['FN'] . ' ' . $row['LN']."\t".$row['LO']."\n";
}

$encrypted2 = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key4), $lines, MCRYPT_MODE_CBC, md5(md5($key4))));
echo $encrypted2;
mysql_close($link);
?>

