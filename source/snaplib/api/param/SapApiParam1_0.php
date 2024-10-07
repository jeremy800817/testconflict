<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\param;

use Snap\InputException;
use Snap\IEntity;
use Snap\object\Partner;
use Snap\object\Order;
use Snap\object\FutureOrder;
use Snap\api\param\ApiParam;
use Snap\api\param\validator\ApiParamValidator;
use Snap\api\param\validator\SapApiParamValidator;
use Snap\api\param\converter\ApiParamConverter;
use Snap\api\param\converter\SapApiParamConverter;
use Snap\api\param\extractor\ApiParamExtractor;
use Snap\api\param\extractor\SapApiParamExtractor;

/**
 * This class specifically provide overrides for GTP api protocol.
 *
 * @author Jeff Lee <Jeff@silverstream.my>
 * @Reference from Devon Koh SapApiParam1_0m
 * @version 1.0
 * @package  snap.api.param
 */


class SapApiParam1_0 extends ApiParam
{
    public function __construct()
    {
        // Partner listing
        $this->registerParameter('partnerlist', 'option', 'required;string|max=8', '', '');
        $this->registerParameter('partnerlist', 'code', 'requiredBy|option=customer|option=vendor', '', '');
        // End partner listing

        // Order listing
        foreach (['purchaseorder', 'salesorder'] as $action ) {
            $this->registerParameter($action, 'isgtpref', 'required;boolean', '', '');
            $this->registerParameter($action, 'key', 'required', '', '');
        }
        // End order listing

        foreach (['postpurchaseorder', 'postsalesorder'] as $action) {
            // $this->registerParameter($action, 'id', '', null, 'constant|0|int');
            $this->registerParameter($action, 'U_GTPNO', '', null, 'fromObject|order|orderno');
            // $this->registerParameter($action, 'series', '', null, 'constant|__null__');
            $this->registerParameter($action, 'docDate', '', '', 'toCurrentTime');
            $this->registerParameter($action, 'docDueDate', '', '', 'toCurrentTime');
            $this->registerParameter($action, 'docType', '', null, 'constant|0|int');
            $this->registerParameter($action, 'cardCode', '', null, 'fromResult|cardcode'); //SAP code
            $this->registerParameter($action, 'cardName', '', null, '');
            $this->registerParameter($action, 'numAtCard', '', null, '');
            //$this->registerParameter($action, 'cardName', '', null, 'fromObject|partnerbranchmap|pbm_name');
            //$this->registerParameter($action, 'numAtCard', '', null, 'fromObject|partnerbranchmap|pbm_id');
            $this->registerParameter($action, 'docCurrency', '', null, 'constant|MYR');
            $this->registerParameter($action, 'docRate', '', null, '');
            $this->registerParameter($action, 'reference1', '', null, '');
            $this->registerParameter($action, 'reference2', '', null, '');
            $this->registerParameter($action, 'comments', '', null, 'fromResult|comments'); // response on SAP_GRN => REMARKS -> can be null on other source(gtpcore)
            $this->registerParameter($action, 'salesPersonCode', '', null, '');
            $this->registerParameter($action, 'shipToCode', '', null, '');
            $this->registerParameter($action, 'discountPercent', '', null, '');
            $this->registerParameter($action, 'project', '', null, '');
            $this->registerParameter($action, 'lines', '', '', 'orderLine');
            // $this->registerParameter($action, 'itemCode', '', null, 'fromObject|product|sapitemcode');
            // $this->registerParameter($action, 'itemDescription', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'quantity', '', null, 'fromObject|order|xau');
            // $this->registerParameter($action, 'discountPercent', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'warehouseCode', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'accountCode', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'projectCode', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'vatGroup', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'freeText', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'lineTotal', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'unitPrice', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'text', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'itemDetails', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'costingCode', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'costingCode2', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'costingCode3', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'costingCode4', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'costingCode5', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'U_PURITY', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'U_GrossWeight', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'U_GOLD_PRICE', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'U_PREMIUM', '', null, 'constant|__null__');
            // $this->registerParameter($action, 'U_REFINING_FEE', '', null, 'constant|__null__');
        }

        //Post Pos sales
        $this->registerParameter('postpospurchaseorder', 'U_GTPNO', '', null, 'fromObject|buyback|buybackno');
        $this->registerParameter('postpospurchaseorder', 'docDate', '', '', 'toCurrentTime');
        $this->registerParameter('postpospurchaseorder', 'docDueDate', '', '', 'toCurrentTime');
        $this->registerParameter('postpospurchaseorder', 'docType', '', null, 'constant|0|int');
        $this->registerParameter('postpospurchaseorder', 'cardCode', '', null, 'fromObject|partner|sapcompanybuycode1'); //SAP code
        $this->registerParameter('postpospurchaseorder', 'cardName', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'numAtCard', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'docCurrency', '', null, 'constant|MYR');
        $this->registerParameter('postpospurchaseorder', 'docRate', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'reference1', '', null, 'fromObject|buyback|partnerrefno');
        $this->registerParameter('postpospurchaseorder', 'reference2', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'comments', '', null, 'fromObject|buyback|remarks');
        $this->registerParameter('postpospurchaseorder', 'salesPersonCode', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'shipToCode', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'discountPercent', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'project', '', null, '');
        $this->registerParameter('postpospurchaseorder', 'lines', '', '', 'buybackLine');

        //GRN Post and request
        $this->registerParameter('grndraft', 'isgtpref', 'required;boolean', '', '');
        $this->registerParameter('grndraft', 'key', 'required', '', '');

        // $this->registerParameter('postgrndraft', 'U_GTPNO', '', '', 'fromObject|order|orderNo');
        $this->registerParameter('postgrndraft', 'U_GTPNO', '', '', 'fromResult|gtpNo');
        $this->registerParameter('postgrndraft', 'docDate', '', '', 'toCurrentTime');
        $this->registerParameter('postgrndraft', 'comments', '', '', 'fromResultNull|comments'); // variants on POS upload excel
        // $this->registerParameter('postgrndraft', 'docDueDate', '', '', 'toCurrentTime');
        // $this->registerParameter('postgrndraft', 'docType', '', null, 'constant|0|int');
        $this->registerParameter('postgrndraft', 'cardCode', '', null, 'fromResult|sapCode'); //SAP code
        // $this->registerParameter('postgrndraft', 'numAtCard', '', null, 'fromObject|partnerbranchmap|pbm_id');
        // $this->registerParameter('postgrndraft', 'comments', '', null, 'fromObject|order|remarks');
        $this->registerParameter('postgrndraft', 'selectedPO', '', '', 'fromResult|selectedPO');
        $this->registerParameter('postgrndraft', 'items', '', '', 'fromResult|items');
        // $this->registerParameter('grn', 'Id', 'numeric', null, '');
        // $this->registerParameter('grn', 'U_GTPNO', 'required;productSapCode', 'toProduct', '');
        // $this->registerParameter('grn', 'series', 'required;string', '', '');
        // $this->registerParameter('grn', 'docDate', '', '', 'toCurrentTime');
        // $this->registerParameter('grn', 'docDueDate', '', null, 'toCurrentTime');
        // $this->registerParameter('grn', 'cardCode', 'required;partnerSapCode', 'toPartner', '');
        // $this->registerParameter('grn', 'numAtCard', '', null, 'constant|__null__');
        // $this->registerParameter('grn', 'comments', 'required;partnerSapCode', 'toPartner', '');
        // $this->registerParameter('grn', 'docEntry', '', null, 'constant|__null__');
        // $this->registerParameter('grn', 'lineNum', 'required;partnerSapCode', 'toPartner', '');
        // $this->registerParameter('grn', 'sequence', '', null, 'constant|__null__');
        // $this->registerParameter('grn', 'itemCode', 'required;partnerSapCode', 'toPartner', '');
        // $this->registerParameter('grn', 'quantity', '', null, 'constant|__null__');
        // $this->registerParameter('grn', 'U_PURITY', '', null, 'constant|__null__');
        // $this->registerParameter('grn', 'U_GrossWeight', '', null, 'constant|__null__');

        //GRN Post and request end

        //Item Listing
        $this->registerParameter('itemlist', 'warehouse', '', '', '');
        $this->registerParameter('itemlist', 'item', '', '', '');
        //Item Listing end


        //Open PO Listing
        $this->registerParameter('openpo', 'verification', 'required;boolean', '', '');
        $this->registerParameter('openpo', 'code', 'required', '', '');
        //Open PO end


        //Rate Card Listing
        $this->registerParameter('ratecard', 'code', '', '', '');
        $this->registerParameter('ratecard', 'item', 'requiredBy|code', '', '');
        //Rate Card end

        // PDF Statement
        $this->registerParameter('statement', 'code', 'required', '', '');
        $this->registerParameter('statement', 'startDate', 'required;datetime', '', '');
        $this->registerParameter('statement', 'endDate', 'required;datetime', '', '');

        $this->registerParameter('poststatement', 'CardCode', '', '', 'fromResult|code');
        $this->registerParameter('poststatement', 'StartDate', '', '', 'fromResult|startDate');
        $this->registerParameter('poststatement', 'EndDate', '', '',   'fromResult|endDate');
        // PDF Statement End
    }
    /**
     * Returns the validator that is to be used for this class
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiPAramValidator
     */
    protected function getValidator($app): ApiParamValidator
    {
        if (!$this->validator) {
            $this->validator = new SapApiParamValidator($app);
        }
        return $this->validator;
    }

    /**
     * Returns the converter that will be used to translate api parameters into objects
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiParamConveter
     */
    protected function getConverter($app): ApiParamConverter
    {
        if (!$this->conveterr) {
            $this->conveter = new \Snap\api\param\converter\SapApiParamConverter($app);
        }
        return $this->conveter;
    }

    /**
     * Returns the extractor that can be used to format a parameter for responding to client
     * 
     * @param  param\validator\App    $app     App object
     * @param  param\converter\string $config  param\validator\Configuration to be used for this validator
     * @return param\extractor\SapApiParamExtractorparam\converter\
     */
    protected function getExtractor($app): ApiParamExtractor
    {
        if (!$this->extractor) {
            $this->extractor = new SapApiParamExtractor($app);
        }
        return $this->extractor;
    }

    public function decodeActionType($params)
    {
        if (isset($this->paramsMap[$params['action']])) {
            return $params['action'];
        }
        return null;
    }
}

 ?>