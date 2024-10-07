<!doctype html>
<html lang="en-gb" dir="ltr">
<?php
 if(!isset($_SESSION)) {
	session_start();
}

// Set minlimit 
$minLimit = '25.00';
?>
<?php include('controllers/config/db.php'); ?>

<?php include('common.php');  ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
#customerbuycountdown  {
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
	#btnpayfpx{
		width: 100%;
	}
	#minbalancenotmeterror{
		text-align: center;
		color: red;
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
		width: 30%;
	}
	#btnpayfpx{
		width: 30%;
	}
	#minbalancenotmeterror{
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
	var js_variable  = '<?php echo $_SESSION['token'];?>';

	// Initialize price toggle
	// Default to 0
	// 0 = Weight is in use ( total runs )
	// 1 = Amount in use ( weight runs )
	var priceToggle = 0;
        $('#div_month').hide();
		// Init Ui
		$('#totaloutputtext').hide();
		$('#weightoutputtext').hide();
		$('#minbalancenotmeterror').hide();
		
        var par_code = '<?php echo $_SESSION['par_code']; ?>';
        var login = '<?php echo $_SESSION['login'];?>';
        var sockethost ='<?php echo $socket ?>';
      var host = sockethost + par_code +'&access_token=' + js_variable ;
	 
      var socket = new WebSocket(host);
        var newsellprice;
        var newbuyprice;
        var previousbuy;
        var previoussell;
        var uuid;
		var minLimit = 25; // RM 25

		// Hide reference
		document.getElementById("reference").style.display = "none";

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
				var total = customerbuy * weight;
				$("#reference").text("Reference Price = " + Number(total).toFixed(2));
		
			}else if($('#weight').prop('readonly')){
				// When weight is disabled
				// Calculate live weights
				var amount = $("#total").val();
				var totalweight = toFixed_norounding(amount/ customerbuy);
				var total = customerbuy * totalweight;
				$("#weight").val(Number(totalweight).toFixed(3));
				//$("#reference").text("Reference Price = " + Number(total).toFixed(2));
			}*/

			// Set toggle
			// 0 = Weight is in use
			// 1 = Amount in use
			if(priceToggle != 1){
				var weight = $("#weight").val();
				var total = customerbuy * weight;
				//$("#reference").text("Reference Price = " + Number(total).toFixed(2));
				$("#total").val(total.toFixed(2));
			}else{
				// When weight is disabled
				// Calculate live weights
				var amount = $("#total").val();
				var totalweight = toFixed_norounding(amount/ customerbuy);
				$("#weight").val(Number(totalweight));
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

					alert(response.message);

				}
			});

            previousbuy = newbuyprice;

            previoussell = newsellprice;

        };

// end Price stream
function toFixed_norounding(n)
{
    var result = n.toFixed(3);
    return result <= n ? result: (result - Math.pow(0.1,3)).toFixed(3);
}

// Set mode toggle
$('#weight').click(function () {
  //$(this).removeAttr('readonly');
  //$("#total").prop("readonly", true);
  priceToggle = 0;
  // calc
  var customerbuy  = '<?php echo $_SESSION['customerbuy'];?>';
  var weight = $(this).val();
  var total = customerbuy * weight;
  total = total.toFixed(2);
  $("#total").val(total);

  $('#totaloutputtext').show();
  $('#weightoutputtext').hide();
  if (total >= minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= false;
		document.getElementById("btnpaywallet").style.opacity= 1.0;
		document.getElementById('btnpayfpx').disabled= false;
		document.getElementById("btnpayfpx").style.opacity= 1.0;

		$('#minbalancenotmeterror').hide();
		
  }else if (total < minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').show();

  }else {
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').hide();
  }
});

$('#tnccheckbox').click(function () {
   

  var total = $('#total').val();

  var weight = $("#weight").val();

  if (total <= 0 ) {
	Swal.fire({
  title: 'Info!',
  text: '<?php echo $lang['msg_zero_value'];?>',
  icon: 'info',
  confirmButtonText: 'OK'
});
$('#total').focus();
$('#tnccheckbox').prop('checked', false);
  }else{
		document.getElementById('btnpaywallet').disabled= false;
		document.getElementById("btnpaywallet").style.opacity= 1.0;
		document.getElementById('btnpayfpx').disabled= false;
		document.getElementById("btnpayfpx").style.opacity= 1.0;
  }

});

