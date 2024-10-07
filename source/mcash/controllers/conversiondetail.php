<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();

}

if ($_POST) {
  $type    = $_POST['type'];
  $payload = json_decode(rawurldecode($_POST['payload']), true);  
  $status = [];
  
  foreach(array_reverse($payload['logistics_log']) as $log) {
    
    if (in_array($log['status'], ['Processing','Packing','Packed','Collected','Sending'])) {
      $status[0]['status'] = $log['status'];
      $status[0]['date'] = $log['date'];
    }

    if (in_array($log['status'], ['Failed', 'Delivered', 'Missing', 'Completed'])) {
      $status[1]['status'] = $log['status'];
      $status[1]['date'] = $log['date'];
    }
  }
}

?>