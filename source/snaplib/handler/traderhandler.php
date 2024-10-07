<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
// silverstream : thE2020Covid
Namespace Snap\handler;

Use Snap\App;
Use Snap\IHandler;
Use Snap\InputException;
Use Snap\object\Order;
Use Snap\object\OrderQueue;
Use Snap\object\Buyback;

class traderHandler extends CompositeHandler
{
    private $app = null;

    function __construct(App $app) {
		//parent::__construct('/root/developer', 'tag');

		$this->mapActionToRights('fillform', '');
        $this->mapActionToRights("getOrders", "/root/system/partner");
		$this->app = $app;
		$tagStore = $app->tagfactory();
		$this->addChild(new ext6gridhandler($this, $tagStore, 1));
    }
    
    public function getOrders($app, $params){
        $posPartners = [
            $app->getConfig()->{"gtp.mib.partner.id"}, //1
            $app->getConfig()->{"gtp.pos3.partner.id"}, //2108446
            $app->getConfig()->{"gtp.pos4.partner.id"} //2108463
        ];
        $mibPartner = [
            $app->getConfig()->{"gtp.mib.partner.id"}, //1
        ];
        $gogoldPartner = [
            $app->getConfig()->{"gtp.go.partner.id"}, //2917155
        ];
        $onecentPartner = [
            $app->getConfig()->{"gtp.one.partner.id"}, //2917154
        ];
        $onecallPartner = [
            $app->getConfig()->{"gtp.onecall.partner.id"}, //2917159
        ];
        $mcashPartner = [
            $app->getConfig()->{"gtp.mcash.partner.id"}, //2917158
        ];
        /*$orderPartners = [
            $app->getConfig()->{"gtp.mib.partner.id"}, //1 - mibPartner
            $app->getConfig()->{"gtp.go.partner.id"}, //3513348 - gogoldPartner
            $app->getConfig()->{"gtp.one.partner.id"}, //3968257 - onecentPartner
            $app->getConfig()->{"gtp.onecall.partner.id"}, //3968035 - onecallPartner
            $app->getConfig()->{"gtp.mcash.partner.id"}, //3968258 - mcashPartner
            $app->getConfig()->{"gtp.bmmb.partner.id"}, //3 - EASIGOLD
            $app->getConfig()->{"gtp.toyyib.partner.id"}, //3975195 - TOYYIBGOLD
        ];*/
        //using config.ini setting
        //settings for production config.ini
        /* Config ini setting
            gtp.order.partners = 1,3513348,3968257,3968035,3968258,3,3975195
        */
        $orderPartners = explode(",", $app->getConfig()->{'gtp.order.partners'});
        $kigaPartners = $app->getConfig()->{'gtp.order.kigapartners'};

        $core_exclude_partners = [];
        $not_core_partners = $this->app->partnerStore()->searchTable()->select()->where('corepartner', 0)->execute();
        foreach ($not_core_partners as $arr){
            array_push($core_exclude_partners, $arr->id);
        }
        // $core_exclude_partners = array_merge($posPartners, $mibPartner, $gogoldPartner);
        $paging = [
            'page' => $params['page'],
            'start' => $params['start'],
            'limit' => $params['limit'],
        ];

        //sorting
        if (isset($params['sort'])) {
            $sort = json_decode($params['sort'], true);
            $orderBy = array();
            foreach($sort as $key => $item) {
                $orderBy[$item['property']] = $item['direction'];
            };
        }

        //filtering
        if (isset($params['filter'])) {
            $filter = json_decode($params['filter'], true);
        }

        // $params['grid'];
        // grid=1,grid=2,grid=2...
        // grid1 = today sales total
        //     create date, trx count, amount today, total weight, avg fp, avg gp, order type
        //     4 data only, today buy, today sell, yesterday buy, yesterday sell
        // grid1.1 = daily sales of product, 
        //     create date, trx count, amount today, total weight, avg fp, order type, sap code, product
        //         product = jewel, scrap, 999.9 100g, 999.9 1000g,
        //     (today and yesterday)
        // grid2 = customers queue to sell
        //     gtp_no, time, product, book by, weight, ask price, refine fee, final, value
        // grid3 = customers qeue to buy
        //     gtp_no, time, product, book by, weight, ask price, refine fee, final, value
        // grid4 = booking orders || ace buy order
        //     gtp_no, time, product, book by(weight, price), gp price, refine fee, final, value, customer 
        // grid5 = export orders || ace sell order
        //     gtp_no, time, product, export by, weight, gp price, premium, final, value, buyer

        // $now = new \DateTime("now", $this->app->getUserTimezone()); 
        // $today = $now; 
        // $userDay = new \DateTime($today->format("Y-m-d"));

        $now = new \DateTime();
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $startAt = new \DateTime($now->format('Y-m-d 00:00:00'));
        $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
        $endAt = new \DateTime($now->format('Y-m-d 23:59:59'));
        $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

        $startAtYesterday = new \DateTime($now->format('Y-m-d 00:00:00'));
        $startAtYesterday = \Snap\common::convertUserDatetimeToUTC($startAtYesterday);
        $startAtYesterday->modify("-6 day");
        $endAtYesterday = new \DateTime($now->format('Y-m-d 23:59:59'));
        $endAtYesterday = \Snap\common::convertUserDatetimeToUTC($endAtYesterday);
        $endAtYesterday->modify("-1 day");
        switch ($params['grid']) {
            //Partner Order Total - top 
            case '1':
                // NON core partner total daily sales
                $handle = $this->app->orderStore()->searchTable(false);

                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'ord_type' => 'DESC'
                    );
                }
                
