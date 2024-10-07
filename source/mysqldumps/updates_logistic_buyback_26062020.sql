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
  `byb_partnerrefno` varbinary(50) DEFAULT NULL,
  `byb_apiversion` varchar(5) DEFAULT NULL,
  `byb_branchid` bigint(20) DEFAULT NULL,
  `byb_buybackno` varbinary(20) DEFAULT NULL,
  `byb_pricestreamid` bigint(20) DEFAULT NULL COMMENT 'request price id',
  `byb_price` bigint(20) DEFAULT NULL COMMENT 'request gold price / client gold price',
  `byb_totalweight` float(20,6) DEFAULT NULL COMMENT 'total weight of items',
  `byb_totalamount` float(20,6) DEFAULT NULL COMMENT 'total price of items excluded fee',
  `byb_totalquantity` bigint(20) DEFAULT NULL COMMENT 'total quantity of items',
  `byb_fee` float(8,2) DEFAULT NULL COMMENT 'buyback fee for total item',
  `byb_items` text COMMENT 'items: serialno, denomination, productid',
  `byb_remarks` varchar(255) DEFAULT NULL,
  `byb_confirmpricestreamid` bigint(20) DEFAULT NULL COMMENT 'gtp ref price stream id',
  `byb_confirmprice` float(20,6) DEFAULT NULL COMMENT 'gtp ref price',
  `byb_confirmon` datetime DEFAULT NULL,
  `byb_collectedon` datetime DEFAULT NULL,
  `byb_collectedby` bigint(20) DEFAULT NULL,
  `byb_status` tinyint(5) DEFAULT NULL,
  `byb_createdon` datetime DEFAULT NULL,
  `byb_createdby` bigint(20) DEFAULT NULL,
  `byb_modifiedon` datetime DEFAULT NULL,
  `byb_modifiedby` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`byb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `buyback` */

/*Table structure for table `buybacklogistic` */

DROP TABLE IF EXISTS `buybacklogistic`;

CREATE TABLE `buybacklogistic` (
  `byl_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `byl_buybackid` bigint(20) DEFAULT NULL,
  `byl_logisticid` bigint(20) DEFAULT NULL,
  `byl_createdon` datetime DEFAULT NULL,
  `byl_createdby` bigint(20) DEFAULT NULL,
  `byl_modifiedon` datetime DEFAULT NULL,
  `byl_modifiedby` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`byl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `buybacklogistic` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;




ALTER TABLE `logistic` 
MODIFY COLUMN `lgs_type` ENUM('Redemption', 'Replenishment', 'Buyback');