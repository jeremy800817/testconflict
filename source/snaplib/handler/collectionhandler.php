<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

USe Snap\App;
use Snap\object\partner;
use Snap\object\GoodsReceiveNote;
use Snap\InputException;

class collectionHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        parent::__construct('/root/gtp', 'collection');
        $this->mapActionToRights("list", "list");
        $this->mapActionToRights("add", "add");
        $this->mapActionToRights("edit", "edit");
        $this->mapActionToRights("delete", "delete");
        $this->mapActionToRights("freeze", "freeze");
        $this->mapActionToRights("unfreeze", "unfreeze");
        $this->mapActionToRights("isunique", "add");
        $this->mapActionToRights("viewdetail", "viewprofile");
        $this->mapActionToRights("fillform", "edit");
        $this->mapActionToRights("displayGRN", "add");
        $this->mapActionToRights("processGRN", "add");
        $this->mapActionToRights("getPODetail", "add");
        $this->mapActionToRights("getPOList", "add");
        $this->mapActionToRights("getSapRateCardList", "add");
        $this->mapActionToRights("getPreDraftGrnItemList", "add");
        $this->mapActionToRights("saveSingleItemDraft", "add");
        $this->mapActionToRights("prefillform", "add");
        $this->mapActionToRights("getSapVendorCodes", "add");
        $this->mapActionToRights("getCustomerList", "add");
        $this->mapActionToRights("addgrn", "add");
        $this->mapActionToRights("getSalesmanList", "list");
        $this->mapActionToRights("getPosBranchList", "list");
        $this->mapActionToRights("getSharedBranchList", "list");
        $this->mapActionToRights('detailview', 'list');
        $this->mapActionToRights('uploadCollectionPOS', 'add');
        $this->mapActionToRights('uploadCollectionSHARED', 'add');
        $this->mapActionToRights("exportExcel", "export");
        //$this->mapActionToRights("isHideButtons", "list");
        


        $this->app = $app;
        $currentStore = $app->goodsreceivenoteStore();
        $this->currentStore = $currentStore; 
        $this->addChild(new ext6gridhandler($this, $currentStore,1));
    }

    function onPreListing($objects, $params, $records){
        // print_r($records[1]);
        $app = App::getInstance();

        foreach ($records as $x => $record){
            $printHtml = '';
            if ($record['jsonpostpayload']){
                $items = json_decode($record['jsonpostpayload']);
                foreach ($items as $item){
                    // print_r($items);exit;
                    if (gettype($item) == 'array'){
                        foreach ($item as $doc){
                            // print_r($doc);exit;
                            $printHtml .= '<tr>';
                            //$printHtml .= 	'<td style="text-align:center; width:200px">'.$item.'</td>';
                            $printHtml .= 	'<td style="text-align:center; width:200px">'.$doc->docNum.'</td>';
                            $printHtml .= 	'<td style="text-align:center; width:200px">'.$doc->u_GTPREFNO.'</td>';
                            $printHtml .= '</tr>';
                        }
                        break;
                    }
                }
                $records[$x]['child'] = $printHtml;
            }

            // Change weight decimal
            $partner = $app->partnerFactory()->getById($record['partnerid']);
            $record['totalxauexpected'] =  $record['totalxauexpected'];
            $record['totalgrossweight'] = $record['totalgrossweight'];
            $record['totalxaucollected'] = $record['totalxaucollected'];
            $record['vatsum'] = $record['vatsum'];

        }
        return $records;
    }

    function onPreQueryListing($params,$sqlHandle, $records) {        
        $app = App::getInstance();
        if (isset($params['partnercode']) && 'POS' === $params['partnercode']) {
            $partnerId_s = [
                $app->getConfig()->{'gtp.pos1.partner.id'},
                $app->getConfig()->{'gtp.pos2.partner.id'},
                $app->getConfig()->{'gtp.pos3.partner.id'},
                $app->getConfig()->{'gtp.pos4.partner.id'}
            ];
        }else if (isset($params['partnercode']) && 'TEKUN' === $params['partnercode']) {
            $partnerId_s = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'}
            ];
        }else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId_s = [
                $app->getConfig()->{'gtp.koponas1.partner.id'},
                $app->getConfig()->{'gtp.koponas2.partner.id'}
            ];
        }else if (isset($params['partnercode']) && 'SAHABAT' === $params['partnercode']) {
            $partnerId_s = [
                $app->getConfig()->{'gtp.sahabat1.partner.id'},
                $app->getConfig()->{'gtp.sahabat2.partner.id'}
            ];
        }
       
        $sqlHandle->andWhere('partnerid', 'IN', $partnerId_s);
        return array($params, $sqlHandle, $fields);  
    }
   

    public function displayGRN(){
       
        $return = $this->app->goodsreceivednoteManager()->test();
        
    }

    public function processGRN($app, $params){

        $json = json_decode($params['json']);
        $json['itemRateDetails'];
        $json['poList'];

        $this->app->goodRecivedNoteManager()->newGRN();
    }

    public function getPODetail($app, $params){
        // echo $params['query'];exit;
        $cardCode = $params['query'];
        $version = '1.0';
        $verify = false;
        // $code = 'VTC010'; // customer id *required
        $code = $cardCode; // customer id *required
        $list = $this->app->apiManager()->sapGetOpenPo($version, $verify, $code);
        // $this->app->dd($list);
        foreach ($list['opnlist'] as $x => $polist){
            $draftGRN_mark = '';
            if ($polist['draftGRN'] == true){
                $draftGRN_mark = '**';
            }
            $list['opnlist'][$x]['item_html_list'] = $draftGRN_mark.$polist['u_GTPREFNO'].' | QTY:'.$polist['quantity'].'('.$polist['opndraft'].') | '.$polist['docNum'].' | '.$polist['docDate'].' | CM:'.$polist['comments'];
        }
        echo json_encode($list['opnlist']);
    }

    public function getPOList(){
        $version = '1.0';
        $type = 'purchase';
        $refOrKey = 0;
        $isRef = false;
        $list = $this->app->apiManager()->sapGetPostedOrders($version, $type, $refOrKey, $isRef);
        echo json_encode($list);
    }

    public function getSapRateCardList($app, $params){
        $version = '1.0';
        $params['version'] = $version;
        // $params['code'] = 'VTC010'; // customer id *required
        $params['item'] = '';
        // $params['code'] = $partner = $this->app->partnerStore()->select("sapcompanybuycode1")->where("id", $)->one();getByField('', $data->customer);
        $list = $this->app->apiManager()->sapGetRateCard($version, $params);

        $returnList = [];
        // 'ratecard => [{
        //         "u_cardcode": "VTP000",
        //         "u_itemcode": "GS-ScrapBar",
        //         "u_purity"  : 0.000000
        // }, ...]
        foreach ($list['ratecard'] as $x => $ratecard){
            $list['ratecard'][$x]['gtp_grossweight'] = 0;
            $list['ratecard'][$x]['gtp_inputweight'] = 0;

            $readonly = (strval($ratecard['u_purity']) != "0") ? true : false;
            $list['ratecard'][$x]['u_purity_readonly'] = $readonly;

            $list['ratecard'][$x]['item_html_list'] = $ratecard['u_itemcode'].'|'.$ratecard['u_purity'].'--'.$ratecard['u_cardcode'];
        }

        //split the items
        $itemsTop = array();
        $itemsBottom = array();
        foreach ($list['ratecard'] as $x => $ratecard) {
            if (preg_match('/^\w{2}-\d.*/', $ratecard['u_itemcode'], $matches)) {
                $itemsTop[] = $ratecard;
            } else {
                $itemsBottom[] = $ratecard;
            }
        }

        //multi sort
        $keys = array_column($itemsTop, 'u_itemcode');
        array_multisort($keys, SORT_DESC, $itemsTop);
        $keys = array_column($itemsBottom, 'u_itemcode');
        array_multisort($keys, SORT_ASC, $itemsBottom);
        $sortedRateCardItems['ratecard'] = array_merge($itemsTop,  $itemsBottom);

        return json_encode($sortedRateCardItems['ratecard']);
    }

    public function getSalesmanList(){
        if ($this->app->hasPermission('/show_all_salesman_partner')){
            $return['salesperson'] = [
                "id" => "-",
                "name" => "Select Sales Person",
            ];
            $salesperson = $this->app->partnerStore()->searchTable(false)->select(['id' => 'sapcode', 'name'])->where('salesmanid', $salesperson = $this->app->getUsersession()->getUser()->id)->execute();
            
        }else{
            $return['salesperson']['id'] = $this->app->getUsersession()->getUser()->sapcode;
            $return['salesperson']['name'] = $this->app->getUsersession()->getUser()->name;
        }
        $return['success'] = true;
        return json_encode($return);
    }

    public function getCustomerList($app, $params){
        
        $filter = json_decode($params['filter']);
        if (!$filter){
            return json_encode(array());
        }
        // $companyId = $filter[0]->value;
        // if ($companyId == 'vendor'){
            //     $options = $companyId;
        // }else if ($companyId == 'customer'){
            //     $options = $companyId;
        // }else{
            //     throw error;
        // }
        $cacher = $this->app->getCacher();
        $key = 'sapBusinessList';
        $sapBusinessList = $cacher->get($key);
        if (!$sapBusinessList){
            echo 'a';
            $options = 'vendor';
            $apimanager=$this->app->apiManager();
            $request = array(
                'version' => '1.0',
                'action' => 'partnerlist',
                'option' => $options,
            );
            $list = $apimanager->sapBusinessList('1.0', $request);
            $cacher->set($key, $list['ocrd'], 1800); // 30 min cached
            $sapBusinessList = $list['ocrd'];
        }
        
        if ($sapBusinessList && $params['query']){
            $array = $sapBusinessList;
            $input = $params['query'];
            $result = array_filter($array, function ($item) use ($input) {
                if (stripos($item['cardName'], $input) !== false) {
                    return true;
                }
                return false;
            });
        }
        // else if ($list['ocrd'] && !$params['query']){
        //     $result = $list['ocrd'];
        // }
        return json_encode(array_values($result));


        return json_encode([
            'success' => true,
            'result' => $list['ocrd']
        ]);


        // $xx = json_decode($params['filter']);
        // print_r($xx[0]->value);exit;
        $filter = json_decode($params['filter']);
        $companyId = $filter[0]->value;
        if ($companyId == 1){
            $where = ['sapcompanybuycode1', 'NOT NULL'];
        }else if ($companyId == 2){
            $where = ['sapcompanybuycode2', 'NOT NULL'];
        }else{
            throw error;
        }
        $query = $params['query'];
        $results = $app->partnerStore()->searchTable()->select()->where($where)->andWhere('name', 'LIKE', '%'.$query.'%')->orWhere('code', 'LIKE', '%'.$query.'%')->execute();

        foreach ($results as $result){
            $result->sapcode;
        }

        return json_encode([
            'success' => true,
            'result' => $results
        ]);
    }

    public function getCompanyList($app, $params){
        // $company1 = [
        //     "id" => 'vendor',
        //     "name" => 'ACG-J'
        // ];
        // $company2 = [
        //     "id" => 'customer',
        //     "name" => 'ACG-B'
        // ];
        $companyList = [
            "id" => 'vendor',
            "name" => 'Ace Capital Growth'
        ];
        $companies = array($companyList);

        $salesperson = $this->app->getUsersession()->getUser()->name;
        // $salesperson->type
        
        return json_encode([
            "success" => true,
            "companies" => $companies,
            "salesperson" => $salesperson
        ]);
    }

    public function prefillform($app, $params){
        return $this->getCompanyList($app, $params);
        // return $this->getSalesmanList($app, $params);
    }

    // public function getSapVendorCodes($app,$params){
    //         $redemptionobj=$this->app->redemptionStore()->getById($params['id']);
    //         $apimanager=$this->app->apiManager();
    //         $request = array(
    //             'version' => '1.0',
    //             'action' => 'partnerlist',
    //             'option' => 'vendor',

    //         );

    //         $log = $apimanager->sapBusinessList('1.0', $request);
    //         $app->dd($log);
        
    // }

    
    public function addgrn($app, $params){
        // stdClass Object
        // (
        //     [po] => Array
        //         (
        //             [0] => stdClass Object
        //                 (
        //                     [docEntry] => 11096
        //                     [docNum] => 2111096
        //                     [u_GTPREFNO] => 20100217
        //                 )
        //         )
        //     [ratecard] => Array
        //         (
        //             [0] => stdClass Object
        //                 (
        //                     [u_itemcode] => GS-950
        //                     [u_purity] => 94
        //                 )
        //             [1] => stdClass Object
        //                 (
        //                     [u_itemcode] => GS-916
        //                     [u_purity] => 90
        //                         "u_itemcode": value.data.u_itemcode,
        //                         "u_purity": value.data.u_purity,
        //                         "u_inputweight": value.data.gtp_inputweight,
        //                         "u_xauweight": value.data.gtp_xauweight
        //                 )
        //         )
        //     [customer] => VTC010
        // )
        
        

        // SAP request data, do here
        // U_GTPNO = U_GTPREFNO;
        // "selectedPO": [
        //     {
        //         "docEntry",
        //         "lineNum",
        //         "sequence"
        //     }
        // ],
        // "items": [
        //     {
        //         "itemCode",
        //         "quantity", // after deduct purity
        //         "U_PURITY", // value as in 89 to 
        //         "U_GrossWeight" // original order weight
        //     }
        // ],
        try{
            $this->app->getDbHandle()->beginTransaction();
            $data = json_decode($params['data']);
            // $data->po
            // $data->ratecard
            // $data->customer
            $partner = $this->app->partnerStore()->getByField('sapcompanybuycode1', $data->customer);

            $requestParams['version'] = '1.0';
            $version = '1.0';
            $sapCode = $data->customer;

            // insert grn table
            $newGrnDraft = $this->app->goodsreceivenoteStore()->create([
                'salespersonid' => $this->app->getUserSession()->getUserId(),
                'partnerid' => $partner->id
            ]);
            $saveNewGrnDraft = $this->app->goodsreceivenoteStore()->save($newGrnDraft);
            $gtpNo = $saveNewGrnDraft->id;


            $total_xau_expected; // order xau
            $total_gross_weight; // input weight
            $total_xau_collected; // input weight * purity 
            $vat_sum; // order xau - xau collected

            $total_submit['grossweight'] = 0;
            $total_submit['collected'] = 0;
            foreach ($data->ratecard as $x => $rateCard){
                $data->ratecard[$x]->{"itemCode"} = $rateCard->u_itemcode;
                // $data[$x]['quantity'] = $rateCard->u_xauweight;
                $quantity = ((float) $rateCard->u_inputweight * (float) $rateCard->u_purity) / 100;
                $data->ratecard[$x]->{"quantity"} = (float) number_format($quantity, 3, '.', '');
                //$data->ratecard[$x]->{"quantity"} = (float) floor(($quantity * 1000)) / 1000;
                $data->ratecard[$x]->{"U_PURITY"} = (float) number_format(floatval($rateCard->u_purity), 2, '.', '');
                $data->ratecard[$x]->{"U_GrossWeight"} = (float) number_format(floatval($rateCard->u_inputweight), 3, '.', '');

                $total_submit['grossweight'] += floatval($rateCard->u_inputweight);
            }
            foreach ($data->po as $x => $po){
                $data->po[$x]->{"lineNum"} = 0;
                $data->po[$x]->{"sequence"} = 0;
                
            }

            $payload = json_encode([$gtpNo, $sapCode, $data->po, $data->ratecard, $requestParams]);

            // reset gtp data -> remain sap data only -- start
            foreach ($data->ratecard as $x => $rateCard){
                unset($rateCard->u_itemcode);
                unset($rateCard->u_purity);
                unset($rateCard->u_inputweight);
                unset($rateCard->u_xauweight);
            }
            foreach ($data->po as $x => $po){
                unset($po->docNum);
                unset($po->u_GTPREFNO);
            }
            // reset gtp data -> remain sap data only -- end
            $return = $this->app->apiManager()->sapPostGrnDraft($version, $gtpNo, $sapCode, $data->po, $data->ratecard, $requestParams);

            $response = json_encode($return);
            $sapReturnCode = $return->actionResult;

            // summary = {
            //     "total_expected_xau": total_expected,
            //     "total_gross_weight": total_gross_weight,
            //     "total_xau_collected": total_xau_collected,
            //     "vatsum": vatsum,
            // }

            if ($return->actionSuccess == 1){
                $saveNewGrnDraft->jsonpostpayload = $payload;
                $saveNewGrnDraft->totalxauexpected = number_format(floatval($data->summary->total_expected_xau), 3, '.', '');
                $saveNewGrnDraft->totalgrossweight = number_format(floatval($total_submit['grossweight']), 3, '.', '');
                $saveNewGrnDraft->totalxaucollected = number_format(floatval($data->summary->total_xau_collected), 3, '.', '');
                $saveNewGrnDraft->vatsum = $data->summary->vatsum;
                $saveNewGrnDraft->status = \Snap\object\GoodsReceiveNote::STATUS_ACTIVE;
                $saveNewGrnDraft = $this->app->goodsreceivenoteStore()->save($newGrnDraft);
            }else{
                throw new \Exception('SAP Failed.');
            }

            $this->app->getDbHandle()->commit();

            return json_encode([
                "success" => true,
                "grn" => $return,
            ]);

        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("POS grn excel upload failed on trans_sap_no:".$gtpNo, SNAP_LOG_ERROR);
            throw $e;
        }
        

        
    }

    /*
    function isHideButtons($app, $params){		

		
		try{
            $userType = $this->app->getUserSession()->getUser()->type;
            $userId = $this->app->getUserSession()->getUser()->id;
            $user=$this->app->userStore()->getById($userId);

			if($user->isSale()){
				$hide = 0;
			} else if ($user->isOperator()) {
				$hide = 0;
			} else if ($user->isTrader()) {
				$hide = 1;
			} else if ($user->isCustomer()) {
				$hide = 1;
			} else if ($user->isReferral()) {
				$hide = 1;
			} else if ($user->isAgent()) {
				$hide = 1;
			}
			if ($hide != 0){
				echo json_encode(['success' => true, 'hide' => true]);   
			}else{
				echo json_encode(['success' => true, 'hide' => false]);   
			}
		  
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
		
	}*/



    public function getPreDraftGrnItemList($app, $params){
        // [F2104070024D,F2104070025D]
        $version = '1.0';
        $params['version'] = $version;
        // $params['code'] = 'VTC010'; // customer id *required
        $params['item'] = '';
        // $params['branchid'] = '350';
        $params['query'];
        $branchid = $params['query'];
        $ratecard = $this->app->apiManager()->sapGetRateCard($version, $params);
        // 'ratecard => [{
        //         "u_cardcode": "VTP000",
        //         "u_itemcode": "GS-ScrapBar",
        //         "u_purity"  : 0.000000
        // }, ...]

        // REF_LINK_01 reformat ratecard for one time variable on next process array_search
        $reformed_ratecard = [];
        foreach($ratecard['ratecard'] as $rate){
            $reformed_ratecard[$rate['u_itemcode']] = $rate['u_purity'];
        }
        // $reformed_ratecard => [
        //     'GS-ScrapBar' => 0.0000,
        //     'GS-...' => ...
        // ]



        $sapcode = $params['code'];
        $u_GTPREFNO_s = $params['u_GTPREFNO_s'];
        $u_GTPREFNO_s = json_decode($u_GTPREFNO_s);

        // $partner = $this->app->partnerStore()->searchTable()->select()->where('sapcode', $sapcode)->one();
        $buybacks = $this->app->buybackStore()->searchTable()->select()->where('buybackno', 'IN', $u_GTPREFNO_s)->execute();
        
        $partnerid = $buyback->partnerid;
        $returnList = [];
        foreach ($buybacks as $buyback){
            $goodrecievednoteorder = $this->app->goodsreceivenoteorderStore()->searchTable()->select()->where('buybackid', $buyback->id)->one();
            if ($branchid){
                $draft_items = $this->app->goodsreceivenotedraftStore()->searchTable()->select()
                    ->where('goodreceivednoteorderid', $goodrecievednoteorder->id)
                    ->andWhere('branchid', $branchid)
                    ->execute();
            }else{
                $draft_items = $this->app->goodsreceivenotedraftStore()->searchTable()->select()
                    ->where('goodreceivednoteorderid', $goodrecievednoteorder->id)
                    ->execute();
            }
            // print_r($goodrecievednoteorder->id);exit;
            // print_r($buyback->id);exit;
            // print_r(json_encode($draft_items[0]->toArray()));exit;
            
            $items_row = [];
            // $this->app->dd($draft_items);exit;
            foreach ($draft_items as $x => $item){
                // $this->app->dd($item);exit;
                
                
                $details = json_decode($item->details); // CONTAIN Multiple detail => type_array_obj
                $item_gross_weight = 0;
                $item_xau_weight = 0;
                $draft_items_array = [];
                foreach ($details as $y => $detail){

                    // print_r($detail);exit;

                    $draft_items_array[$y]['u_purity_readonly'] = true;
        
                    $draft_items_array[$y]['u_itemcode'] = $detail->purity; // DB store item code as purity, coz purity always refer to SAP , `purity <=> purity_item_code`
                    $draft_items_array[$y]['u_purity'] = $reformed_ratecard[$detail->purity]; // REF_LINK_01
        
                    
                    // $draft_items_array[$y]['gtp_grossweight'] = $detail->weight;
                    // $draft_items_array[$y]['gtp_inputweight'] = 0;
                    if (isset($detail->userinputweight)){
                        $draft_items_array[$y]['gtp_inputweight'] = floatval($detail->userinputweight); // user input weight, temp draft input on save single item weight prevent lost data
                        $draft_items_array[$y]['gtp_xauweight'] = floatval($detail->userinputweight) * ($reformed_ratecard[$detail->purity] / 100);
                        $draft_items_array[$y]['gtp_userinputweight_datetime'] = floatval($detail->userinputweightdatetime); // user input weight, temp draft input on save single item weight prevent lost data
                    }else{
                        $draft_items_array[$y]['gtp_inputweight'] = floatval($detail->weight); // input weight = gross weight which pos already provided
                        $draft_items_array[$y]['gtp_xauweight'] = floatval($detail->weight) * ($reformed_ratecard[$detail->purity] / 100);
                    }

                    // always use db data for item row
                    $client_input_xau_weight = floatval($detail->weight) * ($reformed_ratecard[$detail->purity] / 100);
                    $client_input_weight = floatval($detail->weight);

                    // $draft_items_array[$x][$y]['gtp_inputweight'] = $detail->weight / ($reformed_ratecard[$detail->purity] / 100);
                    // $draft_items_array[$x][$y]['gtp_submitedxauweight'] = $detail->weight; //for TENDER
        
                    // $this->app->dd($draft_items_array);
                    // $list[$x]['item_html_list'] = $ratecard['u_itemcode'].'|'.$ratecard['u_purity'].'--'.$ratecard['u_cardcode'];
                    $returnList[$buyback->buybackno] = $draft_items_array;
                    // $returnList[] = $draft_items_array;

                    $item_gross_weight += floatval($client_input_weight);
                    $item_xau_weight += floatval($client_input_xau_weight);
                }

                $items_row[$x]['item_html_list'] = $item->referenceno.' | '.round($item_gross_weight, 4).' | '.round($item_xau_weight, 4);
                $items_row[$x]['referenceno'] = $item->referenceno;
                $items_row[$x]['grossweight'] = round($item_gross_weight, 4);
                $items_row[$x]['item_xauweight'] = round($item_xau_weight, 4);
                $items_row[$x]['details'] = $draft_items_array;

            }
        }
        // $this->app->dd($items_row);exit;
        return json_encode($items_row);
    }

    public function saveSingleItemDraft($app, $params){
        // $params['referenceno']; // item ref number;
        // $params['userinputweight'];

        $data = json_decode($params['data']);

        // print_r($data);exit;

        $userinputweightdatetime = new \DateTime("now");
        $userinputweightdatetime = $userinputweightdatetime->format('Y-m-d H:i:s');

        $grdDraftItem = $this->app->goodsreceivenotedraftStore()->searchTable()->select()
            ->where('referenceno', $data->referenceno)
            ->one();

        $construct_details = json_decode($grdDraftItem->details, true);
        // print_r($construct_details);exit;

        foreach ($construct_details as $x => $detail){
            foreach ($data->details as $input_detail){
                if ($detail['purity'] == $input_detail->u_itemcode){
                    $construct_details[$x]['userinputweight'] = $input_detail->gtp_inputweight;
                    $construct_details[$x]['userinputweightdatatime'] = $userinputweightdatetime;
                }
            }
        }

        // print_r($construct_details);exit;


        // $construct_details[$params['index']]['userinputweight'] = $params['userinputweight'];
        // $construct_details[$params['index']]['userinputweightdatetime'] = $userinputweightdatetime;

        $grdDraftItem->details = json_encode($construct_details);

        $save = $this->app->goodsreceivenotedraftStore()->save($grdDraftItem);
        if ($save){
            $return['success'] = true;
        }else{
            $return['success'] = false;
        }
        return json_encode($return);
    }

    public function getPosBranchList($app, $params){
        if ($params['filter']){
            $filter = json_decode($params['filter']);
            $customer_sapcode = $filter[0]->value;
        }
        $pos1 = $this->app->getConfig()->{'gtp.pos1.partner.id'};
        $pos2 = $this->app->getConfig()->{'gtp.pos2.partner.id'};
        $pos3 = $this->app->getConfig()->{'gtp.pos3.partner.id'};
        $pos4 = $this->app->getConfig()->{'gtp.pos4.partner.id'};

        $partners = [$pos1, $pos2, $pos3, $pos4];
        if ($customer_sapcode){
            $partners = $this->app->partnerStore()->searchTable()->select()->where('sapcompanybuycode1', $customer_sapcode)->execute();
        }else{
            $partners = $this->app->partnerStore()->searchTable()->select()->where('id', 'in', $partners)->execute();
        }
        $branches = [];
        $return = [];
        foreach ($partners as $partner){
            // $partner->branches = $partner->getBranches();
            $branches[$partner->name] = $partner->getBranches();
        }

        foreach ($branches as $x => $branch){
            foreach ($branch as $y => $list){
                $temp['branch_list'] = $list->name . ' - ' .$x;
                $temp['branch_code'] = $y;
                $temp['branchid'] = $list->id;
                $return[] = $temp; 
            }
        }
        // print_r($return);exit;
        $return['results'] = $return;
        return json_encode($return);
    }

    public function getSharedBranchList($app, $params){
        if ($params['filter']){
            $filter = json_decode($params['filter']);
            $customer_sapcode = $filter[0]->value;
        }
        if (isset($params['partnercode']) && 'POS' === $params['partnercode']) {
            $partners = [
                $app->getConfig()->{'gtp.pos1.partner.id'},
                $app->getConfig()->{'gtp.pos2.partner.id'},
                $app->getConfig()->{'gtp.pos3.partner.id'},
                $app->getConfig()->{'gtp.pos4.partner.id'}
            ];
        }else if (isset($params['partnercode']) && 'TEKUN' === $params['partnercode']) {
            $partners = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'}
            ];
        }else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partners = [
                $app->getConfig()->{'gtp.koponas1.partner.id'},
                $app->getConfig()->{'gtp.koponas2.partner.id'}
            ];
        }else if (isset($params['partnercode']) && 'SAHABAT' === $params['partnercode']) {
            $partners = [
                $app->getConfig()->{'gtp.sahabat1.partner.id'},
                $app->getConfig()->{'gtp.sahabat2.partner.id'}
            ];
        }
        // $tekun1 = $this->app->getConfig()->{'gtp.tekun1.partner.id'};
        // $tekun2 = $this->app->getConfig()->{'gtp.tekun2.partner.id'};

        // $partners = [$tekun1, $tekun2];
        if ($customer_sapcode){
            $partners = $this->app->partnerStore()->searchTable()->select()->where('sapcompanybuycode1', $customer_sapcode)->execute();
        }else{
            $partners = $this->app->partnerStore()->searchTable()->select()->where('id', 'in', $partners)->execute();
        }
        $branches = [];
        $return = [];
        foreach ($partners as $partner){
            // $partner->branches = $partner->getBranches();
            $branches[$partner->name] = $partner->getBranches();
        }

        foreach ($branches as $x => $branch){
            foreach ($branch as $y => $list){
                $temp['branch_list'] = $list->name . ' - ' .$x;
                $temp['branch_code'] = $y;
                $temp['branchid'] = $list->id;
                $return[] = $temp; 
            }
        }
        // print_r($return);exit;
        $return['results'] = $return;
        return json_encode($return);
    }

    /*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		$object = $app->goodsreceivenotefactory()->getById($params['id']);

        // print_r($object);

		$partnername = $app->partnerFactory()->getById($object->partnerid)->name;
		$partnercode = $app->partnerFactory()->getById($object->partnerid)->code;
		$salespersonname = $app->userFactory()->getById($object->salespersonid)->name;
		//$productname = $app->productFactory()->getById($object->productid)->name;

		$reconciledbyname = $app->userFactory()->getById($object->reconciledby)->name;
		$cancelbyname = $app->userFactory()->getById($object->cancelby)->name;
		$confirmbyname = $app->userFactory()->getById($object->confirmby)->name;


		if($object->byweight > 0) $byWeight = "Weight";
		else $byWeight = 'Amount';

		$bookingOn = $object->bookingon ? $object->bookingon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		$confirmedOn = $object->confirmon ? $object->confirmon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		$cancelledOn = $object->cancelon ? $object->cancelon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
		$reconciledOn = $object->reconciledon ? $object->reconciledon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		// Status
		if ($object->status == 0){
			$statusname = 'Pending';
		}else if ($object->status == 1){
			$statusname = 'Active';
		}else if ($object->status == 2){
			$statusname = 'Rejected';
		}else {
			$statusname = 'Unidentified';
		}

        $totalxauexpected = number_format($object->totalxauexpected,3);
        $totalxaucollected = number_format($object->totalxaucollected,3);
        $totalgrossweight = number_format($object->totalgrossweight,3);
        $vatsum = number_format($object->vatsum,3);

		$detailRecord['default'] = [ "Collection ID" => $object->id,
                                    'Code' => $partnercode,
								    'Partner' => $partnername,
                                  
                                    'Salesperson' => $salespersonname,
                                   
                                    
                                   
                                    'Xau Expected' => $totalxauexpected,
                                    'Xau Collected' => $totalxaucollected,
                                    'Gross Weight' => $totalgrossweight,
                                    'Vat Sum' => $vatsum,
                                    'Comments' => $object->comments,

									'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

    public function uploadCollectionPOS($app, $params){
        try{
            $file = $_FILES['grnposlist'];

            if ($params['preview'] == 1){
                $preview = true;
            }else{
                $preview = false;
            }
            
            $import = $this->app->GoodsReceivedNoteManager()->readImportGrnExcel_POS($file, $preview);
    
            if ($import){
                $return = [
                    'success' => true
                ];
            }else{
                $return = [
                    'error' => true
                ];
            }
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("POS grn excel upload failed on trans_sap_no:".$gtpNo, SNAP_LOG_ERROR);
            $return = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($return);
    }

    public function uploadCollectionSHARED($app, $params){
        try{
            $file = $_FILES['grnposlist'];

            if ($params['preview'] == 1){
                $preview = true;
            }else{
                $preview = false;
            }
            
            //get partners
            if (isset($params['partner']) && 'TEKUN' === $params['partner']) {
           
                $sharedPartnerId_s = [
                    $app->getConfig()->{'gtp.tekun1.partner.id'},
                    $app->getConfig()->{'gtp.tekun2.partner.id'},
                ];
                $partnername = 'TEKUN';
            }
            else if (isset($params['partner']) && 'KOPONAS' === $params['partner']) {
                $sharedPartnerId_s = [
                    $app->getConfig()->{'gtp.koponas1.partner.id'},
                    $app->getConfig()->{'gtp.koponas2.partner.id'},
                ];
                $partnername = 'KOPONAS';
            }
            else if (isset($params['partner']) && 'SAHABAT' === $params['partner']) {
                $sharedPartnerId_s = [
                    $app->getConfig()->{'gtp.sahabat1.partner.id'},
                    $app->getConfig()->{'gtp.sahabat2.partner.id'},
                ];
                $partnername = 'SAHABAT';
            }
            $import = $this->app->GoodsReceivedNoteManager()->readImportGrnExcel_SHARED($file, $preview, $sharedPartnerId_s);
    
            if ($import){
                $return = [
                    'success' => true
                ];
            }else{
                $return = [
                    'error' => true
                ];
            }
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log($partnername. " grn excel upload failed on trans_sap_no:".$gtpNo, SNAP_LOG_ERROR);
            $return = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($return);
    }

    function exportExcel($app, $params){
        
        //$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

        $posPartnerId_s = [
            $app->getConfig()->{'gtp.pos1.partner.id'},
            $app->getConfig()->{'gtp.pos2.partner.id'},
            $app->getConfig()->{'gtp.pos3.partner.id'},
            $app->getConfig()->{'gtp.pos4.partner.id'}
        ];

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        
        $modulename = 'POS_COLLECTION';

        $conditions = ["partnerid", "IN", $posPartnerId_s];

        $statusRenderer = [
			0 => "Inactive",
			1 => "Active",
		];

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    
    }
}