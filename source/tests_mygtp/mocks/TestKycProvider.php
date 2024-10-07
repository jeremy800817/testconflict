<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

use Snap\object\MyAccountHolder;
use Snap\object\MyKYCResult;
use Snap\object\MyKYCSubmission;

/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 23-Oct-2020
 */

class TestKycProvider implements Snap\IEkycProvider {

    /** @var \Snap\object\MyAccountHolder $accountHolder */
    private $accountHolder = null;
    private $app = null;

    function createSubmission($app, $accountHolder, $params)
    {
        $this->app = $app;
        $this->accountHolder = $accountHolder;
        $submission = $app->mykycsubmissionStore()->create([
            'mykadfront'    => $params['mykadfront'] ?? '-',
            'mykadback'         => $params['mykadfront'] ?? '-',
            'faceimage'         => $params['mykadfront'] ?? '-',
            'accountholderid'         => $accountHolder->id,
            'status'        => MyKYCSubmission::STATUS_PENDING_SUBMISSION,
        ]);
        $submission = $app->mykycsubmissionStore()->save($submission);

        return $submission;

    }

    function submitSubmission($app, $submission)
    {
        $submission->status = MyKYCSubmission::STATUS_PENDING_RESULT;
        $submission = $app->mykycsubmissionStore()->save($submission);
        return $submission;
    }
    
    function getResult($app, $submission)
    {
        $myEkycResult = $app->mykycResultStore()->create([
            'provider' => self::class,
            'submissionid' => $submission->id,
            'result'     => MyKYCResult::RESULT_PASSED,
            'status' => MyKYCResult::STATUS_ACTIVE,
        ]);

        $myEkycResult = $app->mykycResultStore()->save($myEkycResult);
        // $this->onSubmissionResultReceived($app, $submission);
    }

    function canSubmitToProvider($submission)
    {
        return true;
    }

    // function onSubmissionResultReceived($app, )
    // {
    //     if (true === $result['kycpass']) {
    //         $this->accountHolder->kycstatus = MyAccountHolder::KYC_PASSED;
    //     }
    //     $this->app->myaccountholderStore()->save($this->accountHolder);
    // }

}