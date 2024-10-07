ALTER TABLE `mygoldtransaction` CHANGE COLUMN `gtr_settlementmethod` `gtr_settlementmethod` ENUM('FPX','CONTAINER','BANK_ACCOUNT', 'WALLET') NOT NULL AFTER `gtr_originalamount`;

ALTER TABLE `myconversion` CHANGE COLUMN `cvn_logisticfeepaymentmode` `cvn_logisticfeepaymentmode` ENUM('CONTAINER','FPX', 'GOLD', 'WALLET') NOT NULL AFTER `cvn_premiumfee`;

ALTER TABLE `apilogs` CHANGE COLUMN `api_type` `api_type` ENUM('NewPriceStream','NewPriceValidation','SapOrder','SapCancelOrder','SapGenerateGrn','SapGoldSerialRequest','ApiAllocateXau','ApiGetPrice','ApiNewBooking','ApiConfirmBooking','ApiCancelBooking','ApiRedemption','MYGTP','MYGTP_FPX','MYGTP_EKYC', 'MYGTP_WALLET') NOT NULL AFTER `api_id`;