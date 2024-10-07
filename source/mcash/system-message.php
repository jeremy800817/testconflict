<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

	// if ($_SESSION['login'] != "success"  ) {
	// 	header("Location: index.php");
	// 	}
	$remainingGold = $_SESSION['available_balance'];
	$_SESSION['customerbuy'] = 240;
	$_SESSION['customersell'] = 240;
?>
<?php include('controllers/login.php');  ?>
<?php include_once('common.php');  ?>
<?php include_once('controllers/transfercontacthistory.php');  ?>
<?php include_once('controllers/gettransferhistory.php');  ?>
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
<link rel="stylesheet" href="css/style-new.css">
<link rel="stylesheet" type="text/css" href="css/style-extra.css">
<link rel="stylesheet" type="text/css" href="css/daterangepicker.css" />
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="vendor/bootstrap-5.0.2-dist/css/bootstrap.css">

<style>

	/* Disable scrolling horizontal */
	body {
		overflow-x: hidden;
	}
</style>
</head>
<script>
    $(function () {
        var Accordion = function (el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            // Variables privadas
            var links = this.el.find('.history_link');
            // Evento
            links.on('click', {
                el: this.el,
                multiple: this.multiple
            }, this.dropdown)
        }

        Accordion.prototype.dropdown = function (e) {
            var $el = e.data.el;
            $this = $(this),
            $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');

            $(".history_accordion li.open .fa-plus, .history_accordion li .fa-minus").toggleClass("fa-plus fa-minus");


            if (!e.data.multiple) {
                $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
            };
        }

        var accordion = new Accordion($('#history'), false);
    });
</script>
<body class="gogold">
	<!-- <div id="loader" class="form-title center">

		<img src="img/bg/transfer-send.jpg" alt="this slowpoke moves"  width="auto" />
	</div> -->
	<div class="language-switcher">
		<ul>
			<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li id="language-en"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="transfer-details.php?lang=en">Eng</a></li>
       		<li id="language-bm"class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="transfer-details.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<br/>
	<div class="header-inside">
		<div class="inside-title"><?php echo $lang['SystemMessage']; ?></div>
		<a class="back" href="index.php"></a>
	</div>
	

	<div class="">
     
    <!-- Spot Buy/Sell -->
    <ul id="history" class="history_accordion">
        <!-- Start foreach for template -->
        <?php foreach ($transferHistorySorted as $year => $months) { ?>
            <?php foreach ( $months as $month => $days ) { ?>
                <?php foreach ( $days as $day => $records ) { ?>
                    <div class="header-inside">
                        <div class="inside-title"><?php echo $day,' ',$lang[$month.'_month_name'], ' ', $year; ?></div>
                    </div>
                    <?php foreach ( $records as $record) { ?>
                        <li>
                            <!-- Do Checking on message type and display record appropriately -->
                            <?php
                                // Check document type
                                // DOCTYPE Send
                                // Temp midprice value
                                if ($record['status'] === 'Success')
                                {
                                    $statuscolor = 'green';
                                }
                                if ($record['status'] === 'Pending')
                                {
                                    $statuscolor = '#FA9917';
                                }
                                if ($record['status'] === 'Failed' || $record['status'] === 'Inactive')
                                {
                                    $statuscolor = 'red';
                                }

                                if ('send' == $record['type']){
                                    
                                    if($record['sendmessage']){
                                        $message = $record['sendmessage'];
                                    }else{
                                        $message = '-';
                                    }
                                    $name = $record['sendto'];
                                    $achcode = $record['sendtoaccholdercode'];
                                    $phoneno = $record['sendtophoneno'];
                                    $xau = $record['sendxau'];
                                    $type = $record['sendtype'];
                                    $date = $record['sendon'];
                                    $midprice = $record['sendprice'];

                                    $typename = $lang['Sender'];
                                    // Init message
                                    $textheader = "";
                                    $textheader = '<p align="left">' . $lang['Sent'] . '<strong> ' . $xau . ' </strong>' . $lang['SentGoldTo'] . '<strong> ' . $name . ' </strong>';

                                }else if ('receive' == $record['type']){

                                    if($record['receivemessage']){
                                        $message = $record['receivemessage'];
                                    }else{
                                        $message = '-';
                                    }
                                    $name = $record['receivefrom'];
                                    $achcode = $record['receivefromaccholdercode'];
                                    $phoneno = $record['receivefromphoneno'];
                                    $xau = $record['receivexau'];
                                    $type = $record['receivetype'];
                                    $date = $record['receiveon'];
                                    $midprice = $record['receiveprice'];

                                    $typename = $lang['Receiver'];
                                    // Init message
                                    $textheader = "";
                                    $textheader = '<p align="left">' . $lang['Received'] . '<strong> ' . $xau . ' </strong>' . $lang['ReceivedGoldFrom'] . '<strong> ' . $name . ' </strong>';
                                }

                            ?>
                            <div class="history_link">
                                <div class="history_data">
                                <a><?php echo $lang['SystemMessage'] ?></a>
                                <br>
                                <p>
                                    <?php echo $textheader; ?>.<br>
                                </p>
                                <p>
                                    <!-- message lang from lang files -->
                                    <?php echo $lang['ReceiveGold_2'] ?>
                                    

                                    <?php echo '<strong>' . $message . '</strong>'; ?><br>
                                </p>
                            </div>
                                <i class="fa fa-plus"></i>
                            </div>
                            <div class="submenu">
                                <div class="history_data">
                                    <a>
                                        <div class="inside-title-transfer-confirm">
                                            <?php echo $xau.' '.$lang['gram']; ?>
                                            <img src="img/icon/bx_transfer-black.svg" alt="transfer" style="max-width: 18px;vertical-align: middle;"  width="auto" />
                                            <?php echo 'RM'.$midprice; ?>
                                        </div>
                                    </a>
                                    <br>
                                    <table style="text-align:center;">
                                        <!-- <tr style="text-align:center;">
                                            <th>&nbsp;</th>
                                            <th>&nbsp;</th>
                                        </tr> -->
                                        <tr>
                                            <td style="width:30%"><?php echo $typename; ?><br></td>
                                            <td style="width:70%"><?php echo $name; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width:30%"><?php echo $lang['Remarks']; ?><br></td>
                                            <td style="width:70%"><?php echo $message; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width:30%"><?php echo $lang['Date']; ?><br></td>
                                            <td style="width:70%"><?php echo $date; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width:30%"><?php echo $lang['GoldTransferred']; ?> <span class="unit"> (Gram)</span><br></td>
                                            <td style="width:70%"><?php echo $xau; ?></td>
                                        </tr>
                                        <tr>
                                            <td style="width:30%">Status <br></td>
                                            <td style="width:70%;color:<?php echo $statuscolor;?>"><?php echo $record['status']; ?></td>
                                        </tr>
                                    </table>
                                    <!-- <p>
                                        Note:<br>
                                        <span style="font-style: italic;">
                                            *Business days referring to Federal Territory.<br>     
                                        </span>
                                    </p> -->
                                </div>
                            </div>
                        </li>
                    <?php } ?>                
                <?php } ?>
            <?php } ?>
            <!-- border -->
        <?php } ?>

        <!-- Others -->
        <!-- End foreach for template -->
        <form method="POST" action="transaction-detail.php" id="detail-form">
            <input type="hidden" name="payload" id="payload" value="">
            <input type="hidden" name="type" id="type" value="spot">
        </form>
    </ul>
</div>

	<footer class="empty-space">
		<br>
		<br>
	</footer>
</body>

</html>