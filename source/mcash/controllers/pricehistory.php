<?php
include('config/db.php');
if(!isset($_SESSION)) {
    session_set_cookie_params(0);
    session_start();
}

if(isset($_POST['month'])){
$month = $_POST['month'];
$today = date("Y-m-d");
if ($month == 1) {
  $pagesize=30;
$date = strtotime('' . $today .' -1 month');
}


if ($month ==3) {
    $date = strtotime('' . $today .' -3 month');
    $pagesize = 180;
}
if ($month ==6) {
  $date = strtotime('' . $today .' -6 month');
  $pagesize = 360;
}
if ($month ==12) {
  $date = strtotime('' . $today .' -12 month');
  $pagesize = 720;
}
$startfrom = date('Y-m-d', $date);

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
    "merchant_id": "'. $_SESSION['par_code'] . '",
    "action": "gold_prices",
    "product": "DG-999-9",
    "date_from": "' . $startfrom . ' 00:00:00",
    "date_to": "'. $today . ' 23:59:59",
    "page_number": 1,
    "page_size": ' . $pagesize .'
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'] ,
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
//echo $response;

$data = json_decode($response, true);
if($data['success'])
{
$i = 0;
 $count = count($data['data']);
// echo $count;
$countries = array();
while ($i < $count) {
  $countries[$i] = array("date" => $data['data'][$i]['date'], "companysell" => number_format($data['data'][$i]['close_sell'],3,'.',''));
  $i = $i +1; 
}

  $array  = array_reverse($countries);
  
  $response = json_encode($array);
 // header("Content-Type: application/json");
  echo $response;
  
}else{
echo 'some error';

}


}
?>