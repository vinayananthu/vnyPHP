<?php
include("../lib/arf_functions.php");

// Getting details of the student/factuly if they exist already
if(isset($_POST['action']) && $_POST['action']=="fetchDetails")
{
	$email = $_POST['email'];
	print(getStudentInfo($email));
}

else if(isset($_POST['action']) && $_POST['action']=="checkForOverlap")
{
	$email = $_POST['email'];
	$major_time_from = $_POST['major_time_from'];
	$major_time_to = $_POST['major_time_to'];
	print(checkForOverlap($email,$major_time_from,$major_time_to));
}