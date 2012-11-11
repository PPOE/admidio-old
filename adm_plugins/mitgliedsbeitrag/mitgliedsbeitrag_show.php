<?php
 /******************************************************************************
 * Mitgliedsbeitrag
 *
 * Dieses Plugin berechnet Mitgliedsbeiträge und erzeugt dtaus-Dateien
 * im Datenträgeraustauschformat. 
 *
 * Copyright    : (c) 2004 - 2011 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *      
 * Uebergaben:
 *
 * mode    : Ausgabeart   (html, print, dtaus, dtaus-bglz,
 *                         csv-ms-rechnung, csv-oo-rechnung,
 *                         csv-ms-lastschrift, cvs-oo-lastschrift, 
 *                         csv-ms-gesamt, csv-oo-gesamt)
 * start   : Angabe, ab welchem Datensatz Mitglieder angezeigt werden sollen
 * einzug   :Angabe entweder: wenn 4-stellig -> dann Angabe des Abrechnungsjahres
 *                  oder: wenn größer als 4 -> dann Datumsangabe der letzten Abrechnung     
 *  
 *****************************************************************************/

 // Pfad des Plugins ermitteln
$plugin_folder_pos = strpos(__FILE__, 'adm_plugins') + 11;
$plugin_file_pos   = strpos(__FILE__, 'mitgliedsbeitrag_show.php');
$plugin_folder     = substr(__FILE__, $plugin_folder_pos+1, $plugin_file_pos-$plugin_folder_pos-2);

if(!defined('PLUGIN_PATH'))
{
    define('PLUGIN_PATH', substr(__FILE__, 0, $plugin_folder_pos));
}
require_once(PLUGIN_PATH. '/../adm_program/system/common.php');
require_once(PLUGIN_PATH. '/../adm_program/system/classes/list_configuration.php');
require_once(PLUGIN_PATH. '/../adm_program/system/classes/table_roles.php');
require_once(PLUGIN_PATH. '/'.$plugin_folder.'/config.php');
require_once(PLUGIN_PATH. '/'.$plugin_folder.'/DTA.php');

$dta_file = new DTA(DTA_DEBIT);
 
// lokale Variablen der Uebergabevariablen initialisieren
$arr_mode   = array('dtaus','dtaus-bglz','csv-ms-rechnung', 'csv-oo-rechnung',
   'csv-ms-lastschrift', 'csv-oo-lastschrift','csv-ms-gesamt', 'csv-oo-gesamt', 'html', 'print');
$req_start  = 0;
$req_vorschau = 16;
$charset    = '';
$current_year = date("Y");
$req_einzug = 0;
$dtaus_mode = '';

$last_year =   0;
$last_day  = 0;
$last_month  = 0;

$showPlugin = false;

foreach ($plgFreigabe AS $i)
{
    if($i == 'Alle')
    {
        $showPlugin = true;
    }
    elseif($i == 'Benutzer'
    && $g_valid_login == true)
    {
        $showPlugin = true;
    }
    elseif($i == 'Rollenverwalter'
    && $g_current_user->assignRoles())
    {
        $showPlugin = true;
    }
    elseif(hasRole($i))
    {
        $showPlugin = true;
    }
}

// wenn Benutzer keine Rechte hat, dann Fehler ausgeben
if($showPlugin == false)
{
    $g_message->show($g_l10n->get('SYS_INVALID_PAGE_VIEW'));
}

// DB auf Admidio setzen, da evtl. noch andere DBs beim User laufen
$g_db->setCurrentDB();

// Uebergabevariablen pruefen
$req_mode   = strStripTags($_GET['mode']);

if(in_array($req_mode, $arr_mode) == false)
{
    $g_message->show($g_l10n->get('SYS_INVALID_PAGE_VIEW'));
}

if(isset($_GET['start']))
{
    if(is_numeric($_GET['start']) == false)
    {
        $g_message->show($g_l10n->get('SYS_INVALID_PAGE_VIEW'));
    }
    $req_start = $_GET['start'];
}

if(isset($_GET['einzug']))
{
    $req_einzug = $_GET['einzug'];
    //   Angabe entweder: wenn 4-stellig -> dann Angabe des Abrechnungsjahres
    //                  oder: wenn größer als 4 -> dann Datumsangabe der letzten Abrechnung      
    if (strlen($req_einzug)>4)
    {
        $last_year = date("Y",strtotime($req_einzug));
        $last_day  =  date("d",strtotime($req_einzug));
        $last_month = date("m",strtotime($req_einzug));
    
    }
    else
    {
        $current_year = $req_einzug;
    }
}

if(($req_mode == 'csv-ms-rechnung')  || ($req_mode == 'csv-ms-gesamt') || ($req_mode == 'csv-ms-lastschrift')) 
{
	if($req_mode == 'csv-ms-rechnung') 
    {
        $export_mode = "rechnung";
        $filename = $g_organization.$beitrag_filename_csvrechnung;
    }
    else if($req_mode == 'csv-ms-lastschrift') 
    {
        $export_mode = "lastschrift";
        $filename = $g_organization.$beitrag_filename_csvlastschrift;
    }
    else 
    {
        $export_mode = "gesamt";
        $filename = $g_organization.$beitrag_filename_csvgesamt;
    }
    $separator    = ';'; // Microsoft Excel 2007 und neuer braucht ein Semicolon
    $value_quotes = '"';
    $req_mode     = 'csv';
	$charset      = 'iso-8859-1';   
}
else if(($req_mode == 'csv-oo-rechnung')  || ($req_mode == 'csv-oo-gesamt')|| ($req_mode == 'csv-oo-lastschrift')) 
{
	if($req_mode == 'csv-oo-rechnung') 
    {
        $export_mode = "rechnung";
        $filename = $g_organization.$beitrag_filename_csvrechnung;
    }
    else if ($req_mode == 'csv-oo-lastschrift') 
    {
        $export_mode = "lastschrift";
        $filename = $g_organization.$beitrag_filename_csvlastschrift;
    }
    else 
    {
        $export_mode = "gesamt";
        $filename = $g_organization.$beitrag_filename_csvgesamt;
    }
    $separator    = ',';   // fuer CSV-Dateien
    $value_quotes = '"';   // Werte muessen mit Anfuehrungszeichen eingeschlossen sein
    $req_mode     = 'csv';
	$charset      = 'utf-8';
}
else if ($req_mode == 'dtaus') 
{
    $export_mode = 'dtaus';
    $req_mode     = 'csv';
    $charset      = 'utf-8';
    $filename = $beitrag_filename_dtaus0;
 }     
    
