
-- Data exporting was unselected.
-- DROP TABLE IF EXISTS `mykycreminder`;
CREATE TABLE `mykycreminder` (
  `kcr_id` int NOT NULL AUTO_INCREMENT,
  `kcr_accountholderid` int NOT NULL,
  `kcr_senton` datetime NOT NULL,
  `kcr_createdon` datetime NOT NULL,
  `kcr_createdby` bigint(20) NOT NULL,
  `kcr_modifiedon` datetime NOT NULL,
  `kcr_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`kcr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8