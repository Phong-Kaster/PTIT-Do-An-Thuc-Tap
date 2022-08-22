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
App::addRoute("POST", "/login/google/?", "AuthWithGoogle");

// Settings
$settings_pages = [
  "site", "logotype", "other", "experimental",
  "google-analytics", "google-drive", "dropbox", "onedrive", "paypal", "stripe", "facebook", "recaptcha",
  "proxy",

  "notifications", "smtp"
];
App::addRoute("GET|POST", "/settings/[".implode("|", $settings_pages).":page]?/?", "Settings");

/*********************************************************************/
/************************** ADMINISTRATOR ****************************/
/*********************************************************************/



/**************************CATEGORIES CONTROLLER*****************************/
App::addRoute("GET", "/admin/categories/?", "AdminCategories");


/**************************USERS CONTROLLER*****************************/
App::addRoute("GET|POST", "/admin/users/?", "AdminUsers");
App::addRoute("GET|PUT|DELETE|PATCH", "/admin/users/[i:id]/?", "AdminUser");


/**************************PRODUCTS CONTROLLER*****************************/
App::addRoute("GET|POST", "/admin/products/?", "AdminProducts");
App::addRoute("GET|PUT|DELETE", "/admin/products/[i:id]/?","AdminProduct");


/**************************PRODUCTS PHOTO CONTROLLER*****************************/
/**this controller get all photo from a product | upload new photo for product */
App::addRoute("GET|POST|PUT|DELETE", "/admin/products/photos/[i:product_id]?/[i:photo_id]?", "AdminProductsPhotos");


/**************************ORDERS CONTROLLER*****************************/
App::addRoute("GET|POST","/admin/orders/?", "AdminOrders");


/** (*) means uuid() instead of id */
App::addRoute("GET|PUT|DELETE","/admin/orders/[*:id]/?", "AdminOrder");


/**************************ORDERS CONTENT CONTROLLER*****************************/
App::addRoute("GET|POST|DELETE","/admin/orders-content/[*:id]/?", "AdminOrdersContent");


/**************************REVIEWS CONTROLLER*****************************/
App::addRoute("GET|POST|DELETE|PUT","/admin/reviews/[i:id]?", "AdminReviews");



/*********************************************************************/
/****************************** CLIENT *******************************/
/*********************************************************************/

/**************************PRODUCTS CONTROLLER*****************************/
App::addRoute("GET","/products/?", "Products");
App::addRoute("GET","/products/[i:id]/?", "Product");

/**************************ORDERS CONTROLLER*****************************/
App::addRoute("GET|POST","/orders/?", "Orders");
App::addRoute("GET","/latest-order/?", "OrderLatest");
App::addRoute("GET|POST|PUT", "/order/[*:id]/?", "Order");

/**************************REVIEWS CONTROLLER*****************************/
App::addRoute("POST|PUT|DELETE","/reviews/?[i:id]/?", "Reviews");

/**************************PROFILE CONTROLLER*****************************/
App::addRoute("GET|POST", "/profile/?", "Profile");

App::addRoute("GET|POST", "/test/?", "Test");

/**************************CATEGORIES CONTROLLER*****************************/
App::addRoute("GET", "/categories/?", "Categories");
App::addRoute("GET", "/categories/[i:id]/?", "Category");