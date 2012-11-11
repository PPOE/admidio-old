<?php
exit 0;
include_once('config.inc.php');
include_once('functions.inc.php');
include_once('mysql.class.php');

// Datenbank
$db = new MySQL();

$states = get_states($db);							// Bundesländer
$fields = get_field_desc($db);						// Datenfeldbeschreibungen
$list = get_member_data(get_member_ids($db),$db);	// User


// Zeige auch Mitglieder ohne Stimmrecht
$q_membersonly = false;
if(isset($_GET['o']) && strlen($_GET['o'])>0 && $_GET['o']=="on"){
	$q_membersonly = true;
}
if($q_membersonly===false){
	$list = list_members($list);
}


// Länder Filter
$q_land = false;
if(isset($_GET['l']) && strlen($_GET['l'])>0){
	$q_land = $_GET['l'];
}
if($q_land!==false){
	$state = $states[$q_land];
	if($q_land==="0"){
		foreach($states as $state){
			$list = filter_users_neg($list,"state",$state);
		}
	} elseif($q_land!=="10") {
		$list = filter_users($list,"state",$state);
	}
} else {
	$q_land = "10";
	$state = $states[$q_land];
}

// Mailadressen Filter
$q_mail = false;
$hide_mail = array(
	"bgf@piratenpartei.at",
	"mitglied@piratenpartei.at"
);
if(isset($_GET['m']) && strlen($_GET['m'])>0 && $_GET['m']=="on"){
	$q_mail = true;
}
if($q_mail!==false){
	foreach($hide_mail as $hmail){
		$list = filter_users_neg($list,"email",$hmail);
	}
}

// Geburtstage
$q_birthday = false;
if(isset($_GET['b']) && strlen($_GET['b'])>0 && $_GET['b']=="on"){
	$q_birthday = true;
}
if($q_birthday!==false){
	$list = filter_users_neg($list,"birthday","1800-01-01");
}

// Feld-Filter
foreach($fields as $id=>$field){
	$fid = 'f-'.$id;
	if(isset($_GET[$fid]) && strlen($_GET[$fid])>0 && $_GET[$fid]=="on"){
		$filtercols[] = $id;
	}
}
foreach($filtercols as $filterid){
	$filter = strtolower($fields[$filterid]);
	$list = list_filter_col($list,$filter);
}

$headers = get_table_headers($list);

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Statistik - Landesorganisationen</title>
<link rel="stylesheet" type="text/css" href="list.css">
</head>

<body>
<div id="container">
<?php
// Zusammenfassung der Datenanzeige
echo "<div id=\"left\">";
echo "<h3>".count($list)." Mitglieder in ".utf8_encode($state)."</h3>";

// Datentabelle
echo "<table id=\"result\">";
// Tabellenkopf
echo "<th>id</th>";
foreach($headers as $h){
	echo "<th>".str_replace("_"," ",$h)."</th>";
}

// Benutzerdaten
foreach($list as $id=>$user){
	echo "<tr><td>$id</td>";
	foreach($user as $key=>$val){
		echo "<td>".utf8_encode($val)."</td>";
	}
	echo "</tr>\r\n";
}
echo "</table>";
echo "</div>";

// Auswahlformular
echo "<div id=\"right\">";
echo "<form method=\"get\" action=\"\">";
echo "Landesorganisation <select name=\"l\" onChange=\"submit()\">";
foreach($states as $k=>$v){
	echo "	<option value=\"$k\"";
	if($q_land == $k){echo " selected";}
	echo ">".utf8_encode($v)."</option>";
}
echo "</select>";
echo "<br>";
echo "Felder ausblenden: ";
echo "<br>";
foreach($fields as $id=>$field){
	echo "<input type=\"checkbox\" name=\"f-".$id."\" onChange=\"submit()\"";
	if(in_array($id,$filtercols)){echo " checked";}
	echo "> <span class=\"f\">".strtolower(str_replace("_"," ",$field))."</span>";
	echo "<br>";
}
echo "<br>";
echo "<input type=\"checkbox\" name=\"m\" onChange=\"submit()\"";
if($q_mail){echo " checked";}
echo ">";
echo "<span title=\"Blendet Adressen ";
for($c=0;$c<count($hide_mail);$c++){echo $hide_mail[$c];if($c<count($hide_mail)-1){echo ", ";}}
echo " aus\" class=\"m\">";
echo "Nur mit g&uuml;ltiger Mailadresse</span>";
echo "<br>";
echo "<input type=\"checkbox\" name=\"b\" onChange=\"submit()\"";
if($q_birthday){echo " checked";}
echo ">";
echo "<span class=\"b\">Nur mit Geburtsdatum</span>";
echo "<br>";
echo "<input type=\"checkbox\" name=\"o\" onChange=\"submit()\"";
if($q_membersonly){echo " checked";}
echo ">";
echo "<span class=\"o\">Auch Mitglieder ohne Stimmrecht</span>";
echo "<br>";
echo "<input type=\"submit\">";
echo "</form>";
echo "</div>";

?>
</div>
</body>
</html>