else if ($req_mode == 'dtaus-bglz')
 {   
        $export_mode = 'dtaus';
        $dtaus_mode = 'dtaus-bglz';
        $req_mode     = 'csv';
        $value_quotes = '"';   // Werte muessen mit Anfuehrungszeichen eingeschlossen sein   	
   	    $separator    = ';'; // Microsoft Excel 2007 und neuer braucht ein Semicolon
	    $charset      = 'iso-8859-1';     
        $filename = $beitrag_filename_dtausbglz;  
}
else
{
    $separator    = ',';    // fuer CSV-Dateien
    $value_quotes = '';
}

if($req_mode == 'html')
{
    $class_table           = 'tableList';
    $class_sub_header      = 'tableSubHeader';
    $class_sub_header_font = 'tableSubHeaderFont';
}
else if($req_mode == 'print')
{
    $class_table           = 'tableListPrint';
    $class_sub_header      = 'tableSubHeaderPrint';
    $class_sub_header_font = 'tableSubHeaderFontPrint';
}

// enthaelt die komplette CSV-Datei als String
$str_export   = ''; 

//das Array anzeige_liste dient zur Anzeige der Listenansicht und
// als Grundlage für alle Export-Dateien
$anzeige_liste = array();
  
//ab hier wird die eigentliche Mitgliedsbeitragsberechnung durchgeführt
//das Ergebnis steht anschließend in zwei Arrays: $families und $members
//**********************************************************************

// alle familien abfragen
$families = array();
$result = $g_db->query("select rol.rol_name, rol.rol_id, rol.rol_timestamp_create from adm_categories as cat, adm_roles as rol where cat.cat_name='".$beitrag_familie['kategorie']."' and cat.cat_id=rol.rol_cat_id;");

while ($row = $g_db->fetch_array($result))
{
	$families[$row['rol_id']]['name'] = $row['rol_name'];
	$families[$row['rol_id']]['members'] = array();
	$families[$row['rol_id']]['familycreate'] = $row['rol_timestamp_create'];	
}
	       
// alle mitlieder aus den familien abfragen
$allfamilymembers = array();
foreach ($families as $famid => $dummy)
{
	$result = $g_db->query("select mem.mem_usr_id from adm_members as mem, adm_roles as rol where mem.mem_rol_id=rol.rol_id and (mem.mem_end='9999-12-31') and rol.rol_id=".$famid.";");
	while ($row = $g_db->fetch_array($result))
	{
		$allfamilymembers[$row['mem_usr_id']] = $famid;
		$families[$famid]['members'][$row['mem_usr_id']] = array();
	}
}

//in den nachfolgenden Arrays befinden sich nach dem Befüllen die usr_ids der dazugehörigen Mitglieder   
$beitragbefreit = befuelle_array($g_db, $beitrag_befreit['kategorie']) ;  
$studenten = befuelle_array($g_db, $beitrag_ermaessigt['kategorie']) ;  
$spartenmitglieder = befuelle_array($g_db, $beitrag_sparte['kategorie']) ;  
             
// alle mitglieder abfragen
$members = array();
$result = $g_db->query("select mem.mem_usr_id, mem.mem_begin from adm_members as mem, adm_roles as rol where (mem.mem_rol_id=rol.rol_id) and (mem.mem_end='9999-12-31') and (rol.rol_name='".$beitrag_einzel_rolle."');");
while($row = $g_db->fetch_array($result))
{
	$members[$row['mem_usr_id']] = array("Beitritt" => $row['mem_begin']);
}

// Frage für jedes Mitglied die persönlichen Daten ab
// und trage diese im zugehörigen Array ein. Entferne
// alle Mitglieder, die in einer Familie sind
$attributes = array("Nachname" => 0, "Vorname" => 0, "Adresse" => 0, "PLZ" => 0, "Ort" => 0, "Land" => 0, "Geburtstag" => 0, "Geschlecht" => 0, "Kontonummer" => 0, "Bankleitzahl" => 0, "Kontoinhaber" => 0);
foreach($attributes as $attribute => $dummy) 
{
	$result = $g_db->query("select usf_id from adm_user_fields where usf_name='".$attribute."';");
	$row = $g_db->fetch_array($result);
	$attributes[$attribute] = $row['usf_id'];
}
      
// wie lautet die ID des Feldes Beitritt in der Tabelle user_fields
$result = $g_db->query("select usf_id from adm_user_fields where usf_name='Beitritt';");
$row_1 = $g_db->fetch_array($result);
	  
// alle Mitlieder durchlaufen   ...
foreach ($members as $member => $key)
{ 
	// ... und im Array $members das Feld Beitritt überschreiben 
    $result = $g_db->query("select usd_value from adm_user_data where usd_usr_id=".$member." and usd_usf_id=".$row_1['usf_id'].";");
    $row = $g_db->fetch_array($result);
        
    // Überprüfung: wenn kein Beitrittsdatum existiert, dann wird das Datum genommen, an dem das Mitglied erzeugt wurde
    if ($row['usd_value'] <> NULL)
    {  	
        $members[$member]['Beitritt'] = $row['usd_value'];
	}
	
	foreach ($attributes as $attribute => $usf_id) 
    {
		$result = $g_db->query("select usd_value from adm_user_data where usd_usr_id=".$member." and usd_usf_id=".$usf_id.";");
		$row = $g_db->fetch_array($result);
		$members[$member][$attribute] = $row['usd_value'];
	}
	if (isset($allfamilymembers[$member])) 
    {
		$allfamilymembers[$member] = $members[$member];
		unset($members[$member]);
	}
}
            
