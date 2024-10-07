-- do not delete or modified once commit, will execute database based on this file updates
-- Database: `gtp`


-- INFO : example info, all alter table/view add in here with new line section
-- DATE : 2021-08-19 18:36
-- Table `table_name`
ALTER TABLE partnerservice ADD pas_redemptionpremiumfee decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptionpremiumfee pas_redemptionpremiumfee decimal(20,6) NULL AFTER pas_premiumfee;
ALTER TABLE partnerservice ADD pas_redemptioncommission decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptioncommission pas_redemptioncommission decimal(20,6) NULL AFTER pas_redemptionpremiumfee;

-- 
-- INFO : example info, all alter table/view add in here with new line section
-- DATE : 2021-08-19 18:37
-- Table `table_name`
ALTER TABLE partnerservice ADD pas_redemptionpremiumfee decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptionpremiumfee pas_redemptionpremiumfee decimal(20,6) NULL AFTER pas_premiumfee;
ALTER TABLE partnerservice ADD pas_redemptioncommission decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptioncommission pas_redemptioncommission decimal(20,6) NULL AFTER pas_redemptionpremiumfee;

-- 
-- INFO : alter table
-- DATE : 2021-08-31 19:07
-- Table `partnerservice`
ALTER TABLE partnerservice ADD pas_redemptionhandlingfee decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptionhandlingfee pas_redemptionhandlingfee decimal(20,6) NULL AFTER pas_redemptioncommission;

-- 
-- INFO : alter table
-- DATE : 2021-08-31 19:30
-- Table `myconversion`
ALTER TABLE `myconversion` ADD COLUMN `cvn_courierfee` DECIMAL(20,6) NULL DEFAULT NULL AFTER `cvn_premiumfee`;
ALTER TABLE `myconversion` ADD COLUMN `cvn_handlingfee` DECIMAL(20,6) NULL DEFAULT NULL AFTER `cvn_courierfee`;

-- 
-- INFO : alter view
-- DATE : 2021-08-31 21:50
-- Table `vw_myconversion`
CREATE OR REPLACE VIEW `vw_myconversion` AS
SELECT
c.cvn_id,
c.cvn_accountholderid,
c.cvn_refno,
c.cvn_redemptionid,
c.cvn_productid,
c.cvn_commissionfee,
c.cvn_courierfee,
c.cvn_handlingfee,
c.cvn_premiumfee,
c.cvn_logisticfeepaymentmode,
c.cvn_status,
c.cvn_createdon,
c.cvn_createdby,
r.rdm_partnerid           AS cvn_rdmpartnerid,
r.rdm_branchid            AS cvn_rdmbranchid,
r.rdm_salespersonid       AS cvn_rdmsalespersonid,
r.rdm_partnerrefno        AS cvn_rdmpartnerrefno,
r.rdm_redemptionno        AS cvn_rdmredemptionno,
r.rdm_apiversion          AS cvn_rdmapiversion,
r.rdm_type                AS cvn_rdmtype,
r.rdm_sapredemptioncode   AS cvn_rdmsapredemptioncode,
r.rdm_redemptionfee        AS cvn_rdmredemptionfee,
r.rdm_insurancefee        AS cvn_rdminsurancefee,
r.rdm_handlingfee         AS cvn_rdmhandlingfee,
r.rdm_specialdeliveryfee  AS cvn_rdmspecialdeliveryfee,
r.rdm_totalweight         AS cvn_rdmtotalweight,
r.rdm_totalquantity       AS cvn_rdmtotalquantity,
r.rdm_items               AS cvn_rdmitems,
r.rdm_bookingon           AS cvn_rdmbookingon,
r.rdm_bookingprice        AS cvn_rdmbookingprice,
r.rdm_bookingpricestreamid AS cvn_rdmbookingpricestreamid,
r.rdm_confirmon           AS cvn_rdmconfirmon,
r.rdm_confirmby           AS cvn_rdmconfirmby,
r.rdm_confirmpricestreamid AS cvn_rdmconfirmpricestreamid,
r.rdm_confirmprice        AS cvn_rdmconfirmprice,
r.rdm_confirmreference    AS cvn_rdmconfirmreference,
r.rdm_deliveryaddress1    AS cvn_rdmdeliveryaddress1,
r.rdm_deliveryaddress2    AS cvn_rdmdeliveryaddress2,
r.rdm_deliveryaddress3    AS cvn_rdmdeliveryaddress3,
CONCAT_WS(', ', CONCAT_WS(', ', r.rdm_deliveryaddress1, r.rdm_deliveryaddress2), CONCAT_WS(', ', r.rdm_deliveryaddress3, r.rdm_deliverycity)) AS cvn_rdmdeliveryaddress, 
r.rdm_deliverycity        AS cvn_rdmdeliverycity,
r.rdm_deliverypostcode    AS cvn_rdmdeliverypostcode,
r.rdm_deliverystate       AS cvn_rdmdeliverystate,
r.rdm_deliverycountry     AS cvn_rdmdeliverycountry,
r.rdm_deliverycontactname1 AS cvn_rdmdeliverycontactname1,
r.rdm_deliverycontactname2 AS cvn_rdmdeliverycontactname2,
r.rdm_deliverycontactno1  AS cvn_rdmdeliverycontactno1,
r.rdm_deliverycontactno2  AS cvn_rdmdeliverycontactno2,
r.rdm_appointmentbranchid AS cvn_rdmappointmentbranchid,
r.rdm_appointmentdatetime AS cvn_rdmappointmentdatetime,
r.rdm_appointmenton       AS cvn_rdmappointmenton,
r.rdm_appointmentby       AS cvn_rdmappointmentby,
r.rdm_reconciled          AS cvn_rdmreconciled,
r.rdm_reconciledon        AS cvn_rdmreconciledon,
r.rdm_reconciledby        AS cvn_rdmreconciledby,
r.rdm_status              AS cvn_rdmstatus,

