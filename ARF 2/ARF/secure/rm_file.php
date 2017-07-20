<?
error_reporting(0);

include('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();
$username=$auth->getUser();

$db=new SQL;
$db->connect();

$ID=$_GET['fName']/254;

if(!$auth->authorizeView($ID) && !isAdmin($username)){ // Check if the logged in person is authorized to view the form
	print("Sorry, you are not authorized to access this file.");
	exit(0);
}

$field=$_GET['field'];

switch($field){
	case 22055:
		$field="credentials";
		$file_name=$ID."_".$field;
		$inner_html="<input type=\"file\" name=\"credentials\" id=\"credentials\" value=\"Attach file\">";
		break;
}

if($file_name!="" && $field!=""){

	$location=$_SERVER['DOCUMENT_ROOT']."/ARF_new/Uploaded_Files/";
	$files=scandir("$location");
	foreach($files as $val){
		$file=pathinfo($val);
		if(trim($file_name)==trim($file['filename'])){
			$query="UPDATE `appointment_request_form` SET `$field`='' WHERE id='$ID'";
			if(mysql_query($query)){
				if(unlink($location.$file['filename'].".".$file['extension']))
					echo $inner_html;
			}
		}
	}
}
?>