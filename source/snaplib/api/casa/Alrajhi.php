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

/**
 * Class Alrajhi
 * 
 * Represents a Alrajhi payment processing system.
 */
class Alrajhi extends BaseCasa
{

    /**
     * The API URL for the Alrajhi payment processing system.
     */
    protected const API_URL = "http://10.202.212.93:9080/RcmWeb/RcmServices";
    
    /**
     * The account number for the Alrajhi supplier.
     */
    protected const SUPPLIER_BRANCH_CODE = "10100";
	
    protected const SUPPLIER_RELATION_TYPE = "001";
	
    protected const SUPPLIER_ACCOUNT_NO = "0000010119076";

    /**
     * Constructs a new Alrajhi object.
     *
     * @param mixed $app The application instance.
     */
    protected function __construct($app)
    {
        parent::__construct($app);
    }

	/**
	 * Get the transfer type based on the provided source reference number.
	 *
	 * @param string|null $sourcerefno The source reference number.
	 * @return array The transfer type mapping array.
	 */	
	private function getTransferType ($sourcerefno = null)
	{
		if (preg_match('/^CV.*/i', $sourcerefno)) {
			return ['D' => 'GDIDF', 'C' => 'GDICF'];
		} else {
			return ['D' => 'GDIDR', 'C' => 'GDICR'];
		}
	}

    /**
     * Initializes a new transaction in the Alrajhi payment processing system.
     *
     * @param object $accountHolder The account holder object.
     * @param object $paymentDetail The payment detail object.
     *
     * @return array The response from the Alrajhi payment processing system.
     */
    public function initializeTransaction($accountHolder, $paymentDetail)
    {
        $response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => '',
        );
		
		$debitCreditResult = true;
		$internalReferenceNumber = array();
		foreach ($this->getTransferType($paymentDetail->sourcerefno) as $transferType => $functionId) {
			list($accountBranchCodeFrom, $accountRelationTypeFrom, $accountNumberFrom) = explode('-', $paymentDetail->token);
			if ('C' == $transferType) {
				$accountBranchCodeFrom = self::SUPPLIER_BRANCH_CODE;
				$accountRelationTypeFrom = self::SUPPLIER_RELATION_TYPE;
				$accountNumberFrom = self::SUPPLIER_ACCOUNT_NO;
			}
			$ret = $this->debitCreditAccount($accountHolder, $paymentDetail, $accountBranchCodeFrom, $accountRelationTypeFrom, $accountNumberFrom, 
			$transferType, $functionId);
			if (!$ret['success']){
				$debitCreditResult = false;
				$statusCode = $ret['error_code'];
				$errorMessage = $ret['error_message'];
				break;
			} else {
				array_push($internalReferenceNumber, $ret['internalReferenceNumber']);
			}
		}
        
        $initialStatus = $paymentDetail->status;
        $action = IObservation::ACTION_REJECT;
        $paymentDetail->status = MyPaymentDetail::STATUS_FAILED;
        
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

		if (!$debitCreditResult) {
			$response['error_code'] = $statusCode;
            $response['error_message'] = $errorMessage;
            $paymentDetail->failedon = $now;
		} else {
			$internalReferenceNumber = implode(",", $internalReferenceNumber);
			$response['success'] = true;
			$response['data'] = $internalReferenceNumber;
			
			$action = IObservation::ACTION_CONFIRM;
			$paymentDetail->gatewayrefno = $internalReferenceNumber;
			$paymentDetail->status = MyPaymentDetail::STATUS_SUCCESS;
			$paymentDetail->successon = $now;
		}
        
        $paymentDetail = $this->app->mypaymentdetailStore()->save($paymentDetail, ['status', 'successon', 'failedon', 'gatewayrefno']);
        
