<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use DateTime;
use Snap\App;
use Snap\object\OtcUserActivityLog;
use Snap\object\MyLocalizedContent;
Use \Snap\object\Partner;
use Snap\sqlrecorder;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */

class summaryhandler extends CompositeHandler
{
    function __construct(App $app)
    {   
        $this->mapActionToRights('list', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;');
        $this->mapActionToRights('getSummaryData', '/all/access');
    }

    public function getSummaryData($app, $params){

        try{
            
            $startDate = $params['startDate'];
            $endDate = $params['endDate']; 
            $branch = $params['branch']; 
            $totalAccountsgoldpurchased = 50;
            $totalAccountsgoldconvert  = 50;
            $totalAccountsgoldsold = 50;
            $totalGramspurchased = 50; 
            $totalGramsconvert = 50;
            $totalGramssold = 50;
            $totalRMpurchased = 50; 
            $totalRMConvert = 50;
            $totalRMsold = 50;
            $IndividualAcc = 50; 
            $JointAcc = 50;
            $CompanyAcc = 50; 
            $AccBalance = 50; 
            $AccNilBalance = 50;

            $data = [ 
                'startDate' =>$startDate,
                'endDate' =>$endDate,
                'branch' =>$branch,
                'totalAccountsgoldpurchased' =>$totalAccountsgoldpurchased,
                'totalAccountsgoldconvert' =>$totalAccountsgoldconvert,
                'totalAccountsgoldsold' =>$totalAccountsgoldsold,
                'totalGramspurchased' =>$totalGramspurchased, 
                'totalGramsconvert' =>$totalGramsconvert,
                'totalGramssold' =>$totalGramssold,
                'totalRMpurchased' =>$totalRMpurchased,
                'totalRMConvert' =>$totalRMConvert,
                'totalRMsold' =>$totalRMsold,
                'IndividualAcc' =>$IndividualAcc,
                'JointAcc' =>$JointAcc,
                'CompanyAcc' =>$CompanyAcc,
                'AccBalance' =>$AccBalance,
                'AccNilBalance' =>$AccNilBalance,
            ];

            // Return the data as a JSON response
            echo json_encode(['success' => true, 'data' => $data]);

        }catch (\Exception $e) {
             echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
         }
    }


}
