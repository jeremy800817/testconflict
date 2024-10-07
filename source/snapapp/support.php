<?php 

// Check if its coming from easigold, if accessed from easigold app, show page

if(!isset($_SESSION)) {
    session_set_cookie_params(0);
    session_start();
}

// Send Email
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sendemail') {

        include 'easigold/support-complete.php' ;
    }else{
        
        include 'easigold/support.php' ;
    }

}else{
        
    include 'easigold/support.php' ;
}



?>  