<script>
    alert("Helo");
	$(document).ready(function(){
    $("input[id*='exp']").focus(function(){
	$("input[id*='exp']").datepicker({
	inline: true
	})
	})
	});
	</script>