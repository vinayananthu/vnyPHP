<?php
class SQL{ //create a class for make connection
	public function connect() // create a function for connect database
    {
		$host="localhost";
    	$username="forms";    // specifying the sever details for mysql
    	$password="RForms387!";
    	$database="routing";
		$connected=false;

        $conn=mysql_connect($host,$username,$password);

        if(!$conn)// testing the connection
            echo "Cannot connect to the database";

		if(mysql_select_db("$database"))
			$connected=true;

		return $connected;
    }
}

class AuthUser{
	public function auth(){
		$usr = $this->getUser();

		 //Get current URL for redirection
		$address=new self;
		$address=urlencode($address->getAddress());

		if($usr==""){
			header("Location: http://forms.latech.edu/login/?src=$address");
			exit(0);
		}
	}

	// Get user from Cookie
	public function getUser(){
		// Look for the Session on server created by CAS, then retrieve info from there.
		$file = file_get_contents(session_save_path().'/sess_'.$_COOKIE['session_for:index_php']);
		if(trim($file)!=""){
			session_start();
			session_decode($file);
			$usr = $_SESSION['phpCAS']['user'];
		}

		return strtolower($usr);
	}

	// Array of personnel whose signatures are anticipated with their LATEST name and e-mail only.
	public function authPersonnel($ID){
		$check=array_filter(checkSignatures($ID));
		unset($check["sig_id"]);
		unset($check["form_id"]);
		unset($check["form_session_id"]);

		$i=0;
		// Add name and e-mail address of people into an array
		foreach($check as $key=>$val){
			if(substr_count($val, "||.||")>0)  // If the field has been signed
				$val=substr($val, 0, strpos($val, "||.||"));

			if(is_numeric($val)){ // Get info from DB
				$query="SELECT `name_latest`, `email_latest` FROM `people` WHERE id='$val'";
				$result=mysql_query($query);
				$check[$key]=mysql_result($result, 0, "name_latest")."||".mysql_result($result, 0, "email_latest");
			}
			else
				$check[$key]=$val;
		}
		$i++;
		return $check;
	}

// Orig name and e-mail based on ID on signature
	public function getOrigNameAndEmail($ID, $field){
		$query="SELECT `$field` FROM `appointment_request_signatures` WHERE `form_id`='$ID'"; // Get current sig data for respective field based on ID
		$result=mysql_query($query);
		$rows=mysql_num_rows($result);
		$value="";

		if($rows>0){
			$val=mysql_result($result, 0, "$field"); // getting value on that particular field

			// Add name and e-mail address of people into an array
			if(substr_count($val, "||.||")>0)  // If the field has been signed, get only ID
				$val=substr($val, 0, strpos($val, "||.||"));

			// Get info from DB | original name and e-mail
			if(is_numeric($val)){
				$query="SELECT `name`, `email` FROM people WHERE id='$val'";
				$result=mysql_query($query);
				$value=mysql_result($result, 0, "name")."||".mysql_result($result, 0, "email");
			}
			else
				$value=$val;
		}
		return $value;
	}

		// Check if the particular person is authorized to view the particular form
	public function authorizeView($ID){

		$sigList=$this->authPersonnel($ID);
		$true=false;

		$query="SELECT `lname`, `fname`, `owner` FROM `appointment_request_form` WHERE `id`='$ID' AND `form_status`!='2'"; // Hide deleted forms
		$result=mysql_query($query) or die(mysql_error());
		$rows=mysql_num_rows($result);

		if($rows!=0){ // Only run everything below this line if the form exists and has not been deleted

			$owner=mysql_result($result, 0, "owner");

			$name=mysql_result($result, 0, "fname")." ".mysql_result($result, 0, "lname");

			// Complicated: if initiator is the person logged in, but not the PI or has no any other role, allow access to the form.
			$owner=mysql_result($result, 0, 'owner');
			if($this->getUser()==$owner){
				$sigList['Owner']=$owner;
			}

			// This loops grabs arrays with the list of Substitute Signers, and merges these arrays with $sigList
			foreach($sigList as $val){
				$username=substr($val, strpos($val, "||")+2, strpos($val, "@")-strlen($val));
				$substitute_signer=$this->checkSubstituteSigner($username);
				if(is_array($substitute_signer))
					$sigList=array_merge($sigList, $substitute_signer);
			}
			
			foreach($sigList as $val){
				if(substr_count($val, $this->getUser())>0 && $true==0){
					$true=true;
				}
			}
		}

		return $true;
	}
	
	// Checks if any person has any subsitute signer, if yes, puts all substitute signers into an array.
	function checkSubstituteSigner($username){
		if(substr_count($username, "||")>0){
			$username=explode("||", $username);
			$username=retrieveUsernamefromEmail($username[1]);
		}
		elseif(substr_count($username, "@")>0)
			$username=retrieveUsernamefromEmail($username);
	
		$substitue_signers=array();
	
		if(trim($username)!=""){
			$query="SELECT `name`, `email` FROM `appointment_request_substitute_signer` WHERE `added_by`='$username' AND `status`='1'";
			$result=mysql_query($query);
			$rows=mysql_num_rows($result);
			if($rows>0){
				$i=0;
				while($i<$rows){
					$substitute_signers[$i]=mysql_result($result, $i, 'name')."||".mysql_result($result, $i, 'email');
					$i++;	
				}
				return $substitute_signers;
			}
			else
				return 0;
		}
	}

	// Check if the given user is a substitute signer of another particular user
	function isSubstituteSignerOf($signer, $username){
		$true=false;
		$query="SELECT `email` FROM `appointment_request_substitute_signer` WHERE `email` LIKE '$signer%' AND `added_by`='$username' AND `status`='1'";
		$result=mysql_query($query);
		$rows=mysql_num_rows($result);
		
		if($rows>0)
			$true=true;
	
		return $true;
	}

	// Gets the current full URL
	public function getAddress(){
		$url = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		return $url.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
}
//function to check if two dates are overlapping
function overlapping_dates($start_date, $end_date, $test_date){
		if($test_date >= $start_date && $test_date <= $end_date)
			return true;
		else
			return false;
}

// Get raw signatures from the DB
function checkSignatures($ID){
	$result=mysql_query("SELECT * FROM `appointment_request_signatures` WHERE `form_id`='$ID'");

	if(mysql_num_rows($result)>0){
		$result_set = array();
		while($row=mysql_fetch_array($result)) {
			$result_set[]=$row;
		}
		$new_result_set=array_filter($result_set[0]);
		$i=0;
		// Get rid of numeric keys
		foreach($new_result_set as $key=>$val){
			if($i%2!=0){
				$newCheck[$key]=$val;
			}
			$i++;
		}
		return $newCheck;
	}
	else
		return 0;
}

// All the encryption and decryption
class Crypto{
	// Encrypts a string based on a key provided
	function encrypt($data, $key) {
		$init_vector_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$init_vector = mcrypt_create_iv($init_vector_size, MCRYPT_RAND);
		$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, utf8_encode($data), MCRYPT_MODE_ECB, $init_vector);
		return $encrypted_string;
	}

