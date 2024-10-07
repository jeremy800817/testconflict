<head>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<?php
    // Set sessions
    if(!isset($_SESSION)) {
      session_set_cookie_params(0);
      session_start();
    }

    include('config/db.php');
    include('log.php');
    ob_start();
    
    if(isset($_POST['email']))
    {
          //contact //response
        if (isset($_POST['subcategory'])) {
          $_SESSION['Rsubcategory'] = $_POST['subcategory'];
        }else{
          $_SESSION['Rsubcategory']="0";
        }

          // Trim only trim start and end of string, for the one in middle we need to use str replace or preg replace
          $_SESSION['Runame'] = str_replace(array("\r","\n"), "", trim($_SESSION['Tname']));
          $_SESSION['Ric']  = str_replace(array("\r","\n"), "",trim($_SESSION['TidNo'] ?? $_POST['ic']));
          $_SESSION['Remail']  = str_replace(array("\r","\n"), "",trim($_POST['email']));
          $_SESSION['Rcontact']  = '+'. str_replace(array("\r","\n"), "",trim($_SESSION['TtelHp'])); 
          //$_SESSION['Rcontact']  = $_POST['contact'];

if($_POST['occupation'] == "Select")
{
  $_SESSION['Roccupation'] = 3;
}else{
  $_SESSION['Roccupation'] = str_replace(array("\r","\n"), "",trim($_POST['occupation']));
}
       //   $_SESSION['Roccupation'] = $_POST['occupation'];


         
          $_SESSION['Raddress1'] = str_replace(array("\r","\n"), "",trim($_POST['address1']));
          $_SESSION['Raddress2'] = str_replace(array("\r","\n"), "",trim($_POST['address2']));
          $_SESSION['Rcity'] = str_replace(array("\r","\n"), "",trim($_POST['city']));
          $_SESSION['Rpostcode'] = str_replace(array("\r","\n"), "",trim($_POST['postcode']));
          $_SESSION['Rstate'] = str_replace(array("\r","\n"), "",trim($_POST['state']));
          $_SESSION['Rpin'] = str_replace(array("\r","\n"), "",trim($_POST['pin']));
          $_SESSION['campaign'] = str_replace(array("\r","\n"), "",trim($_POST['campaign']));
         
          $bank = str_replace(array("\r","\n"), "",trim($_POST['bank']));

          if(strlen($bank)>0){
            $_SESSION['Rbank'] = str_replace(array("\r","\n"), "",trim($_POST['bank']));
          }else{
            $_SESSION['Rbank'] ="1";
          }
          

          //campaign
          $accountnum = str_replace(array("\r","\n"), "",trim($_POST['accountnumber']));
          if (strlen($accountnum) > 0) {
            $_SESSION['Raccountnumber'] = str_replace(array("\r","\n"), "",trim($_POST['accountnumber']));
          }else{
            $_SESSION['Raccountnumber'] = "00000";
          }
         
          //Raccountnumber
        

          if (isset($_POST['subcategory'])) {
            $_SESSION['Rsuboccupation']=str_replace(array("\r","\n"), "",trim($_POST['subcategory']));
          }else
          {
            $_SESSION['Rsuboccupation']=0;
          }
          
           
          $_SESSION['Rlanguage'] = str_replace(array("\r","\n"), "",trim(strtoupper($_SESSION['Tlanguage'])));
        
        
         
//uname:uname,ic:ic,code:code,email:email,nofname:nofname,nofcontact:nofcontact, contact: contact

$_SESSION['registerdone'] = "success";
$_SESSION['M1'] = "THANK YOU!";
$_SESSION['M2'] = "Thank you for your submission!";
$_SESSION['M3'] ="You Account have been registered!";



   $curl = curl_init();
// if(!isset($_SESSION['TlocAcct'])){
//   $_SESSION['TlocAcct'] = '1234567890';
// }

  $postfields = '{
    "version": "1.0my",
    "merchant_id": "'. $_SESSION['par_code'] . '",
    "action": "register",
    "email": "'. $_SESSION['Remail'] .'",
    "password": "' . substr($_SESSION['TlocAcct'], 0, 8) .'",
    "confirm_password": "'. substr($_SESSION['TlocAcct'], 0, 8) . '",
    "full_name": "'. $_SESSION['Runame'] . '",
    "mykad_number": "'. $_SESSION['Ric'] .'",
    "phone_number": "'. $_SESSION['Rcontact'] . '",
    "phone_verification_code": "000000",
    "occupation_category_id": "' . $_SESSION['Roccupation'] .'",
    "occupation_subcategory_id": "'. $_SESSION['Rsubcategory'].'",
    "preferred_lang": "EN",
    "referral_branch_code": "",
    "referral_salesperson_code": "",
    "nok_full_name": "MGold",
    "nok_phone": "+60129909999",
    "nok_email": "",
    "nok_address": "",
    "nok_relationship": ""
}';
   
