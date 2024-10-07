ALTER TABLE `vaultitem` ADD `sti_sharedgv` smallint DEFAULT '0' AFTER `sti_deliveryordernumber`;


DROP VIEW  IF EXISTS `vw_vaultitem`;
CREATE VIEW `vw_vaultitem` AS
select 
`vaultitem`.*,
`vaultlocation`.`stl_name` AS `sti_vaultlocationname`,
`vaultlocation`.`stl_type` AS `sti_vaultlocationtype`,
`vaultlocation`.`stl_defaultlocation` AS `sti_vaultlocationdefault`,
`vaultlocation`.`stl_partnerid` AS `sti_movetolocationpartnerid`,
`movetovaultlocationid`.`stl_name` AS `sti_movetovaultlocationname`,
`newvaultlocationid`.`stl_name` AS `sti_newvaultlocationname`,
`partner`.`par_name` AS `sti_partnername`,
`partner`.`par_code` AS `sti_partnercode`,
`product`.`pdt_name` AS `sti_productname`,
`product`.`pdt_code` AS `sti_productcode`,
`createdby`.`usr_name` AS `sti_createdbyname`,
`modifiedby`.`usr_name` AS `sti_modifiedbyname` 
from (((((((`vaultitem` left join `vaultlocation` on((`vaultlocation`.`stl_id` = `vaultitem`.`sti_vaultlocationid`))) left join `vaultlocation` `movetovaultlocationid` on((`movetovaultlocationid`.`stl_id` = `vaultitem`.`sti_movetovaultlocationid`))) left join `vaultlocation` `newvaultlocationid` on((`newvaultlocationid`.`stl_id` = `vaultitem`.`sti_newvaultlocationid`))) left join `partner` on((`partner`.`par_id` = `vaultitem`.`sti_partnerid`))) left join `product` on((`product`.`pdt_id` = `vaultitem`.`sti_productid`))) left join `user` `createdby` on((`createdby`.`usr_id` = `vaultitem`.`sti_createdby`))) left join `user` `modifiedby` on((`modifiedby`.`usr_id` = `vaultitem`.`sti_modifiedby`)))