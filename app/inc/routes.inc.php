<?php 
// Language slug
// 
// Will be used theme routes
$langs = [];
foreach (Config::get("applangs") as $l) {
    if (!in_array($l["code"], $langs)) {
        $langs[] = $l["code"];
    }

    if (!in_array($l["shortcode"], $langs)) {
        $langs[] = $l["shortcode"];
    }
}
$langslug = $langs ? "[".implode("|", $langs).":lang]" : "";


/**
 * Theme Routes
 */

// Index (Landing Page)
// 
// Replace "Index" with "Login" to completely disable Landing page 
// After this change, Login page will be your default landing page
// 
// This is useful in case of self use, or having different 
// landing page in different address. For ex: you can install the script
// to subdirectory or subdomain of your wordpress website.
App::addRoute("GET|POST", "/", "Index");
App::addRoute("GET|POST", "/".$langslug."?/?", "Index");

// Login
App::addRoute("GET|POST", "/login/?", "Login");

// Signup
// 
//  Remove or comment following line to completely 
//  disable signup page. This might be useful in case 
//  of self use of the script
App::addRoute("GET|POST", "/".$langslug."?/signup/?", "Signup");


// Settings
$settings_pages = [
  "site", "logotype", "other", "experimental",
  "google-analytics", "google-drive", "dropbox", "onedrive", "paypal", "stripe", "facebook", "recaptcha",
  "proxy",

  "notifications", "smtp"
];
App::addRoute("GET|POST", "/settings/[".implode("|", $settings_pages).":page]?/?", "Settings");


/**************************CATEGORIES CONTROLLER*****************************/
App::addRoute("GET", "/admin/categories/?", "AdminCategories");


/**************************USERS CONTROLLER*****************************/
App::addRoute("GET|POST", "/admin/users/?", "AdminUsers");
App::addRoute("GET|PUT|DELETE|PATCH", "/admin/users/[i:id]/?", "AdminUser");


/**************************PRODUCTS CONTROLLER*****************************/
App::addRoute("GET|POST", "/admin/products/?", "AdminProducts");
App::addRoute("GET|PUT|DELETE", "/admin/products/[i:id]/?","AdminProduct");