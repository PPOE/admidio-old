<?php
include "../adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$gcopy = $_GET;
foreach ($gcopy as $g => $v)
{
  if (strpos("=", $g) !== false)
  {
    $pair = explode("=", $g, 2);
    $_GET[$pair[0]] = $pair[1];
  }
}

// Anzeige-Optionen
if(isset($_GET['width']) && strlen($_GET['width'])>0 && isset($_GET['height']) && strlen($_GET['height'])>0){
        $page_width = intval($_GET['width']);
        $page_height = intval($_GET['height']);
} else {
        $page_width = 665;
        $page_height = 320;
}

$all = 0;
if(isset($_GET['all']) && strlen($_GET['all'])>0){
        $all = intval($_GET['all']);
	if ($all != 0) { $all = 1; };
}

$copy_height = 30;
$container_height = $page_height - $copy_height;

$action = "pie";

switch($action){
	case("pie"):$config = "read";break;
}

$los = array(38 => 'Burgenland', 40 => 'K&auml;rnten', 39 => 'Nieder&ouml;sterreich', 41 => 'Ober&ouml;sterreich', 42 => 'Salzburg', 43 => 'Steiermark', 44 => 'Tirol', 45 => 'Vorarlberg', 37 => 'Wien');

$c = 0;
$laender = array();
$query = mysql_query("SELECT * FROM ppoe_mv_info.mv_statistik WHERE LO != 0 ORDER BY timestamp DESC LIMIT 9;");
while ($query && ($row = mysql_fetch_array($query))) {
	if ($all == 0)
	        $laender[$los[$row["LO"]]] = $row["members"];
	else
                $laender[$los[$row["LO"]]] = $row["users"];
}

mysql_close($link);

sort($laender,"SORT_ASC");
$datastr = "";
$c = 0;
foreach($laender as $name=>$count){
	$n = utf8_encode($name);
	$datastr .= "{ label: \"";
	$datastr .= $n;
	$datastr .= "\",  data: ";
	$datastr .= $count;
	$datastr .= "}";
	if($c != count($laender)){
		$datastr .= ",";
	}
	$datastr .= "\r\n";
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta http-equiv="cleartype" content="on">
<title>Statistik - Landesorganisationen</title>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.pie.js"></script>
<script type="text/javascript">
$(function () {
	var imgs = {
		"Burgenland":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Burgenland.png",
		"K&auml;rnten":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Kaernten.png",
		"Ober&ouml;sterreich":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Oberoesterreich.png",
		"Nieder&ouml;sterreich":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Niederoesterreich.png",
		"Salzburg":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Salzburg.png",
		"Steiermark":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Steiermark.png",
		"Tirol":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Tirol.png",
		"Vorarlberg":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Vorarlberg.png",
		"Wien":"https://archiv.piratenpartei.at/wp-content/uploads/laender/Wien.png"
	};
	var doptions = {
		series: {
			pie: { 
				show: true,
				radius: 1,
				label: {
                    show: true,
                    radius: 7/8,
                    formatter: function(label, series){
						var labelcontaineroptions = 'text-align:center;min-width:26px;padding:3px;border-radius:3px;color:#FFF;background:rgba(128,128,128,0.3);text-shadow:1px 1px 2px #444;';
						var labelcontentoptions = 'display:inline-block;max-height:32px;max-width:20px;margin:0;padding:0;';
						var labelcontent = 
							'<span style="font-size:12px; font-weight:bold;">'+ Math.round(series.percent) + '%</span>'+
							'<br/>'+
							'<img src="' + imgs[label] + 
							'" style="' + labelcontentoptions + 
							'" alt="Wappen ' + label +
							'" title="' + label +  '" />';
						var labelcontainer = '<div style="' + labelcontaineroptions + '" onmouseover="">' + labelcontent + '</div>';
                        return labelcontainer;
                    }
                }				
			}
		}
	};
	var data = [ <?php echo $datastr; ?> ];
	
    function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y - 25,
            left: x - 30,
            border: '1px solid #333',
            padding: '3px',
			'border-radius': '3px',
			color: '#444',
            'background-color': 'rgba(255, 255, 255,0.8)',
			'font-size': '10px',
            opacity: 1
        }).appendTo("body").fadeIn(400);
    }	

    $.plot($("#grafik"), data, doptions);

    var previousPoint = null;
    $(".pieLabel").bind("hover", function (event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

		if (item) {
			if (previousPoint != item.dataIndex) {
				previousPoint = item.dataIndex;
				
				$("#tooltip").remove();
				var x = item.datapoint[0].toFixed(0)/10,
					y = item.datapoint[1].toFixed(0);
				
				showTooltip(item.pageX, item.pageY,
							dateFormat(x*10, "dd.mm.yyyy") + ": " + y );
			}
		}
		else {
			$("#tooltip").remove();
			previousPoint = null;            
		}
    });


});
</script>
</head>

<body style="font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; margin:0; padding:0; color:#444; width:<?php echo $page_width; ?>px">
<div id="grafik" style="width:<?php echo $page_width; ?>px; height:<?php echo $container_height; ?>px; margin: 0; padding:0;"></div>

</body>
</html>
