<?php
/**
 * Plugin Name: Stamped.io Loyalty & Rewards
 * Plugin URI: https://stamped.io/customer-loyalty-software
 * Description: Stamped.io Product Reviews, Ratings, Questions & Answers, Social Integrations, Marketing & Upselling, Loyalty & Rewards and more!
 * Version: 1.0
 * Stable tag: 1.0
 * Author: Stamped.io
 * Author URI: https://stamped.io/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: stamped-io-reviews-for-woocommerce
 */



define("Slarm_woo_stamped_wc_least_version", "1.0");
define('SLARM_WOO_STAMPED_PATH', plugin_dir_path(__FILE__) ); 
define('SLARM_WOO_STAMPED_URL', plugin_dir_url(__FILE__) ); 

if (!function_exists('Slarm_woo_stamped_is_woocommerce_active')) {
    require_once( 'woo-includes/woo-functions.php' );
}

add_action('plugins_loaded', 'Slarm_woo_stamped_io', 0);
add_action('upgrader_process_complete', 'Slarm_woo_stamped_my_upgrade_function', 10, 2);

if (!function_exists('Slarm_woo_stamped_my_upgrade_function')) {
function Slarm_woo_stamped_my_upgrade_function( $upgrader_object, $options ) {
	//Woo_stamped_api::Slarm_Woo_stamped_logging("");
}
}

/**
 * Include all Woo Stamped Io files
 */
if (!function_exists('Slarm_woo_stamped_io')) {
function Slarm_woo_stamped_io() {
    global $woocommerce;
    if ( class_exists( 'WooCommerce' ) ) 
    {
        if (!version_compare($woocommerce->version, Slarm_woo_stamped_wc_least_version, ">=")) {
            add_action('admin_notices', 'Slarm_woo_stamped_woocommerce_version_check_notice');
            return false;
        }
    } 
    else 
    {
        add_action('admin_notices', 'Slarm_woo_stamped_woocommerce_not_installed_notice');
        return false;
    }



    require 'includes/cls_stamped_io_api.php';
    require 'admin/cls_stamped_io_admin.php';
    require 'view/cls_stamped_io_public.php';
    require 'includes/cls_stamped_io.php';

	//Woo_stamped_api::Slarm_Woo_stamped_logging("");
}
}

if (!function_exists('Slarm_woo_stamped_woocommerce_version_check_notice')) {
function Slarm_woo_stamped_woocommerce_version_check_notice() {
    ?>
    <div class="error">
        <p><?php _e("WooCommerce Stamped.io require WooCommerce version " . Slarm_woo_stamped_wc_least_version . " or greater", 'woo-stamped-io'); ?></p>
    </div>
    <?php
}
}

if (!function_exists('Slarm_woo_stamped_woocommerce_not_installed_notice')) {
function Slarm_woo_stamped_woocommerce_not_installed_notice() {
    ?>
    <div class="error">
        <p><?php _e("WooCommerce Stamped.io: WooCommerce  not Activated or not Installed.", 'woo-stamped-io'); ?></p>
    </div>
    <?php
}
}



if (!function_exists('Slarm_woo_stamped_cstm_sanitize_array')) {
    function Slarm_woo_stamped_cstm_sanitize_array( $array ) 
    {   
        foreach ( $array as $key => &$value ) 
        {
            if ( is_array( $value ) ) {
                $value = cwebco_cstm_sanitize_array($value);
            }
            else {
                $value = sanitize_text_field( $value );
            }
        }

    return $array; 
    }
}


