<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\manager;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \Snap\object\GoodsReceivedNoteDraft;

use Spipu\Html2Pdf\Html2Pdf;

class GoodsReceivedNoteManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        
    }


    // $oder = $manager->bookOrder();
    // $order = $manager->comfirmOrder($order);
    // $app->manager()->GoodsReceivedNoteManager->createDraftGRN($order, $branchId, $referenceNo, $product, $items);

    // START - 25-01-2021

    // POS merchant -> createDraftGRN($buyback, $branchId, $referenceNo, $product, json_decode($buyback->items))
    public function createDraftGRN($order, $branchId, $referenceNo, $product, $items, $tenderMode = false){
        // $items = [{
        //     "purity": 23,
        //     "weight": 12.345,
        //     "desc": null
        // },{
        //     "purity": 23,
        //     "weight": 12.345
        //     "desc": null
        // }];

        // $itemArray =[ 
        //     0 => [
        //         "purity" => 13,
        //         "weight" => 321,
        //          "desc" => null
        //     ],
        //     1 => [
        //         "purity" => 13,
        //         "weight" => 321,
        //          "desc" => null
        //     ]
        // ];
        $goodReceiveNoteOrder = $this->app->goodsreceivenoteorderStore()->create([
            "buybackid" => $order->id,
            // "goodreceivedraftid" => $goodReceiveDraft->id,
        ]);
        $goodReceiveNoteOrder = $this->app->goodsreceivenoteorderStore()->save($goodReceiveNoteOrder);

        foreach ($items as $item){
            // for tender
            if ($branchId == 0){
                $item_branchId = $item['branchid'];
            }
            if ($referenceNo == null){
                $referenceNo = $item['referenceNo'];
            }
            // for tender ended
            if ($tenderMode){
                if (is_array($item['item_details'])){
                    // tender modification
                    $goodReceiveDraft = $this->app->goodsreceivenotedraftStore()->create([
                        "goodreceivednoteorderid" => $goodReceiveNoteOrder->id,
                        "branchid" => $item_branchId,
                        "referenceno" => $item['referenceNo'],
                        "product" => $product->id, // string from api
                        "details" => json_encode($item['item_details']),
                        "gtpxauweight" => $item['item_total_weight'],
                        "desc" => 'tender',
                        "status" => GoodsReceivedNoteDraft::STATUS_ACTIVE,
                        // GoodReceiveNoteDraft::STATUS_COMPLETED => after submit to SAP
                    ]);
                    $goodReceiveDraft = $this->app->goodsreceivenotedraftStore()->save($goodReceiveDraft);
                }
            }
            // for pos buyback
            else{
                $goodReceiveDraft = $this->app->goodsreceivenotedraftStore()->create([
                    "goodreceivednoteorderid" => $goodReceiveNoteOrder->id,
                    "branchid" => $branchId,
                    "referenceno" => $referenceNo,
                    "product" => $product->id, // string from api
                    "details" => json_encode(
                        array(
                            'purity' => $item['purity'],
                            'weight' => $item['weight']
                        )
                    ),
                    "gtpxauweight" => $item['item_total_weight'],
                    "desc" => $item['desc'],
                    "status" => GoodsReceivedNoteDraft::STATUS_ACTIVE,
                    // GoodReceiveNoteDraft::STATUS_COMPLETED => after submit to SAP
                ]);
                $goodReceiveDraft = $this->app->goodsreceivenotedraftStore()->save($goodReceiveDraft);
            }
        }
        return true;

    }

    public function submitGRN_SAP(){

    }
    // END - 25-01-2021



    // sample - sales tab on DEMO-GTP
    // orderid = company_buy
    // product = jewel, bar, not digital_gold
    public function createGRN($product, $purity, $finalgram, $salepersonid, $value = null, $grn = null){
        // combine note on one orderid

        // product checking
        if ($product->code == 'DG-999-9'){
            throw error;
        }

    }

    // $POList Array
    // $sapVendorCode gtp1.0 company ID from SAP eg. VTA001%%%%
    // $itemsRateDetail Array(
    //     rateCardId => '',
    //     purity => '12', // in % unit
    //     xauGram => '4' // in gram unit
    // );
    public function newGRN($product, $POList, $sapVendorCode, $itemsRateDetails, $totalGram, $customerId = null){
        
        $grossGram = 0;
        $grossPurity = 0;
        foreach ($itemsRateDetails as $y => $itemRate){
            $y++;
            $gram = $itemRate['gram'];
            $purity = $itemRate['purity'];
            $grossGram += $gram * $purity;
            $grossPurity += $purity;
        }
        $grossPurity = round($grossPurity / $y, 1);
        $balanceGram = $totalGram - $grossGram;
        
        $grnOrders = [];
        foreach ($POList as $x => $PO){
            $grnOrders[$x]['orderid'] = $PO['id'];
        }
        $mainGRN = $this->createGRN();

        $goodreceivenoteid = $mainGRN->id;
        foreach ($grnOrders as $grnOrder){
            $grnNoteOrder = $this->app->goodsreceivenoteorderStore()->create([
                "goodreceivenoteid" => $goodreceivenoteid,
                "orderid" => $grnOrder['orderid'],
            ]);
            $this->app->goodsreceivenoteorderStore()->save($grnNoteOrder);
        }
    }

    public function displayGRN_PDF($grnId){
        $grn = $this->goodsreceivenoteorderStore()->getById($grnId, array(), false, 1);
        $salesPerson = (object)[
            "name" => $grn->salespersonname,
            "email" => $grn->salespersonname
        ];
        $grn->comments;
        $grn->totalxauexpected;
        $grn->totalgrossweight;
        $grn->totalxaucollected;
        $grn->vatsum;
        $grn->status;
        

        //body, header footer will be on other func
        $content;


        

        $html2pdf = new Html2Pdf();

        $grnPdf = $this->completePdf($content);
        
        $html2pdf->writeHTML($grnPdf);
        $html2pdf->output();
    }

    // public function purityCalculation($product, $gram, $purity, $value = null){

    // }


    // public function getSapProduct(){
        
    // }

    // public function getSapPurityRateCardList(){

    // }

    private function completePdf($content){
        $f = 1;
        
        $wrap_start = '<page backtop="10mm" backbottom="10mm" backleft="20mm" backright="20mm">';
        $wrap_end = '</page>';
        
        $header = $this->getHeader();
        $footer = $this->getFooter();

        

        $html = $wrap_start.$header.$footer.$content.$wrap_end;

        return $html;
    }

    public function test(){
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');

        $grnPdf = '
        
        <table style="width: 100%;" >
            <tr>
                <td style="width: 60%;">
                <div style="font-weight:bold;font-size:1.5rem">Goods Receipt PO</div>
                <p>
                    No. 19, Jalan USJ 10/1d,
                    <br>
                    Taipan Business Centre,
                    <br>
                    47620 Subang Jaya, Selangor
                </p>
                </td>
                <td style="width: 60%;vertical-align: top;">
                    <table>
                        <tr style="width:100%">
                            <td>
                            asd-a-sdas-d
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        ';
        $grnPdf = $this->completePdf($grnPdf);


        // ob_start();
        // include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
        // $content = ob_get_clean();
        // $html2pdf->writeHTML($content);
        
        
        $html2pdf->writeHTML($grnPdf);
        return $html2pdf->output('FILENAME.pdf');
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


    public function uploadGrnExcel_POS($file){
        // read excel file, item reference number, get from gtp_db and use its value
        // if have varients(xau_weight) between the excel file and gtp_db, just remark on gtp_db but still push to sap
        $pos1 = $this->app->getConfig()->{'gtp.pos1.partner.id'};
        $pos2 = $this->app->getConfig()->{'gtp.pos2.partner.id'};
        $pos3 = $this->app->getConfig()->{'gtp.pos3.partner.id'};
        $pos4 = $this->app->getConfig()->{'gtp.pos4.partner.id'};

        $partners = [$pos1, $pos2, $pos3, $pos4];

        $file = '';
    }

    private function getPartnersBranches($partners){
        $return_branches = [];
        foreach ($partners as $x => $partner){
            $branches = $this->app->PartnerStore()->getRelatedStore('branches')->searchTable()->select(["id","code"])->where('partnerid', $partner->id)->execute();
            foreach ($branches as $branch){
                $return_branches[$partner->id][$branch->id] = $branch->code;
            }
        }
        return $return_branches;
    }
    public function getPartnerFromBranch_POS($partner_branches, $branch_code){
        foreach ($partner_branches as $x => $partner){
            $branchid = array_search($branch_code, $partner);
            if (!empty($branchid)){
                $return['branchid'] = $branchid;
                $return['partnerid'] = $x;
                return $return;
            }
        }
        return false;
    }
    public function getPartnerFromBranch_POS_id($partner_branches, $branch_code){
        foreach ($partner_branches as $x => $partner){
            $branchid = array_search($branch_code, $partner);
            if (!empty($branchid)){
                $return['branchid'] = $branchid;
                $return['partnerid'] = $x;
                return $return;
            }
        }
        return false;
    }

    public function readImportGrnExcel_POS($file, $preview = false, $debug = false){
        // $version = '1.0';
        // $params['version'] = $version;
        // $params['code'] = $partner->sapcompanybuycode1; // customer id *required
        // $params['item'] = '';
        // $_RETURN_sap_rate_cards = $this->app->apiManager()->sapGetRateCard($version, $params);
        // $data = file_get_contents('C:\laragon\www\gtp\source\snaplib\manager\data.json');
        // // echo $data;
        // // print_r(json_decode($data, true));exit;
        // $data = json_decode($data, true);
        


        // read excel file, item reference number, get from gtp_db and use its value
        // if have varients(xau_weight) between the excel file and gtp_db, just remark on gtp_db but still push to sap

        // NOTE! -> change all old buyback purity weight into details json_encode()
        // NOTE! -> change all old buyback before 30-april all if branchid column == null search branch code, and replace branchid, currently all wrong inserted.

        $cacher = $this->app->getCacher();

        $pos1 = $this->app->getConfig()->{'gtp.pos1.partner.id'};
        $pos2 = $this->app->getConfig()->{'gtp.pos2.partner.id'};
        $pos3 = $this->app->getConfig()->{'gtp.pos3.partner.id'};
        $pos4 = $this->app->getConfig()->{'gtp.pos4.partner.id'};

        $partners = [$pos1, $pos2, $pos3, $pos4];

        // process readable array - START
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader();
        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file['tmp_name']);
        // print_r($inputFileType);exit;
        // $this->app->dd($inputFileType);
        if ($inputFileType == 'Xls'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }
        if ($inputFileType == 'Xlsx'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        if (!$reader){
            throw error;
        }
        $spreadsheet = $reader->load($file['tmp_name']);
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $partners = $this->app->PartnerStore()->searchTable()->select()->where('id', 'IN', [$partner_west, $partner_east])->execute();
        
        $items_array = [];
        
        // $this->app->dd($sheetData);
        
        // fix memory leak on branches from cache
        $branches = $this->getPartnersBranches($partners);
        $buyback = false;
        $tender = false;
        $excel_file_section = null;
        foreach ($sheetData as $x => $row){
            if ($row["A"] >= 1){
                if ($row["A"] == 1){
                    if ($excel_file_section == null){
                        $excel_file_section = 'buyback';
                    }else{
                        $excel_file_section = 'tender';
                    }
                }

                if ($excel_file_section == 'buyback'){
                    $item_array['index'] = $row["A"];
                    $item_array['purchase_no'] = $row["B"];
                    $item_array['referenceNo'] = $row["D"]; // buybackno
                    $item_array['customer_weight'] = $row["E"];
                    $item_array['vendor_weight'] = $row["F"];
                    $item_array['branch_code'] = $row["G"];
                
                    // $buyback = $this->app->buybackStore()->getByField('buybackno', trim($item_array['referenceNo']));
                    $buyback = $this->app->buybackStore()->searchTable()->select()->where('buybackno', trim($item_array['referenceNo']))->andWhere('status', 'not in', [2,6])->orderBy('id', 'desc')->one();
                    // var_dump($item_array['referenceNo']);exit;
                    $item_array['branchid'] = $buyback->branchid;
                    $item_array['partnerid'] = $buyback->partnerid;

                    $item_array['buybackid'] = $buyback->id;

                    $getbranchCode = $this->app->partnerStore()->getRelatedStore('branches')->getById($buyback->branchid);
                    $item_array['posbranchcode'] = $getbranchCode->code;
                    $item_array['type'] = 'buyback';
                }
                if ($excel_file_section == 'tender'){
                    // $item_array['index'] = $row["A"];
                    // $item_array['purchase_no'] = $row["C"];
                    // $item_array['referenceNo'] = $row["C"]; // tender item reference number
                    // $item_array['customer_weight'] = '-';
                    // $item_array['vendor_weight'] = $row["L"];
                    // $item_array['branch_code'] = $row["B"];
                    $item_array['index'] = $row["A"];
                    $item_array['purchase_no'] = $row["D"];
                    $item_array['referenceNo'] = $row["D"]; // tender item reference number
                    $item_array['customer_weight'] = '-';
                    $item_array['vendor_weight'] = $row["M"];
                    $item_array['branch_code'] = $row["C"];
                
                    $draft_item = $this->app->goodsreceivenotedraftStore()->searchTable()->select()->where('referenceno', trim($item_array['referenceNo']))->andWhere('status', 'NOT IN', [2,6])->orderby('id','desc')->one();
                    $grnorder = $this->app->goodsreceivenoteorderStore()->getById($draft_item->goodreceivednoteorderid);
                    $buyback = $this->app->buybackStore()->getById($grnorder->buybackid);
                    $item_array['branchid'] = $draft_item->branchid;
                    $item_array['partnerid'] = $buyback->partnerid;
                    
                    $item_array['buybackid'] = $buyback->id;
                    
                    $getbranchCode = $this->app->partnerStore()->getRelatedStore('branches')->getById($draft_item->branchid);
                    $item_array['posbranchcode'] = $getbranchCode->code;
                    $item_array['type'] = 'tender';
                }
                
                $draft_items = $this->app->goodsreceivenotedraftStore()->searchTable()->select()->where('referenceno', trim($item_array['purchase_no']))->andWhere('status', 'NOT IN', [2,6])->orderby('id','asc')->execute();
                $item_array['item_details'] = [];
                $item_array['item_draftid'] = [];
                if (!$draft_items){
                    // if has no inactive draft item (not yet submit to SAP)
                    continue;
                }
                // $dupicate = false;
                // $dupicate_compare = 0;
                foreach($draft_items as $draft_item_single){
                    // if ($dupicate_compare != $draft_item_single->goodreceivednoteorderid){
                    //     $dupicate = true;
                    //     continue;
                    // }
                    // $dupicate_compare = $draft_item_single->goodreceivednoteorderid;

                    $item_array['item_details'][] = json_decode($draft_item_single->details);
                    $item_array['item_draftid'][] = $draft_item_single->id;
                } 
                

                if (empty($item_array['branchid'])){
                    continue;
                }
                $branch = $this->app->partnerStore()->getRelatedStore('branches')->getById($item_array['branchid']);
                $item_array['branchname'] = $branch->name;
                
                $items_array[$item_array['posbranchcode']][$item_array['type']][$item_array['buybackid']][] = $item_array;
            }
        }
        // print_r($items_array);exit;
        // process readable array - END

        
        $sap_grn_request_data = [];
        $draftids = [];

        foreach ($items_array as $branchcode => $branch){
            foreach ($branch as $type => $buybacks){
                $ratecard = [];
                $comments = '';
                $po = [];
                $ratecard = [];
                $total_grn_weight = 0;
                foreach($buybacks as $buybackid => $buyback){
                    // $buyback_data = $this->app->buybackStore()->getById(3);
                    $buyback_data = $this->app->buybackStore()->getById($buybackid);

                    $version = '1.0';
                    $type = 'purchase';
                    $refOrKey = $buyback_data->buybackno;
                    $isRef = true;
                    $sap_po = $this->app->apiManager()->sapGetPostedOrders($version, $type, $refOrKey, $isRef);
                    // print_r($sap_po);exit;
                    if (!$sap_po){
                        continue;
                    }
                    $docEntry = $sap_po['hdr'][0]['docEntry'];
                    $docNum = $sap_po['hdr'][0]['docNum'];
                    
                    $po[] = [
                        "docEntry" => $docEntry,
                        "docNum" => $docNum,
                        "u_GTPREFNO" => $buyback_data->buybackno
                    ];
                    $total_gtpxauweight = 0;
                    $item_total_weight = 0;
                    // $this->app->dd($buyback);

                    $partner = $this->app->partnerStore()->getById($buyback_data->partnerid);
                    $params['version'] = '1.0';
                    $params['item'] = '';
                    $params['code'] = $partner->sapcompanybuycode1;
                    $ratecard_sap = $this->app->apiManager()->sapGetRateCard($version, $params);
                    $reformed_ratecard = [];
                    foreach($ratecard_sap['ratecard'] as $rate){
                        $reformed_ratecard[$rate['u_itemcode']] = $rate['u_purity'];
                    }
                    $reformed_ratecard['GS-OTHER-1'] = 98; // hardcode for 999 (Jewellery) as 98% - 06-Jan-2022
                    // $this->app->dd($reformed_ratecard);
                    
                    foreach ($buyback as $index => $draftItem){
                        // $total_gtpxauweight += $draftItem['gtpxauweight'];
                        $details = $draftItem['item_details'];
                        $draftids[] = $draftItem['item_draftid']; // update draft id status to submitted;
                        // $this->app->dd($buyback);p
                        foreach ($details as $item_detail){
                            if (is_array($item_detail)){
                                // different format from bb and tender mode _CAUTION 
                                // CHANGE TENDER FORMAT IF CAN and revert this is_array condition
                                foreach ($item_detail as $x_item_details){
                                    $sap_rate_itemcode = $this->formatBuybackPOSPurityToSAPCode($x_item_details->purity);
                                    if (!$sap_rate_itemcode){
                                        $sap_rate_itemcode = $x_item_details->purity;
                                    }
                                    $purity_value = $reformed_ratecard[$sap_rate_itemcode];
                                    $ratecard[] = [
                                        "u_itemcode" => $sap_rate_itemcode,
                                        "u_xauweight" => 0,
                                        "u_purity" => $purity_value,
                                        "u_inputweight" => $x_item_details->weight,
                                    ];
        
                                    $gtpxauweight = (floatval($purity_value) / 100) * floatval($x_item_details->weight);
                                    $total_gtpxauweight += $gtpxauweight;
                                    $item_total_weight += floatval($x_item_details->weight);
                                    $total_grn_weight += $gtpxauweight;
                                }
                            }else{
                                $sap_rate_itemcode = $this->formatBuybackPOSPurityToSAPCode($item_detail->purity);
                                if (!$sap_rate_itemcode){
                                    $sap_rate_itemcode = $item_detail->purity;
                                }
                                $purity_value = $reformed_ratecard[$sap_rate_itemcode];
                                $ratecard[] = [
                                    "u_itemcode" => $sap_rate_itemcode,
                                    "u_xauweight" => 0,
                                    "u_purity" => $purity_value,
                                    "u_inputweight" => $item_detail->weight,
                                ];

                                $gtpxauweight = (floatval($purity_value) / 100) * floatval($item_detail->weight);
                                $total_gtpxauweight += $gtpxauweight;
                                $item_total_weight += floatval($item_detail->weight);
                                $total_grn_weight += $gtpxauweight;
                            }
                            
                        }
                        
                        // if (floatval($total_grn_weight) != floatval($draftItem['vendor_weight'])){
                        //     $variants = (floatval($total_grn_weight) - floatval($draftItem['vendor_weight']));
                        //     $comments .= $buyback->referenceno.'>vrts:'.$variants.';';
                        //     // $comments .= $buyback['referenceNo'].'>vrts:'.$variants.';';
                        // }
                        // $ratecard[] = [
                            // "u_itemcode" => value.data.u_itemcode,
                            // "u_purity" => value.data.u_purity,
                            // "u_inputweight" => value.data.gtp_inputweight,
                            // "u_xauweight" => value.data.gtp_xauweight
                        // ];
                    }
                    $total_grn_weight = $total_grn_weight;
                    // $ratecard[] = $ratecard_item;
                    $partner = $this->app->partnerStore()->getById($buyback_data->partnerid);
                    $customer = $partner->sapcompanybuycode1;

                    $summary = [
                        "total_expected_xau" => 0,
                        "total_gross_weight" => 0,
                        "total_xau_collected" => $total_grn_weight,
                        "vatsum" => 0,
                    ];

                    // $draftids = $draft_item_ids;
                    // summary = {
                    //     "total_expected_xau": total_expected,
                    //     "total_gross_weight": total_gross_weight,
                    //     "total_xau_collected": total_xau_collected,
                    //     "vatsum": vatsum,
                    // }

                    // $branch = $this->app->partnerStore()->getRelatedStore('branches')->getByField('code', $branchcode);
                    

                    // $data = json_encode($data);
                    // post to  SAP_GRN;
                    // $return = $this->sendSAPGrnPOS($data);
                    // if (!$return){

                    // }
                }
                // print_r($ratecard);exit;


                // consolidate RATECARD START
                $new_rate = [];
                foreach ($ratecard as $rcard){
                    $new_rate[$rcard['u_itemcode']]['u_purity'] = $rcard['u_purity'];
                    $new_rate[$rcard['u_itemcode']]['u_inputweight'] += $rcard['u_inputweight'];
                }
                $final_rate = [];
                foreach ($new_rate as $key => $nrate){
                    $final_rate[] = [
                        "u_itemcode" => $key,
                        "u_xauweight" => 0,
                        "u_purity" => $nrate['u_purity'],
                        "u_inputweight" => $nrate['u_inputweight'],
                    ];
                }
                $ratecard = $final_rate;
                // consolidate RATECARD END

                $branchname = $this->app->partnerStore()->getRelatedStore('branches')->getbyField('code',$branchcode);
                $data = [
                    'po' => $po,
                    'ratecard' => $ratecard,
                    'customer' => $customer,
                    'comments' => 'POS:'.$branchcode.'POSNAME:'.$branchname->name.'<>V-'.$comments,
                    'summary' => $summary,
                ];
                $data = json_encode($data);
                // print_r($data);exit;
                // post to  SAP_GRN;

                if ($debug == false){
                    $return = $this->sendSAPGrnPOS($data);
                    if (!$return){

                    }
                    if ($return){
                        // foreach ($draftids as $draft_id){
                        //     // $draft = $this->app->goodsreceivenotedraftStore()->getById($draft_id);
                        //     // $draft->status = 2;
                        //     // $this->app->goodsreceivenotedraftStore()->save($draft);
                        // }
                    }
                }else{
                    echo "<pre>";
                    print_r($data);
                    echo "</pre>";
                }

                // $this->app->dd($data, false);
            }

        }
        
        return $return;

    }

    public function readImportGrnExcel_SHARED($file, $preview = false, $debug = false, $partners = null){
        // $version = '1.0';
        // $params['version'] = $version;
        // $params['code'] = $partner->sapcompanybuycode1; // customer id *required
        // $params['item'] = '';
        // $_RETURN_sap_rate_cards = $this->app->apiManager()->sapGetRateCard($version, $params);
        // $data = file_get_contents('C:\laragon\www\gtp\source\snaplib\manager\data.json');
        // // echo $data;
        // // print_r(json_decode($data, true));exit;
        // $data = json_decode($data, true);
        


        // read excel file, item reference number, get from gtp_db and use its value
        // if have varients(xau_weight) between the excel file and gtp_db, just remark on gtp_db but still push to sap

        // NOTE! -> change all old buyback purity weight into details json_encode()
        // NOTE! -> change all old buyback before 30-april all if branchid column == null search branch code, and replace branchid, currently all wrong inserted.

        $cacher = $this->app->getCacher();

        $pos1 = $this->app->getConfig()->{'gtp.pos1.partner.id'};
        $pos2 = $this->app->getConfig()->{'gtp.pos2.partner.id'};
        $pos3 = $this->app->getConfig()->{'gtp.pos3.partner.id'};
        $pos4 = $this->app->getConfig()->{'gtp.pos4.partner.id'};

        $partners = $partners ? $partners : [$pos1, $pos2, $pos3, $pos4];

        // process readable array - START
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader();
        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file['tmp_name']);
        // print_r($inputFileType);exit;
        // $this->app->dd($inputFileType);
        if ($inputFileType == 'Xls'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }
        if ($inputFileType == 'Xlsx'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        if (!$reader){
            throw error;
        }
        $spreadsheet = $reader->load($file['tmp_name']);
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $partners = $this->app->PartnerStore()->searchTable()->select()->where('id', 'IN', [$partner_west, $partner_east])->execute();
        
        $items_array = [];
        
        // $this->app->dd($sheetData);
        
        // fix memory leak on branches from cache
        $branches = $this->getPartnersBranches($partners);
        $buyback = false;
        $tender = false;
        $excel_file_section = null;
        foreach ($sheetData as $x => $row){
            if ($row["A"] >= 1){
                if ($row["A"] == 1){
                    if ($excel_file_section == null){
                        $excel_file_section = 'buyback';
                    }else{
                        $excel_file_section = 'tender';
                    }
                }

                if ($excel_file_section == 'buyback'){
                    $item_array['index'] = $row["A"];
                    $item_array['purchase_no'] = $row["B"];
                    $item_array['referenceNo'] = $row["D"]; // buybackno
                    $item_array['customer_weight'] = $row["E"];
                    $item_array['vendor_weight'] = $row["F"];
                    $item_array['branch_code'] = $row["G"];
                
                    // $buyback = $this->app->buybackStore()->getByField('buybackno', trim($item_array['referenceNo']));
                    $buyback = $this->app->buybackStore()->searchTable()->select()->where('buybackno', trim($item_array['referenceNo']))->andWhere('status', 'not in', [2,6])->orderBy('id', 'desc')->one();
                    // var_dump($item_array['referenceNo']);exit;
                    $item_array['branchid'] = $buyback->branchid;
                    $item_array['partnerid'] = $buyback->partnerid;

                    $item_array['buybackid'] = $buyback->id;

                    $getbranchCode = $this->app->partnerStore()->getRelatedStore('branches')->getById($buyback->branchid);
                    $item_array['posbranchcode'] = $getbranchCode->code;
                    $item_array['type'] = 'buyback';
                }
                if ($excel_file_section == 'tender'){
                    // $item_array['index'] = $row["A"];
                    // $item_array['purchase_no'] = $row["C"];
                    // $item_array['referenceNo'] = $row["C"]; // tender item reference number
                    // $item_array['customer_weight'] = '-';
                    // $item_array['vendor_weight'] = $row["L"];
                    // $item_array['branch_code'] = $row["B"];
                    $item_array['index'] = $row["A"];
                    $item_array['purchase_no'] = $row["D"];
                    $item_array['referenceNo'] = $row["D"]; // tender item reference number
                    $item_array['customer_weight'] = '-';
                    $item_array['vendor_weight'] = $row["M"];
                    $item_array['branch_code'] = $row["C"];
                
                    $draft_item = $this->app->goodsreceivenotedraftStore()->searchTable()->select()->where('referenceno', trim($item_array['referenceNo']))->andWhere('status', 'NOT IN', [2,6])->orderby('id','desc')->one();
                    $grnorder = $this->app->goodsreceivenoteorderStore()->getById($draft_item->goodreceivednoteorderid);
                    $buyback = $this->app->buybackStore()->getById($grnorder->buybackid);
                    $item_array['branchid'] = $draft_item->branchid;
                    $item_array['partnerid'] = $buyback->partnerid;
                    
                    $item_array['buybackid'] = $buyback->id;
                    
                    $getbranchCode = $this->app->partnerStore()->getRelatedStore('branches')->getById($draft_item->branchid);
                    $item_array['posbranchcode'] = $getbranchCode->code;
                    $item_array['type'] = 'tender';
                }
                
                $draft_items = $this->app->goodsreceivenotedraftStore()->searchTable()->select()->where('referenceno', trim($item_array['purchase_no']))->andWhere('status', 'NOT IN', [2,6])->orderby('id','asc')->execute();
                $item_array['item_details'] = [];
                $item_array['item_draftid'] = [];
                if (!$draft_items){
                    // if has no inactive draft item (not yet submit to SAP)
                    continue;
                }
                // $dupicate = false;
                // $dupicate_compare = 0;
                foreach($draft_items as $draft_item_single){
                    // if ($dupicate_compare != $draft_item_single->goodreceivednoteorderid){
                    //     $dupicate = true;
                    //     continue;
                    // }
                    // $dupicate_compare = $draft_item_single->goodreceivednoteorderid;

                    $item_array['item_details'][] = json_decode($draft_item_single->details);
                    $item_array['item_draftid'][] = $draft_item_single->id;
                } 
                

                if (empty($item_array['branchid'])){
                    continue;
                }
                $branch = $this->app->partnerStore()->getRelatedStore('branches')->getById($item_array['branchid']);
                $item_array['branchname'] = $branch->name;
                
                $items_array[$item_array['posbranchcode']][$item_array['type']][$item_array['buybackid']][] = $item_array;
            }
        }
        // print_r($items_array);exit;
        // process readable array - END

        
        $sap_grn_request_data = [];
        $draftids = [];

        foreach ($items_array as $branchcode => $branch){
            foreach ($branch as $type => $buybacks){
                $ratecard = [];
                $comments = '';
                $po = [];
                $ratecard = [];
                $total_grn_weight = 0;
                foreach($buybacks as $buybackid => $buyback){
                    // $buyback_data = $this->app->buybackStore()->getById(3);
                    $buyback_data = $this->app->buybackStore()->getById($buybackid);

                    $version = '1.0';
                    $type = 'purchase';
                    $refOrKey = $buyback_data->buybackno;
                    $isRef = true;
                    $sap_po = $this->app->apiManager()->sapGetPostedOrders($version, $type, $refOrKey, $isRef);
                    // print_r($sap_po);exit;
                    if (!$sap_po){
                        continue;
                    }
                    $docEntry = $sap_po['hdr'][0]['docEntry'];
                    $docNum = $sap_po['hdr'][0]['docNum'];
                    
                    $po[] = [
                        "docEntry" => $docEntry,
                        "docNum" => $docNum,
                        "u_GTPREFNO" => $buyback_data->buybackno
                    ];
                    $total_gtpxauweight = 0;
                    $item_total_weight = 0;
                    // $this->app->dd($buyback);

                    $partner = $this->app->partnerStore()->getById($buyback_data->partnerid);
                    $params['version'] = '1.0';
                    $params['item'] = '';
                    $params['code'] = $partner->sapcompanybuycode1;
                    $ratecard_sap = $this->app->apiManager()->sapGetRateCard($version, $params);
                    $reformed_ratecard = [];
                    foreach($ratecard_sap['ratecard'] as $rate){
                        $reformed_ratecard[$rate['u_itemcode']] = $rate['u_purity'];
                    }
                    $reformed_ratecard['GS-OTHER-1'] = 98; // hardcode for 999 (Jewellery) as 98% - 06-Jan-2022
                    // $this->app->dd($reformed_ratecard);
                    
                    foreach ($buyback as $index => $draftItem){
                        // $total_gtpxauweight += $draftItem['gtpxauweight'];
                        $details = $draftItem['item_details'];
                        $draftids[] = $draftItem['item_draftid']; // update draft id status to submitted;
                        // $this->app->dd($buyback);p
                        foreach ($details as $item_detail){
                            if (is_array($item_detail)){
                                // different format from bb and tender mode _CAUTION 
                                // CHANGE TENDER FORMAT IF CAN and revert this is_array condition
                                foreach ($item_detail as $x_item_details){
                                    $sap_rate_itemcode = $this->formatBuybackPOSPurityToSAPCode($x_item_details->purity);
                                    if (!$sap_rate_itemcode){
                                        $sap_rate_itemcode = $x_item_details->purity;
                                    }
                                    $purity_value = $reformed_ratecard[$sap_rate_itemcode];
                                    $ratecard[] = [
                                        "u_itemcode" => $sap_rate_itemcode,
                                        "u_xauweight" => 0,
                                        "u_purity" => $purity_value,
                                        "u_inputweight" => $x_item_details->weight,
                                    ];
        
                                    $gtpxauweight = (floatval($purity_value) / 100) * floatval($x_item_details->weight);
                                    $total_gtpxauweight += $gtpxauweight;
                                    $item_total_weight += floatval($x_item_details->weight);
                                    $total_grn_weight += $gtpxauweight;
                                }
                            }else{
                                $sap_rate_itemcode = $this->formatBuybackPOSPurityToSAPCode($item_detail->purity);
                                if (!$sap_rate_itemcode){
                                    $sap_rate_itemcode = $item_detail->purity;
                                }
                                $purity_value = $reformed_ratecard[$sap_rate_itemcode];
                                $ratecard[] = [
                                    "u_itemcode" => $sap_rate_itemcode,
                                    "u_xauweight" => 0,
                                    "u_purity" => $purity_value,
                                    "u_inputweight" => $item_detail->weight,
                                ];

                                $gtpxauweight = (floatval($purity_value) / 100) * floatval($item_detail->weight);
                                $total_gtpxauweight += $gtpxauweight;
                                $item_total_weight += floatval($item_detail->weight);
                                $total_grn_weight += $gtpxauweight;
                            }
                            
                        }
                        
                        // if (floatval($total_grn_weight) != floatval($draftItem['vendor_weight'])){
                        //     $variants = (floatval($total_grn_weight) - floatval($draftItem['vendor_weight']));
                        //     $comments .= $buyback->referenceno.'>vrts:'.$variants.';';
                        //     // $comments .= $buyback['referenceNo'].'>vrts:'.$variants.';';
                        // }
                        // $ratecard[] = [
                            // "u_itemcode" => value.data.u_itemcode,
                            // "u_purity" => value.data.u_purity,
                            // "u_inputweight" => value.data.gtp_inputweight,
                            // "u_xauweight" => value.data.gtp_xauweight
                        // ];
                    }
                    $total_grn_weight = $total_grn_weight;
                    // $ratecard[] = $ratecard_item;
                    $partner = $this->app->partnerStore()->getById($buyback_data->partnerid);
                    $customer = $partner->sapcompanybuycode1;

                    $summary = [
                        "total_expected_xau" => 0,
                        "total_gross_weight" => 0,
                        "total_xau_collected" => $total_grn_weight,
                        "vatsum" => 0,
                    ];

                    // $draftids = $draft_item_ids;
                    // summary = {
                    //     "total_expected_xau": total_expected,
                    //     "total_gross_weight": total_gross_weight,
                    //     "total_xau_collected": total_xau_collected,
                    //     "vatsum": vatsum,
                    // }

                    // $branch = $this->app->partnerStore()->getRelatedStore('branches')->getByField('code', $branchcode);
                    

                    // $data = json_encode($data);
                    // post to  SAP_GRN;
                    // $return = $this->sendSAPGrnPOS($data);
                    // if (!$return){

                    // }
                }
                // print_r($ratecard);exit;


                // consolidate RATECARD START
                $new_rate = [];
                foreach ($ratecard as $rcard){
                    $new_rate[$rcard['u_itemcode']]['u_purity'] = $rcard['u_purity'];
                    $new_rate[$rcard['u_itemcode']]['u_inputweight'] += $rcard['u_inputweight'];
                }
                $final_rate = [];
                foreach ($new_rate as $key => $nrate){
                    $final_rate[] = [
                        "u_itemcode" => $key,
                        "u_xauweight" => 0,
                        "u_purity" => $nrate['u_purity'],
                        "u_inputweight" => $nrate['u_inputweight'],
                    ];
                }
                $ratecard = $final_rate;
                // consolidate RATECARD END

                $branchname = $this->app->partnerStore()->getRelatedStore('branches')->getbyField('code',$branchcode);
                $data = [
                    'po' => $po,
                    'ratecard' => $ratecard,
                    'customer' => $customer,
                    'comments' => 'POS:'.$branchcode.'POSNAME:'.$branchname->name.'<>V-'.$comments,
                    'summary' => $summary,
                ];
                $data = json_encode($data);
                // print_r($data);exit;
                // post to  SAP_GRN;

                if ($debug == false){
                    $return = $this->sendSAPGrnPOS($data);
                    if (!$return){

                    }
                    if ($return){
                        // foreach ($draftids as $draft_id){
                        //     // $draft = $this->app->goodsreceivenotedraftStore()->getById($draft_id);
                        //     // $draft->status = 2;
                        //     // $this->app->goodsreceivenotedraftStore()->save($draft);
                        // }
                    }
                }else{
                    echo "<pre>";
                    print_r($data);
                    echo "</pre>";
                }

                // $this->app->dd($data, false);
            }

        }
        
        return $return;

    }

    private function map_purity($cell, $weight){
        if ($weight <= 0){
            return false;
        }
        switch ($cell) {
            case 'E':
                $purity = 'GS-999-9'; // format to sap_['u_itemcode'] => GS-999/24K
                break;
            case 'F':
                $purity = 'GS-950';
                break;
            case 'G':
                $purity = 'GS-916';
                break;
            case 'H':
                $purity = 'GS-875';
                break;
            case 'I':
                $purity = 'GS-835'; // 833
                break;
            case 'J':
                $purity = 'GS-750/18K';
                break;
            case 'K':
                $purity = 'GS-585/14K';
                break;
            case 'L':
                $purity = 'GS-375/9K';
                break;
            
            default:
                return false;
                break;
        }

        $purities = [
            "purity" => $purity,
            "weight" => $weight
        ];

        return $purities;
    }

    private function formatBuybackPOSPurityToSAPCode($buybackpurity){
        switch ($buybackpurity) {
            case '999 (Bars/Coins)':
                $sap_item_code = 'GS-999-9'; // format to sap_['u_itemcode'] => GS-999/24K
                break;
            case '999':
                $sap_item_code = 'GS-999-9'; // format to sap_['u_itemcode'] => GS-999/24K
                break;
            case '950':
                $sap_item_code = 'GS-950';
                break;
            case '916':
                $sap_item_code = 'GS-916';
                break;
            case '875':
                $sap_item_code = 'GS-875';
                break;
            case '835':
                $sap_item_code = 'GS-835'; // 833
                break;
            case '750':
                $sap_item_code = 'GS-750/18K';
                break;
            case '585':
                $sap_item_code = 'GS-585/14K';
                break;
            case '375':
                $sap_item_code = 'GS-375/9K';
                break;
            case '999 (Jewellery)':
                $sap_item_code = 'GS-OTHER-1';
                break;
            
            default:
                return false;
                break;
        }
        return $sap_item_code;
    }

    private function formatSapRateCardsReturn($sapRateCards = null){
        // print_r($sapRateCards['ratecard']);exit;
        // _PENDING TEMP
        // format sap return card to this format
        $cards = [
            // '999' => 98, // ` their purity code OR our purity code ` => ` _SAP_RETURN rate value `
            '999' => 100, // ` their purity code OR our purity code ` => ` _SAP_RETURN rate value `
            '950' => 95,
            '916' => 90,
            '875' => 85,
            '835' => 80,
            '750' => 75,
        ];
        // _PENDING TEMP

        $cards = [];
        foreach ($sapRateCards['ratecard'] as $x => $card){
            $cards[$card['u_itemcode']] = $card['u_purity'];
        }
        return $cards;
    }
    private function getSapRate($sapRateCards, $purity){
        return $sapRateCards[$purity];
    }

    private function sendSAPGrnPOS($data){
        try{

            
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $this->app->getDbHandle()->beginTransaction();
            $data = json_decode($data);

            // print_r($data);exit;
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
            $comments = $data->comments; // varaints from POS grn excel


            $total_xau_expected; // order xau
            $total_gross_weight = 0; // input weight
            $total_xau_collected; // input weight * purity 
            $vat_sum; // order xau - xau collected
            foreach ($data->ratecard as $x => $rateCard){
                // $data->ratecard[$x]->{"itemCode"} = $rateCard->u_itemcode;
                // // $data[$x]['quantity'] = $rateCard->u_xauweight;
                // $data->ratecard[$x]->{"quantity"} = 0;
                // $data->ratecard[$x]->{"U_PURITY"} = floatval($rateCard->u_purity);
                // $data->ratecard[$x]->{"U_GrossWeight"} = floatval($rateCard->u_inputweight);

                $data->ratecard[$x]->{"itemCode"} = $rateCard->u_itemcode;
                // $data[$x]['quantity'] = $rateCard->u_xauweight;
                $quantity = ((float) $rateCard->u_inputweight * (float) $rateCard->u_purity) / 100;
                $data->ratecard[$x]->{"quantity"} = (float) number_format($quantity, 3, '.', '');
                $data->ratecard[$x]->{"U_PURITY"} = (float) number_format(floatval($rateCard->u_purity), 2, '.', '');
                $data->ratecard[$x]->{"U_GrossWeight"} = (float) number_format(floatval($rateCard->u_inputweight), 3, '.', '');

                $total_gross_weight += floatval($rateCard->u_inputweight);
            }
            foreach ($data->po as $x => $po){
                $data->po[$x]->{"lineNum"} = 0;
                $data->po[$x]->{"sequence"} = $x + 1;
                
            }

            // print_r($data);exit;
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
            
            $return = $this->app->apiManager()->sapPostGrnDraft($version, $gtpNo, $sapCode, $data->po, $data->ratecard, $requestParams, $comments);
            if ($return->actionResult == false){
                throw new Exception($return->errorMessage);
            }

            $response = json_encode($return);
            $sapReturnCode = $return->actionResult;
            // print_r($return);exit;
            // summary = {
            //     "total_expected_xau": total_expected,
            //     "total_gross_weight": total_gross_weight,
            //     "total_xau_collected": total_xau_collected,
            //     "vatsum": vatsum,
            // }

            if ($return->actionSuccess == 1){
                $saveNewGrnDraft->jsonpostpayload = $payload;
                $saveNewGrnDraft->totalxauexpected = $data->summary->total_expected_xau;
                $saveNewGrnDraft->totalgrossweight = $total_gross_weight;
                $saveNewGrnDraft->totalxaucollected = $data->summary->total_xau_collected;
                $saveNewGrnDraft->vatsum = $data->summary->vatsum;
                $saveNewGrnDraft->status = \Snap\object\GoodsReceiveNote::STATUS_ACTIVE;
                $saveNewGrnDraft->comments = $data->comments;
                $saveNewGrnDraft = $this->app->goodsreceivenoteStore()->save($newGrnDraft);
            }

            $this->app->getDbHandle()->commit();
            $return = true;
            $this->log("POS grn excel upload successful on trans_sap_no:".$gtpNo, SNAP_LOG_DEBUG);
            return $return;
        }catch(\Exception $e){
            $this->log("POS grn excel upload failed on trans_sap_no:".$gtpNo, SNAP_LOG_ERROR);
            $this->log("POS grn excel upload failed on trans_sap_error:".$e, SNAP_LOG_ERROR);
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
    }


    public function updateBuybackData(){
        $pos1 = $this->app->getConfig()->{'gtp.pos1.partner.id'};
        $pos2 = $this->app->getConfig()->{'gtp.pos2.partner.id'};
        $pos3 = $this->app->getConfig()->{'gtp.pos3.partner.id'};
        $pos4 = $this->app->getConfig()->{'gtp.pos4.partner.id'};

        $partners = [$pos1, $pos2, $pos3, $pos4];

        $buybacks = $this->app->buybackStore()->searchTable()->select()->where('partnerid', 'IN', $partners)->andWhere('branchid', '>', 500)->execute();
        foreach ($buybacks as $buyback){
            
            $branch = $this->app->partnerStore()->getRelatedStore('branches')->searchTable()->select()->where('code', $buyback->branchid)->andWhere('partnerid', $buyback->partnerid)->one();
            $buyback->branchid = $branch->id;
            $this->app->buybackStore()->save($buyback);

        }

        $drafts = $this->app->goodsreceivenotedraftStore()->searchTable()->select()->execute();
        foreach ($drafts as $draft){
            if ($draft->details == '' && $draft->purity){
                if ($draft->purity == '9.99'){
                    $details['purity'] = '999';
                }elseif ($draft->purity == '1'){
                    $details['purity'] = '999';
                }elseif ($draft->purity == '4'){
                    $details['purity'] = '916';
                }elseif ($draft->purity == '6'){
                    $details['purity'] = '835';
                }elseif ($draft->purity == '7'){
                    $details['purity'] = '750';
                }else{
                    $details['purity'] = $draft->purity;
                }
                $details['weight'] = $draft->weight;
                $draft->details = json_encode($details);
                $this->app->goodsreceivenotedraftStore()->save($draft);
            }

            
            // $gtpxauweight = 0;
            // if ($draft->desc == 'tender'){
            //     $details = json_decode($draft->details);
            //     foreach ($details as $item){
            //         $gtpxauweight += floatval($item->weight);
            //     }
            // }else{
            //     $details = json_decode($draft->details);
            //     foreach ($details as $item){
            //         $gtpxauweight += floatval($item->weight);
            //     }
            // }

            if ($draft->branchid >= 500){
                $grnorder = $this->app->goodsreceivenoteorderStore()->getById($draft->goodreceivednoteorderid);
                $buyback = $this->app->buybackStore()->getById($grnorder->buybackid);
                $partner_id = $buyback->id;

                $branch = $this->app->partnerStore()->getRelatedStore('branches')->searchTable()->select()->where('code', $draft->branchid)->andWhere('partnerid', $buyback->partnerid)->one();
                $draft->branchid = $branch->id;
                $this->app->goodsreceivenotedraftStore()->save($draft);
            }
        }
    }
}
?>