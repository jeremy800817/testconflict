ALTER TABLE apilogs MODIFY COLUMN api_type enum('NewPriceStream','NewPriceValidation','SapOrder','SapCancelOrder','SapGenerateGrn','SapGoldSerialRequest','ApiAllocateXau','ApiGetPrice','ApiNewBooking','ApiConfirmBooking','ApiCancelBooking','ApiRedemption','MYGTP','MYGTP_FPX','MYGTP_EKYC','MYGTP_WALLET') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, ALGORITHM=INPLACE, LOCK=NONE;
ALTER TABLE partnerservice 
ADD pas_redemptionpremiumfee decimal(20,6) NULL AFTER pas_premiumfee,
ADD pas_redemptioncommission decimal(20,6) NULL AFTER pas_redemptionpremiumfee,
ADD pas_redemptioninsurancefee decimal(20,6) NULL AFTER pas_redemptioncommission;
