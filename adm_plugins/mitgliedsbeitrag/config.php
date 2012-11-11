<?php
/******************************************************************************
 * Konfigurationsdatei fuer das Admidio-Plugin Mitgliedsbeitrag
 *
 * Copyright    : (c) 2004 - 2011 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/
// Allgemeine Einstellungen fuer das Plugin

// Freigabe des Plugins für eine bestimmte Benutzergruppe
// 'Alle'            : jeder darf auf das Modul zugreifen (nicht empfohlen)
// 'Benutzer'        : nur registrierte Benutzer (nicht empfohlen)
// 'Rollenverwalter' : nur Benutzer mit dem Recht "Rollen zu erstellen" (nicht empfohlen)
// '<Rollenname>'    : nur Mitglieder dieser Rolle
//  Beispiel:  $plgFreigabe = array('Webmaster');
//
// um das Plugin für mehrere Benutzergruppen freizuschalten, 
// einfach das array um die jeweilige Benutzergruppe erweitern
// Beispiel:  $plgFreigabe = ('Webmaster','Vorstand','Rollenname1','Rollenname2');
$plgFreigabe = array('Webmaster');

// wenn Alter > -1 und <= 14, dann ist der Mitgliedsbeitrag = 20 EUR
// Hinweis: die Angabe -1 ist erforderlich, damit Kleinkinder unter einem Jahr
//          richtig berechnet werden
$beitrag_beitraege_einzel[0]['alter'] = -1;
$beitrag_beitraege_einzel[0]['betrag'] = 200000;
$beitrag_beitraege_einzel[0]['text'] = "Kind";

// wenn Alter > 14 und <= 18, dann ist der Mitgliedsbeitrag = 24 EUR
$beitrag_beitraege_einzel[1]['alter'] = 14;
$beitrag_beitraege_einzel[1]['betrag'] = 240000;
$beitrag_beitraege_einzel[1]['text'] = "Jugendliche";

// wenn Alter > 18 und <= 60, dann ist der Mitgliedsbeitrag = 24 EUR
$beitrag_beitraege_einzel[2]['alter'] = 18;
$beitrag_beitraege_einzel[2]['betrag'] = 240000;
$beitrag_beitraege_einzel[2]['text'] = "Erwachsene";

// wenn Alter > 60, dann ist der Mitgliedsbeitrag = 24 EUR
$beitrag_beitraege_einzel[3]['alter'] = 60;
$beitrag_beitraege_einzel[3]['betrag'] = 240000;
$beitrag_beitraege_einzel[3]['text'] = "Senioren";

// alle Rollenmitglieder dieser Rolle werden für die Beitragsabrechnung herangezogen
// dies wird wahrscheinlich immer die Rolle "Mitglied" sein,
// aber durch das Zuweisen dieses Rollennamens an eine Variable
// gibt es jetzt keine einzige fest kodierte Zuweisung mehr im Plugin Mitgleidsbeitrag
$beitrag_einzel_rolle = "Mitglied";

// Familienbeitrag
$beitrag_familie['betrag'] = 1000000;

//Innerhalb dieser Kategorie sind Familien als Rollen zu definieren
$beitrag_familie['kategorie'] = "Mitgliedsbeitrag -Familie-";

$beitrag_familie['text'] = "Familienbeitrag";

//Schueler- und Studentenbeitrag (=ermeassigter Beitrag)
$beitrag_ermaessigt['betrag'] = 250000; 

// alle Mitglieder dieser Kategorie entrichten einen ermaessigten Beitrag
$beitrag_ermaessigt['kategorie'] = "Mitgliedsbeitrag -ermaessigt-";
$beitrag_ermaessigt['text'] = "Student";

// alle Mitglieder dieser Kategorie sind vom Beitrag befreit
$beitrag_befreit['kategorie'] = "Mitgliedsbeitrag -befreit-";

// Spartenbeitrag
$beitrag_sparte['betrag'] = 100000;

//Mindesteinzugsbetrag; wenn Mindesteinzugsbetrag = 0, dann wird jeder Betrag eingezogen
$beitrag_sparte['mindesteinzug'] = 20000;

// alle Mitglieder dieser Kategorie entrichten einen Spartenbeitrag
$beitrag_sparte['kategorie'] = "Fussball (mit Spartenbeitrag)";

$beitrag_sparte['text'] = "Spartenbeitrag"; 

// alle errechneten Beitraege werden abgerundet
// 1 = Beitrag wird abgerundet (Default) 
// 0 = Beitrag wird nicht abgerundet
$beitrag_abrunden = 1;
                                 
//für den "letzten durchgeführten Einzug" wird dieser Parameter als Tagesangabe verwendet
$beitrag_last_einzugtag = 2;

// fuer die Ausgabedateien
$beitrag_einzugstext = "Mitgliedsbeitrag";

//Name der Ausgabedateien
$beitrag_filename_csvgesamt = "-Mitgliedsbeitrag-Gesamtliste.csv";
$beitrag_filename_csvrechnung = "-Mitgliedsbeitrag-Rechnungen.csv";
$beitrag_filename_csvlastschrift = "-Mitgliedsbeitrag-Lastschriften.csv";
$beitrag_filename_dtaus0 = "dtaus0.txt";     
$beitrag_filename_dtausbglz = "Begleitzettel.csv";

//Empfängerkontodaten
$beitrag_ktonr_target = "12345678";
$beitrag_blz_target = "12345678";
$beitrag_bank_target = "Name der Bank";
$beitrag_inhaber_target = "Name des Vereins";
