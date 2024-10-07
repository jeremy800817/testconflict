<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////


namespace Snap\handler;

use Snap\App;
use Snap\InputException;
use Snap\object\MyPartnerSapSetting;
use Snap\object\MyPartnerSapSettingCode;
use Snap\object\MyPartnerSetting;
use Snap\object\Partner;

/**
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class mypartnerHandler
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getSettings($partnerId)
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);

        if ($settings) {
            $settings = $settings->toArray();

            foreach ($settings as $key => $val) {
                if ($settings[$key] instanceof \DateTime) {
                    $settings[$key] = $settings[$key]->format('Y-m-d H:i:s');
                }
            }
            $settings['partnersettingid'] = $settings['id'];
            unset($settings['id']);
            return $settings;
        }

        return [];
    }

    public function getSapSettings($partnerId)
    {
        $settings = $this->app->mypartnersapsettingStore()->searchTable()->select()
                        ->where('partnerid', $partnerId)
                        ->andWhere('status', MyPartnerSapSetting::STATUS_ACTIVE)
                        ->execute();
        $bpcodes = [];
        $records = [];
        foreach ($settings as $record) {
            $tmp = $record->toArray();
            $tmp['sapsettingid'] = $record->id;
            $tmp['header_tradebp_v'] = filter_var($record->tradebpvendor, FILTER_VALIDATE_BOOLEAN);
            $tmp['header_tradebp_c'] = filter_var($record->tradebpcus, FILTER_VALIDATE_BOOLEAN);
            $tmp['header_nontradebp_v'] = filter_var($record->nontradebpvendor, FILTER_VALIDATE_BOOLEAN);
            $tmp['header_nontradebp_c'] = filter_var($record->nontradebpcus, FILTER_VALIDATE_BOOLEAN);
            $records[] = $tmp;
        }

        $codesetting = $this->app->mypartnersapsettingcodeStore()->getByField('partnerid', $partnerId);
        if ($codesetting) {
            $bpcodes['tradebp_v'] = $codesetting->tradebpvendor;
            $bpcodes['tradebp_c'] = $codesetting->tradebpcus;
            $bpcodes['nontradebp_v'] = $codesetting->nontradebpvendor;
            $bpcodes['nontradebp_c'] = $codesetting->nontradebpcus;
        }

        return ['sapsettings' =>$records, 'sapbpcodes' => $bpcodes];
    }

    /**
     * Update setting for the partner
     *
     * @param  Partner $partner
     * @param  array $params
     * @return void
     */
    public function updateSettings($partner, $params)
    {
        if (empty($params['sapdgcode']) && empty($params['partnersettingid'])) {
            return;
        }

        // Must be negative
        if (0 < floatval($params['payoutfee'])) {
            throw new \Snap\InputException("Payout Fee must be negative or zero.", InputException::INVALID_FIELD);
        }

        if (0 >= intval($params['maxpcsperdelivery'])) {
            throw new \Snap\InputException("Max pieces per delivery must be more than 0.", InputException::INVALID_FIELD);
        }

        foreach ($partner->getServices() as $service) {
            $maxXau = $params['maxxauperdelivery'];
            $product = $this->app->productStore()->getById($service->productid);
            if ($maxXau && $service->canRedeem() && $maxXau < $product->weight) {
                throw new \Snap\InputException("Max XAU per delivery must be at least {$product->weight}g", InputException::INVALID_FIELD);
            }
        }

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        if (! $settings) {
            $settings = $this->app->mypartnersettingStore()->create([
                'status'    => MyPartnerSetting::STATUS_ACTIVE,
                'partnerid' => $partner->id
            ]);
        }

        $from = new \DateTime($params['dgpeakhourfrom'], $this->app->getUserTimeZone());
		$to = new \DateTime($params['dgpeakhourto'], $this->app->getUserTimeZone());
		$params['dgpeakhourfrom'] = $from;
		$params['dgpeakhourto'] = $to;

        $fieldArray = $settings->toArray();
        unset($fieldArray['id'], $fieldArray['partnerid'], $fieldArray['status']);
        foreach ($fieldArray as $key => $val) {
            $settings->$key = $params[$key];
        }

        return $this->app->mypartnersettingStore()->save($settings);
    }

    public function updateSapFeeSettings($partner, $params)
    {
        if (empty($params['tradebp_v']) && empty($params['tradebp_c']) && empty($params['nontradebp_v']) && empty($params['nontradebp_c']) && empty($params['sapsettingsparams'])) {
            return;
        }

        $this->app->getDBHandle()->beginTransaction();
        try{

            $store = $this->app->mypartnersapsettingStore();
            $tradebpVendor = $params['tradebp_v'];
            $tradebpCus    = $params['tradebp_c'];
            $nontradebpVendor = $params['nontradebp_v'];
            $nontradebpCus = $params['nontradebp_c'];

            $shouldSkip = !strlen($tradebpVendor) && !strlen($tradebpCus) && !strlen($nontradebpVendor) && !strlen($nontradebpCus) && !strlen($params['sapsettingsparams']);
            if ($shouldSkip) {
                return;
            }

            $feeSettings = json_decode($params['sapsettingsparams'], true);
            $existingIds = [];
            $objs = [];

            $feeCodeObj = $this->app->mypartnersapsettingcodeStore()->getByField('partnerid', $partner->id);
            if (!$feeCodeObj) {
                $feeCodeObj = $this->app->mypartnersapsettingcodeStore()->create([
                    'partnerid' => $partner->id,
                    'status'    => MyPartnerSapSettingCode::STATUS_ACTIVE
                ]);
            }
            $feeCodeObj->tradebpvendor = 0 < strlen($tradebpVendor) ? $tradebpVendor : "";
            $feeCodeObj->tradebpcus = 0 < strlen($tradebpCus) ? $tradebpCus : "";
            $feeCodeObj->nontradebpvendor = 0 < strlen($nontradebpVendor) ? $nontradebpVendor : "";
            $feeCodeObj->nontradebpcus = 0 < strlen($nontradebpCus) ? $nontradebpCus : "";
            $feeCodeObj = $this->app->mypartnersapsettingcodeStore()->save($feeCodeObj);

            if (0 < count($feeSettings)) {
                if (!strlen($tradebpVendor) && !strlen($tradebpCus) && !strlen($nontradebpVendor) && !strlen($nontradebpCus)) {
                    throw new \Snap\InputException("At least one BP Code must be entered.", \Snap\InputException::FIELD_ERROR);
                }

                foreach ($feeSettings as $fee) {
                    if ($fee['id']) {
                        $obj = $store->getById($fee['id']);
                        $existingIds[] = $obj->id;
                    } else {
                        $obj = $store->create();
                    }

                    if ($fee['tradebpvendor'] && !strlen($tradebpVendor)) {
                            throw new \Snap\InputException("Trade BP Code (Vendor) must be entered.", \Snap\InputException::FIELD_ERROR);
                    }
                    if ($fee['tradebpcus'] && !strlen($tradebpCus)) {
                            throw new \Snap\InputException("Trade BP Code (Customer) must be entered.", \Snap\InputException::FIELD_ERROR);
                    }
                    if ($fee['nontradebpvendor'] && !strlen($nontradebpVendor)) {
                            throw new \Snap\InputException("Non-Trade BP Code (Vendor) must be entered.", \Snap\InputException::FIELD_ERROR);
                    }
                    if ($fee['nontradebpcus'] && !strlen($nontradebpCus)) {
                            throw new \Snap\InputException("Non-Trade BP Code (Customer) must be entered.", \Snap\InputException::FIELD_ERROR);
                    }


                    $obj->partnerid = $partner->id;
                    $obj->transactiontype  = $fee['transactiontype'];
                    $obj->itemcode  = $fee['itemcode'];
                    $obj->tradebpvendor = (int)$fee['tradebpvendor'];
                    $obj->tradebpcus    = (int)($fee['tradebpcus']);
                    $obj->nontradebpvendor  = (int)$fee['nontradebpvendor'];
                    $obj->nontradebpcus     = (int)$fee['nontradebpcus'];
                    $obj->action = $fee['action'];
                    $obj->gtprefno = $fee['gtprefno'];
                    $obj->status = MyPartnerSapSetting::STATUS_ACTIVE;
                    $objs[] = $obj;
                }
            }

            if (! count($existingIds)) {
                $existingIds = [0];
            }

            if(0 != $existingIds[0]){
                // Disable removed
                $toRemove = $store->searchTable()->select()
                                ->where('id', 'not in', $existingIds)
                                ->andWhere('partnerid', $partner->id)
                                ->andWhere('status', MyPartnerSapSetting::STATUS_ACTIVE)
                                ->execute();
                
                if (0 < count($toRemove)) {
                    foreach ($toRemove as $remove) {
                        $remove->status = MyPartnerSapSetting::STATUS_INACTIVE;
                        $store->save($remove);
                    }
                }
            }

            // Save new objs
            if (0 < count($objs)) {
                foreach ($objs as $obj) {
                    $store->save($obj);
                }
            }

            $this->app->getDBHandle()->commit();
        } catch (\Exception $e) {
            $this->app->getDBHandle()->rollBack();
            throw $e;
        }

    }

}
