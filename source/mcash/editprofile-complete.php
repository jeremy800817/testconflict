<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
?>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<?php include('controllers/Eprofile.php'); ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register  | GoGold</title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/style-mcash.css">
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script>

$(document).ready(function(){
	var editprofile = '<?php echo $_SESSION['editprofiledone'];?>';
	if (editprofile == 1) {
      $("#success").show();
    $("#error").hide();
   
    }else{
     // alert('check');
      $("#success").hide();
    }


});

</script>
<style>
.signup-navi {
    width: 100%;
    /* position: relative; */
    max-width: 201px;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 36px;
    margin-top: 24px;
    display: flex;
}
</style>
</head>
<body class="gogold">
	<div class="header">
		<div class="hero"></div>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="page-content-form">
				
				<div class="page-title">
					<?php echo $lang['EditProfile']    ?>
				</div>
				<!--<div class="page-title-desc">
					Join us and start trading immediately
				</div>-->
			
				
				<div class="form-title center">
				<?php echo $_SESSION['edit_profile_error_message'] ?>
				</div>
				
				<div class="form-row">
					<div class="form-col center">
						<p>
						<?php echo $_SESSION['M2'] ?><br />
					
						</p>
					</div>
				</div>
				
				<div class="form-row submit">
					<div id="success" class="form-col">
						<a href="index.php">
						<input type="submit" class="btn" value="<?php echo $lang['GotoDashboard'] ?>" />
						</a>
					</div>
					<div id="error" class="form-col">
						<a href="edit-profile.php">
						<input type="submit" class="btn" value="<?php echo $lang['GoBackToEditProfile'] ?>" />
						</a>
					</div>
				</div>
			</div>
		
		</div>
		
	</div>
	
</body>
</html>