	// Decrypts a string based on a key provided
	function decrypt($enc_data, $key) {
		$init_vector_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$init_vector = mcrypt_create_iv($init_vector_size, MCRYPT_RAND);
		$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $enc_data, MCRYPT_MODE_ECB, $init_vector);
		return $decrypted_string;
	}
}

// Get the list of the colleges from the `colleges` table
function getColleges($college){
	$colleges="";

	$query="SELECT `id`, `college` FROM `colleges` ORDER BY `college`";
	$result=mysql_query($query);
	$rows_num=mysql_num_rows($result);
	$i=0;
	while($i<$rows_num){
		$collegeName=mysql_result($result, $i, "college");
		$collegeID=mysql_result($result, $i, "id");

		if($collegeID==$college)
			$colleges=$colleges."<option value=\"$collegeID\" selected=\"selected\">$collegeName</option>\n";
		else
			$colleges=$colleges."<option value=\"$collegeID\">$collegeName</option>\n";
		$i++;
	}
	return "<option value=\"0\">-- None --</option>\n" . $colleges;
}


// Get one college from ID
function getCollege($college){

	$query = "SELECT `college` FROM `colleges` WHERE `id`='$college'";
	$result = mysql_query($query);
	$rows = mysql_num_rows($result);

	$college = "None";

	if($rows > 0){
		$college = mysql_result($result, 0, "college");
	}

	return $college;
}


// Get the list of the department from the `department` table
function getDepartments($department){
	$departments="";

	$departments_array = array("1" => "PRESIDENT'S OFFICE - 009", 
		"2" => "ACADEMIC AFFAIRS - 010", 
		"3" => "ADMINISTRATIVE AFFAIRS - 012", 
		"4" => "STUDENT AFFAIRS - 014", 
		"5" => "APPLIED &amp; NATURAL SCIENCES - ADM. - 015", 
		"6" => "APPLIED &amp; NATUAL SCIENCES - 016", 
		"7"  => "HEALTH INFORMATION - 017",
		"8" => "LIBERAL ARTS - 018", 
		"9" => "INTERNAL AUDIT - 019", 
		"10" => "COLLEGE OF BUSINESS - 020", 
		"11" => "EDUCATION - 022", 
		"12" => "ENGINEERING - 024", 
		"13" => "HUMAN ECOLOGY - 026",
		"14" => "UNIVERSITY RESEARCH - 027",
		"15" => "GRADUATE SCHOOL - 028",
		"16" => "CONTINUING EDUCATION - 029",
		"17" => "AFROTC - 030",
		"18" => "BARKSDALE - 031",
		"19" => "BOOKSTORE (BARNES &amp; NOBLE) - 032",
		"20" => "BUILDINGS &amp; GROUNDS - 034",
		"21" => "PURCHASING - 036",
		"22" => "TELECOMMUNICATIONS - 038",
		"23" => "COMPTROLLER - 040",
		"24" => "COMPUTING CENTER - 042",
		"25" => "ENVIRONMENTAL - 046",
		"26" => "ATHLETICS - 048",
		"27" => "HUMAN RESOURCES - 052",
		"28" => "LIBRARY - 054",
		"29" => "NEWS BUREAU - 058",
		"30" => "UNIVERSITY POLICE - 060",
		"31" => "UNIVERSITY ADVANCEMENT - 062",
		"32" => "POST OFFICE/ OFFICE SVCS. - 064",
		"33" => "PROPERTY - 066",
		"34" => "REGISTRAR - 068",
		"35" => "FINANCIAL AID - 070", 
		"36" => "BIOLOGICAL SCIENCES - 080", 
		"37" => "ADMISSIONS/ ENROLLMENT MANAGEMENT - 082", 
		"38" => "NURSING - 086", 
		"39" => "SUPERVISING TEACHERS (EDUCATION) - 092");

	asort($departments_array);

	foreach($departments_array as $key=>$val) {
		$departmentName = $val;
		$departmentID = $key;
		
		if($departmentID==$department)
			$departments=$departments."<option value=\"$departmentID\" selected=\"selected\">$departmentName</option>\n";
		else
			$departments=$departments."<option value=\"$departmentID\">$departmentName</option>\n";			
	}

/*
	$query="SELECT id, department FROM department_table ORDER BY department";
	$result=mysql_query($query);
	$rows_num=mysql_num_rows($result);
	$i=0;
	while($i<$rows_num){
		$departmentName=mysql_result($result, $i, "department");
		$departmentID=mysql_result($result, $i, "id");

		if($departmentID==$department)
			$departments=$departments."<option value=\"$departmentID\" selected=\"selected\">$departmentName</option>\n";
		else
			$departments=$departments."<option value=\"$departmentID\">$departmentName</option>\n";
		$i++;
	}
 */
	return $departments;
}

// Get the name of the department that is selected, based on the ID.
function selectDepartment($department){
	
	$departments_array = array("1" => "PRESIDENT'S OFFICE - 009", 
		"2" => "ACADEMIC AFFAIRS - 010", 
		"3" => "ADMINISTRATIVE AFFAIRS - 012", 
		"4" => "STUDENT AFFAIRS - 014", 
		"5" => "APPLIED &amp; NATURAL SCIENCES - ADM. - 015", 
		"6" => "APPLIED &amp; NATUAL SCIENCES - 016", 
		"7"  => "HEALTH INFORMATION - 017",
		"8" => "LIBERAL ARTS - 018", 
		"9" => "INTERNAL AUDIT - 019", 
		"10" => "COLLEGE OF BUSINESS - 020", 
		"11" => "EDUCATION - 022", 
		"12" => "ENGINEERING - 024", 
		"13" => "HUMAN ECOLOGY - 026",
		"14" => "UNIVERSITY RESEARCH - 027",
		"15" => "GRADUATE SCHOOL - 028",
		"16" => "CONTINUING EDUCATION - 029",
		"17" => "AFROTC - 030",
		"18" => "BARKSDALE - 031",
		"19" => "BOOKSTORE (BARNES &amp; NOBLE) - 032",
		"20" => "BUILDINGS &amp; GROUNDS - 034",
		"21" => "PURCHASING - 036",
		"22" => "TELECOMMUNICATIONS - 038",
		"23" => "COMPTROLLER - 040",
		"24" => "COMPUTING CENTER - 042",
		"25" => "ENVIRONMENTAL - 046",
		"26" => "ATHLETICS - 048",
		"27" => "HUMAN RESOURCES - 052",
		"28" => "LIBRARY - 054",
		"29" => "NEWS BUREAU - 058",
		"30" => "UNIVERSITY POLICE - 060",
		"31" => "UNIVERSITY ADVANCEMENT - 062",
		"32" => "POST OFFICE/ OFFICE SVCS. - 064",
		"33" => "PROPERTY - 066",
		"34" => "REGISTRAR - 068",
		"35" => "FINANCIAL AID - 070", 
		"36" => "BIOLOGICAL SCIENCES - 080", 
		"37" => "ADMISSIONS/ ENROLLMENT MANAGEMENT - 082", 
		"38" => "NURSING - 086", 
		"39" => "SUPERVISING TEACHERS (EDUCATION) - 092");

	if($department!=""){
			$department = $departments_array[$department];
	}
	return $department;
}

