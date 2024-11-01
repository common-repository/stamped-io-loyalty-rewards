<?php
namespace Woostampedwpplugin;
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

class Woo_stamped_admin {

    public function __construct() {
        //$this->pl_url = plugins_url("/stampedio-product-reviews");
        $this->pl_url = SLARM_WOO_STAMPED_URL;
        //Adding A tab in Woocommerce Tabs for setting details
        add_action('admin_menu', array($this, 'Slarm_woo_stamped_io_menu'), 10);
        add_action('admin_menu', array($this, 'Slarm_woo_stamped_io_menu_modified_url'), 11);
        add_filter('woocommerce_get_settings_pages', array($this, "Slarm_woo_stamped_io_add_setting_tab"), 10, 1);
    }

    //Adding A tab in Woocommerce Tabs for setting details
    public function Slarm_woo_stamped_io_add_setting_tab($settings) {
        $settings[] = include("cls_stamped_io_settings.php");
        return $settings;
    }

    public function Slarm_woo_stamped_io_menu() {
        add_menu_page('Stamped.io', 'Stamped.io', 'manage_options', 'woo_stamped_io', 'Slarm_woo_stamped_menu_page', $this->pl_url . '/assets/images/699370-icon-23-star-128.png');
    }

    public function Slarm_woo_stamped_menu_page() {
        return '';
    }

    public function Slarm_woo_stamped_io_menu_modified_url() {
        global $menu;
        if (is_array($menu) && count($menu) > 0) {
            foreach ($menu as $key => $val) {
                if ($val[2] == "woo_stamped_io") {
                    $menu[$key][2] = add_query_arg(array("page" => "wc-settings", "tab" => "woo_stamped_io"), admin_url("admin.php"));
                }
            }
        }
    }

}

new Woo_stamped_admin();