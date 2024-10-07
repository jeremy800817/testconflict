
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