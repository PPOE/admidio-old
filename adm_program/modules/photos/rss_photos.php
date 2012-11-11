<?php
/******************************************************************************
 * RSS - Feed fuer Photos
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Erzeugt einen RSS 2.0 - Feed mit Hilfe der RSS-Klasse fuer die 10 neuesten Fotoalben
 *
 * Spezifikation von RSS 2.0: http://www.feedvalidator.org/docs/rss2.html
 *
 * Parameters:
 *
 * headline  - Ueberschrift fuer den RSS-Feed
 *             (Default) Fotoalben
 *
 *****************************************************************************/

require_once('../../system/common.php');
require_once('../../system/classes/rss.php');
require_once('../../system/classes/table_photos.php');

// Nachschauen ob RSS ueberhaupt aktiviert ist...
if ($gPreferences['enable_rss'] != 1)
{
    $gMessage->setForwardUrl($gHomepage);
    $gMessage->show($gL10n->get('SYS_RSS_DISABLED'));
}

// pruefen ob das Modul ueberhaupt aktiviert ist
if ($gPreferences['enable_photo_module'] == 0)
{
    // das Modul ist deaktiviert
    $gMessage->show($gL10n->get('SYS_MODULE_DISABLED'));
}
elseif($gPreferences['enable_photo_module'] == 2)
{
    // nur eingeloggte Benutzer duerfen auf das Modul zugreifen
    require_once('../../system/login_valid.php');
}

// Initialize and check the parameters
$getHeadline = admFuncVariableIsValid($_GET, 'headline', 'string', $gL10n->get('PHO_PHOTO_ALBUMS'));

// die neuesten 10 Fotoalben aus der DB fischen...
$sql = 'SELECT pho.*,
               cre_surname.usd_value as create_surname, cre_firstname.usd_value as create_firstname,
               cha_surname.usd_value as change_surname, cha_firstname.usd_value as change_firstname
          FROM '. TBL_PHOTOS. ' pho
          LEFT JOIN '. TBL_USER_DATA .' cre_surname
            ON cre_surname.usd_usr_id = pho_usr_id_create
           AND cre_surname.usd_usf_id = '.$gProfileFields->getProperty('LAST_NAME', 'usf_id').'
          LEFT JOIN '. TBL_USER_DATA .' cre_firstname
            ON cre_firstname.usd_usr_id = pho_usr_id_create
           AND cre_firstname.usd_usf_id = '.$gProfileFields->getProperty('FIRST_NAME', 'usf_id').'
          LEFT JOIN '. TBL_USER_DATA .' cha_surname
            ON cha_surname.usd_usr_id = pho_usr_id_change
           AND cha_surname.usd_usf_id = '.$gProfileFields->getProperty('LAST_NAME', 'usf_id').'
          LEFT JOIN '. TBL_USER_DATA .' cha_firstname
            ON cha_firstname.usd_usr_id = pho_usr_id_change
           AND cha_firstname.usd_usf_id = '.$gProfileFields->getProperty('FIRST_NAME', 'usf_id').'
         WHERE (   pho_org_shortname = \''. $gCurrentOrganization->getValue('org_shortname'). '\'
               AND pho_locked = 0)
         ORDER BY pho_timestamp_create DESC
         LIMIT 10';
$result = $gDb->query($sql);

$photo_album = new TablePhotos($gDb);

// ab hier wird der RSS-Feed zusammengestellt

// Ein RSSfeed-Objekt erstellen
$rss = new RSSfeed($gCurrentOrganization->getValue('org_homepage'), $gCurrentOrganization->getValue('org_longname'). ' - '.$getHeadline, 
		$gL10n->get('PHO_RECENT_ALBUMS_OF_ORGA', $gCurrentOrganization->getValue('org_longname')));

