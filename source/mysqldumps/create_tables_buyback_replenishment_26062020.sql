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

/*Table structure for table `buyback` */

DROP TABLE IF EXISTS `buyback`;

CREATE TABLE `buyback` (
  `byb_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `byb_partnerid` bigint(20) DEFAULT NULL,
  `byb_partnerrefid` varchar(50) DEFAULT NULL,
  `byb_branchid` bigint(20) DEFAULT NULL,
  `byb_orderid` bigint(20) DEFAULT NULL,
  `byb_productid` bigint(20) DEFAULT NULL,
  `byb_serialno` varchar(127) DEFAULT NULL,
  `byb_collectedon` datetime DEFAULT NULL,
  `byb_collectedby` bigint(20) DEFAULT NULL,
  `byb_status` bigint(20) DEFAULT NULL,
  `byb_createdby` bigint(20) DEFAULT NULL,
  `byb_createdon` datetime DEFAULT NULL,
  `byb_modifiedby` bigint(20) DEFAULT NULL,
  `byb_modifiedon` datetime DEFAULT NULL,
  PRIMARY KEY (`byb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `buyback` */

/*Table structure for table `replenishment` */

DROP TABLE IF EXISTS `replenishment`;

CREATE TABLE `replenishment` (
  `rpm_id` int(11) NOT NULL,
  `rpm_partnerid` bigint(20) DEFAULT NULL,
  `rpm_branchid` bigint(20) NOT NULL COMMENT 'branch replenishment',
  `rpm_salespersonid` bigint(20) DEFAULT NULL COMMENT 'who handle replenishment (should same as logistic senderid) **remove',
  `rpm_replenishmentno` varchar(20) DEFAULT NULL,
  `rpm_sapwhscode` varchar(32) DEFAULT NULL,
  `rpm_saprefno` varchar(50) DEFAULT NULL COMMENT 'sap reference no',
  `rpm_type` enum('replenish') DEFAULT NULL,
  `rpm_productid` bigint(20) NOT NULL COMMENT 'raw data, included all items, optional',
  `rpm_serialno` varbinary(127) NOT NULL,
  `rpm_status` tinyint(4) DEFAULT NULL,
  `rpm_schedule` datetime DEFAULT NULL,
  `rpm_replenishedon` datetime DEFAULT NULL,
  `rpm_createdon` datetime DEFAULT NULL,
  `rpm_createdby` bigint(20) DEFAULT NULL,
  `rpm_modifiedon` datetime DEFAULT NULL,
  `rpm_modifiedby` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`rpm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `replenishment` */

/*Table structure for table `replenishmentlogistic` */

DROP TABLE IF EXISTS `replenishmentlogistic`;

CREATE TABLE `replenishmentlogistic` (
  `rpl_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `rpl_replenishmentid` bigint(20) DEFAULT NULL,
  `rpl_logisticid` bigint(20) DEFAULT NULL,
  `rpl_createdon` datetime DEFAULT NULL,
  `rpl_createdby` bigint(20) DEFAULT NULL,
  `rpl_modifiedon` datetime DEFAULT NULL,
  `rpl_modifiedby` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`rpl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `replenishmentlogistic` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
