ALTER TABLE `partner` ADD `par_group` varchar(45) NULL AFTER `par_status`;
ALTER TABLE `partner` ADD `par_parent` bigint(20) NULL AFTER `par_group`;