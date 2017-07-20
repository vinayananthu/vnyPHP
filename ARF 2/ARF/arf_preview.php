<?php

error_reporting(0);

require_once('lib/arf_functions.php');
$db=new SQL;

if($db->connect())
	echo "";
else
    echo "Not connected";

$ID=$_GET['ID'];


// saving session ID to temperory variable
$session_id = $ID;

$auth=new AuthUser;
$auth->auth();
$username=$auth->getUser();

include_once('includes/form_submit.php');
$form_id = $ID;

//echo "id " . $form_id;
if((!$auth->authorizeView($ID) && !isAdmin($username)) || $rows==0){ // Check if the logged in person is authorized to view the form
	print("Sorry, you are not authorized to view the preview of this form.");
	exit(0);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Appointment Request Form - Preview - SCSU</title>
<link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
<link rel="stylesheet" type="text/css" href="resources/style/arf_style.css"> 
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

    <div class="darker_row">
        <table cellpadding="5" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="15%"><b>Appointment</b></td>
                <td width="20%">
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
				<td width="8%" align="right"><b>College</b></td>
				<td width="25%"><? echo printValue(getCollege($college)); ?></td>
                <td width="15%" align="right"><b>Date</b></td>
                <td width="17%"><? echo printValue("$date"); ?></td>
            </tr>
            <tr>
				<td><b>Appointment for</b></td>
				<td colspan="5"><?
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
           <!-- <td align="right" colspan="2"><b>Graduate/Teaching Assistant fee waiver</b></td>
                <td colspan="2">
                <?
                    $quarter_array=array("Fall", "Winter", "Spring", "Summer");
                    if(sizeof($quarter>0)){
                        $quarter_display="";
                        for($i=0; $i<sizeof($quarter); $i++){
                            if($i==sizeof($quarter)-1)
                                $quarter_display.=" and ";
                            $quarter_display.=$quarter_array[$quarter[$i]-1];
                            if($i!=sizeof($quarter)-1)
                                $quarter_display.=", ";
                        }
                    }
                    echo printValue($quarter_display);
                ?>
                </td> -->
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
			</tr>
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
						if($prorated_amounts[2]!="$0.00")
						{
							if($prorated_amounts[2]!=$monthly_sal)
								echo '<p id="major_time_warning">The salary will be paid based on the pro-rated amount. Salary of the month (' . getDateFormat($major_time_from) . ') is: ' . $prorated_amounts[2] . '</p>';
						}
						if($prorated_amounts[0]!="$0.00")
						{
							if($prorated_amounts[0]!=$monthly_sal)
								echo '<p id="major_time_from_warning">The salary will be paid based on the pro-rated amount. Salary for the first month (' . getDateFormat($major_time_from) . ') is: ' . $prorated_amounts[0] . '</p>';
						}
						if($prorated_amounts[1]!="$0.00")
						{
							if($prorated_amounts[1]!=$monthly_sal)
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
        <? $personnel=getSignersList($ID); ?>
        <table cellspacing="0" cellpadding="5" border="0" width="100%">
            <tr>
                <td colspan="2"><div class="section_title">Requested Signatures</div></td>
            </tr>
            <?
            $i=0;
            foreach($personnel as $key=>$val){ ?>
            <tr>
                <td width="22%"><b><? echo $key ?></b></td>
                <td width="78%"><?php $details=retrieveNameAndEmailFromID("$val"); echo printValue($details[0]);?></td>
            </tr>
            <? } ?>
        </table>
    </div>
<?php $ID = $session_id;?>	
    <div style="text-align: center; margin-top: 10px">
        <input style="padding-left: 10px; padding-right: 10px; cursor: pointer; height: 30px; text-transform: uppercase" type="button" onclick="window.location='arf_form.php?ID=<?php print($ID) ?>'" size ="5" name="edit" value="<< GO BACK AND EDIT">&emsp;
        <input style="padding-left: 10px; padding-right: 10px; cursor: pointer; height: 30px; text-transform: uppercase" type="button" size ="5" name="forward" onclick="window.location='arf.php?ID=<?php print($form_id) ?>&action=init'" value="SEND FOR SIGNATURES >>">
    </div>
</div> <!-- End of main div -->

<? include('includes/footer.php'); ?>
</body>
</html>