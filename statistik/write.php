<?php
include_once('functions.inc.php');
include_once('config.statistik.php');
include_once('mysql.class.php');

$db = new MySQL();

if(isset($_GET['action']) && strlen($_GET['action'])>0 ){
	$action = $_GET['action'];
}
if($action){
	if($action=="write"){
		if(isset($_GET['write']) && strlen($_GET['write'])>0 ){
			$write = $_GET['write'];
		}
		if($write){
			$wa = explode(";",$write);
			foreach($wa as $wl){
				$a = explode(":",$wl);
				$data[$a[0]]=$a[1];
			}
			include_once('functions.inc.php');
			include_once('config.statistik.php');
			include_once('mysql.class.php');
			$db = new MySQL();
			$last_id = last_update_id($db,"mv_statistik") + 1 ;
			$last = last_update_time($db,"mv_statistik");
			$nextallow = $last + 86400; // 24 Stunden
			$now = time();

			if($now > $nextallow){
				$sql = "INSERT INTO mv_statistik (id,users,members,description) VALUES ('" . $last_id . "',".$data['users'].",".$data['members'].",'".$data['quota']."');";
				$db->ExecuteSQL($sql);
			}
		}
	}
	if($action=="read"){
		if(isset($_GET['read']) && strlen($_GET['read'])>0 ){
			$read = $_GET['read'];
		}
		if($read){
			if($read=="lastupdate"){
				echo last_update_time($db,"mv_statistik");
			}
		}
	}
}

?>