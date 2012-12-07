<? 
include "config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);
mysql_set_charset('utf8');
$query = mysql_query("select G2.usd_value,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 35 AND G2.usd_value <= curdate()) as AkkDate
from `adm_users` G1 LEFT JOIN `adm_user_data` G2 ON G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 37 where G1.usr_id IN ( select T3.usr_id from `adm_users` T3 INNER JOIN `adm_members` T4 WHERE T3.usr_id = T4.mem_usr_id AND   T3.usr_valid = 1 AND   T4.mem_rol_id = 2 AND   T4.mem_end >= curdate() ) AND G2.usd_value IS NOT NULL;"); 

$lines = "";
$i = 0;
while ($row = mysql_fetch_assoc($query)) {
  $i++;
  $lines .= $row['usd_value']."\t".$row['LO']."\t".$row['AkkDate']."\n";
//  echo $row['usd_value'];
}
$encrypted2 = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($keyf), $lines, MCRYPT_MODE_CBC, md5(md5($keyf))));
echo $encrypted2;
mysql_close($link);
?>

