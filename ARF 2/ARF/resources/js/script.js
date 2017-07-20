var validateSSN = true;

// Hide Page 2 contents
function hideSecondPage(){
	document.getElementById('page_2_sections').style.display="none";
}

// Display Page 2 contents
function showSecondPage(){
	//document.getElementById('page_2_sections').style.display="";
}
function save_message(){
	alert("You have successfully saved your Appointment Request form. To access this form, go to 'My Forms' and complete it later. ");
}
function cancelForm(id){
	var r=confirm("Are you sure you want to cancel this Appointment Request form?");
	if(r==true){
		document.getElementById('cancel_table').style.display="";
	}
}

function hideVP() {
	var college = document.getElementById('college').value;

	if(college == 0)
		document.getElementById('vice_president_row').style.display = "";
	else {
		document.getElementById('vice_president_row').style.display = "none";
		document.getElementById('vice_president').value = "";
	}
}


function display_row(){
	var appointment_for = document.getElementById('appointment_for').checked;
	if(appointment_for == true)
		document.getElementById('student_position').style.display="none";
	else if(appointment_for == false)
		document.getElementById('student_position').style.display="";
}

/*function display_upload(){
	var selectBox = document.getElementById("position");
    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
    if(selectedValue == 3){
		document.getElementById('doc_upload').style.display="";
		document.getElementById('doc_upload1').style.display="";
	}
	else{
		document.getElementById('doc_upload').style.display="none";
		document.getElementById('doc_upload1').style.display="none";
	}
}*/

$(document).ready(function(){
// Needs further work
/*    $('input[type="radio"]').click(function(){
        if($(this).attr("value")=="1"){
            $(".addmore_btn").not(".1").hide();
            $(".1").show();
        }
    });*/

	/* Added 10/11/2016 */

	// closing cancel ARF form
	$('#close_cancel_ARF').click(function(){
		$('#cancel_table').hide();
	});

	// If Tech e-mail, hide warning message
	$('#email').keydown(function(){
		$('#email_format_warning_div').hide();
	});

	// On Blur, if NOT Tech e-mail, display warning message.
	$('#email').blur(function(){
		if($(this).val().trim()=="" || !( /^\w+([\.-]?\w+)@latech.edu/.test($(this).val())))
		{
			$('#email_format_warning_div').show();
			$('#email_format_warning_div').html('<font color="#FF6666">Please enter a valid SCSU e-mail address.</font>');

			if($(this).val().trim() != "" && !( /^\w+([\.-]?\w+)@latech.edu/.test($(this).val()))) {
				$(this).val('');
				$(this).focus();
			}
		}
	});


	$('#warning_messages').on("click","#major_time_warning_check",function(){
		calculateAmountsWhenMajorTimeWarning();
	});

	$('#warning_messages').on("click","#major_time_from_warning_check",function(){
		calculateAmountsWhenMajorTimeFromWarning();
	});

	$('#warning_messages').on("click","#major_time_to_warning_check",function(){
		calculateAmountsWhenMajorTimeToWarning();
	});

	// adding calendar to the DOB filed and making default year to 18 years before the present year
	$('#datepicker2').datepicker({
		defaultDate: "-18y"
	});

	// Disabling the dates before the form starting date for cancel from period date calendar
	var fromdate = $('#major_time_dates_table tr:nth-child(2) td:nth-child(2) div').text();
	var todate = $('#major_time_dates_table tr:nth-child(2) td:nth-child(4) div').text();
	$('#datepicker15').datepicker({
		beforeShowDay: function(date){
			var string = new Date(jQuery.datepicker.formatDate('mm/dd/yy',date));
			return [(string >= (new Date(fromdate))) && (string <= (new Date(todate)))];
		}
	});
	/* ^ Added 10/11/2016 */

	updateTotalfund(); updateTotalMonthlyAmount(); updateTotalPercent();

	showGrantAccountSignatures();

	// Hiding elements when the form loads from the DB
	if($('#student').is(":checked"))
		hideFacultyElements();

	$('#appointment_for').click(function(){
		$('#pi1_tb tbody').find("tr:nth-child(8)").find("td:nth-child(3)").show();
		$('#pi1_tb tbody').find("tr:nth-child(8)").find("td:nth-child(4)").show();
		$('#exp_tb').show();
		$('#sal1_tb tbody').find("tr").slice(3).show();
		$('#page_2_sections').show();
		// changing CWID to SSN if the student is selected
		$('#pi1_tb tbody tr:nth-child(5) td:nth-child(3)').text("SSN");
		$('#pi1_tb tbody tr:nth-child(5) td:nth-child(3)').css("font-weight","bold");
		$('#ei1_tb').show();
		$('#req_sal').show();
		$("#sal1_tb tbody tr:nth-child(2) td:nth-child(2)").show();
		$('#details_tb tbody tr').each(function(){
			// $('input[id*=major_budget]',this).removeAttr("readonly");
			// $('input[id*=major_budget]',this).removeAttr("tabindex");
		});
	});

	$('#student').click(function(){
		hideFacultyElements();
	});

	$('#doctorate').click(function() {
		if($('#doctorate').is(":checked"))
		{
			$('#univ_doc').val("N/A");
			$('#years_doc').val("N/A");
		}
		else
		{
			$('#univ_doc').val("");
			$('#years_doc').val("");
		}
	});

	$('#master').click(function() {
		if($('#master').is(":checked"))
		{
			$('#univ_master').val("N/A");
			$('#years_master').val("N/A");
		}
		else
		{
			$('#univ_master').val("");
			$('#years_master').val("");
		}
	});

	$('#bachelor').click(function() {
		if($('#bachelor').is(":checked"))
		{
			$('#univ_bachelor').val("N/A");
			$('#years_bachelor').val("N/A");
		}
		else
		{
			$('#univ_bachelor').val("");
			$('#years_bachelor').val("");
		}
	});

	// Restring the user to enter less than 20 hours in hours per week field
	$('#hours_per_week').keyup(function(){
		if($('#hours_per_week').val()>20 || isNaN($('#hours_per_week').val().trim()) || $('#hours_per_week').val().trim()=="")
		{
			$('#hours_per_week').val("");
		}
	});

});
function check_previous_forms(email){
	var email_value = $('#email').val();
	/*var arf_start_date = $('#major_time_from').val();
	var arf_end_date = $('#major_time_to').val();
	alert(arf_start_date);*/

	// Getting name from LDAP using ajax
	$.ajax({
		url:"https://forms.latech.edu/plan_of_study/lib/ajax/ldap.php",
		data:{ldap_email:email_value},
		type:"POST",
		context:this,
		success: function(data){
			if(data.trim()=="++")
				validateSSN = false;
			else
				validateSSN = true;
			var name = data.split('+');
			if(name.length==3)
			{
				$('#lname').val(name[2]);
				$('#fname').val(name[0]);
				$('#mname').val(name[1]);
			}
			else if(name.length==2)
			{
				$('#lname').val(name[1]);
				$('#fname').val(name[0]);
			}
		}
	});

	// Fetching details of student from DB
	$.ajax({
		type: "POST",
		url: "includes/ajax_function_calls.php",
		data: {action:"fetchDetails", email: email_value},
		success: function(data){
				if(data!="")
				{
					var studentInfo = $.parseJSON(data);
					// setting all the elements to the values fetched
					$('#street').val(studentInfo.street);
					$('#city').val(studentInfo.city);
					$('#zip').val(studentInfo.zip);
					$('#datepicker1').val(studentInfo.date_effective);
					$('#ssn').val(studentInfo.ssn);
					$('#datepicker2').val(studentInfo.dob);
					if(studentInfo.sex==1)
						$('#sex').prop("checked",true);
					else
						$('#female').prop("checked",true);
					$('#marital option[value='+ studentInfo.marital +']').attr("selected","selected");
					if(studentInfo.race==1)
					{
						$('#hispanic').prop("checked",true);
					}
					else
					{
						$('#nonhispanic').prop("checked",true);
					}

					$('#raceList').val(studentInfo.raceList);
					$('#nationality').val(studentInfo.nationality);
					$('select[name="dept"]').val(studentInfo.dept);
					$('#rank').val(studentInfo.rank);
				}
				else
				{
					// resetting all values to blank
					// setting all the elements to the values fetched
					$('#street').val("");
					$('#city').val("");
					$('#zip').val("");
					$('#datepicker1').val("");
					$('#ssn').val("");
					$('#datepicker2').val("");
					$('#sex').prop("checked",false);
					$('#female').prop("checked",false);
					$('#marital').val("Select");
					$('#race').val("Select one");
					$('#nationality').val("");
					$('#visa_no').val("");
					$('select[name="dept"]').val("Select Department");
					$('#rank').val("");
				}
			}
    });
}