// For any given ID or name and e-mail, retrieves the name
function retrieveNameAndEmailFromID($ID){
	if(substr_count($ID, '||.||')>0){ // Remove signature, if it contains signature
		$ID=explode("||.||", $ID);
		$ID=$ID[0];
	}

	if(substr_count($ID, "||")>0){ // If it doesn't contain ID but only contains name and email
		$details=explode("||", $ID);
	}
	else{ // If ID of a personnel
		$result=mysql_query("SELECT `name_latest`, `email_latest` FROM people WHERE id='$ID'");
		$rows=mysql_num_rows($result);
		if($rows>0){
			$name=mysql_result($result,0, "name_latest");
			$email=mysql_result($result, 0, "email_email");
			$details=array("$name", "$email");
		}
	}

	if(sizeof($details)>0){
		return $details;
	}
	else
		return false;
}

// Get the list of person for a dropdown menu based on their role
function getIndividuals($condition, $selected){
	$individuals="";
	$result=mysql_query("SELECT `id`, `name_latest` FROM `people` WHERE $condition AND `deleted`='0' ORDER BY `name_latest`"); // Get the latest name to display
	while ($row = mysql_fetch_array($result)){
		if(substr_count($selected, "||.||")==1) // If contains signature, remove.
			$selected=substr($selected, 0, strpos($selected, "||.||"));
		if($selected==$row['id']){
			$individuals=$individuals."<option value=\"".$row['id']."\" selected=\"selected\">".$row["name_latest"]."</option>\n";
		}
		else
			$individuals=$individuals."<option value=\"".$row['id']."\">".$row["name_latest"]."</option>\n";
	}
	return $individuals;
}

// Gets the name of the president and the comptroller
function getOfficials(){
	$query="SELECT `id`, `dept`, `is_department_head`, `is_university_research`,`is_vice_president`,`is_dean_of_college`, `is_comptroller`, `is_human_resources`, `is_president` FROM `people` WHERE (`is_university_research`='1' AND `research_center` = 'Contracts Administrator') OR (`is_vice_president` = '1' AND `dept` LIKE '%Academic Affairs%') OR (`is_comptroller`='1' AND `is_department_head` = '1') OR (`is_vice_president`='1' AND `dept` LIKE '%Research and Development%') OR `is_human_resources`='1' OR `is_president`='1' OR (`is_dean_of_college`=1 AND `dept` LIKE 'Graduate School')";
	$result=mysql_query($query);
	$rows=mysql_num_rows($result);
	$people=array();
	$i=0;

	while($i<$rows){
		if(mysql_result($result, $i, 'is_university_research')==1)
			$people[0]=mysql_result($result, $i, 'id');
		if(mysql_result($result, $i, 'is_vice_president')==1 && substr_count(strtolower(mysql_result($result, $i, 'dept')), "research") > 0)
			$people[1]=mysql_result($result, $i, 'id');
		if(mysql_result($result, $i, 'is_dean_of_college')==1)
			$people[2]=mysql_result($result, $i, 'id');
        if(mysql_result($result, $i, 'is_comptroller')==1)
			$people[3]=mysql_result($result, $i, 'id');
		if(mysql_result($result, $i, 'is_human_resources')==1)
			$people[4]=mysql_result($result, $i, 'id');
		if(mysql_result($result, $i, 'is_vice_president')==1 && substr_count(strtolower(mysql_result($result, $i, 'dept')), "academic") > 0)
			$people[5]=mysql_result($result, $i, 'id');
		if(mysql_result($result, $i, 'is_president')==1)
			$people[6]=mysql_result($result, $i, 'id');
		$i++;
	}
	return $people;
}

function printValue($value){
		if(strlen($value)>0)
			$printValue="<div class=\"div_preview\">$value</div>";
	    else
			$printValue="<span class=\"form_preview_empty\"></span>";
			return $printValue;
}

 //attachments are temporarily hidden
function attachmentExists($filename){

	$root=$_SERVER['DOCUMENT_ROOT'];
	$files=scandir("$root/ARF/Uploaded_Files/");
	$ID=explode("_", $filename);
	$ID=$ID[0];
	if(is_numeric($ID)){ // Testing empty or invalid ID values
		foreach($files as $val){
			$file=pathinfo($val);
			$ID_file=explode("_", $file['filename']);
			$ID_file=$ID_file[0];

			if(substr_count(trim($file['filename']), trim($filename))>0 && $ID_file==$ID){
				return $file['filename'].".".$file['extension'];
			}
		}
	}
	return 0;
}

function getSignersList($ID){
	$personnel=array();

	if(!is_numeric($ID)) {
		$result = mysql_query("SELECT `form_id` FROM `appointment_request_signatures` WHERE `form_session_id` = '$ID'");
		$ID = mysql_result($result, 0, 'form_id');
	}

	$result=mysql_query("SELECT * FROM  `appointment_request_signatures` WHERE `form_id`='$ID'");

	$proj_director=stripslashes(mysql_result($result, $i, "proj_director"));
	$budget_verification = mysql_result($result, $i, "budget_verification");
	$dept_head=mysql_result($result, $i, "dept_head");
	$dean=mysql_result($result, $i, "dean");
	$univ_research=mysql_result($result, $i, "univ_research");
	$vp_research = mysql_result($result, $i, "vp_research");
	$grad_school=mysql_result($result, $i, "grad_school");
	$comptroller_officer=mysql_result($result, $i, "comptroller_officer");
	$vice_president=mysql_result($result, $i, "vice_president");
	$president=mysql_result($result, $i, "president");
	$human_resources=mysql_result($result, $i, "human_resources");

	$people_array=array("$proj_director", "$budget_verification", "$dept_head", "$dean", "$univ_research","$vp_research","$grad_school", "$comptroller_officer", "$vice_president","$president", "$human_resources");
	$people_roles=array("Project Director (Grants Only)", "Budget Verification", "Associate Dean/Dept. Head", "Dean", "University Research","VP of Research","Graduate School", "Comptroller", "Vice President", "President", "Human Resources");

	for($i=0; $i<sizeof($people_array); $i++){
		$personnel[$people_roles[$i]]=$people_array[$i];
	}

	return array_filter($personnel);
}

 // Function to upload attachments to the server. It also saves filename into the DB.
