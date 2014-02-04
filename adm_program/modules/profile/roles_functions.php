<?php
/******************************************************************************
 * Funktionen zum Verwalten der Rollenmitgliedschaft im Profil
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

if ('roles_functions.php' == basename($_SERVER['SCRIPT_FILENAME']))
{
    die('This page may not be called directly !');
}

require_once('../../system/classes/table_members.php');
require_once('../../system/classes/table_roles.php');


function getRolesFromDatabase($gDb,$user_id,$gCurrentOrganization)
{
    require_once('../../system/common.php');
    // Alle Rollen auflisten, die dem Mitglied zugeordnet sind
    $sql = 'SELECT *
              FROM '. TBL_MEMBERS. ', '. TBL_ROLES. ', '. TBL_CATEGORIES. '
             WHERE mem_rol_id  = rol_id
               AND mem_end    >= \''.DATE_NOW.'\'
               AND mem_usr_id  = '.$user_id.'
               AND rol_valid   = 1
               AND rol_visible = 1
               AND rol_cat_id  = cat_id
               AND (  cat_org_id  = '. $gCurrentOrganization->getValue('org_id'). '
                   OR cat_org_id IS NULL )
             ORDER BY cat_org_id, cat_sequence, rol_name';
    return $gDb->query($sql);
}
function getFormerRolesFromDatabase($gDb,$user_id,$gCurrentOrganization)
{
    require_once('../../system/common.php');
    $sql    = 'SELECT *
                 FROM '. TBL_MEMBERS. ', '. TBL_ROLES. ', '. TBL_CATEGORIES. '
                WHERE mem_rol_id  = rol_id
                  AND mem_end     < \''.DATE_NOW.'\'
                  AND mem_usr_id  = '.$user_id.'
                  AND rol_valid   = 1
                  AND rol_visible = 1
                  AND rol_cat_id  = cat_id
                  AND (  cat_org_id  = '. $gCurrentOrganization->getValue('org_id'). '
                      OR cat_org_id IS NULL )
                ORDER BY cat_org_id, cat_sequence, rol_name';
    return $gDb->query($sql);
}
function getRoleMemberships($gDb,$gCurrentUser,$user,$result_role,$count_role,$directOutput,$gL10n)
{
    global $gPreferences, $g_root_path, $gCurrentOrganization;
    
    $count_show_roles  = 0;
    $member = new TableMembers($gDb);
	$role   = new TableRoles($gDb);
    $roleMemHTML = '<ul class="formFieldList" id="role_list">';

    while($row = $gDb->fetch_array($result_role))
    {
        if(($gCurrentUser->viewRole($row['mem_rol_id']) || $gCurrentUser->getValue('usr_id') == $user->getValue('usr_id')) && $row['rol_visible']==1)
        {
            $show_rol_end_date = false;

            $member->clear();
            $member->setArray($row);
			$role->clear();
			$role->setArray($row);
            
            // falls die Mitgliedschaft nicht endet, dann soll das fiktive Enddatum auch nicht angezeigt werden
            $dateEndless = new DateTime('9999-12-31 00:00:00');
            if ($member->getValue('mem_end', $gPreferences['system_date']) != $dateEndless->format($gPreferences['system_date']))
            {
               $show_rol_end_date = true;
            }

            // jede einzelne Rolle anzeigen
            $roleMemHTML .= '<li id="role_'. $row['mem_rol_id']. '">
                <dl>
                    <dt>
                        '. $role->getValue('cat_name'). ' - ';
                            if($gCurrentUser->viewRole($member->getValue('mem_rol_id')) || $gCurrentUser->getValue('usr_id') == $user->getValue('usr_id'))
                            {
                                $roleMemHTML .= '<a href="'. $g_root_path. '/adm_program/modules/lists/lists_show.php?mode=html&amp;rol_id='. $member->getValue('mem_rol_id'). '" title="'. $role->getValue('rol_description'). '">'. $role->getValue('rol_name'). '</a>';
                            }
                            else
                            {
                                echo $role->getValue('rol_name');
                            }
                            if($member->getValue('mem_leader') == 1)
                            {
                                $roleMemHTML .= ' - '.$gL10n->get('SYS_LEADER');
                            }
                        $roleMemHTML .= '&nbsp;
                    </dt>
                    <dd>';
                        if($show_rol_end_date == true)
                        {
                            $roleMemHTML .= $gL10n->get('SYS_SINCE_TO',$member->getValue('mem_begin', $gPreferences['system_date']),$member->getValue('mem_end', $gPreferences['system_date']));
                        }
                        else
                        {
                            $roleMemHTML .= $gL10n->get('SYS_SINCE',$member->getValue('mem_begin', $gPreferences['system_date']));
                        }
                        if($gCurrentUser->assignRoles())
                        {
                            // Löschen wird nur bei anderen Webmastern ermöglicht
                            if ($role->getValue('rol_name') != $gL10n->get('SYS_WEBMASTER'))
                            {
                                $roleMemHTML .= '
                                <a class="iconLink" rel="lnkPopupWindow" href="'.$g_root_path.'/adm_program/system/popup_message.php?type=pro_role&amp;element_id=role_'.
                                    $role->getValue('rol_id'). '&amp;database_id='.$role->getValue('rol_id').'&amp;database_id_2='.$user->getValue('usr_id').'&amp;name='.urlencode($role->getValue('rol_name')).'"><img
                                    src="'. THEME_PATH. '/icons/delete.png" alt="'.$gL10n->get('PRO_CANCEL_MEMBERSHIP').'" title="'.$gL10n->get('PRO_CANCEL_MEMBERSHIP').'" /></a>';
                            }
                            else
                            {
                                $roleMemHTML .= '
                                <a class="iconLink"><img src="'.THEME_PATH.'/icons/dummy.png" alt=""/></a>';
                            }
                            // Bearbeiten des Datums nicht bei Webmastern möglich
                            if ($row['rol_name'] != $gL10n->get('SYS_WEBMASTER'))
                            {
                                $roleMemHTML .= '<a class="iconLink" style="cursor:pointer;" onclick="profileJS.toggleDetailsOn('.$row['rol_id'].')"><img
                                    src="'.THEME_PATH.'/icons/edit.png" alt="'.$gL10n->get('PRO_CHANGE_DATE').'" title="'.$gL10n->get('PRO_CHANGE_DATE').'" /></a>';
                            }
                            else
                            {
                                $roleMemHTML .= '<a class="iconLink"><img src="'.THEME_PATH.'/icons/dummy.png" alt=""/></a>';
                            }

                        }
                    $roleMemHTML .= '</dd>
                </dl>
            </li>
            <li id="mem_rol_'.$role->getValue('rol_id').'" style="text-align: right; visibility: hidden; display: none;">
                <form action="'.$g_root_path.'/adm_program/modules/profile/roles_date.php?usr_id='.$user->getValue('usr_id').'&amp;mode=1&amp;rol_id='.$role->getValue('rol_id').'" method="post">
                    <div>
                        <label for="admRoleStart'.$role->getValue('rol_id').'">'.$gL10n->get('SYS_START').':</label>
                        <input type="text" id="admRoleStart'.$role->getValue('rol_id').'" name="rol_begin" size="10" maxlength="20" value="'.$member->getValue('mem_begin', $gPreferences['system_date']).'"/>
                        <a class="iconLink" id="admRoleAnchorStart'.$role->getValue('rol_id').'" href="javascript:calPopup.select(document.getElementById(\'admRoleStart'.$role->getValue('rol_id').'\'),\'admRoleAnchorStart'.$role->getValue('rol_id').'\',\''.$gPreferences['system_date'].'\',\'admRoleStart'.$role->getValue('rol_id').'\',\'admRoleEnd'.$role->getValue('rol_id').'\');"><img 
                        src="'.THEME_PATH.'/icons/calendar.png" alt="'.$gL10n->get('SYS_SHOW_CALENDAR').'" title="'.$gL10n->get('SYS_SHOW_CALENDAR').'" /></a>&nbsp;

                        <label for="admRoleEnd'.$role->getValue('rol_id').'">'.$gL10n->get('SYS_END').':</label>
                        <input type="text" id="admRoleEnd'.$role->getValue('rol_id').'" name="rol_end" size="10" maxlength="20" value="'.$member->getValue('mem_end', $gPreferences['system_date']).'"/>
                        <a class="iconLink" id="admRoleAnchorEnd'.$role->getValue('rol_id').'" href="javascript:calPopup.select(document.getElementById(\'admRoleEnd'.$role->getValue('rol_id').'\'),\'admRoleAnchorEnd'.$role->getValue('rol_id').'\',\''.$gPreferences['system_date'].'\',\'admRoleStart'.$role->getValue('rol_id').'\',\'admRoleEnd'.$role->getValue('rol_id').'\');"><img 
                        src="'.THEME_PATH.'/icons/calendar.png" alt="'.$gL10n->get('SYS_SHOW_CALENDAR').'" title="'.$gL10n->get('SYS_SHOW_CALENDAR').'" /></a>

                        <a class="iconLink" href="javascript:profileJS.changeRoleDates('.$role->getValue('rol_id').')" id="admSaveMembership'.$role->getValue('rol_id').'"><img src="'.THEME_PATH.'/icons/disk.png" alt="'.$gL10n->get('SYS_SAVE').'" title="'.$gL10n->get('SYS_SAVE').'"/></a>
                        <a class="iconLink" href="javascript:profileJS.toggleDetailsOff('.$role->getValue('rol_id').')"><img src="'.THEME_PATH.'/icons/delete.png" alt="'.$gL10n->get('SYS_ABORT').'" title="'.$gL10n->get('SYS_ABORT').'"/></a>
                    </div>
                </form>
            </li>';
            $count_show_roles++;
        }
    }
    if($count_show_roles == 0)
    {
        $roleMemHTML .= $gL10n->get('ROL_NO_MEMBER_RESP_ROLE_VISIBLE',$gCurrentOrganization->getValue('org_longname'));
    }
            
    $roleMemHTML .= '</ul>
    <span id="calendardiv" style="position: absolute; visibility: hidden;"></span>';

    if($directOutput)
    {
        echo $roleMemHTML;
        return '';
    }
    else
    {
        return $roleMemHTML;	
    }
}
function getFormerRoleMemberships($gDb,$gCurrentUser,$user,$result_role,$count_role,$directOutput,$gL10n)
{
    global $gPreferences, $g_root_path;

    $count_show_roles = 0;
    $member = new TableMembers($gDb);
	$role   = new TableRoles($gDb);
    $formerRoleMemHTML = '<ul class="formFieldList" id="former_role_list">';
    while($row = $gDb->fetch_array($result_role))
    {
        $member->clear();
        $member->setArray($row);
		$role->clear();
		$role->setArray($row);

        if($gCurrentUser->viewRole($member->getValue('mem_rol_id')))
        {
            // jede einzelne Rolle anzeigen
            $formerRoleMemHTML .= '
            <li id="former_role_'. $member->getValue('mem_rol_id'). '">
                <dl>
                    <dt>'.
                        $role->getValue('cat_name');
                        if($gCurrentUser->viewRole($member->getValue('mem_rol_id')))
                        {
                            $formerRoleMemHTML .= ' - <a href="'.$g_root_path.'/adm_program/modules/lists/lists_show.php?mode=html&amp;rol_id='. $member->getValue('mem_rol_id'). '">'. $role->getValue('rol_name'). '</a>';
                        }
                        else
                        {
                            $formerRoleMemHTML .= ' - '. $role->getValue('rol_name');
                        }
                        if($member->getValue('mem_leader') == 1)
                        {
                            $formerRoleMemHTML .= ' - '.$gL10n->get('SYS_LEADER');
                        }
                    $formerRoleMemHTML .= '</dt>
                    <dd>'.$gL10n->get('SYS_FROM_TO', $member->getValue('mem_begin', $gPreferences['system_date']), $member->getValue('mem_end', $gPreferences['system_date']));
                        if($gCurrentUser->isWebmaster())
                        {
                            $formerRoleMemHTML .= '
                            <a class="iconLink" rel="lnkPopupWindow" href="'.$g_root_path.'/adm_program/system/popup_message.php?type=pro_former&amp;element_id=former_role_'.
                                $role->getValue('rol_id'). '&amp;database_id='.$role->getValue('rol_id').'&amp;database_id_2='.$user->getValue('usr_id').'&amp;name='.urlencode($role->getValue('rol_name')).'"><img
                                src="'. THEME_PATH. '/icons/delete.png" alt="'.$gL10n->get('PRO_CANCEL_MEMBERSHIP').'" title="'.$gL10n->get('PRO_CANCEL_MEMBERSHIP').'" /></a>';
                        }
                    $formerRoleMemHTML .= '</dd>
                </dl>
            </li>';
            $count_show_roles++;
        }
    }
    if($count_show_roles == 0 && $count_role > 0)
    {
        $formerRoleMemHTML .= $gL10n->get('ROL_CANT_SHOW_FORMER_ROLES');
    }
    $formerRoleMemHTML .= '</ul>
    <script type="text/javascript">if(profileJS){profileJS.formerRoleCount="'.$count_role.'";}</script>';

    if($directOutput)
    {
        echo $formerRoleMemHTML;
        return "";
    }
    else
    {
        return $formerRoleMemHTML;	
    }
}
?>
