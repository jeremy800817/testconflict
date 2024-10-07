SET SESSION group_concat_max_len = 2048;

SET @dbName = "DBNAME";

SET @tableName = "TABLENAME";
SET @tmpTableName = "TMP_NAME";

SELECT CONCAT("INSERT INTO `", @dbName, "`.`", @tmpTableName, "`\r",
          "(\r",
          column_names, "\r",
          ")\r",
          "SELECT \r",
          column_names, "\r",
          "FROM `", @dbName, "`.`", @tableName, "`;\r\r",
          "RENAME TABLE `", @dbName, "`.`", @tableName, "` TO `", @dbName, "`.`", @tableName, "_old`,\r",
          "`", @dbName, "`.`", @tmpTableName, "` TO `", @dbName, "`.`", @tableName, "`;\r"
) AS template
FROM (
   # This select builds a derived table of table names with ordered and grouped column information in different
   # formats as needed for audit table definitions and trigger definitions.
   SELECT
     table_order_key,
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
     ) table_column_ordering_info
    WHERE table_name = @tableName
   GROUP BY table_name
 ) table_data
ORDER BY table_order_key

-- select `column_name` from `information_schema`.`columns` where `table_schema` = 'gtp2' and `table_name` = 'myaccountholder_bak';