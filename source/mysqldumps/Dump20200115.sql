-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 23, 2019 at 09:38 AM
-- Server version: 5.7.24
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gtp`
--

--
-- Table structure for table `app_state`
--
DROP TABLE IF EXISTS `app_state`;
CREATE TABLE `app_state` (
 `stt_id` bigint(20) NOT NULL AUTO_INCREMENT,
 `stt_userid` bigint(20) DEFAULT NULL,
 `stt_key` tinytext,
 `stt_value` text,
 PRIMARY KEY (`stt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `announcement`
--
CREATE TABLE `announcement` (
  `ann_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ann_partnerid` bigint(20) DEFAULT NULL,
  `ann_code` varchar(20) CHARACTER SET utf8 NOT NULL,
  `ann_title` varchar(127) CHARACTER SET utf8 NOT NULL,
  `ann_description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ann_content` varchar(127) CHARACTER SET utf8 DEFAULT NULL,
  `ann_picture` bigint(20) NOT NULL,
  `ann_rank` bigint(20) NOT NULL,
  `ann_type` enum('Push','Announcement') CHARACTER SET utf8 DEFAULT NULL,
  `ann_status` bigint(20) NOT NULL,
  `ann_displaystarton` datetime NOT NULL,
  `ann_displayendon` datetime NOT NULL,
  `ann_ismobile` smallint(5) DEFAULT NULL,
  `ann_timer` bigint(20)  DEFAULT NULL,
  `ann_createdon` datetime NOT NULL,
  `ann_modifiedon` datetime NOT NULL,
  `ann_createdby` bigint(20) NOT NULL,
  `ann_modifiedby` bigint(20) NOT NULL,
   PRIMARY KEY (`ann_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `api_logs`
--
DROP TABLE IF EXISTS `api_logs`;
CREATE TABLE `api_logs` (
  `api_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `api_type` enum('SapOrder','SapCancelOrder','SapGenerateGrn','SapGoldSerialRequest','ApiAllocateXau','ApiGetPrice','ApiNewBooking','ApiConfirmBooking','ApiCancelBooking','ApiRedemption') NOT NULL,
  `api_fromip` char(25) not null,
  `api_systeminitiate` tinyint not null,
  `api_requestdata` text,
  `api_responsedata` text,
  `api_createdon` datetime not null,
  `api_createdby` bigint not null,
  `api_modifiedon` datetime not null,
  `api_modifiedby` bigint not null,
  `api_status` smallint not null,
  PRIMARY KEY (`api_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `apiGoldRequest`
--
DROP TABLE IF EXISTS `apiGoldRequest`;
CREATE TABLE `apiGoldRequest` (
  `agr_id` bigint(20) not null auto_increment,
  `agr_partnerid` bigint(20) not null,
  `agr_partnerrefid` char(50) not null,
  `agr_apiversion` varchar(5) not null,
  `agr_quantity` smallint not null,
  `agr_reference` varchar(255) not null,
  `agr_timestamp` datetime,
  `agr_createdon` datetime not null,
  `agr_createdby` bigint not null,
  `agr_modifiedon` datetime not null,
  `agr_modifiedby` bigint not null,
  PRIMARY KEY (`agr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `attachment`
--
DROP TABLE IF EXISTS `attachment`;
CREATE TABLE `attachment` (
  `att_id` bigint(20) NOT NULL,
  `att_sourcetype` enum('REDEMPTION') DEFAULT NULL,
  `att_sourceid` bigint(20) DEFAULT '0',
  `att_description` tinytext,
  `att_filename` varchar(128) DEFAULT NULL,
  `att_filesize` int(11) DEFAULT NULL,
  `att_mimetype` varchar(30) DEFAULT NULL,
  `att_data` longblob,
  `att_createdon` datetime DEFAULT NULL,
  `att_createdby` bigint(20) DEFAULT NULL,
  `att_modifiedon` datetime DEFAULT NULL,
  `att_modifiedby` bigint(20) DEFAULT NULL,
  `att_status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `calendar`
--
DROP TABLE IF EXISTS `calendar`;
CREATE TABLE `calendar` (
  `cal_id` int(11) NOT NULL,
  `cal_title` tinytext,
  `cal_holidayon` datetime DEFAULT NULL,
  `cal_createdon` datetime DEFAULT NULL,
  `cal_createdby` bigint(11) DEFAULT NULL,
  `cal_modifiedon` datetime DEFAULT NULL,
  `cal_modifiedby` bigint(11) DEFAULT NULL,
  `cal_status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`cal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The calandar to store possible holidays and working days';

--
-- Table structure for table `api_logs`
--
DROP TABLE IF EXISTS `goods_receive_note`;
CREATE TABLE `goods_receive_note` (
  `grn_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `grn_partnerid` bigint(20) not null,
  `grn_salespersonid` bigint(20) not null,
  `grn_comments` varchar(255),
  `grn_jsonpostpayload` text  not null,
  `grn_totalxauexpected` decimal(20,6) not null,
  `grn_totalgrossweight` decimal(20,6) not null,
  `grn_totalxaucollected` decimal(20,6) not null,
  `grn_vatsum` decimal(20,6) not null,
  `grn_createdon` datetime NOT NULL,
  `grn_createdby` bigint(11) NOT NULL,
  `grn_modifiedon` datetime NOT NULL,
  `grn_modifiedby` bigint(11) NOT NULL,
  `grn_status` tinyint(4) NOT NULL COMMENT '',
  PRIMARY KEY (`grn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `api_logs`
--
DROP TABLE IF EXISTS `goods_receive_note_order`;
CREATE TABLE `goods_receive_note_order` (
  `gro_id` bigint(20) not null AUTO_INCREMENT,
  `gro_orderid` bigint(20) not null,
  `gro_goodsreceivenoteid` bigint(20) not null,
  `gro_createdon` datetime NOT NULL,
  `gro_createdby` bigint(11) NOT NULL,
  `gro_modifiedon` datetime NOT NULL,
  `gro_modifiedby` bigint(11) NOT NULL,
  `gro_status` tinyint(4) NOT NULL COMMENT '',
  PRIMARY KEY (`gro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `iprestriction`
--
DROP TABLE IF EXISTS `iprestriction`;
CREATE TABLE `iprestriction` (
  `ipr_id` bigint(8) NOT NULL AUTO_INCREMENT,
  `ipr_restricttype` enum('LOGIN') NOT NULL,
  `ipr_partnertype` enum('HQ', 'BRANCH') NOT NULL,
  `ipr_ip` varchar(40) NOT NULL COMMENT 'IP Address',
  `ipr_partnerid` bigint(8) NOT NULL COMMENT 'Partner ID',
  `ipr_remark` varchar(255) NOT NULL COMMENT 'Remark',
  `ipr_createdon` datetime NOT NULL,
  `ipr_modifiedon` datetime NOT NULL,
  `ipr_status` smallint(2) NOT NULL,
  `ipr_createdby` bigint(8) NOT NULL,
  `ipr_modifiedby` bigint(8) NOT NULL,
  PRIMARY KEY (`ipr_id`),
  KEY `ipr_partnerid` (`ipr_partnerid`,`ipr_restricttype`,`ipr_status`),
  KEY `ipr_partnertype` (`ipr_partnertype`,`ipr_restricttype`,`ipr_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `logistictracker`
--
DROP TABLE IF EXISTS `logistictracker`;
CREATE TABLE `logistictracker` (
  `lot_id` bigint(20) not null auto_increment,
  `lot_partnerid` bigint(20) not null,
  `lot_apiversion` varchar(5) not null,
  `lot_itemtype` enum('order','Redemption'),
  `lot_itemid` bigint(20) not null,
  `lot_senderid` bigint(20) not null,
  `lot_senderref` varchar(50) not null comment 'Delivery company ref no for the item in delivery',
  `lot_sendon` datetime,
  `lot_sendby` bigint(20) not null,
  `lot_receivedon` datetime,
  `lot_receiveperson` varchar(50) not null,
  `lot_createdon` datetime not null,
  `lot_createdby` bigint not null,
  `lot_modifiedon` datetime not null,
  `lot_modifiedby` bigint not null,
  `lot_status` smallint not null comment '0 - pending, 1 - confirmed, 2 - pending delivery, 3 - completed',
  PRIMARY KEY (`lot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `mbb_apfund`
--
DROP TABLE IF EXISTS `mbb_apfund`;
CREATE TABLE `mbb_apfund` (
  `apf_id` bigint(20) not null auto_increment,
  `apf_partnerid` bigint(20) not null,
  `apf_operationtype` enum('') not null,
  `apf_orderid` bigint(20) not null,
  `apf_beginprice` decimal(20,6) not null,
  `apf_beginpriceid` bigint(20) not null,
  `apf_endprice` decimal(20,6) not null,
  `apf_endpriceid` bigint(20) not null,
  `apf_amountppg` decimal(20,6) not null,
  `apf_amount` decimal(20,6) not null,
  PRIMARY KEY (`apf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `order`
--
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `ord_id` bigint(20) not null AUTO_INCREMENT,
  `ord_partnerid` bigint(20) not null,
  `ord_buyerid` bigint(20) not null,
  `ord_partnerrefid` varchar(50),
  `ord_orderno` varchar(20),
  `ord_pricestreamid` bigint(20) not null,
  `ord_salespersonid` bigint(20) not null,
  `ord_apiversion` varchar(5) not null,
  `ord_type` enum('CompanyBuy','CompanySell','CompanyBuyBack'),
  `ord_productid` bigint(20) not null,
  `ord_isspot` smallint not null,
  `ord_price` decimal(20,6) not null,
  `ord_byweight` smallint not null,
  `ord_xau` decimal(20,6) not null,
  `ord_amount` decimal(20,6) not null,
  `ord_fee` decimal(20,6) not null,
  `ord_remarks` varchar(255),
  `ord_bookingon` datetime not null,
  `ord_bookingprice` decimal(20,6) not null,
  `ord_bookingpricestreamid` bigint(20) not null,
  `ord_confirmon` datetime not null,
  `ord_confirmby` bigint(20) not null,
  `ord_confirmpricestreamid` bigint(20) not null,
  `ord_confirmprice` decimal(20,6) not null,
  `ord_confirmreference` varchar(16) not null,
  `ord_cancelon` datetime not null,
  `ord_cancelby` bigint(20) not null,
  `ord_cancelpriceid` bigint(20) not null,
  `ord_cancelprice` decimal(20,6) not null,
  `ord_notifyurl` varchar(255) not null,
  `ord_createdon` datetime not null,
  `ord_createdby` bigint not null,
  `ord_modifiedon` datetime not null,
  `ord_modifiedby` bigint not null,
  `ord_status` smallint not null,
  PRIMARY KEY (`ord_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Structure for view `vw_apilogs`
--
DROP VIEW  IF EXISTS `vw_apilogs`;
CREATE VIEW `vw_apilogs` AS select `apilogs`.*,
`createdby`.`usr_name` AS `api_createdbyname`,
`modifiedby`.`usr_name` AS `api_modifiedbyname`
from `apilogs`
left join `user` AS createdby on (createdby.usr_id = `apilogs`.`api_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `apilogs`.`api_modifiedby`);

--
-- Table structure for table `order_queue`
--
DROP TABLE IF EXISTS `orderqueue`;
CREATE TABLE `orderqueue` (
  `orq_id` bigint(20) not null AUTO_INCREMENT,
  `orq_orderid` bigint(20) not null,
  `orq_partnerid` bigint(20) not null,
  `orq_buyerid` bigint(20) not null,
  `orq_partnerrefid` char(50) not null,
  `orq_orderqueueno` varchar(20),
  `orq_salespersonid` bigint(20) not null,
  `orq_apiversion` varchar(5) not null,
  `orq_ordertype` enum('CompanyBuy','CompanySell','Redemption'),
  `orq_queuetype` enum('Day','GoodTillDate','GoodTillCancel'),
  `orq_expireon` datetime not null,
  `orq_productid` bigint(20) not null,
  `orq_pricetarget` decimal(20,6) not null,
  `orq_byweight` smallint not null,
  `orq_xau` decimal(20,6) not null,
  `orq_amount` decimal(20,6) not null,
  `orq_remarks` varchar(255),
  `orq_cancelon` datetime,
  `orq_cancelby` bigint(20) not null,
  `orq_matchpriceid` bigint(20) not null,
  `orq_matchon` datetime,
  `orq_notifyurl` varchar(255) not null,
  `orq_notifymatchurl` varchar(255) not null,
  `orq_successnotifyurl` varchar(255) not null,
  `orq_reconciled` smallint not null,
  `orq_reconciledon` datetime,
  `orq_reconciledby` bigint(20) not null,
  `orq_createdon` datetime not null,
  `orq_createdby` bigint not null,
  `orq_modifiedon` datetime not null,
  `orq_modifiedby` bigint not null,
  `orq_status` smallint not null COMMENT 'Pending, Expired, Cancelled, Fullfilled, Matched',
  /*`orq_status` smallint not null COMMENT 'Active, expired, cancelled',*/
  PRIMARY KEY (`orq_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `partner`
--
DROP TABLE IF EXISTS `partner`;
CREATE TABLE `partner` (
  `par_id` bigint(20) not null AUTO_INCREMENT,
  `par_code` varchar(20) not null,
  `par_name` varchar(100) not null,
  `par_address` varchar(200) not null,
  `par_postcode` varchar(20) not null,
  `par_state` varchar(20) not null,
  `par_type` enum('Customer','Referral'),
  `par_pricesourceid` bigint(20),
  `par_salespersonid` bigint(20),
  `par_tradingscheduleid` bigint(20),
  `par_sapcompanysellcode1` varchar(15) not null,
  `par_sapcompanybuycode1` varchar(15) not null,
  `par_sapcompanysellcode2` varchar(15) not null,
  `par_sapcompanybuycode2` varchar(15) not null,
  `par_dailybuylimitxau` float not null,
  `par_dailyselllimitxau` float not null,
  `par_pricelapsetimeallowance` int(6) not null,
  `par_orderingmode` enum('None','Web','API','Both') COMMENT 'How can a order be received by the system',
  `par_autosubmitorder` smallint not null COMMENT 'If orders should be autosubmitted to SAP system upon receiving',
  `par_autocreatematchedorder` smallint not null COMMENT 'Whether to automatically create the order when a F.O. is matched',
  `par_orderconfirmallowance` smallint not null COMMENT 'Time in seconds to allow for confirming of order',
  `par_ordercancelallowance` smallint not null COMMENT 'Time in seconds allowed to cancel orders taken',
  `par_apikey` bigint(20) not null,
  `par_createdon` datetime not null,
  `par_createdby` bigint not null,
  `par_modifiedon` datetime not null,
  `par_modifiedby` bigint not null,
  `par_status` smallint not null,
  PRIMARY KEY (`par_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `partnerBranchMap`
--
DROP TABLE IF EXISTS `partnerBranchMap`;
CREATE TABLE `partnerBranchMap` (
  `pbm_id` bigint(20) not null AUTO_INCREMENT,
  `pbm_partnerid` bigint(20) not null,
  `pbm_branchcode` varchar(10) not null,
  `pbm_name` varchar(20) not null,
  `pbm_partnercode` varchar(20) not null,
  `pbm_sapcode` varchar(20) not null,
  `pbm_createdon` datetime not null,
  `pbm_createdby` bigint(20) not null,
  `pbm_modifiedon` datetime not null,
  `pbm_modifiedby` bigint(20) not null,
  `pbm_status` smallint not null,
  PRIMARY KEY (`pbm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `partnerservice`
--
DROP TABLE IF EXISTS `partner_service`;
DROP TABLE IF EXISTS `partnerservice`;
CREATE TABLE `partnerservice` (
  `pas_id` bigint(20) not null AUTO_INCREMENT,
  `pas_partnerid` bigint(20) not null,
  `pas_partnersapgroup` smallint not null,
  `pas_productid` bigint(20) not null,
  `pas_pricesourcetypeid` bigint(20) not null,
  `pas_refineryfee` decimal(20,6) not null,
  `pas_premiumfee` decimal(20,6) not null,
  `pas_includefeeinprice` smallint not null,
  `pas_canbuy` smallint not null,
  `pas_cansell` smallint not null,
  `pas_canqueue` smallint not null,
  `pas_canredeem` smallint not null,
  `pas_clickminxau` float not null,
  `pas_clickmaxxau` float not null,
  `pas_dailybuylimitxau` float not null,
  `pas_dailyselllimitxau` float not null,
  `pas_createdon` datetime not null,
  `pas_createdby` bigint not null,
  `pas_modifiedon` datetime not null,
  `pas_modifiedby` bigint not null,
  `pas_status` smallint not null,
  PRIMARY KEY (`pas_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `product`
--
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `pdt_id` bigint(29) not null AUTO_INCREMENT,
  `pdt_categoryid` bigint(20) not null,
  `pdt_code` varchar(29) not null,
  `pdt_name` varchar(50) not null,
  `pdt_companycansell` smallint not null,
  `pdt_companycanbuy` smallint not null,
  `pdt_trxbyweight` smallint not null,
  `pdt_trxbycurrency` smallint not null,
  `pdt_deliverable` smallint not null,
  `pdt_sapitemcode` varchar(15) not null,
  `pdt_createdon` datetime not null,
  `pdt_createdby` bigint not null,
  `pdt_modifiedon` datetime not null,
  `pdt_modifiedby` bigint not null,
  `pdt_status` smallint not null,
  PRIMARY KEY (`pdt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `price_provider`
--
DROP TABLE IF EXISTS `priceprovider`;
CREATE TABLE `priceprovider` (
  `prp_id` bigint(20) not null AUTO_INCREMENT,
  `prp_code` varchar(29) not null,
  `prp_name` varchar(50) not null,
  `prp_pricesourceid` bigint(20) not null,
  `prp_productcategoryid` bigint(20) not null,
  `prp_pullmode` smallint not null,
  `prp_currencyid` bigint(20) not null,
  `prp_whitelistip` varchar(200) not null,
  `prp_url` varchar(255) not null,
  `prp_connectinfo` varchar(255) not null,
  `prp_lapsetimeallowance` int not null,
  `prp_createdon` datetime not null,
  `prp_createdby` bigint not null,
  `prp_modifiedon` datetime not null,
  `prp_modifiedby` bigint not null,
  `prp_status` smallint not null,
  PRIMARY KEY (`prp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `price_stream`
--
DROP TABLE IF EXISTS `price_stream`;
DROP TABLE IF EXISTS `pricestream`;
CREATE TABLE `pricestream` (
  `pst_id` bigint(20) not null AUTO_INCREMENT,
  `pst_providerid` bigint(20) not null,
  `pst_providerpriceid` varchar(50) not null,
  `pst_uuid` varchar(32) not null,
  `pst_currencyid` bigint(20) not null,
  `pst_companybuyppg` decimal(20,6),
  `pst_companysellppg` decimal(20,6),
  `pst_pricesourceid` bigint(20) not null,
  `pst_pricesourceon` datetime not null,
  `pst_createdon` datetime not null,
  `pst_createdby` bigint not null,
  `pst_modifiedon` datetime not null,
  `pst_modifiedby` bigint not null,
  `pst_status` smallint not null,
  PRIMARY KEY (`pst_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `price_stream`
--
DROP TABLE IF EXISTS `pricevalidation`;
CREATE TABLE `pricevalidation` (
  `pva_id` bigint(20) not null AUTO_INCREMENT,
  `pva_apilogid` bigint(20) not null,
  `pva_partnerid` bigint(20) not null,
  `pva_pricestreamid` bigint(20) not null,
  `pva_apiversion` varchar(5) not null,
  `pva_validityref` char(36) not null COMMENT 'Price ID to send back to partner',
  `pva_requestedtype` enum('CompanyBuy','CompanySell','Redemption') not null,
  `pva_premiumfee` decimal(20,6) not null,
  `pva_refineryfee` decimal(20,6) not null,
  `pva_validtill` datetime not null,
  `pva_orderid` bigint(20) not null,
  `pva_reference` varchar(255) not null,
  `pva_timestamp` datetime,
  `pva_createdon` datetime not null,
  `pva_createdby` bigint not null,
  `pva_modifiedon` datetime not null,
  `pva_modifiedby` bigint not null,
  `pva_status` smallint not null,
  PRIMARY KEY (`pva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `rbac_permissions`
--
DROP TABLE IF EXISTS `rbac_permissions`;
CREATE TABLE `rbac_permissions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Lft` int(11) NOT NULL,
  `Rght` int(11) NOT NULL,
  `Title` char(64) COLLATE utf8_bin NOT NULL,
  `Description` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Title` (`Title`),
  KEY `Lft` (`Lft`),
  KEY `Rght` (`Rght`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `rbac_rolepermissions`
--
DROP TABLE IF EXISTS `rbac_rolepermissions`;
CREATE TABLE `rbac_rolepermissions` (
  `RoleID` int(11) NOT NULL,
  `PermissionID` int(11) NOT NULL,
  `AssignmentDate` int(11) NOT NULL,
  PRIMARY KEY (`RoleID`,`PermissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `rbac_roles`
--
DROP TABLE IF EXISTS `rbac_roles`;
CREATE TABLE `rbac_roles` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Lft` int(11) NOT NULL,
  `Rght` int(11) NOT NULL,
  `Title` varchar(128) COLLATE utf8_bin NOT NULL,
  `Description` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Title` (`Title`),
  KEY `Lft` (`Lft`),
  KEY `Rght` (`Rght`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `rbac_userroles`
--
DROP TABLE IF EXISTS `rbac_userroles`;
CREATE TABLE `rbac_userroles` (
  `UserID` int(11) NOT NULL,
  `RoleID` int(11) NOT NULL,
  `AssignmentDate` int(11) NOT NULL,
  PRIMARY KEY (`UserID`,`RoleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Table structure for table `redemption`
--
DROP TABLE IF EXISTS `redemption`;
CREATE TABLE `redemption` (
  `rdm_id` bigint(20) not null auto_increment,
  `rdm_partnerid` bigint(20) not null,
  `rdm_branchid` bigint(20) not null,
  `rdm_salespersonid` bigint(20) not null,
  `rdm_partnerrefno` varchar(50),
  `rdm_redemptionno` varchar(20),
  `rdm_apiversion` varchar(5) not null,
  `rdm_type` enum('Redeem','Replendish','Preorder'),
  `rdm_productid` bigint(20) not null,
  `rdm_redemptionfee` decimal(20,6) not null,
  `rdm_insurancefee` decimal(20,6) not null,
  `rdm_handlingfee` decimal(20,6) not null,
  `rdm_specialdeliveryfee` decimal(20,6) not null,
  `rdm_xaubrand` varchar(20) not null,
  `rdm_xauserialno` varchar(20) not null,
  `rdm_xau` decimal(20,6) not null,
  `rdm_fee` decimal(20,6) not null,
  `rdm_bookingon` datetime not null,
  `rdm_bookingprice` decimal(20,6) not null,
  `rdm_bookingpricestreamid` bigint(20) not null,
  `rdm_confirmon` datetime not null,
  `rdm_confirmby` bigint(20) not null,
  `rdm_confirmpricestreamid` bigint(20) not null,
  `rdm_confirmedprice` decimal(20,6) not null,
  `rdm_confirmreference` varchar(16) not null,
  `rdm_deliveryaddress1` varchar(255) not null,
  `rdm_deliveryaddress2` varchar(255) not null,
  `rdm_deliveryaddress3` varchar(255) not null,
  `rdm_deliverypostcode` varchar(10) not null,
  `rdm_deliverystate` varchar(15) not null,
  `rdm_deliverycontactno` varchar(255) not null,
  `rdm_inventory` varchar(255),
  `rdm_processedon` datetime not null,
  `rdm_deliveredon` datetime not null,
  `rdm_createdon` datetime not null,
  `rdm_createdby` bigint not null,
  `rdm_modifiedon` datetime not null,
  `rdm_modifiedby` bigint not null,
  `rdm_status` smallint not null comment '0 - pending, 1 - confirmed, 2 - pending delivery, 3 - completed',
  `rdm_remarks` varchar(255),
  PRIMARY KEY (`rdm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `sales_commission`
--
DROP TABLE IF EXISTS `sales_commission`;
CREATE TABLE `sales_commission` (
  `com_id` bigint(20) not null AUTO_INCREMENT,
  `com_salespersonid` bigint(20) not null,
  `com_startdate` datetime not null,
  `com_enddate` datetime not null,
  `com_totalcompanybuy` decimal(20,6) not null,
  `com_totalcompanysell` decimal(20,6) not null,
  `com_totalxau` decimal(20,6) not null,
  `com_totalfee` decimal(20,6) not null,
  `com_createdon` datetime not null,
  `com_createdby` bigint not null,
  `com_modifiedon` datetime not null,
  `com_modifiedby` bigint not null,
  `com_status` smallint not null,
  PRIMARY KEY (`com_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `smsoutbox`
--

DROP TABLE IF EXISTS `smsoutbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smsoutbox` (
  `sms_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sms_phoneno` varchar(15) DEFAULT NULL,
  `sms_msg` text,
  `sms_senttime` datetime DEFAULT NULL,
  `sms_reference` varchar(100) DEFAULT NULL,
  `sms_msgtype` varchar(160) DEFAULT NULL,
  `sms_operator` varchar(100) DEFAULT NULL,
  `sms_errormsg` varchar(250) DEFAULT NULL,
  `sms_retrycount` int(11) DEFAULT NULL,
  `sms_rawresponse` varchar(512) DEFAULT NULL,
  `sms_createdon` datetime not null,
  `sms_createdby` bigint not null,
  `sms_modifiedon` datetime not null,
  `sms_modifiedby` bigint not null,
  `sms_status` smallint not null,
  PRIMARY KEY (`sms_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tag`
--
DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
  `tag_id` bigint(20) not null AUTO_INCREMENT,
  `tag_category` enum('PriceSource','ProductCategory','Currency','VaultOwner','TradingSchedule') not null,
  `tag_code` varchar(20) not null,
  `tag_description` varchar(50) not null,
  `tag_value` varchar(20) not null,
  `tag_createdon` datetime not null,
  `tag_createdby` bigint not null,
  `tag_modifiedon` datetime not null,
  `tag_modifiedby` bigint not null,
  `tag_status` smallint not null,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `taglink`
--
DROP TABLE IF EXISTS `taglink`;
CREATE TABLE `taglink` (
  `tlk_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tlk_tagid` bigint(11) NOT NULL,
  `tlk_sourcetype` enum('Price','Product','Currency') NOT NULL,
  `tlk_sourceid` bigint(11) NOT NULL,
  PRIMARY KEY (`tlk_id`),
  KEY `tlk_tagid` (`tlk_tagid`),
  KEY `tlk_sourcetype` (`tlk_sourcetype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `taglink`
--
DROP TABLE IF EXISTS `tradingschedule`;
CREATE TABLE `tradingschedule` (
  `tds_id` bigint(20) not null AUTO_INCREMENT,
  `tds_categoryid` bigint(20) not null COMMENT 'A trading category defined in tag table',
  `tds_startat` datetime,
  `tds_endat` datetime,
  `tds_createdon` datetime not null,
  `tds_createdby` bigint not null,
  `tds_modifiedon` datetime not null,
  `tds_modifiedby` bigint not null,
  `tds_status` smallint not null,
  PRIMARY KEY (`tds_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tag`
--
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `usr_id` bigint(20) not null AUTO_INCREMENT,
  `usr_username` varchar(20) not null,
  `usr_password` varchar(200) not null,
  `usr_oldpassword` varchar(255) not null,
  `usr_name` varchar(100) not null,
  `usr_phoneno` varchar(15) not null,
  `usr_email` varchar(50) not null,
  `usr_partnerid` bigint(20) not null,
  `usr_type` enum('Operator','Trader','Customer','Sale','Referral','Agent') not null,
  `usr_passwordmodifiedon` datetime not null,
  `usr_createdon` datetime not null,
  `usr_createdby` bigint not null,
  `usr_modifiedon` datetime not null,
  `usr_modifiedby` bigint not null,
  `usr_status` smallint not null,
  PRIMARY KEY (`usr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `user_role`
--
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `uhr_id` mediumint(8) UNSIGNED NOT NULL,
  `uhr_usrid` mediumint(8) UNSIGNED NOT NULL,
  `uhr_rolid` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `tag`
--
DROP TABLE IF EXISTS `userlog`;
CREATE TABLE `userlog` (
 `usl_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `usl_usrid` text,
 `usl_username` char(32) DEFAULT NULL,
 `usl_sessid` char(32) DEFAULT NULL,
 `usl_ip` char(32) DEFAULT NULL,
 `usl_browser` char(255) DEFAULT NULL,
 `usl_lastactive` int(10) unsigned DEFAULT NULL,
 `usl_logintime` int(10) unsigned DEFAULT NULL,
 `usl_logouttime` int(10) unsigned DEFAULT NULL,
 PRIMARY KEY (`usl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `vaultitem`
--
DROP TABLE IF EXISTS `vaultitem`;
CREATE TABLE `vaultitem` (
  `sti_id` bigint(20) not null auto_increment,
  `sti_vaultlocationid` bigint(20),
  `sti_productid` bigint(20) not null,
  `sti_weight` decimal(20, 6),
  `sti_brand`  varchar(20),
  `sti_serialno` varchar(20),
  `sti_newvaultlocationid` bigint(20),
  `sti_goldrequestid` bigint(20) not null,
  `sti_createdon` datetime,
  `sti_createdby` bigint(20) not null,
  `sti_modifiedon` datetime,
  `sti_modifiedby` bigint(20) not null,
  `sti_status` smallint not null comment '0 - pending, 1 - available, 2 - allocated, 3 - transferring, 4 - returned',
  PRIMARY KEY (`sti_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `vaultlocation`
--
DROP TABLE IF EXISTS `vaultlocation`;
CREATE TABLE `vaultlocation` (
  `stl_id` bigint(20) not null auto_increment,
  `stl_partnerid` bigint(20),
  `stl_name` varchar(20),
  `stl_owner` bigint(20) comment 'tag item - vendor, bank',
  `stl_minimumlevel` mediumint,
  `stl_reorderlevel` mediumint,
  `stl_defaultlocation` smallint comment 'Is default location for transfer operation with gold request',
  `stl_createdon` datetime,
  `stl_createdby` bigint(20) not null,
  `stl_modifiedon` datetime,
  `stl_modifiedby` bigint(20) not null,
  `stl_status` smallint not null,
  PRIMARY KEY (`stl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Structure for view `vw_apiGoldRequest`
--
DROP VIEW  IF EXISTS `vw_apiGoldRequest`;
CREATE VIEW `vw_apiGoldRequest` AS
select `apiGoldRequest`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `apiGoldRequest`.`agr_partnerid`)) AS `agr_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `apiGoldRequest`.`agr_partnerid`)) AS `agr_partnercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `apiGoldRequest`.`agr_createdby`)) AS `agr_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `apiGoldRequest`.`agr_modifiedby`)) AS `agr_modifiedbyname`
from `apiGoldRequest` ;

--
-- Structure for view `vw_goods_receive_note`
--
DROP VIEW  IF EXISTS `vw_goods_receive_note`;
CREATE VIEW `vw_goods_receive_note` AS
select `goods_receive_note`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `goods_receive_note`.`grn_partnerid`)) AS `grn_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `goods_receive_note`.`grn_partnerid`)) AS `grn_partnercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `goods_receive_note`.`grn_salespersonid`)) AS `grn_salespersonname`,
(select `user`.`usr_email` from `user` where (`user`.`usr_id` = `goods_receive_note`.`grn_salespersonid`)) AS `grn_salespersonemail`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `goods_receive_note`.`grn_createdby`)) AS `grn_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `goods_receive_note`.`grn_modifiedby`)) AS `grn_modifiedbyname`
from `goods_receive_note` ;

--
-- Structure for view `vw_goods_receive_note_order`
--
DROP VIEW  IF EXISTS `vw_goods_receive_note_order`;
CREATE VIEW `vw_goods_receive_note_order` AS
select `goods_receive_note_order`.*,
(select `order`.`ord_partnerid` from `order` where (`order`.`ord_id` = `goods_receive_note_order`.`gro_orderid`)) AS `gro_partnerid`,
(select `order`.`ord_buyerid` from `order` where (`order`.`ord_id` = `goods_receive_note_order`.`gro_orderid`)) AS `gro_buyerid`,
(select `order`.`ord_salespersonid` from `order` where (`order`.`ord_id` = `goods_receive_note_order`.`gro_orderid`)) AS `gro_salespersonid`,
(select `order`.`ord_productid` from `order` where (`order`.`ord_id` = `goods_receive_note_order`.`gro_orderid`)) AS `gro_productid`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `goods_receive_note_order`.`gro_createdby`)) AS `gro_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `goods_receive_note_order`.`gro_modifiedby`)) AS `gro_modifiedbyname`
from `goods_receive_note_order` ;

--
-- Structure for view `vw_logistic`
--
DROP VIEW  IF EXISTS `vw_logistic`;
CREATE VIEW `vw_logistic` AS
select `logistic`.*,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistic`.`lgs_senderid`)) AS `lgs_sendername`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistic`.`lgs_sentby`)) AS `lgs_sentbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistic`.`lgs_deliveredby`)) AS `lgs_deliveredbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistic`.`lgs_createdby`)) AS `lgs_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistic`.`lgs_modifiedby`)) AS `lgs_modifiedbyname`
from `logistic` ;

--
-- Structure for view `vw_logistictracker`
--
DROP VIEW  IF EXISTS `vw_logistictracker`;
CREATE VIEW `vw_logistictracker` AS
select `logistictracker`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `logistictracker`.`lot_partnerid`)) AS `lot_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `logistictracker`.`lot_partnerid`)) AS `lot_partnercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistictracker`.`lot_senderid`)) AS `lot_sendername`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistictracker`.`lot_createdby`)) AS `lot_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `logistictracker`.`lot_modifiedby`)) AS `lot_modifiedbyname`
from `logistictracker` ;

--
-- Structure for view `vw_mbb_apfund`
--
DROP VIEW  IF EXISTS `vw_mbb_apfund`;
CREATE VIEW `vw_mbb_apfund` AS
select `mbb_apfund`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `mbb_apfund`.`apf_partnerid`)) AS `apf_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `mbb_apfund`.`apf_partnerid`)) AS `apf_partnercode`,
(select `order`.`ord_buyerid` from `order` where (`order`.`ord_id` = `mbb_apfund`.`apf_orderid`)) AS `apf_buyerid`,
(select `order`.`ord_salespersonid` from `order` where (`order`.`ord_id` = `mbb_apfund`.`apf_orderid`)) AS `apf_salespersonid`
from `mbb_apfund` ;

--
-- Structure for view `vw_order`
--
DROP VIEW  IF EXISTS `vw_order`;
CREATE VIEW `vw_order` AS
select `order`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `order`.`ord_partnerid`)) AS `ord_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `order`.`ord_partnerid`)) AS `ord_partnercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order`.`ord_buyerid`)) AS `ord_buyername`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order`.`ord_salespersonid`)) AS `ord_salespersonname`,
(select `product`.`pdt_code` from `product` where (`product`.`pdt_id` = `order`.`ord_productid`)) AS `ord_productcode`,
(select `product`.`pdt_name` from `product` where (`product`.`pdt_id` = `order`.`ord_productid`)) AS `ord_productname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order`.`ord_confirmby`)) AS `ord_confirmbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order`.`ord_cancelby`)) AS `ord_cancelbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order`.`ord_reconciledby`)) AS `ord_reconciledbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order`.`ord_createdby`)) AS `ord_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order`.`ord_modifiedby`)) AS `ord_modifiedbyname`
from `order` ;

--
-- Structure for view `vw_order_queue`
--
DROP VIEW  IF EXISTS `vw_order_queue`;
CREATE VIEW `vw_order_queue` AS
select `order_queue`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `order_queue`.`orq_partnerid`)) AS `orq_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `order_queue`.`orq_partnerid`)) AS `orq_partnercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order_queue`.`orq_buyerid`)) AS `orq_buyername`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order_queue`.`orq_salespersonid`)) AS `orq_salespersonname`,
(select `product`.`pdt_code` from `product` where (`product`.`pdt_id` = `order_queue`.`orq_productid`)) AS `orq_productcode`,
(select `product`.`pdt_name` from `product` where (`product`.`pdt_id` = `order_queue`.`orq_productid`)) AS `orq_productname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order_queue`.`orq_cancelby`)) AS `orq_cancelbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order_queue`.`orq_reconciledby`)) AS `orq_reconciledbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order_queue`.`orq_createdby`)) AS `orq_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `order_queue`.`orq_modifiedby`)) AS `orq_modifiedbyname`
from `order_queue` ;