function uploadFile($id, $filename, $field){
	$file=$_FILES[$filename];
	$root=$_SERVER['DOCUMENT_ROOT'];

	$true=false;
	if($file["error"]>0)
		$file_error=1;
	else
		$extension=substr($file["name"], stripos($file["name"], ".")+1, strlen($file["name"]));
	$target_path="$root/ARF/Uploaded_Files/";
	$target_path=$target_path.$id."_$filename.$extension";

	if(move_uploaded_file($file["tmp_name"], $target_path)){
		mysql_query("UPDATE `appointment_request_form` SET `$field`='".str_replace("&", "", addslashes($file['name']))."' WHERE id='$id'"); // replaces (&) from filename to prevent conflict
		$true=true;
	}
	return $true;
}


// Interswitching
function getDBColumnNameforSigFromKey($role, $type){
	$people_roles=array("Project Director (Grants Only)", "Budget Verification", "Associate Dean/Dept. Head", "Dean", "University Research", "VP of Research", "Graduate School", "Comptroller Officer", "Vice President", "President", "Human Resources");
	$people_array=array("proj_director", "budget_verification", "dept_head","dean","univ_research","vp_research", "grad_school", "comptroller_officer","vice_president","president","human_resources");

	if($type==1)
		return $people_array[array_search($role, $people_roles)];
	else
		return $people_roles[array_search($role, $people_array)];
}

// Extract signature from the database for any given form ID
function getSignature($field, $ID){
	$result=mysql_query("SELECT `$field` FROM `appointment_request_signatures` WHERE form_id='$ID'");
	if(mysql_num_rows($result)>0){
		$signature=mysql_result($result, 0, "$field");
		if(substr_count($signature, "||.||")>0){
			$signature=explode("||.||", $signature);
			$signature=$signature[1];
			if(trim($signature)!=""){
				return $signature;
			}
		}
	}
}

// Retrieves ID of the person in DB from their username used to login
function retrieveUserIDfromUsername($value){
	if($value==""){
		$auth=new AuthUser;
		$user=$auth->getUser();
	}
	else
		$user=$value;

	$query="SELECT `id` FROM people WHERE `email_latest` LIKE '$user@%'";
	$value = mysql_query($query) or die("A MySQL error has occurred.<br />Your Query: " . $query . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());

	$id=mysql_result($value, 0, "id");
	return $id;
}

// Get UserID, Name, and Email from Initial Signature
function retrieveUserIDfromInitSig($value){
	$value=substr($value, strpos($value, "||")+2, strpos($value, "@")-strpos($value, "||")-2);
	return trim($value);	
}
function retrieveNamefromInitSig($value){
	$value=explode("||", $value);
	return trim($value[0]);
}
function retrieveEmailfromInitSig($value){
	$value=explode("||", $value);
	return trim($value[1]);
}

// Retrieve username from e-mail
function retrieveUsernamefromEmail($value){
	if($value!=""){
		$value=explode("@", $value);
		$value=$value[0];
	}
	return $value;
}


// Check which step the form is stuck on: form status.
function checkFormStatus($ID){
	$query="SELECT t1.form_id FROM appointment_request_signatures t1, appointment_request_form t2 WHERE t1.form_id='$ID' AND t2.id='$ID' AND t2.form_status='1'";
	$result=mysql_query($query) or die(mysql_error());
	$rows=mysql_num_rows($result);

	$status="Finalized"; // Form has been finalized

	if($rows>0){
		$checkSignatures=checkSignatures($ID); // checkSignature() is actually useful on this one

		foreach($checkSignatures as $key=>$val){
			if((substr_count($key, "proj_director") == 1) && substr_count($val, "||.||")==0) {
				$status=1; // Project Director
				break;
			}
			else if((substr_count($key, "budget_verification") == 1) && substr_count($val, "||.||")==0){
				$status=1.1; // Budget Verification
				break;
			}
			else if((substr_count($key, "dept_head") == 1) && substr_count($val, "||.||")==0){
				$status=1.2; // Department Head
				break;
			}
			else if((substr_count($key, "dean") == 1) && substr_count($val, "||.||")==0){
				$status=1.3; // Dean
				break;
			}
			else if((substr_count($key, "univ_research") == 1) && substr_count($val, "||.||")==0){
				$status=1.4; // University Research
				break;
			}
			else if((substr_count($key, "vp_research") == 1) && substr_count($val, "||.||")==0){
				$status=2; // VP for Research
				break;
			}
			else if((substr_count($key, "grad_school") == 1) && substr_count($val, "||.||")==0){
				$status=3; // VP for Research
				break;
			}
			else if((substr_count($key, "comptroller_officer")==1) && substr_count($val, "||.||")==0){
				$status=4;
				break;	
			}
			else if((substr_count($key, "vice_president")==1) && substr_count($val, "||.||")==0){
				$status=5;
				break;	
			}
			else if(substr_count($key, "president")==1 && substr_count($val, "||.||")==0){
				$status=6; // Presidents have not signed
				break; 
			}
			else if(substr_count($key, "human_resources")==1 && substr_count($val, "||.||")==0){
				$status=7; // Pre-Award Coordinator Has not Signed
				break;
			}
		}
		return $status;
	}
}

// Notice about who signed the form
function sendSignedNotice($field){
	global $authPersonnel;

	foreach($authPersonnel as $key=>$val){
		if($key==$field){
			$val=explode("||", $val);
			$name=$val[0];
			$email=$val[1];

			$role=getDBColumnNameforSigFromKey($key, "2");

			$alert="$name has signed the form as $role.";
		}
	}
	return $alert;
}



