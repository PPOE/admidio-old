<?php
/******************************************************************************
 * Captcha - Klasse
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Diese Klasse erzeugt ein Captcha-Bildchen und speichert den zu
 * loesenden Code in der Session ab.
 *
 * Erweiterung: Matthias Roberg
 * Die Klasse kann nach Vorgabe auch eine einfache Rechenaufgabe als Captcha
 * erzeugen. Diese wird als reiner Text ausgegeben und ist somit f�r
 * barriere-freie Seiten geeignet. Der u.g. Ablauf bleibt dabei gleich.
 *
 *
 * Um in einem Formular ein Captcha einzubinden, muss nur diese Datei als
 * Bild eingebunden werden. Zusaetzlich muss natuerlich ein Formularfeld
 * existieren, in das der User den Code eingibt. Die Captcha-Klasse speichert
 * seine eigene Loesung in der SessionVariable $_SESSION['captchacode']. Der
 * vom User eingegebene Code muss nun im aufgerufenen Script verglichen werden.
 *
 * Beispiel:
 *
 * if ( strtoupper($_SESSION['captchacode']) != strtoupper($_POST['captcha']) )
 * {
 *         echo "Das Captcha wurde nicht richtig geloest...";
 * }
 * else
 * {
 *        echo "Das Captcha wurde richtig geloest!";
 * }
 *
 * Wenn die auszuloesende Aktion erfolgreich ausgefuehrt wurde, sollte der
 * CaptchaCode aus der Session geloescht werden, damit man nicht anschliessend
 * erneut das Script aufrufen kann ohne vorher ein neues Captcha geloest zu
 * haben.
 *
 * Zum Beispiel so:
 *
 * // Der CaptchaCode wird bei erfolgreicher Aktion aus der Session geloescht
 * if (isset($_SESSION['captchacode']))
 * {
 *    unset($_SESSION['captchacode']);
 * }
 *
 *****************************************************************************/

// Nur mit einbinden, falls ein Bildchen erzeugt werden soll (dann gibt es immmer einen Type mit dem Wert 'pic')
 if(array_key_exists('type', $_GET))
{
	require_once('../common.php');
}

class Captcha
{
    private $font, $signature, $width, $height, $codeSize, $allowedChars;
    private $backgroundColourR, $backgroundColourG, $backgroundColourB;
    private $backgroundWriting, $backgroundWritingSize;
	private $text_part1,$text_part2,$text_part3_third,$text_part3_half,$text_part4;

    public function __construct()
    {
		global $gPreferences;
		
        // Hier wird jetzt die Schriftart festgelegt. (Standard: Theme)
		if($gPreferences['captcha_fonts'] == 'Theme')
			{$this->font = THEME_SERVER_PATH. '/font.ttf';}
		else
			{$this->font = SERVER_PATH .'/adm_program/system/fonts/'.$gPreferences['captcha_fonts'];}
		
		// Hier wird die Schriftart f�r die Bildunterschrift festgelegt. (Standard: Theme, nicht wechselbar)
		$this->signature = THEME_SERVER_PATH. '/font.ttf';
		
        // Nun die Bildgroesse des Captchas festlegen
        $this->width = $gPreferences['captcha_width'];
        $this->height = $gPreferences['captcha_height'];

        // Hier wird die Hintergrundfarbe festgelegt. Einzelne RGB-Werte (Umwandlung aus Hex-Wert)
		$color = $gPreferences['captcha_background_color'];
		if($color[0] == '#')
			{$color = substr($color, 1);}
		if(strlen($color) == 6)
			{list($r, $g, $b) = array($color[0].$color[1],$color[2].$color[3],$color[4].$color[5]);}
		elseif(strlen($color) == 3)
			{list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);}
		$this->backgroundColourR = hexdec($r);
		$this->backgroundColourG = hexdec($g);
		$this->backgroundColourB = hexdec($b);
        
        // Hier wird die Schriftgroesse des CaptchaCodes festgelegt.
        $this->codeSize = $gPreferences['captcha_font_size'];

        // Hier wird der Untertitel festgelegt.
        $this->backgroundWriting = $gPreferences['captcha_signature'];
        $this->backgroundWritingSize = $gPreferences['captcha_signature_font_size'];

