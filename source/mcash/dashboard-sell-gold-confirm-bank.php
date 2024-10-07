<!doctype html>
<html lang="en-gb" dir="ltr">

<?php include('controllers/login.php');  ?>
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<?php include('controllers/gettransactionfee.php');  ?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
     <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
<?php 
  $sql = "SELECT * From mybank WHERE bnk_id ='" . $_SESSION['bank_id'] . "'";
  $query = mysqli_query($connection, $sql);
  $rowCount = mysqli_num_rows($query);

  //echo 'try ' . $_SESSION['displayname'];  
 
  // If query fails, show the reason 
	  
	  if($rowCount <= 0) {
	   //
	  } else {
		  // Fetch user data and store in php session
		  while ($row = mysqli_fetch_array($query)) {
			$bankname          = $row['bnk_name'];
			
		  }
	  }
?>
<style>
	#customerbuycountdown  {
	margin: auto;
	min-width:200px;
	max-width:1000px;
	/*border-color: #1D976C;
	background-color: #93F9B9;*/
	border-color: #d3a817;
	background-color: #EEEEEE;
	}

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
	#btnpaybank{
		width: 100%;
	}
	#btneditbanknumber{
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
	#btnpaybank{
		width: 60%;
	}
	#btneditbanknumber{
		width: 60%;
	}
	#reflesh{
		width: 50%;
	}
}


</style>

<style>
#bankaccountname{
	text-align: center;
}

#bankaccountnumber{
	text-align: center;
}

#bankaccountusername{
	text-align: center;
}

#sellcampaigncode{
	text-align: center;
}
</style>

<script>

/*
$(document).ready(function(){
  $("#reflesh").click(function(){
    $("#reflesh").hide();
    var js_variable  = '<php echo $_SESSION['token'];?>';
       $.ajax({
                
    type: "POST",
  url: "controllers/refleshprice.php",
  data: {},
dataType: '',
cache: false,
	success: function(response) {
	//location.reload();
	window.location.assign("dashboard-sell-gold.php");
	}
});
  });

});
*/

$(document).ready(function(){
    $(this).scrollTop(0);

 var today = new Date();

var date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();

var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();


//document.getElementById('rootdate').innerHTML = date ;
//document.getElementById('roottime').innerHTML = time ;
 
  $("#pinerror").hide();

  $(".inputs").keyup(function () {
    if (this.value.length == this.maxLength) {
      $(this).next('.inputs').focus();
    }
});

$(':input').keydown(function(e) {
    if ((e.which == 8 || e.which == 46) && $(this).val() =='') {
        $(this).prev('input').focus();
    }
});

/*
$("#weight").on("input", function() {
  var customerbuy  = '<php echo $_SESSION['customerbuy'];?>';
  var weight = $(this).val();
  var total = customerbuy * weight;
  $("#total").val(total);
 // alert(total); 
});*/

$(document).on('focus', selector, function() {
    document.querySelector(selector).scrollIntoView();
});

$("#reflesh").click(function(){
    $("#reflesh").hide();
   
location.reload();
      
     

 
  });

  $(window).resize(function(){
            $('input[type="number"],textarea').on('click', function () {
            var target = this;
            setTimeout(function(){
                    target.scrollIntoViewIfNeeded();
                    // console.log('scrollIntoViewIfNeeded');
                },400);
            });         
        });

});

