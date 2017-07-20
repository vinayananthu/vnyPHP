<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();

$db=new SQL;
$db->connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Forms to Sign - Appointment Request Form - SCSU</title>
    <base href="https://forms.latech.edu/ARF/" />
    <link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
    <link rel="stylesheet" type="text/css" href="resources/style/acc_style.css" />
</head>

<body>
<center>
	<div id="t_logo"></div>

	<div id="m_body">
	<table cellpadding="0" cellspacing="3" width="100%" id="m_table">
    	<tr valign="top">
        	<td width="20%" id="l_menu">
				<? include('../includes/acc_left_menu.php'); ?>
            </td>
            <td>

            	<p><strong>Forms to Sign</strong></p>
            	<?
            	$user=$auth->getUser();

				$User_ID=retrieveUserIDfromUsername("$user");

				$all_user_ids=array(); // All user IDs of the person logged in, including ID's of people logged in person is a substitute signer of

				$all_user_ids[] = $user;

				if($User_ID!="")
					$all_user_ids[]=$User_ID;

				$query="SELECT `added_by` FROM `appointment_request_substitute_signer` WHERE `email` LIKE '$user@%' AND `status`='1'";
				$result=mysql_query($query);
				$rows=mysql_num_rows($result); // Check the number of rows the person has been assigned a substitute signer
				$query="";

				// If the person has been added as the Substitute Signer, search the database using Parent's ID
				if($rows > 0){
					for($i=0; $i<$rows; $i++){
						$added_by=mysql_result($result, $i, "added_by");
						$User_ID_from_sub=retrieveUserIDfromUsername("$added_by"); // Get userID from username obtained from `substitute_signer` table

						if($i==0 && $User_ID==""){
							$User_ID=$User_ID_from_sub; // If $User_ID is empty... needed for first OR set in query
						}

						$all_user_ids[]=$User_ID_from_sub; // Array to store all IDs

						$query.="(`proj_director` LIKE '$User_ID_from_sub%' OR
								 `budget_verification` LIKE '$User_ID_from_sub%' OR 
								 `dept_head` LIKE '$User_ID_from_sub%' OR
								 `univ_research` LIKE '$User_ID_from_sub%' OR
								 `vp_research` LIKE '$User_ID_from_sub%' OR
								 `dean` LIKE '$User_ID_from_sub%' OR
								 `vice_president` LIKE '$User_ID_from_sub%' OR
								 `president` LIKE '$User_ID_from_sub%' OR
								 `comptroller_officer` LIKE '$User_ID_from_sub%' OR
								 `human_resources` LIKE '$User_ID_from_sub%') OR "; // Append this into actual query; depends on how many times a person has been assigned as a substitute signer
					}
				}

				if($User_ID!="" || $User_ID!=0){ // If user_id is exists
					$user_id_query="(`proj_director` LIKE '$User_ID%' OR
															 `budget_verification` LIKE '$User_ID%' OR
															 `dept_head` LIKE '$User_ID%' OR
															 `univ_research` LIKE '$User_ID%' OR
															 `vp_research` LIKE '$User_ID%' OR
															 `dean` LIKE '$User_ID%' OR
															 `vice_president` LIKE '$User_ID%' OR
															 `president` LIKE '$User_ID%' OR
															 `comptroller_officer` LIKE '$User_ID%' OR
															 `human_resources` LIKE '$User_ID%')";
				}

				// Query for Username | Always run, for people who are in signature fields with e-mails, not ID.	
				$user_id_query_users="(`proj_director` LIKE '%||$user@%' OR
															 `budget_verification` LIKE '%||$user@%' OR 
															 `dept_head` LIKE '%||$user@%' OR
															 `univ_research` LIKE '%||$user@%' OR
															 `vp_research` LIKE '%||$user@%' OR
															 `dean` LIKE '%||$user@%' OR
															 `vice_president` LIKE '%||$user@%' OR
															 `president` LIKE '%||$user@%' OR
															 `comptroller_officer` LIKE '%||$user@%' OR
															 `human_resources` LIKE '%||$user@%')";

				if($user_id_query != "")
					$query_bulk .= $user_id_query . " OR ";
				if($user_id_query_users != "")
					$query_bulk .= $user_id_query_users . " OR ";
				if($query != "")
					$query_bulk .= $query . "";

				$query_bulk = substr($query_bulk, 0, strlen($query_bulk) - 4);
				
				if($User_ID!="" || $User_ID!=0 || $user!=""){ // If user_id is exists
					$query="SELECT * FROM `appointment_request_signatures` t1 WHERE $query_bulk AND
															 t1.form_id IN (SELECT t2.id FROM `appointment_request_form` t2
															 WHERE t2.id=t1.form_id AND t2.form_status='1')
															 ORDER BY t1.form_id DESC";
					
					echo $query;
					
					$result=mysql_query($query) or die(mysql_error());
					
					$rows=mysql_num_rows($result);
				}
				else
					$rows=0;

				$i=0;

				$signedStatus=array();

				while($i < $rows){ // loop through each selected signature row
					$form_id=mysql_result($result, $i, 'form_id');

					$roles=array("proj_director", "dept_head", "dean", "univ_research", "vp_research", "comptroller_officer", "vice_president", "president", "human_resources");

					$signed=0;

					for($j=0; $j<sizeof($roles); $j++){ // Loop through each of the roles to ensure all roles have been signed by this particular person
						$role=mysql_result($result, $i, $roles[$j]);

						if(substr_count($role, "||.||") > 0) {
							$role=substr($role, 0, strpos($role, "||.||"));

							if(in_array($role, $all_user_ids) || userExistsInArray($role, $all_user_ids))
								$signed=1;
						}
						else
							$signed=0;

						if($role!='' && (in_array($role, $all_user_ids) || userExistsInArray($role, $all_user_ids))){
							if($signed==1)
								$signedStatus[$i]="Signed";
							else{
								$signedStatus[$i]="Not Signed";
								break;
							}
						}
					}

            		$query="SELECT id, `lname`, `fname`, `mname`, `dept`, `date_effective`, `amt`, `owner`, `timestamp` FROM `appointment_request_form` WHERE `id`='$form_id' AND `form_status`='1'";
					$result_forms=mysql_query($query);
					$rows_forms=mysql_num_rows($result_forms);

					if($i==0 && $rows_forms > 0) {
						echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" id=\"data_table\"><tr><th width=\"6%\">ARF#</th><th width=\"20%\">Department</th><th width=\"20%\">Name</th><th width=\"14%\">Date Effective</th><th width=\"15%\">Amt. to be Paid</th><th width=\"15%\">Date Initiated</th><th width=\"10%\">Status</th></tr>";

						$id=mysql_result($result_forms, $i, 'id');
						$dept=selectDepartment(mysql_result($result_forms, $i, 'dept'));
						$date_effective=mysql_result($result_forms, $i, 'date_effective');
						$amt=explode("||||", mysql_result($result_forms, $i, 'amt'));
						$amt=$amt[1];
						$timestamp=mysql_result($result_forms, $i, 'timestamp');
						$timestamp=date("m-d-Y", strtotime($timestamp));
						$owner=mysql_result($result_forms, $i, "owner");
						
						$name=mysql_result($result_forms, $i, "fname")." ".mysql_result($result_forms, $i, "lname");

						if($i%2!=0)
							$style=" style=\"background: #F1F1F1\"";
						else
							$style="";

						if($signedStatus[$i]=="Signed")
							$signed_style=" style=\"color: #4a9518\"";
						else
							$signed_style=" style=\"color: #e32525\"";

						if($id != "")		// When ID is blank, don't show it.
							echo "<tr$style><td><a href=\"arf.php?ID=$id\">$id</a></td><td>$dept</td><td>$name</td><td>$date_effective</td><td>$amt</td><td>$timestamp</td><td$signed_style>$signedStatus[$i]</td></tr>";

					}
					$i++;
				}
				if($rows_forms > 0) // if signature exists, display close table.
					echo "</table>";
				else
					echo "<p>There is no form in the system that requires your authorization.</p>";
			?>
            </td>
		</tr>
    </table>
    </div>
    <? include('../includes/footer.php'); ?>