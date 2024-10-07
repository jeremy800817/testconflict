--
-- Table structure for table `ftplogs`
--
DROP TABLE IF EXISTS `ftplogs`;

CREATE TABLE `ftplogs` (
  `ftp_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ftp_type` enum('Output','Input') NOT NULL,
  `ftp_fromip` char(25) NOT NULL,
  `ftp_systeminitiate` tinyint(4) NOT NULL,
  `ftp_refobject` varchar(40) NOT NULL,
  `ftp_refobjectid` bigint(20) NOT NULL,
  `ftp_requestdata` text,
  `ftp_responsedata` text,
  `ftp_text` text,
  `ftp_createdon` datetime NOT NULL,
  `ftp_createdby` bigint(20) NOT NULL,
  `ftp_modifiedon` datetime NOT NULL,
  `ftp_modifiedby` bigint(20) NOT NULL,
  `ftp_status` smallint(6) NOT NULL,
  PRIMARY KEY (`ftp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;