
-- Dumping structure for table gtp.accountclosure
DROP TABLE IF EXISTS `myaccountclosure`;
CREATE TABLE IF NOT EXISTS `myaccountclosure` (
  `acs_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `acs_reasonid` bigint(20) NOT NULL,
  `acs_remarks` varchar(255) DEFAULT NULL,
  `acs_accountholderid` bigint(20) NOT NULL,
  `acs_transactionrefno` varchar(36) DEFAULT NULL,
  `acs_status` smallint(6) NOT NULL,
  `acs_requestedon` datetime NOT NULL,
  `acs_closedon` datetime DEFAULT NULL,
  `acs_createdon` datetime NOT NULL,
  `acs_createdby` bigint(20) NOT NULL,
  `acs_modifiedon` datetime NOT NULL,
  `acs_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`acs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.accountdetaillog
DROP TABLE IF EXISTS `myaccountdetaillog`;
CREATE TABLE IF NOT EXISTS `myaccountdetaillog` (
  `adl_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `adl_activity` enum('EDIT_PROFILE','CHANGE_PASSWORD','CHANGE_PIN','SUBMIT_EKYC') NOT NULL,
  `adl_previous` varchar(255) DEFAULT NULL,
  `adl_accountholderid` bigint(20) NOT NULL,
  `adl_status` smallint(6) NOT NULL,
  `adl_createdon` datetime NOT NULL,
  `adl_createdby` bigint(20) NOT NULL,
  `adl_modifiedon` datetime NOT NULL,
  `adl_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`adl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.accountholder
DROP TABLE IF EXISTS `myaccountholder`;
CREATE TABLE IF NOT EXISTS `myaccountholder` (
  `ach_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ach_partnerid` bigint(20) NOT NULL,
  `ach_preferredlang` enum('EN','MS','ZH') DEFAULT 'EN',
  -- `ach_firstname` varchar(255) NOT NULL,
  -- `ach_middlename` varchar(255) DEFAULT NULL,
  -- `ach_lastname` varchar(255) NOT NULL,
  `ach_fullname` varchar(255) NOT NULL,
  `ach_partnercusid` varchar(255) DEFAULT NULL,
  `ach_partnerdata` text DEFAULT NULL,
  `ach_accountholdercode` varchar(20) NOT NULL,
  `ach_email` varchar(50) NOT NULL,
  `ach_password` varchar(255) NOT NULL,
  `ach_oldpassword` varchar(255) DEFAULT NULL,
  `ach_mykadno` varchar(25) NOT NULL,
  `ach_phoneno` varchar(25) NOT NULL,
  -- `ach_occupation` varchar(255) NOT NULL,
  `ach_occupationcategoryid` bigint(20) NOT NULL,
  `ach_occupationsubcategoryid` bigint(20) DEFAULT NULL,
  `ach_referralbranchcode` varchar(255) DEFAULT NULL,
  `ach_referralsalespersoncode` varchar(255) DEFAULT NULL,
  `ach_pincode` varchar(255) DEFAULT NULL,
  `ach_sapacebuycode` varchar(15) DEFAULT NULL,
  `ach_sapacesellcode` varchar(15) DEFAULT NULL,
  `ach_bankid` bigint(20) DEFAULT NULL,
  `ach_accountname` varchar(255) DEFAULT NULL,
  `ach_accountnumber` varchar(255) DEFAULT NULL,
  `ach_nokfullname` varchar(255) NOT NULL,
  `ach_nokmykadno` varchar(12) DEFAULT NULL,
  `ach_nokphoneno` varchar(25) DEFAULT NULL,
  `ach_nokemail` varchar(50) DEFAULT NULL,
  `ach_nokaddress` varchar(255) DEFAULT NULL,
  `ach_nokrelationship` varchar(50) DEFAULT NULL,
  `ach_investmentmade` tinyint(1) DEFAULT 0,
  `ach_ispep` tinyint(1) DEFAULT 0,
  `ach_pepdeclaration` mediumtext DEFAULT NULL,
  `ach_kycstatus` smallint(6) DEFAULT 0,
  `ach_amlastatus` smallint(6) DEFAULT 0,
  `ach_pepstatus` smallint(6) DEFAULT 0,
  `ach_statusremarks` varchar(255) DEFAULT NULL,
  `ach_emailverifiedon` DATETIME DEFAULT NULL,
  `ach_phoneverifiedon` DATETIME DEFAULT NULL,
  `ach_status` smallint(6) NOT NULL,
  `ach_passwordmodified` datetime DEFAULT NULL,
  `ach_lastnotifiedon` datetime DEFAULT NULL,
  `ach_lastloginon` datetime DEFAULT NULL,
  `ach_lastloginip` varchar(15) DEFAULT NULL,
  `ach_verifiedon` datetime DEFAULT NULL,
  `ach_loantotal` decimal(20,6) DEFAULT NULL,
  `ach_loanbalance` decimal(20,6) DEFAULT NULL,
  `ach_loanapprovedate` datetime DEFAULT NULL,
  `ach_loanapproveby` bigint(20) DEFAULT NULL,
  `ach_loanstatus` int DEFAULT NULL,
  `ach_loanreference` varchar(50) DEFAULT NULL,
  `ach_createdon` datetime NOT NULL,
  `ach_createdby` bigint(20) NOT NULL,
  `ach_modifiedon` datetime NOT NULL,
  `ach_modifiedby` bigint(20) NOT NULL,
  
  PRIMARY KEY (`ach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.address
DROP TABLE IF EXISTS `myaddress`;
CREATE TABLE IF NOT EXISTS `myaddress` (
  `add_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `add_line1` varchar(255) NOT NULL,
  `add_line2` varchar(255) DEFAULT NULL,
  `add_city` varchar(50) NOT NULL,
  `add_postcode` varchar(5) NOT NULL,
  `add_state` varchar(50) NOT NULL,
  `add_accountholderid` bigint(20) NOT NULL,
  `add_status` smallint(6) NOT NULL,
  `add_verifiedon` datetime DEFAULT NULL,
  `add_createdon` datetime NOT NULL,
  `add_createdby` bigint(20) NOT NULL,
  `add_modifiedon` datetime NOT NULL,
  `add_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`add_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table gtp.amlascanlog
DROP TABLE IF EXISTS `myamlascanlog`;
CREATE TABLE IF NOT EXISTS `myamlascanlog` (
  `asl_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `asl_status` smallint(6) NOT NULL,
  `asl_scannedon` datetime NOT NULL,
  `asl_createdon` datetime NOT NULL,
  `asl_modifiedon` datetime NOT NULL,
  `asl_createdby` bigint(20) NOT NULL,
  `asl_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`asl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.announcement
DROP TABLE IF EXISTS `myannouncement`;
CREATE TABLE IF NOT EXISTS `myannouncement` (
  `ann_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ann_code` varchar(255) NOT NULL,
  `ann_type` enum('PUSH','ANNOUNCEMENT') NOT NULL,
  `ann_status` smallint(6) NOT NULL,
  `ann_displaystarton` datetime NOT NULL,
  `ann_displayendon` datetime NOT NULL,
  `ann_approvedon` datetime NOT NULL,
  `ann_approvedby` datetime NOT NULL,
  `ann_disabledon` datetime NOT NULL,
  `ann_disabledby` datetime NOT NULL,
  `ann_createdon` datetime NOT NULL,
  `ann_createdby` bigint(20) NOT NULL,
  `ann_modifiedon` datetime NOT NULL,
  `ann_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`ann_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.announcementtheme
DROP TABLE IF EXISTS `myannouncementtheme`;
CREATE TABLE IF NOT EXISTS `myannouncementtheme` (
  `ant_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ant_name` varchar(255) NOT NULL,
  `ant_template` text NOT NULL,
  `ant_rank` smallint(6) NOT NULL,
  `ant_displaystarton` datetime NOT NULL,
  `ant_displayendon` datetime NOT NULL,
  `ant_validfrom` datetime NOT NULL,
  `ant_validto` datetime NOT NULL,
  `ant_status` smallint(6) NOT NULL,
  `ant_createdon` datetime NOT NULL,
  `ant_createdby` bigint(20) NOT NULL,
  `ant_modifiedon` datetime NOT NULL,
  `ant_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`ant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.bank
DROP TABLE IF EXISTS `mybank`;
CREATE TABLE IF NOT EXISTS `mybank` (
  `bnk_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bnk_code` varchar(32) NOT NULL,
  `bnk_name` varchar(255) NOT NULL,
  `bnk_status` smallint(6) NOT NULL,
  `bnk_createdon` datetime NOT NULL,
  `bnk_createdby` bigint(20) NOT NULL,
  `bnk_modifiedon` datetime NOT NULL,
  `bnk_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`bnk_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table gtp.bank
DROP TABLE IF EXISTS `myclosereason`;
CREATE TABLE IF NOT EXISTS `myclosereason` (
  `crn_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `crn_code` varchar(32) NOT NULL,
  `crn_status` smallint(6) NOT NULL,
  `crn_createdon` datetime NOT NULL,
  `crn_createdby` bigint(20) NOT NULL,
  `crn_modifiedon` datetime NOT NULL,
  `crn_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`crn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

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
  `cvn_courierfee` decimal(20,6) DEFAULT NULL,
  `cvn_logisticfeepaymentmode` enum('CONTAINER','FPX', 'GOLD', 'WALLET') NOT NULL,
  `cvn_campaigncode` varchar(255) DEFAULT '',
  `cvn_status` smallint(6) NOT NULL,
  `cvn_createdon` datetime NOT NULL,
  `cvn_createdby` bigint(20) NOT NULL,
  `cvn_modifiedon` datetime NOT NULL,
  `cvn_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`cvn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.dailystoragefee
DROP TABLE IF EXISTS `mydailystoragefee`;
CREATE TABLE IF NOT EXISTS `mydailystoragefee` (
  `dsf_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `dsf_xau` decimal(20,6) NOT NULL,
  `dsf_accountholderid` bigint(20) NOT NULL,
  `dsf_status` smallint(6) NOT NULL,
  `dsf_calculatedon` datetime NOT NULL,
  `dsf_createdon` datetime NOT NULL,
  `dsf_createdby` bigint(20) NOT NULL,
  `dsf_modifiedon` datetime NOT NULL,
  `dsf_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`dsf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

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

-- Dumping structure for table gtp.documentation
DROP TABLE IF EXISTS `mydocumentation`;
CREATE TABLE IF NOT EXISTS `mydocumentation` (
  `doc_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `doc_name` varchar(255) NOT NULL,
  `doc_code` varchar(255) NOT NULL,
  `doc_status` smallint(6) NOT NULL,
  `doc_partnerid` bigint(20) NOT NULL,
  `doc_createdon` datetime NOT NULL,
  `doc_createdby` bigint(20) NOT NULL,
  `doc_modifiedon` datetime NOT NULL,
  `doc_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`doc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.fee
DROP TABLE IF EXISTS `myfee`;
CREATE TABLE IF NOT EXISTS `myfee` (
  `fee_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fee_type` enum('STORAGE','INSURANCE','DISBURSEMENT', 'HANDLING', 'DELIVERY', 'FPX', 'MIN_STORAGE') NOT NULL,
  `fee_value` decimal(20,6) NOT NULL,
  `fee_calculationtype` enum('FIXED','FLOAT') NOT NULL,
  `fee_mode` enum('XAU','MYR') NOT NULL DEFAULT 'XAU',
  `fee_minamount` decimal(20,6) NOT NULL,
  `fee_maxamount` decimal(20,6) NOT NULL,
  `fee_partnerid` bigint(20) NOT NULL,
  `fee_status` smallint(6) NOT NULL,
  `fee_validfrom` datetime NOT NULL,
  `fee_validto` datetime NOT NULL,
  `fee_createdon` datetime NOT NULL,
  `fee_createdby` bigint(20) NOT NULL,
  `fee_modifiedon` datetime NOT NULL,
  `fee_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`fee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.mygoldtransferlog
DROP TABLE IF EXISTS `mygoldtransferlog`;
CREATE TABLE IF NOT EXISTS `mygoldtransferlog` (
  `gtl_id` bigint(20) NOT NULL AUTO_INCREMENT,  
  `gtl_partnerid`  bigint(20) NOT NULL,
  `gtl_filename` varchar(255) NOT NULL,
  `gtl_filecontent` mediumtext NOT NULL,
  `gtl_remarks` text DEFAULT NULL,
  `gtl_status` smallint(6) NOT NULL,
  `gtl_createdon` datetime NOT NULL,
  `gtl_modifiedon` datetime NOT NULL,
  `gtl_createdby` bigint(20) NOT NULL,
  `gtl_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`gtl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mygoldtransfer`;
CREATE TABLE IF NOT EXISTS `mygoldtransfer` (
  `gtf_id` bigint(20) NOT NULL AUTO_INCREMENT,  
  `gtf_accountholderid` bigint(20) NOT NULL,
  `gtf_price` decimal(20,6) NOT NULL,
  `gtf_amount` decimal(20,6) NOT NULL,
  `gtf_xau` decimal(20,6) NOT NULL,
  `gtf_remarks` text DEFAULT NULL,
  `gtf_status` smallint(6) NOT NULL,
  `gtf_createdon` datetime NOT NULL,
  `gtf_modifiedon` datetime NOT NULL,
  `gtf_createdby` bigint(20) NOT NULL,
  `gtf_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`gtf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Old Viewtable
-- Dumping structure for table gtp.goldtransaction
DROP TABLE IF EXISTS `mygoldtransaction`;
CREATE TABLE IF NOT EXISTS `mygoldtransaction` (
  `gtr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `gtr_originalamount` decimal(20,6) NOT NULL,
  `gtr_settlementmethod` enum('FPX','CONTAINER', 'BANK_ACCOUNT', 'WALLET') NOT NULL,
  `gtr_salespersoncode` varchar(255) DEFAULT '',
  `gtr_campaigncode` varchar(255) DEFAULT '',
  `gtr_refno` varchar(36) NOT NULL,
  `gtr_orderid` bigint(20) NOT NULL,
  `gtr_status` smallint(6) NOT NULL,
  `gtr_completedon` datetime DEFAULT NULL,
  `gtr_reversedon` datetime DEFAULT NULL,
  `gtr_failedon` datetime DEFAULT NULL,
  `gtr_createdon` datetime NOT NULL,
  `gtr_createdby` bigint(20) NOT NULL,
  `gtr_modifiedon` datetime NOT NULL,
  `gtr_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`gtr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table gtp.goldtransaction


-- Data exporting was unselected.

-- Dumping structure for table gtp.kycresult
DROP TABLE IF EXISTS `mykycresult`;
CREATE TABLE IF NOT EXISTS `mykycresult` (
  `kyr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kyr_provider` varchar(255) NOT NULL,
  `kyr_remarks` varchar(255) NOT NULL,
  `kyr_data` text NOT NULL,
  `kyr_result` char(1) NOT NULL,
  `kyr_submissionid` bigint(20) NOT NULL,
  `kyr_status` smallint(6) NOT NULL,
  `kyr_createdon` datetime NOT NULL,
  `kyr_createdby` bigint(20) NOT NULL,
  `kyr_modifiedon` datetime NOT NULL,
  `kyr_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`kyr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.kycsubmission
DROP TABLE IF EXISTS `mykycsubmission`;
CREATE TABLE IF NOT EXISTS `mykycsubmission` (
  `kys_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kys_mykadfrontimageid` bigint(20) DEFAULT NULL,
  `kys_mykadbackimageid` bigint(20) DEFAULT  NULL,
  `kys_faceimageid` bigint(20) DEFAULT NULL,
  `kys_doctype` enum('MYTENTERA', 'MYKAD_OLD', 'MYKAD') DEFAULT NULL,
  `kys_remarks` text DEFAULT NULL,
  `kys_journeyid` varchar(36) DEFAULT '',
  `kys_accountholderid` bigint(20) NOT NULL,
  `kys_status` smallint(6) NOT NULL,
  `kys_lastjourneyidon` datetime DEFAULT NULL,
  `kys_submittedon` datetime DEFAULT NULL,
  `kys_createdon` datetime NOT NULL,
  `kys_createdby` bigint(20) NOT NULL,
  `kys_modifiedon` datetime NOT NULL,
  `kys_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`kys_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.kycsubmission
DROP TABLE IF EXISTS `myimage`;
CREATE TABLE IF NOT EXISTS `myimage` (
  `img_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `img_type` enum('BASE64') NOT NULL,
  `img_image` mediumblob NOT NULL,
  `img_status` smallint(6) NOT NULL,
  `img_createdon` datetime NOT NULL,
  `img_createdby` bigint(20) NOT NULL,
  `img_modifiedon` datetime NOT NULL,
  `img_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`img_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.ledger
DROP TABLE IF EXISTS `myledger`;
CREATE TABLE IF NOT EXISTS `myledger` (
  `led_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `led_type` enum('BUY_FPX','BUY_CONTAINER','SELL','CONVERSION','CONVERSION_FEE','STORAGE_FEE', 'REFUND_DG', 'CREDIT', 'DEBIT', 'VAULT_IN', 'VAULT_OUT', 'ACESELL', 'ACEBUY', 'ACEREDEEM', 'TRANSFER') NOT NULL,
  `led_typeid` BIGINT(20) NOT NULL,
  `led_partnerid` bigint(20) NOT NULL,
  `led_accountholderid` bigint(20) NOT NULL,
  `led_debit` decimal(20,6) NOT NULL,
  `led_credit` decimal(20,6) NOT NULL,
  `led_refno` varchar(36) NOT NULL,
  `led_remarks` varchar(255) DEFAULT '',
  `led_status` smallint(6) NOT NULL,
  `led_transactiondate` datetime NOT NULL,
  `led_createdon` datetime NOT NULL,
  `led_createdby` bigint(20) NOT NULL,
  `led_modifiedon` datetime NOT NULL,
  `led_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`led_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
DROP TABLE IF EXISTS `myloantransaction`;
CREATE TABLE `myloantransaction` (
  `mlh_id` int NOT NULL AUTO_INCREMENT,
  `mlh_achid` bigint DEFAULT NULL,
  `mlh_transactiontype` enum('BUY_FPX','BUY_CONTAINER','SELL','CONVERSION','CONVERSION_FEE','STORAGE_FEE', 'REFUND_DG', 'CREDIT', 'DEBIT', 'VAULT_IN', 'VAULT_OUT', 'ACESELL', 'ACEBUY', 'ACEREDEEM', 'TRANSFER') NOT NULL,
  `mlh_gtrrefno` varchar(45) DEFAULT NULL,
  `mlh_transactionamount` decimal(10,3) DEFAULT NULL,
  `mlh_xau` decimal(10,3) DEFAULT NULL,
  `mlh_createdon` datetime NOT NULL,
  `mlh_createdby` bigint(20) NOT NULL,
  `mlh_modifiedon` datetime NOT NULL,
  `mlh_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`mlh_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

-- Dumping structure for table gtp.localizedcontent
DROP TABLE IF EXISTS `mylocalizedcontent`;
CREATE TABLE IF NOT EXISTS `mylocalizedcontent` (
  `loc_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `loc_sourcetype` enum('ANNOUNCEMENT','PUSH_NOTI','DOCUMENTATION','OCCUPATION_CAT', 'OCCUPATION_SUBCAT', 'CLOSE_REASON') NOT NULL,
  `loc_sourceid` bigint(20) NOT NULL,
  `loc_data` json NOT NULL,
  `loc_language` enum('EN','MS','ZH') NOT NULL,
  `loc_status` smallint(6) NOT NULL,
  `loc_createdon` datetime NOT NULL,
  `loc_createdby` bigint(20) NOT NULL,
  `loc_modifiedon` datetime NOT NULL,
  `loc_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.logisticfeemapping
DROP TABLE IF EXISTS `mylogisticfeemapping`;
CREATE TABLE IF NOT EXISTS `mylogisticfeemapping` (
  `loc_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `loc_postcodefrom` varchar(5) NOT NULL,
  `loc_postcodeto` varchar(5) NOT NULL,
  `loc_vendorid` bigint(20) NOT NULL,
  `loc_amount` decimal(20,6) NOT NULL,
  `loc_status` smallint(6) NOT NULL,
  `loc_validfrom` datetime NOT NULL,
  `loc_validto` datetime NOT NULL,
  `loc_createdon` datetime NOT NULL,
  `loc_createdby` bigint(20) NOT NULL,
  `loc_modifiedon` datetime NOT NULL,
  `loc_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table gtp.monthlystoragefee
DROP TABLE IF EXISTS `mymonthlystoragefee`;
CREATE TABLE IF NOT EXISTS `mymonthlystoragefee` (
  `msf_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `msf_pricestreamid` bigint(20) NOT NULL,
  `msf_xau` decimal(20,6) NOT NULL,
  `msf_price` decimal(20,6) NOT NULL,
  `msf_amount` decimal(20,6) NOT NULL,
  `msf_adminfeexau` decimal(20,6) NOT NULL,
  `msf_storagefeexau` decimal(20,6) NOT NULL,
  `msf_accountholderid` bigint(20) NOT NULL,
  `msf_refno` varchar(36) NOT NULL,
  `msf_status` smallint(6) NOT NULL,
  `msf_chargedon` datetime NOT NULL,
  `msf_createdon` datetime NOT NULL,
  `msf_createdby` bigint(20) NOT NULL,
  `msf_modifiedon` datetime NOT NULL,
  `msf_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`msf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.occupationcategory
DROP TABLE IF EXISTS `myoccupationcategory`;
CREATE TABLE IF NOT EXISTS `myoccupationcategory` (
  `occ_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `occ_category` varchar(255) NOT NULL,
  `occ_politicallyexposed` smallint(6) NOT NULL,
  `occ_status` smallint(6) NOT NULL,
  `occ_createdon` datetime NOT NULL,
  `occ_createdby` bigint(20) NOT NULL,
  `occ_modifiedon` datetime NOT NULL,
  `occ_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`occ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table gtp.occupationsubcategory
DROP TABLE IF EXISTS `myoccupationsubcategory`;
CREATE TABLE IF NOT EXISTS `myoccupationsubcategory` (
  `osc_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `osc_occupationcategoryid` bigint(20) NOT NULL,
  `osc_code` varchar(255) NOT NULL,
  `osc_politicallyexposed` smallint(6) NOT NULL,
  `osc_status` smallint(6) NOT NULL,
  `osc_createdon` datetime NOT NULL,
  `osc_createdby` bigint(20) NOT NULL,
  `osc_modifiedon` datetime NOT NULL,
  `osc_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`osc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.paymentdetail
DROP TABLE IF EXISTS `mypaymentdetail`;
CREATE TABLE IF NOT EXISTS `mypaymentdetail` (
  `pdt_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pdt_amount` decimal(20,6) NOT NULL,
  `pdt_paymentrefno` varchar(36) DEFAULT '',
  `pdt_gatewayrefno` varchar(255) DEFAULT '',
  `pdt_sourcerefno` varchar(36) DEFAULT '',
  `pdt_signeddata` text DEFAULT NULL,
  `pdt_location` text DEFAULT NULL,
  `pdt_gatewayfee` decimal(20,6) DEFAULT 0.00,
  `pdt_customerfee` decimal(20,6) DEFAULT 0.00,
  `pdt_token` text DEFAULT NULL,
  `pdt_productdesc` text DEFAULT NULL,
  `pdt_remarks` text DEFAULT NULL,
  `pdt_gatewaystatus` varchar(36) DEFAULT '',
  `pdt_status` smallint(6) NOT NULL,
  `pdt_transactiondate` datetime DEFAULT NULL,
  `pdt_requestedon` datetime DEFAULT NULL,
  `pdt_successon` datetime DEFAULT NULL,
  `pdt_failedon` datetime DEFAULT NULL,
  `pdt_refundedon` datetime DEFAULT NULL,
  `pdt_accountholderid` bigint(20) NOT NULL,
  `pdt_verifiedamount` decimal(20,6) NOT NULL,
  `pdt_createdon` datetime NOT NULL,
  `pdt_createdby` bigint(20) NOT NULL,
  `pdt_modifiedon` datetime NOT NULL,
  `pdt_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`pdt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.pepperson
DROP TABLE IF EXISTS `mypepperson`;
CREATE TABLE IF NOT EXISTS `mypepperson` (
  `pep_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pep_provider` varchar(255) NOT NULL,
  `pep_personid` bigint(20) NOT NULL,
  `pep_file` mediumtext DEFAULT NULL,
  `pep_type` enum('JSON','PDF') NOT NULL,
  `pep_status` smallint(6) NOT NULL,
  `pep_createdon` datetime NOT NULL,
  `pep_createdby` bigint(20) NOT NULL,
  `pep_modifiedon` datetime NOT NULL,
  `pep_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`pep_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.pepsearchresult
DROP TABLE IF EXISTS `mypepsearchresult`;
CREATE TABLE IF NOT EXISTS `mypepsearchresult` (
  `pes_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pes_provider` varchar(255) NOT NULL,
  `pes_request` text DEFAULT NULL,
  `pes_response` mediumtext DEFAULT NULL,
  `pes_matchescount` smallint(6) NOT NULL DEFAULT 0,
  `pes_accountholderid` bigint(20) NOT NULL,
  `pes_status` smallint(6) NOT NULL,
  `pes_createdon` datetime NOT NULL,
  `pes_createdby` bigint(20) NOT NULL,
  `pes_modifiedon` datetime NOT NULL,
  `pes_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`pes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.pricealert
DROP TABLE IF EXISTS `myhistoricalprice`;
CREATE TABLE IF NOT EXISTS `myhistoricalprice` (
  `hpr_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `hpr_open` decimal(20,6) NOT NULL,
  `hpr_high` decimal(20,6) NOT NULL,
  `hpr_low` decimal(20,6) NOT NULL,
  `hpr_close` decimal(20,6) NOT NULL,
  `hpr_priceproviderid` bigint(20) NOT NULL,
  `hpr_status` smallint(6) NOT NULL,
  `hpr_priceon` datetime NOT NULL,
  `hpr_createdon` datetime NOT NULL,
  `hpr_createdby` bigint(20) NOT NULL,
  `hpr_modifiedon` datetime NOT NULL,
  `hpr_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`hpr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.pricealert
DROP TABLE IF EXISTS `mypricealert`;
CREATE TABLE IF NOT EXISTS `mypricealert` (
  `pal_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pal_type` enum('CompanyBuy','CompanySell') NOT NULL,
  `pal_amount` decimal(20,6) NOT NULL,
  `pal_lastprice` decimal(20,6) DEFAULT NULL,
  `pal_triggered` tinyint(1) DEFAULT 0,
  `pal_remarks` varchar(255) DEFAULT NULL,
  `pal_accountholderid` bigint(20) NOT NULL,
  `pal_priceproviderid` bigint(20) NOT NULL,
  `pal_status` smallint(6) NOT NULL,
  `pal_lasttriggeredon` datetime DEFAULT NULL,
  `pal_senton` datetime DEFAULT NULL,
  `pal_createdon` datetime NOT NULL,
  `pal_createdby` bigint(20) NOT NULL,
  `pal_modifiedon` datetime NOT NULL,
  `pal_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`pal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.pushnotification
DROP TABLE IF EXISTS `mypushnotification`;
CREATE TABLE IF NOT EXISTS `mypushnotification` (
  `pnt_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pnt_eventtype` varchar(255) NOT NULL,
  `pnt_code` varchar(255) NOT NULL,
  `pnt_icon` varchar(64) DEFAULT '',
  `pnt_sound` varchar(64) DEFAULT '',
  `pnt_rank`    bigint(20) DEFAULT 0,
  `pnt_validfrom` datetime NOT NULL,
  `pnt_validto` datetime NOT NULL,
  `pnt_status` smallint(6) NOT NULL,
  `pnt_createdon` datetime NOT NULL,
  `pnt_createdby` bigint(20) NOT NULL,
  `pnt_modifiedon` datetime NOT NULL,
  `pnt_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`pnt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.screeninglist
DROP TABLE IF EXISTS `myscreeninglist`;
CREATE TABLE IF NOT EXISTS `myscreeninglist` (
  `scl_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `scl_sourcetype` enum('UN','BNM','MOHA') NOT NULL,
  `scl_type` enum('INDIVIDUAL','BUSINESS') NOT NULL,
  `scl_name` varchar(255) NOT NULL,
  `scl_alias` text,
  `scl_icno` varchar(255) DEFAULT NULL,
  `scl_dob` datetime DEFAULT NULL,
  `scl_businessregno` varchar(255) DEFAULT NULL,
  `scl_address` text,
  `scl_remarks` varchar(255) DEFAULT NULL,
  `scl_status` smallint(6) NOT NULL,
  `scl_listedon` datetime DEFAULT NULL,
  `scl_importedon` datetime DEFAULT NULL,
  `scl_createdon` datetime NOT NULL,
  `scl_modifiedon` datetime NOT NULL,
  `scl_createdby` bigint(20) NOT NULL,
  `scl_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`scl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.myscreeninglistimportlog
DROP TABLE IF EXISTS `myscreeninglistimportlog`;
CREATE TABLE IF NOT EXISTS `myscreeninglistimportlog` (
  `sci_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sci_sourcetype` enum('UN','BNM','MOHA') NOT NULL,
  `sci_url` text NOT NULL,
  `sci_status` smallint(6) NOT NULL,
  `sci_importedon` datetime NOT NULL,
  `sci_createdon` datetime NOT NULL,
  `sci_modifiedon` datetime NOT NULL,
  `sci_importedby` bigint(20) NOT NULL,
  `sci_createdby` bigint(20) NOT NULL,
  `sci_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`sci_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table gtp.screeningmatchlog
DROP TABLE IF EXISTS `myscreeningmatchlog`;
CREATE TABLE IF NOT EXISTS `myscreeningmatchlog` (
  `scm_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `scm_screeninglistid` bigint(20) NOT NULL,
  `scm_accountholderid` bigint(20) NOT NULL,
  `scm_amlascanlogid` bigint(20) NOT NULL,
  `scm_matcheddata` bigint(20) NOT NULL,
  `scm_remarks` varchar(255) DEFAULT NULL,
  `scm_status` smallint(6) NOT NULL,
  `scm_matchedon` datetime NOT NULL,
  `scm_createdon` datetime NOT NULL,
  `scm_modifiedon` datetime NOT NULL,
  `scm_createdby` bigint(20) NOT NULL,
  `scm_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`scm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.

-- Dumping structure for table gtp.token
DROP TABLE IF EXISTS `mytoken`;
CREATE TABLE IF NOT EXISTS `mytoken` (
  `tok_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tok_type` enum('PUSH','ACCESS', 'REFRESH', 'PASSWORD_RESET', 'VERIFICATION', 'VERIFICATION_PHONE', 'PIN_RESET') NOT NULL,
  `tok_token` varchar(255) NOT NULL,
  `tok_remarks` varchar(255) DEFAULT NULL,
  `tok_accountholderid` bigint(20) NOT NULL,
  `tok_status` smallint(6) NOT NULL,
  `tok_expireon` datetime NOT NULL,
  `tok_createdon` datetime NOT NULL,
  `tok_modifiedon` datetime NOT NULL,
  `tok_createdby` bigint(20) NOT NULL,
  `tok_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`tok_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mytoken_archive`;
CREATE TABLE IF NOT EXISTS `mytoken_archive` LIKE `mytoken_archive`;

DROP TABLE IF EXISTS `mypartnerapi`;
CREATE TABLE `mypartnerapi` (
  `pap_id` bigint(8) NOT NULL AUTO_INCREMENT,
  `pap_type` enum('FPX','EKYC'),
  `pap_name` varchar(64) NOT NULL,
  `pap_classtype` varchar(64) NOT NULL,
  `pap_partnerid` bigint(8) NOT NULL,
  `pap_status` smallint(2) NOT NULL,
  `pap_createdon` datetime NOT NULL,
  `pap_modifiedon` datetime NOT NULL,
  `pap_createdby` bigint(8) NOT NULL,
  `pap_modifiedby` bigint(8) NOT NULL,
  PRIMARY KEY (`pap_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `mypartnersetting`;
CREATE TABLE `mypartnersetting` (
  `psg_id` bigint(8) NOT NULL AUTO_INCREMENT,
  `psg_partnerid` bigint(8) NOT NULL,
  `psg_sapdgcode`  varchar(64) NOT NULL,
  `psg_sapmintedwhs`  varchar(64) NOT NULL,
  `psg_sapkilobarwhs` varchar(64) NOT NULL COMMENT 'sap kilobar warehouse name',
  `psg_mininitialxau` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_minbalancexau` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_mindisbursement` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_verifyachemail` tinyint(1) NOT NULL,
  `psg_verifyachphone` tinyint(1) NOT NULL,
  `psg_verifyachpin` tinyint(1) NOT NULL,
  `psg_achcancloseaccount` tinyint(1) NOT NULL,
  `psg_skipekyc` tinyint(1) NOT NULL,
  `psg_skipamla` tinyint(1) NOT NULL,
  `psg_amlablacklistimmediately` tinyint(1) NOT NULL,
  `psg_ekycprovider` varchar(64),
  `psg_partnerpaymentprovider` varchar(64),
  `psg_companypaymentprovider` varchar(64),
  `psg_payoutprovider` varchar(64),
  `psg_transactionfee` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_payoutfee` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_courierfee` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_storagefeeperannum` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_adminfeeperannum` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_minstoragecharge` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_minstoragefeethreshold` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_maxxauperdelivery` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_maxpcsperdelivery` int(11) NOT NULL DEFAULT 0, 
  `psg_dgpartnersellcommission` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_dgpartnerbuycommission` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_dgpeakhourfrom` datetime DEFAULT NULL,
  `psg_dgpeakhourto` datetime DEFAULT NULL,
  `psg_dgpeakpartnersellcommission` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_dgpeakpartnerbuycommission` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_dgacesellcommission` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_dgacebuycommission` decimal(20,6) NOT NULL DEFAULT 0, 
  `psg_pricealertvaliddays` smallint(6) NOT NULL DEFAULT 0,
  `psg_strictinventoryutilisation` tinyint(1) NOT NULL DEFAULT 0,
  `psg_accesstokenlifetime` int(11) NOT NULL DEFAULT 0,
  `psg_refreshtokenlifetime` int(11) NOT NULL DEFAULT 0,
  `psg_enablepushnotification` tinyint(1) NOT NULL DEFAULT 0,
  `psg_uniquenric` tinyint(1) NOT NULL DEFAULT 1,
  `psg_sapitemcoderedeemfees` varchar(15) COMMENT 'adminbuy' DEFAULT '',
  `psg_sapitemcodeannualfees` varchar(15) COMMENT 'adminsell' DEFAULT '',
  `psg_sapitemcodestoragefees` varchar(15) COMMENT 'storagebuy' DEFAULT '',
  `psg_status` smallint(2) NOT NULL,
  `psg_createdon` datetime NOT NULL,
  `psg_modifiedon` datetime NOT NULL,
  `psg_createdby` bigint(8) NOT NULL,
  `psg_modifiedby` bigint(8) NOT NULL,
  PRIMARY KEY (`psg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mypartnersapsetting`;
CREATE TABLE `mypartnersapsetting` (
  `pss_id` bigint(8) NOT NULL AUTO_INCREMENT,
  `pss_partnerid` bigint(8) NOT NULL,
  `pss_transactiontype`  enum('STORAGE_FEE', 'CONVERSION_FEE', 'PROCESSING_FEE', 'ADMIN_FEE') NOT NULL,
  `pss_itemcode`  varchar(64) NOT NULL,
  `pss_action`    varchar(64) NOT NULL,
  `pss_tradebpvendor` int(1) DEFAULT 0,
  `pss_tradebpcus` int(1) DEFAULT 0,
  `pss_nontradebpvendor` int(1) DEFAULT 0,
  `pss_nontradebpcus` int(1) DEFAULT 0,
  `pss_gtprefno`    varchar(64) DEFAULT '',
  `pss_status` smallint(2) NOT NULL,
  `pss_createdon` datetime NOT NULL,
  `pss_modifiedon` datetime NOT NULL,
  `pss_createdby` bigint(8) NOT NULL,
  `pss_modifiedby` bigint(8) NOT NULL,
  PRIMARY KEY (`pss_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mypartnersapsettingcode`;
CREATE TABLE `mypartnersapsettingcode` (
  `psc_id` bigint(8) NOT NULL AUTO_INCREMENT,
  `psc_partnerid` bigint(8) NOT NULL,
  `psc_tradebpvendor` varchar(32) DEFAULT '',
  `psc_tradebpcus` varchar(32) DEFAULT '',
  `psc_nontradebpvendor` varchar(32) DEFAULT '',
  `psc_nontradebpcus` varchar(32) DEFAULT '',
  `psc_status` smallint(2) NOT NULL,
  `psc_createdon` datetime NOT NULL,
  `psc_modifiedon` datetime NOT NULL,
  `psc_createdby` bigint(8) NOT NULL,
  `psc_modifiedby` bigint(8) NOT NULL,
  PRIMARY KEY (`psc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*****************
      VIEWS
*****************/

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

CREATE OR REPLACE VIEW `vw_myscreeninglistimportlog` AS select `myscreeninglistimportlog`.*,
`createdby`.`usr_name` AS `sci_createdbyname`,
`modifiedby`.`usr_name` AS `sci_modifiedbyname`
from `myscreeninglistimportlog`
left join `user` AS createdby on (createdby.usr_id = `myscreeninglistimportlog`.`sci_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `myscreeninglistimportlog`.`sci_modifiedby`);

CREATE OR REPLACE VIEW `vw_myscreeningmatchlog` AS select `myscreeningmatchlog`.*,
`sourcelist`.*,
`createdby`.`usr_name` AS `scm_createdbyname`,
`modifiedby`.`usr_name` AS `scm_modifiedbyname`
from `myscreeningmatchlog`
left join `user` AS createdby on (createdby.usr_id = `myscreeningmatchlog`.`scm_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `myscreeningmatchlog`.`scm_modifiedby`)
left join `myscreeninglist` AS sourcelist on (sourcelist.scl_id = `myscreeningmatchlog`.`scm_screeninglistid`);

CREATE OR REPLACE VIEW `vw_myamlascanlog` AS select 
`myamlascanlog`.`asl_id`,
`myamlascanlog`.`asl_scannedon`,
`matchlog`.`scm_remarks` as `asl_scmremarks`,
`matchlog`.`scm_matcheddata` as `asl_scmmatcheddata`,
`matchlog`.`scm_matchedon` as `asl_scmmatchedon`,
`matchlog`.`scm_status` as `asl_scmstatus`,
`matchlog`.`scm_accountholderid` as `asl_scmaccountholderid`,
`sourcelist`.`scl_sourcetype` as `asl_sclsourcetype`
from `myamlascanlog`
left join `myscreeningmatchlog` AS matchlog on (matchlog.scm_amlascanlogid = `myamlascanlog`.`asl_id`)
left join `myscreeninglist` AS sourcelist on (sourcelist.scl_id = `matchlog`.`scm_screeninglistid`);

CREATE OR REPLACE VIEW `vw_mydisbursement` AS select `mydisbursement`.*,
`myaccountholder`.`ach_fullname` as `dbm_accountholdername`,
`myaccountholder`.`ach_accountholdercode` as `dbm_accountholdercode`,
`createdby`.`usr_name` AS `dbm_createdbyname`,
`modifiedby`.`usr_name` AS `dbm_modifiedbyname`
from `mydisbursement`
LEFT JOIN `myaccountholder` on (`myaccountholder`.`ach_id` = `mydisbursement`.`dbm_accountholderid`)
left join `user` AS createdby on (createdby.usr_id = `mydisbursement`.`dbm_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `mydisbursement`.`dbm_modifiedby`);


CREATE OR REPLACE VIEW `vw_mypaymentdetail` AS select `mypaymentdetail`.*,
`myaccountholder`.`ach_fullname` as `pdt_accountholdername`,
`myaccountholder`.`ach_accountholdercode` as `pdt_accountholdercode`,
`createdby`.`usr_name` AS `pdt_createdbyname`,
`modifiedby`.`usr_name` AS `pdt_modifiedbyname`
from `mypaymentdetail`
LEFT JOIN `myaccountholder` on (`myaccountholder`.`ach_id` = `mypaymentdetail`.`pdt_accountholderid`)
left join `user` AS createdby on (createdby.usr_id = `mypaymentdetail`.`pdt_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `mypaymentdetail`.`pdt_modifiedby`);


CREATE OR REPLACE VIEW `vw_myaccountholder` AS SELECT 
account.*,
occupationcategory.`occ_category` as ach_occupationcategory,
occupationsubcategory.`osc_code` as ach_occupationsubcategory,
`partner`.`par_code` as ach_partnercode,
`partner`.`par_name` as ach_partnername,
`partner`.`par_parent` as ach_partnerparent,
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



CREATE OR REPLACE VIEW `vw_myaccountholdersignup` AS SELECT 
account.*,
kycsubmission.kys_remarks AS ach_kycremarks,
screeninglist.scl_sourcetype AS ach_amlasourcetype,
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
address.`add_state` as ach_addressstate
from `myaccountholder` as account
left join `partner` on (`partner`.`par_id` = account.`ach_partnerid`)
left join `mybank` as bank on (bank.`bnk_id` = account.`ach_bankid`)
left join `myaddress` as address on (address.`add_accountholderid` = account.`ach_id`)
left join `myoccupationcategory` as occupationcategory on (occupationcategory.`occ_id` = account.`ach_occupationcategoryid`)
left join `myoccupationsubcategory` as occupationsubcategory on (occupationsubcategory.`osc_id` = account.`ach_occupationsubcategoryid`)
left join `myscreeningmatchlog` as screeningmatch on (screeningmatch.`scm_accountholderid` = account.`ach_id` 
  AND screeningmatch.`scm_id` = (SELECT MAX(t.`scm_id`) FROM `myscreeningmatchlog` t WHERE t.scm_accountholderid = screeningmatch.scm_accountholderid))
left join `myscreeninglist` as screeninglist on (screeninglist.`scl_id` = screeningmatch.`scm_screeninglistid`)
left join `mykycsubmission` as kycsubmission on (kycsubmission.`kys_accountholderid` = account.`ach_id` 
  AND kycsubmission.`kys_id` = (SELECT MAX(t.`kys_id`) FROM `mykycsubmission` t WHERE t.kys_accountholderid = kycsubmission.kys_accountholderid));

CREATE OR REPLACE VIEW `vw_myledger` AS SELECT   
  ledger.led_id,
  account.ach_fullname as led_achfullname,
  account.ach_mykadno as led_achmykadno,
  account.ach_accountholdercode as led_achaccountholdercode,
  null as led_ordsaprefno,
  CASE 
    WHEN cvn.cvn_type = 'CONVERSION_FEE' THEN cvn.cvn_type 
    ELSE ledger.led_type 
  END AS led_type,
  ledger.led_typeid,
  ledger.led_accountholderid,
  CASE 
    WHEN cvn.cvn_type = 'CONVERSION_FEE' THEN 0
    ELSE ledger.led_debit 
  END AS led_debit,
  ledger.led_credit,
  ledger.led_refno,
  ledger.led_status,
  ledger.led_transactiondate,
  ledger.led_remarks,
  CASE 
    WHEN ledger.led_type = 'PROMO' THEN goldtransfer.gtf_price
    WHEN ledger.led_type = 'STORAGE_FEE' THEN storagefee.msf_price
    ELSE ord.ord_price
  END AS led_ordgoldprice,
  CASE 
    WHEN cvn.cvn_type = 'CONVERSION_FEE' THEN cvn.cvn_amount
    WHEN ledger.led_type = 'BUY_FPX' THEN ord.ord_amount
    ELSE 0 
  END AS led_amountin,
  CASE 
    WHEN ledger.led_type = 'PROMO' THEN goldtransfer.gtf_amount
    WHEN ledger.led_type = 'SELL' THEN ord.ord_amount+COALESCE(ord.ord_fee,0)
    ELSE 0 
  END AS led_amountout
FROM `myledger` AS ledger
LEFT JOIN `myaccountholder` AS account ON (account.`ach_id` = ledger.`led_accountholderid`)
LEFT JOIN `mygoldtransaction` AS gtr ON ((ledger.`led_type` = 'BUY_FPX' OR ledger.`led_type` = 'SELL') AND gtr.`gtr_id` = ledger.`led_typeid`)
LEFT JOIN `mymonthlystoragefee` AS storagefee ON (ledger.`led_type` = 'STORAGE_FEE' AND storagefee.`msf_id` = ledger.`led_typeid`)
LEFT JOIN `mygoldtransfer` AS goldtransfer ON (ledger.`led_type` = 'PROMO' AND goldtransfer.`gtf_id` = ledger.`led_typeid`)
LEFT JOIN `order` AS ord ON ((ledger.`led_type` = 'BUY_FPX' OR ledger.`led_type` = 'SELL')  AND gtr.gtr_orderid = ord.ord_id)
LEFT JOIN (
   SELECT cvn_id, rdm_amount AS cvn_amount, rdm_type AS cvn_type FROM myconversion AS cvn LEFT JOIN (
    SELECT rdm_id, rdm_redemptionfee+rdm_insurancefee+rdm_handlingfee AS rdm_amount, 'CONVERSION_FEE' AS rdm_type FROM redemption
    UNION ALL
    SELECT rdm_id, rdm_totalweight, 'CONVERSION' FROM redemption
  ) AS rdm ON cvn.cvn_redemptionid = rdm.rdm_id
) as cvn ON (ledger.`led_type` = 'CONVERSION' AND cvn.`cvn_id` = ledger.`led_typeid`);


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

CREATE OR REPLACE VIEW `vw_mymonthlystoragefee` AS SELECT 
`mymonthlystoragefee`.*,
SUM(`myledger`.`led_credit`- `myledger`.`led_debit`) AS `msf_ledcurrentxau`,
`myaccountholder`.`ach_accountholdercode` as `msf_achaccountholdercode`,
`myaccountholder`.`ach_fullname` as `msf_achfullname`,
`myaccountholder`.`ach_mykadno` as `msf_achmykadno`,
`partner`.`par_id` AS `msf_partnerid`,
`partner`.`par_code` AS `msf_partnercode`
FROM `mymonthlystoragefee` 
LEFT JOIN `myaccountholder` ON `myaccountholder`.`ach_id` = `mymonthlystoragefee`.`msf_accountholderid`
LEFT JOIN `myledger` ON `myledger`.`led_accountholderid` = `mymonthlystoragefee`.`msf_accountholderid` AND `myledger`.`led_transactiondate` <= `mymonthlystoragefee`.`msf_chargedon`
LEFT JOIN `partner` ON `partner`.`par_id` = `myaccountholder`.`ach_partnerid`
GROUP BY `mymonthlystoragefee`.`msf_id`,`mymonthlystoragefee`.`msf_accountholderid`;

CREATE OR REPLACE VIEW `vw_mydocumentation` AS SELECT 
`mydocumentation`.*,
`createdby`.`usr_name` AS `doc_createdbyname`,
`modifiedby`.`usr_name` AS `doc_modifiedbyname`
from `mydocumentation`
left join `user` AS `createdby` on (`createdby`.`usr_id` = `mydocumentation`.`doc_createdby`)
left join `user` AS `modifiedby` on (`modifiedby`.`usr_id` = `mydocumentation`.`doc_modifiedby`);

CREATE OR REPLACE VIEW `vw_mydocumentationcontent` AS SELECT 
`mydocumentation`.*,
`mylocalizedcontent`.`loc_id` as `doc_locid`,
`mylocalizedcontent`.`loc_language` as `doc_loclanguage`,
`mylocalizedcontent`.`loc_status` as `doc_locstatus`,
`mylocalizedcontent`.`loc_createdon` as `doc_loccreatedon`,
`mylocalizedcontent`.`loc_modifiedon` as `doc_locmodifiedon`,
`loccreatedby`.`usr_name` AS `doc_loccreatedbyname`,
`locmodifiedby`.`usr_name` AS `doc_locmodifiedbyname`,
JSON_UNQUOTE(JSON_EXTRACT(
  `mylocalizedcontent`.`loc_data`, 
  '$.filecontent'
)) AS `doc_locfilecontent`,
JSON_UNQUOTE(JSON_EXTRACT(
  `mylocalizedcontent`.`loc_data`, 
  '$.filename'
)) AS `doc_locfilename`
from `mydocumentation`
left join `mylocalizedcontent` on (`mylocalizedcontent`.`loc_sourcetype` = 'DOCUMENTATION'
  AND `mylocalizedcontent`.`loc_sourceid` = `mydocumentation`.`doc_id`)
left join `user` AS `loccreatedby` on (`loccreatedby`.`usr_id` = `mylocalizedcontent`.`loc_createdby`)
left join `user` AS `locmodifiedby` on (`locmodifiedby`.`usr_id` = `mylocalizedcontent`.`loc_modifiedby`);

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
CASE 
  WHEN `order`.ord_status = 1 OR `order`.ord_status = 5 THEN `order`.ord_fee
  ELSE 0
END as gtr_ordfee,
`order`.ord_remarks as gtr_ordremarks,
`order`.ord_isspot as gtr_ordisspot,
`order`.ord_bookingon as gtr_ordbookingon,
`order`.ord_confirmon as gtr_ordconfirmon,
`order`.ord_cancelon as gtr_ordcancelon,
`order`.ord_status as gtr_ordstatus,

`myaccountholder`.ach_fullname as gtr_achfullname,
`myaccountholder`.ach_accountholdercode as gtr_achcode,
`myaccountholder`.ach_email as gtr_achemail,
`myaccountholder`.ach_mykadno as gtr_achmykadno,
`myaccountholder`.ach_phoneno as gtr_achphoneno,
`myaccountholder`.ach_partnercusid as gtr_achpartnercusid,

`partner`.`par_name` AS `gtr_ordpartnername`,
`product`.pdt_name AS gtr_ordproductname,
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
left join `product`
ON `order`.`ord_productid` = `product`.`pdt_id`
LEFT JOIN `myaccountholder` on (`myaccountholder`.`ach_id` = `order`.`ord_buyerid`)
LEFT JOIN `vw_mypaymentdetail`
ON gtr.gtr_refno = `vw_mypaymentdetail`.pdt_sourcerefno
LEFT JOIN `vw_mydisbursement`
ON gtr.gtr_refno = `vw_mydisbursement`.dbm_transactionrefno
LEFT JOIN `mybank` on (`mybank`.`bnk_id` = `vw_mydisbursement`.`dbm_bankid`)
LEFT JOIN `partner` on (`partner`.`par_id` = `order`.`ord_partnerid`);

CREATE OR REPLACE VIEW `vw_mygoldtransactionhistory` AS SELECT 
`gtr`.`gtr_orderid`, 
`gtr`.`gtr_refno`, 
`gtr`.`gtr_originalamount`, 
`order`.`ord_partnerid` as `gtr_ordpartnerid`,
`order`.`ord_buyerid` as `gtr_ordbuyerid`,
`order`.`ord_type` AS `gtr_ordtype`, 
`order`.`ord_price` AS `gtr_ordprice`,
`order`.`ord_xau` AS `gtr_ordxau`,
`order`.`ord_amount` AS `gtr_ordamount`,
`order`.`ord_fee` AS `gtr_ordfee`,
`gtr`.`gtr_settlementmethod`, 
`gtr`.`gtr_status`, 
`gtr`.`gtr_createdon`
FROM `mygoldtransaction` `gtr`
LEFT JOIN `order` ON `gtr`.`gtr_orderid` = `order`.`ord_id`
UNION SELECT 
null,
`msf`.`msf_refno`,
null,
`ach`.`ach_partnerid`,
`msf`.`msf_accountholderid`,
'StorageFee', 
`msf`.`msf_price`,
`msf`.`msf_xau`,
`msf`.`msf_amount`,
null,
null,
`msf`.`msf_status`, 
`msf`.`msf_chargedon` AS `gtr_createdon`
FROM `mymonthlystoragefee` `msf`
LEFT JOIN `myaccountholder` `ach` ON `msf`.`msf_accountholderid` = `ach`.`ach_id`
UNION SELECT
null,
`led`.`led_refno`,
null,
`led`.`led_partnerid`,
`gtf`.`gtf_accountholderid`,
`led`.`led_type`,
`gtf`.`gtf_price`,
`gtf`.`gtf_xau`,
`gtf`.`gtf_amount`,
null,
null,
`gtf`.`gtf_status`,
`gtf`.`gtf_createdon`
FROM `mygoldtransfer` `gtf`
LEFT JOIN `myledger` `led` ON `led`.`led_type` = 'PROMO' AND `gtf`.`gtf_id` = `led`.`led_typeid` AND `led`.`led_accountholderid` <> 0;


CREATE OR REPLACE VIEW `vw_myaccountclosure` AS SELECT 
`myaccountclosure`.*,
`myaccountholder`.`ach_accountholdercode` as `acs_achaccountholdercode`,
`myaccountholder`.`ach_fullname` as `acs_achfullname`,
`myaccountholder`.`ach_mykadno` as `acs_achmykadno`,
`myaccountholder`.`ach_partnerid` as `acs_achpartnerid`,
JSON_UNQUOTE(JSON_EXTRACT(
  `mylocalizedcontent`.`loc_data`, 
  '$.reason'
)) AS `acs_locreason`
FROM `myaccountclosure` 
left join `mylocalizedcontent` on (`mylocalizedcontent`.`loc_sourcetype` = 'CLOSE_REASON'
  AND `mylocalizedcontent`.`loc_sourceid` = `myaccountclosure`.`acs_reasonid` 
  AND `mylocalizedcontent`.`loc_language` = 'EN')
LEFT JOIN `myaccountholder` ON `myaccountholder`.`ach_id` = `myaccountclosure`.`acs_accountholderid`;

ALTER TABLE `apilogs` CHANGE COLUMN `api_type` `api_type` ENUM('NewPriceStream','NewPriceValidation','SapOrder','SapCancelOrder','SapGenerateGrn','SapGoldSerialRequest','ApiAllocateXau','ApiGetPrice','ApiNewBooking','ApiConfirmBooking','ApiCancelBooking','ApiRedemption','MYGTP','MYGTP_FPX','MYGTP_EKYC', 'MYGTP_WALLET') NOT NULL AFTER `api_id`;
