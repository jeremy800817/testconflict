<?php

Namespace Snap\handler;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */
class defaultloginhandler extends basehandler {

    function getRights($userType = null) {
        return "/all/access";
    }

    /**
     * This method will determine is this particular handler is able to handle the action given.
     * 
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */
    function canHandleAction( $app, $action) {
        return true;
    }

    function encrypt_decrypt($string, $action = 'encrypt')
    {
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'AA74CDCC2BBRT935136HH7B63C27'; // user define private key
        $secret_iv = '5fgf5HJ5g27'; // user define secret key
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    function doAction( $app, $action, $params) {

        if((isset($_REQUEST['hdl']) && $_REQUEST['hdl'] != 'logout') && $app->getUserSession()->isExpired()) {
            echo 'ehre im mdmf';
            $this->log(__CLASS__."::".__FUNCTION__." - Sending json data to client indicating that session has already expired.", SNAP_LOG_DEBUG);
            echo json_encode([ 'success' => false, 'sessionExpired' => true]);
            return;
        }

        if ($action == 'migration_new_login'){
          if($_SESSION['newgtp2login'] && $params['resetpassword'] && $params['resetpasswordconfirm']){
            if(strlen($params['resetpasswordconfirm']) < 8){
              $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
              $title = 'ACE Innovate Asia Berhad';
              $formvalue = 'migration_new_login';
              $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';
              $tags = array('/##key##/','/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/', '/##PLACEHOLDER_RESET##/', '/##PLACEHOLDER_RESET_CONFIRM##/');
              $fillers = array($_SESSION['newgtp2login'], $_SERVER['PHP_SELF'], gettext("Password must more than 8 characters."), gettext('Change Password'), $footer, $title, $devcss, gettext('Submit and Login again'), $formvalue, gettext('New Password'), gettext('Confirm New Password'), );
              $html = file_get_contents(SNAPAPP_DIR . '/client/changepassword.html');
              $html = preg_replace($tags, $fillers, $html);
              return $html;
            }else if ($params['resetpassword'] != $params['resetpasswordconfirm']){
              $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
              $title = 'ACE Innovate Asia Berhad';
              $formvalue = 'migration_new_login';
              $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';
              $tags = array('/##key##/','/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/', '/##PLACEHOLDER_RESET##/', '/##PLACEHOLDER_RESET_CONFIRM##/');
              $fillers = array($_SESSION['newgtp2login'], $_SERVER['PHP_SELF'], gettext("Confirm password do not match."), gettext('Change Password'), $footer, $title, $devcss, gettext('Submit and Login again'), $formvalue, gettext('New Password'), gettext('Confirm New Password'), );
              $html = file_get_contents(SNAPAPP_DIR . '/client/changepassword.html');
              $html = preg_replace($tags, $fillers, $html);
              return $html;
            }else{
              $user = $app->userStore()->searchTable()->select()->where('resettoken', $_SESSION['newgtp2login'])->one();
              $now = new \DateTime("now", $app->getUserTimezone()); 
              $now = $now->format("Y-m-d H:i:s");
              $user->passwordmodifiedon = $now;
              $user->password = array('func' => "SHA2('".$params['resetpasswordconfirm']."', 224)");
              $user->oldpassword = array('func' => "SHA2('".$params['currentpassword']."', 224)");
              $user->resettoken = '';
              $update = $app->userStore()->save($user);
              header('location: /');
              return false;
            }
          }else{
            $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
            $title = 'ACE Innovate Asia Berhad';
            $formvalue = 'migration_new_login';
            $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';
            $tags = array('/##key##/','/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/', '/##PLACEHOLDER_RESET##/', '/##PLACEHOLDER_RESET_CONFIRM##/');
            $fillers = array($_SESSION['newgtp2login'], $_SERVER['PHP_SELF'], gettext("To make sure your account secure, please create a new password to replace the temporary password that you were given."), gettext('Change Password'), $footer, $title, $devcss, gettext('Submit and Login again'), $formvalue, gettext('New Password'), gettext('Confirm New Password'), );
            $html = file_get_contents(SNAPAPP_DIR . '/client/changepassword.html');
            $html = preg_replace($tags, $fillers, $html);
            return $html;
          }
        }

        if($action == 'resetform') {
            $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
            $title = 'ACE Innovate Asia Berhad';
            $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';

            if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
            else $errorMessage = '';
            $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##PLACEHOLDER##/', '/##REQUESTBUTTON##/',);
            $fillers = array($_SERVER['PHP_SELF'], gettext("Enter your registered email and we'll send you a link to get back into your account."), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss,  gettext('Enter Email'), gettext('Submit') );
            // $html = file_get_contents(SNAPAPP_DIR . '/client/forgotpassword.html');
            $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/forgotpasswordmobile.html') : file_get_contents(SNAPAPP_DIR . '/client/forgotpassword.html');
            $html = preg_replace($tags, $fillers, $html);
            return $html;
        }

        if($action == 'submitpassword') {
            $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
            $title = 'ACE Innovate Asia Berhad';
            $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';


            // c is the encryption
            // Required string will be used to check whether it comes from expired
            if($_GET['c']){
              $decrpted_str = $this->encrypt_decrypt($_GET['c'], 'decrypt');
              $origin_c = $_GET['c'];
            }else {
              // get params from previus page
              $decrpted_str =  $this->encrypt_decrypt($params['key'], 'decrypt');
              $origin_c = $params['key'];
            }
            // Check if there is mail
            if($_GET['c']){
              $mail = $this->encrypt_decrypt($_GET['m'], 'decrypt');
              $origin_m = $_GET['m'];
            } else {
              $mail = $this->encrypt_decrypt($params['mail'], 'decrypt');
              $origin_m = $params['mail'];
            }

            // Get Token 
            $output = explode('+', $decrpted_str);

            // Query DB and check
            $user = $app->userStore()->searchTable()->select()->where('email', $mail)->one();

            // Perform check on user input
            // First check: Check if both password match
            // If not same, throw error
            if($params['resetpassword'] != $params['resetpasswordconfirm']){
                // return invalid token
                $formvalue = 'doreset';
                if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
                else $errorMessage = '';
                $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/', '/##KEY##/', '/##MAIL##/',);
                $fillers = array($_SERVER['PHP_SELF'], gettext("Both passwords do not match"), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Click to retry'), $formvalue, $params['key'], $params['mail'] );
                // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = preg_replace($tags, $fillers, $html);
                return $html;
            }

            $tmpRecord = $app->userFactory()->create();
            try {
              if (isset($params['resetpassword']) && strlen($params['resetpassword']) > 0) $tmpRecord->isPasswordValid($params['resetpassword']);
            } catch(\Snap\InputException $e) {
                $fieldErr = 'userpassword';
                $this->log("Error in validating change password info [ new password ]. Error is " . $e->getMessage(), SNAP_LOG_INFO);
                //echo json_encode(['success' => false, 'errmsg' => $e->getMessage(), 'field' => $fieldErr]);
                
                $formvalue = 'doreset';
                $errorMessage = '';
                $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/', '/##KEY##/', '/##MAIL##/',);
                $fillers = array($_SERVER['PHP_SELF'], gettext($e->getMessage()), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Click to retry'), $formvalue, $params['key'], $params['mail'] );
                // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = preg_replace($tags, $fillers, $html);
                return $html;
            }

            $now = new \DateTime("now", $app->getUserTimezone()); 
            $now = $now->format("Y-m-d H:i:s");
            $user->passwordmodified = $now;
            $user->password = array('func' => "SHA2('".$params['resetpassword']."', 224)");
            $user->oldpassword = array('func' => "SHA2('".$params['currentpassword']."', 224)");
            $user->bypassrole = true;
            //$user->username = $params['user_name'];
            //$user->id = $app->getUserSession()->getUserId();
            $update = $app->userStore()->save($user);
            
            if(!$update){
                $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/',);
                $fillers = array($_SERVER['PHP_SELF'], gettext("Internal Error. Unable to proceed updates."), gettext('Forgot Password?'), $footer, $title, $devcss, gettext('Return to login page'), $formvalue );
                // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = preg_replace($tags, $fillers, $html);
                return $html;
            }
            
            // If successful 
            $formvalue = '';
            if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
            else $errorMessage = '';
            $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/',);
            $fillers = array($_SERVER['PHP_SELF'], gettext("Password successfully updated"), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Return to login page'), $formvalue );
            // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
            $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
            $html = preg_replace($tags, $fillers, $html);
            return $html;
            

            // Do password change here
            //$user->password = $params['resetpassword'];
            //$update = $app->userStore()->save($user);

            // Clear old token

            $formvalue = '';
            if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
            else $errorMessage = '';
            $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/',);
            $fillers = array($_SERVER['PHP_SELF'], gettext("Password successfully updated"), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Return to login page'), $formvalue );
            // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
            $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
            $html = preg_replace($tags, $fillers, $html);
            return $html;
        }
        
        if($action == 'doreset') {

            // Init ui components
            $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
            $title = 'ACE Innovate Asia Berhad';
            $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';

            // c is the encryption
            // Required string will be used to check whether it comes from expired
            // Check if there is key
            if($_GET['c']){
              $decrpted_str = $this->encrypt_decrypt($_GET['c'], 'decrypt');
              $origin_c = $_GET['c'];
            }else {
              // get params from previus page
              $decrpted_str =  $this->encrypt_decrypt($params['key'], 'decrypt');
              $origin_c = $params['key'];
            }
            // Check if there is mail
            if($_GET['m']){
              $mail = $this->encrypt_decrypt($_GET['m'], 'decrypt');
              $origin_m = $_GET['m'];
            } else {
              $mail = $this->encrypt_decrypt($params['mail'], 'decrypt');
              $origin_m = $params['mail'];
            }
            
           


            // Get Token 
            $output = explode('+', $decrpted_str);

            // Query DB and check
            $user = $app->userStore()->searchTable()->select()->where('email', $mail)->one();
            // Perform check based on db token and date
            // First check: Check if token is valid
            // If not same, throw error
            if($user->resettoken != $output[0]){
                // return invalid token
                $formvalue = 'resetform';
                $errorMessage = '';
                $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/',);
                $fillers = array($_SERVER['PHP_SELF'], gettext("Token is invalid"), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Click to retry'), $formvalue );
                // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = preg_replace($tags, $fillers, $html);
                return $html;
            }

            // Seocnd check: Check if token is expired
            // Get user time on reset 
            $time=strtotime($user->resetrequestedon->format('Y-m-d H:i:s'));
            //$time_difference = time() - $output[1];
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $app->getUserTimezone());

            //$a = time();
            $current_time = strtotime($now->format('Y-m-d H:i:s'));
            $time_difference = $current_time - $time;
            $min_difference = $time_difference / 60;
            // 15 min window before expiry
            if($min_difference > 15){
                // return time expired
                $formvalue = 'resetform';
                $errorMessage = '';
                $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/',);
                $fillers = array($_SERVER['PHP_SELF'], gettext("Token has expired"), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Click to retry'), $formvalue );
                // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = preg_replace($tags, $fillers, $html);
                return $html;
          
            }
            $url = strtok($_SERVER['HTTP_REFERER'], '?');
            $required_string = substr(strrchr($url, '/'), 1);

            $url_components = parse_url($url);
            $parsed_str = parse_str($url_components['query'], $params);

           // $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
            //$title = 'ACE Innovate Asia Berhad';
            //$devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';

            //if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
            //else $errorMessage = '';
            $errorMessage = '';
            $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##PLACEHOLDER_RESET##/', '/##PLACEHOLDER_RESET_CONFIRM##/', '/##REQUESTBUTTON##/', '/##KEY##/', '/##MAIL##/', );
            $fillers = array($_SERVER['PHP_SELF'], gettext("Enter new password settings"), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Enter New Password'), gettext('Confirm Password'), gettext('Submit'), $origin_c, $origin_m );
            // $html = file_get_contents(SNAPAPP_DIR . '/client/resetpassword.html');
            $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetpasswordmobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetpassword.html');
            $html = preg_replace($tags, $fillers, $html);
            return $html;
        }

        if ($action == 'submitreset') {
           
            try{

                $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
                $now = new \DateTime("now", $app->getUserTimezone());

                $user = $app->userStore()->searchTable()->select()->where('email', $params['resetemail'])->one();
                //print_r($user);

                
                // // Generate Auth Token
                // $token = bin2hex(random_bytes(16));

                // // Convert Auth Token to Sha256()
                // $hashkey = hash('sha256', $token, true);

                // // Save Auth Token
                // $user->resettoken = $hashkey;
            
                // // Save token request time      
                // $user->resetrequestedon = $now;

                // Check
                // if (time < 30 minutes && token match){

                // }


                // Check if User Exists
                if(!$user){
                    //throw new \Exception('Username not found, please input correct username');

                    $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
                    $title = 'ACE Innovate Asia Berhad';
                    $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';

                    if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
                    else $errorMessage = '';
                    $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##PLACEHOLDER##/', '/##REQUESTBUTTON##/',);
                    $fillers = array($_SERVER['PHP_SELF'], gettext("Email associated with user not found, please input correct email."), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss,  gettext('Enter Email'), gettext('Submit') );
                    // $html = file_get_contents(SNAPAPP_DIR . '/client/forgotpassword.html');
                    $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/forgotpasswordmobile.html') : file_get_contents(SNAPAPP_DIR . '/client/forgotpassword.html');
                    $html = preg_replace($tags, $fillers, $html);
                    return $html;
                }

                // generate 16 digit code and save to table
                $code = bin2hex(random_bytes(16));
                $user->resettoken = $code;

                // generate time() and save to table
                $time = $now;
                $user->resetrequestedon = $time;

                // combine 16 digit code + now() and encrypt
                $str = $code."+".$time;
                //$str_encrypt = hash('sha256', $str, true);
                

                // use this encrypt to send email with reset password link
                $str_encrypt = $this->encrypt_decrypt($str, 'encrypt');

                // Encrypt mail
                $mail = $this->encrypt_decrypt($params['resetemail'], 'encrypt');

                $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                //$uriSegments = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
                //$baseUrl = $uriSegments[0];
                //$myBase = dirname($_SERVER['PHP_SELF']);
                //$url = "https://www.xxxx.com/resetpassword?c=".$str_encrypt;

                // Add gtp.php?action=doreset to url attributes
                //$amended_link = $actual_link."?action=doreset"
                $url = $actual_link."?action=doreset&c=".$str_encrypt."&m=".$mail;


                // Do saving, 
                // Save 


                // Check if save is working
                $update = $app->userStore()->save($user);
                if(!$update){
                    throw new \Exception('Internal Error. Unable to proceed updates.');
                }
                // Send out email
                $emailSubject = 'GTP Forgot Password';
                //$bodyEmail = "We heard you need a password reset. Click the link below and\n you'll be redirected to a secure site which you can set a\n 
                //new password\n\n<a href=".$url.">link</a>";

                $bodyEmail = "<body class='' style='background-color: #f6f6f6; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;'>".
                
                "<span class='preheader' style='color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;'>This is preheader text. Some clients will show this text as a preview.</span>".
                "<table border='0' cellpadding='0' cellspacing='0' class='body' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #f6f6f6;'>".
                  "<tr>".
                    "<td style='font-family: sans-serif; font-size: 14px; vertical-align: top;'>&nbsp;</td>".
                    "<td class='container' style='font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 580px; padding: 10px; width: 580px;'>".
                      "<div class='content' style='box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;'>".
            
                       
                        "<table class='main' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px;'>".
            
                          
                          "<tr>".
                            "<td class='wrapper' style='font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;'>".
                              "<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;'>".
                                "<tr>".
                                  "<td style='font-family: sans-serif; font-size: 14px; vertical-align: top;'>".
                                    "<p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>Hi $user->name,</p>".
                                    "<p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>You are getting this mail since you have forgotten your password if you did not request please ignore this mail.</p>".
                                    "<p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>To reset your password please click the box below.</p>".
                                    "<table border='0' cellpadding='0' cellspacing='0' class='btn btn-primary' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; box-sizing: border-box;'>".
                                      "<tbody>".
                                        "<tr>".
                                          "<td align='left' style='font-family: sans-serif; font-size: 14px; vertical-align: top; padding-bottom: 15px;'>".
                                            "<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;'>".
                                              "<tbody>".
                                                "<tr>".
                                                  "<td style='font-family: sans-serif; font-size: 14px; vertical-align: top; background-color: #3498db; border-radius: 5px; text-align: center;'> <a href='$url' target='_blank' style='display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;'>Click here to Reset Password</a> </td>".
                                                "</tr>".
                                              "</tbody>".
                                            "</table>".
                                          "</td>".
                                        "</tr>".
                                      "</tbody>".
                                    "</table>".
                                    "<p style='font-family: sans-serif; font-size: 14px; font-weight: normal; margin: 0; Margin-bottom: 15px;'>Should you require further assistance please reach us at  <a href='mailto:helpdesk@ace2u.com' style='color: #0008B; font-size: 12px; text-align: center; text-decoration: none;'>helpdesk@ace2u.com</a></p>".
                                  "</td>".
                                "</tr>".
                              "</table>".
                            "</td>".
                          "</tr>".
            
                       
                        "</table>".
            
                    
                        "<div class='footer' style='clear: both; Margin-top: 10px; text-align: center; width: 100%;'>".
                          "<table border='0' cellpadding='0' cellspacing='0' style='border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;'>".
                            "<tr>".
                              "<td class='content-block powered-by' style='font-family: sans-serif; vertical-align: top; padding-bottom: 10px; padding-top: 10px; font-size: 12px; color: #999999; text-align: center;'>
                                Powered by <a href='http://htmlemail.io' style='color: #999999; font-size: 12px; text-align: center; text-decoration: none;'>ACE Innovate Asia Berhad</a>".
                              "</td>".
                            "</tr>".
                          "</table>".
                        "</div>".
                        
            
                      
                      "</div>".
                    "</td>".
                    "<td style='font-family: sans-serif; font-size: 14px; vertical-align: top;'>&nbsp;</td>".
                  "</tr>".
                "</table>".
              "</body>";

                // Generate URL for body 
                $mailer = $app->getMailer();
                // user input
                $receiver = $params['resetemail'];
                $mailer->addAddress($receiver);
        
                $mailer->Subject = $emailSubject;
                
                $mailer->Body    = $bodyEmail;

                $mailer->SMTPDebug = 0;  

                $mailer->IsHTML(true); 
                $mailer->setFrom($app->getConfig()->{'snap.mailer.senderemail'}, 'ACE Informant');
        
                $mailer->send();
               

                // $sendEmail = $app->apiManager()->sendNotifyEmail($bodyEmail, $emailSubject, $user->email);

                // return time expired
                $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
                $title = 'ACE Innovate Asia Berhad';
                $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';

          
                $formvalue = '';
                $errorMessage = '';
                $tags = array('/##PHPSELF##/', '/##MESSAGE##/', '/##FORGOTPASSWORDLINK##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##REQUESTBUTTON##/', '/##FORMVALUE##/',);
                $fillers = array($_SERVER['PHP_SELF'], gettext("Please check registered email for reset link"), gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('Return to login page'), $formvalue );
                // $html = file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/resetdisplaymobile.html') : file_get_contents(SNAPAPP_DIR . '/client/resetdisplay.html');
                $html = preg_replace($tags, $fillers, $html);
                return $html;
                //$sendEmail = $this->sendNotifyEmail($bodyEmail,$emailSubject);

            }catch (\Exception $e) {
                throw $e;
                //echo "Message :".$e->getMessage();
            }
           

            return 'doreset';
        }

        if($action == 'invalid_signon') {
          $salt = $app->getUserSession()->getSalt();
          $sessionid = session_id();
          //$forgotPasswdLink = '?action=resetform';
          $forgotPasswdLink = '?action=submitreset';

          $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
          $title = 'ACE Innovate Asia Berhad';
          $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';

          //if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
          $errorMessage = 'Your account has been suspended. Please contact administrator.';
          $tags = array('/##PHPSELF##/', '/##SALT##/', '/##USERNAME##/', '/##PASSWORD##/', '/##SUBMIT##/', '/##FORGOTPASSWORDLINK##/', '/##FORGOTPASSWORD##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##FORMEMAIL##/' , '/##REQUESTBUTTON##/');
          $fillers = array($PHP_SELF, $salt, gettext('Username'), gettext('Password'), gettext('Login'), $forgotPasswdLink, gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('E-mail'), gettext('Submit'));
          // $html = file_get_contents(SNAPAPP_DIR . '/client/forgotpassword.html');
          $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/loginmobile.html') : file_get_contents(SNAPAPP_DIR . '/client/login.html');
          $html = preg_replace($tags, $fillers, $html);
          return $html;
        }

        $salt = $app->getUserSession()->getSalt();
        $sessionid = session_id();
        //$forgotPasswdLink = '?action=resetform';
        $forgotPasswdLink = '?action=submitreset';
        // $footer = 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad';
        $footer = $app->getConfig()->{'snap.environtment.development'} ? 'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad V'. $app->getConfig()->{'version'} . ' - UAT':  'All Rights Reserved &copy; '.date('Y').' ACE Innovate Asia Berhad V'. $app->getConfig()->{'version'} . ' - Prod';
        $title = 'ACE Innovate Asia Berhad';
        $devcss = $app->getConfig()->development ? '<link rel="stylesheet" href="src/resources/css/devcss.css">' : '';

        if($params['username'] || $params['password']) $errorMessage = gettext('Invalid username or password');
        else $errorMessage = '';
        $tags = array('/##PHPSELF##/', '/##SALT##/', '/##USERNAME##/', '/##PASSWORD##/', '/##SUBMIT##/', '/##FORGOTPASSWORDLINK##/', '/##FORGOTPASSWORD##/', '/##FOOTER##/', '/##TITLE##/', '/##ERRORMESSAGE##/', '/##DEVCSS##/', '/##FORMEMAIL##/' , '/##REQUESTBUTTON##/');
        $fillers = array($PHP_SELF, $salt, gettext('Username'), gettext('Password'), gettext('Login'), $forgotPasswdLink, gettext('Forgot Password?'), $footer, $title, $errorMessage, $devcss, gettext('E-mail'), gettext('Submit'));
        $loginPage = '/client/login.html';
        if ($app->getConfig()->{'otc.customlogin'}){
          $loginPage = '/client/login_'.strtolower($app->getConfig()->PROJECTBASE).'.html';
        }else{
          $loginPage = '/client/login.html';
        }
      
        $html = (\Snap\Common::isMobileBrowser()) ? file_get_contents(SNAPAPP_DIR . '/client/loginmobile.html') : file_get_contents(SNAPAPP_DIR . $loginPage);
        $html = preg_replace($tags, $fillers, $html);
        return $html;
    }
}
