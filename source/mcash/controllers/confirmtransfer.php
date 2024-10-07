<?php
include_once('log.php');
if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

if (isset($_POST['weight'])) {

    $pin1 = $_POST['pin1'];
    $pin2 = $_POST['pin2'];
    $pin3 = $_POST['pin3'];
    $pin4 = $_POST['pin4'];
    $pin5 = $_POST['pin5'];
    $pin6 = $_POST['pin6'];

    $error="No";
    $pin = $pin1 . $pin2 . $pin3 . $pin4 . $pin5 . $pin6 ;

    $receiver_fullname = $_SESSION['transferfullname'];
    $receiver_email = $_SESSION['transferemail'];
    $receiver_phonenumber = $_SESSION['transferphone_number'];
    $receiver_accountcode = $_SESSION['transferaccountcode'];
    
    $weight = $_POST['weight'];
    $midprice = $_POST['basemidprice'];
    $total = $_POST['total'];
    $remarks = $_POST['remarks'];

    if($_POST['remarks']){
      $_SESSION['chk_transferremarks'] = $remarks;
    }else{
      $_SESSION['chk_transferremarks'] = '-';
    }


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
    "merchant_id": "' .  $_SESSION['par_code'] .'",
    "action": "goldtransfer",
    "receiver_phone_number": "' . $receiver_phonenumber . '",
    "receiver_email": "' . $receiver_email . '",
    "weight": "' . $weight . '",
    "price": "' . $midprice . '",
    "receiver_accountcode": "' . $receiver_accountcode . '",
    "pin": "' . $pin . '",
    "message": "' . $remarks . '",
    "amount": "' . $total . '"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'] ,
    'Content-Type: application/json'
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);

    $data = json_decode($response, true);
  
    if ($data['success'] === false) {
      if ($data['error'] === 50000) {
        $_SESSION['transfer_error'] = $lang['TransferConfirmErr'];
      } else {
        $_SESSION['transfer_error'] = $data['error_message'];
      }

      
      header('location: ../transfer.php?status=error');
      exit();
    }
  
    $_SESSION['chk_transferfullname'] = $data['data']['receiver_name'];
    // $_SESSION['insurance_fee'] = $data['data']['insurance_fee'];
    $_SESSION['chk_transferemail'] = $data['data']['receiver_email'];
    // $_SESSION['courier_fee'] = $data['data']['courier_fee'];
     $_SESSION['chk_transferphone_number'] = $data['data']['receiver_phonenumber'];
     $_SESSION['chk_transferaccountcode'] = $data['data']['receiver_accountcode'];

     $_SESSION['chk_transferxau'] = number_format($data['data']['received_xau'], 3, '.', ' ');
     $_SESSION['chk_transferprice'] = number_format($data['data']['received_price'], 2, '.', ' ');
     $_SESSION['chk_transferamount'] = number_format($data['data']['received_amount'], 2, '.', ' ');

     header('location: ../transfer-confirm.php');
    //  $_SESSION['totalconvertion'] = $_SESSION['conversion_fee'] + $_SESSION['insurance_fee'] + $_SESSION['courier_fee']  + $_SESSION['transaction_fee'];
}
?>