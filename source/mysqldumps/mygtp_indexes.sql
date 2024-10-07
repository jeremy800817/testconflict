CREATE UNIQUE INDEX sourceref USING BTREE ON gtp.mypaymentdetail (pdt_sourcerefno);
CREATE UNIQUE INDEX sourceref USING BTREE ON gtp.mydisbursement (dbm_transactionrefno);
