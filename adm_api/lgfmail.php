<? 
if (php_sapi_name() != 'cli') { die('error'); }
require("config.php");
require("mail.php");

$sel_mail = "(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 12) as Email";
$sel_reason = "(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 40) as Reason";
$sel_name = "(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 2) as Name";
$sel_nick = "usr_login_name as Nick";
$sel_members_nick = "(select G2.usr_login_name from ppoe_mitglieder.adm_users G2 where members.usr_id = G2.usr_id) as Nick";
$sel_mbuntil = "(select G2.usd_value from ppoe_mitglieder.adm_user_data G2 where G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26) as MBUntil";
$sel_lo = "(SELECT G3.mem_rol_id FROM ppoe_mitglieder.adm_members G3 WHERE G1.usr_id = G3.mem_usr_id AND G3.mem_end > curdate() AND G3.mem_rol_id >= 37 AND G3.mem_rol_id <= 45 LIMIT 1) AS LO";
$sel_mb = "CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26 AND G2.usd_value >= curdate() - INTERVAL 4 DAY LIMIT 1) IS NULL THEN 0 ELSE 1 END AS MB";
$sel_mbm30 = "CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26 AND G2.usd_value >= curdate() + INTERVAL 30 DAY LIMIT 1) IS NULL THEN 0 ELSE 1 END AS MBM30";
$sel_mbp30 = "CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26 AND G2.usd_value >= curdate() - INTERVAL 30 DAY LIMIT 1) IS NULL THEN 0 ELSE 1 END AS MBP30";
$sel_mbp90 = "CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26 AND G2.usd_value >= curdate() - INTERVAL 90 DAY LIMIT 1) IS NULL THEN 0 ELSE 1 END AS MBP90";
$sel_mbp180 = "CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 26 AND G2.usd_value >= curdate() - INTERVAL 180 DAY LIMIT 1) IS NULL THEN 0 ELSE 1 END AS MBP180";
$sel_akk = "CASE WHEN (select G2.usd_value FROM ppoe_mitglieder.adm_user_data G2 WHERE G1.usr_id = G2.usd_usr_id AND G2.usd_usf_id = 35 AND G2.usd_value <= curdate() LIMIT 1) IS NULL THEN 0 ELSE 1 END AS Akk";
$where_member = "where G1.usr_id IN (select T3.usr_id from ppoe_mitglieder.adm_users T3 INNER JOIN ppoe_mitglieder.adm_members T4 WHERE T3.usr_id = T4.mem_usr_id AND   T3.usr_valid = 1 AND   T4.mem_rol_id = 2 AND   T4.mem_end >= curdate() )";


$link = mysql_connect($g_adm_srv,$g_adm_usr,$g_adm_pw);
mysql_select_db($g_adm_db,$link);

$los    = array(0 => 'Keine', 38 => 'Burgenland', 40 => 'K&auml;rnten', 39 => 'Nieder&ouml;sterreich', 41 => 'Ober&ouml;sterreich', 42 => 'Salzburg', 43 => 'Steiermark', 44 => 'Tirol', 45 => 'Vorarlberg', 37 => 'Wien');

$mails = array(
0 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","ag-mentoring@piratenpartei.at"),
37 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-wien@piratenpartei.at","sekretariat-wien@piratenpartei.at"),
38 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-burgenland@piratenpartei.at"),
39 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-noe@piratenpartei.at"),
40 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-kaernten@piratenpartei.at"),
41 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-ooe@piratenpartei.at"),
42 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-sbg@piratenpartei.at"),
43 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-steiermark@piratenpartei.at"),
44 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-tirol@piratenpartei.at"),
45 => array("bv@piratenpartei.at","bgf-intern@piratenpartei.at","lv-vorarlberg@piratenpartei.at")
);

$refill = true;
$date = new DateTime();
$thisYear = (int) $date->format('Y');
$nextYear = $thisYear + 1;

