<?php
/*
Plugin Name: Samti Orders Sender
Description: Samti Orders Sender
Author: Danny Huigen
*/

include_once "encrypter.php";

add_filter( 'page_template', 'orders_sender_page' );
function orders_sender_page( $page_template )
{
    if ( is_page( 'samti-getter' ) ) {
        $page_template = dirname( __FILE__ ) . '/page-getter.php';
    }
    return $page_template;
}

add_filter( 'page_template', 'products_sender_page' );
function products_sender_page( $page_template )
{
    if ( is_page( 'sender' ) ) {
        $page_template = dirname( __FILE__ ) . '/sender.php';
    }
    return $page_template;
}

if (!function_exists("getUrlParameterValue")){
    function getUrlParameterValue($parameter){
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $link_pieces = explode("?",$actual_link);
        foreach ($link_pieces as $single_link_piece){
            if (strpos($single_link_piece, $parameter) !== false) {
                $requested_value = explode("=",$single_link_piece)[1];
                return str_replace("%20" , " " , $requested_value);
            }
        }
        return "NO PARAMETER";
    }
}










/**
 * @snippet       Add House Number to WooCommerce Checkout
 * @how-to        Watch tutorial @ https://businessbloomer.com/?p=19055
 * @sourcecode    https://businessbloomer.com/?p=20663
 * @author        Rodolfo Melogli
 * @testedwith    WooCommerce 3.3.4
 */

add_filter( 'woocommerce_checkout_fields' , 'bbloomer_add_field_and_reorder_fields' );

function bbloomer_add_field_and_reorder_fields( $fields ) {

    // Add New Fields

    $fields['billing']['billing_houseno'] = array(
        'label'     => __('Huisnummer', 'woocommerce'),
        'placeholder'   => _x('Huisnummer', 'placeholder', 'woocommerce'),
        'required'  => true,
        'class'     => array('form-row-last'),
        'clear'     => true
    );

    $fields['shipping']['shipping_houseno'] = array(
        'label'     => __('Huisnummer', 'woocommerce'),
        'placeholder'   => _x('Huisnummer', 'placeholder', 'woocommerce'),
        'required'  => true,
        'class'     => array('form-row-last'),
        'clear'     => true
    );

    // Remove Address_2 Fields

    unset($fields['billing']['billing_address_2']);
    unset($fields['shipping']['shipping_address_2']);

    // Make Address_1 Fields Half Width

    $fields['billing']['billing_address_1']['class'] = array('form-row-first');
    $fields['shipping']['shipping_address_1']['class'] = array('form-row-first');

    // Billing: Sort Fields

    $newfields['billing']['billing_first_name'] = $fields['billing']['billing_first_name'];
    $newfields['billing']['billing_last_name']  = $fields['billing']['billing_last_name'];
    $newfields['billing']['billing_company']    = $fields['billing']['billing_company'];
    $newfields['billing']['billing_email']      = $fields['billing']['billing_email'];
    $newfields['billing']['billing_phone']      = $fields['billing']['billing_phone'];
    $newfields['billing']['billing_country']    = $fields['billing']['billing_country'];
    $newfields['billing']['billing_address_1']  = $fields['billing']['billing_address_1'];
    $newfields['billing']['billing_houseno']    = $fields['billing']['billing_houseno'];
    $newfields['billing']['billing_city']       = $fields['billing']['billing_city'];
    $newfields['billing']['billing_postcode']   = $fields['billing']['billing_postcode'];
    $newfields['billing']['billing_state']      = $fields['billing']['billing_state'];

    // Shipping: Sort Fields

    $newfields['shipping']['shipping_first_name'] = $fields['shipping']['shipping_first_name'];
    $newfields['shipping']['shipping_last_name']  = $fields['shipping']['shipping_last_name'];
    $newfields['shipping']['shipping_company']    = $fields['shipping']['shipping_company'];
    $newfields['shipping']['shipping_country']    = $fields['shipping']['shipping_country'];
    $newfields['shipping']['shipping_address_1']  = $fields['shipping']['shipping_address_1'];
    $newfields['shipping']['shipping_houseno']    = $fields['shipping']['shipping_houseno'];
    $newfields['shipping']['shipping_city']       = $fields['shipping']['shipping_city'];
    $newfields['shipping']['shipping_state']      = $fields['shipping']['shipping_state'];
    $newfields['shipping']['shipping_postcode']   = $fields['shipping']['shipping_postcode'];



    $checkout_fields = array_merge( $fields, $newfields);
    return $checkout_fields;
}

// ------------------------------------
// Add Billing House # to Address Fields

add_filter( 'woocommerce_order_formatted_billing_address' , 'bbloomer_default_billing_address_fields', 10, 2 );

function bbloomer_default_billing_address_fields( $fields, $order ) {
    $fields['billing_houseno'] = get_post_meta( $order->id, '_billing_houseno', true );
    return $fields;
}

// ------------------------------------
// Add Shipping House # to Address Fields

add_filter( 'woocommerce_order_formatted_shipping_address' , 'bbloomer_default_shipping_address_fields', 10, 2 );

function bbloomer_default_shipping_address_fields( $fields, $order ) {
    $fields['shipping_houseno'] = get_post_meta( $order->id, '_shipping_houseno', true );
    return $fields;
}

// ------------------------------------
// Create 'replacements' for new Address Fields

add_filter( 'woocommerce_formatted_address_replacements', 'add_new_replacement_fields',10,2 );

function add_new_replacement_fields( $replacements, $address ) {
    $replacements['{billing_houseno}'] = isset($address['billing_houseno']) ? $address['billing_houseno'] : '';
    $replacements['{shipping_houseno}'] = isset($address['shipping_houseno']) ? $address['shipping_houseno'] : '';
    return $replacements;
}

// ------------------------------------
// Show Shipping & Billing House # for different countries

add_filter( 'woocommerce_localisation_address_formats', 'bbloomer_new_address_formats' );

function bbloomer_new_address_formats( $formats )
{
    $formats['NL'] = "{name}\n{company}\n{address_1}\n{billing_houseno}\n{shipping_houseno}\n{city}\n{state}\n{postcode}\n{country}";
    $formats['BE'] = "{name}\n{company}\n{address_1}\n{billing_houseno}\n{shipping_houseno}\n{city}\n{state}\n{postcode}\n{country}";
    return $formats;
}