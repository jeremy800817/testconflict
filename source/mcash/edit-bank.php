<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_EDIT_BANK'];?></title>
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


<?php 

if($_SESSION['bank_id']){
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

	$selection = '<option value="'.$_SESSION['bank_id'].'">'.$bankname.'</option>';
}else{
	$selection = '<option>Select</option>';
}

if($_SESSION['bank_acc_number']){
	$bankaccountnumber = $_SESSION['bank_acc_number'];
}else{
	$bankaccountnumber = '';
}

if($_SESSION['bank_acc_name']){
	$bankaccountname = $_SESSION['bank_acc_name'];
}else{
	$bankaccountname = '';
}
  
?>


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
	#submitbt{
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
	#submitbt{
		width: 30%;
	}
}
.form-row .error{
	text-align: center;
}

</style>

<script>
	 $(document).ready(function(){
		$(this).scrollTop(0);

		$('#subcategory').hide();
		$('#submitbt').hide();
		// Hide buttons
		$("#pinlabel").hide();
		$("#pin1").hide();
		$("#pin2").hide();
		$("#pin3").hide();
		$("#pin4").hide();
		$("#pin5").hide();
		$("#pin6").hide();
		$("#pinerror").hide();

		$("#pleaseselectbank").hide();
		$("#pleaseenterlettersonly").hide();
		
		$('#occupation').on('change', function(){
  



  var occupationid = $(this).val();

  if(occupationid == 4 || occupationid == 17){
	$('#subcategory').show();
	  $.ajax({
		  type:'POST',
		  url:'controllers/suboccupationcategory.php',
		  data:'occupationid='+occupationid,
		  success:function(html){
			
			$('#response').html(html);
						  
		  }
	  }); 
  }else{
	  $('#response').html('<option value="">No need to select. Please leave it empty.</option>'); 
  }
});

// form submit check

$("form").submit(function (e) {
   var validationFailed = false;
   // do your validation here ...
   bank = document.getElementById('bank').value;
	if("Select" ==  bank){
		$("#pleaseselectbank").show();
		validationFailed = true;
	}else{
		document.getElementById('bankname').value =  $("#bank option:selected").text();
	}
	
   if (validationFailed) {
      e.preventDefault();
      return false;
   }
});

// end submit check
$('input[type="checkbox"]').click(function(){
            if($(this).prop("checked") == true){
                $('#submitbt').show();
				$("#pinlabel").show();
				$("#pin1").show();
				$("#pin2").show();
				$("#pin3").show();
				$("#pin4").show();
				$("#pin5").show();
				$("#pin6").show();
            }
            else if($(this).prop("checked") == false){
                $('#submitbt').hide();
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

		function validate(evt) {
  var theEvent = evt || window.event;
  var key = theEvent.keyCode || theEvent.which;
  key = String.fromCharCode( key );
  var regex = /[0-9]|\./;
  if( !regex.test(key) ) {
    theEvent.returnValue = false;
    if(theEvent.preventDefault) theEvent.preventDefault();
  }
}

$(window).resize(function(){
            $('input[type="number"],textarea').on('click', function () {
            var target = this;
            setTimeout(function(){
                    target.scrollIntoViewIfNeeded();
                    // console.log('scrollIntoViewIfNeeded');
                },400);
            });         
        });

		$(".inputs").keyup(function () {
		if (this.value.length == this.maxLength) {
		$(this).next('.inputs').focus();
		}
		});

	 });

</script>


<script>
// validate input length
// (this, length)
var isNumber = function(e, l) {
	e = e || window.event;
    var charCode = e.which ? e.which : e.keyCode;
    return /\d/.test(String.fromCharCode(charCode));
}

// validate input length
// (this, length)
$(document).ready(function() {
 $('#username').on('keypress', function(e) {
  var regex = new RegExp("^[a-zA-Z ]*$");
  var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
  if (regex.test(str)) {
     return true;
  }
  e.preventDefault();
  return false;
 });
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
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="edit-bank.php?lang=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="edit-bank.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['BANKDETAILEDIT']; ?></div>
		<!--<a class="back" href="index.php">Back</a>-->
		<a class="back" href="index.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			<div class="main-widget">
				<form method="post" action="" >
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['BankName'] ?></label>
						<!--<div class="dropdown">-->
						<?php
                    /*echo '<select id="bank" name="bank" >
                    <option>Select</option>';*/
					echo '<select id="bank" name="bank" >'.
                    $selection;

                    $sqli ="SELECT * FROM mybank";
                    $query = mysqli_query($connection, $sqli);
                    while ($row = mysqli_fetch_array($query)) {
                      echo '<option value="'.$row['bnk_id'].'">'.$row['bnk_name'].'</option>';
                    }

                    echo '</select>';
                   ?>
						<!--</div>-->
						<div class="error"></div>
					</div>
					<div id= "pleaseselectbank" class="error"><?php echo $lang['PleaseSelectBank']; ?></div>
					<br>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo  $lang['BankAccountnumber'] ?></label>
						<input id="accountnumber" value="<?php echo $bankaccountnumber; ?>" type="text" name="accountnumber" maxlength="20"  onkeypress="return isNumber(event)" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo  $lang['BankAccountUsername'] ?></label>
						<input id="username" value="<?php echo $bankaccountname; ?>" type="text" name="username" maxlength="30"  onkeypress="return isText(event)" required />
						<div id="pleaseenterlettersonly" class="error"><?php echo $lang['PleaseEnterLettersOnly']; ?></div>
					</div>
				</div>
				
				<div class="table-row tnc">
					<span class="checkbox">
						<input id="tnccheckbox" type="checkbox" name="" onchange="activateButton(this)"/>
						<span></span> 
					</span>
					<label><?php echo $lang['ConfirmBankDetails'] ?></label>
				</div>
				
				
				<div class="form-row submit">
					<div class="form-col">
							<label id="pinlabel">
								<div class="form-capitalize-title center">
									<?php echo $lang['SECURITY_PIN']; ?>
								</div>
							</label>
							<div  id="form"  style="text-align:center;" class="verify-box">
								<input id="pin1"  class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pin1" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin2"  class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pin2" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin3"  class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pin3" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin4"  class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pin4" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin5"  class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pin5" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pin6"  class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pin6" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
							</div>
							<br>
							<br>
						<input id="submitbt" type="submit" formaction="controllers/editbankaccount.php" name="submit_form" class="btn" value="<?php echo  $lang['SUBMIT'] ?>" />
					</div>
				</div>
				<input id="bankname" value="" type="hidden" name="bankname" />
				</form>
			</div>
		</div>
		
	</div>
	
</body>
</html>