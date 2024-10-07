<?php
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Azam <azam@silverstream.my>
* @version 1.0
* @created 20-10-2021
*/

namespace Snap\api\payout;

use Snap\api\exception\GeneralException;
use Snap\api\exception\MyGtpWalletGatewayPaymentNotFound;
use Snap\App;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;

class MCashPayout extends BasePayout 
{
    protected const ENDPOINT_PAYMENT = "https://www.mcash.my/cross/hfive/paymentv01";
    protected const ENDPOINT_CHECK_PAYMENT = "https://www.mcash.my/cross/hfive/transaction/status";
    protected const ACTION_SELL = "sell";

    protected const STATUS_SUCCESS = 1;
    protected const STATUS_FAILED = 0;
    
    protected $key = null;
    protected $merchantId = null;
    protected $callbackUrl = null;
    protected $redirectUrl = null;

    protected function __construct($app)
    {
        parent::__construct($app);
        
        $this->key         = $app->getConfig()->{'mygtp.mcash.key'};
        $this->merchantId  = $app->getConfig()->{'mygtp.mcash.merchant'};
        $this->callbackUrl = $app->getConfig()->{'mygtp.mcash.callbackurl'};
        $this->redirectUrl = $app->getConfig()->{'mygtp.mcash.redirecturl'};

        if (! $this->merchantId) {
            throw new \Exception("MCash merchant ID not provided");
        }

        if (! $this->callbackUrl) {
            throw new \Exception("MCash callback URL not provided");
        }

        if (! $this->redirectUrl) {
            throw new \Exception("MCash redirect URL not provided");
        }

        if (! $this->key) {
            throw new \Exception("MCash key not provided");
        }
    }

    public function handleResponse($params, $body)
    {
        // MCash Callback        
        $this->logInfo(__METHOD__."() Disbursement callback received: " . $body);
        $params = array_merge($params, json_decode($body, true) ?? []);

        $gatewayRef = $params['orderId'];
        $errMsgTag = ['transactionrefno' => ''];
        if (0 < strlen($gatewayRef)) {
            $disbursement = $this->app->mydisbursementStore()->getByField('transactionrefno', $gatewayRef);
            $errMsgTag['transactionrefno'] = $gatewayRef;
        }
        if (! $disbursement) {
            $this->logDebug(__METHOD__."(): No disbursement found for response {". json_encode($params)."}");
            throw MyGtpWalletGatewayPaymentNotFound::fromTransaction([], $errMsgTag);
        }

        $this->logApiResponse(json_encode($params), $disbursement);

        // Verify signed data from callback
        if (! $this->verifyPaymentResponse($params)) {
            $this->log(__METHOD__. "(): Cannot verify signed data.", SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            throw new \Exception("Signed data verification failed.");
        }
        $this->logDebug(__METHOD__."() Payment response verified.");

        
        $this->processCallback($params);

        return ['success' => true];
    }

    /**
     * Send a payout request to provider
     * 
     * @param MyAccountHolder $accountHolder 
     * @param MyDisbursement $disbursement 
     * @return mixed
     */
    public function createPayout($accountHolder, $disbursement)
    {
        $this->log("------ MCash prepare transaction payment location -----", SNAP_LOG_DEBUG);
        $data = [            
            'merchant'      => $this->merchantId,
            'action'        => self::ACTION_SELL,
            'refId'         => $disbursement->token,
            'orderId'       => $disbursement->transactionrefno,
            'amount'        => number_format(floatval($disbursement->amount) + floatval($disbursement->fee), 2, '.', ''),
            'description'   => $disbursement->productdesc,
            'callbackURL'   => $this->callbackUrl,
            'redirectURL'   => $this->redirectUrl,
        ];
        
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());                
        $formatted = $this->formatData($data);
        $data['hash'] = $this->signData($formatted);
        $data['location'] = static::ENDPOINT_PAYMENT;
        $paywallLocation =  http_build_query($data);
        $disbursement->signeddata = $data['hash'];
        $disbursement->requestedon = $now;
        $disbursement->location = $paywallLocation;
        $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['signeddata', 'requestedon', 'location']);
        
        $this->log("------ MCash prepare transaction payment location END -----", SNAP_LOG_DEBUG);
        $jsonEncodedRequestData = json_encode($data);
        $this->logApiRequest($jsonEncodedRequestData, $disbursement);

