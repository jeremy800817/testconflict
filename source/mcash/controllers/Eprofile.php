<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}


if(isset($_POST['nextofkinname']))
{
  //$formtype = $_POST['formtype'];
  
  // Temp M2U fix
  // if bank is m2u
 
$nextofkin = $_POST['nextofkinname'];
$nokcontact = '+' . $_POST['nextofkincontact'];
$address1 = $_POST['address_line_1'];
$address2 = $_POST['address_line_2'];
$postcode = $_POST['postcode'];
$City = $_POST['city'];
$State = $_POST['state'];
$occupation_category_id =  $_SESSION['occupation_category_id'];

$occupation_subcategory_id = $_SESSION['occupation_subcategory_id'];

  $pin1 = $_POST['pin1'];
  $pin2 = $_POST['pin2'];
  $pin3 = $_POST['pin3'];
  $pin4 = $_POST['pin4'];
  $pin5 = $_POST['pin5'];
  $pin6 = $_POST['pin6'];


  $error="No";
  $pin = $pin1 . $pin2 . $pin3 . $pin4 . $pin5 . $pin6 ;

  // Start
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
        "merchant_id": "' .  $_SESSION['par_code'] .'",
        "action": "profile_update",
        "address_line_1": "'. $address1 .'",
        "address_line_2": "'. $address2 .'",
        "postcode": '.$postcode.', 
        "city": "'. $City .'",
        "state": "'.$State.'",
        "nok_full_name": "'. $nextofkin.'",
        "nok_phone": "'. $nokcontact .'",
        "nok_email": "",
        "nok_address": "",
        "nok_relationship": "",
        "occupation_category_id": ' . $occupation_category_id .',
        "occupation_subcategory_id": "'. $occupation_subcategory_id.'",
        "referral_salesperson_code": "",
        "pin": '. $pin .'
  }
  ',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $_SESSION['token'],
      'Content-Type: application/json'
    ),
  ));
  
     $response = curl_exec($curl);

     curl_close($curl);
     
      $data = json_decode($response, true);

    if ($data['success']) {
        $_SESSION['EditProfile'] = 1;
        $_SESSION['edit_profile_error_message'] = $lang['SucessfulProfileEdit'];
        $_SESSION['edit_profile_title'] = $lang['TITLE_EDIT_PROFILE_SUCCESS'];
        $_SESSION['M2'] = "Thank you";
        $_SESSION['editprofiledone'] = 1;

        $_SESSION['nok_full_name'] = $_POST['nextofkinname'];
        $_SESSION['nok_phone'] = '+' . $_POST['nextofkincontact'];
        $_SESSION['address_line_1'] = $_POST['address_line_1'];
        $_SESSION['address_line_2'] = $_POST['address_line_2'];
        $_SESSION['postcode'] = $_POST['postcode'];
        $_SESSION['city'] = $_POST['city'];
        $_SESSION['state'] = $_POST['state'];

      //header( "location:../editbankaccountnumber-success.php"); 
    }else{
      $_SESSION['EditProfile'] = 0;
      $_SESSION['editprofiledone'] = 0;
      $_SESSION['edit_profile_title'] = $lang['TITLE_EDIT_PROFILE_FAILED'];
      //error_message
      if ($data['error'] === 20005) {
        $_SESSION['edit_profile_error_message'] = $lang['IncorrectPIN'];
      } else {
        $_SESSION['edit_profile_error_message'] = $data['error_message'];
        
      }

      $_SESSION['M2'] = "Sorry. Please try again.";

      
     // header( "location:../editbankaccountnumber-failure.php"); 
    }
}else{

}


?>