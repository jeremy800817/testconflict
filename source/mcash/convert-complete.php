<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/login.php');  ?>
<?php include('common.php');  ?>
<?php include('controllers/editprofile.php') ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_CONVERT_COMPLETE'];?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
	<div id="faq-switcher" class="faq-switcher">
		<ul>
			<li>
				<a href="FAQ.php" rel="noopener noreferrer"><?php echo $lang['FAQ'] ?></a>
			</li>
      		<li>
				<a href="pdpa.php">PDPA</a>
			</li>
    		<li>
				<a href="Tnc.php"><?php echo $lang['Terms_and_Conditions'] ?></a>
			</li>
     		<!-- <li id="deliveryrefundpolicies">
				<a href="DeliveryRefundPolicies.php"><php echo $lang['RefundPolicy'] ?></a>
			</li> -->
     		<li>
				<a href="productDisclosure.php"><?php echo $lang['ProductDisclosure_HEADER'] ?></a>
			</li>
			<li id="resetsecuritypintop">
				<a href="dashboard-pin.php"><?php echo $lang['ResetPin'] ?></a>
			</li>
			<li id="editprofiletop">
				<a href="edit-profile.php" rel="noopener noreferrer"><?php echo $lang['EditProfile'] ?></a>
			</li>
			<li id="editbankaccounttop">
				<a  href="edit-bank.php" rel="noopener noreferrer"><?php echo $lang['EditBankAccount'] ?></a>
			</li>
			<!-- <li id="closeaccount">
				<a href="close-account.php" rel="noopener noreferrer"><php echo $lang['CloseAccount'] ?></a>
			</li> -->
		</ul>
	</div>
	<div id="faq-switcher-small" class="navbar">
		<div class="dropdown">
			<button class="dropbtn">
				<i class="fa fa-bars"></i>
			</button>
			<div class="dropdown-content">
				<a href="FAQ.php"><?php echo $lang['FAQ'] ?></a>
				<a href="pdpa.php">PDPA</a>
				<a href="Tnc.php"><?php echo $lang['Terms_and_Conditions'] ?></a>
       			<!-- <a href="DeliveryRefundPolicies.php"><php echo $lang['RefundPolicy'] ?></a> -->
        		<a href="productDisclosure.php"><?php echo $lang['ProductDisclosure_HEADER'] ?></a>
				<a id="resetsecurity" href="dashboard-pin.php"><?php echo $lang['ResetPin'] ?></a>
				<a id="editprofile" href="edit-profile.php" rel="noopener noreferrer"><?php echo $lang['EditProfile'] ?></a>
				<a id="editbankaccount" href="edit-bank.php" rel="noopener noreferrer"><?php echo $lang['EditBankAccount'] ?></a>
				<!-- <a id="closeaccount" href="close-account.php" rel="noopener noreferrer"><php echo $lang['CloseAccount'] ?></a> -->
			</div>
		</div>
	</div>
	<div class="language-switcher"> 
		<ul>
		<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="convert-complete.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="convert-complete.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title">
			<?php echo $lang['ConvertGold']; ?>
		</div>
		<a class="back" href='convert.php'></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="trans-navi">
				<div class="step active">
					<div class="num">1</div>
					<div class="label"><?php echo $status; ?></div>
					<div class="date"><?php echo date(' h:i A d/m/Y', strtotime($createdOn)); ?></div>
				</div>
				<div class="step active">
					<div class="num">2</div>
					<div class="label"><?php echo $lang['Shipped']; ?></div>
					<div class="date"></div>
				</div>
				<div class="step">
					<div class="num">3</div>
					<div class="label"><?php echo $lang['Received']; ?></div>
				</div>
			</div>
			
			<div class="main-widget">
				<div class="table-row title">
					<div class="sub"><?php echo $lang['Receipt']; ?></div>
					<?php echo $lang['ConversionDetail']; ?>
				</div>
				<div class="table-row first">
					<div class="col label"><?php echo $lang['Date']; ?></div>
					<div class="col value"><?php echo date('d M Y h:i A', strtotime($createdOn)); ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['Name']; ?></div>
					<div class="col value"><?php echo $_SESSION['name']; ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['NRIC']; ?></div>
					<div class="col value"><?php echo $_SESSION['ic']; ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['GoGoldAccNo']; ?></div>
					<div class="col value"><?php echo $_SESSION['accountcode']; ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['TotalConversion']; ?> <span class="unit">Gram</span></div>
					<div class="col value"><?php echo number_format($_SESSION['gram'],2);  ?></div>
				</div>
				<!--<div class="table-row">
					<div class="col label">Purchase Price <span class="unit">Unit price</span></div>
					<div class="col value">RM242.50</div>
				</div>
				<div class="table-row">
					<div class="col label">Purity</div>
					<div class="col value">999.9 (LBMA Standard)</div>
				</div>
				<div class="table-row">
					<div class="col label">Vault</div>
					<div class="col value">G4S Malaysia<br />as Storage Provider</div>
				</div>
				<div class="table-row">
					<div class="col label em">Final Total</div>
					<div class="col value em highlight">RM1,212.50</div>
				</div>-->
				<div class="table-row">
					<div class="col label"><?php echo $lang['Address']; ?></div>
					<div class="col value">
							<?php echo $_SESSION['address_line_1']; ?><br />
							<?php echo $_SESSION['address_line_2'] ? $_SESSION['address_line_2'] . '<br/>' : '' ?>
							<?php echo $_SESSION['postcode'] . ' ' . $_SESSION['city']; ?><br />
							<?php echo $_SESSION['state']; ?>
					</div>
				</div>
				<div class="table-row last">
					<div class="col label">Status</div>
					<div class="col value highlight"><?php echo $status; ?></div>
				</div>
				<div class="footnote">
					This receipt is computer-generated and so signature is required.
				</div>
			</div>
			
			<div class="page-action">
				<?php if ($status == 'Pending') { ?>
					<a id="btnpay"  href="<?php echo $location; ?>" ><input type="button" class="btn" value="<?php echo $lang['Pay'];?>" /></a>
				<?php } else { ?>
					<a href="/" ><?php echo $lang['Done'];?></a>
				<?php } ?>
			</div>
			
		</div>
		
	</div>
	
</body>
</html>