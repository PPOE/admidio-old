<?php

require_once('../adm_program/system/common.php');

require_once('../adm_program/system/classes/list_configuration.php');
require_once('../adm_program/system/classes/table_roles.php');
require_once('../adm_api/config.php');

if(!$gCurrentUser || !$gValidLogin || strtotime($gCurrentUser->getValue('BEZAHLT_BIS')) < time())
{
  $_SESSION['navigation']->clear();
  $_SESSION['navigation']->addUrl("https://mitglieder.piratenpartei.at/adm_program/system/login.php");
  $_SESSION['navigation']->addUrl("https://mitglieder.piratenpartei.at/adm_program/system/login.php");
  //$gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
  function str2hex($string) {
    $hex = "";
    for ($i = 0; $i < strlen($string); $i++) {
      $hex .= (strlen(dechex(ord($string[$i]))) < 2) ? "0" . dechex(ord($string[$i])) : dechex(ord($string[$i]));
    }
    return $hex;
  }
  $thisurl = "https://mitglieder.piratenpartei.at/blog";
  header("Location: https://mitglieder.piratenpartei.at/adm_program/system/login.php?target=" . str2hex($thisurl));
  return;
}

$secret = $blogSecret;

$url = "http://blog.europaanders.at/wp-content/plugins/piratelogin/";

// fetch encrypted token
$response = file($url);
$encrypted_data = trim(urldecode($response[0]));

// decrypt token
$size = mcrypt_get_iv_size("rijndael-128", "cbc");
$iv = substr($encrypted_data, 0, $size); // iv is prepended to ciphertext

$encrypted_access_token = substr($encrypted_data, $size);
$key = substr(md5($secret), 0, mcrypt_get_key_size("rijndael-128", "cbc"));
$access_token = aes128_decrypt($encrypted_access_token, $key, $iv);

// redirect
header("Location: {$url}?token={$access_token}");

function aes128_decrypt($ciphertext, $key, $iv) {
  $block_size = mcrypt_get_block_size ("rijndael-128", "cbc");
  $plaintext = mcrypt_decrypt (MCRYPT_RIJNDAEL_128, $key, base64_decode($ciphertext), MCRYPT_MODE_CBC, $iv);
  $pad = ord(substr($plaintext, -1));
  $plaintext = substr( $plaintext, 0, (0 - $pad) );
  return $plaintext;
}
?>
