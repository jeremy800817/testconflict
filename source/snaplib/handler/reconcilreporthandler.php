<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use \Snap\store\dbdatastore as DbDatastore;
use Snap\App;
use \Snap\object\Documents;
use \Snap\object\VaultItem;
use \Snap\object\Partner;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Html;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Haris Jumail(jumail@silverstream.my)
 * @version 1.0
 */
class reconcilreporthandler extends CompositeHandler
{
    function __construct(App $app)
    {

        //parent::__construct('/root/mbb;/root/bmmb;/root/go;/root/one;', 'vault');
        $this->mapActionToRights('list', '/all/access');
        $this->mapActionToRights('vaultdata', '/all/access');
        $this->mapActionToRights('listofdata', '/all/access');
        $this->mapActionToRights('matchtransaction', '/all/access');
        $this->mapActionToRights('missingmbb', '/all/access');
        $this->mapActionToRights('missinggpt', '/all/access');

        $this->app = $app;
        $orderStore = $app->orderStore();
        $this->currentStore = $orderStore;
        $this->addChild(new ext6gridhandler($this, $orderStore, 1));
    }



    function onPreListing($objects, $params, $records)
    {

        $app = App::getInstance();

        $userType = $this->app->getUserSession()->getUser()->type;

        foreach ($records as $key => $record) {
            // Acquire progress percentage.
            if (!$records[$key]['movetovaultlocationname']) {
                $records[$key]['movetovaultlocationname'] = "-";
            }
            if (!$records[$key]['newvaultlocationname']) {
                $records[$key]['newvaultlocationname'] = "-";
            }
        }

        return $records;
    }

    function gettxtdata()
    {

        $filename = 'arb_GOLD_ACE_AUTO_RECON_20230708.txt';
        // Read the entire file as an array of lines
        $fileLines = file($filename);

        // Initialize an empty array
        $fieldValues = [];

        // Loop through each line
        foreach ($fileLines as $line) {
            // Split the line into an array using the pipe symbol as the delimiter
            $data = explode('|', $line);

            // Get the desired field number (e.g., field number 2)
            $fieldNumber = 9;
            $fieldValue = $data[$fieldNumber];

            // Push the field value into the array
            $fieldValues[] = $fieldValue;
        }

        // Join the field values into a comma-separated string
        $fieldValuesString = implode(',', $fieldValues);

        // Remove the trailing comma from the string
        $fieldValuesString = rtrim($fieldValuesString, ',');

        return $fieldValuesString;
    }

