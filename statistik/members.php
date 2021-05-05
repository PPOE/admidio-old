<? 
include "../adm_api/config.php";

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$lo = 0;
if (isset($_GET['lo']))
{
  $lo = intval($_GET['lo']);
}
if ($lo == 0)
{
  $lo = 2;
}

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

<a href="table.php">Bundesl&auml;nder</a> &bull; <a href="nuts.php">NUTS-Regionen</a> &bull; <a href="members.php">Altersstatistik, etc.</a> <br />

<table>
<tr><th>Merkmal</th><th>Häufigkeit und Wert</th></tr>
END;
$members = 0;
$query = mysql_query("SELECT COUNT(*) AS c FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate();");
if ($query && ($row = mysql_fetch_assoc($query))) {
$members = $row['c'];
}
echo "<tr><td>Mitgliedsbeitrag</td><td><table border=1><tr><th>Anzahl</th><th>Durchschnitt</th></tr>";
$query = mysql_query("SELECT COUNT(*) AS c,ROUND(AVG(A.usd_value),2) AS a FROM adm_members LEFT JOIN adm_user_data A ON mem_usr_id = A.usd_usr_id LEFT JOIN adm_user_data B ON mem_usr_id = B.usd_usr_id WHERE A.usd_usf_id = 27 AND mem_rol_id = 2 AND mem_end > curdate() AND B.usd_usf_id = 26 AND B.usd_value > curdate();");
while ($query && ($row = mysql_fetch_assoc($query))) {
echo "<tr><td>{$row['c']}</td><td>{$row['a']} €</td></tr>";
}
echo "</tr></table></tr>";

echo "<tr><td>Geschlecht</td><td><table border=1><tr><th>Wert</th><th>Häufigkeit</th></tr>";
//$query = mysql_query("SELECT * FROM (SELECT usd_value AS v,COUNT(*) AS c FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 11 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate()) GROUP BY usd_value ORDER BY c DESC LIMIT 10) A WHERE c >= 25;");
$query =  mysql_query("SELECT * FROM (SELECT usd_value AS v,COUNT(*) AS c FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 11 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = 2  AND mem_begin <= curdate() AND mem_end >= curdate()) GROUP BY usd_value ORDER BY c DESC ) A ;");

$members_c = $members;
while ($query && ($row = mysql_fetch_assoc($query))) {

//echo "<tr><td>".$row['v']."</td></tr>";
echo "<tr><td>".str_replace(array('1','2'),array('männlich','weiblich'),$row['v'])."</td><td>{$row['c']}</td></tr>";

$members_c -= $row['c'];
}
echo "<tr><td>Ohne Angabe</td><td>$members_c</td>";
echo "</tr></table></tr>";

//echo "<tr><td>Vorname</td><td><table border=1><tr><th>Wert</th><th>Häufigkeit</th></tr>";
//$query = mysql_query("SELECT * FROM (SELECT usd_value AS v,COUNT(*) AS c FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 2 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate()) GROUP BY usd_value ORDER BY c DESC LIMIT 10) A WHERE c >= 25;");
//$members_c = $members;
//while ($query && ($row = mysql_fetch_assoc($query))) {
//echo "<tr><td>{$row['v']}</td><td>{$row['c']}</td></tr>";
//$members_c -= $row['c'];
//}
//echo "<tr><td>Rest</td><td>$members_c</td>";
//echo "</tr></table></tr>";

//echo "<tr><td>PLZ</td><td><table border=1><tr><th>Wert</th><th>Häufigkeit</th></tr>";
//$query = mysql_query("SELECT * FROM (SELECT usd_value AS v,COUNT(*) AS c FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 4 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate()) GROUP BY usd_value ORDER BY c DESC LIMIT 10) A WHERE c >= 25;");
//$members_c = $members;
//while ($query && ($row = mysql_fetch_assoc($query))) {
//echo "<tr><td>{$row['v']}</td><td>{$row['c']}</td></tr>";
//$members_c -= $row['c'];
//}
//echo "<tr><td>Rest</td><td>$members_c</td>";
//echo "</tr></table></td></tr>";

