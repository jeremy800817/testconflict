<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
$curl = curl_init();

$endDate = $_GET['to'] ?? (new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y-m-d');
$date = strtotime('' . $endDate .' -2 month');
$startDate = $_GET['from'] ?? date('Y-m-01', $date);

curl_setopt_array($curl, array(
  CURLOPT_URL => $_GET['APIURL'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "version": "1.0my",
    "merchant_id": "' . $_GET['par_code'] . '",
    "action": "statement",
    "date_from": "'. $startDate .' 00:00:00",
    "date_to": "'. $endDate . ' 23:59:59"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_GET['token'],
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

$response = curl_exec($curl);

curl_close($curl);

header("Content-type:application/pdf");
header("Content-Disposition: attachment;filename=MGOLD_STATEMENT_". $_GET['accountcode'] ."_". $endDate .".pdf");
header("Content-Length: " . strlen($response));
echo $response;

?>