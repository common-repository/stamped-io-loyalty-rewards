<?php
namespace Woostampedwpplugin;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Woo_stamped_io {

    public $pl_url;

    public function __construct() {
        //$this->pl_url = plugins_url("/stampedio-product-reviews");
        $this->pl_url = SLARM_WOO_STAMPED_URL;
        $this->Slarm_stamped_global_init();
        if (is_admin()) {
            $this->Slarm_stamped_admin_init();
        } else {
            $this->Slarm_stamped_public_init();
        }
    }

    /**
     * GLobal Setting for admin and public
     */
    public function Slarm_stamped_global_init() {
        $this->Slarm_stamped_activate_order_status();
        add_shortcode("Woo_stamped_io", array($this, "Slarm_stamped_io_shortcode"));
    }

    /**
     * Admin Setting for admin
     */
    public function Slarm_stamped_admin_init() {
        add_action('admin_enqueue_scripts', array($this, 'Slarm_stamped_admin_js'));
        $this->Slarm_stamped_admin_ajax();
    }

    /**
     * Public Setting for Public
     */
    public function Slarm_stamped_public_init() {
        $this->Slarm_stamped_prepare_scripts();
    }

    /**
     * All Wp Ajax Hooks
     */
    public function Slarm_stamped_admin_ajax() {
        add_action("wp_ajax_Woo_stamped_bulk_reviews", array($this, "Slarm_stamped_bulk_reviews_ajax"));
        add_action("wp_ajax_nopriv_Woo_stamped_bulk_reviews", array($this, "Slarm_stamped_bulk_reviews_ajax"));
        add_action("wp_ajax_Woo_stamped_bulk_export_review", array($this, "Slarm_stamped_bulk_export_review"));
        add_action("wp_ajax_nopriv_Woo_stamped_bulk_export_review", array($this, "Slarm_stamped_bulk_export_review"));
        add_action("wp_ajax_Woo_stamped_clear_reviews_cache", array($this, "Slarm_stamped_clear_reviews_cache"));
        add_action("wp_ajax_nopriv_Woo_stamped_clear_reviews_cache", array($this, "Slarm_stamped_clear_reviews_cache"));
    }

    public function Slarm_stamped_admin_js() {
        wp_enqueue_style('woo-stamped-io-admin-css', $this->pl_url . '/assets/css/woo-stamped.io.css', false, '1.9.1');
        wp_register_script('woo-stamped-io-admin-js', $this->pl_url . '/assets/js/woo-stamped.io.js', false, '1.9.1');
        wp_enqueue_script('woo-stamped-io-admin-js');
        $this->Slarm_stamped_admin_localize_scripts();
    }

    public function Slarm_stamped_admin_localize_scripts() {
        $vars = array();
        $vars["ajax_url"] = admin_url("admin-ajax.php");
        wp_localize_script('woo-stamped-io-admin-js', 'Woo_stamped_admin', $vars);
    }

    public function Slarm_stamped_prepare_scripts() {
        add_action('wp_enqueue_scripts', array($this, 'Slarm_stamped_public_js_css'));
    }

    public function Slarm_stamped_public_js_css() {
        //wp_register_style('woo-stamped-io-public-css', "//cdn-stamped-io.azureedge.net/files/widget.min.css", false, '1.0.0');
        //wp_register_script('woo-stamped-io-public-cdn', "//cdn-stamped-io.azureedge.net/files/widget.min.js", false, '1.0.0');
        //wp_enqueue_style('woo-stamped-io-public-css');
        //wp_enqueue_script('woo-stamped-io-public-cdn');

        wp_register_script('woo-stamped-io-public-custom', $this->pl_url . '/assets/js/woo-stamped.io-public.js', false, '1.9.0');
        wp_enqueue_script('woo-stamped-io-public-custom');
        $this->Slarm_stamped_localize_vars();
    }

    public function Slarm_stamped_localize_vars() {
        $vars = array();
        $vars["pub_key"] = Woo_stamped_api::Slarm_get_public_keys();
        $vars["url"] = Woo_stamped_api::Slarm_get_site_url();
        wp_localize_script('woo-stamped-io-public-custom', 'Woo_stamped', $vars);
    }

