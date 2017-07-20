<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('../lib/functions.php');

$auth=new AuthUser;
$auth->auth();

$db=new SQL;
$db->connect();

$id=$_GET['edit'];
if($_POST['submit']=="Save Department")
	$action="edit";
else if($_POST['submit']=="Add Department")
	$action="add";
else if($_POST['delete']=="Delete Department")
	$action="delete";

if((is_numeric($id) && $action=="edit") || $action=="add" || $action=="delete"){
	// Delete a Profile
	if($action=="delete"){
		if(mysql_query("DELETE FROM `department_table` WHERE id='$id'")){
			$delete_success=1;	
		}
	}

	// If attempted to add or edit a Profile
	if($delete_success!=1){
		$department=$_POST['department'];
		$email=$_POST['email'];
		$campus_mail=$_POST['campus_mail'];
		$telephone=str_replace("-", "", $_POST['telephone']);

		$added_by=$auth->getUser();
		$last_modified=time();
		$adder_ip=$_SERVER['REMOTE_ADDR'];

		if($action=="add"){
			$rows=mysql_num_rows(mysql_query("SELECT `department_table` FROM department WHERE department='$department'"));
			if($rows>0){
				echo $profile_exists=1;
			}
		}

		// Add a profile
		if($profile_exists!=1 && $action=="add"){
			$query="INSERT INTO `department_table` (department,
													email,
													campus_mail,
													telephone,
													added_by,
													last_modified,
													adder_ip) VALUES
												  ('$department',
												   '$email',
												   '$campus_mail',
												   '$telephone',
												   '$added_by',
												   '$last_modified',
												   '$adder_ip')";
			if(mysql_query($query)){
				$add_success=1;
			}
		}
		// Edit a profile
		else if($action=="edit"){
			$query="UPDATE `department_table` SET department='$department',
												  email='$email',
												  campus_mail='$campus_mail',
												  telephone='$telephone',
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
	$query="SELECT * FROM `department_table` WHERE id='$id'";
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
	$department=mysql_result($result, 0, "department");
	$email=mysql_result($result, 0, "email");
	$campus_mail=mysql_result($result, 0, "campus_mail");
	$telephone=mysql_result($result, 0, "telephone");

	if($rows>0)
		$edit=1;
}
else if($_GET['add']==1){
	$add=1;
}

if($edit!=1 && $add!=1){
	$query="SELECT * FROM `department_table` ORDER BY department";
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><? if($edit==1){ echo "Edit a Department"; } else if($add==1){ echo "Add a Department"; } else { echo "Departments List"; } ?> - Travel Authorization Form - SCSU</title>
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
		<? if($delete_success==1){ ?>
                <div id="success_notice">The department has been deleted.</div>
                <? } else if($add_success==1){ ?>
                <div id="success_notice">The department has been added.</div>
                <? } else if($edit_success==1){ ?>
                <div id="success_notice">The department has been edited.</div>
                <? } ?>

				<? if($edit==1 || $add==1){ ?>
            	<form action="" method="POST">
            	<table id="data_table" cellpadding="4" cellspacing="0" width="100%">
                    <tr><th colspan="2"><? if($edit==1){ echo "Edit"; } else{ echo "Add"; } ?> a Department</th></tr>
                    <tr><td width="25%">Department Name</td><td><input type="text" name="department" value="<? print_r($department) ?>" /></td></tr>
                    <tr><td>Email</td><td><input type="text" name="email" value="<? print_r($email) ?>" /></td></tr>
                    <tr><td>Campus Mail</td><td><input type="text" name="email" value="<? print_r($email) ?>" /></td></tr>
                    <tr><td>Telephone</td><td><input type="text" name="telephone" value="<? print_r($telephone) ?>" /></td></tr>
                    <tr>
                        <td><input type="submit" name="submit" value="<? if($edit=="1"){ echo "Save"; } else{ echo "Add"; } ?> Department" /></td>
                        <td align="right"><? if($edit=="1"){ ?><input type="submit" name="delete" style="background: #a51f1f; color: #FFF; font-size: 8pt; border: 0; border-radius: 2px" value="Delete Department" /><? } ?></td>
                    </tr>
               	</table>
                </form>
            <? } else{ ?>
            	<p id="desc" style="border-bottom: 1px dotted #AAA; padding-bottom: 10px">Add a <a href="admin/departments.php?add=1">New Department</a></p>
            	<p>Here is the list of the departments in our system:</p>
				<table cellspacing="0" cellpadding="2" width="100%" id="data_table">
                	<tr><th>Department Name</th><th width="10%">Action</th></tr>
                    <?
						$i=0;
						while($i<$rows){
							$id=mysql_result($result, $i, "id");
							$department=mysql_result($result, $i, "department");

							if($i%2!=0)
								$style=" style=\"background: #F1F1F1\"";
							else
								$style="";

							echo "<tr$style><td>$department</td><td><a href=\"admin/departments.php?edit=$id\">Edit</a></td></tr>";
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