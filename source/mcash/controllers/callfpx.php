<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
//The names of the POST variables that we want to send
//to the external website.
include_once('../common.php');
include("log.php");

$_SESSION['transTime1'] = date('Y-m-d H:i:s');
if(isset($_SESSION['transTime2'])){
  unset($_SESSION['transTime2']);
}

if(isset($_POST['submit_form']))
{
  $pin1 = $_POST['pin1'];
  $pin2 = $_POST['pin2'];
  $pin3 = $_POST['pin3'];
  $pin4 = $_POST['pin4'];
  $pin5 = $_POST['pin5'];
  $pin6 = $_POST['pin6'];

  $error="No";
$pin = $pin1 . $pin2 . $pin3 . $pin4 . $pin5 . $pin6 ;

//echo $pin;
  $formtype = $_POST['formtype'];
  $campaigncode ='No';
  $_SESSION['lastaction'] = "buyfpx";
  if('BUY' == $formtype){
    $campaigncode = $_POST['buycampaigncode'];
    
  
  }else if('SELL' == $formtype){
    $bankaccountname = $_POST['bankaccountname'];
    $bankaccountnumber = $_POST['bankaccountnumber'];
    $campaigncode = $_POST['sellcampaigncode'];
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
        "action": "spot_acesell",
        "product": "DG-999-9",
        "settlement_method": "fpx",
        "weight": "'. $_SESSION['weight'] .'",
        "uuid": "' . $_SESSION['uuid'] .'",
        "from_alert": false,
        "campaign_code": "' .$campaigncode . '",
        "pin": "' . $pin .'"
    }',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $_SESSION['token'],
        'Content-Type: application/json',
        'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    fpx($_SESSION['email'],$_SESSION['uuid'],$_SESSION['weight'],$response);
    $data = json_decode($response, true);
 //echo $data;
    if ($data['success']) {
      $fpxurl = $data['data']['location'];
      $_SESSION['refno'] = $data['data']['refno'];
    //  echo $fpxurl;
  //window.open('https://www.outsystems.com', '_system');
      header('location:'.$fpxurl);
//Setup cURL
      
  
    }else{
      if ($data['error'] === 40009) {
        $_SESSION['get_transaction_error_message'] = $lang['TradingHourErr'];
      } else {
        $_SESSION['get_transaction_error_message'] = $data['error_message'];
        
      }  
      $_SESSION['get_transaction_error_message_type'] = 'spot_acesell';
      header('location: ../failedbuysell.php');
    }



}

?>