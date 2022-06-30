<?php 
require_once APPPATH.'/config/common.config.php'; // Common configuration
require_once APPPATH.'/config/db.config.php'; // Database configuration
require_once APPPATH.'/config/i18n.config.php'; // i18n configuration

// ASCII Secure random crypto key
define("CRYPTO_KEY", "def00000953dd1ebc7f114b6d769cc8feede974cc053aecd286ecb892a5ef0b82b848406f8d90a4efe4259400b0f05be5b53eefead92886375a794c2f0ccf7c76b13780c");

// General purpose salt
define("EC_SALT", "0TKSeBDgx5R1wgTW");


// Path to instagram sessions directory
define("SESSIONS_PATH", APPPATH . "/sessions");
// Path to temporary files directory
define("TEMP_PATH", ROOTPATH . "/assets/uploads/temp");


// Path to themes directory
define("THEMES_PATH", ROOTPATH . "/inc/themes");
// URI of themes directory
define("THEMES_URL", APPURL . "/inc/themes");


// Path to plugins directory
define("PLUGINS_PATH", ROOTPATH . "/inc/plugins");
// URI of plugins directory
define("PLUGINS_URL", APPURL . "/inc/plugins");

// Path to ffmpeg binary executable
// NULL means it's been installed on global path
// If you set the value other than null, then it will only be 
// validated during posting the videos
define("FFMPEGBIN", NULL);

// Path to ffprobe binary executable
// NULL means it's been installed on global path
// If you set the value other than null, then it will only be 
// validated during posting the videos
define("FFPROBEBIN", NULL);