// füge die Inhalte von allfamilymembers in families ein
foreach($families as $family => $faminfo)
{
	foreach($faminfo['members'] as $member => $dummy) 
    {
		$families[$family]['members'][$member] = $allfamilymembers[$member];
		unset($allfamilymembers[$member]);
	}
}
       
//Schleife: alle Familien durchlaufen
foreach($families as $family => $faminfo) 
{	  
    $familycreate =  strtotime($families[$family]['familycreate']) ;
    $families[$family]['Beitrag'] = 0;      
    $families[$family]['Beitragstext'] = $beitrag_einzugstext." ".date("Y", strtotime($current_year."-12-31"));
              
    //Abrechnung nur durchführen, wenn:
    //entweder last_day (oder last_month)= 0  --> d.h.im Abrechnungsjahr noch keine Abrechnung durchgeführt
    //oder die Familie erst nach dem letzten Abrechnungstag angelegt wurde
        
    //falls die Familie bereits vor dem letzten Abrechnungstag existierte, 
    //dann wurde der Beitrag bereits eingezogen
      
    if (($last_day  == 0) || (((date("U",$familycreate)) > (date("U",strtotime($current_year."-".$last_month."-".$last_day))))))
    {
        //Überprüfung, ob anteilig oder voll

        //Familie wurde bereits vor dem Abrechnungsjahr erstellt: Vollberechnung
        if ((date("Y",$familycreate)) < (date("Y", strtotime($current_year."-12-31")))) 
        {
            berechne_familienbeitrag($families,$family,$beitrag_familie,"voll",$beitrag_abrunden);
            
            //alle Mitglieder der Familie durchlaufen
            foreach($faminfo['members'] as $key =>$wert) 
            {
                berechne_spartenbeitrag($families,$family,$spartenmitglieder,$current_year,$beitrag_sparte,$key,$wert,$beitrag_abrunden);
            } 
        }

        //familycreate > Abrechnungsjahr: 
        //Famile wurde nach dem Abrechnungsjahr erstellt --> keine Aktion notwendig

    	//familycreate = Abrechnungsjahr (Familie wurde im Abrechnungsjahr erstellt):
    	//anteilige Berechnung (13-timestamp-Monat/12)* Familienbeitrag
    	elseif ((date("Y",$familycreate)) == (date("Y", strtotime($current_year."-12-31")))) 
    	{
    		berechne_familienbeitrag($families,$family,$beitrag_familie,"",$beitrag_abrunden);
          
            //alle Mitglieder der Familie durchlaufen
            foreach($faminfo['members'] as $key =>$wert) 
            {
 
                // hat das Mitglied Beitrittsdatum >= familycreate
        		// Abrechnung fällt in die Familie, wird über den anteiligen Familienbeitrag abgedeckt
        		// keine Aktion notwendig

        		// hat das Mitglied Beitrittsdatum < familycreate
        		// Mitglied war bereits vor der Erstellung der Familie als Einzelmitglied Mitglied
         		// anteilige Berechnung des Beitrags für den Zeitraum Beitritt bis familycreate
        			
        		if (date("U", strtotime($wert['Beitritt'] )) < date("U", $familycreate))
                {
                    //wenn Beitrittsjahr < familycreate-jahr  --> Monate von 01.01. bis familycreate -1
                    if (date("Y", strtotime($wert['Beitritt'] )) <  date("Y", $familycreate))
                    {
                        berechne_mitgliedsbeitrag($families,$family,$studenten,$beitrag_beitraege_einzel,$beitrag_ermaessigt,$current_year,$last_day,$last_month,$last_year,$key,$wert,(date("m", $familycreate)-1),$beitrag_abrunden) ;
                    } 
                    //wenn Beitrittsjahr = familycreate-jahr --> Monate von Beitrittsmonat bis familycreate -1
                    elseif  (date("Y", strtotime($wert['Beitritt'] )) ==  date("Y", $familycreate))
                    {
                           berechne_mitgliedsbeitrag($families,$family,$studenten,$beitrag_beitraege_einzel,$beitrag_ermaessigt,$current_year,$last_day,$last_month,$last_year,$key,$wert,(date("m", $familycreate) - date("m", strtotime($wert['Beitritt'] ))),$beitrag_abrunden) ;
                    } 
                } 
      
                //ist das Mitglied eine Spartenmitglied
                berechne_spartenbeitrag($families,$family,$spartenmitglieder,$current_year,$beitrag_sparte,$key,$wert,$beitrag_abrunden);
            }
    	}
    }
}

// alle beitragsfreien Mitglieder aus den Einzelmitgliedern löschen
foreach($beitragbefreit as $key =>$wert) 
{
    unset($members[$wert]);
} 

// Beiträge der Mitglieder bestimmen; dazu alle Mitglieder durchlaufen
foreach ($members as $member => $memberdata) 
{
    $members[$member]['Beitrag'] = 0;      
    $members[$member]['Beitragstext'] = $beitrag_einzugstext." ".date("Y", strtotime($current_year."-12-31"));

    //Abrechnung nur durchführen, wenn entweder last_day  (oder last_month)= 0  
    // --> d.h. 1. im Abrechnungsjahr noch keine Abrechnung durchgeführt
    //     oder 2. ein vergangenes Abrechnungsjahr gewählt wurde   
    // und  das Mitglied in diesem Jahr bereits existierte 
    //oder das Mitglied erst nach dem letzten Abrechnungstag angelegt wurde
        
    //falls das Mitglied bereits vor dem letzten Abrechnungstag existierte, 
    //dann wurde der Beitrag bereits eingezogen
      
    if ((($last_day  == 0) && ((date("Y", strtotime($memberdata['Beitritt']))) <= (date("Y",strtotime($current_year."-12-31"))))) || (((date("U", strtotime($memberdata['Beitritt']))) > (date("U",strtotime($current_year."-".$last_month."-".$last_day)))) && checkdate($last_month,$last_day,$current_year)))
    {
        berechne_mitgliedsbeitrag($members,$member,$studenten,$beitrag_beitraege_einzel,$beitrag_ermaessigt,$current_year,$last_day,$last_month,$last_year,$member,$memberdata,12,$beitrag_abrunden);
    	berechne_spartenbeitrag($members,$member,$spartenmitglieder,$current_year,$beitrag_sparte,$member,$memberdata,$beitrag_abrunden);
    }         
}
  
