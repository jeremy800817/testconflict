
CREATE VIEW `vw_mbbapfund` AS 
SELECT
  
  `mbbapfundtable`.*,
  `order`.`ord_xau` AS `apf_xau`,
  (`order`.`ord_xau` * `mbbapfundtable`.`apf_amount`) AS `apf_difamount`,
  `order`.`ord_orderno` AS `apf_orderno`
FROM 
	mbbapfund AS `mbbapfundtable`
LEFT JOIN `order` ON apf_orderid = `order`.ord_id
