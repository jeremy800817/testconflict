CREATE TABLE `mykycoperatorlogs` (
  `kyl_id` int NOT NULL AUTO_INCREMENT,
  `kyl_type` enum('APPROVE', 'REJECT') DEFAULT NULL,
  `kyl_accountholderid` int DEFAULT NULL,
  `kyl_remarks` text DEFAULT NULL,
  `kyl_approvedby` bigint(20) DEFAULT NULL,
  `kyl_approvedon` datetime NOT NULL,
  `kyl_status` smallint(6) NOT NULL,
  `kyl_createdon` datetime NOT NULL,
  `kyl_createdby` bigint(20) NOT NULL,
  `kyl_modifiedon` datetime NOT NULL,
  `kyl_modifiedby` bigint(20) NOT NULL,
   PRIMARY KEY (`kyl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