$('#total').click(function () {
  //$(this).removeAttr('readonly');
  //$("#weight").prop
  
 ("readonly", true);
  // calc
  priceToggle = 1;
  var customerbuy  = '<?php echo $_SESSION['customerbuy'];?>';
  var total = $(this).val();
  var goldweight = total / customerbuy;
  var roundedweight = toFixed_norounding(goldweight);
  var newtotal = roundedweight * customerbuy;
  $("#weight").val(roundedweight);

  $('#weightoutputtext').show();
  $('#totaloutputtext').hide();
  
  if (total >= minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= false;
		document.getElementById("btnpaywallet").style.opacity= 1.0;
		document.getElementById('btnpayfpx').disabled= false;
		document.getElementById("btnpayfpx").style.opacity= 1.0;

		$('#minbalancenotmeterror').hide();
  }else if (total < minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').show();

  }else {
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').hide();
  }
});

// End Set toggle
$("#weight").keyup(function() {
	if($(this).val().indexOf('.') > -1){
					if($(this).val().substr($(this).val().indexOf('.')+1).length > 3){
						$(this).val($(this).val().substr(0 , $(this).val().indexOf('.')+4));
					}
				}
  var customerbuy  = '<?php echo $_SESSION['customerbuy'];?>';
  var weight = $(this).val();
  var total = customerbuy * weight;
  total = total.toFixed(2);
  $("#total").val(total);
  
  if (total >= minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= false;
		document.getElementById("btnpaywallet").style.opacity= 1.0;
		document.getElementById('btnpayfpx').disabled= false;
		document.getElementById("btnpayfpx").style.opacity= 1.0;

		$('#minbalancenotmeterror').hide();
  }else if (total < minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').show();

  }else {
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').hide();
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
var customerbuy  = '<?php echo $_SESSION['customerbuy'];?>';
  var total = $(this).val();
  var goldweight = total / customerbuy;
  var roundedweight = toFixed_norounding(goldweight);
  var newtotal = roundedweight * customerbuy;
  
  //$("#reference").val(newtotal);
  $("#weight").val(roundedweight);
  //$("#reference").text("Reference Price = " + Number(newtotal).toFixed(2));
  if (total >= minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= false;
		document.getElementById("btnpaywallet").style.opacity= 1.0;
		document.getElementById('btnpayfpx').disabled= false;
		document.getElementById("btnpayfpx").style.opacity= 1.0;

		$('#minbalancenotmeterror').hide();
  }else if (total < minLimit && document.getElementById('tnccheckbox').checked == true){
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').show();

  }else {
		document.getElementById('btnpaywallet').disabled= true;
		document.getElementById("btnpaywallet").style.opacity= .65;
		document.getElementById('btnpayfpx').disabled= true;
		document.getElementById("btnpayfpx").style.opacity= .65;

		$('#minbalancenotmeterror').hide();
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


document.getElementById("tnccheckbox").checked = false;

document.getElementById('btnpaywallet').disabled= true;
document.getElementById("btnpaywallet").style.opacity= .65;
document.getElementById('btnpayfpx').disabled= true;
document.getElementById("btnpayfpx").style.opacity= .65;

  var counter = 10;
 
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
var validate = function(e, l, type) {
  	var t = e.value;
  	e.value = (t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), l)) : t;

}
</script>

<script>
	function activateButton(element) {

		var total = $("#total").val();
		var minLimit = 25;
		if(element.checked && total >= minLimit) {
			// check if values are good
			document.getElementById('btnpaywallet').disabled= false;
			document.getElementById("btnpaywallet").style.opacity= 1.0;
			document.getElementById('btnpayfpx').disabled= false;
			document.getElementById("btnpayfpx").style.opacity= 1.0;

			$('#minbalancenotmeterror').hide();
		}
		else if (total < minLimit && document.getElementById('tnccheckbox').checked == true){
			document.getElementById('btnpaywallet').disabled= true;
			document.getElementById("btnpaywallet").style.opacity= .65;
			document.getElementById('btnpayfpx').disabled= true;
			document.getElementById("btnpayfpx").style.opacity= .65;

			$('#minbalancenotmeterror').show();

		}
		else  {
			document.getElementById('btnpaywallet').disabled= true;
			document.getElementById("btnpaywallet").style.opacity= .65;
			document.getElementById('btnpayfpx').disabled= true;
			document.getElementById("btnpayfpx").style.opacity= .65;

			$('#minbalancenotmeterror').hide();
		}

	}
</script>

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['BUY_GOLD_TITLE'] ?></title>
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
<script src="js/tooltip-script.js"></script>
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
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="dashboard-buy-gold.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="dashboard-buy-gold.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['BuyInsideTitle']; ?></div>
		<!--<a class="back" href="index.php">Back</a>-->
		<a class="back" href="index.php"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="main-widget">
				<div class="balance-row">
					<label><?php echo $lang['GOLDBALANCE']; ?></label>
					<div class="col right">
						<div class="unit"><abbr title="The gold remaining in your account in grams." rel="tooltip"><?php echo $lang['gram']; ?></abbr></div>
						<div class="num"><?php echo $_SESSION['goldbalance'];?></div>
						
					</div>
				</div>
				<div class="buy-sell-row">
					<div class="col">
						<div class="action-title"><?php echo $lang['CustomerSell']; ?></div>
							<div class="current-value ">
								<div class="icon"></div>
								<div class="num"><p id="rootsell"></p></div>
								
							</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
					</div>
					<div class="col selected">
						<div class="action-title"><?php echo $lang['CustomerBuy']; ?></div>
							<div class="current-value ">
								<div class="icon"></div>
								<div class="num"><p id="rootbuy"> </p></div>
								
							</div>
						<div class="unit"><?php echo $lang['pergram']; ?></div>
					</div>
				</div>
			</div>
			
			<div class="page-content-form">
				<form method="post" action="dashboard-buy-gold-confirm.php" onsubmit="">
				<div class="form-title center">
					<?php echo $lang['BuyCenterTitle']; ?>
				</div>
				
				<div class="buy-sell-row">
					<div class="col">
						<div class="form-row">
							<div class="form-col limit">
								<label><?php echo $lang['BuySellWeightLabel']; ?></label><br/>
									<label style="color:transparent">break   </label>
									<!-- <input id="weight" min="0" name="weight" type="number" step="any" oninput="validate(this, 4, 'weight')" class="opp" placeholder="0.00"  style="text-align: center;">		 -->
									<input id="weight" min="0" name="weight" type="number" step="any" class="opp" placeholder="0.00"  style="text-align: center;">
									<!--ignore class error, just borrowing the font style-->
									<div id= "weightoutputtext" class="error"><?php echo $lang['BuySellWeightUndertext']; ?></div>
									
							</div>
						</div>
					</div>
					<div class="col">
						<div class="form-row">
							<div class="form-col limit">
								<label><?php echo $lang['BuySellTotalLabel']; ?></label><br/>
								<label style="color:transparent">break   </label>
								<label style="color:transparent;" id="reference">Reference</label>
									<!-- <input id="total"  min="25" type="number" step="any" oninput="validate(this, 3, 'total')" name="total" type="number" class="opp" placeholder="0.00"  style="text-align: center;"> -->
									<input id="total"  min="25" type="number" step="any" name="total" type="number" class="opp" placeholder="0.00"  style="text-align: center;">
									<!--ignore class error, just borrowing the font style-->
									<div id= "totaloutputtext" class="error"><?php echo $lang['BuySellTotalUndertext']; ?></div>
						
									<!--<div class="error">Please enter numbers</div>-->
									
							</div>
						</div>
					</div>
				</div>
				
				<div class="form-row tnc">
					<div class="form-col center">
						<span class="checkbox"><input id="tnccheckbox" type="checkbox" checked onchange="activateButton(this)"/><span></span></span>
						<span class="label"><?php echo $lang['I_AGREE']; ?> <a href="Tnc.php"  ><?php echo $lang['TERMS_AND_CONDITIONS']; ?></a></span>
					</div>
				</div>

				<div id="minbalancenotmeterror" class="error"><?php echo $lang['MIN_BALANCE_NOT_MET'].' '.$minLimit; ?></div>
				
				<div class="form-row submit">
					<div class="form-col gap">
						<input type="hidden" value="spot_acesell" name="type">
						<!--<input id="buybutton" type="submit" name="submit_form" value="<php echo $lang['SUBMIT']; ?>" class="btn">-->
						<input id="btnpaywallet" type="submit" name="wallet" formaction="dashboard-buy-gold-confirm-wallet.php" class="btn" value="<?php echo $lang['PAYBYWALLET']; ?>" />		
						<input id="btnpayfpx"  type="submit" name="fpx" formaction="dashboard-buy-gold-confirm-fpx.php" class="btn" value="<?php echo $lang['PAYBYFPX']; ?>" />		
					
						</input>
					</div>
				</div>
				</form>
			</div>
		
		</div>
		
	</div>
	
</body>
</html>