function validate(id){
	var match = "";
	if($('#pi1_tb tbody tr:nth-child(5) td:nth-child(3)').text()=="CWID")
		match = /(\d{0,3})(\d{0,2})(\d{0,3})/;
	else
		match = /(\d{0,3})(\d{0,2})(\d{0,4})/;

	var value = document.getElementById(id).value;
		document.getElementById(id).value = value
			.match(/\d*/g).join('')
			.match(match).slice(1).join('-')
			.replace(/-*$/g, '');
}

// Checks if the Supervisor's e-mail is a student e-mail. If yes, empties it out.
function validateEmail(email){
	if(email.length==6 && email.match(/^[a-zA-Z]{3}[\d]{3}/))
	{
		$('#supervisor_email').val('');
	}
}


// Ensures starting digit is either 1, 2, 3 or 6 and the second digit is 2.  Only accepted digits are 12, 22, 32, and 62.
function dept_code_format(id){

	var value = document.getElementById(id).value;
	var length = value.length;
	if((length==1 && value.match(/^[1,2,3,6].*/)) || (length==2 && value.match(/^[1,2,3,6]2.*/)) || length>2)
	{
		if(value.length<14)
		{
			document.getElementById(id).value = value
				.match(/\d*/g).join('')
				.match(/(\d{0,2})(\d{0,4})(\d{0,5})/).slice(1).join('-')
				.replace(/-*$/g, '')
			;
		}
		// checking the last character
		else
		{
			// if the last character is not an alphabet, remove it
			if(!value[13].match(/[a-zA-Z]/i))
			{
				document.getElementById(id).value = value.split('').reverse().join('').replace(/[^a-zA-z]/i,'').split('').reverse().join('');
			}

		}
	}
	else
	{
		$("#"+id).val('');
	}
}


// Delete form on delete request
function deleteForm(id){
	var r=confirm("Are you sure you want to delete this Appointment Request form?");
	if(r==true){
		window.location="https://forms.latech.edu/ARF/admin/forms_status.php?del="+id;
	}
}



