<?php
error_reporting(0);
require_once('lib/arf_functions.php');
$db=new SQL;
$db->connect();
	
$ID=$_GET['ID'];

$auth=new AuthUser;
$auth->auth();
$username=$auth->getUser();

include('includes/form_submit.php');

$authPersonnel=$auth->authPersonnel($ID);

// Allow access to only authorized personnel
if((!$auth->authorizeView($ID) && !isAdmin($username)) || $rows==0) { // Check if the logged in person is authorized to view the form
	print("Sorry, this form either has been deleted or you do not have enough permission to view this form.");
	exit(0);
}


// For encrypting and decrypting purposes
$enc=new Crypto;

// Initialize form and send for initial signatures
if($_GET['action']=='init' && substr_count($_SERVER['HTTP_REFERER'], "preview.php")>0) {
	if(!is_numeric($ID)) {
		$result = mysql_query("SELECT `id` FROM `appointment_request_form` WHERE `session_id` = '$ID'");
		$rows = mysql_num_rows($result);
		$i = 0;

		// Loop through each of the forms belonging to the same session ID
		while($i < $rows) {
			$initiated = 1;
			$ID = mysql_result($result, $i, 'id');
			checkFormStatusAndSendNotifications($ID, "initiator");
			changeFormStatus($ID, '1');
		}

		header("arf.php?ID=$ID");
	}
	else {
		$initiated = 1;
		checkFormStatusAndSendNotifications($ID, "initiator");
		changeFormStatus($ID, '1');
	}
}


//cancelling a form for a particular duration
$all_date_array = array();
$cancel_date_array = array();  // red
$invalid_date_array = array(); //yellow 

if(isset($_POST['cancel'])){
	$cancel_time_from = $_POST['cancel_time_from'];
	$cancel_time_to = $major_time_to;
	$cancel_query = "UPDATE `appointment_request_form` SET 
								`cancel_time_from`='$cancel_time_from',
								`cancel_time_to`='$cancel_time_to'
					WHERE id='$ID'";
	mysql_query($cancel_query);
	array_push($all_date_array, $cancel_time_from, $cancel_time_to); 
}
	
array_push($cancel_date_array, $cancel_time_from, $cancel_time_to); 

for($count = 0; $count < sizeof($major_dcs); $count++){ 			

		array_push($all_date_array, $major_times_from[$count]);
		array_push($all_date_array, $major_times_to[$count]);
		if(overlapping_dates($major_times_from[$count],$major_times_to[$count],$major_times_from[$count+1]))
			array_push($invalid_date_array, date("m-d-Y",strtotime($major_times_from[$count])+5184000), date("m-d-Y", strtotime($major_times_from[$count+1])-5184000));	
}
sort($all_date_array);


// Signature Process
if($_POST['Sign']=="I verify" && strlen($_POST['sig'])>3 && substr_count($_POST['sig'], " ")>0){
	$signature=addslashes(trim($_POST['sig']));
	$field=getDBColumnNameforSigFromKey($_POST['role'], "1"); // Getting column name like depthead1 from Department Head 1
	$substitute_signer=trim($_POST['auth']);

	// When substitute signer signs, append username to the name.
	if($substitute_signer!="")
		$signature=$signature." (".$substitute_signer.")";

	$time=time();
	$IP=$_SERVER['REMOTE_ADDR'];
	$signature=$signature."+-__-+" . "DocID: " . hash("crc32", $ID). "+-__-+" .$time."+-__-+".$IP;

	$authEncData=$auth->getOrigNameAndEmail($ID, $field); // Getting original initial name and email

	$signature="||.||".urlencode($enc->encrypt($signature, md5($authEncData))); // Encrypt the signature part

	// If Grad School, get Quarters for Fee Waiver from the form (when the form is signed) and store it into the DB.
	if($field == "grad_school") {
		$quarters = $_POST['quarters'];
		$quarters = implode(", ", $quarters);
		$specific_position = $_POST['specific_position'];
		
		if($quarters != "" || $specific_position != "") {
			$query = "UPDATE `appointment_request_form` SET `quarter`='$quarters', `specific_position` = '$specific_position' WHERE `id`='$ID'";
			mysql_query($query);
		}
	}

	$query="SELECT `$field` FROM `appointment_request_signatures` WHERE form_id='$ID'";
	$result=mysql_query($query);

	if(substr_count(mysql_result($result, 0, "$field"), "||.||")==0){
		$replace=array("'", "\""); // avoid sql conflict
		$repace_with=array("|key|", "|key1|");

		$query="UPDATE `appointment_request_signatures` SET `$field`=CONCAT(`$field`, '$signature') WHERE form_id='$ID'";

		if(mysql_query($query)){
			$sign_success=1;
			checkFormStatusAndSendNotifications($ID, $field);
		}
	}
}
else if($_POST['Sign']=="I verify")
	$sign_error=1;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Appointment Request Form - The Office of Human Resources - SCSU</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
<link rel="stylesheet" type="text/css" href="resources/style/arf_style.css"> 
<script type="text/javascript" language="javascript" src="resources/js/script.js"></script>

<script type="text/javascript" language="javascript">
	$(document).ready(function(){
		//Check to see if the window is top if not then display button
		$(window).scroll(function(){
			if ($(this).scrollTop() > 100) {
				$('.scrollToTop').fadeIn();
			} else {
				$('.scrollToTop').fadeOut();
			}
		});
	
		//Click event to scroll to top
		$('.scrollToTop').click(function(){
			$('html, body').animate({scrollTop : 0},800);
			return false;
		});
		
	});
	<? if($initiated==1){ ?>
		alert('The Appointment Request Form you initiated has been sent for signatures. The status of the form can be checked by visiting the "Check Forms Status" link on the home page. Thanks for using the e-ARF system!');
	<? } ?>
	</script>


<?	// For when any person signs the form successfully
	if($sign_success==1){
		$alert=sendSignedNotice($field);
		$alert=explode("has", $alert);
		$alert="Congratulations! You have".$alert[1];
?>
	<script language="javascript">
        alert('<? echo $alert; ?>');
    </script>
<? } ?>