--
-- Structure for view `vw_partner`
--
DROP VIEW  IF EXISTS `vw_partner`;
CREATE VIEW `vw_partner` AS
select `partner`.*,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `partner`.`par_salespersonid`)) AS `par_salespersonname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `partner`.`par_createdby`)) AS `par_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `partner`.`par_modifiedby`)) AS `par_modifiedbyname`
from `partner` ;

--
-- Structure for view `vw_partnerBranchMap`
--
DROP VIEW  IF EXISTS `vw_partnerBranchMap`;
CREATE VIEW `vw_partnerBranchMap` AS
select `partnerBranchMap`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `partnerBranchMap`.`pbm_partnerid`)) AS `pbm_partnername`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `partnerBranchMap`.`pbm_createdby`)) AS `pbm_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `partnerBranchMap`.`pbm_modifiedby`)) AS `pbm_modifiedbyname`
from `partnerBranchMap` ;

--
-- Structure for view `vw_partnerservice`
--
DROP VIEW  IF EXISTS `vw_partner_service`;
DROP VIEW  IF EXISTS `vw_partnerservice`;
CREATE VIEW `vw_partnerservice` AS
select `partnerservice`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `partnerservice`.`pas_partnerid`)) AS `pas_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `partnerservice`.`pas_partnerid`)) AS `pas_partnercode`,
(select `product`.`pdt_code` from `product` where (`product`.`pdt_id` = `partnerservice`.`pas_productid`)) AS `pas_productcode`,
(select `product`.`pdt_name` from `product` where (`product`.`pdt_id` = `partnerservice`.`pas_productid`)) AS `pas_productname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `partnerservice`.`pas_createdby`)) AS `pas_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `partnerservice`.`pas_modifiedby`)) AS `pas_modifiedbyname`
from `partnerservice` ;

