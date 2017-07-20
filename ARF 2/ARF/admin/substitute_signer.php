<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();

$db=new SQL;
$db->connect();

$ID=$_GET['edit'];
if($_POST['submit']=="Save Substitute Signer")
	$action="edit";
else if($_POST['submit']=="Add Substitute Signer")
	$action="add";
else if($_POST['delete']=="Delete Substitute Signer")
	$action="delete";

if((is_numeric($ID) && $action=="edit") || $action=="add" || $action=="delete"){
	// Delete a Substitute Signer
	if($action=="delete"){
		if(mysql_query("DELETE FROM `appointment_request_substitute_signer` WHERE id='$ID'")){
			$delete_success=1;	
		}
	}

	// If attempted to add or edit a Substitute Signer
	if($delete_success!=1){
		$name=$_POST['name'];
		$email=$_POST['email'];
		$status=$_POST['status'];

		$added_by=$auth->getUser();

		if($action=="add"){
			$rows=mysql_num_rows(mysql_query("SELECT email FROM `appointment_request_substitute_signer` WHERE email='$email' AND `added_by`='$added_by'"));
			if($rows>0){
				echo $substitute_signer_exists=1;
			}
		}

		$last_modified=time();
		$adder_ip=$_SERVER['REMOTE_ADDR'];

		// Add a Substitute Signer
		if($substitute_signer_exists!=1 && $action=="add"){
			$query="INSERT INTO `appointment_request_substitute_signer` (name,
										email,
										added_by,
										last_modified,
										adder_ip,
										status) VALUES
									  ('$name',
									   '$email',
									   '$added_by',
									   '$last_modified',
									   '$adder_ip',
									   '$status')";
			if(mysql_query($query)){
				$add_success=1;
			}
		}
		// Edit a Substitute Signer
		else if($action=="edit"){
			$query="UPDATE `appointment_request_substitute_signer` SET name='$name',
									  email='$email', 
									  added_by='$added_by',
									  last_modified='$last_modified',
									  adder_ip='$adder_ip',
									  status='$status' WHERE id='$ID'";
			if(mysql_query($query)){
				$edit_success=1;
			}
		}
	}
}
else if(is_numeric($ID)){
	$query="SELECT * FROM `appointment_request_substitute_signer` WHERE id='$ID'";
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
	$name=mysql_result($result, 0, "name");
	$email=mysql_result($result, 0, "email");
	$status=mysql_result($result, 0, "status");

	if($rows>0)
		$edit=1;
}
else if($_GET['add']==1){
	$add=1;
}

if($edit!=1 && $add!=1){
	$query="SELECT * FROM `appointment_request_substitute_signer` WHERE `added_by`='".$auth->getUser()."'"; // Pick people only respective person has added.
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><? if($edit==1){ echo "Edit a Substitute Signer"; } else if($add==1){ echo "Add a Substitute Signer"; } else { echo "Substitute Signer"; } ?> - Appointment Request Form - SCSU</title>
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
                <div id="success_notice">The substitute signer has been deleted.</div>
                <? } else if($add_success==1){ ?>
                <div id="success_notice">The substitute signer has been added.</div>
                <? } else if($edit_success==1){ ?>
                <div id="success_notice">The substitute signer has been edited.</div>
                <? } ?>

				<? if($edit==1 || $add==1){ ?>
            	<form action="" method="POST">
            	<table id="data_table" cellpadding="4" cellspacing="0" width="100%">
                    <tr><th colspan="2"><? if($edit==1){ echo "Edit"; } else{ echo "Add"; } ?> a Substitute Signer</th></tr>
                    <tr><td width="20%">Name</td><td><input type="text" name="name" value="<? print_r($name) ?>" /></td></tr>
                    <tr><td>E-mail</td><td><input type="text" name="email" value="<? print_r($email) ?>" /></td></tr>
                    <tr><td>Current Status</td>
                    	<td><input type="radio" value="1" name="status" id="active" <? if($edit!=1 || ($edit==1 && $status=="1")){ echo "checked"; } ?> /><label for="active">Active</label>&emsp;
                        	<input type="radio" value="0" name="status" id="inactive" <? if($edit==1 && $status=="0"){ echo "checked"; } ?> /><label for="inactive"> Inactive</label></td></tr> 
                    <tr>
                        <td><input type="submit" name="submit" value="<? if($edit=="1"){ echo "Save"; } else{ echo "Add"; } ?> Substitute Signer" /></td>
                        <td align="right"><? if($edit=="1"){ ?><input type="submit" name="delete" style="background: #a51f1f; color: #FFF; font-size: 8pt; border: 0; border-radius: 2px" value="Delete Substitute Signer" /><? } ?></td>
                    </tr>
               	</table>
                </form>
            <? } else{ ?>
            	<p id="desc" style="border-bottom: 1px dotted #AAA; padding-bottom: 10px">Add a <a href="admin/substitute_signer.php?add=1">Substitute Signer</a></p>
               <?
               	if($rows==0){
               		echo "You do not have any authorized subsitute signer yet.";
				}
				else{
				?>
            	<p>Below are the people you authorized to sign your documents as your subsitute signers:</p>
				<table cellspacing="0" cellpadding="2" width="100%" id="data_table">
                	<tr><th>Name</th><th>E-mail</th><th widht="20">Status</th><th width="10%">Action</th></tr>
                    <?
						$i=0;
						while($i<$rows){
							$ID=mysql_result($result, $i, "id");
							$name=mysql_result($result, $i, "name");
							$email=mysql_result($result, $i, "email");
							$status=mysql_result($result, $i, "status");
							if($status=="0")
								$status="<font color=\"#aa0513\">Inactive</font>";
							else
								$status="<font color=\"#197414\">Active</font>";

							if($i%2!=0)
								$style=" style=\"background: #F1F1F1\"";
							else
								$style="";
							echo "<tr$style><td>$name</td><td>$email</td><td>$status</td><td><a href=\"admin/substitute_signer.php?edit=$ID\">Edit</a></td></tr>";

							$i++;
						}
					?>
                </table>
                <? } } ?>
            </td>
		</tr>
    </table>
    </div>
    <? include('../includes/footer.php'); ?>