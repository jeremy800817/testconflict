<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * Encapsulates the partner table on the database
 *
 * This class encapsulates the partner table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$id 						ID of the system
 * @property       	string 			    $code        				Partner code
 * @property       	string     			$name    					Partner name
 * @property       	string     			$address       				Partner address
 * @property        int       			$postcode   				Partner postcode
 * @property       	string     			$state    					Partner state
 * @property       	enum       			$type    					Partner type
 * @property        int       			$pricesourceid    			Price source id
 * @property        int       			$salespersonid    			USER ID
 * @property        int       			$tradingscheduleid    		trading schedule id
 * @property       	string     			$sapcompanysellcode1   		sell code 01 ( SAP_J )
 * @property       	string     			$sapcompanybuycode1   		buy code 01  ( SAP_J )
 * @property       	string     			$sapcompanysellcode2   		sell code 02 ( SAP_B )
 * @property       	string     			$sapcompanybuycode2   		buy code 02  ( SAP_B )
 * @property       	float      			$dailybuylimitxau   		daily buy limit
 * @property       	float      			$dailyselllimitxau   		daily sell limit
 * @property       	float      			$pricelapsetimeallowance   	Price lapse time allowance
 * @property       	enum      			$orderingmode   			Ordering mode
 * @property       	int      			$autosubmitorder   			Auto submit order
 * @property       	int      			$autocreatematchedorder   	Auto create match order
 * @property       	int      			$orderconfirmallowance   	Order confirm allowance
 * @property       	int      			$ordercancelallowance   	Order cancel allowance
 * @property        enum                $calculatormode             Calculation mode to use when computing prices
 * @property        String              $apikey                     Secret key to use for generate API digest
 * @property       	DateTime   			$createdon     				DateTime
 * @property       	int        			$createdby     				USER ID
 * @property       	DateTime 	   		$modifiedon    				DateTime
 * @property       	int         		$modifiedby    				User id
 * @property       	int       			$status    		    		status
 * @property       	string     			$group    		    		grouping
 * @property       	int       			$status    		    		status
 *
 * @author Ang
 * @version 1.0
 * @created 2019/1/17 9:30 AM
 */
class Partner extends SnapObject {

	const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_REJECTED = 3;

    const TYPE_CUSTOMER = 'Customer';
    const TYPE_REFERRAL = 'Referral';

    const MODE_NONE = 'None';
    const MODE_WEB = 'Web';
    const MODE_API = 'API';
    const MODE_BOTH = 'Both';

    const CALC_GTP = 'GTP';
    const CALC_MBB = 'MBB';
    const CALC_BMMB = 'BMMB';

    const PARENT_MASTER = 0;
    const PARENT_LOAN = 1;
    const PARENT_AFFILIATE = 2;
    const PARENT_PUBLIC = 3;
    const PARENT_AFFILIATEPUBLIC = 4;

    private $services = null;
    private $branches = null;
    private $deletedServices = [];
    private $deletedBranches = [];
    private $calculators = [];

	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
    protected function reset() {
        $this->members = array(
            'id' => null,
            'code' => null,
            'name' => null,
            'address' => null,
            'postcode' => null,
            'state' => null,
            'type' => null,
            'corepartner' => null,
            'pricesourceid' => null,
            'salespersonid' => null,
            'tradingscheduleid' => null,
            'sapcompanysellcode1' => null,
            'sapcompanybuycode1' => null,
            'sapcompanysellcode2' => null,
            'sapcompanybuycode2' => null,
            'dailybuylimitxau' => null,
            'dailyselllimitxau' => null,
            'pricelapsetimeallowance' => null,
            'orderingmode' => null,
            'autosubmitorder' => null,
            'autocreatematchedorder' => null,
            'orderconfirmallowance' => null,
            'ordercancelallowance' => null,
            'calculatormode' => self::CALC_GTP,
            'apikey' => null,
            'sharedgv' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,
            'group' => null,
            'parent' => null,
            'projectbase' => null,
            'sendername' => null,
            'senderemail' => null,
            'projectemail' => null,
        );		
        
        $this->viewMembers = [
            'salespersonname' => null,
            'pricesourcename' => null,
            'tradingschedulename' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
		];

    }
    

