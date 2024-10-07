<!doctype html>
<html lang="en-gb" dir="ltr">
<?php include('common.php');  ?>
<?php include('controllers/config/db.php'); ?>
<?php
 if(!isset($_SESSION)) {
	session_set_cookie_params(0);
	session_start();
}

// Required string will be used to check whether it comes from expired
$url = strtok($_SERVER['HTTP_REFERER'], '?');
$required_string = substr(strrchr($url, '/'), 1);

$remainingGold = $_SESSION['available_balance'];

if (strcasecmp('dashboard-sell-gold-confirm-bank.php', $required_string) == 0 || strcasecmp('dashboard-sell-gold-confirm-wallet.php', $required_string) == 0){
	
	// Set value for amount if coming from expired price
	$weight = $_SESSION['weight'];
	$amount = $_SESSION['amount'];
}else{
	// Set value for amount
	$weight = "0.000";
	$amount = "0.00";
}

?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<style>
#customersellcountdown  {
  margin: auto;
  /*border-color: #1D976C;
  background-color: #93F9B9;*/
  border-color: #d3a817;
  background-color: #ffc80a;
}

/*
input[readonly]{
	background-color: #F5F5F5 ;
}*/

.form-row textarea, 
.form-row input[type=text], 
.form-row input[type=email], 
.form-row input[type=password], 
.form-row input[type=number]:focus{
	border: 1px solid #53C4CC ;
}

#weightoutputtext{
	color: #111111 ;
}
#totaloutputtext{
	color: #111111 ;
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
	#btnpaybank{
		width: 100%;
	}
	#minbalanceerror{
		text-align: center;
		color: red;
	}
	
}
@media only screen and (min-width: 768px) {
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
		width: 68%;
	}
	#btnpaybank{
		width: 68%;
	}
	#minbalanceerror{
		text-align: center;
		color: red;
	}
}

@media only screen and (min-width: 769px) {
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
		width: 35%;
	}
	#btnpaybank{
		width: 35%;
	}
	#minbalanceerror{
		text-align: center;
		color: red;
	}
}


</style>

<!-- Ported Scripts -->
<script type="text/javascript">
 var customerbuy  = '<?php echo $_SESSION['customerbuy'];?>';
  var customersell  = '<?php echo $_SESSION['customersell'];?>';

