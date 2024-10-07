<!doctype html>
<html lang="en-gb" dir="ltr">
	<?php include('controllers/login.php');  ?>
	<?php include('controllers/pricealertlist.php');  ?>
	<?php include('common.php');  ?>


	<script>
	// validate input length
	// (this, length)
	var validate = function(e, l) {
	var t = e.value;
	e.value = (t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), l)) : t;
	}
	</script>

	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?php echo $lang['TITLE_PRICE_ALERT'];?></title>
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
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<script>
			$(document).ready(function(){
				$(this).scrollTop(0);
			});
		</script>
		<style>
			.swal2-select {
				display: none;
			}
		</style>
		<?php
			if(isset($_GET['err'])){
				echo '
					<script>
						$(document).ready(function(){
							Swal.fire({
								icon: "error",
								title: "Price Alert setup failed.",
								text: "'.($_SESSION["pa_error_message"] ?? "Please try again" ).'",
								confirmButtonText: "OK",
								confirmButtonColor: "#53C4CC"
							});
						});
					</script>
					';
			} 
		?>
		<script>
			$(document).ready(function(e){
				$("#submitbuyprice, #submitsellprice").keyup(function(e) {
					if($(this).val().indexOf('.') > -1){
						if($(this).val().substr($(this).val().indexOf('.')+1).length > 2){
							$(this).val($(this).val().substr(0 , $(this).val().indexOf('.')+3));
						}
					}
				}).keydown(function (event) {
					if (event.shiftKey == true) {
						event.preventDefault();
					}
					
					if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 110) {

					} 
					else {
						event.preventDefault();
					}

					if($(this).val().indexOf('.') !== -1 && event.keyCode == 190){
						event.preventDefault();
					} 
					//if a decimal has been added, disable the "."-button
				});
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
				<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="price-alert.php?lang=en">Eng</a></li>
				<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="price-alert.php?lang=bm">BM</a></li>
			</ul>
		</div>
		<div class="header">
			<div class="hero">
				<div class="client-logo"></div>
				<div class="main-logo"></div>
			</div>
		</div>
		
		<div class="header-inside">
			<div class="inside-title"><?php echo $lang['PriceAlert']; ?></div>
			<a class="back" href="/"></a>
		</div>
		
		<div class="page-content">
			
			<div class="page-content-box">
				
				<div class="page-content-form">
					
					<div class="page-sub-title">
						<?php echo $lang['SetYouPriceAlert']; ?>
					</div>
					<div class="page-title-desc">
						<?php echo $lang['_PriceAlertRemarks']; ?>
					</div>

					<form method="POST" action="controllers/addpricealert.php" onsubmit="return validatePriceAlert(this)">
						<div class="table-row align-top">
							<div class="col label em"><?php echo $lang['CustomerBuy']; ?> <span class="unit">RM per gram</span></div>
							<div id="alert-inputbuy" class="col value alert-input">
								<!--<input type="number" name="" value="220.00" />-->
								<div class="form-row ">
									<div class="form-col ">
										<!-- <input id="submitbuyprice" name="price" min="0" type="number" step="any" oninput="validate(this, 3)" name="total" type="number" class="opp" placeholder="<?php //echo $lang['InsertBuyPrice']; ?>"  style="text-align: center;"> -->
										<input id="submitbuyprice" name="price" min="0" type="number" step="any" name="total" type="number" class="opp" placeholder="<?php echo $lang['InsertBuyPrice']; ?>"  style="text-align: center;">
										<input id="submitbuytarget" type="submit" class="btn buy" name="SubmitBuyTarget" value="<?php echo $lang['SetBuyTarget']; ?>" />
											<input type="hidden" name="alerttype" value="buy" />
									</div>
								</div>
							</div>
						</div>
					</form>
					
					<form method="POST" action="controllers/addpricealert.php" onsubmit="return validatePriceAlert(this)">
						<div class="table-row align-top">
							<div class="col label em"><?php echo $lang['CustomerSell']; ?> <span class="unit"><?php echo $lang['RMperGram']; ?></span></div>
							<div id="alert-inputsell" class="col value alert-input">
								<!-- <input id="submitsellprice" name="price" min="0" type="number" step="any" oninput="validate(this, 3)" name="total" type="number" class="opp" placeholder="<?php //echo $lang['InsertSellPrice']; ?>"  style="text-align: center;"> -->
								<input id="submitsellprice" name="price" min="0" type="number" step="any" name="total" type="number" class="opp" placeholder="<?php echo $lang['InsertSellPrice']; ?>"  style="text-align: center;">
								<div class="form-row">
									<div class="form-col">
										<input id="submitselltarget" type="submit" class="btn sell" name="SubmitSellTarget" value="<?php echo $lang['SetSellTarget']; ?>" />
										<input type="hidden" name="alerttype" value="sell" />
									</div>
								</div>
							</div>
						</div>				
					</form>
				</div>
				
				<div class="box-listing">
					<div class="box-listing-tabs">
						<a class="active" rel="one"><?php echo $lang['Active']; ?></a>
						<a class="" rel="two"><?php echo $lang['Triggered']; ?></a>
					</div>
					<div class="box-listing-tabs-content">
						<div class="box-listing-tab-content active" rel="one">
							<div class="box-listing-rows">
								
								<?php 
									foreach ($priceAlerts as $alert) {
										if (strlen($alert['last_triggered']) === 0) {
											if('Buy' == $alert['type']){
												$buttonText = $lang['Buy'];
											}else{
												$buttonText = $lang['Sell'];
											}
								?>

								<div class="box-listing-row">
									<div class="col icon">
										<span class="badge <?php echo strtolower($alert['type']) ?>"><?php echo $buttonText; ?></span>
									</div>
									<div class="col desc">
										<div class="title"><?php echo $lang['TargetRM'] ?> <?php echo number_format($alert['price'], 2); ?></div>
										<div class="subtitle"><?php echo date('d M Y h:i A', strtotime($alert['date'])); ?></div>
									</div>
									<div class="col action">
										<form method="POST" action="controllers/deletepricealert.php">
											<input type="hidden" name="price_alert_id" value="<?php echo $alert['id']; ?>">
											<a class="remove" onclick="parentNode.submit();"><?php echo $lang['Remove']; ?>Remove</a>
										</form>
									</div>
								</div>

								<?php
									}
								}
								?>	
							</div>
						</div>
						
						<div class="box-listing-tab-content" rel="two">
							<div class="box-listing-rows">
								<?php 
									foreach ($priceAlerts as $alert) {
										if (strlen($alert['last_triggered']) > 0) {
											if('Buy' == $alert['type']){
												$buttonText = $lang['Buy'];
											}else{
												$buttonText = $lang['Sell'];
											}
								?>
								<div class="box-listing-row">
									<div class="col icon">
										<span class="badge <?php echo strtolower($alert['type']) ?>"><?php echo $buttonText; ?></span>
									</div>
									<div class="col desc">
										<div class="title"><?php echo $lang['TargetRM'] ?> <?php echo number_format($alert['price'], 2); ?></div>
										<div class="subtitle"><?php echo date('d M Y h:i A', strtotime($alert['date'])); ?></div>
									</div>
									<div class="col action">
										<form method="POST" action="controllers/deletepricealert.php">
											<input type="hidden" name="price_alert_id" value="<?php echo $alert['id']; ?>">
											<a class="remove" onclick="parentNode.submit();"><?php echo $lang['Remove']; ?></a>
										</form>
									</div>
								</div>
								<?php 
									}
								}
								?>	
							</div>
						</div>
					</div>
				</div>
			
			</div>
			
		</div>
		
	</body>
</html>