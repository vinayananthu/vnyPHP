<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();
$username=$auth->getUser();

$db=new SQL;
$db->connect();

// Delete request has to come from "sign_form.php"
if(is_numeric($_GET['del']) && isAdmin($username) && (substr_count($_SERVER['HTTP_REFERER'], "arf.php")==1 || substr_count($_SERVER['HTTP_REFERER'], "arf_preview.php")==1)){
	if(deleteForm($_GET['del'])==1){
		$deleted=1;
	}
	
}


// Holding and Unholding the ARF based on flags.
if(is_numeric($_GET['flag']) && isAdmin($username)){
	$query="UPDATE `appointment_request_form` SET `flag`=IF(`flag`=1, 0, 1) WHERE `id`='".$_GET['flag']."'";	// Toggle the flag
	$result=mysql_query($query);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Forms Status - Appointment Request Form- SCSU</title>
    <base href="https://forms.latech.edu/ARF/" />
    <link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
    <link rel="stylesheet" type="text/css" href="resources/style/acc_style.css" />
	<script type="text/javascript" language="javascript" src="https://forms.latech.edu/routing/res/table_sort.js"></script>

	<script>
	function flagForm(id, current_flag){
		var flag;
		if(current_flag==1)
			var should_flag=confirm("Are you sure you want to put this ARF on hold?");
		else if(current_flag==2)
			var should_flag=confirm("Are you sure you want to unhold this ARF?");
		if (should_flag==true) {
			document.location="/ARF/admin/forms_status.php?flag="+id;
		}
	}
    </script>

    <style>
	.sortable th{
		cursor: pointer
	}
	.sortable th:hover{
		background: #333
	}
	.sortable tr:nth-child(even) {
    	background-color: #F1F1F1;
	}
	</style>
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
            	<? if($deleted==1){ // Admins can delete ?>
					<div id="success_notice">The form has been deleted successfully.</div>
                <? } ?>
				
            	<p><strong>Current Forms Status</strong></p>
            	<?
            	$query="SELECT id, `lname`, `fname`, `mname`, `dept`, `date_effective`, `amt`, `owner`, `timestamp`, `flag` FROM `appointment_request_form` WHERE `form_status`='1' ORDER BY `timestamp` DESC";
	
				$result=mysql_query($query);
				$rows=mysql_num_rows($result);
				
				$i=0;
				$j=0;

				while($i<$rows){
					
					$id=mysql_result($result, $i, 'id');

					if($auth->authorizeView($id) || isAdmin($username)){

						$form_exists=1;

						$dept=selectDepartment(mysql_result($result, $i, 'dept'));
						$date_effective=mysql_result($result, $i, 'date_effective');
						$amt=explode("||||", mysql_result($result, $i, 'amt'));
						$amt=$amt[1];
						$timestamp=mysql_result($result, $i, 'timestamp');
						$timestamp=date("m-d-Y", strtotime($timestamp));
						$owner= mysql_result($result, $i, "owner");
						$flag = mysql_result($result, $i, "flag");
	
						$name=mysql_result($result, $i, "fname")." ".mysql_result($result, $i, "lname");

						$status=checkFormStatus($id);

						if($status=="Finalized")
							$status="<font color='#006600'>Finalized</font>";
						else
							$status="<font color='#CC0000'>$status</font>";


						if($flag==1){
							$image="../../travel_authorization/resources/images/ico_flag_on.png";
							$jscript="2";
							$condition="Unhold";
						}
						else if($flag==0){
							$image="../../travel_authorization/resources/images/ico_flag_off.png";
							$jscript="1";
							$condition="Hold";
						}
						

						// If admin, append a new column
						if(isAdmin($username)) {
							$action_column = "<th width=\"4%\">Action</th>";
							$extra_column = "<td><img src=\"$image\" title=\"$condition this form\" onclick=\"flagForm('$id', '$jscript')\" style=\"cursor: pointer\" /></td>";
						}

						if($j==0){ ?>
                        <p style="border-top: 1px dotted #AAA; margin-bottom: 20px; padding-top: 10px; font-size: 8pt">
                            <strong><sup>*</sup> Status Codes:</strong><br />
                            <strong>1</strong>: Sent for signatues, but Project Director, Budget Verification (1.1), Department Head (1.2), Dean (1.3), or University Research (1.4) has not signed.<br />
                            <strong>2</strong>: The VP of Research has not signed.<br />
                            <strong>3</strong>: Graduate School has not signed.<br />
                            <strong>4</strong>: Comptroller has not signed.<br />
                            <strong>5</strong>: Vice President has not signed.<br />
                            <strong>6</strong>: The President has not signed.<br />
                            <strong>7</strong>: The Human Resources office has not signed.<br />
                            <strong>Finalized</strong>: Everyone has signed and the Appointment Request Form has been finalized.
                        </p>
                        <?
							echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" id=\"data_table\" class=\"sortable\"><tr><th width=\"6%\">ARF#</th><th width=\"20%\">Department</th><th width=\"20%\">Name</th><th width=\"14%\">Date Effective</th><th width=\"15%\">Amt. to be Paid</th><th width=\"15%\">Date Initiated</th><th width=\"10%\">Status</th>$action_column</tr>";
						}

						echo "<tr$style><td><a href=\"arf.php?ID=$id\">$id</a></td><td>$dept</td><td>$name</td><td>$date_effective</td><td>$amt</td><td>$timestamp</td><td>$status</td>$extra_column</tr>";

						$j++;
					}
					$i++;
				}

				if($form_exists==1)
					echo "</table>";
				else
					echo "<p>There is no form in the system that you can check a status of.</p>";

			?>
            </td>
		</tr>
    </table>
    </div>
 
<? include('../includes/footer.php'); ?>