////////////////////////////////////
// INFO TO LGF NEW MEMBER
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id, $sel_mail, $sel_nick, $sel_lo, $sel_reason from ppoe_mitglieder.adm_users G1 $where_member) A WHERE (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END) NOT IN (SELECT usr_id,LO FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $nick = "";
  if (isset($row["Nick"]))
    $nick = $row["Nick"];
  $reason = "";
  if (isset($row["Reason"]))
    $reason = $row["Reason"];
  $lo = intval($row["LO"]);
  if ($lo == null || count($lo) == 0)
    $lo = 0;
  echo "Generating Mails for new user $lo $id (not TO user)\n";
  foreach ($mails[$lo] AS $mail)
  {
    if ($mail == 'bgf-intern@piratenpartei.at')
      continue;
    $subject = "[Admidio] Neues Mitglied zugeordnet";
    $text = "Es wurde ein neues Mitglied ($nick) der LO {$los[$lo]} zugeordnet:

https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=$id

Du musst eingeloggt sein, um diesen Link zu öffnen.

Das Mitglied hat folgende Interessen angegeben: $reason";
    echo "NEW Mail to $mail $lo $id\n";
    utf8_mail($mail,$subject,$text);
  }
  echo "Adding User $id to newsletter $lo\n";
  $prefs = 1;
  if($lo == 38) {$prefs += 2;}
  if($lo == 40) {$prefs += 4;}
  if($lo == 39) {$prefs += 8;}
  if($lo == 41) {$prefs += 16;}
  if($lo == 42) {$prefs += 32;}
  if($lo == 43) {$prefs += 64;}
  if($lo == 45) {$prefs += 128;}
  if($lo == 37) {$prefs += 512;}
  $mail = $row["Email"];
$q2 = mysql_query("SELECT * FROM users WHERE email = '".mysql_escape_string($mail)."'");
if (!$q2 || mysql_num_rows($q2) == 0)
{
$sid = mt_rand();
$q2 = mysql_query("SELECT * FROM users WHERE sid = $sid");
while ($q2 && mysql_num_rows($q2) > 0)
{
$sid = mt_rand();
}
$q4 = mysql_query("INSERT INTO ppoe_api_data.users (email, prefs, sid, confirmed) VALUES ('".mysql_escape_string($mail)."', $prefs, $sid, 1);");
echo "INSERT INTO ppoe_api_data.users (email, prefs, sid, confirmed) VALUES ('".mysql_escape_string($mail)."', $prefs, $sid, 1);\n";
if (!$q4)
{
  echo "FAILED!!!!\n";
}
}
}
}

////////////////////////////////////
// INFO TO LGF LOST MEMBER
////////////////////////////////////
$query = mysql_query("SELECT *,$sel_members_nick FROM ppoe_api_data.members WHERE (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END) NOT IN (select G1.usr_id, $sel_lo from ppoe_mitglieder.adm_users G1 $where_member);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $nick = "";
  if (isset($row["Nick"]))
    $nick = $row["Nick"];
  $lo = intval($row["LO"]);
  foreach ($mails[$lo] AS $mail)
  {
    if ($mail == 'bv@piratenpartei.at')
      continue;
    $subject = "[Admidio] Mitglied entfernt";
    $text = "Es wurde ein Mitglied ($nick) aus der LO {$los[$lo]} entfernt:

https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=$id

Du musst eingeloggt sein, um diesen Link zu öffnen.
";
    echo "DEL Mail to $mail $lo $id\n";
    utf8_mail($mail,$subject,$text);
  }
}
}

