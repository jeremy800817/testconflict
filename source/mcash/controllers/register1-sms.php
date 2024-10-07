<?php
 if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
if (isset($_POST['email'])) {
//contact //response
if (isset($_POST['subcategory'])) {
  $_SESSION['Rsubcategory'] = $_POST['subcategory'];
}else{
  $_SESSION['Rsubcategory']="0";
}

  $_SESSION['Runame'] = $_SESSION['Tname'];
  $_SESSION['Ric']  = $_SESSION['TidNo'];
  $_SESSION['Remail']  = $_POST['email'];
  //$_SESSION['Rcontact']  = $_SESSION['TtelHp']; 
  $_SESSION['Rcontact']  = $_POST['contact'];
  $_SESSION['Roccupation'] = $_POST['occupation'];
  $_SESSION['Rbank'] = $_POST['bank'];
  $_SESSION['Raddress1'] = $_POST['address1'];
  $_SESSION['Raddress2'] = $_POST['address2'];
  $_SESSION['Rcity'] = $_POST['city'];
  $_SESSION['Rpostcode'] = $_POST['postcode'];
  $_SESSION['Rstate'] = $_POST['state'];
  $_SESSION['Rpin'] = $_POST['pin'];
  $_SESSION['campaign'] = $_POST['campaign'];
 
  //campaign
  $_SESSION['Raccountnumber'] = $_POST['accountnumber'];
  //Raccountnumber

  $_SESSION['Rsuboccupation']=0;
   
  $_SESSION['Rlanguage'] = strtoupper($_SESSION['Tlanguage']);


   $contact = $_SESSION['Rcontact'];

    $curl = curl_init();
    $firstCharacter = $contact[0];
    if ($firstCharacter != "+") {
      $contact = "+" . $contact;
    }
    $_SESSION['Rcontact'] =  $contact;

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
    "merchant_id": "' .  $_SESSION['par_code'] . '",
    "action": "resend_verification_phone",
    "phone_number": "'. $contact .'"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
   
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);
   // echo $response;
    $data = json_decode($response, true);

    if ($data['success']) {
    
    }else{
      //error_message
      $error = $data['error_message'];
      echo '<script>alert("'. $error .'")</script>';
      //header("location: register-1.php");
    }

}else{
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
  "merchant_id": "' .  $_SESSION['par_code'] . '",
  "action": "resend_verification_phone",
  "phone_number": "'.  $_SESSION['Rcontact'] .'"
}',
CURLOPT_HTTPHEADER => array(
  'Content-Type: application/json'
 
),
));

  $response = curl_exec($curl);

  curl_close($curl);
 // echo $response;
  $data = json_decode($response, true);

  if ($data['success']) {
  
  }else{
    //error_message
    $error = $data['error_message'];
    echo '<script>alert("'. $error .'")</script>';
    //header("location: register-1.php");
  }
  }

?>