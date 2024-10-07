<?php

namespace Snap\util\ekyc;

use Snap\IEkycProvider;
use Snap\IPepProvider;
use Snap\object\ApiLogs;
use Snap\object\MyKYCResult;
use Snap\object\MyKYCSubmission;
use Snap\object\MyPepPerson;
use Snap\object\MyPepSearchResult;
use Snap\TLogging;

use function GuzzleHttp\json_encode;

class Innov8tifProvider implements IEkycProvider, IPepProvider
{
    use TLogging;

    protected $app;
    protected $apiKey;
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $pepThreshold;
    protected $journeyIdValidity;
    protected $imageConfigs;

    const URL_OKAYID      = '/api/ekyc/okayid';
    const URL_OKAYDOC     = '/api/ekyc/okaydoc';
    const URL_OKAYLIVE    = '/api/ekyc/okaylive';
    const URL_OKAYFACE    = '/api/ekyc/okayface';
    const URL_JOURNEYID   = '/api/ekyc/journeyid';
    const URL_SCORECARD   = '/api/ekyc/scorecard';
    const URL_AML_PERSON  = '/ekyc/api/aml/v1/person';
    const URL_AML_PERSON_PDF  = '/ekyc/api/aml/v1/person/pdf';

    const API_TYPE_OKAYID   = 'OkayID';
    const API_TYPE_OKAYFACE = 'OkayFace';
    const API_TYPE_OKAYLIVE = 'OkayLive';
    const API_TYPE_OKAYDOC  = 'OkayDoc';

    const RESULT_FIELD_LIVEFACECHECK                = 'liveFaceCheck';
    const RESULT_FIELD_FACIALVERIFICATION           = 'facialVerification';
    const RESULT_FIELD_LANDMARK                     = 'landmark';
    const RESULT_FIELD_COLORDETECTION               = 'colorDetection';
    const RESULT_FIELD_HOLOGRAPHICPHOTOQUALITYCHECK = 'holographicPhotoQualityCheck';
    const RESULT_FIELD_SCREENDETECTION              = 'screenDetection';
    const RESULT_FIELD_IDNOFONTCHECK                = 'idNoFontCheck';
    const RESULT_FIELD_MICROPRINT                   = 'microprint';
    const RESULT_FIELD_HOLOGRAM                     = 'hologram';
    const RESULT_FIELD_HOLOGRAPHICPHOTOCOMPARISION  = 'holographicPhotoComparision';

    const PASSED = 'clear';
    const FAILED = 'suspicious';
    const CAUTIOS = 'cautious';

    const RESPONSE_STATUS_ERROR = 'error';
    const RESPONSE_STATUS_SUCCESS = 'success';

    const IMAGE_TYPE_FACE = 'face';
    const IMAGE_TYPE_NRIC = 'nric';

    const ERR_MAX_API_CALL_EXCEEDED             = 'MAX_API_CALL_EXCEEDED';
    const ERR_MISSING_PARAM_CRENDENTIAL         = 'MISSING_PARAM_CRENDENTIAL';
    const ERR_INVALID_JOURNEY_ID                = 'INVALID_JOURNEY_ID';
    const ERR_UNRECOGNIZED_IMAGE                = 'UNRECOGNIZED_IMAGE';
    const ERR_FACE_NOT_FOUND                    = 'FACE_NOT_FOUND';
    const ERR_UNEXPECTED_ERROR                  = 'UNEXPECTED_ERROR';
    const ERR_ERROR_NO_FACE_DETECTED            = 'ERROR_NO_FACE_DETECTED';
    const ERR_INTEG_ERROR                       = 'INTEG_ERROR';
    const ERR_EMPTY_DOCTYPE                     = 'EMPTY_DOCTYPE';
    const ERR_UNRECOGNIZED_DOCTYPE              = 'UNRECOGNIZED_DOCTYPE';
    const ERR_ERROR_IN_DOCTYPE_OR_VERSION       = 'ERROR_IN_DOCTYPE_OR_VERSION';
    const ERR_CONFIG_FILE_NOT_EXIST             = 'CONFIG_FILE_NOT_EXIST';
    const ERR_USERNAME_NOT_EXIST                = 'USERNAME_NOT_EXIST';
    const ERR_MISSING_FILE                      = 'error';
    const ERR_ERROR_IN_GETTING_SCORECARD_CONFIG = 'ERROR_IN_GETTING_SCORECARD_CONFIG';

    const OCR_FIELD_TYPE_NAME      = 25;
    const OCR_FIELD_TYPE_GENDER    = 12;
    const OCR_WFIELD_TYPE_GENDER   = 71172108;
    const OCR_FIELD_TYPE_MYKADNO   = 2;

    const RESULT_CHECK_FAILED   = 'F';
    const RESULT_CHECK_CAUTIOUS = 'C';

    const DOC_TYPE_NEWIC     = 'Malaysia - Id Card #2';
    const DOC_TYPE_OLDIC     = 'Malaysia - Id Card #1';
    const DOC_TYPE_MYTENTERA = 'Malaysia - Armed Forces Id Card #1';

    const RESULT_DOC_TYPE_MYKAD_OLD = 'mykad_old';
    const RESULT_DOC_TYPE_MYKAD_NEW = 'mykad_new';
    const RESULT_DOC_TYPE_MYTENTERA = 'mytentera';

    const OKAY_DOC_PARAMS = array(
        self::DOC_TYPE_MYTENTERA => array('docType' => 'mytentera', 'version' => '2', 'type' => 'nonpassport'),
        self::DOC_TYPE_NEWIC => array(
            'docType' => 'mykad',
            'version' => '7',
            'type' => 'nonpassport',
            'landmarkCheck' => 'true',
            'fontCheck' => 'true',
            'microprintCheck' => 'true',
            'photoSubstitutionCheck' => 'true',
            'icTypeCheck' => 'true',
            'colorMode' => 'true',
            'hologram' => 'true',
            'screenDetection' => 'true',
            'ghostPhotoColorDetection' => 'true',
            'idBlurDetection' => 'false',
            'idBrightnessDetection' => 'true',
            'faceBrightnessDetection' => 'true',
            'facePhotoSubstitution' => 'true'
        ),
        self::DOC_TYPE_OLDIC => array(
            'docType' => 'mykad',
            'version' => '7',
            'type' => 'nonpassport',
            'landmarkCheck' => 'true',
            'fontCheck' => 'true',
            'microprintCheck' => 'true',
            'photoSubstitutionCheck' => 'true',
            'icTypeCheck' => 'true',
            'colorMode' => 'true',
            'hologram' => 'true',
            'screenDetection' => 'true',
            'ghostPhotoColorDetection' => 'true',
            'idBlurDetection' => 'false',
            'idBrightnessDetection' => 'true',
            'faceBrightnessDetection' => 'true',
            'facePhotoSubstitution' => 'true'
        )
    );

