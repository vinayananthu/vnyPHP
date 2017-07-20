<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('../lib/arf_functions.php');

header('Content-Type: text/html; charset=utf-8');
$auth=new AuthUser;
$auth->auth();

$db=new SQL;
$db->connect();

$id=$_GET['edit'];
if($_POST['submit']=="Save Profile")
	$action="edit";
else if($_POST['submit']=="Add Profile")
	$action="add";
else if($_POST['delete']=="Delete Profile")
	$action="delete";

if((is_numeric($id) && $action=="edit") || $action=="add" || $action=="delete"){
	// Delete a Profile
	if($action=="delete"){
		if(mysql_query("UPDATE `people` SET `deleted`='1' WHERE id='$id'")){
			$delete_success=1;	
		}
	}

	// If attempted to add or edit a Profile
	if($delete_success!=1){
		$name=addslashes($_POST['name']);
		$research_center=addslashes($_POST['research_center']);
		$dept=addslashes($_POST['dept']);
		$phone=$_POST['phone'];
		$email=$_POST['email'];
		if($action=="add"){
			$rows=mysql_num_rows(mysql_query("SELECT email FROM people WHERE email='$email'"));
			if($rows>0){
				$profile_exists=1;
			}
		}
		$mailing_address=addslashes($_POST['mailing_address']);
		$is_preaward_coordinator=$_POST['is_preaward_coordinator'];
		$is_postaward_coordinator=$_POST['is_postaward_coordinator'];
		$is_center_director=$_POST['is_center_director'];
		$is_department_head=$_POST['is_department_head'];
		$is_dean_of_college=$_POST['is_dean_of_college'];
		$is_research_director=$_POST['is_research_director'];
		$is_university_research=$_POST['is_university_research'];
		$is_human_resources=$_POST['is_human_resources'];
		$is_vice_president=$_POST['is_vice_president'];
		$is_president=$_POST['is_president'];
		$is_comptroller=$_POST['is_comptroller'];

		$added_by=$auth->getUser();
		$last_modified=time();
		$adder_ip=$_SERVER['REMOTE_ADDR'];

		// Add a profile
		if($profile_exists!=1 && $action=="add"){
			$query="INSERT INTO people (name,
										name_latest,
										research_center,
										dept,
										phone,
										email,
										email_latest,
										mailing_address,
										is_preaward_coordinator,
										is_postaward_coordinator,
										is_center_director,
										is_department_head,
										is_dean_of_college,
										is_research_director,
										is_university_research,
										is_human_resources,
										is_vice_president,
										is_president,
										is_comptroller,
										added_by,
										last_modified,
										adder_ip) VALUES
									  ('$name',
									   '$name',
									   '$research_center',
									   '$dept',
									   '$phone',
									   '$email',
									   '$email',
									   '$mailing_address',
									   '$is_preaward_coordinator',
									   '$is_postaward_coordinator',
									   '$is_center_director',
									   '$is_department_head',
									   '$is_dean_of_college',
									   '$is_research_director',
									   '$is_university_research',
									   '$is_human_resources',
									   '$is_vice_president',
									   '$is_president',
									   '$is_comptroller',
									   '$added_by',
									   '$last_modified',
									   '$adder_ip')";
			if(mysql_query($query)){
				$add_success=1;
			}
		}
		// Edit a profile
		else if($action=="edit"){ // Always update name and email to only latest_name and latest_email columns
			$query="UPDATE people SET name_latest='$name',
									  research_center='$research_center',
									  dept='$dept',
									  phone='$phone',
									  email_latest='$email', 
									  mailing_address='$mailing_address',
									  is_preaward_coordinator='$is_preaward_coordinator',
									  is_postaward_coordinator='$is_postaward_coordinator',
									  is_center_director='$is_center_director',
									  is_department_head='$is_department_head',
									  is_dean_of_college='$is_dean_of_college',
									  is_research_director='$is_research_director',
									  is_university_research='$is_university_research',
									  is_human_resources='$is_human_resources',
									  is_vice_president='$is_vice_president',
									  is_president='$is_president',
									  is_comptroller='$is_comptroller',
									  added_by='$added_by',
									  last_modified='$last_modified',
									  adder_ip='$adder_ip' WHERE id='$id'";
			if(mysql_query($query)){
				$edit_success=1;
			}
		}
	}
}
else if(is_numeric($id)){
	$query="SELECT * FROM people WHERE id='$id'";
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
	$name=mysql_result($result, 0, "name_latest"); // only get the latest name
	$research_center=mysql_result($result, 0, "research_center");
	$dept=mysql_result($result, 0, "dept");
	$phone=mysql_result($result, 0, "phone");
	$email=mysql_result($result, 0, "email_latest"); // only get the latest e-mail
	$mailing_address=mysql_result($result, 0, "mailing_address");
	$is_preaward_coordinator=mysql_result($result, 0, "is_preaward_coordinator");
	$is_postaward_coordinator=mysql_result($result, 0, "is_postaward_coordinator");
	$is_center_director=mysql_result($result, 0, "is_center_director");
	$is_department_head=mysql_result($result, 0, "is_department_head");
	$is_dean_of_college=mysql_result($result, 0, "is_dean_of_college");
	$is_research_director=mysql_result($result, 0, "is_research_director");
	$is_university_research=mysql_result($result, 0, "is_university_research");
	$is_human_resources=mysql_result($result, 0, "is_human_resources");
	$is_vice_president=mysql_result($result, 0, "is_vice_president");
	$is_president=mysql_result($result, 0, "is_president");
	$is_comptroller=mysql_result($result, 0, "is_comptroller");

	if($rows>0)
		$edit=1;
}
else if($_GET['add']==1){
	$add=1;
}

