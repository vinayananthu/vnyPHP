<?php
// error_reporting(0);

$enc = new Crypto;

if(isset($_POST['preview']) || isset($_POST['save'])){

	if($_POST['preview']=="PREVIEW" || $_POST['preview']=="EDIT & PREVIEW"){
		$form_type = '1';		// Submitted form
	}
	else if($_POST['save']=="SAVE AS DRAFT"){
		$form_type = '2';		// Saved, but not submitted(Incomplete Form)
	}

	// For encrypting and decrypting purposes
	$appointment=$_POST['appointment'];
	$date=$_POST['date'];
	$quarter=implode(", ", $_POST['quarter']);
	$appointment_for=$_POST['appointment_for'];
	$college=$_POST['college'];
	$position=$_POST['position'];
	$specific_position = $_POST['specific_position'];
	$hours_per_week = $_POST['hours_per_week'];
	$graduationdate = $_POST['graduationdate'];
	$credentials=$_POST['credentials'];

	$lname=addslashes(trim($_POST['lname']));
	$fname=addslashes(trim($_POST['fname']));
	$mname=addslashes(trim($_POST['mname']));

	$street=addslashes($_POST['street']);
	$city=addslashes($_POST['city']);
	$zip=$_POST['zip'];
	$date_effective=$_POST['date_effective'];
	$email=$_POST['email'];
	$ssn=trim($_POST['ssn'])!="" ? urlencode($enc->encrypt($_POST['ssn'], md5($username))) : '';
	$dob=$_POST['dob'];
	$sex=$_POST['sex'];
	$marital=$_POST['marital'];
	$race=$_POST['race'];
	$raceList = $_POST['raceList'];
	$nationality=$_POST['nationality'];

	$univ_doc=$_POST['univ_doc'];
	$years_doc=$_POST['years_doc'];
	$univ_master=$_POST['univ_master'];
	$years_master=$_POST['years_master'];
	$univ_bachelor=$_POST['univ_bachelor'];
	$years_bachelor=$_POST['years_bachelor'];

	$dept=$_POST['dept'];
	$rank=addslashes($_POST['rank']);

	$supervisor = addslashes($_POST['supervisor_name'])."||".$_POST['supervisor_email'];

	$higher_edu=$_POST['higher_edu'];
	$years_tech=$_POST['years_tech'];
	$other=$_POST['other'];
	$exp=$_POST['exp'];

	$req_sal=$_POST['req_sal'];
	$base_sal = $_POST['base_amt'];
	$monthly_sal = $_POST['monthly_amt'];
	$amt=$_POST['base_amt']."||||".$_POST['monthly_amt'];
	$replaces=$_POST['replaces'];
	$jobType=$_POST['jobType'];
	$rtime=$_POST['rtime'];
	$salaryCharged=$_POST['salaryCharged'];
	$salaryBasis=$_POST['salaryBasis'];
	$other_sal=$_POST['other_sal'];
	$retirement=$_POST['retirement'];
	$orp=$_POST['orp'];

	$major_dc=addslashes(implode("||||",$_POST['major_dc']));
	$major_budget=addslashes(implode("||||",$_POST['major_budget']));
	$major_perc=addslashes(implode("||||",$_POST['major_perc']));
	$major_amt=addslashes(implode("||||",$_POST['major_amt']));
	$major_time_from=$_POST['major_time_from'];
	$major_time_to=$_POST['major_time_to'];
	$cancel_time_from=$_POST['cancel_time_from'];
	$cancel_time_to=$_POST['cancel_time_to'];
	$major_fund=addslashes(implode("||||",$_POST['major_fund']));

	$major_comments=addslashes($_POST['major_comments']);

	if($appointment!=2 && $appointment!=3){
		$a_univ_exp= addslashes(implode("||||",$_POST['a_univ_exp']));
		$a_position_exp= addslashes(implode("||||",$_POST['a_position_exp']));
		$a_from_exp= addslashes(implode("||||",$_POST['a_from_exp']));
		$a_to_exp=addslashes(implode("||||",$_POST['a_to_exp']));

		$b_exp= addslashes(implode("||||",$_POST['b_exp']));
		$b_position_exp= addslashes(implode("||||",$_POST['b_position_exp']));
		$b_from_exp= addslashes(implode("||||",$_POST['b_from_exp']));
		$b_to_exp= addslashes(implode("||||",$_POST['b_to_exp']));

		$c1_univ_exp= addslashes(implode("||||",$_POST['c1_univ_exp']));
		$c1_position_exp= addslashes(implode("||||",$_POST['c1_position_exp']));
		$c1_from_exp= addslashes(implode("||||",$_POST['c1_from_exp']));
		$c1_to_exp= addslashes(implode("||||",$_POST['c1_to_exp']));
		$c2_univ_exp= addslashes(implode("||||",$_POST['c2_univ_exp']));
		$c2_position_exp= addslashes(implode("||||",$_POST['c2_position_exp']));
		$c2_from_exp= addslashes(implode("||||",$_POST['c2_from_exp']));
		$c2_to_exp= addslashes(implode("||||",$_POST['c2_to_exp']));
	}

	$total_exp=trim($_POST['total_exp']);
	$relevant_exp=trim($_POST['relevant_exp']);
	$all_other_exp=trim($_POST['all_other_exp']);
	$other_relevant_exp=trim($_POST['other_relevant_exp']);

	$prepared_by = trim(addslashes($_POST['prepared_by']));

	// Signatures
	$proj_director_name=trim(addslashes($_POST['proj_director_name']));
	$proj_director_email=$_POST['proj_director_email'];

	if($proj_director_name!="" && $proj_director_email!="")
		$proj_director=$proj_director_name."||".$proj_director_email;

	$budget_verification = $_POST['budget_verification'];
	$dept_head=$_POST['dept_head'];
	$dean=$_POST['dean'];

	$vice_president=$_POST['vice_president'];


	$officials=getOfficials();

	$major_dc_array = explode("||||",$major_dc);
	foreach($major_dc_array as $value){
		$dept_no_array = explode("-", $value);
		// 		if($dept_no_array[0] == 32 && ($dept_no_array[2]{0} >= 4 && $dept_no_array[2]{0} <= 6)) {
		if($dept_no_array[0] == 32) {
			$univ_research=$officials[0];
			$vp_research = $officials[1];
		}
	}

	if($appointment_for == 2){
		$grad_school=$officials[2]; 	// Graduate School | Dean of Graduate School
	}

	$comptroller_officer=$officials[3];

	$human_resources=$officials[4];

	if(is_numeric($college) && $college != 0) {
		$vice_president = $officials[5];
	}

	$president=$officials[6];

	// checking if the ARF end date exceeds the current academic year
	$major_time_to_details = explode("/", $major_time_to);
	$major_time_to_year = $major_time_to_details[2];
	$major_time_to_month = $major_time_to_details[0];

	$major_time_from_details = explode("/", $major_time_from);
	$major_time_from_year = $major_time_from_details[2];
	$major_time_from_month = $major_time_from_details[0];

	$academic_to_year = $major_time_from_year;
	if($major_time_from_month>6)
		$academic_to_year += 1;

	$count = 0;

	if($major_time_to_year >= $academic_to_year)
	{
		$count = $major_time_to_year - $academic_to_year;
		if($major_time_to_month>6)
			$count = $count+1;
		$major_time_to_temp = $major_time_to;
	}

	// cancelling the existing forms if there is an overlap.
	$query = "SELECT id,major_time_from,major_time_to FROM `appointment_request_form` WHERE email='" . $email . "' and (( str_to_date('" . $major_time_from . "','%m/%d/%Y') between str_to_date(`major_time_from`,'%m/%d/%Y') and str_to_date(`major_time_to`,'%m/%d/%Y')) OR (str_to_date('" . $major_time_to . "','%m/%d/%Y') between str_to_date(`major_time_from`,'%m/%d/%Y') and str_to_date(`major_time_to`,'%m/%d/%Y')) OR (str_to_date('" . $major_time_from . "','%m/%d/%Y') < str_to_date(`major_time_from`,'%m/%d/%Y') and str_to_date(`major_time_to`,'%m/%d/%Y') < str_to_date('" . $major_time_to . "','%m/%d/%Y')))";
	$result=mysql_query($query);
	$rows_num=mysql_num_rows($result);
	for($i=0;$i<$rows_num;$i++)
	{
		$cancel_from_date = $major_time_from;
		$cancel_to_date = $major_time_to;
		if(strtotime(mysql_result($result, $i, "major_time_from"))>strtotime($major_time_from))
		{
			$cancel_from_date = mysql_result($result, $i, "major_time_from");
		}
		if(strtotime(mysql_result($result, $i, "major_time_to"))<strtotime($major_time_to))
		{
			$cancel_to_date = mysql_result($result, $i, "major_time_to");
		}
		$query_update_cancel_time = "UPDATE `appointment_request_form` SET `cancel_time_from`='" . $cancel_from_date . "',`cancel_time_to`='" . $cancel_to_date . "' WHERE `id`=" . mysql_result($result, $i, "id");
		mysql_query($query_update_cancel_time);
	}

	// hash id
	$salt = $email . $owner . md5(uniqid(rand(),true));
	$session_id = hash('sha256',$salt);

	if(($_POST['preview']=="PREVIEW" || $_POST['save']=="SAVE AS DRAFT") && $ID==""){

	for($i=0;$count>=0;$count--,$i++)
	{
		if($count>0)
		{
			$major_time_to = "06/30/" . ($academic_to_year+$i);
			if($i>0)
				$major_time_from = "07/01/" . ($academic_to_year+($i-1));
		}
		else if ($count==0 && $i!=0)
		{
			$major_time_to = $major_time_to_temp;
			$major_time_from = "07/01/" . ($academic_to_year+($i-1));
		}

		// calculating base salary
		$base_sal_count = 0;
		$major_time_from_base_sal = 0.0;
		$major_time_to_base_sal = 0.0;
		$major_time_from_values = explode("/", $major_time_from);
		$major_time_to_values = explode("/", $major_time_to);

		// Pro-rated amounts
		$last_month_amount = 0.0;
		$first_month_amount = 0.0;
		$same_month_amount = 0.0;

		//major_time_warning_check
		if(!(isset($_POST['major_time_warning_check'])))
		{
			if($major_time_from_values[1]>1)
			{
				$base_sal_count++;
				if(isset($_POST['major_time_from_warning_check']) && $_POST['major_time_from_warning_check']=="yes")
				{
					$days_in_month = cal_days_in_month(CAL_GREGORIAN, $major_time_from_values[0], $major_time_from_values[2]);
					$major_time_from_base_sal = (floatval(str_replace(array('$',','), "", $monthly_sal))/$days_in_month)*($days_in_month-$major_time_from_values[1]+1);
					$major_time_from_base_sal = floor($major_time_from_base_sal*100)/100;
					$first_month_amount = $major_time_from_base_sal;
				}
				else
				{
					$major_time_from_base_sal = floatval(str_replace(array('$',','), "", $monthly_sal));
					$first_month_amount = $major_time_from_base_sal;
				}
			}

			$days_in_month = cal_days_in_month(CAL_GREGORIAN, $major_time_to_values[0], $major_time_to_values[2]);
			if(($major_time_to_values[1]<$days_in_month))
			{
				$base_sal_count++;
				if(isset($_POST['major_time_to_warning_check']) && $_POST['major_time_to_warning_check']=="yes")
				{
					$major_time_to_base_sal = (floatval(str_replace(array('$',','), "", $monthly_sal))/$days_in_month)*($major_time_to_values[1]);
					$last_month_amount = $major_time_to_base_sal;
				}
				else
				{
					$major_time_to_base_sal = floatval(str_replace(array('$',','), "", $monthly_sal));
					$last_month_amount = $major_time_to_base_sal;
				}
			}

			$base_sal = (($major_time_to_values[2]-$major_time_from_values[2])*12 + ($major_time_to_values[0]-$major_time_from_values[0]) + 1 - $base_sal_count) * (floatval(str_replace(array('$',','), "", $monthly_sal)));
			$base_sal = $base_sal + $major_time_from_base_sal +$major_time_to_base_sal;
			$base_sal = floor($base_sal*100)/100;

			$amt= "$" . number_format($base_sal,2) . "||||" . $monthly_sal;
		}
		else // If major to and from values are from same month
		{
			$same_month_amount = floatval(str_replace(array('$',','), "", $base_sal));
		}


		// Re-Calculating Source of funds table amounts (these amounts will change if form splits)

		for($j=0;$j<sizeof($_POST['major_amt']);$j++)
		{
			$major_amt_recalculated[$j] = "$" . number_format((floatval(str_replace(array('$',','), "", $monthly_sal)) * floatval(str_replace(array('%'), "", $_POST['major_perc'][$j])))/100,2);
			$major_fund_recalculated[$j] = "$" . number_format((floatval(str_replace(array('$',','), "", $base_sal)) * floatval(str_replace(array('%'), "", $_POST['major_perc'][$j])))/100,2);
		}

		$major_amt = implode("||||", $major_amt_recalculated);
		$major_fund = implode("||||", $major_fund_recalculated);

		$prorated_amounts = "$" . number_format($first_month_amount,2) . "||||" . "$" . number_format($last_month_amount,2) . "||||" . "$" . number_format($same_month_amount,2);

		$query="INSERT INTO `appointment_request_form`(`session_id`,
													`date`,
												   `appointment`,
												   `quarter`,
												   `appointment_for`,
												   `college`,
												   `position`,
												   `specific_position`,
												   `hours_per_week`,
												   `credentials`,
												   `lname`,
												   `fname`,
												   `mname`,
												   `street`,
												   `city`,
												   `zip`,
												   `date_effective`,
												   `email`,
												   `ssn`,
												   `dob`,
												   `sex`,
												   `marital`,
												   `race`,
												   `raceList`,
												   `nationality`,
												   `univ_doc`,
												   `years_doc`,
												   `univ_master`,
												   `years_master`,
												   `univ_bachelor`,
												   `years_bachelor`,
												   `dept`,
												   `rank`,
												   `supervisor`,
												   `higher_edu`,
												   `years_tech`,
												   `other`,
												   `exp`,
												   `req_sal`,
												   `amt`,
												   `prorated_amounts`,
												   `jobType`,
												   `salaryBasis`,
												   `other_sal`,
												   `replaces`,
												   `rtime`,
												   `salaryCharged`,
												   `retirement`,
												   `orp`,
												   `major_dc`,
												   `major_budget`,
												   `major_perc`,
												   `major_amt`,
												   `major_time_from`,
												   `major_time_to`,
												   `cancel_time_from`,
												   `cancel_time_to`,
												   `major_fund`,
												   `major_comments`,
												   `a_univ_exp`,
												   `a_position_exp`,
												   `a_from_exp`,
												   `a_to_exp`,
												   `b_exp`,
												   `b_position_exp`,
												   `b_from_exp`,
												   `b_to_exp`,
												   `c1_univ_exp`,
												   `c1_position_exp`,
												   `c1_from_exp`,
												   `c1_to_exp`,
												   `c2_univ_exp`,
												   `c2_position_exp`,
												   `c2_from_exp`,
												   `c2_to_exp`,
												   `total_exp`,
												   `relevant_exp`,
												   `all_other_exp`,
												   `other_relevant_exp`,
												   `prepared_by`,
												   `owner`,
												   `form_type`)
								VALUES('$session_id',
										'$date',
								       '$appointment',
									   '$quarter',
									   '$appointment_for',
									   '$college',
									   '$position',
									   '$specific_position',
									   '$hours_per_week',
									   '$credentials',
									   '$lname',
									   '$fname',
									   '$mname',
									   '$street',
									   '$city',
									   '$zip',
									   '$date_effective',
									   '$email',
									   '$ssn',
									   '$dob',
									   '$sex',
									   '$marital',
									   '$race',
									   '$raceList',
									   '$nationality',
									   '$univ_doc',
									   '$years_doc',
									   '$univ_master',
									   '$years_master',
									   '$univ_bachelor',
									   '$years_bachelor',
									   '$dept',
									   '$rank',
									   '$supervisor',
									   '$higher_edu',
									   '$years_tech',
									   '$other',
									   '$exp',
									   '$req_sal',
									   '$amt',
									   '$prorated_amounts',
									   '$replaces',
									   '$jobType',
									   '$salaryBasis',
									   '$other_sal',
									   '$rtime',
									   '$salaryCharged',
									   '$retirement',
									   '$orp',
									   '$major_dc',
									   '$major_budget',
									   '$major_perc',
									   '$major_amt',
									   '$major_time_from',
									   '$major_time_to',
									   '$cancel_time_from',
									   '$cancel_time_to',
									   '$major_fund',
									   '$major_comments',
									   '$a_univ_exp',
									   '$a_position_exp',
									   '$a_from_exp',
									   '$a_to_exp',
									   '$b_exp',
									   '$b_position_exp',
									   '$b_from_exp',
									   '$b_to_exp',
									   '$c1_univ_exp',
									   '$c1_position_exp',
									   '$c1_from_exp',
									   '$c1_to_exp',
									   '$c1_univ_exp',
									   '$c2_position_exp',
									   '$c2_from_exp',
									   '$c2_to_exp',
									   '$total_exp',
									   '$relevant_exp',
									   '$all_other_exp',
									   '$other_relevant_exp',
									   '$prepared_by',
									   '$username',
									   '$form_type')";

		if(mysql_query($query) or die(mysql_error())){
			$ID=mysql_insert_id();
			$arf_query="INSERT INTO `appointment_request_signatures`
													(`proj_director`,
													`budget_verification`,
													`dept_head`,
													`dean`,
													`univ_research`,
													`vp_research`,
													`grad_school`,
													`comptroller_officer`,
													`vice_president`,
													`vice_president_1`,
													`president`,
													`human_resources`,
													`form_id`,
													`form_session_id`)
								VALUES
									   ('$proj_director',
									    '$budget_verification',
										'$dept_head',
										'$dean',
										'$univ_research',
										'$vp_research',
										'$grad_school',
										'$comptroller_officer',
										'$vice_president',
										'$vice_president_1',
										'$president',
										'$human_resources',
										'$ID',
										'$session_id')";
			mysql_query($arf_query);
		}
		//echo $count;
		//echo $query;
	}
			if($form_type==1){
				// uploadFile($ID, 'attachment_file', 'attachment_file'); Upload file is not activated at the moment
				//uploadFile($ID, 'credentials', 'credentials');
				header("Location: arf_preview.php?ID=$session_id");
			}
			else if($form_type==2){
				//uploadFile($ID, 'credentials', 'credentials');
				header("Location: arf_form.php?ID=$session_id");
			}
	} // end of insert query

	else if(($_POST['preview']=="EDIT & PREVIEW" || $_POST['save']=="SAVE AS DRAFT") && ($ID!="")){
		//  Deleting the old records from both form and signature tables while updating
		if(!is_numeric($ID))
			$id = 'session_id';
		else
			$id = 'id';
		// Delete previous form and signatures for that form, if the new INSERT was successful.
		$query6 = "UPDATE `appointment_request_form` SET `form_status` = '2' WHERE " . $id ." = '$ID'";
		mysql_query($query6);
		// $query7 = "DELETE FROM `appointment_request_signatures` WHERE form_" . $id ." = '$ID'";
		// mysql_query($query7);
		// End Deletion

		for($i=0;$count>=0;$count--,$i++)
		{
			if($count>0)
			{
			$major_time_to = "06/30/" . ($academic_to_year+$i);
			if($i>0)
				$major_time_from = "07/01/" . ($academic_to_year+($i-1));
			}
			else if ($count==0 && $i!=0)
			{
			$major_time_to = $major_time_to_temp;
			$major_time_from = "07/01/" . ($academic_to_year+($i-1));
			}


			// calculating base salary
			$base_sal_count = 0;
			$major_time_from_base_sal = 0.0;
			$major_time_to_base_sal = 0.0;
			$major_time_from_values = explode("/", $major_time_from);
			$major_time_to_values = explode("/", $major_time_to);

			// Pro-rated amounts
			$last_month_amount = 0.0;
			$first_month_amount = 0.0;
			$same_month_amount = 0.0;

		if(!(isset($_POST['major_time_warning_check'])))
		{
			if($major_time_from_values[1]>1)
			{
				$base_sal_count++;
				if(isset($_POST['major_time_from_warning_check']) && $_POST['major_time_from_warning_check']=="yes")
				{
					$days_in_month = cal_days_in_month(CAL_GREGORIAN, $major_time_from_values[0], $major_time_from_values[2]);
					$major_time_from_base_sal = (floatval(str_replace(array('$',','), "", $monthly_sal))/$days_in_month)*($days_in_month-$major_time_from_values[1]+1);
					$major_time_from_base_sal = floor($major_time_from_base_sal*100)/100;
					$first_month_amount = $major_time_from_base_sal;
				}
				else
				{
					$major_time_from_base_sal = floatval(str_replace(array('$',','), "", $monthly_sal));
					$first_month_amount = $major_time_from_base_sal;
				}
			}

			$days_in_month = cal_days_in_month(CAL_GREGORIAN, $major_time_to_values[0], $major_time_to_values[2]);
			if(($major_time_to_values[1]<$days_in_month))
			{
				$base_sal_count++;
				if(isset($_POST['major_time_to_warning_check']) && $_POST['major_time_to_warning_check']=="yes")
				{
					$major_time_to_base_sal = (floatval(str_replace(array('$',','), "", $monthly_sal))/$days_in_month)*($major_time_to_values[1]);
					$last_month_amount = $major_time_to_base_sal;
				}
				else
				{
					$major_time_to_base_sal = floatval(str_replace(array('$',','), "", $monthly_sal));
					$last_month_amount = $major_time_to_base_sal;
				}
			}

			$base_sal = (($major_time_to_values[2]-$major_time_from_values[2])*12 + ($major_time_to_values[0]-$major_time_from_values[0]) + 1 - $base_sal_count) * (floatval(str_replace(array('$',','), "", $monthly_sal)));
			$base_sal = $base_sal + $major_time_from_base_sal +$major_time_to_base_sal;
			$base_sal = floor($base_sal*100)/100;

			$amt= "$" . number_format($base_sal,2) . "||||" . $monthly_sal;
		}
		else // If major to and from values are from same month
		{
			$same_month_amount = floatval(str_replace(array('$',','), "", $base_sal));
		}

		// Re-Calculating Source of funds table amounts (these amounts will change if form splits)

		for($j=0;$j<sizeof($_POST['major_amt']);$j++)
		{
			$major_amt_recalculated[$j] = "$" . number_format((floatval(str_replace(array('$',','), "", $monthly_sal)) * floatval(str_replace(array('%'), "", $_POST['major_perc'][$j])))/100,2);
			$major_fund_recalculated[$j] = "$" . number_format((floatval(str_replace(array('$',','), "", $base_sal)) * floatval(str_replace(array('%'), "", $_POST['major_perc'][$j])))/100,2);
		}

		$major_amt = implode("||||", $major_amt_recalculated);
		$major_fund = implode("||||", $major_fund_recalculated);

		// Pro-rated amounts
		$prorated_amounts = "$" . number_format($first_month_amount,2) . "||||" . "$" . number_format($last_month_amount,2) . "||||" . "$" . number_format($same_month_amount,2);
			/*if($i==0)
			{
				$query2="UPDATE `appointment_request_form` SET
															`session_id`='$session_id',
															`date`='$date',
															`appointment`='$appointment',
															`quarter`='$quarter',
															`appointment_for`='$appointment_for',
														    `college`='$college',
														    `position`='$position',
														    `hours_per_week` = '$hours_per_week',
														    `credentials`='$credentials',
															`lname`='$lname',
															`fname`='$fname',
															`mname`='$mname',
															`street`='$street',
															`city`='$city',
															`zip`='$zip',
															`date_effective`='$date_effective',
															`email`='$email',
															`ssn`='$ssn',
															`dob`='$dob',
															`sex`='$sex',
															`marital`='$marital',
															`race`='$race',
															`raceList`='$raceList',
															`nationality`='$nationality',
															`univ_doc`='$univ_doc',
															`years_doc`='$years_doc',
															`univ_master`='$univ_master',
															`years_master`='$years_master',
															`univ_bachelor`='$univ_bachelor',
															`years_bachelor`='$years_bachelor',
															`dept`='$dept',
															`rank`='$rank',
															`higher_edu`='$higher_edu',
															`years_tech`='$years_tech',
															`other`='$other',
															`exp`='$exp',
															`req_sal`='$req_sal',
															`amt`='$amt',
															`jobType`='$jobType',
															`salaryBasis`='$salaryBasis',
															`other_sal`='$other_sal',
															`replaces`='$replaces',
															`rtime`='$rtime',
															`salaryCharged`='$salaryCharged',
															`retirement`='$retirement',
															`orp`='$orp',
															`major_dc`='$major_dc',
															`major_budget`='$major_budget',
															`major_perc`='$major_perc',
															`major_amt`='$major_amt',
															`major_time_from`='$major_time_from',
															`major_time_to`='$major_time_to',
															`cancel_time_from`='$cancel_time_from',
															`cancel_time_to`='$cancel_time_to',
															`major_fund`='$major_fund',
															`major_comments`='$major_comments',
															`a_univ_exp`='$a_univ_exp',
															`a_position_exp`='$a_position_exp',
															`a_from_exp`='$a_from_exp',
															`a_to_exp`='$a_to_exp',
															`b_exp`='$b_exp',
															`b_position_exp`='$b_position_exp',
															`b_from_exp`='$b_from_exp',
															`b_to_exp`='$b_to_exp',
															`c1_univ_exp`='$c1_univ_exp',
															`c1_position_exp`='$c1_position_exp',
															`c1_from_exp`='$c1_from_exp',
															`c1_to_exp`='$c1_to_exp',
															`c2_univ_exp`='$c1_univ_exp',
															`c2_position_exp`='$c2_position_exp',
															`c2_from_exp`='$c2_from_exp',
															`c2_to_exp`='$c2_to_exp',
															`total_exp`='$total_exp',
															`relevant_exp`='$relevant_exp',
															`all_other_exp`='$all_other_exp',
															`other_relevant_exp`='$other_relevant_exp',
															form_type ='$form_type'
									WHERE " . $id . "='$ID'";

								if(mysql_query($query2) or die(mysql_error())){
									$arf_update="UPDATE `appointment_request_signatures` SET
														`proj_director`='$proj_director',
														`dept_head`='$dept_head',
														`dean`='$dean',
														`univ_research`='$univ_research',
														`vp_research` = '$vp_research',
			 											`grad_school`='$grad_school',
														`comptroller_officer`='$comptroller_officer',
														`vice_president`='$vice_president',
														`vice_president_1`='$vice_president_1',
			 											`president`='$president',
														`human_resources`='$human_resources'
												WHERE `form_id`='$ID'";
								}
								mysql_query($arf_update);
						}
						else
						{*/
							$query="INSERT INTO `appointment_request_form`(`session_id`,
								`date`,
								`appointment`,
								`quarter`,
								`appointment_for`,
								`college`,
								`position`,
								`specific_position`,
								`hours_per_week`,
								`credentials`,
								`lname`,
								`fname`,
								`mname`,
								`street`,
								`city`,
								`zip`,
								`date_effective`,
								`email`,
								`ssn`,
								`dob`,
								`sex`,
								`marital`,
								`race`,
								`raceList`,
								`nationality`,
								`univ_doc`,
								`years_doc`,
								`univ_master`,
								`years_master`,
								`univ_bachelor`,
								`years_bachelor`,
								`dept`,
								`rank`,
								`supervisor`,
								`higher_edu`,
								`years_tech`,
								`other`,
								`exp`,
								`req_sal`,
								`amt`,
								`prorated_amounts`,
								`jobType`,
								`salaryBasis`,
								`other_sal`,
								`replaces`,
								`rtime`,
								`salaryCharged`,
								`retirement`,
								`orp`,
								`major_dc`,
								`major_budget`,
								`major_perc`,
								`major_amt`,
								`major_time_from`,
								`major_time_to`,
								`cancel_time_from`,
								`cancel_time_to`,
								`major_fund`,
								`major_comments`,
								`a_univ_exp`,
								`a_position_exp`,
								`a_from_exp`,
								`a_to_exp`,
								`b_exp`,
								`b_position_exp`,
								`b_from_exp`,
								`b_to_exp`,
								`c1_univ_exp`,
								`c1_position_exp`,
								`c1_from_exp`,
								`c1_to_exp`,
								`c2_univ_exp`,
								`c2_position_exp`,
								`c2_from_exp`,
								`c2_to_exp`,
								`total_exp`,
								`relevant_exp`,
								`all_other_exp`,
								`other_relevant_exp`,
								`prepared_by`,
								`owner`,
								`form_type`)
								VALUES('$session_id',
								'$date',
								'$appointment',
								'$quarter',
								'$appointment_for',
								'$college',
								'$position',
								'$specific_position',
								'$hours_per_week',
								'$credentials',
								'$lname',
								'$fname',
								'$mname',
								'$street',
								'$city',
								'$zip',
								'$date_effective',
								'$email',
								'$ssn',
								'$dob',
								'$sex',
								'$marital',
								'$race',
								'$raceList',
								'$nationality',
								'$univ_doc',
								'$years_doc',
								'$univ_master',
								'$years_master',
								'$univ_bachelor',
								'$years_bachelor',
								'$dept',
								'$rank',
								'$supervisor',
								'$higher_edu',
								'$years_tech',
								'$other',
								'$exp',
								'$req_sal',
								'$amt',
								'$prorated_amounts',
								'$replaces',
								'$jobType',
								'$salaryBasis',
								'$other_sal',
								'$rtime',
								'$salaryCharged',
								'$retirement',
								'$orp',
								'$major_dc',
								'$major_budget',
								'$major_perc',
								'$major_amt',
								'$major_time_from',
								'$major_time_to',
								'$cancel_time_from',
								'$cancel_time_to',
								'$major_fund',
								'$major_comments',
								'$a_univ_exp',
								'$a_position_exp',
								'$a_from_exp',
								'$a_to_exp',
								'$b_exp',
								'$b_position_exp',
								'$b_from_exp',
								'$b_to_exp',
								'$c1_univ_exp',
								'$c1_position_exp',
								'$c1_from_exp',
								'$c1_to_exp',
								'$c1_univ_exp',
								'$c2_position_exp',
								'$c2_from_exp',
								'$c2_to_exp',
								'$total_exp',
								'$relevant_exp',
								'$all_other_exp',
								'$other_relevant_exp',
								'$prepared_by',
								'$username',
								'$form_type')";

							if(mysql_query($query)){
								$ID=mysql_insert_id();
								$arf_query="INSERT INTO `appointment_request_signatures`
								(`proj_director`,
								`budget_verification`,
								`dept_head`,
								`dean`,
								`univ_research`,
								`vp_research`,
								`grad_school`,
								`comptroller_officer`,
								`vice_president`,
								`vice_president_1`,
								`president`,
								`human_resources`,
								`form_id`,
								`form_session_id`)
								VALUES
								('$proj_director',
								'$budget_verification',
								'$dept_head',
								'$dean',
								'$univ_research',
								'$vp_research',
								'$grad_school',
								'$comptroller_officer',
								'$vice_president',
								'$vice_president_1',
								'$president',
								'$human_resources',
								'$ID',
								'$session_id')";
								mysql_query($arf_query);
						}
				/*}*/
		}

								if($form_type==1){
										// uploadFile($ID, 'attachment_file', 'attachment_file'); // File Upload is not activated at the moment
										//uploadFile($ID, 'credentials', 'credentials');
										header("Location: arf_preview.php?ID=$session_id");
									}
									else if($form_type==2){
										//uploadFile($ID, 'credentials', 'credentials');
										header("Location: arf_form.php?ID=$session_id");
									}
					}
	}