//hier ist das Ende der Mitgliedsbeitragsberechnung
//**********************************************************************

//die Anzeigeliste befüllen
$anzeige_liste = befuelle_anzeigeliste($families, $members);

// wieviele Zeilen hat meine Anzeigeliste?
$num_members = count($anzeige_liste);

if($num_members == 0)
{
    // Es sind keine Daten vorhanden !
    $g_message->show($g_l10n->get('LST_NO_USER_FOUND'));
}

if($num_members < $req_start)
{
    $g_message->show($g_l10n->get('SYS_INVALID_PAGE_VIEW'));
}

if($req_mode == 'html' && $req_start == 0)
{
    // Url fuer die Zuruecknavigation merken, aber nur in der Html-Ansicht
    $_SESSION['navigation']->addUrl(CURRENT_URL);
}

if($req_mode != 'csv')
{
    // Html-Kopf wird geschrieben
    if($req_mode == 'print')
    {
    	header('Content-type: text/html; charset=utf-8');
        echo '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="de" xml:lang="de">
        <head>
            <!-- (c) 2004 - 2011 The Admidio Team - http://www.admidio.org -->
            
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        
            <title>'. $g_current_organization->getValue('org_longname'). ' - Liste - </title>
            
            <link rel="stylesheet" type="text/css" href="'. THEME_PATH. '/css/print.css" />
            <script type="text/javascript" src="'. $g_root_path. '/adm_program/system/js/common_functions.js"></script>

            <style type="text/css">
                @page { size:landscape; }
            </style>
        </head>
        <body class="bodyPrint">';
    }
    else            
    {
        $g_layout['title']    = $g_l10n->get('LST_LIST');
        $g_layout['includes'] = false;
        $g_layout['header']   = '
            <style type="text/css">
                body {
                    margin: 20px;
                }
            </style>
            <script type="text/javascript"><!--
                function exportList(element)
                {
                    var sel_list = element.value;
                
                    if(sel_list.length > 1)
                    {
                        self.location.href = "'. $g_root_path. '/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php?" +
                            "mode=" + sel_list + "&einzug='.$req_einzug.'";
                    }
                }
                function currentyearList(element)
                {
                    var sel_list = element.value;
                    
                    if(sel_list.length > 1)
                    {
                        self.location.href = "'. $g_root_path. '/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php?" +
                            "mode=html&mitglied_jahr_current=" + sel_list;
                    }
                }
                function einzugList(element)
                {
                    var sel_list = element.value;
                    
                    if(sel_list.length > 1)
                    {
                        self.location.href = "'. $g_root_path. '/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php?" +
                            "mode=html&einzug=" + sel_list;
                    }
                }     
            //--></script>';    
       require(SERVER_PATH. '/adm_program/system/overall_header.php');
    }
 
    echo '<h1 class="moduleHeadline">'. 'Mitgliedsbeitrag'. '</h1><h3>';
    
    if ($last_day == 0)
    {
         echo 'Einzugsbeträge für das gesamte Jahr '.$current_year.'.</h3>';    
    }
    else
    {
        echo 'Einzugsbeträge für das Jahr '.$last_year. ' (seit dem '.$last_day.'.'.$last_month. '.)</h3>';        
    }

    if($req_mode == 'html')
    {
        echo '<ul class="iconTextLinkList"> '; 

        echo '<li>
                <span class="iconTextLink">
                    <a href="#" onclick="window.open(\''.$g_root_path.'/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php?&amp;mode=print&amp;einzug='.$req_einzug.'\', \'_blank\')"><img
                    src="'. THEME_PATH. '/icons/print.png" alt="'.$g_l10n->get('LST_PRINT_PREVIEW').'" title="'.$g_l10n->get('LST_PRINT_PREVIEW').'" /></a>
                    <a href="#" onclick="window.open(\''.$g_root_path.'/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php?&amp;mode=print&amp;einzug='.$req_einzug.'\', \'_blank\')">'.$g_l10n->get('LST_PRINT_PREVIEW').'</a>
                </span>
            </li> 
            <li>
                <span class="iconTextLink">
                    <img src="'. THEME_PATH. '/icons/database_out.png" alt="'.$g_l10n->get('LST_EXPORT_TO').'" />
                    <select size="1" name="export_mode" onchange="exportList(this)">
                        <option value="" selected="selected">'.$g_l10n->get('LST_EXPORT_TO').' ...</option>
                        <option value="dtaus">DTA - Datenträgeraustauschdatei</option>
                        <option value="dtaus-bglz">DTA - Begleitzettel - '.$g_l10n->get('LST_MICROSOFT_EXCEL').' ('.$g_l10n->get('SYS_ISO_8859_1').')</option>
                        <option value="csv-ms-rechnung">Rechnungen - '.$g_l10n->get('LST_MICROSOFT_EXCEL').' ('.$g_l10n->get('SYS_ISO_8859_1').')</option>
                        <option value="csv-oo-rechnung">Rechnungen - '.$g_l10n->get('LST_CSV_FILE').' ('.$g_l10n->get('SYS_UTF8').')</option>
                        <option value="csv-ms-lastschrift">Lastschriften - '.$g_l10n->get('LST_MICROSOFT_EXCEL').' ('.$g_l10n->get('SYS_ISO_8859_1').')</option>
                        <option value="csv-oo-lastschrift">Lastschriften - '.$g_l10n->get('LST_CSV_FILE').' ('.$g_l10n->get('SYS_UTF8').')</option>
                        <option value="csv-ms-gesamt">Gesamtliste - '.$g_l10n->get('LST_MICROSOFT_EXCEL').' ('.$g_l10n->get('SYS_ISO_8859_1').')</option>
                        <option value="csv-oo-gesamt">Gesamtliste - '.$g_l10n->get('LST_CSV_FILE').' ('.$g_l10n->get('SYS_UTF8').')</option>
                    </select>
                </span>
            </li> 
            <li>
                <span class="iconTextLink">
                    <img src="'. THEME_PATH. '/icons/dates.png" alt="'.$g_l10n->get('LST_EXPORT_TO').'" />
                    <select size="1" name="export_mode" onchange="einzugList(this)">
                        <option value="" selected="selected">Abrechnung erstellen für ...</option>
                        '.erzeuge_current_year_liste().'                   
                    </select>
                </span>
            </li>  
            <li>
                <span class="iconTextLink">
                    <img src="'. THEME_PATH. '/icons/dates.png" alt="'.$g_l10n->get('LST_EXPORT_TO').'" />
                    <select size="1" name="export_mode" onchange="einzugList(this)">
                        <option value="" selected="selected">Letzter durchgeführter Einzug ...</option>
                        '.erzeuge_last_einzug_liste($beitrag_last_einzugtag).'                     
                    </select>
                </span>
            </li>  
        </ul>';
    }
}

