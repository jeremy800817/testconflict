<?php
namespace GOPAYZ;
require __DIR__ . "/../vendor/autoload.php";
    include('config/db.php');
    include('log.php');
    global $seamlessloginemail, $seamlessloginpw, $connection;
    $_SESSION['login'] = "no";
    $_SESSION['APIURL'] = $api;
    $_SESSION['McashApi'] = $McashApi;
    $sql = "SELECT * From partner WHERE par_name = '" . $par_name. "'";
    $query = mysqli_query($connection, $sql);
    $rowCount = mysqli_num_rows($query);
    $_SESSION['displayname'] = "Guest";
   
    //echo 'try ' . $_SESSION['displayname'];  
   
    // If query fails, show the reason 
    if(!$query){
      die("SQL query failed: " . mysqli_error($connection));
    }

        if($rowCount <= 0) {
            $accountNotExistErr = '<div class="alert alert-danger">
                    PARTNER NOT EXITS.
                </div>';
              
        } else {
            // Fetch user data and store in php session
            while ($row = mysqli_fetch_array($query)) {
                $par_code            = $row['par_code'];
                $par_id            = $row['par_id'];
            }
        }
        $_SESSION['par_code'] = $par_code;
        $_SESSION['par_id'] = $par_id;
        
    if (isset($_GET['channel']) || isset($_SESSION['guestdefault'])) {
     
      if (isset($_GET['channel'])) {
          $payload = $_GET['payload'];
          $channel = $_GET['channel'];
      }else if (isset($_SESSION['payload'])) {
        $payload = $_SESSION['payload'];
        $channel = $_SESSION['channel'];
      }else{
        $payload="no";
        $channel="no";
      }
           
      if ($payload !="no") {
          $_SESSION['gopayztoken'] = $gopayztoken . $payload;
          $_SESSION['payload'] = $payload;
          $_SESSION['channel'] = $channel;
          $_SESSION['guestdefault'] = "Yes";
      }

      if($_SESSION['guestdefault']="Yes")
      {
        unset($_SESSION['guestdefault']);
      }
    
    
     if ($channel == "MCASH") {
        
         $data = callMcashApi($McashKey,$payload);
        
         $status =  $data['status'];
         $message =  $data['message'];
         if ($status =="1") {
          $_SESSION['TlocAcct'] = $data['data']['mcashUniqueId']; //unique acccount number
          
             $_SESSION['TidNo'] = $data['data']['idNo'];
         
             $_SESSION['Temail'] =  $data['data']['email'];
         
             $_SESSION['TtelHp'] =  $data['data']['hp'];
         
             
             $_SESSION['Tname'] =  $data['data']['name'];
          
             $_SESSION['Tdob'] =  $data['data']['dob'];
         
             $_SESSION['ThomeAddress1'] =  $data['data']['homeAddress'];
        
             $_SESSION['ThomeState'] = $data['data']['state'];
           
             $_SESSION['ThomeZip'] = $data['data']['zip'];
         
             $_SESSION['TkycInd'] = "1";
            
             $phonenum = "+" . $_SESSION['TtelHp'];
         
             $sql = "SELECT * From myaccountholder WHERE ach_partnercusid ='" . $_SESSION['TlocAcct'] . "'";
             $query = mysqli_query($connection, $sql);
             $rowCount = mysqli_num_rows($query);
        
             // If query fails, show the reason
              
             if ($rowCount <= 0) {
                 //
             } else {
                 // Fetch user data and store in php session
                 while ($row = mysqli_fetch_array($query)) {
                     $_SESSION['Temail']          = $row['ach_email'];
                 }
             }
           
             // Use MCash unique id as password
             login($_SESSION['Temail'], substr($_SESSION['TlocAcct'], 0, 8));
         } else {
             $_SESSION['Temail'] = "nouser";
             $_SESSION['TidNo'] = "Asdf1234";
             $_SESSION['login'] = "nodirect";
              //  login('jeff@silverstream.my', 'Asdf1234');
             login($_SESSION['Temail'], $_SESSION['TidNo']);
         }
     }
    
  }else if(isset($_SESSION['name'])){
    if($_SESSION['email'] == $defaultlogin )
    {
      login($_SESSION['email'], $defaultpw );
    }else{

      if (0 < strlen($_SESSION['TlocAcct'])) {
        login($_SESSION['email'], substr($_SESSION['TlocAcct'], 0, 8));
      } else {
        login($_SESSION['email'], $_SESSION['ic']);
      }
    }
    $_SESSION['login'] = "success";

  }else if(isset($_GET['code']))
  {
   
    if ($_GET['code'] == 'silverstream') {
        $uname = $defaultlogin;
        $pw = $defaultpw ;
        $_SESSION['login'] = "success";
        login($uname, $pw);
       
    }
  }
  else{
  //  echo "don't get isset";
   $uname = "nouser";
   $pw = $defaultpw;
    $_SESSION['login'] = "nodirect";
       login($uname, $pw);
     
      }
 


