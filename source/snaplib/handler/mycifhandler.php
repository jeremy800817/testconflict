<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyAccountHolder;
use Snap\object\MyAddress;
use Snap\object\MyKYCResult;
use Snap\object\MyKYCSubmission;
use Snap\object\MyLocalizedContent;
use Snap\object\MyScreeningMatchLog;
use Snap\object\MyKYCOperatorLogs;
use Spipu\Html2Pdf\Html2Pdf;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 */
class mycifhandler extends CompositeHandler
{
    function __construct(App $app)
    {
        // parent::__construct('/root/bmmb', 'profile');
        $this->app = $app;
        $this->mapActionToRights('getmycifdata', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/noor/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');
        $this->mapActionToRights('printPepQuestionnaire', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/noor/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');
        $this->addChild(new ext6gridhandler($this, $app->myaccountholderStore(), 1));
    }


    public function getMyCifData($app, $params)
    {
        /** @var MyAccountHolder $accountHolder */
        $accountHolder = $app->myaccountholderStore()->searchView()->select()->find($params['id']);
        
        /** @var MyKYCSubmission $myKycSubmission */
        $myKycSubmission = $app->mykycsubmissionStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', $accountHolder->id)
            ->orderBy('createdon', 'DESC')
            ->one();

        /** @var MyScreeningMatchLog $myScreeningMatchLog */
        $myScreeningMatchLog = $app->myscreeningmatchlogStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', '=', $accountHolder->id)
            ->orderBy('createdon', 'DESC')
            ->one();

        // Check if KYC is manually submitted 
        // Get latest log
        $kycOperatorLogs = $this->app->mykycoperatorlogsStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', $accountHolder->id)
            ->andWhere('type', MyKYCOperatorLogs::TYPE_APPROVE)
            ->orderBy('createdon', 'DESC')
            ->one();

        $occupationCategory = $app->myoccupationcategoryStore()->getById($accountHolder->occupationcategoryid);
        $occupationCategory->language = MyLocalizedContent::LANG_ENGLISH;


        $profile = [
            'information' => [
                'fullname' => $accountHolder->fullname,
                'mykadno' => $accountHolder->mykadno,
                'accountholdercode' => $accountHolder->accountholdercode,
                'occupationcategory' => $occupationCategory->name,
                'occupation' => $accountHolder->occupation,
                'acebuycode' => $accountHolder->acebuycode,
                'acesellcode' => $accountHolder->acesellcode,
                'branchcode' => $accountHolder->branchcode,
                'lastloginip' => $accountHolder->lastloginip,
                'lastloginon' => $accountHolder->lastloginon ? $accountHolder->lastloginon->format('Y-m-d H:i:s') : '',
                'verification' => $accountHolder->getEkycStatusString(),
                'verifiedon' => $accountHolder->verifiedon ? $accountHolder->verifiedon->format('Y-m-d H:i:s') : '',
                'emailtriggeredon' => $accountHolder->emailtriggeredon ? $accountHolder->emailtriggeredon->format('Y-m-d H:i:s') : '',
                'investmentmade' => $accountHolder->investmentmade ? gettext('Yes') : gettext('No'),
                'ispep' => $accountHolder->isPep(),
                'status' => $accountHolder->getStatusString()
            ],
            'contact' => [
                'email' => $accountHolder->email,
                'phoneno' => $accountHolder->phoneno,
                'line1' => $accountHolder->addressline1,
                'line2' => $accountHolder->addressline2,
                'city' => $accountHolder->addresscity,
                'postcode' => $accountHolder->addresspostcode,
                'state' => $accountHolder->addressstate,
            ],
            'nextofkin' => [
                'nokfullname' => $accountHolder->nokfullname,
                'nokmykadno' => $accountHolder->nokmykadno,
                'nokbankname' => $accountHolder->nokbankname,
                'nokaccountnumber' => $accountHolder->nokaccountnumber,
            ],
            'bankinginfo' => [
                'bankname' => $accountHolder->bankname,
                'accountname' => $accountHolder->accountname,
                'accountnumber' => $accountHolder->accountnumber,
            ],
        ];

        if ($accountHolder->ekycIncomplete()) {
            $kycStatus = 'Incomplete';
        }

        if ($accountHolder->ekycPending()) {
            $kycStatus = 'Pending';
        }

        if ($accountHolder->ekycPassed()) {
            $kycStatus = 'Passed';
        }

        if ($accountHolder->ekycFailed()) {
            $kycStatus = 'Failed';
        }

        if ($myKycSubmission) {

            $faceImageBase64 = $myKycSubmission->getFaceImage();
            $imageInfo = getimagesizefromstring(base64_decode($faceImageBase64));
            $image  = '<img width="150" src="data:' . $imageInfo['mime'] . ';base64,' . $faceImageBase64 . '">';
    
            $myKadFrontImageBase64 = $myKycSubmission->getMyKadFrontImage();
            $imageInfo = getimagesizefromstring(base64_decode($myKadFrontImageBase64));
            $myKadFrontImage = '<div style="display: table-cell;height: 250px;text-align: center;width: 360px;vertical-align: middle;"><img style="max-width:350px;max-height:230px" src="data:' . $imageInfo['mime'] . ';base64,' . $myKadFrontImageBase64 . '"></div>';
    
            $myKadBackImageBase64 = $myKycSubmission->getMyKadBackImage();
            $imageInfo = getimagesizefromstring(base64_decode($myKadBackImageBase64));
            $myKadBackImage = '<div style="display: table-cell;height: 250px;text-align: center;width: 360px;vertical-align: middle;"><img style="max-width:350px;max-height:230px" src="data:' . $imageInfo['mime'] . ';base64,' . $myKadBackImageBase64 . '"></div>';

            /** @var MyKYCResult $result */
            $result = $myKycSubmission->getResult();
            
            // Get Journey ID if any
            $journeyid = $myKycSubmission->journeyid;

        } else {
            $image = '<div style="width: 150px;height: 190px;background: #ececec;"></div>';
            $myKadFrontImage = '<div style="display: table-cell;height: 250px;text-align: center;width: 360px;vertical-align: middle;"><img style="max-width:350px;max-height:230px"></div>';    
            $myKadBackImage = '<div style="display: table-cell;height: 250px;text-align: center;width: 360px;vertical-align: middle;"><img style="max-width:350px;max-height:230px"></div>';
            $result = null;
            $journeyid = null;
        }

        // Check logs for any manual ekyc approval
        if($kycOperatorLogs){
            $kycmanualapproveon = $kycOperatorLogs->createdon ? $kycOperatorLogs->createdon->format('Y-m-d H:i:s') : '';
            $manualapprover = $this->app->userStore()->getById($kycOperatorLogs->approvedby);
            $kycmanualapproveby = $manualapprover->name;

            $kycmanualapproveremarks = $kycOperatorLogs->remarks;

        }else{
            $kycmanualapproveon = null;
            $kycmanualapproveby = null;
            $kycmanualapproveremarks = null;

        }

        // Query and check for any records
        $kycremindercount = $this->app->mykycreminderStore()->searchTable()->select()->where('accountholderid', $params['id'])->count();

        $kyc = [
            'mykadfrontimage' => $myKadFrontImage,
            'mykadbackimage' => $myKadBackImage,
            'kycstatus' => $kycStatus,
            'documenttype' => $myKycSubmission->doctype,
            'submissionstatus' => $myKycSubmission ? $myKycSubmission->getStatusString() : 'N/A',
            'result' =>  $result ? $result->getResultString() : 'N/A',
            'journeyid' =>  $journeyid ? $journeyid : 'N/A',
            'lastsubmissionon' => $myKycSubmission->submittedon ? $myKycSubmission->submittedon->format('Y-m-d H:i:s') : '',
            'remarks' => $myKycSubmission->remarks,
            'iskycmanualapproved' =>  $accountHolder->isKYCManualApproved(),
            'iskycmanualapproveddisplay' =>  ($accountHolder->isKYCManualApproved() == true )? 'Yes' : 'No',
            'kycmanualapproveon' =>  $kycmanualapproveon ? $kycmanualapproveon : 'N/A',
            'kycmanualapproveby' =>  $kycmanualapproveby ? $kycmanualapproveby : 'N/A',
            'kycmanualapproveremarks' =>  $kycmanualapproveremarks ? $kycmanualapproveremarks : 'N/A',
            // New fields for email logs
            'accountholderid' =>  $params['id'] ? $params['id'] : 'N/A',
            'accountholdername' =>  $accountHolder->fullname ? $accountHolder->fullname : 'N/A',
            'kycremindercount' =>  $kycremindercount ? $kycremindercount : 0,
        ];

        $questionnaire = $this->generatePepQuestionnaire(json_decode($accountHolder->pepdeclaration, true));

        if ($accountHolder->pepPending()) {
            $pepStatus = 'Pending';
        }

        if ($accountHolder->pepPassed()) {
            $pepStatus = 'Passed';
        }

        if ($accountHolder->pepFailed()) {
            $pepStatus = 'Failed';
        }

        if (! $accountHolder->isPep()) {
            $pepStatus = 'N/A';
        }

        $pep = [
            'questionnaire' => $questionnaire,
            'pepstatus' => $pepStatus,
            'remarks' => $accountHolder->statusremarks
        ];

        $unchargedbalance = $app->myGtpAccountManager()->getAccountHolderUnchargedStorageFees($accountHolder);
        $currentGoldBalance = $accountHolder->getCurrentGoldBalance();

        // Check if account is closed, if account is closed set available balance to 0
        $isClosed = $accountHolder->isClosed();
        $goldbalance = [
            'availablebalance' => number_format($currentGoldBalance - $unchargedbalance, 3, '.', ''),
            'totalbalance'     => number_format($currentGoldBalance, 3, '.', ''),
            'unchargedbalance' => number_format($unchargedbalance, 3, '.', ''),
            'isclosed'         => $isClosed,
        ];


        $amla = [
            'amlastatus'      => $accountHolder->getAmlaStatusString(),
            'remarks'         => $myScreeningMatchLog ? $myScreeningMatchLog->remarks : '',
            'lastscreeningon' => $myScreeningMatchLog ? $myScreeningMatchLog->matchedon->format('Y-m-d H:i:s') : '',
        ];

        echo json_encode(array('success' => true, 'image' => $image, 'accountholder' => $profile, 'kyc' => $kyc, 'pep' => $pep, 'goldbalance' => $goldbalance, 'amla' => $amla));
    }
    
    public function printPepQuestionnaire($app, $params)
    {
        /** @var MyAccountHolder $accountHolder */
        $accountHolder = $app->myaccountholderStore()->getById($params['accountholderid']);
        $questionnaire = $this->generatePepQuestionnaire(json_decode($accountHolder->pepdeclaration, true));

        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');

        $content = '<table style="width: 100%;">
            <tr>
                <td>
                <h3>PEP Declaration</h3>
                <span><p>Full Name		   : ' . $accountHolder->fullname .'</p></span>
                <span><p>NRIC No           : ' . $accountHolder->mykadno . '</p></span>
                </td>
            </tr>
            <tr>
                <td><hr></td>
            </tr>
            <tr>
                <td>
                <span>'. $questionnaire.'</span>
                </td>
            </tr>
        </table>';

        $html2pdf->writeHTML($this->completePdf($content));
        return $html2pdf->output('GTP_PEP_' . $accountHolder->mykadno . '.pdf');
    }

    protected function generatePepQuestionnaire($declaration)
    {
        $questionOnly = $this->getQuestionnaireQuestion($declaration['metadata']['version'], $declaration['metadata']['language']);

        $questionnaire = [];
        foreach ($declaration['questions'] as $question) {
            $questionId = $question['id'];
            $questionText = $questionOnly[$questionId];
            $questionnaire[$questionId]['question'] = $questionText;
            $questionnaire[$questionId]['answers'] = $question['answers'];
        }

        $template = $this->getQuestionnaireTemplate();
        return $this->applyTemplate($questionnaire, $template);
    }

    /**
     * Get the  questionnaire question for the given version and language
     *
     * @param  int $version
     * @param  string $language
     * @return array
     */
    protected function getQuestionnaireQuestion($version, $language)
    {
        // [ Version => [ Language => [ QuestionId => ... ]]]
        $question = [
            5 => [
                MyAccountHolder::LANG_EN => [
                    1 => "1. Have you ever been categorized as a 'PEP' (Politically Exposed Person) by a bank, brokerage firm or any financial institution?",
                    2 => "2. Are you, an immediate family member/close business associate/relative/close friend, to an individual who is currently or formally qualify as one of the PeP categories below?",
                    3 => "3. If the answer to the above question is 'Yes', then please select all that apply (source of income)",
                    4 => "4. Primary Applicant?",
                    5 => "5. Source of wealth"
                ],
            ]
        ];

        return $question[$version][$language] ?? [];
    }

    protected function getQuestionnaireTemplate()
    {
        $html = <<<'HTML'
##QUESTIONSTART##
<p><strong>##QUESTION##</strong></p>
<ul style="list-style-type:none">
    ##ANSWERSTART##
    <li>##ANSWER##</li>
    ##ANSWEREND##
</ul>
##QUESTIONEND##
HTML;

        return $html;
    }

    protected function applyTemplate($questionnaire, $template)
    {
        preg_match('/((?<=##QUESTIONSTART##)[\s\S]*?(?=##QUESTIONEND##))/', $template, $matches);


        $replacements = '';
        foreach ($questionnaire as $question) {
            $tags = ['##QUESTION##'];
            $fillers = [$question['question']];
            $replacements .= str_replace($tags, $fillers, $matches[0]);

            preg_match('/((?<=##ANSWERSTART##)[\s\S]*?(?=##ANSWEREND##))/', $template, $matches2);
            $answers = '';
            foreach ($question['answers'] as $answer) {
                $tags = ['##ANSWER##'];
                $fillers = [$answer['value']];
                $answers .= str_replace($tags, $fillers, $matches2[0]);
            }
            $replacements = preg_replace('/##ANSWERSTART##[\s\S]*##ANSWEREND##/', $answers, $replacements);
        }
        return preg_replace('/##QUESTIONSTART##[\s\S]*##QUESTIONEND##/', $replacements, $template);
    }

    private function completePdf($content, $showHeader = false, $showFooter = false){

        $wrap_start = '<page backtop="10mm" backbottom="10mm" backleft="20mm" backright="20mm">';
        $wrap_end = '</page>';

        $header = $showHeader ? $this->getHeader() : '';
        $footer = $showFooter ? $this->getFooter() : '';
        $html = $wrap_start.$header.$footer.$content.$wrap_end;

        return $html;
	}

	private function getHeader($draft = false){
        $header = '
            <page_header>

                <table style="width: 100%; border: solid 1px black;">
                    <tr>
                        <td style="text-align: left;    width: 33%">html2pdf</td>
                        <td style="text-align: center;    width: 34%">Test dheader</td>
                        <td style="text-align: right;    width: 33%">'.date("Y").'</td>
                    </tr>
                </table>
            </page_header>
        ';


        return $header;
    }

    private function getFooter($draft = false){
        $footer = '
            <page_footer>
            '.date('d/m/Y').'
            </page_footer>
        ';

        return $footer;
	}
}