if($req_mode != 'csv')
{
    // Tabellenkopf schreiben
    echo '<table class="'.$class_table.'" style="width: 95%;" cellspacing="0">
        <thead><tr>';
}

$align = 'left';

if($req_mode != 'csv') 
{
    // die Überschriften der Spalten einlesen  
    foreach($anzeige_liste as $member => $memberdata) 
    {
        // die Laufende Nummer noch davorsetzen
        echo '<th style="text-align: '.$align.';">'.$g_l10n->get('SYS_ABR_NO').'</th>'; 
                
        foreach($memberdata as $key => $data) 
        {             
            echo '<th style="text-align: '.$align.';">'.$key.'</th>';            				            
        } 
        echo '</tr></thead><tbody>';
        break;
    } 
}

if($req_mode == 'html' && $g_preferences['lists_members_per_page'] > 0)
{
    // Anzahl der Mitglieder, die auf einer Seite angezeigt werden
    $members_per_page = $g_preferences['lists_members_per_page'];     
}
else
{
    $members_per_page = $num_members;
}

$j = 0;
$k = 0;
$irow = $req_start + 1;  // Zaehler fuer die jeweilige Zeile
$align = 'left';

if($req_mode != 'csv')
{ 
    foreach($anzeige_liste as $member => $memberdata) 
    {
        if($j > $req_start-1 && $k < $members_per_page)
        {   
            // die Laufende Nummer noch davorsetzen
            echo '<td style="text-align: '.$align.';">'.$irow.'</td>';   
                
    		foreach($memberdata as $key => $data) 
            {
                echo '<td style="text-align: '.$align.';">'.$data.'</td>';
            } 
            echo '</tr>';
            $k++;   
            $irow++;
        }
        $j++;
    }
}
 
