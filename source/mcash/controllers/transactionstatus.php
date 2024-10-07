<?php

include_once('log.php');
include_once('config/db.php');
include_once('common.php');

if (isset($_GET['channel'])) {
    $channel = $_GET['channel'];
    $payload = $_GET['payload'];
    $status  = $_GET['status'];

    if ($channel == "MCASH") {

        $_SESSION['payload'] = $payload;

        if ($status == "SUCCESSFUL" || $status == "1") {
            // Using forwardCall
            // checkPayment($reqRefno,$specialapi);

            // $_SESSION['accountcode'] = $paymentId;
            $_SESSION["message1"] = $lang['ThankYou'];
            $_SESSION["message"] = $lang['PaymentSuccessMsg'];
            if ($_SESSION['lastaction']=="paybywallet") {
                $_SESSION['langReceipt'] = $lang['CustomerBuy'];
                $_SESSION['langGpurchase'] = $lang["GoldPurchase"];
                $_SESSION['langPprice'] = $lang["PurchasePrice"];
                $_SESSION['langFees'] = $lang["TransactionFee"];
                $_SESSION['langTtbuy'] = $lang["TOTALBUY"];
                $_SESSION['u_price'] = $_SESSION['unit_price'];
                $_SESSION['w_fee'] = $_SESSION['wallet_fee'];
                $_SESSION['o_total'] = $_SESSION['original_total'];
                $_SESSION['t_wallet'] = $_SESSION['total_wallet'];
            }elseif($_SESSION['lastaction']=="walletconvertion"){
                $_SESSION['langReceipt'] = $lang['ConvertGold']; 
                $_SESSION['langGpurchase'] = $lang["ConvertGold"];
                $_SESSION['langPprice'] = $lang["Product"];
                $_SESSION['langFees'] = $lang["ConversionFee"];
                $_SESSION['langTtbuy'] = $lang["TotalConversion"];
                $_SESSION['weight'] = $_SESSION['quantity'];
                $_SESSION['u_price'] = $_SESSION['product'];
                $_SESSION['w_fee'] = $_SESSION['conversion_fee'];
                $_SESSION['o_total'] = $_SESSION['total_fee'];
                $_SESSION['t_wallet'] = $_SESSION['total_fee'];
            }elseif ($_SESSION['lastaction'] =="creditbywallet") {
                $_SESSION['langReceipt'] = $lang['CustomerSell'];
                $_SESSION['langGpurchase'] = $lang['GOLDSOLD'];
                $_SESSION['langPprice'] = $lang['SELLPRICE'];
                $_SESSION['langFees'] = $lang["TransactionFee"];
                $_SESSION['langTtbuy'] = $lang['TOTALSELL'];
                $_SESSION['u_price'] = $_SESSION['unit_price'];
                $_SESSION['w_fee'] = $_SESSION['wallet_fee'];
                $_SESSION['o_total'] = $_SESSION['original_total'];
                $_SESSION['t_wallet'] = $_SESSION['total_wallet'];
            }else{
                //
            }
        }else if($status == "FAILED" || $status == "0") {
            $_SESSION["message1"] = $lang['Sorry'];
            $_SESSION["message"] = $lang['YourPaymentFail'];
        }else if($status =="DUPLICATED"){
            $_SESSION["message1"] = $lang['Sorry'];
            $_SESSION["message"] = $lang['DuplicatePayment'];
        }else if($status =="INSUFFICIENT FUND")
        {
            $_SESSION["message1"] = $lang['Sorry'];
            $_SESSION["message"] = $lang['InsufficientFundMsg'];
        }else{
            $_SESSION["message1"] = $lang['Pending'];
            $_SESSION["message"] = $lang['TransactionPendingMsg'];
        }
    }
}else{
    $_SESSION["message1"] = $lang['NoTransactionDetected'];
    $_SESSION["message"] = $lang['NoTransactionDetectedMsg'];
}

?>