	/**
     * A validation function where to check mandatory fields
     *
     * @internal
     * @param $value
     * @param null|string $message
     * @param null|string $key
     * @throws InputException
     */
    private function validateMandatoryField($value, ?string $message, ?string $key): void
    {
        if (empty($value)) {
            throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
        }
    }

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
	 * valid state, the method will return false. Otherwise it will return true.
	 *
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	public function isValid() {
		$this->validateMandatoryField($this->members['pricesourceid'], 'Price source id is mandatory', 'pricesourceid');
        $this->validateMandatoryField($this->members['code'], 'Code is mandatory', 'code');
        $this->validateMandatoryField($this->members['name'], 'Name is mandatory', 'name');
        if (0 == strlen($this->members['code'])) {
            throw new InputException(gettext('Code is required'), InputException::FIELD_ERROR, 'code');
        } elseif (20 < strlen($this->members['code'])) {
            throw new InputException(gettext('Length of the code field can not be more than 20 characters'), InputException::FIELD_ERROR, 'code');
        } else {
            //Make sure that the code is unique.
            $res = $this->getStore()->searchTable()->select()->where('code', '=', $this->members['code']);
            if($this->members['id']) {
                $res = $res->andWhere('id', '!=', $this->members['id']);
            }
            $data = $res->count();
            if ($data) {
                throw new InputException(sprintf(gettext('The code %s is already in use by another entry'), $this->members['code']), InputException::FIELD_ERROR, 'code');
            }
        }
		return true;
	}

    /**
     * This method initialises and returns the array of partnerservices that is associated with it
     *
     * @return \Snap\object\partnerservice[] Array of partner services
     */
    public function getServices()
    {
        if (null == $this->services) {
            $serviceStore = $this->getStore()->getRelatedStore('services');
            $results = $serviceStore->searchTable()->select()->where('partnerid', $this->members['id'])->execute();
            if ($results) {
                foreach ($results as $aService) {
                    $aService->lockFromEdit();
                    $this->services[$aService->productid] = $aService;
                }
            } else {
                $this->services = [];
            }
        }
        return $this->services;
    }

    /**
     * This method initialises and returns the array of partnerservices that is associated with it
     *
     * @return \Snap\object\partnerservice[] Array of partner services
     */
    public function getBranches()
    {        
        if (null == $this->branches) {
            $branchStore = $this->getStore()->getRelatedStore('branches');
            $results = $branchStore->searchTable()->select()->where('partnerid', $this->members['id'])->execute();
            if ($results) {
                foreach ($results as $branch) {
                    $branch->lockFromEdit();
                    $this->branches[$branch->code] = $branch;
                }
            } else {
                $this->branches = [];
            }
        }
        return $this->branches;
    }
  


    /**
     * This method is called before a delete operation is done.
     * @internal
     * @return boolean  True if can continune to delete the object.  False otherwise.
     */
    public function onPredelete()
    {
        if (! $this->services) {
            $this->getServices();
        }
        foreach ($this->services as $aService) {
            $serviceStore->delete($aService);
        }
        foreach ($this->getBranches() as $aBranch) {
            $branchStore->delete($aBranch);
        }
        return parent::onPredelete();
    }

    /**
     * This method is called prior to the data being updated.  This method is intended to be used by
     * the object to make any prior actions before being updated
     * @internal
     * @return boolean   True if update can continue.  False otherwise.
     */
    public function onPrepareUpdate()
    {
        if ($this->services && 0 < $this->members['id']) {
            foreach ($this->services as $aService) {
                $aService->partnerid = $this->members['id'];
                if (! $aService->isValid()) {
                    return false;
                }  //ensure that the services settings are also correct.
            }
        }
        if (count($this->getBranches())) {
            foreach ($this->branches as $aBranch) {
                if (! $aBranch->isValid()) {
                    return false;
                }
            }
        }
        return parent::onPrepareUpdate();
    }

