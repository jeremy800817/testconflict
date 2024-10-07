ALTER TABLE `myaccountholder` ADD `ach_loantotal` decimal(20,6) NULL AFTER `ach_campaigncode`;
ALTER TABLE `myaccountholder` ADD `ach_loanbalance` decimal(20,6) NULL AFTER `ach_loantotal`;
ALTER TABLE `myaccountholder` ADD `ach_loanapprovedate` datetime NULL AFTER `ach_loanbalance`;
ALTER TABLE `myaccountholder` ADD `ach_loanapproveby` bigint(20) NULL AFTER `ach_loanapprovedate`;
ALTER TABLE `myaccountholder` ADD `ach_loanstatus` int NULL AFTER `ach_loanapproveby`;
ALTER TABLE `myaccountholder` ADD `ach_loanreference` varchar(50) NULL AFTER `ach_loanstatus`;


