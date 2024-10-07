ALTER TABLE `mydailystoragefee`
	ADD COLUMN `dsf_adminfeexau` DECIMAL(20,6) NOT NULL AFTER `dsf_xau`,
	ADD COLUMN `dsf_storagefeexau` DECIMAL(20,6) NOT NULL AFTER `dsf_adminfeexau`,
	ADD COLUMN `dsf_balancexau` DECIMAL(20,6) NOT NULL AFTER `dsf_storagefeexau`;