--
-- Structure for view `vw_product`
--
DROP VIEW  IF EXISTS `vw_product`;
CREATE VIEW `vw_product` AS
select `product`.*,
(select `tag`.`tag_code` from `tag` where (`tag`.`tag_id` = `product`.`pdt_categoryid`)) AS `pdt_categoryname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `product`.`pdt_createdby`)) AS `pdt_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `product`.`pdt_modifiedby`)) AS `pdt_modifiedbyname`
from `product` ;

--
-- Structure for view `vw_priceprovider`
--
DROP VIEW  IF EXISTS `vw_priceprovider`;
CREATE VIEW `vw_priceprovider` AS
select `priceprovider`.*,
(select `tag`.`tag_code` from `tag` where (`tag`.`tag_id` = `priceprovider`.`prp_productcategoryid`)) AS `prp_productcategoryname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `priceprovider`.`prp_createdby`)) AS `prp_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `priceprovider`.`prp_modifiedby`)) AS `prp_modifiedbyname`
from `priceprovider` ;

--
-- Structure for view `vw_pricestream`
--
DROP VIEW  IF EXISTS `vw_price_stream`;
DROP VIEW  IF EXISTS `vw_pricestream`;
CREATE VIEW `vw_pricestream` AS
select `pricestream`.*,
(select `tag`.`tag_code` from `tag` where (`tag`.`tag_id` = `pricestream`.`pst_currencyid`)) AS `pst_categoryname`,
(select `priceprovider`.`prp_name` from `priceprovider` where (`priceprovider`.`prp_id` = `pricestream`.`pst_providerid`)) AS `pst_providername`,
(select `priceprovider`.`prp_code` from `priceprovider` where (`priceprovider`.`prp_id` = `pricestream`.`pst_providerid`)) AS `pst_providercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `pricestream`.`pst_createdby`)) AS `pst_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `pricestream`.`pst_modifiedby`)) AS `pst_modifiedbyname`
from `pricestream` ;

