ALTER TABLE `order`
	ADD COLUMN `ord_discountprice` float(8,3) NULL DEFAULT 0 AFTER `ord_fee`,
	ADD COLUMN `ord_discountinfo` TEXT NULL DEFAULT '' AFTER `ord_discountprice`,