// Check whose signature is received and email the respective recipients. The most complex process.
function checkFormStatusAndSendNotifications($ID, $field){

	global $authPersonnel;
	global $auth;
	global $fname;
	global $lname;
	global $email;
	global $owner;
	global $appointment_for;
	global $prepared_by;

	/* Associate Dean of Graduate Studies signs all forms */
	$supervisor_email = "sdua@latech.edu";
	$supervisor_name = "Sumeet Dua";

	global $proj_director_email;
	global $proj_director_name;

	$employee_name=$fname." ".$lname;

	// Send an e-mail to the initiator.
	if(substr_count($field, "initiator")) {
		$message="Hello $prepared_by:

You have just initiated an ARF for $employee_name.

You can access this form by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

If you did NOT initiate this form, please contact us immediately.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";

		$to = $owner . "@latech.edu";
		$subject = "You have initiated an ARF";
		$from = "noreply-HR@latech.edu";

		$headers = "From: $from"."\r\n".
				   "Reply-To: $from"."\r\n" .
				   "X-Mailer: PHP/".phpversion();

		if(mail($to, $subject, $message, $headers)){
			$send=1;
		}		
	}
	
	// E-mailing depending on who signed, and who is next on sequence
	if(substr_count($field, "initiator") > 0 && (array_key_exists('proj_director', $authPersonnel))){ // If form initiated and Grant PI present, send to Grant PI
		// For dept heads, university_research
		foreach($authPersonnel as $key=>$val){
			if(substr_count($key, "proj_director")>0 && getSignature($key, "$ID")==""){ // Send to Grant PI only if they have not signed yet
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}

				$message="Hello ".str_replace(" (Grants Only)", "", getDBColumnNameforSigFromKey($key, "2")).":

The Appointment Request Form has been initiated by \"$owner\" for $employee_name. As the Project Director, your signature is required to process this ARF further.

You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";

				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}			
			}
		}
	}
	else if((substr_count($field, "initiator")+substr_count($field, "proj_director") > 0) && (array_key_exists('budget_verification', $authPersonnel) || array_key_exists('dept_head', $authPersonnel) || array_key_exists('univ_research', $authPersonnel))){ // If initiator or grantpi signed, send to budget verification, university research, and academic director.

		// For dept heads, university_research
		foreach($authPersonnel as $key=>$val){
			if((substr_count($key, "budget_verification")+substr_count($key, "dept_head")+substr_count($key, "univ_research")) && getSignature($key, "$ID")==""){
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}

				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":
	
The Appointment Request Form has been initiated by \"$owner\" for $employee_name. Your signature is required on this ARF to process it further.
	
You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."
	
You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.
	
NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.
	
Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
	
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();
	
				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}			
			}
		}
	}
	else if((substr_count($field, "budget_verification")+substr_count($field, "dept_head")+substr_count($field, "univ_research")>0) && (array_key_exists('dean', $authPersonnel)) && checkHeadsSignatures($ID, $field, 'heads')){ // If initiator or grantpi signed, send to budget verification, university research, and academic director.

		// For Deans
		foreach($authPersonnel as $key=>$val){
			if(substr_count($key, "dean") && getSignature($key, "$ID")==""){
				$receiver_info=explode("||", $val);

				// If the appointment is for the student, and the dean is Dr. Hegab, send e-mail to Dr. Dua
				if($appointment_for == "2" && substr_count($receiver_info[1], "hhegab") > 0 && substr_count($key, "dean") > 0)
					$receiver_email = "sdua@latech.edu";
				else
					$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}

				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":
	
The Appointment Request Form has been initiated by \"$owner\" for $employee_name. Your signature is required on this ARF to process it further.
	
You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."
	
You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.
	
NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.
	
Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
	
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();
	
				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}			
			}
		}
	}
	else if(((substr_count($field, "dean") > 0)
				&& checkHeadsSignatures($ID, $field, "heads1"))
				|| (!array_key_exists('budget_verification', $authPersonnel) && !array_key_exists('dept_head', $authPersonnel) && !array_key_exists('dean', $authPersonnel) && !array_key_exists('univ_research', $authPersonnel))
				&& (array_key_exists('vp_research', $authPersonnel) && checkHeadsSignatures($ID, $field, 'heads1'))){

		foreach($authPersonnel as $key=>$val){
			if((substr_count($key, "vp_research") > 0) && getSignature($key, "$ID")==""){ // E-mail Dean
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}

				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":
	
The Appointment Request Form initiated by \"$owner\" for $employee_name has been forwarded to you. Your signature is required on this ARF to process it further.

You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
			
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}
			}
		}
	}
	else if((substr_count($field, "vp_research") > 0 || !array_key_exists('vp_research', $authPersonnel)) && checkHeadsSignatures($ID, $field, "heads2")){

		foreach($authPersonnel as $key=>$val){
			if(substr_count($key, "grad_school")>0 && getSignature($key, "$ID")==""){ // Vice President
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}
	
				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":

The Appointment Request Form initiated by \"$owner\" for $employee_name has been forwarded to you. Your signature is required on this ARF to process it further.

You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
			
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}
			}
		}
	}
	else if((substr_count($field, "grad_school") > 0 || !array_key_exists('grad_school', $authPersonnel)) && checkHeadsSignatures($ID, $field, "heads3")){

		foreach($authPersonnel as $key=>$val){
			if(substr_count($key, "comptroller_officer")>0 && getSignature($key, "$ID")==""){ // Vice President
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
				if($substitute_signer!=0){
					foreach($substitute_signer as $val) {
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}
	
				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":

The Appointment Request Form initiated by \"$owner\" for $employee_name has been forwarded to you. Your signature is required on this ARF to process it further.

You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
			
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}
			}
		}
	}
	else if((substr_count($field, "comptroller_officer") > 0 || !array_key_exists('comptroller_officer', $authPersonnel)) && checkHeadsSignatures($ID, $field, "heads4")){

		foreach($authPersonnel as $key=>$val){
			if(substr_count($key, "vice_president")>0 && getSignature($key, "$ID")==""){ // Vice President
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}
	
				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":

The Appointment Request Form initiated by \"$owner\" for $employee_name has been forwarded to you. Your signature is required on this ARF to process it further.

You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
			
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}
			}
		}
	}
	else if((substr_count($field, "vice_president")>0 || !array_key_exists('vice_president', $authPersonnel)) && checkHeadsSignatures($ID, $field, "heads5")){

		foreach($authPersonnel as $key=>$val){
			if(substr_count($key, "president")>0 && getSignature($key, "$ID")==""){ // President
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);

				// Dr. Guice does not want to receive e-mails.
				if($receiver_email == "guice@latech.edu") {
					$receiver_email = "dummy_email@latech.edu";
				}

				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}
	
				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":

The Appointment Request Form initiated by \"$owner\" for $employee_name has been forwarded to you. Your signature is required on this ARF to process it further.

You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
			
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}
			}
		}
	}
	else if(substr_count($field, "president") > 0 && getSignature($field, "$ID") != ""){
	
		foreach($authPersonnel as $key=>$val){
			if(substr_count($key, "human_resources")>0 && getSignature($key, "$ID")==""){ // Email president
				$receiver_info=explode("||", $val);
				$receiver_email=$receiver_info[1];

				// Append substitute signer's e-mails to receiver's email, substitute signer exists
				$substitute_signer=$auth->checkSubstituteSigner($receiver_email);
			
				if($substitute_signer!=0){
					foreach($substitute_signer as $val){
						$val=explode("||", $val);
						$receiver_email=$receiver_email.",".$val[1];
					}
				}

				$message="Hello ".getDBColumnNameforSigFromKey($key, "2").":

The Appointment Request Form initiated by \"$owner\" for $employee_name has been forwarded to you. Your signature is required on this ARF to finalize it.

You can access this form to sign it by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

NOTE: If you have designated a substitute signer, that person is also receiving a copy of this e-mail.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";
	
				$to = $receiver_email;
				$subject = "Appointment Request Form requires your attention";
				$from = "noreply-HR@latech.edu";
			
				$headers = "From: $from"."\r\n".
						   "Reply-To: $from"."\r\n" .
						   "X-Mailer: PHP/".phpversion();

				if(mail($to, $subject, $message, $headers)){
					$send=1;
				}
			}
		}
	}
	
	// Check if the form has been finalized. If yes, email the initiator
	if(checkFormStatus($ID)=="Finalized"){
			$message="Hello $owner:

The Appointment Request Form for $employee_name you initiated has been finalized after receiving signatures from all the officials.

You can view the finalized form by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";

			$to = $owner."@latech.edu";
			$subject = "Appointment Request Form has been finalized";
			$from = "noreply-HR@latech.edu";
	
			$headers = "From: $from"."\r\n".
					   "Reply-To: $from"."\r\n" .
					   "X-Mailer: PHP/".phpversion();

		if(mail($to, $subject, $message, $headers)){
			$send=1;
		}


		// Send message to Janet and Dianna after the form is finalized.
		$message="Hello Janet and Dianna:

The Appointment Request Form for $employee_name has been finalized after receiving signatures from all the officials.

You can view the finalized form by clicking on the following link:
https://forms.latech.edu/ARF/arf.php?ID=$ID&PIN=".getRandomPIN()."

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.

Thank you,
Office of Human Resources
Phone: (318) 257-2235
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";

		$to = "jcooper@latech.edu, dcoleman@latech.edu";
		$subject = "Appointment Request Form has been finalized";
		$from = "noreply-HR@latech.edu";
	
		$headers = "From: $from"."\r\n".
					   "Reply-To: $from"."\r\n" .
					   "X-Mailer: PHP/".phpversion();

		if(mail($to, $subject, $message, $headers)){
			$send=1;
		}


		/***** Sending emails to supervisor or project director or both for Award Letter *****/
		$email_names = Array();
		$email_ids = Array();
		$roles = Array();

		$email_names[0] = $supervisor_name;
		$email_ids[0] = $supervisor_email;
		$roles[0] = "Supervisor";

		$supervisor_info = "";
		if($supervisor_name != "" && $supervisor_email != "") {
			$supervisor_info = $supervisor_name . "||" . $supervisor_email;
		}

		$proj_director_info = "";
		if($proj_director_name != "" && $proj_director_email != "") {
			$proj_director_info = $proj_director_name . "||" . $proj_director_email;
		}

		$employee_info = "";
		if($employee_name != "" && $email != "") {
			$employee_info = $employee_name . "||" . $email;
		}

		
		if(isSignRequired($ID)) // Project director's sign is required
		{
			// Insert work supervisor and student details into award letter signatures table
			$sql_insert_keys = "INSERT INTO `award_letter_signatures`(`form_id`, `supervisor`, `proj_director`, `student`) VALUES ('$ID','$supervisor_info', '$proj_director_info','$employee_info')";
			$email_names[1] = $proj_director_name;
			$email_ids[1] = $proj_director_email;
			$roles[1] = "Project Director";
		}
		else
		{

			$sql_insert_keys = "INSERT INTO `award_letter_signatures`(`form_id`, `supervisor`, `student`) VALUES ('$ID','$supervisor_info','$employee_info')";
		}
		
		mysql_query($sql_insert_keys);
		
		// Emailing required people to sign the award letter form
		for($count = 0; $count < sizeof($email_ids); $count++)
		{
			$receiver_name = $email_names[$count];
			$receiver_email = $email_ids[$count];

			// Append substitute signer's e-mails to receiver's email, substitute signer exists
			$substitute_signer = $auth->checkSubstituteSigner($receiver_email);

			if($substitute_signer != 0){
				foreach($substitute_signer as $val){
					$val=explode("||", $val);
					$receiver_email=$receiver_email.",".$val[1];
				}
			}


			$message = "Hello $receiver_name:

The Appointment Request Form for $employee_name, for whom you are the $roles[$count], has been finalized after receiving signatures from all the officials.

Please review and sign the Award Letter by cicking on the following link:
https://forms.latech.edu/award_letter/award_letter.php?ID=$ID&PIN=39284X3984VVY.

You will need your Tech username and password to login into the system before you can access this form. If you encounter any issue, please feel free to contact us.
		
Thank you,
Office of Graduate Studies
College of Engineering and Science
Phone: (318) 257-4314
____________
PLEASE DO NOT reply to this e-mail. The e-mails sent to this e-mail address are not monitored.";

		$to = $receiver_email;
		$subject = "$employee_info's Award Letter is ready for your signature";
			$from = "noreply-COES-GS@latech.edu";

			$headers = "From: $from"."\r\n".
					"Reply-To: $from"."\r\n" .
					"X-Mailer: PHP/".phpversion();

					mail($to, $subject, $message, $headers);
		}
	}
}


