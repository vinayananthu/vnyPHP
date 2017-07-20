<?
error_reporting(0);

date_default_timezone_set('America/Chicago'); // Central Time-zone

include('lib/arf_functions.php');

$auth=new AuthUser;

$auth->auth();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Appointment Request Form - SCSU</title>
    <link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
    <link rel="stylesheet" type="text/css" href="resources/style/acc_style.css" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>

<body>
<center>
	<div id="t_logo"></div>

	<div id="m_body">
    <style>
		ol{
			margin: 0
		}
	</style>
	<table cellpadding="0" cellspacing="3" width="100%" id="m_table">
    	<tr valign="top">
        	<td width="20%" id="l_menu">
				<? include('includes/acc_left_menu.php'); ?>
            </td>
            <td>
            	<p>
                	This online Appointment Request system saves time and paper by enabling you to complete the form online and sign it electronically. The entire authorization process is undertaken electronically! All you will need to sign these forms is a device with a web-browser and an internet connection.
                </p>
                <h2>Instructions:</h2>
                <p>
                    <ol>
                    	<li>Department Heads, Dean, or other Budget Unit Head will initiate.</li>
                    	<li>Completed Form should be forwarded to appropriate offices for signature.</li>
                    	<li>Official transcripts for new teaching faculty should accompany the original appointment form.</li>
                    	<li>This form should be fully processed with complete information prior to the effective date of employment.</li>
                    	<li>All new appointments should be fully processed and have Board of Supervisor approval prior to the effective date of employment. (Graduate and Teaching Assistant appointments do not require Board of Supervisor approval.)</li>
                    	<li>Forms NOT received in the Personnel Office by the <a href="http://finance.latech.edu/hr/2016_monthly_deadlines.pdf" title="Monthly Deadline for 2016" />Monthly Payroll Deadline</a> will be processed the following month.</li>
                    	<li>The Personnel Office will forward a final approved copy to appropriate unit(s).</li>
                        <li>Status of the form at any stage can be checked by clicking on the "Check form status" link.</li>
                    </ol>
                </p>
                <? /*
				<p>This online Travel Authorization system saves time and paper by enabling you to complete the form online and sign it electronically. The entire authorization process is undertaken electronically, so you will not have to run around offices to get the signatures! All you will need to sign these forms is a device with a web-browser and an internet connection. Yay!</p> */ ?>
                <p id="desc" style="border-top: 1px dotted #AAA; padding-top: 10px">Start a <a href="arf_form.php" title="Create a New Form">New Form</a> or <a href="admin/CSV_to_ARF.php" title="Create New Forms from CSV rows">Upload a CSV</a></p>

				<!-- News and Events -->
				<div id="news">
                	<div class="news_box" style="margin-right: 20px">
                    	<div class="news_title">New @ Tech</div>
						<? $news=file_get_contents("https://forms.latech.edu/lib/rss_parser.php?type=news"); echo $news; ?>
                    </div>
                	<div class="news_box">
                    	<div class="news_title">Upcoming Events</div>
						<? $news=file_get_contents("https://forms.latech.edu/lib/rss_parser.php?type=events"); echo $news; ?>
                    </div>
                    <div style="clear: both"></div>
				</div>
                <!-- News and Events Ends -->
            </td>
		</tr>
    </table>

    </div>
    <? include('includes/footer.php'); ?>
</center>
</body>
</html>