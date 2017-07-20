<?php

error_reporting(0);

require_once('lib/arf_functions.php');
$db=new SQL;
if($db->connect())
	echo "";
else
    echo "Not connected";

$ID = $_GET['ID'];

$auth=new AuthUser;
$auth->auth();
$username=$auth->getUser();

if(!canCreateForm($username)) {
	print("Sorry, you are not allowed to initiate an ARF. Please contact your Administrative Assistant to create an ARF for you.");
	exit(0);
}

require('includes/form_submit.php');

include_once('../plan_of_study/lib/ajax/ldap.php');  // Use the file in Plan of Study.
if($username != "" && $prepared_by == "") {
	$prepared_by = explode("+", getNameEmailLDAP($username));
	$prepared_by = array_filter($prepared_by);
	$prepared_by = implode(" ", $prepared_by) . " ($username@latech.edu)";
}


if(is_numeric($ID) && $ID > 0) {
	$authPersonnel=$auth->authPersonnel($ID);

	// Allow access to only authorized personnel
	if((!$auth->authorizeView($ID) && !isAdmin($username)) || $rows==0) { // Check if the logged in person is authorized to view the form
		print("Sorry, this form either has been deleted or you do not have enough permission to view this form.");
		exit(0);
	}
}


// If values are passed from the CSV, populate respective fields.
$csv_values=$_POST['csv_values'];
if(trim($csv_values!="")){
	$csv_input=1;
	$csv_values=explode("&&&&", $csv_values);
	$appointment=strtolower($csv_values[0])=="new" ? "1": (strtolower($csv_values[0])=="continuing" ? "2": (strtolower($csv_values[0]=="amended" ? "3": "0")));
	$lname=$csv_values[1];
	$fname=$csv_values[2];
	$mname=$csv_values[3];
	$street=$csv_values[4];
	$city=$csv_values[5];
	$zip=$csv_values[6];
	$date_effective=$csv_values[7];
	$dob=$csv_values[8];
	$ssn=$csv_values[9];
	$sex=strtolower($csv_values[10])=="male" ? "1": (strtolower($csv_values[10])=="female" ? "2": "0");
	$marital_status=strtolower($csv_values[11])=="single" ? "1": (strtolower($csv_values[11])=="married" ? "2": (strtolower($csv_values[11]=="divorced" ? "3": "0")));
	$race=$csv_values[12];
	$req_sal=$csv_values[13];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Appointment Request Form - The Office of Human Resources</title>
<link rel="stylesheet" type="text/css" href="resources/style/arf_style.css">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
<script language="javascript" type="text/javascript" src="resources/js/script.js"></script>
</head>
<body>
<? include('includes/header.php'); ?>
<div id="main_div">
	<div id="header_logo">
        <img src="resources/images/logo.png">
	</div>

	<div id="instructions">
		<strong>INSTRUCTIONS:</strong> Department Head, Dean, or other Budget Unit Head will initiate and retain one copy. Completed original form should then be forwarded to appropriate offices for signature. Official transcripts for new teaching faculty should accompany the original appointment form. This form should be fully processed with complete information prior to the effective date of employment. All new appointments should be fully processed and have Board of Supervisor approval prior to the effective date of employment. (Graduate, Research and Teaching Assistant appointments do not require Board of Supervisor approval.) Forms received after the Monthly Payroll Deadline in the Office of Human Resources will be processed the following month. The Office of Human Resources will forward a final approved copy to appropriate unit(s).
	</div>

	<form name="myform" enctype="multipart/form-data" method="post">
		<div class="darker_row">
			<table cellpadding="3" cellspacing="0" border="0" width="100%" id="appointment_tb">
				<tr>
					<td width="13%"><b>Appointment</b></td>
					<td width="25%">
						<input type="radio" name="appointment" value="1" id="appointment" onclick="showSecondPage()" <?php if(($edit==1 || $csv_input==1) && ($appointment==1)) echo "checked"; ?>><label for="appointment">New</label>&nbsp;
						<input type="radio" name="appointment" value="2" id="continuing" onclick="hideSecondPage()" <?php if(($edit==1 || $csv_input==1) && ($appointment==2)) echo "checked"; ?>><label for="continuing">Continuing</label>&nbsp;
						<input type="radio" name="appointment" value="3" id="amended" onclick="hideSecondPage()" <?php if(($edit==1 || $csv_input==1) && ($appointment==3)) echo "checked"; ?>><label for="amended">Amended</label></td>
					<td width="14%" align="right"><b>College</b></td>
					<td width="18%">
						<select name="college" id="college" onChange="hideVP()" onBlur="autoPopulateSignatures()" style="width:150px">
							<option readonly selected value="" >Select College</option>
							<? if($edit==1) echo getColleges($college); else echo getColleges(0); ?>
						</select>
					</td>
					<td width="8%" align="right"><b>Date</b></td>
					<td width="20%"><input type="text" name="date" placeholder="mm/dd/yyyy" id="datepicker" class="dp"<? if($date=="") echo " value=\"".date("m/d/Y")."\""; else echo " value=\"$date\""; ?>></td>
				</tr>
				<tr>
					<td><b>Appointment for</b></td>
					<td>
						<input type="radio" name="appointment_for" value="1" id="appointment_for" <?php if(($edit==1 || $csv_input==1) && ($appointment_for==1)) echo "checked"; ?> onclick="display_row()" onBlur="autoPopulateSignatures()"><label for="appointment_for">Faculty/Staff</label>&nbsp;
						<input type="radio" name="appointment_for" value="2" id="student" <?php if(($edit==1 || $csv_input==1) && ($appointment_for==2)) echo "checked"; ?> onclick="display_row()" onBlur="autoPopulateSignatures()"><label for="student">Student</label>
					</td>
					<td style="display: none" colspan="2"><b>Graduate/Teaching Assistant Fee Waiver</b></td>
					<td style="display: none" colspan="2">
                    	<?
							$quarter_array=array("Fall", "Winter", "Spring", "Summer");
							for($i=1; $i<=sizeof($quarter_array); $i++){
						?>
                    		<label><input type="checkbox" name="quarter" value="<? echo $i ?>" id="quarter"<?php if(in_array($i, $quarter)) echo "checked"; ?>><? echo $quarter_array[$i-1]; ?></label>
						<?
						}
						?>
					</td>
				</tr>
				<tr id="student_position" <?php if(!(($edit==1 || $csv_input==1) && ($appointment_for==2))) echo 'style="display:none"';?> >
					<td><b>Position</b></td>
					<td colspan="2">
						<select name="position" id="position" style="width:100%">
							<option readonly selected value="">Select Position</option>
							<option <?php if($edit==1 && $position==1) echo "selected";?> value="1" >Graduate/Research Assistant</option>
							<option <?php if($edit==1 && $position==2) echo "selected";?> value="2" >Teaching Assistant (Instructor on Record)</option>
						</select>
					</td>
					<td align="right">Expected Hours/Week</td>
					<td> <input type="text" name="hours_per_week" id="hours_per_week" onblur=checkMinimumWage(); value="<?php if($edit==1) echo $hours_per_week;?>"> </td>
					<!--  <td id="doc_upload" style="display:none" align="right"><b>Upload Credentials</b></td>
					<td id="doc_upload1" style="display:none" colspan="3" id="<? echo $ID."_credentials"; ?>">
						<?
						if(attachmentExists($ID."_credentials")!=0){
							echo "<div class=\"edit_file_display\"><div class=\"edit_file_display_action\"><a href=\"javascript:removeFile('$id_files', '22055', '".$ID."_credentials')\">[ Remove Attachment ]</a></div>Attached File: <a href=\"secure/access/$id_files/22055/$credentials\" target=\"_blank\">$credentials</a></div>";
						}
						else{
							?><input type="file" name="credentials" id="credentials" value="Attach file">
							<?
						}
						?>
					</td> -->
				</tr>
			</table>
		</div>

        <table cellspacing="0" cellpadding="5" border="0" width="100%" id="pi1_tb">
            <tr>
                <td colspan="6"><span class="section_title">Personal Information</span></td>
            </tr>
            <tr>
            	<td><b>Email</b></td>
				<td colspan="5">
					<input type="text" style="width: 99%" name="email" placeholder="xyz@latech.edu" id="email" <? if($edit==1 || $csv_input==1) echo " value=\"$email\""; ?> onBlur="check_previous_forms('email')">
					<div id="email_format_warning_div" style="display: none"></div>
				</td>
            </tr>
            <tr  bgcolor="#F1F1F1">
                <td width="13%"><b>Name</b></td>
                <td width="29%"><input type="text" name="lname" <? if($edit==1 || $csv_input==1) echo "value=\"$lname\" "; ?> id="lname"><div>Last</div></td>
                <td width="29%" colspan="2"><input type="text" <? if($edit==1 || $csv_input==1) echo "value=\"$fname\" "; ?> name="fname" id="fname"><div>First</div></td>
                <td width="29%" colspan="2"><input type="text" <? if($edit==1 || $csv_input==1) echo "value=\"$mname\" "; ?> name="mname" id="mname"><div>Middle</div></td>
            </tr>
            <tr>
                <td><b>Address</b></td>
                <td>
                    <input type="text" name="street" id="street" <? if($edit==1 || $csv_input==1) echo "value=\"$street\" "; ?>>
                    <div>Street Address</div>
                </td>
                <td colspan="2">
                    <input type="text" name="city" id="city" <? if($edit==1 || $csv_input==1) echo "value=\"$city\" "; ?>>
                    <div>City/State</div>
                </td>
                <td colspan="2">
                    <input type="text" name="zip" id="zip" <? if($edit==1 || $csv_input==1) echo "value=\"$zip\" "; ?>>
                    <div>Zip Code</div>
                </td>
            </tr>
            <tr  bgcolor="#F1F1F1">
                <td><b>Date Effective</b></td>
                <td><input type="text" name="date_effective" placeholder="mm/dd/yyyy" id="datepicker1" class="dp"<? if($edit==1 || $csv_input==1) echo " value=\"$date_effective\""; ?>></td>
				<td align="right"><b>SSN</b></td>
                <td colspan="3"><input type="text" name="ssn" id="ssn" <? if($edit==1 || $csv_input==1) echo "value=\"$ssn\" "; ?> onKeyup="validate('ssn')"></td>
            </tr>
            <tr>
                <td><b>Date of Birth</b></td>
                <td>
                    <input type="text" name="dob" placeholder="mm/dd/yyyy" id="datepicker2"<? if($edit==1 || $csv_input==1) echo " value=\"$dob\""; ?>>
                </td>
                <td align="right"><b>Sex</b></td>
                <td>
                    <input type="radio" name="sex" value="1" id="sex" <?php if(($edit==1 || $csv_input==1) && ($sex==1)) echo "checked"; ?>><label for="sex">Male</label>
                    <input type="radio" name="sex" value="2" id="female" <?php if(($edit==1 || $csv_input==1) && ($sex==2)) echo "checked"; ?>><label for="female">Female</label>
                </td>
                <td width="15%" align="right"><b>Marital Status</b></td>
                <td>
                    <select name="marital" id="marital">
                        <option readonly selected value="">Select</option>
                        <option value="1" <?php if(($edit==1 || $csv_input==1) && ($marital==1)) echo "selected"; ?>>Single</option>
                        <option value="2" <?php if(($edit==1 || $csv_input==1) && ($marital==2)) echo "selected"; ?>>Married</option>
                        <option value="3" <?php if(($edit==1 || $csv_input==1) && ($marital==3)) echo "selected"; ?>>Divorced</option>
                    </select>
                </td>
            </tr>
            <tr  bgcolor="#F1F1F1">
                <td><b>Ethnicity</b></td>
                <td>
                	<input type="radio" value="1" name="race" id="hispanic" <?php if(($edit==1 || $csv_input==1) && $race==1) echo "checked"; ?> /><label for="hispanic">Hispanic</label>
					<input type="radio" value="2" name="race" id="nonhispanic" <?php if(($edit==1 || $csv_input==1) && $race==2) echo "checked"; ?> /><label for="nonhispanic">Non-hispanic</label><br>
               </td>
               <td align="right"><b>Race</b></td>
               <td>
                    <select name="raceList" id="raceList">
                        <option readonly selected value="0">Select one</option>
                        <option value="1" <?php if(($edit==1 || $csv_input==1) && ($raceList==1)) echo "selected"; ?>>African American</option>
                        <option value="2" <?php if(($edit==1 || $csv_input==1) && ($raceList==2)) echo "selected"; ?>>American Indian or Alaskan Native</option>
                        <option value="3" <?php if(($edit==1 || $csv_input==1) && ($raceList==3)) echo "selected"; ?>>Asian or Pacific Islander</option>
                        <option value="4" <?php if(($edit==1 || $csv_input==1) && ($raceList==4)) echo "selected"; ?>>White or Caucasian</option>
               		</select>
               </td>
                <td align="right"><b>Nationality</b></td>
                <td align="right">
	                <select name="nationality" id="nationality">
	                	<option readonly selected value="">Select one</option>
	                	<option value='147' <?php if($edit==1 && $nationality==147) echo 'selected';?>>American</option>
						<option value='1' <?php if($edit==1 && $nationality==1) echo 'selected';?>>Afghan</option>
						<option value='2' <?php if($edit==1 && $nationality==2) echo 'selected';?>>Albanian</option>
						<option value='3' <?php if($edit==1 && $nationality==3) echo 'selected';?>>Algerian</option>
						<option value='4' <?php if($edit==1 && $nationality==4) echo 'selected';?>>Andorran</option>
						<option value='5' <?php if($edit==1 && $nationality==5) echo 'selected';?>>Angolan</option>
						<option value='6' <?php if($edit==1 && $nationality==6) echo 'selected';?>>Argentinian</option>
						<option value='7' <?php if($edit==1 && $nationality==7) echo 'selected';?>>Armenian</option>
						<option value='8' <?php if($edit==1 && $nationality==8) echo 'selected';?>>Australian</option>
						<option value='9' <?php if($edit==1 && $nationality==9) echo 'selected';?>>Austrian</option>
						<option value='10' <?php if($edit==1 && $nationality==10) echo 'selected';?>>Azerbaijani</option>
						<option value='11' <?php if($edit==1 && $nationality==11) echo 'selected';?>>Bahamian</option>
						<option value='12' <?php if($edit==1 && $nationality==12) echo 'selected';?>>Bangladeshi</option>
						<option value='13' <?php if($edit==1 && $nationality==13) echo 'selected';?>>Barbadian</option>
						<option value='14' <?php if($edit==1 && $nationality==14) echo 'selected';?>>Belorussian</option>
						<option value='15' <?php if($edit==1 && $nationality==15) echo 'selected';?>>Belgian</option>
						<option value='16' <?php if($edit==1 && $nationality==16) echo 'selected';?>>Beninese</option>
						<option value='17' <?php if($edit==1 && $nationality==17) echo 'selected';?>>Bhutanese</option>
						<option value='18' <?php if($edit==1 && $nationality==18) echo 'selected';?>>Bolivian</option>
						<option value='19' <?php if($edit==1 && $nationality==19) echo 'selected';?>>Bosnian</option>
						<option value='20' <?php if($edit==1 && $nationality==20) echo 'selected';?>>Brazilian</option>
						<option value='21' <?php if($edit==1 && $nationality==21) echo 'selected';?>>Briton</option>
						<option value='22' <?php if($edit==1 && $nationality==22) echo 'selected';?>>Bruneian</option>
						<option value='23' <?php if($edit==1 && $nationality==23) echo 'selected';?>>Bulgarian</option>
						<option value='24' <?php if($edit==1 && $nationality==24) echo 'selected';?>>Burmese</option>
						<option value='25' <?php if($edit==1 && $nationality==25) echo 'selected';?>>Burundian</option>
						<option value='26' <?php if($edit==1 && $nationality==26) echo 'selected';?>>Cambodian</option>
						<option value='27' <?php if($edit==1 && $nationality==27) echo 'selected';?>>Cameroonian</option>
						<option value='28' <?php if($edit==1 && $nationality==28) echo 'selected';?>>Canadian</option>
						<option value='29' <?php if($edit==1 && $nationality==29) echo 'selected';?>>Chadian</option>
						<option value='30' <?php if($edit==1 && $nationality==30) echo 'selected';?>>Chilean</option>
						<option value='31' <?php if($edit==1 && $nationality==31) echo 'selected';?>>Chinese</option>
						<option value='32' <?php if($edit==1 && $nationality==32) echo 'selected';?>>Colombian</option>
						<option value='33' <?php if($edit==1 && $nationality==33) echo 'selected';?>>Congolese</option>
						<option value='34' <?php if($edit==1 && $nationality==34) echo 'selected';?>>Croatian</option>
						<option value='35' <?php if($edit==1 && $nationality==35) echo 'selected';?>>Cuban</option>
						<option value='36' <?php if($edit==1 && $nationality==36) echo 'selected';?>>Cypriot</option>
						<option value='37' <?php if($edit==1 && $nationality==37) echo 'selected';?>>Czech</option>
						<option value='38' <?php if($edit==1 && $nationality==38) echo 'selected';?>>Dane</option>
						<option value='39' <?php if($edit==1 && $nationality==39) echo 'selected';?>>Dominican</option>
						<option value='40' <?php if($edit==1 && $nationality==40) echo 'selected';?>>Ecuadorean</option>
						<option value='41' <?php if($edit==1 && $nationality==41) echo 'selected';?>>Egyptian</option>
						<option value='42' <?php if($edit==1 && $nationality==42) echo 'selected';?>>Salvadorean</option>
						<option value='43' <?php if($edit==1 && $nationality==43) echo 'selected';?>>Englishman</option>
						<option value='44' <?php if($edit==1 && $nationality==44) echo 'selected';?>>Eritrean</option>
						<option value='45' <?php if($edit==1 && $nationality==45) echo 'selected';?>>Estonian</option>
						<option value='46' <?php if($edit==1 && $nationality==46) echo 'selected';?>>Ethiopian</option>
						<option value='47' <?php if($edit==1 && $nationality==47) echo 'selected';?>>Fijian</option>
						<option value='48' <?php if($edit==1 && $nationality==48) echo 'selected';?>>Finn</option>
						<option value='49' <?php if($edit==1 && $nationality==49) echo 'selected';?>>Frenchman</option>
						<option value='50' <?php if($edit==1 && $nationality==50) echo 'selected';?>>Gabonese</option>
						<option value='51' <?php if($edit==1 && $nationality==51) echo 'selected';?>>Gambian</option>
						<option value='52' <?php if($edit==1 && $nationality==52) echo 'selected';?>>Georgian</option>
						<option value='53' <?php if($edit==1 && $nationality==53) echo 'selected';?>>German</option>
						<option value='54' <?php if($edit==1 && $nationality==54) echo 'selected';?>>Ghanaian</option>
						<option value='55' <?php if($edit==1 && $nationality==55) echo 'selected';?>>Greek</option>
						<option value='56' <?php if($edit==1 && $nationality==56) echo 'selected';?>>Grenadian</option>
						<option value='57' <?php if($edit==1 && $nationality==57) echo 'selected';?>>Guatemalan</option>
						<option value='58' <?php if($edit==1 && $nationality==58) echo 'selected';?>>Guinean</option>
						<option value='59' <?php if($edit==1 && $nationality==59) echo 'selected';?>>Guyanese</option>
						<option value='60' <?php if($edit==1 && $nationality==60) echo 'selected';?>>Haitian</option>
						<option value='61' <?php if($edit==1 && $nationality==61) echo 'selected';?>>Dutchman</option>
						<option value='62' <?php if($edit==1 && $nationality==62) echo 'selected';?>>Honduran</option>
						<option value='63' <?php if($edit==1 && $nationality==63) echo 'selected';?>>Hungarian</option>
						<option value='64' <?php if($edit==1 && $nationality==64) echo 'selected';?>>Icelander</option>
						<option value='65' <?php if($edit==1 && $nationality==65) echo 'selected';?>>Indian</option>
						<option value='66' <?php if($edit==1 && $nationality==66) echo 'selected';?>>Indonesian</option>
						<option value='67' <?php if($edit==1 && $nationality==67) echo 'selected';?>>Iranian</option>
						<option value='68' <?php if($edit==1 && $nationality==68) echo 'selected';?>>Iraqi</option>
						<option value='69' <?php if($edit==1 && $nationality==69) echo 'selected';?>>Irishman</option>
						<option value='70' <?php if($edit==1 && $nationality==70) echo 'selected';?>>Israeli</option>
						<option value='71' <?php if($edit==1 && $nationality==71) echo 'selected';?>>Italian</option>
						<option value='72' <?php if($edit==1 && $nationality==72) echo 'selected';?>>Jamaican</option>
						<option value='73' <?php if($edit==1 && $nationality==73) echo 'selected';?>>Japanese</option>
						<option value='74' <?php if($edit==1 && $nationality==74) echo 'selected';?>>Jordanian</option>
						<option value='75' <?php if($edit==1 && $nationality==75) echo 'selected';?>>Kazakh</option>
						<option value='76' <?php if($edit==1 && $nationality==76) echo 'selected';?>>Kenyan</option>
						<option value='77' <?php if($edit==1 && $nationality==77) echo 'selected';?>>Korean</option>
						<option value='78' <?php if($edit==1 && $nationality==78) echo 'selected';?>>Kuwaiti</option>
						<option value='79' <?php if($edit==1 && $nationality==79) echo 'selected';?>>Laotian</option>
						<option value='80' <?php if($edit==1 && $nationality==80) echo 'selected';?>>Latvian</option>
						<option value='81' <?php if($edit==1 && $nationality==81) echo 'selected';?>>Lebanese</option>
						<option value='82' <?php if($edit==1 && $nationality==82) echo 'selected';?>>Liberian</option>
						<option value='83' <?php if($edit==1 && $nationality==83) echo 'selected';?>>Libyan</option>
						<option value='84' <?php if($edit==1 && $nationality==84) echo 'selected';?>>Liechtensteiner</option>
						<option value='85' <?php if($edit==1 && $nationality==85) echo 'selected';?>>Lithuanian</option>
						<option value='86' <?php if($edit==1 && $nationality==86) echo 'selected';?>>Luxembourger</option>
						<option value='87' <?php if($edit==1 && $nationality==87) echo 'selected';?>>Macedonian</option>
						<option value='88' <?php if($edit==1 && $nationality==88) echo 'selected';?>>Madagascan</option>
						<option value='89' <?php if($edit==1 && $nationality==89) echo 'selected';?>>Malawian</option>
						<option value='90' <?php if($edit==1 && $nationality==90) echo 'selected';?>>Malaysian</option>
						<option value='91' <?php if($edit==1 && $nationality==91) echo 'selected';?>>Maldivian</option>
						<option value='92' <?php if($edit==1 && $nationality==92) echo 'selected';?>>Malian</option>
						<option value='93' <?php if($edit==1 && $nationality==93) echo 'selected';?>>Maltese</option>
						<option value='94' <?php if($edit==1 && $nationality==94) echo 'selected';?>>Mauritanian</option>
						<option value='95' <?php if($edit==1 && $nationality==95) echo 'selected';?>>Mauritian</option>
						<option value='96' <?php if($edit==1 && $nationality==96) echo 'selected';?>>Mexican</option>
						<option value='97' <?php if($edit==1 && $nationality==97) echo 'selected';?>>Moldovan</option>
						<option value='98' <?php if($edit==1 && $nationality==98) echo 'selected';?>>Monacan</option>
						<option value='99' <?php if($edit==1 && $nationality==99) echo 'selected';?>>Mongolian</option>
						<option value='100' <?php if($edit==1 && $nationality==100) echo 'selected';?>>Montenegrin</option>
						<option value='101' <?php if($edit==1 && $nationality==101) echo 'selected';?>>Moroccan</option>
						<option value='102' <?php if($edit==1 && $nationality==102) echo 'selected';?>>Mozambican</option>
						<option value='103' <?php if($edit==1 && $nationality==103) echo 'selected';?>>Namibian</option>
						<option value='104' <?php if($edit==1 && $nationality==104) echo 'selected';?>>Nepalese</option>
						<option value='105' <?php if($edit==1 && $nationality==105) echo 'selected';?>>Nicaraguan</option>
						<option value='106' <?php if($edit==1 && $nationality==106) echo 'selected';?>>Nigerien</option>
						<option value='107' <?php if($edit==1 && $nationality==107) echo 'selected';?>>Nigerian</option>
						<option value='108' <?php if($edit==1 && $nationality==108) echo 'selected';?>>Norwegian</option>
						<option value='109' <?php if($edit==1 && $nationality==109) echo 'selected';?>>Pakistani</option>
						<option value='110' <?php if($edit==1 && $nationality==110) echo 'selected';?>>Panamanian</option>
						<option value='111' <?php if($edit==1 && $nationality==111) echo 'selected';?>>Paraguayan</option>
						<option value='112' <?php if($edit==1 && $nationality==112) echo 'selected';?>>Peruvian</option>
						<option value='113' <?php if($edit==1 && $nationality==113) echo 'selected';?>>Filipino</option>
						<option value='114' <?php if($edit==1 && $nationality==114) echo 'selected';?>>Pole</option>
						<option value='115' <?php if($edit==1 && $nationality==115) echo 'selected';?>>Portuguese</option>
						<option value='116' <?php if($edit==1 && $nationality==116) echo 'selected';?>>Qatari</option>
						<option value='117' <?php if($edit==1 && $nationality==117) echo 'selected';?>>Romanian</option>
						<option value='118' <?php if($edit==1 && $nationality==118) echo 'selected';?>>Russian</option>
						<option value='119' <?php if($edit==1 && $nationality==119) echo 'selected';?>>Rwandan</option>
						<option value='120' <?php if($edit==1 && $nationality==120) echo 'selected';?>>Saudi</option>
						<option value='121' <?php if($edit==1 && $nationality==121) echo 'selected';?>>Scot</option>
						<option value='122' <?php if($edit==1 && $nationality==122) echo 'selected';?>>Senegalese</option>
						<option value='123' <?php if($edit==1 && $nationality==123) echo 'selected';?>>Serbian</option>
						<option value='124' <?php if($edit==1 && $nationality==124) echo 'selected';?>>Singaporean</option>
						<option value='125' <?php if($edit==1 && $nationality==125) echo 'selected';?>>Slovak</option>
						<option value='126' <?php if($edit==1 && $nationality==126) echo 'selected';?>>Slovenian</option>
						<option value='127' <?php if($edit==1 && $nationality==127) echo 'selected';?>>Somali</option>
						<option value='128' <?php if($edit==1 && $nationality==128) echo 'selected';?>>Spaniard</option>
						<option value='129' <?php if($edit==1 && $nationality==129) echo 'selected';?>>SriLankan</option>
						<option value='157' <?php if($edit==1 && $nationality==157) echo 'selected'; ?>>St. Lucian</option>
						<option value='130' <?php if($edit==1 && $nationality==130) echo 'selected';?>>Sudanese</option>
						<option value='131' <?php if($edit==1 && $nationality==131) echo 'selected';?>>Surinamese</option>
						<option value='132' <?php if($edit==1 && $nationality==132) echo 'selected';?>>Swazi</option>
						<option value='133' <?php if($edit==1 && $nationality==133) echo 'selected';?>>Swede</option>
						<option value='134' <?php if($edit==1 && $nationality==134) echo 'selected';?>>Swiss</option>
						<option value='135' <?php if($edit==1 && $nationality==135) echo 'selected';?>>Syrian</option>
						<option value='136' <?php if($edit==1 && $nationality==136) echo 'selected';?>>Taiwanese</option>
						<option value='137' <?php if($edit==1 && $nationality==137) echo 'selected';?>>Tadzhik</option>
						<option value='138' <?php if($edit==1 && $nationality==138) echo 'selected';?>>Tanzanian</option>
						<option value='139' <?php if($edit==1 && $nationality==139) echo 'selected';?>>Thai</option>
						<option value='140' <?php if($edit==1 && $nationality==140) echo 'selected';?>>Togolese</option>
						<option value='141' <?php if($edit==1 && $nationality==141) echo 'selected';?>>Trinidadian</option>
						<option value='142' <?php if($edit==1 && $nationality==142) echo 'selected';?>>Tunisian</option>
						<option value='143' <?php if($edit==1 && $nationality==143) echo 'selected';?>>Turk</option>
						<option value='144' <?php if($edit==1 && $nationality==144) echo 'selected';?>>Ugandan</option>
						<option value='145' <?php if($edit==1 && $nationality==145) echo 'selected';?>>Ukrainian</option>
						<option value='146' <?php if($edit==1 && $nationality==146) echo 'selected';?>>British</option>
						<option value='148' <?php if($edit==1 && $nationality==148) echo 'selected';?>>Uruguayan</option>
						<option value='149' <?php if($edit==1 && $nationality==149) echo 'selected';?>>Uzbek</option>
						<option value='150' <?php if($edit==1 && $nationality==150) echo 'selected';?>>Venezuelan</option>
						<option value='151' <?php if($edit==1 && $nationality==151) echo 'selected';?>>Vietnamese</option>
						<option value='152' <?php if($edit==1 && $nationality==152) echo 'selected';?>>Welshman</option>
						<option value='153' <?php if($edit==1 && $nationality==153) echo 'selected';?>>Yemeni</option>
						<option value='154' <?php if($edit==1 && $nationality==154) echo 'selected';?>>Yugoslav</option>
						<option value='155' <?php if($edit==1 && $nationality==155) echo 'selected';?>>Zambian</option>
						<option value='156' <?php if($edit==1 && $nationality==156) echo 'selected';?>>Zimbabwean</option>
	            	</select>
                </td>
            </tr>
            <tr>
                <td><b>Department</b></td>
                <td>
                	<select name="dept">
                    	<option value="0" selected readonly>Select Department</option>
						<?php echo getDepartments($dept); ?>
					</select>
					<br />
					<font style="font-size: 8pt; color: #444; font-style: italic">Please choose the <strong>hiring department</strong>.</font>
                </td>
                <td align="right" colspan="2"><b>Rank or Discipline</b></td>
                <td colspan="2"><input type="text" name="rank" id="rank" <? if($edit==1) echo "value=\"$rank\" "; ?>></td>
            </tr>
            <tr bgcolor="#F1F1F1">
            	<td><b>Work Supervisor</b></td>
            	<td colspan="2">
            		<input type="text" placeholder="xyz@latech.edu" onKeyUp="validateEmail(this.value)" onChange="getNameFromLDAP('supervisor_email')" name="supervisor_email" id="supervisor_email" <? if($edit==1 || $csv_input==1) echo "value=\"$supervisor_email\" "; ?>>
                    <div>Tech E-mail</div>
            	</td>
            	<td colspan="2">
            		<input type="text" name="supervisor_name" id="supervisor_name" <? if($edit==1 || $csv_input==1) echo "value=\"$supervisor_name\" "; ?>>
                    <div>Name</div>
            	</td>
            	<td></td>
            </tr>
        </table>

		<div class="darker_row">
			<table cellspacing="0" cellpadding="5" border="0" width="100%" id="ei1_tb">
				<tr>
					<td colspan="4"><span class="section_title">Educational Attainments</span></td>
				</tr>
				<tr>
					<th colspan="2"width="18%">Degree</th>
					<th width="62%">University</th>
					<th width="20%">Year Earned</th>
				</tr>
				<tr>
					<td align="left"><b>N/A </b><input type="checkbox" id="doctorate"></td>
					<td align="right"><b>Doctorate</b></td>
					<td><input type="text" name="univ_doc" id="univ_doc" <? if($edit==1) echo "value=\"$univ_doc\" "; ?>></td>
					<td><input type="text" name="years_doc" id="years_doc" <? if($edit==1) echo "value=\"$years_doc\" "; ?>></td>
				</tr>
				<tr>
					<td align="left"><b>N/A </b><input type="checkbox" id="master"></td>
					<td align="right"><b>Master</b></td>
					<td><input type="text" name="univ_master" id="univ_master" <? if($edit==1) echo "value=\"$univ_master\" "; ?>></td>
					<td><input type="text" name="years_master" id="years_master" <? if($edit==1) echo "value=\"$years_master\" "; ?>></td>
				</tr>
				<tr>
					<td align="left"><b>N/A </b><input type="checkbox" id="bachelor"></td>
					<td align="right"><b>Bachelor</b></td>
					<td><input type="text" name="univ_bachelor" id="univ_bachelor" <? if($edit==1) echo "value=\"$univ_bachelor\" "; ?>></td>
					<td><input type="text" name="years_bachelor" id="years_bachelor" <? if($edit==1) echo "value=\"$years_bachelor\" "; ?>></td>
				</tr>
			</table>
		</div>

		<script language="javascript">
			function total_experience(type){
				if(type==1){
					var higher_edu=parseFloat(document.getElementById('higher_edu').value);
					higher_edu=(isNaN(higher_edu))? 0: higher_edu;
					var years_tech=parseFloat(document.getElementById('years_tech').value);
					years_tech=(isNaN(years_tech))? 0: years_tech;
					var other=parseFloat(document.getElementById('other').value);
					other=(isNaN(other))? 0: other;
					var total_exp=(higher_edu+years_tech+other);
					document.getElementById('exp').value=total_exp;
				}
				else if(type==2){
					var total_exp_2=parseFloat(document.getElementById('total_exp').value);
					total_exp_2=(isNaN(total_exp_2))? 0: total_exp_2;
					var relevant_exp=parseFloat(document.getElementById('relevant_exp').value);
					relevant_exp=(isNaN(relevant_exp))? 0: relevant_exp;
					var all_other_exp=parseFloat(document.getElementById('all_other_exp').value);
					all_other_exp=(isNaN(all_other_exp))? 0: all_other_exp;
					var other_relevant_exp=parseFloat(document.getElementById('other_relevant_exp').value);
					other_relevant_exp=(isNaN(other_relevant_exp))? 0: other_relevant_exp;
					var grant_total_exp=(total_exp_2+relevant_exp+all_other_exp+other_relevant_exp);
					document.getElementById('grant_total_exp').value=grant_total_exp;
				}
			}
		</script>
        <table style="display: none" cellpadding="5" cellspacing="0" border="0" width="100%" id="exp_tb">
            <tr>
                <td colspan="8"><span class="section_title">Experience</span></td>
            </tr>
            <tr bgcolor="#F1F1F1">
                <td width="12%"><b>Higher Education</b></td>
                <td width="8%"><input type="text" name="higher_edu" id="higher_edu" <? if($edit==1) echo "value=\"$higher_edu\" "; ?> onBlur="total_experience(1)"></td>
                <td width="12%" align="right"><b>Years at Tech</b></td>
                <td width="8%"><input type="text" name="years_tech" size="15" id="years_tech" <? if($edit==1) echo "value=\"$years_tech\" "; ?> onBlur="total_experience(1)"></td>
                <td width="12%" align="right"><b>Other</b></td>
                <td width="8%"><input type="text" name="other" size="15" id="other" <? if($edit==1) echo "value=\"$other\" "; ?> onBlur="total_experience(1)"></td>
                <td width="12%" align="right"><b>Total Experience</b></td>
                <td width="8%"><input type="text" name="exp" size="15" id="exp" <? if($edit==1) echo "value=\"$exp\" "; ?> style="font-weight: bold" tabindex="-1" readonly></td>
            </tr>
        </table>

		<div class="darker_row">
			<table cellpadding="5" cellspacing="0" border="0" width="100%" id="sal1_tb">
				<tr>
					<td colspan="4"><span class="section_title">Salary Details</span></td>
				</tr>
				<tr>
					<td width="50%" colspan="2"><b>Amount To Be Paid:</b></td>
					<td width="50%" colspan="2"><b>Requested Salary (Yearly):</b></td>
				</tr>
				<tr>
					<td><input type="text" name="monthly_amt" id="monthly_amt" <? if($edit==1) echo "value=\"$amt[1]\" "; ?> onBlur="this.value=formatCurrency(this.value);calculateYearlySalary();checkMinimumWage();" >
					<div>Monthly</div></td>
					<td><input type="text" name="base_amt" readonly id="base_amt" <? if($edit==1) echo "value=\"$amt[0]\" "; ?> onBlur="this.value=formatCurrency(this.value)" >
					<div>Base</div></td>
					<td colspan="2" valign="top"><input type="text" readonly name="req_sal" id="req_sal" <? if($edit==1 || $csv_input==1) echo "value=\"$req_sal\" "; ?> onBlur="this.value=formatCurrency(this.value)" ></td>
				</tr>
				<tr>
					<td width="12%"><label for="replaces"><b>Replaces:</b></label></td>
					<td width="38%"><input type="text" name="replaces" id="replaces" <? if($edit==1) echo "value=\"$replaces\" "; ?>></td>
					<td width="25%" align="right"><b>Job Type:</b></td>
					<td width="25%">
                        <input type="radio" name="jobType" value="1" id="part"<?php if(($edit==1) && ($jobType==1)) echo "checked"; ?>><label for="part">Part-Time</label>&emsp;
						<input type="radio" name="jobType" value="2" id="full" <?php if(($edit==1) && ($jobType==2)) echo "checked"; ?>><label for="full">Full-Time</label></td>
                </tr>
                <tr>
                    <td><label for="releasedTime"><b>Released-Time</b></label></td>
                    <td><input type="text" name="rtime" id="rtime" <? if($edit==1) echo "value=\"$rtime\" "; ?>></td>
                    <td align="right"><b>Salary Charged to</b></td>
                    <td><input type="text" name="salaryCharged" id="salaryCharged" <? if($edit==1) echo "value=\"$salaryCharged\" "; ?>></td>
                </tr>
                <tr>
                    <td><b>Salary Basis</b></td>
                    <td colspan="2">
                        <input type="radio" name="salaryBasis" value="1" id="nine" <?php if(($edit==1) && ($salaryBasis==1)) echo "checked"; ?>><label for="nine">9-Months</label>&emsp;
                        <input type="radio" name="salaryBasis" value="2" id="twelve" <?php if(($edit==1) && ($salaryBasis==2)) echo "checked"; ?>><label for="twelve">12-Months</label>&emsp;
                        <input type="radio" name="salaryBasis" value="3" id="quarter" <?php if(($edit==1) && ($salaryBasis==3)) echo "checked"; ?>><label for="quarter">Quarterly</label>&emsp;
                        <input type="radio" name="salaryBasis" value="4" id="other_salary" <?php if(($edit==1) && ($salaryBasis==4)) echo "checked"; ?>><label for="other_salary">Other<sup>*</sup></label>
                    </td>
                    <td><sup>*</sup>If "Other,":
                        <input type="text" name="other_sal" id="other_sal" <? if($edit==1) echo "value=\"$other_sal\" "; ?>>
                    </td>
                </tr>
                <tr>
                    <td><b>Retirement</b></td>
                    <td colspan="2">
                        <input type="radio" name="retirement" value="1" id="ss" <?php if(($edit==1) && ($retirement==1)) echo "checked"; ?>><label for="ss">Social Security</label>&emsp;
                        <input type="radio" name="retirement" value="2" id="teacher" <?php if(($edit==1) && ($retirement==2)) echo "checked"; ?>><label for="teacher">Teachers</label>&emsp;
                        <input type="radio" name="retirement" value="3" id="emp" <?php if(($edit==1) && ($retirement==3)) echo "checked"; ?>><label for="emp">Employees'</label>&emsp;
                        <input type="radio" name="retirement" value="4" id="orp" <?php if(($edit==1) && ($retirement==4)) echo "checked"; ?>><label for="orp">ORP<sup>*</sup></label>
                    </td>
                    <td>
                        <sup>*</sup>If "ORP,":
                        <input type="text" name="orp" size="5" <? if($edit==1) echo "value=\"$orp\" "; ?>>
                    </td>
				</tr>
			</table>
		</div>

<? /* for file attachment - Eliminated for now

	<div class="section_box">
        <table cellspacing="2" cellpadding="2" width="100%" border="0" bgcolor="#DDDDDD">
            <tr>
                <td colspan="1" class="section_title">Attach A File<font style="text-transform: lowercase"></font></td>
            </tr>
            <tr>
                <td width="40%" id="<? echo $ID."_attachment_file"; ?>">
                    <?
                    if(attachmentExists($ID."_attachment_file")!=0){
                        echo "<div class=\"edit_file_display\"><div class=\"edit_file_display_action\"><a href=\"javascript:removeFile('$id_files', '22055', '".$ID."_attachment_file')\">[ Remove Attachment ]</a></div>Attached File: <a href=\"secure/access/$id_files/22055/$attachment_file\" target=\"_blank\">$attachment_file</a></div>";

                    }
                    else{
                        ?><input type="file" name="attachment_file" id="attachment_file">
                        <?
                    }
                    ?>
                </td>
            </tr>
        </table>
	</div>
*/ ?>

		<table style="padding-bottom: 25px;" cellspacing="0" cellpadding="5" border="0" width="100%" class="darker_row" id="major_time_dates_table">
            <tbody>
                <tr>
                    <td colspan="4"><span class="section_title">Time Period</span></td>
                </tr>
                <tr>
                    <td align="right">
                    	From:
                    </td>
                    <td>
                    	<input type="text" onchange="disableDates();checkForWarnings();checkForOverlap();" name="major_time_from" id="major_time_from" <?if($edit==1) echo "value=\"$major_time_from\" "; ?> class="dp">
                    </td>
                    <td align="right">
                    	To:
                    </td>
                    <td>
                    	<input type="text" onchange="checkForWarnings();checkForOverlap();" name="major_time_to" id="major_time_to" <?if($edit==1) echo "value=\"$major_time_to\" "; ?> class="dp">
                    </td>
                </tr>
                <tr>
                	<td colspan="4"><div id="warning_messages">
                		<?php // Displaying pro-rated check boxes
							if($edit==1)
							{
								// checking for same month pro-rated check box
								if($prorated_amounts[2]!="$0.00")
								{
									if($prorated_amounts[2]!=$monthly_sal)
										echo '<input name="major_time_warning_check" id="major_time_warning_check" value="yes" checked type="checkbox">';
									else
										echo '<input name="major_time_warning_check" id="major_time_warning_check" value="yes" type="checkbox">';
									echo '<label id="major_time_warning_check_label" for="major_time_warning_check"> Pro-rate salary for the month (' . getDateFormat($major_time_from) . ')?</label>';
									echo '<p id="major_time_warning">Salary of the month (' . getDateFormat($major_time_from) . ') is: ' . $prorated_amounts[2] . '</p>';
								}
								if($prorated_amounts[0]!="$0.00")
								{
									if($prorated_amounts[0]!=$monthly_sal)
										echo '<input name="major_time_from_warning_check" id="major_time_from_warning_check" value="yes" checked type="checkbox">';
									else
										echo '<input name="major_time_from_warning_check" id="major_time_from_warning_check" value="yes" type="checkbox">';
									echo '<label id="major_time_from_warning_check_label" for="major_time_from_warning_check"> Pro-rate salary for the first month (' . getDateFormat($major_time_from) . ')?</label>';
									echo '<p id="major_time_from_warning">Salary for the first month (' . getDateFormat($major_time_from) . ') is: ' . $prorated_amounts[0] . '</p>';
								}
								if($prorated_amounts[1]!="$0.00")
								{
									if($prorated_amounts[1]!=$monthly_sal)
										echo '<input name="major_time_to_warning_check" id="major_time_to_warning_check" value="yes" checked type="checkbox">';
									else
										echo '<input name="major_time_to_warning_check" id="major_time_to_warning_check" value="yes" type="checkbox">';
									echo '<label id="major_time_to_warning_check_label" for="major_time_to_warning_check"> Pro-rate salary for the last month ('. getDateFormat($major_time_to) . ')?</label>';
									echo '<p id="major_time_to_warning">Salary for the last month (' . getDateFormat($major_time_to) . ') is: ' . $prorated_amounts[1] . '.</p>';
								}
							}
						?>
                	</div></td>
                </tr>
         	</tbody>
        </table>

        <table cellspacing="0" cellpadding="5" border="0" width="100%" id="details_tb">
            <thead>
                <tr>
                    <td colspan="6"><span class="section_title">Source Of Funds</span></td>
                </tr>
                <tr>
                    <th width="15%">Department Codes</th>
                    <th width="20%">Budget Page & Line</th>
                    <th width="16%">Monthly Amount</th>
                    <th width="10%">%</th>
                    <th width="12%">Total Funds</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if($edit!=1){ ?>
                <tr>
                    <td><input type="text" name="major_dc[]" onBlur="checkResearchFund()" id="major_dc_0" onKeyUp="dept_code_format('major_dc_0')" maxlength="14"></td>
                    <td><input type="text" "major_budget[]" id="major_budget"></td>
                    <td><input type="text" name="major_amt[]" id="major_amt" onBlur="this.value=formatCurrency(this.value);updateTotalMonthlyAmount();updatePercentage(this);"></td>
                    <td><input type="text" readonly name="major_perc[]" id="major_perc"></td>
                    <td><input type="text" readonly name="major_fund[]" id="major_fund"></td>
                </tr>
            <?php
            }
            if($edit==1){
                for($count=0; $count<sizeof($major_dcs); $count++){
            ?>
                <tr>
                    <td><input type="text" name="major_dc[]" onBlur="checkResearchFund()" id="major_dc_<? echo $count ?>" <? echo "value=\"$major_dcs[$count]\" "; ?> onKeyUp="dept_code_format('major_dc_<? echo $count ?>')" maxlength="14"></td>
                    <td><input type="text" name="major_budget[]" id="major_budget_<? echo $count ?>" <? echo "value=\"$major_budgets[$count]\" "; ?>></td>
                    <td><input type="text" name="major_amt[]" id="major_amt_<? echo $count ?>" <? echo "value=\"$major_amts[$count]\" "; ?> onBlur="this.value=formatCurrency(this.value);updateTotalMonthlyAmount();updatePercentage(this);"></td>
                    <td><input type="text" readonly name="major_perc[]" id="major_perc_<? echo $count ?>" <? echo "value=\"$major_percs[$count]\" "; ?>></td>
                    <td><input type="text" readonly name="major_fund[]" id="major_fund_<? echo $count ?>" <? echo "value=\"$major_funds[$count]\" "; ?>></td>
                </tr>
                <?php
                }
			} ?>
            </tbody>
        </table>

        <div class="addmore_btn">
            <table style="width: 100%" cellspacing="0" cellpadding="0" border="0">
                <tr bgcolor="#CCCCCC" style="font-weight: bold">
                    <td width="40%">
                        <input type="image" src="resources/images/btn_addrow.png" onclick="javascript: addRow('details_tb','5'); return false">
                        <input type="image" src="resources/images/btn_removerow.png" style="<? if(sizeof($major_dns)<=1){ echo "display: none"; } ?>" id="remove_table_5" onclick="javascript: removeRow('details_tb'); return false">
                    </td>
                    <td width="9%">Total: </td>
                    <td width="22%" id="total_monthly_amount">$0.00</td>
                    <td width="14%" id="total_percent">0.00%</td>
                    <td id="total_fund">$0.00</td>
                    <td>
                </tr>
            </table>
		</div>

		<div>
			<table cellspacing="0" cellpadding="5" width="100%" border="0">
				<tr bgcolor="#AAA">
					<th align="left">Comments</th>
				</tr>
				<tr>
					<td><textarea name="major_comments"><? if($edit==1) echo $major_comments; ?></textarea></td>
				</tr>
			</table>
		</div>

        <div id="page_2_sections"<? if($appointment==2 || $appointment==3) echo " style=\"display: none !important\""; ?>">
            <div style="background: #39F; padding: 5px; ; text-shadow: 1px 1px 2px #019; color: #FFF; margin-top: 10px">On the following sections (A, B, and C), please list in reverse order stating the most recent experience first.</div>

            <div class="darker_row">
                <table cellspacing="0" cellpadding="5" border="0" width="100%" id="higher_edu_tb">
                    <thead>
                        <tr>
                            <td colspan="4"><span class="section_title">A. Higher Education Experience</span></td>
                        </tr>
                        <tr>
                            <th width="30%">University/Employer</th>
                            <th width="30%">Position of Service</th>
                            <th width="20%">From Date</th>
                            <th width="20%">To Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if($edit!=1){ ?>
                        <tr>
                            <td><input type="text" name="a_univ_exp[]" size="5" id="a_univ_exp_0"></td>
                            <td><input type="text" name="a_position_exp[]" size="5" id="a_position_exp_0"></td>
                            <td><input type="text" name="a_from_exp[]" size="5" id="a_from_exp_0" class="dp"></td>
                            <td><input type="text" name="a_to_exp[]" size="5" id="a_to_exp_0" class="dp"></td>
                        </tr>
                        <?php
                        }
                        if($edit==1){
                            for($count=0;$count<sizeof($a_univ_exps);$count++){
                        ?>
                        <tr>
                            <td><input type="text" name="a_univ_exp[]" id="a_univ_exp_<? echo $count ?>" <? echo "value=\"$a_univ_exps[$count]\" "; ?>></td>
                            <td><input type="text" name="a_position_exp[]" id="a_position_exp_<? echo $count ?>" <? echo "value=\"$a_position_exps[$count]\" "; ?>></td>
                            <td><input type="text" name="a_from_exp[]" id="a_from_exp_<? echo $count ?>" <? echo "value=\"$a_from_exps[$count]\" "; ?> class="dp"></td>
                            <td><input type="text" name="a_to_exp[]" id="a_to_exp_<? echo $count ?>" <? echo "value=\"$a_to_exps[$count]\" "; ?> class="dp"></td>
                        </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>

                <div class="addmore_btn">
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tr>
                         <td width="65%">
                                <input type="image" src="resources/images/btn_addrow.png" id="add_table_1" onclick="javascript: addRow('higher_edu_tb','1'); return false">
                                <input type="image" src="resources/images/btn_removerow.png" style="<? if(sizeof($a_univ_exps)<=1){ echo "display: none"; } ?>" id="remove_table_1" onclick="javascript: removeRow('higher_edu_tb'); return false">
                        </td>
                        </tr>
                    </table>
                </div>
            </div>

            <table cellspacing="0" cellpadding="5" border="0" width="100%" id="other_edu_tb">
                <thead>
                    <tr>
                        <td colspan="4"><span class="section_title">B. Other Education Experience</span></td>
                    </tr>
                    <tr>
                        <th width="30%">University/Employer</th>
                        <th width="30%">Position & Nature of Service</th>
                        <th width="20%">From Date</th>
                        <th width="20%">To Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if($edit!=1){ ?>
                    <tr>
                        <td><input type="text" name="b_exp[]" size="5" id="b_exp_0"></td>
                        <td><input type="text" name="b_position_exp[]" size="5" id="b_position_exp_0"></td>
                        <td><input type="text" name="b_from_exp[]" size="5" id="b_from_exp_0" class="dp"></td>
                        <td><input type="text" name="b_to_exp[]" size="5" id="b_to_exp_0" class="dp"></td>
                    </tr>
                <?php
                }
                else if($edit==1){
                    for($count=0;$count<sizeof($b_exps);$count++){
                ?>
                    <tr>
                        <td><input type="text" name="b_exp[]" id="b_exp_<? echo $count ?>" <? echo "value=\"$b_exps[$count]\" "; ?>></td>
                        <td><input type="text" name="b_position_exp[]" id="b_position_exp_<? echo $count ?>" <? echo "value=\"$b_position_exps[$count]\" "; ?>></td>
                        <td><input type="text" name="b_from_exp[]" id="b_from_exp_<? echo $count ?>" <? echo "value=\"$b_from_exps[$count]\" "; ?> class="dp"></td>
                        <td><input type="text" name="b_to_exp[]" id="b_to_exp_<? echo $count ?>" <? echo "value=\"$b_to_exps[$count]\" "; ?> class="dp"></td>
                    </tr>
                <?php
                    }
                }
                ?>
                </tbody>
            </table>

            <div class="addmore_btn">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td width="65%">
                            <input type="image" src="resources/images/btn_addrow.png" id="add_table_2" onclick="javascript: addRow('other_edu_tb','2'); return false">
                            <input type="image" src="resources/images/btn_removerow.png" style="<? if(sizeof($b_exps)<=1){ echo "display: none"; } ?>" id="remove_table_2" onclick="javascript: removeRow('other_edu_tb'); return false">
                        </td>
                    </tr>
                </table>
            </div>

            <div class="darker_row">
                <table cellspacing="0" cellpadding="5" border="0" width="100%" id="other_tb">
                    <thead>
                        <tr>
                            <td colspan="4"><span class="section_title">C. Other Experience</span></td>
                        </tr>
                        <tr>
                            <th width="30%">Employer</th>
                            <th width="30%">Position &amp; Nature of Service</th>
                            <th width="20%">From Date</th>
                            <th width="20%">To Date</th>
                        </tr>
                        <tr>
                            <td colspan="4"><b>1. Since Baccalaureate Degree</b></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if($edit!=1){ ?>
                        <tr>
                            <td><input type="text" name="c1_univ_exp[]" size="5" id="c1_univ_exp_0"></td>
                            <td><input type="text" name="c1_position_exp[]" size="5" id="c1_position_exp_0"></td>
                            <td><input type="text" name="c1_from_exp[]" size="5" id="c1_from_exp_0" class="dp"></td>
                            <td><input type="text" name="c1_to_exp[]" size="5" id="c1_to_exp_0" class="dp"></td>
                        </tr>
                    <?php }
                    else if($edit==1){
                        for($count=0;$count<sizeof($c1_univ_exps);$count++){
                    ?>
                        <tr>
                            <td><input type="text" name="c1_univ_exp[]" id="c1_univ_exp_<? echo $count ?>" <? echo "value=\"$c1_univ_exps[$count]\" "; ?>></td>
                            <td><input type="text" name="c1_position_exp[]" id="c1_position_exp_<? echo $count ?>" <? echo "value=\"$c1_position_exps[$count]\" "; ?>></td>
                            <td><input type="text" name="c1_from_exp[]" id="c1_from_exp_<? echo $count ?>" <? echo "value=\"$c1_from_exps[$count]\" "; ?> class="dp"></td>
                            <td><input type="text" name="c1_to_exp[]" id="c1_to_exp_<? echo $count ?>" <? echo "value=\"$c1_to_exps[$count]\" "; ?> class="dp"></td>
                        </tr>
                    <?php } }?>
                    </tbody>
                </table>

                <div class="addmore_btn">
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td width="65%">
                            <input type="image" src="resources/images/btn_addrow.png" id="add_table_3" onclick="javascript: addRow('other_tb','3'); return false">
                            <input type="image" src="resources/images/btn_removerow.png"  style="<? if(sizeof($c1_univ_exps)<=1){ echo "display: none"; } ?>" id="remove_table_3" onclick="javascript: removeRow('other_tb'); return false">
                            </td>
                        </tr>
                    </table>
                </div>

                <table cellspacing="0" cellpadding="5" border="0" width="100%" id="other_tb1">
                    <thead>
                        <tr>
                            <td colspan="4"><b>2. Prior to Baccalaureate</b></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if($edit!=1){ ?>
                        <tr>
                            <td width="30%"><input type="text" name="c2_univ_exp[]" size="5" id="c2_univ_exp_0"></td>
                            <td width="30%"><input type="text" name="c2_position_exp[]" size="5" id="c2_position_exp_0"></td>
                            <td width="20%"><input type="text" name="c2_from_exp[]" size="5" id="c2_from_exp_0" class="dp"></td>
                            <td width="20%"><input type="text" name="c2_to_exp[]" size="5" id="c2_to_exp_0" class="dp"></td>
                        </tr>

                    <?php
                    }
                    else if($edit==1){
                        for($count=0;$count<sizeof($c2_univ_exps);$count++){
                    ?>
                        <tr>
                            <td width="30%"><input type="text" name="c2_univ_exp[]" id="c2_univ_exp_<? echo $count ?>" <? echo "value=\"$c2_univ_exps[$count]\" "; ?>></td>
                            <td width="30%"><input type="text" name="c2_position_exp[]" id="c2_position_exp_<? echo $count ?>" <? echo "value=\"$c2_position_exps[$count]\" "; ?>></td>
                            <td width="20%"><input type="text" name="c2_from_exp[]" id="c2_from_exp_<? echo $count ?>" <? echo "value=\"$c2_from_exps[$count]\" "; ?> class="dp"></td>
                            <td width="20%"><input type="text" name="c2_to_exp[]" id="c2_to_exp_<? echo $count ?>" <? echo "value=\"$c2_to_exps[$count]\" "; ?> class="dp"></td>
                        </tr>
                    <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>

                <div class="addmore_btn">
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tr>
                         <td width="65%">
                                <input type="image" src="resources/images/btn_addrow.png" id="add_table_4" onclick="javascript: addRow('other_tb1','4'); return false">
                                <input type="image" src="resources/images/btn_removerow.png" style="<? if(sizeof($c2_univ_exps)<=1){ echo "display: none"; } ?>" id="remove_table_4" onclick="javascript: removeRow('other_tb1'); return false">
                         </td>
                        </tr>
                    </table>
                </div>
            </div>

            <table cellspacing="0" cellpadding="5" border="0" id="summary_eval_tb" width="100%">
                <tr>
                    <td colspan="4"><span class="section_title">Summary Evaluation</span></td>
                </tr>
                <tr bgcolor="#F1F1F1">
                    <th width="20%">Total Years of Experience in Higher Education</th>
                    <th width="20%">Of These, Years Applicable to Present Teaching Field</th>
                    <th width="20%">All Other Experiences</b></th>
                    <th width="20%">Other Experience Applicable to Present Work</th>
                    <th width="20%">TOTAL</th>
				</tr>
				<tr>
                    <td><input type="text" name="total_exp" id="total_exp"<? if($edit==1) echo " value=\"$total_exp\" "; ?> onBlur="total_experience(2);"></td>
                    <td><input type="text" name="relevant_exp" id="relevant_exp"<? if($edit==1) echo " value=\"$relevant_exp\" "; ?> onBlur="total_experience(2);"></td>
                    <td><input type="text" name="all_other_exp" id="all_other_exp"<? if($edit==1) echo " value=\"$all_other_exp\" "; ?> onBlur="total_experience(2);"></td>
                    <td><input type="text" name="other_relevant_exp" id="other_relevant_exp"<? if($edit==1) echo " value=\"$other_relevant_exp\" "; ?> onBlur="total_experience(2);"></td>
                    <td><input type="text" name="grant_total_exp" id="grant_total_exp" style="font-weight: bold"<? if($edit==1) echo "value=\"$total_exp_grand\""; ?>/></td>
                </tr>
            </table>

    	</div><!-- Page 2 ends -->


		<div class="darker_row">
			<table cellspacing="0" cellpadding="5" border="0" width="100%">
				<tr>
					<td colspan="3"><span class="section_title">Prepared By</span></td>
				</tr>
				<tr>
					<td><input type="text" name="prepared_by" readonly id="prepared_by" value="<? echo $prepared_by ?>" /></td>
				</tr>
			</table>
		</div>

            <div class="darker_row">
                <table cellspacing="0" cellpadding="5" border="0" id="req_sign_tb" width="100%">
                    <tr>
                        <td colspan="3"><span class="section_title">Requested Signatures</span></td>
                    </tr>
                    <tr class="grants_fund_only"<? if($proj_director[0]=="") { echo  "style=\"display: none\""; } ?>>
                        <td width="22%" valign="top"><b>Project Director (Grants only)</b></td>
                        <td width="39%">
                            <input type="text" id="proj_director_name" name="proj_director_name" size="10" <? if($edit==1) echo "value=\"$proj_director[0]\" "; ?>>
                            <div>Project Director's Name</div>
                        </td>
                        <td width="39%">
                            <input type="text" id="proj_director_email" name="proj_director_email" size="10" <? if($edit==1) echo "value=\"$proj_director[1]\" "; ?>>
                            <div>Project Director's Tech Email</div>
                        </td>
                    </tr>
                    <tr class="grants_fund_only"<? if($budget_verification == "") { echo "style=\"display: none\""; } ?>>
                        <td width="22%" valign="top"><b>Budget Verification (If needed)</b></td>
                    	<td colspan="2">
                    		<select name="budget_verification" id="budget_verification" class="officials_select">
	                            <option readonly selected value="">Budget Verification</option>
	                            <? echo getIndividuals("is_budget_verification='1' AND dept LIKE '%Dean%'", "$budget_verification"); ?>
                            </select>
                    	</td>
                    </tr>
                    <tr>
                        <td width="22%"><b>Associate Dean/Dept. Head</b></td>
                        <td colspan="2" width="78%">
                            <select name="dept_head" id="dept_head" class="officials_select">
                            <option readonly selected value="">Associate Dean/Department Head</option>
                            <? echo getIndividuals("is_department_head='1'", "$dept_head"); ?>
                            <? echo getIndividuals("is_center_director='1'", "$dept_head"); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Dean</b></td>
                        <td colspan="2">
                            <select name="dean" id="dean" class="officials_select">
                            <option readonly selected value="">Dean</option>
                            <? echo getIndividuals("is_dean_of_college='1'", "$dean"); ?>
                            </select>
                        </td>
                    </tr>
                    <tr id="vice_president_row">
                        <td><b>Vice President</b></td>
                        <td colspan="2">
                            <select name="vice_president" id="vice_president" class="officials_select">
                            <option readonly selected value="">Vice President</option>
                            <? echo getIndividuals("is_vice_president='1'", "$vice_president"); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 10px">
				<input type="submit" name="save" style="padding-left: 10px; padding-right: 10px; cursor: pointer; height: 30px; text-transform: uppercase" value="SAVE AS DRAFT" onclick="save_message()">&nbsp;
                <input type="submit" name="preview" style="padding-left: 10px; padding-right: 10px; cursor: pointer; height: 30px; text-transform: uppercase"  value="<?php if($ID!=""){ echo "EDIT &amp; PREVIEW"; } else{ echo "PREVIEW"; } ?>" onclick="return validateForm()">
            </div>
        </form>

	<script src="resources/js/jquerycal/external/jquery/jquery.js"></script>
	<script src="resources/js/jquerycal/jquery-ui.js"></script>

	<script language="javascript">
		// Based on the selection of 'college' and 'appointment_for', populate respective signatures;
		function autoPopulateSignatures() {
			var college = $('#college').val();
			var student = $('#student').is(':checked');

			// Only select appropriate individuals, then disable all other people from the list.
			if(college == 4 && student == true) {
				$('#dept_head').val('5');
				$('#dept_head option[value != "5"]').attr('disabled', 'true');

				$('#dean').val('28');
				$('#dean option[value != "28"]').attr('disabled', 'true');
			}
			else {
				$('#dept_head').val('');
				$('#dept_head option').removeAttr('disabled');

				$('#dean').val('');
				$('#dean option').removeAttr('disabled');
			}
		}

		// Check if the fund is coming from research Grant.  If yes, there are additional fields to complete.
		function checkResearchFund() {
			var department_codes = document.getElementsByName('major_dc[]');
			for(var i = 0; i < department_codes.length; i++) {
				var department_codes_array = department_codes[i].value.split('-');

// 				if(department_codes_array[0] == 32 && (parseInt(department_codes_array[2].charAt(0)) >= 4 && parseInt(department_codes_array[2].charAt(0)) <= 6)) {

				if(department_codes_array[0] == 32) {
					$(".grants_fund_only").removeAttr('style');
					break;
				}
				else {
					$(".grants_fund_only").attr('style', 'display: none');
					$("#proj_director_name").val('');
					$("#proj_director_email").val('');
				}
			}
		}

		/* Validates fields based on 'field' parameter
	function validate(field){
		var err_msg = "";

		if(field == "ssn"){
			var ssn = $('#'+field).val();

			if(ssn.trim()!=""){
				var is_numeric = /^\d+$/.test(ssn);

				if(is_numeric && ssn.length==9){
					ssn = ssn.substring(0, 3) + "-" + ssn.substring(3, 5) + "-" + ssn.substring(5, ssn.length);
					$('#'+field).val(ssn);
				}
				else if(!is_numeric && ssn.length==11){
					ssn=ssn.split('-');
					if(!(/^\d+$/.test(ssn[0])) || !(/^\d+$/.test(ssn[1])) || !(/^\d+$/.test(ssn[2])))
						err_msg = "Please enter a valid Social Security Number.";
				}
				else
					err_msg = "Please enter a valid Social Security Number.";
			}
		}

		if(err_msg != ""){
			$('#'+field).val('');
			alert(err_msg);
			setTimeout(function() { // Firefox doesn't just focus back into prev field.
				$('#'+field).focus(); $('#'+field).select()
			}, 5);
		}
	}
	*/

	// Display date picker on Date Fields
	$( ".dp" ).datepicker({
		inline: true
	});

	// Fetch information from LDAP, and populate the Name field for supervisor.
	function getNameFromLDAP(field){
		var email = $('#'+field).val();
		var mailformat= /^\w+([\.-]?\w+)*@([\.-]?\w+)*.([\.-]?\w+)*/;
		// var mailformat= /^\w+([\.-]?\w+)*@latech.edu$/;
		// var mailformat = /^(([^<>()[]\.,;:s@"]+(.[^<>()[]\.,;:s@"]+)*)|(".+"))@(([[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}])|(([a-zA-Z-0-9]+.)+[a-zA-Z]{2,}))$/

		var field_name = field.replace("email", "name");

		if(email != ''){
			if(!mailformat.test(email)) {
				$('#'+field).val(''); // Emptying invalid field.
				alert('Please enter a valid e-mail address.');
				setTimeout(function() {
					$('#'+field).focus(); $('#'+field).select()
				}, 5);
			}
			else {
				$('#'+field_name).css({'background':'url("../plan_of_study/resources/images/ico_loading.gif") 5px center no-repeat', 'background-color':'#FFF'});
				$.ajax({
				type: "POST",
				url: "../plan_of_study/lib/ajax/ldap.php",
				data: {ldap_email: email},

				success: function(data) {
					$('#'+field_name).css({'background':''});

					var data = data.replace(/\+/g, ' ');
					data = data.replace('  ', ' ').trim();

					if(data == ""){
						$('#'+field).val('');// Emptying invalid field.
						$('#'+field_name).val('');
						alert('No valid name was retrieved for the e-mail address you entered. Please re-try it with a correct Tech e-mail address.');
						setTimeout(function() { // Firefox doesn't just focus back into prev field.
							$('#'+field).focus(); $('#'+field).select()
						}, 5);
					}
					else{
						if(data.indexOf("||") > 0) {
							var data_array = data.split("||");
							$('#'+field_name).val(data_array[0]);			// Replaces '+' from full name retrieved.
							field_name = field.replace("name", "email");
							$('#' + field_name).val(data_array[1]);
						}
						else
							$('#'+field_name).val(data);			// Replaces '+' from full name retrieved.

					}
				}
				});
			}
		}
		else{ // Empty the corresponding field when blurred on empty e-mail address.
			$('#'+field_name).val('');
		}
	}
	</script>
</div> <!-- End of main div -->

<? include('includes/footer.php'); ?>
