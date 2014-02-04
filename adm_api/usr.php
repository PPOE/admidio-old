<?
if (!isset($_GET['user_id']))
  die("");
if (isset($_POST['password']))
{
  $password = $_POST['password'];
  $user_id = strtr($_GET['user_id'], '-_$', '+/=');
  $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($password), base64_decode($user_id), MCRYPT_MODE_CBC, md5(md5($password))), "\0");
  header("Location: /adm_program/modules/profile/profile.php?user_id=$decrypted");
}
?>
<html>
<form method="POST">
<p>
<table border=0>
<tr>
<td>Admidio Decryption Password: <input type="password" name="password" size="20" value=""></td>
<td><input type="submit" name="action" value="Show Admidio ID"></td>
</tr>
</table>
</p>
</form>
</html>