--
-- Structure for view `vw_pricevalidation`
--
DROP VIEW  IF EXISTS `vw_pricevalidation`;
CREATE VIEW `vw_pricevalidation` AS
select `pricevalidation`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `pricevalidation`.`pva_partnerid`)) AS `pva_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `pricevalidation`.`pva_partnerid`)) AS `pva_partnercode`,
(select `order`.`ord_buyerid` from `order` where (`order`.`ord_id` = `pricevalidation`.`pva_orderid`)) AS `gro_buyerid`,
(select `order`.`ord_salespersonid` from `order` where (`order`.`ord_id` = `pricevalidation`.`pva_orderid`)) AS `gro_salespersonid`,
(select `order`.`ord_productid` from `order` where (`order`.`ord_id` = `pricevalidation`.`pva_orderid`)) AS `gro_productid`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `pricevalidation`.`pva_createdby`)) AS `pva_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `pricevalidation`.`pva_modifiedby`)) AS `pva_modifiedbyname`
from `pricevalidation` ;

--
-- Structure for view `vw_redemption`
--
DROP VIEW  IF EXISTS `vw_redemption`;
CREATE VIEW `vw_redemption` AS
select `redemption`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `redemption`.`rdm_partnerid`)) AS `rdm_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `redemption`.`rdm_partnerid`)) AS `rdm_partnercode`,
(select `partnerBranchMap`.`pbm_name` from `partnerBranchMap` where (`partnerBranchMap`.`pbm_id` = `redemption`.`rdm_branchid`)) AS `rdm_branchname`,
(select `partnerBranchMap`.`pbm_branchcode` from `partnerBranchMap` where (`partnerBranchMap`.`pbm_id` = `redemption`.`rdm_branchid`)) AS `rdm_branchcode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `redemption`.`rdm_salespersonid`)) AS `rdm_salespersonname`,
(select `product`.`pdt_code` from `product` where (`product`.`pdt_id` = `redemption`.`rdm_productid`)) AS `rdm_productcode`,
(select `product`.`pdt_name` from `product` where (`product`.`pdt_id` = `redemption`.`rdm_productid`)) AS `rdm_productname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `redemption`.`rdm_createdby`)) AS `rdm_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `redemption`.`rdm_modifiedby`)) AS `rdm_modifiedbyname`
from `redemption` ;


