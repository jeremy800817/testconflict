<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

	if ($_SESSION['login'] != "success"  ) {
		header("Location: index.php");
		}

		$query = [
			'APIURL' => $_SESSION['APIURL'],
			'par_code' => $_SESSION['par_code'],
			'token' => $_SESSION['token'],
			'accountcode' => $_SESSION['accountcode']
		];

		$queryString = http_build_query($query);

	?>
<?php include_once('common.php');  ?>
<?php include_once('controllers/transactionhistory.php');  ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $lang['TITLE_TRANSACTIONS'];?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" type="text/css" href="css/style-mcash.css">
<link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script>

$(document).ready(function(){
	$('select').niceSelect();

	$('#orderstatus').next().show();
    $('#conversionstatus').next().hide();

    $(this).scrollTop(0);
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
	  $('a[name="downloadstatement"]').attr("href", "controllers/accountstatement.php?from=" + picker.startDate.format('YYYY-MM-DD') + "&to=" + picker.endDate.format('YYYY-MM-DD') + "&<?php echo $queryString; ?>")
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
					<li ><a href="transactions.php?lang=en">Eng</a></li>
					<li><a href="transactions.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header">
		<div class="hero">
			<div class="client-logo"></div>
			<div class="main-logo"></div>
		</div>
	</div>
	
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['Transaction']; ?></div>
		<a class="back" href="/"></a>
	</div>

	<div class="page-content">

		<div class="page-content-box">

			<div class="page-content-form">
				<div class="balance-row last" style="gap: 10px">
					<div class="col left" style="display: flex; align-items: center; justify-content: center;">
						<!-- <div class="num"><?php echo $lang['Statement']; ?></div> -->
						<div class="form-row">
							<div class="form-col">
								<input type="text" name="daterange" autocomplete="off" readonly="readonly" style="border-radius: 6px 0 0 6px;">
							</div>
						</div>
						<div class="form-row">
							<a name="downloadstatement" href="controllers/accountstatement.php?<?php echo $queryString; ?>" target="_blank" download>
                                <button class="btn btn-cal primary" style="padding: 10px 15px;border-radius: 0 5px 5px 0;border-width: 2px;border-style: solid;border: 2px solid;border-color: #0DB14B;">
                                    <i class="fa fa-arrow-down" aria-hidden="true"></i>
                                </button>
                            </a>
						</div>
					</div>
					<div class="col right">
						<div class="form-row">
							<select id="orderstatus" name="orderstatus" >
								<option value="All"><?php echo $lang['All']; ?></option>
								<option value="Pending"><?php echo $lang['Pending']; ?></option>
								<option value="Paid"><?php echo $lang['Paid']; ?></option>
								<option value="Confirmed"><?php echo $lang['Confirmed']; ?></option>
								<option value="Failed"><?php echo $lang['Failed']; ?></option>
							</select>
							<select id="conversionstatus" name="conversionstatus" style="display: none;">
								<option value="All"><?php echo $lang['All']; ?></option>
								<option value="Pending"><?php echo $lang['Pending']; ?></option>
								<option value="Paid"><?php echo $lang['Paid']; ?></option>
								<option value="Expired"><?php echo $lang['Expired']; ?></option>
								<option value="Cancelled"><?php echo $lang['Cancelled']; ?></option>
							</select>
 						</div>
 					</div>
				</div>
			</div>
			<div class="box-listing">
				<div class="box-listing-tabs">
					<a class="tab active" rel="one"><?php echo $lang['Sell_and_Buy']; ?></a>
					<a class="tab" rel="two"><?php echo $lang['Conversion']; ?></a>
					<a class="tab" rel="three"><?php echo $lang['Others']; ?></a>
				</div>
				<div class="box-listing-tabs-content">
					<div class="box-listing-tab-content active" rel="one">
						<div class="box-listing-rows">
							<?php foreach ($spots['data'] as $spot) { ?>
								<div class="box-listing-row transaction" data-status="<?php echo $spot['status']; ?>" data-type="spot" data-transaction="<?php echo rawurlencode(json_encode($spot)); ?>" >
								<div class="col icon">
									<?php
									  	if ($spot['type'] == 'buy') {
									  		$badge = 'buy';
											$type = $lang['Buy'];
									  	} elseif ($spot['type'] == 'sell') {
									  		$badge = 'sell';
											$type = $lang['Sell'];
										} elseif ($spot['type'] == 'promo') {
											$badge = 'promo';
										  	$type = $lang['Promo'];
									  	} else {
											$badge = 'buy';
											$type = ucfirst($spot['type']);
										}
									?>
									<span class="badge <?php echo $badge; ?>"><?php echo $type; ?></span>
								</div>
								<div class="col desc">
									<div class="title">RM <?php echo number_format($spot['total_transaction_amount'], 2);?></div>
									<div class="subtitle"><?php echo date('d M Y h:i A', strtotime($spot['date'])); ?></div>
								</div>
								<div class="col value">
								<?php echo $spot['type'] == 'sell' ? '-' : '+'; ?><?php echo $spot['weight']; ?>
									<div class="unit">Gram</div>
									<?php
										$t = $spot['status'];

										if ($t === 'Confirmed' || $t === 'Paid')
										{
											$color = 'green';
										}
										if ($t === 'Pending')
										{
											$color = '#FA9917';
										}
										if ($t === 'Failed')
										{
											$color = 'red';
										}
									?>
									<span id="a" style="font-size:14px;color:<?php echo $color;?>">
										<?php echo $lang[$spot['status']] ?? $spot['status']; ?>
									</span>

								</div>
							</div>
							<?php } ?>
						</div>
					</div>

					<div class="box-listing-tab-content" rel="two">
						<div class="box-listing-rows">
							<?php foreach ($conversions['data'] as $conversion) { ?>
							<div class="box-listing-row conversion" data-status="<?php echo $conversion['status']; ?>" data-type="conversion" data-transaction="<?php echo rawurlencode(json_encode($conversion)); ?>" >
								<div class="col desc">
									<div class="title"><?php echo $lang['Convert']; ?> <?php echo $conversion['total_weight'] / $conversion['total_items'] ?> Gram x <?php echo $conversion['total_items']; ?></div>
									<div class="subtitle"><?php echo date('d M Y h:i A', strtotime($conversion['created_on'])); ?></div>
								</div>
								<div class="col value">
									-<?php echo number_format($conversion['total_weight'], 3); ?>
									<div class="unit">Gram</div>
									<?php
										$t = $conversion['status'];

										if ($t === 'Confirmed' || $t === 'Paid')
										{
											$color = 'green';
										}
										if ($t === 'Pending')
										{
											$color = '#FA9917';
										}
										if ($t === 'Expired' || $t === 'Cancelled')
										{
											$color = 'red';
										}
									?>
									<span id="a" style="font-size:14px;color:<?php echo $color;?>">
										<?php echo $lang[$conversion['status']] ?? $conversion['status']; ?>
									</span>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>

					<div class="box-listing-tab-content" rel="three">
						<div class="box-listing-rows">
							<?php foreach ($adminstoragefees['data'] as $adminstoragefee) { ?>
							<div class="box-listing-row">
								<div class="col desc">
									<div class="title"><?php echo $lang['AdminAndStorageFee']; ?></div>
									<div class="subtitle"><?php echo date('d M Y h:i A', strtotime($adminstoragefee['date'])); ?></div>
								</div>
								<div class="col value">
									-<?php echo number_format($adminstoragefee['weight'], 3); ?>
									<div class="unit">Gram</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>

					<!-- <div class="box-listing-tab-content" rel="three">
						<div class="box-listing-rows">
							<div class="box-listing-row">
								<div class="col desc">
									<div class="title">Annual storage fees</div>
									<div class="subtitle">21 Dec 2020 10:01AM</div>
								</div>
								<div class="col value">
									-5.00
									<div class="unit">Gram</div>
								</div>
							</div>
						</div>
					</div> -->

					<form method="POST" action="transaction-detail.php" id="detail-form">
						<input type="hidden" name="payload" id="payload" value="">
						<input type="hidden" name="type" id="type" value="spot">
					</form>
				</div>
			</div>

		</div>

	</div>

</body>
</html>
