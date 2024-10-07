ALTER TABLE `redemption` ADD `rdm_reconciled` smallint(6) NOT NULL AFTER `rdm_appointmentby`;
ALTER TABLE `redemption` ADD `rdm_reconciledon` datetime NOT NULL AFTER `rdm_reconciled`;
ALTER TABLE `redemption` ADD `rdm_reconciledby` bigint(20) NOT NULL AFTER `rdm_reconciledon`;



ALTER TABLE `buyback` ADD `byb_reconciled` smallint(6) NOT NULL AFTER `byb_collectedby`;
ALTER TABLE `buyback` ADD `byb_reconciledon` datetime NOT NULL AFTER `byb_reconciled`;
ALTER TABLE `buyback` ADD `byb_reconciledby` bigint(20) NOT NULL AFTER `byb_reconciledon`;


/*please drop/create table view again*/
--
-- Structure for view `vw_redemption`
--
DROP VIEW  IF EXISTS `vw_redemption`;
CREATE VIEW `vw_redemption` AS select `redemption`.*,
`partner`.`par_name` AS `rdm_partnername`,
`partner`.`par_code` AS `rdm_partnercode`,
`partnerbranchmap`.`pbm_name` AS `rdm_branchname`,
`partnerbranchmap`.`pbm_code` AS `rdm_branchcode`,
`partnerbranchmap`.`pbm_sapcode` AS `rdm_branchsapcode`,
`salespersonid`.`usr_name` AS `rdm_salespersonname`,
`createdby`.`usr_name` AS `rdm_createdbyname`,
`modifiedby`.`usr_name` AS `rdm_modifiedbyname`,
(case `redemption`.`rdm_status` when 0 then 'Pending' when 1 then 'Confirmed' when 2 then 'Completed' when 3 then 'Failed' when 4 then 'Process Delivery' when 5 then 'Cancelled'  when 6 then 'Reversed' end) AS `rdm_statusname`
from `redemption`
left join `partner` on (`partner`.`par_id` = `redemption`.`rdm_partnerid`)
left join `partnerbranchmap` on (`partnerbranchmap`.`pbm_id` = `redemption`.`rdm_branchid`)
left join `user` AS salespersonid on (salespersonid.usr_id = `redemption`.`rdm_salespersonid`)
left join `user` AS createdby on (createdby.usr_id = `redemption`.`rdm_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `redemption`.`rdm_modifiedby`);



DROP VIEW  IF EXISTS `vw_buyback`;
CREATE VIEW `vw_buyback` AS select `buyback`.*,
`partner`.`par_name` AS `byb_partnername`,
`partner`.`par_code` AS `byb_partnercode`,
`partnerbranchmap`.`pbm_name` AS `byb_branchname`,
`partnerbranchmap`.`pbm_code` AS `byb_branchcode`,
`partnerbranchmap`.`pbm_sapcode` AS `byb_branchsapcode`,
`createdby`.`usr_name` AS `rdm_createdbyname`,
`modifiedby`.`usr_name` AS `rdm_modifiedbyname`,
(case `buyback`.`byb_status` when 0 then 'Pending' when 1 then 'Confirmed' when 2 then 'Process Collect' when 3 then 'Completed' when 4 then 'Failed' when 5 then 'Reversed' end) AS `byb_statusname` 
from `buyback`
left join `partner` on (`partner`.`par_id` = `buyback`.`byb_partnerid`)
left join `partnerbranchmap` on (`partnerbranchmap`.`pbm_code` = `buyback`.`byb_branchid`)
left join `user` AS createdby on (createdby.usr_id = `buyback`.`byb_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `buyback`.`byb_modifiedby`);
