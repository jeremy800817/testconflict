<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Dianah <dianah@silverstream.my>
* @version 1.0
* @created 16-Aug-2022
*/

namespace Snap\api\wallet;

use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version1\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\Version1\AsymmetricSecretKey;
use ParagonIE\Paseto\Protocol\Version1;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\api\exception\GeneralException;
use Snap\api\payout\BasePayout;
use Snap\api\payout\WavPayPayout;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyPaymentDetail;


class WavPay extends BaseWallet
{
    // Production endpoints
    protected const ENDPOINT_PAYMENT = "https://mapi.wavpay.net/api/merchant_web/payment_initial";
    protected const ENDPOINT_CHECK_PAYMENT = "https://mapi.wavpay.net/api/merchant_web/query";
    protected const ACTION_BUY = "buy";

    protected const CURRENCY_MYR = 'MYR';

    protected const STATUS_SUCCESS = 'SUCCESS';
    protected const STATUS_FAILED = 'FAILED';
    protected const STATUS_PENDING = 'PENDING';

    protected const PAYMENT_METHOD = 'WALLET';

    protected function __construct($app)
    {
        parent::__construct($app);
        
        $this->merchantAccId    = $app->getConfig()->{'mygtp.wavpay.merchantidsetbywavpay'};
        $this->secretkey        = $app->getConfig()->{'mygtp.wavpay.secretkey'};
        //$this->merchantId      = $app->getConfig()->{'mygtp.wavpay.merchant'};
        $this->callbackUrl      = $app->getConfig()->{'mygtp.wavpay.callbackurl'};
        $this->redirectUrl      = $app->getConfig()->{'mygtp.wavpay.redirecturl'};

        /*if (! $this->merchantId) {
            throw new \Exception("MCash merchant ID not provided");
        }*/

        if (! $this->merchantAccId) {
            throw new \Exception("WavPay merchant ID not provided");
        }

        if (! $this->callbackUrl) {
            throw new \Exception("WavPay callback URL not provided");
        }

        if (! $this->redirectUrl) {
            throw new \Exception("WavPay redirect URL not provided");
        }
    }

