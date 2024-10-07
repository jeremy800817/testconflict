ALTER TABLE `buyback` ADD `byb_reconciledsaprefno` bigint(20) NOT NULL AFTER `byb_reconciledby`;


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