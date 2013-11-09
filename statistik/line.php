<?php
include "../adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);
$gcopy = $_GET;
foreach ($gcopy as $g => $v)
{
  if (strpos("=",$g) !== false)
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

$page_width -= 50; // make it work with the iframe

$lo = 0;
if(isset($_GET['lo']) && strlen($_GET['lo'])>0){
        $lo = intval($_GET['lo']);
}

if ($lo < 37 || $lo > 45) {
	$lo = 0;
}
$copy_height = 30;
$container_height = $page_height - $copy_height;

$c = 0;
$stat = array();
$q = "";
/*if ($lo == 0)
{
  $q = "SELECT * FROM ppoe_mv_info.mv_statistik GROUP BY timestamp ORDER BY timestamp ASC;";
}
else*/
{
  $q = "SELECT * FROM ppoe_mv_info.mv_statistik WHERE LO = $lo ORDER BY timestamp ASC;";
}
$query = mysql_query($q);
while ($query && ($row = mysql_fetch_array($query))) {
	$stat[$c++] = array(
			"members" => $row["members"],
                        "akk" => $row["akk"],
			"users" => $row["users"],
			"datum" => $row["timestamp"]
			);
}
mysql_close($link);

$c = count($stat);
$datastr1 = "";
$c1=1;
foreach($stat as $id=>$data){
	$mtime = strtotime($data["datum"])*1000;
	$datastr1.= "[".$mtime.",".$data["members"]."]";
	if($c1!==$c){
		$datastr1 .= ",";
	}
	$c1++;
}
$datastr2 = "";
$c1=1;
$memmax = 20;
foreach($stat as $id=>$data){
        $memmax = max($memmax,intval($data["users"] * 1.2));
	$mtime = strtotime($data["datum"])*1000;
	$datastr2.= "[".$mtime.",".$data["users"]."]";
	if($c1!==$c){
		$datastr2 .= ",";
	}
	$c1++;
}
$datastr3 = "";
$c1=1;
foreach($stat as $id=>$data){
	$mtime = strtotime($data["datum"])*1000;
	$datastr3.= "[".$mtime.",".$data["akk"]."]";
	if($c1!==$c){
		$datastr3 .= ",";
	}
	$c1++;
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
<script language="javascript" type="text/javascript" src="date.format.js"></script>
<script type="text/javascript">
$(function () {
	var members = [<?php echo $datastr1; ?>];
	var users = [<?php echo $datastr2; ?>];
	var akk = [<?php echo $datastr3; ?>];
	var memdata = {	label: "Stimmberechtigt", data: members };
	var userdata = { label: "Mitglieder", data: users };
	var akkdata = { label: "Liquid", data: akk };
	var data = 	[ memdata , userdata, akkdata ];

	var datasets = {
		"Liquid": {
			label: "Liquid Accounts",
			data: akk
		},
		"Mitglieder": {
			label: "Stimmberechtigt",
			data: members
		},
		"Registriert": {
			label: "Mitglieder",
			data: users
		}
	};
    
	// hard-code color indices to prevent them from shifting as
    // countries are turned on/off
    var i = 0;
    $.each(datasets, function(key, val) {
        val.color = i;
        ++i;
    });
    // insert checkboxes 
    var choiceContainer = $("#choices");
    $.each(datasets, function(key, val) {
        choiceContainer.append('<input type="checkbox" name="' + key +
                               '" checked="checked" id="id' + key + '">' +
                               '<label for="id' + key + '">'
                                + val.label + '</label> ');
    });
    choiceContainer.find("input").click(plotAccordingToChoices);

	var doptions = {
		series: {
			lines: { show: true },
			points: {
				show: true,
				radius: 2
			}
		},
		grid: { hoverable: true, clickable: true },
		xaxis: {
			show: true,
			mode: "time",
			timeformat: "%d.%m.",
			minTickSize: [1, "day"],
			//labelWidth: 40,
			labelHeight: 15,
			//reserveSpace: true
		},
		yaxis: {
			show: true,
			min: 0,
			minTickSize: 1,
			max: <?echo $memmax;?>
		},
		legend: {
			position: "nw"
		}
	};
    
    function plotAccordingToChoices() {
        var data = [];

        choiceContainer.find("input:checked").each(function () {
            var key = $(this).attr("name");
            if (key && datasets[key])
                data.push(datasets[key]);
        });

        if (data.length > 0)
            $.plot($("#grafik"), data, doptions);
    }
	
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

    plotAccordingToChoices();
	
    var previousPoint = null;
    $("#grafik").bind("plothover", function (event, pos, item) {
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

    $("#grafik").bind("plotclick", function (event, pos, item) {
        if (item) {
            $("#clickdata").text("You clicked point " + item.dataIndex + " in " + item.series.label + ".");
            plot.highlight(item.series, item.datapoint);
        }
    });
	
});
</script>
</head>

<body style="font-family:'Helvetica Neue', Helvetica, Arial, sans-serif; margin:0; padding:0; color:#444; width:<?php echo $page_width; ?>px">
<div id="grafik" style="width:<?php echo $page_width; ?>px; height:<?php echo $container_height; ?>px; margin: 0; padding:0;"></div>
<div id="choices" style="height:<?php echo $copy_height; ?>"></div>

</body>
</html>