    /**
     * This method is used to inform the object that the update has been completed.  The object can
     * perform any further post update actions as required.
     * @internal
     * @param  IEntity $latestCopy The last copy of the object
     * @return void
     */
    public function onCompletedUpdate(IEntity $latestCopy)
    {
        $ret = parent::onCompletedUpdate($latestCopy);
        $serviceStore = $this->getStore()->getRelatedStore('services');
        if (is_array($this->deletedServices)) {
            foreach ($this->deletedServices as $toDelete) {
                $serviceStore->delete($toDelete);
            }
            $this->deletedServices = null;
        }
        if ($this->services) {
            foreach ($this->services as $key => $aService) {
                $this->services[$key]->partnerid = $this->members['id'];
                $serviceStore->save($this->services[$key]);
            }
        }
        $branchStore = $this->getStore()->getRelatedStore('branches');
        if (is_array($this->deletedBranches)) {
            foreach ($this->deletedBranches as $toDelete) {
                $branchStore->delete($toDelete);
            }
            $this->deletedBranches = null;
        }
        if ($this->branches) {
            foreach ($this->branches as $key => $aBranch) {
                $this->branches[$key]->partnerid = $this->members['id'];
                $branchStore->save($this->branches[$key]);
            }
        }
        return $ret;
    }

    /**
     * This method will implement serializing the object into a cacheable string for optimum storage.
     * @internal
     * @return String
     */
    public function toCache()
    {
        $objectPartStr = parent::toCache();
        if (! is_array($this->services)) {
            $this->getServices();
        }
        if (is_array($this->services)) {
            foreach ($this->services as $aCur => $aService) {
                $objectPartStr .= "*V*" . $aService->toCache();
            }
        }
        if (null == $this->branches) {
            $this->getBranches();
        }
        if (is_array($this->branches) && count($this->branches)) {
            foreach ($this->branches as $aBranch) {
                $objectPartStr .= "*BRC*" . $aBranch->toCache();
            }
        }
        return $objectPartStr;
    }

    /**
     * This method will need to implement expanding the object back to its original from the cached data provided.
     * @internal
     * @param  string $data The original data provided in toCache()
     * @return void
     */
    public function fromCache($data)
    {
        $branchStore = $this->getStore()->getRelatedStore('branches');
        $partsArray = explode('*BRC*', $data);
        for ($i = 1; $i < count($partsArray); $i++) {
            $aBranch = $branchStore->create();
            $aBranch->fromCache(($partsArray[$i]));
            $this->branches[] = $aBranch;
        }
        $data = $partsArray[0];
        $serviceStore = $this->getStore()->getRelatedStore('services');
        $partsArray = explode('*V*', $data);
        for ($i = 1; $i < count($partsArray); $i++) {
            $aService = $serviceStore->create();
            $aService->fromCache(($partsArray[$i]));
            $aService->lockFromEdit();
            $this->services[$aService->productid] = $aService;
        }
        $ret = parent::fromCache($partsArray[0]);
        if (is_array($this->branches) && count($this->branches)) {
            foreach ($this->branches as $aBranch) {
                //got to set the parent only after our own data has been restored.  otherwise ID = 0
                $aBranch->partnerid = $this->members['id'];
                $aBranch->lockFromEdit();
            }
        }
        return $ret;
    }

