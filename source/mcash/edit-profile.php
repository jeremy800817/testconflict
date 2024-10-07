<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>
<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
$nokphone = $_SESSION['nok_phone'];

 $str = ltrim($nokphone , '+');
?>

<!doctype html>
<html lang="en-gb" dir="ltr">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['TITLE_EDIT_PROFILE'];?></title>
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

		$('#subcategory').hide();
		$('#submitbt').hide();

		$("#pinlabel").hide();
		$("#pin1").hide();
		$("#pin2").hide();
		$("#pin3").hide();
		$("#pin4").hide();
		$("#pin5").hide();
		$("#pin6").hide();
		$("#pinerror").hide();

function validate(evt)
{
    if(evt.keyCode!=8)
    {
        var theEvent = evt || window.event;
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
        var regex = /[0-9]|\./;
        if (!regex.test(key))
        {
            theEvent.returnValue = false;

            if (theEvent.preventDefault)
                theEvent.preventDefault();
            }
        }
    }

	
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

$(document).on('focus', selector, function() {
    document.querySelector(selector).scrollIntoView();
});
$("#pin").keypress(function() {
    return (/\d/.test(String.fromCharCode(event.which) ))
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
.signup-navi {
    width: 100%;
    /* position: relative; */
    max-width: 201px;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 36px;
    margin-top: 24px;
    display: flex;
	color: black;
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
		<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="edit-profile.php?lang=en">Eng</a></li>
        <li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="edit-profile.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['EditProfile']; ?></div>
		<!--<a class="back" href="index.php">Back</a>-->
		<a class="back" href="index.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			<div class="main-widget">
				<form method="post" action="editprofile-complete.php" >
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['Address1'] ?></label>
						<input id="address_line_1" value="<?php echo $_SESSION['address_line_1'] ?>" type="text" name="address_line_1" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['Address2'] ?></label>
						<input id="address_line_2" value="<?php echo  $_SESSION['address_line_2'] ?>" type="text" name="address_line_2" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['City']  ?></label>
						<input id="city" value="<?php echo $_SESSION['city'] ?>" type="text" name="city" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['PostCode'] ?></label>
						<input id="postcode" value="<?php echo $_SESSION['postcode'] ?>" name="postcode" required minlength="5" required oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="5" type="number" onkeypress='validate(event)' onkeydown="javascript: return event.keyCode == 69 ? false : true" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['State'] ?></label>
						<select id="state" name="state" >
<!-- This is default define value using php variable $r -->
					<option value='<?php echo $_SESSION['state'];?>' selected='selected'><?php echo $_SESSION['state'];?></option>

<!-- Other options values -->
                    <option value="JOHOR">JOHOR</option>
                    <option value="KEDAH">KEDAH</option>
                    <option value="KELANTAN">KELANTAN</option>
                    <option value="MELAKA">MELAKA</option>
                    <option value="NEGERI SEMBILAN">NEGERI SEMBILAN</option>
                    <option value="PAHANG">PAHANG</option>
                    <option value="PULAU PINANG">PULAU PINANG</option>
                    <option value="PERAK">PERAK</option>
                    <option value="PERLIS">PERLIS</option>
                    <option value="SELANGOR">SELANGOR</option>
                    <option value="TERENGGANU">TERENGGANU</option>
                    <option value="SABAH">SABAH</option>
                    <option value="SARAWAK">SARAWAK</option>
                    <option value="WP KUALA LUMPUR">WP KUALA LUMPUR</option>
                    <option value="WP LABUAN">WP LABUAN</option>
                    <option value="WP PUTRAJAYA">WP PUTRAJAYA</option>
                    </select>
						<div class="error"></div>
					</div>
				</div>
				
				<div class="form-title center">
					<?php echo $lang['EmergencyContact']; ?>
				</div>
				
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['NoKName'] ?> </label>
						<input id="nextofkinname" type="text" name="nextofkinname" value="<?php echo $_SESSION['nok_full_name'] ?>" required />
						
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['NoKContact'] ?></label>
						<input id="nextofkincontact" value="<?php echo $str ?>" type="number" placeholder="601xxxxxxxx" name="nextofkincontact" required  />
						<div class="error"></div>
					</div>
				</div>
				
				<div class="table-row tnc">
					<span class="checkbox">
						<input id="tnccheckbox" type="checkbox" name="" onchange="activateButton(this)"/>
						<span></span> 
					</span>
					I agree to the <a rel="noopener noreferrer" href="Tnc.php">Terms & Conditions</a>
				</div>
				
				
				<div class="form-row center">
					<div class="form-col">
						<label id="pinlabel">
							<div class="form-capitalize-title center">
								<?php echo $lang['SecurityPIN']; ?>
							</div>
						</label>
						<div class="verify-box">
							<input id="pin1" autocomplete="off" type="number" style="-webkit-text-security:disc;"" pattern="[0-9]*" inputmode="numeric" name="pin1" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify1" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
							<input id="pin2" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin2" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify2" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
							<input id="pin3" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin3" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify3" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
							<input id="pin4" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin4" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify4" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
							<input id="pin5" autocomplete="off" type="number" style="-webkit-text-security:disc;"" pattern="[0-9]*" inputmode="numeric" name="pin5" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify5" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
							<input id="pin6" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin6" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify6" oninput="this.value=this.value.slice(0,this.maxLength)"required/>
						</div>
						<div class="error"><?php echo $_SESSION['error_pin'] ?? ''; unset($_SESSION['error_pin']); ?></div>
					</div>
				</div>
				
				<div class="form-row submit">
					<div class="form-col">
						<input id="submitbt" type="submit" class="btn" value="<?php echo $lang['SUBMIT']    ?>" />
					</div>
				</div>
				</form>
			</div>
		</div>
		
	</div>
	
</body>
</html>