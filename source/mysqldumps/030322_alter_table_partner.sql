ALTER TABLE `partner`
	ADD COLUMN `par_projectbase` VARCHAR(63) NULL DEFAULT '' AFTER `par_parent`,
	ADD COLUMN `par_sendername` VARCHAR(63) NULL DEFAULT '' AFTER `par_projectbase`,
	ADD COLUMN `par_senderemail` VARCHAR(63) NULL DEFAULT '' AFTER `par_sendername`;
