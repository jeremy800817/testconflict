ALTER TABLE `order` ADD `ord_reconciledsaprefno` bigint(20) NOT NULL AFTER `ord_reconciledby`;

/*please run below sql to update view table*/
DROP VIEW  IF EXISTS `vw_order`;
ALTER VIEW `vw_order` AS select `order`.*,
`partner`.`par_name` AS `ord_partnername`,
`partner`.`par_code` AS `ord_partnercode`,
`buyerid`.`usr_name` AS `ord_buyername`,
`salespersonid`.`usr_name` AS `ord_salespersonname`,
`product`.`pdt_name` AS `ord_productname`,
`product`.`pdt_code` AS `ord_productcode`,
`confirmby`.`usr_name` AS `ord_confirmbyname`,
`cancelby`.`usr_name` AS `ord_cancelbyname`,
`reconciledby`.`usr_name` AS `ord_reconciledbyname`,
`createdby`.`usr_name` AS `ord_createdbyname`,
`modifiedby`.`usr_name` AS `ord_modifiedbyname`,
(case `order`.`ord_status` when 0 then 'Pending' when 1 then 'Confirmed' when 2 then 'Pending Payment' when 3 then 'Pending Cancel' when 4 then 'Cancelled' when 5 then 'Completed' end) AS `ord_statusname`
from `order`
left join `partner` on (`partner`.`par_id` = `order`.`ord_partnerid`)
left join `user` AS buyerid on (buyerid.usr_id = `order`.`ord_buyerid`)
left join `user` AS salespersonid on (salespersonid.usr_id = `order`.`ord_salespersonid`)
left join `product` on (`product`.`pdt_id` = `order`.`ord_productid`)
left join `user` AS confirmby on (confirmby.usr_id = `order`.`ord_confirmby`)
left join `user` AS cancelby on (cancelby.usr_id = `order`.`ord_cancelby`)
left join `user` AS reconciledby on (reconciledby.usr_id = `order`.`ord_reconciledby`)
left join `user` AS createdby on (createdby.usr_id = `order`.`ord_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `order`.`ord_modifiedby`);