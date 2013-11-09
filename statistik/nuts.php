<? 
include "../adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

echo <<<END
<!DOCTYPE html>
<html lang="de-at" dir="ltr" class="client-nojs">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>Mitglieder Tabelle (NUTS-3)</title>
<link rel="stylesheet" type="text/css" media="all" href="style.css">
<script type="text/javascript" src="sorttable.js"></script>
</head>
<body>

<a href="table.php">Bundesl&auml;nder</a> &bull; <a href="nuts.php">NUTS-Regionen</a> &bull; <a href="members.php">Altersstatistik, etc.</a> <br />

<table class="sortable">

<tr>
<th> Region </th>
<th> Mitglied </th>
<th> zahlend </th>
<th> zahlend in&#160;% </th>
<th> Stimmrecht </th>
<th> stimmber. in&#160;% </th>
<th> Einwohner in 1000 </th>
<th> Mitgl. / 1000 Einw. </th>
<th> Fl&auml;che (km&sup2;) </th>
<th> Mitgl. / 1000 km&sup2;
</th></tr>
END;

$query = mysql_query("SELECT * FROM nutsdata;");
while ($query && ($row = mysql_fetch_assoc($query)))
{
  $regions[] = $row;
}

$gesamt_area = 0.0;
$gesamt_people = 0;

foreach ($regions as $region) {
  $num = $region['id'];
  $query = mysql_query("SELECT COUNT(*) FROM (SELECT usr_id FROM ppoe_api_data.members LEFT JOIN adm_user_data ON usd_usr_id = usr_id AND usd_usf_id IN (4,39) LEFT JOIN nutsplz ON usd_value = plz WHERE nuts = '$num' GROUP BY usr_id) A;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_members = $row[0];
  }
  $query = mysql_query("SELECT COUNT(*) FROM (SELECT usr_id FROM ppoe_api_data.members LEFT JOIN adm_user_data ON usd_usr_id = usr_id AND usd_usf_id IN (4,39) LEFT JOIN nutsplz ON usd_value = plz WHERE nuts = '$num' AND MB = 1 GROUP BY usr_id) A;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_zahlend = $row[0];
  }
  $query = mysql_query("SELECT COUNT(*) FROM (SELECT usr_id FROM ppoe_api_data.members LEFT JOIN adm_user_data ON usd_usr_id = usr_id AND usd_usf_id IN (4,39) LEFT JOIN nutsplz ON usd_value = plz WHERE nuts = '$num' AND MB = 1 AND Akk = 1 GROUP BY usr_id) A;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_stimmberechtigt = $row[0];
  }
  echo "<tr>\n";
  echo "<td><b>{$region['name']} ($num)</b></td>\n";
  echo "<td>$lo_members</td>\n";
  echo "<td>$lo_zahlend</td>\n";
  echo "<td>" . sprintf("%.1f", round(100 * $lo_zahlend / $lo_members,1)) . "%</td>\n";
  echo "<td>$lo_stimmberechtigt</td>\n";
  echo "<td>" . sprintf("%.1f", round(100 * $lo_stimmberechtigt / $lo_members,1)) . "%</td>\n";
  echo "<td>" . round($region['people'] / 1000,0) . "</td>\n";
  $gesamt_people += $region['people'] / 1000.0;
  echo "<td>" . sprintf("%.3f", round($lo_members / ($region['people'] / 1000.0),3)) . "</td>\n";
  echo "<td>" . round($region['area'],0) . "</td>\n";
  $gesamt_area += $region['area'];
  echo "<td>" . sprintf("%.1f", round(1000 * $lo_members / $region['area'],1)) . "</td>\n";
  echo "</tr>\n";
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

echo "<tr class=\"sortbottom even\">\n";
echo "<td> <b>Bund</b> </td>\n";
echo "<td> <b>$gesamt</b> </td>\n";
echo "<td> <b>$zahlend</b> </td>\n";
echo "<td> <b>" . sprintf("%.1f", round(100 * $zahlend / $gesamt,1))  . "%</b> </td>\n";
echo "<td> <b>$stimmberechtigt</b> </td>\n";
echo "<td> <b>" . sprintf("%.1f", round(100 * $stimmberechtigt / $gesamt,1))  . "%</b> </td>\n";
echo "<td> <b>" . round($gesamt_people,0) . "</b> </td>\n";
echo "<td> <b>" . sprintf("%.2f", round($gesamt / $gesamt_people,3)) . "</b> </td>\n";
echo "<td> <b>" . round($gesamt_area,0) . "</b> </td>\n";
echo "<td> <b>" . sprintf("%.1f", round(1000 * $gesamt / $gesamt_area,1)) . "</b> </td>\n";
echo "</tr></html>\n";

mysql_close($link);
?>

