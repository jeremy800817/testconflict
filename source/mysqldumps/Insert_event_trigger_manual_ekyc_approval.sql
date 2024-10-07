/* Add event trigger for manual approve EKYC */

INSERT INTO `eventtrigger` (`etr_id`, `etr_grouptypeid`, `etr_moduleid`, `etr_actionid`, `etr_matcherclass`, `etr_processorclass`, `etr_messageid`, `etr_observableclass`, `etr_oldstatus`, `etr_newstatus`, `etr_objectclass`, `etr_storetolog`, `etr_groupidfieldname`, `etr_evalcode`, `etr_createdon`, `etr_modifiedon`, `etr_status`, `etr_createdby`, `etr_modifiedby`) VALUES
(DEFAULT, 100, 100, 6, '\\Snap\\object\\MyGtpEventTriggerMatcher', '\\Snap\\object\\MyGtpEmailEventProcessor', 13, '\\Snap\\manager\\MyGtpAccountManager', 7, 1, '\\Snap\\object\\MyAccountHolder', '1', '', '', '2022-05-13 07:58:42', '2022-05-13 08:11:22', 1, 11, 11);
