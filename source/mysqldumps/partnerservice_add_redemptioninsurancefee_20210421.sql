ALTER TABLE partnerservice ADD pas_redemptioninsurancefee decimal(20,6) NULL;
ALTER TABLE partnerservice CHANGE pas_redemptioninsurancefee pas_redemptioninsurancefee decimal(20,6) NULL AFTER pas_redemptioncommission;