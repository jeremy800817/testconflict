CREATE OR REPLACE VIEW `vw_mytransfergold` AS SELECT 
`mytransfergold`.*,
`fromaccount`.`ach_fullname` AS `gtb_fromfullname`,
`fromaccount`.`ach_accountholdercode` AS `gtb_fromaccountholdercode`,
`toaccount`.`ach_fullname` AS `gtb_tofullname`,
`toaccount`.`ach_accountholdercode` AS `gtb_toaccountholdercode`,
`partner`.`par_id` AS `gtb_frompartnerid`,
`partner`.`par_code` AS `gtb_partnercode`,
`partner`.`par_name` AS `gtb_partnername`,
`createdby`.`usr_name` AS `gtb_createdbyname`,
`modifiedby`.`usr_name` AS `gtb_modifiedbyname`
FROM `mytransfergold` 
LEFT JOIN `myaccountholder` as fromaccount ON fromaccount.ach_id = `mytransfergold`.`gtb_fromaccountholderid`
LEFT JOIN `myaccountholder` as toaccount ON toaccount.ach_id = `mytransfergold`.`gtb_toaccountholderid`
LEFT JOIN `partner` ON `partner`.`par_id` = `fromaccount`.`ach_partnerid`
left join `user` AS createdby on (createdby.usr_id = `mytransfergold`.`gtb_createdby`)
left join `user` AS modifiedby on (modifiedby.usr_id = `mytransfergold`.`gtb_modifiedby`)
GROUP BY `mytransfergold`.`gtb_id`,`mytransfergold`.`gtb_accountholderid`;
