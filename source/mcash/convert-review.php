<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include_once('controllers/login.php');  ?>
<?php include_once('common.php');  ?>
<?php include_once('controllers/convertionfee.php');  ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_CONVERT_REVIEW'];?></title>
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
		$(':input').keydown(function(e) {
			if ((e.which == 8 || e.which == 46) && $(this).val() =='') {
				$(this).prev('input').focus();
			}
		});		
		//Init
		$('#buttonconfirm').hide();

		$("#pinlabel").hide();
		$("#pin1").hide();
		$("#pin2").hide();
		$("#pin3").hide();
		$("#pin4").hide();
		$("#pin5").hide();
		$("#pin6").hide();
		$("#pinerror").hide();
		$('input[type="checkbox"]').click(function(){
            if($(this).prop("checked") == true){
                $('#buttonconfirm').show();
				
				$("#pinlabel").show();
				$("#pin1").show();
				$("#pin2").show();
				$("#pin3").show();
				$("#pin4").show();
				$("#pin5").show();
				$("#pin6").show();
            }
            else if($(this).prop("checked") == false){
                $('#buttonconfirm').hide();

				$("#pinlabel").hide();
				$("#pin1").hide();
				$("#pin2").hide();
				$("#pin3").hide();
				$("#pin4").hide();
				$("#pin5").hide();
				$("#pin6").hide();
				$("#pinerror").hide();
            }
        });
	});

	$(document).on('focus', selector, function() {
    document.querySelector(selector).scrollIntoView();
});
</script>
<style>
	input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>