    public function __construct()
    {
        $app                     = \Snap\App::getInstance();
        $this->app = $app;
        $this->apiKey            = $app->getConfig()->{'innov8tif.apikey'};
        $this->baseUrl           = $app->getConfig()->{'innov8tif.url'};
        $this->pepBaseUrl        = $app->getConfig()->{'innov8tif.pepurl'};
        $this->username          = $app->getConfig()->{'innov8tif.username'};
        $this->password          = $app->getConfig()->{'innov8tif.password'};
        $this->amlThreshold      = $app->getConfig()->{'innov8tif.amlthreshold'};
        $this->journeyIdValidity = $app->getConfig()->{'innov8tif.journeyidvalidity'};
        $this->pepCacheExpiry    = $app->getConfig()->{'innov8tif.pepcacheexpiry'};

        $this->imageConfigs['nric']['maxsize']   = $app->getConfig()->{'innov8tif.nric.maxsize'};
        $this->imageConfigs['nric']['minsize']   = $app->getConfig()->{'innov8tif.nric.minsize'};
        $this->imageConfigs['nric']['minheight'] = $app->getConfig()->{'innov8tif.nric.minheight'};
        $this->imageConfigs['nric']['minwidth']  = $app->getConfig()->{'innov8tif.nric.minwidth'};
        $this->imageConfigs['face']['maxsize']   = $app->getConfig()->{'innov8tif.face.maxsize'};
        $this->imageConfigs['face']['minheight'] = $app->getConfig()->{'innov8tif.face.minheight'};
        $this->imageConfigs['face']['minwidth']  = $app->getConfig()->{'innov8tif.face.minwidth'};
    }

    /**
     * Append the url to base url
     *
     * @param  string $url
     * @return string
     */
    protected function getApiEndpoint(string $url)
    {
        return $this->baseUrl . $url;
    }

    /**
     * Append the url to base url
     *
     * @param  string $url
     * @return string
     */
    protected function getPepApiEndpoint(string $url)
    {
        return $this->pepBaseUrl . $url;
    }

    /**
     * This method return the validity start date for Innov8tif journey id
     *
     * @return string
     */
    protected function getValidityStartDate()
    {
        $validity = intval($this->journeyIdValidity);

        $date = new \DateTime();
        $date->sub(new \DateInterval("P{$validity}D"));

        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Method to get the unqique journey id from Innov8tif
     *
     * @return string|null
     */
    public function getNewJourneyId()
    {
        try {
            $data = [
                'username' => $this->username,
                'password' => $this->password,
            ];

            $response = $this->sendRequest($this->getApiEndpoint(self::URL_JOURNEYID), ['json' => $data]);

            if (isset($response['journeyId'])) {
                return $response['journeyId'];
            }

            $this->log(__METHOD__ . "(): Cannot get a new journey id. No Journey id was found in response: " . json_encode($response), SNAP_LOG_ERROR);
        } catch (\Exception $e) {

            $this->log(__METHOD__ . "(): Cannot get a new journey id with error: " . $e->getMessage(), SNAP_LOG_ERROR);
            throw $e;
        }
    }

    public function applyJourneyIdToSubmission($app, $currentSubmission)
    {
        $oldSubmission = $app->mykycSubmissionStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', '=', $currentSubmission->accountholderid)
            ->where('lastjourneyidon', '>',  $this->getValidityStartDate())
            ->one();

        if (!$oldSubmission) {
            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());

            $currentSubmission->journeyid = $this->getNewJourneyId();
            $currentSubmission->lastjourneyidon = $now->format('Y-m-d H:i:s');

            return $currentSubmission;
        }

        $currentSubmission->journeyid = $oldSubmission->journeyid;
        $currentSubmission->lastjourneyidon = $oldSubmission->lastjourneyidon;

        return $currentSubmission;
    }

    /**
     * This method check if given base64 is a valid image and meets all the requirements
     *
     * @param  string $type
     * @param  string $base64String
     * @return bool
     */
    protected function imagePassRequirement($type, $base64String)
    {
        $data = base64_decode($base64String);

        // Get the resoultion
        $resolution = getimagesizefromstring($data);

        if (!$resolution || !$this->imageConfigs[$type] ?? false) {
            return false;
        }

        $imageSize       = strlen($data);
        $imageWidth      = $resolution[0];
        $imageHeight     = $resolution[1];

        $configMaxSize   = $this->convertSizeToByte($this->imageConfigs[$type]['maxsize'] ?? 0);
        $configMinSize   = $this->convertSizeToByte($this->imageConfigs[$type]['minsize'] ?? 0);
        $configMinHeight = $this->imageConfigs[$type]['minheight'] ?? 0;
        $configMinWidth  = $this->imageConfigs[$type]['minwidth'] ?? 0;

        if ($imageSize > $configMaxSize || $imageSize < $configMinSize || $imageWidth < $configMinWidth || $imageHeight < $configMinHeight) {
            return false;
        }

        return true;
    }