//$ID!="" && $ID>0 && is_numeric($ID)
if($ID!=""){
	$edit=1;
	//echo is_nan($ID);
	// Need to fetch using session_id if the page is other than arf.php
	if(!is_numeric($ID))
			$id = 'session_id';
	else
		$id = 'id';

	$query3="SELECT * FROM `appointment_request_form` WHERE " . $id . " = '$ID' order by id asc";

	//echo $query3;
	$result=mysql_query($query3);
	$rows=mysql_num_rows($result);
	//echo $rows;
	$i=0;
	while($i < 1){
		$owner=mysql_result($result, $i, "owner");						// ENC

		$appointment=mysql_result($result, $i, "appointment");
		$date=mysql_result($result, $i, "date");
		$d_date=explode("/", $date);
		$month=$d_date[0];
		$day=$d_date[1];
		$year=$d_date[2];
		$quarter = mysql_result($result, $i, "quarter");
		if($quarter != "")
			$quarter=explode(", ", $quarter);

		$appointment_for=mysql_result($result, $i, "appointment_for");
		$college=mysql_result($result, $i, "college");
		$position=mysql_result($result, $i, "position");
		$specific_position = mysql_result($result, $i, "specific_position");
		$hours_per_week=mysql_result($result, $i, "hours_per_week");
		$credentials=mysql_result($result, $i, "credentials");
		$lname=stripslashes(mysql_result($result, $i, "lname"));
		$fname=stripslashes(mysql_result($result, $i, "fname"));
		$mname=stripslashes(mysql_result($result, $i, "mname"));
		$street=stripslashes(mysql_result($result, $i, "street"));
		$city=stripslashes(mysql_result($result, $i, "city"));
		$zip=mysql_result($result, $i, "zip");
		$date_effective=mysql_result($result, $i, "date_effective");
		$effective_date=explode("/", $date_effective);
		$email=mysql_result($result, $i, "email");
		$month_effective=$effective_date[0];
		$day_effective=$effective_date[1];
		$year_effective=$effective_date[2];
		$ssn=trim($enc->decrypt(urldecode(mysql_result($result, $i, "ssn")), md5($owner)));
		$dob=mysql_result($result, $i, "dob");
		$dob_date=explode("/", $dob);
		$month_dob=$dob_date[0];
		$day_dob=$dob_date[1];
		$year_dob=$dob_date[2];
		$sex=mysql_result($result, $i, "sex");
		$marital=mysql_result($result, $i, "marital");
		$race=mysql_result($result, $i, "race");
		$raceList=mysql_result($result, $i, "raceList");
		$nationality=mysql_result($result, $i, "nationality");
		$univ_doc=mysql_result($result, $i, "univ_doc");
		$years_doc=mysql_result($result, $i, "years_doc");
		$univ_master=mysql_result($result, $i, "univ_master");
		$years_master=mysql_result($result, $i, "years_master");
		$univ_bachelor=mysql_result($result, $i, "univ_bachelor");
		$years_bachelor=mysql_result($result, $i, "years_bachelor");
		$dept=mysql_result($result, $i, "dept");
		$rank=stripslashes(mysql_result($result, $i, "rank"));

		$supervisor = explode("||", stripslashes(mysql_result($result, $i, "supervisor")));
		$supervisor_name = $supervisor[0];
		$supervisor_email = $supervisor[1];

		$higher_edu=mysql_result($result, $i, "higher_edu");
		$years_tech=mysql_result($result, $i, "years_tech");
		$other=mysql_result($result, $i, "other");
		$exp=mysql_result($result, $i, "exp");
		$req_sal=mysql_result($result, $i, "req_sal");

		$query4 = "SELECT min(str_to_date(`major_time_from`,'%m/%d/%Y')) as major_time_from, max(str_to_date(`major_time_to`,'%m/%d/%Y')) as major_time_to FROM `appointment_request_form` WHERE " . $id . " = '$ID'";
		$result4 = mysql_query($query4);

		$major_time_from_values = explode("-", mysql_result($result4, $i, "major_time_from"));
		$major_time_from = $major_time_from_values[1] . "/" . $major_time_from_values[2] . "/" . $major_time_from_values[0];
		//$major_times_from=explode("||||", $major_time_from);
		$major_time_to_values = explode("-", mysql_result($result4, $i, "major_time_to"));
		$major_time_to = $major_time_to_values[1] . "/" . $major_time_to_values[2] . "/" . $major_time_to_values[0];
		//$major_times_to=explode("||||", $major_time_to);

		$monthly_sal = explode("||||", mysql_result($result, $i, "amt"))[1];
		$base_sal = 0.00;

		// Getting pro-rated amounts
		$prorated_amounts = explode("||||", mysql_result($result, $i, "prorated_amounts"));

		// caluclating amounts for arf_preview (Have to sum up all the splitted ARFs) and arf pages
		while($j<$rows)
		{
			// if form splits, we need to get the last month pro-rated information to dispay on preview and arf_form.php pages
			if($j>0) // if there are more than one form
			{
				// if same month pro-rated is set, that does mean that the form has never been splitted,
				// if the first month pro-rated is set, it will be applied for the first form for sure,
				// So we just need to take care of last month pro-rated check box
				// index 1 is last month pro-rated amount
				$prorated_amounts[1] = explode("||||", mysql_result($result, $j, "prorated_amounts"))[1];
			}

			$base_sal=$base_sal + floatval(str_replace(array('$',','), "", explode("||||", mysql_result($result, $j, "amt"))[0]));
			//$major_amt_temp=mysql_result($result, $j, "major_amt");
			//$major_amts_temp=explode("||||", $major_amt_temp);
			$major_fund_temp=mysql_result($result, $j, "major_fund");
			$major_funds_temp=explode("||||", $major_fund_temp);

			// Summing up the amounts for total fund for displaying preview page
			for($k=0;$k<sizeof($major_funds_temp);$k++)
			{
				//$major_amts[$k] = $major_amts[$k] + floatval(str_replace(array('$',','), "", $major_amts_temp[$k]));
				$major_funds[$k] = $major_funds[$k] + floatval(str_replace(array('$',','), "", $major_funds_temp[$k]));
			}
			$j++;
		}

		$major_amt=mysql_result($result, $i, "major_amt");
		$major_amts=explode("||||", $major_amt);
		$amt = explode("||||", ("$" . number_format($base_sal,2) . "||||" . $monthly_sal));
		$replaces=mysql_result($result, $i, "replaces");
		$jobType=mysql_result($result, $i, "jobType");
		$rtime=mysql_result($result, $i, "rtime");
		$salaryCharged=mysql_result($result, $i, "salaryCharged");
		$salaryBasis=mysql_result($result, $i, "salaryBasis");
		$other_sal=mysql_result($result, $i, "other_sal");
		$retirement=mysql_result($result, $i, "retirement");
		$orp=mysql_result($result, $i, "orp");
		$id_files=$ID*254; 											// ID for file links
		$attachment_file=mysql_result($result, $i, "attachment_file");
		$major_dc=mysql_result($result, $i, "major_dc");
		$major_dcs=explode("||||", $major_dc);
		$major_budget=mysql_result($result, $i, "major_budget");
		$major_budgets=explode("||||", $major_budget);
		$major_perc=mysql_result($result, $i, "major_perc");
		$major_percs=explode("||||", $major_perc);

		$cancel_time_from=mysql_result($result, $i, "cancel_time_from");
		$cancel_time_to=mysql_result($result, $i, "cancel_time_to");
		$major_comments=stripslashes(mysql_result($result, $i, "major_comments"));
		$a_univ_exp=mysql_result($result, $i, "a_univ_exp");
		$a_univ_exps=explode("||||", $a_univ_exp);
		$a_position_exp=mysql_result($result, $i, "a_position_exp");
		$a_position_exps=explode("||||", $a_position_exp);
		$a_from_exp=mysql_result($result, $i, "a_from_exp");
		$a_from_exps=explode("||||", $a_from_exp);
		$a_to_exp=mysql_result($result, $i, "a_to_exp");
		$a_to_exps=explode("||||", $a_to_exp);
		$b_exp=mysql_result($result, $i, "b_exp");
		$b_exps=explode("||||", $b_exp);
		$b_position_exp=mysql_result($result, $i, "b_position_exp");
		$b_position_exps=explode("||||", $b_position_exp);
		$b_from_exp=mysql_result($result, $i, "b_from_exp");
		$b_from_exps=explode("||||", $b_from_exp);
		$b_to_exp=mysql_result($result, $i, "b_to_exp");
		$b_to_exps=explode("||||", $b_to_exp);

		$c1_univ_exp=mysql_result($result, $i, "c1_univ_exp");
		$c1_univ_exps=explode("||||", $c1_univ_exp);
		$c1_position_exp=mysql_result($result, $i, "c1_position_exp");
		$c1_position_exps=explode("||||", $c1_position_exp);
		$c1_from_exp=mysql_result($result, $i, "c1_from_exp");
		$c1_from_exps=explode("||||", $c1_from_exp);
		$c1_to_exp=mysql_result($result, $i, "c1_to_exp");
		$c1_to_exps=explode("||||", $c1_to_exp);

		$c2_univ_exp=mysql_result($result, $i, "c2_univ_exp");
		$c2_univ_exps=explode("||||", $c2_univ_exp);
		$c2_position_exp=mysql_result($result, $i, "c2_position_exp");
		$c2_position_exps=explode("||||", $c2_position_exp);
		$c2_from_exp=mysql_result($result, $i, "c2_from_exp");
		$c2_from_exps=explode("||||", $c2_from_exp);
		$c2_to_exp=mysql_result($result, $i, "c2_to_exp");
		$c2_to_exps=explode("||||", $c2_to_exp);

		$total_exp=mysql_result($result, $i, "total_exp");
		$relevant_exp=mysql_result($result, $i, "relevant_exp");
		$all_other_exp=mysql_result($result, $i, "all_other_exp");
		$other_relevant_exp=mysql_result($result, $i, "other_relevant_exp");
		$total_exp_grand=$total_exp+$relevant_exp+$all_other_exp+$other_relevant_exp;

		$prepared_by = stripslashes(mysql_result($result, $i, "prepared_by"));

		$remarks_comments=mysql_result($result, $i, "remarks_comments");
		$form_status=mysql_result($result, $i, "form_status");
		$i++;
	}

	if(is_numeric($ID))
		$id = 'form_id';
	else
		$id = 'form_session_id';


	$arf_preview="SELECT * FROM `appointment_request_signatures` WHERE `" . $id ."`='$ID'";

	$result=mysql_query($arf_preview);
	$rows_sig=mysql_num_rows($result);

	//echo $rows_sig;
	$i=0;
	$ID = mysql_result($result, $i, "form_id");

	while($i<$rows_sig){
		$proj_director=mysql_result($result, $i, "proj_director");
		$proj_director=explode("||", $proj_director);
		$proj_director_name = $proj_director[0];
		$proj_director_email = $proj_director[1];
		$budget_verification = mysql_result($result, $i, "budget_verification");
		$dept_head=mysql_result($result, $i, "dept_head");
		$dean=mysql_result($result, $i, "dean");
		$univ_research=mysql_result($result, $i, "univ_research");
		$vp_research=mysql_result($result, $i, "vp_research");
		$grad_school=mysql_result($result, $i, "grad_school");
		$comptroller_officer=mysql_result($result, $i, "comptroller_officer");
		$vice_president=mysql_result($result, $i, "vice_president");
		$vice_president_1=mysql_result($result, $i, "vice_president_1");
		$president=mysql_result($result, $i, "president");
		$human_resources=mysql_result($result, $i, "human_resources");
		$i++;
	}
}