////////////////////////////////////
// INFO TO MEMBER NOT AKK BUT MB
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id, $sel_name, $sel_mbuntil, $sel_mail, $sel_lo, $sel_mb, $sel_akk from ppoe_mitglieder.adm_users G1 $where_member) A WHERE (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END,MB) NOT IN (SELECT usr_id,LO,MB FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $lo = intval($row["LO"]);
  $mail = $row["Email"];
  $mb = $row["MB"];
  $akk = $row["Akk"];
  $name = $row["Name"];
  $mbuntil = $row["MBUntil"];
  if ($akk != 1 && $mb == 1) {
    $subject = "[Liquid] Akkreditierung notwendig";
    $text = 'Hallo '.$name.'!

Laut Mitgliederdatenbank bist du Mitglied und hast einen aufrechten Zahlungsstatus, bist aber noch nicht akkreditiert. Um an unserer Online-Abstimmungsplattform Liquid teilzunehmen, musst du dich noch akkreditieren.

„Akkreditierung“ bedeutet einfach, dass die Geschäftsführung deine Identität bestätigen muss. Es gibt diesbezüglich einige Möglichkeiten, siehe hierzu bitte https://wiki.piratenpartei.at/wiki/AG:Liquid/Akkreditierungsbefugte .

Kurz noch ein paar Informationen zu Liquid und warum es wichtig ist, dass du dich akkreditierst und daran teilnehmen kannst:

Liquid ist das von uns genutzte Mittel der Liquid Democracy http://piratenpartei.at/liquid-democracy/ , einer Mischform aus direkter und repräsentativer Demokratie. In Liquid erarbeiten wir unser Parteiprogramm, es dient als Kanalisation der Meinungsbildung und zur Beschlussfassung innerhalb der Piratenpartei.

Dort werden Anträge gestellt (zum Programm, zur Geschäftsordnung, ...) oder Umfragen/Meinungsbilder formuliert und zu diesen Anträgen Anregungen oder Gegenanträge gestellt. Am Ende wird über all jene Vorschläge, die ausreichend viele Unterstützer gefunden haben, abgestimmt.

Solltest du noch weitere Fragen haben, wende dich bitte einfach an die AG Liquid (liquidsupport@piratenpartei.at); wir werden versuchen, dir möglichst rasch zu helfen.

Mit piratigen Grüßen,
  die AG Liquid
';
    echo "NOT AKK BUT MB Mail to $mail $lo $id $mb\n";
  }
}
}

////////////////////////////////////
// INFO TO MEMBER 30 DAYS LEFT!!
////////////////////////////////////
///
$query = mysql_query("SELECT * FROM (select G1.usr_id, $sel_name, $sel_mbuntil, $sel_mail, $sel_lo, $sel_mb, $sel_mbm30, $sel_akk from ppoe_mitglieder.adm_users G1 $where_member) A WHERE MB AND NOT MBM30 AND (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END,MBM30) NOT IN (SELECT usr_id,LO,MBM30 FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $mail = $row["Email"];
  $name = $row["Name"];
  $mbuntil = $row["MBUntil"];
  $subject = "[Piraten] Erinnerung: Mitgliedsbeitrag";
  $text = 'Hallo '.$name.'!

Wir danken dir für die Zahlung deines Mitgliedsbeitrags im vergangenen Jahr und hoffen, dass du auch weiterhin ein aktives, unterstützendes Mitglied der Piratenpartei bleiben wirst.

Ebenfalls möchten wir uns dafür bedanken, dass wir '.$thisYear.' einen durchschnittlichen Mitgliedsbeitrag von über 40€ erhalten haben! Um für '.$nextYear.' stimmberechtigt zu sein (in Liquid und auf den Mitgliederversammlungen), bitten wir dich wie jedes Jahr erneut um die Entrichtung des Mitgliedsbeitrages.

Um für '.$nextYear.' stimmberechtigt zu sein (in Liquid und auf den Mitgliederversammlungen), bitten wir dich wie jedes Jahr erneut um die Entrichtung des Mitgliedsbeitrages. Ab einem Beitrag von € 20 erhältst du Stimmrecht. Um alle unsere Kosten zu decken und Handlungsspielraum im neuen Jahr zu haben, bitten wir dich aber, einen Mitgliedsbeitrag von mindestens € 40 zu zahlen, sofern dir das möglich ist.

Solltest du deinen Mitgliedsbeitrag für '.$nextYear.' bereits überwiesen haben (ab 1. Oktober '.$thisYear.') wird dieser selbstverständlich berücksichtigt. Dein Jahresbeitrag sowie weitere Beitragszahlungen, bis maximal €1000 im Jahr, werden außerdem als Mitgliedsbeitrag gerechnet, sofern nicht explizit anders gewünscht. Darüber hinausgehende Einzahlungen werden als Spenden angesehen. In Anlehnung an die Empfehlung der Piratenpartei Deutschland, fänden wir es auch sehr entgegenkommend, wenn du deinen Mitgliedsbeitrag an deiner aktuellen Einkommenssituation ausrichten würdest, und dir die Mitgliedschaft und die finanzielle Handlungsfähigkeit etwa 1% deines Einkommens als Mitgliedsbeitrag wert wäre.

Bitte überweise deinen Mitgliedsbeitrag auf folgendes Konto:

Kontoinhaber: Piratenpartei Österreichs
IBAN: AT916000050110110437
BIC: OPSKATWW

oder schicke deinen Mitgliedsbeitrag über PayPal an spende@piratenpartei.at

Wir bitten dich im Buchungstext/Verwendungszweck deinen Nicknamen anzugeben, sowie "MB'.$nextYear.'" oder "Mitgliedsbeitrag".

Wir danken dir für deine finanzielle Unterstützung!

Mit piratigen Grüßen,
 deine Bundesgeschäftsführung
';
  utf8_mail($mail,$subject,$text);
  echo "INFO TO MEMBER 30 DAYS LEFT!! Mail to $mail $id\n";
}
}