        // Diese Zeichen sind erlaubt innerhalb des Captcha-Codes.
        $this->allowedChars = $gPreferences['captcha_signs'];

    }

    public function getCaptcha()
    {
		// erst einmal einen Code generieren
			$this->generateNewCaptchaCode();

			// und hier wird das Captcha generiert und ausgegeben
			$this->makeCaptcha();
    }
	
	public function getCaptchaCalc($text_part1,$text_part2,$text_part3_third,$text_part3_half,$text_part4)
    {
		// Zuweisung der Einstiegsvariablen
		$number = array(rand(40,60),rand(20,40),rand(1,20));
		$operator_value = array();
		$result = $number[0];
		
		// Rechenaufgabe erstellen
		for($count=1;$count<=2;$count++)
		{
			$operator = rand(1,2);
			if($operator == 1)
			{
				$result = $result+$number[$count];
				$operator_value[$count-1] = '+';
			}
			if($operator == 2)
			{
				$result = $result-$number[$count];
				$operator_value[$count-1] = '-';
			}
			if($count==2 && $result<1)
			{
				$count=1;
				$result = $number[0];
			}
		}
		
		// Individualwert dazurechen
		$ready = 0;
		while($ready < 1)
		{
			$number[3] = rand(20,100);
			if(is_int($number[3]/3))
			{
				$operator_value[2] = $text_part3_third;
				$result = $result+($number[3]/3);
				$ready = 1;
			}
			else if(is_int($number[3]/2))
			{
				$operator_value[2] = $text_part3_half;
				$result = $result+($number[3]/2);
				$ready = 1;
			}
		}
		
		// L�sung in der Session speichern
        $_SESSION['captchacode'] = $result;
		
		// Aufgabe ausgeben
		echo "$text_part1 $number[0]$operator_value[0]$number[1]$operator_value[1]$number[2] $text_part2 $operator_value[2] $number[3] $text_part4";
		//echo "<br>= $result (".$_SESSION['captchacode'].")";
    }	


    private function generateNewCaptchaCode()
    {
        // neuen CaptchaCode erzeugen...

        // Hier wird die Anzahl der Captcha-Zeichen festgelegt
        // (das Captcha soll zwischen 4 und 6 Zeichen beinhalten)
        $this->charCount = rand(4,6);

        $this->captchaCode = '';
        for ($i=0; $i < $this->charCount; $i++)
        {
            $this->captchaCode = $this->captchaCode. $this->allowedChars{rand(0,strlen($this->allowedChars)-1)};
        }

        // hier wird der Code jetzt in der Session gespeichert...
        $_SESSION['captchacode'] = $this->captchaCode;
    }


    private function makeCaptcha()
    {

        // ein leeres Bild definieren
        $image = imagecreate($this->width, $this->height);

        // Hintergrundfarbe setzen...
        $background =  imagecolorallocate($image, $this->backgroundColourR, $this->backgroundColourG, $this->backgroundColourB);
        ImageFilledRectangle($image, 0, 0, $this->width, $this->height, $background);

        // Gitter in den Hintergrund zeichnen...
        // erst vertikal...
        for($i=0; $i < $this->width; $i += intval($this->backgroundWritingSize / 2))
        {
            $color    = imagecolorallocate($image, $this->backgroundColourR - 40, $this->backgroundColourG - 40, $this->backgroundColourB - 40);
            imageline($image, $i, 0, $i, $this->height, $color);
        }

        // ...dann horizontal
        for($i=0; $i < $this->height; $i += intval($this->backgroundWritingSize / 2))
        {
            imageline($image, 0, $i, $this->width, $i, $color);
        }

        // Untertitel in das Captcha reinschreiben...
        ImageTTFText($image, $this->backgroundWritingSize, 0, 15, $this->height-5, imagecolorallocate($image, 0, 0, 0), $this->signature, $this->backgroundWriting);



        // Jetzt wird dem Bild der eigentliche CaptchaCode hinzugefuegt...
        $xStartPosition = 15;

        for ($i=0; $i < $this->charCount; $i++)
        {
                $xPosition = intval($xStartPosition + $i * ($this->width / ($this->charCount +1)));

                $text    = substr($this->captchaCode, $i, 1);
                $color    =  imagecolorallocate($image, $this->backgroundColourR - 125, $this->backgroundColourG - 55, $this->backgroundColourB - 90);
                ImageTTFText($image, $this->codeSize, 0, $xPosition, 35, $color, $this->font, $text);
        }

        // Jetzt noch das finale Bild ausgeben...
        header('Content-type: image/png');
        ImagePNG($image);
        ImageDestroy($image);

    }
}

// Wenn ein Bildchen erzeugt werden soll, wird hier die Klasse initialisiert und die Ausgabe angestossen.
if($gPreferences['captcha_type']=='pic')
{
	$captcha = new Captcha();
	$captcha->getCaptcha();
}

?>