a.ach_fullname AS cvn_accountholdername,
a.ach_accountholdercode AS cvn_accountholdercode
FROM myconversion c
LEFT JOIN redemption r ON c.cvn_redemptionid = r.rdm_id
LEFT JOIN myaccountholder a ON c.cvn_accountholderid =  a.ach_id;

-- INFO : example info, all alter table/view add in here with new line section
-- DATE : 2021-08-29 11:44
-- Table `mydailystoragefee`
ALTER TABLE `mydailystoragefee`
	ADD COLUMN `dsf_adminfeexau` DECIMAL(20,6) NOT NULL AFTER `dsf_xau`,
	ADD COLUMN `dsf_storagefeexau` DECIMAL(20,6) NOT NULL AFTER `dsf_adminfeexau`,
	ADD COLUMN `dsf_balancexau` DECIMAL(20,6) NOT NULL AFTER `dsf_storagefeexau`;

-- INFO : Alter table to add new column to support uniquenric setting
-- DATE : 2021-08-02 17:04
-- Table `mypartnersetting`
ALTER TABLE `mypartnersetting` ADD COLUMN `psg_uniquenric` tinyint(1) NOT NULL DEFAULT 1 AFTER `psg_enablepushnotification`

-- INFO : Alter table mymonthlystoragefee to swap column name
-- DATE : 2021-09-02 10:15
-- Table `mymonthlystoragefee`
ALTER TABLE `mymonthlystoragefee`
	CHANGE COLUMN `msf_adminfeexau` `msf_storagefeexau` DECIMAL(20,6) NOT NULL AFTER `msf_amount`,
	CHANGE COLUMN `msf_storagefeexau` `msf_adminfeexau` DECIMAL(20,6) NOT NULL AFTER `msf_storagefeexau`;
	
-- INFO : Alter table mydisbursement to add new column
-- DATE : 2021-09-07 18:25
-- Table `mydisbursement`
ALTER TABLE `mydisbursement`
	ADD COLUMN `dbm_token` TEXT NULL DEFAULT NULL AFTER `dbm_transactionrefno`,
	ADD COLUMN `dbm_signeddata` TEXT NULL DEFAULT NULL AFTER `dbm_token`,
	ADD COLUMN `dbm_remarks` TEXT NULL DEFAULT NULL AFTER `dbm_signeddata`;

-- INFO : Refresh view table to select new field in dailystoragefee table
-- DATE : 2021-09-05 14:45
-- Table `vw_mydailystoragefee`
CREATE OR REPLACE VIEW `vw_mydailystoragefee` AS SELECT 
`mydailystoragefee`.*,
SUM(`myledger`.`led_credit`- `myledger`.`led_debit`) AS `dsf_ledcurrentxau`,
`myaccountholder`.`ach_accountholdercode` as `dsf_achaccountholdercode`,
`myaccountholder`.`ach_fullname` as `dsf_achfullname`,
`myaccountholder`.`ach_mykadno` as `dsf_achmykadno`,
`partner`.`par_id` AS `dsf_partnerid`,
`partner`.`par_code` AS `dsf_partnercode`
FROM `mydailystoragefee` 
LEFT JOIN `myaccountholder` ON `myaccountholder`.`ach_id` = `mydailystoragefee`.`dsf_accountholderid`
LEFT JOIN `myledger` ON `myledger`.`led_accountholderid` = `mydailystoragefee`.`dsf_accountholderid` AND `myledger`.`led_transactiondate` <= `mydailystoragefee`.`dsf_calculatedon`
LEFT JOIN `partner` ON `partner`.`par_id` = `myaccountholder`.`ach_partnerid`
GROUP BY `mydailystoragefee`.`dsf_id`,`mydailystoragefee`.`dsf_accountholderid`;

