<?php
exit 0;
include_once('functions.inc.php');
include_once('config.read.php');
include_once('mysql.class.php');

$page_title = "Statistik";

$userfile = "http://mitglieder.piratenpartei.at/statistik/read.php?action=users&show=data";
$utmp = ""; $ufp = fopen($userfile,"r"); while( $buf = fread($ufp,16) ){ $utmp .= $buf; } fclose($ufp);
$u = $utmp;

$memberfile = "http://mitglieder.piratenpartei.at/statistik/read.php?action=members&show=data";
$mtmp = ""; $mfp = fopen($memberfile,"r"); while( $buf = fread($mfp,16) ){ $mtmp .= $buf; } fclose($mfp);
$m = $mtmp;

$q = round($m/$u*100,2);


$last = "http://mitglieder.piratenpartei.at/statistik/write.php?action=read&read=lastupdate";
$lout = ""; $fl=fopen($last,"r"); while($ltmp=fread($fl,1024)){ $lout .= $ltmp; } fclose($fl);
$last_update = $lout;


// Write membercount into statistics db
$write = "http://mitglieder.piratenpartei.at/statistik/write.php?action=write&write=users:".$u.";members:".$m.";quota:".$q."";
$fw=fopen($write,"r");
fclose($fw);
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html dir="ltr" lang="de-DE" class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html dir="ltr" lang="de-DE" class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html dir="ltr" lang="de-DE" class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html dir="ltr" lang="de-DE" class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html dir="ltr" lang="de-DE"> <!--<![endif]-->
<head>
<meta charset="UTF-8">
<title><?php echo $page_title; ?> - Piratenpartei Österreichs</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta http-equiv="cleartype" content="on">
<link rel="apple-touch-icon" href="http://piratenpartei.at/wp-content/themes/piratenkleider-ppoe/apple-touch-icon.png">
<link rel="shortcut icon" href="http://piratenpartei.at/wp-content/themes/piratenkleider-ppoe/favicon.ico">
<link rel="stylesheet" type="text/css" media="all" href="http://piratenpartei.at/wp-content/themes/piratenkleider-ppoe/style.css">
<!--[if lt IE 9 ]>  <link rel="stylesheet" type="text/css" media="all" href="http://piratenpartei.at/wp-content/themes/piratenkleider-ppoe/ie.css">  <![endif]-->
</head>
<body class="page page-parent page-template-default">
	<!-- Start Header -->
	<div class="section header">
		<div class="row">
			<div class="branding">
				<a href="http://piratenpartei.at/" title="Link zur Startseite" rel="home" class="logo">
					<img src="http://piratenpartei.at/wp-content/themes/piratenkleider-ppoe/assets/logo.png" alt="Logo Piratenpartei Österreichs">
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
								<span class="day"><?php echo date("d",$last_update); ?></span>
								<span class="month"><?php echo date("m",$last_update); ?>.</span>
								<span class="year"><?php echo date("Y",$last_update); ?></span>
							</div>
						</div>
						<div class="post-entry">
							<!-- Start Text -->
								<p>Letztes Update am <?php echo date("d.m.Y",$last_update); ?></p>
								<p>&nbsp;</p>
								<div class="statistik" style="margin:0 auto; font-size:1.9em; line-height:1.3em;">
									<p>Die Piratenpartei Österreichs hat derzeit <strong><?php echo $u; ?></strong> angemeldete Benutzer, davon sind <strong><?php echo $m; ?></strong> Mitglieder stimmberechtigt.</p>
									<p>Das entspricht einer Zahlungsquote von <strong><?php echo $q; ?>%</strong>.</p>
								</div>
								<div id="statistik-diagramm" style="margin-bottom:1em;">
								</div>
							<!-- Ende Text -->
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
