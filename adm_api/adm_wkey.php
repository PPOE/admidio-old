<? 
include "config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$query = mysql_query("select G1.usr_login_name,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 12) as Email,
(select G2.usd_value from `adm_user_data` G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 33) as BackupEmail
from `adm_users` G1
where G1.usr_id IN (
select T1.usr_id
from `adm_users` T1 INNER JOIN `adm_user_data` T2
WHERE T1.usr_id = T2.usd_usr_id
AND   T2.usd_usf_id = 26
AND   T2.usd_value IS NOT NULL
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
  if ($row[1] == "mitglied@piratenpartei.at" && strlen($row[2]) > 4)
    $row[1] = $row[2];
  $lines .= $row[0]."\t".$row[1]."\n";
}

$encrypted2 = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($keyw), $lines, MCRYPT_MODE_CBC, md5(md5($keyw))));
echo $encrypted2;
mysql_close($link);
?>