    public function Slarm_stamped_io_shortcode($atts, $content = "") {
        global $post;
        $atts = shortcode_atts(array("type" => 'badge', "product_id" => 0), $atts, 'Slarm_stamped_io_shortcode');
        extract($atts);

        global $product;
        if (isset($product_id) && $product_id > 0) {
            $product = new WC_Product($product_id);
        }

        if (empty($type)) {
            $type = "badge";
        }

        if ($type == 'badge') {
            ob_start();
            Woo_stamped_public::Slarm_woo_stamped_review_badge();
            return ob_get_clean();
        }

        if ($type == 'widget') {
            ob_start();
            Woo_stamped_public::Slarm_woo_stamped_review_box();
            return ob_get_clean();
        }

        if ($type == 'rich-snippet') {
            $data = $this->Slarm_stamped_fetched_aggregate_rating($product->get_id());
            return Woo_stamped_public::Slarm_woo_stamped_aggregate_rating($data);
        }
    }

    public function Slarm_stamped_bulk_reviews_ajax() {
        $response = $this->Slarm_stamped_bulk_review();
        $return = array("status" => "success", "text" => "<b>Order history exported to Stamped.io</b>");
        echo json_encode($return);
        exit();
    }

    public function Slarm_stamped_activate_order_status() {
        $statuses = Woo_stamped_api::Slarm_get_activated_status();
        if (is_array($statuses) && count($statuses) > 0) {
            foreach ($statuses as $status) {
                add_action("woocommerce_order_status_{$status}", array($this, 'Slarm_stamped_submit_review'), 10);
            }
        }
    }

    public static function Slarm_stamped_order_json($ID) {
        $orderData = array();
        $order = new WC_Order($ID);
        if ($order) {
            $line_items = $order->get_items("line_item");
            $items = array();
            $pro = 0;
            $productId = 0;
            if (is_array($line_items) && count($line_items) > 0) {
                foreach ($line_items as $key => $v) {
                    $productId = $v["variation_id"] !== "0" ? $v["product_id"] : $v["product_id"];
                    if ($productId != "") {
						/*
						$parent_grouped_id = 0;

						// The SQL query
						$results = $wpdb->get_results( "
							SELECT pm.meta_value as child_ids, pm.post_id
							FROM {$wpdb->prefix}postmeta as pm
							INNER JOIN {$wpdb->prefix}posts as p ON pm.post_id = p.ID
							INNER JOIN {$wpdb->prefix}term_relationships as tr ON pm.post_id = tr.object_id
							INNER JOIN {$wpdb->prefix}terms as t ON tr.term_taxonomy_id = t.term_id
							WHERE p.post_type LIKE 'product'
							AND p.post_status LIKE 'publish'
							AND t.slug LIKE 'grouped'
							AND pm.meta_key LIKE '_children'
							ORDER BY p.ID
						" );

						// Retreiving the parent grouped product ID
						foreach( $results as $result ){
							foreach( maybe_unserialize( $result->child_ids ) as $child_id )
								if( $child_id == $productId ){
									$parent_grouped_id = $result->post_id;
									break;
								}
							if( $parent_grouped_id != 0 ) break;
						}
	
						if( $parent_grouped_id != 0 ){
							$productId = $parent_grouped_id;
						} */

                        $products = new WC_Product($productId);
                        $url = get_the_post_thumbnail_url($products->get_id());
                        $items[] = array("productId" => $productId,
                            //"productDescription" => $products->post->post_content,
                            "productBrand" => null,
                            "productTitle" => $v["name"],
                            "productPrice" => $products->get_price(),
                            "productType" => null,
                            "productSKU" => $products->get_sku(),
                            "productTags" => strip_tags(wc_get_product_tag_list($products->get_id())),
                            "productUrl" => get_the_permalink($productId),
                            "productImageUrl" => $url,
                        );
                    }
                }
            }
            $orderData = array(
                'email' => $order->get_billing_email() ? $order->get_billing_email() : '',
                'firstName' => $order->get_billing_first_name() ? $order->get_billing_first_name() : '',
                'lastName' => $order->get_billing_last_name() ? $order->get_billing_last_name() : '',
                'location' => $order->get_billing_country(),
                'phoneNumber' => $order->get_billing_phone() ? $order->get_billing_phone() : '',
                'orderNumber' => $order->get_order_number(),
                'orderId' => $ID,
                'orderCurrencyISO' => $order->get_currency(),
                'orderTotalPrice' => $order->get_total(),
                'orderSource' => "WooCommerce",
                'orderDate' => $order->get_date_created()->format('c'),
                'itemsList' => $items
            );

            // POLYLANG INTEGRATION
	        if ( function_exists('pll_get_post_language') ) {
		        $orderData['locale'] = pll_get_post_language($ID);
	        }
        }
        return $orderData;
    }

