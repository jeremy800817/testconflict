<head>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<?php
    include('log.php');
    if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }
    if (isset($_POST)) {
        $alertprice = $_POST['price'];
        $alerttype = $_POST['alerttype'];

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
                "action": "new_price_alert",
                "price": '. $alertprice .' ,
                "type": "'. $alerttype .'",
                "remark": ""
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $_SESSION['token'],
                'Content-Type: application/json',
                'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;
        logaccess($_SESSION['email'],"addalertprice",$response);
        $data = json_decode($response, true);
        if ($data['success']) {
            header("location: ../price-alert.php");
        }
        else{
            $_SESSION['pa_error_message'] = substr($data["error_message"],strpos($data['error_message'],':')+1);
            header("location: ../price-alert.php?err=1");
        }
    }
    else{
        echo "some error";
    }
?>