// Get greeting based on current time
function getGreeting(){
	$hour = date('H', time());
	if( $hour > 6 && $hour <= 11)
	  $greeting="Good morning";
	else if($hour > 11 && $hour <= 16)
	  $greeting="Good afternoon";
	else if($hour > 16 && $hour <= 23)
	  $greeting="Good evening";
	else
	  $greeting="Good night";
	return $greeting;
}


// Check if the logged in  person is from OUR
function isAdmin($username){
	$inOUR=false;
	$ourPersonnel = array("smu004", "hopeu", "abell", "vgy003", "sdua", "ramu", "cervin", "bdoxey", "leporati", "jcooper", "dcoleman");
	if(in_array($username, $ourPersonnel)){
		$inOUR=true;
	}
	return $inOUR;
}


// Only allow access to creating forms to these individuals. Final three are human resources.
function canCreateForm($username) {
	$creator = false;
	$adminAssistants = array("vgy003", "smu004", "sdua", "desiree", "gwen", "mdoughty", "sellis", "lgaskin", "ahill", "coffill", "csly", "msmith", "hopeu", "abell", "fredda", "awallace", "charlott", "leporati", "jcooper", "dcoleman", "dthomas", "jamiet", "srmoore", "rboyte", "cwcooper");
	if(in_array($username, $adminAssistants)) {
		$creator = true;
	}
	return $creator;
}


/* if users list is in a array ['abc934', 'bcd823', ...], check whether this username is in the signature line */
function userExistsInArray($role, $all_user_ids){
	foreach($all_user_ids as $val){
		if(!is_numeric($val)){
			if(substr_count($role, "||".$val."@")>0){
				return true;
			}
		}
	}
	return false;
}


// Count the number of remarks
function countRemarks($ID){
	$num=0;
	$query="SELECT `remarks_comments` FROM `appointment_request_form` WHERE `id`='$ID'";
	$result=mysql_query($query);
	if(mysql_num_rows($result)>0){
		$remarks=mysql_result($result, 0, 'remarks_comments');
		if(substr_count($remarks, "\n[[")>0)
			$num=substr_count($remarks, "\n[[");
	}
	return $num;
}


