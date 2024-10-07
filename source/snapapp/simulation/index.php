
<?php require "data.php";?>
<?php require "simulator.php";?>
<?php $simulator = new \Simulator($api_method);?>


<!DOCTYPE html>
<html>
<head>
	<title>API Simulation</title>
</head>
<body>
		
	<div align="center">
		<form method="GET">
			<b>API Method : </b>
			<?php echo $simulator->getApiLstSelectBox();?>
		</form>
	</div>
	
	<div><hr></div>

	

	<div align="center">

		<h3>Parameters:</h3>
		<form method ='POST'>
		<?php echo $simulator->getParamHTML();?>
		</form>
	</div>

	<div><hr></div>

	<div align="center">
		
		<h3>Response : </h3>
		
		<?php echo $simulator->getAPIResponse();?>
		
	</div>

	<div><hr></div>

	<div align="center">
		
		<b>History : </b>
		<br>
		<?php echo $simulator->getHistory();?>
		
	</div>

</body>
</html>