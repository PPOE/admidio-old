<? 
include "config.php";
header("Content-Type: text/plain");

$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$query = mysql_query("SELECT rol_description,mem_usr_id,usd_value FROM adm_roles LEFT JOIN adm_members ON mem_rol_id = rol_id AND mem_end > NOW() LEFT JOIN adm_user_data ON mem_usr_id = usd_usr_id AND usd_usf_id = 12 WHERE rol_description LIKE '%{%}%';"); 

$groups = array();

while ($row = mysql_fetch_assoc($query)) {
$matches = array();
preg_match_all('/{(.*?)(:.*?)?(:.*?)?(:.*?)?(:.*?)?(:.*?)?(:.*?)?(:.*?)?}/',$row['rol_description'],$matches,PREG_PATTERN_ORDER);
foreach ($matches[1] as $k => $match)
{
if (!isset($groups[$match]))
  $groups[$match] = array();
if (strlen($row['usd_value']) >= 8)
  $groups[$match][$row['usd_value']] = 1;
for ($i = 2; $i <= 8; $i++)
{
  if (strlen($matches[$i][$k]) >= 8 && substr($matches[$i][$k],0,1) == ':')
  {
    $groups[$match][substr($matches[$i][$k],1)] = 1;
  }
}
}
}

function aes256_encrypt($data, $key, $iv) {
        $block_size = mcrypt_get_block_size ("rijndael-128", "cbc");
        $pad = $block_size - (strlen ($data) % $block_size);
        if ( $pad <= 0 ) { $pad = 16; }
        $padded_data = $data.str_repeat (chr ($pad), $pad);
        $message = base64_encode( mcrypt_encrypt (MCRYPT_RIJNDAEL_128,
 $key, $padded_data, MCRYPT_MODE_CBC, $iv) );
        return $message;
}
function aes256_decrypt($data, $key, $iv ) {
        $block_size = mcrypt_get_block_size ("rijndael-128", "cbc");
        $message = mcrypt_decrypt (MCRYPT_RIJNDAEL_128, $key,
base64_decode($data), MCRYPT_MODE_CBC, $iv);
        $pad = ord(substr($message, -1));
        $message = substr( $message, 0, (0 - $pad) );
        return $message;
}

ksort($groups);
$data = "";
foreach ($groups as $list => $recpt)
{
  if (count($recpt) == 0)
  {
    $rcpt['dummy-organ@piratenpartei.at'] = 1;
  }
  if ($list != "bgf@piratenpartei.at")
    $data .= "$list: " . implode(",",array_keys($recpt)) . "\n";
}
  echo aes256_encrypt($data,$mail_key,$mail_iv);
mysql_close($link);
?>

