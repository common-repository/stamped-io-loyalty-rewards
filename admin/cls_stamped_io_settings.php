<?php
namespace Woostampedwpplugin;
if (!class_exists('Woostampedwpplugin\Woo_stamped_admin_tab')) :

    /**
     * WC_Settings_Webhooks
     */

    class Woo_stamped_admin_tab extends \WC_Settings_Page {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->id = 'woo_stamped_io';
            $this->label = __('Stamped.io', 'woocommerce');
            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('woocommerce_settings_' . $this->id, array($this, 'Slarm_woo_output'));
            add_action('woocommerce_settings_save_' . $this->id, array($this, 'Slarm_woo_save'));
            add_action('woocommerce_sections_' . $this->id, array($this, 'Slarm_output_sections'));
            add_action('woocommerce_admin_field_Woo_stamped_button_html', array($this, 'Slarm_woo_stamped_button_html'));
            add_action('woocommerce_admin_field_Woo_stamped_html', array($this, 'Slarm_woo_stamped_html'));
        }

        /**
         * Woocommerce Custom field for Button for WooStamped Order history
         * @param type $value
         */
        public function Slarm_woo_stamped_button_html($value) {
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="
                           <?php echo esc_attr($value['id']); ?>"><?php echo esc_html($value['title']); ?>
                    </label>

                </th>
                <td class="forminp">
                    <input type="button" name="<?php echo esc_attr($value['id']); ?>" id="<?php echo esc_attr($value['id']); ?>" class="<?php echo esc_attr($value['class']); ?>" value="<?php echo esc_attr($value['value']); ?>"> <i class="processing" style="display: inline-block;">
                        <span class="spinner"></span>
                        <span class="response" ></span>
                    </i>
                    <br />
                    <p>
                        <?php esc_html_e( $value["desc"], 'stamped-io-reviews-for-woocommerce' ); ?>
                    </p>
                </td>
            </tr>
            <?php
        }

        public function Slarm_woo_stamped_html() {
            $review_time = "reviews-" . time();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <input type="hidden" name="paged" class="woo_stamped_counter" value="1">
                    <input type="hidden" name="filename" class="woo_stamped_filename" value="<?php esc_html_e( $review_time, 'stamped-io-reviews-for-woocommerce' );?>">
                </th>
                <td class="forminp bulk_import_fields" counter="1">
                </td>
            </tr>

            <?php
        }

        /**
         * Get sections
         *
         * @return array
         */
        public function Slarm_get_sections() {
            $sections = array(
                'woo_stamped_io' => __('Stamped.io Settings', 'woocommerce'),
                'woo_stamped_io_import_history' => __('Import Order History', 'woocommerce'),
                'woo_stamped_io_export_reviews' => __('Export Product Reviews', 'woocommerce'),
            );
            return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
        }

        /**
         * Output the settings
         */
        public function Slarm_woo_output() {
            global $current_section;
            $settings = $this->Slarm_get_settings($current_section);
            \WC_Admin_Settings::output_fields($settings);
        }

        /**
         * Save settings
         */
        public function Slarm_woo_save() {
            global $current_section;
            $settings = $this->Slarm_get_settings($current_section);
            \WC_Admin_Settings::save_fields($settings);
        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function Slarm_get_settings($current_section = '') {

            if ($current_section == "woo_stamped_io_import_history") {
                $settings = array(array('title' => __('Import Order history', 'woocommerce'), 'type' => 'title', 'id' => 'checkout_page_history_order'),
                    array(
                        'title' => __('Import Orders', 'woocommerce'),
                        'id' => 'Woo_stamped_bulk_review_button',
                        'type' => 'Woo_stamped_button_html',
                        'class' => 'button-primary woocommerce-save-button',
                        'css' => '',
                        'value' => 'Import Order History',
                        'desc' => "<style>.submit{display:none !important}</style><i>Import your existing orders history up to 6 months to Stamped.io.<br />
If you need to import orders that is back-dated more than 6 months,<br />
please email us at hello@stamped.io</i>",
                        'desc_tip' => true,
                    ),
                    array(
                        'id' => 'Woo_stamped_bulk_review_button',
                        'type' => 'Woo_stamped_html',
                        'class' => 'button-primary woocommerce-save-button',
                ));
                return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
            } else if ($current_section == "woo_stamped_io_export_reviews") {
                $settings = array(array('title' => __('Export Product Reviews', 'woocommerce'), 'type' => 'title', 'id' => 'checkout_page_history_order'),
                    array(
                        'title' => __('Export Product Reviews', 'woocommerce'),
                        'id' => 'Woo_stamped_bulk_export_review_button',
                        'type' => 'Woo_stamped_button_html',
                        'class' => 'button-primary woocommerce-save-button',
                        'css' => '',
                        'value' => 'Export Order reviews',
                        'desc' => "<style>.submit{display:none !important}</style><i>Import your existing product reviews.<br />
CSV will be generated and available for download once processing is complete.<br />
</i>",
                        'desc_tip' => true,
                    ),
                    array(
                        'id' => 'Woo_stamped_bulk_review_button',
                        'type' => 'Woo_stamped_html',
                        'class' => 'button-primary woocommerce-save-button',
                ));
                return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
            } else {
                $settings = array(
                    array('title' => __('Stamped.io Settings', 'woocommerce'), 'type' => 'title', 'id' => 'stampe_io_settings'),
                    array(
                        'title' => __('API Public Key', 'woocommerce'),
                        'id' => 'stamped_public_key',
                        'type' => 'text',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        'desc' => "",
                        'desc_tip' => true,
                    ),
                    array(
                        'title' => __('API Private Key', 'woocommerce'),
                        'id' => 'stamped_private_key',
                        'type' => 'text',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        //     'desc'=>"fgfj",
                        'desc_tip' => true,
                    ),
                    array(
                        'title' => __('Store URL', 'woocommerce'),
                        'id' => 'stamped_store_url',
                        'type' => 'text',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        'desc'=> "Use the store URL you've registered in Stamped.io. Note: Do not include http:// or https://."
                    ),
                    array(
                        'title' => __('Status', 'woocommerce'),
                        'id' => 'stamped_order_status',
                        'type' => 'multiselect',
                        'options' => wc_get_order_statuses(),
                        'placeholder' => 'Choose Status',
                        'class' => 'wc-enhanced-select',
                        'css' => 'min-width:300px;',
                        'default' => 'wc-completed',
                        'desc' => __('<i>Set the order statuses that will trigger the review requests to be sent, example of commonly used statuses are "Complete", "Shipped," "Pending," or "Received.</i>', 'woocommerce'),
                        'desc_tip' => true
                    ),
                    array('type' => 'sectionend', 'id' => 'stampe_io_settings'),
                    array('title' => __('Display Settings', 'woocommerce'), 'type' => 'title', 'id' => 'stamped_io_extra_settings'),
                    array(
                        'title' => __('Disable Native WooCommerce Reviews', 'woocommerce'),
                        'id' => 'stamped_disable_wc_review',
                        'type' => 'checkbox',
                        'class' => '',
                        'css' => '',
                        'default' => 'yes',
                        'desc_tip' => true,
                    ),
                    array(
                        'title' => __('Display Stamped.io Reviews', 'woocommerce'),
                        'id' => 'stamped_show_review_inside_below',
                        'type' => 'radio',
                        'class' => '',
                        'default' => 'below',
                        'options' => array("inside" => __("Inside Tab"), "below" => __("Below Description"), "Other" => __("Custom location")),
                        'css' => '',
                        'desc_tip' => true,
                    ),
                    array(
                        'title' => __('Enable reviews ratings in Category Page', 'woocommerce'),
                        'id' => 'stamped_show_rating_on_grid_view',
                        'type' => 'checkbox',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        'desc_tip' => true,
                        'default' => 'yes'
                    ),
                    array(
                        'title' => __('Enable reviews ratings in Product Page', 'woocommerce'),
                        'id' => 'stamped_rating_enable_on_product',
                        'type' => 'checkbox',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        'desc_tip' => true,
                        'default' => 'yes'
                    ),
                    array('type' => 'sectionend', 'id' => 'stamped_io_rewards_settings'),
                    array('title' => __('Loyalty & Rewards', 'woocommerce'), 'type' => 'title', 'id' => 'stamped_io_rewards_status_settings'),
                    array(
                        'title' => __('Enable Loyalty & Rewards', 'woocommerce'),
                        'id' => 'stamped_enable_rewards',
                        'type' => 'checkbox',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        'desc_tip' => true,
                        'default' => 'no'
                    ),
                    array('type' => 'sectionend', 'id' => 'stamped_io_extra_settings'),
                    array('title' => __('SEO and Cache Settings', 'woocommerce'), 'type' => 'title', 'id' => 'stamped_io_seo_extra_settings'),
                    array(
                        'title' => __('Enable reviews rich snippet', 'woocommerce'),
                        'id' => 'stamped_enable_rich_snippet',
                        'type' => 'checkbox',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        'desc_tip' => true,
                        'default' => 'yes'
                    ),
                    array(
                        'title' => __('Enable reviews cache', 'woocommerce'),
                        'id' => 'stamped_enable_reviews_cache',
                        'type' => 'checkbox',
                        'class' => '',
                        'css' => 'min-width:300px;',
                        'desc_tip' => true,
                        'default' => 'yes'
                    ),
                    array(
                        'title' => __('Clear Reviews Cache', 'woocommerce'),
                        'id' => 'Woo_stamped_clear_reviews_cache',
                        'type' => 'Woo_stamped_button_html',
                        'class' => 'button-primary woocommerce-save-button',
                        'css' => '',
                        'value' => 'Clear Reviews Cache',
                        'desc' => "Clear all reviews cache from local database",
                        'desc_tip' => true,
                    ),
                    array('type' => 'sectionend', 'id' => 'stamped_io_seo_extra_settings'),
                );
                return apply_filters('woocommerce_get_settings_' . $this->id, $settings);
            }
        }

        public function Slarm_output_sections() {
            global $current_section;

            $sections = $this->Slarm_get_sections();
            if (Woo_stamped_api::Slarm_get_public_keys() != "" && Woo_stamped_api::Slarm_get_private_keys() != "") {
                echo "<p>" . __("Customize your review request email & manage your scheduled review requests queue", "woocommerce") . ": <a href='https://go.stamped.io/#/signin?passwordHash=" . Woo_stamped_api::Slarm_get_token() . "' target='_blank'>" . __("Dashboard") . "</a></p>";
            } else {
                echo "<p>" . __("Don't have an account yet? Signup for a Free Stamped.io account here: ", "woocommerce") . "<a href='https://www.stamped.io/account/signup' target='_blank'>https://www.stamped.io/account/signup</a></p>";
            }
            echo '<ul class="subsubsub">';
            $array_keys = array_keys($sections);

            if ($current_section == "") {
                $current_section = $this->id;
            }
            foreach ($sections as $id => $label) {
                //$thisadminurl = admin_url('admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title($id));
                //$newadminurl = esc_html( $thisadminurl, 'stamped-io-reviews-for-woocommerce' );

                /*echo '<li><a href="' . $thisadminurl . '" class="current">' . $label . '</a> </li>';*/
                echo '<li><a href="' . admin_url('admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title($id)) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end($array_keys) == $id ? '' : '|' ) . ' </li>';
            }

            echo '</ul><br class="clear" />';
        }

    }

    return new Woo_stamped_admin_tab();
    endif;