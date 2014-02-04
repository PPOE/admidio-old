<?php
/******************************************************************************
 * Class handle role rights, cards and other things of users
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Diese Klasse dient dazu ein Userobjekt zu erstellen.
 * Ein User kann ueber diese Klasse in der Datenbank verwaltet werden
 *
 * Beside the methods of the parent class there are the following additional methods:
 *
 * checkPassword($password) - check password against stored hash    
 * deleteUserFieldData()    - delete all user data of profile fields; 
 *                            user record will not be deleted
 * getListViewRights()  - Liefert ein Array mit allen Rollen und der 
 *                        Berechtigung, ob der User die Liste einsehen darf
 *                      - aehnlich getProperty, allerdings suche ueber usf_id
 * getVCard()           - Es wird eine vCard des Users als String zurueckgegeben
 * viewProfile          - Ueberprueft ob der User das Profil eines uebrgebenen
 *                        Users einsehen darf
 * viewRole             - Ueberprueft ob der User eine uebergebene Rolle(Liste)
 *                        einsehen darf
 * isWebmaster()        - gibt true/false zurueck, falls der User Mitglied der
 *                        Rolle "Webmaster" ist
 *
 *****************************************************************************/

require_once(SERVER_PATH. '/adm_program/system/classes/table_users.php');

class User extends TableUsers
{
    protected $webmaster;

    public $mProfileFieldsData; 		// object with current user field structure
    public $roles_rights = array(); // Array ueber alle Rollenrechte mit dem entsprechenden Status des Users
    protected $list_view_rights = array(); // Array ueber Listenrechte einzelner Rollen => Zugriff nur �ber getListViewRights()
    protected $role_mail_rights = array(); // Array ueber Mailrechte einzelner Rollen

    // Konstruktor
    public function __construct(&$db, $userFields, $usr_id = 0)
    {
		$this->mProfileFieldsData = clone $userFields; // create explicit a copy of the object (param is in PHP5 a reference)
        parent::__construct($db, $usr_id);
    }

    // check password against stored hash    
    public function checkPassword($password)
    {
        // if password is stored with phpass hash, then use phpass
        if(substr($this->getValue('usr_password'), 0, 1) == '$')
        {
            $passwordHasher = new PasswordHash(9, true);
            if($passwordHasher->CheckPassword($password, $this->getValue('usr_password')) == true)
            {
                return true;
            }
        }
        // if password is stored the old was then use md5
        elseif(md5($password) == $this->getValue('usr_password'))
        {
            $this->setValue('usr_password', $password);
            return true;
        }
        return false;
    }