echo "<tr><td>Ort</td><td><table border=1><tr><th>Wert</th><th>Häufigkeit</th></tr>";
$query = mysql_query("SELECT * FROM (SELECT usd_value AS v,COUNT(*) AS c FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 5 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate()) GROUP BY usd_value ORDER BY c DESC LIMIT 10) A WHERE c >= 25;");
$members_c = $members;
while ($query && ($row = mysql_fetch_assoc($query))) {
echo "<tr><td>{$row['v']}</td><td>{$row['c']}</td></tr>";
$members_c -= $row['c'];
}

echo "<tr><td>Rest</td><td>$members_c</td>";

echo "</tr></table></td></tr>";

echo "<tr><td>Land</td><td><table border=1><tr><th>Wert</th><th>Häufigkeit</th></tr>";
$query = mysql_query("SELECT * FROM (SELECT usd_value AS v,COUNT(*) AS c FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 6 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate()) GROUP BY usd_value ORDER BY c DESC LIMIT 10) A WHERE c >= 25;");
$members_c = $members;
while ($query && ($row = mysql_fetch_assoc($query))) {
echo "<tr><td>{$row['v']}</td><td>{$row['c']}</td></tr>";
$members_c -= $row['c'];
}
echo "<tr><td>Rest</td><td>$members_c</td>";
echo "</tr></table></td></tr>";

echo "<tr><td>Altersdurchschnitt</td><td>";
$query = mysql_query("SELECT AVG(v) AS v FROM (SELECT (TO_DAYS(NOW()) - TO_DAYS(usd_value)) / 365.25 AS v FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 10 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate())) A WHERE v >= 10 AND v <= 110;");
if ($query && ($row = mysql_fetch_assoc($query))) {
echo round($row['v'],1);
}
echo "</td></tr>";

echo <<<END
<tr><td>Alter</td><td>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="flot/excanvas.min.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="flot/jquery.js"></script>
<script language="javascript" type="text/javascript" src="flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="date.format.js"></script>
<script type="text/javascript">
$(function () {
	var members = [
END;
$query = mysql_query("SELECT v,COUNT(v) AS c FROM (SELECT ROUND((TO_DAYS(NOW()) - TO_DAYS(usd_value)) / 365.25) AS v FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 10 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate())) A WHERE v >= 10 AND v <= 110 GROUP BY v;");
$comma = false;
while ($query && ($row = mysql_fetch_assoc($query))) {
if ($comma)
	echo ",";
$comma = true;
echo "[{$row['v']},{$row['c']}]";
}
echo <<<END
	];
        var members2 = [
END;
$query = mysql_query("SELECT v,COUNT(v) AS c FROM (SELECT ROUND((TO_DAYS(NOW()) - TO_DAYS(usd_value)) / 365.25 / 5) * 5 AS v FROM ppoe_mitglieder.adm_user_data WHERE usd_usf_id = 10 AND usd_usr_id IN (SELECT mem_usr_id FROM adm_members WHERE mem_rol_id = $lo AND mem_begin <= curdate() AND mem_end >= curdate())) A WHERE v >= 10 AND v <= 110 GROUP BY v;");
$comma = false;
$memmax = 0;
while ($query && ($row = mysql_fetch_assoc($query))) {
if ($comma)
        echo ",";
$comma = true;
echo "[{$row['v']},{$row['c']}]";
$memmax = max($memmax,intval($row['c']));
}
echo <<<END
        ];
	var memdata = {	label: "Alter", data: members };
	var memdata2 = { label: "Alter in 5er Schritten", data: members2 };
	var data = 	[ memdata, memdata2 ];

	var datasets = {
		"Alter": {
			label: "Alter",
			data: members
		},
                "Alter in 5er Schritten": {
                        label: "Alter in 5er Schritten",
                        data: members2
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
				radius: 3
			}
		},
		grid: { hoverable: true, clickable: true },
		xaxis: {
			show: true,
			mode: "text",
			//labelWidth: 40,
			labelHeight: 15,
			//reserveSpace: true
		},
		yaxis: {
			show: true,
			min: 0,
			max: $memmax,
			minTickSize: 1
		},
		legend: {
			position: "ne"
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
				var x = item.datapoint[0].toFixed(0),
					y = item.datapoint[1].toFixed(0);
				
				showTooltip(item.pageX, item.pageY,
							x + " Jahre alt: " + y );
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
<div id="grafik" style="width:615px; height:350px; margin: 0; padding:0;"></div>
<div style="display:none;" id="choices" style="height:30"></div>
END;
echo "</td></tr>";
echo "</table></body></html>\n";

mysql_close($link);
?>

