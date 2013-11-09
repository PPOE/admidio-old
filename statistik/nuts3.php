<? 
include "../adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

echo <<<END
<!DOCTYPE html>
<html lang="de-at" dir="ltr" class="client-nojs">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>PLZ zu NUTS-3</title>
<link rel="stylesheet" type="text/css" media="all" href="style.css">
<script type="text/javascript" src="sorttable.js"></script>
</head>
<body>
<table class="sortable">

<tr>
<th> PLZ </th>
<th> Region </th>
<th> Bezeichnung </th>
</th></tr>
END;

$query = mysql_query("SELECT * FROM nutsplz LEFT JOIN nutsdata ON id = nuts ORDER BY plz;");
while ($query && ($row = mysql_fetch_assoc($query)))
{
  echo "<tr><td>" . $row['plz'] . "</td><td>" . $row['id'] . "</td><td>" . $row['name'] . "</td></tr>\n";
}
echo "</tr></html>\n";

mysql_close($link);
?>

