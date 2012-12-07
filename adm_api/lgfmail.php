<? 
include "/var/www/adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$los    = array(38 => 'Burgenland', 40 => 'K&auml;rnten', 39 => 'Nieder&ouml;sterreich', 41 => 'Ober&ouml;sterreich', 42 => 'Salzburg', 43 => 'Steiermark', 44 => 'Tirol', 45 => 'Vorarlberg', 37 => 'Wien');

$mails = array(
37 => array("lgf-wien@piratenpartei.at"),
38 => array("lgf-burgenland@piratenpartei.at"),
39 => array("lgf-noe@piratenpartei.at"),
40 => array("lgf-kaernten@piratenpartei.at"),
41 => array("lgf-ooe@piratenpartei.at"),
42 => array("lgf-sbg@piratenpartei.at"),
43 => array("lgf-steiermark@piratenpartei.at"),
44 => array("lgf-tirol@piratenpartei.at"),
45 => array("lgf-vorarlberg@piratenpartei.at")
);

$refill = false;
$date = new DateTime("now");

////////////////////////////////////
// INFO TO LGF NEW MEMBER
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id,
(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 12) as Email,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO
from ppoe_mitglieder.adm_users G1
where G1.usr_id IN (
select T3.usr_id
from ppoe_mitglieder.adm_users T3 INNER JOIN ppoe_mitglieder.adm_members T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T3.usr_valid = 1
AND   T4.mem_rol_id = 2
AND   T4.mem_end >= curdate()
)) A WHERE (usr_id,LO) NOT IN (SELECT usr_id,LO FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $lo = $row["LO"];
  echo "Mail for $lo $id\n";
  foreach ($mails[$lo] AS $mail)
  {
    echo "NEW Mail to $mail $lo $id\n";
    exec("/var/www/adm_api/info_new.sh $mail $id");
  }
}
}

////////////////////////////////////
// INFO TO LGF LOST MEMBER
////////////////////////////////////
$query = mysql_query("SELECT * FROM ppoe_api_data.members WHERE (usr_id,LO) NOT IN (select G1.usr_id,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO
from ppoe_mitglieder.adm_users G1
where G1.usr_id IN (
select T3.usr_id
from ppoe_mitglieder.adm_users T3 INNER JOIN ppoe_mitglieder.adm_members T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T3.usr_valid = 1
AND   T4.mem_rol_id = 2
AND   T4.mem_end >= curdate()
)) A;");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $lo = $row["LO"];
  foreach ($mails[$lo] AS $mail)
  {
    echo "DEL Mail to $mail $lo $id\n";
    exec("/var/www/adm_api/info_del.sh $mail $id");
  }
}
}

////////////////////////////////////
// INFO TO MEMBER NOT AKK BUT MB
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id,
(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 12) as Email,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO,
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
)) A WHERE (usr_id,LO,MB) NOT IN (SELECT usr_id,LO,MB FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $lo = $row["LO"];
  $mail = $row["Email"];
  $mb = $row["MB"];
  $akk = $row["Akk"];
  if ($akk != 1 && $mb == 1) {
    echo "NOT AKK BUT MB Mail to $mail $lo $id $mb\n";
    exec("/var/www/adm_api/info_not_akk_but_mb.sh $mail $id");
  }
}
}


if ($refill == true)
{
$query = mysql_query("TRUNCATE ppoe_api_data.members;");

$query = mysql_query("INSERT INTO ppoe_api_data.members (usr_id, Email, LO, MB, Akk) SELECT * FROM (select G1.usr_id,
(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 12) as Email,
(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO,
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
)) A;");
}


$query = mysql_query("SELECT * FROM ppoe_mv_info.mv_statistik WHERE LO = 0 AND timestamp >= '" . date_format($date, 'Y-m-d 00:00:00') . "' AND timestamp <= '" . date_format($date, 'Y-m-d 23:59:59') . "';");
if ($query && ($row = mysql_fetch_array($query)))
{
}
else
{
foreach ($los as $num => $lo) {
  $lo_members = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE LO = $num;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_members = $row[0];
  }
  $lo_zahlend = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE LO = $num AND MB = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_zahlend = $row[0];
  }
  $lo_stimmberechtigt = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE LO = $num AND MB = 1 AND Akk = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_stimmberechtigt = $row[0];
  }
  $query = mysql_query("INSERT INTO ppoe_mv_info.mv_statistik (akk,members,users,LO,timestamp) VALUES (" . $lo_stimmberechtigt . ", " . $lo_zahlend . ", " . $lo_members . ", " . $num . ", '" . date_format($date, 'Y-m-d') . "');");
}

$query = mysql_query("select COUNT(*)
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

$zahlend = 0;

if ($query && $row = mysql_fetch_array($query)) {
  $zahlend = $row[0];
}

$query = mysql_query("select COUNT(*)
from `adm_users` G1
where G1.usr_id IN (
select T1.usr_id
from `adm_users` T1 INNER JOIN `adm_user_data` T2
WHERE T1.usr_id = T2.usd_usr_id
AND   T2.usd_usf_id = 35
AND   T2.usd_value <= curdate()
)
and G1.usr_id IN (
select T3.usr_id
from `adm_users` T3 INNER JOIN `adm_members` T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T4.mem_rol_id = 26
AND   T4.mem_end >= curdate()
) and G1.usr_id IN (
select T3.usr_id
from `adm_users` T3 INNER JOIN `adm_members` T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T3.usr_valid = 1
AND   T4.mem_rol_id = 2
AND   T4.mem_end >= curdate()
)");

$stimmberechtigt = 0;

if ($query && $row = mysql_fetch_array($query)) {
  $stimmberechtigt = $row[0];
}

$query = mysql_query("select COUNT(*)
from `adm_users` G1
where G1.usr_id IN (
select T3.usr_id
from `adm_users` T3 INNER JOIN `adm_members` T4
WHERE T3.usr_id = T4.mem_usr_id
AND   T3.usr_valid = 1
AND   T4.mem_rol_id = 2
AND   T4.mem_end >= curdate()
)");

$gesamt = 0;

if ($query && $row = mysql_fetch_array($query)) {
  $gesamt = $row[0];
}

$query = mysql_query("INSERT INTO ppoe_mv_info.mv_statistik (akk, members,users,LO,timestamp) VALUES (" . $stimmberechtigt . ", " . $zahlend . ", " . $gesamt . ", 0, '" . date_format($date, 'Y-m-d') . "');");
}

mysql_close($link);

echo date_format($date, 'Y-m-d H:i:s') . "\n";

?>

