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
<th> Bereich </th>
<th> Abonnenten </th>
<th> (davon unbest&auml;tigt) </th>
</th></tr>
END;

//$los    = array(2 => 'Burgenland', 4 => 'K&auml;rnten', 39 => 'Nieder&ouml;sterreich', 41 => 'Ober&ouml;sterreich', 42 => 'Salzburg', 43 => 'Steiermark', 44 => 'Tirol', 45 => 'Vorarlberg', 37 => 'Wien');
//$prefs = 1;
//if($bund == "bund") {$prefs += 1;}
//if($bgld == "bgld") {$prefs += 2;}
//if($ktn == "ktn") {$prefs += 4;}
//if($noe == "noe") {$prefs += 8;}
//if($ooe == "ooe") {$prefs += 16;}
//if($sbg == "sbg") {$prefs += 32;}
//if($stmk == "stmk") {$prefs += 64;}
//if($vlbg == "vlbg") {$prefs += 128;}
//if($w == "w") {$prefs += 512;}

  $users = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.users;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $users = $row[0];
  }
  $users_nc = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.users WHERE confirmed = 0;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $users_nc = $row[0];
  }

echo "<tr>";// class=\"sortbottom even\">\n";
echo "<td> <b>Newsletter</b> </td>\n";
echo "<td> <b>$users</b> </td>\n";
echo "<td> <b>$users_nc</b> </td>\n";
  
  $users = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.presse_users;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $users = $row[0];
  }
  $users_nc = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.presse_users WHERE confirmed = 0;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $users_nc = $row[0];
  }

echo "<tr>";// class=\"sortbottom even\">\n";
echo "<td> <b>Presseverteiler</b> </td>\n";
echo "<td> <b>$users</b> </td>\n";
echo "<td> <b>$users_nc</b> </td>\n";
echo "</tr></html>\n";

mysql_close($link);
?>

