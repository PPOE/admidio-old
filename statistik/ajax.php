<?php
include_once('config.inc.php');
include_once('functions.inc.php');
include_once('mysql.class.php');

if ($_GET) {
// Prüfe Variablen
	if($_GET['l']){
		$l = $_GET['l'];
	}
	
	if($_GET['m']){
		$m = $_GET['m'];
	}
	echo $l." ".$m;
	
	$db = new MySQL();			// Datenbank
	$states = get_states($db);	// Bundesländer
	$users = get_member_data(get_member_ids($db),$db);	// User
	$list = list_members($users);						// Mitglieder
	

// Länder Filter
if($l!==false){
	$state = $states[$l];
	if($l==="0"){
		$list = filter_users_land($list,NULL);
	} elseif($l!=="10") {
		$list = filter_users($list,"state",$state);
	}
} else {
	$l = "10";
	$state = $states[$l];
}

// Mailadressen Filter
if(isset($m) && strlen($m)>0 && $m=="1"){
	$m = true;
} else {
	$m = false;
}
if($m!==false){
	$hide_mail = array(
		"bgf@piratenpartei.at",
		"mitglied@piratenpartei.at"
	);
	foreach($hide_mail as $hmail){
		$list = filter_users_neg($list,"email",$hmail);
	}
}
echo json_encode($list);

/*

// Tabellenkopf
echo "<th>id</th>".get_table_headers($list)."\r\n";

// Benutzerdaten
foreach($list as $id=>$user){
	echo "<tr><td>$id</td>";
	foreach($user as $key=>$val){
		echo "<td>".$val."</td>";
	}
	echo "</tr>\r\n";
}
*/

}
?>