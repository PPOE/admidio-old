<?php
include_once('config.inc.php');
include_once('functions.inc.php');
include_once('mysql.class.php');

// Datenbank
$db = new MySQL();

$states = get_states($db);							// Bundesländer
$fields = get_field_desc($db);						// Datenfeldbeschreibungen
$list = get_member_data(get_member_ids($db),$db);	// User
$starttime = time();

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

// Länder
foreach($list as $st){
	$lo = $st["state"];
	$st_i[]=$lo;
}
$ldb = get_state_list($db);
$laender = array();
foreach($st_i as $land){
	foreach($ldb as $id=>$li){
		if($land==$li){
			$laender[$land]++;
		}
	}
}

// Farben
$colors = array(
	'gelb'=>array(255,220,0),
	'orange'=>array(249,178,0),
	'hellrot'=>array(233,95,78),
	'dunkelrot'=>array(219,0,64),
	'schwarz'=>array(0,0,0),
	'violett'=>array(76,37,130),
	'blau'=>array(69,94,143)
);

sort($laender,"SORT_ASC");

$datastr = "";
foreach($laender as $name=>$count){
	$n = utf8_encode($name);
	if($n=="Kärnten")			:	$col = $colors["hellrot"];	endif;
	if($n=="Burgenland")		:	$col = $colors["dunkelrot"];		endif;
	if($n=="Tirol")				:	$col = $colors["violett"];	endif;
	if($n=="Vorarlberg")		:	$col = $colors["orange"];endif;
	if($n=="Oberösterreich")	:	$col = $colors["schwarz"];	endif;
	if($n=="Niederösterreich")	:	$col = $colors["dunkelrot"];endif;
	if($n=="Salzburg")			:	$col = $colors["gelb"];	endif;
	if($n=="Steiermark")		:	$col = $colors["blau"];		endif;
	if($n=="Wien")				:	$col = $colors["schwarz"];	endif;
	$color = "rgba(".$col[0].",".$col[1].",".$col[2].",0.8)";
	$datastr .= "{ label: \"";
	$datastr .= utf8_encode($name);
	$datastr .= "\",  data: ";
	$datastr .= $count;
	/*
	$datastr .= ", color: \"";
	$datastr .= $color;
	$datastr .= "\" ";
	*/
	$datastr .= "},\r\n";
}
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="ltr" lang="de-DE" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="ltr" lang="de-DE" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="ltr" lang="de-DE" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="ltr" lang="de-DE" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="ltr" lang="de-DE"> <!--<![endif]-->
<head>
<meta charset="UTF-8">
<title>Statistik - Landesorganisationen</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta http-equiv="cleartype" content="on">
<link rel="shortcut icon" href="favicon.ico">
<link rel="stylesheet" type="text/css" media="all" href="style.css">
<link rel="stylesheet" type="text/css" href="list.css">
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.pie.js"></script>
<script type="text/javascript">
$(function () {
	var data = [
<?php echo $datastr; ?>
	];
    $.plot($("#grafik"), data, 
	{
		series: {
			pie: { 
				show: true,
				radius: 1,
				label: {
                    show: true,
                    radius: 1,
                    formatter: function(label, series){
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                    },
                    background: { opacity: .5 }
                }				
			}
		}
	});
});
</script>
</head>
<body class="page page-parent page-template-default">
	<!-- Start Header -->
	<div class="section header">
		<div class="row">
			<div class="branding">
				<a href="http://piratenpartei.at/" title="Link zur Startseite" rel="home" class="logo">
					<img src="assets/logo.png" alt="Logo Piratenpartei Österreichs">
				</a>
				<h1 class="visuallyhidden">Piratenpartei Österreichs</h1>
				<p class="visuallyhidden"></p>
			</div>
		</div>
	</div>
	<!-- Ende Header -->
	<!-- Start Breadcrumbs -->
	<div class="section breadcrumbs">
		<div class="row">
			<div class="skin">
				<div id="crumbs"><span class="current"><?php echo $page_title; ?></span></div>
			</div>
		</div>
	</div>
	<!-- Ende Breadcrumbs -->
	<div class="section content">
		<div class="row">
			<div class="content-primary" style="width:100%">
				<div class="skin">
					<div class="post">
						<div class="post-title">
							<h1><?php echo $page_title; ?></h1>
						</div>
						<div class="post-info">
							<div class="cal-icon">
								<span class="day"><?php echo date("d",$starttime); ?></span>
								<span class="month"><?php echo date("m",$starttime); ?>.</span>
								<span class="year"><?php echo date("Y",$starttime); ?></span>
							</div>
						</div>
						<div class="post-entry">
							<!-- Start Text -->
								<?php
								// Zusammenfassung der Datenanzeige
								echo "<div id=\"left\">";
								echo "<h3>".count($list)." Mitglieder</h3>";
								
								echo "<div id=\"grafik\"></div>";
								
								echo "</div>";
								
								// Auswahlformular
								echo "<div id=\"plotbox\">";
								echo "<form method=\"get\" action=\"\">";
								echo "<input type=\"checkbox\" name=\"m\" onChange=\"submit()\"";
								if($q_mail){echo " checked";}
								echo ">";
								echo "<span title=\"Blendet Adressen ";
								for($c=0;$c<count($hide_mail);$c++){echo $hide_mail[$c];if($c<count($hide_mail)-1){echo ", ";}}
								echo " aus\" class=\"plotbox-m\">";
								echo "Nur mit g&uuml;ltiger Mailadresse</span>";
								echo "<br>";
								echo "<input type=\"checkbox\" name=\"b\" onChange=\"submit()\"";
								if($q_birthday){echo " checked";}
								echo ">";
								echo "<span class=\"plotbox-b\">Nur mit Geburtsdatum</span>";
								echo "<br>";
								echo "<input type=\"checkbox\" name=\"o\" onChange=\"submit()\"";
								if($q_membersonly){echo " checked";}
								echo ">";
								echo "<span class=\"plotbox-o\">Auch Mitglieder ohne Stimmrecht</span>";
								echo "<br>";
								echo "<input type=\"submit\">";
								echo "</form>";
								echo "</div>";
								
								?>
							<hr>
				        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="section footer">
		<div class="row">
			<div class="first-footer-widget-area">
				<div class="skin">
					<div class="widget">
						<h3 class="widget-title">Piratenpartei Österreichs</h3>
						<p>Piratenpartei Österreichs, Lange Gasse 1/4, 1080 Wien</p>
						<p>Für den Inhalt verantwortlich: Bundesgeschäftsführung, <a href="mailto:bgf@piratenpartei.at" style="display:inline; background:none; padding:0;">bgf@piratenpartei.at</a></p>
					</div>
				</div>
			</div>
			<div class="second-footer-widget-area">
				<div class="skin">
					<div class="widget"></div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
