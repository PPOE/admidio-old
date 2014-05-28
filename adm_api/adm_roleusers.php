<? 
include "config.php";
//header("Content-Type: text/plain");
//header('Content-Type: text/html; charset=utf-8');

/* zuordnung von rolle zu user (admidio user, nicht nick!) 
derzeit nicht sonderlich elegant...
da die Rechte bei davical ohnehin nur gruppe/user tupel sind, ist es einfacher diese auch hier nicht zusammenzufassen.
*/


$roleuser_password = $keyr;
$roleuser_key = hash('SHA256', $roleuser_password, true);
$roleuser_iv = hash('md5', $roleuser_password, true);


$mysqli = new mysqli($g_adm_srv,$g_adm_usr,$g_adm_pw,$g_adm_db);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

if (!$mysqli->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
} else {
//    printf("Current character set: %s\n", $mysqli->character_set_name());
}

$query = ("select LOWER(rol_name), LOWER(users.usr_login_name) from ppoe_mitglieder.adm_roles join ppoe_mitglieder.adm_members members ON  ppoe_mitglieder.adm_roles.rol_id = members.mem_rol_id AND members.mem_end >= now() join ppoe_mitglieder.adm_users users ON users.usr_id = members.mem_usr_id WHERE ppoe_mitglieder.adm_roles.rol_cat_id = '16' OR ppoe_mitglieder.adm_roles.rol_cat_id = '17';");

$data = "";

if ($result = $mysqli->query($query)) {
setlocale(LC_ALL, 'de_DE.UTF8');
    /* fetch object array */
    while ($row = $result->fetch_row()) {
          $data = $data . "'" . preg_replace("/ /","_",preg_replace("/[ ]*\([^)]*\)[ ]*/","",iconv("utf-8","ascii//TRANSLIT", $row[0]))) ."': '" . iconv("utf-8","ascii//TRANSLIT", $row[1]) . "'\n" ;
    }

    /* free result set */
    $result->free();
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

//echo $data;
echo aes256_encrypt($data,$roleuser_key,$roleuser_iv);

/* close connection */
$mysqli->close();

?>
