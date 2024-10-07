<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

	if ($_SESSION['login'] != "success"  ) {
		header("Location: index.php");
		}
	$remainingGold = $_SESSION['available_balance'];
	$_SESSION['customerbuy'] = 240;
	$_SESSION['customersell'] = 240;
?>
<?php include('controllers/login.php');  ?>
<?php include_once('common.php');  ?>
<?php include_once('controllers/transfercontacthistory.php');  ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $lang['TITLE_TRANSFER_DETAILS'];?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/style-extra.css">
<link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script>

function startTimer(duration, display) {
	var timer = duration, minutes, seconds;
	myTimer = setInterval(function () {
		minutes = parseInt(timer / 60, 10);
		seconds = parseInt(timer % 60, 10);

		minutes = minutes < 10 ? "0" + minutes : minutes;
		seconds = seconds < 10 ? "0" + seconds : seconds;

		display.textContent = minutes + ":" + seconds;
		
		if (--timer < 0) {
		
			document.getElementById('midprice').innerHTML = midprice;
			$("#basemidprice").val(midprice);
			midpricefreeze = midprice;
			// Set toggle
			// 0 = Weight is in use
			// 1 = Amount in use
			if(priceToggle != 1){
				var weight = $("#weight").val();
				var total = midpricefreeze * weight;
				//$("#reference").text("Reference Price = " + Number(total).toFixed(2));
				$("#total").val(total.toFixed(2));
			}else{
				// When weight is disabled
				// Calculate live weights
				var amount = $("#total").val();
				var totalweight = toFixed_norounding(amount/ midpricefreeze);
				$("#weight").val(Number(totalweight));
				// Extra check in case price hit
				remainderGold = goldBalance - $("#weight").val();
				if (remainderGold <= 0.001){
					document.getElementById('btntransfer').disabled= true;
					document.getElementById("btntransfer").style.opacity= .65;
				}
			}
			// Do price refresh
			timer = duration;
		}
	}, 1000);
	
	return myTimer;
}

function toFixed_norounding(n)
{
    var result = n.toFixed(3);
    return result <= n ? result: (result - Math.pow(0.1,3)).toFixed(3);
}

function closeWindow(){

	// Slide popup trigger for confirmation form
	if ($('.slider').hasClass('close')){
		// Clicked when hidden
		// Do nothing

	
	}else{
		// Clicked when open. 
		// close slider
		$('.slider').toggleClass('close');
	}
		

}

