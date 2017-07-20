<?
error_reporting(0);

include('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();
$username=$auth->getUser();

$ID=$_POST['ID'];
$msg=addslashes($_POST['msg']);

$db=new SQL;
$db->connect();

if(strlen(trim($msg))==0){
	echo "Error Occured";
	return 0;
}
else{
	$query="SELECT `remarks_comments` FROM `appointment_request_form` WHERE `id`='$ID'";

	$replace=array("\n\n", "[[");
	$replace_with=array("</em></p><p>", "<br><em style=\"font-size: 8pt; color: #444\">&emsp;&#8212; ");

	$result=mysql_query($query);
	$rows=mysql_num_rows($result);

	if($rows<1){
		echo "Error Occured";
		return 0;
	}
	else{
		$msg_db=addslashes(mysql_result($result, 0, 'remarks_comments'));
		$msg=$msg."\n[[".$username." | ".date("M j, Y H:i:s", time())."\n\n".$msg_db;
		$query="UPDATE `appointment_request_form` SET `remarks_comments`='$msg' WHERE id='$ID'";
		if(mysql_query($query))
			echo urldecode(str_replace($replace, $replace_with, stripslashes($msg)));
		else{
			echo "Error Occured";
			return 0;
		}
	}
}
?>