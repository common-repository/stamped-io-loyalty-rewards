<?php
namespace Woostampedwpplugin;
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

abstract class Woo_stamped_api {

    /**
     * 
     * @return string get stamped io site url
     */
    public static function Slarm_get_site_url() {
        return get_option("stamped_store_url");
    }

    /**
     * checking native review function is disabled 
     * @return string return WC Native Rating is Enable or disabled
     */
    public static function Slarm_disallow_natice_rating() {
        return get_option("stamped_disable_wc_review");
    }

    /**
     * 
     * @return type cheking stamped io rating is enable on archive and single product page
     */
    public static function Slarm_show_stamped_rating_on_archive() {
        return get_option("stamped_show_rating_on_grid_view");
    }

    /**
     * 
     * @return String Getting Location of Stamped Io review box,Inside tab,Below Tabs,Custom Location
     */
    public static function Slarm_get_selected_area_of_stamped_area() {
        return get_option("stamped_show_review_inside_below");
    }

    public static function Slarm_get_rating_enable_on_product() {
        return get_option("stamped_rating_enable_on_product");
    }

	public static function Slarm_get_enable_rich_snippet(){
		return get_option("stamped_enable_rich_snippet");
	}

	public static function Slarm_get_enable_reviews_cache(){
		return get_option("stamped_enable_reviews_cache");
	}

	public static function Slarm_et_enable_rewards(){
		return get_option("stamped_enable_rewards");
	}

    /**
     * 
     * @return String Getting Public key
     */
    public static function Slarm_get_public_keys() {
        return get_option("stamped_public_key");
    }

    /**
     * 
     * @return String Getting Public Key
     */
    public static function Slarm_get_private_keys() {
        return get_option("stamped_private_key");
    }

    /**
     * 
     * @return Base64 Token for basic Oauth 
     */
    public static function Slarm_get_token() {
        $public_key = self::Slarm_get_public_keys();
        $private_key = self::Slarm_get_private_keys();
        $token_key = trim($public_key) . ":" . trim($private_key);
        return base64_encode($token_key);
    }

    /**
     * 
     * @return string get all selected order status which one selected in setting page
     */
    public static function Slarm_stamped_order_status() {
        $status = get_option("stamped_order_status");
        return $status;
    }

    /**
     * 
     * @return Get all activated order status without slicing wc-
     */
    public static function Slarm_get_activated_status() {
        $output = array();
        $status = self::Slarm_stamped_order_status();
        if (is_array($status) && count($status) > 0) {
            foreach ($status as $val) {
                if (strpos($val, "wc-") > -1) {
                    $val = substr($val, 3);
                }
                $output[] = $val;
            }
        }
        return $output;
    }

    /**
     * 
     * @param type $requst_service string or part of service url   /survey/review/bulk
     * @param type $postfields  array of json post data
     * @param type $method Method use for curl request defult is POST
     * @return type
     */
    public static function Slarm_send_request($requst_service, $postfields = array(), $method = "POST") {
        $domainName = self::get_site_url();
        $baseToken = self::get_token();

        $curl = curl_init();

        $options = array(
            CURLOPT_URL => "https://stamped.io/api/{$domainName}{$requst_service}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "{$method}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic {$baseToken}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
            CURLOPT_SSL_VERIFYPEER => false
        );
        if (count($postfields) > 0) {
            $options[CURLOPT_POSTFIELDS] = json_encode($postfields);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return $err;
        } else {
            return json_decode($response);
        } 
    }
    
    public static function Slarm_send_request_v2($store_hash, $requst_service, $postfields = array(), $method = "POST") {
        $baseToken = self::get_token();

        $curl = curl_init();

        $options = array(
            CURLOPT_URL => "https://stamped.io/api/v2/{$store_hash}{$requst_service}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "{$method}",
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic {$baseToken}",
                "cache-control: no-cache",
                "content-type: application/json",
            ),
            CURLOPT_SSL_VERIFYPEER => false
        );
        if (count($postfields) > 0) {
            $options[CURLOPT_POSTFIELDS] = json_encode($postfields);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return $err;
        } else {
            return json_decode($response);
        }  
    }

    public static function Slarm_get_request($product_id, $product_title, $postfields = array(), $method = "POST") {
       $domainName = self::get_site_url();
        $public_key = self::get_public_keys();

        $request = wp_remote_get( "https://stamped.io/api/widget?productId={$product_id}&apiKey={$public_key}&storeUrl={$domainName}&productName={$product_title}",
             array(
             'timeout' => 5,
             'sslverify' => false,
             'headers' => array('authorization' => 'Basic {$baseToken}',
                               'cache-control'=> 'no-cache',
                               'content-type'=> 'application/json') 
             ));

        if( is_wp_error( $request ) ) {
            return false; // Bail early
        }

        $body = wp_remote_retrieve_body( $request );

        $data = json_decode( $body );

        if( ! empty( $data ) ) {
           return $data;
        }

        /*
        $curl = curl_init();

        $options = array(
            CURLOPT_URL => "https://stamped.io/api/widget?productId={$product_id}&apiKey={$public_key}&storeUrl={$domainName}&productName={$product_title}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "{$method}",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
            ),
            CURLOPT_SSL_VERIFYPEER => false
        );
        if (count($postfields) > 0) {
            $options[CURLOPT_POSTFIELDS] = json_encode($postfields);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return $err;
        } else {
            return json_decode($response);
        }
        */
    }

    function Slarm_Woo_stamped_logging($string, $filename = "woo_stamped_log.txt") {
        $upload_dir = wp_upload_dir();
        $base_path = $upload_dir['basedir'] . '/woo-stamped-io-logs';
        if (file_exists($base_path)) {
			array_map('unlink', glob("$base_path/*.*"));
            rmdir($base_path);
        }

        return true;
    }
}
