-- alter view order --
CREATE OR REPLACE VIEW vw_order AS
select `order`.`ord_id` AS `ord_id`,
`order`.`ord_partnerid` AS `ord_partnerid`,
`order`.`ord_buyerid` AS `ord_buyerid`,
`order`.`ord_partnerrefid` AS `ord_partnerrefid`,
`order`.`ord_orderno` AS `ord_orderno`,
`order`.`ord_pricestreamid` AS `ord_pricestreamid`,
`order`.`ord_salespersonid` AS `ord_salespersonid`,
`order`.`ord_apiversion` AS `ord_apiversion`,
`order`.`ord_type` AS `ord_type`,
`order`.`ord_productid` AS `ord_productid`,
`order`.`ord_isspot` AS `ord_isspot`,
`order`.`ord_price` AS `ord_price`,
`order`.`ord_byweight` AS `ord_byweight`,
`order`.`ord_xau` AS `ord_xau`,
`order`.`ord_amount` AS `ord_amount`,
`order`.`ord_fee` AS `ord_fee`,
`order`.`ord_remarks` AS `ord_remarks`,
`order`.`ord_bookingon` AS `ord_bookingon`,
`order`.`ord_bookingprice` AS `ord_bookingprice`,
`order`.`ord_bookingpricestreamid` AS `ord_bookingpricestreamid`,
`order`.`ord_confirmon` AS `ord_confirmon`,
`order`.`ord_confirmby` AS `ord_confirmby`,
`order`.`ord_confirmpricestreamid` AS `ord_confirmpricestreamid`,
`order`.`ord_confirmprice` AS `ord_confirmprice`,
`order`.`ord_confirmreference` AS `ord_confirmreference`,
`order`.`ord_cancelon` AS `ord_cancelon`,
`order`.`ord_cancelby` AS `ord_cancelby`,
`order`.`ord_cancelpricestreamid` AS `ord_cancelpricestreamid`,
`order`.`ord_cancelprice` AS `ord_cancelprice`,
`order`.`ord_notifyurl` AS `ord_notifyurl`,
`order`.`ord_reconciled` AS `ord_reconciled`,
`order`.`ord_reconciledon` AS `ord_reconciledon`,
`order`.`ord_reconciledby` AS `ord_reconciledby`,
`order`.`ord_reconciledsaprefno` AS `ord_reconciledsaprefno`,
`order`.`ord_createdon` AS `ord_createdon`,
`order`.`ord_createdby` AS `ord_createdby`,
`order`.`ord_modifiedon` AS `ord_modifiedon`,
`order`.`ord_modifiedby` AS `ord_modifiedby`,
`order`.`ord_status` AS `ord_status`,
`partner`.`par_name` AS `ord_partnername`,
`partner`.`par_code` AS `ord_partnercode`,
`buyerid`.`usr_name` AS `ord_buyername`,
`salespersonid`.`usr_name` AS `ord_salespersonname`,
`product`.`pdt_name` AS `ord_productname`,
`product`.`pdt_code` AS `ord_productcode`,
`confirmby`.`usr_name` AS `ord_confirmbyname`,
`cancelby`.`usr_name` AS `ord_cancelbyname`,
`reconciledby`.`usr_name` AS `ord_reconciledbyname`,
`createdby`.`usr_name` AS `ord_createdbyname`,
`modifiedby`.`usr_name` AS `ord_modifiedbyname`,
`pricevalidation`.`pva_uuid` AS `ord_uuid`,
(case `order`.`ord_status` when 0 then 'Pending' when 1 then 'Confirmed' when 2 then 'Pending Payment' when 3 then 'Pending Cancel' when 4 then 'Cancelled' when 5 then 'Completed' end) AS `ord_statusname` ,
(CASE WHEN (`partner`.`par_corepartner` = 1) THEN `order`.`ord_price` + (`order`.`ord_fee`) ELSE `order`.`ord_price` END) AS `ord_fpprice`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN "Refinery Fee" WHEN (`order`.`ord_type` = 'CompanySell') THEN "Premium Fee" ELSE "NIL" END) AS `ord_feetypename`

