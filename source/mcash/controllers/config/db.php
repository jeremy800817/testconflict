<?php 

    // Enable us to use Headers
    ob_start();

    // Set sessions
    if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

    //uat server
    

//local

$hostname = "192.168.50.27";
$username = "gtp";
$password = "d4ObEvw2RwHD5QU#";
$dbname = "gtp";
$specialapi = 'http://mgolduatapi.ace2u.com/wallet/gopayz.php';  //new url
$api ='http://mgolduatapi.ace2u.com/mygtp.php';
$m1frontapi = 'http://mgolduatapi.ace2u.com/fpx/m1front.php';
$m1backapi = 'http://mgolduatapi.ace2u.com/fpx/m1back.php'; //M1 Api
$gopayztoken = "https://gopayzuat.ace2u.com?channel=GOPAYZ&payload=";
$socket="wss://mgolduatapi.ace2u.com/mygtp.php?version=1.0my&action=pricestream&merchant_id=";
$livenow ="Yes";
$par_name ='MCASH';
$McashKey='615aaea8c713b';
$defaultlogin ='jeff@silverstream.my';
$defaultpw ='Asdf1234';
$version ='1.0my';
$McashApi='https://newbackend.mcash.my/cross/ext/usr/info';
$McashMerchant='acegoldUAT';

    $connection = mysqli_connect($hostname, $username, $password, $dbname) or die("Database connection not established.")

?>