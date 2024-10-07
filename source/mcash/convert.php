<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/login.php');  ?>
<?php include('common.php');  ?>


<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_CONVERT'];?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/style-mcash.css">
<link rel="stylesheet" type="text/css" href="css/card-slider-style.css">
<link rel="stylesheet" type="text/css" href="slick/slick.css"/>
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script type="text/javascript" src="slick/slick.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script src="js/tooltip-script.js"></script>
<script src="js/card-slider-script.js"></script>

<script>
	$(document).ready(function() {
		$('#btnpayfpx').hide();
		$('#btnpaywallet').hide();
	});
	document.getElementById('btnpaywallet').disabled= true;
	document.getElementById("btnpaywallet").style.opacity= .65;
	document.getElementById('btnpayfpx').disabled= true;
	document.getElementById("btnpayfpx").style.opacity= .65;
	function activateButton(element) {
		if(element.checked && total >= 5) {
			// check if values are good
			document.getElementById('btnpaywallet').disabled= false;
			document.getElementById("btnpaywallet").style.opacity= 1.0;
			document.getElementById('btnpayfpx').disabled= false;
			document.getElementById("btnpayfpx").style.opacity= 1.0;
		}
		else  {
			document.getElementById('btnpaywallet').disabled= true;
			document.getElementById("btnpaywallet").style.opacity= .65;
			document.getElementById('btnpayfpx').disabled= true;
			document.getElementById("btnpayfpx").style.opacity= .65;
		}
	}
</script>
<style>
	@media only screen and (max-width: 600px) {
		.form-col{
			margin: 0 auto;
			padding: 0;
			border: 0;
			font-size: 100%;
			font: inherit;
			vertical-align: baseline;
			width: 90%;
			margin: auto;
		}
		#btnpaywallet{
			width: 100%;
		}
		#btnpayfpx{
			width: 100%;
		}
		#plus {
			width: 20px;
			height: 20px;
			display: block;
			position: absolute;
			top: 50%;
			left: auto;
			right: -1.9em;
			cursor: pointer;
			transform: translateY(-50%);
			background: transparent url(../img/icon/icon-plus.svg) center center/contain no-repeat;
		}
		#minus {
			width: 20px;
			height: 20px;
			display: block;
			position: absolute;
			top: 50%;
			left: -1.9em;
			right: auto;
			cursor: pointer;
			transform: translateY(-50%);
			background: transparent url(../img/icon/icon-minus.svg) center center/contain no-repeat;
		}
		.plus-minus input.plus-minus-value {	
			width: 174px;
		}
	}
	@media only screen and (min-width: 601px) {
		.form-col{
			margin: 0 auto;
			padding: 0;
			border: 0;
			font-size: 100%;
			font: inherit;
			vertical-align: baseline;
			width: 60%;
		}
		#btnpaywallet{
			width: 30%;
		}
		#btnpayfpx{
			width: 30%;
		}
		#plus {
			width: 20px;
			height: 20px;
			display: block;
			position: absolute;
			top: 50%;
			left: auto;
			right: -5em;
			cursor: pointer;
			transform: translateY(-50%);
			background: transparent url(../img/icon/icon-plus.svg) center center/contain no-repeat;
		}
		#minus {
			width: 20px;
			height: 20px;
			display: block;
			position: absolute;
			top: 50%;
			left: -5em;
			right: auto;
			cursor: pointer;
			transform: translateY(-50%);
			background: transparent url(../img/icon/icon-minus.svg) center center/contain no-repeat;
		}
		.plus-minus input.plus-minus-value {	
			width: 174px;
		}
	}