// Message to a PI from officials, requesting changes/modifications/revisions/...
if(trim($_POST['message_pi'])!=""){
	$ip=$_SERVER['REMOTE_ADDR'];
	$message=trim($_POST['message_pi']);

	$all_emails=trim($_POST['message_emails']); // Including Post and Pre-Award Coordinators. Update when personnel changes.

	//$receiver_name=$prepared_by;
	//$receiver_email=$owner."@latech.edu";
	$receiver_name=$owner;
	$receiver_email=$owner."@latech.edu";

	$subject="Request for Appointment Request Form Revision";
	$from="noreply-HR@latech.edu";

	$email_message="Hello $receiver_name,

The user \"$username\" sent you a message regarding an Appointment Request Form you initiated. The text of the message is given below.

------------------------------------------

$message
------------------------------------------

You can access this form for revision here: https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";

	$headers = "From: $from"."\r\n".
			   "CC: $all_emails"."\r\n".
			   "Reply-To: $from"."\r\n".
			   "X-Mailer: PHP/".phpversion();

	if(mail($receiver_email, $subject, $email_message, $headers)){
		$pi_messaged=1;

		// Appending into Remarks/Comments
		$query="SELECT `remarks_comments` FROM `appointment_request_form` WHERE `id`='$ID'";

		$replace=array("\n\n", "[[");
		$replace_with=array("</em></p><p>", "<br><em style=\"font-size: 8pt; color: #444\">&emsp;&#8212; ");

		$result=mysql_query($query);
		$rows=mysql_num_rows($result);

		$msg_db=stripslashes(mysql_result($result, 0, 'remarks_comments'));

		$message=$message."\n[[".$username." | Revision Request | ".date("M j, Y H:i:s", time())."\n\n".$msg_db; // Appending new msg with old msg for remarks/comments

		$remarks_comments=$message; // Update Remarks on Form

		$query="UPDATE `appointment_request_form` SET `remarks_comments`='".addslashes($message)."' WHERE id='$ID'";
		mysql_query($query) or die(mysql_error());
		// End appending
	}
}
// End ask for revision

?>
