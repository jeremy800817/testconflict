<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Shahanas
 * @version 1.0
 */
class goldbarstatushandler extends CompositeHandler {
	function __construct(App $app) {
		//parent::__construct('/root/mbb', 'goldbarstatus');
        //parent::__construct('/root/mbb;/root/bmmb;/root/system;', 'goldbarstatus');
        $this->mapActionToRights('getstatuscount', '/root/mbb/goldbarstatus/list');      
        $this->mapActionToRights('getMintedGoldbarDetails', '/root/mbb/goldbarstatus/list;/root/bmmb/mintedbar/list;/root/system/mintedbar/list;');  
        $this->mapActionToRights('getSharedMintedList', '/root/bmmb/mintedbar/list;/root/system/mintedbar/list;');
        $this->mapActionToRights('exportMintedList', '/root/bmmb/mintedbar/list;/root/system/mintedbar/list;');
              
        $this->app = $app;
		$vaultitemStore = $app->vaultitemfactory();		
		$this->addChild(new ext6gridhandler($this, $vaultitemStore, 1 ));
    }

    function getstatuscount($app,$params){
        $partnerid=$app->getConfig()->{'gtp.mib.partner.id'};   
        if($partnerid==null){            
            throw new \Exception("Partner id does not exists");
        } 
        $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid', $partnerid)->where('weight', 1000)->execute();        
        $logical=$countinacehq=$countinaceg4s=$countinmbbg4s=$total=$overall=0;
        if (count($items) > 0){
            foreach($items as $aItem){              
               if($aItem->vaultlocationid==1)$countinacehq++;
               if($aItem->vaultlocationid==2)$countinaceg4s++;
               if($aItem->vaultlocationid==3)$countinmbbg4s++;              
               if(!$aItem->vaultlocationid && null == $aItem->deliveryordernumber &&  1 == $aItem->status)$logical++; 
               // Old logical check
               /*if($aItem->productid!=null && 
                    $aItem->weight!=null && 
                    $aItem->partnerid!=null && 
                    $aItem->serialno!=null && 
                    $aItem->deliveryordernumber==null && 
                    $aItem->allocated==0 && 
                    ($aItem->allocatedon=='0000-00-00 00:00:00' || $aItem->allocatedon==null) && 
                    $aItem->status == 1)
                {
                   $logical++;
                };*/
            }
            $total=$countinacehq+$countinaceg4s+$countinmbbg4s;
            $overall=$logical+$countinacehq+$countinaceg4s+$countinmbbg4s;
        }      
        echo json_encode([
            'success' => true,
            'logicalcount'=>$logical,
            'hqcount' => $countinacehq,
            'aceg4scount' => $countinaceg4s,
            'mbbg4scount' => $countinmbbg4s,
            'total'=>$total,
            'overall'=>$overall
        ]);  
    }
    function getMintedGoldbarDetails($app,$params){                
        $partnerid=$app->getConfig()->{'gtp.mib.partner.id'};   
        if($partnerid==null){            
            throw new \Exception("Partner id does not exists");
        } 
        $partner=$this->app->partnerStore()->getById($partnerid);   
        $apimanager=$this->app->apiManager();
        $result = $apimanager->sapGetWarehouseList($partner,'1.0m');         
        $responseArray= $finalRespArray=array();
        if($result[0]['success']=='N'){
            echo json_encode(array('records'=>$finalRespArray));   
            exit();
        }        
        if(sizeof($result)>0){
            // Add total to list
            $responseArray["MIB_TOTAL"]['bin']= "TOTAL_QTY_BY_DENO";
            $responseArray["MIB_TOTAL"]['branch']= "";
            $responseArray["MIB_TOTAL"]['1_gram']=  $result['denominationList']['GS-999-9-1g'];
            $responseArray["MIB_TOTAL"]['5_gram']=  $result['denominationList']['GS-999-9-5g'];
            $responseArray["MIB_TOTAL"]['10_gram']=  $result['denominationList']['GS-999-9-10g'];
            $responseArray["MIB_TOTAL"]['50_gram']=  $result['denominationList']['GS-999-9-50g'];
            $responseArray["MIB_TOTAL"]['100_gram']=  $result['denominationList']['GS-999-9-100g'];
            $responseArray["MIB_TOTAL"]['1000_gram']=  $result['denominationList']['GS-999-9-1000g'];
            
            
            foreach($result as $data ){
                if(!$responseArray[$data['BinCode']]){
                    $responseArray[$data['BinCode']]=array('bin'=>$data['BinCode'],'branch'=>'-','1_gram'=>0,'5_gram'=>0,'10_gram'=>0,'50_gram'=>0,'100_gram'=>0,'1000_gram'=>0);   
                }                
                $partnerBranch = $app->partnerStore()->getRelatedStore('branches')->searchTable()->select()->where('partnerid', $partnerid)->where('sapcode', $data['BinCode'])->execute();
                foreach($partnerBranch as $branchObj){
                    $responseArray[$data['BinCode']]['branch']= $branchObj->name;
                }
                if($data['ItemCode']=='GS-999-9-1g'){
                    $responseArray[$data['BinCode']]['1_gram']=$data['OnHandQty'];
                }else if($data['ItemCode']=='GS-999-9-5g'){
                    $responseArray[$data['BinCode']]['5_gram']=$data['OnHandQty'];
                }else if($data['ItemCode']=='GS-999-9-10g'){
                    $responseArray[$data['BinCode']]['10_gram']=$data['OnHandQty'];
                }else if($data['ItemCode']=='GS-999-9-50g'){
                    $responseArray[$data['BinCode']]['50_gram']=$data['OnHandQty'];
                }else if($data['ItemCode']=='GS-999-9-100g'){
                    $responseArray[$data['BinCode']]['100_gram']=$data['OnHandQty'];
                }else if($data['ItemCode']=='GS-999-9-1000g'){
                    $responseArray[$data['BinCode']]['1000_gram']=$data['OnHandQty'];
                }
            }
            ksort($responseArray);                  
            foreach( $responseArray as $data){
                $total=0;
                $total=$data['1_gram']+$data['5_gram']+$data['10_gram']+$data['50_gram']+$data['100_gram']+$data['1000_gram'];
                $data['total']= $total;
                array_push($finalRespArray,$data);
            }
        }        
        /* $key2=$this->searchForStore('BIN 33', $finalRespArray);
        if($key2!=null){
            $this->__unshift($finalRespArray, $finalRespArray[$key2]);
        } 
        $key1=$this->searchForStore('BIN 32', $finalRespArray);
        if($key1!=null){
            $this->__unshift($finalRespArray, $finalRespArray[$key1]);
        } */   
        $key3=$this->searchForStore('MIB_BNK-SIT', $finalRespArray);
        if($key3!=null){
            $this->__unshift($finalRespArray, $finalRespArray[$key3]);
        } 
        $key2=$this->searchForStore('MIB_BNK-47620', $finalRespArray);
        if($key2!=null){
            $this->__unshift($finalRespArray, $finalRespArray[$key2]);
        }     
        $key1=$this->searchForStore('TOTAL_QTY_BY_DENO', $finalRespArray);
        if($key1!=null){
            $this->__unshift($finalRespArray, $finalRespArray[$key1]);
        } 
        echo json_encode(array('records'=>$finalRespArray));   
       
    }  
    function getSharedMintedList($app,$params){                
        $partnerid=$app->getConfig()->{'gtp.mib.partner.id'};   
        if($partnerid==null){            
            throw new \Exception("Partner id does not exists");
        } 
        //$partner=$this->app->partnerStore()->getById($partnerid);   
        $partner = 'MINT_WHS';

        // check partner code 
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            //$partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $partner = 'MINT_WHS';
        }else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            //$partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $partner = 'BURSA';
        } 
        $apimanager=$this->app->apiManager();
        // If partnercode bmmb
        // if($params['partnercode']){
        //     $result = $apimanager->sapGetSharedMintedList($partner,'1.0');      
        // }else{
        //     $result = $apimanager->sapGetSharedMintedList($partner,'1.0my');      
        // }
        // As bmmb share same vault as common ( gogold etc)
        $result = $apimanager->sapGetSharedMintedList($partner,'1.0my'); 
   
        $responseArray= $finalRespArray=array();
        if($result[0]['success']=='N'){
            echo json_encode(array('records'=>$finalRespArray));   
            exit();
        }        
        // if(sizeof($result)>0){
        //     // Add total to list
        //     //$responseArray["MW_TOTAL"]['bin']= "TOTAL_QTY_BY_DENO";
        //     //$responseArray["MW_TOTAL"]['branch']= "";
        //     $responseArray["MW_TOTAL"]['1_gram']=  $result['denominationList']['GS-999-9-1g'];
        //     $responseArray["MW_TOTAL"]['5_gram']=  $result['denominationList']['GS-999-9-5g'];
        //     $responseArray["MW_TOTAL"]['10_gram']=  $result['denominationList']['GS-999-9-10g'];
        //     $responseArray["MW_TOTAL"]['50_gram']=  $result['denominationList']['GS-999-9-50g'];
        //     $responseArray["MW_TOTAL"]['100_gram']=  $result['denominationList']['GS-999-9-1-DINAR'];
        //     $responseArray["MW_TOTAL"]['1000_gram']=  $result['denominationList']['GS-999-9-5-DINAR'];
            
            
        //     foreach($result as $data ){
        //         if(!$responseArray[$data['BinCode']]){
        //             $responseArray[$data['BinCode']]=array('bin'=>$data['BinCode'],'branch'=>'-','1_gram'=>0,'5_gram'=>0,'10_gram'=>0,'50_gram'=>0,'100_gram'=>0,'1000_gram'=>0);   
        //         }                
        //         $partnerBranch = $app->partnerStore()->getRelatedStore('branches')->searchTable()->select()->where('partnerid', $partnerid)->where('sapcode', $data['BinCode'])->execute();
        //         foreach($partnerBranch as $branchObj){
        //             $responseArray[$data['BinCode']]['branch']= $branchObj->name;
        //         }
        //         if($data['ItemCode']=='GS-999-9-1g'){
        //             $responseArray[$data['BinCode']]['1_gram']=$data['OnHandQty'];
        //         }else if($data['ItemCode']=='GS-999-9-5g'){
        //             $responseArray[$data['BinCode']]['5_gram']=$data['OnHandQty'];
        //         }else if($data['ItemCode']=='GS-999-9-10g'){
        //             $responseArray[$data['BinCode']]['10_gram']=$data['OnHandQty'];
        //         }else if($data['ItemCode']=='GS-999-9-50g'){
        //             $responseArray[$data['BinCode']]['50_gram']=$data['OnHandQty'];
        //         }else if($data['ItemCode']=='GS-999-9-100g'){
        //             $responseArray[$data['BinCode']]['100_gram']=$data['OnHandQty'];
        //         }else if($data['ItemCode']=='GS-999-9-1000g'){
        //             $responseArray[$data['BinCode']]['1000_gram']=$data['OnHandQty'];
        //         }
        //     }
        //     ksort($responseArray);                  
        //     foreach( $responseArray as $data){
        //         $total=0;
        //         $total=$data['1_gram']+$data['5_gram']+$data['10_gram']+$data['50_gram']+$data['100_gram']+$data['1000_gram'];
        //         $data['total']= $total;
        //         array_push($finalRespArray,$data);
        //     }
        // }        
        if ($result['denominationList']['GS-999-9-0.5g']){
            $responseArray["MW_TOTAL"]['0.5_gram']=  $result['denominationList']['GS-999-9-0.5g'];
        }else {
            $responseArray["MW_TOTAL"]['0.5_gram']= 0;
        }

        if ($result['denominationList']['GS-999-9-1g']){
            $responseArray["MW_TOTAL"]['1_gram']=  $result['denominationList']['GS-999-9-1g'];
        }else {
            $responseArray["MW_TOTAL"]['1_gram']=  0;
        }

        if ($result['denominationList']['GS-999-9-2.5g']){
            $responseArray["MW_TOTAL"]['2.5_gram']=  $result['denominationList']['GS-999-9-2.5g'];
        }else {
            $responseArray["MW_TOTAL"]['2.5_gram']=  0;
        }

        if ($result['denominationList']['GS-999-9-5g']){
            $responseArray["MW_TOTAL"]['5_gram']=  $result['denominationList']['GS-999-9-5g'];
        }else {
            $responseArray["MW_TOTAL"]['5_gram']=  0;
        }

        if ($result['denominationList']['GS-999-9-10g']){
            $responseArray["MW_TOTAL"]['10_gram']=  $result['denominationList']['GS-999-9-10g'];
        }else {
            $responseArray["MW_TOTAL"]['10_gram']=  0;
        }

        if ($result['denominationList']['GS-999-9-50g']){
            $responseArray["MW_TOTAL"]['50_gram']=  $result['denominationList']['GS-999-9-50g'];
        }else {
            $responseArray["MW_TOTAL"]['50_gram']=  0;
        }

        if ($result['denominationList']['GS-999-9-100g']){
            $responseArray["MW_TOTAL"]['100_gram']=  $result['denominationList']['GS-999-9-100g'];
        }else {
            $responseArray["MW_TOTAL"]['100_gram']=  0;
        }

        if ($result['denominationList']['GS-999-9-1000g']){
            $responseArray["MW_TOTAL"]['1000_gram']=  $result['denominationList']['GS-999-9-1000g'];
        }else {
            $responseArray["MW_TOTAL"]['1000_gram']=  0;
        }

       
        if ($result['denominationList']['GS-999-9-1-DINAR']){
            $responseArray["MW_TOTAL"]['1_DINAR']=  $result['denominationList']['GS-999-9-1-DINAR'];
        }else {
            $responseArray["MW_TOTAL"]['1_DINAR']=  0;
        }

        if ($result['denominationList']['GS-999-9-5-DINAR']){
            $responseArray["MW_TOTAL"]['5_DINAR']=  $result['denominationList']['GS-999-9-5-DINAR'];
        }else {
            $responseArray["MW_TOTAL"]['5_DINAR']=  0;
        }

        

        echo json_encode([
            'success' => true,
            'zeropointfivegrams' =>  $responseArray["MW_TOTAL"]['0.5_gram'],
            'onegram'=>  $responseArray["MW_TOTAL"]['1_gram'],
            'twopointfivegrams' =>  $responseArray["MW_TOTAL"]['2.5_gram'],
            'fivegrams' =>  $responseArray["MW_TOTAL"]['5_gram'],
            'tengrams' =>  $responseArray["MW_TOTAL"]['10_gram'],
            'fiftygrams' => $responseArray["MW_TOTAL"]['50_gram'],
            'hundredgrams' => $responseArray["MW_TOTAL"]['100_gram'],
            'thousandgrams' => $responseArray["MW_TOTAL"]['1000_gram'],
            'onedinar' => $responseArray["MW_TOTAL"]['1_DINAR'],
            'fivedinar' => $responseArray["MW_TOTAL"]['5_DINAR'],
            'total'=> $result['denominationList'],
            'overall'=> $result['inventoryList']
        ]);  
       
    }  
    function __unshift(&$array, $value){
        $key = array_search($value, $array);
        if($key) unset($array[$key]);
        array_unshift($array, $value);  
        return $array;
    }    
    function searchForStore($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['bin'] === $id) {
                return $key;
            }
        }
        return null;
     }

    function exportMintedList($app, $params){

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        $partner = 'MINT_WHS';
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $partner = 'MINT_WHS';
        }else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $partner = 'BURSA';
        } 
        $apimanager=$this->app->apiManager();
         // If partnercode bmmb
         if($params['partnercode']){
            $results = $apimanager->sapGetSharedMintedList($partner,'1.0');      
        }else{
            $results = $apimanager->sapGetSharedMintedList($partner,'1.0my');      
        }         
        $responseArray= $finalRespArray=array();
        if($results[0]['success']=='N'){
            echo json_encode(array('records'=>$finalRespArray));   
            exit();
        }    
        $count_05 = 0;
        $count_1 = 0;
        $count_25 = 0;
        $count_5 = 0;
        $count_10 = 0;
        $count_50 = 0;
        $count_100 = 0;
        $count_1000 = 0;
        $count_1_Dinar = 0;
        $count_5_Dinar = 0;
        // Loop for correct inventory SN and make new response array
        foreach ($results['inventoryList'] as $result){
            
            // Compile all results into one excel
            if ($result['ItemCode'] == 'GS-999-9-0.5g'){
                // save sn and qty
                $responseArray[$count_05]["GS-999-9-0.5g"]= $result['Serial'];
                $count_05++;

            }else if ($result['ItemCode'] == 'GS-999-9-1g'){
                // save sn and qty
                $responseArray[$count_1]["GS-999-9-1g"]= $result['Serial'];
                $count_1++;

            }else if ($result['ItemCode'] == 'GS-999-9-2.5g'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_25]["GS-999-9-2.5g"]= $result['Serial'];
                $count_25++;
                
            }else if ($result['ItemCode'] == 'GS-999-9-5g'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_5]["GS-999-9-5g"]= $result['Serial'];
                $count_5++;
                
            }else if ($result['ItemCode'] == 'GS-999-9-10g'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_10]["GS-999-9-10g"]= $result['Serial'];
                $count_10++;
                
            }else if ($result['ItemCode'] == 'GS-999-9-50g'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_50]["GS-999-9-50g"]= $result['Serial'];
                $count_50++;
                
            }else if ($result['ItemCode'] == 'GS-999-9-100g'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_100]["GS-999-9-100g"]= $result['Serial'];
                $count_100++;
                
            }else if ($result['ItemCode'] == 'GS-999-9-1000g'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_1000]["GS-999-9-1000g"]= $result['Serial'];
                $count_1000++;
                
            }else if ($result['ItemCode'] == 'GS-999-9-1-DINAR'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_1_Dinar]["GS-999-9-1-DINAR"]= $result['Serial'];
                $count_1_Dinar++;
                
            }else if ($result['ItemCode'] == 'GS-999-9-5-DINAR'){
                // save sn and qty
                //print_r($result['Serial']);
                $responseArray[$count_5_Dinar]["GS-999-9-5-DINAR"]= $result['Serial'];
                $count_5_Dinar++;
                
            }

            
            // Sort array keys

            $new_keys = array('GS-999-9-0.5g', 
                                'GS-999-9-1g', 
                                'GS-999-9-2.5g',
                                'GS-999-9-5g', 
                                'GS-999-9-10g',
                                'GS-999-9-50g', 
                                'GS-999-9-100g',
                                'GS-999-9-1000g',
                                'GS-999-9-1-DINAR', 
                                'GS-999-9-5-DINAR',
                            );

            // Start old code ( individual download )
            // // check if type matches deno type
            // if ($result['ItemCode'] == $params["type"]){
            //     // save sn and qty
            //     //print_r($result['Serial']);
            //     $responseArray[$count]["serial"]= $result['Serial'];
            //     // Do special rendering for minted bar
            //     // If more than 0, means not redeemed yet
            //     if($result['Total Qty'] > 0){
            //         $responseArray[$count]["quantity"]= "Yes";
            //     }else{
            //         $responseArray[$count]["quantity"]= "No";
            //     }
                
            //     //print_r($result['Total Qty']);
            //     $count++;
                
            // }
            // End old code
        }

        $this->app->reportingManager()->generateMintedExportFile($responseArray, $header, $dateRange->startDate, $dateRange->endDate, $params["type"]);
    }

}

?>
