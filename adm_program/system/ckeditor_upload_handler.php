<?php
/******************************************************************************
 * Handle image uploads from CKEditor
 *
 * Copyright    : (c) 2004 - 2012 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Parameters:
 *
 * CKEditor        : ID of textarea, that had triggered the upload
 * CKEditorFuncNum : function number, that will handle in the editor the new URL
 * langCode        : language code
 *
 *****************************************************************************/

require_once('common.php');
require_once('login_valid.php');
require_once('classes/image.php');
require_once('classes/my_files.php');

$getCKEditor        = admFuncVariableIsValid($_GET, 'CKEditor', 'string', null, true, null, true);
$getCKEditorFuncNum = admFuncVariableIsValid($_GET, 'CKEditorFuncNum', 'string', null, true, null, true);
$getlangCode        = admFuncVariableIsValid($_GET, 'langCode', 'string', '', false, null, true);

$message = '';

// checks if the server settings for file_upload are set to ON
if (ini_get('file_uploads') != '1')
{
    $message = $gL10n->get('SYS_SERVER_NO_UPLOAD');
}

// if necessary create the module folders in adm_my_files
if($getCKEditor == 'ann_description')
{
    $folderName = 'announcements';
}
elseif($getCKEditor == 'dat_description')
{
    $folderName = 'dates';
}
elseif($getCKEditor == 'lnk_description')
{
    $folderName = 'weblinks';
}
elseif($getCKEditor == 'mail_body')
{
    $folderName = 'mail';
}
elseif($getCKEditor == 'room_description')
{
    $folderName = 'rooms';
}
elseif($getCKEditor == 'usf_description')
{
    $folderName = 'user_fields';
}

// set path to module folder in adm_my_files
$myFilesProfilePhotos = new MyFiles($folderName);
if($myFilesProfilePhotos->checkSettings())
{
	// upload photo to images folder of module folder
	if($myFilesProfilePhotos->setSubFolder('images'))
	{
		// create a filename with the unix timestamp, 
		// so we have a scheme for the filenames and the risk of duplicates is low
		$localFile = time(). substr($_FILES['upload']['name'], strrpos($_FILES['upload']['name'], '.'));
		$serverUrl = $myFilesProfilePhotos->getServerPath().'/'.$localFile;
		if(file_exists($serverUrl))
		{
			// if file exists than create a random number and append it to the filename
			$serverUrl = $myFilesProfilePhotos->getServerPath().'/'.substr($localFile, 0, strrpos($localFile, '.')).'_'.rand().substr($localFile, strrpos($localFile, '.'));
		}
		$htmlUrl   = $g_root_path.'/adm_program/system/show_image.php?module='.$folderName.'&file='.$localFile;
		move_uploaded_file($_FILES['upload']['tmp_name'], $serverUrl);
	}
	else
	{
		$message = strStripTags($gL10n->get($myFilesProfilePhotos->errorText, $myFilesProfilePhotos->errorPath, '<a href="mailto:'.$gPreferences['email_administrator'].'">', '</a>'));
	}
}
else
{
    $message = strStripTags($$gL10n->get($myFilesProfilePhotos->errorText, $myFilesProfilePhotos->errorPath, '<a href="mailto:'.$gPreferences['email_administrator'].'">', '</a>'));
}

// now call CKEditor function and send photo data
echo '<html><body><script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('.$getCKEditorFuncNum.', "'.$htmlUrl.'","'.$message.'");</script></body></html>';

?>