    /**
     * Method to handle direct/indirect returns & callbacks from client
     * 
     * @param array $params         Params decoded by handler
     * @param string $postBody      Body of POST request if any
     */
    public function handleRequest($params, $postBody)
    {
        $this->log("------ Receive post body as ".$postBody." -----", SNAP_LOG_DEBUG);
        $decodedData = json_decode($postBody,true);
        //Array ( [postAuthCode] => test [postOrderId] => test2 [postUserId] => test3 [postStatus] => test4 [postMerchantId] => test5 [postSecureHash] => 9efb5555d258f592f0c50b1cf5d51e658af9c3ec58887b04d367b4327af17063 [walletgateway] => WAVPAYORDERID [refno] => P202209011500004 ) - redirectlink

        /*check securehash*/
        /*fix for redirect & callback different parameter name*/
        if(!isset($decodedData['postAuthCode'])) {
            $callback = true;
            $decodedData['postAuthCode'] = $decodedData['authCode'];
        }
        if(!isset($decodedData['postOrderId'])) $decodedData['postOrderId'] = $decodedData['orderId'];
        //if(isset($decodedData['userId'])) $decodedData['postUserId'] = $decodedData['userId'];
        if(!isset($decodedData['postStatus'])) $decodedData['postStatus'] = $decodedData['status'];
        if(!isset($decodedData['postMerchantId'])) $decodedData['postMerchantId'] = $decodedData['merchantId'];
        if(!isset($decodedData['postSecureHash'])) $decodedData['postSecureHash'] = $decodedData['secureHash'];
        if(!isset($decodedData['postPaymentMethod'])) $decodedData['postPaymentMethod'] = self::PAYMENT_METHOD;
        if(!isset($decodedData['postUserId'])) {
            /*to get user id*/
            $getPayment = $this->app->mypaymentdetailStore()->getByField('gatewayrefno', $decodedData['postOrderId']);
            $userDataFromToken    = $this->verifySignedData($getPayment->token);   
            $decodedLog = json_encode($userDataFromToken);

            $this->log("------ Get user wallet details from token for callback wavpay ".$decodedLog." -----", SNAP_LOG_DEBUG);
            /*end getting user id*/
            $decodedData['postUserId'] = $userDataFromToken['channelUserId'];
        }
        /*fix end*/
        $checkHash = $this->verifyPaymentResponse($decodedData,$callback);

        if($checkHash){
            $paymentRefNo = $decodedData['refno'];
            $gatewayRefNo = $decodedData['postOrderId'];

            if (0 < strlen($paymentRefNo)) {
                $payment = $this->app->mypaymentdetailStore()->getByField('paymentrefno', $paymentRefNo);
                $errMsgTag['paymentrefno'] = $paymentRefNo;
            } else {
                $payment = $this->app->mypaymentdetailStore()->getByField('gatewayrefno', $gatewayRefNo);
                $errMsgTag['gatewayrefno'] = $gatewayRefNo;
            }

            if (! $payment) {
                $this->logDebug(__METHOD__."(): No payment found for response {". json_encode($params)."}");
                throw MyGtpWalletGatewayPaymentNotFound::fromTransaction([], $errMsgTag);
            }

            $this->logApiResponse(json_encode($decodedData), $payment);

            $signedData = $decodedData['postSecureHash'];
            $payment->signeddata = $signedData;
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['signeddata']);

            if($payment) {
                $this->processCallback($decodedData);
            }

            return ['success' => true];
        } else {
            throw new \Exception("Something wrong. Please contact our service to inform the issue.");
        }
    }


    /**
     * Update the gateway request date and status
     * 
     * @param MyAccountHolder $accHolder            The account holder
     * @param MyPaymentDetail $payment              The payment detail
     * 
     * @return bool
     */
    function initializeTransaction($accHolder, $payment)
    {        
        $this->log("------ WavPay prepare transaction payment location -----", SNAP_LOG_DEBUG);
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        //fortest purpose
        //$token = 'v1.public.eyJyZXF1ZXN0RGF0ZSI6IjIwMjIwODAzMTcyODU3IiwiY2hhbm5lbFVzZXJJZCI6IjEwNTMxOTQxMDIiLCJpZFR5cGUiOiJOIiwiaWRObyI6IjgwMDMzMC0wNy01MzE5IiwiZW1haWwiOiJhYmNAY3l6LmNvbSIsInRlbEhwIjoiMTA1MzE5NDEwMiIsImNoYW5uZWwiOiJXQVZQQVkiLCJuYW1lIjoiTEVFIEJPT04gQUlLIiwiY2hhbm5lbExvZ2luVG9rZW4iOnsidHlwZSI6IlN0cmluZyIsImRhdGEiOiIxODgwY2YxNy0zNTRhLTRhN2ItODM1MC1lMGU4NDBiYTAwYWEifX039axNV_KeenC9n7XHzKJyny6-NVUzajFPo1M3KHno_tp79bJzu1fu4GhN7hvLg7NTteP_8JfyoqT37mTGE6YwPmCnOyftdoDyW4kRB6vOdtdN7XhS8eMDHTn5WcVQRrxIDGw2NecocixnE0LbRM-_fJed_E9E_fQjl48uvR8VOXXFfKYFGGh7r24vCPZ9K2Gv6FxppE0jOtuwgFKzZNVvUe_7taGGfbuO6sIVV73MdMUTdiBcnIDiC6g1uqWqWPq-NztMIB0nZXuZDR6ISpHull8aTJVZTq2kAbQpYG3L4YbJhH5CXcVnCcR0URiLQxd6OYieybcQcw-db962nNHz';
        //$decodedData    = $this->verifySignedData($token);    
        $decodedData    = $this->verifySignedData($payment->token);   
        $decodedLog = json_encode($decodedData);

        $this->log("------ Decoded data details ".$decodedLog." -----", SNAP_LOG_DEBUG);

        $sessionid      = $decodedData['channelLoginToken']['data'];     

        $data = [            
            'primaryCustomerEmail'          => $accHolder->email,
            'primaryCustomerPhoneNumber'    => $accHolder->phoneno,
            'ctbPaymentReference'           => $payment->paymentrefno,
            'ctbMerchantAccountIdentifier'  => $this->merchantAccId,
            'payableAmount'                 => number_format(floatval($payment->amount) + floatval($payment->customerfee) + floatval($payment->gatewayfee), 2, '.', ''),
            'paymentCurrency'               => self::CURRENCY_MYR,
            'redirectionUrl'                => $this->redirectUrl.'?payload='.$payment->token.'&refno='.$payment->paymentrefno.'&uuid='.$payment->priceuuid.'&channel=WAVPAY',
            //'redirectionUrl'                => $this->redirectUrl,
            'callbackUrl'                   => $this->callbackUrl,
            'orderDescription'              => $payment->productdesc,
            'userId'                        => $decodedData['channelUserId'],
            'sessionId'                     => $sessionid
        ];

        $formatted = $this->formatData($data);    
        $data['secureHash'] = $this->signData($formatted);
        $testData = json_encode($data);
        $this->log("------ Data details ".$testData." -----", SNAP_LOG_DEBUG);
        $payment->signeddata = $data['secureHash'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['signeddata']);  

        try {
            $client = $this->httpClientFactory();
            $response = $this->doSendCreateTransaction($data, $client);
            $jsonEncodedData = json_encode($data);
            $this->logApiRequest($jsonEncodedData, $payment);
            $this->log("------ Wavpay Create transaction request -----", SNAP_LOG_DEBUG);
            $this->log($jsonEncodedData, SNAP_LOG_DEBUG);
            $this->log("------ Wavpay Create transaction request END -----", SNAP_LOG_DEBUG);
            // Paywall location is returned
            if (200 == $response->getStatusCode()) {
                $paywallLocation = $response->getBody()->getContents();
                $this->log("------ WavPay Create transaction response -----", SNAP_LOG_DEBUG);
                $this->log($paywallLocation, SNAP_LOG_DEBUG);
                $this->log("------ WavPay Create transaction response END -----", SNAP_LOG_DEBUG);

                $dataResponse = json_decode($paywallLocation, true);
                $dataResponse['data']['refno'] = $payment->paymentrefno;
                $dataResponse['data']['details'] = $data;
                $responseStatus = strtoupper($dataResponse['msg']);
                if($responseStatus == 'SUCCESS' && isset($dataResponse['data']['paymentUrl'])){
                    $payment->gatewaystatus = 'PENDING';
                    $payment->status = MyPaymentDetail::STATUS_PENDING_PAYMENT;
                    $payment->location = json_encode($dataResponse['data']);
                    $payment->gatewayrefno = $dataResponse['data']['gatewayReference'];
                    $payment->requestedon = $now;
                    $payment = $this->app->mypaymentdetailStore()->save($payment);
                }

                return $paywallLocation;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $err = $e->getMessage();
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- WavPay Create Transaction Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- WavPay Create Transaction Error End ---", SNAP_LOG_ERROR);
            return false;
        }

        /*$data['location'] = static::ENDPOINT_PAYMENT;        
        $paywallLocation =  json_encode($data);

        $payment->gatewaystatus = 'PENDING';
        $payment->location = $paywallLocation;
        $payment->status = MyPaymentDetail::STATUS_PENDING_PAYMENT;
        $payment->requestedon = $now;
        $payment = $this->app->mypaymentdetailStore()->save($payment);

        $this->log("------ WavPay prepare transaction payment location END -----", SNAP_LOG_DEBUG);
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $payment);*/

        
    }
    
    /**
     * Get status of transaction from WavPay
     * 
     * @param MyPaymentDetail $paymentDetail
     * 
     */
    function getPaymentStatus($paymentDetail)
    {   
        try {     
            $response = $this->doSendGetTransactionStatus($paymentDetail);
            $returnArray = json_decode($response->getBody()->getContents(), true);

            // Check for response status first. The transaction status is inside data array
            if (self::STATUS_SUCCESS === intval($returnArray['status']) && array_key_exists('data', $returnArray)) {
                $this->log("------ WavPay transaction status found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_DEBUG);
                $this->processCallback($returnArray['data']);

                return $returnArray;
            }

            $this->log("------ WavPay transaction status not found for {$paymentDetail->sourcerefno} -----", SNAP_LOG_ERROR);
            return $returnArray;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- WavPay Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- WavPay Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
    }
    
    /**
     * Verifies the callback from wavpay
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response,$callback = false)
    {
        $formattedData = $this->formatResponseData($response,$callback);
        return $this->verifySignedDataResponse($response['postSecureHash'], $formattedData);
    }

    protected function processCallback($response, $skipAppendResponse = false)
    {
        //wavpay status : SUCCESS/PENDING/FAILED
        if(0 < strlen($response['refno'])) $payment = $this->app->mypaymentdetailStore()->getByField('paymentrefno', $response['refno']);
        else $payment = $this->app->mypaymentdetailStore()->getByField('gatewayrefno', $response['postOrderId']);

        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$response['refno']} / {$response['postOrderId']})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$response['refno']} / {$response['postOrderId']})");
        }

        $this->logDebug(__METHOD__."(): Received response from WavPay");
        $this->logDebug(json_encode($response));
        if (!$skipAppendResponse) {
            $payment->remarks = $this->appendResponses($payment->remarks, $response);
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['remarks']);
        }
        $payment->verifiedamount = $payment->amount;
        $payment->gatewaystatus = $response['postStatus'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['verifiedamount', 'gatewaystatus']);
        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Set status of the payment
        $skip = false;
        $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        if (self::STATUS_FAILED == $response['postStatus'] && $state->can(MyPaymentDetail::STATUS_FAILED)) {
            $action = IObservation::ACTION_REJECT;
            $payment->status = MyPaymentDetail::STATUS_FAILED;
            $payment->failedon = $now;
        } else if (self::STATUS_SUCCESS == $response['postStatus']) {
            $action = IObservation::ACTION_CONFIRM;
            $payment->status = MyPaymentDetail::STATUS_SUCCESS;
            $payment->successon = $now;
        } else {
            $this->log("Payment {$payment->sourcerefno} callback received but no action was taken.", SNAP_LOG_ERROR);
            $skip = true;
        }

        if (!$skip) {
            $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'refundedon', 'verifiedamount']);
            $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $response]));
        }
    }

    /**
     * Sign and base64 encode the data to be sent to wavpay
     *
     * @param  string $unsignedData
     * @return string
     */
    protected function signData($unsignedData)
    {
        $this->logDebug(__METHOD__."() Signing data: $unsignedData");
        //return base64_encode(hash('sha256', $unsignedData));
        return hash('sha256', $unsignedData);
    }

    /**
     * Verifies the signed data
     * 
     * @return boolean
     */
    protected function verifySignedDataResponse($signedData, $plainData)
    {        
        //$signedData = base64_encode($signedData);
        $rawSigned = strtoupper(hash('sha256', $plainData));

        $this->log("WavPay - comparing hashing from post hash => ".$signedData." & signeddata => ".$rawSigned , SNAP_LOG_ERROR);
        return $rawSigned === $signedData; //because wavpay passing securehash in uppercase
    }

    protected function verifySignedData($signedData, $plainData = null)
    {
        $this->logDebug(__METHOD__."(): Verifying data signed: $signedData");

        $pubKeyLoc = $this->app->getConfig()->{'mygtp.wavpay.publickeypath'};
        $key = file_get_contents($pubKeyLoc);

        if (0 >= strlen($key)) {
            throw new \Exception("Unable to retrieve public key");
        }

        try {
            $publicKey   = new AsymmetricPublicKey($key, new Version1()); 
            $verifiedData = Version1::verify($signedData, $publicKey);
            return json_decode($verifiedData, true);
        } catch (PasetoException $e) {
            $this->logDebug(__METHOD__."(): " . $e->getMessage());
            throw new \Exception("Unable to verify signed data");
        }
        
    }

    /**
     *  merchantid          merchant provided by wavpay
     *  orderId             User token send by wavpay when open merchant H5 page
     *  paymentmethod       payment type
     *  status              Transaction action: buy / sell
     *  userId              Customer id provide by wavpay
     */
    protected function formatData($arr)
    {              
        //sort the array first
        ksort($arr);  
        $arr[] = $this->secretkey;
        $formatted = implode("|", $arr);
        $this->log("------ Formatted data before hashing ".$formatted." -----", SNAP_LOG_DEBUG);
        return $formatted;
    }

    protected function formatResponseData($arr,$callback)
    {
        $tmpArr = [];
        $tmpArr[] = $arr['postMerchantId'];
        $tmpArr[] = $arr['postOrderId'];
        //if(!$callback) $tmpArr[] = $arr['postPaymentMethod'];
        $tmpArr[] = $arr['postPaymentMethod'];
        $tmpArr[] = $arr['postStatus'];
        $tmpArr[] = $arr['postUserId'];         
        $tmpArr[] = $this->secretkey;        

        $formatted = implode("|", $tmpArr);
        $this->log("------ Formatted data before hashing: After payment ".$formatted." -----", SNAP_LOG_DEBUG);
        return $formatted;
    }

    protected function formatCheckPaymentData($arr)
    {
        $tmpArr = [];
        $tmpArr[] = $this->key;
        $tmpArr[] = $arr['orderId'];
        $tmpArr[] = $arr['action'];        
        $tmpArr[] = $this->merchantId;
        $formatted = implode("", $tmpArr);
        return $formatted;
    }

    /**
     * Appends the responses
     * 
     * @param string    $existing   Existing remarks
     * @param array     $response   Response from wavpay 
     * 
     * @return string   Appended remarks
     */
    protected function appendResponses($existing, $response)
    {
        $currentRemarks = json_decode($existing, true);
        $now = new \DateTime();

        $currentRemarks[$now->format('Y-m-d H:i:s')] = $response;
        return json_encode($currentRemarks);
    }

    /**
     * Does the actual request to wavpay server to get transaction status
     * 
     * @param MyPaymentDetail $paymentDetail        The payment detail     
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatus($paymentDetail)
    {
        $data = [      
            'merchant_id'                 => $this->merchantAccId,
            'merchant_reference_number'   => $paymentDetail->paymentrefno
        ];
        
        $formatted              = $this->formatData($data);
        $data['secure_hash']    = $this->signData($formatted);
        $client                 = $this->httpClientFactory();        
        $url                    = static::ENDPOINT_CHECK_PAYMENT."/".$this->merchantAccId."/".$paymentDetail->paymentrefno."/".$data['secure_hash'];
        $response               = $client->get($url);

        return $response;
    }

    /**
     * Does the actual request to Wavpay server to create transaction
     * 
     * @param array              $data      The data to send
     * @param \GuzzleHttp\Client $client    Guzzle client
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendCreateTransaction($data, $client)
    {
        $url = static::ENDPOINT_PAYMENT;
        $body = json_encode($data);
        $response = $client->post($url, [
            'body'  => $body
        ]);

        /*$response = '{
          "code": 200,
          "msg": "success",
          "data": {
              "gatewayReference": "82642662",
              "paymentUrl": "https://mapi-dev.wavpay.net/api/merchant_web/request/21120001/82642662/b21be0fa-1fd4-47e1-92d6-4e67a93b21f0/39714e68-eade-4cd0-8383-1271b8ad6135/BCEB251CB505F5A3DFC28804D9045EB8F5E41AC2F4BECBE358FF9F403ECB0458"
          }
      }';*/

        return $response;
    }

    protected function httpClientFactory($addToken = true)
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ];

        return new \GuzzleHttp\Client($options);
    }
}


?>