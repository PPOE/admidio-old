<?
function qmail($to, $subject,$message,$additional_headers = null,$additional_parameters = null)
{
  global $gDb;
  $to = base64_encode($to);
  $subject = base64_encode($subject);
  $message = base64_encode($message);
  if ($additional_headers != null)
  {
	$additional_headers = base64_encode($additional_headers);
	if ($additional_parameters != null)
	{
		$additional_parameters = base64_encode($additional_parameters);
		$gDb->query("INSERT INTO adm_mail_queue (mto,msubject,mbody,mheaders,mparams) VALUES ('$to', '$subject', '$message', '$additional_headers','$additional_parameters');");
	}
	else
	{
		$gDb->query("INSERT INTO adm_mail_queue (mto,msubject,mbody,mheaders) VALUES ('$to', '$subject', '$message', '$additional_headers');");
	}
  }
  else
  {
	$gDb->query("INSERT INTO adm_mail_queue (mto,msubject,mbody) VALUES ('$to', '$subject', '$message');");
  }
  return true;
}

?>
