<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use \Snap\store\dbdatastore as DbDatastore;
use Snap\App;
use Snap\object\order;
use Snap\InputException;
use Snap\manager\MyGtpAccountManager;
use Snap\object\account;
use Snap\object\MyAccountHolder;
use Snap\object\rebateConfig;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mypepsearchresultHandler extends CompositeHandler
{
    function __construct(App $app)
    {
        parent::__construct('/root/bmmb', 'approval');

        $this->mapActionToRights('getPepMatchData', 'list;/root/bmmb/profile/list');
        $this->mapActionToRights('approveAccountHolder', 'list');
        $this->mapActionToRights('rejectAccountHolder', 'list');
        $this->mapActionToRights('printPepPdf', 'list;/root/bmmb/profile/list');

        $this->app = $app;
        $this->addChild(new ext6gridhandler($this, $app->mypepsearchresultStore(), 1));
    }

    function getPepMatchData($app, $params)
    {
        // Get Account Holder
        $accountHolderId = $params['accountholderid'];
        $accountHolder   = $app->myAccountHolderStore()->getById($accountHolderId);
        $gtpmanager      = $app->myGtpAccountManager();

        try {
            // Partner Obj and Account Holder Obj
            $rawresult = $gtpmanager->getPepMatches($accountHolder->getPartner(), $accountHolder);
            $records = $rawresult['matches'];

            foreach ($records as $x => $record) {

                // Set record accountholderid
                $filteredrecords[$x]['accountholderid'] = $params['accountholderid'];
                $filteredrecords[$x]['score'] = $record['score'];
                $filteredrecords[$x]['personid'] = $record['person']['id'];
                $filteredrecords[$x]['name'] = implode(' ', array_filter([$record['person']['forename'], $record['person']['middlename'], $record['person']['surname']]));
                $filteredrecords[$x]['dateofbirth'] = $record['person']['dateOfBirth'];
                $filteredrecords[$x]['ispep'] = $record['person']['isPEP'];
                $filteredrecords[$x]['peplevel'] = $record['person']['pepLevel'];

                $filteredrecords[$x]['detail'] = '<table style="width:100%;">
                        <tbody style="text-align:left">
                            <tr>
                                <td rowspan="10" style="vertical-align: top; padding-right: 10px"><img src="' .  $record['person']['imageURL'] . '" /></td>
                            </tr>
                            <tr>
                                <th style="width:24%">Title</th><td>:</td><td style="width:24%">' . $record['person']['title']['description'] . '</td>
                                <th style="width:24%">Alternative Title</th><td>:</td><td style="width:24%">' . $record['person']['alternativeTitle'] . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Nationality</th><td>:</td><td style="width:24%">' . $record['person']['nationality']['nationality'] . '</td>
                                <th style="width:24%">Gender</th><td>:</td><td style="width:24%">' . $record['person']['gender'] . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Mobile Phone</th><td>:</td><td style="width:24%">' . $record['person']['mobileNumber'] . '</td>
                                <th style="width:24%">Telephone</th><td>:</td><td style="width:24%">' . $record['person']['telephoneNumber'] . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Email</th><td>:</td><td style="width:24%">' . $record['person']['email'] . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Is Deceased</th><td>:</td><td style="width:24%">' . ($record['person']['isDeceased'] ? 'Yes' : 'No') . '</td>
                                <th style="width:24%">Date of Death</th><td>:</td><td style="width:24%">' . $record['person']['dateOfDeath'] . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Is Adverse Media</th><td>:</td><td style="width:24%">' . ($record['person']['isAdverseMedia'] ? 'Yes' : 'No') . '</td>
                                <th style="width:24%">Is Disqualified Director</th><td>:</td><td style="width:24%">' . ($record['person']['isDisqualifiedDirector'] ? 'Yes' : 'No') . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Is Financial Regulator</th><td>:</td><td style="width:24%">' . ($record['person']['isFinancialRegulator'] ? 'Yes' : 'No') . '</td>
                                <th style="width:24%">Is Law Enforcement</th><td>:</td><td style="width:24%">' . ($record['person']['isLawEnforcement'] ? 'Yes' : 'No') . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Is Sanction Previous</th><td>:</td><td style="width:24%">' . ($record['person']['isSanctionsCurrent'] ? 'Yes' : 'No') . '</td>
                                <th style="width:24%">Is Sanction Current</th><td>:</td><td style="width:24%">' . ($record['person']['isSanctionsPrevious'] ? 'Yes' : 'No') . '</td>
                            </tr>
                            <tr>
                                <th style="width:24%">Is Insolvent</th><td>:</td><td style="width:24%">' . ($record['person']['isInsolvent'] ? 'Yes' : 'No') . '</td>
                            </tr>
                        </tbody>
                    </table>';
            }

            echo json_encode(['success' => true, 'records' => $filteredrecords]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    /**
     * Get the PDF file for the pep person using Person Id, person id can be retrieved using getPepMatches()
     *
     * @param  Partner $partner
     * @param  int $personId
     * @return string
     */

    public function printPepPdf($app, $params)
    {
        try {
            $accountholder = $app->myaccountholderStore()->getById($params['accountholderid']);
            $partner = $app->partnerStore()->getById($accountholder->partnerid);
            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $this->app->myGtpAccountManager();

            // Partner Obj and PEP Person Id
            $content = $accMgr->getPepPdfForPerson($partner, $params['personid']);

            header('Content-Type: application/pdf');
            header("Content-Length: " . strlen($content));
            header('Content-disposition: inline; filename="summary.pdf"');
            header('Cache-Control: public, must-revalidate, max-age=3600');
            header('Pragma: public');

            echo $content;
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function approveAccountHolder($app, $params)
    {
        try {

            $accountHolder = $app->myaccountholderStore()->getById($params['id']);

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $accMgr->approveAccountHolder($accountHolder, $params['remarks'] ?? null);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function rejectAccountHolder($app, $params)
    {
        try {

            if (!isset($params['remarks']) || 0 == strlen($params['remarks'])) {
                throw new \Snap\InputException(gettext("Remarks is required for rejection"), \Snap\InputException::GENERAL_ERROR, 'remarks');
            }

            $accountholder = $app->myaccountholderStore()->getById($params['id']);

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $this->app->mygtpAccountManager();
            $accMgr->rejectAccountHolder($accountholder, $params['remarks']);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }
}