// jetzt wird die Variable $str_export für die CSV-Datei befüllt  
if($req_mode == 'csv')
{
    $dta_file->setAccountFileSender(
        array(
            "name"           => $beitrag_inhaber_target,
            "bank_code"      => $beitrag_blz_target,
            "account_number" => $beitrag_ktonr_target
        )
    );

    // die Überschriften der Spalten einlesen  
    foreach($anzeige_liste as $member => $memberdata) 
    {
        // die Laufende Nummer noch davorsetzen
        $str_export .= $value_quotes. $g_l10n->get('SYS_ABR_NO'). $value_quotes;      
                
        foreach($memberdata as $key => $data) 
        {             
            $str_export .= $separator. $value_quotes. $key. $value_quotes;                  				            
        } 
        $str_export .= "\n";
        break;
    } 
  
    $summe_betrag_familie = 0;
    $summe_betrag_mitglied = 0;
    
    $krow = 1;         //$krow ist der Zähler für die jeweiligen Zeilen in der CSV-Datei
    foreach($anzeige_liste as $member => $memberdata) 
    {

        if (($export_mode == 'rechnung') && ($memberdata['Bezahlart'] == 'Rechnung'))
        {
            $str_export .= $value_quotes. $krow. $value_quotes;
            $krow++;  
                
            foreach($memberdata as $key => $data) 
            {                          
                $str_export .= $separator. $value_quotes. $data. $value_quotes;                  				            
            } 
            $str_export .= "\n";
        
            if ($memberdata['Beitragsart'] == "Einzel")
            {
                $summe_beitrag_mitglied += $memberdata['Beitrag'];    
            }
            else 
            {
                $summe_beitrag_familie += $memberdata['Beitrag'];    
            }
        }
        if (($export_mode == 'lastschrift') && ($memberdata['Bezahlart'] == 'Konto'))
        {
            $str_export .= $value_quotes. $krow. $value_quotes;
            $krow++;  
                
		    foreach($memberdata as $key => $data) 
            {                          
                $str_export .= $separator. $value_quotes. $data. $value_quotes;                  				            
            } 
            $str_export .= "\n";
        
            if ($memberdata['Beitragsart'] == 'Einzel')
            {
                $summe_beitrag_mitglied += $memberdata['Beitrag'];    
            }
            else 
            {
                $summe_beitrag_familie += $memberdata['Beitrag'];    
            }
        }
        if ($export_mode == 'gesamt')
        {
            $str_export .= $value_quotes. $krow. $value_quotes;
            $krow++;  
                
            foreach($memberdata as $key => $data) 
            {                          
                $str_export .= $separator. $value_quotes. $data. $value_quotes;                  				            
            } 
            $str_export .= "\n";
        
            if ($memberdata['Beitragsart'] == 'Einzel')
            {
                $summe_beitrag_mitglied += $memberdata['Beitrag'];    
            }
            else 
            {
                $summe_beitrag_familie += $memberdata['Beitrag'];    
            }
        }
        if (($export_mode == 'dtaus') && ($memberdata['Bezahlart'] == 'Konto'))
        {
            $dta_file->addExchange(
                array(
                    "name"           => $memberdata['Ort/Kontoinhaber'],        // Name of account owner.
                    "bank_code"      => $memberdata['PLZ/Bankleitzahl'],        // Bank code.
                    "account_number" => $memberdata['Adresse/Kontonummer'],     // Account number.
                ),
                number_format($memberdata['Beitrag'],2,".", ""),                // Amount of money                                      
                $memberdata['Beitragstext']    // Description of the transaction ("Verwendungszweck").
            );     
        }    
    } 

    $str_export .= "\n";
    $str_export .= $value_quotes.'Beitrag - nur Familien'.$value_quotes.$separator.$value_quotes.number_format($summe_beitrag_familie,2,",", ".").$value_quotes;
    $str_export .= "\n";
    $str_export .= $value_quotes.'Beitrag - nur Mitglieder'.$value_quotes.$separator.$value_quotes.number_format($summe_beitrag_mitglied,2,",", ".").$value_quotes;
    $str_export .= "\n";
    $str_export .= $value_quotes.'Gesamtbeitrag'.$value_quotes.$separator.$value_quotes.number_format($summe_beitrag_mitglied + $summe_beitrag_familie,2,",", ".").$value_quotes;
    $str_export .= "\n";

    //wenn DTA - Datenträgeraustauschdatei, dann $str_export mit den dtaus-Daten überschreiben
    if ($export_mode == 'dtaus') 
    {
        $str_export = $dta_file->getFileContent();           
    }   

    //wenn DTA - Begleitzettel, dann $str_export mit den Daten für den Begleitzettel überschreiben
    if ($dtaus_mode == 'dtaus-bglz')
    {
        $meta = $dta_file->getMetaData();
         
        $str_export = $value_quotes.'Begleitzettel'.$value_quotes."\n\n";
        $str_export .= $value_quotes.'Belegloser Datenträgeraustausch'.$value_quotes."\n\n"; 
        $str_export .= $value_quotes.'Sammeleinziehungsauftrag'.$value_quotes."\n\n\n"; 
        $str_export .= $value_quotes.'Erstellungsdatum:'.$value_quotes.$separator.$value_quotes.strftime("%d.%m.%y", $meta["date"]).$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Anzahl der Überweisungen:'.$value_quotes.$separator.$value_quotes.$meta["count"].$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Summe der Beträge in EUR:'.$value_quotes.$separator.$value_quotes.$meta["sum_amounts"].$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Kontrollsumme Kontonummern:'.$value_quotes.$separator.$value_quotes.$meta["sum_accounts"].$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Kontrollsumme Bankleitzahlen:'.$value_quotes.$separator.$value_quotes.$meta["sum_bankcodes"].$value_quotes."\n\n\n";  
        $str_export .= $value_quotes.'Auftraggeber'.$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Name:'.$value_quotes.$separator.$value_quotes.$beitrag_inhaber_target.$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Beauftragtes Bankinstitut:'.$value_quotes.$separator.$value_quotes.$beitrag_bank_target.$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Bankleitzahl:'.$value_quotes.$separator.$value_quotes.$meta["sender_bank_code"].$value_quotes."\n\n";  
        $str_export .= $value_quotes.'Kontonummer:'.$value_quotes.$separator.$value_quotes.$meta["sender_account"].$value_quotes."\n\n\n";  
        $str_export .= $value_quotes.'____________________________________________________'.$value_quotes."\n\n"; 
        $str_export .= $value_quotes.'Ort, Datum, Unterschrift'.$value_quotes."\n\n\n"; 
        $str_export .= $value_quotes.'Auftragsdatei: '.$beitrag_filename_dtaus0.$value_quotes."\n";
    } 

    // nun die erstellte CSV-Datei an den User schicken
    header('Content-Type: text/comma-separated-values; charset='.$charset);
    //header('Content-Type: text/html; charset='.$charset);
    header('Content-Disposition: attachment; filename="'.$filename.'"');
	if($charset == 'iso-8859-1')
	{
		echo utf8_decode($str_export);
	}
	else
	{
		echo $str_export;
	}
}
else
{
    echo '</tbody></table>';

    if($req_mode != 'print')
    {
        // Navigation mit Vor- und Zurueck-Buttons
        $base_url = $g_root_path. '/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php?mode='.$req_mode.'&einzug='.$req_einzug;
     
        echo generatePagination($base_url, $num_members, $members_per_page, $req_start, TRUE);
    }

    if($req_mode == 'print')
    {
        echo '</body></html>';
    }
    else
    {    
        echo '
        <ul class="iconTextLinkList">
            <li>
                <span class="iconTextLink">
                    <a href="'.$g_root_path.'/adm_program/system/back.php"><img 
                    src="'. THEME_PATH. '/icons/back.png" alt="'.$g_l10n->get('SYS_BACK').'" /></a>
                    <a href="'.$g_root_path.'/adm_program/system/back.php">'.$g_l10n->get('SYS_BACK').'</a>
                </span>
            </li>
        </ul>';
    
       require(SERVER_PATH. '/adm_program/system/overall_footer.php');
    }
}

