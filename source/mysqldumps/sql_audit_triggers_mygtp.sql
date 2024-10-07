SET SESSION group_concat_max_len = 2048;

SET @dbName = "DBNAME";

SET @tableName = "TABLENAME";

SET @tablePrefix = "PREFIX"; 
-- table prefix eg par_, odr_ etc_

SELECT CONCAT("DROP TABLE IF EXISTS `", @dbName, "`.`", table_data.audit_table, "`;\r",
          "CREATE TABLE `", @dbName, "`.`", table_data.audit_table, "`\r",
          "(\r",
        --   "  `auditAction` ENUM ('INSERT', 'UPDATE', 'DELETE'),\r",
        --   "  `auditTimestamp` timestamp DEFAULT CURRENT_TIMESTAMP,\r",
        --   "  `auditId` INT(14) AUTO_INCREMENT,",

          "  `",@tablePrefix,"auditkey` bigint(11) unsigned NOT NULL AUTO_INCREMENT,\r",
          "  `",@tablePrefix,"action` enum('Add','Delete','Update') NOT NULL,\r",
          "  `",@tablePrefix,"actionby` mediumint(9) NOT NULL,\r",
          "  `",@tablePrefix,"actionuser` varchar(120) NOT NULL,\r",
          "  `",@tablePrefix,"actionrealby` mediumint(9) NOT NULL,\r",
          "  `",@tablePrefix,"actionrealuser` varchar(120) NOT NULL,\r",
          "  `",@tablePrefix,"actiontimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\r",

          column_defs, ",\r"
          "  PRIMARY KEY (`",@tablePrefix,"auditkey`),\r",
          "  INDEX (`",@tablePrefix,"actiontimestamp`)\r",
          ")\r",
          "  ENGINE = InnoDB;\r\r",
          "DROP TRIGGER IF EXISTS `", @dbName, "`.`", table_data.insert_trigger, "`;\r",
          "DELIMITER $$ \r",
          "CREATE TRIGGER `", @dbName, "`.`", table_data.insert_trigger, "`\r",
          "  AFTER INSERT ON `", @dbName, "`.`", table_data.db_table, "`\r",
          "  FOR EACH ROW BEGIN \r",
          "  DECLARE theDoer INT DEFAULT 0; \r",
          "  DECLARE theName VARCHAR(200) DEFAULT ''; \r",
          "  DECLARE theRealDoer INT DEFAULT 0; \r",
          "  DECLARE theRealName VARCHAR(200) DEFAULT ''; \r",
          "  SELECT IFNULL(@actionBy,0) INTO theDoer; \r",
          "  SELECT IFNULL(@actionRealBy,0) INTO theRealDoer; \r",
          "  SELECT usr_username INTO theName FROM `user` WHERE usr_id = @actionBy; \r",
          "  SELECT usr_username INTO theRealName FROM `user` WHERE usr_id = @actionBy; \r",
          "  INSERT INTO `", @dbName, "`.`", table_data.audit_table, "`\r",
          "     (`",@tablePrefix,"action`, `",@tablePrefix,"actionby`, `",@tablePrefix,"actionuser`, `",@tablePrefix,"actionrealby`, `",@tablePrefix,"actionrealuser`, `",@tablePrefix,"actiontimestamp`,", table_data.column_names, ")\r",
          "  VALUES\r",
          "     ('Add', theDoer, theName, theRealDoer, theRealName, NOW(),", table_data.NEWcolumn_names, ");\r",
          " END \r",
          "$$ \r",
          "DELIMITER ; \r\r",

          "DROP TRIGGER IF EXISTS `", @dbName, "`.`", table_data.update_trigger, "`;\r",
          "DELIMITER $$ \r",
          "CREATE TRIGGER `", @dbName, "`.`", table_data.update_trigger, "`\r",
          "  AFTER UPDATE ON `", @dbName, "`.`", table_data.db_table, "`\r",
          "  FOR EACH ROW BEGIN \r",
          "  DECLARE theDoer INT DEFAULT 0; \r",
          "  DECLARE theName VARCHAR(200) DEFAULT ''; \r",
          "  DECLARE theRealDoer INT DEFAULT 0; \r",
          "  DECLARE theRealName VARCHAR(200) DEFAULT ''; \r",
          "  SELECT IFNULL(@actionBy,0) INTO theDoer; \r",
          "  SELECT IFNULL(@actionRealBy,0) INTO theRealDoer; \r",
          "  SELECT usr_username INTO theName FROM `user` WHERE usr_id = @actionBy; \r",
          "  SELECT usr_username INTO theRealName FROM `user` WHERE usr_id = @actionBy; \r",
          "  INSERT INTO `", @dbName, "`.`", table_data.audit_table, "`\r",
          "     (`",@tablePrefix,"action`, `",@tablePrefix,"actionby`, `",@tablePrefix,"actionuser`, `",@tablePrefix,"actionrealby`, `",@tablePrefix,"actionrealuser`, `",@tablePrefix,"actiontimestamp`,", table_data.column_names, ")\r",
          "  VALUES\r",
          "     ('Update', theDoer, theName, theRealDoer, theRealName, NOW(),", table_data.NEWcolumn_names, ");\r\r",
          " END \r",
          "$$ \r",
          "DELIMITER ; \r\r",

          "DROP TRIGGER IF EXISTS `", @dbName, "`.`", table_data.delete_trigger, "`;\r",
          "DELIMITER $$ \r",
          "CREATE TRIGGER `", @dbName, "`.`", table_data.delete_trigger, "`\r",
          "  AFTER DELETE ON `", @dbName, "`.`", table_data.db_table, "`\r",
          "  FOR EACH ROW BEGIN \r",
          "  DECLARE theDoer INT DEFAULT 0; \r",
          "  DECLARE theName VARCHAR(200) DEFAULT ''; \r",
          "  DECLARE theRealDoer INT DEFAULT 0; \r",
          "  DECLARE theRealName VARCHAR(200) DEFAULT ''; \r",
          "  SELECT IFNULL(@actionBy,0) INTO theDoer; \r",
          "  SELECT IFNULL(@actionRealBy,0) INTO theRealDoer; \r",
          "  SELECT usr_username INTO theName FROM `user` WHERE usr_id = @actionBy; \r",
          "  SELECT usr_username INTO theRealName FROM `user` WHERE usr_id = @actionBy; \r",
          "  INSERT INTO `", @dbName, "`.`", table_data.audit_table, "`\r",
          "     (`",@tablePrefix,"action`, `",@tablePrefix,"actionby`, `",@tablePrefix,"actionuser`, `",@tablePrefix,"actionrealby`, `",@tablePrefix,"actionrealuser`, `",@tablePrefix,"actiontimestamp`,", table_data.column_names, ")\r",
          "  VALUES\r",
          "     ('Delete', theDoer, theName, theRealDoer, theRealName, NOW(),", table_data.OLDcolumn_names, ");\r\r"
          " END \r",
          "$$ \r",
          "DELIMITER ; \r\r" 
) AS template
FROM (
   # This select builds a derived table of table names with ordered and grouped column information in different
   # formats as needed for audit table definitions and trigger definitions.
   SELECT
     table_order_key,
     table_name                                                                      AS db_table,
     CONCAT(@tablePrefix, table_name)                                                AS audit_table,
     CONCAT(table_name, "_inserts")                                                  AS insert_trigger,
     CONCAT(table_name, "_updates")                                                  AS update_trigger,
     CONCAT(table_name, "_deletes")                                                  AS delete_trigger,
     GROUP_CONCAT("\r  `", column_name, "` ", column_type ORDER BY column_order_key) AS column_defs,
     GROUP_CONCAT("`", column_name, "`" ORDER BY column_order_key)                   AS column_names,
     GROUP_CONCAT("NEW.`", column_name, "`" ORDER BY column_order_key)               AS NEWcolumn_names,
     GROUP_CONCAT("OLD.`", column_name, "`" ORDER BY column_order_key)               AS OLDcolumn_names
   FROM
     (
       -- This select builds a derived table of table names, column names and column types for
       -- non-audit tables of the specified db, along with ordering keys for later order by.
       -- The ordering must be done outside this select, as tables (including derived tables)
       -- are by definition unordered.
       -- We're only ordering so that the generated audit schema maintains a resemblance to the
       -- main schema.
       SELECT
         information_schema.tables.table_name        AS table_name,
         information_schema.columns.column_name      AS column_name,
         information_schema.columns.column_type      AS column_type,
         information_schema.tables.create_time       AS table_order_key,
         information_schema.columns.ordinal_position AS column_order_key
       FROM information_schema.tables
         JOIN information_schema.columns
           ON information_schema.tables.table_name = information_schema.columns.table_name
       WHERE information_schema.tables.table_schema = @dbName
             AND information_schema.columns.table_schema = @dbName
             AND information_schema.tables.table_name NOT LIKE CONCAT(@tablePrefix, '%')
     ) table_column_ordering_info
    WHERE table_name = @tableName
   GROUP BY table_name
 ) table_data
ORDER BY table_order_key

-- select `column_name` from `information_schema`.`columns` where `table_schema` = 'gtp2' and `table_name` = 'myaccountholder_bak';