    public function Slarm_stamped_submit_review($ID) {
        $orderData = self::Slarm_stamped_order_json($ID);
        //Woo_stamped_api::Slarm_Woo_stamped_logging("single call before start  array " . print_r($orderData, true));
        //Woo_stamped_api::Slarm_Woo_stamped_logging("single call before start json  " . json_encode($orderData, true));
        $response = Woo_stamped_api::Slarm_send_request("/survey/reviews", $orderData);

        //$response = Woo_stamped_api::Slarm_send_request_v2("", "/survey/reviews", $orderData);

        //Woo_stamped_api::Slarm_Woo_stamped_logging("single call after success " . print_r($response, true));
        if (is_object($response)) {
            update_post_meta($ID, "send_to_stamped_for_review", 1);
        }
    }

    public function Slarm_stamped_fetched_aggregate_rating($product_id) {
        $agrr_review = get_post_meta($product_id, "stamped_io_product_reviews", true);
        $ttl = (int) get_post_meta($product_id, "stamped_io_product_ttl", true);
        if ($agrr_review == null || $agrr_review == "" || $ttl < time()) {
            $outcome = (array) Woo_stamped_api::Slarm_send_request("/richSnippet?productId={$product_id}", array(), "GET");
            if (isset($outcome["httpStatusCode"])) {
                $ttl = (int) $outcome["ttl"] + time();
                update_post_meta($product_id, "stamped_io_product_reviews", $outcome);
                update_post_meta($product_id, "stamped_io_product_ttl", $ttl);
                $agrr_review = $outcome;
            }
        }
        return $agrr_review;
    }

    public function Slarm_stamped_bulk_review() {
        $posted_data = Slarm_woo_stamped_cstm_sanitize_array($_POST);
        extract($posted_data);
        $per_page = 500;
        $response = array();
        $status = Woo_stamped_api::Slarm_stamped_order_status();
        $args = array(
            "post_type" => "shop_order",
            "post_status" => ((is_array($status) && count($status) > 0) ? $status : 'any'),
            "posts_per_page" => $per_page,
            "paged" => $paged
        );

        $args["date_query"] = array(
            array(
                "after" => "-12 months"
            )
        );
        $orders = new WP_Query($args);
        $fp = $orders->found_posts;
        //Woo_stamped_api::Slarm_Woo_stamped_logging("found-post {$orders->found_posts}");
        if ($orders->have_posts()) {
            while ($orders->have_posts()) {
                $orders->the_post();
                global $post;
                $bulkorderData[] = self::Slarm_stamped_order_json($post->ID);
            }
        }
        $response = "";
        if (is_array($bulkorderData) && count($bulkorderData) > 0) {
            $response = Woo_stamped_api::Slarm_send_request("/survey/reviews/bulk", $bulkorderData);

            //Woo_stamped_api::Slarm_Woo_stamped_logging("Bulk call response " . print_r($response, true));

            if (is_object($response) && count($response) > 0) {
                update_options("update_stamped_bulk_order", date("Y-m-d"));
                //Woo_stamped_api::Slarm_Woo_stamped_logging("Bulk call success ");

                $output = "Success";
            }
        }
        $cal = "(" . (($paged - 1) * $per_page) . "-" . ($paged * $per_page) . ")";


        $response = "<b>" . __("{$cal} Order imported to Stamped.io") . "</b><br />";



        $rm = $fp % $per_page;
        $cn = $fp / $per_page;
        $cn = (int) $cn;
        if ($rm > 0) {
            $cn++;
        }
//        var_dump($cn);
//        var_dump($paged);
        $paged++;

        $returnOutput["status"] = "success";
        $returnOutput["paged"] = $paged;
        $returnOutput["response"] = $response;
        if ($paged > $cn) {
            $returnOutput["laststep"] = "yes";
            $returnOutput["response"] = "<b>" . __("{$fp} Orders imported to Stamped.io ") . "</b><br />";
        }

        echo json_encode($returnOutput);
        exit();
    }
	
