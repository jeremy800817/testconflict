<?php
 if(!isset($_SESSION)) {
    session_set_cookie_params(0);
    session_start();
}
include('../controllers/config/db.php');
if (isset($_POST['transactionAmount'])) {
    
  $transactionId  =  $_POST['transactionId'];
  //  $transactionAmount = 548.85;
    $transactionAmount = $_POST['transactionAmount'];
   // $transactionId = 2106240951130888;
    $fpxTxnId  =  $_POST['fpxTxnId'];
   // $fpxTxnId = 2106240951130888;
    $merchantOrderNo  =  $_POST['merchantOrderNo'];
   // $merchantOrderNo = 16244994500346597191;
    $status  =  $_POST['status'];
   // $status = "APPROVED";
    $sellerOrderNo  =  $_POST['sellerOrderNo'];
   // $sellerOrderNo  = 44994499481964484;
    $description  =  $_POST['description'];
   // $description = "Buy";
    $signedData  =  $_POST['signedData'];
    $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "transaction Amount: ".( $transactionAmount).
    "transactionId: ".($transactionId).
    "fpxTxnId: ".($fpxTxnId).
    "sellerOrderNo: ".($sellerOrderNo).
    "merchantOrderNo: ".($merchantOrderNo).
    "signedData: ".($signedData).
    "-------------------------".PHP_EOL;
  
  //Save string to log, use FILE_APPEND to append.
    file_put_contents('logdata_.log', $log, FILE_APPEND);
   
   // $apiurl = "http://gopayzuatapi.ace2u.com/fpx/m1back.php";
    $postfields = array();
$postfields['transactionAmount'] = $transactionAmount ;
$postfields['transactionId'] = $transactionId;
$postfields['fpxTxnId'] = $fpxTxnId;
$postfields['merchantOrderNo'] = $merchantOrderNo;
$postfields['status'] = $status;
$postfields['sellerOrderNo'] = $sellerOrderNo;
$postfields['description'] = $description;
$postfields['signedData'] = $signedData;
 
$data = [
  'transactionAmount' => $transactionAmount,
  'transactionId'  => $transactionId,
  'fpxTxnId'  => $fpxTxnId,
  'merchantOrderNo'  => $merchantOrderNo,
  'status'  => $status,
  'sellerOrderNo'  => $sellerOrderNo,
  'description'  => $description,
  'signedData'  => $signedData
  ];
  
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $m1backapi);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  $response = curl_exec($ch);
  curl_close($ch);
  
  echo $response;

    //POST string
               
    
  $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "Post: ".($data).PHP_EOL.
      "response: ".($response).PHP_EOL.
      "-------------------------".PHP_EOL;
    
    //Save string to log, use FILE_APPEND to append.
      file_put_contents('log_.log', $log, FILE_APPEND);


    if ($data['success']) {
      $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
      "Attempt: ".($data['success']).PHP_EOL.
      "-------------------------".PHP_EOL;
    
    //Save string to log, use FILE_APPEND to append.
      file_put_contents('log_.log', $log, FILE_APPEND);
            echo $response;
    }else{
      $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
      "Attempt: ".($data['success']).PHP_EOL.
      "response: ".($data['error_message']).
      "transaction Amount: ".( $transactionAmount).
      "transactionId: ".($transactionId).
      "fpxTxnId: ".($fpxTxnId).
      "sellerOrderNo: ".($sellerOrderNo).
      "merchantOrderNo: ".($merchantOrderNo).
      "signedData: ".($signedData).
      "-------------------------".PHP_EOL;
    
    //Save string to log, use FILE_APPEND to append.
      file_put_contents('log_.log', $log, FILE_APPEND);
            echo $response;
    }

    
}else{
  $entityBody = file_get_contents('php://input');

  $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
  "attempt access: ".($entityBody).
  "-------------------------".PHP_EOL;

//Save string to log, use FILE_APPEND to append.
  file_put_contents('logdata_.log', $log, FILE_APPEND);
}

?>
<!doctype html>
<html lang="en-gb" dir="ltr">
<head>

<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register 2 | GoGold</title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/nice-select.css">
<link rel="stylesheet" type="text/css" href="js/jquery-ui/jquery-ui.min.css">
<link rel="stylesheet" type="text/css" href="css/style.css">
<script src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui/jquery-ui.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src="js/script.js"></script>
<script>


</script>
</head>
<body class="gogold">
	<div class="header">
		<div class="hero"></div>
	</div>
	
	<div class="page-content">
		
		<div class="page-content-box">
			
			<div class="page-content-form">
				
				<div class="page-title">
					FPX payment status
				</div>
							
							
				<div class="form-title center">
				
				</div>
				
				<div class="form-row">
					<div class="form-col center">
						<p>
						<?php echo $response ?>
						
						</p>
					</div>
				</div>
				
				<div class="form-row submit">
					<div class="form-col">
						<a href="index.php">
						<input type="submit" class="btn" value="Go to Dashboard" />
						</a>
					</div>
					
				</div>
			</div>
		
		</div>
		
	</div>
	
</body>
</html>