// Adding new rows
function addRow(table_id, section){
	var addRow = true;
	// restricting the number of rows of source of funds table to 8
	if(section == '5' && $('#'+ table_id+ ' tbody tr').size()>7)
		addRow = false;

	if(addRow)
	{
		var row="";
		if(section=="1"){
			var row_table_1 = $('#higher_edu_tb tr').length-3; /*table1.rows.length - 1;*/

			row="<td><input type=\"text\" name=\"a_univ_exp[]\" id=\"a_univ_exp_"+(row_table_1+1)+"\"></td><td><input type=\"text\" name=\"a_position_exp[]\" id=\"a_position_exp_"+(row_table_1+1)+"\"></td><td><input type=\"text\" name=\"a_from_exp[]\" id=\"a_from_exp_"+(row_table_1+1)+"\" class=\"dp\"></td><td><input type=\"text\" name=\"a_to_exp[]\" id=\"a_to_exp_"+(row_table_1+1)+"\" class=\"dp\"></td>";
			row_table_1++;

		}
		else if(section=="2"){
			var row_table_2 = $('#other_edu_tb tr').length-3;

			row="<td><input type=\"text\" name=\"b_exp[]\" id=\"b_exp_"+(row_table_2+1)+"\"></td><td><input type=\"text\" name=\"b_position_exp[]\" id=\"b_position_exp_"+(row_table_2+1)+"\"></td><td><input type=\"text\" name=\"b_from_exp[]\" id=\"b_from_exp_"+(row_table_2+1)+"\" class=\"dp\"></td><td><input type=\"text\" name=\"b_to_exp[]\" id=\"b_to_exp_"+(row_table_2+1)+"\" class=\"dp\"></td>";
			row_table_2++;
		}
		else if(section=="3"){
			var row_table_3 = $('#other_tb tr').length-3;

			row="<td><input type=\"text\" name=\"c1_univ_exp[]\" id=\"c1_univ_exp_"+(row_table_3+1)+"\"></td><td><input type=\"text\" name=\"c1_position_exp[]\" id=\"c1_position_exp_"+(row_table_3+1)+"\"></td><td><input type=\"text\" name=\"c1_from_exp[]\" id=\"c1_from_exp_"+(row_table_3+1)+"\" class=\"dp\"></td><td><input type=\"text\" name=\"c1_to_exp[]\" id=\"c1_to_exp_"+(row_table_3+1)+"\" class=\"dp\"></td>";
			row_table_3++;

		}
		else if(section=="4"){
			var row_table_4 = $('#other_tb1 tr').length-3;

			row="<td><input type=\"text\" name=\"c2_univ_exp[]\" id=\"c2_univ_exp_"+(row_table_4+1)+"\"></td><td><input type=\"text\" name=\"c2_position_exp[]\" id=\"c2_position_exp_"+(row_table_4+1)+"\"></td><td><input type=\"text\" name=\"c2_from_exp[]\" id=\"c2_from_exp_"+(row_table_4+1)+"\" class=\"dp\"></td><td><input type=\"text\" name=\"c2_to_exp[]\" id=\"c2_to_exp_"+(row_table_4+1)+"\" class=\"dp\"></td>";
			row_table_4++;

		}
		else if(section=="5"){
			var row_table_5 = $('#details_tb tr').length-3;
			// condition for making Budget Page & Line element disable
			var disabled="";
			var tabIndex = '';
			if($('#student').is(':checked'))
			{
				disabled = "readonly";
				tabIndex = "tabindex = '-1'";
			}
			else
			{
				disabled = "";
				tabIndex = '';
			}
			row="<td><input type=\"text\" name=\"major_dc[]\" onBlur=\"checkResearchFund()\" id=\"major_dc_"+(row_table_5+1)+"\" onKeyUp=\"dept_code_format('major_dc_"+(row_table_5+1)+"')\" maxlength=\"14\"></td><td><input type=\"text\" name=\"major_budget[]\" id=\"major_budget_"+(row_table_5+1)+"\"></td><td><input type=\"text\" name=\"major_amt[]\" id=\"major_amt_"+(row_table_5+1)+"\" onBlur=\"this.value=formatCurrency(this.value);updateTotalMonthlyAmount();updatePercentage(this);\"></td><td><input type=\"text\" readonly name=\"major_perc[]\" id=\"major_perc_"+(row_table_5+1)+"\"></td><td><input type=\"text\" name=\"major_fund[]\" readonly id=\"major_fund_"+(row_table_5+1)+"\"></td>";
			row_table_5++;
		}

		$("#"+table_id).each(function () {
			if(table_id=="higher_edu_tb")
				document.getElementById('remove_table_1').style.display="inline";
			else if(table_id=="other_edu_tb")
				document.getElementById('remove_table_2').style.display="inline";
			else if(table_id=="other_tb")
				document.getElementById('remove_table_3').style.display="inline";
			else if(table_id=="other_tb1")
				document.getElementById('remove_table_4').style.display="inline";
			else if(table_id=="details_tb")
				document.getElementById('remove_table_5').style.display="inline";

			var tds = '<tr>';
				tds+=row;
				tds += '</tr>';
			if ($('tbody', this).length > 0) {
				$('tbody', this).append(tds);
			} else {
				$(this).append(tds);
			}
		});

		$( ".dp" ).datepicker({
			inline: true
		});
	}
}

