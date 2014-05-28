<?php
/******************************************************************************
 * Validate login data, create cookie and sign in the user to Admidio
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

require_once('../adm_program/system/common.php');
require_once('config.php');

// Initialize parameters
$userFound  = 0;
$loginname  = '';
$password   = '';

function hex2str($hex) {
  $str = "";
  for($i=0;$i<strlen($hex);$i+=2)
  $str .= chr(hexdec(substr($hex,$i,2)));
  return $str;
}

// Filter parameters
// parameters could be from login dialog or login plugin !!!
if(isset($_POST['usr_login_name']) && strlen($_POST['usr_login_name']) > 0)
{
    $loginname = hex2str($_POST['usr_login_name']);
    $password  = hex2str($_POST['usr_password']);
}
/*else if(isset($_GET['usr_login_name']) && strlen($_GET['usr_login_name']) > 0)
{
    $loginname = hex2str($_GET['usr_login_name']);
    $password  = hex2str($_GET['usr_password']);
}*/
if(strlen($loginname) == 0 || strlen($password) == 0)
{
  echo "ERROR\n";
  exit();
}

foreach ($botLogins as $botLogin)
{
  if ($loginname == $botLogin[0] && $password == $botLogin[1])
  {
    echo "OK\n";
    exit();
  }
}
// check name and password
// user must have membership of one role of the organization

$sql    = 'SELECT usr_id
             FROM '. TBL_USERS. ', '. TBL_MEMBERS. '
            WHERE UPPER(usr_login_name) LIKE UPPER(\''.mysql_escape_string($loginname).'\')
              AND usr_valid      = 1
              AND mem_usr_id     = usr_id
              AND mem_rol_id     = 2
              AND mem_begin     <= \''.DATE_NOW.'\'
              AND mem_end        > \''.DATE_NOW.'\';';
$result = $gDb->query($sql);

$userFound = $gDb->num_rows($result);
$userRow   = $gDb->fetch_array($result);

if ($userFound >= 1)
{
    // create user object
    $gCurrentUser = new User($gDb, $gProfileFields, $userRow['usr_id']);
    
    if($gCurrentUser->getValue('usr_number_invalid') >= 3)
    {
        // wenn innerhalb 15 min. 3 falsche Logins stattfanden -> Konto 15 min. sperren
        if(time() - strtotime($gCurrentUser->getValue('usr_date_invalid', 'Y-m-d H:i:s')) < 900)
        {
            $gCurrentUser->clear();
            echo "ERROR\n";
            exit();
        }
    }

    if($gCurrentUser->checkPassword($password) == true)
    {
        //$gCurrentSession->setValue('ses_usr_id', $gCurrentUser->getValue('usr_id'));
        //$gCurrentSession->save();

        $gCurrentUser->setValue('usr_last_session_id', NULL);

        // Logins zaehlen und aktuelles Login-Datum aktualisieren
        $gCurrentUser->updateLoginData();
        echo "OK\n";
        exit();
    }
    else
    {
        // ungueltige Logins werden mitgeloggt
        
        if($gCurrentUser->getValue('usr_number_invalid') >= 3)
        {
            $gCurrentUser->setValue('usr_number_invalid', 1);
        }
        else
        {
            $gCurrentUser->setValue('usr_number_invalid', $gCurrentUser->getValue('usr_number_invalid') + 1);
        }
        $gCurrentUser->setValue('usr_date_invalid', DATETIME_NOW);
        $gCurrentUser->save(false);   // Zeitstempel nicht aktualisieren
        $gCurrentUser->clear();

        echo "FAIL\n";
        exit();
    }
}
echo "FAIL\n";
exit();
?>