--
-- Structure for view `vw_sales_commission`
--
DROP VIEW  IF EXISTS `vw_sales_commission`;
CREATE VIEW `vw_sales_commission` AS
select `sales_commission`.*,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `sales_commission`.`com_salespersonid`)) AS `com_salespersonname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `sales_commission`.`com_createdby`)) AS `com_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `sales_commission`.`com_modifiedby`)) AS `com_modifiedbyname`
from `sales_commission` ;

--
-- Structure for view `vw_taglink`
--
DROP VIEW  IF EXISTS `vw_taglink`;
CREATE VIEW `vw_taglink` AS
select `taglink`.*,
(select `tag`.`tag_category` from `tag` where (`tag`.`tag_id` = `taglink`.`tlk_tagid`)) AS `tlk_tagcategory`,
(select `tag`.`tag_code` from `tag` where (`tag`.`tag_id` = `taglink`.`tlk_tagid`)) AS `tlk_tagcode`
from `taglink` ;

--
-- Structure for view `vw_tag`
--
DROP VIEW  IF EXISTS `vw_tag`;
CREATE VIEW `vw_tag` AS
select `tag`.*,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `tag`.`tag_createdby`)) AS `tag_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `tag`.`tag_modifiedby`)) AS `tag_modifiedbyname`
from `tag` ;


/*DROP VIEW  IF EXISTS `vw_tag`;
CREATE VIEW `vw_tag` AS
select `tag`.`tag_id` AS `tag_id`,
`tag`.`tag_category` AS `tag_category`,
`tag`.`tag_code` AS `tag_code`,
`tag`.`tag_description` AS `tag_description`,
`tag`.`tag_createdon` AS `tag_createdon`,
`tag`.`tag_createdby` AS `tag_createdby`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `tag`.`tag_createdby`)) AS `tag_createdbyname`,
`tag`.`tag_modifiedon` AS `tag_modifiedon`,
`tag`.`tag_modifiedby` AS `tag_modifiedby`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `tag`.`tag_modifiedby`)) AS `tag_modifiedbyname`,
`tag`.`tag_status` AS `tag_status`
from `tag`;*/

--
-- Structure for view `vw_user`
--
DROP VIEW  IF EXISTS `vw_user`;
CREATE VIEW `vw_user` AS
select `user`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `user`.`usr_partnerid`)) AS `usr_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `user`.`usr_partnerid`)) AS `usr_partnercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `user`.`usr_createdby`)) AS `usr_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `user`.`usr_modifiedby`)) AS `usr_modifiedbyname`
from `user` ;


