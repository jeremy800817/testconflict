<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

	// if ($_SESSION['login'] != "success"  ) {
	// 	header("Location: index.php");
	// 	}
	$remainingGold = $_SESSION['available_balance'];
	$_SESSION['customerbuy'] = 240;
	$_SESSION['customersell'] = 240;
?>
<?php include('controllers/login.php');  ?>
<?php include_once('common.php');  ?>
<?php include_once('controllers/transfercontacthistory.php');  ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $lang['TITLE_TRANSFER_DETAILS'];?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/style-extra.css">
<link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

	/* Disable scrolling horizontal */
	body {
		overflow-x: hidden;
	}
</style>
<script>

	
$(function() {

	$('#system-message-redirect').click(function(){
		window.location.href='system-message.php';
	})	

	$('#news-redirect').click(function(){
		Swal.fire({
			title: 'Info!',
			text: 'Coming Soon',
			icon: 'info',
			confirmButtonText: 'OK',
			confirmButtonColor: '#53C4CC'
		});
		// window.location.href='news.php';
	})	

});

</script>
</head>
<body class="gogold message-center">
	<!-- <div id="loader" class="form-title center">

		<img src="img/bg/transfer-send.jpg" alt="this slowpoke moves"  width="auto" />
	</div> -->
	<div class="language-switcher transfer">
		<ul>
			<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="transfer-details.php?lang=en">Eng</a></li>
       		<li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="transfer-details.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<br/>
	<div class="header-inside">
		<div class="inside-title message-center"><?php echo $lang['MessageCenter']; ?></div>
		<a class="back" href="index.php"></a>
	</div>
	

	<div class="page-content-box white">
	

	<footer class="empty-space">
		<br>
		<br>
	</footer>

	<div class="main-widget message-center">
			<div id="system-message-redirect" class="inside-profile-transfer">
				<div class="col">
					<img src="img/icon/information-circle.svg" alt="user"  width="auto" />
				</div>
				<div class="col">
					<div class="transfer-profile-title"><?php echo $lang['SystemMessage']; ?></div>
					<div class="transfer-profile-text"><?php echo $lang['TransferMessages']  ?></div>
				</div>
			</div>
		</div>

		<!-- <div class="main-widget message-center" style="opacity: 0.5;">
			<div id="news-redirect" class="inside-profile-transfer">
				<div class="col">
					<img src="img/icon/promotion-icon.svg" alt="user"  width="auto" />
				</div>
				<div class="col">
					<div class="transfer-profile-title"><php echo $lang['News']; ?></div>
					<div class="transfer-profile-text"><php echo $lang['LatestNews']  ?></div>
				</div>
			</div>
		</div> -->
	
	</div>
</body>

			


</html>