-- INFO : Add new field to table
-- DATE : 2021-09-10 10:44
-- Table `vw_myaccountholder`
ALTER TABLE `myaccountholder`
	ADD COLUMN `ach_lastnotifiedon` DATETIME NULL DEFAULT NULL AFTER `ach_passwordmodified`,
	DROP COLUMN `ach_lastnotifiedon`;

-- INFO : Refresh view table to select new field in vw_myaccountholder table
-- DATE : 2021-09-10 10:44
-- Table `vw_myaccountholder`
CREATE OR REPLACE VIEW `vw_myaccountholder` AS SELECT 
account.*,
occupationcategory.`occ_category` as ach_occupationcategory,
occupationsubcategory.`osc_code` as ach_occupationsubcategory,
`partner`.`par_code` as ach_partnercode,
`partner`.`par_name` as ach_partnername,
bank.`bnk_code` as ach_bankcode,
bank.`bnk_name` as ach_bankname,
address.`add_line1` as ach_addressline1,
address.`add_line2` as ach_addressline2,
address.`add_city` as ach_addresscity,
address.`add_postcode` as ach_addresspostcode,
address.`add_state` as ach_addressstate,
ledger.`led_xaubalance` as ach_xaubalance,
ledger.`led_amountbalance` as ach_amountbalance
from `myaccountholder` as account
left join `partner` on (`partner`.`par_id` = account.`ach_partnerid`)
left join `mybank` as bank on (bank.`bnk_id` = account.`ach_bankid`)
left join `myaddress` as address on (address.`add_accountholderid` = account.`ach_id`)
left join `myoccupationcategory` as occupationcategory on (occupationcategory.`occ_id` = account.`ach_occupationcategoryid`)
left join `myoccupationsubcategory` as occupationsubcategory on (occupationsubcategory.`osc_id` = account.`ach_occupationsubcategoryid`)
left join (
  SELECT
    ledger.led_accountholderid,
    SUM(ledger.led_credit - CASE 
      WHEN cvn.cvn_type = 'CONVERSION_FEE' THEN 0
      ELSE ledger.led_debit 
    END) AS led_xaubalance,
    SUM(CASE 
      WHEN ledger.led_type = 'BUY_FPX' THEN ord.ord_amount+COALESCE(ord.ord_fee,0)
      ELSE 0 
    END - CASE 
      WHEN cvn.cvn_type = 'CONVERSION_FEE' THEN cvn.cvn_amount
      WHEN ledger.led_type = 'SELL' THEN ord.ord_amount+COALESCE(ord.ord_fee,0)
      ELSE 0 
    END) AS led_amountbalance
  FROM `myledger` AS ledger
  LEFT JOIN `mygoldtransaction` AS gtr ON ((ledger.`led_type` = 'BUY_FPX' OR ledger.`led_type` = 'SELL') AND gtr.`gtr_id` = ledger.`led_typeid`)
  LEFT JOIN `order` AS ord ON ((ledger.`led_type` = 'BUY_FPX' OR ledger.`led_type` = 'SELL')  AND gtr.gtr_orderid = ord.ord_id)
  LEFT JOIN (
    SELECT cvn_id, rdm_amount AS cvn_amount, rdm_type AS cvn_type FROM myconversion AS cvn LEFT JOIN (
      SELECT rdm_id, rdm_redemptionfee+rdm_insurancefee+rdm_handlingfee AS rdm_amount, 'CONVERSION_FEE' AS rdm_type FROM redemption
      UNION ALL
      SELECT rdm_id, rdm_totalweight, 'CONVERSION' FROM redemption
    ) AS rdm ON cvn.cvn_redemptionid = rdm.rdm_id
  ) as cvn ON (ledger.`led_type` = 'CONVERSION' AND cvn.`cvn_id` = ledger.`led_typeid`) GROUP BY led_accountholderid
) AS ledger ON ledger.led_accountholderid = account.ach_id;

