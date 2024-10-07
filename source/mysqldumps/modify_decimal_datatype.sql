ALTER TABLE goods_receive_note
MODIFY COLUMN grn_totalxauexpected decimal(20,6);
ALTER TABLE goods_receive_note
MODIFY COLUMN grn_totalgrossweight decimal(20,6);
ALTER TABLE goods_receive_note
MODIFY COLUMN grn_totalxaucollected decimal(20,6);
ALTER TABLE goods_receive_note
MODIFY COLUMN grn_vatsum decimal(20,6);

ALTER TABLE mbb_apfund
MODIFY COLUMN apf_beginprice decimal(20,6);
ALTER TABLE mbb_apfund
MODIFY COLUMN apf_endprice decimal(20,6);
ALTER TABLE mbb_apfund
MODIFY COLUMN apf_amountppg decimal(20,6);
ALTER TABLE mbb_apfund
MODIFY COLUMN apf_amount decimal(20,6);

ALTER TABLE `order`
MODIFY COLUMN ord_price decimal(20,6);
ALTER TABLE `order`
MODIFY COLUMN ord_xau decimal(20,6);
ALTER TABLE `order`
MODIFY COLUMN ord_amount decimal(20,6);
ALTER TABLE `order`
MODIFY COLUMN ord_fee decimal(20,6);
ALTER TABLE `order`
MODIFY COLUMN ord_bookingprice decimal(20,6);
ALTER TABLE `order`
MODIFY COLUMN ord_confirmprice decimal(20,6);
ALTER TABLE `order`
MODIFY COLUMN ord_cancelprice decimal(20,6);

ALTER TABLE order_queue
MODIFY COLUMN orq_pricetarget decimal(20,6);
ALTER TABLE order_queue
MODIFY COLUMN orq_xau decimal(20,6);
ALTER TABLE order_queue
MODIFY COLUMN orq_amount decimal(20,6);

ALTER TABLE partnerservice
MODIFY COLUMN pas_refineryfee decimal(20,6);
ALTER TABLE partnerservice
MODIFY COLUMN pas_premiumfee decimal(20,6);

ALTER TABLE pricestream
MODIFY COLUMN pst_companybuyppg decimal(20,6);
ALTER TABLE pricestream
MODIFY COLUMN pst_companysellppg decimal(20,6);

ALTER TABLE pricevalidation
MODIFY COLUMN pva_premiumfee decimal(20,6);
ALTER TABLE pricevalidation
MODIFY COLUMN pva_refineryfee decimal(20,6);

ALTER TABLE redemption
MODIFY COLUMN rdm_redemptionfee decimal(20,6);
ALTER TABLE redemption
MODIFY COLUMN rdm_insurancefee decimal(20,6);
ALTER TABLE redemption
MODIFY COLUMN rdm_handlingfee decimal(20,6);
ALTER TABLE redemption
MODIFY COLUMN rdm_specialdeliveryfee decimal(20,6);
ALTER TABLE redemption
MODIFY COLUMN rdm_xau decimal(20,6);
ALTER TABLE redemption
MODIFY COLUMN rdm_fee decimal(20,6);
ALTER TABLE redemption
MODIFY COLUMN rdm_bookingprice decimal(20,6);
ALTER TABLE redemption
MODIFY COLUMN rdm_confirmedprice decimal(20,6);

ALTER TABLE sales_commission
MODIFY COLUMN com_totalcompanybuy decimal(20,6);
ALTER TABLE sales_commission
MODIFY COLUMN com_totalcompanysell decimal(20,6);
ALTER TABLE sales_commission
MODIFY COLUMN com_totalxau decimal(20,6);
ALTER TABLE sales_commission
MODIFY COLUMN com_totalfee decimal(20,6);

ALTER TABLE vaultitem
MODIFY COLUMN sti_weight decimal(20,6);