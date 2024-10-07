<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();

}

$spots = getTransactions('buysellpromo', 10);
$adminstoragefees = getTransactions('adminstoragefee',10);
$conversions = getConversions(10);

function getTransactions($type = 'buysellpromo', $pageSize = 30) {
  $page=1;
  $curl = curl_init();
  
  // $today = date("Y-m-d");
  $today = (new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y-m-d');
  $date = strtotime('' . $today .' -1 year');
  
  $yearbefore = date('Y-m-d', $date);
  
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
      "merchant_id": "' . $_SESSION['par_code'] . '",
      "action": "spot_transaction_history",
      "date_from": "'. $yearbefore .' 00:00:00",
      "date_to": "'. $today . ' 23:59:59",
      "page_number": ' . $page . ',
      "page_size": '. $pageSize .',
      "type": "'. $type. '"
  }',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer ' . $_SESSION['token'],
      'Content-Type: application/json'
    ),
  ));
  
  $response = curl_exec($curl);  
  
  curl_close($curl);
  return json_decode($response, true);
}

function getConversions($pageSize = 30) {
  $page=1;
  $curl = curl_init();
  
  // $today = date("Y-m-d");
  $today = (new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y-m-d');
  $date = strtotime('' . $today .' -1 year');
  
  $yearbefore = date('Y-m-d', $date);
  
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
      "merchant_id": "' . $_SESSION['par_code'] . '",
      "action": "conversion_history",
      "date_from": "'. $yearbefore .' 00:00:00",
      "date_to": "'. $today . ' 23:59:59",
      "page_number": ' . $page . ',
      "page_size": '. $pageSize .'
  }',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer ' . $_SESSION['token'],
      'Content-Type: application/json'      
    ),
  ));
  
  $response = curl_exec($curl);  
  
  curl_close($curl);
  return json_decode($response, true);
}

?>