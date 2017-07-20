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
	<title>Your Forms - Appointment Request Form - SCSU</title>
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
            	<p><strong>Your Forms</strong></p>
            <?	$user=$auth->getUser();
            	$query="SELECT id, `lname`, `fname`, `mname`, `dept`, `date_effective`, `amt`, `owner`,`form_type`, `timestamp` FROM `appointment_request_form` WHERE `form_status`!='2' AND (`owner` LIKE '%$user%') ORDER BY `timestamp` DESC"; // Check if the traveler is listed or is owner of form, eliminate deleted forms
				$result=mysql_query($query);
				$rows=mysql_num_rows($result);
				if($rows==0)
					echo "<p>You have not initiated any form yet. If you want to start a new Appointment Request Form, you can do so <a href=\"arf_form.php\">here</a>.</p>";

				$i=0;
				while($i<$rows){
					$id=mysql_result($result, $i, 'id');

					$dept= mysql_result($result, $i, 'dept');
		           	$dept_array = Array("PRESIDENT'S OFFICE - 009","ACADEMIC AFFAIRS - 010","ADMINISTRATIVE AFFAIRS - 012","STUDENT AFFAIRS - 014","APPLIED &amp; NATURAL SCIENCES � ADM. - 015","APPLIED &amp; NATUAL SCIENCES - 016","HEALTH INFORMATION - 017","LIBERAL ARTS - 018","INTERNAL AUDIT - 019","COLLEGE OF BUSINESS - 020","EDUCATION - 022","ENGINEERING - 024","HUMAN ECOLOGY - 026","UNIVERSITY RESEARCH - 027","GRADUATE SCHOOL - 028","CONTINUING EDUCATION - 029","AFROTC - 030","BARKSDALE - 031","BOOKSTORE (BARNES &amp; NOBLE) - 032","BUILDINGS &amp; GROUNDS - 034","PURCHASING - 036","TELECOMMUNICATIONS - 038","COMPTROLLER - 040","COMPUTING CENTER - 042","ENVIRONMENTAL - 046","ATHLETICS - 048","HUMAN RESOURCES - 052","LIBRARY - 054","NEWS BUREAU - 058","UNIVERSITY POLICE - 060","UNIVERSITY ADVANCEMENT - 062","POST OFFICE/ OFFICE SVCS. - 064","PROPERTY - 066","REGISTRAR - 068","FINANCIAL AID - 070","BIOLOGICAL SCIENCES - 080","ADMISSIONS/ ENROLLMENT MANAGEMENT - 082","NURSING - 086","SUPERVISING TEACHERS (EDUCATION) - 092");
					$dept = $dept_array[$dept - 1];

					$date_effective=mysql_result($result, $i, 'date_effective');
					$amt=explode("||||", mysql_result($result, $i, 'amt'));
					$amt=$amt[1];
					$timestamp=mysql_result($result, $i, 'timestamp');
					$timestamp=date("m-d-Y", strtotime($timestamp));
					$owner=mysql_result($result, $i, "owner");

					$name=mysql_result($result, $i, "fname")." ".mysql_result($result, $i, "lname");
					$form_type=mysql_result($result, $i, 'form_type');
					
					if($form_type=="1") {
						$value="<font color='#006600'>Completed</font>";
						$link_addon = "";
					}
					else if($form_type=="2") {
						$value="<font color='#CC0000'><em>In progress...</em></font>";
						$link_addon = "_form";
					}

					if($i%2!=0)
						$style=" style=\"background: #F1F1F1\"";
					else
						$style="";


					if($i==0)
						echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" id=\"data_table\"><tr><th width=\"6%\">ARF#</th><th width=\"20%\">Department</th><th width=\"20%\">Name</th><th width=\"14%\">Date Effective</th><th width=\"15%\">Amt. to be Paid</th><th width=\"15%\">Date Initiated</th><th width=\"10%\">Status</th></tr>";
					echo "<tr$style><td><a href=\"arf$link_addon.php?ID=$id\">$id</a></td><td>$dept</td><td>$name</td><td>$date_effective</td><td>$amt</td><td>$timestamp</td><td>$value</td></tr>";

					if($i==$rows-1)
						echo "</table>";
					$i++;
				}
			?>
            </td>
		</tr>
    </table>
    </div>
    <? include('../includes/footer.php'); ?>