
ALTER TABLE buyback ADD COLUMN byb_productid BIGINT(20) AFTER byb_buybackno;
ALTER TABLE buyback ADD COLUMN byb_bookingon DATETIME AFTER byb_remarks;
ALTER TABLE buyback ADD COLUMN byb_bookingprice float(8,6) AFTER byb_bookingon;
ALTER TABLE buyback ADD COLUMN byb_bookingpricestreamid BIGINT(20) AFTER byb_bookingprice;

ALTER TABLE goodsreceivenoteorder ADD COLUMN gro_buybackid BIGINT(20) AFTER gro_orderid;


CREATE TABLE `goodreceivenotedraft` (
  `grd_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `grd_goodreceivednoteorderid` bigint(20) DEFAULT NULL,
  `grd_branchid` bigint(20) DEFAULT NULL,
  `grd_referenceno` varchar(63) DEFAULT NULL,
  `grd_product` varchar(63) DEFAULT NULL,
  `grd_purity` float(3,2) DEFAULT NULL,
  `grd_weight` float(8,6) DEFAULT NULL,
  `grd_desc` varchar(255) DEFAULT NULL,
  `grd_createdon` datetime DEFAULT NULL,
  `grd_createdby` bigint(20) DEFAULT NULL,
  `grd_modifiedon` datetime DEFAULT NULL,
  `grd_modifiedby` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`grd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE goodreceivenotedraft ADD COLUMN grd_status smallint(4) AFTER grd_modifiedby;
