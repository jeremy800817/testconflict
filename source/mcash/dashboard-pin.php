<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<?php include('controllers/forgetpin.php'); ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reset PIN | GoGold</title>
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

<script>
	 $(document).ready(function(){
		$(this).scrollTop(0);
	
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
		

// form submit check

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

$("#pinnumber").keypress(function() {
    return (/\d/.test(String.fromCharCode(event.which) ))
});

$("#confirmpin").keypress(function() {
    return (/\d/.test(String.fromCharCode(event.which) ))
});

$('#confirmpin6').blur(function() {
   var pin1 = $('#pinnumber1').val();
   var pin2 = $('#pinnumber2').val();
   var pin3 = $('#pinnumber3').val();
   var pin4 = $('#pinnumber4').val();
   var pin5 = $('#pinnumber5').val();
   var pin6 = $('#pinnumber6').val();
   var pincomplete = pin1 + pin2 + pin3 + pin4 + pin5 + pin6;

   var confirm1 = $('#confirmpin1').val();
   var confirm2 = $('#confirmpin2').val();
   var confirm3 = $('#confirmpin3').val();
   var confirm4 = $('#confirmpin4').val();
   var confirm5 = $('#confirmpin5').val();
   var confirm6 = $('#confirmpin6').val();
var confirmpincomplete = confirm1+confirm2+confirm3+confirm4+confirm5+confirm6;


   if(pincomplete == confirmpincomplete){
	$("#message").html("Pin match.");
	$("#tnccheckbox").attr('disabled', false);
    }else{
		$("#message").html("Pin not match");
		$("confirmpin1").val("");
		$("confirmpin2").val("");
		$("confirmpin3").val("");
		$("confirmpin4").val("");
		$("confirmpin5").val("");
		$("confirmpin6").val("");
		$("confirmpin1").focus();
		$("#tnccheckbox").attr('disabled', true);

	}

  });

  $( "#confirmpin1" ).keydown(function() {
	$("#tnccheckbox").attr('disabled', false);
});

$(':input').keydown(function(e) {
    if ((e.which == 8 || e.which == 46) && $(this).val() =='') {
        $(this).prev('input').focus();
    }
});

  $("#pinerror").hide();

  $(".inputs").keyup(function () {
    if (this.value.length == this.maxLength) {
      $(this).next('.inputs').focus();
    }
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
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="edit-profile.php?dashboard-pin=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="edit-profile.php?dashboard-pin=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['resetpin']; ?></div>
		<!--<a class="back" href="index.php">Back</a>-->
		<a class="back" href="index.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			<div class="main-widget">
			<form method="post" action="">	
				<div class="page-title-desc2">
					<?php echo $lang['resetpintext'] ?>
				</div>
				
				
				<div class="form-row">
					<div class="form-col">
						<label>
							<div class="form-capitalize-title center">
								<?php echo $lang['Newpin'] ?>
							</div>
						</label>
						<div  id="form"  style="text-align:center;" class="verify-box">
						<input id="pinnumber1" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pinnumber1" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pinnumber2" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pinnumber2" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pinnumber3" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pinnumber3" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pinnumber4" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pinnumber4" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pinnumber5" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pinnumber5" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="pinnumber6" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="pinnumber6" style="text-align: center; " maxLength="1" size="1" min="0s" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
						</div>
					</div>
					<br>
				</div>

				
				<div class="form-row">
					<div class="form-col">
						<label>
						<div class="form-capitalize-title center">
							<?php echo  $lang['confirmpin'] ?>
						</div>
						</label>
						<div  id="form"  style="text-align:center;" class="verify-box">
						<input id="confirmpin1" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="confirmpin1" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="confirmpin2" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="confirmpin2" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="confirmpin3" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="confirmpin3" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="confirmpin4" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="confirmpin4" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="confirmpin5" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="confirmpin5" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
								<input id="confirmpin6" class="inputs" autocomplete="off" type="number" style="-webkit-text-security:disc;" inputmode="numeric" name="confirmpin6" style="text-align: center; " maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" oninput="this.value=this.value.slice(0,this.maxLength)"/>
						</div>
						<label id="message" style="text-align: center;"></label> 
					
					</div>
				</div>
				
				<div class="table-row tnc">
					<span class="checkbox">
						<input id="tnccheckbox" type="checkbox" name="tnccheckbox" onchange="activateButton(this)"/>
						<span></span> 
					</span>
					<label><?php echo $lang['ConfirmPinDetails'] ?></label>
				</div>
				
				
				<div class="form-row submit">
					<div class="form-col">
							<label id="pinlabel">
								<div class="form-capitalize-title center">
									<?php echo $lang['EnterOTP']; ?>
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
						<input id="submitbt" type="submit" formaction="controllers/resetpin.php" name="submit_form" class="btn" value="<?php echo  $lang['SUBMIT'] ?>" />
					</div>
				</div>
			</form>
			</div>
		</div>
		
	</div>
	
</body>
</html>