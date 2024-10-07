<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

	if ($_SESSION['login'] != "success"  ) {
		header("Location: index.php");
		}

	?>
<?php include_once('common.php');  ?>
<?php include_once('controllers/transfercontacthistory.php');  ?>
<?php include_once('controllers/gettransferhistory.php');  ?>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $lang['TITLE_TRANSFER'];?></title>
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
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script>

$( document ).ready(function() {
	$('select').niceSelect();

	$('#orderstatus').next().show();
  $('#conversionstatus').next().hide();
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

  // Add script for clicking on recent contacts
  $('.inside-profile-transfer.recent').on("click", function (ev) {
	
	if ($(this).attr('data')) {
		// get innertext value
		$index = $(this).attr('data');
		$name = "sendtophoneno" + $index;
		$phoneno = $('div[name="'+$name+'"]')[0].innerText;

		// remove country code 
		$country_code = $("#countryCode").val();

		// temp solution // need regex filter code
		$phone_no = $phoneno.replace(/^\+?60|\|1|\D/, '');

		$('#contact').val($phone_no);
	} else {
		Swal.fire({
			title: 'Info!',
			text: 'Unable to detect contact data',
			icon: 'info',
			confirmButtonText: 'OK',
			confirmButtonColor: '#53C4CC'
		});
	}

  });

});
</script>
<?php 
if (isset($_GET['status'])) {
	if ($_GET['status'] == 'error') {
		if($_SESSION['checkcustomer_error']){

		echo '
		<script type="text/javascript">
		$(document).ready(function(){
		
			Swal.fire({
			title: "'.$lang['TransferFail'].'",
			text: "Not an existing gogold user",
			icon: "info",
			confirmButtonText: "Invite Now",
			showCancelButton: true,
			cancelButtonText: "OK",
			}).then(function (result) {
				if (result.value) {
					window.location.href = "friend-invite.php";
				}
				else {
				}
			});
		});
		
		</script>
		';
		}else{
		echo '
		<script type="text/javascript">
		$(document).ready(function(){
		
			Swal.fire({
				title: "'.$lang['TransferFail'].'",
				text: "Not an existing gogold user",
				icon: "info",
				confirmButtonText: "Invite Now",
				showCancelButton: true,
				cancelButtonText: "OK",
				}).then(function (result) {
					if (result.value) {
						window.location.href = "friend-invite.php";
					}
					else {
					}
				});
			});
		
		</script>
		';
		}
		
	}
}

?>
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

	.selector {
		/* show +60 */
		display: inline-block;

	}

</style>
</head>
<body class="gogold-transfer">
	<div class="language-switcher transfer">
		<ul>
			<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="transfer.php?lang=en">Eng</a></li>
       		<li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="transfer.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<br/>
	<div class="header-inside">
		<div class="inside-title-transfer"><?php echo $lang['Transfer']; ?></div>
		<!-- <a class="back" href="/" style="color:black;display:flex; font-size: 16px"><span style="margin-top:-1px">&nbsp;&nbsp;&nbsp;&nbsp;Back</span></a> -->
		<a class="back white" href="/" style="color:black;display:flex; font-size: 16px"></a>
	</div>

	<div class="page-content">

		<div class="page-content-box">
			<div class="box-listing transfer">
				<div class="box-listing-tabs">
					<a class="tab active" rel="one">&nbsp;</a>
					<!-- <a class="tab" rel="two"><php echo $lang['Receive']; ?></a> -->
				</div>
				<div class="box-listing-tabs-content transfer">
					<div class="box-listing-tab-content active" rel="one">
						<form method="POST" action="controllers/checkcustomer.php" onsubmit="">
							<div class="input-group">
								<select id="countryCode" name="countrycode" class="selector">
									<option value="+60">+60</option>
								</select>
								<input id="contact" required type="tel" name="contact" placeholder="<?php echo $lang['TransferContactText'] ?>" class="contact-middle" value="<?php echo isset($pesato_register) && $pesato_register == 1 ? $_SESSION['TtelHp']:''; ?>">
								<input id="checkcontact" type="submit" class="btn" name="checkcontact" value="<?php echo $lang['Enter']; ?>" />
							</div>
						</form>
						<!-- inset code here for contacts -->
						<div class="box-listing-rows transfer">
							<div class="inside-title-transfer left">  <?php echo $lang['RecentContacts']; ?></div>
								<?php
								$loop = 0; 
								if ($transferHistory['data']){
									foreach ($transferHistory['data']['assender'] as $transfer) { 
										$sendtophoneno = 'sendtophoneno'.$loop;
										$recordid = $loop;
										$loop++;
								?>
								<div class="box-listing-row conversion" data-status="<?php echo $transfer['status']; ?>" data-type="transfer-history" data-transaction="<?php echo rawurlencode(json_encode($transfer)); ?>" >
									<div data='<?php echo $recordid; ?>' class="inside-profile-transfer recent">
										<div class="col">
											<img src="img/icon/user.svg" alt="user"  width="60" height="60" />
										</div>
										<div class="col">
											<div class="transfer-profile-title"><?php echo $transfer['sendto']; ?></div>
											<div class="transfer-profile-text"><?php echo $transfer['sendon']  ?></div>
											<div name='<?php echo $sendtophoneno; ?>' class="transfer-profile-text"><?php echo $transfer['sendtophoneno'] ?></div>
											<div class="transfer-profile-text"><?php echo $transfer['sendxau'] ?> gram</div>
										</div>
									</div>
								</div>
								<?php }} ?>
						</div>
					</div>

					<div class="box-listing-tab-content" rel="two">
						<div class="box-listing-rows transfer">
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
<footer class="footer transfer">
   
   <p ><?php echo $lang['Footer_Transfer_1']; ?></p><p><?php echo $_SESSION['goldbalance'].' '.strToUpper($lang['gram']); ?></p>
</footer>
</html>