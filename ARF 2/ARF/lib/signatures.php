<?php
error_reporting(0);

if(substr_count($_SERVER['HTTP_REFERER'],"latech.edu")<1)
	exit();

$token=$_GET['token'];

if($_COOKIE['usr']=="" && substr_count($token, "||")!=2){
	echo "Authentication Failed.";
	exit(0);
}

$token=explode("||", $token);
$ID=$token[0];
$field=$token[1];
$type=$token[2];

// functions


function qrCode($text){
	include("BarcodeQR.php");
	// set BarcodeQR object
	$qr = new BarcodeQR();
	
	// create URL QR code
	$qr->text("$text");
	
	// display new QR code image
	$qr->draw();
}

function generateSignature($String_Content) {
	// Set font size
	$font_size = 3;

	$ts=explode("\n",$String_Content);
	$width=0;

	foreach ($ts as $k=>$string) { //compute width
		$width=max($width,strlen($string));
	}

	// Create image width dependant on width of the string
	$width=imagefontwidth($font_size)*$width;

	// Set height to that of the font
	$height=imagefontheight($font_size)*count($ts);

	$el=imagefontheight($font_size);
	$em=imagefontwidth($font_size);

	// Create the image pallette
	$img=imagecreatetruecolor($width, $height);
	
	// White background
	$bg=imagecolorallocate($img, 255, 255, 255);
	imagefilledrectangle($img, 0, 0, $width , $height , $bg);

	// Black font color
	$color = imagecolorallocate($img, 0, 0, 0);

	foreach ($ts as $k=>$string) {
		// Length of the string
		$len = strlen($string);

		// Y-coordinate of character, X changes, Y is static
		$ypos = 0;

		// Loop through the string
		for($i=0;$i<$len;$i++){

			// Position of the character horizontally
			$xpos = $i * $em;
			$ypos = $k * $el;

			// Draw character
			imagechar($img, $font_size, $xpos, $ypos, $string, $color);

			// Remove character from string
			$string = substr($string, 1);		 
		}
	}

	// Return the image
	header("Content-Type: image/png");
	imagepng($img);

	// Remove image
 	imagedestroy($img);
}
// end functions

	include("arf_functions.php");
	$sql=new SQL;
	$sql->connect();

	$enc=new Crypto; // For decrypting

	$auth=new AuthUser;
	$authEncData=$auth->getOrigNameAndEmail($ID, $field);

	if($ID!=""){
		$query="SELECT `$field` FROM `appointment_request_signatures` WHERE `form_id`='$ID'";
		$result=mysql_query($query);

		$signature=explode("||.||", mysql_result($result, 0, "$field"));
		$decrypted=stripslashes($enc->decrypt(urldecode($signature[1]), md5($authEncData)));

		$signature=explode("+-__-+", $decrypted);
		if($type=='X18d!98ZXWX!ZAT'){
			$signature=$signature[0]."\n".$signature[1]."\n".date("r", $signature[2]);
		}
		else if($type=="Xl8d!98ZXWX1ZAT")
			$signature=$signature[0]."\n".$signature[1]."\n".date("r", $signature[2])."\n".$signature[3];
	}

	if($ID!="" && $type=="X18d!98ZXWX!ZAT"){
		generateSignature($signature);
	}
	else if($ID!="" && $type="Xl8d!98ZXWX1ZAT"){
		qrCode($signature);
	}
?>