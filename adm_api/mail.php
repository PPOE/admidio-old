<?php

function utf8_mail($to, $subject, $message)
{
  $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
  $sender = "=?UTF-8?B?".base64_encode("Piratenpartei Österreichs")."?=";
  $headers = "From: $sender <noreply@piratenpartei.at>\r\n";
  $headers .= "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\n";

  return mail($to, $subject, $message, $headers);
}

?>
