<?php
/******************************************************************************
 * Constants that are used within Admidio
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

if ('constants.php' == basename($_SERVER['SCRIPT_FILENAME']))
{
    die('This page may not be called directly !');
}

// !!! Please do not edit these version numbers !!!
define('ADMIDIO_VERSION', '2.3.6');
define('BETA_VERSION', '0');
define('MIN_PHP_VERSION', '5.2.0');

if(BETA_VERSION > 0)
{
    define('BETA_VERSION_TEXT', ' Beta '.BETA_VERSION);
}
else
{
    define('BETA_VERSION_TEXT', '');
}


// verschiedene Pfade
define('SERVER_PATH', substr(__FILE__, 0, strpos(__FILE__, 'adm_program')-1));
if(strpos($_SERVER['SCRIPT_FILENAME'], "/adm_") !== false && isset($g_root_path) == true)
{
    // aktuelle aufgerufene Url (klappt nur so, da SSL-Proxies nicht ueber _SERVER ausgelesen werden koennen)
    define('CURRENT_URL',$g_root_path. substr($_SERVER['SCRIPT_FILENAME'], strpos($_SERVER['SCRIPT_FILENAME'], "/adm_")). "?". $_SERVER['QUERY_STRING']);
}
else
{
    define('CURRENT_URL', "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
}

date_default_timezone_set('Europe/Berlin');
define('DATE_NOW', date('Y-m-d', time()));
define('DATETIME_NOW', date('Y-m-d H:i:s', time()));

// Defines fuer alle Datenbanktabellen
define('TBL_ANNOUNCEMENTS',     $g_tbl_praefix. '_announcements');
define('TBL_AUTO_LOGIN',        $g_tbl_praefix. '_auto_login');
define('TBL_CATEGORIES',        $g_tbl_praefix. '_categories');
define('TBL_DATE_ROLE',         $g_tbl_praefix. '_date_role');
define('TBL_DATES',             $g_tbl_praefix. '_dates');
define('TBL_FILES',             $g_tbl_praefix. '_files');
define('TBL_FOLDERS',           $g_tbl_praefix. '_folders');
define('TBL_FOLDER_ROLES',      $g_tbl_praefix. '_folder_roles');
define('TBL_GUESTBOOK',         $g_tbl_praefix. '_guestbook');
define('TBL_GUESTBOOK_COMMENTS',$g_tbl_praefix. '_guestbook_comments');
define('TBL_LINKS',             $g_tbl_praefix. '_links');
define('TBL_LIST_COLUMNS',      $g_tbl_praefix. '_list_columns');
define('TBL_LISTS',             $g_tbl_praefix. '_lists');
define('TBL_MEMBERS',           $g_tbl_praefix. '_members');
define('TBL_ORGANIZATIONS',     $g_tbl_praefix. '_organizations');
define('TBL_PHOTOS',            $g_tbl_praefix. '_photos');
define('TBL_PREFERENCES',       $g_tbl_praefix. '_preferences');
define('TBL_ROLE_DEPENDENCIES', $g_tbl_praefix. '_role_dependencies');
define('TBL_ROLES',             $g_tbl_praefix. '_roles');
define('TBL_ROOMS',             $g_tbl_praefix. '_rooms');
define('TBL_SESSIONS',          $g_tbl_praefix. '_sessions');
define('TBL_TEXTS',             $g_tbl_praefix. '_texts');
define('TBL_USERS',             $g_tbl_praefix. '_users');
define('TBL_USER_DATA',         $g_tbl_praefix. '_user_data');
define('TBL_USER_FIELDS',       $g_tbl_praefix. '_user_fields');
?>
