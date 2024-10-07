CREATE OR REPLACE VIEW `vw_myaccountholder` AS SELECT 
account.*,
occupationcategory.`occ_category` as ach_occupationcategory,
occupationsubcategory.`osc_code` as ach_occupationsubcategory,
`partner`.`par_code` as ach_partnercode,
`partner`.`par_name` as ach_partnername,
`partner`.`par_parent` as ach_partnerparent,
bank.`bnk_code` as ach_bankcode,
bank.`bnk_name` as ach_bankname,
address.`add_line1` as ach_addressline1,
address.`add_line2` as ach_addressline2,
address.`add_city` as ach_addresscity,
address.`add_postcode` as ach_addresspostcode,
address.`add_state` as ach_addressstate,
ledger.`led_xaubalance` as ach_xaubalance,
ledger.`led_amountbalance` as ach_amountbalance
from `myaccountholder` as account
left join `partner` on (`partner`.`par_id` = account.`ach_partnerid`)
left join `mybank` as bank on (bank.`bnk_id` = account.`ach_bankid`)
left join `myaddress` as address on (address.`add_accountholderid` = account.`ach_id`)
left join `myoccupationcategory` as occupationcategory on (occupationcategory.`occ_id` = account.`ach_occupationcategoryid`)
left join `myoccupationsubcategory` as occupationsubcategory on (occupationsubcategory.`osc_id` = account.`ach_occupationsubcategoryid`)
left join (
  SELECT
    ledger.led_accountholderid,
    SUM(ledger.led_credit - CASE 
      WHEN cvn.cvn_type = 'CONVERSION_FEE' THEN 0
      ELSE ledger.led_debit 
    END) AS led_xaubalance,
    SUM(CASE 
      WHEN ledger.led_type = 'BUY_FPX' THEN ord.ord_amount+COALESCE(ord.ord_fee,0)
      ELSE 0 
    END - CASE 
      WHEN cvn.cvn_type = 'CONVERSION_FEE' THEN cvn.cvn_amount
      WHEN ledger.led_type = 'SELL' THEN ord.ord_amount+COALESCE(ord.ord_fee,0)
      ELSE 0 
    END) AS led_amountbalance
  FROM `myledger` AS ledger
  LEFT JOIN `mygoldtransaction` AS gtr ON ((ledger.`led_type` = 'BUY_FPX' OR ledger.`led_type` = 'SELL') AND gtr.`gtr_id` = ledger.`led_typeid`)
  LEFT JOIN `order` AS ord ON ((ledger.`led_type` = 'BUY_FPX' OR ledger.`led_type` = 'SELL')  AND gtr.gtr_orderid = ord.ord_id)
  LEFT JOIN (
    SELECT cvn_id, rdm_amount AS cvn_amount, rdm_type AS cvn_type FROM myconversion AS cvn LEFT JOIN (
      SELECT rdm_id, rdm_redemptionfee+rdm_insurancefee+rdm_handlingfee AS rdm_amount, 'CONVERSION_FEE' AS rdm_type FROM redemption
      UNION ALL
      SELECT rdm_id, rdm_totalweight, 'CONVERSION' FROM redemption
    ) AS rdm ON cvn.cvn_redemptionid = rdm.rdm_id
  ) as cvn ON (ledger.`led_type` = 'CONVERSION' AND cvn.`cvn_id` = ledger.`led_typeid`) GROUP BY led_accountholderid
) AS ledger ON ledger.led_accountholderid = account.ach_id;