        return $paywallLocation;
    }

    /**
     * Handle expired transaction called from the job file after a certain timeout
     *
     * @param  MyDisbursement $disbursement
     * @return void
     */
    public function handleExpiredTransaction($disbursement)
    {        
        $this->log(__METHOD__ . ": No payment response received for {$disbursement->transactionrefno}, notify cancelling order" , SNAP_LOG_ERROR);

        // Notify the listener that the order should be expired / cancelled
        $returnArray = $this->getPayoutStatus($disbursement);
        if (self::STATUS_FAILED === intval($returnArray['status']) || (array_key_exists('data', $returnArray) && self::STATUS_FAILED === intval($returnArray['data']['status']))) {
            $this->notify(new IObservation($disbursement, IObservation::ACTION_CANCEL, $disbursement->status));
        }
    }

    /**
     * Verifies the callback from MCash
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response)
    {
        $formattedData = $this->formatResponseData($response);
        return $this->verifySignedData($response['hash'], $formattedData);
    }

    /**
     * Manually reads payout status from provider's server
     * 
     * @param MyDisbursement $disbursement 
     * 
     * @return array
     */
    public function getPayoutStatus($disbursement)
    {
        try {     
            $response = $this->doSendGetTransactionStatus($disbursement);
            $returnArray = json_decode($response->getBody()->getContents(), true);

            // Check for response status first. The transaction status is inside data array
            if (self::STATUS_SUCCESS === intval($returnArray['status']) && array_key_exists('data', $returnArray)) {
                $this->log("------ MCash transaction status found for Disbursement {$disbursement->transactionrefno} -----", SNAP_LOG_DEBUG);
                $this->processCallback($returnArray['data']);

                return $returnArray;
            }

            $this->log("------ MCash transaction status not found for Disbursement {$disbursement->transactionrefno} -----", SNAP_LOG_ERROR);
            return $returnArray;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse()->getBody()->getContents();
            $this->log("--- GoPayz Get Transaction Status Error Start ---", SNAP_LOG_ERROR);
            $this->log(sprintf("Error code: %d", $e->getCode()), SNAP_LOG_ERROR);
            $this->log(sprintf("URL: %s", $e->getRequest()->getUri()->getHost().$e->getRequest()->getRequestTarget()), SNAP_LOG_ERROR);
            $this->log($body, SNAP_LOG_ERROR);
            $this->log("--- GoPayz Get Transaction Status Error End ---", SNAP_LOG_ERROR);
        }
    }

    /**
     * Sign and base64 encode the data to be sent to MCash
     *
     * @param  string $unsignedData
     * @return string
     */
    protected function signData($unsignedData)
    {
        $this->logDebug(__METHOD__."() Signing data: $unsignedData");
        return base64_encode(hash('sha256', $unsignedData));
    }

    /**
     * Verifies the signed data
     * 
     * @return boolean
     */
    protected function verifySignedData($signedData, $plainData)
    {        
        $signedData = base64_decode($signedData);
        $rawSigned = hash('sha256', $plainData);

        return $rawSigned === $signedData;
    }

    /**
     *  key         Key provided by mcash
     *  orderId     User token send by MCash when open merchant H5 page
     *  amount      Amount of transaction
     *  action      Transaction action: buy / sell
     *  merchant    The merchant id provided by MCash
     */
    protected function formatData($arr)
    {        
        $tmpArr = [];
        $tmpArr[] = $this->key;
        $tmpArr[] = $arr['orderId'];
        $tmpArr[] = number_format($arr['amount'], 2, '.', '');
        $tmpArr[] = $arr['action'];        
        $tmpArr[] = $this->merchantId;
        $formatted = implode("", $tmpArr);
        return $formatted;
    }

    protected function formatResponseData($arr)
    {
        $tmpArr = [];
        $tmpArr[] = $this->key;
        $tmpArr[] = number_format($arr['amount'], 2, '.', '');
        $tmpArr[] = $arr['status'];        
        $tmpArr[] = $arr['orderId'];
        $tmpArr[] = $arr['type'];        
        $tmpArr[] = $this->merchantId;
        $formatted = implode("", $tmpArr);
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

    protected function processCallback($response, $skipAppendResponse = false)
    {
        $transactionRefNo = $response['orderId'];
        $mcashStatus = intval($response['status']);
        $disbursement = $this->app->mydisbursementStore()->getByField('transactionrefno', $transactionRefNo);
        if (! $disbursement) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$transactionRefNo})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$transactionRefNo})");
        }

        $this->logDebug(__METHOD__."(): Received response from MCash");
        $this->logDebug(json_encode($response));

        if (!$skipAppendResponse) {
            $disbursement->remarks = $this->appendResponses($disbursement->remarks, $response);
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['remarks']);
        }

        $initialStatus = $disbursement->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        $skip = false;
        if (self::STATUS_SUCCESS == $mcashStatus) {
            $this->log(__METHOD__ . ": Success payment response received for {$disbursement->transactionrefno}}" , SNAP_LOG_DEBUG);

            $action = IObservation::ACTION_CONFIRM;            
            $disbursement->disbursedon = $now;
            $disbursement->verifiedamount = $response['amount'];
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['disbursedon', 'verifiedamount', 'gatewayrefno', 'remarks']);
            
            $this->log(__METHOD__ . ": Success payment response received for {$transactionRefNo}, code {$mcashStatus}" , SNAP_LOG_DEBUG);
        } elseif (self::STATUS_FAILED == $mcashStatus) {            
            $this->log(__METHOD__ . ": Failed payment response received for {$transactionRefNo}, code {$mcashStatus}" , SNAP_LOG_ERROR);
            $action = IObservation::ACTION_CANCEL;
        } else {
            $this->log("Disbursement {$disbursement->transactionrefno} callback received but no action was taken.", SNAP_LOG_ERROR);
            $skip = true;
        }                

        if (!$skip) {
            $this->notify(new IObservation($disbursement, $action, $initialStatus, ['response' => $response]));
        }
    }

    /**
     * Appends the responses
     * 
     * @param string    $existing   Existing remarks
     * @param array     $response   Response from MCash 
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
     * Does the actual request to MCash server to get transaction status
     * 
     * @param MyDisbursement $disbursement        The disbursement object
     * 
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function doSendGetTransactionStatus($disbursement)
    {
        $data = [            
            'merchant' => $this->merchantId,
            'orderId'  => $disbursement->transactionrefno,
            'action'   => self::ACTION_SELL,
        ];
        
        $formatted    = $this->formatCheckPaymentData($data);
        $data['hash'] = $this->signData($formatted);
        $client       = $this->httpClientFactory();        
        $url          = static::ENDPOINT_CHECK_PAYMENT;
        $response     = $client->post($url, ['form_params' => $data]);

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