CREATE OR REPLACE VIEW `vw_vaultitem` AS select `vaultitem`.*,
`vaultlocation`.`stl_name` AS `sti_vaultlocationname`,
`vaultlocation`.`stl_type` AS `sti_vaultlocationtype`,
`vaultlocation`.`stl_defaultlocation` AS `sti_vaultlocationdefault`,
`vaultlocation`.`stl_partnerid` AS `sti_movetolocationpartnerid`,
`movetovaultlocationid`.`stl_name` AS `sti_movetovaultlocationname`,
`newvaultlocationid`.`stl_name` AS `sti_newvaultlocationname`,
`partner`.`par_name` AS `sti_partnername`,
`partner`.`par_code` AS `sti_partnercode`,
`product`.`pdt_name` AS `sti_productname`,
`product`.`pdt_code` AS `sti_productcode`,
`createdby`.`usr_name` AS `sti_createdbyname`,
`modifiedby`.`usr_name` AS `sti_modifiedbyname`
from `vaultitem`
left join `vaultlocation` on (`vaultlocation`.`stl_id` = `vaultitem`.`sti_vaultlocationid`)
left join `vaultlocation` AS movetovaultlocationid on (movetovaultlocationid.stl_id = `vaultitem`.`sti_movetovaultlocationid`)
left join `vaultlocation` AS newvaultlocationid on (newvaultlocationid.stl_id = `vaultitem`.`sti_newvaultlocationid`)
left join `partner` on (`partner`.`par_id` = `vaultitem`.`sti_partnerid`)
left join `product` on (`product`.`pdt_id` = `vaultitem`.`sti_productid`)
left join `user` AS createdby on (createdby.usr_id = `vaultitem`.`sti_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `vaultitem`.`sti_modifiedby`);

--
-- Structure for view `vw_vaultlocation`
--
DROP VIEW  IF EXISTS `vw_vaultlocation`;
CREATE VIEW `vw_vaultlocation` AS
select `vaultlocation`.*,
(select `partner`.`par_name` from `partner` where (`partner`.`par_id` = `vaultlocation`.`stl_partnerid`)) AS `stl_partnername`,
(select `partner`.`par_code` from `partner` where (`partner`.`par_id` = `vaultlocation`.`stl_partnerid`)) AS `stl_partnercode`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `vaultlocation`.`stl_createdby`)) AS `stl_createdbyname`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `vaultlocation`.`stl_modifiedby`)) AS `stl_modifiedbyname`
from `vaultlocation` ;

--
-- Structure for view `vw_tradingschedule`
--
DROP VIEW  IF EXISTS `vw_tradingschedule`;
CREATE VIEW `vw_tradingschedule` AS
select `tradingschedule`.`tds_id` AS `tds_id`,
`tradingschedule`.`tds_categoryid` AS `tds_categoryid`,
`tradingschedule`.`tds_type` AS `tds_type`,
(select `tag`.`tag_category` from `tag` where (`tag`.`tag_id` = `tradingschedule`.`tds_categoryid`)) AS `tds_categoryname`,
(select `tag`.`tag_code` from `tag` where (`tag`.`tag_id` = `tradingschedule`.`tds_categoryid`)) AS `tds_categorycode`,
`tradingschedule`.`tds_startat` AS `tds_startat`,
`tradingschedule`.`tds_endat` AS `tds_endat`,
`tradingschedule`.`tds_createdon` AS `tds_createdon`,
`tradingschedule`.`tds_createdby` AS `tds_createdby`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `tradingschedule`.`tds_createdby`)) AS `tds_createdbyname`,
`tradingschedule`.`tds_modifiedon` AS `tds_modifiedon`,
`tradingschedule`.`tds_modifiedby` AS `tds_modifiedby`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `tradingschedule`.`tds_modifiedby`)) AS `tds_modifiedbyname`,
`tradingschedule`.`tds_status` AS `tds_status`
from `tradingschedule`;

--
-- Structure for view `vw_orderqueue`
--
/*
DROP VIEW  IF EXISTS `vw_orderqueue`;
CREATE VIEW `vw_orderqueue` AS
select `orderqueue`.`orq_id` AS `orq_id`,
`orderqueue`.`orq_orderid` AS `orq_orderid`,
`orderqueue`.`orq_partnerid` AS `orq_partnerid`,
`orderqueue`.`orq_buyerid` AS `orq_buyerid`,
`orderqueue`.`orq_partnerrefid` AS `orq_partnerrefid`,
`orderqueue`.`orq_orderqueueno` AS `orq_orderqueueno`,
`orderqueue`.`orq_salespersonid` AS `orq_salespersonid`,
`orderqueue`.`orq_apiversion` AS `orq_apiversion`,
`orderqueue`.`orq_ordertype` AS `orq_ordertype`,
`orderqueue`.`orq_queuetype` AS `orq_queuetype`,
`orderqueue`.`orq_expireon` AS `orq_expireon`,
`orderqueue`.`orq_productid` AS `orq_productid`,
`orderqueue`.`orq_pricetarget` AS `orq_pricetarget`,
`orderqueue`.`orq_byweight` AS `orq_byweight`,
`orderqueue`.`orq_xau` AS `orq_xau`,
`orderqueue`.`orq_amount` AS `orq_amount`,
`orderqueue`.`orq_remarks` AS `orq_remarks`,
`orderqueue`.`orq_cancelon` AS `orq_cancelon`,
`orderqueue`.`orq_cancelby` AS `orq_cancelby`,
`orderqueue`.`orq_matchpriceid` AS `orq_matchpriceid`,
`orderqueue`.`orq_matchon` AS `orq_matchon`,
`orderqueue`.`orq_notifyurl` AS `orq_notifyurl`,
`orderqueue`.`orq_notifymatchurl` AS `orq_notifymatchurl`,
`orderqueue`.`orq_successnotifyurl` AS `orq_successnotifyurl`,
`orderqueue`.`orq_reconciled` AS `orq_reconciled`,
`orderqueue`.`orq_reconciledon` AS `orq_reconciledon`,
`orderqueue`.`orq_reconciledby` AS `orq_reconciledby`,
`orderqueue`.`orq_createdon` AS `orq_createdon`,
`orderqueue`.`orq_createdby` AS `orq_createdby`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `orderqueue`.`orq_createdby`)) AS `orq_createdbyname`,
`orderqueue`.`orq_modifiedon` AS `orq_modifiedon`,
`orderqueue`.`orq_modifiedby` AS `orq_modifiedby`,
(select `user`.`usr_name` from `user` where (`user`.`usr_id` = `orderqueue`.`orq_modifiedby`)) AS `orq_modifiedbyname`,
`orderqueue`.`orq_status` AS `orq_status`
from `orderqueue`;
*/

DROP VIEW  IF EXISTS `vw_orderqueue`;
CREATE VIEW `vw_orderqueue` AS select `orderqueue`.*,
`order`.`ord_orderno` AS `orq_orderno`,
`partner`.`par_name` AS `orq_partnername`,
`partner`.`par_code` AS `orq_partnercode`,
`buyerid`.`usr_name` AS `orq_buyername`,
`salespersonid`.`usr_name` AS `orq_salespersonname`,
`partner`.`par_pricesourceid` AS `orq_pricesourceid`,
`product`.`pdt_name` AS `orq_productname`,
`product`.`pdt_code` AS `orq_productcode`,
`cancelby`.`usr_name` AS `orq_cancelbyname`,
`reconciledby`.`usr_name` AS `orq_reconciledbyname`,
`createdby`.`usr_name` AS `orq_createdbyname`,
`modifiedby`.`usr_name` AS `orq_modifiedbyname`
from `orderqueue`
left join `order` on (`order`.`ord_id` = `orderqueue`.`orq_orderid`)
left join `partner` on (`partner`.`par_id` = `orderqueue`.`orq_partnerid`)
left join `user` AS buyerid on (buyerid.usr_id = `orderqueue`.`orq_buyerid`)
left join `user` AS salespersonid on (salespersonid.usr_id = `orderqueue`.`orq_salespersonid`)
left join `product` on (`product`.`pdt_id` = `orderqueue`.`orq_productid`)
left join `user` AS cancelby on (cancelby.usr_id = `orderqueue`.`orq_cancelby`)
left join `user` AS reconciledby on (reconciledby.usr_id = `orderqueue`.`orq_reconciledby`)
left join `user` AS createdby on (createdby.usr_id = `orderqueue`.`orq_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `orderqueue`.`orq_modifiedby`);


/*(select `tag`.`tag_category` from `tag` where (`tag`.`tag_id` = `tradingschedule`.`tds_categoryid`)) AS `tds_categoryname`,
(select `tag`.`tag_code` from `tag` where (`tag`.`tag_id` = `tradingschedule`.`tds_categoryid`)) AS `tds_categorycode`,*/