    /**
     * Convert human size to byte, e.g. 5KB to 5000
     *
     * @param  string $size
     * @return int
     */
    protected function convertSizeToByte($size)
    {
        $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $size);
        switch ($arr[1]) {
            case 'MB':
            case 'Mb':
            case 'mB':
            case 'mb':
                return intval($arr[0] * 1000 * 1000);
                break;
            case 'KB':
            case 'Kb':
            case 'kB':
            case 'kb':
                return intval($arr[0] * 1000);
                break;
            default:
                return intval($arr[0]);
                break;
        }
    }

    /**
     * Main method to submit the necessary facial data to the e-KYC provider
     * @param  AccountHolder    $accountHolder The account holder submitting this
     * @param  array            $params        The required data to be submitted
     *
     * @return MyKycResult|null                Returns if submission is successful or not
     */
    public function createSubmission($app, $accountHolder, $params)
    {
        // Search for a previous valid submission first
        $myEkycSubmission = $app->mykycSubmissionStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', '=', $accountHolder->id)
            ->where('createdon', '>',  $this->getValidityStartDate())
            ->andWhere(function ($q) {
                $q->where('status', MyKycSubmission::STATUS_INCOMPLETE)
                    ->orWhere('status', MyKycSubmission::STATUS_PENDING_SUBMISSION);
            })
            ->one();

        // Create a new one if none exists
        if (!$myEkycSubmission) {

            $myEkycSubmission = $app->mykycSubmissionStore()->create([
                'accountholderid' => $accountHolder->id,
                'status' => MyKycSubmission::STATUS_INCOMPLETE
            ]);
        }

        // Check which image is being passed and only keep the one passing requirements
        if (isset($params['mykad_front_b64']) && 0 < strlen($params['mykad_front_b64'])) {
            if (!$this->imagePassRequirement(self::IMAGE_TYPE_NRIC, $params['mykad_front_b64'])) {
                $this->log(__METHOD__ . ":() Submitted data (mykad_front_64) for account holder ({$myEkycSubmission->accountholderid}) does not meet requirement specifications", SNAP_LOG_ERROR);
                throw \Snap\api\exception\EkycSubmissionInvalid::fromTransaction([], ['message' => 'Image (mykad_front_b64) does not meet the requirement specification.']);
            }

            $myEkycSubmission->mykadfrontimageid = $myEkycSubmission->saveImage($params['mykad_front_b64']);
        }

        if (isset($params['mykad_back_b64']) && 0 < strlen($params['mykad_back_b64'])) {
            if (!$this->imagePassRequirement(self::IMAGE_TYPE_NRIC, $params['mykad_back_b64'])) {
                $this->log(__METHOD__ . ":() Submitted data (mykad_back_64) for account holder ({$myEkycSubmission->accountholderid}) does not meet requirement specifications", SNAP_LOG_ERROR);
                throw \Snap\api\exception\EkycSubmissionInvalid::fromTransaction([], ['message' => 'Image (mykad_front_b64) does not meet the requirement specification.']);
            }
            $myEkycSubmission->mykadbackimageid =  $myEkycSubmission->saveImage($params['mykad_back_b64']);
        }

        if (isset($params['face_image_b64']) && 0 < strlen($params['face_image_b64'])) {
            if (!$this->imagePassRequirement(self::IMAGE_TYPE_FACE, $params['face_image_b64'])) {
                $this->log(__METHOD__ . ":() Submitted data (face_image_b64) for account holder ({$myEkycSubmission->accountholderid}) does not meet requirement specifications", SNAP_LOG_ERROR);
                throw \Snap\api\exception\EkycSubmissionInvalid::fromTransaction([], ['message' => 'Image (face_image_b64) does not meet the requirement specification.']);
            }

            $myEkycSubmission->faceimageid =  $myEkycSubmission->saveImage($params['face_image_b64']);
        }

        // If no image was provided, then throw exception
        if (0 === strlen($params['mykad_front_b64']) && 0 === strlen($params['mykad_back_b64']) && 0 === strlen($params['face_image_b64'])) {
            throw \Snap\api\exception\EkycSubmissionInvalid::fromTransaction([], ['message' => 'No image was provided.']);
        }

        // Update submission to pending submission if all data is present
        if ($this->canSubmitToProvider($myEkycSubmission) && MyKycSubmission::STATUS_INCOMPLETE == $myEkycSubmission->status) {
            $myEkycSubmission->status = MyKycSubmission::STATUS_PENDING_SUBMISSION;
        }

        // Save into submission table
        /** @var MyKycSubmission */
        $myEkycSubmission = $app->mykycSubmissionStore()->save($myEkycSubmission);
        return $myEkycSubmission;
    }

    public function submitSubmission($app, $myEkycSubmission)
    {
        try {
            // If all exists only then we submit all to ekyc
            if ($this->canSubmitToProvider($myEkycSubmission)) {

                // Create a new journeyid regardless of previous submission
                // $myEkycSubmission->journeyid = $this->getNewJourneyId();

                // Reuse journeyid
                if (0 === strlen($myEkycSubmission->journeyid)) {
                    // $myEkycSubmission->journeyid = $this->getNewJourneyId();
                    $myEkycSubmission = $this->applyJourneyIdToSubmission($app, $myEkycSubmission);
                }

                $_now = new \DateTime();
                $myEkycSubmission->submittedon = \Snap\common::convertUTCToUserDatetime($_now);
                $myEkycSubmission = $app->mykycSubmissionStore()->save($myEkycSubmission);

                $okayIdResponse = $this->submitOkayID($myEkycSubmission);

                $documentType = $okayIdResponse['documentType'];
                $croppedIdFrontImage = $okayIdResponse['images'][0]['Base64ImageString'];
                $croppedIdBackImage = $okayIdResponse['images'][1]['Base64ImageString'];


                if (!in_array($documentType, [self::DOC_TYPE_MYTENTERA, self::DOC_TYPE_NEWIC, self::DOC_TYPE_OLDIC])) {

                    $this->log(__METHOD__ . "Document type (" . $documentType . ") not supported ", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\EkycSubmissionInvalid::fromTransaction([], ['message' => 'Document type not supported.']);
                }

                $this->processOkayIdOcrData($myEkycSubmission, $okayIdResponse);
                $this->submitOkayFace($myEkycSubmission);
                $this->submitOkayDoc($myEkycSubmission, $documentType);

                // Disable OkayLive as OkayFace already has a livenessDetection set to true,
                // which will call OkayFace API implicitly by Innov8tif backends.
                // $this->submitOkayLive($myEkycSubmission);

                // Reset remarks
                $myEkycSubmission->remarks = null;
                $myEkycSubmission->doctype = $this->getDocumentType($documentType);
                // Succeeded in submitting the required facial data. Update submission status
                $myEkycSubmission->status = MyKycSubmission::STATUS_PENDING_RESULT;
                $myEkycSubmission->mykadfrontimageid = $myEkycSubmission->saveImage($croppedIdFrontImage);
                $myEkycSubmission->mykadbackimageid = $myEkycSubmission->saveImage($croppedIdBackImage);

                $myEkycSubmission = $app->mykycSubmissionStore()->save($myEkycSubmission);
                return $myEkycSubmission;
            } else {
                $this->log("canSubmitToProvider() check fail for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_ERROR);
                $this->log("mykadfront length:" . strlen($myEkycSubmission->getMyKadFrontImage()), SNAP_LOG_DEBUG);
                $this->log("mykadback length:" . strlen($myEkycSubmission->getMyKadBackImage()), SNAP_LOG_DEBUG);
                $this->log("faceimage length:" . strlen($myEkycSubmission->getFaceImage()), SNAP_LOG_DEBUG);
            }
        } catch (\Snap\api\exception\ProviderApiError $e) {

            $this->log(__METHOD__ . "(): Failed to submit data for account holder ({$myEkycSubmission->accountholderid}) with error " . $e->getMessage(), SNAP_LOG_ERROR);

            // Increase submission retries
            $this->incrementSubmissionRetries($myEkycSubmission);

            // If submission retries hit limit, flag the submission status as incomplete
            if (!$this->canRetrySubmission($myEkycSubmission)) {
                $myEkycSubmission->remarks = $e->getMessage();
                $myEkycSubmission->status = MyKycSubmission::STATUS_INACTIVE;
                $myEkycSubmission = $app->mykycSubmissionStore()->save($myEkycSubmission);
                $this->log(__METHOD__ . "(): Failed to retry submmission for account holder ({$myEkycSubmission->accountholderid}), limit exceeded.", SNAP_LOG_ERROR);
            }

            throw $e;
        } catch (\Exception $e) {

            $this->log(__METHOD__ . "(): Failed to submit data for account holder ({$myEkycSubmission->accountholderid}) with error " . $e->getMessage(), SNAP_LOG_ERROR);
            $myEkycSubmission->remarks = $e->getMessage();
            // If image related error,
            $myEkycSubmission->status = MyKycSubmission::STATUS_INACTIVE;
            $myEkycSubmission = $app->mykycSubmissionStore()->save($myEkycSubmission);

            throw $e;
        }
    }

    /**
     * Parse result from Innov8tif
     *
     * @param MyKycSubmission $myEkycSubmission     The submission record
     *
     * @return MyKycResult|null
     */
    public function getResult($app, $myEkycSubmission)
    {
        try {

            // Submission document type is updated for each new submission by account holder
            if (MyKycSubmission::DOC_TYPE_MYKAD === $myEkycSubmission->doctype) {
                $documentType = self::RESULT_DOC_TYPE_MYKAD_NEW;
            } else if (MyKycSubmission::DOC_TYPE_MYKAD_OLD === $myEkycSubmission->doctype) {
                $documentType = self::RESULT_DOC_TYPE_MYKAD_OLD;
            } else if (MyKycSubmission::DOC_TYPE_MYTENTERA === $myEkycSubmission->doctype) {
                $documentType = self::RESULT_DOC_TYPE_MYTENTERA;
            } else {
                $documentType = self::RESULT_DOC_TYPE_MYKAD_NEW;
            }

            $this->log(__METHOD__ . "(): Using EKYC result document type ({$documentType}) for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);

            $results = $this->getScorecardResult($myEkycSubmission);

            // Check for existing result if any
            $myEkycResult = $myEkycSubmission->getResult();
            // Create new one if none
            if (!$myEkycResult) {
                $myEkycResult = $app->mykycResultStore()->create([
                    'provider' => self::class,
                    'submissionid' => $myEkycSubmission->id,
                    'status' => MyKYCResult::STATUS_ACTIVE,
                ]);
            }

            // Default to failed
            $myEkycResult->result = MyKycResult::RESULT_FAILED;
            $latestSubmissionResult = null;

            // No scorecard
            if (!isset($results['scorecardResultList']) || null === $results['scorecardResultList'] || empty($results['scorecardResultList'])) {
                $this->log(__METHOD__ . "(): EKYC Submission does not have result for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_ERROR);
            } else {
                $scorecardResultList = $results['scorecardResultList'];
                // Find the passed scorecard
                foreach ($scorecardResultList as $result) {

                    // Find the pass result regardless of document type
                    if (self::PASSED === $result['scorecardStatus']) {
                        $this->log(__METHOD__ . "(): EKYC Result Passed for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);
                        // Reset remarks
                        $myEkycSubmission->remarks = '';
                        $app->mykycSubmissionStore()->save($myEkycSubmission, ['remarks']);

                        $myEkycResult->result = MyKycResult::RESULT_PASSED;
                        break;
                    }

                    if ($documentType === $result['docType']) {
                        $this->log(__METHOD__ . "(): Found document type ({$documentType}) result for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);
                        $latestSubmissionResult = $result;
                    }
                }
            }

            // If all scorecards failed, check using configuration, field that does not exist in configuration is bypassed
            if (MyKycResult::RESULT_FAILED === $myEkycResult->result && $latestSubmissionResult) {
                $this->log(__METHOD__ . "(): EKYC Result Failed for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_ERROR);
                $myEkycResult->result = $this->getFieldCheckResult($myEkycSubmission, $latestSubmissionResult);
            }

            // Save response
            $myEkycResult->data = json_encode($results);
            $myEkycResult = $app->mykycResultStore()->save($myEkycResult);
            return $myEkycResult;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Failed to parse result with error " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     *  Check each result field and return the result. This method can also be used to bypass the result from Innov8tif
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @param  array  $scorecardResult
     * @return string
     */
    public function getFieldCheckResult($myEkycSubmission, $scorecardResult)
    {
        $this->log(__METHOD__ . "(): Checking scorecard result.", SNAP_LOG_DEBUG);

        // Reset remarks
        $myEkycSubmission->remarks = '';
        $fieldCheck = $this->app->getConfig()->{'innov8tif.score.fieldcheck'} ?? null;
        $fieldCheckArray = explode(',', $fieldCheck);
        $failed = false;

        $checkResultList = $scorecardResult['checkResultList'];

        foreach ($checkResultList as $checkResult) {

            if (self::RESULT_CHECK_FAILED == $checkResult['checkStatus']) {
                $this->log(__METHOD__ . "(): [{$this->getFieldApiType($checkResult['checkType'])}] Failed status found for ({$checkResult['checkType']}) for account holder ({$myEkycSubmission->accountholderid})", SNAP_LOG_ERROR);

                // Concat remarks
                $myEkycSubmission->remarks .= "Failed for field checking ({$checkResult['checkType']}). \n";

                if (in_array($checkResult['checkType'], $fieldCheckArray)) {
                    $failed = true;
                }
            }

            if (self::RESULT_CHECK_CAUTIOUS == $checkResult['checkStatus']) {
                $this->log(__METHOD__ . "(): [{$this->getFieldApiType($checkResult['checkType'])}] Cautious for field checking ({$checkResult['checkType']}) for account holder ({$myEkycSubmission->accountholderid})", SNAP_LOG_ERROR);

                // Concat remarks
                $myEkycSubmission->remarks .= "Found cautious status for field ({$checkResult['checkType']}) \n";
            }
        }

        // Save remarks
        $this->app->mykycSubmissionStore()->save($myEkycSubmission);

        // If fieldcheck config was not set
        if (!$fieldCheck) {
            $this->log(__METHOD__ . "(): Bypass fieldcheck configuration could not be found. Returning result as failed for account holder ({$myEkycSubmission->accountholderid})", SNAP_LOG_ERROR);
            return MyKycResult::RESULT_FAILED;
        }

        // If the all check specified in the config file passed
        if (!$failed) {
            $this->log(__METHOD__ . "(): All of the bypass check for scorecard passed for account holder ({$myEkycSubmission->accountholderid})", SNAP_LOG_DEBUG);

            return MyKycResult::RESULT_PASSED;
        }

        $this->log(__METHOD__ . "(): Some of the field failed the check", SNAP_LOG_ERROR);
        // All scorecard result failed
        return MyKycResult::RESULT_FAILED;
    }

    /**
     * To check if all required params is provided
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @return boolean
     */
    public function canSubmitToProvider($myEkycSubmission)
    {
        return $myEkycSubmission->mykadfrontimageid && $myEkycSubmission->mykadbackimageid && $myEkycSubmission->faceimageid;
    }

    /**
     * Get the result from Innov8tif
     *
     * @return array
     */
    public function getScorecardResult(MyKycSubmission $myEkycSubmission)
    {
        try {
            $params = ['journeyId' => $myEkycSubmission->journeyid];
            $this->log(__METHOD__ . "(): Getting scorecard results for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);
            return $this->sendRequest($this->getApiEndpoint(self::URL_SCORECARD), ['query' => $params], 'GET');
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error on trying to get scorecard results for account holder ({$myEkycSubmission->accountholderid}). Error message: {$e->getMessage()}", SNAP_LOG_ERROR);
            throw $e;
        }
    }

    /**
     * Submit data to OkayId endpoint
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @return array
     */
    public function submitOkayID(MyKycSubmission $myEkycSubmission)
    {
        try {
            $this->log(__METHOD__ . "(): Preparing to submit OkayId for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);

            $defaultParams = [
                'cambodia' => false,
                'faceImageEnabled' => true,
                'imageEnabled' => true,
            ];

            $params = array_merge($defaultParams, [
                'base64ImageString' => $myEkycSubmission->getMyKadFrontImage(),
                'backImage' => $myEkycSubmission->getMyKadBackImage(),
                'journeyId' => $myEkycSubmission->journeyid,
            ]);

            return $this->sendRequest($this->getApiEndpoint(self::URL_OKAYID), ['json' => $params]);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error on trying to submit OkayId for account holder ({$myEkycSubmission->accountholderid}). Error message: {$e->getMessage()}", SNAP_LOG_ERROR);
            // throw new \Exception("OkayID: " . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Submit data to the OkayFace endpoint
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @return array
     */
    public function submitOkayFace(MyKycSubmission $myEkycSubmission)
    {
        try {
            $this->log(__METHOD__ . "(): Preparing to submit OkayFace for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);

            $defaultParams = [
                [
                    'name' => 'livenessDetection',
                    'contents' => true,
                ],
            ];

            $params = array_merge($defaultParams, [
                [
                    'name' => 'journeyId',
                    'contents' => $myEkycSubmission->journeyid,
                ],
                [
                    'name' => 'imageBest',
                    'contents' => $this->compressOkayFaceImage($this->base64ToFileHandle($myEkycSubmission->getFaceImage())),
                ],
                [
                    'name' => 'imageIdCard',
                    'contents' => $this->compressOkayFaceImage($this->base64ToFileHandle($myEkycSubmission->getMyKadFrontImage())),
                ],
            ]);

            return $this->sendRequest($this->getApiEndpoint(self::URL_OKAYFACE), ['multipart' => $params]);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error on trying to submit OkayFace for account holder ({$myEkycSubmission->accountholderid}). Error message: {$e->getMessage()}", SNAP_LOG_ERROR);
            // throw new \Exception("OkayFace: " . $e->getMessage());
            throw $e;

        }
    }

    /**
     * Compress the image for OkayFace submission
     *
     * @param  resource $originalImage
     * @return resource
     */
    protected function compressOkayFaceImage($originalImage)
    {
        // Using config face limit
        $sizeLimit = $this->convertSizeToByte($this->imageConfigs['face']['maxsize']);
        $size      = fstat($originalImage)['size'];
        $image     = $originalImage;

        // Loop until we get the size below or equal limit
        if ($sizeLimit < $size) {
            $resource  = imagecreatefromstring(stream_get_contents($originalImage));
            rewind($originalImage);
            $location  =  stream_get_meta_data($image)['uri'];
            $image = $this->resizeImage($resource, $location, 85, 90);
        }

        return $image;
    }

    /**
     * Convert base64 string to a resource
     *
     * @param  string   $encodedString
     * @return resource
     */
    public function base64ToFileHandle($encodedString)
    {
        $fp = tmpfile();
        fwrite($fp, base64_decode($encodedString));
        fseek($fp, 0);

        return $fp;
    }

    /**
     * Submit data to OkayLive endpoint
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @return array
     */
    public function submitOkayLive(MyKycSubmission $myEkycSubmission)
    {
        try {
            $this->log(__METHOD__ . "(): Preparing to submit OkayLive for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);
            $params = [
                [
                    'name' => 'journeyId',
                    'contents' => $myEkycSubmission->journeyid,
                ],
                [
                    'name' => 'imageBest',
                    'contents' => $this->base64ToFileHandle($myEkycSubmission->getFaceImage()),
                ],
            ];

            return $this->sendRequest(
                $this->getApiEndpoint(self::URL_OKAYLIVE),
                ['multipart' => $params]
            );
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error on trying to submit OkayLive for account holder ({$myEkycSubmission->accountholderid}). Error message: {$e->getMessage()}", SNAP_LOG_ERROR);
            // throw new \Exception("OkayLive: " . $e->getMessage());
            throw $e;

        }
    }

    /**
     * Submit data to OkayDoc endpoint
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @param  string          $documentType
     * @return array
     */
    public function submitOkayDoc(MyKycSubmission $myEkycSubmission, $documentType)
    {
        try {
            $this->log(__METHOD__ . "(): Preparing to submit OkayDoc for account holder ({$myEkycSubmission->accountholderid}).", SNAP_LOG_DEBUG);

            $defaultParams = self::OKAY_DOC_PARAMS[$documentType];

            $params = array_merge($defaultParams, [
                'idImageBase64Image' => $myEkycSubmission->getMyKadFrontImage(),
                'journeyId' => $myEkycSubmission->journeyid,
            ]);

            return $this->sendRequest($this->getApiEndpoint(self::URL_OKAYDOC), ['json' => $params]);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error on trying to submit OkayDoc for account holder ({$myEkycSubmission->accountholderid}). Error message: {$e->getMessage()}", SNAP_LOG_ERROR);
            // throw new \Exception("OkayDoc: " . $e->getMessage());
            throw $e;

        }
    }

    /**
     * Search for pep record for the account holder
     *
     * @param  \Snap\App         $app
     * @param  MyAccountHolder   $accountHolder
     * @param  array             $params
     * @return MyPepSearchResult
     */
    public function searchForPepRecords($app, $accountHolder, $params)
    {
        try {
            $params = array_merge([
                'Forename'         => null,
                'Middlename'       => null,
                'Surname'          => $accountHolder->fullname,
                'DateOfBirth'      => $accountHolder->getDateOfBirth(),
                'YearOfBirth'      => $accountHolder->getYearOfBirth(),
                'Address'          => null,
                'City'             => null,
                'County'           => null,
                'Postcode'         => null,
                'Country'          => 'Malaysia'
            ], $params);

            $expiryDate = new \DateTime();
            $expiryDate->setTimezone($this->app->getServerTimezone());
            $expiryDate->sub(new \DateInterval("P{$this->pepCacheExpiry}D"));
            $expiryDate = $expiryDate->format('Y-m-d H:i:s');

            $oldSearchResult = $app->mypepsearchresultStore()
                ->searchTable()
                ->select()
                ->where('accountholderid', '=', $accountHolder->id)
                ->where('createdon', '>',  $expiryDate)
                ->one();

            if (!$oldSearchResult) {

                $newSearchResult = $app->mypepsearchresultStore()->create([
                    'provider' => self::class,
                    'request' => json_encode(['data' => $params, 'url' => $this->getApiEndpoint(self::URL_AML_PERSON)]),
                    'accountholderid' => $accountHolder->id,
                    'status' => MyPepSearchResult::STATUS_ACTIVE,
                ]);
                $newSearchResult = $app->mypepsearchresultStore()->save($newSearchResult);

                $results = $this->postAmlPersonSearch($params);

                $newSearchResult->matchescount = $results['recordsFound'];
                $newSearchResult->response = json_encode($results);
                $newSearchResult = $app->mypepsearchresultStore()->save($newSearchResult);

                return $newSearchResult;
            }

            return $oldSearchResult;
        } catch (\Snap\api\exception\ProviderApiError $e) {
            $this->log(__METHOD__ . ": Failed to parse result with error " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . ": Failed to parse result with error " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     * Search the account holder for AML records
     *
     * @param  int   $accHolderId
     * @return array
     */
    public function postAmlPersonSearch($params)
    {
        $defaultParams = [
            'apiKey' => $this->apiKey,
            'Threshold' => $this->amlThreshold,
            'PEP' => true,
            'CurrentSanctions' => false,
            'AdverseMedia' => false,
        ];

        $params = array_merge($defaultParams, $params);

        $this->log(__METHOD__ . ": Searching for person with params" . json_encode($params), SNAP_LOG_DEBUG);
        return $this->sendPepRequest($this->getPepApiEndpoint(self::URL_AML_PERSON), ['json' => $params], 'POST');
    }

    /**
     * Return the cached Pep Person PDF file or fetch from innov8tif if it is not available
     *
     * @param  int $personId
     * @return MyPepPerson
     */
    public function getPepPdf($personId)
    {
        $validFrom = new \DateTime();
        $validFrom->setTimezone($this->app->getServerTimezone());
        $validFrom->sub(new \DateInterval("P{$this->pepCacheExpiry}D"));
        $validFrom = $validFrom->format('Y-m-d H:i:s');

        $savedFile = $this->app->mypeppersonStore()
            ->searchTable()
            ->select()
            ->where('provider', self::class)
            ->where('personid', $personId)
            ->where('type', MyPepPerson::TYPE_PDF)
            ->where('createdon', '>',  $validFrom)
            ->one();

        if (!$savedFile) {

            $this->log(__METHOD__ . ": Preparing to get PDF for person id " . $personId, SNAP_LOG_DEBUG);
            $url = $this->getPepApiEndpoint(self::URL_AML_PERSON_PDF) . '/' . $personId;
            $personFile =  $this->getAmlPerson($url);

            $newPepPersonFile = $this->app->mypeppersonStore()->create([
                'provider' => self::class,
                'personid' => $personId,
                'file' => $personFile,
                'type' => MyPepPerson::TYPE_PDF,
                'status' => MyPepPerson::STATUS_ACTIVE,
            ]);

            return $this->app->mypeppersonStore()->save($newPepPersonFile);
        }

        return $savedFile;
    }

    /**
     * Return the cached Pep Person JSON file or fetch from innov8tif if it is not available
     *
     * @param  int $personId
     * @return MyPepPerson
     */
    public function getPepJson($personId)
    {
        $expiryDate = new \DateTime();
        $expiryDate->setTimezone($this->app->getServerTimezone());
        $expiryDate->sub(new \DateInterval("P{$this->pepCacheExpiry}D"));
        $expiryDate = $expiryDate->format('Y-m-d H:i:s');

        $savedFile = $this->app->mypeppersonStore()
            ->searchTable()
            ->select()
            ->where('provider', self::class)
            ->where('personid', $personId)
            ->where('type', MyPepPerson::TYPE_JSON)
            ->where('createdon', '>',  $expiryDate)
            ->one();

        if (!$savedFile) {

            $this->log(__METHOD__ . ": Preparing to get JSON for person id " . $personId, SNAP_LOG_DEBUG);

            $url = $this->getPepApiEndpoint(self::URL_AML_PERSON) . '/' . $personId;
            $personFile = $this->getAmlPerson($url);

            $newPepPersonFile = $this->app->mypeppersonStore()->create([
                'provider' => self::class,
                'personid' => $personId,
                'file' => json_encode($personFile),
                'type' => MyPepPerson::TYPE_JSON,
                'status' => MyPepPerson::STATUS_ACTIVE,
            ]);

            return $this->app->mypeppersonStore()->save($newPepPersonFile);
        }

        return $savedFile;
    }

    /**
     * Get the Person pep record from innov8tif
     *
     * @param  int $personId
     * @param  string $type
     * @return array
     */
    protected function getAmlPerson($url)
    {
        $params = [
            'apiKey' => $this->apiKey,
        ];

        $this->log(__METHOD__ . ": Getting person record at " . $url, SNAP_LOG_DEBUG);
        return $this->sendPepRequest($url, ['query' => $params], 'GET', ['headers' => null]);
    }

    /**
     * Send PEP specific HTTP request to the end point
     *
     * @param string $url
     * @param array  $options
     * @param string $method
     * @return array
     */
    protected function sendPepRequest($url, $options, $method = 'POST', $config = [])
    {
        try {
            $this->log(__METHOD__ . "(): Sending request to {$url}.", SNAP_LOG_DEBUG);
            $requestData = [
                'url' => $url,
                'options' => $options,
                'method' => $method,
            ];

            $log = $this->logApiRequest(json_encode($requestData));

            $client = new \GuzzleHttp\Client(array_merge($this->getHttpClientDefaults(false), $config));
            $response = $client->request($method, $url, $options);



            if (200 == $response->getStatusCode()) {
                $responseBody = $response->getBody()->getContents();
            } else {
                $responseBody = "Unexpected response from server with status code: " . $response->getStatusCode();
                throw new \Exception($responseBody);
            }

            if (false === mb_detect_encoding($responseBody, 'UTF-8', true)) {
                $responseBody = $this->encodeToUtf8($responseBody);
            }

            $this->logApiResponse($log->id, $responseBody);

            $jsonResponse = json_decode($responseBody, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                // JSON is valid
                return $this->handlePepApiResponse($jsonResponse);
            }

            return $responseBody;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to Innov8tif {$url} with error " . $e->getMessage() . "\nResponse:" . $responseBody, SNAP_LOG_ERROR);
            $this->logApiResponse($log->id, $responseBody);

            throw $e;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "() Unable to handle response from Innov8tif {$url} with error " . $e->getMessage() . "\nResponse:" . $responseBody, SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     * Send EKYC HTTP request to the end point
     *
     * @param string $url
     * @param array  $options
     * @param string $method
     * @return array
     */
    protected function sendRequest($url, $options, $method = 'POST')
    {
        try {
            $requestData = [
                'url' => $url,
                'method' => $method
            ];
            $log = $this->logApiRequest(json_encode($requestData));
            $client = new \GuzzleHttp\Client($this->getHttpClientDefaults(false));
            $response = $client->request($method, $url, $options);

            if (200 == $response->getStatusCode()) {
                $responseBody = $response->getBody()->getContents();
            } else {
            }
            $this->logApiResponse($log->id, $responseBody);
            return $this->handleApiResponse(json_decode($responseBody, true));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to Innov8tif {$url} with error " . $e->getMessage() . "\nResponse:" . $responseBody, SNAP_LOG_ERROR);
            $this->logApiResponse($log->id, $responseBody);

            throw $e;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "() Unable to handle response from Innov8tif {$url} with error " . $e->getMessage() . "\nResponse:" . $responseBody, SNAP_LOG_ERROR);
            throw $e;
        }
    }

    /**
     * Encode string to utf8
     *
     * @param  string $string
     * @return string
     */
    protected function encodeToUtf8($string)
    {
        $this->log(__METHOD__ . ": Encoding non UTF-8 to UTF-8", SNAP_LOG_DEBUG);
        return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "ISO-8859-1", true));
    }

    /**
     * Handle PEP response from Innov8tif either it was success or error response
     *
     * @param array $response
     */
    protected function handlePepApiResponse(array $response)
    {
        // Only check for error
        if (isset($response['status']) && self::RESPONSE_STATUS_ERROR == $response['status']) {

            return $this->handleApiResponse($response);
        }

        return $response;
    }

    /**
     * Handle response from Innov8tif either it was success or error response
     *
     * @param array $response
     */
    protected function handleApiResponse(array $response)
    {
        // Check if status is success
        if (isset($response['status']) && self::RESPONSE_STATUS_SUCCESS == $response['status']) {
            $this->log(__METHOD__ . "(): Response received with success status.", SNAP_LOG_DEBUG);
            return $response;
        }

        // If the status is error or not status, then we treat it as error
        $errorMessage = $response['message'] ?? $response['error'] ?? $response['statusMessage'] ?? 'Provider error';
        $refinedErrorMessage = $this->getRefinedErrorMessage($errorMessage);
        $this->log(__METHOD__ . "(): Response received with error message: {$refinedErrorMessage}", SNAP_LOG_ERROR);

        if (self::ERR_MISSING_PARAM_CRENDENTIAL === $errorMessage) {
            throw \Snap\api\exception\ProviderApiError::fromTransaction([], ['message' => 'The credentials for ' . __CLASS__ . ' is not valid']);
        }

        if (self::ERR_MAX_API_CALL_EXCEEDED === $errorMessage) {
            throw \Snap\api\exception\ProviderApiError::fromTransaction([], ['message' => 'The maximum API call for ' . __CLASS__ . ' has exceeded']);
        }

        throw new \Exception($refinedErrorMessage);
    }

    /**
     * Get the refined error message for the error
     *
     * @param  string $errorMessage
     * @return string
     */
    protected function getRefinedErrorMessage($errorMessage)
    {
        switch ($errorMessage) {
            case self::ERR_MISSING_PARAM_CRENDENTIAL:
                return 'Missing credential for getting journey id, error (' . $errorMessage . ')';
                break;
            case self::ERR_MAX_API_CALL_EXCEEDED:
                return 'Maximum api call is reached, error (' . $errorMessage . ')';
                break;
            case self::ERR_INVALID_JOURNEY_ID:
                return 'Invalid journey id or expired journey id is used, error (' . $errorMessage . ')';
                break;
            case self::ERR_UNRECOGNIZED_IMAGE:
                return 'Could not detect the document, error (' . $errorMessage . ')';
                break;
            case self::ERR_FACE_NOT_FOUND:
                return 'Could not detect the face image, error (' . $errorMessage . ')';
                break;
            case self::ERR_UNEXPECTED_ERROR:
                return 'Image file size is too large, error (' . $errorMessage . ')';
                break;
            case self::ERR_ERROR_NO_FACE_DETECTED:
                return 'Document or face image has no face detected, error (' . $errorMessage . ')';
                break;
            case self::ERR_INTEG_ERROR:
                return 'Missing document and face image , error (' . $errorMessage . ')';
                break;
            case self::ERR_EMPTY_DOCTYPE:
                return 'Empty doc type for OkayDoc , error (' . $errorMessage . ')';
                break;
            case self::ERR_UNRECOGNIZED_DOCTYPE:
                return 'Empty doc type for OkayDoc , error (' . $errorMessage . ')';
                break;
            case self::ERR_ERROR_IN_DOCTYPE_OR_VERSION:
                return 'Empty version for OkayDoc , error (' . $errorMessage . ')';
                break;
            case self::ERR_CONFIG_FILE_NOT_EXIST:
                return 'Scorecard config file not found / not exists , error (' . $errorMessage . ')';
                break;
            case self::ERR_USERNAME_NOT_EXIST:
                return 'Innov8tif username provided is invalid or not exists , error (' . $errorMessage . ')';
                break;
            case self::ERR_MISSING_FILE:
                return 'No document image and face image provided, error (' . $errorMessage . ')';
                break;
            case self::ERR_ERROR_IN_GETTING_SCORECARD_CONFIG:
                return 'Invalid or empty Innov8tif username , error (' . $errorMessage . ')';
                break;
            default:
                // For non coded error, we return the error message from Innov8tif
                return $errorMessage;
        }
    }

    protected function httpClientFactory($options = null)
    {
        $defaults = $this->getHttpClientDefaults();

        // Merge defaults with user-provided options
        if ($options && is_array($options) && !empty($options)) {
            // $defaults = $defaults + $options;
            array_merge_recursive($defaults, $options);
        }

        return new \GuzzleHttp\Client($defaults);
    }

    protected function getHttpClientDefaults()
    {
        return [
            'verify' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
        ];
    }

    protected function logApiRequest($request)
    {
        $log = $this->app->apiLogStore()->create([
            'type' => ApiLogs::TYPE_MYGTP_EKYC,
            'systeminitiate' => 1,
            'fromip' => 'localhost',
            'requestdata' => $request,
            'status' => 1
        ]);
        $log = $this->app->apiLogStore()->save($log);

        $data = json_encode(['requestid' => $log->id, 'requestip' => 'localhost', 'requestdata' => $request]);
        $this->log(__CLASS__ . ":: $data", SNAP_LOG_DEBUG);

        return $log;
    }

    public function logApiResponse($requestId, $response)
    {
        $log = $this->app->apiLogStore()
            ->searchTable()
            ->select()
            ->where('id', $requestId)
            ->one();

        $log->responsedata = $response;
        $log = $this->app->apiLogStore()->save($log);

        $data = json_encode(['requestid' => $log->id, 'responsedata' => $response]);
        $this->log(__CLASS__ . ":: $data", SNAP_LOG_DEBUG);

        return $log;
    }

    /**
     * Checks if the submission can be retry
     *
     * @param MyKycSubmission $myEkycSubmission
     *
     * @return bool
     */
    protected function canRetrySubmission(MyKycSubmission $myEkycSubmission)
    {
        $this->log(__METHOD__ . "() Checking if submission can be retried for account holder ({$myEkycSubmission->accountholderid})", SNAP_LOG_DEBUG);
        $key = "{ekycsubmission}:{$myEkycSubmission->id}";
        $retries = $this->app->getCache($key);

        // Allow the submission less than this amount
        return 3 > $retries;
    }

    /**
     * Increment number of retries for submission
     *
     * @param MyKycSubmission $myEkycSubmission
     * @return void
     */
    protected function incrementSubmissionRetries(MyKycSubmission $myEkycSubmission)
    {
        $this->log(__METHOD__ . "(): Incrementing submission retries for account holder ({$myEkycSubmission->accountholderid})", SNAP_LOG_DEBUG);
        $key = "{ekycsubmission}:{$myEkycSubmission->id}";
        // Increment last failed amount and keep for 5 minutes
        $this->app->getCacher()->increment($key, 1, 300);
    }

    /**
     * Process OCR data returned by provider, and check if the data matches the account
     * holder data and throw an exception if it does not match.
     *
     * @param  MyKycSubmission $submission
     * @param  array           $response
     * @return void
     */
    protected function processOkayIdOcrData($submission, $response)
    {
        $this->log(__METHOD__ . "(): Attempt to process OCR data received.", SNAP_LOG_DEBUG);

        if (self::RESPONSE_STATUS_SUCCESS !== $response['status'] || 0 === count($response['result'])) {
            $this->log(__METHOD__ . "(): No OCR result returned by the provider " . self::class, SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'OCR data could not be retreived']);
        }

        $scannedName = null;
        $scannedGender = null;
        $scannedMyKadNo = null;

        foreach ($response['result'] as $result) {
            foreach ($result['ListVerifiedFields']['pFieldMaps'] as $fieldMaps) {
                if (self::OCR_FIELD_TYPE_NAME === $fieldMaps['FieldType']) {
                    $scannedName = $fieldMaps['Field_Visual'];
                }

                if (self::OCR_FIELD_TYPE_GENDER === $fieldMaps['FieldType']) {
                    $scannedGender = $fieldMaps['Field_Visual'];
                }

                if (self::OCR_FIELD_TYPE_MYKADNO === $fieldMaps['FieldType']) {
                    $scannedMyKadNo = $fieldMaps['Field_Visual'];
                }
            }
        }

        $accHolder = $this->app->myaccountHolderStore()
            ->searchTable()
            ->select()
            ->where('id', $submission->accountholderid)
            ->one();

        // Updated by Cheok on 2020-12-23 to disable ocr matching on name
        // $name        = strtoupper(implode(' ', [$accHolder->firstname, $accHolder->middlename, $accHolder->lastname]));
        // $scannedName = strtoupper(str_replace('^', ' ', $scannedName));

        // if ($name !== $scannedName) {
        //     $this->log(__METHOD__ . "(): Registered name ($name) does not match with the OCR provided data ($scannedName).", SNAP_LOG_ERROR);
        //     throw \Snap\api\exception\EkycSubmissionInvalid::fromTransaction([], ['message' => "MyKad/MyTentera IC number ($scannedMyKadNo) does not match with the registered data ({$accHolder->mykadno})"]);
        // }
        // End update by Cheok

        if ($accHolder->mykadno != $scannedMyKadNo) {
            $this->log(__METHOD__ . "(): Registered MyKad number ($scannedMyKadNo) does not match with the OCR provided data ({$accHolder->mykadno}).", SNAP_LOG_ERROR);
            throw \Snap\api\exception\EkycSubmissionInvalid::fromTransaction([], ['message' => "MyKad/MyTentera IC number ($scannedMyKadNo) does not match with the registered data ({$accHolder->mykadno})"]);
        }
    }

    /**
     * Get the document type for the submission
     *
     * @param  string $documentType
     * @return string
     */
    protected function getDocumentType($documentType)
    {
        switch ($documentType) {
            case self::DOC_TYPE_MYTENTERA:
                return MyKycSubmission::DOC_TYPE_MYTENTERA;
                break;
            case self::DOC_TYPE_OLDIC:
                return MyKycSubmission::DOC_TYPE_MYKAD_OLD;
                break;
            case self::DOC_TYPE_NEWIC:
                return MyKycSubmission::DOC_TYPE_MYKAD;
                break;
            default:
                return MyKycSubmission::DOC_TYPE_MYKAD;
                break;
        }
    }

    /**
     * Get the field api group type
     *
     * @param  string $field
     * @return string
     */
    protected function getFieldApiType($field)
    {
        switch ($field) {
            case self::RESULT_FIELD_LANDMARK:
            case self::RESULT_FIELD_COLORDETECTION:
            case self::RESULT_FIELD_HOLOGRAPHICPHOTOQUALITYCHECK:
            case self::RESULT_FIELD_SCREENDETECTION:
            case self::RESULT_FIELD_IDNOFONTCHECK:
            case self::RESULT_FIELD_MICROPRINT:
            case self::RESULT_FIELD_HOLOGRAM:
            case self::RESULT_FIELD_HOLOGRAPHICPHOTOCOMPARISION:
                return self::API_TYPE_OKAYDOC;
                break;
            case self::RESULT_FIELD_LIVEFACECHECK:
                return self::API_TYPE_OKAYLIVE;
                break;
            case self::RESULT_FIELD_FACIALVERIFICATION:
                return self::API_TYPE_OKAYFACE;
                break;
            default:
                return self::API_TYPE_OKAYDOC;
                break;
        }
    }

    /**
     * Resize and compress the image
     *
     * @param  resource $source
     * @param  integer  $scale
     * @return resource
     */
    protected function resizeImage($source, $location, $scale = 100, $quality = 100)
    {
        $oldWidth  = imagesx($source);
        $oldHeight = imagesy($source);

        $width     = $oldWidth * $scale/100;
        $height    = ceil($oldHeight * ($width/$oldWidth));
        $image     = imagecreatetruecolor($width, $height);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);

        $exif = exif_read_data($location);
        if ($image && $exif && isset($exif['Orientation']))
        {
            $ort = $exif['Orientation'];

            if ($ort == 6 || $ort == 5)
            $image = imagerotate($image, 270, null);
            if ($ort == 3 || $ort == 4)
            $image = imagerotate($image, 180, null);
            if ($ort == 8 || $ort == 7)
            $image = imagerotate($image, 90, null);

            if ($ort == 5 || $ort == 4 || $ort == 7)
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        $temp = tmpfile();
        imagejpeg($image, $temp, $quality);
        imagedestroy($image);

        return $temp;
    }
}