<? if($sign_error==1){ // For when person doesn't type full name for signature ?>
	<script language="javascript">
    alert("ERROR: Please enter your full name to sign this form.");
    </script>
<? } ?>
</head>
<body>
<? include('includes/header.php'); ?>

<div id="main_div">
	<div id="top">
        <?
			if(trim($remarks_comments)!="")
            	$remarks_count=countRemarks($ID);

            if($remarks_count>0){
            ?>
            <script type='text/javascript'>//<![CDATA[ 
            $(window).load(function(){
            $('a').click(function(){
                $('html, body').animate({
                    scrollTop: $( $(this).attr('href') ).offset().top
                }, 500);
                return false;
            });
            });//]]>
            </script>
            <a href="#remarks" id="remarks_notification">
                Click here to read <strong><? echo $remarks_count; ?> remark<? if($remarks_count>1){ echo "s"; } ?>/comment<? if($remarks_count>1){ echo "s"; } ?></strong> this form has.
            </a>
            <? } ?>
        
            <div id="printer">
                <a href="javascript: printReady()">Printer-friendly Version</a>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
    </div>

	<div id="header_logo">
        <img src="resources/images/logo.png">
	</div>

	<div id="instructions">
		<strong>INSTRUCTIONS:</strong> Department Head, Dean, or other Budget Unit Head will initiate and retain one copy. Completed original form should then be forwarded to appropriate offices for signature. Official transcripts for new teaching faculty should accompany the original appointment form. This form should be fully processed with complete information prior to the effective date of employment. All new appointments should be fully processed and have Board of Supervisor approval prior to the effective date of employment. (Graduate, Research and Teaching Assistant appointments do not require Board of Supervisor approval.) Forms received after the Monthly Payroll Deadline in the Office of Human Resources will be processed the following month. The Office of Human Resources will forward a final approved copy to appropriate unit(s).
	</div>

	<a href="#" class="scrollToTop">Scroll To Top</a>
	<form method="POST">
		<table cellpadding="5" cellspacing="0" border="0" width="100%" id="cancel_table" style="display:none; margin-top: 10px">
			<tr bgcolor="#e5e5ff">
				<td style="color:#b32c00"><b><i>Enter the period for which you want to cancel this ARF:</i></b></td>
				<td><b>From:</b><br><input type="text" name="cancel_time_from" placeholder="mm/dd/yyyy" id="datepicker15" class="dp"></td>
				<td><b>To:</b><br><input type="text" name="cancel_time_to" placeholder="mm/dd/yyyy" id="datepicker16" class="dp"></td>
				<td><input type="submit" name="cancel" value="cancel" style="padding-left: 5px; padding-right: 5px; cursor: pointer; height: 20px; text-transform: uppercase; background: #b32c00"></td>
			</tr>
		</table>
		
	<form>
		<?php 
            // Displaying other ARF forms
            $query_other_ids = "SELECT id FROM `appointment_request_form` WHERE `session_id`= (select `session_id` from appointment_request_form where id='$ID') and id <> '$ID';"; 
            $result_other_ids = mysql_query($query_other_ids);
            $num_rows = mysql_num_rows($result_other_ids);
            $i = 0;
            if($num_rows!=0)
            	echo "<p>This form is a part of an original form that was split.  The following are the other parts: ";
            while($i<$num_rows)
            {
            	echo "<div style=\"padding: 5px; background: #F1F1F1\"><strong><a href='arf.php?ID=" . mysql_result($result_other_ids, $i, 'id') . "'>Form " . ($i+1) . "</a></strong></div><br>";
            	$i++;
            }
         ?>
		<table cellspacing="0" cellpadding="0" width="100%" border="0" id="timeline">
			<tr>
				<td><span class="section_title">Timeline:</span></td>
			</tr>
			<tr>
				<td>
					<!-- Time line graph -->
					<p>Timeline for the Academic Year: <strong><?php if(date('n')>6) echo "07/01/" . date('Y') . " - 06/30/" . (date('Y')+1); else echo "07/01/" . (date('Y')-1) . " - 06/30/" . date('Y');?></strong></p>
					<div id="outerDiv" style="box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; position:relative; border-radius: 5px; width: 100%; height: 50px; background-color:#ffffff; border: 1px solid black">
						<?php 
						// getting other ARF forms of the user
							echo getARFs($email);
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="32"><i><div><font color="#FF0000">Red</font> = cancelled period; White = invalid period; <font color="#26a000">Green</font> = valid period.</div></i></td>
			</tr>
		</table>
		
    <div class="darker_row">
        <table cellpadding="5" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="13%"><b>Appointment</b></td>
                <td width="21%">
                <?
                     $appointment_array=array("New", "Continuing", "Amended");

                     switch($appointment){
                        case 0:
                            echo printValue();
                            break;
                        case 1:
                            echo printValue($appointment_array[0]);
                            break;
                        case 2:
                            echo printValue($appointment_array[1]);
                            break;
                        case 3:
                            echo printValue($appointment_array[2]);
                            break;
                     }
                ?>
                </td>
				<td width="11%" align="right"><b>College</b></td>
				<td width="26%"><? echo printValue(getCollege($college)); ?></td>
                <td width="11%" align="right"><b>Date</b></td>
                <td width="18%"><? echo printValue("$date"); ?></td>
            </tr>
            <tr>
				<td><b>Appointment for</b></td>
				<td colspan="<? if($quarter == "") echo "5"; else echo 2; ?>"><?
                     $appointment_for_array = array("Faculty/Staff", "Student");
                     switch($appointment_for){
                        case 0:
                            echo printValue();
                            break;
                        case 1:
                            echo printValue($appointment_for_array[0]);
                            break;
                        case 2:
                            echo printValue($appointment_for_array[1]);
                            break;
						}
				?></td>
				<? if($quarter != "") { ?>
            	<td align="right" colspan="2"><b>Graduate/Teaching Assistant fee waiver<? if($specific_position != "" || $quarter!= "") { echo "*"; } ?></b></td>
                <td colspan="2">
                <?
                    if(sizeof($quarter>0)){
                        $quarter_display="";
                        for($i=0; $i<sizeof($quarter); $i++){
                            if($i==sizeof($quarter)-1)
                                $quarter_display.=" and ";
                            $quarter_display.=$quarter[$i];
                            if($i!=sizeof($quarter)-1)
                                $quarter_display.=", ";
                        }
                    }
                    echo printValue($quarter_display);
                ?>
                </td>
                <? } ?>
            </tr>
			<? if($appointment_for == 2){ ?>
			<tr>
				<td><b>Position</b></td>
				<td colspan="2"><?
                     $position_array=array("Graduate/Research Assistant", "Teaching Assistant (Instructor on Record)");
                     switch($position){
                        case 0:
                            echo printValue();
                            break;
                        case 1:
                            echo printValue($position_array[0]);
                            break;
                        case 2:
                            echo printValue($position_array[1]);
                            break;
						}
				?></td>
				<td align="right"><b>Expected Hours/Week</b></td>
				<td colspan="2">
					<?
						echo printValue($hours_per_week);
					?>
				</td>
			<? if($specific_position != "" || $quarter != "") { ?>
			<tr bgcolor="#EEEEEE"><td colspan="6" style="font-size: 8pt" align="right">*Information added by Graduate School</td></tr>
			<? } ?>
			<? } ?>
        </table>
    </div>


    <table cellspacing="0" cellpadding="5" border="0" width="100%" id="pi1_tb">
        <tr>
            <td colspan="6"><span class="section_title">Personal Information</span></td>
        </tr>
        <tr>
        	<td><b>Email</b></td>
			<td colspan="5"><?php echo printValue("$email"); ?></td>
			
        </tr>
        <tr>
            <td width="13%"><b>Name</b></td>
            <td width="29%">
                <?php echo printValue("$lname"); ?>
                <div>Lastname</div>
            </td>
            <td width="29%" colspan="2">
                <?php echo printValue("$fname"); ?>
                <div>Firstname</div>
            </td>
            <td width="29%" colspan="2">
                <?php echo printValue("$mname"); ?>
                <div>Middlename</div>
            </td>
        </tr>
        <tr bgcolor="#F1F1F1">
            <td><b>Address</b></td>
            <td><?php echo printValue("$street"); ?>
                <div>Street Address</div>
            </td>
            <td colspan="2"><?php echo printValue("$city"); ?>
                <div>City/State</div>
            </td>
            <td colspan="2"><?php echo printValue("$zip"); ?>
                <div>Zip Code</div>
            </td>
        </tr>
        <tr>
            <td><b>Date Effective</b></td>
            <td><?php echo printValue("$date_effective"); ?></td>
			<td align="right" colspan="1"><b><?php if($appointment_for==2) echo "CWID"; else echo "SSN";?></b></td>
            <td colspan="1"><?php echo printValue("$ssn"); ?></td>	
        </tr>
        <tr bgcolor="#F1F1F1">
            <td><b>Date of Birth</b></td>
            <td><?php  echo printValue("$dob"); ?></td>
            <td align="right" align="15%"><b>Sex</b></td>
            <td>
            <? 
                $sex_array=array("Male", "Female");
                switch($sex){
                    case 0:
                        echo printValue();
                        break;
                    case 1:
                        echo printValue($sex_array[0]);
                        break;
                    case 2:
                        echo printValue($sex_array[1]);
                        break;
                }
            ?>
            </td>
            <td width="15%" align="right"><b>Marital Status</b></td>
            <td>
            <?
                $marital_array=array("Single", "Married", "Divorced");
				switch($marital){
					case 0:
						echo printValue();
						break;
					case 1:
						echo printValue($marital_array[0]);
						break;
					case 2:
						echo printValue($marital_array[1]);
						break;
					case 3:
						echo printValue($marital_array[2]);
						break;
				}
            ?>
            </td>
        </tr>
        <tr>
            <td><b>Ethnicity</b></td>
            <td>
            <?
       		if($race == 1)
       			echo printValue("Hispanic");
			else
				echo printValue("Non-hispanic");
            ?>
            </td>
            <td align="right"><b>Race</b></td>
            <td>
            <?
				$race_array=array("African American", "American Indian or Alaskan Native", "Asian or Pacific Islander", "White or Caucasian");
	
	            switch($raceList){
	                case 0:
	                    echo printValue("None");
	                    break;
	                case 1:
	                    echo printValue($race_array[0]);
	                    break;
	                case 2:
	                    echo printValue($race_array[1]);
	                    break;
	                case 3:
	                    echo printValue($race_array[2]);
	                    break;
	                case 4:
	                    echo printValue($race_array[3]);
	                    break;
				}
			?>
            </td>
            <td align="right"><b>Nationality</b></td>
            <td>
            	<?php 
            	$nationality_array = Array("Afghan","Albanian","Algerian","Andorran","Angolan","Argentinian","Armenian","Australian","Austrian","Azerbaijani","Bahamian","Bangladeshi","Barbadian","Belorussian","Belgian","Beninese","Bhutanese","Bolivian","Bosnian","Brazilian","Briton","Bruneian","Bulgarian","Burmese","Burundian","Cambodian","Cameroonian","Canadian","Chadian","Chilean","Chinese","Colombian","Congolese","Croatian","Cuban","Cypriot","Czech","Dane","Dominican","Ecuadorean","Egyptian","Salvadorean","Englishman","Eritrean","Estonian","Ethiopian","Fijian","Finn","Frenchman","Gabonese","Gambian","Georgian","German","Ghanaian","Greek","Grenadian","Guatemalan","Guinean","Guyanese","Haitian","Dutchman","Honduran","Hungarian","Icelander","Indian","Indonesian","Iranian","Iraqi","Irishman","Israeli","Italian","Jamaican","Japanese","Jordanian","Kazakh","Kenyan","Korean","Kuwaiti","Laotian","Latvian","Lebanese","Liberian","Libyan","Liechtensteiner","Lithuanian","Luxembourger","Macedonian","Madagascan","Malawian","Malaysian","Maldivian","Malian","Maltese","Mauritanian","Mauritian","Mexican","Moldovan","Monacan","Mongolian","Montenegrin","Moroccan","Mozambican","Namibian","Nepalese","Nicaraguan","Nigerien","Nigerian","Norwegian","Pakistani","Panamanian","Paraguayan","Peruvian","Filipino","Pole","Portuguese","Qatari","Romanian","Russian","Rwandan","Saudi","Scot","Senegalese","Serbian","Singaporean","Slovak","Slovenian","Somali","Spaniard","SriLankan","Sudanese","Surinamese","Swazi","Swede","Swiss","Syrian","Taiwanese","Tadzhik","Tanzanian","Thai","Togolese","Trinidadian","Tunisian","Turk","Ugandan","Ukrainian","British","American","Uruguayan","Uzbek","Venezuelan","Vietnamese","Welshman","Yemeni","Yugoslav","Zambian","Zimbabwean", "St. Lucian");
            	if($nationality==0)
            		echo printValue();
            	else
            		echo printValue($nationality_array[$nationality-1]); ?>
            </td>
		</tr>
        <tr bgcolor="#F1F1F1">
            <td><b>Department</b></td>
            <td<?php if($appointment_for==2) echo " colspan='5'>"; 
            	$dept_array = Array("PRESIDENT'S OFFICE - 009","ACADEMIC AFFAIRS - 010","ADMINISTRATIVE AFFAIRS - 012","STUDENT AFFAIRS - 014","APPLIED &amp; NATURAL SCIENCES � ADM. - 015","APPLIED &amp; NATUAL SCIENCES - 016","HEALTH INFORMATION - 017","LIBERAL ARTS - 018","INTERNAL AUDIT - 019","COLLEGE OF BUSINESS - 020","EDUCATION - 022","ENGINEERING - 024","HUMAN ECOLOGY - 026","UNIVERSITY RESEARCH - 027","GRADUATE SCHOOL - 028","CONTINUING EDUCATION - 029","AFROTC - 030","BARKSDALE - 031","BOOKSTORE (BARNES &amp; NOBLE) - 032","BUILDINGS &amp; GROUNDS - 034","PURCHASING - 036","TELECOMMUNICATIONS - 038","COMPTROLLER - 040","COMPUTING CENTER - 042","ENVIRONMENTAL - 046","ATHLETICS - 048","HUMAN RESOURCES - 052","LIBRARY - 054","NEWS BUREAU - 058","UNIVERSITY POLICE - 060","UNIVERSITY ADVANCEMENT - 062","POST OFFICE/ OFFICE SVCS. - 064","PROPERTY - 066","REGISTRAR - 068","FINANCIAL AID - 070","BIOLOGICAL SCIENCES - 080","ADMISSIONS/ ENROLLMENT MANAGEMENT - 082","NURSING - 086","SUPERVISING TEACHERS (EDUCATION) - 092");
            	if($dept==0)
            		echo printValue();
            	else
            		echo printValue($dept_array[$dept-1]); ?></td>
            <td <?php if($appointment_for==2) echo " style='display:none;'"?> align="right" colspan="2"><b>Rank/Discipline</b></td>
            <td <?php if($appointment_for==2) echo " style='display:none;'"?> colspan="2"><?php  echo printValue("$rank"); ?></td>
        </tr>
        <tr bgcolor="#F1F1F1">
        	<td><b>Work Supervisor</b></td>
        	<td colspan="5">
        		<? if($supervisor_name != "") echo printValue("$supervisor_name ($supervisor_email)"); ?>
        	</td>
        </tr>
    </table>

    <div <?php if($appointment_for==2) echo " style='display:none;'"?> class="darker_row">
        <table cellspacing="0" cellpadding="5" border="0" width="100%">
            <tr>
                <td colspan="3"><div class="section_title">Educational Attainments</div></td>
            </tr>
            <tr>
                <th width="18%">DEGREE</th>
                <th width="62%">UNIVERSITY</th>
                <th width="20%">YEAR EARNED</th>
            </tr>
            <tr>
                <td align="center"><b>Doctorate</b></td>
                <td><?php  echo printValue("$univ_doc"); ?></td>
                <td><?php  echo printValue("$years_doc"); ?></td>
            </tr>
            <tr>
                <td align="center"><b>Master</b></td>
                <td><?php  echo printValue("$univ_master"); ?></td>
                <td><?php  echo printValue("$years_master"); ?></td>
            </tr>
            <tr>
                <td align="center"><b>Bachelor</b></td>
                <td><?php  echo printValue("$univ_bachelor"); ?></td>
                <td><?php  echo printValue("$years_bachelor"); ?></td>
            </tr>
        </table>
    </div>

    <table <?php if($appointment_for==2) echo " style='display:none;'"?> cellpadding="5" cellspacing="0" width="100%">
        <tr>
            <td colspan="8"><div class="section_title">Experience</div></td>
        </tr>
        <tr bgcolor="#F1F1F1">
            <td width="12%"><b>Higher Education</b></td>
            <td width="8%"><?php  echo printValue("$higher_edu"); ?></td>
            <td width="12%" align="right"><b>Years at Tech</b></td>
            <td width="8%"><?php  echo printValue("$years_tech"); ?></td>
            <td width="12%" align="right"><b>Other</b></td>
            <td width="8%"><?php  echo printValue("$other"); ?></td>
            <td width="12%" align="right"><b>Total Experience</b></td>
            <td width="8%"><strong><?php  echo printValue("$exp"); ?></strong></td>
        </tr>
    </table>

    <div class="darker_row">
        <table cellpadding="5" cellspacing="0" border="0" width="100%">	
            <tr>
                <td colspan="4"><div class="section_title">Salary Details</div></td>
            </tr>
            <tr>
                <td width="50%" colspan="2"><b>Amount to be paid</b></td>
                <td <?php if($appointment_for==2) echo " style='display:none;'"?> width="50%" colspan="2"><b>Requested Salary (Yearly)</b></td>
         	</tr>
            <tr>
                <td><?php  echo printValue("$amt[1]"); ?><i style="font-size: 8pt; color: #333">Monthly</i></td>
                <td><?php  echo printValue("$amt[0]"); ?><i style="font-size: 8pt; color: #333">Base</i></td>
                <td <?php if($appointment_for==2) echo " style='display:none;'"?> colspan="2" valign="top"><?php  echo printValue("$req_sal"); ?></td>
         	</tr>	
            <tr <?php if($appointment_for==2) echo " style='display:none;'"?>>
                <td width="12%"><b>Replaces</b></td>
                <td width="38%"><?php  echo printValue("$replaces"); ?></td>
                <td width="25%" align="right"><b>Job Type</b></td>
                <td width="25%">
                <?
                $jobType_array=array("Part-Time","Full-Time");
                switch($jobType){
                    case 0:
                        echo printValue();
                        break;
                    case 1:
                        echo printValue($jobType_array[0]);
                        break;
                    case 2:
                        echo printValue($jobType_array[1]);
                        break;
                }
                ?>
                </td>
            </tr>
            <tr <?php if($appointment_for==2) echo " style='display:none;'"?>>
                <td><b>Released-Time</b></td>
                <td><?php echo printValue("$rtime"); ?></td>
                <td align="right"><b>Salary Charged To</b></td>
                <td><?php echo printValue("$salaryCharged"); ?></td>
            </tr>
            <tr <?php if($appointment_for==2) echo " style='display:none;'"?>>
                <td><b>Salary Basis</b></td>
                <td colspan="3"><?
                    $salaryBasis_array=array("9-Months", "12-Months", "Quarterly", "Other");
                    $salaryBasis_print="";
                    switch($salaryBasis){
                        case 1:
                            $salaryBasis_print=$salaryBasis_array[0];
                            break;
                        case 2:
                            $salaryBasis_print=$salaryBasis_array[1];
                            break;
                        case 3:
                            $salaryBasis_print=$salaryBasis_array[2];
                            break;
                        case 4:
                            $salaryBasis_print=$salaryBasis_array[3];
                            break;
                    }

                    if($other_sal!=""){
                        $salaryBasis_print="<strong>".$salaryBasis_print;
                        $salaryBasis_print.="</strong>: $other_sal";
                    }

                    echo printValue($salaryBasis_print);
                ?>
                </td>
            </tr>
            <tr <?php if($appointment_for==2) echo " style='display:none;'"?>>
                <td><b>Retirement</b></td>
                <td colspan="3">
                <?
                $retirement_array=array("Social Security", "Teachers", "Employees", "ORP");
                $retirement_print="";
                switch($retirement){
                    case 1:
                        $retirement_print=$retirement_array[0];
                        break;
                    case 2:
                        $retirement_print=$retirement_array[1];
                        break;
                    case 3:
                        $retirement_print=$retirement_array[2];
                        break;
                    case 4:
                        $retirement_print=$retirement_array[3];
                        break;
                }
                if($orp!=""){
                    $retirement_print="<strong>".$retirement_print;
                    $retirement_print.="</strong>: $orp";
                }

                echo printValue($retirement_print);
                ?>
                </td>
            </tr>
        </table>
    </div>

