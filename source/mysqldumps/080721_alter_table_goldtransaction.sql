ALTER TABLE `mygoldtransaction`
	ADD COLUMN `gtr_extradata` VARCHAR(255) NULL DEFAULT '' AFTER `gtr_salespersoncode`;