    /**
     * Add or update a service for partner
     * @param  Product $product           Product related for this service to add or update
     * @param  String  $partnersapgroup   SAP code
     * @param  Double  $refineryfee       Refinery fee amount
     * @param  Double  $premiumfee        Premium fee
     * @param  Bool    $includefeeinprice Whether to include the fees into the pricing or not
     * @param  Bool    $canbuy            If merchant can buy the product
     * @param  Bool    $cansell           If merchant can sell the product
     * @param  Bool    $canqueue          If merchant can put a future order for the product
     * @param  Bool    $canredeem         If merchant can redeen the product     * 
     * @param  float   $buyclickminxau     Buy Click Min XAU
     * @param  float   $buyclickmaxxau     Buy Click Max XAU
     * @param  float   $sellclickminxau    Sell Click Min XAU
     * @param  float   $sellclickmaxxau    Sell Click Max XAU
     * @param  Double  $dailybuylimitxau  Maximum weight for buy
     * @param  Double  $dailyselllimitxau Maximum weight for sekk
     * @param  float   $redemptionpremiumfee    Premium fee during redemption for this product
     * @param  float   $redemptioncommission    Commission during redemption for this product
     * @param  float   $redemptioninsurancefee  Insurance fee during redemption for this product
     * @param  float   $redemptionhandlingfee   Handling fee during redemption for this product
     * @return Boolean                    True if successful.  False / throw errors is not.
     */
    public function registerService( $product, $id,$partnersapgroup, $refineryfee, $premiumfee, $includefeeinprice, 
                                $canbuy, $cansell, $canqueue, $canredeem, $buyclickminxau,$buyclickmaxxau, $sellclickminxau,$sellclickmaxxau,
                                $dailybuylimitxau, $dailyselllimitxau, $redemptionpremiumfee, $redemptioncommission, $redemptioninsurancefee, $redemptionhandlingfee, $specialpricetype, $specialpricecondition, $specialpricecompanybuyoffset, $specialpricecompanyselloffset ) {
        if (! $this->services) {
            $this->getServices();
        }
        if (isset($this->services[$product->id])) {
            $selectedService = $this->services[$product->id];
            $selectedService = clone $selectedService;            
        } else {            
            $selectedService = $this->getStore()->getRelatedStore('services')->create(['productid' => $product->id, 'partnerid' => $this->members['id'],]);
        }        
        if(is_string($partnersapgroup) || '' != $partnersapgroup) {
            $selectedService->partnersapgroup = $partnersapgroup;
        }
        foreach(['refineryfee' => $refineryfee, 'premiumfee' => $premiumfee, 'buyclickminxau' => $buyclickminxau, 'buyclickmaxxau' => $buyclickmaxxau,'sellclickminxau' => $sellclickminxau, 'sellclickmaxxau' => $sellclickmaxxau, 
              'dailybuylimitxau' => $dailybuylimitxau, 'dailyselllimitxau' => $dailyselllimitxau, 'redemptionpremiumfee' => $redemptionpremiumfee,
              'redemptioncommission' => $redemptioncommission, 'redemptioninsurancefee' => $redemptioninsurancefee, 'redemptionhandlingfee' => $redemptionhandlingfee,
              'specialpricecondition' => $specialpricecondition,'specialpricecompanybuyoffset' => $specialpricecompanybuyoffset,'specialpricecompanyselloffset' => $specialpricecompanyselloffset,] as $field => $value) {
            if(is_numeric($value)) {
                $selectedService->{$field} = $value;
            }
        }

        foreach(['includefeeinprice' => $includefeeinprice, 'canbuy' => $canbuy, 'cansell' => $cansell, 
              'canqueue' => $canqueue, 'canredeem' => $canredeem] as $field => $value) {
            if(0 == $value || 1 == $value) {
                $selectedService->{$field} = $value;
            }
        }        
        
        foreach(['specialpricetype' => $specialpricetype ] as $field => $value) {
                $selectedService->{$field} = $value;
        }       
        $selectedService->status = 1;
        $selectedService->lockFromEdit();
        $this->services[$product->id] = $selectedService;
        return true; 
    }

    /**
     * This method is used to remove the selected services from the merchant.
     *
     * @api
     * @param  currency $currencyToRemove Currency to remove
     * @return void
     */
    public function unregisterService(Product $productToRemove)
    {
        $this->removeServiceForProduct($productToRemove);
    }

    /**
     * @api
     * Remove the services that are applied for the indicated currency.
     *
     * @param  currency $currency Currency object to remove
     * @return null
     */
    public function removeServiceForProduct(Product $product)
    {
        if (! $this->services) {
            $this->getServices();
        }
        foreach ($this->services as $key => $aService) {
            if ($product->id == $aService->productid) {
                unset($this->services[$key]);
                $this->deletedServices[] = $aService;
                break;
            }
        }
    }

    /**
     * Register a branch for this partner / merchant
     * @param  String $code    
     * @param  String $name    
     * @param  String $sapcode 
     * @return Boolean         
     */
    public function registerBranch($id,$code,$name,$sapcode,$address,$postcode,$city,$contactno,$status) {      
        if (! $this->branches) {
            $this->getBranches();
        } 
        if (isset($this->branches[$code])) {    
            $selectedBranch = $this->branches[$code];
            $selectedBranch = clone $selectedBranch;
        } else {           
            $selectedBranch = $this->getStore()->getRelatedStore('branches')->create(['code' => $code, 'partnerid' => $this->members['id'],'status' => $status]);
        }  
        $selectedBranch->code = $code;
        $selectedBranch->name = $name;
        $selectedBranch->sapcode = $sapcode;       
        $selectedBranch->address = $address;       
        $selectedBranch->postcode = $postcode;       
        $selectedBranch->city = $city;       
        $selectedBranch->contactno = $contactno;   
        $selectedBranch->status = $status; 
        $selectedBranch->lockFromEdit();
        //print_r($selectedBranch->toArray());       
        $this->branches[$code] = $selectedBranch;
        return true;
    }