////////////////////////////////////
// INFO TO MEMBER PAY NOW!!
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id, $sel_nick, $sel_name, $sel_mbuntil, $sel_mail, $sel_lo, $sel_mb, $sel_akk from ppoe_mitglieder.adm_users G1 $where_member) A WHERE NOT MB AND (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END,1) IN (SELECT usr_id,LO,MB FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $mail = $row["Email"];
  $nick = $row["Nick"];
  $name = $row["Name"];
  $mbuntil = $row["MBUntil"];
  $subject = "[Piraten] Erinnerung: Mitgliedsbeitrag";
  $text = 'Hallo '.$name.'!

Zuallererst ein gutes neues Jahr! Wahrscheinlich hattest du die letzten Tage anderes zu tun, allerdings möchten wir dich erinnern, dass am 1.1. dein Mitgliedsbeitrag ausgelaufen ist, und deine Mitgliedschaft somit ruhend gestellt wurde. Dein Stimmrecht in Liquid wird noch für 10 Tage erhalten bleiben.

Um für '.$thisYear.' stimmberechtigt zu sein (in Liquid oder auf den Mitgliederversammlungen), bitten wir dich wie jedes Jahr erneut um die Entrichtung des Mitgliedsbeitrages. Dieser wurde in der letzten Abstimmung hierzu auf eine Höhe von € 40,00 festgelegt. Genauere Informationen findest du weiter unten.

Bitte überweise deinen Mitgliedsbeitrag auf folgendes Konto:

Kontoinhaber: Piratenpartei Österreichs
IBAN: AT916000050110110437
BIC: OPSKATWW

oder schicke deinen Mitgliedsbeitrag an spende@piratenpartei.at mit dem Vermerk, dass es ein Mitgliedsbeitrag ist (MB'.$thisYear.') und deinem Klarnamen, damit wir die Überweisung auch zuordnen können.

Wir danken dir für deine finanzielle Unterstützung!

Wenn die Beitragszahlung innerhalb der nächsten Woche eingeht, wirst du zu keinem Zeitpunkt das Stimmrecht in Liquid verlieren.

Mit piratigen Grüßen,
 deine Bundesgeschäftsführung
';
  utf8_mail($mail,$subject,$text);
  echo "INFO TO MEMBER PAY NOW!! Mail to $mail $id\n";
  $lo = intval($row["LO"]);
  foreach ($mails[$lo] AS $mail)
  {
    if ($mail == 'bgf-intern@piratenpartei.at')
      continue;
    $subject = "[Admidio] Mitglied ohne Mitgliedsbeitrag";
    $text = "Ein Mitglied ($nick) der LO {$los[$lo]} zahlt keinen Mitgliedsbeitrag mehr:

https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=$id

Es wurde bereits benachrichtigt, aber vielleicht hilft ja eine persönliche Nachfrage. Sollte sich der Zahlungsstatus nicht mehr ändern, erhältst du in 3 Monaten eine erneute Benachrichtigung. Sollte nach 6 Monaten noch immer keine Zahlung eingegangen sein, wird die BGF das Mitglied löschen.

Du musst eingeloggt sein, um den obigen Link zu öffnen.";
    utf8_mail($mail,$subject,$text);
  }

}
}

