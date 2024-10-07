<?php

session_set_cookie_params(0);
session_start();
if(isset($_POST['uuid'])){
  $uuid = $_POST['uuid']; 
  $customerbuy = $_POST['customerbuy'];
  $customersell = $_POST['customersell'];
    
    $_SESSION['uuid'] = $uuid;
    $_SESSION['customerbuy'] = $customerbuy;
    $_SESSION['customersell'] = $customersell;

    echo $uuid;
    echo "   ";
    echo $customerbuy;
  
 
}else{
echo "no data post in";
}

?>