    /**
     * This method is used to remove the selected branch from the merchant.
     *
     * @api
     * @param  PartnerBranchMap $branchToRemove Branch to remove
     * @return void
     */
    public function unregisterBranch(PartnerBranchMap $branchToRemove)
    {
        $this->removeBranch($branchToRemove);
    }

    /**
     * @api
     * Remove the services that are applied for the indicated Branch.
     *
     * @param  Branch $branch Branch object to remove
     * @return null
     */
    public function removeBranch(PartnerBranchMap $branch)
    {
        if (! $this->branches) {
            $this->getBranches();
        }
        foreach ($this->branches as $key => $Branch) {
            if ($branch->code == $key) {
                unset($this->branches[$key]);
                $this->deletedBranches[] = $Branch;
                break;
            }
        }
    }

    /**
     * Common method to return boolean data functions for different services.
     * @param  String  $action 
     * @param  Product $product
     * @return Boolean         
     */
    private function checkServiceStatus($action, $product)
    {
        if(! $this->services) {
            $this->getServices();
        }
        if(! isset($this->services[$product->id])) {
            return false;
        } elseif(method_exists($this->services[$product->id], 'can' . $action)) {
            return call_user_func_array([$this->services[$product->id], 'can' . $action], []);
        }
        return false;
    }

    /**
     * Checks if this merchant has services setup for the specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function hasService(Product $product)
    {
        if(! $this->services) {
            $this->getServices();
        }
        return isset($this->services[$product->id]);
    }

    /**
     * Checks if this merchant has can buy the specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function canBuy(Product $product)
    {
        return $this->checkServiceStatus('Buy', $product);
    }

    /**
     * Checks if this merchant has can sell the specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function canSell(Product $product)
    {
        return $this->checkServiceStatus('Sell', $product);
    }

    /**
     * Checks if this merchant has can scheduled future order the specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function canQueue(Product $product)
    {
        return $this->checkServiceStatus('Queue', $product);
    }

    /**
     * Checks if this merchant has can redeem the specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function canRedeem(Product $product)
    {
        return $this->checkServiceStatus('Redeem', $product);
    }

    /**
     * Checks if this merchant should include fee in the pricing the specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function includefeeinprice(Product $product)
     {
        return $this->checkServiceStatus('includefeeinprice', $product);
    }

    /**
     * gets the refinery fee specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function getRefineryFee($product)
    {
        return $this->services[$product->id]->refineryfee;
    }

    /**
     * gets the premium fee specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function getPremiumFee($product)
    {
        return $this->calculator()->round($this->services[$product->id]->premiumfee);
    }

    public function getRedemptionPremiumFee($product)
    {
        return $this->calculator()->round($this->services[$product->id]->redemptionpremiumfee);
    }

    public function getRedemptionCommissionFee($product)
    {
        return $this->calculator()->round($this->services[$product->id]->redemptioncommission);
    }

    public function getRedemptionInsuranceFee($product)
    {
        return $this->calculator()->round($this->services[$product->id]->redemptioninsurancefee);
    }

    public function getRedemptionHandlingFee($product)
    {
        return $this->calculator()->round($this->services[$product->id]->redemptionhandlingfee);
    }

    /**
     * gets the SAP code specified product
     * @param  Product $product 
     * @return boolean          
     */
    public function getSapGroupCode($product)
    {
        return $this->calculator()->round($this->services[$product->id]->premiumfee);
    }

    /**
     * gets the maximum amount to set per trx for product
     * @param  Product $product 
     * @return boolean         
     * param/operant $buy inherited from spotorder thought   
     */
    public function getProductClickMax($product, $buy)
    {
        // this buy is CompanyBuy
        if($buy != true){
            return $this->services[$product->id]->buyclickmaxxau;
        }else{
            return $this->services[$product->id]->sellclickmaxxau;
        }
    }

