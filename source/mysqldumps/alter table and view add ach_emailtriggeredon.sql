
/** Update db **/
/* 1st alter table add emailtriggeredon
2nd add alter view to include emailtriggered on in vw_accountholder */

ALTER TABLE `myaccountholder` ADD `ach_emailtriggeredon`  DATETIME DEFAULT NULL AFTER `ach_statusremarks`;

CREATE OR REPLACE VIEW `vw_myaccountholder` AS SELECT 
`account`.`ach_id` AS `ach_id`,`account`.`ach_partnerid` AS `ach_partnerid`,`account`.`ach_preferredlang` AS `ach_preferredlang`,`account`.`ach_fullname` AS `ach_fullname`,`account`.`ach_partnercusid` AS `ach_partnercusid`,`account`.`ach_accountholdercode` AS `ach_accountholdercode`,`account`.`ach_email` AS `ach_email`,`account`.`ach_password` AS `ach_password`,`account`.`ach_oldpassword` AS `ach_oldpassword`,`account`.`ach_mykadno` AS `ach_mykadno`,`account`.`ach_phoneno` AS `ach_phoneno`,`account`.`ach_occupationcategoryid` AS `ach_occupationcategoryid`,`account`.`ach_occupationsubcategoryid` AS `ach_occupationsubcategoryid`,`account`.`ach_referralbranchcode` AS `ach_referralbranchcode`,`account`.`ach_referralsalespersoncode` AS `ach_referralsalespersoncode`,`account`.`ach_pincode` AS `ach_pincode`,`account`.`ach_sapacebuycode` AS `ach_sapacebuycode`,`account`.`ach_sapacesellcode` AS `ach_sapacesellcode`,`account`.`ach_bankid` AS `ach_bankid`,`account`.`ach_accountname` AS `ach_accountname`,`account`.`ach_accountnumber` AS `ach_accountnumber`,`account`.`ach_nokfullname` AS `ach_nokfullname`,`account`.`ach_nokmykadno` AS `ach_nokmykadno`,`account`.`ach_nokphoneno` AS `ach_nokphoneno`,`account`.`ach_nokemail` AS `ach_nokemail`,`account`.`ach_nokaddress` AS `ach_nokaddress`,`account`.`ach_nokrelationship` AS `ach_nokrelationship`,`account`.`ach_investmentmade` AS `ach_investmentmade`,`account`.`ach_ispep` AS `ach_ispep`,`account`.`ach_pepdeclaration` AS `ach_pepdeclaration`,`account`.`ach_kycstatus` AS `ach_kycstatus`,`account`.`ach_amlastatus` AS `ach_amlastatus`,`account`.`ach_pepstatus` AS `ach_pepstatus`,`account`.`ach_statusremarks` AS `ach_statusremarks`,`account`.`ach_emailverifiedon` AS `ach_emailverifiedon`,`account`.`ach_phoneverifiedon` AS `ach_phoneverifiedon`,`account`.`ach_status` AS `ach_status`,`account`.`ach_passwordmodified` AS `ach_passwordmodified`,`account`.`ach_lastnotifiedon` AS `ach_lastnotifiedon`,`account`.`ach_lastloginon` AS `ach_lastloginon`,`account`.`ach_lastloginip` AS `ach_lastloginip`,
`account`.`ach_verifiedon` AS `ach_verifiedon`,
`account`.`ach_emailtriggeredon` AS `ach_emailtriggeredon`,
`account`.`ach_promocode` AS `ach_promocode`,
`account`.`ach_campaigncode` AS `ach_campaigncode`,`account`.`ach_type` AS `ach_type`,`account`.`ach_createdon` AS `ach_createdon`,`account`.`ach_createdby` AS `ach_createdby`,`account`.`ach_modifiedon` AS `ach_modifiedon`,`account`.`ach_modifiedby` AS `ach_modifiedby`,`occupationcategory`.`occ_category` AS `ach_occupationcategory`,`occupationsubcategory`.`osc_code` AS `ach_occupationsubcategory`,`partner`.`par_code` AS `ach_partnercode`,`partner`.`par_name` AS `ach_partnername`,`bank`.`bnk_code` AS `ach_bankcode`,`bank`.`bnk_name` AS `ach_bankname`,`address`.`add_line1` AS `ach_addressline1`,`address`.`add_line2` AS `ach_addressline2`,`address`.`add_city` AS `ach_addresscity`,`address`.`add_postcode` AS `ach_addresspostcode`,`address`.`add_state` AS `ach_addressstate`,`ledger`.`led_xaubalance` AS `ach_xaubalance`,`ledger`.`led_amountbalance` AS `ach_amountbalance` from ((((((`myaccountholder` `account` left join `partner` on((`partner`.`par_id` = `account`.`ach_partnerid`))) left join `mybank` `bank` on((`bank`.`bnk_id` = `account`.`ach_bankid`))) left join `myaddress` `address` on((`address`.`add_accountholderid` = `account`.`ach_id`))) left join `myoccupationcategory` `occupationcategory` on((`occupationcategory`.`occ_id` = `account`.`ach_occupationcategoryid`))) left join `myoccupationsubcategory` `occupationsubcategory` on((`occupationsubcategory`.`osc_id` = `account`.`ach_occupationsubcategoryid`))) left join (select `ledger`.`led_accountholderid` AS `led_accountholderid`,sum((`ledger`.`led_credit` - (case when (`cvn`.`cvn_type` = 'CONVERSION_FEE') then 0 else `ledger`.`led_debit` end))) AS `led_xaubalance`,sum(((case when (`ledger`.`led_type` = 'BUY_FPX') then (`ord`.`ord_amount` + coalesce(`ord`.`ord_fee`,0)) else 0 end) - (case when (`cvn`.`cvn_type` = 'CONVERSION_FEE') then `cvn`.`cvn_amount` when (`ledger`.`led_type` = 'SELL') then (`ord`.`ord_amount` + coalesce(`ord`.`ord_fee`,0)) else 0 end))) AS `led_amountbalance` from (((`myledger` `ledger` left join `mygoldtransaction` `gtr` on((((`ledger`.`led_type` = 'BUY_FPX') or (`ledger`.`led_type` = 'SELL')) and (`gtr`.`gtr_id` = `ledger`.`led_typeid`)))) left join `order` `ord` on((((`ledger`.`led_type` = 'BUY_FPX') or (`ledger`.`led_type` = 'SELL')) and (`gtr`.`gtr_orderid` = `ord`.`ord_id`)))) left join (select `cvn`.`cvn_id` AS `cvn_id`,`rdm`.`rdm_amount` AS `cvn_amount`,`rdm`.`rdm_type` AS `cvn_type` from (`myconversion` `cvn` left join (select `redemption`.`rdm_id` AS `rdm_id`,((`redemption`.`rdm_redemptionfee` + `redemption`.`rdm_insurancefee`) + `redemption`.`rdm_handlingfee`) AS `rdm_amount`,'CONVERSION_FEE' AS `rdm_type` from `redemption` union all select `redemption`.`rdm_id` AS `rdm_id`,`redemption`.`rdm_totalweight` AS `rdm_totalweight`,'CONVERSION' AS `CONVERSION` from `redemption`) `rdm` on((`cvn`.`cvn_redemptionid` = `rdm`.`rdm_id`)))) `cvn` on(((`ledger`.`led_type` = 'CONVERSION') and (`cvn`.`cvn_id` = `ledger`.`led_typeid`)))) where (`ledger`.`led_status` = 1) group by `ledger`.`led_accountholderid`) `ledger` on((`ledger`.`led_accountholderid` = `account`.`ach_id`)))