from ((((((((((`order` left join `pricevalidation` on((`pricevalidation`.`pva_orderid` = `order`.`ord_id`))) left join `partner` on((`partner`.`par_id` = `order`.`ord_partnerid`))) left join `user` `buyerid` on((`buyerid`.`usr_id` = `order`.`ord_buyerid`))) 
left join `user` `salespersonid` on((`salespersonid`.`usr_id` = `order`.`ord_salespersonid`))) 
left join `product` on((`product`.`pdt_id` = `order`.`ord_productid`))) 
left join `user` `confirmby` on((`confirmby`.`usr_id` = `order`.`ord_confirmby`))) 
left join `user` `cancelby` on((`cancelby`.`usr_id` = `order`.`ord_cancelby`))) 
left join `user` `reconciledby` on((`reconciledby`.`usr_id` = `order`.`ord_reconciledby`))) 
left join `user` `createdby` on((`createdby`.`usr_id` = `order`.`ord_createdby`))) 
left join `user` `modifiedby` on((`modifiedby`.`usr_id` = `order`.`ord_modifiedby`)))


-- alter view goldtransaction --
CREATE OR REPLACE VIEW vw_mygoldtransaction AS 
select `gtr`.`gtr_id` AS `gtr_id`,
`gtr`.`gtr_originalamount` AS `gtr_originalamount`,
`gtr`.`gtr_settlementmethod` AS `gtr_settlementmethod`,
`gtr`.`gtr_salespersoncode` AS `gtr_salespersoncode`,
`gtr`.`gtr_campaigncode` AS `gtr_campaigncode`,`gtr`.`gtr_refno` AS `gtr_refno`,`gtr`.`gtr_orderid` AS `gtr_orderid`,`gtr`.`gtr_status` AS `gtr_status`,`gtr`.`gtr_completedon` AS `gtr_completedon`,`gtr`.`gtr_reversedon` AS `gtr_reversedon`,`gtr`.`gtr_failedon` AS `gtr_failedon`,`gtr`.`gtr_createdon` AS `gtr_createdon`,`gtr`.`gtr_createdby` AS `gtr_createdby`,`gtr`.`gtr_modifiedon` AS `gtr_modifiedon`,
`gtr`.`gtr_modifiedby` AS `gtr_modifiedby`,
(case when (`order`.`ord_type` = 'CompanyBuy') then `mdbm`.`dbm_gatewayrefno` when (`order`.`ord_type` = 'CompanySell') then `mpdt`.`pdt_gatewayrefno` else `mpdt`.`pdt_gatewayrefno` end) AS `gtr_dbmpdtgatewayrefno`,
(case when (`order`.`ord_type` = 'CompanyBuy') then `mdbm`.`dbm_refno` when (`order`.`ord_type` = 'CompanySell') then `mpdt`.`pdt_paymentrefno` else `mpdt`.`pdt_paymentrefno` end) AS `gtr_dbmpdtreferenceno`,
(case when (`order`.`ord_type` = 'CompanyBuy') then `mdbm`.`dbm_requestedon` when (`order`.`ord_type` = 'CompanySell') then `mpdt`.`pdt_requestedon` else `mpdt`.`pdt_requestedon` end) AS `gtr_dbmpdtrequestedon`,
(case when (`order`.`ord_type` = 'CompanyBuy') then `c`.`ach_fullname` when (`order`.`ord_type` = 'CompanySell') then `b`.`ach_fullname` else `b`.`ach_fullname` end) AS `gtr_dbmpdtaccountholdername`,
(case when (`order`.`ord_type` = 'CompanyBuy') then `c`.`ach_accountholdercode` when (`order`.`ord_type` = 'CompanySell') then `b`.`ach_accountholdercode` else `b`.`ach_accountholdercode` end) AS `gtr_dbmpdtaccountholdercode`,
(case when (`order`.`ord_type` = 'CompanyBuy') then `mdbm`.`dbm_verifiedamount` when (`order`.`ord_type` = 'CompanySell') then `mpdt`.`pdt_verifiedamount` else `mpdt`.`pdt_verifiedamount` end) AS `gtr_dbmpdtverifiedamount`,
(CASE WHEN (`partner`.`par_corepartner` = 1) THEN `order`.`ord_price` + (`order`.`ord_fee`) ELSE `order`.`ord_price` END) AS `gtr_ordfpprice`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN "Refinery Fee" WHEN (`order`.`ord_type` = 'CompanySell') THEN "Premium Fee" ELSE "NIL" END) AS `gtr_ordfeetypename`

