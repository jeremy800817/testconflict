<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2022
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

use Snap\App;
use Snap\ICliJob;
use \Snap\object\partner;
use \Snap\object\product;
use \Snap\object\PriceProvider;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 * 20211108 - This file is alternative to grab price for all active providers at table at 8:30am everyday
 *
 * @author jeremy <jeremy@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class QueryPriceStreamJob  extends basejob {
    
    /**
     * job expire time
     * @var Integer
     */
    private $timeExpire = 60;
      
    /**
     * job sleep time
     * @var Integer
     */
    private $timeSleep = 1000000;
	
	/**
     * server proxy url
     * @var string
     */
    private $proxyUrl;
    
    /**
     * price stream partner store
     * @var \Snap\object\Partner
     */
    private $priceStreamPartner;
    
    /**
     * price stream product store
     * @var \Snap\object\Product
     */
    private $priceStreamProduct;
    
    /**
     * price stream api request url
     * @var String
     */
    private $priceStreamRequestUrl;
     
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        echo "doJob start...\n";
        
        $this->initPriceStreamConfig($app);

        $startTime = time();
        while( $this->timeExpire > (time() - $startTime)) {
            //get price stream
            $response = $this->getPriceStream($app);
            if (0 == $response['status']) {
                $app->log(__METHOD__ . '(): Response error: ' . json_encode($response), SNAP_LOG_ERROR);
            } else {
                //digest string
                $signStrSettings = array(
                    'delimiter' => '&',
                    'separator' => '=',
                    'exclude' => array('id', 'digest', 'requestParams')
                );
                $digestStr = $this->getSignStr($response, $signStrSettings);
                $digestStr .= '&key=' . $this->priceStreamPartner->apikey;
                
                //verify
                $custom = array(
                    'method' => 'hash',
                    'algorithm' => 'sha256',
                    'sign'   => $response['digest']
                );
                $verify = $this->getVerify($digestStr, $custom);
                
                //save into pricestream, apilogs
                if (!$verify) {
                    $app->log(__METHOD__ . '(): Verify digest failed!', SNAP_LOG_ERROR);
                } else {
                    $priceProvider = $app->priceProviderStore()->getForPartnerByProduct($this->priceStreamPartner, $this->priceStreamProduct);
					if ($app->getStore('otcpricingmodel')) {
						$adjustedPrice = $app->priceManager()->getOtcPricingModelBasePrice($priceProvider, $response['companybuyppg'], $response['companysellppg']);
					} else {
						$adjustedPrice = $app->priceManager()->adjustPriceStream($this->priceStreamPartner, $this->priceStreamProduct, $response['companybuyppg'], $response['companysellppg']);
					}
                    $app->priceManager()->onReceiveNewPriceStreamData($priceProvider, 
                                                        $adjustedPrice['companybuyppg'],
                                                        $adjustedPrice['companysellppg'],
                                                        $response['rawfxusdbuy'],
                                                        $response['rawfxusdsell'],
                                                        $response['rawfxsource'],
                                                        $response['providerpriceid'],
                                                        $response['pricesourceon'],
                                                        json_encode($response),
                                                        json_encode($response['requestParams']),
                                                        $adjustedPrice['priceadjusterid']);
                }            
            }

            //sleep 1 second
            usleep($this->timeSleep);
        }
        
        echo "doJob done...\n";
    }
    
    /**
     * Initilize price stream config
     * @param  App    $app    The snap application class
     * @return void
     */
    private function initPriceStreamConfig ($app) {

        $this->priceStreamPartner = $app->partnerStore()->getByField('id', $app->getConfig()->{'gtp.pricestream.partnerid'});
        $this->priceStreamProduct = $app->productStore()->getByField('code', $app->getConfig()->{'gtp.pricestream.productcode'});
        $this->priceStreamRequestUrl = $app->getConfig()->{'gtp.pricestream.requesturl'};
        $this->timeExpire = ($app->getConfig()->{'gtp.pricestream.timeexpire'}) ? $app->getConfig()->{'gtp.pricestream.timeexpire'} : $this->timeExpire;
        $this->timeSleep = ($app->getConfig()->{'gtp.pricestream.timesleep'}) ? $app->getConfig()->{'gtp.pricestream.timesleep'} : $this->timeSleep;
        $this->proxyUrl = ($app->getConfig()->{'gtp.server.proxyurl'}) ? $app->getConfig()->{'gtp.server.proxyurl'} : $this->proxyUrl;
    }
    
    /**
     * This is price stream api request
     * @param  App      $app                    The snap application class
     * @return array    $priceStreamResponse    The price stream response
     */
    private function getPriceStream ($app) {
    
        //params
        $params = array(
            'version' => '1.0m',
            'merchant_id' => $this->priceStreamPartner->code,
            'action' => 'query_price_stream',
            'product' => $this->priceStreamProduct->code,
            'currency' => 'MYR',
            'reference' => 'query price stream',
            'timestamp' => date('Y-m-d H:i:s'),
        );

        //digest string
        $signStrSettings = array(
            'delimiter' => '&',
            'separator' => '='
        );
        $digestStr = $this->getSignStr($params, $signStrSettings);
        $digestStr .= '&key=' . $this->priceStreamPartner->apikey;
        
        //digest
        $digest = hash('sha256', $digestStr);
        $params['digest'] = $digest;
        
        //curl post
        $curlPostSettings = array( 
            'paymenturl' => $this->priceStreamRequestUrl, 
            'format' => 'form'
        );
        $_buf = $this->curlPost($params, $curlPostSettings);
        
        $priceStreamResponse = json_decode($_buf, true);
        $priceStreamResponse['requestParams'] = $params;
        
        return $priceStreamResponse;
        
    }
    
    /** Sign Build */ 
    /** 
     * This method is used to generate sign
     *
     * @param string $data The data use to generate sign
     * @param array  $custom The custom array
     *  explain :
     *      array(
     *           'boolean' => true, 
     *           'method' => 'md5', 
     *           'encryptionkey' => 'Dasdfghjkl', 
     *           'algorithm' => OPENSSL_ALGO_SHA1, 
     *           'option' => array('cost' => 12, 'salt' => 'adsf')
     *      )
     *      boolean = true or false (optional)
     *      method = example:md5, openssl_sign, hash_hmac, sha1, hash, password_hash (required)
     *      encryptionkey = The secret key to generate sign (required for openssl_sign, hash_hmac)
     *      algorithm = The algorithm (required for openssl_sign, hash_hmac, hash, password_hash)
     *      option = The options (required for password_hash)
     * @return string $sign return sign
     * @access public
     */ 
    private function getSign($data, $custom = array())
    {
        if ($custom['boolean']) {
            $boolean = true;
        } else {
            $boolean = false;
        }
        //md5
        if ($custom['method'] == 'md5') {
            $sign = md5($data, $boolean);
        }
        //openssl_sign
        if ($custom['method'] == 'openssl_sign') {
            $privateKey = openssl_get_privatekey($custom['key']);
            openssl_sign($data, $sign, $privateKey, $custom['algorithm']);
            openssl_free_key($privateKey);
        }
        //hash_hmac
        if ($custom['method'] == 'hash_hmac') {
            $sign = hash_hmac($custom['algorithm'], $data, $custom['key'], $boolean);
        }
        //sha1
        if ($custom['method'] == 'sha1') {
            $sign = sha1($data, $boolean);
        }
        //hash
        if ($custom['method'] == 'hash') {
            $sign = hash($custom['algorithm'], $data, $boolean);
        }
        //password_hash
        if ($custom['method'] == 'password_hash') {
            $sign = password_hash($data, $custom['algorithm'], $custom['option']);
        }
        
        return $sign;
    }
    
    /** Verify Return Result */
    /** 
     * This method is used to verify sign
     * @param string $data The data use to verify sign
     * @param array $custom The custom array
     *  explain :
     *      array(
     *           'sign' => 'adsjdsadsads', 
     *           'boolean' => true, 
     *           'method' => 'md5', 
     *           'key' => 'Dasdfghjkl', 
     *           'algorithm' => OPENSSL_ALGO_SHA1, 
     *           'option' => array('cost' => 12, 'salt' => 'adsf')
     *      )
     *      sign = sign from gateway (required)
     *      boolean = true or false (optional)
     *      method = example:md5, openssl_sign, hash_hmac, sha1, hash, password_hash (required)
     *      key = The secret key to generate sign (required for openssl_sign, hash_hmac)
     *      algorithm = The algorithm (required for openssl_sign, hash_hmac, hash, password_hash)
     *      option = The options (required for password_hash)
     * @return boolean True if verify = 1. Otherwise false.
     * @access public
     */ 
    private function getVerify($data, $custom = array())
    {
        //md5
        if ($custom['method'] == 'md5') {
            $sign = $this->getSign($data, $custom);
            if (strcasecmp($sign, $custom['sign']) == 0) {
                return true;
            }
        }
        //hash_hmac
        if ($custom['method'] == 'hash_hmac') {
            $sign = $this->getSign($data, $custom);
            if (strcasecmp($sign, $custom['sign']) == 0) {
                return true;
            }
        }
        //sha1
        if ($custom['method'] == 'sha1') {
            $sign = $this->getSign($data, $custom);
            if (strcasecmp($sign, $custom['sign']) == 0) {
                return true;
            }
        }
        //hash
        if ($custom['method'] == 'hash') {
            $sign = $this->getSign($data, $custom);
            if (strcasecmp($sign, $custom['sign']) == 0) {
                return true;
            }
        }
        //password_hash
        if ($custom['method'] == 'password_hash') {
            $sign = $this->getSign($data, $custom);
            if (strcasecmp($sign, $custom['sign']) == 0) {
                return true;
            }
        }
        //openssl_verify
        if ($custom['method'] == 'openssl_verify') {
            $publicKey = openssl_get_publickey($custom['key']);
            if (openssl_verify($data, $custom['sign'], $publicKey, $custom['algorithm'])) {
                openssl_free_key($publicKey);
                return true;
            }
        }

        return false;
    }
    
    /** Sign String Build */
    /** 
     * This method is used to build sign string
     * @param array $data The data use to build sign string
     * @param array $custom The custom array
     *  explain :
     *      array(
     *           'sort' => 'asc', 
     *           'exclude' => array('sign'), 
     *           'delimiter' => '&', 
     *           'separator' => '='
     *      )      
     *      sort = sort data, asc or desc (optional)
     *      exclude = exclude params from sign string, example : array('sign') (optional)
     *      delimiter = use to join between data, example : &, |, =>, etc (optional)
     *      separator = name=value, name^value; Use ' ', if need join name & value, example : name+value (optional)
     * @return string $signStr return sign string
     * @access public
     */
    private function getSignStr($data, $custom = array())
    {
        if ($custom['sort'] == 'asc') {
            ksort($data);
        }
        if ($custom['sort'] == 'desc') {
            krsort($data);
        }
        $arr = array();
        $exclude = array('webshopurl', 'webshopgwip', 'forward', 'count2', 'encryptionkey', 'decryptionkey');
        if ($custom['exclude']) {
            $exclude = array_merge($exclude, $custom['exclude']);
        }

        foreach ($data as $name => $value) {
            if (in_array($name, $exclude)) {
                continue;
            }
            if ($value === true) {
                $value = 'true';
            }
            if ($value === false) {
                $value = 'false';
            }
            // if ($value === null) {
            //     $value = 'null';
            // }
            // if ($value == '') {
            //     continue;
            // }
            if ($custom['separator']) {
                if ($custom['separator'] == ' ') {
                    $separator = '';
                } else {
                    $separator = $custom['separator'];
                }
                array_push($arr, $name . $separator . $value);
            } else {
               array_push($arr, $value);
            }
            
        }
        $signStr = implode($custom['delimiter'], $arr);

        return $signStr;
    }
    
    /** CURL post */
    /**
     * @param array $data The CURL post data
     * @param array $custom The custom array
     *  explain :
     *      array(
     *           'paymenturl' => 'http://www.paymenturl.com', 
     *           'format' => 'form', 
     *           'header' => 'Content-Type: text/xml', 
     *           'noquerystr' => true
     *      )
     *      paymenturl = payment url (required)
     *      format = form, json, xml (required)
     *      header = curl header (optional)
     *      noquerystr = no need build query string (optional) 
     * @return string $_buf CURL result
     * @access public
     */
    private function curlPost($data, $custom = array())
    {
		$app = \Snap\App::getInstance();
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        if ($custom['format'] == 'form') {
            $header = array('Content-Type: application/x-www-form-urlencoded');
            if ($custom['noquerystr'] == true) {
                $qstr = $data;
            } else {
                $qstr = http_build_query($data);
            }
        } elseif ($custom['format'] == 'json') {
            $header = array('Content-Type: application/json');
            $qstr = json_encode($data);
        } elseif ($custom['format'] == 'xml') {
            $header = array('Content-Type: text/xml');
            $qstr = $data;
        }
        if ($custom['header']) {
            $header = $custom['header'];
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $custom['paymenturl']);
        if (0 < strlen($this->proxyUrl)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyUrl);
        }
        if (strlen($qstr) > 0) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $qstr);            
        }
		
		// Start measuring the duration
		$start = microtime(true);
		
        $_buf = curl_exec($ch);
		
		// Check for cURL errors
		if ($_buf === false) {
			$error = curl_error($ch);
			$app->log(__METHOD__ . "(): cURL error: {$error}", SNAP_LOG_ERROR);
		}
		
		// Calculate the duration in seconds
		$duration = microtime(true) - $start;
		
		$app->log(__METHOD__ . "(): cURL request took {$duration} seconds to complete.", SNAP_LOG_ERROR);
        
        return $_buf;
    }

    /**
     * This method is used to display options parameter for this job.
     * @return Array of associative array of parameters.
     *         E.g.[
     *            'param1' => array('required' => true, 'type' => 'int', 'desc' => 'Some description'),
     *            'param2' => array('required' => false, 'default' => 1, type' => 'string', 'desc' => 'Some description 22222'),
     *         ]
     *         -Where [required] indicates if the params is required for the job to run.  The cli will ensure this parameter is provided
     *                [type] is the expected data type of the parameter or its valid values.
     *                [default] is the default value for the field.
     *                [desc] is the description of the parameter and what it does.
     */
    function describeOptions() {
        return [];
    }
}
?>