<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<?php include('controllers/transactionstatus.php') ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TransactionStatus']; ?> | MGold</title>
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
    $(this).scrollTop(0);
});
</script>
</head>
<body class="gogold">
    <div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
		<div class="main-widget">
				<div class="table-row title">
					<div class="sub"><?php echo $lang['Receipt'] ?></div>
					<?php echo $_SESSION['langReceipt'] ?>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['DATE'] ?></div>
					<div class="col value"><?php date_default_timezone_set("Asia/Kuala_Lumpur"); echo date('D, d M Y H:i:s') ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['NAME']; ?></div>
					<div class="col value"><?php echo  $_SESSION['name']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['NRIC']; ?></div>
					<div class="col value"><?php echo  $_SESSION['ic']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['GOGOLDACCNO']; ?></div>
					<div class="col value"><?php echo  $_SESSION['accountcode']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo  $_SESSION['langGpurchase']; ?> <span class="unit">Gram</span></div>
					<div class="col value"><?php echo  number_format($_SESSION['weight'],3)  ?> gram</div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo  $_SESSION['langPprice']; ?> <span class="unit"><?php echo $_SESSION['lastaction']=="walletconvertion" ? '': $lang['UnitPrice']; ?> </span></div>
					<div class="col value"><?php echo $_SESSION['lastaction']=="walletconvertion" ? '': 'RM'; ?> <?php echo  $_SESSION['u_price']  ?> <?php echo $_SESSION['lastaction']=="walletconvertion" ? '': '/ gram'; ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $_SESSION['langFees']; ?></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['w_fee'], 2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo$_SESSION['langTtbuy']; ?></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['o_total'],2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['PURITY']; ?></div>
					<div class="col value"><?php echo $lang['PURITY_VALUE']; ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['VAULT']; ?></div>
					<div class="col value">SG4S, Malaysia<br /><?php echo $lang['STORAGE_PROVIDER']; ?></div>
				</div>
				<div class="table-row total">
					<div class="label"><?php echo $lang['FINAL_TOTAL']; ?></div>
					<div class="value">RM  <?php echo  number_format($_SESSION['t_wallet'], 2)     ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['Status'] ?></div>
					<div class="col value"><?php echo  $_SESSION["message"] ?></div>
				</div>
			</div>
			
			<div class="footnote">
				<?php echo $lang['RECEIPT_TEXT'] ?>
			</div>
				
			<div class="page-action">
				<a class="back" href="index.php"><input type="submit" class="btn" formaction="index.php" value="<?php echo $lang['Done'] ?>" /></a>
			</div>
		</div>
	</div>
</body>
</html>