////////////////////////////////////
// INFO TO MEMBER THANKS FOR PAYING
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id, $sel_nick, $sel_name, $sel_mbuntil, $sel_mail, $sel_lo, $sel_mb, $sel_akk from ppoe_mitglieder.adm_users G1 $where_member) A WHERE MB AND (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END,0) IN (SELECT usr_id,LO,MB FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $mail = $row["Email"];
  $nick = $row["Nick"];
  $name = $row["Name"];
  $mbuntil = $row["MBUntil"];
  $subject = "[Piraten] Du bist stimmberechtigt!";
  $text = 'Hallo '.$name.'!

Danke, dass du deinen Mitgliedsbeitrag eingezahlt hast! Damit leistest du einen wichtigen Beitrag für eine bessere Politik.

Dein Mitgliedsstatus ist damit bis einschließlich $mbuntil gültig. Du kannst diesen sowie deine hinterlegten Daten in deinem Profil in der Mitgliedsverwaltung überprüfen: https://mitglieder.piratenpartei.at/
Solltest du Unterstützung bei der Anmeldung dort brauchen, dann schreibe uns bitte einfach an bgf@piratenpartei.at .

Mit piratigen Grüßen,
 deine Bundesgeschäftsführung
';
  utf8_mail($mail,$subject,$text);

  echo "INFO TO MEMBER THANKS FOR PAYING Mail to $mail $id\n";
  $lo = intval($row["LO"]);
  foreach ($mails[$lo] AS $mail)
  {
    if ($mail == 'bgf-intern@piratenpartei.at')
      continue;
    $subject = "[Admidio] Mitglied hat den Mitgliedsbeitrag eingezahlt!";
    $text = "Ein Mitglied ($nick) der LO {$los[$lo]} hat den Mitgliedsbeitrag eingezahlt:

https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=$id

Du musst eingeloggt sein, um den obigen Link zu öffnen.";
    utf8_mail($mail,$subject,$text);
  }

}
}

////////////////////////////////////
// INFO TO MEMBER SHOULD HAVE PAID 90 DAYS AGO
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id, $sel_nick, $sel_name, $sel_mbuntil, $sel_mail, $sel_lo, $sel_mb, $sel_mbp90, $sel_mbp180, $sel_akk from ppoe_mitglieder.adm_users G1 $where_member) A WHERE NOT MB AND NOT MBP90 AND MBP180 AND (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END,0,1) IN (SELECT usr_id,LO,MB,MBP90 FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $mail = $row["Email"];
  $nick = $row["Nick"];
  $name = $row["Name"];
  $mbuntil = $row["MBUntil"];
  $subject = "[Piraten] Erinnerung: Mitgliedsbeitrag";
  $text = 'Hallo '.$name.'!

Wir schreiben dich nun an, weil du seit drei Monaten keinen Mitgliedsbeitrag mehr gezahlt hast.

Um für '.$thisYear.' stimmberechtigt zu sein (in Liquid oder auf den Mitgliederversammlungen), bitten wir dich wie jedes Jahr erneut um die Entrichtung des Mitgliedsbeitrages. Dieser wurde in der letzten Abstimmung hierzu auf eine Höhe von € 40,00 festgelegt. Genauere Informationen findest du weiter unten.

Bitte überweise deinen Mitgliedsbeitrag auf folgendes Konto:

Kontoinhaber: Piratenpartei Österreichs
IBAN: AT916000050110110437
BIC: OPSKATWW

oder schicke deinen Mitgliedsbeitrag an spende@piratenpartei.at mit dem Vermerk, dass es ein Mitgliedsbeitrag ist (MB'.$thisYear.') und deinem Klarnamen, damit wir die Überweisung auch zuordnen können.

Ab einem Beitrag von € 20 erhältst du Stimmrecht. Um alle unsere Kosten zu decken und Handlungsspielraum im neuen Jahr zu haben, bitten wir dich aber, einen Mitgliedsbeitrag von mindestens € 40 zu zahlen, sofern dir das möglich ist. Dein Jahresbeitrag sowie weitere Beitragszahlungen, bis maximal €1000 im Jahr, werden außerdem als Mitgliedsbeitrag gerechnet, sofern nicht explizit anders gewünscht. Darüber hinausgehende Einzahlungen werden als Spenden angesehen.

In Anlehnung an die Empfehlung der Piratenpartei Deutschland, fänden wir es auch sehr entgegenkommend, wenn du deinen Mitgliedsbeitrag an deiner aktuellen Einkommenssituation ausrichten würdest, und dir die Mitgliedschaft und die finanzielle Handlungsfähigkeit etwa 1% deines Einkommens als Mitgliedsbeitrag wert wäre.

Außerdem möchten wir dich darauf hinweisen, dass in etwa drei Monaten gemäß Satzung §4 (7) eine Streichung deiner Mitgliedschaft erfolgt, falls der Mitgliedsbeitrag bis dahin nicht eingegangen ist. Wir würden dies sehr bedauern und hoffen daher auf deine Beitragszahlung und/oder eine Rückmeldung!

Mit piratigen Grüßen,
 deine Bundesgeschäftsführung
';
  utf8_mail($mail,$subject,$text);

  echo "INFO TO MEMBER 90 DAYS DUE!! Mail to $mail $id\n";
  $lo = intval($row["LO"]);
  foreach ($mails[$lo] AS $mail)
  {
    if ($mail == 'bgf-intern@piratenpartei.at')
      continue;
    $subject = "[Admidio] Mitglied seit 3 Monaten ohne Mitgliedsbeitrag";
    $text = "Ein Mitglied ($nick) der LO {$los[$lo]} zahlt seit 3 Monaten keinen Mitgliedsbeitrag mehr:

https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=$id

Es wurde bereits mehrfach benachrichtigt, aber vielleicht hilft ja eine persönliche Nachfrage. Sollte sich der Zahlungsstatus nicht mehr ändern, erhältst du in 3 Monaten eine erneute Benachrichtigung. Die BGF wird dann das Mitglied löschen.

Du musst eingeloggt sein, um den obigen Link zu öffnen.";
    utf8_mail($mail,$subject,$text);
  }
}
}

