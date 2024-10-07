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