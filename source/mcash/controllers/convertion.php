<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
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
    "merchant_id": "'. $_SESSION['par_code'] .'",
    "action": "conversion",
    "product": "' . $_SESSION['product'] . '",
    "quantity": "' . $_SESSION['quantity'] . '",
    "pin": "' .  $_SESSION['pin'] . '"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJPTkVHT0xEQFVBVCIsImp0aSI6IjU5ODNjNzAxYWM5M2I0NmQ2MzIyOGQzZDViOTE3ZDM1ZWFiOWE5ZTY4YjdiZjRhZDdmNzY2YzIzMjAyNGJiMmYxNzc5OTQxMmQ4NWNiYjNhIiwiaWF0IjoxNjIwNjk4NTkwLCJuYmYiOjE2MjA2OTg1OTAsImV4cCI6MTYyMDc4NDk5MCwic3ViIjoiamVmZkBzaWx2ZXJzdHJlYW0ubXkiLCJzY29wZXMiOltdfQ.Muws-sPmBm_CQ3ELZOjgRcSvr0OBFQD5m-lh5tBL4LUgrc-ZgiTlqzRzlWEKIbem1Lnwkodmnb11cwRDGD-EUWFuerJT7e3d2AzgbZvIcx0tVxsJ7faGQ3fk8h8TF4OCsm6NYb52wkbk_ZdzrUCCHEHoi9tTnJhcRdNFGetCWK2uPDSllwci1BC-Veo5kLJH6ifzxrHkiY6mwaRx9SDS7mgIwx5s0Asr2Y5OD-dxD2gLWMWDQhCWzs77y_fMPaVZ6oUdkldl3guuGt7OEyTOG6qYc0r0kv43TvQWfqafewYurMxshsFNarFuG5cwr0ITRTbwU6g3q8ouNfDBxayemg',
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;


?>