function berechne_spartenbeitrag(&$dom_array,$sub_array_key,$spartenmitglieder,$current_year,$beitrag_sparte,$member_key,$member_data,$beitrag_abrunden)
{
	//ist das Mitglied ein Spartenmitglied
    if(in_array($member_key,$spartenmitglieder))
    {   
        $beitragsparte = $beitrag_sparte['betrag'];
        $text = $beitrag_sparte['text'];   
        //wenn das Mitglied erst im Abrechnungsjahr beigetreten ist: anteilig
        if (date("Y", strtotime($member_data['Beitritt'])) == (date("Y", strtotime($current_year."-12-31")))) 
        {      
            $beitragsparte = ($beitragsparte * (13 - date("n", strtotime($member_data['Beitritt'])))) / 12;
            $text = $text." (anteilig)";
            
			//wenn der anteilige Spartenbeitrag kleiner als der Mindesteinzugsbetrag ist wird nicht eingezogen
            if ($beitragsparte < $beitrag_sparte['mindesteinzug'])
            {
			    $beitragsparte = 0;
            }  
        }
        //das Mitglied ist nach dem Abrechnungsjahr beigetreten         
        elseif ((date("Y",(strtotime($member_data['Beitritt'])))) > (date("Y", strtotime($current_year."-12-31")))) 
        {
            $beitragsparte = 0;
        }
                          
        //letzer möglicher Fall: das Mitglied ist vor dem Abrechnungsjahr beigetreten: Vollbeitrag: keine Aktion 
        
        //Beitrag abrunden
        if ($beitrag_abrunden == 1)
        {
            $beitragsparte = floor($beitragsparte/10000)*10000;
        }
                        
        // Addition und Text schreiben, nur wenn temporärer Beitrag != 0 
        if ($beitragsparte != 0)
        {    
            $dom_array[$sub_array_key]['Beitrag']  += $beitragsparte;
   			$dom_array[$sub_array_key]['Beitragstext']  = $dom_array[$sub_array_key]['Beitragstext']." + ".$text." ".$member_data['Vorname']." ".number_format($beitragsparte/10000.0,2,",", ".")." EUR";
        }      
    } 
}

function berechne_mitgliedsbeitrag(&$dom_array,$sub_array_key,$studenten,$beitrag_beitraege_einzel,$beitrag_ermaessigt,$current_year,$last_day,$last_month,$last_year,$key,$member_data,$monate,$beitrag_abrunden)
{
	$age = date("Y", strtotime($current_year."-12-31")) - date("Y", strtotime($member_data['Geburtstag']));
	$beitrag = 0;
	$text = "";
		        	
	for ($i = 1; $i < sizeof($beitrag_beitraege_einzel); $i = $i + 1) 
    {
		if (($age > $beitrag_beitraege_einzel[$i - 1]['alter']) && ($age <= $beitrag_beitraege_einzel[$i]['alter'])) 
        {
			$beitrag = $beitrag_beitraege_einzel[$i - 1]['betrag'];
			$text  = (($monate != 12)?" + ":"").$beitrag_beitraege_einzel[$i - 1]['text'];
		}
	}
	if ($beitrag == 0) 
    {
		$beitrag = $beitrag_beitraege_einzel[sizeof($beitrag_beitraege_einzel) - 1]['betrag'];
		$text  = (($monate != 12)?" + ":"").$beitrag_beitraege_einzel[sizeof($beitrag_beitraege_einzel) - 1]['text'];
	}
		
	//ist das Mitglied ein Mitglied der ermäßigten Kategorie
    if(in_array($key,$studenten) )
    {
        $beitrag =  $beitrag_ermaessigt['betrag'];
        $text = (($monate != 12)?" + ":"").$beitrag_ermaessigt['text'];
    }
        
	// Falls das Mitglied erst in dem angegebenen Jahr eingetreten ist, wird der Beitrag lediglich teilweise (monatlich eingezogen)
	if ((date("Y", strtotime($current_year."-12-31")) == date("Y", strtotime($member_data['Beitritt']))) && ($monate == 12))
    {
        //anteiligen Mitgliedsbeitrag errechnen
		$beitrag = ($beitrag * (13 - date("n", strtotime($member_data['Beitritt'])))) / 12;
			
		// Liegt das Beitrittsdatum vor dem Datum des letzten Einzugs, dann wird nicht eingezogen, weil der Betrag
		// bereits in der vorherigen Abrechnung erhoben wurde
		if ((date("U", strtotime($last_year."-".$last_month."-".$last_day)) > date("U", strtotime($member_data['Beitritt'])))&& checkdate($last_month,$last_day,$current_year)) 
        {
			$beitrag = 0;
		}
		else
		{
			$text = $text." (anteilig)";
		}
	}
    //anteiligen Mitgliedsbeitrag errechnen , nur bei Aufruf aus der Routine Familienbeitrag 
	elseif ($monate != 12)
	{
		$beitrag = ($beitrag * $monate / 12);
		$text = $text." (anteilig)";
	}
		
    //Beitrag abrunden
    if ($beitrag_abrunden == 1)
    {
        $beitrag = floor($beitrag/10000)*10000;
    }
        
    // Addition und Text schreiben, nur wenn temporärer Beitrag != 0 
    if ($beitrag != 0)
    {    
        $dom_array[$sub_array_key]['Beitrag']  = $dom_array[$sub_array_key]['Beitrag'] + $beitrag;
  		$dom_array[$sub_array_key]['Beitragstext']  = $dom_array[$sub_array_key]['Beitragstext']." ".$text." ".number_format($beitrag/10000.0,2,",", ".")." EUR";
    }      	     
}

function berechne_familienbeitrag(&$dom_array,$sub_array_key,$beitrag_familie,$schluessel,$beitrag_abrunden)
{
    $familycreate =  strtotime($dom_array[$sub_array_key]['familycreate']);
    $beitragfamilie = $beitrag_familie['betrag'];
    
    if ($schluessel != "voll")
    {    
        $beitragfamilie = ($beitragfamilie * (13 - date("n", $familycreate))) / 12;  
    }
    
    //Beitrag abrunden
    if ($beitrag_abrunden == 1)
    {
        $beitragfamilie = floor($beitragfamilie/10000)*10000;
    } 
        
    $dom_array[$sub_array_key]['Beitrag'] = $beitragfamilie;      
    $dom_array[$sub_array_key]['Beitragstext'] = $dom_array[$sub_array_key]['Beitragstext']." ".$beitrag_familie['text'].(($schluessel != "voll")?" (anteilig) ":" ").number_format($beitragfamilie/10000.0,2,",", ".")." EUR" ;   	     
}