// Dem RSSfeed-Objekt jetzt die RSSitems zusammenstellen und hinzufuegen
while ($row = $gDb->fetch_array($result))
{
    // Daten in ein Photo-Objekt uebertragen
    $photo_album->clear();
    $photo_album->setArray($row);

    // Die Attribute fuer das Item zusammenstellen

    //Titel
    $parents = '';
    $pho_parent_id = $photo_album->getValue('pho_pho_id_parent');
    //Titel muss mit Ordnerstruktur zusammengesetzt werden
    while ($pho_parent_id != NULL)
    {
        //Erfassen des Eltern Albums
        $sql=' SELECT *
                 FROM '. TBL_PHOTOS. '
                WHERE pho_id = '.$pho_parent_id;
        $result_parents = $gDb->query($sql);
        $adm_photo_parent = $gDb->fetch_array($result_parents);

        //Link zusammensetzen
        $parents = ' > '.$adm_photo_parent['pho_name'].$parents;

        //Elternveranst
        $pho_parent_id=$adm_photo_parent['pho_pho_id_parent'];
    }
    $title = $gL10n->get('PHO_PHOTO_ALBUMS').$parents.' > '.$photo_album->getValue('pho_name');

    //Link
    $link  = $g_root_path.'/adm_program/modules/photos/photos.php?pho_id='. $photo_album->getValue('pho_id');

    //Inhalt zusammensetzen
    $description = $gL10n->get('PHO_PHOTO_ALBUMS').$parents.' > '. $photo_album->getValue('pho_name');
    $description = $description. '<br /><br /> '.$gL10n->get('PHO_PHOTOS').': '.$photo_album->countImages();
    $description = $description. '<br /> '.$gL10n->get('SYS_DATE').': '.$photo_album->getValue('pho_begin', $gPreferences['system_date']);
    //Enddatum nur wenn anders als startdatum
    if($photo_album->getValue('pho_end') != $photo_album->getValue('pho_begin'))
    {
        $description = $gL10n->get('SYS_DATE_FROM_TO', $description, $photo_album->getValue('pho_end', $gPreferences['system_date']));
    }
    $description = $description. '<br />'.$gL10n->get('PHO_PHOTOGRAPHER').': '.$photo_album->getValue('pho_photographers');

    //die letzten fuenf Fotos sollen als Beispiel genutzt werden
    if($photo_album->getValue('pho_quantity') >0)
    {
        $description = $description. '<br /><br />'.$gL10n->get('SYS_PREVIEW').':<br />';
        for($bild=$photo_album->getValue('pho_quantity'); $bild>=$photo_album->getValue('pho_quantity')-4 && $bild>0; $bild--)
        {
            $bildpfad = SERVER_PATH. '/adm_my_files/photos/'.$photo_album->getValue('pho_begin','Y-m-d').'_'.$photo_album->getValue('pho_id').'/'.$bild.'.jpg';
            //Zu Sicherheit noch überwachen ob das Foto existiert, wenn ja raus damit
            if (file_exists($bildpfad))
            {
                $description = $description. '<img src="'.$g_root_path.'/adm_program/modules/photos/photo_show.php?pho_id='.
                               $photo_album->getValue('pho_id').'&amp;photo_nr='.$bild.'&amp;pho_begin='.$photo_album->getValue('pho_begin', 'Y-m-d').
                               '&amp;thumb=1" border="0" />&nbsp;';
            }
        }
    }

    //Link zur Momepage
    $description = $description. '<br /><br /><a href="'.$link.'">'. $gL10n->get('SYS_LINK_TO', $gCurrentOrganization->getValue('org_homepage')). '</a>';

    // Den Autor und letzten Bearbeiter des Albums ermitteln und ausgeben
    $description = $description. '<br /><br /><i>'.$gL10n->get('SYS_CREATED_BY', $photo_album->getValue('create_firstname'). ' '. $photo_album->getValue('create_surname'), $photo_album->getValue('pho_timestamp_create')). '</i>';

    if($photo_album->getValue('pho_usr_id_change') > 0)
    {
        $description = $description. '<br /><i>'.$gL10n->get('SYS_LAST_EDITED_BY', $photo_album->getValue('change_firstname'). ' '. $photo_album->getValue('change_surname'), $photo_album->getValue('pho_timestamp_change')). '</i>';
    }

    $pubDate = date('r',strtotime($photo_album->getValue('pho_timestamp_create')));

    // Item hinzufuegen
    $rss->addItem($title, $description, $pubDate, $link);
}

// jetzt nur noch den Feed generieren lassen
$rss->buildFeed();

?>