<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}

if ($_POST) {
  $payload = json_decode(rawurldecode($_POST['payload']), true);
  $_SESSION['convert_payload'] = $payload;
}
else{
  if(isset($_SESSION['convert_payload'])){
    $payload = $_SESSION['convert_payload'];
  }
  else{
    die('payload not set');
  }
}
?>