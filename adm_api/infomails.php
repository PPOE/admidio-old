<? 
include "/var/www/adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$date = new DateTime("now");

$query = mysql_query("select G1.usr_id,
(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 12) as Email,
CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26 AND G2.usd_value >= curdate() LIMIT 1) IS NULL THEN 0 ELSE 1 END AS MB,
CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 35 AND G2.usd_value <= curdate() LIMIT 1) IS NULL THEN 0 ELSE 1 END AS Akk
from ppoe_mitglieder.adm_users G1
where G1.usr_id IN (
select T3.usr_id
from ppoe_mitglieder.adm_users T3 INNER JOIN ppoe_mitglieder.adm_members T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T3.usr_valid = 1
AND   T4.mem_rol_id = 2
AND   T4.mem_end >= curdate()
);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $id = $row["usr_id"];
  $mail = $row["Email"];
  $mb = $row["MB"];
  $akk = $row["Akk"];
  if ($mb != 1) {
    echo "MB Info to $id\n";
  }
  else if ($akk != 1) {
    echo "Akk Info to $id\n";
  }
}
}

mysql_close($link);

echo date_format($date, 'Y-m-d H:i:s') . "\n";

?>