// Checks if all the heads or the presidents have signed the form for their roles
function checkHeadsSignatures($ID, $field, $type){
	$checkSignatures=checkSignatures($ID);
	$all_signed=1;
	if($type=="heads"){
		foreach($checkSignatures as $key=>$val){
			if(substr_count($key, "proj_director") + substr_count($key, "budget_verification") + substr_count($key, "dept_head") + substr_count($key, "univ_research") > 0){
				if(substr_count($val, "||.||")==0 && $all_signed==1 && $field!=$key){
					$all_signed=0;	
				}
			}
		}
	}
	else if($type=="heads1"){
		foreach($checkSignatures as $key=>$val){
			if(substr_count($key, "proj_director") + substr_count($key, "budget_verification") + substr_count($key, "dept_head") + substr_count($key, "univ_research") + substr_count($key, "dean") > 0){
				if(substr_count($val, "||.||")==0 && $all_signed==1 && $field!=$key){
					$all_signed=0;	
				}
			}
		}
	}
	else if($type=="heads2"){
		foreach($checkSignatures as $key=>$val){
			if(substr_count($key, "proj_director") + substr_count($key, "budget_verification") + substr_count($key, "dept_head") + substr_count($key, "univ_research") + substr_count($key, "dean") + substr_count($key, "vp_research") > 0){
				if(substr_count($val, "||.||")==0 && $all_signed==1 && $field!=$key){
					$all_signed=0;	
				}
			}
		}
	}
	else if($type=="heads3"){
		foreach($checkSignatures as $key=>$val){
			if(substr_count($key, "proj_director") + substr_count($key, "budget_verification") + substr_count($key, "dept_head") + substr_count($key, "univ_research") + substr_count($key, "dean") + substr_count($key, "vp_research") + substr_count($key, "grad_school") > 0){
				if(substr_count($val, "||.||")==0 && $all_signed==1 && $field!=$key){
					$all_signed=0;	
				}
			}
		}
	}
	else if($type=="heads4"){
		foreach($checkSignatures as $key=>$val){
			if(substr_count($key, "proj_director") + substr_count($key, "budget_verification") + substr_count($key, "dept_head") + substr_count($key, "univ_research") + substr_count($key, "dean") + substr_count($key, "vp_research") + substr_count($key, "grad_school") + substr_count($key, "comptroller_officer") > 0){
				if(substr_count($val, "||.||")==0 && $all_signed==1 && $field!=$key){
					$all_signed=0;	
				}
			}
		}
	}
	else if($type=="heads5") {
		foreach($checkSignatures as $key=>$val){
			if(substr_count($key, "proj_director") + substr_count($key, "budget_verification") + substr_count($key, "dept_head") + substr_count($key, "univ_research") + substr_count($key, "dean") + substr_count($key, "vp_research") + substr_count($key, "grad_school") + substr_count($key, "comptroller_officer") + substr_count($key, "vice_president") > 0){
				if(substr_count($val, "||.||")==0 && $all_signed==1 && $field!=$key){
					$all_signed=0;	
				}
			}
		}
	}

	return $all_signed;
}


// Random PIN Generator
function getRandomPIN($length){
	if($length=="")
		$length=10;

	$rand=substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	return $rand;
}


// Delete form based on ID
function deleteForm($ID){
	$deleted=0;

	if(changeFormStatus($ID, "2")) // Changing status to 2 means deleting
		$deleted=1;

	return $deleted;
}


// Toggle form's status between 0 (invisible to officials) to 1 (visible to officials)
function changeFormStatus($ID, $value){
	$status_changed=0;
	$query="UPDATE `appointment_request_form` SET `form_status`='$value' WHERE `id`='$ID'";

	if(mysql_query($query))
		$status_changed=1;

	return $status_changed;
}


function getARFs($email)
{
	$year = date('Y');
	if(date('n')>6)
		$year = $year+1;
	$query_arf_dates = "SELECT min(STR_TO_DATE(`major_time_from`,'%m/%d/%Y')) as start_date,max(STR_TO_DATE(`major_time_to`,'%m/%d/%Y')) as end_date FROM `appointment_request_form` WHERE email='" . $email . "' and STR_TO_DATE(`major_time_from`,'%m/%d/%Y') BETWEEN STR_TO_DATE('07/01/" . ($year-1) . "','%m/%d/%Y') AND STR_TO_DATE('06/30/" . $year ."','%m/%d/%Y')";
	$result_arf_dates = mysql_query($query_arf_dates);
	$start_date = mysql_result($result_arf_dates, 0, "start_date");
	$end_date = mysql_result($result_arf_dates, 0, "end_date");
	$daysForTheYear = getNumberOfDays($start_date, $end_date);
	$query="SELECT `id`,`major_time_from`,`major_time_to`,`cancel_time_from`,`cancel_time_to` FROM `appointment_request_form` WHERE email='" . $email . "' and STR_TO_DATE(`major_time_from`,'%m/%d/%Y') BETWEEN STR_TO_DATE('07/01/" . ($year-1) . "','%m/%d/%Y') AND STR_TO_DATE('06/30/" . $year ."','%m/%d/%Y')";
	$result=mysql_query($query);
	$rows_num=mysql_num_rows($result);
	
	if($rows_num==0)
		return "No records found";
	else
	{
		$innerDivs = "";
		$i=0;
		while($i<$rows_num)
		{
			$effectiveFromDate = mysql_result($result, $i, "major_time_from");
			$effectiveToDate = mysql_result($result, $i, "major_time_to");
			$cancelFromDate = mysql_result($result, $i, "cancel_time_from");
			$cancelToDate = mysql_result($result, $i, "cancel_time_to");

			$left = getLeft($effectiveFromDate,$start_date,$daysForTheYear);
			$width = getWidth($effectiveFromDate, $effectiveToDate,$start_date,$end_date,$daysForTheYear);
			$innerDivs = $innerDivs . "<div style=\"text-align: center;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;border-left:2px solid black;position:absolute;top:0;left:" . $left . "%;width:" . $width . "%; height:50px;background-color:#00ff00\">" . $effectiveFromDate . " - " . $effectiveToDate . "</div>";

				// cancel period
			if($cancelFromDate!="")
			{
				$left = getLeft($cancelFromDate,$start_date,$daysForTheYear);
				$width = getWidth($cancelFromDate, $cancelToDate,$start_date,$end_date,$daysForTheYear);
				$innerDivs = $innerDivs . "<div style=\"text-align: center;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;border-left:2px solid black;position:absolute;top:0;left:" . $left . "%;width:" . $width . "%; height:50px;background-color:#ff0000\">" . $cancelFromDate . " - " . $cancelToDate . "</div>";
			}
			$i++;
		}
		return $innerDivs;
	}
	
}

