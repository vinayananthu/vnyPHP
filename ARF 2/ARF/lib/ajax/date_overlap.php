<? 
error_reporting(0);
include_once('../functions.php');
$db=new SQL;
if($db->connect())
	echo "";
else
    echo "Not connected";

$ID=$_POST['ID'];
$email_value = $_POST['email'];
$major_time_from = $_POST['major_time_from'];
$major_time_to = $_POST['major_time_to'];

$current_fromdate_array=explode("||||", $major_time_from);
$current_todate_array=explode("||||", $major_time_to);

$query="SELECT * FROM `appointment_request_form` where `email`='$email_value'";
$result=mysql_query($query);
$rows=mysql_num_rows($result);
$i=0;
	
	while($i<$rows){ 				//multiple forms loop
		$start_date=mysql_result($result, $i, "major_time_from");
		$end_date=mysql_result($result, $i, "major_time_to");
		$prev_start_date_array=explode("||||",$start_date);
		$prev_end_date_array=explode("||||", $end_date);
		for($x=0; $x<sizeof($current_fromdate_array); $x++){
			for($y=0; $y<sizeof($prev_start_date_array); $y++){
				if((($current_fromdate_array[$y] >= $prev_start_date_array[$y]) && ($current_fromdate_array[$y] <= $prev_end_date_array[$y])) || (($current_todate_array[$y] >= $prev_start_date_array[$y]) && ($current_todate_array[$y] <= $prev_end_date_array[$y]))){
						echo 'Overlap';
				
				}
			}
		}
		$i++;
	}
	echo "No overlap";
?>