<? /* Removing attachments part for now, because it may not be needed.
    <div class="section_box">
        <table cellspacing="2" cellpadding="2" width="100%" border="0" bgcolor="#DDDDDD">
    <tr>
        <td colspan="1" class="section_title">Attach A File<font style="text-transform: lowercase"></font></td>
    </tr>
    <tr>
            <td width="40%"><?
            if(attachmentExists($ID."_attachment_file")!=0){
            echo "<strong>Attached File:</strong>";
            echo printValue("<a href=\"secure/access/$id_files/22055/$attachment_file\" target=\"_blank\">$attachment_file</a>", "file");
        }
        else
            echo "No attachment";
            ?></td>
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
                <td align="right">From:</td>
                <td><?php echo printValue("$major_time_from"); ?></td>
                <td align="right">To:</td>
                <td><?php echo printValue("$major_time_to"); ?></td>
            </tr>
            <tr>
            	<td colspan="4"><div id="warning_messages">
            		<?php // Displaying pro-rated check boxes
            			
						if($prorated_amounts[2]!="$0.00" && sizeof(array_filter($prorated_amounts)) != 0)
						{
							if($prorated_amounts[2]!=$monthly_sal)
								echo '<p id="major_time_warning">The salary will be paid based on the pro-rated amount. Salary of the month (' . getDateFormat($major_time_from) . ') is: ' . $prorated_amounts[2] . '</p>';
						}
						if($prorated_amounts[0]!="$0.00" && sizeof(array_filter($prorated_amounts)) != 0)
						{
							if($prorated_amounts[0]!=$monthly_sal)
								echo '<p id="major_time_from_warning">The salary will be paid based on the pro-rated amount. Salary for the first month (' . getDateFormat($major_time_from) . ') is: ' . $prorated_amounts[0] . '</p>';
						}
						if($prorated_amounts[1]!="$0.00" && sizeof(array_filter($prorated_amounts)) != 0)
						{
							if($prorated_amounts[1]!=$monthly_sal)
								// Changing form last month to first month, if the form has been splitted and it is the only month in that ARF
								if(explode("/", $major_time_from)[0]==explode("/", $major_time_to)[0]) // if the months are same
									echo '<p id="major_time_to_warning">The salary will be paid based on the pro-rated amount. Salary for the first month (' . getDateFormat($major_time_to) . ') is: ' . $prorated_amounts[1] . '.</p>';
								else
									echo '<p id="major_time_to_warning">The salary will be paid based on the pro-rated amount. Salary for the last month (' . getDateFormat($major_time_to) . ') is: ' . $prorated_amounts[1] . '.</p>';
						}
						?>
            	</div></td>
            </tr>
    	</tbody>
  	</table>

    <table cellspacing="0" cellpadding="5" border="0" width="100%">
        <tr>
            <td colspan="4"><div class="section_title">Source Of Funds</div></td>
        </tr>
        <tr>
            <th width="15%">Department Codes</th>
            <th width="20%">Budget Page & Line</th>
            <th width="16%">Monthly Amount</th>
            <th width="10%">%</th>
            <th width="12%">Total Funds</th>
        </tr>
        <? for($count = 0; $count < sizeof($major_dcs); $count++){ ?>
        <tr>
            <td><?php  echo printValue("$major_dcs[$count]"); ?></td>
            <td><?php  echo printValue("$major_budgets[$count]"); ?></td>
            <td><?php  echo printValue("$major_amts[$count]"); ?></td>
            <td><?php  echo printValue("$major_percs[$count]"); ?></td>
            <td><?php  echo printValue("$" . number_format($major_funds[$count],2)); ?></td>
        </tr>
        <? } ?>
    </table>

    <table cellspacing="0" cellpadding="5" width="100%" border="0">
    	<tr>
			<td style="text-align: right;padding-right: 10px;" width="35%"><b>Total:<b></th>
			<td width="16%"><?php  echo printValue("$monthly_sal"); ?></td>
            <td width="10%"><?php  echo printValue("100.00%"); ?></td>
            <td width="12%"><?php  echo printValue("$" . number_format($base_sal,2)); ?></td>
		</tr>
    </table>

	<table cellspacing="0" cellpadding="5" width="100%" border="0">
		<tr>
			<th align="left">Comments</th>
		</tr>
		<tr>
			<td><? if($edit==1) echo printValue("$major_comments"); ?></td>
		</tr>
	</table>

    <? if($appointment==1){ // If "New," display below this before signatures. ?>
    <div <?php if($appointment_for==2) echo " style='display:none;'"?> class="darker_row">
        <table cellspacing="0" cellpadding="5" border="0" width="100%">
            <tr>
                <td colspan="4"><div class="section_title">A. Higher Education Experience</div></td>
            </tr>
            <tr>
                <th width="30%">University/Employer</th>
                <th width="30%">Position of Service</th>
                <th width="20%">From Date</th>
                <th width="20%">To Date</th>
            </tr>
            <? for($count=0;$count<sizeof($a_univ_exps);$count++){ ?>
            <tr>
                <td><?php  echo printValue("$a_univ_exps[$count]"); ?></td>
                <td><?php  echo printValue("$a_position_exps[$count]"); ?></td>
                <td><?php  echo printValue("$a_from_exps[$count]"); ?></td>
                <td><?php  echo printValue("$a_to_exps[$count]"); ?></td>
            </tr>
            <? } ?>
        </table>
    </div>

    <table <?php if($appointment_for==2) echo " style='display:none;'"?> cellspacing="0" cellpadding="5" border="0" width="100%">
        <tr>
            <td colspan="4"><div class="section_title">B. Other Education Experience</div></td>
        </tr>
        <tr>
            <th width="30%">University/Employer</th>
            <th width="30%">Position &amp Nature of Service</th>
            <th width="20%">From Date</th>
            <th width="20%">To Date</th>
        </tr>
        <? for($count=0;$count<sizeof($b_exps);$count++){ ?>
        <tr>
            <td><?php  echo printValue("$b_exps[$count]"); ?></td>
            <td><?php  echo printValue("$b_position_exps[$count]"); ?></td>
            <td><?php  echo printValue("$b_from_exps[$count]"); ?></td>
            <td><?php  echo printValue("$b_to_exps[$count]"); ?></td>
        </tr>
        <? } ?>
    </table>

    <div <?php if($appointment_for==2) echo " style='display:none;'"?> class="darker_row">
        <table cellspacing="0" cellpadding="5" border="0" width="100%">
            <tr>
                <td colspan="4"><div class="section_title">C. Other Experience</div></td>
            </tr>
            <tr>
                <td colspan="4"><b>1. Since Baccalaureate Degree</b></td>
            </tr>
            <tr>
                <th width="30%">Employer</th>
                <th width="30%">Position &amp; Nature of Service</th>
                <th width="20%">From Date</th>
                <th width="20%">To Date</th>
            </tr>
            <? for($count=0;$count<sizeof($c1_univ_exps);$count++){ ?>
            <tr>
                <td><?php  echo printValue("$c1_univ_exps[$count]"); ?></td>
                <td><?php  echo printValue("$c1_position_exps[$count]"); ?></td>
                <td><?php  echo printValue("$c1_from_exps[$count]"); ?></td>
                <td><?php  echo printValue("$c1_to_exps[$count]"); ?></td>
            </tr>
            <? } ?>
        </table>

        <table <?php if($appointment_for==2) echo " style='display:none;'"?> cellspacing="0" cellpadding="5" border="0" width="100%">
            <tr>
                <td colspan="4"><b>2. Prior to Baccalaureate</b></td>
            </tr>
            <? for($count=0;$count<sizeof($c2_univ_exps);$count++){ ?>
            <tr>
                <td width="30%"><?php  echo printValue("$c2_univ_exps[$count]"); ?></td>
                <td width="30%"><?php  echo printValue("$c2_position_exps[$count]"); ?></td>
                <td width="20%"><?php  echo printValue("$c2_from_exps[$count]"); ?></td>
                <td width="20%"><?php  echo printValue("$c2_to_exps[$count]"); ?></td>
            </tr>
            <? } ?>
        </table>
    </div>

    <table cellspacing="0" cellpadding="5" border="0" width="100%"<?php if($appointment_for == 2) echo " style='display:none;'"?>>
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
            <td><? echo printValue($total_exp); ?></td>
            <td><? echo printValue($relevant_exp); ?></td>
            <td><? echo printValue($all_other_exp); ?></td>
            <td><? echo printValue($other_relevant_exp); ?></td>
            <td><strong><? echo printValue($total_exp_grand); ?></strong></td>
        </tr>
    </table>
    <? } // If page two also ?>

	<div class="darker_row">
		<table cellspacing="0" cellpadding="5" border="0" width="100%">
			<tr>
				<td colspan="3"><span class="section_title">Prepared By</span></td>
			</tr>
			<tr>
				<td><? if($prepared_by != "") echo printValue("$prepared_by"); ?></td>
			</tr>
		</table>
	</div>

    <div class="darker_row">
    <?
    $authPersonnel=$auth->authPersonnel($ID);

        // Get the list of anticipated signatures ?>
        <table cellspacing="0" cellpadding="5" border="0">
            <tr>
                <td colspan="4"><div class="section_title">Requested Signatures</div></td>
            </tr>
            <?
            $i=0;
            foreach($authPersonnel as $key=>$val){ if($i%2!=0){ $style=" class=\"sig_darker\""; } else $style=""; ?>
            <tr<? echo $style ?>>
                <td width="22%">
                <?
                    $display_key=getDBColumnNameforSigFromKey($key, "2");
                    echo "<strong>$display_key</strong>"; // Role
                ?>
                </td>
                <td width="78%">
                <?php
                    if(strlen(trim(getSignature($key, $ID)))>0){ // If signature is present ?>
                    <div style="padding: 5px; background: #FFF; border-radius: 3px; box-shadow: inset 2px 2px 3px #AAA; padding-right: 15px">
                        <table cellpadding="0" cellspacing="3">
                            <tr>
                                <td width="15%"><img src="lib/signatures.php?token=<? echo $ID."||".$key."||"."Xl8d!98ZXWX1ZAT"; ?>"></td>
                                <td><img src="lib/signatures.php?token=<? echo $ID."||".$key."||"."X18d!98ZXWX!ZAT"; ?>"></td>
                            </tr>
                        </table>
                    </div>
                <?	}
                    else if(strtolower(retrieveUserIDfromInitSig($val))==strtolower($username) || $auth->isSubstituteSignerOf($username, retrieveUserIDfromInitSig($val))){
                ?>
        		<script language="javascript">
				function toggleDisable(id){
					var button=document.getElementById(id).disabled;
					if(button==true){
						document.getElementById(id).disabled=false;
						document.getElementById(id).style.opacity='1';
					}
					else{
						document.getElementById(id).disabled=true;
						document.getElementById(id).style.opacity='0.4';
					}
				}
				</script>
                <div style="border-radius: 3px; background: #FFF; box-shadow: inset 2px 2px 2px #888; padding: 10px"><? // $appointment==2 was added to make this statement false all the time ?>
                    <form style="margin: 0" method="POST">
                	<? if((substr_count($key, 'dept_head')>0 || substr_count($key, 'dean')>0 || substr_count($key, 'vice_president')>0) && ($appointment==1 && $appointment==2)){
                		$readonly=1;

						if($key=="dept_head"){
							$submit_id="signature_button_dept_head";
						}
						else if($key=="dean"){
							$submit_id="signature_button_dean";
						}
						else if($key=="vice_president"){
							$submit_id="signature_button_vice_president";
						}
                        echo "<table id=\"fa_verify\" cellpadding=\"0\" cellspacing=\"0\"><tr><td><input type=\"checkbox\" onchange=\"toggleDisable('$submit_id')\" id=\"fa_checkbox_$submit_id\"></td><td><label for=\"fa_checkbox_$submit_id\">I verify that the credentials listed on sections A, B, C and Summary Evaluation are appropriate.</label></td></table>";
                	}
					?>

						<? if($appointment_for == 2 && substr_count($key, 'grad_school') > 0) { ?>
						<div style="margin-bottom: 15px">
                     		<strong>Please choose the quarter(s) for Graduate/Teaching Assistant fee waiver:</strong><br />
                     		<label><input type="checkbox" name="quarters[]" value="Fall"> Fall&emsp;</label>
                     		<label><input type="checkbox" name="quarters[]" value="Winter"> Winter&emsp;</label>
                     		<label><input type="checkbox" name="quarters[]" value="Spring"> Spring&emsp;</label>
                     		<label><input type="checkbox" name="quarters[]" value="Summer"> Summer</label>
                     	</div>

						<? if($position == 1) { ?>
						<div style="margin-bottom: 15px">
                     		<strong>Please choose the specific Position for this Graduate/Research Assistantship:</strong><br />
                     		<label><input type="radio" name="specific_position" value="Graduate Assistant"> Graduate Assistant&emsp;</label>
                     		<label><input type="radio" name="specific_position" value="Graduate Research Assistant"> Graduate Research Assistant&emsp;</label>
                     		<label><input type="radio" name="specific_position" value="Research Assistant"> Research Assistant&emsp;</label>
                     	</div>
                     	<? } ?>

						<? } ?>

	                    <div style="color: #13467a; font-weight: bold; font-size: 8pt; font-family: Verdana; margin-bottom: 10px">
	                    	This Appointment Request Form requires your signature for processing. By entering your full name below, you agree that you have checked all the fields and verify that they are appropriate.
	                    </div>
                        <input type="text" id="sig" style="font-size: 22pt; box-shadow: inset 1px 1px 2px #888; background-color: #F8F8F8; border-radius: 0; padding: 5px; width: 315px; border: 0; border-bottom: 1px solid #666; color: #274b0a" name="sig"<? if($sign_error==1) echo " autofocus"; ?>><br>
                        <div style="font-size: 8pt">Today's date: <? echo date("m/j/Y"); ?></div>
                        <input type="hidden" name="role" value="<? echo $display_key; ?>">
                        <? if($auth->isSubstituteSignerOf($username, retrieveUserIDfromInitSig($val))){ // When Subsitute signer is signing, append username to a signature ?>
                        <input type="hidden" name="auth" value="<? echo $username ?>">
                        <? } ?>
                        <input type="submit" id="signature_button<? if($key=="dept_head") echo "_dept_head"; else if($key=="dean") echo "_dean"; else if($key=="vice_president") echo "_vice_president"; ?>" <? if($readonly==1) echo " disabled"; ?> name="Sign" style="<? if($readonly==1) echo "opacity: 0.4; "; ?>margin-top: 8px" title="Sign this form" value="I verify">
                    </form>
                </div>
                <?
				}
				else{
					echo printValue(retrieveNamefromInitSig($val));
				}
				?></td>
                <? $i++;  ?>
            </tr>
            <? } ?>
        </table>
    </div>
	
	<div class="section_box">
        	<table cellpadding="4" width="100%" cellspacing="0">
				<tr>
                	<th colspan="2" align="left">Remarks/Comments</th>
                </tr>
                <tr>
                    <td width="50%" style="vertical-align: top">
                    	<form>
                            <textarea name="rem" cols="50" style="min-width: 425px; max-width: 425px" rows="5" id="comments_remarks"></textarea><br>
                            <input type="submit" value="Add Remarks" onclick="remarks(); return false;">
                        </form>
                    </td>
					<td style="vertical-align: top" id="remarks">
                    	<strong>Remarks/Comments</strong>
                        <div id="display_remarks">
                        	<?
                            	if(strlen(trim($remarks_comments))==0)
									echo "There are no remarks/comments for this form yet.";
								else{
									echo "<p>";
									$replace=array("\n\n", "[[");
									$replace_with=array("</em></p><p>", "<br><em style=\"font-size: 8pt; color: #444\">&emsp;&#8212; ");
									$remarks_comments=str_replace($replace, $replace_with, $remarks_comments);
									echo urldecode($remarks_comments);
									echo "</p>";
								}
							?>
                        </div>
					</td>
                </tr>
            </table>
        </div>
		
		<script src="resources/js/jquerycal/external/jquery/jquery.js"></script>
		<script src="resources/js/jquerycal/jquery-ui.js"></script>

		<script type="text/javascript">
        function remarks(){
			
            var response;
            if (window.XMLHttpRequest)
                response = new XMLHttpRequest();
            else if (window.ActiveXObject)
                response = new ActiveXObject("Msxml2.XMLHTTP");
            else
                throw new Error("Unfortunately, your browser doesn't support AJAX, so we cannot submit your remarks/comments.");
           
            response.onreadystatechange = function () {
                if (response.readyState === 4) {
                    if (response.status == 200 && response.status < 300) {
                        document.getElementById('display_remarks').innerHTML = response.responseText;
                        document.getElementById("comments_remarks").value='';
                        document.getElementById('comments_remarks').focus();
                    }
                }
            }
        
            var remarks = document.getElementById("comments_remarks").value;
        
            if(remarks==""){
                alert("Cannot post your remark/comment because your message is empty.");
                return;
            }
        
            response.open('POST', 'secure/remarks.php');
            response.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            response.send("msg="+encodeURIComponent(remarks)+"&ID=<? echo $ID ?>");
        }
        </script>

		<div id="tw">
            <div id="wbg" onclick="hw();"></div>
            <div id="wc">
                <img src="https://forms.latech.edu/routing/i/ico-clx.gif" style="cursor: pointer" title="Close" onclick="hw();" align="right">
                <div id="wct">
                    <strong>Contact Initiator to Revise this Form</strong>
                    <div id="wcd">Please write a detailed message asking the person who prepared this form for revision.</div>
                    <div id="wcf"></div>
                </div>
            </div>
        </div>
        
        <script language="javascript">
        end_opacity = 60;
        increase_opacity_by = 15;
        timeout = 15;
        win = document.getElementById('tw');
        winbackground = document.getElementById('wbg');
        wincontent = document.getElementById('wc');
        cur_opacity = 0;
        
        var timer = null;
        
        function sendMessage(){
            document.getElementById('wcf').innerHTML="<form method=\"POST\"><div style=\"width: 420px\"><textarea style=\"min-width: 400px; max-width: 400px; max-height: 80px; height: 80px\" name=\"message_pi\"></textarea><p style=\"font-size: 8pt; color: #444\">When sent, this message will be appended into the Remarks/Comments section, and will also be sent to the personnel whose <strong>e-mail addresses</strong> you enter on the text field below <strong>seperated by a comma</strong>:</p><input type=\"text\" name=\"message_emails\" style=\"width: 400px; margin-bottom: 10px\"><br><input type=\"submit\" name=\"submit\" value=\"Send Message\"></div></form>";
            document.getElementById('wc').style.height="245px";
            document.getElementById('wc').style.padding="10px";
        
            if(timeout > 0) {
                cur_opacity = 0;
                winbackground.style.opacity = cur_opacity / 100;
                winbackground.style.filter = "alpha(opacity=" + cur_opacity + ")";
                win.style.display = 'block';
                wincontent.style.display = 'none';
                timer = setTimeout("increase_opacity()",timeout);
            }
            else {
                winbackground.style.opacity = end_opacity / 100;
                winbackground.style.filter = "alpha(opacity=" + end_opacity + ")";
                win.style.display = 'block';
                wincontent.style.display = 'block';
            }
        }
        
        function increase_opacity() {
            cur_opacity += increase_opacity_by;
            winbackground.style.opacity = cur_opacity / 100;
            winbackground.style.filter = "alpha(opacity=" + cur_opacity + ")";
        
            if(cur_opacity < end_opacity) {
                timer = setTimeout("increase_opacity()",timeout);
            } else {
                wincontent.style.display = 'block';
            }
        }
        function hw() {win.style.display = 'none'; document.getElementById('wcf').innerHTML=""; }
		
		// Display date picker on Date Fields
			$( ".dp" ).datepicker({
				inline: true
			});
        </script>
</div>
</form>
<? include('includes/footer.php'); ?>
</body>
</html>
