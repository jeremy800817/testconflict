<?php

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if(!isset($_SESSION)) {
      session_set_cookie_params(0);
      session_start();
    }

    $path = realpath(dirname(__FILE__) . '/..');
    // echo $newpath = $path.'/vendor/autoload.php';
    // namespace PKB;
    // require __DIR__ . "/../vendor/autoload.php";
    $newpath1 = $path.'/lib/PHPMailer/src/Exception.php';
    $newpath2 = $path.'/lib/PHPMailer/src/PHPMailer.php';
    $newpath3 = $path.'/lib/PHPMailer/src/SMTP.php';
    $redirectedpath = '/support.php?status=sendemail';

    require $newpath1;
    require $newpath2;
    require $newpath3;
    $success = false;
    
    if($_POST["message"]) {


      // mail("ang@silverstream.my", "Website Inquiry: " . $_POST['subject'],
      
      $to['email'] = "helpdesk@ace2u.com";      
      $to['name'] = "helpdesk@ace2u.com";   
      $subject = "Website Inquiry: " . $_POST['subject'];
      $str = "From: ".$_POST['name']. " (".$_POST['email'].")<br/><br/>" .$_POST['message'];
      $mail = new PHPMailer;
      $mail->IsSMTP();                                     
      $mail->SMTPAuth = true;
      $mail->Host = 'smtp-relay.gmail.com';
      $mail->Port = 587;
      $mail->SMTPSecure = 'tls';
      $mail->name = 'easigold.ace2u.com';
      $mail->SMTPAuth = false;
      // $mail->Username = 'xyz@domainname.com';
      // $mail->Password = 'email account password';
      // $mail->SMTPSecure = 'ssl';
      // $mail->From = 'From Email Address';
      // $mail->FromName = "Any Name";
      $mail->setFrom( 'helpdesk@ace2u.com', "Easigold Inquiry: " .$_POST['subject']);
      $mail->AddReplyTo('helpdesk@ace2u.com', 'NO REPLY'); 
      $mail->AddAddress($to['email'],$to['name']);

      // Add additional addresses
      $mail->AddAddress('jeff@silverstream.my','jeff@silverstream.my');
      $mail->AddAddress('ang@silverstream.my','ang@silverstream.my');
      
      // $mail->Priority = 1;
      // $mail->AddCustomHeader("X-MSMail-Priority: High");
      // $mail->WordWrap = 50;    
      $mail->IsHTML(true);  
      $mail->Subject = $subject;
      $mail->Body    = $str;
      
      if(!$mail->Send()) {
        $_SESSION['emailstatus'] = 0;
        $_SESSION['EmailMsg'] = "Message was not sent <br />PHPMailer Error: " . $mail->ErrorInfo;          
      }else{
        $_SESSION['emailstatus'] = 1;
        $_SESSION['EmailMsg'] = "Message has been sent";
      }
      
      // End
    }

    // header( "location:../index-landing.php?status=sendemail");
    header( "location:".$redirectedpath);
?>