<?
$message="Hello Test:

The Appointment Request Form has been initiated by \"$owner\" for $employee_name. As the Project Director, your signature is required to process this ARF further.

You can access this form to sign it by clicking on the following link:

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = "smu004@latech.edu";
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";

				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

if(mail($to, $subject, $message, $headers)){
	$send=1;
}

echo phpinfo()
?>