    public function Slarm_stamped_clear_reviews_cache() {
        global $wpdb;
        $sql = ("DELETE FROM `{$wpdb->prefix}postmeta` WHERE `meta_key` LIKE '%stamped_io_product%'");
        $result = $wpdb->get_results($sql, ARRAY_A);
		
        $return = array("status" => "success", "text" => "<b>Reviews Cache has been cleared</b>");
        echo json_encode($return);

        exit();
	}

    public function Slarm_stamped_bulk_export_review() {
        $posted_data = Slarm_woo_stamped_cstm_sanitize_array($_POST);
        extract($posted_data);
        global $wpdb;
        $path = plugin_dir_path(__FILE__);
        $path = $path . "csv";
        if (!file_exists($path)) {
            mkdir($path);
        }
        $handle = fopen("{$path}/{$filename}.csv", "a");
        $per_page = 500;
        $response = array();
        $paged_e = $paged - 1;

        if ($paged == 1) {
            fputcsv($handle, array("product_id", "author", "email", "rating", "title", "body", "created_at", "reply", "replied_at", "product_image", "product_url", "published"));
        }

        $fp = 0;
        $sql_all = sprintf("SELECT * FROM `{$wpdb->prefix}comments` WHERE `comment_post_ID` IN (SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_status` LIKE 'publish' AND `post_type` LIKE 'product') ORDER BY `comment_post_id`");
        $result_all = $wpdb->get_results($sql_all, ARRAY_A);
        if (is_array($result_all) && count($result_all) > 0) {
            $fp = count($result_all);
        }
        $sql = sprintf("SELECT * FROM `{$wpdb->prefix}comments` WHERE `comment_post_ID` IN (SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_status` LIKE 'publish' AND `post_type` LIKE 'product') ORDER BY `comment_post_id` ASC LIMIT %d,500", ($paged_e * $per_page));
        $result = $wpdb->get_results($sql, ARRAY_A);
        $complete = array();
        $comments = array();
        $child = array();
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $key => $val) {
                extract($val);
                $complete[$comment_ID] = $val;
                $post_thumbnail_id = get_post_thumbnail_id($comment_post_ID);
                $product_image = "";
                if ($post_thumbnail_id > 0) {
                    $image_attributes = wp_get_attachment_image_src($post_thumbnail_id);
                    $product_image = $image_attributes[0];
                }
                $product_url = get_the_permalink($comment_post_ID);
                $is_published = "FALSE";
                if ($comment_approved == 1) {
                    $is_published = "TRUE";
                }

                $comments[$comment_ID] = array($comment_post_ID, $comment_author, $comment_author_email, get_comment_meta($comment_ID, "rating", true), null, $comment_content, $comment_date, ($comment_parent == "0" ? '' : 1 ), '', $product_image, $product_url, $is_published);
                if ($comment_parent != 0) {
                    $child[$comment_ID] = $comment_parent;
                }
            }
        }

        if (is_array($child) && count($child) > 0) {
            foreach ($child as $key => $v) {
                $comments[$v][7] = ($comments[$key][5] != '' ? 1 : '');
                $comments[$v][8] = $comments[$key][6];
                unset($comments[$key]);
            }
        }

        if (is_array($comments) && count($comments) > 0) {
            foreach ($comments as $key => $val) {
                fputcsv($handle, $val);
            }
        }
        fclose($handle);
        $cal = "(" . (($paged - 1) * $per_page) . "-" . ($paged * $per_page) . ")";
        $response = "<b></b><br />";
        $rm = $fp % $per_page;
        $cn = $fp / $per_page;
        $cn = (int) $cn;
        if ($rm > 0) {
            $cn++;
        }
        $paged++;
        $returnOutput["status"] = "success";
        $returnOutput["paged"] = $paged;
        $returnOutput["response"] = $response;
        if ($paged > $cn) {
            $returnOutput["laststep"] = "yes";
            $returnOutput["response"] = "<b>" . __("Reviews Exported to CSV <a href='{$this->pl_url}/includes/csv/{$filename}.csv' ") . " target='_blank'>Click here to download</a></b><br />";
        }
        echo json_encode($returnOutput);
        exit();
    }

}

$woo_stamped = new Woo_stamped_io();


