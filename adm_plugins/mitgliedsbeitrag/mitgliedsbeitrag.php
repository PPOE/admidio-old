<?php
/******************************************************************************
 * Mitgliedsbeitrag
 *
 * Version 2.2.0
 *
 * Dieses Plugin berechnet Mitgliedsbeiträge und erzeugt eine dtaus-Datei
 * im Datenträgeraustauschformat mit dazugehörigem Datenträgerbegleitzettel.
 * Zusätzlich können diverse Listen erstellt werden, um z.B. Serienbriefe
 * für Rechnungen zu generieren.   
 *
 * Copyright    : (c) 2004 - 2011 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Version 1.0.1: Gerald Lutter
 * Datum        : 10.01.2011
 *    
 * Version 2.0.0: rmb
 * Datum        : 12.07.2011    
 * Änderung     : - Neues Feld "Beitritt" für ein Mitglied
 *                - Berechnung eines Spartenbeitrages
 *                - Berechnung eines Schüler- und Studentenbeitrages
 *                - Beiträge können abgerundet werden
 *                - der Kategoriename für Familien ist frei wählbar    
 *  
 * Version 2.1.0: rmb
 * Datum        : 26.10.2011
 * Änderung     : - Dem Plugin wurde eine Weboberfläche verpasst.
 *                - Die erzeugte CSV-Datei wird nicht mehr auf dem Server 
 *                  zwischengespeichert, sie wird in der Listenansicht zum
 *                  Download angeboten. 
 *                - Das zusätzliche Plugin downloadfile.php wird nicht mehr benötigt.       
 *
 * Version 2.2.0: rmb
 * Datum        : 21.11.2011
 * Änderung     : - Das externe Programm dtaus wird nicht mehr benötigt. Durch die
 *                  Integration der Klasse DTA ist es jetzt möglich, direkt die
 *                  dtaus-Datei und den dazugehörigen Begleitzettel zu erstellen. 
 *                - Die Exportdateien und die Bildschirmanzeige wurden 
 *                  in ihrer Struktur vereinheitlicht. Sie weisen jetzt alle
 *                  dieselben Spalten an derselben Position auf.  
 *                - bisher wurden bei einer Familie die Kontodaten eines zufällig
 *                  ausgewählten Mitglieds verwendet. Falls genau bei diesem Mitglied 
 *                  keine Kontodaten hinterlegt waren, wurde auf Rechnung umgestellt.
 *                  Dies wurde geändert. Es werden alle Mitglieder einer Familie
 *                  abgefragt. Nur wenn bei keinem Mitglied Kontodaten hinterlegt sind,
 *                  wird auf Rechnung umgestellt. 
 *                - Die Berechtigung das Plugin aufzurufen, wurde um 
 *                  Rollenmitgliedschaften erweitert.          
 *                 
 *****************************************************************************/

// Pfad des Plugins ermitteln
$plugin_folder_pos = strpos(__FILE__, 'adm_plugins') + 11;
$plugin_file_pos   = strpos(__FILE__, 'mitgliedsbeitrag.php');
$plugin_folder     = substr(__FILE__, $plugin_folder_pos+1, $plugin_file_pos-$plugin_folder_pos-2);

if(!defined('PLUGIN_PATH'))
{
    define('PLUGIN_PATH', substr(__FILE__, 0, $plugin_folder_pos));
}
require_once(PLUGIN_PATH. '/../adm_program/system/common.php');
require_once(PLUGIN_PATH. '/'.$plugin_folder.'/config.php');

// DB auf Admidio setzen, da evtl. noch andere DBs beim User laufen
$g_db->setCurrentDB();

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

// Zeige Link zum Plugin
if($showPlugin == true)
{
    echo '<div id="plugin_'. $plugin_folder. '" class="admPluginContent">
    <div class="admPluginHeader"><h3>Mitgliedsbeitrag</h3></div>
    <div class="admPluginBody">
        <span class="menu"><a href="'. $g_root_path. '/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php?mode=html&einzug=2011"><img 
            style="vertical-align: middle;" src="'. THEME_PATH. '/icons/lists.png" alt="Mitgliedsbeitrag" title="Mitgliedsbeitrag" /></a>
            <a href="'. $g_root_path. '/adm_plugins/mitgliedsbeitrag/mitgliedsbeitrag_show.php? mode=html&einzug=2011">Mitgliedsbeitrag</a></span>
    </div></div>';
}

?>