</style>
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="convert.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="convert.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['ConvertGold']; ?></div>
		<!--<a class="back" href="/">Back</a>-->
		<a class="back" href="index.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			<form method="post" action="convert-review.php" onsubmit="return validateConversion();">
			<div class="main-widget">
					<div class="balance-row convert">
						<label><?php echo $lang['GOLDBALANCE']; ?></label>
						<div class="balance-value">
						<div class="unit"><abbr title="The gold remaining in your account in grams." rel="tooltip"><?php echo $lang['gram']; ?></abbr></div>
							<div class="num" id="gold-balance"><?php echo number_format($_SESSION['goldbalance'],3); ?></div>
							
						</div>
					</div>
					
					<section class="slider">
						<div>
							<img src="img/goldbar/0.5g.png">
							<div class="desc">
							<!-- <h2>0.5 Gram</h2> -->
							<!-- <p>$289.99 - $299.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-0.5g' data-product-weight='0.5'>0.5 <span>Gram</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/1g.png">
							<div class="desc">
							<!-- <h2>1 Gram</h2> -->
							<!-- <p>$299.99 - $329.99</p> -->
							<a href="#select-quanitity" class="btn selected" data-product-code='GS-999-9-1g' data-product-weight='1'>1 <span>Gram</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/2.5g.png">
							<div class="desc">
							<!-- <h2>2.5 Gram</h2> -->
							<!-- <p>$289.99 - $299.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-2.5g' data-product-weight='2.5'>2.5 <span>Gram</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/5g.png">
							<div class="desc">
							<!-- <h2>5 Gram</h2> -->
							<!-- <p>$299.99 - $329.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-5g' data-product-weight='5'>5 <span>Gram</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/10g.png">
							<div class="desc">
							<!-- <h2>10 Gram</h2> -->
							<!-- <p>$289.99 - $299.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-10g' data-product-weight='10'>10 <span>Gram</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/50g.png">
							<div class="desc">
							<!-- <h2>50 Gram</h2> -->
							<!-- <p>$299.99 - $329.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-50g' data-product-weight='50'>50 <span>Gram</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/100g.png">
							<div class="desc">
							<!-- <h2>100 Gram</h2> -->
							<!-- <p>$299.99 - $329.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-100g' data-product-weight='100'>100 <span>Gram</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/1dinar.png">
							<div class="desc">
							<!-- <h2>1 Dinar</h2> -->
							<!-- <p>$299.99 - $329.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-1-DINAR' data-product-weight='4.25'>1 <span>Dinar</span></a>
							</div>
						</div>

						<div>
							<img src="img/goldbar/5dinar.png">
							<div class="desc">
							<!-- <h2>5 Dinar</h2> -->
							<!-- <p>$299.99 - $329.99</p> -->
							<a href="#select-quanitity" class="btn" data-product-code='GS-999-9-5-DINAR' data-product-weight='21.25'>5 <span>Dinar</span></a>
							</div>
						</div>

					</section>

					<!-- <div class="select-weight-row">
						<a class="convert-unit avail" data-product-code='GS-999-9-0.5g' data-product-weight='0.5'>0.5 <span>Gram</span></a>
						<a class="convert-unit avail selected" data-product-code='GS-999-9-1g' data-product-weight='1'>1 <span>Gram</span></a>
						<a class="convert-unit avail" data-product-code='GS-999-9-2.5g' data-product-weight='2.5'>2.5 <span>Gram</span></a>
						<a class="convert-unit avail" data-product-code='GS-999-9-5g' data-product-weight='5'>5 <span>Gram</span></a>
						<a class="convert-unit avail" data-product-code='GS-999-9-10g' data-product-weight='10'>10 <span>Gram</span></a>
						<a class="convert-unit avail" data-product-code='GS-999-9-50g' data-product-weight='50'>50 <span>Gram</span></a>
						<a class="convert-unit avail" data-product-code='GS-999-9-100g' data-product-weight='100'>100 <span>Gram</span></a>
						<a class="convert-unit avail" data-product-code='GS-999-9-1-DINAR' data-product-weight='4.25'>1 <span>Dinar</span></a>
						<a class="convert-unit avail" data-product-code='GS-999-9-5-DINAR' data-product-weight='21.25'>5 <span>Dinar</span></a>
					</div> -->
					
				</div>
				
				<div class="page-content-form single-padding">
					<div class="form-row">
						<div class="form-col center">
							<label><?php echo $lang['Quantity']; ?></label>
							<div class="plus-minus">
								<a class="minus"></a>
								<input type="number" name="quantity" id="quantity" class="plus-minus-value center" value="1" />
								<a class="plus"></a>
							</div>
						</div>
					</div>
					
					<div class="table-row">
						<div class="col label"><?php echo $lang['TotalConversion']; ?> <span class="unit">gram</span></div>
						<div class="col value em" id="total-conversion">1.00</div>
					</div>
					<div class="table-row">
						<div class="col label"><?php echo $lang['BalanceafterConversion']; ?> <span class="unit">gram</span></div>
						<div class="col value em" id="balance-conversion"><?php echo number_format($_SESSION['goldbalance'] - 1, 3); ?></div>						
					</div>
					
					<div class="form-row tnc">
						<div class="form-col center">
							<span class="checkbox"><input id="tncbox" type="checkbox" /><span></span></span>
							<span class="label"><?php echo $lang['I_AGREE']; ?> <a href="Tnc.php"><?php echo $lang['TERMS_AND_CONDITIONS']; ?></a></span>
						</div>
					</div>
					
					<div class="form-row submit">
						<div class="form-col gap">
							<div class="error"><?php echo $_SESSION['conversion_error'] ?? ''; unset($_SESSION['conversion_error']); ?></div>
							<div id="goldbalanceinsufficienterror" class="error"><?php echo $lang['MIN_CONVERT_NOT_MET'].' '.$_SESSION['minbalance'].'g'; ?></div>
							<input type="hidden" value="GS-999-9-1g" name="product" id="product">
							<input type="hidden" id="minbalance" value="<?php echo $_SESSION['minbalance']; ?>" name="minbalance" id="minbalance">
							<input type="hidden" value="1" name="weight" id="weight">
							<input type="submit" class="btn" value="<?php echo $lang['PAYBYWALLET'];?>" name="paybywallet" id="btnpaywallet" <?php $_SESSION['goldbalance'] > 0 ?: 'disabled';?> />
							<input type="submit" class="btn" value="<?php echo $lang['PAYBYFPX'];?>" name="paybyfpx" id="btnpayfpx" <?php $_SESSION['goldbalance'] > 0 ?: 'disabled';?> />
						</div>
					</div>
					
				</div>
			</form>
		</div>
		
	</div>
	
</body>
</html>