$( document ).ready(function() {
	$('select').niceSelect();

	$('#orderstatus').next().show();
  $('#conversionstatus').next().hide();
  $("#pinerror").hide();
  $('.loader').hide();
  // Inits
  	goldBalance = '<?php echo $_SESSION['available_balance'];?>';
	var js_variable  = '<?php echo $_SESSION['token'];?>';
	priceToggle = 0;

  var par_code = '<?php echo $_SESSION['par_code']; ?>';
    var login = '<?php echo $_SESSION['login'];?>';
	var sockethost ='<?php echo $socket ?>';
    var host = sockethost + par_code +'&access_token=' + js_variable ;

	var socket = new WebSocket(host);
    var newsellprice;
    var newbuyprice;
	var previousbuy;
    var previoussell;
	
	//Init buttons
	document.getElementById('btnconfirmtransfer').disabled= true;
	document.getElementById("btnconfirmtransfer").style.opacity= .65;

	//Init timer 
	display = document.getElementById('refreshtimer') ;

	// 3 min timer
	myTimer = startTimer(180, display);

	// Init dots
	var dots = window.setInterval( function() {
	var wait = document.getElementById("wait");
	if ( wait.innerHTML.length > 3 ) 
		wait.innerHTML = "";
	else 
		wait.innerHTML += ".";
	}, 100);

	// Init blank remark display on slider popup
	$('#remarksdisplay').text('-');
	
  // Socket on message
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

        //rootarrow
        // Mid Price
		midprice = Number(customerbuy) + Number(customersell);

		midprice= Number(midprice / 2).toFixed(2);
	
		// Init midprice if empty
		if (document.getElementById('midprice').innerHTML == ""){
			document.getElementById('midprice').innerHTML = midprice;
			$("#basemidprice").val(midprice);
			midpricefreeze = midprice;
		}

		// Set toggle
		// 0 = Weight is in use
		// 1 = Amount in use
		if(priceToggle != 1){
			var weight = $("#weight").val();
			var total = midpricefreeze * weight;
			//$("#reference").text("Reference Price = " + Number(total).toFixed(2));
			$("#total").val(total.toFixed(2));
		}else{
			// When weight is disabled
			// Calculate live weights
			var amount = $("#total").val();
			var totalweight = toFixed_norounding(amount/ midpricefreeze);
			$("#weight").val(Number(totalweight));
			// Extra check in case price hit
			remainderGold = goldBalance - $("#weight").val();
			if (remainderGold <= 0.001){
				document.getElementById('btntransfer').disabled= true;
				document.getElementById("btntransfer").style.opacity= .65;
			}
		}
			

		// Add click function for refresh
		$('.refresh-button').on('click', function() {
			
			// Reset timer 
			clearInterval(myTimer);
			startTimer(180, display);

			if(previousbuy > newbuyprice )
			{
				document.getElementById("midprice").style.color = "red";
				document.getElementById('midprice').innerHTML = midprice;
				$("#basemidprice").val(midprice);
			
			}else{ 
				document.getElementById("midprice").style.color = "green";
				document.getElementById('midprice').innerHTML = midprice;
				$("#basemidprice").val(midprice);

			}
			if(previoussell > newsellprice )
			{
				document.getElementById("midprice").style.color = "red";
				document.getElementById('midprice').innerHTML = midprice;
				$("#basemidprice").val(midprice);
			}else{
				document.getElementById("midprice").style.color = "green";
				document.getElementById('midprice').innerHTML = midprice;
				$("#basemidprice").val(midprice);

			}

			midpricefreeze = midprice;
			// Set toggle
			// 0 = Weight is in use
			// 1 = Amount in use
			if(priceToggle != 1){
				var weight = $("#weight").val();
				var total = midpricefreeze * weight;
				//$("#reference").text("Reference Price = " + Number(total).toFixed(2));
				$("#total").val(total.toFixed(2));
			}else{
				// When weight is disabled
				// Calculate live weights
				var amount = $("#total").val();
				var totalweight = toFixed_norounding(amount/ midpricefreeze);
				$("#weight").val(Number(totalweight));
				// Extra check in case price hit
				remainderGold = goldBalance - $("#weight").val();
				if (remainderGold <= 0.001){
					document.getElementById('btntransfer').disabled= true;
					document.getElementById("btntransfer").style.opacity= .65;
				}
			}
		});

		// Set mode toggle
		$('#weight').click(function () {
			priceToggle = 0;
			// calc
		
			var weight = $(this).val();
			var total = midpricefreeze * weight;
			total = total.toFixed(2);
			$("#total").val(total);

			if (total >= 5){
					document.getElementById('btnconfirmtransfer').disabled= false;
					document.getElementById("btnconfirmtransfer").style.opacity= 1.0;
			}else {
					document.getElementById('btnconfirmtransfer').disabled= true;
					document.getElementById("btnconfirmtransfer").style.opacity= .65;
			}
		});
		
		$('#total').click(function () {
			//$(this).removeAttr('readonly');
			//$("#weight").prop("readonly", true);
			// calc
			priceToggle = 1;

			var total = $(this).val();
			var goldweight = total / midpricefreeze;
			var roundedweight = toFixed_norounding(goldweight);
			var newtotal = roundedweight * midpricefreeze;
			$("#weight").val(roundedweight);
			
			if (total >= 5 ){
					document.getElementById('btnconfirmtransfer').disabled= false;
					document.getElementById("btnconfirmtransfer").style.opacity= 1.0;
			}else {
					document.getElementById('btnconfirmtransfer').disabled= true;
					document.getElementById("btnconfirmtransfer").style.opacity= .65;
			}
		});

		// End Set toggle
		$("#weight").keyup(function() {
		
			var weight = $(this).val();
			var total = midpricefreeze * weight;
			total = total.toFixed(2);
			$("#total").val(total);
			
			if (total >= 5 ){
					document.getElementById('btnconfirmtransfer').disabled= false;
					document.getElementById("btnconfirmtransfer").style.opacity= 1.0;
			}else {
					document.getElementById('btnconfirmtransfer').disabled= true;
					document.getElementById("btnconfirmtransfer").style.opacity= .65;
			}
			// alert(total); 
		});

		//total
		$("#total").keyup(function() {
		
			var total = $(this).val();
			
			var goldweight = total / midpricefreeze;
			var roundedweight = toFixed_norounding(goldweight);
			var newtotal = roundedweight * midpricefreeze;
			
			//$("#reference").val(newtotal);
			$("#weight").val(roundedweight);
			//$("#reference").text("Reference Price = " + Number(newtotal).toFixed(2));
			if (total >= 5 ){
					document.getElementById('btnconfirmtransfer').disabled= false;
					document.getElementById("btnconfirmtransfer").style.opacity= 1.0;
			}else {
				
					document.getElementById('btnconfirmtransfer').disabled= true;
					document.getElementById("btnconfirmtransfer").style.opacity= .65;
			}
			// alert(total); 
		});

		// Slide popup trigger for confirmation form
		$('#btnconfirmtransfer').click(function() {
			if ($('.slider').hasClass('close')){
				// Clicked when hidden
				// Set slider values
				document.getElementById('weighttransferred').innerHTML = Number(document.getElementById('weight').value).toFixed(3); + ' gram';
				document.getElementById('amounttransferred').innerHTML = 'RM ' + Number(document.getElementById('midprice').innerHTML).toFixed(2); +' / gram';

				document.getElementById('finaltotaltransferred').innerHTML = 'RM ' + Number(document.getElementById('total').value).toFixed(2);

				// Open slider
				$('.slider').toggleClass('close');
			}else{
				// Clicked when open. leave this part empty
				
			}
			
		});

		$('#form_submit').on('submit', function() {
			$('.loader').show();

		});
		

		// Add remarks into displayfield
		$('#remarks').on('keyup',function() {
			var key = $(this).val(),
				result = key;
			if (result != undefined) {
				$('#remarksdisplay').text(result);
			} else {
				$('#remarksdisplay').text('');
			}
		})

		// Pin 
		$("#pin").keypress(function() {
			return (/\d/.test(String.fromCharCode(event.which) ))
		});


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
});