        $this->notify(new IObservation($paymentDetail, $action, $initialStatus, ['response' => $response]));
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }
	
	/**
	 * Perform a debit or credit transaction on the account.
	 *
	 * @param object $accountHolder The account holder object.
	 * @param object $paymentObj The payment object.
	 * @param string $accountBranchCodeFrom The branch code of the account.
	 * @param string $accountRelationTypeFrom The relation type of the account.
	 * @param string $accountNumberFrom The account number.
	 * @param string $transferType The transfer type.
	 * @param string $functionId The function ID.
	 * @return array The response array containing transaction details.
	 */
	private function debitCreditAccount ($accountHolder, $paymentObj, $accountBranchCodeFrom, $accountRelationTypeFrom, $accountNumberFrom, $transferType, $functionId)
	{
		$response = array(
            'success' => false,
            'internalReferenceNumber' => '',
            'error_code' => '',
            'error_message' => '',
        );
		
		$now = new \DateTime();
		$rqUid = 'GDI' . $now->format('YmdHisv');
		$partyId = $accountHolder->partnercusid;
		$amt = $paymentObj->amount;
		$startDate = $now->format('Ymd');
		
		$soapRequest = <<<XML
			<soapenv:Envelope
				xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:obj="http://finmeccanica.com/services/rcm/objectsIFX"
				xmlns:ifx="http://www.ifxforum.org/IFX_2X">
				<soapenv:Header/>
				<soapenv:Body>
					<obj:DoSingleDebitCreditMsgRq>
						<ifx:RqUID>{$rqUid}</ifx:RqUID>
						<ifx:MsgRqHdr>
							<ifx:CredentialsRqHdr>
								<ifx:SubjectRole>SystemUser</ifx:SubjectRole>
								<ifx:SecTokenLogin>
									<ifx:LoginName>GDI</ifx:LoginName>
								</ifx:SecTokenLogin>
							</ifx:CredentialsRqHdr>
							<ifx:ContextRqHdr>
								<ifx:NetworkTrnData>
									<ifx:NetworkOwner>GDI</ifx:NetworkOwner>
								</ifx:NetworkTrnData>
								<ifx:CustLangPref>en</ifx:CustLangPref>
							</ifx:ContextRqHdr>
						</ifx:MsgRqHdr>
						<obj:DoSingleDebitCredit>
							<obj:AccessCode></obj:AccessCode>
							<ifx:PartyId>{$partyId}</ifx:PartyId>
							<obj:TransferType>{$transferType}</obj:TransferType>
							<obj:AccountBranchCodeFrom>{$accountBranchCodeFrom}</obj:AccountBranchCodeFrom>
							<obj:AccountRelationTypeFrom>{$accountRelationTypeFrom}</obj:AccountRelationTypeFrom>
							<obj:AccountNumberFrom>{$accountNumberFrom}</obj:AccountNumberFrom>
							<ifx:CurAmt>
								<ifx:Amt>{$amt}</ifx:Amt>
							</ifx:CurAmt>
							<obj:Remarks></obj:Remarks>
							<obj:StartDate>{$startDate}</obj:StartDate>
							<obj:FunctionId>{$functionId}</obj:FunctionId>
							<obj:FeeAmt>
								<ifx:Amt></ifx:Amt>
							</obj:FeeAmt>
						</obj:DoSingleDebitCredit>
					</obj:DoSingleDebitCreditMsgRq>
				</soapenv:Body>
			</soapenv:Envelope>
		XML;
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);
        
        $this->logApiRequest($soapRequest, $paymentObj);

        $soapXML = $this->curlPost($soapRequest);
        
        $this->logApiResponse($soapXML, $paymentObj);
		
		$xmlObj = simplexml_load_string($soapXML);
		$ns = $xmlObj->getNamespaces(true);
		foreach ($ns as $key => $value) {
			$xmlObj->registerXPathNamespace($key, $value);
		}
		
		$statusCode = (int) $xmlObj->xpath('//b:StatusCode')[0];
		
		if (0 != $statusCode) {
			$response['error_code'] = $statusCode;
            $response['error_message'] = (string) $xmlObj->xpath('//b:StatusDesc')[0];
		} else {
			$response['success'] = true;
			$response['internalReferenceNumber'] = (string) $xmlObj->xpath('//a:InternalReferenceNumber')[0];
		}
		
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

		$debitCreditResult = true;
		$internalReferenceNumber = array();
		foreach ($this->getTransferType() as $transferType => $functionId) {
			list($accountBranchCodeFrom, $accountRelationTypeFrom, $accountNumberFrom) = explode('-', $disbursement->token);
			if ('D' == $transferType) {
				$accountBranchCodeFrom = self::SUPPLIER_BRANCH_CODE;
				$accountRelationTypeFrom = self::SUPPLIER_RELATION_TYPE;
				$accountNumberFrom = self::SUPPLIER_ACCOUNT_NO;
			}
			$ret = $this->debitCreditAccount($accountHolder, $disbursement, $accountBranchCodeFrom, $accountRelationTypeFrom, $accountNumberFrom, 
			$transferType, $functionId);
			if (!$ret['success']){
				$debitCreditResult = false;
				$statusCode = $ret['error_code'];
				$errorMessage = $ret['error_message'];
				break;
			} else {
				array_push($internalReferenceNumber, $ret['internalReferenceNumber']);
			}
		}

        $initialStatus = $disbursement->status;
        $action = IObservation::ACTION_NONE;
        
        if (!$debitCreditResult) {
            $response['error_code'] = $statusCode;
            $response['error_message'] = $errorMessage;
        } else {
			$internalReferenceNumber = implode(",", $internalReferenceNumber);
            $response['success'] = true;
			$response['data'] = $internalReferenceNumber;
            $action = IObservation::ACTION_CONFIRM;

            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());
            $disbursement->disbursedon = $now;
            $disbursement->verifiedamount = $disbursement->amount;
            $disbursement->gatewayrefno = $internalReferenceNumber;
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['disbursedon', 'verifiedamount', 'gatewayrefno']);
        }
        
        $this->notify(new IObservation($disbursement, $action, $initialStatus, ['response' => $response]));
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }

    /**
     * Reverses a transaction in the Alrajhi payment processing system.
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
     * Gets customer information from the Alrajhi API.
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
        $rqUid = 'GDI' . $now->format('YmdHisv');

		$soapRequest = <<<XML
			<soapenv:Envelope
			xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
			xmlns:obj="http://finmeccanica.com/services/rcm/objectsIFX"
			xmlns:ifx="http://www.ifxforum.org/IFX_2X">
			<soapenv:Header/>
			<soapenv:Body>
			<obj:GetCustomerDetailsRq>
			<ifx:RqUID>{$rqUid}</ifx:RqUID>
			<ifx:MsgRqHdr>
			<ifx:CredentialsRqHdr>
			<ifx:SubjectRole>SystemUser</ifx:SubjectRole>
			<ifx:SecTokenLogin>
			<ifx:LoginName>GDI</ifx:LoginName>
			</ifx:SecTokenLogin>
			</ifx:CredentialsRqHdr>
			<ifx:ContextRqHdr>
			<ifx:NetworkTrnData>
			<ifx:NetworkOwner>GDI</ifx:NetworkOwner>
			</ifx:NetworkTrnData>
			<ifx:CustLangPref>en</ifx:CustLangPref>
			</ifx:ContextRqHdr>
			</ifx:MsgRqHdr>
		XML;
		
		if ('1' == $input['searchFlag']) {			
			$soapRequest .= <<<XML
			<ifx:PartyId>{$input['keyword']}</ifx:PartyId>
			XML;
			$coHeadingFlag = 'C';
		}
		
		if ('2' == $input['searchFlag']) {			
			$soapRequest .= <<<XML
			<obj:IdType>1</obj:IdType>
			<obj:IdNumber>{$input['keyword']}</obj:IdNumber>
			XML;
			$coHeadingFlag = 'P';
		}
		
		if ('3' == $input['searchFlag']) {			
			$soapRequest .= <<<XML
			<obj:IdType>B</obj:IdType>
			<obj:IdNumber>{$input['keyword']}</obj:IdNumber>
			XML;
			$coHeadingFlag = 'A';
		}
		
		$soapRequest .= <<<XML
			<obj:CoHeadingFlag>{$coHeadingFlag}</obj:CoHeadingFlag>
			<obj:SearchFlag>{$input['searchFlag']}</obj:SearchFlag>
			</obj:GetCustomerDetailsRq>
			</soapenv:Body>
			</soapenv:Envelope>
		XML;
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);
        
        $soapXML = $this->curlPost($soapRequest);
		$xmlObj = simplexml_load_string($soapXML);
		$ns = $xmlObj->getNamespaces(true);
		
		foreach ($ns as $key => $value) {
			$xmlObj->registerXPathNamespace($key, $value);
		}
		
		$statusCode = (int) $xmlObj->xpath('//b:StatusCode')[0];
		
		if (0 != $statusCode) {
            $response['error_code'] = $statusCode;
            $response['error_message'] = (string) $xmlObj->xpath('//b:StatusDesc')[0];
        } else {
			$response['success'] = true;
			$customerDetails = array();
			$customerDetailsRs = $xmlObj->xpath('//a:GetCustomerDetailsRs/*');
			foreach ($customerDetailsRs as $node) {
				$key = $node->getName();
				$value = (string) trim($node);
				if ('' != $value) $customerDetails[$key] = $value;
			}
			
			$phone = trim($customerDetails['MobilePhoneCountryCode']) . trim($customerDetails['MobilePhoneAreaCode']) . trim($customerDetails['MobilePhoneNumber']);
			if (0 == strlen($phone)) {
				$phone = trim($customerDetails['OfficePhoneCountryCode']) . trim($customerDetails['OfficePhoneAreaCode']) . trim($customerDetails['OfficePhoneNumber']);
			}
			
			$state = trim($customerDetails['StateCode']);
			$mailingState = trim($customerDetails['MailingStateCode']);
			
			$joinAccountExists = count($xmlObj->xpath('//a:JoinPrincipalCIC')) > 0;
			if ($joinAccountExists) {
				$joinAccounts = array();
				$joinPrincipalCIC = $xmlObj->xpath('//a:GetCustomerDetailsRs/a:JoinPrincipalCIC');
				foreach ($joinPrincipalCIC as $index => $child) {
					foreach ($child->xpath('*') as $node) {
						$key = $node->getName();
						$value = (string) trim($node);
						if ('' != $value) {
							if ('CustomerType' == $key) $value = $this->getAccountTypeValue($value);
							if ('StateCode' == $key) $value = $this->getStateDesc($value);
							$joinAccounts[$index][$key] = $value;
						}
					}
				}
			}
			
			$response['data'] = array(
                'myaccountholder' => array(
                    'fullname' => trim($customerDetails['Heading1']),
                    'partnercusid' => trim($customerDetails['PartyId']),
                    'email' => trim($customerDetails['Email']),
                    'mykadno' => trim($customerDetails['DocumentNumber']),
                    'phoneno' => $phone,
					'accounttype' => $this->getAccountTypeValue(trim($customerDetails['CustomerType']))
                ),
                'myaddress' => array(
                    'line1' => trim($customerDetails['Street1']),
                    'line2' => trim($customerDetails['Street2']),
                    'city' => trim($customerDetails['City']),
                    'postcode' => trim($customerDetails['ZipCode']),
                    'state' => ($state) ? $this->getStateDesc($state) : '',
					'mailingline1' => trim($customerDetails['MailingStreet1']),
                    'mailingline2' => trim($customerDetails['MailingStreet2']),
                    'mailingcity' => trim($customerDetails['MailingCity']),
                    'mailingpostcode' => trim($customerDetails['MailingZipCode']),
                    'mailingstate' => ($mailingState) ? $this->getStateDesc($mailingState) : ''
                ),
				'joinaccount' => $joinAccounts
            );
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
		$accountType = $this->getAccountTypeStrByValue($myAccountHolder->accounttype);
		
		if ('' == $accountType) return false;
		
		if ('A' == $accountType || 'D' == $accountType) {
			$input = array(
				'searchFlag' => '3',
				'keyword' => $myAccountHolder->mykadno
			);
		}
		
		if ('C' == $accountType) {
			$input = array(
				'searchFlag' => '1',
				'keyword' => $myAccountHolder->partnercusid
			);
		}
		
		if ('P' == $accountType) {
			$input = array(
				'searchFlag' => '2',
				'keyword' => $myAccountHolder->mykadno
			);
		}

        $customerInfo = $this->getCustomerInfo($input);
        if ($customerInfo['curl_error']) return $customerInfo;
        if (false == $customerInfo['success']) return false;
        return $customerInfo['data'];
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
		$rqUid = 'GDI' . $now->format('YmdHisv');
		
		$soapRequest = <<<XML
			<soapenv:Envelope
				xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:obj="http://finmeccanica.com/services/rcm/objectsIFX"
				xmlns:ifx="http://www.ifxforum.org/IFX_2X">
				<soapenv:Body>
					<obj:PartyAcctRelInqRq>
						<ifx:RqUID>{$rqUid}</ifx:RqUID>
						<ifx:MsgRqHdr>
							<ifx:CredentialsRqHdr>
								<ifx:SubjectRole>SystemUser</ifx:SubjectRole>
								<ifx:SecTokenLogin>
									<ifx:LoginName>GDI</ifx:LoginName>
								</ifx:SecTokenLogin>
							</ifx:CredentialsRqHdr>
							<ifx:ContextRqHdr>
								<ifx:NetworkTrnData>
									<ifx:NetworkOwner>GDI</ifx:NetworkOwner>
								</ifx:NetworkTrnData>
								<ifx:CustLangPref>en</ifx:CustLangPref>
							</ifx:ContextRqHdr>
						</ifx:MsgRqHdr>
						<ifx:PartyId>{$input['keyword']}</ifx:PartyId>
						<obj:SearchFlag>0</obj:SearchFlag>
					</obj:PartyAcctRelInqRq>
				</soapenv:Body>
			</soapenv:Envelope>	
		XML;
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);

        $soapXML = $this->curlPost($soapRequest);
		$xmlObj = simplexml_load_string($soapXML);
		$ns = $xmlObj->getNamespaces(true);
		
		foreach ($ns as $key => $value) {
			$xmlObj->registerXPathNamespace($key, $value);
		}
		
		$statusCode = (int) $xmlObj->xpath('//b:StatusCode')[0];
		
		if (0 != $statusCode) {
            $response['error_code'] = $statusCode;
            $response['error_message'] = (string) $xmlObj->xpath('//b:StatusDesc')[0];
        } else {
			$hasAccount = count($xmlObj->xpath('//b:PartyAcctRelRec')) > 0;
			if ($hasAccount) {
				$account = array();
				foreach ($xmlObj->xpath('//b:PartyAcctRelRec') as $key => $b) {
					$partyId = (isset($b->xpath('//b:PartyId')[$key])) ? (string) $b->xpath('//b:PartyId')[$key] : '';
					$acctIdentValue = (isset($b->xpath('//b:AcctIdentValue')[$key])) ? (string) $b->xpath('//b:AcctIdentValue')[$key] : '';
					$acctTypeValue = (isset($b->xpath('//b:AcctTypeValue')[$key])) ? (string) $b->xpath('//b:AcctTypeValue')[$key] : '';
					$curCodeType = (isset($b->xpath('//b:CurCodeType')[$key])) ? (string) $b->xpath('//b:CurCodeType')[$key] : '';
					$branchIdent = (isset($b->xpath('//b:BranchIdent')[$key])) ? (string) $b->xpath('//b:BranchIdent')[$key] : '';
					$acctTitle = (isset($b->xpath('//b:AcctTitle')[$key])) ? (string) $b->xpath('//b:AcctTitle')[$key] : '';
					$openDt = (isset($b->xpath('//b:OpenDt')[$key])) ? (string) $b->xpath('//b:OpenDt')[$key] : '';
					$closedDt = (isset($b->xpath('//b:ClosedDt')[$key])) ? (string) $b->xpath('//b:ClosedDt')[$key] : '';
					$segmentValue = (isset($b->xpath('//b:SegmentValue')[$key])) ? (string) $b->xpath('//b:SegmentValue')[$key] : '';
					$partyAcctRelType = (isset($b->xpath('//b:PartyAcctRelType')[$key])) ? (string) $b->xpath('//b:PartyAcctRelType')[$key] : '';
					if ('MYR' == (string) $b->xpath('//b:CurCodeType')[$key] /* && !$closedDt */) {
						$account[$key]['PartyId'] = $partyId;
						$account[$key]['AcctIdentValue'] = $acctIdentValue;
						$account[$key]['AcctTypeValue'] = $acctTypeValue;
						$account[$key]['CurCodeType'] = $curCodeType;
						$account[$key]['BranchIdent'] = $branchIdent;
						$account[$key]['AcctTitle'] = $acctTitle;
						$account[$key]['OpenDt'] = $openDt;
						$account[$key]['ClosedDt'] = $closedDt;
						$account[$key]['SegmentValue'] = $segmentValue;
						$account[$key]['PartyAcctRelType'] = $partyAcctRelType;
					}
				}
				$response['success'] = true; 
                $response['data'] = $account; 
			} else {
				$response['error_message'] = 'no accounts returned';
			}
		}
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
    }
	
	/**
	 * Get the description of a state based on its code.
	 *
	 * @param string $stateCode The state code.
	 * @return string The state description.
	 */
	private function getStateDesc ($stateCode)
	{
		$states = array(
			'01' => 'JOHOR',
			'02' => 'KEDAH',
			'03' => 'KELANTAN',
			'04' => 'MELAKA',
			'05' => 'NEGERI SEMBILAN',
			'06' => 'PAHANG',
			'07' => 'PULAU PINANG',
			'08' => 'PERAK',
			'09' => 'PERLIS',
			'10' => 'SABAH',
			'11' => 'SARAWAK',
			'12' => 'SELANGOR',
			'13' => 'TERENGGANU',
			'14' => 'WILAYAH PERSEKUTUAN K LUMPUR',
			'15' => 'WILAYAH PERSEKUTUAN LABUAN',
			'16' => 'WILAYAH PERSEKUTUAN PUTRAJAYA'
		);
		
		if (isset($states[$stateCode])) {
            return $states[$stateCode];
        } else {
            return 'Unknown';
        }
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
            case "A":
                return MyAccountHolder::TYPE_COMPANY;
                break;
            case "C":
                return MyAccountHolder::TYPE_COHEADING;
                break;
            case "D":
                return MyAccountHolder::TYPE_SOLEPROPRIETORSHIP;
                break;
            case "P":
                return MyAccountHolder::TYPE_INDIVIDUAL;
                break;   
            default:
                return "";
                break;
        }
    }
	
	/**
	 * Get the account type string by its corresponding value.
	 *
	 * @param int $value The value of the account type.
	 * @return string The account type string.
	 */
	private function getAccountTypeStrByValue ($value)
	{
		switch ($value) {
			case MyAccountHolder::TYPE_COMPANY:
				return "A";
				break;
			case MyAccountHolder::TYPE_COHEADING:
				return "C";
				break;
			case MyAccountHolder::TYPE_SOLEPROPRIETORSHIP:
				return "D";
				break;
			case MyAccountHolder::TYPE_INDIVIDUAL:
				return "P";
				break;
			default:
                return "";
                break;	
		}
	}
    
    /**
     * Sends a SOAP request to the Alrajhi API.
     *
     * @param string $soapRequest The SOAP request XML.
     * @param string $method The API method to call.
     * @return array The API response data.
     */
    private function curlPost ($soapRequest)
    {
        $headers = array(
			'Content-Type: text/xml',
			'SOAPAction: ' . self::API_URL
		);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $soapRequest,
            CURLOPT_HTTPHEADER => $headers,
        ));
        
        $soapXML = curl_exec($curl);
		
		if (curl_errno($curl)) {
            $message = 'error no: ' . curl_errno($curl) . ', error: ' . curl_error($curl);
            $this->logDebug(__METHOD__ . "(), message: " . $message);
            return array('curl_error' => $message);
        }
		
        curl_close($curl);

        return $soapXML;
    }
	
	/**
	 * Get evidence code for a given party ID.
	 *
	 * @param string $partyId The party ID.
	 *
	 * @return array The evidence code response.
	 */
	public function getEvidenceCode ($partyId)
	{
		$response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => ''
        );
		
		$now = new \DateTime();
        $rqUid = 'GDI' . $now->format('YmdHisv');

		$soapRequest = <<<XML
			<soapenv:Envelope
				xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:obj="http://finmeccanica.com/services/rcm/objectsIFX"
				xmlns:ifx="http://www.ifxforum.org/IFX_2X">
				<soapenv:Header/>
				<soapenv:Body>
					<obj:PartyEvidenceInqRq>
						<ifx:RqUID>{$rqUid}</ifx:RqUID>
						<ifx:MsgRqHdr>
							<ifx:CredentialsRqHdr>
								<ifx:SubjectRole>SystemUser</ifx:SubjectRole>
								<ifx:SecTokenLogin>
									<ifx:LoginName>GDI</ifx:LoginName>
								</ifx:SecTokenLogin>
							</ifx:CredentialsRqHdr>
							<ifx:ContextRqHdr>
								<ifx:NetworkTrnData>
									<ifx:NetworkOwner>GDI</ifx:NetworkOwner>
								</ifx:NetworkTrnData>
								<ifx:CustLangPref>en</ifx:CustLangPref>
							</ifx:ContextRqHdr>
						</ifx:MsgRqHdr>
						<ifx:PartySel>
							<ifx:PartyKeys>
								<ifx:PartyId>{$partyId}</ifx:PartyId>
							</ifx:PartyKeys>
						</ifx:PartySel>
					</obj:PartyEvidenceInqRq>
				</soapenv:Body>
			</soapenv:Body></soapenv:Envelope>
		XML;	
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);

        $soapXML = $this->curlPost($soapRequest);
		$xmlObj = simplexml_load_string($soapXML);
		$ns = $xmlObj->getNamespaces(true);
		
		foreach ($ns as $key => $value) {
			$xmlObj->registerXPathNamespace($key, $value);
		}
		
		$statusCode = (int) $xmlObj->xpath('//b:StatusCode')[0];

        if (0 != $statusCode) {
            $response['error_code'] = $statusCode;
            $response['error_message'] = (string) $xmlObj->xpath('//b:StatusDesc')[0];
        } else {
			$hasEvidence = count($xmlObj->xpath('//a:Evidence')) > 0;
			if ($hasEvidence) {
				$evidence = array();
				foreach ($xmlObj->xpath('//a:Evidence') as $key => $a) {
					array_push($evidence, (string) $a->xpath('//a:EvidenceValueDesc')[$key]);
				}
				$response['success'] = true; 
				$response['data'] = $evidence; 				
			} else {
				$response['error_message'] = 'no evidence returned';
			}
        }
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
	}
	
	/**
	 * Performs a relationship create or close operation.
	 *
	 * @param mixed $accountHolder The account holder.
	 * @param string $branchIdent The branch identifier.
	 * @param bool $open Determines whether the relationship should be opened or closed. Default is true.
	 *
	 * @return array The response array.
	 */
	public function doRelationshipCreateClose ($accountHolder, $branchIdent, $open = true)
	{
		$response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => ''
        );
		
		$now = new \DateTime();
		$rqUid = 'GDI' . $now->format('YmdHisv');
		$acctTypeValue = '810';
		$acctId = '000'.$acctTypeValue . str_pad($accountHolder->id, 7, '0', STR_PAD_LEFT);
		$effDt = $now->format('Ymd');
		$functionCode = ($open) ? '001' : '002';

		$soapRequest = <<<XML
			<soapenv:Envelope
				xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:obj="http://finmeccanica.com/services/rcm/objectsIFX"
				xmlns:ifx="http://www.ifxforum.org/IFX_2X">
				<soapenv:Header/>
				<soapenv:Body>
					<obj:DoRelationshipCreateCloseRq>
						<ifx:RqUID>{$rqUid}</ifx:RqUID>
						<ifx:MsgRqHdr>
							<ifx:CredentialsRqHdr>
								<ifx:SubjectRole>SystemUser</ifx:SubjectRole>
								<ifx:SecTokenLogin>
									<ifx:LoginName>GDI</ifx:LoginName>
								</ifx:SecTokenLogin>
							</ifx:CredentialsRqHdr>
							<ifx:ContextRqHdr>
								<ifx:NetworkTrnData>
									<ifx:NetworkOwner>GDI</ifx:NetworkOwner>
								</ifx:NetworkTrnData>
								<ifx:CustLangPref>en</ifx:CustLangPref>
							</ifx:ContextRqHdr>
						</ifx:MsgRqHdr>
						<obj:AccessCode/>
						<ifx:PartyId>{$accountHolder->partnercusid}</ifx:PartyId>
						<ifx:AcctKeys>
							<ifx:BranchIdent>{$branchIdent}</ifx:BranchIdent>
							<ifx:AcctType>
								<ifx:AcctTypeValue>{$acctTypeValue}</ifx:AcctTypeValue>
							</ifx:AcctType>
							<ifx:AcctId>{$acctId}</ifx:AcctId>
							<ifx:CurCode>
								<ifx:CurCodeValue>412</ifx:CurCodeValue>
							</ifx:CurCode>
						</ifx:AcctKeys>
						<obj:EffDt>{$effDt}</obj:EffDt>
						<obj:FunctionCode>{$functionCode}</obj:FunctionCode>
					</obj:DoRelationshipCreateCloseRq>
				</soapenv:Body>
			</soapenv:Envelope>
		XML;	
        
        $this->logDebug(__METHOD__ . "(), soapRequest: " . $soapRequest);

        $soapXML = $this->curlPost($soapRequest);
		$xmlObj = simplexml_load_string($soapXML);
		$ns = $xmlObj->getNamespaces(true);
		
		foreach ($ns as $key => $value) {
			$xmlObj->registerXPathNamespace($key, $value);
		}
		
		$statusCode = (int) $xmlObj->xpath('//b:StatusCode')[0];

        if (0 != $statusCode) {
            $response['error_code'] = $statusCode;
            $response['error_message'] = (string) $xmlObj->xpath('//b:StatusDesc')[0];
        } else {
			$response['success'] = true;
        }
        
        $this->logDebug(__METHOD__ . "(), response: " . json_encode($response));
        
        return $response;
	}
}


?>