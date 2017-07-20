<?php

$major_time_to_details = explode("/", $major_time_to);
$major_time_to_year = $major_time_to_details[2];
$major_time_to_month = $major_time_to_details[0];

$major_time_from_details = explode("/", $major_time_from);
$major_time_from_year = $major_time_from_details[2];
$major_time_from_month = $major_time_from_details[0];

$count = 0;

$academic_to_year = $major_time_from_year;
if($major_time_from_month>6)
	$academic_to_year += 1;

$count = 0;

if($major_time_to_year >= $academic_to_year)
{
	$count = $major_time_to_year - $academic_to_year;
	if($major_time_to_year == $academic_to_year && $major_time_to_month>6)
		$count = $count+1;
	$major_time_to_temp = $major_time_to;
}

// Test scenarios
// Prints 1 if it returns true, space for false
echo checkValidARF('vgy003',8,2016);
echo checkValidARF('vgy003',8,2,2016);

function checkValidARF(){
	$number_of_arguments = func_num_args();
	$email = func_get_arg(0) . '@latech.edu';
	$month = func_get_arg(1);

	$conn = connect();
	$valid = false;

	$sql_check_valid_date = "SELECT id, `major_time_from`, `major_time_to`, `cancel_time_from`, `cancel_time_to` FROM `appointment_request_form` WHERE `email`='" . $email . "' order by id asc";
	$result = mysqli_query($conn, $sql_check_valid_date);
	while($row = mysqli_fetch_assoc($result))
	{
		$valid_from_date = $row['major_time_from'];
		$valid_to_date = '';

		// finding if the arf is having valid period
		if($row['cancel_time_from']!='')
		{
			$valid_days = getNumberOfDays($row['major_time_from'], $row['cancel_time_from']);
			if($valid_days>0)
			{
				$valid_to_date = date('m/d/Y', strtotime($valid_from_date . '+ ' . $valid_days . ' days'));
			}
		}
		else
			$valid_to_date = $row['major_time_to'];

		if($valid_to_date!='')
		{
			// If number of arguments passed are 3, then checking for month, else specific date
			//checkValidARF('vgy003','12','2016') -- valid day for month
			if($number_of_arguments == 3)
			{
				$year = func_get_arg(2);
				$valid_from_date_array = split("/", $valid_from_date);
				$valid_to_date_array = split("/", $valid_to_date);
				if($month >= intval($valid_from_date_array[0]) && $year >= intval($valid_from_date_array[2]) && $month <= intval($valid_to_date_array[0]) && $year <= intval($valid_to_date_array[2]))
				{
					$valid = true;
					//echo $row['id'];
					break;
				}
			}
			//checkValidARF('vgy003','12','1','2016') -- valid on specific day
			else
			{
				$day = func_get_arg(2);
				$year = func_get_arg(3);
				$check_date = date("m/d/Y", strtotime($month . "/" . $day . "/" . $year));
				$valid_from_date = date("m/d/Y", strtotime($valid_from_date));
				$valid_to_date = date("m/d/Y", strtotime($valid_to_date));
				if($check_date >= $valid_from_date && $check_date <= $valid_to_date)
				{
					$valid = true;
					//echo $row['id'];
					break;
				}
			}
		}
	}
	return $valid;
}

function connect() // create a function for connect database
{
	$host="localhost";
    $username="forms";    // specifying the sever details for mysql
    $password="RForms387!";
    $database="routing";
	$connected=false;

    $conn=mysqli_connect($host,$username,$password,$database);

    return $conn;
}

// Function to get the number of days between two given dates
function getNumberOfDays($fromDate,$toDate){
	$datetime1 = new DateTime($fromDate);
	$datetime2 = new DateTime($toDate);
	$difference = $datetime1->diff($datetime2);

	return ($difference->days);
}
