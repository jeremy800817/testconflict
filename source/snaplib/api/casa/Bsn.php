<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 12-Nov-2020
*/

namespace Snap\api\casa;

use Snap\api\exception\GeneralException;
use Snap\api\mygtp\MyGtpApiSender;
use Snap\App;
use Snap\IObservation;
use Snap\object\MyPaymentDetail;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;

/**
 * Class Bsn
 * 
 * Represents a BSN payment processing system.
 */
class Bsn extends BaseCasa
{

    /**
     * The API URL for the BSN payment processing system.
     */
    //protected const API_URL = "http://10.10.88.20:3617/cics/gold/dfhwsdsh/goldsys";
    protected const API_URL = "http://10.10.55.152:2617/cics/gold/dfhwsdsh/goldsys"; 
    /**
     * The account number for the BSN biller.
     */
    protected const BILLER_ACCOUNT_NO = "0010041100046094";//0010029100001781 // 0010041100046094
    protected const BILLER_ORGN_CODE = "M3333500";
    protected const TERMINAL_NO = "3200";

    /**
     * Constructs a new Bsn object.
     *
     * @param mixed $app The application instance.
     */
    protected function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * Initializes a new transaction in the BSN payment processing system.
     *
     * @param object $accountHolder The account holder object.
     * @param object $paymentDetail The payment detail object.
     *
     * @return array The response from the BSN payment processing system.
     */
    public function initializeTransaction($accountHolder, $paymentDetail)
    {
        $response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => '',
        );
		
		$now = new \DateTime();
        $TransNo = 'GLD' . $now->format('YmdHisv');
        $TerminalNo = self::TERMINAL_NO;
		$AcctNo1 = $accountHolder->accountnumber;
        $AcctNo2 = self::BILLER_ACCOUNT_NO;
        $Amt = number_format($paymentDetail->amount, 2, ".", "");
		$RefDetail = $paymentDetail->sourcerefno;
		$SupervisorId = $this->getSupervisorId($paymentDetail);
        $soapRequest = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://datapower.bsn/cics/gold/dfhwsdsh/goldsys"><SOAP-ENV:Body><ns1:BDS21800-PINDAHAN><TRANS-NO>'.$TransNo.'</TRANS-NO><TRANS-ID>SPPM</TRANS-ID><SYSTEM-ID>21</SYSTEM-ID><TRANS-CODE>1810</TRANS-CODE><TERMINAL-NO>'.$TerminalNo.'</TERMINAL-NO><TERMINAL-ID>00</TERMINAL-ID><SUPERVISOR-ID>'.$SupervisorId.'</SUPERVISOR-ID><TYPE-CODE>01</TYPE-CODE><ACCT-NO1>'.$AcctNo1.'</ACCT-NO1><ACCT-NO2>'.$AcctNo2.'</ACCT-NO2><REFERENCE>iGOLD</REFERENCE><REFDETAIL>'.$RefDetail.'</REFDETAIL><AMT>'.$Amt.'</AMT></ns1:BDS21800-PINDAHAN></SOAP-ENV:Body></SOAP-ENV:Envelope>';
		