////////////////////////////////////
// INFO TO MEMBER SHOULD HAVE PAID 180 DAYS AGO
////////////////////////////////////
$query = mysql_query("SELECT * FROM (select G1.usr_id, $sel_name, $sel_nick, $sel_mbuntil, $sel_mail, $sel_lo, $sel_mb, $sel_mbp180, $sel_akk from ppoe_mitglieder.adm_users G1 $where_member) A WHERE NOT MB AND NOT MBP180 AND (usr_id,CASE WHEN LO IS NULL THEN 0 ELSE LO END,0,0,1) IN (SELECT usr_id,LO,MB,MBP90,MBP180 FROM ppoe_api_data.members);");
if ($query) {
while ($row = mysql_fetch_array($query)) {
  $refill = true;
  $id = $row["usr_id"];
  $mail = $row["Email"];
  $name = $row["Name"];
  $nick = $row["Nick"];
  $mbuntil = $row["MBUntil"];
  $subject = "[Piraten] Erinnerung: Mitgliedsbeitrag";
  $text = 'Hallo '.$name.'!

Wir schreiben dich nun an, weil du seit sechs Monaten keinen Mitgliedsbeitrag gezahlt hast.

Um für '.$thisYear.' stimmberechtigt zu sein (in Liquid oder auf den Mitgliederversammlungen), bitten wir dich wie jedes Jahr erneut um die Entrichtung des Mitgliedsbeitrages. Dieser wurde in der letzten Abstimmung hierzu auf eine Höhe von € 40,00 festgelegt. Genauere Informationen findest du weiter unten.

Bitte überweise deinen Mitgliedsbeitrag auf folgendes Konto:

Kontoinhaber: Piratenpartei Österreichs
IBAN: AT916000050110110437
BIC: OPSKATWW

oder schicke deinen Mitgliedsbeitrag an spende@piratenpartei.at mit dem Vermerk, dass es ein Mitgliedsbeitrag ist (MB'.$thisYear.') und deinem Klarnamen, damit wir die Überweisung auch zuordnen können.

Ab einem Beitrag von € 20 erhältst du Stimmrecht. Um alle unsere Kosten zu decken und Handlungsspielraum im neuen Jahr zu haben, bitten wir dich aber, einen Mitgliedsbeitrag von mindestens € 40 zu zahlen, sofern dir das möglich ist. Dein Jahresbeitrag sowie weitere Beitragszahlungen, bis maximal €1000 im Jahr, werden außerdem als Mitgliedsbeitrag gerechnet, sofern nicht explizit anders gewünscht. Darüber hinausgehende Einzahlungen werden als Spenden angesehen.

In Anlehnung an die Empfehlung der Piratenpartei Deutschland, fänden wir es auch sehr entgegenkommend, wenn du deinen Mitgliedsbeitrag an deiner aktuellen Einkommenssituation ausrichten würdest, und dir die Mitgliedschaft und die finanzielle Handlungsfähigkeit etwa 1% deines Einkommens als Mitgliedsbeitrag wert wäre.

Außerdem möchten wir dich darauf hinweisen, dass in den nächsten Tagen gemäß Satzung §4 (7) eine Streichung deiner Mitgliedschaft erfolgt, falls wir von dir keine Rückmeldung oder eine Beitragszahlung erhalten. Wir würden dies sehr bedauern und hoffen daher auf deine Beitragszahlung und/oder eine Rückmeldung!

Mit piratigen Grüßen,
 deine Bundesgeschäftsführung
';
  utf8_mail($mail,$subject,$text);

  echo "INFO TO MEMBER 180 DAYS DUE!! Mail to $mail $id\n";
  $lo = intval($row["LO"]);
  foreach ($mails[$lo] AS $mail)
  {
    if ($mail == 'bgf-intern@piratenpartei.at')
      $mail = 'bgf@piratenpartei.at';
    $subject = "[Admidio] Mitglied seit 6 Monaten ohne Mitgliedsbeitrag";
    $text = "Ein Mitglied ($nick) der LO {$los[$lo]} zahlt seit 6 Monaten keinen Mitgliedsbeitrag mehr:

https://mitglieder.piratenpartei.at/adm_program/modules/profile/profile.php?user_id=$id

Es wurde bereits mehrfach benachrichtigt, die Streichung muss jetzt erfolgen.

Du musst eingeloggt sein, um den obigen Link zu öffnen.";
    utf8_mail($mail,$subject,$text);
    echo "ATTENTION: User is 180 days due and may be deleted; info to $mail $lo $id\n";
  }
}
}


