<?
// To display "Edit this form" link
$current_url=$auth->getAddress();

if(substr_count($current_url, "_form")+substr_count($current_url, "_preview")==0){
	$edit_link=1;
}
?>

<div id="t_scroller">
	<div style="width: 920px; padding: 10px; margin-left: auto; margin-right: auto; text-align: left">
    	<div style="float: right">
        	<a href="../ARF/">Home</a>
            <? if($edit_link==1) { ?>
				<a href="arf_form.php?ID=<? echo $ID ?>">Edit</a>
            	<?
               	if($auth->getUser() != $owner && substr_count($auth->getAddress(), "arf.php") > 0){
				?>
				<a href="javascript: sendMessage()">Ask for Revision</a>
               	<? } ?>
            <? } ?>
                <a href="arf_form.php">New</a>
                <a href="admin/forms.php">My Forms</a>
                <a href="admin/forms_to_authorize.php">Forms to Sign</a>
                <? if(isAdmin($username) && $ID!=""){ ?>
           		<a href="javascript: deleteForm('<? echo $ID ?>')">Delete</a>
			<? } ?>

			<?
			if($edit_link == 1) {	// Show cancel option only on the already initiated ARF that has been sent for signatures.
			?>
				<a href="javascript: cancelForm('<? echo $ID ?>')">Cancel Form</a>
			<?
			}
			?>
			<a href="https://forms.latech.edu/login/?logout=1" id="logout">Log Out</a>
        </div>
		Hello, <? echo $auth->getUser() ?>
	</div>
</div>