window.onload = function(){

(function(){

//Onload trigger
//document.getElementById("btnpaywallet").disabled = true;
//document.getElementById("btnpaybank").disabled = true;
//document.getElementById("buttonconfirm").disabled = true;
//document.getElementById("tnccheckbox").checked = false;

// Do checking if there are any error
// Save session data here

// Start refresh counter
  $("#reflesh").hide();
  var counter = 20;
 
  setInterval(function() {
    counter--;
    if (counter >= 0) {
      span = document.getElementById("count");
      span.innerHTML = '<?php echo $lang['BUYSELLCOUNTER']; ?>' + " " + counter;
    }
    // Display 'counter' wherever you want to display it.
    if (counter === 0) {//    alert('this is where it happens');
	// Change color to red
	document.getElementById("customerbuycountdown").style.backgroundColor = "#e52d27";
	document.getElementById("customerbuycountdown").style.borderColor = "#b31217";
	document.getElementById("customerbuycountdown").style.color = "#ffffff";
    //    alert('this is where it happens');
 
	//window.location = 'dashboard-sell-gold.php';
    $("#buttonconfirm").hide();
	//$("#btnpaywallet").hide();
	$("#btnpaybank").hide();
	$("#reflesh").show();
        clearInterval(counter);
    }

	

  }, 1000);

})();

}

</script>

<script>
	function activateButton(element) {

		if(element.checked) {
			//document.getElementById("btnpaywallet").disabled = false;
			document.getElementById("btnpaybank").disabled = false;
			document.getElementById("buttonconfirm").disabled = false;
		}
		else  {
			//document.getElementById("btnpaywallet").disabled = true;
			document.getElementById("btnpaybank").disabled = true;
			document.getElementById("buttonconfirm").disabled = true;
		}

	}
</script>
<script>
	function empty() {
		
		var pin1 = document.getElementById("pin1").value;
		var pin2 = document.getElementById("pin2").value;
		var pin3 = document.getElementById("pin3").value;
		var pin4 = document.getElementById("pin4").value;
		var pin5 = document.getElementById("pin5").value;
		var pin6 = document.getElementById("pin6").value;
		if (pin1 == "") {
			$("#pinerror").show();
			return false;
		}
		if (pin2 == "") {
			$("#pinerror").show();
			return false;
		}
		if (pin3 == "") {
			$("#pinerror").show();
			return false;
		}
		if (pin4 == "") {
			$("#pinerror").show();
			return false;
		}
		if (pin5 == "") {
			$("#pinerror").show();
			return false;
		}
		if (pin6 == "") {
			$("#pinerror").show();
			return false;
		}
	}