/*function checkIfTheARFIsEligible($fromDate,$toDate){
	$year = date('Y');
	$month = date('n');
	
	if($month>6)
		$year = $year+1;
	
	$fromDate = explode("||||", $fromDate)[0];
	$fromYear = explode("/", $fromDate)[2];
	$fromMonth = explode("/", $fromDate)[0];
	
	$toDate = explode("||||", $toDate)[0];
	$toYear = explode("/", $toDate)[2];
	$toMonth = explode("/", $toDate)[0];
	
	if(($fromYear==$year-1 && $fromMonth>6) || ($fromYear==$year && $fromMonth<7) || ($toYear==$year-1 && $toMonth>6) || ($toYear==$year && $toMonth<7))
		return true;
	else
		return false;
}*/


function getLeft($fromDate,$start_date,$daysForTheYear){
	$year = date('Y');
	$month = date('n');
	
	if($month>6)
		$year = $year+1;
	
	$fromYear = explode("/", $fromDate)[2];
	$fromMonth = explode("/", $fromDate)[0];
	$fromDay = explode("/", $fromDate)[1];
	
	if($fromMonth<7 && $fromYear==$year-1)
		return 0;
	else
	{
		$days = getNumberOfDays($start_date, $fromYear . "-" . $fromMonth . "-" . $fromDay);
		$days -= 1;
		return ((($days*100)/$daysForTheYear));
	}
	
}

function getWidth($fromDate, $toDate,$start_date,$end_date,$daysForTheYear){
	$year = date('Y');
	$month = date('n');
	if($month>6)
		$year = $year+1;
	
	$fromYear = explode("/", $fromDate)[2];
	$fromMonth = explode("/", $fromDate)[0];
	$fromDay = explode("/", $fromDate)[1];
	
	$toYear = explode("/", $toDate)[2];
	$toMonth = explode("/", $toDate)[0];
	$toDay = explode("/", $toDate)[1];
	
	// if the effective to date is in next academic yearyear
	if($toYear==$year && $toMonth>6)
		$days = getNumberOfDays($fromYear . "-" . $fromMonth . "-" . $fromDay, $end_date);
	else
	{
		// if the from date is in before calendar year
		if($fromYear==$year-1 && $fromMonth<7)
			$days = getNumberOfDays($start_date, $toYear . "-" . $toMonth . "-" . $toDay);
		else
			$days = getNumberOfDays($fromYear . "-" . $fromMonth . "-" . $fromDay, $toYear . "-" . $toMonth . "-" . $toDay);
	}
	return ((($days*100)/$daysForTheYear));
}

function getNumberOfDays($fromDate,$toDate){
	$datetime1 = new DateTime($fromDate);
	$datetime2 = new DateTime($toDate);
	$difference = $datetime1->diff($datetime2);
	
	return ($difference->days)+1;
}

/*function getNumberOfDaysForTheYear(){
	$year = date('Y');
	$month = date('n');

	if($month>6)
		$year = $year+1;

	if((($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0))
		return 366;
	else
		return 365;
}*/

function getStudentInfo($email){
	$db=new SQL;
	$enc=new Crypto;
	$db->connect();
	$query="SELECT `street`,`city`,`zip`,`date_effective`,`ssn`,`dob`,`sex`,`marital`,`race`,`raceList`,`nationality`,`dept`,`rank`,owner FROM `appointment_request_form` WHERE email = '" . $email ."' and id = (select max(id) from appointment_request_form where email = '" . $email ."')";
	$result=mysql_query($query);
	$rows_num=mysql_num_rows($result);
	if($rows_num>0)
	{
		$i=0;
		while($i<1)
		{
			$owner=mysql_result($result, $i, "owner"); // ENC
			$street=stripslashes(mysql_result($result, $i, "street"));
			$city=stripslashes(mysql_result($result, $i, "city"));
			$zip=mysql_result($result, $i, "zip");
			$date_effective=mysql_result($result, $i, "date_effective");
			$ssn=trim($enc->decrypt(urldecode(mysql_result($result, $i, "ssn")), md5($owner)));
			$dob=mysql_result($result, $i, "dob");
			$sex=mysql_result($result, $i, "sex");
			$marital=mysql_result($result, $i, "marital");
			$race=mysql_result($result, $i, "race");
			$raceList=mysql_result($result, $i, "raceList");
			$nationality=mysql_result($result, $i, "nationality");
			$dept=mysql_result($result, $i, "dept");
			$rank=stripslashes(mysql_result($result, $i, "rank"));
			$studentInfo = Array("street"=>$street,"city"=>$city,"zip"=>$zip,"date_effective"=>$date_effective,"ssn"=>$ssn,"dob"=>$dob,"sex"=>$sex,"marital"=>$marital,"race"=>$race,"raceList"=>$raceList,"nationality"=>$nationality,"dept"=>$dept,"rank"=>$rank);
			$i++;
		}
		return json_encode($studentInfo);
	}
	else
	{
		return "";
	}	
}

function checkForOverlap($email,$major_time_from,$major_time_to){
	$db=new SQL;
	$db->connect();
	$query="SELECT * FROM `appointment_request_form` WHERE email='" . $email . "' and ((str_to_date('" . $major_time_from . "','%m/%d/%Y') between str_to_date(`major_time_from`,'%m/%d/%Y') and str_to_date(`major_time_to`,'%m/%d/%Y')) OR (str_to_date('" . $major_time_to . "','%m/%d/%Y') between str_to_date(`major_time_from`,'%m/%d/%Y') and str_to_date(`major_time_to`,'%m/%d/%Y')) OR (str_to_date('" . $major_time_from . "','%m/%d/%Y') < str_to_date(`major_time_from`,'%m/%d/%Y') and str_to_date(`major_time_to`,'%m/%d/%Y') < str_to_date('" . $major_time_to . "','%m/%d/%Y')))";
	$result=mysql_query($query);
	$rows_num=mysql_num_rows($result);
	if($rows_num > 0)
		return "yes";
	else
		return "no";
}

function getDateFormat($date){
	$date = explode("/", $date);
	$month = date("F",mktime(0,0,0,$date[0],$date[1],$date[2]));
	return $month . ", " . $date[2];
}

function isSignRequired($ID){
	// Getting department codes of the form
	$isSignRequired = false;
	$sql_get_department_codes = "SELECT major_dc FROM `appointment_request_form` WHERE id=' " . $ID . "'";
	$department_codes = mysql_result(mysql_query($sql_get_department_codes), 0,"major_dc");
	$department_codes = explode("||||", $department_codes);
	foreach ($department_codes as $department_code)
	{
		// for the codes which have 4080, do not have the third number
		$codes = explode("-", $department_code);
		if(sizeof($codes)>2)
		if($codes[0]=="32" && intval(substr($codes[2], 0,5))<70000 && intval(substr($codes[2], 0,5))>40000)
		{
			$isSignRequired = true;
			break;
		}
	}
	return $isSignRequired;
}
?>