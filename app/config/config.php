<?php 
require_once APPPATH.'/config/common.config.php'; // Common configuration
require_once APPPATH.'/config/db.config.php'; // Database configuration
require_once APPPATH.'/config/i18n.config.php'; // i18n configuration
require_once APPPATH.'/config/google-service.config.php';


// ASCII Secure random crypto key
define("CRYPTO_KEY", "def00000696dcbac44167211cb0ae542ac9d5001a06d45c0d487f4309f403bfcc2694f99fa081ebd69096a18237a96010b9b9b8aa8be7a00d222b8ba100d496b293ba488");

// General purpose salt
define("EC_SALT", "ImINZ0B8kD2PmWuU");


// Path to instagram sessions directory
define("SESSIONS_PATH", APPPATH . "/sessions");
// Path to temporary files directory
define("TEMP_PATH", ROOTPATH . "/assets/uploads/temp");


// Path to themes directory
define("THEMES_PATH", ROOTPATH . "/inc/themes");
// URI of themes directory
define("THEMES_URL", APPURL . "/inc/themes");


// default path for uploaded photos
define("UPLOAD_PATH", ROOTPATH . "/assets/uploads");