</script>

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['SELL_GOLD_CONFIRM_TITLE'] ?></title>
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="dashboard-sell-gold-confirm-bank.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="dashboard-sell-gold-confirm-bank.php?lang=bm">BM</a></li>
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
			<?php echo $lang['SellGold'] ?>
		</div>
		<a class="back" href="dashboard-sell-gold.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="main-widget">
				<div class="table-row title">
					<div class="sub">e-AQAD</div>
					<?php echo $lang['CustomerSell']; ?>
				</div>
				<div class="table-row">
				<div class="col label"><?php echo $lang['DATE']; ?></div>
					<div class="col value em"><?php date_default_timezone_set("Asia/Kuala_Lumpur"); echo date('D, d M Y H:i:s') ?></div>
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
					<div class="col label"><?php echo $lang['GOLDSOLD']; ?> <span class="unit">Gram</span></div>
					<div class="col value"><?php echo  number_format($_SESSION['weight'],3)  ?> gram</div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['SELLPRICE']; ?> <span class="unit">Unit price</span></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['unit_price'], 2)  ?> / gram</div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['FEES']; ?></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['transaction_fee'], 2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['TOTALSELL']; ?></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['total_transaction_amount'],2)  ?></div>
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
					<div class="value">RM <?php echo  number_format($_SESSION['total_transaction_amount'], 2)     ?></div>
				</div>
			</div>
			
			<div class="main-widget">
				<div class="form-sub-title center">
					<?php echo $lang['FPXHEADERTIMER']; ?>
				</div>
				<div class="form-row">
					<div class="form-col center limit2">
						<span class="wait-counter" id="customerbuycountdown"><a class="convert-unit avail" ><span id="count" class=" center"></span></a></span>
						<div class="error"></div>
					</div>
				</div>
			</div>
			
			<div class="main-widget">

				<form id="callpayform" method="post" action="controllers\transactionsell.php" onsubmit="return validate();">
							
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['BankAccountname']; ?></label>
							
								<!--<select>
									<option>Bank Muamalat Berhad</option>
								</select>-->
								<input type="text" disabled="disabled" id="bankaccountname" name="bankaccountname" value="<?php echo  $bankname     ?>" />
								<div class="error"></div>
							
							<div class="error"></div>
						</div>
					</div>
					
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['BankAccountnumber']; ?></label>
							<input type="text" disabled="disabled" id="bankaccountnumber" name="bankaccountnumber" value="<?php echo  $_SESSION['bank_acc_number']     ?>" />
							<div class="error"></div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-col">
							<label><?php echo $lang['BankAccountUsername']; ?></label>
							
								<!--<select>
									<option>Bank Muamalat Berhad</option>
								</select>-->
								<input type="text" disabled="disabled" id="bankaccountusername" name="bankaccountusername" value="<?php echo  $_SESSION['bank_acc_name']     ?>" />
								<div class="error"></div>
							
							<div class="error"></div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-col" style="text-align:center">
							<!--<a href="controllers\callwallet.php" >	<input type="submit" class="btn" value="Pay By Wallet" /></a>-->
							<!--<a id="btnpaywallet" href="< echo $_SESSION['walleturl']  ?>" >	<input type="submit" class="btn" value="Pay By Wallet" /></a>-->
							<!--<input id="btnpaywallet"  type="submit" formaction="controllers\callwallet.php" class="btn" value="<php echo $lang['PAYBYWALLET']; ?>" />-->		
							<input id="btneditbanknumber" formaction="edit-bank.php" type="submit" class="btn" value="<?php echo $lang['BANKNOEDIT']; ?>" />		
						</div>
					</div>	
				
				
					<div class="form-row">
						<div class="form-col center limit2">
							<label><?php echo $lang['CAMPAIGN_CODE']; ?></label>
								<input id="sellcampaigncode" type="text" placeholder="(Optional)" name="sellcampaigncode" />
								<div class="error"><?php echo $lang['NRIC_CAMPAIGN_ERROR']; ?></div>
						</div>
					</div>
					
					<div class="form-row center">
						<div class="form-col">
							<label><?php echo $lang['SECURITY_PIN']; ?></label>
							<div  id="form"  style="text-align:center;" class="verify-box">
								<input class="inputs " id="pin1" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pin1" style="text-align: center; " maxLength="1" size="1" min="0" max="9"  pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input class="inputs " id="pin2" autocomplete="off" type="number" style="-webkit-text-security:disc;" name="pin2" inputmode="numeric" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input class="inputs " id="pin3" autocomplete="off" type="number" style="-webkit-text-security:disc;" name="pin3" inputmode="numeric" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input class="inputs " id="pin4" autocomplete="off" type="number" style="-webkit-text-security:disc;" name="pin4" inputmode="numeric" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input class="inputs " id="pin5" autocomplete="off" type="number" style="-webkit-text-security:disc;" name="pin5" inputmode="numeric" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input class="inputs " id="pin6" autocomplete="off" type="number" style="-webkit-text-security:disc;" name="pin6" inputmode="numeric" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
							</div>
							<div id="pinerror" class="error"><?php echo $lang['INCORRECT_SECURITY_PIN']; ?></div>
						</div>
					</div>
					<div class="space"></div>
					<div class="form-row submit">
						<div class="form-col">
							<input type="hidden" name="bank" value="1">
							<input id="buttonconfirm" class="btn " formaction="controllers\transactionsell.php" value="<?php echo $lang['SUBMIT']; ?>" type="button" onclick="this.disabled=true;this.form.submit();">
							<input id="formtype" type="hidden" name="formtype" value="SELL">
						</div>
					</div>
				</form>
				<div class="form-row">
					<div class="form-col center">
						<a class="btn" id="reflesh" href="dashboard-sell-gold-confirm-bank.php"><?php echo $lang['REFRESH']; ?></button></a>
					</div>
				</div>
				
			</div>
			
		</div>
		
	</div>
	
</body>
</html>