curl_setopt_array($curl, array(
  CURLOPT_URL => $_SESSION['APIURL'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $postfields,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));

   $response = curl_exec($curl);
  //echo $response; 
   curl_close($curl);
   logaccess(($_SESSION['Remail'] ?? $_POST['email']), 'register' . PHP_EOL . $postfields, $response);
    $data = json_decode($response, true);
  
   
    if ($data['success']) {

      $sql = "UPDATE myaccountholder SET ach_campaigncode='". $_SESSION['campaign'] ."', ach_partnercusid='". $_SESSION['TlocAcct'] ."', ach_type='" .  $_SESSION['TkycInd'] . "' WHERE ach_partnerid=" . $_SESSION['par_id'] . " AND ach_email='" . $_SESSION['Remail'] . "'";
                 
      if ($connection->query($sql) === TRUE) {
          //  echo "Record updated successfully";
        } else {
        //echo "Error updating record: " . $connection->error;
       }

        $curl = curl_init();
        //echo "start api";
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
          "merchant_id": "'. $_SESSION['par_code'] . '",
          "action": "login",
          "grant_type": "password",
          "email": "' . $_SESSION['Remail'] . '",
          "password": "'. substr($_SESSION['TlocAcct'], 0, 8) . '",
          "push_token": ""
      }',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
        ),
      ));
      
          $response = curl_exec($curl);
         // echo $response;
          curl_close($curl);
        
          $tokan;
          //$json = file_get_contents($response);
          // Converts it into a PHP object
          $data = json_decode($response, true);
          //echo $data;
            
          if ($data['success']) {
              $_SESSION['name'] = $data['data']['user_summary']['profile']['full_name'];
              $_SESSION['phonenumber'] = $data['data']['user_summary']['profile']['phone_number'];
              $_SESSION['email'] = $data['data']['user_summary']['profile']['email'];
              $_SESSION['userstatus'] = $data['data']['user_summary']['user_status'];
              $_SESSION['token'] = $data['data']['token']['access_token'];
              $_SESSION['goldbalance'] = $data['data']['user_summary']['gold_balance'];
              $_SESSION['ic'] = $data['data']['user_summary']['profile']['mykad_number'];
              $_SESSION['address_line_1'] = $data['data']['user_summary']['profile']['address_line_1'];
              $_SESSION['address_line_2'] = $data['data']['user_summary']['profile']['address_line_2'];
              $_SESSION['postcode'] = $data['data']['user_summary']['profile']['postcode'];
              $_SESSION['city'] = $data['data']['user_summary']['profile']['city'];
              $_SESSION['state'] = $data['data']['user_summary']['profile']['state'];
              $_SESSION['pin_set'] = $data['data']['user_summary']['pin_set'];
              $_SESSION['pin_set'] = $data['data']['user_summary']['pin_set'];
              $_SESSION['nok_full_name'] = $data['data']['user_summary']['profile']['nok_full_name'];
              $_SESSION['nok_phone'] = $data['data']['user_summary']['profile']['nok_phone'];
              $_SESSION['bank_acc_name'] = $data['data']['user_summary']['profile']['bank_acc_name'];
              $_SESSION['bank_acc_number'] = $data['data']['user_summary']['profile']['bank_acc_number'];
              $_SESSION['occupation_category_id'] = $data['data']['user_summary']['profile']['occupation_category_id'];
              $_SESSION['occupation_subcategory_id'] = $data['data']['user_summary']['profile']['occupation_subcategory_id'];
              //nok_full_name
              //pin_set
              //accountcode

              $_SESSION['login'] = "success";

              //set pin

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
              "merchant_id": "'. $_SESSION['par_code'].'",
              "action": "pin_update",
              "current_pin": "",
              "new_pin": "'. $_SESSION['Rpin'] . '",
              "confirm_new_pin": "'. $_SESSION['Rpin']  . '"
          }',
            CURLOPT_HTTPHEADER => array(
              'Authorization: Bearer ' . $_SESSION['token'] ,
              'Content-Type: application/json',
              'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
            ),
          ));
          
              $response = curl_exec($curl);
            //  echo $response;
              logaccess($_SESSION['email'],"updatepin",$response);
              curl_close($curl);
              $data = json_decode($response, true);
            
              if ($data['success']) {

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
                "action": "profile_update",
                "address_line_1": "'. $_SESSION['Raddress1']  . '",
                "address_line_2": "'. $_SESSION['Raddress2'] .'",
                "postcode": "'. $_SESSION['Rpostcode'] . '", 
                "city": "'. $_SESSION['Rcity'].'",
                "state": "'. $_SESSION['Rstate'] .'",
                "nok_full_name": "MGold",
                "nok_phone": "+60123393399",
                "nok_email": "",
                "nok_address": "",
                "nok_relationship": "",
                "occupation_category_id": '. $_SESSION['occupation_category_id'] . ',
                "occupation_subcategory_id": "'.  $_SESSION['occupation_subcategory_id'] . '",
                "referral_salesperson_code": "",
                "pin": "'. $_SESSION['Rpin']  .'"
            }',
              CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $_SESSION['token'],
                'Content-Type: application/json',
                'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
              ),
            ));
            
                $response = curl_exec($curl);
              //  echo $response;
                logaccess($_SESSION['email'],"updateaddress",$response);
                curl_close($curl);
            //bank account
            $data = json_decode($response, true);
              if ($data['success']) {
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
                    "action": "bank_account_update",
                    "bank_id": '.$_SESSION['Rbank'].',
                    "bank_acc_name": "'.  $_SESSION['Runame'] . '",
                    "bank_acc_number": "'. $_SESSION['Raccountnumber'] . '",
                    "pin": "'. $_SESSION['Rpin'] . '"
                }',
                  CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $_SESSION['token'],
                    'Content-Type: application/json',
                    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
                  ),
                ));
                
                $response = curl_exec($curl);
               // echo $response;
                curl_close($curl);
                $data = json_decode($response, true);
                if ($data['success']) {

                }
                              else{
                             
                              }
              }else{
                $error = $data['error_message'];
               // echo '<script>alert("'. $error . '")</script>';
               echo '
               <script type="text/javascript">
               
               $(document).ready(function(){
               
                 Swal.fire({
                   
                   title: "Error!",
                   text: "'.  $error . '",
                   icon: "error",
                   confirmButtonText: "Ok"
                 });
               
               </script>
               ';
              }
            
           
              }else{
                $error = $data['error_message'];
               // echo '<script>alert("'. $error . '")</script>';
               echo '
               <script type="text/javascript">
               
               $(document).ready(function(){
               
                 Swal.fire({
                   
                   title: "Error!",
                   text: "'.  $error . '",
                   icon: "error",
                   confirmButtonText: "Ok"
                 });
               
               </script>
               ';
              }
              //update address
    
             
          }
    }else{
      $error = $data['error_message'];
      echo '
<script type="text/javascript">

$(document).ready(function(){

  Swal.fire({
    
    title: "Error!",
    text: "'.  $error . '",
    icon: "error",
    confirmButtonText: "Ok"
  });

</script>
';
      $_SESSION['M1'] = "Something Wrong!";
      $_SESSION['M2'] = $error;
      $_SESSION['M3'] ="Please Register again!";
    //  header('location: /register.php');
    }
}

?>