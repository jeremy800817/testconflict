ALTER TABLE `partner`
	ADD COLUMN `par_projectemail` VARCHAR(63) NULL DEFAULT '' AFTER `par_sendername`;
