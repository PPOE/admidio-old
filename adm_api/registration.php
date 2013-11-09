<?
if (php_sapi_name() != 'cli') { die('error'); }

global $_SERVER;
$_SERVER['HTTP_HOST'] = "";
$_SERVER['REQUEST_URI'] = "";
$_SERVER['REMOTE_ADDR'] = "";
require_once('/var/www/adm_program/system/common.php');
require_once('/var/www/adm_program/system/classes/system_mail.php');
require_once('/var/www/adm_program/system/classes/table_members.php');

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$los    = array(10 => 0, 1 => 38, 2 => 40, 3 => 39, 4 => 41, 5 => 42, 6 => 43, 7 => 44, 8 => 45, 9 => 37);

$query = mysql_query("SELECT * FROM ppoe_mitglieder.adm_users A LEFT JOIN ppoe_mitglieder.adm_user_data B ON A.usr_id = B.usd_usr_id AND B.usd_usf_id = 23 WHERE usr_valid = 0 AND usr_password IS NOT NULL;");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $id = intval($row["usr_id"]);
  $lo = intval($los[intval($row["usd_value"])]);
  echo "user $id\n";
  $getNewUserId = $id;
  $new_user = new User($gDb, $gProfileFields, $getNewUserId);

  // User auf aktiv setzen
  $new_user->setValue('usr_valid', 1);
  $new_user->setValue('usr_reg_org_shortname', '');
  $new_user->save();

  // nur ausfuehren, wenn E-Mails auch unterstuetzt werden
  if($gPreferences['enable_system_mails'] == 1)
  {
    // Mail an den User schicken, um die Anmeldung zu bestaetigen
    $sysmail = new SystemMail($gDb);
    $sysmail->addRecipient($new_user->getValue('EMAIL'), $new_user->getValue('FIRST_NAME'). ' '. $new_user->getValue('LAST_NAME'));
    echo "mail to " . $new_user->getValue('EMAIL') . "\n";
    if($sysmail->sendSystemMail('SYSMAIL_REGISTRATION_USER', $new_user) == false)
    {
       echo $gL10n->get('SYS_EMAIL_NOT_SEND', $new_user->getValue('EMAIL'));
    }
  }

  $member = new TableMembers($gDb);
  $member->startMembership(2, $new_user->getValue('usr_id'));
  if ($lo >= 37 && $lo <= 45)
  {
    $member->startMembership($lo, $new_user->getValue('usr_id'));
    echo "add $id to $lo\n";
  }
}
}
mysql_close($link);

?>