		$this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);
        
        $this->logApiRequest($soapRequest, $paymentDetail);

        $data = $this->curlPost($soapRequest, 'BDS21800-PINDAHAN');
        if ($data['curl_error']) return $data;
        
        $this->logApiResponse(json_encode($data), $paymentDetail);
        
        $initialStatus = $paymentDetail->status;
        $action = IObservation::ACTION_REJECT;
        $paymentDetail->status = MyPaymentDetail::STATUS_FAILED;
        
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        
        if (isset($data['faultcode'])) {
            $response['error_code'] = $data['faultcode'];
            $response['error_message'] = $data['faultstring'];
            $response['detail'] = $data['detail'] ?? '';
            
            $paymentDetail->failedon = $now;
        } else {
			$response['data'] = $data;
            $response['success'] = true;
			$paymentDetail->gatewayrefno = $data['date'];
			$action = IObservation::ACTION_CONFIRM;
			$paymentDetail->status = MyPaymentDetail::STATUS_SUCCESS;
			$paymentDetail->successon = $now;
        }
        
        $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail, ['status', 'successon', 'failedon', 'gatewayrefno']);
        
        //callback
        $this->notify(new IObservation($paymentDetail, $action, $initialStatus, ['response' => $response]));
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }
    
    /**
     * Creates a payout for the specified account holder and disbursement.
     *
     * @param AccountHolder $accountHolder The account holder object to create the payout for.
     * @param Disbursement $disbursement The disbursement object containing the payout details.
     * @return array An array containing the result of the payout creation process. The array has the following keys:
     *               - success: A boolean indicating whether the payout creation was successful.
     *               - data: The data returned by the payout creation process, if any.
     *               - error_code: The error code returned by the payout creation process, if any.
     *               - error_message: The error message returned by the payout creation process, if any.
     */
    public function createPayout($accountHolder, $disbursement)
    {
        $response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => '',
        );
        
        $now = new \DateTime();
        $TransNo = 'GLD' . $now->format('YmdHisv');
		$TerminalNo = self::TERMINAL_NO;
        $AcctNo1 = self::BILLER_ACCOUNT_NO;
        $AcctNo2 = $accountHolder->accountnumber;
        $Amt = number_format($disbursement->amount, 2, ".", "");
		$RefDetail = $disbursement->transactionrefno;
		$SupervisorId = $this->getSupervisorId($disbursement);
        $soapRequest = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://datapower.bsn/cics/gold/dfhwsdsh/goldsys"><SOAP-ENV:Body><ns1:BDS21800-PINDAHAN><TRANS-NO>'.$TransNo.'</TRANS-NO><TRANS-ID>SPPM</TRANS-ID><SYSTEM-ID>21</SYSTEM-ID><TRANS-CODE>1810</TRANS-CODE><TERMINAL-NO>'.$TerminalNo.'</TERMINAL-NO><TERMINAL-ID>00</TERMINAL-ID><SUPERVISOR-ID>'.$SupervisorId.'</SUPERVISOR-ID><TYPE-CODE>01</TYPE-CODE><ACCT-NO1>'.$AcctNo1.'</ACCT-NO1><ACCT-NO2>'.$AcctNo2.'</ACCT-NO2><REFERENCE>iGOLD</REFERENCE><REFDETAIL>'.$RefDetail.'</REFDETAIL><AMT>'.$Amt.'</AMT></ns1:BDS21800-PINDAHAN></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);
        
        $this->logApiRequest($soapRequest, $disbursement);

        $data = $this->curlPost($soapRequest, 'BDS21800-PINDAHAN');
        if ($data['curl_error']) return $data;
        
        $this->logApiResponse(json_encode($data), $disbursement);
        
        $initialStatus = $disbursement->status;
        $action = IObservation::ACTION_CANCEL;
        
        if (isset($data['faultcode'])) {
            $response['error_code'] = $data['faultcode'];
            $response['error_message'] = $data['faultstring'];
            $response['detail'] = $data['detail'] ?? '';
        } else {
            //if ($data['ic'] == $accountHolder->mykadno && $data['name'] == $accountHolder->accountname) {
                $response['success'] = true;
                $action = IObservation::ACTION_CONFIRM;  
            //} else {
            //    $response['error_message'] = 'Return response (ic/name) not match with account holder (IC/NAME)';
            //}

            $response['data'] = $data;
            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());
            $disbursement->disbursedon = $now;
            $disbursement->verifiedamount = $disbursement->amount;
            $disbursement->gatewayrefno = $data['date'];
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['disbursedon', 'verifiedamount', 'gatewayrefno']);
        }
        
        //callback
        $this->notify(new IObservation($disbursement, $action, $initialStatus, ['response' => $response]));
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }
	
	/**
	 * Get the supervisor ID based on the payment information.
	 *
	 * @param MyPaymentDetail|MyDisbursement $payment The payment object.
	 * @return string The supervisor ID.
	 */
	private function getSupervisorId ($payment)
	{
		$supervisorId = '00';
		if ($payment instanceof MyPaymentDetail && preg_match('/^GT.*/i', $payment->sourcerefno)) {
			$supervisorId = '01';
		}
		
		if ($payment instanceof MyPaymentDetail && preg_match('/^CV.*/i', $payment->sourcerefno)) {
			$supervisorId = '03';
		}
		
		if ($payment instanceof MyDisbursement) {
			$supervisorId = '02';
		}
		
		return $supervisorId;
	}

    /**
     * Reverses a transaction in the BSN payment processing system.
     *
     * @param object $accountHolder The account holder object.
     * @param object $paymentDetail The payment detail object.
     *
     * @return void
     */
    public function reverseTransaction($accountHolder, $paymentDetail)
    {
        throw GeneralException::fromTransaction([], [
            'message'   => "Not implemented"
        ]);
    }
    
    /**
     * Gets customer information from the BSN API.
     *
     * @param array $input An array of input parameters for the API.
     * @return array An array of response data from the API.
     */
    public function getCustomerInfo ($input)
    {
        $response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => '',
        );
        
        $now = new \DateTime();
        $TransNo = 'GLD' . $now->format('YmdHisv');
        $soapRequest = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://datapower.bsn/cics/gold/dfhwsdsh/goldsys"><SOAP-ENV:Body><ns1:SA1Z1003><TransNo>'.$TransNo.'</TransNo><TypOpt>'.$input['typopt'].'</TypOpt><OptInp>'.$input['optinp'].'</OptInp></ns1:SA1Z1003></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);
        
        $data = $this->curlPost($soapRequest, 'SA1Z1003');
        if ($data['curl_error']) return $data;
        
        if (isset($data['faultcode'])) {
            $response['error_code'] = $data['faultcode'];
            $response['error_message'] = $data['faultstring'];
            $response['detail'] = $data['detail'] ?? '';
        } else {
            $response['success'] = true;
			//$dateOfBirth = new \DateTime($data['TarikhLahir']);
			//$dateOfBirth = $dateOfBirth->format('Y-m-d H:i:s');
            $response['data'] = array(
                'myaccountholder' => array(
                    'fullname' => $data['Nama'],
                    'partnercusid' => $data['NoPelanggan'],
                    'email' => $data['Email'],
                    'mykadno' => $data['NoIC'],
                    'phoneno' => $data['Mobile'],
                    'accountname' => $data['Nama'],
                    'accountnumber' => $data['NoAkaun']
                ),
                'myaddress' => array(
                    'line1' => $data['AlamatSurat'],
                    'postcode' => $data['Poskod']
                ),
				'achadditionaldata' => array(
                    'title' => $data['Gelaran'],
                    'dateofbirth' => $data['TarikhLahir'],
                    'gender' => ('L' == $data['Jantina']) ? 'LELAKI' : 'PEREMPUAN',
                    'maritalstatus' => $data['TarafKahwin'],
                    'religion' => $data['Agama'],
                    'bumiputera' => ('BUMI' == $data['TarafBumi']) ? 'BUMI' : 'BUKAN BUMI',
                    'idtype' => $data['JenisIC'],
                    'category' => $data['Kategori'],
                    'race' => $data['Keturunan'],
                    'nationality' => $this->getCountryDesc($data['Negara'])
                )
            );
        }
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }
    
    /**
     * Retrieve account information from a SOAP API.
     *
     * @param array $input An array containing the 'typopt' and 'optinp' values to be used in the SOAP request.
     *
     * @return array An array containing the response data. The 'success' field indicates whether the request was successful, the 'data' field contains an array of account numbers, and the 'error_code' and 'error_message' fields contain any error information.
     */
    public function getCasaInfo ($input)
    {
        $response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => '',
        );
        
        $now = new \DateTime();
        $TransNo = 'GLD' . $now->format('YmdHisv');
        $soapRequest = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://datapower.bsn/cics/gold/dfhwsdsh/goldsys"><SOAP-ENV:Body><ns1:SA1Z1004><TransNo>'.$TransNo.'</TransNo><TypOpt>'.$input['typopt'].'</TypOpt><OptInp>'.$input['optinp'].'</OptInp></ns1:SA1Z1004></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);

        $data = $this->curlPost($soapRequest, 'SA1Z1004');
        if ($data['curl_error']) return $data;
        /*
            <Detail>
                System (5) FD(1) 
                AcctNo(16) FD(1) 
                Passbook/Series(7) FD(1) 
                AcctType(7) FD(1) - SENDIRI, BERSAMA, AMANAH, UNKNOWN, CASHLES, CASHLNE
                AcctOper(13) FD(1) - PASSBOOK, KAD AKAUN
                AcctHandling(8) FD(1) - S/SEORAN, SEMUA
                AcctStatus(15) FD(1) - AKTIF, TUTUP, T/AKTIF
                AcctCategory(8) FD(1) - 1-DEWASA, 2-REMAJA, 3-KANAK2
                OPenDate(8) FD(1) 
                CloseDate(8) FD(1) 
                State(3)
            </Detail>
        */

        if (isset($data['faultcode'])) {
            $response['error_code'] = $data['faultcode'];
            $response['error_message'] = $data['faultstring'];
            $response['detail'] = $data['detail'] ?? '';
        } else {
            if ($data['Detail']) {
                $account = array();
				$casaAccount = (is_array($data['Detail'])) ? $data['Detail'] : $data;
				$scheme = array(
					'SGB', 'WDH', 'CSL', 'BSH'
				);
                foreach ($casaAccount as $detail) {
                    preg_match("/(.{5})\s+(.{16})\s+(.{7})\s+(.{7})\s+(.{13})\s+(.{8})\s+(.{15})\s+(.{8})\s+(.{8})\s+(.{8})\s+(.{3})/", $detail, $matches);
                    if ('AKTIF' == trim($matches[7]) && in_array(trim($matches[1]), $scheme)) {
                        $account[] = [
                            'accountnumber' => trim($matches[2]),
                            'accounttype' => $this->getAccountTypeValue(trim($matches[4])),
                            'accounttypestr' => trim($matches[4])
                        ];
                    }
                }
                $response['success'] = true; 
                $response['data'] = $account; 
            }
        }
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }
	
	/**
	 * Get the account balance based on the account holder.
	 *
	 * @param MyAccountHolder $accountHolder The account holder object.
	 * @return array The account balance response.
	 */
	public function getAccountBalance ($accountHolder)
	{
		$accountBalanceType = $this->getAccountBalanceType($accountHolder);

		$response = [
			'success' => false,
			'balance' => '',
			'error_code' => '',
			'error_message' => 'Unknown account type [' . $accountBalanceType . ']'
		];
		
		if ($accountBalanceType) {
			$methodName = 'getAccountBalance' . $accountBalanceType;
			if (method_exists($this, $methodName)) {
				$response = $this->$methodName($accountHolder);
			}			
		}

		return $response;
	}
	
	/**
	 * Get the account balance type for the given account holder.
	 *
	 * @param AccountHolder $accountHolder The account holder object.
	 * @return string The account balance type ('Giro', 'Giroi', or the account balance type code).
	 */
	public function getAccountBalanceType ($accountHolder)
	{
		$accountBalanceType = substr($accountHolder->accountnumber, 5, 2);
		if (MyAccountHolder::TYPE_GIRO == $accountBalanceType) {
			$accountBalanceType = 'Giro';
		}
		if (MyAccountHolder::TYPE_GIROI == $accountBalanceType) {
			$accountBalanceType = 'Giroi';
		}
		
		return $accountBalanceType;
	}

	/**
	 * Get the account balance for giro accounts.
	 *
	 * @param MyAccountHolder $accountHolder The account holder object.
	 * @return array The account balance response for giro accounts.
	 */	
	public function getAccountBalanceGiro ($accountHolder)
	{
		$response = array(
            'success' => false,
            'balance' => '',
            'error_code' => '',
            'error_message' => ''
        );
		
		$now = new \DateTime();
		$TransNo = $now->format('YmdHisv');
		$TerminalNo = self::TERMINAL_NO;
		$acctNo = $accountHolder->accountnumber;
		$soapRequest = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://datapower.bsn/cics/gold/dfhwsdsh/goldsys"><SOAP-ENV:Body><ns1:bds29250-tanyabaki><trans-no>'.$TransNo.'</trans-no><trans-id>+G30SGBI</trans-id><system-id>29</system-id><trans-code>9250</trans-code><terminal-no>'.$TerminalNo.'</terminal-no><terminal-id>00</terminal-id><supervisor-id>00</supervisor-id><time-stamp-ind>01</time-stamp-ind><acct-no>'.$acctNo.'</acct-no></ns1:bds29250-tanyabaki></SOAP-ENV:Body></SOAP-ENV:Envelope>';
		
		$this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);

        $data = $this->curlPost($soapRequest, 'bds29250-tanyabaki');
		
		if (isset($data['faultcode'])) {
            $response['error_code'] = $data['faultcode'];
            $response['error_message'] = $data['faultstring'];
        } else {
			$response['success'] = true; 
			$response['balance'] = $data['AVAILABLE-BAL']; 
        }
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
	}
	
	/**
	 * Get the account balance for giroi accounts.
	 *
	 * @param MyAccountHolder $accountHolder The account holder object.
	 * @return array The account balance response for giroi accounts.
	 */
	public function getAccountBalanceGiroi ($accountHolder)
	{
		$response = array(
            'success' => false,
            'balance' => '',
            'error_code' => '',
            'error_message' => ''
        );
		
		$now = new \DateTime();
		$TransNo = $now->format('YmdHisv');
		$TerminalNo = self::TERMINAL_NO;
		$acctNo = $accountHolder->accountnumber;
		$soapRequest = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://datapower.bsn/cics/gold/dfhwsdsh/goldsys"><SOAP-ENV:Body><ns1:BDS41250-INQ><TRANS-NO>'.$TransNo.'</TRANS-NO><TRANS-ID>WDHI</TRANS-ID><SYSTEM-ID>41</SYSTEM-ID><TRANS-CODE>1250</TRANS-CODE><TERMINAL-NO>'.$TerminalNo.'</TERMINAL-NO><TERMINAL-ID>00</TERMINAL-ID><SUPERVISOR-ID>00</SUPERVISOR-ID><ACCCT-NO>'.$acctNo.'</ACCCT-NO></ns1:BDS41250-INQ></SOAP-ENV:Body></SOAP-ENV:Envelope>';
		
		$this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);

        $data = $this->curlPost($soapRequest, 'BDS41250-INQ');
		
		if (isset($data['faultcode'])) {
            $response['error_code'] = $data['faultcode'];
            $response['error_message'] = $data['faultstring'];
        } else {
			$response['success'] = true; 
			$response['balance'] = $data['AVAILABLE-BAL']; 
        }
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
	}
    
	/**
	 * Update the account holder information.
	 *
	 * @param MyAccountHolder $myAccountHolder The account holder object.
	 * @return array|bool The customer information or false on failure.
	 */
    public function updateMyAccountHolder ($myAccountHolder)
    {
        $input = array(
            'typopt' => '4',
            'optinp' => $myAccountHolder->accountnumber
        );
        $customerInfo = $this->getCustomerInfo($input);
        if ($customerInfo['curl_error']) return $customerInfo;
        if (false == $customerInfo['success']) return false;
        return $customerInfo['data'];
    }
	
	/**
	 * Deducts the management fee from the account holder based on the provided payment details.
	 *
	 * @param object $accountHolder An object representing the account holder, which includes the account number.
	 * @param object $paymentDetail An object containing details of the payment, such as the amount and status.
	 * @param string $paymentRef A reference string for the payment transaction.
	 *
	 * @return array An array containing the response with the following keys:
	 *               - 'success' (bool): Indicates whether the operation was successful.
	 *               - 'data' (mixed): The data returned from the SOAP request, or an empty string if unsuccessful.
	 *               - 'error_code' (string): The error code, if any.
	 *               - 'error_message' (string): The error message, if any.
	 */
	public function deductManagementFee($accountHolder, $paymentDetail, $paymentRef)
    {
        $response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => '',
        );
		
		$now = new \DateTime();
		$TransNo = 'GLD' . $now->format('YmdHisv');
		$TrxDate = $now->format('YmdHisv');
		$TransId = '02';
		$TerminalNo = self::TERMINAL_NO;
		$TerminalId = '00';
		$SupervisorId = $this->getSupervisorId($paymentDetail);
		$TransCode = 'CB701';
		$SubCode = '00';
		$ReversalInd = '';
		$SAFInd = 'N';
		$FrAct = $accountHolder->accountnumber;
		$TransAmt = number_format($paymentDetail->amount, 2, ".", "");
		$PaymentRef = $paymentRef;
		
        $soapRequest = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://datapower.bsn/cics/gold/dfhwsdsh/goldsys"><SOAP-ENV:Body><ns1:WESBGateway_Trx><TransNo>'.$TransNo.'</TransNo><TrxDate>'.$TrxDate.'</TrxDate><TransId>'.$TransId.'</TransId><TerminalNo>'.$TerminalNo.'</TerminalNo><TerminalId>'.$TerminalId.'</TerminalId><SupervisorId>'.$SupervisorId.'</SupervisorId><TransCode>'.$TransCode.'</TransCode><SubCode>'.$SubCode.'</SubCode><ReversalInd>'.$ReversalInd.'</ReversalInd><SAFInd>'.$SAFInd.'</SAFInd><FrAct>'.$FrAct.'</FrAct><TransAmt>'.$TransAmt.'</TransAmt><PaymentRef>'.$PaymentRef.'</PaymentRef></ns1:WESBGateway_Trx></SOAP-ENV:Body></SOAP-ENV:Envelope>';
		
		$this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);
        
        $this->logApiRequest($soapRequest, $paymentDetail);

        $data = $this->curlPost($soapRequest, 'WESBGateway_Trx');
        if ($data['curl_error']) return $data;
        
        $this->logApiResponse(json_encode($data), $paymentDetail);
        
        $paymentDetail->status = MyPaymentDetail::STATUS_FAILED;
        
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        
        if ('Y' != $data['RespCode']) {
            $response['error_code'] = $data['RespCode'];
            $response['error_message'] = $data['ErrCode'];
            $paymentDetail->failedon = $now;
        } else {
			$response['data'] = $data;
            $response['success'] = true;
			$paymentDetail->gatewayrefno = $data['TraceNo'];
			$paymentDetail->status = MyPaymentDetail::STATUS_SUCCESS;
			$paymentDetail->successon = $now;
        }
        
        $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail, ['status', 'successon', 'failedon', 'gatewayrefno']);
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }
    
    /**
     * Get the account type value based on the string representation.
     *
     * @param string $str The string representation of the account type.
     * @return int|string The account type value or an empty string if the string representation is not recognized.
     */
    private function getAccountTypeValue($str)
    {
        switch ($str) {
            case "SENDIRI":
            case "BSH":
                return MyAccountHolder::TYPE_SENDIRI;
                break;
            case "BERSAMA":
                return MyAccountHolder::TYPE_BERSAMA;
		break;
	    case "ORGANIS":
                return MyAccountHolder::TYPE_ORGANIS;
                break;
            case "AMANAH":
                return MyAccountHolder::TYPE_AMANAH;
                break;
            case "UNKNOWN":
                return MyAccountHolder::TYPE_UNKNOWN;
                break;
            case "CASHLES":
                return MyAccountHolder::TYPE_CASHLES;
                break;
            case "CASHLNE":
                return MyAccountHolder::TYPE_CASHLNE;
                break;    
            default:
                return "";
                break;
        }
    }
	
	/**
	 * Get the description of a country based on its country code.
	 *
	 * @param string $countryCode The country code.
	 * @return string The description of the country or 'Unknown' if the country code is not found.
	 */
	private function getCountryDesc ($countryCode)
	{
		$countries = array(
			'MY' => 'MALAYSIA',
			'AF' => 'AFGHANISTAN',
			'AL' => 'ALBANIA',
			'DZ' => 'ALGERIA',
			'AS' => 'AMERICAN SAMOA',
			'AD' => 'ANDORRA',
			'AO' => 'ANGOLA',
			'AI' => 'ANGUILLA',
			'AQ' => 'ANTARCTICA',
			'AG' => 'ANTIGUA AND BARBUDA',
			'AR' => 'ARGENTINA',
			'AM' => 'ARMENIA',
			'AW' => 'ARUBA',
			'AU' => 'AUSTRALIA',
			'AT' => 'AUSTRIA',
			'AZ' => 'AZERBAIJAN',
			'BS' => 'BAHAMAS',
			'BH' => 'BAHRAIN',
			'BD' => 'BANGLADESH',
			'BB' => 'BARBADOS',
			'BY' => 'BELARUS',
			'BE' => 'BELGIUM',
			'BZ' => 'BELIZE',
			'BJ' => 'BENIN',
			'BM' => 'BERMUDA',
			'BT' => 'BHUTAN',
			'BO' => 'BOLIVIA',
			'BA' => 'BOSNIA AND HERZEGOVINA',
			'BW' => 'BOTSWANA',
			'BV' => 'BOUVET ISLAND',
			'BR' => 'BRAZIL',
			'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
			'BN' => 'BRUNEI DARUSSALAM',
			'BG' => 'BULGARIA',
			'BF' => 'BURKINA FASO',
			'BI' => 'BURUNDI',
			'KH' => 'CAMBODIA',
			'CM' => 'CAMEROON',
			'CA' => 'CANADA',
			'CV' => 'CAPE VERDE',
			'KY' => 'CAYMAN ISLANDS',
			'CF' => 'CENTRAL AFRICAN REPUBLIC',
			'TD' => 'CHAD',
			'CL' => 'CHILE',
			'CN' => 'CHINA',
			'CX' => 'CHRISTMAS ISLAND',
			'CC' => 'COCOS (KEELING) ISLANDS',
			'CO' => 'COLOMBIA',
			'KM' => 'COMOROS',
			'CG' => 'CONGO',
			'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
			'CK' => 'COOK ISLANDS',
			'CR' => 'COSTA RICA',
			'CI' => 'COTE D IVOIRE',
			'HR' => 'CROATIA',
			'CU' => 'CUBA',
			'CY' => 'CYPRUS',
			'CZ' => 'CZECH REPUBLIC',
			'DK' => 'DENMARK',
			'DJ' => 'DJIBOUTI',
			'DM' => 'DOMINICA',
			'DO' => 'DOMINICAN REPUBLIC',
			'TP' => 'EAST TIMOR',
			'EC' => 'ECUADOR',
			'EG' => 'EGYPT',
			'SV' => 'EL SALVADOR',
			'GQ' => 'EQUATORIAL GUINEA',
			'ER' => 'ERITREA',
			'EE' => 'ESTONIA',
			'ET' => 'ETHIOPIA',
			'XE' => 'EUROPEAN COMMUNITY',
			'FO' => 'FAEROE ISLANDS',
			'FK' => 'FALKLAND ISLANDS (MALVINAS)',
			'FJ' => 'FIJI',
			'FI' => 'FINLAND',
			'FR' => 'FRANCE',
			'GF' => 'FRENCH GUIANA',
			'PF' => 'FRENCH POLYNESIA',
			'TF' => 'FRENCH SOUTHERN TERRITORIES',
			'GA' => 'GABON',
			'GM' => 'GAMBIA',
			'GE' => 'GEORGIA',
			'DE' => 'GERMANY',
			'GH' => 'GHANA',
			'GI' => 'GIBRALTAR',
			'GR' => 'GREECE',
			'GL' => 'GREENLAND',
			'GD' => 'GRENADA',
			'GP' => 'GUADELOUPE',
			'GU' => 'GUAM',
			'GT' => 'GUATEMALA',
			'GG' => 'GUERNSEY, C.I.',
			'GN' => 'GUINEA',
			'GW' => 'GUINEA-BISSAU',
			'GY' => 'GUYANA',
			'HT' => 'HAITI',
			'HM' => 'HEARD AND MCDONALD ISLANDS',
			'VA' => 'HOLY SEE (VATICAN CITY STATE)',
			'HN' => 'HONDURAS',
			'HK' => 'HONG KONG',
			'HU' => 'HUNGARY',
			'IS' => 'ICELAND',
			'IN' => 'INDIA',
			'ID' => 'INDONESIA',
			'IR' => 'IRAN (ISLAMIC REPUBLIC OF)',
			'IQ' => 'IRAQ',
			'IE' => 'IRELAND',
			'IM' => 'ISLE OF MAN',
			'IL' => 'ISRAEL',
			'IT' => 'ITALY',
			'JM' => 'JAMAICA',
			'JP' => 'JAPAN',
			'JE' => 'JERSEY, C.I.',
			'JO' => 'JORDAN',
			'KZ' => 'KAZAKHSTAN',
			'KE' => 'KENYA',
			'KI' => 'KIRIBATI',
			'KP' => 'KOREA, DEMOCRATIC PEOPLE S REPUBLIC OF',
			'KR' => 'KOREA, REPUBLIC OF',
			'KW' => 'KUWAIT',
			'KG' => 'KYRGYZSTAN',
			'LN' => 'LABUAN INT OFFSHORE FINANCIAL',
			'LA' => 'LAO PEOPLE S DEMOCRATIC REPUBLIC',
			'LV' => 'LATVIA',
			'LB' => 'LEBANON',
			'LS' => 'LESOTHO',
			'LR' => 'LIBERIA',
			'LY' => 'LIBYAN ARAB JAMAHIRIYA',
			'LI' => 'LIECHTENSTEIN',
			'LT' => 'LITHUANIA',
			'LU' => 'LUXEMBOURG',
			'MO' => 'MACAU',
			'MK' => 'MACEDONIA, THE FORMER YUGOSLAV REPBL. O',
			'MG' => 'MADAGASCAR',
			'MW' => 'MALAWI',
			'MV' => 'MALDIVES',
			'ML' => 'MALI',
			'MT' => 'MALTA',
			'MH' => 'MARSHALL ISLANDS',
			'MQ' => 'MARTINIQUE',
			'MR' => 'MAURITANIA',
			'MU' => 'MAURITIUS',
			'YT' => 'MAYOTTE',
			'MX' => 'MEXICO',
			'FM' => 'MICRONESIA (FEDERATED STATES OF)',
			'MD' => 'MOLDOVA, REPUBLIC OF',
			'MC' => 'MONACO',
			'MN' => 'MONGOLIA',
			'MS' => 'MONTSERRAT',
			'MA' => 'MOROCCO',
			'MZ' => 'MOZAMBIQUE',
			'MM' => 'MYANMAR',
			'NA' => 'NAMIBIA',
			'NR' => 'NAURU',
			'NP' => 'NEPAL',
			'NL' => 'NETHERLANDS',
			'AN' => 'NETHERLANDS ANTILLES',
			'NT' => 'NEUTRAL ZONE BETWEEN SAUDI ARABIA & IRAQ',
			'NC' => 'NEW CALEDONIA',
			'NZ' => 'NEW ZEALAND',
			'NI' => 'NICARAGUA',
			'NE' => 'NIGER',
			'NG' => 'NIGERIA',
			'NU' => 'NIUE',
			'NF' => 'NORFOLK ISLAND',
			'MP' => 'NORTHERN MARIANA ISLANDS',
			'NO' => 'NORWAY',
			'OM' => 'OMAN',
			'OT' => 'OTHERS',
			'PK' => 'PAKISTAN',
			'PW' => 'PALAU',
			'PS' => 'PALESTINIAN TERRITORY, OCCUPIED',
			'PA' => 'PANAMA',
			'PZ' => 'PANAMA CANAL ZONE',
			'PG' => 'PAPUA NEW GUINEA',
			'PY' => 'PARAGUAY',
			'PE' => 'PERU',
			'PH' => 'PHILIPPINES',
			'PN' => 'PITCAIRN',
			'PL' => 'POLAND',
			'PT' => 'PORTUGAL',
			'PR' => 'PUERTO RICO',
			'QA' => 'QATAR',
			'RE' => 'REUNION',
			'RO' => 'ROMANIA',
			'RU' => 'RUSSIAN FEDERATION',
			'RW' => 'RWANDA',
			'SH' => 'SAINT HELENA',
			'KN' => 'SAINT KITTS AND NEVIS',
			'LC' => 'SAINT LUCIA',
			'PM' => 'SAINT PIERRE AND MIQUELON',
			'VC' => 'SAINT VINCENT AND THE GRENADINES',
			'WS' => 'SAMOA',
			'SM' => 'SAN MARINO',
			'ST' => 'SAO TOME AND PRINCIPE',
			'SA' => 'SAUDI ARABIA',
			'SN' => 'SENEGAL',
			'SC' => 'SEYCHELLES',
			'SL' => 'SIERRA LEONE',
			'SG' => 'SINGAPORE',
			'SK' => 'SLOVAKIA',
			'SI' => 'SLOVENIA',
			'SB' => 'SOLOMON ISLANDS',
			'SO' => 'SOMALIA',
			'ZA' => 'SOUTH AFRICA',
			'GS' => 'SOUTH GEORGIA AND SOUTH SANDWICH ISLANDS',
			'ES' => 'SPAIN',
			'LK' => 'SRI LANKA',
			'SD' => 'SUDAN',
			'SR' => 'SURINAME',
			'SJ' => 'SVALBARD AND JAN MAYEN ISLANDS',
			'SZ' => 'SWAZILAND',
			'SE' => 'SWEDEN',
			'CH' => 'SWITZERLAND',
			'SY' => 'SYRIAN ARAB REPUBLIC',
			'TW' => 'TAIWAN',
			'TJ' => 'TAJIKISTAN',
			'TZ' => 'TANZANIA, UNITED REPUBLIC OF',
			'TH' => 'THAILAND',
			'TG' => 'TOGO',
			'TK' => 'TOKELAU',
			'TO' => 'TONGA',
			'TT' => 'TRINIDAD AND TOBAGO',
			'TN' => 'TUNISIA',
			'TR' => 'TURKEY',
			'TM' => 'TURKMENISTAN',
			'TC' => 'TURKS AND CAICOS ISLANDS',
			'TV' => 'TUVALU',
			'UG' => 'UGANDA',
			'UA' => 'UKRAINE',
			'AE' => 'UNITED ARAB EMIRATES',
			'GB' => 'UNITED KINGDOM',
			'US' => 'UNITED STATES',
			'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
			'UY' => 'URUGUAY',
			'UZ' => 'UZBEKISTAN',
			'VU' => 'VANUATU',
			'VE' => 'VENEZUELA',
			'VN' => 'VIET NAM',
			'VG' => 'VIRGIN ISLANDS, BRITISH',
			'VI' => 'VIRGIN ISLANDS, U.S',
			'WF' => 'WALLIS AND FUTUNA ISLANDS',
			'EH' => 'WESTERN SAHARA',
			'YE' => 'YEMEN',
			'YU' => 'YUGOSLAVIA',
			'ZM' => 'ZAMBIA',
			'ZW' => 'ZIMBABWE'
		);
		
		if (isset($countries[$countryCode])) {
            return $countries[$countryCode];
        } else {
            return 'Unknown';
        }
	}
    
    /**
     * Sends a SOAP request to the BSN API.
     *
     * @param string $soapRequest The SOAP request XML.
     * @param string $method The API method to call.
     * @return array The API response data.
     */
    private function curlPost ($soapRequest, $method)
    {
		//$this->logDebug(__METHOD__ . "(), method: " . $method);
		//$this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);
        // Set the headers
        $headers = array(
            'Content-Type: text/xml',
            'SOAPAction: http://datapower.bsn/cics/gold/dfhwsdsh/goldsys/' . $method
        );

        // Send the request
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $soapRequest,
            CURLOPT_HTTPHEADER => $headers,
        ));
        
        $soapXML = curl_exec($curl);
        $this->logDebug("curlPost soapXML : " . $soapXML); 
        if (curl_errno($curl)) {
            $message = 'error no: ' . curl_errno($curl) . ', error: ' . curl_error($curl);
            $this->logDebug(__METHOD__ . "(), message: " . $message);
            return array('curl_error' => $message);
        }
        
        curl_close($curl);
        
        // Load the SOAP XML string
        $soap = simplexml_load_string($soapXML);

        // Register the SOAP namespace
        $soap->registerXPathNamespace('s', 'http://schemas.xmlsoap.org/soap/envelope/');

        // Extract the desired data from the SOAP response
        $response = $soap->xpath('//s:Body/*')[0];
        
        //replace empty array to ''
        $data = array_map(function($value){
            return is_array($value) && empty($value) ? '' : $value;
        }, json_decode(json_encode($response), true));
        $this->logDebug("curlPost data : " . json_encode($data));
        // Convert the SimpleXMLElement object to an array
        return $data;
    }

}


?>