    // Methode prueft, ob der User das uebergebene Rollenrecht besitzt und setzt das Array mit den Flags,
    // welche Rollen der User einsehen darf
    public function checkRolesRight($right = '')
    {
        global $gL10n;

        if($this->getValue('usr_id') > 0)
        {
            if(count($this->roles_rights) == 0)
            {
                global $gCurrentOrganization;
                $tmp_roles_rights  = array('rol_assign_roles'  => '0', 'rol_approve_users' => '0',
                                           'rol_announcements' => '0', 'rol_dates' => '0',
                                           'rol_download'      => '0', 'rol_edit_user' => '0',
                                           'rol_guestbook'     => '0', 'rol_guestbook_comments' => '0',
                                           'rol_mail_to_all'   => '0',
                                           'rol_photo'         => '0', 'rol_profile' => '0',
                                           'rol_weblinks'      => '0', 'rol_all_lists_view' => '0');

                // Alle Rollen der Organisation einlesen und ggf. Mitgliedschaft dazu joinen
                $sql = 'SELECT *
                          FROM '. TBL_CATEGORIES. ', '. TBL_ROLES. '
                          LEFT JOIN '. TBL_MEMBERS. '
                            ON mem_usr_id  = '. $this->getValue('usr_id'). '
                           AND mem_rol_id  = rol_id
                           AND mem_end     > \''.DATE_NOW.'\'
                         WHERE rol_valid   = 1
                           AND rol_cat_id  = cat_id
                           AND (  cat_org_id = '. $gCurrentOrganization->getValue('org_id').' 
                               OR cat_org_id IS NULL ) ';
                $this->db->query($sql);

                while($row = $this->db->fetch_array())
                {
                    // Rechte nur beruecksichtigen, wenn auch Rollenmitglied
                    if($row['mem_usr_id'] > 0)
                    {
                        // Rechte der Rollen in das Array uebertragen,
                        // falls diese noch nicht durch andere Rollen gesetzt wurden
                        foreach($tmp_roles_rights as $key => $value)
                        {
                            if($value == '0' && $row[$key] == '1')
                            {
                                $tmp_roles_rights[$key] = '1';
                            }
                        }
                    }

                    // Webmasterflag setzen
                    if($row['mem_usr_id'] > 0 && $row['rol_name'] == $gL10n->get('SYS_WEBMASTER'))
                    {
                        $this->webmaster = 1;
                    }

                    // Listenansichtseinstellung merken
                    // Leiter duerfen die Rolle sehen
                    if($row['mem_usr_id'] > 0 && ($row['rol_this_list_view'] > 0 || $row['mem_leader'] == 1))
                    {
                        // Mitgliedschaft bei der Rolle und diese nicht gesperrt, dann anschauen
                        $this->list_view_rights[$row['rol_id']] = 1;
                    }
                    elseif($row['rol_this_list_view'] == 2)
                    {
                        // andere Rollen anschauen, wenn jeder sie sehen darf
                        $this->list_view_rights[$row['rol_id']] = 1;
                    }
                    else
                    {
                        $this->list_view_rights[$row['rol_id']] = 0;
                    }

                    // Mailrechte setzen
                    // Leiter duerfen der Rolle Mails schreiben
                    if($row['mem_usr_id'] > 0 && ($row['rol_mail_this_role'] > 0 || $row['mem_leader'] == 1))
                    {
                        // Mitgliedschaft bei der Rolle und diese nicht gesperrt, dann anschauen
                        $this->role_mail_rights[$row['rol_id']] = 1;
                    }
                    elseif($row['rol_mail_this_role'] >= 2)
                    {
                        // andere Rollen anschauen, wenn jeder sie sehen darf
                        $this->role_mail_rights[$row['rol_id']] = 1;
                    }
                    else
                    {
                        $this->role_mail_rights[$row['rol_id']] = 0;
                    }
                }
                $this->roles_rights = $tmp_roles_rights;

                // ist das Recht 'alle Listen einsehen' gesetzt, dann dies auch im Array bei allen Rollen setzen
                if($this->roles_rights['rol_all_lists_view'])
                {
                    foreach($this->list_view_rights as $key => $value)
                    {
                        $this->list_view_rights[$key] = 1;
                    }
                }

                // ist das Recht 'allen Rollen EMails schreiben' gesetzt, dann dies auch im Array bei allen Rollen setzen
                if($this->roles_rights['rol_mail_to_all'])
                {
                    foreach($this->role_mail_rights as $key => $value)
                    {
                        $this->role_mail_rights[$key] = 1;
                    }
                }

            }

            if(strlen($right) == 0 || $this->roles_rights[$right] == 1)
            {
                return true;
            }
        }
        return 0;
    }

    // alle Klassenvariablen wieder zuruecksetzen
    public function clear()
    {
        parent::clear();

        // die Daten der Profilfelder werden geloescht, die Struktur bleibt
		$this->mProfileFieldsData->clearUserData();

        $this->webmaster = 0;

        // Arrays initialisieren
        $this->roles_rights = array();
        $this->list_view_rights = array();
        $this->role_mail_rights = array();
    }
    
    // returns true if a column of user table or profile fields has changed
    public function columnsValueChanged()
    {
    	if($this->columnsValueChanged == true
    	|| $this->mProfileFieldsData->columnsValueChanged == true)
    	{
    		return true;
    	}
    
    	return false;
    }
    
    // delete all user data of profile fields; user record will not be deleted
    public function deleteUserFieldData()
    {
		$this->db->startTransaction();
		
        // delete every entry from adm_users_data
        foreach($this->mProfileFieldsData->mUserData as $field)
        {
			$field->delete();
        }
        
		$this->mProfileFieldsData->mUserData = array();
		$this->db->endTransaction();
    }
    
	/** Creates an array with all roles where the user has the right to view them
	 *  @return Array with roles where user has the right to view them
	 */
    public function getAllVisibleRoles()
    {
		$visibleRoles = array();
        $this->checkRolesRight();

		foreach($this->list_view_rights as $role => $right)
		{
			if($right == 1)
			{
				$visibleRoles[] = $role;
			}
		}
        return $visibleRoles;
    }