function befuelle_array(&$g_db, $kategorie)
{
     // Hilfsarray befüllen (alle Rollen innerhalb dieser Kategorie)
	$hilfsarray = array();
	$result = $g_db->query("select rol.rol_name, rol.rol_id from adm_categories as cat, adm_roles as rol where cat.cat_name='".$kategorie."' and cat.cat_id=rol.rol_cat_id;");
 
	while($row = $g_db->fetch_array($result))
	{
		$hilfsarray[$row['rol_id']]['name'] = $row['rol_name'];
		$hilfsarray[$row['rol_id']]['members'] = array();
	}

	// alle Mitlieder aus diesen Rollen abfragen
	$hauptarray = array();
	foreach($hilfsarray as $hilfsid => $dummy)
	{
		$result = $g_db->query("select mem.mem_usr_id from adm_members as mem, adm_roles as rol where mem.mem_rol_id=rol.rol_id  and (mem.mem_end='9999-12-31') and rol.rol_id=".$hilfsid.";");
		while($row = $g_db->fetch_array($result))
		{
			$hauptarray[]= $row['mem_usr_id']; 
		}
	}
    return $hauptarray;    
}

function befuelle_anzeigeliste($families, $members)
{
    //die Daten, die angezeigt werden sollen, sind in zwei unterschiedlich
    //strukturierten arrays. Diese werden hier zusammengefügt.

    $anzeige = array();
    $i = 1;
    
    foreach($members as $member => $memberdata) 
    {
		if ($memberdata['Beitrag'] != 0.0) 
        {
			if(($memberdata['Kontonummer'] == "") || ($memberdata['Bankleitzahl'] == "") || ($memberdata['Kontoinhaber'] == "")) 
            {
				$anzeige[$i]['Bezahlart'] = "Rechnung";
				$anzeige[$i]['Adresse/Kontonummer'] = $memberdata['Adresse'];
				$anzeige[$i]['PLZ/Bankleitzahl'] = $memberdata['PLZ'];
				$anzeige[$i]['Ort/Kontoinhaber'] = $memberdata['Ort'];
				
			} 
            else 
            {
				$anzeige[$i]['Bezahlart'] = "Konto";
				$anzeige[$i]['Adresse/Kontonummer'] = $memberdata['Kontonummer'];
				$anzeige[$i]['PLZ/Bankleitzahl'] = $memberdata['Bankleitzahl'];
				$anzeige[$i]['Ort/Kontoinhaber'] = $memberdata['Kontoinhaber'];
			}
			$anzeige[$i]['Nachname'] = $memberdata['Nachname'];
			$anzeige[$i]['Vorname'] = $memberdata['Vorname'];
			$anzeige[$i]['Geschlecht'] = ($memberdata['Geschlecht'] == 1 ? "m":"w");
			$anzeige[$i]['Beitragsart'] = "Einzel";
			$anzeige[$i]['Beitragstext'] = $memberdata['Beitragstext'];
			$anzeige[$i]['Beitrag'] = number_format($memberdata['Beitrag']/10000.0,2,",", ".");
		}
		$i++;
	}

	foreach($families as $family => $familydata) 
    {
		if ($familydata['Beitrag'] != 0.0) 
        {
			foreach($familydata['members'] as $member => $memberdata)
            {
				if(($memberdata['Kontonummer'] == "") || ($memberdata['Bankleitzahl'] == "") || ($memberdata['Kontoinhaber'] == "")) 
                {
					$anzeige[$i]['Bezahlart'] = "Rechnung";
					$anzeige[$i]['Adresse/Kontonummer'] = $memberdata['Adresse'];
					$anzeige[$i]['PLZ/Bankleitzahl'] = $memberdata['PLZ'];
					$anzeige[$i]['Ort/Kontoinhaber'] = $memberdata['Ort'];
					$anzeige[$i]['Nachname'] = $familydata['name'];
                    $anzeige[$i]['Vorname'] = "";
                    $anzeige[$i]['Geschlecht'] = "";
                    $anzeige[$i]['Beitragsart'] = "Familie";
                    $anzeige[$i]['Beitragstext'] = $familydata['Beitragstext'];
                    $anzeige[$i]['Beitrag'] = number_format($familydata['Beitrag']/10000.0,2,",", ".");
				} 
                else 
                {
				    $anzeige[$i]['Bezahlart'] = "Konto";
					$anzeige[$i]['Adresse/Kontonummer'] = $memberdata['Kontonummer'];
					$anzeige[$i]['PLZ/Bankleitzahl'] = $memberdata['Bankleitzahl'];
					$anzeige[$i]['Ort/Kontoinhaber'] = $memberdata['Kontoinhaber'];
					$anzeige[$i]['Nachname'] = $familydata['name'];
			        $anzeige[$i]['Vorname'] = "";
			        $anzeige[$i]['Geschlecht'] = "";
			        $anzeige[$i]['Beitragsart'] = "Familie";
			        $anzeige[$i]['Beitragstext'] = $familydata['Beitragstext'];
			        $anzeige[$i]['Beitrag'] = number_format($familydata['Beitrag']/10000.0,2,",", ".");
			        break;
				}
			}
            $i++;	
		}
	}  
    return $anzeige;
}

function erzeuge_current_year_liste()
{
    $ret_str = "";
    for ($i = -10; $i < 2; $i = $i + 1) 
    {
			$ret_str = $ret_str.'<option value='.(date('Y') + $i).'>'.(date('Y') + $i).'</option>';		
	}
    return $ret_str;
}

function erzeuge_last_einzug_liste($beitrag_last_einzugtag)
{
    $ret_str = "";
    for ($i = 1; $i < 13; $i = $i + 1) 
    {
			$ret_str = $ret_str.'<option value='.date('Y').'-'.$i.'-'.$beitrag_last_einzugtag.'>'.$beitrag_last_einzugtag.'.'.$i.'.'.date('Y').'</option>';		
	}
    return $ret_str;
}
?>