if ($refill == true)
{
$query = mysql_query("TRUNCATE ppoe_api_data.members;");

$query = mysql_query("INSERT INTO ppoe_api_data.members (usr_id, Email, LO, MB, MBM30, MBP30, MBP90, MBP180, Akk) SELECT * FROM (select G1.usr_id, $sel_mail, $sel_lo, $sel_mb, $sel_mbm30, $sel_mbp30, $sel_mbp90, $sel_mbp180, $sel_akk from ppoe_mitglieder.adm_users G1 $where_member) A;");
}

$query = mysql_query("SELECT * FROM ppoe_mv_info.mv_statistik WHERE LO = 0 AND timestamp >= '" . date_format($date, 'Y-m-d 00:00:00') . "' AND timestamp <= '" . date_format($date, 'Y-m-d 23:59:59') . "';");
if ($query && ($row = mysql_fetch_array($query)))
{
}
else
{
foreach ($los as $num => $lo) {
//echo "$num\n";
  if ($num == 0)
    $where = "1";
  else
    $where = "LO = $num";
  $lo_members = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE $where;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_members = $row[0];
  }
  $lo_zahlend = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE $where AND MB = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_zahlend = $row[0];
  }
  $lo_stimmberechtigt = 0;
  $query = mysql_query("SELECT COUNT(*) FROM ppoe_api_data.members WHERE $where AND (MB = 1 OR MBP30 = 1) AND Akk = 1;");
  if ($query && ($row = mysql_fetch_array($query))) {
    $lo_stimmberechtigt = $row[0];
  }
  $query = mysql_query("INSERT INTO ppoe_mv_info.mv_statistik (akk,members,users,LO,timestamp) VALUES (" . $lo_stimmberechtigt . ", " . $lo_zahlend . ", " . $lo_members . ", " . $num . ", '" . date_format($date, 'Y-m-d') . "');");
//echo "INSERT INTO ppoe_mv_info.mv_statistik (akk,members,users,LO,timestamp) VALUES (" . $lo_stimmberechtigt . ", " . $lo_zahlend . ", " . $lo_members . ", " . $num . ", '" . date_format($date, 'Y-m-d') . "');\n";
}
}

mysql_close($link);

echo date_format($date, 'Y-m-d H:i:s') . "\n";

?>

