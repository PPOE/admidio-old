<?php
/******************************************************************************
 * Various functions for rooms handling
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Parameters:
 * 
 * room_id : ID of room, that should be shown
 * mode :    1 - create or edit room
 *           2 - delete room
 *****************************************************************************/

require_once('../../system/common.php');
require_once('../../system/classes/table_rooms.php');
require_once('../../libs/htmlawed/htmlawed.php');

// Initialize and check the parameters
$getRoomId = admFuncVariableIsValid($_GET, 'room_id', 'numeric', 0);
$getMode   = admFuncVariableIsValid($_GET, 'mode', 'numeric', null, true);

// nur berechtigte User duerfen die Profilfelder bearbeiten
if (!$gCurrentUser->isWebmaster())
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

// Raumobjekt anlegen
$room = new TableRooms($gDb);
if($getRoomId > 0)
{
    $room->readData($getRoomId);   
}

if($getMode == 1)
{
    $_SESSION['rooms_request'] = $_REQUEST;

    if(strlen($_POST['room_name']) == 0)
    {
        $gMessage->show($gL10n->get('SYS_FIELD_EMPTY', $gL10n->get('SYS_ROOM')));
    }
    if(strlen($_POST['room_capacity']) == 0)
    {
        $gMessage->show($gL10n->get('SYS_FIELD_EMPTY', $gL10n->get('ROO_CAPACITY')));
    }

    // make html in description secure
    $_POST['room_description'] = htmLawed(stripslashes($_POST['room_description']), array('safe' => 1));

    // POST Variablen in das Termin-Objekt schreiben
    foreach($_POST as $key => $value)
    {
        if(strpos($key, 'room_') === 0)
        {
            $room->setValue($key, $value);
        }
    }
    // Daten in Datenbank schreiben
    $return_code = $room->save();
    
    unset($_SESSION['rooms_request']);
    $_SESSION['navigation']->deleteLastUrl();

    header('Location: '. $_SESSION['navigation']->getUrl());
    exit();
}
// Löschen des Raums
else if($getMode == 2) 
{
    $sql = 'SELECT * FROM '.TBL_DATES.' WHERE dat_room_id = '.$getRoomId;
    $result = $gDb->query($sql);
    $row = $gDb->num_rows($result);
    if($row == 0)
    {
        $room->delete();
        echo 'done';
    }
    // Loeschen erfolgreich -> Rueckgabe fuer XMLHttpRequest
    
}
?>
