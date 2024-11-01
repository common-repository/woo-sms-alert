<?php

/* Send New ORDER Message Code Start */
$plugin_dir_path = str_replace('\\', '/', plugin_dir_path(__FILE__));
require_once $plugin_dir_path . "twilio-vendor/autoload.php";
/* WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)) */

use Twilio\Rest\Client;

/* woocommerce_checkout_order_processed */
/* woocommerce_thankyou */

add_action('woocommerce_thankyou', 'wpts_send_twolio_sms_to_customers_ei', 1, 1);

function wpts_send_twolio_sms_to_customers_ei($order_id)
{

    $AccountSid = get_option('wc_twilio_estatic_account_id');
    $AuthToken = get_option('wc_twilio_estatic_auth_token');
    $from_number = get_option('wc_twilio_estatic_from_number');

    $client = new Client($AccountSid, $AuthToken);

    $twilio_estatic_enabled = get_option('wc_twilio_estatic_enabled');
    if ($twilio_estatic_enabled == 'yes') {
        $order = new WC_Order($order_id);
        /* You can do here whatever you want */

        $cur_order_status = $order->get_status();

        $message_ei = get_option('wc_twilio_sms_' . $cur_order_status . '_sms_template');

        if ($message_ei == '') {
            $message_ei = get_option('wc_twilio_sms_default_sms_template');
        }
        $send_message = wpts_replace_order_message_variables($order_id, $message_ei);

        $billing_phone = get_post_meta($order_id, '_billing_phone', true);

        if ($billing_phone != '') {

            $twilio_ei_enable_admin = get_option('wc_twilio_estatic_enable_sms_admin');

            if ($twilio_ei_enable_admin == 'yes') {
                $twilio_ei_admin_mobile_number = get_option('wc_twilio_estatic_admin_mobile_number');
                $twilio_ei_admin_sms_message = get_option('wc_twilio_estatic_admin_sms_message');
                $send_admin_message = wpts_replace_order_message_variables($order_id, $twilio_ei_admin_sms_message, $cur_order_status);

                /* Send twilio SMS to ADMIN, code HERE */
                $response = $client->messages->create(
                    $twilio_ei_admin_mobile_number, array(
                        'from' => $from_number,
                        'body' => $send_admin_message)
                );
                if ($response) {
                    $order->add_order_note(__("Sent message to Admin: " . $send_message, 'woocommerce'));
                } else {
                    $order->add_order_note(__('Twilio Order SMS Sending to Customer Fail!!!.', 'woocommerce'));
                }
            }
            /*  Send a message */

            /* Send twilio SMS to Customer, code HERE */
            $response = $client->messages->create(
                $billing_phone, array(
                    'from' => $from_number,
                    'body' => $send_message)
            );
            if ($response) {
                $order->add_order_note(__("Sent message to Customer: " . $send_message, 'woocommerce'));
            } else {
                $order->add_order_note(__('Twilio Order SMS Sending to Customer Fail!!!.', 'woocommerce'));
            }
        }
    }
}

add_action('woocommerce_process_shop_order_meta', 'wpts_send_twilio_order_sms_to_customers');

function wpts_send_twilio_order_sms_to_customers($order_id)
{

    $AccountSid = get_option('wc_twilio_estatic_account_id');
    $AuthToken = get_option('wc_twilio_estatic_auth_token');

    $from_number = get_option('wc_twilio_estatic_from_number');

    $client = new Client($AccountSid, $AuthToken);

    $twilio_estatic_enabled = get_option('wc_twilio_estatic_enabled');

    if ($twilio_estatic_enabled == 'yes') {
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

        $message_ei = get_option('wc_twilio_sms_' . $get_order_status_msg . '_sms_template');

        if ($message_ei == '') {
            $message_ei = get_option('wc_twilio_sms_default_sms_template');
        }

        $send_message = wpts_replace_order_message_variables($order_id, $message_ei, $post_order_status);

        $billing_phone = get_post_meta($order_id, '_billing_phone', true);

        if ($billing_phone != '' && $post_order_status != $cur_order_status) {

            $twilio_ei_enable_admin = get_option('wc_twilio_estatic_enable_sms_admin');

            if ($twilio_ei_enable_admin == 'yes') {
                $twilio_ei_admin_mobile_number = get_option('wc_twilio_estatic_admin_mobile_number');
                $twilio_ei_admin_sms_message = get_option('wc_twilio_estatic_admin_sms_message');
                $send_admin_message = wpts_replace_order_message_variables($order_id, $twilio_ei_admin_sms_message, $post_order_status);

                /* Send twilio SMS to ADMIN, code HRER */
                $response = $client->messages->create(
                    $twilio_ei_admin_mobile_number, array(
                        'from' => $from_number,
                        'body' => $send_admin_message)
                );

                if ($response) {
                    $order->add_order_note(__("Sent message to Admin: " . $send_message, 'woocommerce'));
                } else {
                    $order->add_order_note(__('Twilio Order SMS Sending to Customer Fail!!!.', 'woocommerce'));
                }
            }
            /*  Send a message */

            /* Send twilio SMS to Customer, code HRER */
            $response = $client->messages->create(
                $billing_phone, array(
                    'from' => $from_number,
                    'body' => $send_message,
                )
            );

            if ($response) {
                $order->add_order_note(__("Sent message to Customer: " . $send_message, 'woocommerce'));
            } else {
                $order->add_order_note(__('Twilio Order SMS Sending to Customer Fail!!!.', 'woocommerce'));
            }
            /* wp_redirect(admin_url('edit.php?post_type=shop_order'));
        exit(); */
        }
    }
}
