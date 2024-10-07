ALTER TABLE priceadjuster 
ADD COLUMN paj_usepercent smallint(6) COMMENT '0 = Not percentage, 1 = Use Percentage for spread' AFTER paj_tier,
ADD COLUMN paj_buypercent  decimal(5,2) DEFAULT NULL COMMENT 'If Use Percent is 1, use this' AFTER paj_usepercent,
ADD COLUMN paj_sellpercent  decimal(5,2) DEFAULT NULL COMMENT 'If Use Percent is 1, use this' AFTER paj_buypercent;

/* View Table */

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`gateway` SQL SECURITY DEFINER VIEW `vw_priceadjuster` AS select `priceadjuster`.`paj_id` AS `paj_id`,`priceadjuster`.`paj_priceproviderid` AS `paj_priceproviderid`,`priceadjuster`.`paj_uuid` AS `paj_uuid`,`priceadjuster`.`paj_fxbuypremium` AS `paj_fxbuypremium`,`priceadjuster`.`paj_fxsellpremium` AS `paj_fxsellpremium`,`priceadjuster`.`paj_buymargin` AS `paj_buymargin`,`priceadjuster`.`paj_sellmargin` AS `paj_sellmargin`,`priceadjuster`.`paj_refinefee` AS `paj_refinefee`,`priceadjuster`.`paj_supplierpremium` AS `paj_supplierpremium`,`priceadjuster`.`paj_buyspread` AS `paj_buyspread`,`priceadjuster`.`paj_sellspread` AS `paj_sellspread`,`priceadjuster`.`paj_effectiveon` AS `paj_effectiveon`,`priceadjuster`.`paj_effectiveendon` AS `paj_effectiveendon`,
`priceadjuster`.`paj_tier` AS `paj_tier`,
`priceadjuster`.`paj_usepercent` AS `paj_usepercent`,
`priceadjuster`.`paj_buypercent` AS `paj_buypercent`,
`priceadjuster`.`paj_sellpercent` AS `paj_sellpercent`,
`priceadjuster`.`paj_createdon` AS `paj_createdon`,`priceadjuster`.`paj_createdby` AS `paj_createdby`,`priceprovider`.`prp_name` AS `paj_priceprovidername`,`createdby`.`usr_name` AS `paj_createdbyname` from ((`priceadjuster` left join `priceprovider` on((`priceprovider`.`prp_id` = `priceadjuster`.`paj_priceproviderid`))) left join `user` `createdby` on((`createdby`.`usr_id` = `priceadjuster`.`paj_createdby`)))