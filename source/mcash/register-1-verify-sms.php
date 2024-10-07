<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<?php include('controllers/register1-sms.php'); ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_REGISTER_1'];?></title>
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

	$(".inputs").keyup(function () {
    $(this).next().focus();

	  // Set FAQ Setting
	  var login = '<?php echo $_SESSION['login'];?>';
		if (login == "no") {

			$("#resetsecurity").hide();
			$("#editprofile").hide();
			$("#editbankaccount").hide();
			$("#resetsecuritypintop").hide();
			$("#editprofiletop").hide();
			$("#editbankaccounttop").hide();

		}else if(login =="nodirect"){
			$("#resetsecurity").hide();
			$("#editprofile").hide();
			$("#editbankaccount").hide();
			$("#resetsecuritypintop").hide();
			$("#editprofiletop").hide();
			$("#editbankaccounttop").hide();

		}
		else {

			$("#resetsecurity").show();
			$("#editprofile").show();
			$("#editbankaccount").show();
			$("#resetsecuritypintop").show();
			$("#editprofiletop").show();
			$("#editbankaccounttop").show();
		}
 
	
});

$("#back_btn").click(function (){
	alert('New OTP have sent to you. Please wait for a moment.');
	$.ajax({
                
				type: "POST",
			url: "controllers/register1-sms.php",
			data: {},
			dataType: 'json',
			cache: false,
			success: function(response) {
			
				
			
				}
			});
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="register-1-verify-sms.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="register-1-verify-sms.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="page-content-form">
				
				<div class="page-title">
					<?php echo $lang['RegisterNow']    ?>
				</div>
				<div class="page-title-desc">
					<?php echo $lang['Joinus']    ?>
				</div>
				
				<div class="signup-navi">
					<div class="step active">
						<div class="num">1</div>
						<div class="label"><?php echo $lang['AboutYou']    ?></div>
					</div>
					<div class="step">
						<div class="num">2</div>
						<div class="label"><?php echo $lang['NextofKin']    ?></div>
					</div>
				</div>
				
				<div class="form-title">
					<?php echo $lang['AboutYou'] ?>
				</div>
				
				<div class="form-row">
					<div class="form-col">
						<label>Full Name (as per NRIC)</label>
						<input  value="<?php echo $_SESSION['Tname'] ?>" type="text" name="" />
					
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Email Address</label>
						<input type="email" value="<?php echo $_SESSION['Temail'] ?>" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>NRIC</label>
						<input value="<?php echo $_SESSION['TidNo'] ?>" type="text" name="" placeholder="XXXXXX-XX-XXXX" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Contact No.</label>
						<input value="<?php echo $_SESSION['TtelHp'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Address 1</label>
						<input value="<?php echo $_SESSION['Raddress1'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Address 2</label>
						<input value="<?php echo $_SESSION['Raddress2'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>City</label>
						<input value="<?php echo $_SESSION['Rcity'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Post Code</label>
						<input value="<?php echo $_SESSION['Rpostcode'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>State</label>
						<input value="<?php echo $_SESSION['Rstate'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Bank Name</label>
						<input value="<?php echo $_SESSION['Rbank'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Bank Account Number</label>
						<input value="<?php echo $_SESSION['Raccountnumber'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label>Occupation Category</label>
						<input value="<?php echo $_SESSION['Roccupation'] ?>" type="text" name="" />
						<div class="error"></div>
					</div>
				</div>
		
				
				<div class="form-row submit">
					<div class="form-col">
						<input type="submit" class="btn" value="Next" />
					</div>
				</div>
			</div>
		
		</div>
		
	</div>

	<div class="overlay">
		<div class="overlay-wrap">
			<div class="overlay-wrap-inner">
				<div class="pop">
					<div class="page-content-form">
						<div class="modal-title">
						<?php echo $lang['VerifyCN']; ?>
						</div>
						<div class="form-title center">
						<?php echo $lang['EnterPIN']; ?>
						</div>
						<div class="page-title-desc">
						<?php echo $lang['sentPINto']; echo $_SESSION['Rcontact']; ?>.
						</div>
						<form method="post" action="register-2.php" >
						<div class="form-row">
							<div class="form-col">
								<div class="verify-box">
									<input id="pin1" name="pin1" type="password" maxlength="1" class="verify1 inputs"  />
									<input id="pin2" name="pin2" type="password" maxlength="1" class="verify2 inputs"  />
									<input id="pin3" name="pin3" type="password" maxlength="1" class="verify3 inputs" />
									<input id="pin4" name="pin4" type="password" maxlength="1" class="verify4 inputs" />
									<input id="pin5" name="pin5" type="password" maxlength="1" class="verify5 inputs" />
									<input id="pin6" name="pin6" type="password" maxlength="1" class="verify6 inputs" />
								</div>
								
							</div>
						</div>
						<div class="form-row submit">
							<div class="form-col">
								<input type="submit" class="btn" value="Confirm" />
								<input id="back_btn" type="button" class="btn" value="Resend OTP" />
							</div>
						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	
</body>
</html>