    function missinggpt($app, $params)
    {

        $transdatec =  $params['transdatec'];

        $path = $app->getConfig()->{'mygtp.acereport.ftp'}; //change based on partner 

        $combinedContents = '';

        foreach (glob($path . '*.txt') as $filename) {
            $fileLines = file($filename);
            $combinedContents .= implode('', $fileLines);
        }

        $fileLines = explode("\n", $combinedContents);
        $typeoftransactionr = [];
        $ammountr = [];
        $remarksar = [];
        $transdater = [];
        $txncoder = [];

        foreach ($fileLines as $line) {

            $data = explode('|', $line);
            $date = $data[0];
            $transdate = $data[0];
            $txncode = $data[6];
            $typeoftransaction = $data[7];
            $ammount = $data[8];
            $remark = $data[9];
            $typeoftransactionr[] = $typeoftransaction;
            $ammountr[] = $ammount;
            if (!empty($remark)) {
                $remarksar[] = $remark;
            } else {
                $remarksar[] = "1231235454545sdsd";
            }
            $transdater[] = $transdate;
            $txncoder[] = $txncode;
        }


        if ($transdatec) {
            $date = substr($transdatec, 0, 10);
            $date = "%" . $date . "%";
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date);
        } else {
            $date = date('Y-m-d');
            $date = "%" . $date . "%";
            // $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->whereNotIn('refno', $remarksar)->execute();
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date)->execute();
        }

        $product = array();
        foreach ($orderlist as $orderlist) {
            $gtrRefnos[] = $orderlist->refno;
        }
        $unmatchedValues = array_diff($remarksar, $gtrRefnos);
        $unmatchedIndices = array_keys(array_intersect($remarksar, $unmatchedValues));

        foreach ($unmatchedIndices as $index) {
            $datetransaction = $transdater[$index];
            $typeoftransaction = $typeoftransactionr[$index];
            $ammount =  number_format($ammountr[$index], 2);
            $txncode = $txncoder[$index];

            $order[] = array(
                'ids' => $orderlist->id,
                'gtpamountc' => $ammount,
                'typeoftransactionc' => $typeoftransaction,
                'transdatec' => $datetransaction,
                'txncode' => $txncode

            );
        }

        echo json_encode(array('records' => $order));
    }

    function missingmbb($app, $params)
    {

        $transdatec =  $params['transdatec'];

        $path = $app->getConfig()->{'mygtp.acereport.ftp'}; //change based on partner 

        $combinedContents = '';

        foreach (glob($path . '*.txt') as $filename) {
            $fileLines = file($filename);
            $combinedContents .= implode('', $fileLines);
        }

        $fileLines = explode("\n", $combinedContents);
        $typeoftransactionr = [];
        $ammountr = [];
        $remarksar = [];
        $transdater = [];
        $txncoder = [];

        foreach ($fileLines as $line) {
            // $data = explode('|', $line);
            // $date = $data[0];
            // $transdate = $data[0];
            // $txncode = $data[6];
            // $typeoftransaction = $data[7];
            // $ammount = $data[8];
            // $remark = $data[9];
            // $typeoftransactionr[] = $typeoftransaction;
            // $ammountr[] = $ammount;
            // $remarksar[] = $remark;
            // $transdater[] = $transdate;
            // $txncoder[] = $txncode;

            $data = explode('|', $line);
            $date = $data[0];
            $transdate = $data[0];
            $txncode = $data[6];
            $typeoftransaction = $data[7];
            $ammount = $data[8];
            $remark = $data[9];

            // if (!empty($typeoftransaction)) {
            $typeoftransactionr[] = $typeoftransaction;
            // }
            //  if (!empty($ammount)) {
            $ammountr[] = $ammount;
            //  }
            if (!empty($remark)) {
                $remarksar[] = $remark;
            } else {
                $remarksar[] = "nonelllll";
            }
            // if (!empty($transdate)) {
            $transdater[] = $transdate;
            // }
            //if (!empty($txncode)) {
            $txncoder[] = $txncode;
            //}
        }
        $fieldValuesString = implode(',', $remarksar);
        $fieldValuesString = rtrim($fieldValuesString, ',');
        // print_r($remarksar);
        // echo "<br>";
        // die();

        //  $remarksar = ['GT202304141100001', 'GT202304141100002', ''];
        // print_r($remarksar);
        // die();

        if ($transdatec) {
            $date = substr($transdatec, 0, 10);
            $date = "%" . $date . "%";
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date)->whereNotIn('refno', $remarksar)->execute();
        } else {
            $date = date('Y-m-d');
            $date = "%" . $date . "%";
            // $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->whereNotIn('refno', $remarksar)->execute();
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date)->whereNotIn('refno', $remarksar)->execute();
        }

        $product = array();
        foreach ($orderlist as $orderlist) {
            $ammount = null;
            $typeoftransaction = null;
            $datetransaction = null;
            $txncode = null;
            foreach ($remarksar as $remark) {
                if ($remark == $orderlist->refno) {
                    $matchingRemark = $remark;
                    $index = array_search($remark, $remarksar);
                    $datetransaction = $transdater[$index];
                    $typeoftransaction = $typeoftransactionr[$index];
                    $ammount =  number_format($ammountr[$index], 2);
                    $txncode = $txncoder[$index];
                    break;
                }
            }
            $order[] = array(
                'ids' => $orderlist->id,
                'transdates' => date_format($orderlist->createdon, 'Ymd'),
                'transactioncodes' => $orderlist->orderid,
                'pricequeryids' => '',
                'typeoftransactioncs' => $orderlist->ordtype,
                'gtpgrams' => $orderlist->ordxau,
                'gtpreversals' => 'N',
                'gtpgoldprices' => $orderlist->ordfpprice,
                'gtpamounts' => number_format($orderlist->ordamount, 2),
                'refno' => $orderlist->refno,
                'gtpamountc' => $ammount,
                'typeoftransactionc' => $typeoftransaction,
                'transdatec' => $datetransaction,
                'txncode' => $txncode

            );
        }
        echo json_encode(array('records' => $order));
    }
    function matchtransaction($app, $params)
    {

        $transdatec =  $params['transdatec'];

        $path = $app->getConfig()->{'mygtp.acereport.ftp'}; //change based on partner 

        $combinedContents = '';

        foreach (glob($path . '*.txt') as $filename) {
            $fileLines = file($filename);
            $combinedContents .= implode('', $fileLines);
        }

        $fileLines = explode("\n", $combinedContents);
        $typeoftransactionr = [];
        $ammountr = [];
        $remarksar = [];
        $transdater = [];
        $txncoder = [];

        foreach ($fileLines as $line) {
            $data = explode('|', $line);
            $date = $data[0];
            $transdate = $data[0];
            $txncode = $data[6];
            $typeoftransaction = $data[7];
            $ammount = $data[8];
            $remark = $data[9];
            $typeoftransactionr[] = $typeoftransaction;
            $ammountr[] = $ammount;
            if (!empty($remark)) {
                $remarksar[] = $remark;
            } else {
                $remarksar[] = "nonelllll";
            }

            $transdater[] = $transdate;
            $txncoder[] = $txncode;
        }
        $fieldValuesString = implode(',', $remarksar);
        $fieldValuesString = rtrim($fieldValuesString, ',');
        // echo $fieldValuesString;
        // die();
        //print_r($remarksar);
        // die();
        if ($transdatec) {
            $date = substr($transdatec, 0, 10);
            $date = "%" . $date . "%";
            //echo $date;
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date)->whereIn('refno', $remarksar)->execute();
        } else {
            $date = date('Y-m-d');
            // echo $date;
            // die();
            $date = "%" . $date . "%";
            //$orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->whereIn('refno', $remarksar)->execute();
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date)->whereIn('refno', $remarksar)->execute();
        }

        $product = array();
        foreach ($orderlist as $orderlist) {
            $ammount = null;
            $typeoftransaction = null;
            $datetransaction = null;
            $txncode = null;
            foreach ($remarksar as $remark) {
                if ($remark == $orderlist->refno) {
                    $matchingRemark = $remark;
                    $index = array_search($remark, $remarksar);
                    $datetransaction = $transdater[$index];
                    $typeoftransaction = $typeoftransactionr[$index];
                    $ammount = number_format($ammountr[$index], 2);
                    $txncode = $txncoder[$index];
                    break;
                }
            }
            $order[] = array(
                'ids' => $orderlist->id,
                'transdates' => date_format($orderlist->createdon, 'Ymd'),
                'transactioncodes' => $orderlist->orderid,
                'pricequeryids' => '',
                'typeoftransactioncs' => $orderlist->ordtype,
                'gtpgrams' => $orderlist->ordxau,
                'gtpreversals' => 'N',
                'gtpgoldprices' => $orderlist->ordfpprice,
                'gtpamounts' => number_format($orderlist->ordamount, 2),
                'refno' => $orderlist->refno,
                'gtpamountc' =>  $ammount,
                'typeoftransactionc' => $typeoftransaction,
                'transdatec' => $datetransaction,
                'txncode' => $txncode

            );
        }
        echo json_encode(array('records' => $order));
    }


    function unmatchtransactionstatus($app, $params)
    {

        $transdatec =  $params['transdatec'];

        $path = $app->getConfig()->{'mygtp.acereport.ftp'}; //change based on partner 

        $combinedContents = '';

        foreach (glob($path . '*.txt') as $filename) {
            $fileLines = file($filename);
            $combinedContents .= implode('', $fileLines);
        }

        $fileLines = explode("\n", $combinedContents);
        $typeoftransactionr = [];
        $ammountr = [];
        $remarksar = [];
        $transdater = [];
        $txncoder = [];

        foreach ($fileLines as $line) {
            $data = explode('|', $line);
            $date = $data[0];
            $transdate = $data[0];
            $txncode = $data[6];
            $typeoftransaction = $data[7];
            $ammount = $data[8];
            $remark = $data[9];
            $typeoftransactionr[] = $typeoftransaction;
            $ammountr[] = $ammount;
            if (!empty($remark)) {
                $remarksar[] = $remark;
            } else {
                $remarksar[] = "nonelllll";
            }

            $transdater[] = $transdate;
            $txncoder[] = $txncode;
        }
        $fieldValuesString = implode(',', $remarksar);
        $fieldValuesString = rtrim($fieldValuesString, ',');
        if ($transdatec) {
            $date = substr($transdatec, 0, 10);
            $date = "%" . $date . "%";
            //echo $date;
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date)->whereIn('refno', $remarksar)->execute();
        } else {
            $date = date('Y-m-d');
            // echo $date;
            // die();
            $date = "%" . $date . "%";
            //$orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->whereIn('refno', $remarksar)->execute();
            $orderlist = $this->app->mygoldtransactionStore()->searchView()->select()->where('createdon', 'like', $date)->whereIn('refno', $remarksar)->execute();
        }

        $product = array();
        foreach ($orderlist as $orderlist) {
            $ammount = null;
            $typeoftransaction = null;
            $datetransaction = null;
            $txncode = null;
            foreach ($remarksar as $remark) {
                if ($remark == $orderlist->refno) {
                    $matchingRemark = $remark;
                    $index = array_search($remark, $remarksar);
                    $datetransaction = $transdater[$index];
                    $typeoftransaction = $typeoftransactionr[$index];
                    $ammount = number_format($ammountr[$index], 2);
                    $txncode = $txncoder[$index];
                    break;
                }
            }
            $order[] = array(
                'ids' => $orderlist->id,
                'transdates' => date_format($orderlist->createdon, 'Ymd'),
                'transactioncodes' => $orderlist->orderid,
                'pricequeryids' => '',
                'typeoftransactioncs' => $orderlist->ordtype,
                'gtpgrams' => $orderlist->ordxau,
                'gtpreversals' => 'N',
                'gtpgoldprices' => $orderlist->ordfpprice,
                'gtpamounts' => number_format($orderlist->ordamount, 2),
                'refno' => $orderlist->refno,
                'gtpamountc' =>  $ammount,
                'typeoftransactionc' => $typeoftransaction,
                'transdatec' => $datetransaction,
                'txncode' => $txncode

            );
        }
        echo json_encode(array('records' => $order));
    }
}
