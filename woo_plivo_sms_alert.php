<?php

/* Send New ORDER Message Code Start */
$plugin_dir_path = str_replace('\\', '/', plugin_dir_path(__FILE__));
require $plugin_dir_path . 'plivo-vendor/autoload.php';

use Plivo\RestClient;

/* woocommerce_checkout_order_processed */
/* woocommerce_thankyou */

add_action('woocommerce_thankyou', 'wpts_send_plivo_sms_to_customers_ei', 1, 1);

function wpts_send_plivo_sms_to_customers_ei($order_id)
{

    $auth_id = get_option('wc_plivo_estatic_account_id');
    $auth_token = get_option('wc_plivo_estatic_auth_token');
    $from_number = get_option('wc_plivo_estatic_from_number');

    $plivo_auth = new RestClient($auth_id, $auth_token);

    $plivo_estatic_enabled = get_option('wc_plivo_estatic_enabled');
    if ($plivo_estatic_enabled == 'yes') {
        $order = new WC_Order($order_id);
        /* You can do here whatever you want */

        $cur_order_status = $order->get_status();

        $message_ei = get_option('wc_plivo_sms_' . $cur_order_status . '_sms_template');

        if ($message_ei == '') {
            $message_ei = get_option('wc_plivo_sms_default_sms_template');
        }

        $send_message = wpts_replace_order_message_variables($order_id, $message_ei);
        $billing_phone = get_post_meta($order_id, '_billing_phone', true);

        if ($billing_phone != '') {

            $plivo_ei_enable_admin = get_option('wc_plivo_estatic_enable_sms_admin');

            if ($plivo_ei_enable_admin == 'yes') {
                $plivo_ei_admin_mobile_number = get_option('wc_plivo_estatic_admin_mobile_number');
                $plivo_ei_admin_sms_message = get_option('wc_plivo_estatic_admin_sms_message');
                $send_admin_message = wpts_replace_order_message_variables($order_id, $plivo_ei_admin_sms_message, $post_order_status);
                
                $admin_response = $plivo_auth->messages->create(
                    $from_number, #from
                    [$plivo_ei_admin_mobile_number], #to
                    $send_admin_message
                );

                if ($admin_response->messageUuid[0]) {
                    /* Update Order Note */
                    $order->add_order_note(__("Message send to admin: ".$send_admin_message, 'woocommerce'));
                } else {
                    $order->add_order_note(__('Plivo Order Admin SMS Sending Fail!!!.', 'woocommerce'));
                }
            }

            /* Send a message to customer */
            $response = $plivo_auth->messages->create(
                $from_number, #from
                [$billing_phone], #to
                $send_message
            );
            /* Send message */
            if ($response->messageUuid[0]) {
                //* Update Order Note
                $order->add_order_note(__("Message sent to customer: ".$send_message, 'woocommerce'));
            } else {
                $order->add_order_note(__('Plivo Order SMS Sending Fail!!!.', 'woocommerce'));
            }
        }
    }
}

add_action('woocommerce_process_shop_order_meta', 'wpts_send_plivo_order_sms_to_customers');

function wpts_send_plivo_order_sms_to_customers($order_id)
{

    $auth_id = get_option('wc_plivo_estatic_account_id');
    $auth_token = get_option('wc_plivo_estatic_auth_token');
    $from_number = get_option('wc_plivo_estatic_from_number');

    // $plivo_auth = new RestAPI($auth_id, $auth_token);
    $plivo_auth = new RestClient($auth_id, $auth_token);

    $plivo_estatic_enabled = get_option('wc_plivo_estatic_enabled');
    if ($plivo_estatic_enabled == 'yes') {
        $order = new WC_Order($order_id);
        /* You can do here whatever you want */

        $cur_order_status = $order->get_status();

        $post_order_status = '';
        if (isset($_POST['order_status'])) {
            $post_order_stat = sanitize_text_field($_POST['order_status']);
            $post_order_statu = explode('wc-', $post_order_stat);
            $post_order_status = end($post_order_statu);
            $get_order_status_msg = $post_order_status;
        } else {
            $get_order_status_msg = $cur_order_status;
        }

        $message_ei = get_option('wc_plivo_sms_' . $get_order_status_msg . '_sms_template');

        if ($message_ei == '') {
            $message_ei = get_option('wc_plivo_sms_default_sms_template');
        }

        $send_message = wpts_replace_order_message_variables($order_id, $message_ei, $post_order_status);
        $billing_phone = get_post_meta($order_id, '_billing_phone', true);

        if ($billing_phone != '' && $post_order_status != $cur_order_status) {
            $plivo_ei_enable_admin = get_option('wc_plivo_estatic_enable_sms_admin');

            if ($plivo_ei_enable_admin == 'yes') {
                $plivo_ei_admin_mobile_number = get_option('wc_plivo_estatic_admin_mobile_number');
                $plivo_ei_admin_sms_message = get_option('wc_plivo_estatic_admin_sms_message');
                $send_admin_message = wpts_replace_order_message_variables($order_id, $plivo_ei_admin_sms_message, $post_order_status);
                
                $admin_response = $plivo_auth->messages->create(
                    $from_number, #from
                    [$plivo_ei_admin_mobile_number], #to
                    $send_admin_message
                );

                if ($admin_response->messageUuid[0]) {
                    /* Update Order Note */
                    $order->add_order_note(__("Message sent to admin: ".$send_admin_message, 'woocommerce'));
                } else {
                    $order->add_order_note(__('Plivo Order Admin SMS Sending Fail!!!.', 'woocommerce'));
                }
            }
            /* Send a message to customer */
            $response = $plivo_auth->messages->create(
                $from_number, #from
                [$billing_phone], #to
                $send_message
            );
            /* Send message */
            if ($response->messageUuid[0]) {
                //* Update Order Note
                $order->add_order_note(__("Message Sent To Customer: ".$send_message, 'woocommerce'));
            } else {
                $order->add_order_note(__('Plivo Order SMS Sending Fail!!!.', 'woocommerce'));
            }
        }
    }
}
