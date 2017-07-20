<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();

$db=new SQL;
$db->connect();

$keyword=trim($_GET['q']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><? if($keyword!=""){ echo $keyword." - Search Results"; } else{ echo "Search"; } ?> - SCSU</title>
    <base href="https://forms.latech.edu/ARF/" />
    <link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
    <link rel="stylesheet" type="text/css" href="resources/style/acc_style.css" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
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

            <?
				if($keyword!=""){
					$query_condition=" WHERE `form_status`!='2' AND MATCH(`lname`, `fname`, `mname`, `owner`, `dept`) AGAINST('+".addslashes($keyword)."' IN BOOLEAN MODE)"; // Do not selected deleted forms
				}

            	if($keyword!=""){
            		$query="SELECT id, `lname`, `fname`, `mname`,`dept`, `date_effective`, `amt`, `owner`, `timestamp` FROM `appointment_request_form` $query_condition ORDER BY `Timestamp` DESC";
					$result=mysql_query($query);
					$rows=mysql_num_rows($result);
				}
			?>
            <?
            	if($rows>1){ $plural="s"; } if($keyword!=""){ echo "<p><strong>Found $rows result$plural for \"$keyword\"</strong></p>"; } ?>
            <?
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
						$owner=mysql_result($result, $i, "owner");
	
						$name=mysql_result($result, $i, "fname")." ".mysql_result($result, $i, "lname");

						if($j%2!=0)
							$style=" style=\"background: #F1F1F1\"";
						else
							$style="";

						$status=checkFormStatus($id);

						if($status=="Finalized")
							$status="<font color='#006600'>Finalized</font>";
						else
							$status="<font color='#CC0000'>$status</font>";
	
						if($j==0)
							echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" id=\"data_table\"><tr><th width=\"6%\">ARF#</th><th width=\"20%\">Department</th><th width=\"20%\">Name</th><th width=\"14%\">Date Effective</th><th width=\"15%\">Amt. to be Paid</th><th width=\"15%\">Date Initiated</th><th width=\"10%\">Status</th></tr>";

						echo "<tr$style><td><a href=\"arf.php?ID=$id\">$id</a></td><td>$dept</td><td>$name</td><td>$date_effective</td><td>$amt</td><td>$timestamp</td><td>$status</td></tr>";

						$j++;
					}
					$i++;
				}

				if($form_exists==1)
					echo "</table>";
				else if($keyword!="")
					echo "<p>Sorry, the system was unable to find any form based on the keywords you entered. Please try again with a different keyword.</p>";
			?>

			<? if($rows>0){ // Do not show status meanings when no form found ?>
                <p style="border-top: 1px dotted #AAA; margin-bottom: 20px; padding-top: 10px; font-size: 8pt">
                    <strong><sup>*</sup> Status Codes:</strong><br />
                    <strong>1</strong>: The form has been sent for signatues, but Project Director, Department Head, Dean, or University Research has not signed.<br />
                    <strong>2</strong>: The VP of Research has not signed.<br />
                    <strong>3</strong>: Graduate School has not signed.<br />
                    <strong>4</strong>: Comptroller has not signed.<br />
                    <strong>5</strong>: Vice President has not signed.<br />
                    <strong>6</strong>: The President has not signed.<br />
                    <strong>7</strong>: The Human Resources office has not signed.<br />
                    <strong>Finalized</strong>: Everyone has signed and the Appointment Request Form has been finalized.
                </p>
            <? } ?>
            </td>
		</tr>
    </table>
    </div>
    
<? include('../includes/footer.php'); ?>