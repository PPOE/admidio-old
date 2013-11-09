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

<a href="table.php" target="_parent">Bundesl&auml;nder</a> &bull; <a href="nuts.php" target="_parent">NUTS-Regionen</a> &bull; <a href="members.php" target="_parent">Altersstatistik, etc.</a> <br />

<table class="sortable">

<tr>
<th> Land </th>
<th> Mitglied </th>
<th> Stimmrecht </th>
<th> stimmber. in&#160;% </th>
<th> Liquid </th>
<th> Liquid in&#160;% </th>
<th> Einwohner in 1000 </th>
<th> Mitgl. / Mio. Einw. </th>
<th> Fl&auml;che (km&sup2;) </th>
<th> Mitgl. / 1000 km&sup2;
</th></tr>
END;

$los    = array(38 => 'Burgenland', 40 => 'K&auml;rnten', 39 => 'Nieder&ouml;sterreich', 41 => 'Ober&ouml;sterreich', 42 => 'Salzburg', 43 => 'Steiermark', 44 => 'Tirol', 45 => 'Vorarlberg', 37 => 'Wien', 0 => 'Keines', 1 => 'Bund');
$people = array(0 => 0, 38 => 286,          40 => 558,       39 => 1617,               41 => 1417,             42 => 534,        43 => 1213,         44 => 714,     45 => 373,          37 => 1731);
$area   = array(0 => 0, 38 => 3962,         40 => 9536,      39 => 19178,              41 => 11982,            42 => 7154,       43 => 16401,        44 => 12648,   45 => 2601,         37 => 415);
$people[1] = array_sum($people);
$area[1] = array_sum($area);
foreach ($los as $num => $lo) {
  if ($num == 1)
  {
    $where = "1";
    $b = "<b>";
    $b2 = "</b>";
    $class = " class=\"sortbottom even\"\n";
  }
  else
  {
    $where = "LO = $num";
    $b = "";
    $b2 = "";
    $class = "";
  }
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE $where;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_members = $row[0];
  }
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE $where AND MB = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_zahlend = $row[0];
  }
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE $where AND (MB = 1 OR MBM14 = 1) AND Akk = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_stimmberechtigt = $row[0];
  }
  echo "<tr$class>\n";
  echo "<td><b>$lo</b></td>\n";
  echo "<td>$b$lo_members$b2</td>\n";
  echo "<td>$b$lo_zahlend$b2</td>\n";
  echo "<td>$b" . sprintf("%.1f", round(100 * $lo_zahlend / $lo_members,1)) . "%$b2</td>\n";
  echo "<td>$b$lo_stimmberechtigt$b2</td>\n";
  echo "<td>$b" . sprintf("%.1f", round(100 * $lo_stimmberechtigt / $lo_members,1)) . "%$b2</td>\n";
  echo "<td>$b" . $people[$num] . "$b2</td>\n";
  echo "<td>$b" . sprintf("%.1f", round(1000 * $lo_members / $people[$num],1)) . "$b2</td>\n";
  echo "<td>$b" . $area[$num] . "$b2</td>\n";
  echo "<td>$b" . sprintf("%.1f", round(1000 * $lo_members / $area[$num],1)) . "$b2</td>\n";
  echo "</tr>\n";
}

echo "</html>\n";

mysql_close($link);
?>

