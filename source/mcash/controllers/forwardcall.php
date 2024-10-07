<?php

// Using this file to call api to avoid CORS error

include('../controllers/config/db.php');

$url = base64_decode($_GET['url']);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
