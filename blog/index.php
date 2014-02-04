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
  $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

$secret = $blogSecret;

$url = "http://wahlallianz.at/blog/wp-content/plugins/piratelogin/";

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
