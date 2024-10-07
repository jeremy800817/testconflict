<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();

}

$transferHistory = getTransferHistory(10, false);
$transferHistorySorted = getTransferHistory(10, true);
// $adminstoragefees = getTransactions('adminstoragefee',10);
$conversions = getConversions(10);

function getTransferHistory($pageSize = 30, $sorter = false ) {
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
      "action": "gold_transfer_history"
  }',
    CURLOPT_HTTPHEADER => array(
      'Authorization: Bearer ' . $_SESSION['token'],
      'Content-Type: application/json'
    ),
  ));
  
  $response = curl_exec($curl);  

  if(true == $sorter){
    $returns = json_decode($response, true);
    if (sizeof($returns['data']) > 0){
      foreach ( $returns['data'] as $records )
      {
        foreach ( $records as $record) {
           // Split date depending on return data
          // check if send or return
          if($record['sendon']){
            $time  = strtotime($record['sendon']);
            $record['type'] = 'send';
          }else if($record['receiveon']){
            $time  = strtotime($record['receiveon']);
            $record['type'] = 'receive';
          }
        
          $day   = date('d',$time);
          $month = date('m',$time);
          $year  = date('Y',$time);
          
     
          $templateData[$year][$month][$day][] = $record;
        }
        
      }
    }
   
  }
  

  // do filter if sorters = true

  curl_close($curl);

  if(true == $sorter){
    $templateData = [];
    return $templateData;
  }else{
    return json_decode($response, true);
  }
  
}

// function getConversions($pageSize = 30) {
//   $page=1;
//   $curl = curl_init();
  
//   // $today = date("Y-m-d");
//   $today = (new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y-m-d');
//   $date = strtotime('' . $today .' -1 year');
  
//   $yearbefore = date('Y-m-d', $date);
  
//   curl_setopt_array($curl, array(
//     CURLOPT_URL => $_SESSION['APIURL'],
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_ENCODING => '',
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 0,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     CURLOPT_CUSTOMREQUEST => 'POST',
//     CURLOPT_POSTFIELDS =>'{
//       "version": "1.0my",
//       "merchant_id": "' . $_SESSION['par_code'] . '",
//       "action": "conversion_history",
//       "date_from": "'. $yearbefore .' 00:00:00",
//       "date_to": "'. $today . ' 23:59:59",
//       "page_number": ' . $page . ',
//       "page_size": '. $pageSize .'
//   }',
//     CURLOPT_HTTPHEADER => array(
//       'Authorization: Bearer ' . $_SESSION['token'],
//       'Content-Type: application/json'      
//     ),
//   ));
  
//   $response = curl_exec($curl);  
  
//   curl_close($curl);
//   return json_decode($response, true);
// }

?>