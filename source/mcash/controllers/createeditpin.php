<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
if (isset($_POST['pin'])) {
  $pin  = $_POST['pin'];
  $currentpin ="";
  if (isset($_POST['newpin'])) {
    $pin = $_POST['newpin'];
    $currentpin =  $_POST['pin'];
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
    "merchant_id": "ONEGOLD@UAT",
    "action": "pin_update",
    "current_pin": "'. $currentpin . '",
    "new_pin": "'. $pin  . '",
    "confirm_new_pin": "'. $pin  . '"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'] ,
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);
    //echo $response;

    $data = json_decode($response, true);

    if ($data['success']) {
        $respons = json_encode(array( 'success' => true , 'value1' => $my_result,));
        header("Content-Type: application/json");
        echo $respons;
        exit;
    }else{
      echo '<script>alert("Pin not match. Please try again")</script>';
    }
}