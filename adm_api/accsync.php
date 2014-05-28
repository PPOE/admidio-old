<?
if (php_sapi_name() != 'cli') { die('error'); }
include "config.php";
include "/var/www/config.php";

$sData = file_get_contents("https://finanzen.piratenpartei.at/mbapi.php");
if (strlen($key4) == 0)
  die('Kein Passwort!');

if (strlen($sData) < 100)
  die('Daten von Mitgliederverwaltung zu kurz!');

$sData = explode("\n",$sData);
$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key4), base64_decode($sData[1]), MCRYPT_MODE_CBC, md5(md5($key4))), "\0");
if ($sData[0] != sha1($decrypted))
  die('Datenübertragung nicht erfolgreich!');
$lines = explode("\n",$decrypted);

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$count = 0;
foreach ($lines as $line)
{
  if (strpos($line,"\t") === false)
    continue;
  list($id,$amount,$date) = explode("\t",$line);
  $amount = $amount / 100.0;
  $id = intval($id);
  $query = mysql_query("select usr_id,A.usd_value AS date,B.usd_value AS amount from adm_users left join adm_members on usr_id = mem_usr_id and mem_rol_id = 2 left join adm_user_data A on usr_id = A.usd_usr_id and A.usd_usf_id = 26 left join adm_user_data B on usr_id = B.usd_usr_id and B.usd_usf_id = 27 where usr_valid = 1 and mem_rol_id = 2 and mem_end >= curdate() and usr_id = $id");
  if ($row = mysql_fetch_assoc($query)) {
    $count++;
    $amount2 = $row['amount'];
    $date2 = $row['date'];
    if (strtotime($date) > strtotime($date2))
    {
      $q = "update adm_user_data set usd_value = '$date' where usd_usf_id = 26 and usd_usr_id = $id\n";
      $query2 = mysql_query($q);
      if (mysql_affected_rows() > 0)
      {
        echo $q;
      }
      else
      {
        $q = "insert into adm_user_data (usd_value,usd_usf_id,usd_usr_id) values ('$date',26,$id)\n";
        $query2 = mysql_query($q);
        echo $q;
      }

    }
    if ($amount != $amount2)
    {
      $q = "update adm_user_data set usd_value = '$amount' where usd_usf_id = 27 and usd_usr_id = $id\n";
      echo $q;
      $query2 = mysql_query($q);
      if (mysql_affected_rows() > 0)
      {
        echo $q;
      }
      else
      {
        $q = "insert into adm_user_data (usd_value,usd_usf_id,usd_usr_id) values ('$amount',27,$id)\n";
        $query2 = mysql_query($q);
        echo $q;
      }
    }
  }
}

mysql_close($link);

?>

