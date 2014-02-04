<?php
function mail_utf8($to, $subject, $message, $headers)
{
  $to = base64_encode($to);
  $subject = base64_encode($subject);
  $message = base64_encode($message);
  $headers = base64_encode($headers);
  mysql_query("INSERT INTO ppoe_api_data.mail_queue (mto,msubject,mbody,mheaders) VALUES ('$to', '$subject', '$message', '$headers');");
  return true;
}

function utf8_mail($to, $subject, $message)
{
  $subject = "=?UTF-8?B?".base64_encode($subject)."?=";
  $sender = "=?UTF-8?B?".base64_encode("Piratenpartei Österreichs")."?=";
  $headers = "From: $sender <bgf@piratenpartei.at>\r\n";
  $headers .= "MIME-Version: 1.0\r\nContent-type: text/plain; charset=UTF-8\r\n";

  return mail_utf8($to, $subject, $message, $headers);
}

?>
