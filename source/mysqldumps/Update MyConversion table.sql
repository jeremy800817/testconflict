-- Update MyConversion table

-- Dumping structure for table gtp.conversion
DROP TABLE IF EXISTS `myconversion`;
CREATE TABLE IF NOT EXISTS `myconversion` (
  `cvn_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cvn_accountholderid` bigint(20) NOT NULL,
  `cvn_refno` varchar(20) NOT NULL,
  `cvn_redemptionid` bigint(20) NOT NULL,
  `cvn_productid` bigint(20) NOT NULL,
  `cvn_commissionfee` decimal(20,6) DEFAULT NULL,
  `cvn_premiumfee` decimal(20,6) DEFAULT NULL,
  `cvn_logisticfeepaymentmode` enum('CONTAINER','FPX', 'GOLD') NOT NULL,
  `cvn_status` smallint(6) NOT NULL,
  `cvn_createdon` datetime NOT NULL,
  `cvn_createdby` bigint(20) NOT NULL,
  `cvn_modifiedon` datetime NOT NULL,
  `cvn_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`cvn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE OR REPLACE VIEW `vw_myconversion` AS
SELECT
c.cvn_id,
c.cvn_accountholderid,
c.cvn_refno,
c.cvn_redemptionid,
c.cvn_productid,
c.cvn_commissionfee,
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

a.ach_fullname AS cvn_accountholdername,
a.ach_accountholdercode AS cvn_accountholdercode
FROM myconversion c
LEFT JOIN redemption r ON c.cvn_redemptionid = r.rdm_id
LEFT JOIN myaccountholder a ON c.cvn_accountholderid =  a.ach_id;