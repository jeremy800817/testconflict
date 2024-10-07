<?php
 if(!isset($_SESSION)) {
	session_set_cookie_params(0);
	session_start();
}

include('../controllers/config/db.php');

$lastaction = $_SESSION['lastaction'];
$date = '';

if (isset($_GET['transactionId'])) {
    $transanctionid  =  $_GET['transactionId'];
	$sellerOrderNo = $_GET['sellerOrderNo'];

	if($lastaction =="buyfpx"){
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $_SESSION['APIURL'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
				"version": "1.0my",
				"merchant_id": "'. $_SESSION['par_code'] .'",
				"action": "spot_status",
				"uuid": "'. $_SESSION['uuid'] .'"
			}',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $_SESSION['token'],
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		//echo $response;

		$data = json_decode($response, true);
		if ($data['success']) {
			$transactionid = $data['data']['transactionid'];
			$weight = $data['data']['weight'];
			$price = $data['data']['price'];
			$status = $data['data']['status'];
			$status_code = $data['data']['status_code'];
			$transaction_fee = $data['date']['transaction_fee'];
			$amount = $data['data']['amount'];
			$total_transaction_amount = $data['data']['total_transaction_amount'];
			$type = $data['data']['type'];
			$date = $data['data']['date'];
			//date
		  
			if ($status=='Paid') {
		   
				$curl = curl_init();
	
				curl_setopt_array($curl, array(
					CURLOPT_URL => $_SESSION['APIURL'],
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS =>'{
						"transactionId": ' . $transanction . '
					
					}',
					CURLOPT_HTTPHEADER => array(
						'Content-Type: application/json'
					),
				));
		
				$response = curl_exec($curl);
		
				curl_close($curl);
		   
			}
	
		   
			//echo $response;
		}

		$receipttype="Customer Buy";
		$actiontype="Gold Purchase (Gram)";
		$PURCHASEPRICE = "Purchase Price";
	}
	else if($lastaction =="conversionfpx"){
		$receipttype ="Convert Gold";
		$actiontype="Gold Convert (Gram)";
		$PURCHASEPRICE = "Convert Price";
		$weight = $_SESSION['gram'];
		$price = $_SESSION['conversion_fee'];
		$total_transaction_amount = $_SESSION['total_fee']; 
		// $transanctionid = $_SESSION['FPXpaymentrefno'];

		if(isset($_GET['transactionId']) && $_GET['transactionId'] != ''){
			$sql = "select a.cvn_status, b.pdt_transactiondate from myconversion a join mypaymentdetail b on b.pdt_sourcerefno = a.cvn_refno where b.pdt_gatewayrefno = ? order by cvn_id desc limit 1";
			$stmt = $connection->prepare($sql);
			$stmt->bind_param('s',$_GET['transactionId']); //i for integer, s for string, d for double, b for blob
			
			$stmt->execute();
			$result = $stmt->get_result();
			
			$q = array();
			while($row=$result->fetch_assoc()){
				array_push($q,$row);
			}
			if(count($q) > 0){
				$date = New DateTime(($q[0]['pdt_transactiondate'] ?? 'now'), new DateTimeZone("UTC"));

				$date->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
				$date = $date->format('D, d M Y H:i:s');
				$status_code = $q[0]['cvn_status'];
			}
		}
		else{
			$status_code = 3;
		}
	}
    
	if($date == ''){
		$date = New DateTime("now", new DateTimeZone("Asia/Kuala_Lumpur"));
		$date = $date->format('D, d M Y H:i:s');
	}

	// For ajax to call the url to getPaymentStatus
	$url = '../controllers/forwardcall.php?url=' . base64_encode($m1frontapi . '?' . http_build_query(['transactionId' => $_GET['transactionId']]));
}

?>
<!doctype html>
<html lang="en-gb" dir="ltr">
<?php  if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    } ?>
<?php include('../common.php');  ?>