<style>
@media only screen and (max-width: 600px) {
	.form-col{
		margin: 0 auto;
		padding: 0;
		border: 0;
		font-size: 100%;
		font: inherit;
		vertical-align: baseline;
		width: 100%;
	}
	#btnpaywallet{
		width: 100%;
	}
	#reflesh{
		width: 100%;
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
		width: 50%;
	}
	#reflesh{
		width: 50%;
	}
}
#campaigncode{
	text-align: center;
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="convert-review.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="convert-review.php?lang=bm">BM</a></li>
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
		<a class="back" href="convert.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="main-widget">
				<form method="post" action="convert-complete.php" onsubmit="return validateConversionReview();">
					<div class="form-title center">
						<?php echo $lang['ReviewAddress']; ?>
					</div>
					
					<div class="space"></div>
					
					<div class="form-sub-title">
						<?php echo $lang['ContactInformation']; ?>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['ContactNo']; ?></label>
							<input type="text" id="phonenumber" name="phonenumber" value="<?php echo $_SESSION['phonenumber']; ?>" readonly/>
							<div class="error"></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['EmailAddress']; ?></label>
							<input type="email" id="email" name="email" value="<?php echo $_SESSION['email']; ?>" readonly/>
							<div class="error"></div>
						</div>
					</div>
					
					<div class="form-sub-title">
						<?php echo $lang['Address']; ?>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['Address1']; ?></label>
							<input type="text" id="address_line_1" name="address_line_1" value="<?php echo $_SESSION['address_line_1']; ?>" />
							<div class="error"><?php echo $_SESSION['error_address_line_1'] ?? ''; unset($_SESSION['error_address_line_1']); ?></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['Address2']; ?> (<?php echo $lang['Optional']; ?>)</label>
							<input type="text" id="address_line_2" name="address_line_2" value="<?php echo $_SESSION['address_line_2']; ?>" />
							<div class="error"></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['City']; ?></label>
							<input type="text" id="city" name="city" value="<?php echo  $_SESSION['city'];?>" />
							<div class="error"><?php echo $_SESSION['error_city'] ?? ''; unset($_SESSION['error_city']); ?></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['PostCode']; ?></label>
							<input type="text" id="postcode" name="postcode" value="<?php echo $_SESSION['postcode'];?>" />
							<div class="error"><?php echo $_SESSION['error_postcode'] ?? ''; unset($_SESSION['error_postcode']); ?></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['State']; ?></label>
							<div class="dropdown">
								<select id="state" name="state" >
									<option <?php echo $_SESSION['state'] == 'Johor' ? 'selected' : ''; ?> >Johor</option>
									<option <?php echo $_SESSION['state'] == 'Kedah' ? 'selected' : ''; ?> >Kedah</option>
									<option <?php echo $_SESSION['state'] == 'Kelantan' ? 'selected' : ''; ?> >Kelantan</option>
									<option <?php echo $_SESSION['state'] == 'Labuan' ? 'selected' : ''; ?> >Labuan</option>
									<option <?php echo $_SESSION['state'] == 'Kuala Lumpur' ? 'selected' : ''; ?> >Kuala Lumpur</option>
									<option <?php echo $_SESSION['state'] == 'Malacca' ? 'selected' : ''; ?> >Malacca</option>
									<option <?php echo $_SESSION['state'] == 'Negeri Sembilan' ? 'selected' : ''; ?> >Negeri Sembilan</option>
									<option <?php echo $_SESSION['state'] == 'Pahang' ? 'selected' : ''; ?> >Pahang</option>
									<option <?php echo $_SESSION['state'] == 'Penang' ? 'selected' : ''; ?> >Penang</option>
									<option <?php echo $_SESSION['state'] == 'Perak' ? 'selected' : ''; ?> >Perak</option>
									<option <?php echo $_SESSION['state'] == 'Perlis' ? 'selected' : ''; ?> >Perlis</option>
									<option <?php echo $_SESSION['state'] == 'Putrajaya' ? 'selected' : ''; ?> >Putrajaya</option>
									<option <?php echo $_SESSION['state'] == 'Sabah' ? 'selected' : ''; ?> >Sabah</option>
									<option <?php echo $_SESSION['state'] == 'Sarawak' ? 'selected' : ''; ?> >Sarawak</option>
									<option <?php echo $_SESSION['state'] == 'Selangor' ? 'selected' : ''; ?> >Selangor</option>
									<option <?php echo $_SESSION['state'] == 'Terengganu' ? 'selected' : ''; ?> >Terengganu</option>
								</select>
							</div>
							<div class="error"><?php echo $_SESSION['error_state'] ?? ''; unset($_SESSION['error_state']); ?></div>
						</div>
					</div>
					
					<div class="form-sub-title">
						<?php echo $lang['ConversionFee']; ?>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['ConversionFee']; ?></label>
							<input type="text" readonly value="RM <?php echo number_format($_SESSION['conversion_fee'], 2); ?>" />
							<div class="error"></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['TransactionFee']; ?></label>
							<input type="text" readonly value="RM <?php echo number_format($_SESSION['transaction_fee'], 2); ?>" />
							<div class="error"></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['TotalFee']; ?></label>
							<input type="text" readonly value="RM <?php echo number_format($_SESSION['total_fee'], 2);?>" />
							<div class="error"></div>
						</div>
					</div>

					<div class="form-row tnc">
						<span class="checkbox">
							<input type="checkbox" name="tnc" onchange="activateButton(this)"/>
							<span></span> 
						</span>
						<?php echo $lang['Iagreetothe']; ?> <a href="Tnc.php"  ><?php echo $lang['Terms_and_Conditions']; ?></a>.
					</div>
					
					<input type="hidden" name="nok_full_name" value="<?php echo $_SESSION['nok_full_name']; ?>" />
					<input type="hidden" name="nok_phone" value="<?php echo $_SESSION['nok_phone']; ?>" />

					<div class="form-row center">
						<div class="form-col">
							<div class="form-row">
									<div class="form-col center">
										<label><?php echo $lang['CAMPAIGN_CODE']; ?></label>
										<input id="campaigncode" type="text" placeholder="(Optional)" name="campaigncode" />
										<div class="error"><?php echo $lang['ConvertReviewCampaign']; ?>
									</div>
								</div>
							</div>
							<label id="pinlabel"><?php echo $lang['SecurityPIN']; ?></label>
							<div class="verify-box">
								<input id="pin1" name="pin[]" autocomplete="off" type="password" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" maxLength="1" size="1" min="1" max="9" pattern="[0-9]{1}" class="verify1" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin2" name="pin[]" autocomplete="off" type="password" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" maxLength="1" size="1" min="1" max="9" pattern="[0-9]{1}" class="verify2" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin3" name="pin[]" autocomplete="off" type="password" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" maxLength="1" size="1" min="1" max="9" pattern="[0-9]{1}" class="verify3" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin4" name="pin[]" autocomplete="off" type="password" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" maxLength="1" size="1" min="1" max="9" pattern="[0-9]{1}" class="verify4" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin5" name="pin[]" autocomplete="off" type="password" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" maxLength="1" size="1" min="1" max="9" pattern="[0-9]{1}" class="verify5" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin6" name="pin[]" autocomplete="off" type="password" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" maxLength="1" size="1" min="1" max="9" pattern="[0-9]{1}" class="verify6" oninput="this.value=this.value.slice(0,this.maxLength)"/>
							</div>
							<div class="error"><?php echo $_SESSION['error_pin'] ?? ''; unset($_SESSION['error_pin']); ?></div>
						</div>
					</div>

					<div class="space"></div>
					<div class="form-row submit">
						<div class="form-col">
							<div class="error"><?php echo $_SESSION['conversion_error'] ?? ''; unset($_SESSION['conversion_error']); ?></div>
							<input name="quantity" type="hidden" value="<?php echo $quantity ?? $_SESSION['quantity']; ?>" />
							<input name="product" type="hidden" value="<?php echo $product ?? $_SESSION['product']; ?>" />
							<input name="weight" type="hidden" value="<?php echo $weight ?? $_SESSION['weight']; ?>" />
							<?php if (($_SESSION['payment_mode'] ?? $payMethod ?? 'fpx') === 'wallet') { ?>
								<input name="wallet" type="hidden" class="btn" value="<?php echo $lang['PAYBYWALLET']; ?>" />
							<?php } else { ?>
								<input name="fpx" type="hidden" class="btn" value="<?php echo $lang['PAYBYFPX']; ?>" />
							<?php } ?>

							<input type="button" onclick="this.disabled=true;this.form.submit();" class="btn" id="buttonconfirm" name="<?php echo $_SESSION['payment_mode'] ?? $payMethod ?? 'fpx' ?>" value="<?php echo $lang['Confirm']; ?>" />
						</div>
					</div>
				</form>
			</div>
		
		</div>
		
	</div>
	
</body>
</html>