    // Methode prueft, ob evtl. ein Wert aus der User-Fields-Tabelle
    // angefordert wurde und gibt diesen zurueck
    public function getValue($field_name, $format = '')
    {
        if(strpos($field_name, 'usr_') === 0)
        {
            return parent::getValue($field_name, $format);
        }
        else
        {
			return $this->mProfileFieldsData->getValue($field_name, $format, $this->getValue('usr_id'));
        }
    }

    // gibt die Userdaten als VCard zurueck
    // da das Windows-Adressbuch einschliesslich XP kein UTF8 verarbeiten kann, alles in ISO-8859-1 ausgeben
    public function getVCard()
    {
        global $gCurrentUser, $gPreferences;

        $editAllUsers = $gCurrentUser->editProfile($this->getValue('usr_id'));

        $vcard  = (string) "BEGIN:VCARD\r\n";
        $vcard .= (string) "VERSION:2.1\r\n";
        if($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('FIRST_NAME', 'usf_hidden') == 0))
        {
            $vcard .= (string) "N;CHARSET=ISO-8859-1:" . utf8_decode($this->getValue('LAST_NAME')). ";". utf8_decode($this->getValue('FIRST_NAME')) . ";;;\r\n";
        }
        if($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('LAST_NAME', 'usf_hidden') == 0))
        {
            $vcard .= (string) "FN;CHARSET=ISO-8859-1:". utf8_decode($this->getValue('FIRST_NAME')) . " ". utf8_decode($this->getValue('LAST_NAME')) . "\r\n";
        }
        if (strlen($this->getValue('usr_login_name')) > 0)
        {
            $vcard .= (string) "NICKNAME;CHARSET=ISO-8859-1:" . utf8_decode($this->getValue("usr_login_name")). "\r\n";
        }
        if (strlen($this->getValue('PHONE')) > 0
        && ($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('PHONE', 'usf_hidden') == 0)))
        {
            $vcard .= (string) "TEL;HOME;VOICE:" . $this->getValue('PHONE'). "\r\n";
        }
        if (strlen($this->getValue('MOBILE')) > 0
        && ($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('MOBILE', 'usf_hidden') == 0)))
        {
            $vcard .= (string) "TEL;CELL;VOICE:" . $this->getValue('MOBILE'). "\r\n";
        }
        if (strlen($this->getValue('FAX')) > 0
        && ($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('FAX', 'usf_hidden') == 0)))
        {
            $vcard .= (string) "TEL;HOME;FAX:" . $this->getValue('FAX'). "\r\n";
        }
        if($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('ADDRESS', 'usf_hidden') == 0 && $this->mProfileFieldsData->getProperty('CITY', 'usf_hidden') == 0
        && $this->mProfileFieldsData->getProperty('POSTCODE', 'usf_hidden') == 0  && $this->mProfileFieldsData->getProperty('COUNTRY', 'usf_hidden') == 0))
        {
            $vcard .= (string) "ADR;CHARSET=ISO-8859-1;HOME:;;" . utf8_decode($this->getValue('ADDRESS')). ";" . utf8_decode($this->getValue('CITY')). ";;" . utf8_decode($this->getValue('POSTCODE')). ";" . utf8_decode($this->getValue('COUNTRY')). "\r\n";
        }
        if (strlen($this->getValue('WEBSITE')) > 0
        && ($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('WEBSITE', 'usf_hidden') == 0)))
        {
            $vcard .= (string) "URL;HOME:" . $this->getValue('WEBSITE'). "\r\n";
        }
        if (strlen($this->getValue('BIRTHDAY')) > 0
        && ($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('BIRTHDAY', 'usf_hidden') == 0)))
        {
            $vcard .= (string) "BDAY:" . $this->getValue('BIRTHDAY', 'Ymd') . "\r\n";
        }
        if (strlen($this->getValue('EMAIL')) > 0
        && ($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('EMAIL', 'usf_hidden') == 0)))
        {
            $vcard .= (string) "EMAIL;PREF;INTERNET:" . $this->getValue('EMAIL'). "\r\n";
        }
        if (file_exists(SERVER_PATH.'/adm_my_files/user_profile_photos/'.$this->getValue('usr_id').'.jpg') && $gPreferences['profile_photo_storage'] == 1)
        {
            $img_handle = fopen (SERVER_PATH. '/adm_my_files/user_profile_photos/'.$this->getValue('usr_id').'.jpg', 'rb');
            $vcard .= (string) "PHOTO;ENCODING=BASE64;TYPE=JPEG:".base64_encode(fread ($img_handle, filesize (SERVER_PATH. '/adm_my_files/user_profile_photos/'.$this->getValue('usr_id').'.jpg'))). "\r\n";
            fclose($img_handle);
        }
        if (strlen($this->getValue('usr_photo')) > 0 && $gPreferences['profile_photo_storage'] == 0)
        {
            $vcard .= (string) "PHOTO;ENCODING=BASE64;TYPE=JPEG:".base64_encode($this->getValue('usr_photo')). "\r\n";
        }
        // Geschlecht ist nicht in vCard 2.1 enthalten, wird hier fuer das Windows-Adressbuch uebergeben
        if ($this->getValue('GENDER') > 0
        && ($editAllUsers || ($editAllUsers == false && $this->mProfileFieldsData->getProperty('GENDER', 'usf_hidden') == 0)))
        {
            if($this->getValue('GENDER') == 1)
            {
                $wab_gender = 2;
            }
            else
            {
                $wab_gender = 1;
            }
            $vcard .= (string) "X-WAB-GENDER:" . $wab_gender . "\r\n";
        }
        if (strlen($this->getValue('usr_timestamp_change')) > 0)
        {
            $vcard .= (string) "REV:" . $this->getValue('usr_timestamp_change', 'ymdThis') . "\r\n";
        }

        $vcard .= (string) "END:VCARD\r\n";
        return $vcard;
    }

    public function readData($usr_id, $sql_where_condition = '', $sql_additional_tables = '')
    {
        parent::readData($usr_id, $sql_where_condition, $sql_additional_tables);

		// read data of all user fields from current user
		$this->mProfileFieldsData->readUserData($this->getValue('usr_id'));
    }
    
    // bei setValue werden die Werte nicht auf Gueltigkeit geprueft
    public function noValueCheck()
    {
        $this->mProfileFieldsData->noValueCheck();
    }

    public function save($updateFingerPrint = true)
    {
        global $gCurrentSession;
        $fields_changed = $this->columnsValueChanged;
		$this->db->startTransaction();

		// if value of a field changed then update timestamp of user object
		if($this->mProfileFieldsData->columnsValueChanged)
		{
            $this->columnsValueChanged = true;
		}

        parent::save($updateFingerPrint);
		
		// save data of all user fields
		$this->mProfileFieldsData->saveUserData($this->getValue('usr_id'));

        if($fields_changed && is_object($gCurrentSession))
        {
            // einlesen aller Userobjekte der angemeldeten User anstossen, da evtl.
            // eine Rechteaenderung vorgenommen wurde
            $gCurrentSession->renewUserObject($this->getValue('usr_id'));
        }
		$this->db->endTransaction();
    }

    // interne Methode, die bei setValue den uebergebenen Wert prueft
    // und ungueltige Werte auf leer setzt
    public function setValue($field_name, $field_value, $check_value = true)
    {
        global $gCurrentUser;
        $return_code  = true;
        $update_field = false;

        if(strpos($field_name, 'usr_') !== 0)
        {
            // Daten fuer User-Fields-Tabelle

            // gesperrte Felder duerfen nur von Usern mit dem Rollenrecht 'alle Benutzerdaten bearbeiten' geaendert werden
            // bei Registrierung muss die Eingabe auch erlaubt sein
            if((  $this->mProfileFieldsData->getProperty($field_name, 'usf_disabled') == 1
               && ($gCurrentUser->editUsers() == true || $gCurrentUser->editProfile($this->getValue('usr_id'))))
            || $this->mProfileFieldsData->getProperty($field_name, 'usf_disabled') == 0
            || ($gCurrentUser->getValue('usr_id') == 0 && $this->getValue('usr_id') == 0))
            {
                // versteckte Felder duerfen nur von Usern mit dem Rollenrecht 'alle Benutzerdaten bearbeiten' geaendert werden
                // oder im eigenen Profil
                if((  $this->mProfileFieldsData->getProperty($field_name, 'usf_hidden') == 1
                   && ($gCurrentUser->editUsers() == true || $gCurrentUser->editProfile($this->getValue('usr_id'))))
                || $this->mProfileFieldsData->getProperty($field_name, 'usf_hidden') == 0
                || $gCurrentUser->getValue('usr_id') == $this->getValue('usr_id'))
                {
                    $update_field = true;
                }
            }

            // nur Updaten, wenn sich auch der Wert geaendert hat
            if($update_field == true
            && $field_value  != $this->mProfileFieldsData->getValue($field_name))
            {
				$return_code = $this->mProfileFieldsData->setValue($field_name, $field_value);
            }
        }
        else
        {
            $return_code = parent::setValue($field_name, $field_value);
        }
        return $return_code;
    }

    // Funktion prueft, ob der angemeldete User Ankuendigungen anlegen und bearbeiten darf
    public function editAnnouncements()
    {
        return $this->checkRolesRight('rol_announcements');
    }

    // Funktion prueft, ob der angemeldete User Registrierungen bearbeiten und zuordnen darf
    public function approveUsers()
    {
        return $this->checkRolesRight('rol_approve_users');
    }

    // Funktion prueft, ob der angemeldete User Rollen zuordnen, anlegen und bearbeiten darf
    public function assignRoles()
    {
        return $this->checkRolesRight('rol_assign_roles');
    }

    //Ueberprueft ob der User das Recht besitzt, alle Rollenlisten einsehen zu duerfen
    public function viewAllLists()
    {
        return $this->checkRolesRight('rol_all_lists_view');
    }

    //Ueberprueft ob der User das Recht besitzt, allen Rollenmails zu zusenden
    public function mailAllRoles()
    {
        return $this->checkRolesRight('rol_mail_to_all');
    }

    // Funktion prueft, ob der angemeldete User Termine anlegen und bearbeiten darf
    public function editDates()
    {
        return $this->checkRolesRight('rol_dates');
    }

    // Funktion prueft, ob der angemeldete User Downloads hochladen und verwalten darf
    public function editDownloadRight()
    {
        return $this->checkRolesRight('rol_download');
    }

    // Funktion prueft, ob der angemeldete User das entsprechende Profil bearbeiten darf
    public function editProfile($profileID = NULL)
    {
        if($profileID == NULL)
        {
            $profileID = $this->getValue('usr_id');
        }

        //soll das eigene Profil bearbeitet werden?
        if($profileID == $this->getValue('usr_id') && $this->getValue('usr_id') > 0)
        {
            $edit_profile = $this->checkRolesRight('rol_profile');

            if($edit_profile == 1)
            {
                return true;
            }
            else if ($this->editUsers() == true)
            {
                return true;
            }

        }
        else if ($this->editUsers() == true)
        {
            return true;
        }
        if ($this->isLeaderFor($profileID) && $this->isLGF())
        {
            return true;
        }
        return false;
    }

    // Funktion prueft, ob der angemeldete User irgendwo LGF ist
    public function isLGF()
    {
        $sql = "SELECT * FROM ". TBL_ROLES. " WHERE rol_id IN (SELECT mem_rol_id FROM ". TBL_MEMBERS. " WHERE mem_usr_id = " . $this->getValue("usr_id") . " AND mem_end >= curdate()) AND rol_name LIKE 'LV %'";
        $this->db->query($sql);
        if($this->db->num_rows() > 0)
        {
            while($row = $this->db->fetch_array())
            {
            }
            return true;
        }
        return false;
    }

    // Funktion prueft, ob der angemeldete User irgendwo Leiter ist
    public function isLeader()
    {
        $sql = "SELECT mem_rol_id FROM ". TBL_MEMBERS. " WHERE mem_usr_id = " . $this->getValue("usr_id") . " AND mem_leader = 1 AND mem_end >= curdate()";
        $this->db->query($sql);
        if($this->db->num_rows() > 0)
        {
            while($row = $this->db->fetch_array())
            {
            }
            return true;
        }
        return false;
    }

    // Funktion prueft, ob der angemeldete User irgendwo Leiter ist
    public function isLeaderFor($profileID)
    {
        $sql = "SELECT * FROM ". TBL_MEMBERS. " WHERE mem_rol_id IN (SELECT mem_rol_id FROM ". TBL_MEMBERS. " WHERE mem_usr_id = " . $this->getValue("usr_id") . " AND mem_leader = 1 AND mem_end >= curdate()) AND mem_usr_id = " . $profileID . " AND mem_end >= curdate()";
        $this->db->query($sql);
        if($this->db->num_rows() > 0)
        {
            while($row = $this->db->fetch_array())
            {
            }
            return true;
        }
        return false;
    }
    
    // Funktion prueft, ob der angemeldete User fremde Benutzerdaten bearbeiten darf
    public function editUsers()
    {
        return $this->checkRolesRight('rol_edit_user');
    }

    // Funktion prueft, ob der angemeldete User Gaestebucheintraege loeschen und editieren darf
    public function editGuestbookRight()
    {
        return $this->checkRolesRight('rol_guestbook');
    }

    // Funktion prueft, ob der angemeldete User Gaestebucheintraege kommentieren darf
    public function commentGuestbookRight()
    {
        return $this->checkRolesRight('rol_guestbook_comments');
    }

    // Funktion prueft, ob der angemeldete User Fotos hochladen und verwalten darf
    public function editPhotoRight()
    {
        return $this->checkRolesRight('rol_photo');
    }

    // Funktion prueft, ob der angemeldete User Weblinks anlegen und editieren darf
    public function editWeblinksRight()
    {
        return $this->checkRolesRight('rol_weblinks');
    }

    // Funktion prueft, ob der User ein Profil einsehen darf
    public function viewProfile($usr_id)
    {
        global $gCurrentOrganization;
        $view_profile = false;

        //Hat ein User Profileedit rechte, darf er es natuerlich auch sehen
        if($this->editProfile($usr_id))
        {
            $view_profile = true;
        }
        else
        {
            // Benutzer, die alle Listen einsehen duerfen, koennen auch alle Profile sehen
            if($this->viewAllLists())
            {
                $view_profile = true;
            }
            else
            {
                $sql    = 'SELECT rol_id, rol_this_list_view
                             FROM '. TBL_MEMBERS. ', '. TBL_ROLES. ', '. TBL_CATEGORIES. '
                            WHERE mem_usr_id = '.$usr_id. '
                              AND mem_end    > \''.DATE_NOW.'\'
                              AND mem_rol_id = rol_id
                              AND rol_valid  = 1
                              AND rol_cat_id = cat_id
                              AND (  cat_org_id = '. $gCurrentOrganization->getValue('org_id').'
                                  OR cat_org_id IS NULL ) ';
                $this->db->query($sql);

                if($this->db->num_rows() > 0)
                {
                    while($row = $this->db->fetch_array())
                    {
                        if($row['rol_this_list_view'] == 2)
                        {
                            // alle angemeldeten Benutzer duerfen Rollenlisten/-profile sehen
                            $view_profile = true;
                        }
                        elseif($row['rol_this_list_view'] == 1
                        && isset($this->list_view_rights[$row['rol_id']]))
                        {
                            // nur Rollenmitglieder duerfen Rollenlisten/-profile sehen
                            $view_profile = true;
                        }
                    }
                }
            }
        }
        return $view_profile;
    }

    // Methode prueft, ob der angemeldete User eine bestimmte oder alle Listen einsehen darf
    public function viewRole($rol_id)
    {
        $view_role = false;
        // Abfrage ob der User durch irgendeine Rolle das Recht bekommt alle Listen einzusehen
        if($this->viewAllLists())
        {
            $view_role = true;
        }
        else
        {
            // Falls er das Recht nicht hat Kontrolle ob fuer eine bestimmte Rolle
            if(isset($this->list_view_rights[$rol_id]) && $this->list_view_rights[$rol_id] > 0)
            {
                $view_role = true;
            }
        }
        return $view_role;
    }

	// Methode prueft, ob der angemeldete User einer bestimmten oder allen Rolle E-Mails zusenden darf
    public function mailRole($rol_id)
    {
        $mail_role = false;
        // Abfrage ob der User durch irgendeine Rolle das Recht bekommt alle Listen einzusehen
        if($this->mailAllRoles())
        {
            $mail_role = true;
        }
        else
        {
            // Falls er das Recht nicht hat Kontrolle ob fuer eine bestimmte Rolle
            if(isset($this->role_mail_rights[$rol_id]) && $this->role_mail_rights[$rol_id] > 0)
            {
                $mail_role = true;
            }
        }
        return $mail_role;
    }

    // Methode liefert true zurueck, wenn der User Mitglied der Rolle "Webmaster" ist
    public function isWebmaster()
    {
        $this->checkRolesRight();
        return $this->webmaster;
    }
}
?>
