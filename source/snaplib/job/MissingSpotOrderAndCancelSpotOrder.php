<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

USe Snap\App;
Use Snap\ICliJob;
// Use Snap\TLogging;
Use Snap\object\OrderQueue;
Use Snap\object\Order;
Use Snap\object\MbbApFund;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class MissingSpotOrderAndCancelSpotOrder extends basejob {

    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/MissingSpotOrderAndCancelSpotOrder.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=missingorder"
    public function doJob($app, $params = array()) {
        
        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};
        // ensure config has value;
        if (empty($mibPartnerId)){
            $message = "Unable to process MbbAPFundRecalculationJob, could not get mib/etc partnerId";
            $this->log($message, SNAP_LOG_ERROR);
            echo $message;
            exit;
        }
        
        if ($params['action'] == 'missingorder'){
            if ($params['preview'] != '0'){
                $preview = true;
            }
            if ($params['uat'] != '0'){
                $uat = true;
            }
            $this->missingorder($app, $preview, $uat);
        }
        if ($params['action'] == 'ordercancel'){
            $this->spotordercancel($app);
        }
        
        
    }

    function describeOptions() {

    }

    private function missingorder($app, $preview = true, $uat = true){

        $mibPartnerId = $app->getConfig()->{'gtp.mib.partner.id'};

        $missingOrders = [
            [
                "pvmt" => 'PVMD0000000A1E', // pvmt time
                "partnerrefid" => 'MG9042243411',
                "xau" => '1.000', //weight
                "apiversion" => '1.0m',
                "reference" => 'MG9042243411',
                "fee" => 0,
                "order_type" =>'weight', // weight
                "sattlement" => 241.20,
                "bookingpricestreamid" => 2038416
            ],
            [
                "pvmt" => 'PVMD0000000A21', // pvmt time
                "partnerrefid" => 'MG9042275393',
                "xau" => '0.500', //weight
                "apiversion" => '1.0m',
                "reference" => 'MG9042275393',
                "fee" => 0,
                "order_type" =>'weight', // weight
                "sattlement" => 120.66,
                "bookingpricestreamid" => 2038930
            ],
            [
                "pvmt" => 'PVMD0000000A50', // pvmt time
                "partnerrefid" => 'MG9042539814',
                "xau" => '0.500', //weight
                "apiversion" => '1.0m',
                "reference" => 'MG9042539814',
                "fee" => 0,
                "order_type" =>'weight', // weight
                "sattlement" => 120.16,
                "bookingpricestreamid" => 2043504
            ],
            [
                "pvmt" => 'PVMD0000000A6D', // pvmt time
                "partnerrefid" => 'MG9042769537',
                "xau" => '0.041', //weight
                "apiversion" => '1.0m',
                "reference" => 'MG9042769537',
                "fee" => 0,
                "order_type" =>'weight', // weight
                "sattlement" => 9.86,
                "bookingpricestreamid" => 2046797
            ],
            [
                "pvmt" => 'PVMD0000000A79', // pvmt time
                "partnerrefid" => 'MG9042862134',
                "xau" => '0.041', //weight
                "apiversion" => '1.0m',
                "reference" => 'MG9042862134',
                "fee" => 0,
                "order_type" =>'weight', // weight
                "sattlement" => 9.86,
                "bookingpricestreamid" => 2048028
            ],
            [
                "pvmt" => 'PVMD0000000AB0', // pvmt time
                "partnerrefid" => 'MG9042979100',
                "xau" => '0.043', //weight
                "apiversion" => '1.0m',
                "reference" => 'MG9042979100',
                "fee" => 0,
                "order_type" =>'weight', // weight
                "sattlement" => 10.29,
                "bookingpricestreamid" => 2049520
            ],
            [
                "pvmt" => 'PVMD000000C652', // pvmt time
                "partnerrefid" => 'MG9051566719',
                "xau" => '0.042', //weight
                "apiversion" => '1.0m',
                "reference" => 'MG9051566719',
                "fee" => 0,
                "order_type" =>'weight', // weight
                "sattlement" => 10.1,
                "bookingpricestreamid" => 2225295
            ],
        ];

        $cacher = $app->getCacher();
        $partner = $app->partnerStore()->getById($mibPartnerId);
        $product = $app->productStore()->getById(3); // uat 1, prod 3

        $app->getDbHandle()->beginTransaction();

        foreach ($missingOrders as $missingOrder){
            
            if ($uat){
                $price = $app->priceValidationStore()->searchTable()->select()->where('uuid', 'PVMT0000000001')->one();
                $bookings = $app->pricestreamStore()->getById(1);
            }else{
                $price = $app->priceValidationStore()->searchTable()->select()->where('uuid', $missingOrder['pvmt'])->one();
                $bookings = $app->pricestreamStore()->getById($missingOrder['bookingpricestreamid']);
            }

            if ($price->requestedtype == 'CompanyBuy'){
                $companyBuy = true;
            }else{
                $companyBuy = false;
            }

            $newOrder = $app->orderStore()->create([
                'partnerid' => $mibPartnerId,
                'buyerid' => null,
                'partnerrefid' => $missingOrder['partnerrefid'],
                'orderno' => $app->SpotOrderManager()->generateOrderNo($cacher, $partner, $product, $companyBuy, $missingOrder['partnerrefid']),
                'pricestreamid' => $price->pricestreamid,
                'salespersonid' => null,
                'apiversion' => "1.0m",
                'type' => $price->requestedtype,
                'productid' => $product->id,
                'isspot' => 1,
                'price' => $price->price,
                'byweight' => 1,
                'xau' => $missingOrder['xau'],
                'amount' => $partner->calculator()->multiply($missingOrder['xau'], $price->price),
                'fee' => 0,
                'remarks' => $missingOrder['reference'],
                'bookingon' => $bookings->createdon,
                'bookingprice' => $bookings->price,
                'bookingpricestreamid' => $bookings->id,
                'reconciled' => 1, 
                'status' => Order::STATUS_COMPLETED
            ]);
            $saveOrder = $app->orderStore()->save($newOrder);

            if ($preview){
                echo "NEW:".$saveOrder->amount." <==> MBB:".$missingOrder['sattlement']."-price-".$price->price."\n";
            }

            if ($saveOrder){
                $price->orderid = $saveOrder->id;
                $app->priceValidationStore()->save($price);
            }

        }

        if ($preview){
            if($app->getDBHandle()->inTransaction()) {
                $app->getDbHandle()->rollback();
            }
        }else{
            $app->getDbHandle()->commit();
        }

    }

}
