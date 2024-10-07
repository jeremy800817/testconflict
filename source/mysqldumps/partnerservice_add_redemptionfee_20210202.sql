ALTER TABLE partnerservice ADD pas_redemptionpremiumfee decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptionpremiumfee pas_redemptionpremiumfee decimal(20,6) NULL AFTER pas_premiumfee;
ALTER TABLE partnerservice ADD pas_redemptioncommission decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptioncommission pas_redemptioncommission decimal(20,6) NULL AFTER pas_redemptionpremiumfee;