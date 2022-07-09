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
define("TABLE_CONFIGURATION","configuration");
define("TABLE_PRODUCTS","products");
define("TABLE_PRODUCT_CATEGORY","product_category");
define("TABLE_PRODUCTS_PHOTO", "productsphoto");
define("TABLE_ORDERS_CONTENT", "orderscontent");
define("TABLE_ORDERS", "orders");
define("TABLE_REVIEWS", "productsreview");