ALTER TABLE `mbbapfund` ADD `apf_p1priceon` DATETIME NULL AFTER `apf_p1pricestreamid`;
ALTER TABLE `mbbapfund` ADD `apf_p2priceon` DATETIME NULL AFTER `apf_p2pricestreamid`;
ALTER TABLE `mbbapfund` ADD `apf_p3priceon` DATETIME NULL AFTER `apf_p3pricestreamid`;

DELIMITER $$

ALTER ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_mbbapfund` AS 
SELECT
  `mbbapfund`.`apf_id`              AS `apf_id`,
  `mbbapfund`.`apf_partnerid`       AS `apf_partnerid`,
  `mbbapfund`.`apf_operationtype`   AS `apf_operationtype`,
  `mbbapfund`.`apf_orderid`         AS `apf_orderid`,
  `mbbapfund`.`apf_p1buyprice`      AS `apf_p1buyprice`,
  `mbbapfund`.`apf_p1sellprice`     AS `apf_p1sellprice`,
  `mbbapfund`.`apf_p2buyprice`      AS `apf_p2buyprice`,
  `mbbapfund`.`apf_p2sellprice`     AS `apf_p2sellprice`,
  `mbbapfund`.`apf_p3buyprice`      AS `apf_p3buyprice`,
  `mbbapfund`.`apf_p3sellprice`     AS `apf_p3sellprice`,
  `mbbapfund`.`apf_p1pricestreamid` AS `apf_p1pricestreamid`,
  `mbbapfund`.`apf_p2pricestreamid` AS `apf_p2pricestreamid`,
  `mbbapfund`.`apf_p3pricestreamid` AS `apf_p3pricestreamid`,
  `mbbapfund`.`apf_beginprice`      AS `apf_beginprice`,
  `mbbapfund`.`apf_beginpriceid`    AS `apf_beginpriceid`,
  `mbbapfund`.`apf_endprice`        AS `apf_endprice`,
  `mbbapfund`.`apf_endpriceid`      AS `apf_endpriceid`,
  `mbbapfund`.`apf_amountppg`       AS `apf_amountppg`,
  `mbbapfund`.`apf_amount`          AS `apf_amount`,
  `mbbapfund`.`apf_createdon`       AS `apf_createdon`,
  `mbbapfund`.`apf_createdby`       AS `apf_createdby`,
  `mbbapfund`.`apf_modifiedon`      AS `apf_modifiedon`,
  `mbbapfund`.`apf_modifiedby`      AS `apf_modifiedby`,
  `mbbapfund`.`apf_status`          AS `apf_status`,
  `mbbapfund`.`apf_remarks`         AS `apf_remarks`,
  `partner`.`par_name`              AS `apf_partnername`,
  `partner`.`par_code`              AS `apf_partnercode`,
  `mbbapfund`.`apf_p1priceon`       AS `apf_p1priceon`,
  `mbbapfund`.`apf_p2priceon`       AS `apf_p2priceon`,
  `mbbapfund`.`apf_p3priceon`       AS `apf_p3priceon`,
  `order`.`ord_xau`                 AS `apf_orderxau`,
  `order`.`ord_orderno`             AS `apf_orderno`,
  `order`.`ord_type`                AS `apf_ordertype`,
  `order`.`ord_createdon`           AS `apf_ordercreatedon`
FROM ((`mbbapfund`
    LEFT JOIN `partner`
      ON ((`partner`.`par_id` = `mbbapfund`.`apf_partnerid`)))
   LEFT JOIN `order`
     ON ((`order`.`ord_id` = `mbbapfund`.`apf_orderid`)))$$

DELIMITER ;