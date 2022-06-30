<?php
/**
 * Define database credentials
 */
define("DB_HOST", "localhost"); 
define("DB_NAME", "ecommerce"); 
define("DB_USER", "root"); 
define("DB_PASS", ""); 
define("DB_ENCODING", "utf8"); // DB connnection charset


/**
 * Define DB tables
 */
define("TABLE_PREFIX", "ec_");

// Set table names without prefix
define("TABLE_USERS", "users");
define("TABLE_CATEGORIES","categories");
define("TABLE_PRODUCTS", "products");