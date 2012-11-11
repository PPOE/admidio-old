<?php
/*
 functions.php
*/

function count_members($a_users,$x = 0){
	$membercount = 0;
	$now = time();
	foreach($a_users as $user){
		$bis = strtotime($user["bezahlt_bis"]);
		if( $now < ($bis + $x * 1000) ) {
			$membercount++;
		}
	}
	return $membercount;
}

function list_filter_col($list,$filter){
	foreach($list as $id=>$l){
		foreach($l as $k=>$v){
			if($k!==$filter){
				$user[$k]=$v;
			}
		}
		$return[$id]=$user;
	}
	return $return;
}


function filter_users($list,$category,$crit){
	foreach($list as $id=>$user){
		if($user[$category] == $crit){
			$newlist[$id] = $user;
		}
	}
	return $newlist;
}
function filter_users_neg($list,$category,$crit){
	foreach($list as $id=>$user){
		if($user[$category] !== $crit){
			$newlist[$id] = $user;
		}
	}
	return $newlist;
}

function filter_users_land($list,$land){
	foreach($list as $id=>$user){
		if(is_null($land)){
			if(is_null($user["state"])){
				$newlist[$id] = $user;
			}
		} else {
			if($user["state"] === $land){
				$newlist[$id] = $user;
			}
		}
	}
	return $newlist;
}

function list_members($users,$x = 0){
	$now = time();
	foreach($users as $id=>$user){
		$bis = strtotime($user["bezahlt_bis"]);
		if( $now < ($bis + $x) ) {
			$members[$id] = $user;
		}
	}
	return $members;
}

function list_mails($users){
	$not_working = array("mitglied@piratenpartei.at","bgf@piratenpartei.at",NULL);
	foreach($users as $id=>$user){
		$mailadress = $user["email"];
		if( !in_array($mailadress,$not_working) ){
			$mails[$id] = $user["email"];
		}
	}
	return $mails;
}

function get_state_list($db){
	$sql_states = "SELECT usf_value_list FROM adm_user_fields WHERE usf_id = 23";
	$db->ExecuteSQL($sql_states);
	$statelist = $db->ArrayResult();
	$statelist = explode("\r\n",$statelist["usf_value_list"]);
	return $statelist;
}

function get_states($db){
	$list[0] = "Ohne";
	// Get Bundesländer
	$statelist = get_state_list($db);
	foreach($statelist as $k=>$sl){$list[$k+1]=$sl;}
	$list[$k+2] = "Alle";
	return $list;
}

function get_field_desc($db){
	// Get field descriptions
	$sql_userfields = "SELECT usf_id, usf_type, usf_name_intern, usf_name, usf_value_list FROM adm_user_fields;";
	$db->ExecuteSQL($sql_userfields);
	$r_userfields = $db->ArrayResults();
	foreach($r_userfields as $uf){
		$ufname = $uf["usf_name_intern"];
		$ufid = $uf["usf_id"];
		$fields[$ufid] = $ufname;
	}
	return $fields;
}

function get_member_ids($db){
	// Get member user ids from database
	$sql_mem = "SELECT mem_id, mem_usr_id FROM adm_members WHERE mem_rol_id = 2 ORDER BY mem_id ASC;";
	$db->ExecuteSQL($sql_mem);
	$result_mem = $db->ArrayResults();
	foreach($result_mem as $res_mem){
		$mem_id = $res_mem["mem_id"];
		$mem_usr_id = $res_mem["mem_usr_id"];	
		$ids[$mem_id] = $mem_usr_id;
	}
	return $ids;
}

function get_member_data($ids,$db){
	foreach($ids as $usr_id){	
		// User Login Name
		$sql_user = "SELECT usr_login_name FROM adm_users WHERE usr_id = ".$usr_id.";";
		$db->ExecuteSQL($sql_user);
		$result_usr_login = $db->ArrayResult();
		$usr_login_name = $result_usr_login["usr_login_name"];
		$n_nickname = $usr_login_name;
		// User Data
		$sql_userdata = "SELECT usd_usf_id, usd_value FROM adm_user_data WHERE usd_usr_id = ".$usr_id.";";
		$db->ExecuteSQL($sql_userdata);
		$result_userdata = $db->ArrayResults();
		$user = create_user_entry($result_userdata,$usr_id,$usr_login_name,$db);
		$users[$usr_id] = $user;
	}
	return $users;
}