-- INFO : Add last notified column to table
-- DATE : 2021-09-11 12:30
-- Table `vw_myaccountholder`
ALTER TABLE `myaccountholder`
	ADD COLUMN `ach_lastnotifiedon` datetime DEFAULT null AFTER `ach_passwordmodified`;

-- 
-- INFO : add new vaultitemtrans
-- DATE : 2021-09-15 11:47
-- Table `vaultitemtrans`
CREATE TABLE `vaultitemtrans` (
  `vit_id` int(11) NOT NULL AUTO_INCREMENT,
  `vit_partnerid` int(11) DEFAULT NULL,
  `vit_type` enum('TRANSFER','RETURN','CANCEL') DEFAULT NULL,
  `vit_documentno` varchar(128) DEFAULT NULL,
  `vit_documentdateon` datetime DEFAULT NULL COMMENT 'user input date, can be different from record insert date',
  `vit_fromlocationid` int(11) DEFAULT NULL,
  `vit_tolocationid` int(11) DEFAULT NULL,
  `vit_cancelby` int(11) DEFAULT NULL,
  `vit_cancelon` datetime DEFAULT NULL,
  `vit_status` int(11) DEFAULT NULL,
  `vit_createdon` datetime DEFAULT NULL,
  `vit_createdby` int(11) DEFAULT NULL,
  `vit_modifiedon` datetime DEFAULT NULL,
  `vit_modifiedby` int(11) DEFAULT NULL,
  PRIMARY KEY (`vit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- 
-- INFO : add new vaultitemtransitem
-- DATE : 2021-09-15 11:47
-- Table `vaultitemtransitem`
CREATE TABLE `vaultitemtransitem` (
  `vti_id` int(11) NOT NULL AUTO_INCREMENT,
  `vti_vaultitemtransid` int(11) DEFAULT NULL,
  `vti_vaultitemid` int(11) DEFAULT NULL,
  `vti_status` int(11) DEFAULT NULL,
  `vti_createdon` datetime DEFAULT NULL,
  `vti_createdby` int(11) DEFAULT NULL,
  `vti_modifiedon` datetime DEFAULT NULL,
  `vti_modifiedby` int(11) DEFAULT NULL,
  PRIMARY KEY (`vti_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- 
-- INFO : add new VIEW vaultitemtrans
-- DATE : 2021-09-15 11:47
-- Table `vw_vaultitemtrans`
CREATE OR REPLACE VIEW `vw_vaultitemtrans` AS SELECT `vaultitemtrans`.*,
`fromlocation`.`stl_name` AS `vit_fromlocationname`,
`tolocation`.`stl_name` AS `vit_tolocationname`,
`partner`.`par_name` AS `vit_partnername`,
`cancelby`.`usr_name` AS `vit_cancelbyname`,
`createdby`.`usr_name` AS `vit_createdbyname`,
`modifiedby`.`usr_name` AS `vit_modifiedbyname`
FROM `vaultitemtrans`
LEFT JOIN `vaultlocation` AS fromlocation ON (`vaultitemtrans`.`vit_fromlocationid` = `fromlocation`.`stl_id`)
LEFT JOIN `vaultlocation` AS tolocation ON (`vaultitemtrans`.`vit_tolocationid` = `tolocation`.`stl_id`)
LEFT JOIN `partner` AS partner ON (partner.par_id = `vaultitemtrans`.`vit_partnerid`)
LEFT JOIN `user` AS cancelby ON (cancelby.usr_id = `vaultitemtrans`.`vit_cancelby`)
LEFT JOIN `user` AS createdby ON (createdby.usr_id = `vaultitemtrans`.`vit_createdby`)
LEFT JOIN `user` AS modifiedby ON (modifiedby.usr_id = `vaultitemtrans`.`vit_modifiedby`);

-- 
-- INFO : add new VIEW vaultitemtransitem
-- DATE : 2021-09-15 11:47
-- Table `vw_vaultitemtransitem`
CREATE OR REPLACE VIEW `vw_vaultitemtransitem` AS SELECT `vaultitemtransitem`.*,
`vaultitem`.sti_serialno AS vti_serialno
FROM `vaultitemtransitem`
LEFT JOIN vaultitem ON (`vaultitemtransitem`.`vti_vaultitemid` = `vaultitem`.`sti_id`)
LEFT JOIN `user` AS createdby ON (createdby.usr_id = vti_createdby)
LEFT JOIN `user` AS modifiedby ON (modifiedby.usr_id = vti_modifiedby);

-- 
-- INFO : add new sharedgv indicator for common warehouse implementation, at table(vaultitem, partner)
-- DATE : 2021-09-15 11:47
-- Table `(vaultitem, partner)`
ALTER TABLE `vaultitem`
	ADD COLUMN `sti_sharedgv` smallint DEFAULT 0 AFTER `sti_deliveryordernumber`;
ALTER TABLE `partner`
	ADD COLUMN `par_sharedgv` smallint DEFAULT 0 AFTER `par_apikey`;
DELIMITER $$
ALTER ALGORITHM=UNDEFINED DEFINER=`gtp`@`%` SQL SECURITY DEFINER VIEW `vw_vaultitem` AS 
SELECT
  `vaultitem`.`sti_id`                    AS `sti_id`,
  `vaultitem`.`sti_partnerid`             AS `sti_partnerid`,
  `vaultitem`.`sti_vaultlocationid`       AS `sti_vaultlocationid`,
  `vaultitem`.`sti_productid`             AS `sti_productid`,
  `vaultitem`.`sti_weight`                AS `sti_weight`,
  `vaultitem`.`sti_brand`                 AS `sti_brand`,
  `vaultitem`.`sti_serialno`              AS `sti_serialno`,
  `vaultitem`.`sti_allocated`             AS `sti_allocated`,
  `vaultitem`.`sti_allocatedon`           AS `sti_allocatedon`,
  `vaultitem`.`sti_utilised`              AS `sti_utilised`,
  `vaultitem`.`sti_movetovaultlocationid` AS `sti_movetovaultlocationid`,
  `vaultitem`.`sti_moverequestedon`       AS `sti_moverequestedon`,
  `vaultitem`.`sti_movecompletedon`       AS `sti_movecompletedon`,
  `vaultitem`.`sti_returnedon`            AS `sti_returnedon`,
  `vaultitem`.`sti_newvaultlocationid`    AS `sti_newvaultlocationid`,
  `vaultitem`.`sti_deliveryordernumber`   AS `sti_deliveryordernumber`,
  `vaultitem`.`sti_sharedgv`              AS `sti_sharedgv`,
  `vaultitem`.`sti_createdon`             AS `sti_createdon`,
  `vaultitem`.`sti_createdby`             AS `sti_createdby`,
  `vaultitem`.`sti_modifiedon`            AS `sti_modifiedon`,
  `vaultitem`.`sti_modifiedby`            AS `sti_modifiedby`,
  `vaultitem`.`sti_status`                AS `sti_status`,
  `vaultlocation`.`stl_name`              AS `sti_vaultlocationname`,
  `vaultlocation`.`stl_type`              AS `sti_vaultlocationtype`,
  `vaultlocation`.`stl_defaultlocation`   AS `sti_vaultlocationdefault`,
  `vaultlocation`.`stl_partnerid`         AS `sti_movetolocationpartnerid`,
  `movetovaultlocationid`.`stl_name`      AS `sti_movetovaultlocationname`,
  `newvaultlocationid`.`stl_name`         AS `sti_newvaultlocationname`,
  `partner`.`par_name`                    AS `sti_partnername`,
  `partner`.`par_code`                    AS `sti_partnercode`,
  `product`.`pdt_name`                    AS `sti_productname`,
  `product`.`pdt_code`                    AS `sti_productcode`,
  `createdby`.`usr_name`                  AS `sti_createdbyname`,
  `modifiedby`.`usr_name`                 AS `sti_modifiedbyname`
FROM (((((((`vaultitem`
         LEFT JOIN `vaultlocation`
           ON ((`vaultlocation`.`stl_id` = `vaultitem`.`sti_vaultlocationid`)))
        LEFT JOIN `vaultlocation` `movetovaultlocationid`
          ON ((`movetovaultlocationid`.`stl_id` = `vaultitem`.`sti_movetovaultlocationid`)))
       LEFT JOIN `vaultlocation` `newvaultlocationid`
         ON ((`newvaultlocationid`.`stl_id` = `vaultitem`.`sti_newvaultlocationid`)))
      LEFT JOIN `partner`
        ON ((`partner`.`par_id` = `vaultitem`.`sti_partnerid`)))
     LEFT JOIN `product`
       ON ((`product`.`pdt_id` = `vaultitem`.`sti_productid`)))
    LEFT JOIN `user` `createdby`
      ON ((`createdby`.`usr_id` = `vaultitem`.`sti_createdby`)))
   LEFT JOIN `user` `modifiedby`
     ON ((`modifiedby`.`usr_id` = `vaultitem`.`sti_modifiedby`)))$$
DELIMITER ;

-- INFO : add new core partner flag
-- DATE : 2021-10-28 11:53
-- Table `partner`
ALTER TABLE `partner`
ADD COLUMN `par_corepartner` INT(1) DEFAULT 0 AFTER `par_type`;

-- 
-- INFO : modify VIEW partner
-- DATE : 2021-10-28 11:53
-- Table `vw_partner`
CREATE OR REPLACE VIEW `vw_partner` AS SELECT `partner`.*,
`salespersonid`.`usr_name` AS `par_salespersonname`,
`createdby`.`usr_name` AS `vit_createdbyname`,
`modifiedby`.`usr_name` AS `vit_modifiedbyname`
FROM `partner`
LEFT JOIN `user` AS salespersonid ON (salespersonid.usr_id = `partner`.`par_salespersonid`)
LEFT JOIN `user` AS createdby ON (createdby.usr_id = `partner`.`par_createdby`)
LEFT JOIN `user` AS modifiedby ON (modifiedby.usr_id = `partner`.`par_modifiedby`);

-- 
-- INFO : modify VIEW mygoldtransaction
-- DATE : 2021-10-11 11:15
-- Table `vw_mygoldtransaction`
CREATE OR REPLACE VIEW `vw_mygoldtransaction` AS SELECT 
`gtr`.`gtr_id` AS `gtr_id`,
`gtr`.`gtr_originalamount` AS `gtr_originalamount`,
`gtr`.`gtr_settlementmethod` AS `gtr_settlementmethod`,
`gtr`.`gtr_salespersoncode` AS `gtr_salespersoncode`,
`gtr`.`gtr_campaigncode` AS `gtr_campaigncode`,
`gtr`.`gtr_refno` AS `gtr_refno`,
`gtr`.`gtr_orderid` AS `gtr_orderid`,
`gtr`.`gtr_status` AS `gtr_status`,
`gtr`.`gtr_completedon` AS `gtr_completedon`,
`gtr`.`gtr_reversedon` AS `gtr_reversedon`,
`gtr`.`gtr_failedon` AS `gtr_failedon`,
`gtr`.`gtr_createdon` AS `gtr_createdon`,
`gtr`.`gtr_createdby` AS `gtr_createdby`,
`gtr`.`gtr_modifiedon` AS `gtr_modifiedon`,
`gtr`.`gtr_modifiedby` AS `gtr_modifiedby`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN `vw_mydisbursement`.`dbm_gatewayrefno` WHEN (`order`.`ord_type` = 'CompanySell') THEN `vw_mypaymentdetail`.`pdt_gatewayrefno` ELSE `vw_mypaymentdetail`.`pdt_gatewayrefno` END) AS `gtr_dbmpdtgatewayrefno`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN `vw_mydisbursement`.`dbm_refno` WHEN (`order`.`ord_type` = 'CompanySell') THEN `vw_mypaymentdetail`.`pdt_paymentrefno` ELSE `vw_mypaymentdetail`.`pdt_paymentrefno` END) AS `gtr_dbmpdtreferenceno`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN `vw_mydisbursement`.`dbm_requestedon` WHEN (`order`.`ord_type` = 'CompanySell') THEN `vw_mypaymentdetail`.`pdt_requestedon` ELSE `vw_mypaymentdetail`.`pdt_requestedon` END) AS `gtr_dbmpdtrequestedon`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN `vw_mydisbursement`.`dbm_accountholdername` WHEN (`order`.`ord_type` = 'CompanySell') THEN `vw_mypaymentdetail`.`pdt_accountholdername` ELSE `vw_mypaymentdetail`.`pdt_accountholdername` END) AS `gtr_dbmpdtaccountholdername`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN `vw_mydisbursement`.`dbm_accountholdercode` WHEN (`order`.`ord_type` = 'CompanySell') THEN `vw_mypaymentdetail`.`pdt_accountholdercode` ELSE `vw_mypaymentdetail`.`pdt_accountholdercode` END) AS `gtr_dbmpdtaccountholdercode`,
(CASE WHEN (`order`.`ord_type` = 'CompanyBuy') THEN `vw_mydisbursement`.`dbm_verifiedamount` WHEN (`order`.`ord_type` = 'CompanySell') THEN `vw_mypaymentdetail`.`pdt_verifiedamount` ELSE `vw_mypaymentdetail`.`pdt_verifiedamount` END) AS `gtr_dbmpdtverifiedamount`,
`order`.`ord_partnerid` AS `gtr_ordpartnerid`,
`order`.`ord_buyerid` AS `gtr_ordbuyerid`,
`order`.`ord_orderno` AS `gtr_ordorderno`,
`order`.`ord_type` AS `gtr_ordtype`,
`order`.`ord_price` AS `gtr_ordprice`,
`order`.`ord_xau` AS `gtr_ordxau`,
`order`.`ord_amount` AS `gtr_ordamount`,
(CASE WHEN ((`gtr`.`gtr_status` = 1) OR (`gtr`.`gtr_status` = 2)) THEN `order`.`ord_fee` ELSE 0 END) AS `gtr_ordfee`,
`order`.`ord_remarks` AS `gtr_ordremarks`,
`order`.`ord_isspot` AS `gtr_ordisspot`,
`order`.`ord_bookingon` AS `gtr_ordbookingon`,
`order`.`ord_confirmon` AS `gtr_ordconfirmon`,
`order`.`ord_cancelon` AS `gtr_ordcancelon`,
`order`.`ord_status` AS `gtr_ordstatus`,
`myaccountholder`.`ach_fullname` AS `gtr_achfullname`,
`myaccountholder`.`ach_accountholdercode` AS `gtr_achcode`,
`myaccountholder`.`ach_email` AS `gtr_achemail`,
`myaccountholder`.`ach_mykadno` AS `gtr_achmykadno`,
`myaccountholder`.`ach_phoneno` AS `gtr_achphoneno`,
`myaccountholder`.`ach_partnercusid` AS `gtr_achpartnercusid`,
`myaccountholder`.`ach_type` AS `gtr_achtype`,
(CASE WHEN (`myaccountholder`.`ach_type` = 1) THEN 'Premium' ELSE 'Basic' END) AS `gtr_achtypename`,
`partner`.`par_name` AS `gtr_ordpartnername`,
`product`.`pdt_name` AS `gtr_ordproductname`,
`vw_mypaymentdetail`.`pdt_amount` AS `gtr_pdtamount`,
`vw_mypaymentdetail`.`pdt_sourcerefno` AS `gtr_pdtsourcerefno`,
`vw_mypaymentdetail`.`pdt_signeddata` AS `gtr_pdtsigneddata`,
`vw_mypaymentdetail`.`pdt_location` AS `gtr_pdtlocation`,
`vw_mypaymentdetail`.`pdt_gatewayfee` AS `gtr_pdtgatewayfee`,
`vw_mypaymentdetail`.`pdt_customerfee` AS `gtr_pdtcustomerfee`,
`vw_mypaymentdetail`.`pdt_token` AS `gtr_pdttoken`,
`vw_mypaymentdetail`.`pdt_status` AS `gtr_pdtstatus`,
`vw_mypaymentdetail`.`pdt_transactiondate` AS `gtr_pdttransactiondate`,
`vw_mypaymentdetail`.`pdt_successon` AS `gtr_pdtsuccesson`,
`vw_mypaymentdetail`.`pdt_failedon` AS `gtr_pdtfailedon`,
`vw_mypaymentdetail`.`pdt_refundedon` AS `gtr_pdtrefundedon`,
`vw_mydisbursement`.`dbm_amount` AS `gtr_dbmamount`,
`vw_mydisbursement`.`dbm_bankid` AS `gtr_dbmbankid`,
`vw_mydisbursement`.`dbm_bankrefno` AS `gtr_dbmbankrefno`,
`vw_mydisbursement`.`dbm_accountname` AS `gtr_dbmaccountname`,
`vw_mydisbursement`.`dbm_accountnumber` AS `gtr_dbmaccountnumber`,
`vw_mydisbursement`.`dbm_acebankcode` AS `gtr_dbmacebankcode`,
`vw_mydisbursement`.`dbm_fee` AS `gtr_dbmfee`,
`vw_mydisbursement`.`dbm_status` AS `gtr_dbmstatus`,
`vw_mydisbursement`.`dbm_transactionrefno` AS `gtr_dbmtransactionrefno`,
`vw_mydisbursement`.`dbm_disbursedon` AS `gtr_dbmdisbursedon`
,`mybank`.`bnk_name` AS `gtr_dbmbankname`
FROM (((((((`mygoldtransaction` `gtr`
LEFT JOIN `order` ON((`gtr`.`gtr_orderid` = `order`.`ord_id`)))
LEFT JOIN `product` ON((`order`.`ord_productid` = `product`.`pdt_id`)))
LEFT JOIN `myaccountholder` ON((`myaccountholder`.`ach_id` = `order`.`ord_buyerid`)))
LEFT JOIN `vw_mypaymentdetail` ON((`gtr`.`gtr_refno` = `vw_mypaymentdetail`.`pdt_sourcerefno`)))
LEFT JOIN `vw_mydisbursement` ON((`gtr`.`gtr_refno` = `vw_mydisbursement`.`dbm_transactionrefno`)))
LEFT JOIN `mybank` ON((`mybank`.`bnk_id` = `vw_mydisbursement`.`dbm_bankid`)))
LEFT JOIN `partner` ON((`partner`.`par_id` = `order`.`ord_partnerid`)));


