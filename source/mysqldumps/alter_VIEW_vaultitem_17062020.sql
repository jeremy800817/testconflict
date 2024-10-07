DELIMITER $$

ALTER ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_vaultitem` AS 
SELECT
  `vaultitem`.`sti_id`                    AS `sti_id`,
  `vaultitem`.`sti_partnerid`             AS `sti_partnerid`,
  `vaultitem`.`sti_vaultlocationid`       AS `sti_vaultlocationid`,
  `vaultitem`.`sti_productid`             AS `sti_productid`,
  `vaultitem`.`sti_weight`                AS `sti_weight`,
  `vaultitem`.`sti_brand`                 AS `sti_brand`,
  `vaultitem`.`sti_serialno`              AS `sti_serialno`,
  `vaultitem`.`sti_allocated`             AS `sti_allocated`,
  `vaultitem`.`sti_allocatedon`           AS `sti_allocatedon`,
  `vaultitem`.`sti_movetovaultlocationid` AS `sti_movetovaultlocationid`,
  `vaultitem`.`sti_moverequestedon`       AS `sti_moverequestedon`,
  `vaultitem`.`sti_movecompletedon`       AS `sti_movecompletedon`,
  `vaultitem`.`sti_returnedon`            AS `sti_returnedon`,
  `vaultitem`.`sti_newvaultlocationid`    AS `sti_newvaultlocationid`,
  `vaultitem`.`sti_createdon`             AS `sti_createdon`,
  `vaultitem`.`sti_createdby`             AS `sti_createdby`,
  `vaultitem`.`sti_modifiedon`            AS `sti_modifiedon`,
  `vaultitem`.`sti_modifiedby`            AS `sti_modifiedby`,
  `vaultitem`.`sti_status`                AS `sti_status`,
  `vaultlocation`.`stl_name`              AS `sti_vaultlocationname`,
  `vaultlocation`.`stl_type`              AS `sti_vaultlocationtype`,
  `vaultlocation`.`stl_defaultlocation`   AS `sti_vaultlocationdefault`,
  (SELECT
     `partner`.`par_name`
   FROM `partner`
   WHERE (`partner`.`par_id` = `vaultitem`.`sti_partnerid`)) AS `sti_partnername`,
  (SELECT
     `partner`.`par_code`
   FROM `partner`
   WHERE (`partner`.`par_id` = `vaultitem`.`sti_partnerid`)) AS `sti_partnercode`,
  (SELECT
     `product`.`pdt_code`
   FROM `product`
   WHERE (`product`.`pdt_id` = `vaultitem`.`sti_productid`)) AS `sti_productcode`,
  (SELECT
     `product`.`pdt_name`
   FROM `product`
   WHERE (`product`.`pdt_id` = `vaultitem`.`sti_productid`)) AS `sti_productname`,
  (SELECT
     `user`.`usr_name`
   FROM `user`
   WHERE (`user`.`usr_id` = `vaultitem`.`sti_createdby`)) AS `sti_createdbyname`,
  (SELECT
     `user`.`usr_name`
   FROM `user`
   WHERE (`user`.`usr_id` = `vaultitem`.`sti_modifiedby`)) AS `sti_modifiedbyname`
FROM (`vaultitem`
   LEFT JOIN `vaultlocation`
     ON ((`vaultlocation`.`stl_id` = `vaultitem`.`sti_vaultlocationid`)))$$

DELIMITER ;