<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php echo $lang['BUY_GOLD_RECEIPT_TITLE'] ?></title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="../css/nice-select.css">
<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="../css/style.css">
<script src="../js/jquery.js"></script>
<script type="text/javascript" src="../js/jquery-ui/jquery-ui.min.js"></script>
<script src="../js/jquery.nice-select.min.js"></script>
<script src="../js/script.js"></script>
<script>
$(document).ready(function(){
	$.ajax('<?php echo $url;?>');
});
</script>
</head>
<body class="gogold">
	<div class="language-switcher">
		<ul>
		<li class="active"><?php echo $lang['HI'] ?> <?php echo $_SESSION['displayname'] ?></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'en') || !isset($_SESSION['lang']) ? 'active' : ''; ?>"><a href="dashboard-buy-gold-receipt.php?lang=en">Eng</a></li>
			<li class="<?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] == 'bm') ? 'active' : ''; ?>"><a href="dashboard-buy-gold-receipt.php?lang=bm">BM</a></li>
		</ul>
	</div>
	<div class="header-inside">
		<div class="inside-title">
			<?php echo $receipttype ?>
		</div>
		<a class="back"></a>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
		
			<div class="main-widget">
				<div class="table-row title">
					<div class="sub"><?php echo $lang['Receipt'] ?></div>
					<?php echo $receipttype ?>
				</div>
				<div class="table-row">
					<div class="col label">Transaction Id.</div>
					<div class="col value"><?php echo $transanctionid;    ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['DATE'] ?></div>
					<div class="col value"><?php echo $date ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['NAME'] ?></div>
					<div class="col value"><?php echo  $_SESSION['name']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['NRIC'] ?></div>
					<div class="col value"><?php echo  $_SESSION['ic']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['GOGOLDACCNO'] ?></div>
					<div class="col value"><?php echo $_SESSION['accountcode']  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $actiontype ?> <span class="unit">Gram</span></div>
					<div class="col value"><?php echo  number_format($weight,3)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $PURCHASEPRICE ?> <span class="unit">Unit price</span></div>
					<div class="col value">RM <?php echo   number_format($price, 2)  ?></div>
				</div>
                <div class="table-row">
					<div class="col label"><?php echo $lang['FEES'] ?></div>
					<div class="col value">RM <?php echo  number_format($_SESSION['transaction_fee'], 2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['PURITY'] ?></div>
					<div class="col value"><?php echo $lang['PURITY_VALUE'] ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['VAULT'] ?></div>
					<div class="col value">SG4S, Malaysia<br /><?php echo $lang['STORAGE_PROVIDER'] ?></div>
				</div>
				<div class="table-row">
					<div class="col label em"><?php echo $lang['FINAL_TOTAL'] ?></div>
					<div class="col value em highlight">RM <?php echo  number_format($total_transaction_amount,2)  ?></div>
				</div>
				<div class="table-row">
					<div class="col label"><?php echo $lang['Status'] ?></div>
					<div class="col value">
						<?php 
							// $_SESSION['transTime2'] = date('Y-m-d H:i:s');
							
							// $t1 = new DateTime($_SESSION['transTime1']);
							// $t2 = new DateTime($_SESSION['transTime2']);

							// $diff = $t2->getTimestamp() - $t1->getTimestamp();
							// if($diff >= 180){
							// 	echo $lang['Expire_Refund'];
							// }
							// else{
							// 	if(isset($status_code)){
							// 		if ($status_code == 3 || $status_code == 0){ //3 - Failed, 0 - Pending Payment
							// 			echo $lang['Failed'];
							// 		}
							// 		elseif ($status_code == 1 || $status_code == 2){
							// 			echo $lang['Successful'];
							// 		}
							// 	}
							// 	else{
							// 		echo $lang['Failed'];
							// 	}
							// } 

							if(isset($_SESSION['transTime2'])){
								$diff = $_SESSION['time_diff'] ?? 0;
								if($diff >= 180){	
									echo $lang['Expire_Refund'];
								}
								else{
									if(isset($status_code)){
										if($status_code == 3 || $status_code == 0){
											echo $lang['Failed'];
										}
										elseif(($status_code == 1 || $status_code == 2)){
											echo $lang['Successful'];
										}
									}
									else{
										echo $lang['Failed'];
									}
									
								}
							}
							else{
								$_SESSION['transTime2'] = date('Y-m-d H:i:s');
							
								$t1 = new DateTime($_SESSION['transTime1']);
								$t2 = new DateTime($_SESSION['transTime2']);

								$diff = $t2->getTimestamp() - $t1->getTimestamp();
								$_SESSION['time_diff'] = $diff;
								if($diff >= 180){	
									echo $lang['Expire_Refund'];
								}
								else{
									if(isset($status_code)){
										if($status_code == 3 || $status_code == 0){
											echo $lang['Failed'];
										}
										elseif(($status_code == 1 || $status_code == 2)){
											echo $lang['Successful'];
										}
									}
									else{
										echo $lang['Failed'];
									}
									
								}
							} 
						?>
					</div>
				</div>
			</div>
			
			<div class="footnote">
				<?php echo $lang['RECEIPT_TEXT'] ?>
			</div>
			
			<div class="page-action">
				<a href="../../index.php"><input type="submit" class="btn" value="<?php echo $lang['Done'] ?>" /></a> 
			</div>
			
		</div>
		
	</div>
	
</body>
</html>