-- 
-- INFO : alter table
-- DATE : 2021-11-12 19:30
-- Table `priceprovider`
-- add in index for sorting
ALTER TABLE `priceprovider` ADD COLUMN `prp_index` INT(3) NULL DEFAULT NULL AFTER `prp_futureorderparams`;

-- INFO : Add new field to table
-- DATE : 2021-11-11 12:30
-- Table `mydisbursement`
ALTER TABLE `mydisbursement`
	ADD COLUMN `dbm_location` TEXT NULL AFTER `dbm_signeddata`,
	ADD COLUMN `dbm_productdesc` TEXT NULL AFTER `dbm_location`;

-- 
-- INFO : modify Table mygoldtransaction
-- DATE : 2021-12-22 13:00
-- Table `mygoldtransaction`
ALTER TABLE `mygoldtransaction`
	CHANGE COLUMN `gtr_settlementmethod` `gtr_settlementmethod` ENUM('FPX','CONTAINER','BANK_ACCOUNT','WALLET','LOAN') NOT NULL AFTER `gtr_originalamount`;

-- 
-- INFO : add new vaultitemtrans column for bmmb confirmation 
-- DATE : 2021-09-28 11:47
-- Table `vw_vaultitemtrans`
alter table vaultitemtrans 
add column vit_transferrequestby BIGINT after vit_cancelon,
add column vit_confirmrequestby BIGINT after vit_transferrequestby,
add column vit_completerequestby BIGINT after vit_confirmrequestby,
add column vit_transferrequeston DATETIME after vit_completerequestby,
add column vit_confirmrequeston DATETIME after vit_transferrequeston,
add column vit_completerequeston DATETIME after vit_confirmrequeston;

