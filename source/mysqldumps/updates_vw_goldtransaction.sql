CREATE OR REPLACE VIEW `vw_mygoldtransaction` AS
SELECT gtr.*,

CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `mydisbursement`.dbm_gatewayrefno
    WHEN `order`.ord_type = "CompanySell" THEN `mypaymentdetail`.pdt_gatewayrefno
    ELSE `mypaymentdetail`.pdt_gatewayrefno
END
as gtr_dbmpdtgatewayrefno,

CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `mydisbursement`.dbm_refno
    WHEN `order`.ord_type = "CompanySell" THEN `mypaymentdetail`.pdt_paymentrefno
    ELSE `mypaymentdetail`.pdt_paymentrefno
END
as gtr_dbmpdtreferenceno,


CASE
    WHEN `order`.ord_type = "CompanyBuy" THEN `mydisbursement`.dbm_requestedon
    WHEN `order`.ord_type = "CompanySell" THEN `mypaymentdetail`.pdt_requestedon
    ELSE `mypaymentdetail`.pdt_requestedon
END
as gtr_dbmpdtrequestedon,


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

`mypaymentdetail`.pdt_amount as gtr_pdtamount,
-- `mypaymentdetail`.pdt_paymentrefno as gtr_pdtpaymentrefno,
-- `mypaymentdetail`.pdt_gatewayrefno as gtr_pdtgatewayrefno,
`mypaymentdetail`.pdt_sourcerefno as gtr_pdtsourcerefno,
`mypaymentdetail`.pdt_signeddata as gtr_pdtsigneddata,
`mypaymentdetail`.pdt_location as gtr_pdtlocation,
`mypaymentdetail`.pdt_gatewayfee as gtr_pdtgatewayfee,
`mypaymentdetail`.pdt_customerfee as gtr_pdtcustomerfee,
`mypaymentdetail`.pdt_token as gtr_pdttoken,
`mypaymentdetail`.pdt_status as gtr_pdtstatus,
`mypaymentdetail`.pdt_transactiondate as gtr_pdttransactiondate,
-- `mypaymentdetail`.pdt_requestedon as gtr_pdtrequestedon,
`mypaymentdetail`.pdt_successon as gtr_pdtsuccesson,
`mypaymentdetail`.pdt_failedon as gtr_pdtfailedon,
`mypaymentdetail`.pdt_refundedon as gtr_pdtrefundedon,

`mydisbursement`.dbm_amount as gtr_dbmamount,
`mydisbursement`.dbm_bankid as gtr_dbmbankid,
`mydisbursement`.dbm_accountname as gtr_dbmaccountname,
`mydisbursement`.dbm_accountnumber as gtr_dbmaccountnumber,
`mydisbursement`.dbm_acebankcode as gtr_dbmacebankcode,
`mydisbursement`.dbm_fee as gtr_dbmfee,
-- `mydisbursement`.dbm_refno as gtr_dbmrefno,
`mydisbursement`.dbm_accountholderid as gtr_dbmaccountholderid,
`mydisbursement`.dbm_status as gtr_dbmstatus,
-- `mydisbursement`.dbm_gatewayrefno as gtr_dbmgatewayrefno,
`mydisbursement`.dbm_transactionrefno as gtr_dbmtransactionrefno,
-- `mydisbursement`.dbm_requestedon as gtr_dbmrequestedon,
`mydisbursement`.dbm_disbursedon as gtr_dbmdisbursedon,
`mybank`.`bnk_name` AS `gtr_dbmbankname`

FROM mygoldtransaction gtr
LEFT JOIN `order`
ON gtr.gtr_orderid = `order`.ord_id
LEFT JOIN `mypaymentdetail`
ON gtr.gtr_refno = `mypaymentdetail`.pdt_sourcerefno
LEFT JOIN `mydisbursement`
ON gtr.gtr_refno = `mydisbursement`.dbm_transactionrefno
LEFT JOIN `mybank` on (`mybank`.`bnk_id` = `mydisbursement`.`dbm_bankid`)
LEFT JOIN `partner` on (`partner`.`par_id` = `order`.`ord_partnerid`);