function login(string $uname, string $password)
{
  
//Save string to log, use FILE_APPEND to append.
  
      
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
      "email": "' . $uname . '",
      "password": "' . $password . '",
      "push_token": ""
  }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
      
    ),
  ));
  
        $response = curl_exec($curl);
  
        curl_close($curl);
        //echo $response;
       
        //$json = file_get_contents($response);
        // Converts it into a PHP object
        $data = json_decode($response, true);
        //echo $data;
        //$counters = $data['data']['token'];
        logaccess($uname , "login", $response );
        if ($data['success']) {
            $_SESSION['name'] = $data['data']['user_summary']['profile']['full_name'];
            $_SESSION['phonenumber'] = $data['data']['user_summary']['profile']['phone_number'];
            $_SESSION['email'] = $data['data']['user_summary']['profile']['email'];
            $_SESSION['userstatus'] = $data['data']['user_summary']['user_status'];
            $_SESSION['token'] = $data['data']['token']['access_token'];
            $_SESSION['goldbalance'] = $data['data']['user_summary']['gold_balance'];
            $_SESSION['available_balance'] = $data['data']['user_summary']['available_balance'];
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
            $_SESSION['avgbuyprice'] = $data['data']['user_summary']['avgbuyprice'];
            $_SESSION['currentgoldvalue'] = $data['data']['user_summary']['currentgoldvalue'];
            $_SESSION['totalcostgoldbalance'] = $data['data']['user_summary']['totalcostgoldbalance'];
            $_SESSION['avgcostprice'] = $data['data']['user_summary']['avgcostprice'];
            $_SESSION['diffcurrentpriceprcetage'] = $data['data']['user_summary']['diffcurrentpriceprcetage'];
            //"bank_id"
            $_SESSION['bank_id'] = $data['data']['user_summary']['profile']['bank_id'];
            $_SESSION['bank_acc_name'] = $data['data']['user_summary']['profile']['bank_acc_name'];
            $_SESSION['bank_acc_number'] = $data['data']['user_summary']['profile']['bank_acc_number'];
            $_SESSION['occupation_category_id'] = $data['data']['user_summary']['profile']['occupation_category_id'];
            $_SESSION['occupation_subcategory_id'] = $data['data']['user_summary']['profile']['occupation_subcategory_id'];
            $_SESSION['accountcode'] = $data['data']['user_summary']['profile']['accountcode'];
            $_SESSION['accountcodereferral'] = $data['data']['user_summary']['profile']['accountcodereferral'];
            $_SESSION['tradenow'] =$data['data']['app_config']['can_trade_now'];
            $_SESSION['courierfee'] =$data['data']['app_config']['courierfee'];

            // Min Balance
            $_SESSION['minbalance'] =$data['data']['app_config']['min_balance'];

           if (isset($_SESSION['Temail'])) {
               $_SESSION['login'] = "success";
               $_SESSION['displayname'] =  $_SESSION['name'];
           }
           if($_SESSION['login']="success")
           {
            $_SESSION['login'] = "success";
            $_SESSION['displayname'] =  $_SESSION['name'];
           }

          // if ($data['data']['user_summary']['received_gifts']) {
          //     $_SESSION['received_gifts'] = $data['data']['user_summary']['received_gifts'];
          // } else{
          //   $_SESSION['received_gifts'] = null;
          // }
            //nok_full_name
            //pin_set
            //accountcode
            
           
        } 
        else {
          if ($_SESSION['login'] == "nodirect") {
          
          }
          else{
            $_SESSION['displayname'] = "Guest";
            $_SESSION['login'] = "no";
        
            $_SESSION['p'] = 1;
          }

          $curl = curl_init();
          //   echo "start api";
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
              "merchant_id": "' . $_SESSION['par_code'] .'",
              "action": "login",
              "grant_type": "password",
              "email": "jeff@silverstream.my",
              "password": "Asdf1234",
              "push_token": ""
            }',
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json'
            ),
          ));
        
          $response = curl_exec($curl);
        
          curl_close($curl);
           // echo $response;
           
            //$json = file_get_contents($response);
            // Converts it into a PHP object
          $data = json_decode($response, true);

          if ($data['success']) {
            $_SESSION['token'] = $data['data']['token']['access_token'];
            $_SESSION['tradenow'] =$data['data']['app_config']['can_trade_now'];
            $_SESSION['goldbalance'] = 0;
          }
          else{
            $strErr = "<script src='//cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            $strErr .= "<script>Swal.fire({title: 'Info!',allowOutsideClick: false, text: 'Server is Busy. Please try again',icon: 'info',confirmButtonText: 'Refresh Page'}).then((result) =>{if(result.isConfirmed){location.reload();}});</script>";
            echo $strErr;
          }
           
        }
    
}

function callMcashApi($key, $Mcashtoken)
{

  $hash =$key . $Mcashtoken . 'getUserInfo' . $GLOBALS['McashMerchant'];

  $hash256 = hash('sha256',  $hash);

$hash64 = base64_encode($hash256);

  $curl = curl_init();
     
        //echo "start api";
        curl_setopt_array($curl, array(
    CURLOPT_URL => $GLOBALS['McashApi'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
      "merchant": "' . $GLOBALS['McashMerchant'] . '",
      "action": "getUserInfo",
      "usrToken": "'. $Mcashtoken .'",
      "hash": "'. $hash64 .'"
  }',
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
      
    ),
  ));
  
        $response = curl_exec($curl);
        curl_close($curl);
     
        $data = json_decode($response, true);

        return $data;
}
?>