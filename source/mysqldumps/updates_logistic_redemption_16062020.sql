/*
SQLyog Community v13.1.5  (64 bit)
MySQL - 5.7.24 : Database - gtp
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`gtp` /*!40100 DEFAULT CHARACTER SET utf8 */;

/*Table structure for table `logisticlog` */

DROP TABLE IF EXISTS `logisticlog`;

CREATE TABLE `logisticlog` (
  `lgl_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `lgl_logisticid` BINARY(1) DEFAULT NULL,
  `lgl_type` ENUM('Public','Private') DEFAULT NULL,
  `lgl_value` VARCHAR(128) DEFAULT NULL,
  `lgl_time` DATETIME DEFAULT NULL,
  `lgl_remarks` VARCHAR(128) DEFAULT NULL,
  `lgl_createdon` DATETIME DEFAULT NULL,
  `lgl_createdby` BIGINT(20) DEFAULT NULL,
  `lgl_modifiedon` DATETIME DEFAULT NULL,
  `lgl_modifiedby` BIGINT(20) DEFAULT NULL,
  PRIMARY KEY (`lgl_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `logitstic`;

CREATE TABLE `logitstic` (
  `lgs_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `lgs_type` ENUM('Redemption','Replenishment') DEFAULT NULL,
  `lgs_typeid` BIGINT(20) DEFAULT NULL COMMENT 'redetmptionid,replenishmentid',
  `lgs_vendorid` BIGINT(20) DEFAULT NULL COMMENT 'courier name/company/type, eg: ace_employee, GDEX, Courier1, Courier2, ACE_COURIER',
  `lgs_senderid` BIGINT(20) DEFAULT NULL COMMENT 'ace sender ID',
  `lgs_awbno` VARCHAR(20) DEFAULT NULL,
  `lgs_contactname1` VARCHAR(127) DEFAULT NULL,
  `lgs_contactname2` VARCHAR(127) DEFAULT NULL,
  `lgs_contactno1` VARCHAR(127) DEFAULT NULL,
  `lgs_contactno2` VARCHAR(127) DEFAULT NULL,
  `lgs_address1` VARCHAR(127) DEFAULT NULL,
  `lgs_address2` VARCHAR(127) DEFAULT NULL,
  `lgs_address3` VARCHAR(127) DEFAULT NULL,
  `lgs_city` VARCHAR(127) DEFAULT NULL,
  `lgs_postcode` VARCHAR(10) DEFAULT NULL,
  `lgs_state` VARCHAR(127) DEFAULT NULL,
  `lgs_country` VARCHAR(127) DEFAULT NULL,
  `lgs_frombranchid` BIGINT(20) DEFAULT NULL COMMENT 'for replenishment type of transfer',
  `lgs_tobranchid` BIGINT(20) DEFAULT NULL COMMENT 'for replenishment type of transfer',
  `lgs_senton` DATETIME DEFAULT NULL,
  `lgs_sentby` BIGINT(20) DEFAULT NULL,
  `lgs_recievedperson` VARCHAR(127) DEFAULT NULL,
  `lgs_deliveredon` DATETIME DEFAULT NULL,
  `lgs_deliveredby` BIGINT(20) DEFAULT NULL,
  `lgs_deliverydate` DATETIME DEFAULT NULL COMMENT 'logistic delivery date',
  `lgs_attemps` SMALLINT(2) DEFAULT '0' COMMENT 'Ordinal number',
  `lgs_status` ENUM('active','processing','failed','cancelled','completed') DEFAULT NULL COMMENT 'cancelled will be on cancel redemption or special case only',
  `lgs_createdon` DATETIME DEFAULT NULL,
  `lgs_createdby` BIGINT(20) DEFAULT NULL,
  `lgs_modifiedon` DATETIME DEFAULT NULL,
  `lgs_modifiedby` BIGINT(20) DEFAULT NULL,
  PRIMARY KEY (`lgs_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8;

/*Table structure for table `redemption` */

DROP TABLE IF EXISTS `redemption`;

CREATE TABLE `redemption` (
  `rdm_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rdm_partnerid` bigint(20) NOT NULL,
  `rdm_branchid` bigint(20) NOT NULL,
  `rdm_salespersonid` bigint(20) NOT NULL,
  `rdm_partnerrefno` varchar(50) DEFAULT NULL,
  `rdm_redemptionno` varchar(20) DEFAULT NULL,
  `rdm_apiversion` varchar(5) NOT NULL,
  `rdm_type` enum('branch','delivery','special_delivery','appointment') NOT NULL,
  `rdm_sapredemptioncode` varchar(20) DEFAULT NULL,
  `rdm_redemptionfee` decimal(20,6) DEFAULT NULL,
  `rdm_insurancefee` decimal(20,6) DEFAULT NULL,
  `rdm_handlingfee` decimal(20,6) DEFAULT NULL,
  `rdm_specialdeliveryfee` decimal(20,6) DEFAULT NULL,
  `rdm_totalweight` decimal(20,6) NOT NULL,
  `rdm_totalquantity` int(11) NOT NULL,
  `rdm_items` text NOT NULL COMMENT 'item arr obj json',
  `rdm_bookingon` datetime DEFAULT NULL,
  `rdm_bookingprice` decimal(20,6) DEFAULT NULL,
  `rdm_bookingpricestreamid` bigint(20) DEFAULT NULL,
  `rdm_confirmon` datetime DEFAULT NULL,
  `rdm_confirmby` bigint(20) DEFAULT NULL,
  `rdm_confirmprice` decimal(20,6) DEFAULT NULL,
  `rdm_confirmpricestreamid` bigint(20) DEFAULT NULL,
  `rdm_comfirmreference` varchar(20) DEFAULT NULL,
  `rdm_deliveryaddress1` varchar(255) DEFAULT NULL,
  `rdm_deliveryaddress2` varchar(255) DEFAULT NULL,
  `rdm_deliveryaddress3` varchar(255) DEFAULT NULL,
  `rdm_deliverycity` varchar(255) DEFAULT NULL,
  `rdm_deliverypostcode` varchar(10) DEFAULT NULL,
  `rdm_deliverystate` varchar(20) DEFAULT NULL,
  `rdm_deliverycountry` varchar(30) DEFAULT NULL,
  `rdm_deliverycontactname1` varchar(255) DEFAULT NULL,
  `rdm_deliverycontactname2` varchar(255) DEFAULT NULL,
  `rdm_deliverycontactno1` varchar(20) DEFAULT NULL,
  `rdm_deliverycontactno2` varchar(20) DEFAULT NULL,
  `rdm_appointmentbranchid` bigint(20) DEFAULT NULL,
  `rdm_appointmentdatetime` datetime DEFAULT NULL,
  `rdm_appointmenton` decimal(10,0) DEFAULT NULL,
  `rdm_appointmentby` varchar(255) DEFAULT NULL,
  `rdm_createdon` datetime DEFAULT NULL,
  `rdm_createdby` bigint(20) NOT NULL,
  `rdm_modifiedon` datetime NOT NULL,
  `rdm_modifiedby` bigint(20) NOT NULL,
  `rdm_status` smallint(6) NOT NULL DEFAULT '0',
  `rdm_remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rdm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
