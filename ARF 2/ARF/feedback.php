<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();

$username=$auth->getUser();
$Email=$username."@latech.edu";

$sub=$_POST['err_sub'];
$msg=trim($_POST['err_msg']);
$url=urldecode($_GET['error_url']);
$ip=$_SERVER['REMOTE_ADDR'];

$message="Reporter: $username\nE-mail: $Email\n\n-------------------------\n$msg\n-------------------------\n\nError URL: $url\n\n";

if($sub=="Send" && $msg==""){
?>
<script langauge="Javascript">
alert("Please write a description of a problem before submitting an issue.");
</script>
<?
}
elseif($msg!=""){
	$to = "formshelp@latech.edu";
	$to1= "3182432467@txt.att.net";
	$subject = "Appointment Request Form Problem Reported";
	$from = "$Email";
	$mail_from="$from";

    $headers = "From: $from"."\r\n".
               "Reply-To: $from"."\r\n" .
               "X-Mailer: PHP/".phpversion();

	if(mail($to, $subject, $message, $headers)){ // Emailing message
		if(mail($to1, $subject, $message, $headers)){ // Texting message
			$sent=1;
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Report a Problem - Appointment Request Form - SCSU</title>
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
				<? include('includes/acc_left_menu.php'); ?>
            </td>
            <td id="desc">
            	<strong>Report a Problem or Give a Feedback</strong>
                <? if($sent==1){ ?>
                <p style="margin-top: 10px">Thank you for reporting a problem or sending us your feedback. Your message has been submitted to our technical support team. They will look into this issue as soon as possible.</p>
                <? } else { ?>
				<p style="margin-top: 10px">If you are sending a message about the problem you have encountered in the system, please describe the problem in as detail as possible. Your detail description will help us identify the problem correctly, replicate it, and solve it.</p>
				<form method="post" name="err_report"><p><textarea name="err_msg" style="font-family: arial; border: 2px solid #888888; border-right: 1px solid #888888; border-bottom: 1px solid #888888; font-size: 12pt; background: #F7F7F7; color: #555555; width: 450px; max-width: 400px; height: 200px"></textarea><br />
<input type="submit" name="err_sub" value="Send"></p></form>
				<? } ?>
            </td>
		</tr>
    </table>
    </div>
	<script language="javascript">
        document.err_report.err_msg.focus();
    </script>
    <? include('includes/footer.php'); ?>