// Remove rows
function removeRow(table_id){
	$("#"+table_id).each(function(){

		// table 1, hide remove button
		if($('tbody tr', this).length==2 && table_id=="higher_edu_tb"){
			document.getElementById('remove_table_1').style.display="none";
		}

		// table 2, hide remove button
		if($('tbody tr', this).length==2 && table_id=="other_edu_tb"){
			document.getElementById('remove_table_2').style.display="none";
		}

		// table 3, hide remove button
		if($('tbody tr', this).length==2 && table_id=="other_tb"){
			document.getElementById('remove_table_3').style.display="none";
		}

		// table 4, hide remove button
		if($('tbody tr', this).length==2 && table_id=="other_tb1"){
			document.getElementById('remove_table_4').style.display="none";
		}

		// table 5, hide remove button
		if($('tbody tr', this).length==2 && table_id=="details_tb"){
			document.getElementById('remove_table_5').style.display="none";
		}

		if($('tbody', this).length>0 && $('tbody tr', this).length>1){
			$('tbody tr:last', this).remove();
			updateTotalPercent();
			updateTotalMonthlyAmount();
			updateTotalfund();
		}
	});
}
// Ajax to delete a file from the form
function removeFile(name, field, id)
{
	var xmlhttp;
	if(name=="")
	{
		return;
	}

	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function()
	{
		if(xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			document.getElementById(id).innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","secure/rm_file.php?fName="+name+"&field="+field, true);
	xmlhttp.send();
}

// Validation to check if fields are empty
function validateForm(){

	var guilty_id="";
	var required_fields;
	var required_fields_description;

	if(validateSSN)
	{
		if($('#student').is(':checked'))
		{
			required_fields=new Array('appointment','datepicker','lname','fname','street','city','zip','datepicker1','email','ssn','datepicker2','sex','marital','race','nationality','dept','hours_per_week','dept_head','dean');
			required_fields_description=new Array("Appointment Type","Date","Last Name","First Name","Street","City","Zip Code","Date Effective","Email Address","Social Security Number/CWID","Date of Birth","Sex","Marital Status","Race","Nationality","Department","Hours per Week","Department Head","Dean");
		}
		else
		{
			required_fields=new Array('appointment','datepicker','lname','fname','street','city','zip','datepicker1','email','ssn','datepicker2','sex','marital','race','nationality','dept','rank','univ_doc','years_doc','univ_master','years_master','univ_bachelor','years_bachelor','dept_head','dean');
			required_fields_description=new Array("Appointment Type","Date","Last Name","First Name","Street","City","Zip Code","Date Effective","Email Address","Social Security Number/CWID","Date of Birth","Sex","Marital Status","Race","Nationality","Department","Rank/Discipline","Doctorate University","Doctorate Year","Master Univeristy","Master Year","Bachelor University","Bachelor Year","Department Head","Dean");
		}
	}
	else
	{
		if($('#student').is(':checked'))
		{
			required_fields=new Array('appointment','datepicker','lname','fname','street','city','zip','datepicker1','email','datepicker2','sex','marital','race','nationality','dept','hours_per_week','dept_head','dean');
			required_fields_description=new Array("Appointment Type","Date","Last Name","First Name","Street","City","Zip Code","Date Effective","Email Address","Date of Birth","Sex","Marital Status","Race","Nationality","Department","Hours per Week","Department Head","Dean");
		}
		else
		{
			required_fields=new Array('appointment','datepicker','lname','fname','street','city','zip','datepicker1','email','datepicker2','sex','marital','race','nationality','dept','rank','univ_doc','years_doc','univ_master','years_master','univ_bachelor','years_bachelor','dept_head','dean');
			required_fields_description=new Array("Appointment Type","Date","Last Name","First Name","Street","City","Zip Code","Date Effective","Email Address","Date of Birth","Sex","Marital Status","Race","Nationality","Department","Rank/Discipline","Doctorate University","Doctorate Year","Master Univeristy","Master Year","Bachelor University","Bachelor Year","Department Head","Dean");
		}
	}

	// If grant PI information is required, make it mandotary
	var css = $('.grants_fund_only').css('display');
	if(css == "table-row") {
		required_fields_description = required_fields_description.concat(["Grant PI Name", "Grant PI E-mail"]);
		required_fields = required_fields.concat(["proj_director_name", "proj_director_email"]);
	}

	var txt="";

	for(var i=0; i<required_fields.length; i++){
		var fieldValue=document.forms['myform'].elements[required_fields[i]].value;

		if(i==8){
				var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
				if(!fieldValue.trim().match(mailformat) && fieldValue!=""){
					txt=txt+" - Invalid LaTech E-mail Address \n";
					if(guilty_id=="")
					     guilty_id="email";
				}
			}

		if(fieldValue=="" || fieldValue=="blank"){
			txt=txt+" - "+required_fields_description[i]+"\n";
			if(guilty_id=="")
				guilty_id=required_fields[i];
		}
	}

	/*if(parseFloat($('#hours_per_week').val())>20)
	{
		if(guilty_id=="")
			$('#hours_per_week').focus();
		txt = txt + " - " + "Number of hours per week should be <= 20.\n";
	}*/

	if($('#base_amt').val().replace(/[$,]/g,'')!=$('#total_fund').text().replace(/[$,]/g,''))
	{
		if(guilty_id=="")
			$('#base_amt').focus();
		txt = txt + " - " + "Base amount and Total fund should be equal.\n";
	}
	if($('#total_percent').text()!=="100.00%")
	{
		if(guilty_id=="")
			$('#total_percent').focus();
		txt = txt + " - " + "Total % of funds should be 100.\n";
	}
	if($('#minimumWageWarning').length)
	{
		if(guilty_id=="")
			$('#hours_per_week').focus();
		txt = txt + " - " + "Monthly rate is less than the Minimum wage.\n";
	}

	if(txt==""){
		return true;
	}
	else{
		alert("You need to complete the following details to proceed: \n"+txt);
		if(guilty_id!=""){
			document.getElementById(guilty_id).focus();
			guilty_id="";
		}
		return false;
	}
}

// Change layout to make it printer-friendly
function printReady(){
	document.getElementById('t_scroller').style.display="none";
	document.body.style.margin="0";
	document.body.style.background="none";
	document.getElementById('main_div').style.boxShadow="none";
	document.getElementById('main_div').style.padding="0";
	document.getElementById('main_div').style.marginTop="0";

	var form_preview_box=document.getElementsByClassName('div_preview');

	for(var i=0; i<form_preview_box.length; i++){
		form_preview_box[i].setAttribute('style', 'background-color: #FFFFFF !important; font-size: 10pt !important');
		form_preview_box[i].style.border="1px solid #555";
		form_preview_box[i].style.paddingBottom="2px";
		form_preview_box[i].style.boxShadow="inset 1px 1px 2px #DDD";
	}

	var form_preview_empty=document.getElementsByClassName('div_preview_empty');
	for(var i=0; i<form_preview_empty.length; i++){
		form_preview_empty[i].setAttribute('style', 'background-color: #EEEEEE !important; font-size: 10pt !important');
		form_preview_empty[i].style.border="1px solid #888";
		form_preview_empty[i].style.paddingBottom="2px";
		form_preview_empty[i].style.boxShadow="inset 1px 1px 2px #DDD";
	}

	var sig_darker=document.getElementsByClassName('sig_darker');
	for(var i=0; i<sig_darker.length; i++){
		sig_darker[i].style.background="#EEE";
	}

	var th=document.getElementsByTagName('th');
	for(var i=0; i<th.length; i++){
		th[i].style.background="#DDD";
		th[i].style.fontWeight="bold";
		th[i].style.color="#333";
	}

	document.getElementById('timeline').style.display="none";
	document.getElementById('feedback').style.display="none";
	document.getElementById('footer').style.display="none";

	document.getElementById('printer').innerHTML="<font color=\"#BBBBBB\" style=\"cursor: pointer\" title=\"Exit printer-friendly version!\" onclick=\"location.reload()\">[x]</font>";
	document.getElementById('remarks_notification').style.display="none";
}


function updateTotalfund(){
	var totalFund = 0.00;
	$('#details_tb tbody tr').each(function(){
		var totalFundamount = $("td:nth-child(5)",this).find("input").val().replace(/[$,]/g,'');
		if(totalFundamount.trim()!="" && !isNaN(totalFundamount))
			totalFund = totalFund + parseFloat(totalFundamount);
		else
			totalFund = totalFund + 0.00;
	});

	$('#total_fund').text(formatCurrency(totalFund));
	if($('#base_amt').val().replace(/[$,]/g,'')!=$('#total_fund').text().replace(/[$,]/g,''))
		$('#total_fund').css("color","RED");
	else
		$('#total_fund').css("color","GREEN");

}

function updateTotalMonthlyAmount(){
	var totalMonthlyAmount = 0.00;
	$('#details_tb tbody tr').each(function(){
		var monthlyAmount = $("td:nth-child(3)",this).find("input").val().replace(/[$,]/g,'');
		if(monthlyAmount.trim()!="" && !isNaN(monthlyAmount))
			totalMonthlyAmount = totalMonthlyAmount + parseFloat(monthlyAmount);
		else
			totalMonthlyAmount = totalMonthlyAmount + 0.00;
	});
	$('#total_monthly_amount').text(formatCurrency(totalMonthlyAmount));
}

// Updating base salary and amounts from source of funds table when the monthly amount changes
function calculateAmounts(){

	var major_time_from = $('#major_time_from').val();
	var major_time_from_values = major_time_from.split("/");
	var major_time_to = $('#major_time_to').val();
	var major_time_to_values = major_time_to.split("/");

	// default base salary based on number of months
	if(major_time_from!="" && major_time_to!="")
	{
		var base_sal = ((major_time_to_values[2]-major_time_from_values[2])*12 + (major_time_to_values[0]-major_time_from_values[0]) + 1) * (parseFloat($('#monthly_amt').val().replace(/[$,]/g,'')));
		$('#base_amt').val(formatCurrency(base_sal));
	}
	else
		$('#base_amt').val($('#monthly_amt').val());

	// Checking if pro-rated check boxes exist, meaning that adjusting monthly amount, and caling the respective functions.
	if($('#major_time_warning_check').length)
		calculateAmountsWhenMajorTimeWarning();
	if($('#major_time_from_warning_check').length)
		calculateAmountsWhenMajorTimeFromWarning();
	if($('#major_time_to_warning_check').length)
		calculateAmountsWhenMajorTimeToWarning();

	// function to update the amounts from source of funds table
	updateSourceOfFunds();

}

// Update the total percent on Source of Funds.
function updateTotalPercent(){
	var totalPercent = 0.00;
	var percent = 0;
	$('#details_tb tbody tr').each(function(){
		percent = $("td:nth-child(4)",this).find("input").val().trim().replace('%','');
		if(percent!="" && !isNaN(percent))
			totalPercent = totalPercent + parseFloat(percent);
	});

	$('#total_percent').text(totalPercent.toFixed(2)+"%");
	if($('#total_percent').text()!=="100.00%")
		$('#total_percent').css("color","RED");
	else
		$('#total_percent').css("color","GREEN");
}

// Show Grant PI and Budget Verification Lines, if grant account
function showGrantAccountSignatures() {
	var major_dc_info = document.getElementsByName('major_dc[]');
	for(var i = 0; i < major_dc_info.length; i++) {
		var values = major_dc_info[i].value.split("-");
		if(values[0] == 32 && (values[2] > 40000 && values[2] < 60000)) {
			$('.grants_fund_only').css('display', 'table-row');
			return;
		}
	}
}

function hideFacultyElements(){
	$('#pi1_tb tbody').find("tr:nth-child(8)").find("td:nth-child(3)").hide();
	$('#pi1_tb tbody').find("tr:nth-child(8)").find("td:nth-child(4)").hide();
	$('#exp_tb').hide();
	$('#sal1_tb tbody').find("tr").slice(3).hide();
	$('#page_2_sections').hide();
	// changing SSN to CWID if the student is selected
	$('#pi1_tb tbody tr:nth-child(5) td:nth-child(3)').text("CWID");
	$('#pi1_tb tbody tr:nth-child(5) td:nth-child(3)').css("font-weight","bold");
	$('#ei1_tb').hide();
	$('#req_sal').hide();
	$("#sal1_tb tbody tr:nth-child(2) td:nth-child(2)").hide();
	$('#details_tb tbody tr').each(function(){
		// $('input[id*=major_budget]',this).attr("readonly",true);
		// $('input[id*=major_budget]',this).attr("tabindex","-1");
	});
}

function calculateYearlySalary(){
	$('#req_sal').val(formatCurrency($('#monthly_amt').val().replace(/[$,]/g,'')*12));
}


function checkForWarnings(){
	var major_time_from = $('#major_time_from').val();
	var major_time_from_values = major_time_from.split("/");
	var major_time_to = $('#major_time_to').val();
	var major_time_to_values = major_time_to.split("/");
	base_sal_count = 0;
	major_time_from_sal = 0.0;
	major_time_to_sal = 0.0;
	var date = new Date();
	var year = date.getFullYear();
	var month = (date.getMonth())+1;
	if(month>6)
		year = year+1;

	// Removing all warning messages
	$('#arf_split_warning').remove();


	if(major_time_from != "" && major_time_to != "")
	{
		// if major time to year is in not in the current academimc year
		if(major_time_to_values[2] >= year && major_time_to_values[0]>6)
		{
			// if from and to years are same, from date month should be before july
			if(major_time_from_values[2] == major_time_to_values[2])
			{
				if(major_time_from_values[0]<7)
				{
					$('#warning_messages').append("<p id='arf_split_warning'>Your form will be split into two or more.");
				}
			}
			// else month can be any
			else
			{
				$('#warning_messages').append("<p id='arf_split_warning'>Your form will be split into two or more.");
			}
		}

		// if from and to date are in same month and year
		if((major_time_from_values[0] == major_time_to_values[0]) && (major_time_from_values[2] == major_time_to_values[2]))
		{
			// Removing major time and from warnings if set before
			removeMajorTimeFromWarnings();
			removeMajorTimeToWarnings();
			calculateAmountsWhenMajorTimeWarning();
			var daysInMonth = new Date(major_time_to_values[2],major_time_to_values[0],0).getDate();
			// calculating salary
			if(major_time_from_values[1]>1 || major_time_to_values[1]<daysInMonth)
			{
				if($('#major_time_warning_check').length)
				{
					// updating the month/year on check box label
					$('#major_time_warning_check_label').text('Pro-rate salary for the month (' + getMonthName(major_time_from_values[0]) + ", " + major_time_from_values[2] + ')?');

				}
				else{
					// adding check box for asking the user if they want to calculate the salary on pro-rated
					$('#warning_messages').append('<input name="major_time_warning_check" id="major_time_warning_check" value="yes" type="checkbox"><label id="major_time_warning_check_label" for="major_time_warning_check"> Pro-rate salary for the month (' + getMonthName(major_time_from_values[0]) + ", " +major_time_from_values[2]  + '?</label>');
					$('#warning_messages').append("<p id='major_time_warning'> Salary of the month (" + getMonthName(major_time_from_values[0]) + ", " +major_time_from_values[2] + ") is: " + $('#monthly_amt').val() + ".");
				}
			}
			else
			{
				// Removing major time warnings if set before
				removeMajorTimeWarnings();
			}

		}
		// else
		else
		{
			// Removing major time warnings if set before
			removeMajorTimeWarnings();
			// checking for major time from date
			if(major_time_from_values[1]>1)
			{
				base_sal_count++;
				major_time_from_sal = parseFloat($('#monthly_amt').val().replace(/[$,]/g,''));
				if($('#major_time_from_warning_check').length)
				{
					// updating the month/year on check box label
					$('#major_time_from_warning_check_label').text('Pro-rate salary for the first month (' + getMonthName(major_time_from_values[0]) + ", " + major_time_from_values[2] + ')?');
					calculateAmountsWhenMajorTimeFromWarning();
				}
				else{
					// Asking the user if they want to caluclate the salary on pro-rated.
					$('#warning_messages').append('<input name="major_time_from_warning_check" id="major_time_from_warning_check" value="yes" type="checkbox"><label id="major_time_from_warning_check_label" for="major_time_from_warning_check"> Pro-rate salary for the first month (' + getMonthName(major_time_from_values[0]) + ", " + major_time_from_values[2] + ')?</label>');
					$('#warning_messages').append("<p id='major_time_from_warning'> Salary for the first month (" + getMonthName(major_time_from_values[0]) + ", " +major_time_from_values[2] + ") is: " + $('#monthly_amt').val() + ".");
				}
			}
			else
				removeMajorTimeFromWarnings();

			var daysInMonth = new Date(major_time_to_values[2],major_time_to_values[0],0).getDate();

			// // checking for major time to date
			if(major_time_to_values[1]<daysInMonth)
			{
				base_sal_count++;
				major_time_to_sal = parseFloat($('#monthly_amt').val().replace(/[$,]/g,''));
				if($('#major_time_to_warning_check').length)
				{
					// updating the month/year on check box label
					$('#major_time_to_warning_check_label').text('Pro-rate salary for the last month (' + getMonthName(major_time_to_values[0]) + ", " + major_time_to_values[2] + ')?');
					calculateAmountsWhenMajorTimeToWarning();
				}
				else{
					// Asking the user if they want to caluclate the salary on pro-rated.
					$('#warning_messages').append('<input name="major_time_to_warning_check" id="major_time_to_warning_check" value="yes" type="checkbox"><label id="major_time_to_warning_check_label" for="major_time_to_warning_check"> Pro-rate salary for the last month (' + getMonthName(major_time_to_values[0]) + " " +major_time_to_values[2] + ')?</label>');
					$('#warning_messages').append("<p id='major_time_to_warning'> Salary for the last month (" + getMonthName(major_time_to_values[0]) + " " +major_time_to_values[2]  + ") is: " + $('#monthly_amt').val() + ".");
				}
			}
			else
				removeMajorTimeToWarnings();

			var base_sal = ((major_time_to_values[2]-major_time_from_values[2])*12 + (major_time_to_values[0]-major_time_from_values[0]) + 1 - base_sal_count) * (parseFloat($('#monthly_amt').val().replace(/[$,]/g,'')));
			base_sal = base_sal + major_time_from_sal + major_time_to_sal;
			$('#base_amt').val(formatCurrency(base_sal));

			// Added 1/21/2016 to fix same month (first to end) error
			// calculateAmountsWhenMajorTimeWarning();"
		}
		updateSourceOfFunds();
	}
}


//Format currency into US system on text fields
function formatCurrency(num)
{
	num=num.toString().replace(/\$|\,/g,'');
	if(isNaN(num))
		num="0";
		sign=(num==(num=Math.abs(num)));
		cents=((Math.round(num*100.00)) | 0) % 100;

		num=Math.floor(num*100+0.50000000001);
		num=Math.floor(num/100).toString();

		if(cents<10)
			cents="0"+cents;

		for(var i=0;i<Math.floor((num.length-(1+i))/3);i++)
			num=num.substring(0,num.length-(4*i+3))+','+num.substring(num.length-(4*i+3));

		return(((sign)?'':'-')+'$'+num+'.'+cents);
}

Number.prototype.format = function(n, x) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
};

function checkForOverlap(){
	var major_time_from = $('#major_time_from').val();
	var major_time_to = $('#major_time_to').val();
	var email = $('#email').val();
	$.ajax({
		url:"includes/ajax_function_calls.php",
		data:{action:"checkForOverlap",major_time_from:major_time_from,major_time_to:major_time_to,email:email},
		type:"POST",
		success: function(data){
			if(data=="yes")
			{
				$('#overlapDates').remove();
				$('#warning_messages').append("<p id='overlapDates'>There pre-exists an ARF for this individual with overlapping dates; therefore, initiating this ARF will replace the pre-existing ARF's overlapping period.");
			}
			else
				$('#overlapDates').remove();
		}
	});
}

function updatePercentage(monthlyElement){
	var percent = "0.0000";

	if(!($('#monthly_amt').val()=='' || $(monthlyElement).val().replace(/[$,]/g,'')==''))
		percent = ((parseFloat($(monthlyElement).val().replace(/[$,]/g,''))/parseFloat($('#monthly_amt').val().replace(/[$,]/g,'')))*100).toFixed(4);

	/* Works only with one source of fund. Needs to get fixed. */
	// When percent calculated is non-numeric
	if(isNaN(percent)) {
		percent = "100.00";
	}


	// upating total funds amount
	if($('#base_amt').val()!="")
		$(monthlyElement).parent('td').siblings('td').find('input[name*=major_fund]').val("$" + ((percent * parseFloat($('#base_amt').val().replace(/[$,]/g,'')))/100).format(4, 3));
	else
		$(monthlyElement).parent('td').siblings('td').find('input[name*=major_fund]').val('$0.0000');

	$(monthlyElement).parent('td').siblings('td').find('input[name*=major_perc]').val(percent+"%");
	updateTotalPercent();
	updateTotalfund();
}

function checkMinimumWage() {
	if($('#hours_per_week').val().trim()!='' && $('#monthly_amt').val().trim()!="")
	{
		var totalHrsPerYear = 52 * parseInt($('#hours_per_week').val(), 10);
		var yearlySalary = 7.25 * totalHrsPerYear;
		var monthlyPay = yearlySalary / 12;

		if(monthlyPay > parseFloat($('#monthly_amt').val().trim().replace(/[$,]/g,'')))
		{
			$('#minimumWageWarning').remove();
			$('#warning_messages').append('<p id = "minimumWageWarning"> Monthly rate is less than the minimum wage.');
		}
		else
		{
			$('#minimumWageWarning').remove();
		}
	}
}



// Calculating base salary and other amounts from source of fund table. This function will be triggered when both
// from date month and to date month are from same month and either from date or to date or both starts or ends
// other than start and end date of month
function calculateAmountsWhenMajorTimeWarning(){
	var salaryOfTheMonth = 0.0;
	var major_time_from = $('#major_time_from').val();
	var major_time_from_values = major_time_from.split("/");
	var major_time_to = $('#major_time_to').val();
	var major_time_to_values = major_time_to.split("/");
	var daysInMonth = new Date(major_time_to_values[2],major_time_to_values[0],0).getDate();
	// Removing any previous warnings
	$('#major_time_warning').remove();
	// calculating the salary based on the checkbox
	if($('#major_time_warning_check').is(':checked'))
	{
		salaryOfTheMonth = formatCurrency(((parseFloat($('#monthly_amt').val().replace(/[$,]/g,''))/daysInMonth)*(major_time_to_values[1]-major_time_from_values[1]+1)));
		$('#major_time_warning_check_label').after("<p id='major_time_warning'>The salary will be paid based on the pro-rated amount. Salary of the month (" + getMonthName(major_time_from_values[0]) + ", " +major_time_from_values[2]  + ") is: " + salaryOfTheMonth + ".");
	}
	else
	{
		salaryOfTheMonth = $('#monthly_amt').val();
		$('#major_time_warning_check_label').after("<p id='major_time_warning'> Salary of the month (" + getMonthName(major_time_from_values[0]) + ", " +major_time_from_values[2]  + ") is: " + salaryOfTheMonth + ".");
	}

	// Base salary
	if($('#monthly_amt').val().trim()!="")
	{
		$('#base_amt').val(salaryOfTheMonth);
	}
	else
	{
		$('#base_amt').val("$0.00");
	}

	// function to update the amounts from source of funds table
	updateSourceOfFunds();
}

function calculateAmountsWhenMajorTimeFromWarning(){
	var salaryOfTheMonth = 0.0;
	var major_time_from = $('#major_time_from').val();
	var major_time_from_values = major_time_from.split("/");
	var major_time_to = $('#major_time_to').val();
	var major_time_to_values = major_time_to.split("/");
	$('#major_time_from_warning').remove();

	if($('#major_time_from_warning_check').is(':checked')){
		var daysInMonth = new Date(major_time_from_values[2],major_time_from_values[0],0).getDate();
		major_time_from_sal = ((parseFloat($('#monthly_amt').val().replace(/[$,]/g,''))/daysInMonth)*(daysInMonth-major_time_from_values[1]+1));
		salaryOfTheMonth = formatCurrency(major_time_from_sal);
		$('#major_time_from_warning_check_label').after("<p id='major_time_from_warning'>The salary will be paid based on the pro-rated amount. Salary for the first month (" + getMonthName(major_time_from_values[0]) + ", " +major_time_from_values[2]  + ") is: " + salaryOfTheMonth + ".");
	}
	else{
		major_time_from_sal = parseFloat($('#monthly_amt').val().replace(/[$,]/g,''));
		salaryOfTheMonth = $('#monthly_amt').val();
		$('#major_time_from_warning_check_label').after("<p id='major_time_from_warning'> Salary for the first month (" + getMonthName(major_time_from_values[0]) + ", " +major_time_from_values[2]  + " is: " + salaryOfTheMonth + ".");
	}

	// Base salary
	if($('#monthly_amt').val().trim()!="")
	{
		var base_sal = ((major_time_to_values[2]-major_time_from_values[2])*12 + (major_time_to_values[0]-major_time_from_values[0]) + 1 - base_sal_count) * (parseFloat($('#monthly_amt').val().replace(/[$,]/g,'')));
		base_sal = base_sal + major_time_from_sal + major_time_to_sal;
		$('#base_amt').val(formatCurrency(base_sal));
	}
	else
	{
		$('#base_amt').val("$0.00");
	}

	// function to update the amounts from source of funds table
	updateSourceOfFunds();
}

function calculateAmountsWhenMajorTimeToWarning(){
	var salaryOfTheMonth = 0.0;
	var major_time_from = $('#major_time_from').val();
	var major_time_from_values = major_time_from.split("/");
	var major_time_to = $('#major_time_to').val();
	var major_time_to_values = major_time_to.split("/");
	$('#major_time_to_warning').remove();

	if($('#major_time_to_warning_check').is(':checked')){
		var daysInMonth = new Date(major_time_to_values[2],major_time_to_values[0],0).getDate();
		major_time_to_sal = ((parseFloat($('#monthly_amt').val().replace(/[$,]/g,''))/daysInMonth)*(major_time_to_values[1]));
		salaryOfTheMonth = formatCurrency(major_time_to_sal);
		$('#major_time_to_warning_check_label').after("<p id='major_time_to_warning'>The salary will be paid based on the pro-rated amount. Salary for the last month (" + getMonthName(major_time_to_values[0]) + ", " +major_time_to_values[2]  + ") is: " + salaryOfTheMonth + ".");
	}
	else{
		major_time_to_sal = parseFloat($('#monthly_amt').val().replace(/[$,]/g,''));
		salaryOfTheMonth = $('#monthly_amt').val();
		$('#major_time_to_warning_check_label').after("<p id='major_time_to_warning'> Salary for the last month (" + getMonthName(major_time_to_values[0]) + ", " +major_time_to_values[2]  + ") is: " + salaryOfTheMonth + ".");
	}

	// Base salary
	if($('#monthly_amt').val().trim()!="")
	{
		var base_sal = ((major_time_to_values[2]-major_time_from_values[2])*12 + (major_time_to_values[0]-major_time_from_values[0]) + 1 - base_sal_count) * (parseFloat($('#monthly_amt').val().replace(/[$,]/g,'')));
		base_sal = base_sal + major_time_from_sal + major_time_to_sal;
		$('#base_amt').val(formatCurrency(base_sal));
	}
	else
	{
		$('#base_amt').val("$0.00");
	}

	// function to update the amounts from source of funds table
	updateSourceOfFunds();
}


// function to update source of fund amounts for each of the row
function updateSourceOfFunds(){
	$('#details_tb tbody tr').each(function(){
		updateTotalMonthlyAmount();
		var monthly_amount = $('input[name^="major_amt"]',this);
		updatePercentage($(monthly_amount));
	});
}

function removeMajorTimeWarnings(){
	$('#major_time_warning').remove();
	$('#major_time_warning_check').remove();
	$('#major_time_warning_check_label').remove();
}

function removeMajorTimeFromWarnings(){
	$('#major_time_from_warning').remove();
	$('#major_time_from_warning_check').remove();
	$('#major_time_from_warning_check_label').remove();
}

function removeMajorTimeToWarnings(){
	$('#major_time_to_warning').remove();
	$('#major_time_to_warning_check').remove();
	$('#major_time_to_warning_check_label').remove();
}

function getMonthName(monthNumber)
{
	var month_names = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
	return month_names[monthNumber-1];
}

function disableDates(){
	var fromDate = $('#major_time_from').val();
	var toDate = $('#major_time_to').val();
	if(((new Date(fromDate)) > (new Date(toDate))))
		$('#major_time_to').val('');
	$('#major_time_to').datepicker('destroy');
	$('#major_time_to').datepicker({
		beforeShowDay: function(date){
			var string = new Date(jQuery.datepicker.formatDate('mm/dd/yy',date));
			return [(string >= (new Date(fromDate)))];
		}
	});
}
