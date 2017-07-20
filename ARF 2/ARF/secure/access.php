<?php
error_reporting(0);

include('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();
$username=$auth->getUser();

$db=new SQL;
$db->connect();

$ID=$_GET['id']/254;

if(!$auth->authorizeView($ID) && !isAdmin($username)){ // Check if the logged in person is authorized to view the form
	print("Sorry, you are not authorized to access this file.");
	exit(0);
}

$file_name=$_GET['filename'];
$field=$_GET['field'];

switch($field){
	case 22055:
		$field_name="_credentials";
		break;
}

if($file_name!=""){
	$ext=pathinfo($file_name, PATHINFO_EXTENSION);
	$file_path="../Uploaded_Files/".$ID.$field_name.".".$ext;
}

header('Content-Type: '.get_mimetype("$file_path"));
$file=readfile("$file_path");

return $file;

function get_mimetype($filepath) {
    if(!preg_match('/\.[^\/\\\\]+$/',$filepath)) {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
    }
    switch(strtolower(preg_replace('/^.*\./','',$filepath))) {
        // START MS Office 2007 Docs
        case 'docx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        case 'docm':
            return 'application/vnd.ms-word.document.macroEnabled.12';
        case 'dotx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
        case 'dotm':
            return 'application/vnd.ms-word.template.macroEnabled.12';
        case 'xlsx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        case 'xlsm':
            return 'application/vnd.ms-excel.sheet.macroEnabled.12';
        case 'xltx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
        case 'xltm':
            return 'application/vnd.ms-excel.template.macroEnabled.12';
        case 'xlsb':
            return 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
        case 'xlam':
            return 'application/vnd.ms-excel.addin.macroEnabled.12';
        case 'pptx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        case 'pptm':
            return 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
        case 'ppsx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
        case 'ppsm':
            return 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
        case 'potx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.template';
        case 'potm':
            return 'application/vnd.ms-powerpoint.template.macroEnabled.12';
        case 'ppam':
            return 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
        case 'sldx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.slide';
        case 'sldm':
            return 'application/vnd.ms-powerpoint.slide.macroEnabled.12';
        case 'one':
            return 'application/msonenote';
        case 'onetoc2':
            return 'application/msonenote';
        case 'onetmp':
            return 'application/msonenote';
        case 'onepkg':
            return 'application/msonenote';
        case 'thmx':
            return 'application/vnd.ms-officetheme';
            //END MS Office 2007 Docs

    }
    return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
}
?>