function create_user_entry($result_userdata,$usr_id,$n_nickname,$db){
	$fields = get_field_desc($db);
	$statelist = get_states($db);
	$usd_value = NULL;
	$n_state = $usd_value;
	$n_lastname = $usd_value;
	$n_firstname = $usd_value;
	$n_address = $usd_value;
	$n_postcode = $usd_value;
	$n_city = $usd_value;
	$n_country = $usd_value;
	$n_birthday = $usd_value;
	$n_email = $usd_value;
	$n_am = $usd_value;
	$n_bis = $usd_value;
	$n_betrag = $usd_value;
	$n_status = $usd_value;
	$n_phone = $usd_value;
	$n_mobile = $usd_value;
	$n_fax = $usd_value;
	$n_gender = $usd_value;
	$n_website = $usd_value;


	foreach($result_userdata as $res_userdata){
		$usd_usf_id = $res_userdata["usd_usf_id"];
		$usd_field = $fields[$usd_usf_id];
		$usd_value = $res_userdata["usd_value"];
		switch($usd_field){
			case("LAST_NAME"):
			$n_lastname = $usd_value;
			break;
			case("FIRST_NAME"):
			$n_firstname = $usd_value;
			break;
			case("ADDRESS"):
			$n_address = $usd_value;
			break;
			case("POSTCODE"):
			$n_postcode = $usd_value;
			break;
			case("CITY"):
			$n_city = $usd_value;
			break;
			case("COUNTRY"):
			$n_country = $usd_value;
			break;
			case("BIRTHDAY"):
			$n_birthday = $usd_value;
			break;
			case("EMAIL"):
			$n_email = $usd_value;
			break;
			case("NICKNAME"):
			$n_nickname = $usd_value;
			break;
			case("BEZAHLT_AM"):
			$n_am = $usd_value;
			break;
			case("BEZAHLT_BIS"):
			$n_bis = $usd_value;
			break;
			case("BETRAG"):
			$n_betrag = $usd_value;
			break;
			case("STATUS"):
			$n_status = $usd_value;
			break;
			case("STAAT"):
			if($usd_value>0 && $usd_value<10){
				$n_staat = $statelist[$usd_value];
			} else {
				$n_staat = $usd_value;
			}
			break;
			case("PHONE"):
			$n_phone = $usd_value;
			break;
			case("MOBILE"):
			$n_mobile = $usd_value;
			break;
			case("FAX"):
			$n_fax = $usd_value;
			break;
			case("GENDER"):
			$n_gender = $usd_value;
			break;
			case("WEBSITE"):
			$n_website = $usd_value;
			break;
		}
	}
	$usera = array(
		'login'=>$n_nickname,
		'nickname'=>$n_nickname,
		'first_name'=>$n_firstname,
		'last_name'=>$n_lastname,
		'email'=>$n_email,
		'address'=>$n_address,
		'postcode'=>$n_postcode,
		'city'=>$n_city,
		'state'=>$n_staat,
		'country'=>$n_country,
		'birthday'=>$n_birthday,
		'bezahlt_am'=>$n_am,
		'bezahlt_bis'=>$n_bis,
		'betrag'=>$n_betrag,
		'status'=>$n_status,
		'phone'=>$n_phone,
		'mobile'=>$n_mobile,
		'fax'=>$n_fax,
		'gender'=>$n_gender,
		'website'=>$n_website
	);
	return $usera;
}

function get_table_headers($list){
	$c=0;
	foreach($list as $user){
		foreach($user as $key=>$val){
			$return[]= $key;
		}
		$c++; if($c>0){ break; }
	}
	return $return;
}

function last_update_id($db,$table="test_anzahl"){
	$sql_last = "SELECT max(id) FROM ".$table."";
	$db->ExecuteSQL($sql_last);
	$res_last = $db->ArrayResult();
	$last_id = $res_last["max(id)"];
	return $last_id;
}

function last_update_time($db,$table="test_anzahl"){	
	$sql_lasttime = "SELECT timestamp FROM ".$table." WHERE id = ".last_update_id($db,$table).";";
	$db->ExecuteSQL($sql_lasttime);
	$res_lasttime = $db->ArrayResult();
	$last_update_a = explode(" ",$res_lasttime["timestamp"]);
	$last_update = strtotime($last_update_a[0]);
	return $last_update;
}


function get_last_id($db_pre_write,$dblink){
	$dbwrite = mysql_select_db($db_name_write,$dblink) or die(mysql_error());
	$sql_last = "SELECT max(id) FROM ".$db_pre_write."anzahl";
	$result_last = mysql_query($sql_last) or die(mysql_error());
	while($res_last = mysql_fetch_row($result_last)){
		$last_id = $res_last[0];
	}
	return $last_id;
}

function get_last_timestamp($db_pre_write,$last_id,$dblink){
	$dbwrite = mysql_select_db($db_name_write,$dblink) or die(mysql_error());
	$sql_lasttime = "SELECT timestamp FROM ".$db_pre_write."anzahl WHERE id = ".$last_id.";";
	$result_lasttime = mysql_query($sql_lasttime) or die(mysql_error());
	while($res_lasttime = mysql_fetch_row($result_lasttime)){
		$last_update_a = explode(" ",$res_lasttime[0]);
		$last_update = strtotime($last_update_a[0]);
	}
	return $last_update;
}



?>