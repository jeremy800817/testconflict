<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    } ?>
<?php include('common.php');  ?>
<?php include('controllers/gettransactionfee.php');  ?>

<script>

$(document).ready(function(){
    $(this).scrollTop(0);

  $("#reflesh").click(function(){
    $("#reflesh").hide();
    var js_variable  = '<?php echo $_SESSION['token'];?>';
       $.ajax({
                
    type: "POST",
  url: "controllers/refleshprice.php",
  data: {},
dataType: '',
cache: false,
success: function(response) {
location.reload();
      
     

    }
});
  });

});


$(document).ready(function(){
 var today = new Date();

var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();

var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();


document.getElementById('rootdate').innerHTML = date ;
document.getElementById('roottime').innerHTML = time ;
 
});


</script>

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['BUY_GOLD_RECEIPT_TITLE'] ?></title>
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="dashboard-buy-gold-receipt.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="dashboard-buy-gold-receipt.php?lang=bm">BM</a></li>
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
			<?php echo $lang['BuyGold'] ?>
		</div>
		<a class="back"  href="dashboard-buy-gold.php">Back</a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="main-widget">
				<div class="table-row title">
					<div class="sub"><?php echo $lang['Receipt'] ?></div>
					<?php echo $lang['CustomerBuy'] ?>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['DATE'] ?></div>
					<div class="col value"><?php date_default_timezone_set("Asia/Kuala_Lumpur"); echo date('D, d M Y H:i:s') ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['NAME'] ?></div>
					<div class="col value"><?php echo  $_SESSION['name']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['NRIC'] ?></div>
					<div class="col value"><?php echo  $_SESSION['ic']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['GOGOLDACCNO'] ?></div>
					<div class="col value"><?php echo  $_SESSION['accountcode']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['GOLDPURCHASE'] ?> <span class="unit">Gram</span></div>
					<div class="col value"><?php echo  number_format($_SESSION['weight'],3)  ?> gram</div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['PURCHASEPRICE'] ?>e <span class="unit">Unit price</span></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['unit_price'], 2)  ?> / gram</div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['FEES'] ?></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['transaction_fee'], 2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['TOTALBUY'] ?></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['total_transaction_amount'],2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['PURITY'] ?></div>
					<div class="col value"><?php echo $lang['PURITY_VALUE'] ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['VAULT'] ?></div>
					<div class="col value">SG4S, Malaysia<br /><?php echo $lang['STORAGE_PROVIDER'] ?></div>
				</div>
				<div class="table-row">
					<div class="col label em"><?php echo $lang['FINAL_TOTAL'] ?></div>
					<div class="col value em highlight">RM <?php echo  number_format($_SESSION['total_transaction_amount'],2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['Status'] ?></div>
					<div class="col value"><?php echo $lang['Successful'] ?></div>
				</div>
				
				<div class="footnote">
					<?php echo $lang['RECEIPT_TEXT'] ?>
				</div>
			</div>
			
			<div class="page-action">
				<a class="back" href="index.php"><input type="submit" class="btn" formaction="index.php" value="<?php echo $lang['Done'] ?>" /></a>
			</div>
			
		</div>
		
	</div>
	
</body>
</html>