                $query = $handle
                    ->select(["type", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_fpprice')
                    ->addField($handle->raw("(SUM(ord_price * ord_xau) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startAtYesterday->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("type", "IN", [Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUY])
                    ->andWhere("status", "IN", [Order::STATUS_CONFIRMED, Order::STATUS_PENDING])
                    ->andWhere("partnerid", 'IN', $core_exclude_partners)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type']);

                    foreach ($orderBy as $column => $direction) {
                        if (preg_match('/createdon$/', $column)) {
                            $query->orderBy($handle->raw('date('.$column.')'), $direction);
                        } else {
                            $query->orderBy($column, $direction);
                        }
                    }
                $return = $query->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
                foreach ($return as $x => $temp){
                    //$return[$x]['date'] = $endAt->format("Y-m-d");
                    //$return[$x]['ord_type_desc'] = $temp['ord_type'] . ' ('.$endAt->format("Y-m-d").')';
                    $createdon = $this->convertUTCToUserDatetime($temp['ord_createdon']);
                    $return[$x]['ord_createdon'] = $createdon->format('Y-m-d');
                    $ord_type = ('CompanyBuy' == $temp['ord_type']) ? 'Buy' : 'Sell';
                    $return[$x]['ord_type_desc'] = $ord_type . "\n(".$createdon->format('d/m').")";
                }

                //echo'<pre>';print_r($return);

                /*$return2 = $handle
                    ->select(["type", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startAtYesterday->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAtYesterday->format("Y-m-d H:i:s"))
                    ->andWhere("type", "IN", [Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUY])
                    ->andWhere("status", "IN", [Order::STATUS_CONFIRMED, Order::STATUS_PENDING])
                    ->andWhere("partnerid", 'IN', $core_exclude_partners)
                    ->groupBy('type')
                    ->orderBy('type')
                    ->execute();
                foreach ($return2 as $x => $temp){
                    $return2[$x]['date'] = $endAtYesterday->format("Y-m-d");
                    $return2[$x]['ord_type_desc'] = $temp['ord_type'] . ' ('.$endAtYesterday->format("Y-m-d").')';
                }
                $returnx = array_merge($return, $return2);

                $return = $returnx;*/

                break;
            
            //GTP Sales Total - bottom    
            case '1.1':
                // core partner total daily sales, group by product
                $handle = $this->app->orderStore()->searchTable(false);

                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'ord_type' => 'ASC'
                    );
                }

                $query = $handle
                    ->select(["p.pdt_name", "type", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_fpprice')
                    ->addField($handle->raw("(SUM(ord_price * ord_xau) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startAtYesterday->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("type", "IN", [Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUY])
                    ->join('product as p', 'p.pdt_id', '=', 'order.ord_productid')
                    ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type', 'productid']);

                    foreach ($orderBy as $column => $direction) {
                        if (preg_match('/createdon$/', $column)) {
                            $query->orderBy($handle->raw('date('.$column.')'), $direction);
                        } else {
                            $query->orderBy($column, $direction);
                        }
                    }
                $return = $query->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
                foreach ($return as $x => $temp){
                    //$return[$x]['date'] = $endAt->format("Y-m-d");
                    //$return[$x]['ord_type_desc'] = $temp['ord_type'] . ' ('.$endAt->format("Y-m-d").')';
                    $createdon = $this->convertUTCToUserDatetime($temp['ord_createdon']);
                    $return[$x]['ord_createdon'] = $createdon->format('Y-m-d');
                    $ord_type = ('CompanyBuy' == $temp['ord_type']) ? 'Buy' : 'Sell';
                    $return[$x]['ord_type_desc'] = $ord_type."\n(".$temp['pdt_name'].")\n(".$createdon->format('d/m').")";
                }

                /*$return2 = $handle
                    ->select(["p.pdt_name","type"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startAtYesterday->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAtYesterday->format("Y-m-d H:i:s"))
                    ->andWhere("type", "IN", [Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUY])
                    ->join('product as p', 'p.pdt_id', '=', 'order.ord_productid')
                    ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->groupBy(['type','productid'])
                    ->orderBy('type')
                    ->execute();
                foreach ($return2 as $x => $temp){
                    $return2[$x]['date'] = $endAtYesterday->format("Y-m-d");
                    $return2[$x]['ord_type_desc'] = $temp['ord_type'] . ' ('.$endAtYesterday->format("Y-m-d").')';
                }
                $returnx = array_merge($return, $return2);

                $return = $returnx;*/
                
                break;
            
            //GTP Sales Total - top
            case '1.2':
                // core partner total daily sales, group by partner
                $handle = $this->app->orderStore()->searchTable(false);

                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'ord_type' => 'ASC'
                    );
                }

                $query = $handle
                    ->select(["p.pdt_name", "type", "createdon", "partnerid"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_fpprice')
                    ->addField($handle->raw("(SUM(ord_price * ord_xau) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startAtYesterday->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("type", "IN", [Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUY])
                    ->join('product as p', 'p.pdt_id', '=', 'order.ord_productid')
                    ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type']);

                    foreach ($orderBy as $column => $direction) {
                        if (preg_match('/createdon$/', $column)) {
                            $query->orderBy($handle->raw('date('.$column.')'), $direction);
                        } else {
                            $query->orderBy($column, $direction);
                        }
                    }
                $return = $query->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
                foreach ($return as $x => $temp){
                    //$return[$x]['date'] = $endAt->format("Y-m-d");
                    //$return[$x]['ord_type_desc'] = $temp['ord_type'] . ' ('.$endAt->format("Y-m-d").')';
                    $createdon = $this->convertUTCToUserDatetime($temp['ord_createdon']);
                    $return[$x]['ord_createdon'] = $createdon->format('Y-m-d');
                    $ord_type = ('CompanyBuy' == $temp['ord_type']) ? 'Buy' : 'Sell';
                    $return[$x]['ord_type_desc'] = $ord_type."\n(".$temp['pdt_name'].")\n(".$createdon->format('d/m').")";
                    $return[$x]['ord_partnerid'] = $this->app->partnerFactory()->getById($temp['ord_partnerid'])->name;
                }

                break;

            case '2':
                // booking orders
                // company buy
                $query = $this->app->orderStore()->searchView(false)->select()
                    ->where("type", Order::TYPE_COMPANYBUY)
                    ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->limit($paging['start'], $paging['limit'])
                    ->orderby('id', 'desc');
                $return = $query->execute();
                $total = $query->count('id');
                break;
            case '3':
                // que to sell
                $query = $this->app->orderQueueStore()->searchView(false)->select()
                    ->where("ordertype", OrderQueue::OTYPE_COMPANYSELL)
                    ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [OrderQueue::STATUS_ACTIVE])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->limit($paging['start'], $paging['limit'])
                    ->orderby('id', 'desc');
                $return = $query->execute();
                $total = $query->count('id');
                break;
            case '4':
                // que to buy
                $query = $this->app->orderQueueStore()->searchView(false)->select()
                    ->where("ordertype", OrderQueue::OTYPE_COMPANYBUY)
                    ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [OrderQueue::STATUS_ACTIVE])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->limit($paging['start'], $paging['limit'])
                    ->orderby('id', 'desc');
                $return = $query->execute();
                $total = $query->count('id');
                break;
            case '5':
                // export order, pending
                // companysell
                $query = $this->app->orderStore()->searchView(false)->select()
                    ->where("type", Order::TYPE_COMPANYSELL)
                    ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->limit($paging['start'], $paging['limit'])
                    ->orderby('id', 'desc');
                $return = $query->execute();
                $total = $query->count('id');
                break;
            case '6':
                // buyback POS -> ITS `order` buy treat as buyback stucture
                // TOTAL SALES AVG, TODAY AND YESTERDAY
                $query = $this->app->buybackStore()->searchView(false)->select()
                    ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [Buyback::STATUS_PENDING, Buyback::STATUS_CONFIRMED])
                    ->andWhere("partnerid", "IN", $posPartners)
                    ->limit($paging['start'], $paging['limit'])
                    ->orderby('id', 'desc');
                $return = $query->execute();
                $total = $query->count('id');
                break;
            case '7':
                // buyback POS -> ITS `order` buy treat as buyback stucture
                // companyBuy TRANS TODAY ONLY
                $query = $this->app->buybackStore()->searchView(false)->select()
                    ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [Buyback::STATUS_PENDING, Buyback::STATUS_CONFIRMED])
                    ->andWhere("partnerid", "IN", $posPartners)
                    ->limit($paging['start'], $paging['limit'])
                    ->orderby('id', 'desc');
                $return = $query->execute();
                $total = $query->count('id');
                break;
            
            //POS Buyback Total    
            case 'pos01':
                $handle = $this->app->buybackStore()->searchTable(false);
                $startX = $startAt->modify("-14 days");
                $endX = $endAt;

                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'byb_createdon' => 'DESC'
                    );
                }
                
                //pos status
                $status = [Buyback::STATUS_PENDING, Buyback::STATUS_CONFIRMED];
                //miga status
                if ($filter) {
                    foreach ($filter as $key => $aFilter) {
                        if ('partnerid' == $aFilter['property'] && '1' == $aFilter['value'] && '=' == $aFilter['operator']) {
                            $status = [Buyback::STATUS_CONFIRMED, Buyback::STATUS_PROCESSCOLLECT, Buyback::STATUS_COMPLETED];
                        }
                    }
                }

                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('totalweight', 'total_xau')
                    ->addFieldSum('totalamount', 'total_amount')
                    ->addField($handle->raw("(SUM(byb_totalamount) / SUM(byb_totalweight))"), 'avg_fpprice')
                    ->addField($handle->raw("(SUM(byb_price * byb_totalweight) / SUM(byb_totalweight))"), 'avg_gpprice')
                    ->addField('partnerid', 'partnerid')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", $status)
                    ->andWhere("partnerid", "IN", $posPartners)
                    ->groupBy([$handle->raw('date(byb_createdon)')]);

                    foreach ($orderBy as $column => $direction) {
                        if (preg_match('/createdon$/', $column)) {
                            $query->orderBy($handle->raw('date('.$column.')'), $direction);
                        } else {
                            $query->orderBy($column, $direction);
                        }
                    }

                    if ($filter) {
                        foreach($filter as $key => $aFilter) {
                            $query->andWhere($aFilter['property'], $aFilter['operator'], $aFilter['value']);
                        }
                    }

                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(byb_id) OVER ()"), 'total')->one();
                $total = $total['total'];

                foreach ($return as $x => $temp){
                    $createdon = $this->convertUTCToUserDatetime($temp['byb_createdon']);
                    $return[$x]['byb_createdon'] = $createdon->format('Y-m-d');
                    $return[$x]['type'] = 'Buyback';
                    $return[$x]['partnerid'] = (1 == $temp['partnerid']) ? $this->app->partnerFactory()->getById($temp['partnerid'])->name : 'POS';
                }
                
                $return = $return;

                break;
                
            // case 'pos02':
            //     $handle = $this->app->buybackStore()->searchTable(false);
            //     $return = $handle
            //         ->select(["p.pdt_name","type"])
            //         ->addFieldCount('id', 'total_trx')
            //         ->addFieldSum('xau', 'total_xau')
            //         ->addFieldSum('amount', 'total_amount')
            //         ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
            //         ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
            //         ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
            //         ->andWhere("type", "IN", [Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUY])
            //         ->join('product as p', 'p.pdt_id', '=', 'order.ord_productid')
            //         ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
            //         ->groupBy(['type','productid'])
            //         ->execute();
            //     foreach ($return as $x => $temp){
            //         $return[$x]['date'] = $endAt->format("Y-m-d");
            //     }
            //     $return2 = $handle
            //         ->select(["p.pdt_name","type"])
            //         ->addFieldCount('id', 'total_trx')
            //         ->addFieldSum('xau', 'total_xau')
            //         ->addFieldSum('amount', 'total_amount')
            //         ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
            //         ->where("createdon", ">=", $startAtYesterday->format("Y-m-d H:i:s"))
            //         ->where("createdon", "<=",  $endAtYesterday->format("Y-m-d H:i:s"))
            //         ->andWhere("type", "IN", [Order::TYPE_COMPANYSELL, Order::TYPE_COMPANYBUY])
            //         ->join('product as p', 'p.pdt_id', '=', 'order.ord_productid')
            //         ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
            //         ->groupBy(['type','productid'])
            //         ->execute();
            //     foreach ($return2 as $x => $temp){
                //         $return2[$x]['date'] = $endAtYesterday->format("Y-m-d");
            //     }
            //     $returnx = array_merge($return, $return2);

            //     $return = $returnx;
            //     break;    

            case 'mib':
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;
    
                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField('type', 'type')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
                    ->andWhere("partnerid", "IN", $mibPartner)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type'])
                    ->orderby('id', 'desc');
                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $return[$x]['date'] = date('Y-m-d', strtotime($temp['ord_createdon']));
                }
                $return = $return;
                break;

            //MIB Future Order Total - bottom    
            case 'mibfuture':
                $handle = $this->app->orderqueueStore()->searchTable(false);

                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'orq_createdon' => 'DESC'
                    );
                }

                $query = $handle
                    ->select(['pricetarget', 'ordertype', 'createdon', 'amount', 'xau'])
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField($handle->raw("(SUM(orq_amount) / SUM(orq_xau))"), 'avg_gpprice')
                    ->andWhere("status", "IN", [OrderQueue::STATUS_PENDING, OrderQueue::STATUS_ACTIVE, OrderQueue::STATUS_MATCHED])
                    ->andWhere("partnerid", "IN", $mibPartner)
                    ->groupBy(['pricetarget', 'ordertype']);

                    foreach ($orderBy as $column => $direction) {
                        if (preg_match('/createdon$/', $column)) {
                            $query->orderBy($handle->raw('date('.$column.')'), $direction);
                        } else {
                            $query->orderBy($column, $direction);
                        }
                    }
                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(orq_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $createdon = $this->convertUTCToUserDatetime($temp['orq_createdon']);
                    $return[$x]['orq_createdon'] = $createdon->format('Y-m-d');
                }
                $return = $return;

                break;

            //MIB Future Order Total - top
            case 'mibfuturesummary':
                $handle = $this->app->orderqueueStore()->searchTable(false);

                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'orq_createdon' => 'DESC'
                    );
                }

                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('amount', 'total_amount')
                    ->addField('ordertype', 'ordertype')
                    ->addField($handle->raw("(SUM(orq_amount) / SUM(orq_xau))"), 'avg_fpprice')
                    ->addField($handle->raw("(SUM(orq_pricetarget * orq_xau) / SUM(orq_xau))"), 'avg_gpprice')
                    ->andWhere("status", "IN", [OrderQueue::STATUS_PENDING, OrderQueue::STATUS_ACTIVE, OrderQueue::STATUS_MATCHED])
                    ->andWhere("partnerid", "IN", $mibPartner)
                    ->groupBy(['ordertype']);
                    
                    foreach ($orderBy as $column => $direction) {
                        if (preg_match('/createdon$/', $column)) {
                            $query->orderBy($handle->raw('date('.$column.')'), $direction);
                        } else {
                            $query->orderBy($column, $direction);
                        }
                    }
                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(orq_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $createdon = $this->convertUTCToUserDatetime($temp['orq_createdon']);
                    $return[$x]['orq_createdon'] = $createdon->format('Y-m-d H:i:s');
                }
                $return = $return;
                break;

            case 'gogoldsummary':
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;
                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addField($handle->raw("SUM(ord_amount)"), 'total_amount')
                    ->addFieldSum('fee', 'total_fee')
                    ->addField('type', 'type')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->where(function($q) {
                        $q->andwhere(function($x){
                                $x->where('type', Order::TYPE_COMPANYBUY)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                        $q->orwhere(function($p){
                                $p->where('type', Order::TYPE_COMPANYSELL)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                    })
                    ->andWhere("partnerid", "IN", $gogoldPartner)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type'])
                    ->orderby('id', 'desc');


                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $return[$x]['date'] = date('Y-m-d', strtotime($temp['ord_createdon']));
                }
                $return = $return;
                break;

            case 'onecentsummary':
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;
                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addField($handle->raw("SUM(ord_amount)"), 'total_amount')
                    ->addFieldSum('fee', 'total_fee')
                    ->addField('type', 'type')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->where(function($q) {
                        $q->andwhere(function($x){
                                $x->where('type', Order::TYPE_COMPANYBUY)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                        $q->orwhere(function($p){
                                $p->where('type', Order::TYPE_COMPANYSELL)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                    })
                    ->andWhere("partnerid", "IN", $onecentPartner)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type'])
                    ->orderby('id', 'desc');

                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $return[$x]['date'] = date('Y-m-d', strtotime($temp['ord_createdon']));
                }
                $return = $return;
                break;
                
            case 'onecallsummary':
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;
                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addField($handle->raw("SUM(ord_amount)"), 'total_amount')
                    ->addFieldSum('fee', 'total_fee')
                    ->addField('type', 'type')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->where(function($q) {
                        $q->andwhere(function($x){
                                $x->where('type', Order::TYPE_COMPANYBUY)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                        $q->orwhere(function($p){
                                $p->where('type', Order::TYPE_COMPANYSELL)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                    })
                    ->andWhere("partnerid", "IN", $onecallPartner)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type'])
                    ->orderby('id', 'desc');

                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $return[$x]['date'] = date('Y-m-d', strtotime($temp['ord_createdon']));
                }
                $return = $return;
                break;
                
            case 'mcashsummary':
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;
                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addField($handle->raw("SUM(ord_amount)"), 'total_amount')
                    ->addFieldSum('fee', 'total_fee')
                    ->addField('type', 'type')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->where(function($q) {
                        $q->andwhere(function($x){
                                $x->where('type', Order::TYPE_COMPANYBUY)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                        $q->orwhere(function($p){
                                $p->where('type', Order::TYPE_COMPANYSELL)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                    })
                    ->andWhere("partnerid", "IN", $mcashPartner)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type'])
                    ->orderby('id', 'desc');

                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $return[$x]['date'] = date('Y-m-d', strtotime($temp['ord_createdon']));
                }
                $return = $return;
                break;
                
            case 'pkbgoldsummary':
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', 'PKB@UAT')->execute();
			
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }

                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addField($handle->raw("SUM(ord_amount)"), 'total_amount')
                    ->addFieldSum('fee', 'total_fee')
                    ->addField('type', 'type')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->where(function($q) {
                        $q->andwhere(function($x){
                                $x->where('type', Order::TYPE_COMPANYBUY)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                        $q->orwhere(function($p){
                                $p->where('type', Order::TYPE_COMPANYSELL)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                    })
                    ->andWhere("partnerid", "IN", $partnerId)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type'])
                    ->orderby('id', 'desc');


                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $return[$x]['date'] = date('Y-m-d', strtotime($temp['ord_createdon']));
                }
                $return = $return;
                break;
            
            case 'bumiragoldsummary': 
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', 'BUMIRA@UAT')->execute();
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }
                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addField($handle->raw("SUM(ord_amount)"), 'total_amount')
                    ->addFieldSum('fee', 'total_fee')
                    ->addField('type', 'type')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->where(function($q) {
                        $q->andwhere(function($x){
                                $x->where('type', Order::TYPE_COMPANYBUY)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                        $q->orwhere(function($p){
                                $p->where('type', Order::TYPE_COMPANYSELL)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                    })
                    ->andWhere("partnerid", "IN", $partnerId)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type'])
                    ->orderby('id', 'desc');


                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $return[$x]['date'] = date('Y-m-d', strtotime($temp['ord_createdon']));
                }
                $return = $return;

                break;    
                
            //Partner Order Total - bottom    
            case 'traderordersordertotals':
                //Partner Order Total
                $handle = $this->app->orderStore()->searchTable(false);
                $startX = $startAt->modify("-7 days");
                $endX = $endAt;

                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'ord_createdon' => 'DESC',
                        'ord_partnerid' => 'ASC',
                    );
                }

                $query = $handle
                    ->select(["id", "createdon"])
                    ->addFieldCount('id', 'total_trx')
                    ->addFieldSum('xau', 'total_xau')
                    ->addFieldSum('fee', 'total_fee')
                    ->addField('type', 'type')
                    ->addField($handle->raw("CASE WHEN ord_partnerid in(".$kigaPartners.") THEN 'KIGA' ELSE ord_partnerid END AS ord_partnerid2"))
                    ->addField($handle->raw("SUM(ord_amount)"), 'total_amount')
                    ->addField($handle->raw("(SUM(ord_amount) / SUM(ord_xau))"), 'avg_fpprice')
                    ->addField($handle->raw("(SUM(ord_price * ord_xau) / SUM(ord_xau))"), 'avg_gpprice')
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endX->format("Y-m-d H:i:s"))
                    ->where(function($q) {
                        $q->andwhere(function($x){
                                $x->where('type', Order::TYPE_COMPANYBUY)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                        $q->orwhere(function($p){
                                $p->where('type', Order::TYPE_COMPANYSELL)
                                    ->where('status', 'IN', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED]);
                            });
                    })
                    ->andWhere("partnerid", "IN", $orderPartners)
                    ->groupBy([$handle->raw('date(ord_createdon)'), 'type', 'ord_partnerid2']);
 
                    if ($filter) {
                        foreach($filter as $key => $aFilter) {
                            $query->where($aFilter['property'], $aFilter['operator'], $aFilter['value']);
                        }
                    }

                    foreach ($orderBy as $column => $direction) {
                        if (preg_match('/createdon$/', $column)) {
                            $query->orderBy($handle->raw('date('.$column.')'), $direction);
                        } else {
                            $query->orderBy($column, $direction);
                        }
                    }

                $return = $query->limit($paging['start'], $paging['limit'])->execute();
                $total = $query->addField($handle->raw("count(ord_id) OVER ()"), 'total')->one();
                $total = $total['total'];
    
                foreach ($return as $x => $temp){
                    $createdon = $this->convertUTCToUserDatetime($temp['ord_createdon']);
                    $return[$x]['ord_createdon'] = $createdon->format('Y-m-d');
                    if (is_numeric($temp['ord_partnerid2'])) {
                        $partnerId = $this->app->partnerFactory()->getById($temp['ord_partnerid2'])->name;
                    } else {
                        $partnerId = $temp['ord_partnerid2'];
                    }
                    $return[$x]['partnerid'] = $partnerId;
                }
                $return = $return;
                
                break;
            
            //Company Buy & Sell    
            case 'traderordersbuysell':
                // Company Buy & Sell
                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'ord_createdon' => 'DESC'
                    );
                }

                $startX = $startAt->modify("-3 days");
                $handle = $this->app->orderStore()->searchView(false);
                $fields = array(
                    "createdon", "partnername", "type", "productname", "xau", "price",
                    "fee", "fpprice", "amount", "byweight", "orderno"
                );
                $query = $handle
                    ->select($fields)
                    ->where("type", "IN", [Order::TYPE_COMPANYBUY, Order::TYPE_COMPANYSELL])
                    ->where("createdon", ">=", $startX->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->limit($paging['start'], $paging['limit']);

                    foreach ($orderBy as $column => $direction) {
                        $query->orderBy($column, $direction);
                    }
                $return = $query->execute();
                $total = $query->count('id');

                foreach ($return as $x => $temp){
                    $createdon = $this->convertUTCToUserDatetime($temp['ord_createdon']);
                    $return[$x]['ord_createdon'] = $createdon->format('Y-m-d H:i:s');
                    $return[$x]['ord_byweight'] = (1 == $temp['ord_byweight']) ? "Weight" : 'Amount';
                }
                $return = $return;

                break;
            
            //Customer Queue Buy & Sell    
            case 'traderorderscustomerqueuebuysell':
                // Customer Queue Buy & Sell
                //sort
                if (!$orderBy) {
                    $orderBy = array(
                        'orq_createdon' => 'DESC'
                    );
                }

                $handle = $this->app->orderQueueStore()->searchView(false);
                $fields = array(
                    "createdon", "partnername", "ordertype", "productname", "xau", "pricetarget",
                    "amount", "byweight", "orderqueueno", "companysellppg", "companybuyppg"
                );

                $query = $handle
                    ->select($fields)
                    ->where("ordertype", "IN", [OrderQueue::OTYPE_COMPANYSELL, OrderQueue::OTYPE_COMPANYBUY])
                    ->where("createdon", ">=", $startAt->format("Y-m-d H:i:s"))
                    ->where("createdon", "<=",  $endAt->format("Y-m-d H:i:s"))
                    ->andWhere("status", "IN", [OrderQueue::STATUS_ACTIVE])
                    ->andWhere("partnerid", 'NOT IN', $core_exclude_partners)
                    ->limit($paging['start'], $paging['limit']);

                    foreach ($orderBy as $column => $direction) {
                        $query->orderBy($column, $direction);
                    }
                $return = $query->execute();
                $total = $query->count('id');

                foreach ($return as $x => $temp){
                    $createdon = $this->convertUTCToUserDatetime($temp['orq_createdon']);
                    $return[$x]['orq_createdon'] = $createdon->format('Y-m-d H:i:s');
                    $return[$x]['orq_byweight'] = (1 == $temp['orq_byweight']) ? "Weight" : 'Amount';
                    $return[$x]['orq_matchprice'] = (OrderQueue::OTYPE_COMPANYSELL == $temp['orq_ordertype']) ? $temp['orq_companysellppg'] : $temp['orq_companybuyppg'];
                }

                $return = $return;

                break;

            default:
            throw new \Exception("Invalid Grid Data");
        }
        // $total = $total;
        $return = [
            'success' => true,
            'totalRecords' => $total,
            'records' => $return,
        ];
        return json_encode($return);

    }

    private function convertUTCToUserDatetime($dateTime) {
        $dateTimeObj = new \DateTime($dateTime);
        return \Snap\common::convertUTCToUserDatetime($dateTimeObj);
    }

}

// /*!50001 VIEW `today_bo` AS select `bo`.`bookingOrderId` 
// AS `GtpID`,cast(`bo`.`createDate` as time) AS `CreateTime`,
// `pm`.`product_name` AS `GtpProduct`,
// (case when (`bo`.`bookingModeBy` = 0) then 'Amount' else 'Weight' end) AS `BookBy`,
// `bo`.`oldord_weight` AS `Weight`,`bo`.`oldord_price2` AS `GenPrice`,`bo`.`refineryfee` AS `RefineryFee`,(`bo`.`oldord_price2` + `bo`.`refineryfee`) AS `FinalPrice`,`bo`.`oldord_amount` AS `TrxValue`,`bo`.`userName` AS `Customer` from (`bookingorder` `bo` join `agm_product` `pm` on((`bo`.`prodtype` = `pm`.`product_id`))) where ((cast(`bo`.`createDate` as date) = cast(now() as date)) and (`bo`.`status` <> 6)) order by `bo`.`bookingOrderId` desc */;
// /*!50001 SET character_set_client      = @saved_cs_client */;


// /*!50001 VIEW `today_ex` AS select `ex`.`exportOrderId` AS `GtpID`,
// cast(`ex`.`createDate` as time) AS `CreateTime`,
// `pm`.`product_name` AS `GtpProduct`,(case when (`ex`.`exportModeBy` = 0)
//  then 'Amount' else 'Weight' end) AS `ExportBy`,`ex`.`oldexp_weight` AS `Weight`,
//  `ex`.`oldexp_price` AS `GenPrice`,`ex`.`oldexp_premiumfee` AS `PremiumFee`,
//  (`ex`.`oldexp_price` + `ex`.`oldexp_premiumfee`) AS `FinalPrice`,
//  `ex`.`oldexp_amount` AS `TrxValue`,`ex`.`userName` AS `Buyer` 
//  from (`exportorder` `ex` join `agm_product` `pm` on((`ex`.`prodtype` = `pm`.`product_id`)))
//   where ((cast(`ex`.`createDate` as date) = 
//  cast(now() as date)) and (`ex`.`status` <> 6)) order by `ex`.`exportOrderId` desc */;
// /*

// /*!50001 VIEW `today_ask2sell` AS select `a2s`.`askOrderId` AS `GtpID`,
// cast(`a2s`.`createDate` as time) AS `CreateTime`,`pm`.`product_name` AS `GtpProduct`,(case when (`a2s`.`bookingModeBy` = 0) 
// then 'Amount' else 'Weight' end) AS `BookBy`,`a2s`.`askOfferWeight` AS `OfferWeight`,
// `a2s`.`askOfferPriceg` AS `OfferPrice`,`pmap`.`refine_fee` AS `RefineryFee`,
// round((`a2s`.`askOfferPriceg` + `pmap`.`refine_fee`),3) AS `FinalOfferPrice`,
// round(`a2s`.`askOfferTotalAmt`,3) AS `OfferTotalNettValue`,`a2s`.`userName` AS `Customer` 
// from ((`ask2sellorder` `a2s` join `agm_product` `pm` on((`a2s`.`prodtype` = `pm`.`product_id`))) join `agm_gtpproductmap` 
// `pmap` on(((`a2s`.`prodtype` = `pmap`.`product_id`) and (`a2s`.`userId` = `pmap`.`cust_id`)))) where ((cast(`a2s`.`createDate` as date)
//  = cast(now() as date)) and (`a2s`.`askstatus` = 3)) order by `a2s`.`askOrderId` desc */;
// /*!50001 SET character_set_client      = @saved_cs_client */;
// /*!50001 SET character_set_results     = @saved_cs_results */;
// /*!50001 SET collation_connection      = @saved_col_connection */;

// /*!50001 DROP VIEW IF EXISTS `today_bid2buy`*/;
// /*!50001 SET @saved_cs_client          = @@character_set_client */;
// /*!50001 SET @saved_cs_results         = @@character_set_results */;
// /*!50001 SET @saved_col_connection     = @@collation_connection */;
// /*!50001 SET character_set_client      = utf8 */;
// /*!50001 SET character_set_results     = utf8 */;
// /*!50001 SET collation_connection      = utf8_general_ci */;
// /*!50001 CREATE ALGORITHM=UNDEFINED */
// /*!50013 DEFINER=`root`@`%` SQL SECURITY DEFINER */
// /*!50001 VIEW `today_bid2buy` AS select `b2b`.`bidOrderId` AS `GtpID`,cast(`b2b`.`createDate` as time) AS `CreateTime`,`pm`.`product_name` AS `GtpProduct`,(case when (`b2b`.`exportModeBy` = 0) then 'Amount' else 'Weight' end) AS `BookBy`,`b2b`.`bidOfferWeight` AS `OfferWeight`,`b2b`.`bidOfferPriceg` AS `OfferPrice`,`pmap`.`premium_fee` AS `PremiumFee`,round((`b2b`.`bidOfferPriceg` + `pmap`.`premium_fee`),3) AS `FinalOfferPrice`,round(`b2b`.`bidOfferTotalAmt`,3) AS `OfferTotalNettValue`,`b2b`.`userName` AS `Customer` from ((`bid2buyorder` `b2b` join `agm_product` `pm` on((`b2b`.`prodtype` = `pm`.`product_id`))) join `agm_gtpproductmap` `pmap` on(((`b2b`.`prodtype` = `pmap`.`product_id`) and (`b2b`.`userId` = `pmap`.`cust_id`)))) where ((cast(`b2b`.`createDate` as date) = cast(now() as date)) and (`b2b`.`bidstatus` = 3)) order by `b2b`.`bidOrderId` desc */;
// /*!50001 SET character_set_client      = @saved_cs_client */;
// /*!50001 SET character_set_results     = @saved_cs_results */;
// /*!50001 SET collation_connection      = @saved_col_connection */;