$(function() {

  $('input[name="daterange"]').daterangepicker({
	"maxSpan": {
        "days": 61
    },
    "autoApply": true,
	"drops": "down",    
	locale: {
		"cancelLabel": "<?php echo $lang['Cancel']; ?>",
		"applyLabel": "<?php echo $lang['Download']; ?>",
		"format": "DD/MM/YYYY",
        "separator": " - ",
        "daysOfWeek": [
            "<?php echo $lang['DaySu']; ?>",
            "<?php echo $lang['DayMo']; ?>",
            "<?php echo $lang['DayTu']; ?>",
            "<?php echo $lang['DayWe']; ?>",
            "<?php echo $lang['DayTh']; ?>",
            "<?php echo $lang['DayFr']; ?>",
            "<?php echo $lang['DaySa']; ?>"
        ],
	},
	"buttonClasses": "btn btn-sm btn-cal",
    "applyButtonClasses": "primary",
    "cancelClass": "secondary"
  }, function(start, end, label) {

  });

  $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
	  $('a[name="downloadstatement"').attr("href", "controllers/accountstatement.php?from=" + picker.startDate.format('YYYY-MM-DD') + "&to=" + picker.endDate.format('YYYY-MM-DD'))	  
  });

  $('#orderstatus').on("change", function (ev) {

	const val = $(this).val();
	$(".box-listing-rows > .box-listing-row.transaction").each(function (index, element) {
		if (val === 'All') {
			$(element).show();
		} else if (val === $(element).data('status')) {
			$(element).show();
		} else {
			$(element).hide();
		}
	});
  });

  $('#conversionstatus').on("change", function (ev) {

	const val = $(this).val();
	$(".box-listing-rows > .box-listing-row.conversion").each(function (index, element) {
		if (val === 'All') {
			$(element).show();
		} else if (val === $(element).data('status')) {
			$(element).show();
		} else {
			$(element).hide();
		}
	});
  });

  $('a.tab').on("click", function (ev) {


	if ($(this).attr('rel') == 'one') {
		$('#orderstatus').next().show();
		$('#conversionstatus').next().hide();
	} else if ($(this).attr('rel') == 'two') {
		$('#orderstatus').next().hide();
		$('#conversionstatus').next().show();
	} else {
		$('#orderstatus').next().hide();
		$('#conversionstatus').next().hide();
	}

  });

});
</script>
<style>

	/* Disable scrolling horizontal */
	body {
		overflow-x: hidden;
	}
	select {
		display: none;
	}
	.btn-cal {
		font-size: 16px;
		color: #ffffff;
		border-radius: 6px;
		display: inline-block;
		font-weight: bold;
		border: 0px;
		background: #53C4CC;        
	}
	.btn.primary {
		background: #53C4CC;
	}
	.btn.secondary {
		background: #cccccc;
	}

	.daterangepicker .drp-selected {
		display: none;
	}

	.daterangepicker td.active, .daterangepicker td.active:hover {
		background: #53C4CC;
		border-color: transparent;
		color: #fff;
	}

    select {
        min-width: 120px;
        max-width: 120px;
    }
    select:focus {
        width: auto;
    }

    .nice-select.open .list {
        width: unset;
    }

	/* Style pin */
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
<body class="gogold-transfer-details">
	<div class="form-title center loader">
		<img src="img/bg/transfer-send.jpg" alt="this slowpoke moves"  width="auto" />
		<div class="inside-title-transfer-confirm"><?php echo $lang['Transferring']; ?><span id="wait">.</span></div>
	</div>
	
	<div class="language-switcher transfer">
		<ul>
			<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="transfer-details.php?lang=en">Eng</a></li>
       		<li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="transfer-details.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<br/>
	<div class="header-inside">
		<div class="inside-title-transfer"><?php echo $lang['Transfer']; ?></div>
		<!-- <a class="back" href="/" style="color:black;display:flex; font-size: 16px"><span style="margin-top:-1px">&nbsp;&nbsp;&nbsp;&nbsp;Back</span></a> -->
		<a class="back white" href="transfer.php" style="color:black;display:flex; font-size: 16px"></a>
	</div>

	<div class="page-content-box">
			<div class="box-listing transfer">
				<div class="box-listing-tabs">
					<a class="tab avoid-clicks" rel="one"><?php echo $lang['Transfer']; ?></a>
					<a class="tab avoid-clicks" rel="two">&nbsp;</a>
				</div>
	</div>
	<div class="page-content-form-user">
		
		<div class="inside-profile-transfer">
			<div class="col">
				<img src="img/icon/user.svg" alt="user"   width="60" height="60" />
			</div>
			<div class="col">
				<div class="transfer-profile-title"><?php echo $_SESSION['transferfullname']; ?></div>
				<div class="transfer-profile-text"><?php echo $_SESSION['transferemail']  ?></div>
				<div class="transfer-profile-text"><?php echo $_SESSION['transferphone_number'] ?></div>
			</div>
		</div>
	</div>

	<div class="main-widget">
		<div class="balance-row balance-row2">
			<label><?php echo $lang['GOLDBALANCE']; ?></label>
			<div class="balance-value">
			<div class="unit"><abbr title="The gold remaining in your account in grams." rel="tooltip"><?php echo $lang['gram']; ?></abbr></div>
				<div class="num transfer"><?php echo $_SESSION['goldbalance']; ?></div>
				<!--<div class="num"><abbr title="The gold remaining in your account in grams." rel="tooltip"><php echo $_SESSION['goldbalance']; ?></abbr></div>-->
				<!--<div class="unit"><php echo $lang['gram']; ?></div>-->

			</div>
			
		</div>
		<div class="buy-sell-row transfer">
			<div class="col center">
				<div class="action-title"><?php echo $lang['MidPrices']; ?></div>
				<div class="current-value up">
					<div class="num"><p id="midprice"></p></div>
				</div>
				<div class="unit"><?php echo $lang['pergram']; ?></div>
			</div>
			<div class="col sideup">
				<div class="unit" id="refreshtimer"></p></div>
				<img class="refresh-button" src="img/icon/jam_refresh-reverse.svg" alt="refresh"  width="auto" />
			
				
			</div>
		</div>
	</div>
	<form id="form_submit" method="POST" action="controllers/confirmtransfer.php" onsubmit="">
		<div class="page-content-form transfer">
		
			<div class="form-title left">
				<?php echo $lang['BuySellWeightLabel']; ?>
			</div>

			<input id="weight" min="0" name="weight" type="number" step="any" oninput="validate(this, 4, 'weight')" class="opp" placeholder="0.000"  style="text-align: center;">		
					
		</div>
		<img class="transfer-icon" src="img/icon/bx_transfer-alt.svg" alt="transfer-arrow"  width="30" height="30" />
		<div class="page-content-form transfer">
		
			<div class="form-title left">
				<?php echo $lang['BuySellTotalLabel']; ?>
			</div>

			<input id="total"  type="text" name="total" type="text" class="opp" placeholder="0.00"  style="text-align: center;">	
					
		</div>
		<input id="basemidprice"  type="hidden" name="basemidprice" type="basemidprice" class="opp" placeholder="0.00"  style="text-align: center;">	
		<div class="page-content-form transfer">

			<input id="remarks"  type="remarks" name="remarks" type="text" maxlength="75" class="opp" placeholder="<?php echo $lang['Text_Placeholder_Transfer_What'];?>"  style="text-align: center;">	
					
		</div>
		
		<div class="overlay slider close">
		
			<div class="page-content-box">
				
				<div class="main-widget">
					<div id="btncancelpopup" name="cancelicon" onclick="closeWindow()">
						<i class="fa fa-times" aria-hidden="true"></i>
					</div>
			
					<div class="table-row title">
						<?php echo $lang['TransferDetails'] ?>
					</div>
					<div class="table-row">
						<div class="col label"><?php echo $lang['Receiver'] ?></div>
						<div class="col value"><?php echo  $_SESSION['transferfullname']  ?></div>
					</div>
					<div class="table-row">
						<div class="col label"><?php echo $lang['Remarks'] ?></div>
						<div class="col value"><p class="remarksdisplay" type="text" readonly name="remarksdisplay" id="remarksdisplay"></p></div>
					</div>
					<div class="table-row">
						<div class="col label"><?php echo $lang['DATE'] ?></div>
						<div class="col value"><?php date_default_timezone_set("Asia/Kuala_Lumpur"); echo date('D, d M Y H:i:s') ?></div>
					</div>
					<div class="table-row">
						<div class="col label"><?php echo $lang['GoldTransferred'] ?> <span class="unit">Gram</span></div>
						<div id="weighttransferred" class="col value em highlight"></div>
						
					</div>
					<div class="table-row">
						<div class="col label em"><?php echo $lang['MidPrice'] ?> <span class="unit">Unit price</span></div>
						<div id="amounttransferred" class="col value"></div>
					</div>
					<div class="table-row total transfer">
						<div class="label"><?php echo $lang['FINAL_TOTAL']; ?></div>
						<div id="finaltotaltransferred" class="value"></div>
					</div>

					<div class="form-row center">
						<div class="form-col">
							<label id="pinlabel">
								<div class="form-capitalize-title center">
									<?php echo $lang['SecurityPIN']; ?>
								</div>
							</label>
							<div class="verify-box">
								<input id="pin1" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin1" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify1" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
								<input id="pin2" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin2" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify2" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
								<input id="pin3" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin3" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify3" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
								<input id="pin4" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin4" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify4" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
								<input id="pin5" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin5" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify5" oninput="this.value=this.value.slice(0,this.maxLength)" required/>
								<input id="pin6" autocomplete="off" type="number" style="-webkit-text-security:disc;" pattern="[0-9]*" inputmode="numeric" name="pin6" type="password" maxLength="1" size="1" min="0" max="9" pattern="[0-9]{1}" class="verify6" oninput="this.value=this.value.slice(0,this.maxLength)"required/>
							</div>
							<div class="error"><?php echo $_SESSION['error_pin'] ?? ''; unset($_SESSION['error_pin']); ?></div>
						</div>
					</div>
				</div>
			

				<div class="form-row submit">
					<div class="form-col">
						<input id='btnsendtransfer' type="submit" class="trigger btn" name="btnsendtransfer"  formaction="controllers/confirmtransfer.php" value="<?php echo $lang['SUBMIT']; ?>"/>
					</div>
				</div>
			</div>


		</div>
		
	</form>

	<div class="form-row submit">
			<div class="form-col">
				<input id='btnconfirmtransfer' type="submit" class="trigger btn" name="btnconfirmtransfer"  formaction="" value="<?php echo $lang['ConfirmTransfer']; ?>"/>
				
			</div>
	</div>
	<footer class="empty-space">
		<br>
		<br>
	</footer>
</body>

</html>