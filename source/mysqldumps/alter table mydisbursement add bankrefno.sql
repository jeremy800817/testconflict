-- Dumping structure for table gtp.disbursement
DROP TABLE IF EXISTS `mydisbursement`;
CREATE TABLE IF NOT EXISTS `mydisbursement` (
  `dbm_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `dbm_amount` decimal(20,6) NOT NULL,
  `dbm_refno` varchar(36) NOT NULL,
  `dbm_verifiedamount` decimal(20,6) DEFAULT NULL,
  `dbm_gatewayrefno` varchar(36) DEFAULT NULL,
  `dbm_transactionrefno` varchar(36) DEFAULT NULL,
  `dbm_bankid` bigint(20) NOT NULL,
  `dbm_bankrefno` varchar(36) DEFAULT NULL,
  `dbm_accountname` varchar(255) NOT NULL,
  `dbm_accountnumber` varchar(255) NOT NULL,
  `dbm_acebankcode` bigint(20) DEFAULT NULL,
  `dbm_fee` decimal(20,6) DEFAULT NULL,
  `dbm_accountholderid` bigint(20) NOT NULL,
  `dbm_status` smallint(6) NOT NULL,
  `dbm_requestedon` datetime DEFAULT NULL,
  `dbm_disbursedon` datetime DEFAULT NULL,
  `dbm_cancelledon` datetime DEFAULT NULL,
  `dbm_createdon` datetime NOT NULL,
  `dbm_createdby` bigint(20) NOT NULL,
  `dbm_modifiedon` datetime NOT NULL,
  `dbm_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`dbm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- Data exporting was unselected.


CREATE OR REPLACE VIEW `vw_mypricealert` AS SELECT
`mypricealert`.*,
`myaccountholder`.`ach_fullname` AS `pal_accountholderfullname`,
`myaccountholder`.`ach_accountholdercode` AS `pal_accountholdercode`,
`myaccountholder`.`ach_mykadno` AS `pal_accountholdermykadno`,
`myaccountholder`.`ach_partnerid` AS `pal_accountholderpartnerid`,
`priceprovider`.`prp_code` As `pal_priceprovidercode`,
`priceprovider`.`prp_name` As `pal_priceprovidername`
FROM `mypricealert`
left join `myaccountholder` ON `mypricealert`.`pal_accountholderid` = `myaccountholder`.`ach_id`
left join `priceprovider` ON `mypricealert`.`pal_priceproviderid` = `priceprovider`.`prp_id`;

CREATE OR REPLACE VIEW `vw_mygoldtransaction` AS
SELECT gtr.*,

CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `vw_mydisbursement`.dbm_gatewayrefno
    WHEN `order`.ord_type = "CompanySell" THEN `vw_mypaymentdetail`.pdt_gatewayrefno
    ELSE `vw_mypaymentdetail`.pdt_gatewayrefno
END
as gtr_dbmpdtgatewayrefno,

CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `vw_mydisbursement`.dbm_refno
    WHEN `order`.ord_type = "CompanySell" THEN `vw_mypaymentdetail`.pdt_paymentrefno
    ELSE `vw_mypaymentdetail`.pdt_paymentrefno
END
as gtr_dbmpdtreferenceno,


CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `vw_mydisbursement`.dbm_requestedon
    WHEN `order`.ord_type = "CompanySell" THEN `vw_mypaymentdetail`.pdt_requestedon
    ELSE `vw_mypaymentdetail`.pdt_requestedon
END
as gtr_dbmpdtrequestedon,


CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `vw_mydisbursement`.dbm_accountholdername
    WHEN `order`.ord_type = "CompanySell" THEN `vw_mypaymentdetail`.pdt_accountholdername
    ELSE `vw_mypaymentdetail`.pdt_accountholdername
END
as gtr_dbmpdtaccountholdername,

CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `vw_mydisbursement`.dbm_accountholdercode
    WHEN `order`.ord_type = "CompanySell" THEN `vw_mypaymentdetail`.pdt_accountholdercode
    ELSE `vw_mypaymentdetail`.pdt_accountholdercode
END
as gtr_dbmpdtaccountholdercode,

CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `vw_mydisbursement`.dbm_verifiedamount
    WHEN `order`.ord_type = "CompanySell" THEN `vw_mypaymentdetail`.pdt_verifiedamount
    ELSE `vw_mypaymentdetail`.pdt_verifiedamount
END
as gtr_dbmpdtverifiedamount,


`order`.ord_partnerid as gtr_ordpartnerid,
`order`.ord_buyerid as gtr_ordbuyerid,
`order`.ord_orderno as gtr_ordorderno,
`order`.ord_type as gtr_ordtype,
`order`.ord_price as gtr_ordprice,
`order`.ord_xau as gtr_ordxau,
`order`.ord_amount as gtr_ordamount,
`order`.ord_fee as gtr_ordfee,
`order`.ord_remarks as gtr_ordremarks,
`order`.ord_bookingon as gtr_ordbookingon,
`order`.ord_confirmon as gtr_ordconfirmon,
`order`.ord_cancelon as gtr_ordcancelon,
`order`.ord_status as gtr_ordstatus,
`partner`.`par_name` AS `gtr_ordpartnername`,

`vw_mypaymentdetail`.pdt_amount as gtr_pdtamount,
-- `mypaymentdetail`.pdt_paymentrefno as gtr_pdtpaymentrefno,
-- `mypaymentdetail`.pdt_gatewayrefno as gtr_pdtgatewayrefno,
`vw_mypaymentdetail`.pdt_sourcerefno as gtr_pdtsourcerefno,
`vw_mypaymentdetail`.pdt_signeddata as gtr_pdtsigneddata,
`vw_mypaymentdetail`.pdt_location as gtr_pdtlocation,
`vw_mypaymentdetail`.pdt_gatewayfee as gtr_pdtgatewayfee,
`vw_mypaymentdetail`.pdt_customerfee as gtr_pdtcustomerfee,
`vw_mypaymentdetail`.pdt_token as gtr_pdttoken,
`vw_mypaymentdetail`.pdt_status as gtr_pdtstatus,
`vw_mypaymentdetail`.pdt_transactiondate as gtr_pdttransactiondate,
-- `mypaymentdetail`.pdt_requestedon as gtr_pdtrequestedon,
`vw_mypaymentdetail`.pdt_successon as gtr_pdtsuccesson,
`vw_mypaymentdetail`.pdt_failedon as gtr_pdtfailedon,
`vw_mypaymentdetail`.pdt_refundedon as gtr_pdtrefundedon,
-- `mypaymentdetail`.pdt_accountholderid as gtr_pdtaccountholderid,

`vw_mydisbursement`.dbm_amount as gtr_dbmamount,
`vw_mydisbursement`.dbm_bankid as gtr_dbmbankid,
`vw_mydisbursement`.dbm_bankrefno as gtr_dbmbankrefno,
`vw_mydisbursement`.dbm_accountname as gtr_dbmaccountname,
`vw_mydisbursement`.dbm_accountnumber as gtr_dbmaccountnumber,
`vw_mydisbursement`.dbm_acebankcode as gtr_dbmacebankcode,
`vw_mydisbursement`.dbm_fee as gtr_dbmfee,
-- `mydisbursement`.dbm_refno as gtr_dbmrefno,
-- `mydisbursement`.dbm_accountholderid as gtr_dbmaccountholderid,
`vw_mydisbursement`.dbm_status as gtr_dbmstatus,
-- `mydisbursement`.dbm_gatewayrefno as gtr_dbmgatewayrefno,
`vw_mydisbursement`.dbm_transactionrefno as gtr_dbmtransactionrefno,
-- `mydisbursement`.dbm_requestedon as gtr_dbmrequestedon,
`vw_mydisbursement`.dbm_disbursedon as gtr_dbmdisbursedon,
`mybank`.`bnk_name` AS `gtr_dbmbankname`

FROM mygoldtransaction gtr
LEFT JOIN `order`
ON gtr.gtr_orderid = `order`.ord_id
LEFT JOIN `vw_mypaymentdetail`
ON gtr.gtr_refno = `vw_mypaymentdetail`.pdt_sourcerefno
LEFT JOIN `vw_mydisbursement`
ON gtr.gtr_refno = `vw_mydisbursement`.dbm_transactionrefno
LEFT JOIN `mybank` on (`mybank`.`bnk_id` = `vw_mydisbursement`.`dbm_bankid`)
LEFT JOIN `partner` on (`partner`.`par_id` = `order`.`ord_partnerid`);