$(document).ready(function(){

    $(this).scrollTop(0);

	var goldBalance = '<?php echo $_SESSION['available_balance'];?>';
	var TkycInd  = '1';
	var js_variable  = '<?php echo $_SESSION['token'];?>';

	
	var priceToggle = 0;
        $('#div_month').hide();
		// Init Ui
		$('#totaloutputtext').hide();
		$('#weightoutputtext').hide();
		$('#minbalanceerror').hide();
		
		// Initialize amount and weight
		document.getElementById("weight").value = '<?php echo $weight;?>';
		document.getElementById("total").value = '<?php echo $amount;?>';

		// Hide prompt popup
		

        var par_code = '<?php echo $_SESSION['par_code']; ?>';
        var login = '<?php echo $_SESSION['login'];?>';
		var sockethost ='<?php echo $socket ?>';
        var host = sockethost + par_code +'&access_token=' + js_variable ;
		
		//var host = 'wss://gopayzprod.ace2u.com/mygtp.php?version=1.0my&action=pricestream&merchant_id='+ par_code +'&access_token=' + js_variable ;
        var socket = new WebSocket(host);
        var newsellprice;
        var newbuyprice;
        var previousbuy;
        var previoussell;
        var uuid;

		socket.onmessage = function(e) {
            // console.log(e.data);
            var myObj = JSON.parse(e.data);
            var xx = new Date();
            //customerbuy = myObj.data[0].companysell;
            //customersell = myObj.data[0].companybuy;
			customerbuy = Number(myObj.data[0].companysell).toFixed(2);
            customersell = Number(myObj.data[0].companybuy).toFixed(2);
            uuid =  myObj.data[0].uuid;

			newbuyprice = customerbuy;
			newsellprice = customersell;
			// Check field to populate
			// Default setting, amount is disabled
			/*
			if($('#total').prop('readonly')){
				var weight = $("#weight").val();
				var total = customersell * weight;
				$("#reference").text("Reference Price = " + Number(total).toFixed(2));
		
			}else if($('#weight').prop('readonly')){
				// When weight is disabled
				// Calculate live weights
				var amount = $("#total").val();
				var totalweight = toFixed_norounding(amount/ customersell);
				$("#weight").val(Number(totalweight).toFixed(3));
			}	*/

			// Set toggle
			// 0 = Weight is in use
			// 1 = Amount in use
			if(priceToggle != 1){
				var weight = $("#weight").val();
				var total = customersell * weight;
				//$("#reference").text("Reference Price = " + Number(total).toFixed(2));
				$("#total").val(total.toFixed(2));
			}else{
				// When weight is disabled
				// Calculate live weights
				var amount = $("#total").val();
				var totalweight = toFixed_norounding(amount/ customersell);
				$("#weight").val(Number(totalweight));

				// Extra check in case price hit
				remainderGold = goldBalance - $("#weight").val();
				if (remainderGold <= 0){
					document.getElementById('btnpaywallet').disabled= true;
					document.getElementById("btnpaywallet").style.opacity= .65;
					document.getElementById('btnpaybank').disabled= true;
					document.getElementById("btnpaybank").style.opacity= .65;
				}
			}
			
        //rootarrow
        
            if(previousbuy > newbuyprice )
            {
            document.getElementById("rootbuy").style.color = "red";
            document.getElementById('rootbuy').innerHTML ="&#8595; " + customerbuy ;
           
            }else{ 
                document.getElementById("rootbuy").style.color = "green";
            document.getElementById('rootbuy').innerHTML = "&#8593; " + customerbuy;

            }
            if(previoussell > newsellprice )
            {
            document.getElementById("rootsell").style.color = "red";
            document.getElementById('rootsell').innerHTML = "&#8595; " + customersell;
            }else{
                document.getElementById("rootsell").style.color = "green";
            document.getElementById('rootsell').innerHTML = "&#8593; " + customersell;

            }
           
            $.ajax({
                
			type: "POST",
			url: "captureid.php",
			data: {uuid: uuid, customersell: customersell, customerbuy:  customerbuy},
			dataType: 'json',
			cache: false,
			success: function(response) {

					//alert(response.message);

				}
			});

            previousbuy = newbuyprice;

            previoussell = newsellprice;

        };
		// end price stream

	

	// end Price stream
function toFixed_norounding(n)
{
    var result = n.toFixed(3);
    return result <= n ? result: (result - Math.pow(0.1,3)).toFixed(3);
}

  $("#reflesh").click(function(){
    //$("#buybutton").show();
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

  $('#tnccheckbox').click(function () {
   

   var total = $('#total').val();
 
   var weight = $("#weight").val();
 
   if (total <= 0 ) {
	 Swal.fire({
		title: 'Info!',
		text: '0 value not allow',
		icon: 'info',
		confirmButtonText: 'OK'
	});
		$('#total').focus();
		$('#tnccheckbox').prop('checked', false);
 	
   }else{

		if(document.getElementById('tnccheckbox').checked == true){
			document.getElementById('btnpaywallet').disabled= false;
			document.getElementById("btnpaywallet").style.opacity= 1.0;
			document.getElementById('btnpaybank').disabled= false;
			document.getElementById("btnpaybank").style.opacity= 1.0;
		}else{
		
			document.getElementById('btnpaywallet').disabled= true;
			document.getElementById("btnpaywallet").style.opacity= .65;
			document.getElementById('btnpaybank').disabled= true;
			document.getElementById("btnpaybank").style.opacity= .65;
		}
		
   }
 
 });

// Set mode toggle
$('#weight').click(function () {
  //$(this).removeAttr('readonly');
  //$("#total").prop("readonly", true);
  priceToggle = 0;
  // calc
  var customersell  = '<?php echo $_SESSION['customersell'];?>';
  var weight = $(this).val();
  var total = customersell * weight;
  total = total.toFixed(2);
  $("#total").val(total);

  $('#totaloutputtext').show();
  $('#weightoutputtext').hide();
  // Check gold balance
  	remainderGold = goldBalance - weight;
  	if (remainderGold >= 0){
		  // Check if balance sufficient
		if (total >= 5 && total <= amountLimit && document.getElementById('tnccheckbox').checked == true){

			// amountLimit is 3k for basic and 5k for premium
			// Check if user is premium or basic
			
				// Premium user
				// Enable bank and wallet
				document.getElementById('btnpaywallet').disabled= false;
				document.getElementById("btnpaywallet").style.opacity= 1.0;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			
		}else if (total > amountLimit && document.getElementById('tnccheckbox').checked == true) {

			// When total exceeds limit
			// Check if user is premium or basic
			
				// Premium user
				// Enable bank 
				// Disable wallet
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			

		}else {
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				// document.getElementById("upgrade-prompt").style.display = "none";

				$('#minbalanceerror').hide();
		}
 	}else {
		 		// Disable all
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				$('#minbalanceerror').show();
				// document.getElementById("upgrade-prompt").style.display = "none";
	}
  
});

$('#total').click(function () {
  //$(this).removeAttr('readonly');
  //$("#weight").prop("readonly", 'readonly');
	priceToggle = 1;


  // calc
  var customersell  = '<?php echo $_SESSION['customersell'];?>';
  var total = $(this).val();
  var goldweight = total / customersell;
  var roundedweight = toFixed_norounding(goldweight);
  var newtotal = roundedweight * customersell;
  $("#weight").val(roundedweight);

  $('#weightoutputtext').show();
  $('#totaloutputtext').hide();
  
  	remainderGold = goldBalance - $("#weight").val();
  	if (remainderGold >= 0){
		// Check if balance sufficient
		if (total >= 5 && total <= amountLimit && document.getElementById('tnccheckbox').checked == true){

			// amountLimit is 3k for basic and 5k for premium
			// Check if user is premium or basic
		
				// Premium user
				// Enable bank and wallet
				document.getElementById('btnpaywallet').disabled= false;
				document.getElementById("btnpaywallet").style.opacity= 1.0;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			

		}else if (total > amountLimit && document.getElementById('tnccheckbox').checked == true) {

			// When total exceeds limit
			// Check if user is premium or basic
		
				// Premium user
				// Enable bank 
				// Disable wallet
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			

		}else {
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				// document.getElementById("upgrade-prompt").style.display = "none";

				$('#minbalanceerror').hide();
		}
	}else{
				// Disable all
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				// document.getElementById("upgrade-prompt").style.display = "none";

				$('#minbalanceerror').show();
	}
  
});

// End Set toggle
$("#weight").keyup(function() {
	if($(this).val().indexOf('.') > -1){
					if($(this).val().substr($(this).val().indexOf('.')+1).length > 3){
						$(this).val($(this).val().substr(0 , $(this).val().indexOf('.')+4));
					}
				}
  var customersell  = '<?php echo $_SESSION['customersell'];?>';
  var weight = $(this).val();
  var total = customersell * weight;
  total = total.toFixed(2);
  $("#total").val(total);
  
  	remainderGold = goldBalance - weight;
  	if (remainderGold >= 0){
		if (total >= 5 && total <= amountLimit && document.getElementById('tnccheckbox').checked == true){

			// amountLimit is 3k for basic and 5k for premium
			// Check if user is premium or basic
		
				// Premium user
				// Enable bank and wallet
				document.getElementById('btnpaywallet').disabled= false;
				document.getElementById("btnpaywallet").style.opacity= 1.0;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			

		}else if (total > amountLimit && document.getElementById('tnccheckbox').checked == true) {

			// When total exceeds limit
			// Check if user is premium or basic
			
				// Premium user
				// Enable bank 
				// Disable wallet
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			

		}else {
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				// document.getElementById("upgrade-prompt").style.display = "none";

				$('#minbalanceerror').hide();
		}
	}else{
				// block all and show balance message
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				// document.getElementById("upgrade-prompt").style.display = "none";

				$('#minbalanceerror').show();
	}

  
 // alert(total); 
});

$("#weight").keydown(function (event) {
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

//total
$("#total").keyup(function() {
	if($(this).val().indexOf('.') > -1){
					if($(this).val().substr($(this).val().indexOf('.')+1).length > 2){
						$(this).val($(this).val().substr(0 , $(this).val().indexOf('.')+3));
					}
				}
  
var customersell  = '<?php echo $_SESSION['customersell'];?>';
  var total = $(this).val();
  var goldweight = total / customersell;
  var roundedweight = toFixed_norounding(goldweight);
  var newtotal = roundedweight * customersell;
  
  //$("#total").val(newtotal);
  $("#weight").val(roundedweight);

  	remainderGold = goldBalance - $("#weight").val();
  	if (remainderGold >= 0){
		if (total >= 5 && total <= amountLimit && document.getElementById('tnccheckbox').checked == true){

			// amountLimit is 3k for basic and 5k for premium
			// Check if user is premium or basic
			
				// Premium user
				// Enable bank and wallet
				document.getElementById('btnpaywallet').disabled= false;
				document.getElementById("btnpaywallet").style.opacity= 1.0;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			

		}else if (total > amountLimit && document.getElementById('tnccheckbox').checked == true) {

			// When total exceeds limit
			// Check if user is premium or basic
		
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= false;
				document.getElementById("btnpaybank").style.opacity= 1.0;

				$('#minbalanceerror').hide();

			

		}else {
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				// document.getElementById("upgrade-prompt").style.display = "none";

				$('#minbalanceerror').hide();
		}
	}else{
			// Disable
			document.getElementById('btnpaywallet').disabled= true;
			document.getElementById("btnpaywallet").style.opacity= .65;
			document.getElementById('btnpaybank').disabled= true;
			document.getElementById("btnpaybank").style.opacity= .65;

			// document.getElementById("upgrade-prompt").style.display = "none";

			$('#minbalanceerror').show();
	}
  
 // alert(total); 
});

$("#total").keydown(function (event) {
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
var srcEvent = null;

$("input[type=text],input[type=number]")

    .mousedown(function (event) {
        srcEvent = event;
    })
    .mouseup(function (event) {
        var delta = Math.abs(event.clientX - srcEvent.clientX) 
                  + Math.abs(event.clientY - srcEvent.clientY);
        
        var threshold = 2;
        if (delta <= threshold) {
            try {
                // ios likes this but windows-chrome does not on number fields
                $(this)[0].selectionStart = 0;
                $(this)[0].selectionEnd = 1000;
            } catch (e) {
                // windows-chrome likes this
                $(this).select();
            }
        }
    });

});



window.onload = function(){

(function(){

/*
if(document.getElementById('total').placeholder = 'Click here to sell by AMOUNT'){
	document.getElementById('buybutton').disabled= true;
	document.getElementById("buybutton").style.opacity= .65;
}*/
document.getElementById("tnccheckbox").checked = false;

document.getElementById('btnpaywallet').disabled= true;
document.getElementById("btnpaywallet").style.opacity= .65;
document.getElementById('btnpaybank').disabled= true;
document.getElementById("btnpaybank").style.opacity= .65;

  $("#reflesh").hide();
 
 
 document.getElementById('rootbuy').innerHTML = customerbuy ;
 
 document.getElementById('rootsell').innerHTML = customersell ;
 

})();

}




</script>

<script>
// function scripts
function toTrunc(value,n){
    x=(value.toString()+".0").split(".");
    return parseFloat(x[0]+"."+x[1].substr(0,n));
}

// validate input length
// (this, length)
var validate = function(e, l) {
  var t = e.value;
  e.value = (t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), l)) : t;
}
</script>

<script>
			amountLimit = 5000;

	function activateButton(element) {

		var total = $("#total").val();

		// Set to 1 for testing
		// var TkycInd  = '<?php // echo $_SESSION['TkycInd'];?>';
		var TkycInd  = '1';
	
			amountLimit = 5000;
			document.getElementById('btnpromptbank').type = "hidden";

	
		var goldBalance  = '<?php echo $_SESSION['available_balance'];?>';
		// Start remainder gold check
		remainderGold = goldBalance - $("#weight").val();
  		if (remainderGold >= 0){
			if (total >= 5 && total <= amountLimit && element.checked){

				// amountLimit is 3k for basic and 5k for premium
				// Check if user is premium or basic
				
					// Premium user
					// Enable bank and wallet
					document.getElementById('btnpaywallet').disabled= false;
					document.getElementById("btnpaywallet").style.opacity= 1.0;
					document.getElementById('btnpaybank').disabled= false;
					document.getElementById("btnpaybank").style.opacity= 1.0;

					$('#minbalanceerror').hide();

				

			}else if (total > amountLimit && element.checked) {

				// When total exceeds limit
				// Check if user is premium or basic
			
					// Premium user
					// Enable bank 
					// Disable wallet
					document.getElementById('btnpaywallet').disabled= true;
					document.getElementById("btnpaywallet").style.opacity= .65;
					document.getElementById('btnpaybank').disabled= false;
					document.getElementById("btnpaybank").style.opacity= 1.0;

					$('#minbalanceerror').hide();

				

			}else {
				document.getElementById('btnpaywallet').disabled= true;
				document.getElementById("btnpaywallet").style.opacity= .65;
				document.getElementById('btnpaybank').disabled= true;
				document.getElementById("btnpaybank").style.opacity= .65;

				// document.getElementById("upgrade-prompt").style.display = "none";

				$('#minbalanceerror').hide();
			}
		}else{
			// End
			document.getElementById('btnpaywallet').disabled= true;
			document.getElementById("btnpaywallet").style.opacity= .65;
			document.getElementById('btnpaybank').disabled= true;
			document.getElementById("btnpaybank").style.opacity= .65;

			// document.getElementById("upgrade-prompt").style.display = "none";

			$('#minbalanceerror').show();
		}
		// End Remainder Gold Check
		

	}
	function openPrompt() {
		//alert("You are not eligible to access this function. Please upgrade your wallet.");
		Swal.fire({
  title: 'Sorry!',
  text: 'You are not eligible to access this function. Please upgrade your wallet.',
  icon: 'info',
  confirmButtonText: 'OK'
});
		//document.getElementById("myForm").style.display = "block";
	}

</script>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['SELL_GOLD_TITLE'] ?></title>
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="dashboard-sell-gold.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="dashboard-sell-gold.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['SellInsideTitle']; ?></div>
		<!--<a class="back" href="index.php">Back</a>-->
		<a class="back" href="index.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="main-widget">
				<div class="balance-row">
					<label><?php echo $lang['GOLDBALANCE']; ?></label>
					<div class="col current right">
						<div class="num"><?php echo $_SESSION['goldbalance'];?></div>
						<div class="unit"><?php echo $lang['gram']; ?></div>
					</div>
				</div>
				<div class="buy-sell-row">
					<div class="col selected">
						<div class="action-title"><?php echo $lang['CustomerSell']; ?></div>
						<div class="current-value">
							<div class="icon"></div>
							<div class="num"><p id="rootsell"></p></div>
						</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
					</div>
					<div class="col">
						<div class="action-title"><?php echo $lang['CustomerBuy']; ?></div>
						<div class="current-value">
							<div class="icon"></div>
							<div class="num"><p id="rootbuy"> </p></div>
						</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
					</div>
				</div>
			</div>
			
			<div class="page-content-form">
			<form method="post" action="" onsubmit="return validate();">
				<div class="form-title center">
					<?php echo $lang['SellCenterTitle']; ?>
				</div>
				
				<div class="buy-sell-row">
					<div class="col">
						<div class="form-row">
							<div class="form-col limit">
								<label><?php echo $lang['BuySellWeightLabel']; ?></label>
								<!-- <input id="weight" min="0" name="weight" type="number" step="any" oninput="validate(this, 4)" class="opp" placeholder="0.000"  style="text-align: center;">		 -->
								<input id="weight" min="0" name="weight" type="number" step="any" class="opp" placeholder="0.000"  style="text-align: center;">		
								<!--ignore class error, just borrowing the font style-->
								<div id= "weightoutputtext" class="error"><?php echo $lang['BuySellWeightUndertext']; ?></div>
							</div>
						</div>
					</div>
					<div class="col">
						<div class="form-row">
							<div class="form-col limit">
								<label><?php echo $lang['BuySellTotalLabel']; ?></label>
								<!-- <input id="total"  min="10" type="number" step="any" oninput="validate(this, 3)" name="total" type="number" class="opp" placeholder="0"  style="text-align: center;"> -->
								<input id="total"  min="10" type="number" step="any" name="total" type="number" class="opp" placeholder="0"  style="text-align: center;">
									<!--ignore class error, just borrowing the font style-->
								<div id= "totaloutputtext" class="error"><?php echo $lang['BuySellTotalUndertext']; ?></div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="form-row tnc">
					<div class="form-col center">
						<span class="checkbox"><input id="tnccheckbox" type="checkbox" /><span></span></span>
						<span class="label"><?php echo $lang['I_AGREE']; ?> <a href="Tnc.php"  ><?php echo $lang['TERMS_AND_CONDITIONS']; ?></a></span>
					</div>
				</div>
				<div id="minbalanceerror" class="error"><?php echo $lang['MIN_BALANCE_EXCEEDED'].' '.$remainingGold; ?></div>
					
				<div class="form-row submit">
					<div class="form-col gap">
						<input type="hidden" value="spot_acebuy" name="type" />		
								<!--<input id="buybutton" type="submit" name="submit_form" value="<php echo $lang['SUBMIT']; ?>" class="btn">-->
								<input id="btnpaywallet"  type="submit" name="wallet" formaction="dashboard-sell-gold-confirm-wallet.php" class="btn" value="<?php echo $lang['SELLBYWALLET']; ?>" />		
								<input id="btnpaybank"  type="submit" name="bank_account" formaction="dashboard-sell-gold-confirm-bank.php" class="btn" value="<?php echo $lang['SELLBYBANK']; ?>" />	
								<button id="reflesh" class="btn"><?php echo $lang['REFRESH']; ?></button>
					</div>
				</div>
				</form>
			</div>
		
		</div>
		
	</div>
	
</body>
</html>