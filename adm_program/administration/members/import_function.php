<?php
/******************************************************************************
 * Prepare values of import form for further processing
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

require_once('../../system/common.php');
require_once('../../system/login_valid.php');
require_once('../../system/classes/table_roles.php');

// Initialize and check the parameters
$postImportCoding   = admFuncVariableIsValid($_POST, 'import_coding', 'string', null, true, array('iso-8859-1', 'utf-8'));
$postRoleId         = admFuncVariableIsValid($_POST, 'import_role_id', 'numeric', null, true);
$postUserImportMode = admFuncVariableIsValid($_POST, 'user_import_mode', 'numeric', null, true);

$_SESSION['import_request'] = $_REQUEST;
unset($_SESSION['import_csv_request']);

// nur berechtigte User duerfen User importieren
if(!$gCurrentUser->editUsers())
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

if(strlen($_FILES['userfile']['tmp_name']) == 0)
{
    $gMessage->show($gL10n->get('SYS_FIELD_EMPTY', $gL10n->get('SYS_FILE')));
}
else if($_FILES['userfile']['error'] == 1)
{
    //Dateigroesse ueberpruefen Servereinstellungen
    $gMessage->show($gL10n->get('SYS_FILE_TO_LARGE_SERVER', $gPreferences['max_file_upload_size']));
}
else if($postRoleId == 0)
{
    $gMessage->show($gL10n->get('SYS_FIELD_EMPTY', $gL10n->get('SYS_ROLE')));
}

// Rolle einlesen und pruefen, ob der User diese selektieren kann und dadurch nicht
// evtl. ein Rollenzuordnungsrecht bekommt, wenn er es vorher nicht hatte
$role = new TableRoles($gDb, $postRoleId);

if($gCurrentUser->viewRole($role->getValue('rol_id')) == false
|| ($gCurrentUser->assignRoles() == false && $role->getValue('rol_assign_roles') == false))
{
    $gMessage->show($gL10n->get('MEM_ROLE_SELECT_RIGHT', $role->getValue('rol_name')));
}

// read file in an array; auto-detect the line endings of different os
ini_set('auto_detect_line_endings', 1);
$_SESSION['file_lines']       = file($_FILES['userfile']['tmp_name']);
$_SESSION['rol_id']           = $role->getValue('rol_id');
$_SESSION['user_import_mode'] = $postUserImportMode;

if($postImportCoding == 'iso-8859-1')
{
    // Daten der Datei erst einmal in UTF8 konvertieren, damit es damit spaeter keine Probleme gibt
    foreach($_SESSION['file_lines'] as $key => $value)
    {
        $_SESSION['file_lines'][$key] = utf8_encode($value);
    }
}
    
// CSV-Import (im Moment gibt es nur diesen, spaeter muss hier dann unterschieden werden)
header('Location: '.$g_root_path.'/adm_program/administration/members/import_csv_config.php');
exit();

?>