`order`.`ord_partnerid` AS `gtr_ordpartnerid`,
`order`.`ord_buyerid` AS `gtr_ordbuyerid`,
`order`.`ord_orderno` AS `gtr_ordorderno`,
`order`.`ord_type` AS `gtr_ordtype`,
`order`.`ord_price` AS `gtr_ordprice`,`order`.`ord_xau` AS `gtr_ordxau`,`order`.`ord_amount` AS `gtr_ordamount`,(case when ((`gtr`.`gtr_status` = 1) or (`gtr`.`gtr_status` = 2)) then `order`.`ord_fee` else 0 end) AS `gtr_ordfee`,`order`.`ord_remarks` AS `gtr_ordremarks`,`order`.`ord_isspot` AS `gtr_ordisspot`,`order`.`ord_bookingon` AS `gtr_ordbookingon`,`order`.`ord_confirmon` AS `gtr_ordconfirmon`,`order`.`ord_cancelon` AS `gtr_ordcancelon`,`order`.`ord_status` AS `gtr_ordstatus`,`a`.`ach_fullname` AS `gtr_achfullname`,`a`.`ach_accountholdercode` AS `gtr_achcode`,`a`.`ach_email` AS `gtr_achemail`,`a`.`ach_mykadno` AS `gtr_achmykadno`,`a`.`ach_phoneno` AS `gtr_achphoneno`,`a`.`ach_partnercusid` AS `gtr_achpartnercusid`,`a`.`ach_type` AS `gtr_achtype`,(case when (`a`.`ach_type` = 1) then 'Premium' else 'Basic' end) AS `gtr_achtypename`,`partner`.`par_name` AS `gtr_ordpartnername`,`product`.`pdt_name` AS `gtr_ordproductname`,`mpdt`.`pdt_amount` AS `gtr_pdtamount`,`mpdt`.`pdt_sourcerefno` AS `gtr_pdtsourcerefno`,`mpdt`.`pdt_signeddata` AS `gtr_pdtsigneddata`,`mpdt`.`pdt_location` AS `gtr_pdtlocation`,`mpdt`.`pdt_gatewayfee` AS `gtr_pdtgatewayfee`,`mpdt`.`pdt_customerfee` AS `gtr_pdtcustomerfee`,`mpdt`.`pdt_token` AS `gtr_pdttoken`,`mpdt`.`pdt_status` AS `gtr_pdtstatus`,`mpdt`.`pdt_transactiondate` AS `gtr_pdttransactiondate`,`mpdt`.`pdt_successon` AS `gtr_pdtsuccesson`,`mpdt`.`pdt_failedon` AS `gtr_pdtfailedon`,`mpdt`.`pdt_refundedon` AS `gtr_pdtrefundedon`,`mdbm`.`dbm_amount` AS `gtr_dbmamount`,`mdbm`.`dbm_bankid` AS `gtr_dbmbankid`,`mybank`.`bnk_swiftcode` AS `gtr_dbmbankswiftcode`,`mdbm`.`dbm_bankrefno` AS `gtr_dbmbankrefno`,`mdbm`.`dbm_accountname` AS `gtr_dbmaccountname`,`mdbm`.`dbm_accountnumber` AS `gtr_dbmaccountnumber`,`mdbm`.`dbm_acebankcode` AS `gtr_dbmacebankcode`,`mdbm`.`dbm_fee` AS `gtr_dbmfee`,`mdbm`.`dbm_status` AS `gtr_dbmstatus`,`mdbm`.`dbm_transactionrefno` AS `gtr_dbmtransactionrefno`,`mdbm`.`dbm_disbursedon` AS `gtr_dbmdisbursedon`,`mybank`.`bnk_name` AS `gtr_dbmbankname` from (((((((((`mygoldtransaction` `gtr` left join `order` on((`gtr`.`gtr_orderid` = `order`.`ord_id`))) left join `product` on((`order`.`ord_productid` = `product`.`pdt_id`))) left join `myaccountholder` `a` on((`a`.`ach_id` = `order`.`ord_buyerid`))) left join `mypaymentdetail` `mpdt` on((`gtr`.`gtr_refno` = `mpdt`.`pdt_sourcerefno`))) left join `myaccountholder` `b` on((`b`.`ach_id` = `mpdt`.`pdt_accountholderid`))) left join `mydisbursement` `mdbm` on((`gtr`.`gtr_refno` = `mdbm`.`dbm_transactionrefno`))) left join `myaccountholder` `c` on((`c`.`ach_id` = `mdbm`.`dbm_accountholderid`))) left join `mybank` on((`mybank`.`bnk_id` = `mdbm`.`dbm_bankid`))) 
left join `partner` on((`partner`.`par_id` = `order`.`ord_partnerid`)))