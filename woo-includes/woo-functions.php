<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Functions used by plugins
 */
if (!class_exists('WC_Dependencies'))
    require_once 'class-wc-dependencies.php';

/**
 * WC Detection
 */
if (!function_exists('Slarm_woo_stamped_is_woocommerce_active')) {
    function Slarm_woo_stamped_is_woocommerce_active() {
        return WC_Dependencies::Slarm_woocommerce_active_check();
    }
}