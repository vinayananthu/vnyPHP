<div id="feedback">
	<? $address=urlencode($auth->getAddress());?>
	<strong>Disclaimer:</strong> This system is in a Beta phase. Please <a href="feedback.php?error_url=<? echo $address ?>">inform us</a> if you encounter any issue or have any feedback.
</div>

<div id="footer"><p>Copyright &copy; <a href="http://finance.latech.edu/hr/">The Office of Human Resources</a>, <a href="http://www.latech.edu/">SCSU</a>, 2016-<? echo date("Y"); ?></div>

<style>
	#feedback{
		text-align: center;
		margin: 10px;
		font-size: 8pt
	}
	#feedback a{
		color: #3C6
	}
	#footer{
		text-align: center;
		font-size: 10px;
		margin: 10px;
		color: #999
	}
	#footer A{
		text-decoration: none;
		color: #999
	}
	#footer A:hover{
		text-decoration: underline
	}
</style>
</body>
</html>