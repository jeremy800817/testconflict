ALTER TABLE mypartnersetting ADD COLUMN psg_sapitemcoderedeemfees varchar(15) COMMENT 'adminbuy' AFTER psg_refreshtokenlifetime;
ALTER TABLE mypartnersetting ADD COLUMN psg_sapitemcodeannualfees varchar(15) COMMENT 'adminsell' AFTER psg_sapitemcoderedeemfees;
ALTER TABLE mypartnersetting ADD COLUMN psg_sapitemcodestoragefees varchar(15) COMMENT 'storagebuy' AFTER psg_sapitemcodeannualfees;
