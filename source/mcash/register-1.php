<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('controllers/config/db.php'); ?>
<?php include('common.php');  ?>


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
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	 $(document).ready(function(){
		$(this).scrollTop(0);

		$('#subcategory').hide();
		$('#submitbt').hide();
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

$('input[type="checkbox"]').click(function(){
            if($(this).prop("checked") == true){
                $('#submitbt').show();
            }
            else if($(this).prop("checked") == false){
                $('#submitbt').hide();
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
$("#pin").keypress(function() {
    return (/\d/.test(String.fromCharCode(event.which) ))
});

$('input[type="checkbox"]').click(function(){
            if($(this).is(":checked")){
                var totalval = $("#pin").val().length;
                if (totalval < 6) {
					//alert('Min 6 digits pin code require');
					Swal.fire({
  title: 'Sorry!',
  text: 'Min 6 digits pin code required.',
  icon: 'info',
  confirmButtonText: 'OK'
})
					$("#pin").focus();
					$("#pin").select();
					$('#tnccheckbox').prop('checked', false);
					$('#submitbt').hide();
				}

            }
            else if($(this).is(":not(:checked)")){
                
            }
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="register-1.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="register-1.php?lang=bm">BM</a></li>
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
						<div class="label"><?php echo $lang['RegisteredUser']    ?></div>
					</div>
				</div>
				
				<div class="form-title">
					<?php echo $lang['AboutYou'] ?>
				</div>
				
				<form method="post" action="register-thankyou.php" id="frmRegister">
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['FullNameNRIC'] ?> </label>
						<input id="uname" type="text" name="uname" value="<?php echo $_SESSION['Tname'] ?>" disabled />
						
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['EmailAddress'] ?></label>
						<input id="email" value="<?php echo $_SESSION['Temail'] ?>" type="email"  name="email" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['NRIC'] ?></label>
						<input id="ic" type="text" value="<?php echo isset($_SESSION['TidNo']) ?? ''; ?>" name="ic"   oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="12" minlength="12" required placeholder="Example: 910101082022"  onkeypress="return isNumberKey(event);" />
						<div class="error" id="erric"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['ContactNo'] ?></label>
						<input id="contact" value="<?php echo $_SESSION['TtelHp'] ?>" type="text" name="contact" required disabled  />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['Address1'] ?></label>
						<input id="address1" value="<?php echo $_SESSION['ThomeAddress1']   ?>" type="text" name="address1" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['Address2'] ?></label>
						<input id="address2" value="" type="text" name="address2" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['City']  ?></label>
						<input id="city" value="" type="text" name="city" required />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['PostCode'] ?></label>
						<input id="postcode" value="<?php echo $_SESSION['ThomeZip']  ?>" name="postcode" required minlength="5" required oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="5" type="number" onkeypress='validate(event)' onkeydown="javascript: return event.keyCode == 69 ? false : true" />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['State'] ?></label>
						<select id="state" name="state" >
<!-- This is default define value using php variable $r -->
					<!-- Other options values -->
					<option selected value="<?php echo $_SESSION['ThomeState']  ?>"><?php echo $_SESSION['ThomeState']  ?></option>
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
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['BankName'] ?></label>
						<div class="dropdown-selector">
						<?php
                    echo '<select id="bank" name="bank" >
                    <option value="1">Select</option>';

                    $sqli ="SELECT * FROM mybank";
                    $query = mysqli_query($connection, $sqli);
					$rowCount = mysqli_num_rows($query);
                   
                    while ($row = mysqli_fetch_array($query)) {
					
                      echo '<option value="'.$row['bnk_id'].'">'.$row['bnk_name'].'</option>';
                    }

                    echo '</select>';
                   ?>
						</div>
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo  $lang['BankAccountnumber'] ?></label>
						<input id="accountnumber" type="text" name="accountnumber" placeholder="(Optional)"  />
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php  echo $lang['OccupationCategory'] ?></label>
						<div class="dropdown-selector">
						<?php
                    echo '<select id="occupation" name="occupation">
                    <option value="3">Select</option>';

                    $sqli ="SELECT * FROM myoccupationcategory where occ_status=1";
                    $query = mysqli_query($connection, $sqli);
                    while ($row = mysqli_fetch_array($query)) {
                      echo '<option value=" '.$row['occ_id'].' ">'.$row['occ_category'].'</option>';
                    }

                    echo '</select>';
                   ?>
						</div>
						<div class="error"></div>
					</div>
				</div>
				<div  class="form-row">
					<div id="subcategory" class="form-col">
						<label>Occupation</label>
						<div class="dropdown">
						<div id="response"></div>
						</div>
						<div class="error"></div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['CreatePin']  ?> </label>
						<input id="pin" type="number" style="-webkit-text-security:disc;" value=""  name="pin"  required oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" minlength="6" maxlength="6" type="number" onkeypress='validate(event)' onkeydown="javascript: return event.keyCode == 69 ? false : true"  />
						<div class="error">Create Security Pin (Please enter only 6 digit numeric)</div>
					</div>
				</div>
				<div class="form-row">
					<div class="form-col">
						<label><?php echo $lang['Campaign']  ?> (Optional) </label>
						<input id="campaign" value="" type="text" name="campaign" maxlength="12" />
						<div class="error"></div>
					</div>
				</div>
				
				<div class="form-row tnc">
					<div class="form-col center">
						<span class="checkbox">
							<input id="tnccheckbox" type="checkbox" name="" onchange="activateButton(this)"/>
							<span></span> 
						</span>
						<span class="label">I agree to the <a rel="noopener noreferrer" href="Tnc.php">Terms & Conditions</a></span>
					</div>
				</div>
				
				<div class="form-row submit">
					<div class="form-col">
						<input id="submitbt" type="submit" class="btn" value="Register" />
					</div>
				</div>
				</form>
			</div>
		
		</div>
		
	</div>
	<script>
		function isNumberKey(evt){
			var charCode = (evt.which) ? evt.which : evt.keyCode
			if (charCode > 31 && (charCode < 48 || charCode > 57))
				return false;
			return true;
		}

		// $('#submitbt').click(function(e){
		// 	e.preventDefault();
		// 	if($('#ic').val() == ""){
		// 		$('#erric').html('Please Fill in the NRIC');
		// 	}
		// 	elseif($('#ic').val().length != 12){
		// 		$('#erric').html('Please Fill in the NRIC in the correct format.');
		// 	}
		// 	else{
		// 		$('#erric').html('');
		// 	}
		// });
	</script>
</body>
</html>