if($edit!=1 && $add!=1){
	$query="SELECT * FROM people WHERE `deleted`='0' ORDER BY name";
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><? if($edit==1){ echo "Edit a Profile"; } else if($add==1){ echo "Add a Profile"; } else { echo "People's List"; } ?> - Appointment Request Form - SCSU</title>
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
				<? if($delete_success==1){ ?>
                <div id="success_notice">The profile has been deleted.</div>
                <? } else if($add_success==1){ ?>
                <div id="success_notice">The profile has been added.</div>
                <? } else if($edit_success==1){ ?>
                <div id="success_notice">The profile has been edited.</div>
                <? } ?>

				<? if($edit==1 || $add==1){ ?>
            	<form action="" method="POST">
            	<table id="data_table" cellpadding="4" cellspacing="0" width="100%">
                    <tr><th colspan="2"><? if($edit==1){ echo "Edit"; } else{ echo "Add"; } ?> a Profile</th></tr>
                    <tr><td width="25%">Name</td><td><input type="text" name="name" value="<? print_r($name) ?>" /></td></tr>
                    <tr><td>Phone</td><td><input type="text" name="phone" value="<? print_r($phone) ?>" /></td></tr>
                    <tr><td>E-mail</td><td><input type="text" name="email" value="<? print_r($email) ?>" /></td></tr>
                    <tr><td>Mailing Address</td><td><input type="text" name="mailing_address" value="<? print_r($mailing_address) ?>" /></td></tr>
                    <tr><td>Research Center</td><td><input type="text" name="research_center" value="<? print_r($research_center) ?>" /></td></tr>
                    <tr><td>Department</td><td><input type="text" name="dept" value="<? print_r($dept) ?>" /></td></tr>
                    <tr><td valign="top">Roles that Apply:</td>
                        <td>
                            <input type="checkbox" name="is_preaward_coordinator" id="is_preaward_coordinator" value="1"<? if($is_preaward_coordinator==1){ echo " checked"; } ?> /><label for="is_preaward_coordinator"> Pre-Award Coordinator</label><br />
                            <input type="checkbox" name="is_postaward_coordinator" id="is_postaward_coordinator" value="1"<? if($is_postaward_coordinator==1){ echo " checked"; } ?> /><label for="is_postaward_coordinator"> Post-Award Coordinator</label><br />
                            <input type="checkbox" name="is_center_director" id="is_center_director" value="1"<? if($is_center_director==1){ echo " checked"; } ?> /><label for="is_center_director"> Center Director</label><br />
                            <input type="checkbox" name="is_department_head" id="is_department_head" value="1"<? if($is_department_head==1){ echo " checked"; } ?> /><label for="is_department_head"> Department Head</label><br />
                            <input type="checkbox" name="is_dean_of_college" id="is_dean_of_college" value="1"<? if($is_dean_of_college==1){ echo " checked"; } ?> /><label for="is_dean_of_college"> Dean of College</label><br />
                            <input type="checkbox" name="is_research_director" id="is_research_director" value="1"<? if($is_research_director==1){ echo " checked"; } ?> /><label for="is_research_director"> Research Director</label><br />
                            <input type="checkbox" name="is_university_research" id="is_university_research" value="1"<? if($is_university_research==1){ echo " checked"; } ?> /><label for="is_university_research"> University Research</label><br />
                            <input type="checkbox" name="is_human_resources" id="is_human_resources" value="1"<? if($is_human_resources==1){ echo " checked"; } ?> /><label for="is_human_resources"> Human Resources</label><br />
                            <input type="checkbox" name="is_comptroller" id="is_comptroller" value="1"<? if($is_comptroller==1){ echo " checked"; } ?> /><label for="is_comptroller"> Comptroller</label><br />
                            <input type="checkbox" name="is_vice_president" id="is_vice_president" value="1"<? if($is_vice_president==1){ echo " checked"; } ?> /><label for="is_vice_president"> Vice President</label><br />
                            <input type="checkbox" name="is_president" id="is_president" value="1"<? if($is_president==1){ echo " checked"; } ?> /><label for="is_president"> President</label>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="submit" name="submit" value="<? if($edit=="1"){ echo "Save"; } else{ echo "Add"; } ?> Profile" /></td>
                        <td align="right"><? if($edit=="1"){ ?><input type="submit" name="delete" style="background: #a51f1f; color: #FFF; font-size: 8pt; border: 0; border-radius: 2px" value="Delete Profile" /><? } ?></td>
                    </tr>
               	</table>
                </form>
            <? } else{ ?>
            	<p id="desc" style="border-bottom: 1px dotted #AAA; padding-bottom: 10px">Add a <a href="admin/people.php?add=1">New Profile</a></p>
            	<p>Here is the list of people in our system with their respective roles:</p>
				<table cellspacing="0" cellpadding="2" width="100%" id="data_table">
                	<tr><th width="18%">Name</th><th width="20%">Role</th><th width="36%">Department</th><th width="18%">Research Center
                 <th width="8%">Action</th></tr>
                    <?
						$i=0;
						while($i<$rows){
							$id=mysql_result($result, $i, "id");
							$name=mysql_result($result, $i, "name_latest");
							$dept=mysql_result($result, $i, "dept");
							$research_center=mysql_result($result, $i, "research_center");

							$role="";
							if(mysql_result($result, $i, "is_center_director")==1){
								$role="Center Director";
							}
							if(mysql_result($result, $i, "is_dean_of_college")==1){
								if($role!='')
									$role.="<br />";
								$role.="Dean of College";
							}
							if(mysql_result($result, $i, "is_department_head")==1){
								if($role!='')
									$role.="<br />";
								$role.="Department Head";
							}
							if(mysql_result($result, $i, "is_postaward_coordinator")==1){
								if($role!='')
									$role.="<br />";
								$role.="Post-Award Coordinator";
							}
							if(mysql_result($result, $i, "is_preaward_coordinator")==1){
								if($role!='')
									$role.="<br />";
								$role.="Pre-Award Coordinator";
							}
							if(mysql_result($result, $i, "is_human_resources")==1){
								if($role!='')
									$role.="<br />";
								$role.="Human Resources";	
							}
							if(mysql_result($result, $i, "is_president")==1){
								if($role!='')
									$role.="<br />";
								$role.="President";
							}
							if(mysql_result($result, $i, "is_research_director")==1){
								if($role!='')
									$role.="<br />";
								$role.="Research Director";
							}
							if(mysql_result($result, $i, "is_university_research")==1){
								if($role!='')
									$role.="<br />";
								$role.="University Research";	
							}
							if(mysql_result($result, $i, "is_comptroller")==1){
								if($role!='')
									$role.="<br />";
								$role.="Comptroller";
							}
							if(mysql_result($result, $i, "is_vice_president")==1){
								if($role!='')
									$role.="<br />";
								$role.="Vice President of Research";
							}

							if($i%2!=0)
								$style=" style=\"background: #F1F1F1\"";
							else
								$style="";
							echo "<tr$style><td>$name</td><td>$role</td><td>$dept</td><td>$research_center</td><td><a href=\"admin/people.php?edit=$id\">Edit</a></td></tr>";
							$i++;
						}
					?>
                </table>
                <? } ?>
            </td>
		</tr>
    </table>
    </div>
    <? include('../includes/footer.php'); ?>