    /**
     * gets the minimum amount to set per trx for product
     * @param  Product $product 
     * @return boolean
     * param/operant $buy inherited from spotorder thought          
     */
    public function getProductClickMin($product, $buy)
    {
        // this buy is CompanyBuy
        if($buy != true){
            return $this->services[$product->id]->buyclickminxau;
        }else{
            return $this->services[$product->id]->sellclickminxau;
        }
    }

    /**
     * gets the daily maximum purchase amount limit for the product
     * @param  Product $product 
     * @return boolean          
     */
    public function getProductDailyBuyLimit($product)
    {
        // _REMARKS => spot order manager and future order manager use `company perspective`, but here is partner perspective
        // _TODO => fix variable naming on manager 
        return $this->services[$product->id]->dailyselllimitxau;
    }

    /**
     * gets the daily minimum purchase amount limit for the product
     * @param  Product $product 
     * @return boolean          
     */
    public function getProductDailySellLimit($product)
    {
        // _REMARKS => spot order manager and future order manager use `company perspective`, but here is partner perspective
        // _TODO => fix variable naming on manager 
        return $this->services[$product->id]->dailybuylimitxau;
    }

    /**
     * Returns the service by the product
     * @param  Product $product [description]
     * @return [type]           [description]
     */
    public function getServiceForProduct(Product $product) {
        if(! $this->services) {
            $this->getServices();
        }
        if(isset($this->services[$product->id])) {
            return $this->services[$product->id];
        }
        return null;
    }

    /**
     * Returns the branch specified by code
     * @param  String $code
     * @return PartnerBranchMap
     */
    public function getBranch($code) {
        if(! $this->branches) {
            $this->getBranches();
        }
        if(isset($this->branches[$code])) {
            return $this->branches[$code];
        }
        return null;
    }

    /**
     * Gets a calculation utility out based on merchant's preference.
     * @return \Snap\util\Calculator
     */
    public function calculator($forPrice = true)
    {
        // Updated by Cheok on 2021-05-24 to fix wrong decimal when calling calculator() with different $forPrice
        if(! $this->calculators[$forPrice]) {
        // End update by Cheok
            $useRounding = true;
            $roundResultsOnly = true;
            if(self::CALC_GTP == $this->calculatormode) {
                $numDecimals = 3;
            } else if (self::CALC_BMMB == $this->calculatormode) {
                $numDecimals = $forPrice ? 2 : 3;
            } else if(self::CALC_MBB == $this->calculatormode && $forPrice) {
                $numDecimals = 2;
            } else if(self::CALC_MBB == $this->calculatormode && ! $forPrice) {
                $numDecimals = 3;
            }
            $this->calculators[$forPrice] = new \Snap\util\Calculator($numDecimals, $useRounding, $roundResultsOnly);
        }
        return $this->calculators[$forPrice];
    }

    /**
     * Gets all partner parent types from system
     * @return \Snap\util\Calculator
     */
    public function getPartnerParentStatus() {



        $typeArr = [];

        $statusname = "";



        $displayStatus = [

            self::PARENT_MASTER => array( 'forSalesman' => false, 'forOperator' => true, 'color' => '#E20404', 'description' => gettext('Master')), // Main Master
            self::PARENT_LOAN => array( 'forSalesman' => false, 'forOperator' => true, 'color' => '#B2FC00', 'description' => gettext('Loan')), // Loan partner
            self::PARENT_AFFILIATE => array( 'forSalesman' => false, 'forOperator' => true, 'color' => '#2719F7', 'description' => gettext('Affiliate Member')), // Affiliate
            self::PARENT_PUBLIC => array( 'forSalesman' => true, 'forOperator' => true, 'color' => '#E20404', 'description' => gettext('Public')), // Public
            self::PARENT_AFFILIATEPUBLIC => array( 'forSalesman' => true, 'forOperator' => true, 'color' => '#E20404', 'description' => gettext('Affiliate Public')), // Affiliate public

        ];

        //determine user type to show the appropriate statuses only

        // $userCheckField = ($user->isSale()) ? 'forSalesman' : 'forOperator';

        foreach($displayStatus as $status => $data) {

            // if( $data[$userCheckField]) {
				
            //     $typeArr[] = (object)array("id" => $status, "code" => $data['description']);

            // }
            $typeArr[] = (object)array("id" => $status, "code" => $data['description'], "color" => $data['color']);
        }

        return $typeArr;

    }
}
?>