-- 
-- INFO : modifiy vaultitemtrans type column enum for bmmb confirmation 
-- DATE : 2021-09-28 11:47
-- Table `vw_vaultitemtrans`
ALTER TABLE vaultitemtrans MODIFY COLUMN vit_type ENUM('TRANSFER','RETURN','CANCEL','TRANSFERCONFIRMATION');


-- 
-- INFO : add new mytransfergold
-- DATE : 2022-04-22 16:34
-- Table `mytransfergold`
CREATE TABLE `mytransfergold` (
  `gtb_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `gtb_partnerid` bigint(20) NOT NULL,
  `gtb_accountholderid` bigint(20) NOT NULL,
  `gtb_type` enum('INVITE','TRANSFER') NOT NULL,
  `gtb_fromaccountholderid` bigint(20) NOT NULL,
  `gtb_toaccountholderid` bigint(20) DEFAULT NULL,
  `gtb_receiveremail` varchar(50) NOT NULL COMMENT 'if new invite user',
  `gtb_receivername` varchar(255) NOT NULL COMMENT 'if new invite user',
  `gtb_contact` varchar(25) NOT NULL COMMENT 'if new invite user',
  `gtb_refno` varchar(25) NOT NULL,
  `gtb_xau` decimal(20,6) NOT NULL,
  `gtb_price` decimal(20,6) NOT NULL COMMENT 'mid price of buy and sell',
  `gtb_amount` decimal(20,6) NOT NULL,
  `gtb_message` varchar(255) DEFAULT NULL,
  `gtb_sendercode` varchar(15) DEFAULT NULL,
  `gtb_receivercode` varchar(15) DEFAULT NULL,
  `gtb_transferon` datetime DEFAULT NULL,
  `gtb_cancelon` datetime DEFAULT NULL,
  `gtb_expireon` datetime NOT NULL COMMENT 'if new invite user',
  `gtb_status` smallint(6) NOT NULL,
  `gtb_createdon` datetime NOT NULL,
  `gtb_createdby` bigint(20) NOT NULL,
  `gtb_modifiedon` datetime NOT NULL,
  `gtb_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`gtb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;