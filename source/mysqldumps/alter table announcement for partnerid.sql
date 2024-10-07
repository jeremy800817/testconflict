--
-- Table structure for table `announcement`
--
CREATE TABLE `announcement` (
  `ann_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ann_partnerid` bigint(20) DEFAULT NULL,
  `ann_code` varchar(20) CHARACTER SET utf8 NOT NULL,
  `ann_title` varchar(127) CHARACTER SET utf8 NOT NULL,
  `ann_description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ann_content` varchar(127) CHARACTER SET utf8 DEFAULT NULL,
  `ann_picture` bigint(20) NOT NULL,
  `ann_rank` bigint(20) NOT NULL,
  `ann_type` enum('Push','Announcement') CHARACTER SET utf8 DEFAULT NULL,
  `ann_status` bigint(20) NOT NULL,
  `ann_displaystarton` datetime NOT NULL,
  `ann_displayendon` datetime NOT NULL,
  `ann_timer` bigint(20)  DEFAULT NULL,
  `ann_createdon` datetime NOT NULL,
  `ann_modifiedon` datetime NOT NULL,
  `ann_createdby` bigint(20) NOT NULL,
  `ann_modifiedby` bigint(20) NOT NULL,
   PRIMARY KEY (`ann_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
