<? 
include "../adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

echo <<<END
<!DOCTYPE html>
<html lang="de-at" dir="ltr" class="client-nojs">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>Mitglieder Tabelle</title>
<link rel="stylesheet" type="text/css" media="all" href="style.css">
<script type="text/javascript" src="sorttable.js"></script>
</head>
<body>
<table class="sortable">

<tr>
<th> Landesorganisation </th>
<th> Mitglieder </th>
<th> zahlend </th>
<th> zahlend in&#160;% </th>
<th> stimmberechtigt </th>
<th> stimmber. in&#160;% </th>
<th> Einwohner in 1000 </th>
<th> Mitgl. pro Mio. Einw. </th>
<th> Fl&auml;che (km&sup2;) </th>
<th> Mitglieder pro 1000 km&sup2;
</th></tr>
END;

$los    = array(38 => 'Burgenland', 40 => 'K&auml;rnten', 39 => 'Nieder&ouml;sterreich', 41 => 'Ober&ouml;sterreich', 42 => 'Salzburg', 43 => 'Steiermark', 44 => 'Tirol', 45 => 'Vorarlberg', 37 => 'Wien');
$people = array(38 => 286,          40 => 558,       39 => 1617,               41 => 1417,             42 => 534,        43 => 1213,         44 => 714,     45 => 373,          37 => 1731);
$area   = array(38 => 3962,         40 => 9536,      39 => 19178,              41 => 11982,            42 => 7154,       43 => 16401,        44 => 12648,   45 => 2601,         37 => 415);

foreach ($los as $num => $lo) {
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE LO = $num;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_members = $row[0];
  }
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE LO = $num AND MB = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_zahlend = $row[0];
  }
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE LO = $num AND MB = 1 AND Akk = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_stimmberechtigt = $row[0];
  }
  echo "<tr>\n";
  echo "<td><b>$lo</b></td>\n";
  echo "<td>$lo_members</td>\n";
  echo "<td>$lo_zahlend</td>\n";
  echo "<td>" . sprintf("%.1f", round(100 * $lo_zahlend / $lo_members,1)) . "%</td>\n";
  echo "<td>$lo_stimmberechtigt</td>\n";
  echo "<td>" . sprintf("%.1f", round(100 * $lo_stimmberechtigt / $lo_members,1)) . "%</td>\n";
  echo "<td>" . $people[$num] . "</td>\n";
  echo "<td>" . sprintf("%.1f", round(1000 * $lo_members / $people[$num],1)) . "</td>\n";
  echo "<td>" . $area[$num] . "</td>\n";
  echo "<td>" . sprintf("%.1f", round(1000 * $lo_members / $area[$num],1)) . "</td>\n";
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

$gesamt_people = array_sum($people);
$gesamt_area = array_sum($area);

echo "<tr class=\"sortbottom even\">\n";
echo "<td> <b>Bund</b> </td>\n";
echo "<td> <b>$gesamt</b> </td>\n";
echo "<td> <b>$zahlend</b> </td>\n";
echo "<td> <b>" . sprintf("%.1f", round(100 * $zahlend / $gesamt,1))  . "%</b> </td>\n";
echo "<td> <b>$stimmberechtigt</b> </td>\n";
echo "<td> <b>" . sprintf("%.1f", round(100 * $stimmberechtigt / $gesamt,1))  . "%</b> </td>\n";
echo "<td> <b>" . $gesamt_people . "</b> </td>\n";
echo "<td> <b>" . sprintf("%.1f", round(100 * $gesamt / $gesamt_people,1)) . "</b> </td>\n";
echo "<td> <b>" . $gesamt_area . "</b> </td>\n";
echo "<td> <b>" . sprintf("%.1f", round(1000 * $gesamt / $gesamt_area,1)) . "</b> </td>\n";
echo "</tr></html>\n";

mysql_close($link);
?>

