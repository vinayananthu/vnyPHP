<?php 
error_reporting(0);
include_once 'lib/arf_functions.php';

$fileName = "ARF report.csv";
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename='" . $fileName . "'");
$out = fopen("php://output", 'w');

// Connecting to the DB
$db=new SQL;
$db->connect();

$enc=new Crypto; // For decrypting
$auth=new AuthUser;


$sql_query_forms = "SELECT * FROM `appointment_request_form`";
$result = mysql_query($sql_query_forms);
$numofRows = mysql_num_rows($result);
$avg_days = 0;
$total_days = 0;
$forms_count = 0;
fputcsv($out, Array("Form ID","First Name","Middle Name","Last Name","Form Initiated Date","Form Completed Date","Days To Complete Form"),",");
for($i=0;$i<$numofRows;$i++)
{
	$initiated_date = new DateTime(mysql_result($result, $i,"timestamp"));
	$initiated_date = "" . $initiated_date->format("Y-m-d");
	$initiated_date = strtotime($initiated_date);
	$form_id = mysql_result($result, $i,"id");
	// Getting Grad school signature from signatures table
	$sql_query_form_signatures = "SELECT `human_resources` FROM `appointment_request_signatures` WHERE `form_id`='$form_id'";
	$signature = mysql_result(mysql_query($sql_query_form_signatures), 0,"human_resources");
	// checking if signature exists
	if(sizeof(explode("||.||", $signature))>1)
	{
		// Form details
		$student_first_name = mysql_result($result, $i,"fname");
		$student_middle_name = mysql_result($result, $i,"mname");
		$student_last_name = mysql_result($result, $i,"lname");
		$student_email = mysql_result($result, $i,"email");
		
		
		$forms_count++;
		$signature = explode("||.||", $signature)[1];
		// Decrypting the signature and extracting the date
		$authEncData = $auth->getOrigNameAndEmail($form_id, 'human_resources');
		$decrypted = stripslashes($enc->decrypt(urldecode($signature), md5($authEncData)));
		$completion_date=explode("+-__-+", $decrypted)[2];
		// Calculating the difference days (creating date - last signed date)
		$days_to_complete = $completion_date - $initiated_date;
		$completion_date = date("Y-m-d",$completion_date);
		$days_to_complete = ceil($days_to_complete/ (60*60*24));
		$total_days = $total_days + $days_to_complete;
		fputcsv($out, Array($form_id,$student_first_name,$student_middle_name,$student_last_name,date("Y-m-d",$initiated_date),$completion_date,$days_to_complete),",");
	}
}
fputcsv($out, Array(''),",");
fputcsv($out, Array("Average number of days to comeplte the form: ",ceil($total_days/$forms_count)),",");
fclose($out);
?>