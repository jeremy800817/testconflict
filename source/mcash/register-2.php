<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<?php
 if(!isset($_SESSION)) {
	session_set_cookie_params(0);
	session_start();
}
if (isset($_POST['pin1'])) {
$code1 = $_POST['pin1'];
$code2 = $_POST['pin2'];
$code3 = $_POST['pin3'];
$code4 = $_POST['pin4'];
$code5 = $_POST['pin5'];
$code6 = $_POST['pin6'];

	$_SESSION['Rcode'] = $code1 . $code2 . $code3 . $code4 . $code5 . $code6; 

}


?>

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_REGISTER_2'];?></title>
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

$("#errorphone").hide();
	
$("#back_btn").click(function (){
  window.history.back();
});

$('#nextofkincontact').blur(function() {
   var contact = $('#nextofkincontact').val();
   let firstChar = contact.charAt(0) 

   if(firstChar == 6){
	$("#errorphone").hide();
    }else{
		$("#errorphone").show();
		$('#nextofkincontact').focus();
		$("#confirm").attr('disabled', true);
	}

  });

  $( "#nextofkincontact" ).keydown(function() {
	$("#confirm").attr('disabled', false);
});

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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="register-2.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="register-2.php?lang=bm">BM</a></li>
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
					<div class="step active">
						<div class="num">2</div>
						<div class="label"><?php echo $lang['NextofKin']    ?></div>
					</div>
				</div>
				
				<div class="form-title">
				<?php echo $lang['NextofKin']    ?>
				</div>
				<form method="post" action="register-thankyou.php" >
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['FullNameNRIC'] ?></label>
						<input id="nextofkin" type="text" name="nextofkin" required />
						<div class="error">Please enter Full Name</div>
					</div>
				</div>
				
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['ContactNo'] ?></label>
						<input id="nextofkincontact" type="number" name="nextofkincontact" placeholder="601XXXXXXXX" required />
						<div id="errorphone" class="error">Contact number must start with 6</div>
					</div>
				</div>
				<div class="form-row submit">
					<div class="form-col col2">
						<input id="back_btn" type="button" class="btn " value="Previous" />
						<input id="confirm"  type="submit" class="btn" value="Next" />
					</div>
				</div>
				</form>
			</div>
		
		</div>
		
	</div>
	
</body>
</html>