
/* Alter price provider table */
alter table priceprovider add column `prp_providergroupid` bigint after `prp_productcategoryid`

/* Alter price provider view */
CREATE OR REPLACE VIEW `vw_priceprovider` AS select `priceprovider`.`prp_id` AS `prp_id`,`priceprovider`.`prp_code` AS `prp_code`,`priceprovider`.`prp_name` AS `prp_name`,`priceprovider`.`prp_pricesourceid` AS `prp_pricesourceid`,`priceprovider`.`prp_productcategoryid` AS `prp_productcategoryid`,
`priceprovider`.`prp_providergroupid` AS `prp_providergroupid`,
`priceprovider`.`prp_pullmode` AS `prp_pullmode`,`priceprovider`.`prp_currencyid` AS `prp_currencyid`,`priceprovider`.`prp_whitelistip` AS `prp_whitelistip`,`priceprovider`.`prp_url` AS `prp_url`,`priceprovider`.`prp_connectinfo` AS `prp_connectinfo`,`priceprovider`.`prp_lapsetimeallowance` AS `prp_lapsetimeallowance`,`priceprovider`.`prp_futureorderstrategy` AS `prp_futureorderstrategy`,`priceprovider`.`prp_futureorderparams` AS `prp_futureorderparams`,`priceprovider`.`prp_createdon` AS `prp_createdon`,`priceprovider`.`prp_createdby` AS `prp_createdby`,`priceprovider`.`prp_modifiedon` AS `prp_modifiedon`,`priceprovider`.`prp_modifiedby` AS `prp_modifiedby`,`priceprovider`.`prp_status` AS `prp_status`,`priceprovider`.`prp_index` AS `prp_index`,
productcategorytag.`tag_code` AS `prp_productcategoryname`,
currencytag.`tag_code` AS `prp_currencycode`,
pricegrouptag.`tag_code` AS `prp_providergroupcode`,`createdby`.`usr_name` AS `prp_createdbyname`,`modifiedby`.`usr_name` AS `prp_modifiedbyname` 
from (((`priceprovider` left join `tag` as productcategorytag on((productcategorytag.`tag_id` = `priceprovider`.`prp_productcategoryid`))) left join `user` `createdby` on((`createdby`.`usr_id` = `priceprovider`.`prp_createdby`))) left join `user` `modifiedby` on((`modifiedby`.`usr_id` = `priceprovider`.`prp_modifiedby`)))
left join `tag` as currencytag on((currencytag.`tag_id` = `priceprovider`.`prp_currencyid`))
left join `tag` as pricegrouptag on((pricegrouptag.`tag_id` = `priceprovider`.`prp_providergroupid`)) 


/* Insert fields to tag */
INSERT INTO `tag` (`tag_id`, `tag_category`, `tag_code`, `tag_description`, `tag_value`, `tag_createdon`, `tag_createdby`, `tag_modifiedon`, `tag_modifiedby`, `tag_status`) VALUES
(DEFAULT, 'PriceProvider', 'PriceProvider:Dealer', 'User log in to buy and sell', 'Dealer', '2022-09-05 16:53:48', 1, '2022-09-05 16:53:48', 1, 1),
(DEFAULT, 'PriceProvider', 'PriceProvider:Banking', 'All banks (eg: Miga/Easigold)', 'Banking', '2022-09-05 16:53:48', 1, '2022-09-05 16:53:48', 10, 1),

(DEFAULT, 'PriceProvider', 'PriceProvider:Koperasi', 'All Koperasi and Subsidiaries (eg: KGOLD, Kopetrogold)', 'Koperasi', '2022-09-05 16:53:48', 1, '2022-09-05 16:53:48', 1, 1),
(DEFAULT, 'PriceProvider', 'PriceProvider:E-wallet', 'Platform with E-wallet (eg: GoGold/Mgold)', 'E-wallet', '2022-09-05 16:53:48', 1, '2022-09-05 16:53:48', 10, 1),
(DEFAULT, 'PriceProvider', 'PriceProvider:Glc', 'User log in to buy and sell', 'GLC', '2022-09-05 16:53:48', 1, '2022-09-05 16:53:48', 1, 1),
(DEFAULT, 'PriceProvider', 'PriceProvider:Buyback', 'Bank Buyback (eg: Pos Arahnu/Agro Bank)', 'Buyback', '2022-09-05 16:53:48', 1, '2022-09-05 16:53:48', 10, 1),
(DEFAULT, 'PriceProvider', 'PriceProvider:Others', 'Others (eg: AirGold/NusaGold)', 'Others', '2022-09-05 16:53:48', 1, '2022-09-05 16:53:48', 1, 1);

