<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

	if ($_SESSION['login'] != "success"  ) {
		header("Location: index.php");
		}
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

<style>

	/* Disable scrolling horizontal */
	body {
		overflow-x: hidden;
	}
</style>
</head>
<body class="gogold-transfer-confirm">
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
		<!-- <div class="inside-title-transfer"><php echo $lang['Transfer']; ?></div> -->
		<!-- <a class="back" href="/" style="color:black;display:flex; font-size: 16px"><span style="margin-top:-1px">&nbsp;&nbsp;&nbsp;&nbsp;Back</span></a> -->
		<!-- <a class="back white" href="transfer.php" style="color:black;display:flex; font-size: 16px"></a> -->

		<img src="img/icon/done.svg" alt="done"  width="auto" />
		<div class="inside-title-transfer-confirm">
			<?php echo $_SESSION['chk_transferxau'].' '.$lang['gram']; ?>
			<img src="img/icon/bx_transfer-white.svg" alt="transfer" style="vertical-align: middle;"  width="auto" />
			<?php echo 'RM'.$_SESSION['chk_transferamount']; ?>
		</div>
		<div class="inside-title-transfer-confirm"><?php echo $lang['Transferred']; ?></div>
	</div>

	<div class="page-content-box">
				
		<div class="main-widget">
			<!-- <div id="btncancelpopup" name="cancelicon" onclick="closeWindow()">
				<i class="fa fa-times" aria-hidden="true"></i>
			</div> -->
	
			<div class="table-row title">
				<?php echo $lang['TransferDetails'] ?>
			</div>
			<div class="table-row">
				<div class="col label"><?php echo $lang['Receiver'] ?></div>
				<div class="col value"><?php echo  $_SESSION['transferfullname']  ?></div>
			</div>
			<div class="table-row">
				<div class="col label"><?php echo $lang['Remarks'] ?></div>
				<div class="col value"><?php echo  $_SESSION['chk_transferremarks']  ?></div>
			</div>
			<div class="table-row">
				<div class="col label"><?php echo $lang['DATE'] ?></div>
				<div class="col value"><?php date_default_timezone_set("Asia/Kuala_Lumpur"); echo date('D, d M Y H:i:s') ?></div>
			</div>
			<div class="table-row">
				<div class="col label"><?php echo $lang['GoldTransferred'] ?> <span class="unit">Gram</span></div>
				<div id="weighttransferred" class="col value em highlight"><?php echo  number_format($_SESSION['chk_transferxau'],3)  ?></div>
				
			</div>
			<div class="table-row">
				<div class="col label em"><?php echo $lang['MidPrice'] ?> <span class="unit">Unit price</span></div>
				<div id="amounttransferred" class="col value">RM <?php echo  number_format($_SESSION['chk_transferprice'], 2)  ?> / gram</div>
			</div>
			<div class="table-row total transfer">
				<div class="label"><?php echo $lang['FINAL_TOTAL']; ?></div>
				<div id="finaltotaltransferred" class="value">RM <?php echo $_SESSION['chk_transferamount']; ?></div>
			</div>
		</div>
	
	</div>

	<div class="form-row submit">
			<div class="form-col">
				<a href="../../index.php"><input id='btnconfirmtransfer' type="submit" class="btn" value="<?php echo $lang['Done'] ?>" /></a> 
				
			</div>
	</div>

	<footer class="empty-space">
		<br>
		<br>
	</footer>
</body>

</html>