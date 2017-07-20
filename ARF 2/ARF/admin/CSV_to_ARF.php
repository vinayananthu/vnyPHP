<?
error_reporting(0);

require_once('../lib/arf_functions.php');

$auth=new AuthUser;
$auth->auth();

$db=new SQL;
$db->connect();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base href="https://forms.latech.edu/ARF/" />
<link rel="Shortcut Icon" type="image/x-icon" href="https://forms.latech.edu/routing/i/favicon.ico">
<link rel="stylesheet" type="text/css" href="resources/style/acc_style.css" />
<title>CSV -> ARF - SCSU</title>
<style>
	body{
		background: #EEE;
		margin: 0;
		font-size: 10pt;
		font-family: Arial;
	}
	tr[bgcolor="#FFFFFF"]:hover{
		background: #EEE
	}
	tr a{
		display: block;
		background: #339933;
		padding: 2px;
		padding-left: 5px;
		padding-right: 5px;
		border-radius: 3px;
		box-shadow: 1px 1px 2px #AAA;
		text-decoration: none;
		color: #FFF
	}
	tr a:hover{
		background: #FF6600
	}
	p{
		margin-top: 10px
	}
	span a{
		color: #039
	}
</style>
</head>

<body>
<center style="padding: 10px">
	<div id="t_logo"></div>
</center>

<div style="background: #CEE7FF; padding: 10px">
	<div style="box-shadow: inset 0px 0px 10px #006BB2; border-radius: 8px; width: 350px; background: #FFF; padding: 10px; text-align: center; margin-right: auto; margin-left: auto">
		<form method="POST" enctype="multipart/form-data">
			<input type="file" name="csv_input" />
		    <input type="submit" name="upload" value="Upload CSV" />
		</form>
		<span style="font-size: 8pt; margin-top: 10px; display: block">or cancel and return <a href="/ARF/">back to home</a>.</span>
	</div>
	<?
    $column_headers=array("appointment_type", "last_name", "first_name", "middle_name", "address", "city", "zip", "date_effective", "date_of_birth", "ssn", "sex", "marital_status", "race", "requested_salary");
    ?>

	<p>
		<strong>NOTE:</strong> The following column titles are supported at the moment:
		<?
        for($i=0; $i<sizeof($column_headers); $i++){
            echo "<em><strong>".$column_headers[$i]."</strong></em>";
            if($i!=sizeof($column_headers)-1)
                echo ", ";
        }
        ?>
	</p>
	<span style="color: #660000">The accurate extraction and population of information is possible only if the column titles on the CSV file match with the specified column titles.</span>
</div>

<?
if(isset($_POST['upload']) && $_POST['upload']=="Upload CSV"){ // Check if the file has been uploaded

	$csv_array=array_map('array_filter', parse_csv(file_get_contents($_FILES['csv_input']['tmp_name']))); // Get a two-dimentional array for the CSV
	$csv_array=array_filter($csv_array);

	$value_index[]="ADFAFD0F5D";

	foreach($csv_array[0] as $key=>$val){ // Check if the column heads on CSV match with our pre-defined column heads
		for($i=0; $i<sizeof($column_headers); $i++){
			if($val==$column_headers[$i]){
				$value_index[$i]="$key";
			}
		}
	}

	// If in array, unset it.
	if(in_array("ADFAFD0F5D", $value_index)){
		unset($value_index[0]);
	}

	echo "<div style=\"background: #DDD; box-shadow: 0 0 4px #AAA; padding: 5px\"><table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" width=\"100%\">";
	for($i=0; $i<sizeof($column_headers); $i++){
		if($i==0)
			echo "\n		<thead><tr>";
		
		echo "\n			<th>$column_headers[$i]</th>";
		
		if($i==sizeof($column_headers)-1)
			echo "\n<th>Action</th>\n		</tr></thead>\n";
	}

	/* if the column head match, then print the matched column into a new row and column. */
	for($j=1; $j<sizeof($csv_array); $j++){
		if($j==1)
			echo "\n		<tbody>\n			<tr bgcolor=\"#FFFFFF\">";
		else
			echo "\n			<tr bgcolor=\"#FFFFFF\">";


		for($k=0; $k<sizeof($column_headers); $k++){
			if(array_key_exists($k, $value_index)){
				echo "<td class=\"row_$j\">".$csv_array[$j][$value_index[$k]]."</td>";
			}
			else
				echo "<td class=\"row_$j\"></td>";
		}

	if($j==sizeof($csv_array)-2)
		echo "<td align=\"center\"><a href=\"javascript: generateForm($j)\" title=\"Create new ARF from this row's data\">New</a></td></tr></tbody>\n";
	else
		echo "<td align=\"center\"><a href=\"javascript: generateForm($j)\" title=\"Create new ARF from this row's data\">New</a></td></tr>\n";
	}

	echo "</table></div>";
}


// Parses a CSV file into two-dimentional array containing column titles and data
function parse_csv ($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
{
    return array_map(
        function ($line) use ($delimiter, $trim_fields) {
            return array_map(
                function ($field) {
                    return str_replace('!!VVV!!', '"', utf8_decode(urldecode($field)));
                },
                $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line)
            );
        },
        preg_split(
            $skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s',
            preg_replace_callback(
                '/"(.*?)"/s',
                function ($field) {
                    return urlencode(utf8_encode($field[1]));
                },
                $enc = preg_replace('/(?<!")""/', '!!VVV!!', $csv_string)
            )
        )
    );
}

if(sizeof($value_index)==0 && isset($value_index)){
?>
<div style="color: #FF1919; height: 100px; margin-top: 40px; text-align: center">No matching column found. Please make sure that the column titles on the CSV file match with the column titles specified above.</div>
<?
}
?>

	<script langauge="javascript">
		function generateForm(id){
			var values=document.getElementsByClassName('row_'+id);
			var values_content="";
			var separator="&&&&";

			for(var i=0; i<values.length; i++){
				if(i==values.length-1)
					separator="";

				values_content+=values[i].innerHTML+separator;
			}
			post(values_content);
		}

		function post(values_content) {
		    var form = document.createElement("form");
		    form.setAttribute("method", "POST");
		    form.setAttribute("target", "_blank");
		    form.setAttribute("action", "https://forms.latech.edu/ARF/arf_form.php");

            var values_field = document.createElement("input");
            values_field.setAttribute("type", "hidden");
            values_field.setAttribute("name", 'csv_values');
            values_field.setAttribute("value", values_content);
		
		    form.appendChild(values_field);

	    	document.body.appendChild(form);
	    	form.submit();
		}
	</script>

    <? include('../includes/footer.php'); ?>