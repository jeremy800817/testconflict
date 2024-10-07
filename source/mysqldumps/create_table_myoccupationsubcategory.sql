-- Updates to table

-- Dumping structure for table gtp.occupationsubcategory
DROP TABLE IF EXISTS `myoccupationsubcategory`;
CREATE TABLE IF NOT EXISTS `myoccupationsubcategory` (
  `osc_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `osc_occupationcategoryid` bigint(20) NOT NULL,
  `osc_code` varchar(255) NOT NULL,
  `osc_politicallyexposed` smallint(6) NOT NULL,
  `osc_status` smallint(6) NOT NULL,
  `osc_createdon` datetime NOT NULL,
  `osc_createdby` bigint(20) NOT NULL,
  `osc_modifiedon` datetime NOT NULL,
  `osc_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`osc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Dumping structure for table gtp.accountholder
DROP TABLE IF EXISTS `myaccountholder`;
CREATE TABLE IF NOT EXISTS `myaccountholder` (
  `ach_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ach_partnerid` bigint(20) NOT NULL,
  `ach_preferredlang` enum('EN','MS','ZH') DEFAULT 'EN',
  -- `ach_firstname` varchar(255) NOT NULL,
  -- `ach_middlename` varchar(255) DEFAULT NULL,
  -- `ach_lastname` varchar(255) NOT NULL,
  `ach_fullname` varchar(255) NOT NULL,
  `ach_accountholdercode` varchar(20) NOT NULL,
  `ach_email` varchar(50) NOT NULL,
  `ach_password` varchar(255) NOT NULL,
  `ach_oldpassword` varchar(255) DEFAULT NULL,
  `ach_mykadno` varchar(25) NOT NULL,
  `ach_phoneno` varchar(25) NOT NULL,
  --`ach_occupation` varchar(255) NOT NULL,
  `ach_occupationcategoryid` bigint(20) NOT NULL,
  `ach_occupationsubcategoryid` bigint(20) DEFAULT NULL,
  `ach_referralbranchcode` varchar(255) DEFAULT NULL,
  `ach_referralsalespersoncode` varchar(255) DEFAULT NULL,
  `ach_pincode` varchar(255) DEFAULT NULL,
  `ach_sapacebuycode` varchar(15) DEFAULT NULL,
  `ach_sapacesellcode` varchar(15) DEFAULT NULL,
  `ach_bankid` bigint(20) DEFAULT NULL,
  `ach_accountname` varchar(255) DEFAULT NULL,
  `ach_accountnumber` varchar(255) DEFAULT NULL,
  `ach_nokfullname` varchar(255) NOT NULL,
  `ach_nokmykadno` varchar(12) NOT NULL,
  `ach_nokphoneno` varchar(25) DEFAULT NULL,
  `ach_nokemail` varchar(50) DEFAULT NULL,
  `ach_nokaddress` varchar(255) DEFAULT NULL,
  `ach_nokrelationship` varchar(50) DEFAULT NULL,
  `ach_investmentmade` tinyint(1) DEFAULT 0,
  `ach_ispep` tinyint(1) DEFAULT 0,
  `ach_pepdeclaration` mediumtext DEFAULT NULL,
  `ach_kycstatus` smallint(6) DEFAULT 0,
  `ach_amlastatus` smallint(6) DEFAULT 0,
  `ach_pepstatus` smallint(6) DEFAULT 0,
  `ach_statusremarks` varchar(255) DEFAULT NULL,
  `ach_emailverifiedon` DATETIME DEFAULT NULL,
  `ach_phoneverifiedon` DATETIME DEFAULT NULL,
  `ach_status` smallint(6) NOT NULL,
  `ach_passwordmodified` datetime DEFAULT NULL,
  `ach_lastloginon` datetime DEFAULT NULL,
  `ach_lastloginip` varchar(15) DEFAULT NULL,
  `ach_verifiedon` datetime DEFAULT NULL,
  `ach_createdon` datetime NOT NULL,
  `ach_createdby` bigint(20) NOT NULL,
  `ach_modifiedon` datetime NOT NULL,
  `ach_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`ach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Dumping structure for table gtp.localizedcontent
DROP TABLE IF EXISTS `mylocalizedcontent`;
CREATE TABLE IF NOT EXISTS `mylocalizedcontent` (
  `loc_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `loc_sourcetype` enum('ANNOUNCEMENT','PUSH_NOTI','DOCUMENTATION','OCCUPATION_CAT', 'OCCUPATION_SUBCAT', 'CLOSE_REASON') NOT NULL,
  `loc_sourceid` bigint(20) NOT NULL,
  `loc_data` json NOT NULL,
  `loc_language` enum('EN','MS','ZH') NOT NULL,
  `loc_status` smallint(6) NOT NULL,
  `loc_createdon` datetime NOT NULL,
  `loc_createdby` bigint(20) NOT NULL,
  `loc_modifiedon` datetime NOT NULL,
  `loc_modifiedby` bigint(20) NOT NULL,
  PRIMARY KEY (`loc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Imports for event trigger
-- Blank as not yet having copywriting

INSERT INTO `eventtrigger` (`etr_id`, `etr_grouptypeid`, `etr_moduleid`, `etr_actionid`, `etr_matcherclass`, `etr_processorclass`, `etr_messageid`, `etr_observableclass`, `etr_oldstatus`, `etr_newstatus`, `etr_objectclass`, `etr_storetolog`, `etr_groupidfieldname`, `etr_evalcode`, `etr_createdon`, `etr_modifiedon`, `etr_status`, `etr_createdby`, `etr_modifiedby`) VALUES
(DEFAULT, 100, 100, 11, '\\Snap\\object\\MyGtpEventTriggerMatcher', '\\Snap\\object\\EmailEventProcessor', 9, '\\Snap\\manager\\MyGtpAccountManager', 1, 1, '\\Snap\\object\\MyAccountHolder', '1', 'partnerid', '', '2021-04-26 09:28:30', '2021-04-28 11:29:17', 1, 11, 11),
(DEFAULT, 100, 100, 11, '\\Snap\\object\\MyGtpEventTriggerMatcher', '\\Snap\\object\\EmailEventProcessor', 9, '\\Snap\\manager\\MyGtpAccountManager', 0, 0, '\\Snap\\object\\MyAccountHolder', '1', 'partnerid', '', '2021-04-26 10:48:39', '2021-04-28 11:28:55', 1, 11, 11);



INSERT INTO `eventmessage` (`evm_id`, `evm_code`, `evm_replace`, `evm_subject`, `evm_content`, `evm_createdon`, `evm_modifiedon`, `evm_createdby`, `evm_modifiedby`, `evm_status`) VALUES
(DEFAULT, 'PeP Occupation Check', '##ACCOUNTHOLDERID##||##OTHERPARAM_accountholderid##,##MYKADNO##||##OTHERPARAM_mykadno##,##NAME##||##OTHERPARAM_name##,##EMAIL##||##OTHERPARAM_email##,##CODE##||##OTHERPARAM_code##,##OCCUPATION##||##OTHERPARAM_occupation##,##SUBOCCUPATION##||##OTHERPARAM_suboccupation##,##ACCOUNTCODE##||##OTHERPARAM_accountholdercode##', 'PeP Occupation Check for ##ACCOUNTCODE##', '<div style=\"\"><div style=\"\"><font face=\"Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif\"><span style=\"font-size: 13px;\">Dear Compliance Officer,</span></font></div><div style=\"\"><font face=\"Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif\"><span style=\"font-size: 13px;\"><br></span></font></div><div style=\"\"><font face=\"Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif\"><span style=\"font-size: 13px;\">A registered user</span></font><span style=\"font-size: 13px; font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, helvetica, arial, verdana, sans-serif;\">&nbsp;has fall under PeP occupation check which requires your further checking &amp; perusal.</span></div><div style=\"\"><font face=\"Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif\"><span style=\"font-size: 13px;\"><br></span></font></div><div style=\"\"><font face=\"Open Sans, Helvetica Neue, helvetica, arial, verdana, sans-serif\"><span style=\"font-size: 13px;\">Details:</span></font></div><div style=\"\"><ul style=\"font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, helvetica, arial, verdana, sans-serif; font-size: 13px;\"><li>Full Name&nbsp;: ##NAME##</li><li>NRIC No&nbsp;: ##MYKADNO##</li><li>Occupation: ##OCCUPATION##</li><li>Sub Occupation: ##SUBOCCUPATION##</li><li>User ID: ##ACCOUNTCODE##</li></ul></div></div><div style=\"color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, helvetica, arial, verdana, sans-serif; font-size: 13px;\">The account registered below is PEP</div>', '2021-04-28 11:02:08', '2021-04-30 11:30:18', 11, 11, 0);