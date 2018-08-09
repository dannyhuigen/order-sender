<?php

if (getUrlParameterValue("orderId") !== "NO PARAMETER")
{

    $all_items_in_order = get_option_bd(getUrlParameterValue("orderId") , "all") ;
    $order_details = getSingleOrder(getUrlParameterValue("orderId"));

    $array_to_send = array(
        "order_details" => $order_details,
        "products" => $all_items_in_order,
    );

    $myJSON = json_encode($array_to_send);
    echo dec_enc("encrypt",$myJSON);
}
else if (getUrlParameterValue("changeStatus") !== "NO PARAMETER"){

        $encrypted_order_id = getUrlParameterValue("changeStatus");
        $decrpted_order_id = dec_enc("decrypt", $encrypted_order_id);

        $order = new WC_Order($decrpted_order_id);
        $order->update_status('completed', 'order status updated by Danny\'s order system '); // order note is optional, if you want to  add a note to order
        echo "The satus of order with the ID " . $decrpted_order_id . " has been updated to complete<br><br>";
        echo "current status of order: " . $order->get_status();

}
else{
    $myJSON = json_encode(getAllOrders());
    echo dec_enc("encrypt",$myJSON);
}

function getSingleOrder($order_id){

    $order = wc_get_order( $order_id);
    $order_data = $order->get_data();
    $post_url = admin_url( 'post.php?post=' . $order->ID ) . '&action=edit';

    foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
        $order_item_name           = $shipping_item_obj->get_name();
        $order_item_type           = $shipping_item_obj->get_type();
        $shipping_method_title     = $shipping_item_obj->get_method_title();
        $shipping_method_id        = $shipping_item_obj->get_method_id(); // The method ID
        $shipping_method_total     = $shipping_item_obj->get_total();
        $shipping_method_total_tax = $shipping_item_obj->get_total_tax();
        $shipping_method_taxes     = $shipping_item_obj->get_taxes();
    }

    return array(

        "order_id" => $order_data['id'],
        "order_parent_id" => $order_data['parent_id'],
        "order_status" => $order_data['status'],
        "order_currency" => $order_data['currency'],
        "order_version" => $order_data['version'],
        "order_payment_method" => $order_data['payment_method'],
        "order_payment_method_title" => $order_data['payment_method_title'],

        "order_shipping_name" => $order_item_name,
        "order_shipping_method" => $shipping_method_title,

        "order_date_created" => $order_data['date_created']->date('Y-m-d H:i:s'),
        "order_date_modified" => $order_data['date_modified']->date('Y-m-d H:i:s'),

        "order_timestamp_created" => $order_data['date_created']->getTimestamp(),
        "order_timestamp_modified" => $order_data['date_modified']->getTimestamp(),


        "order_discount_total" => $order_data['discount_total'],
        "order_discount_tax" => $order_data['discount_tax'],
        "order_shipping_total" => $order_data['shipping_total'],
        "order_shipping_tax" => $order_data['shipping_tax'],
        "order_total" => $order_data['cart_tax'],
        "order_total_tax" => $order_data['total_tax'],
        "order_customer_id" => $order_data['customer_id'], // ... and so on

        "order_billing_first_name" => $order_data['billing']['first_name'],
        "order_billing_last_name" => $order_data['billing']['last_name'],
        "order_billing_company" => $order_data['billing']['company'],
        "order_billing_address_1" => $order_data['billing']['address_1'],
        "order_billing_address_2" => $order_data['billing']['address_2'],
        "order_billing_city" => $order_data['billing']['city'],
        "order_billing_state" => $order_data['billing']['state'],
        "order_billing_postcode" => $order_data['billing']['postcode'],
        "order_billing_country" => $order_data['billing']['country'],
        "order_billing_email" => $order_data['billing']['email'],
        "order_billing_phone" => $order_data['billing']['phone'],

        "order_shipping_first_name" => $order_data['shipping']['first_name'],
        "order_shipping_last_name" => $order_data['shipping']['last_name'],
        "order_shipping_company" => $order_data['shipping']['company'],
        "order_shipping_address_1" => $order_data['shipping']['address_1'],
        "order_shipping_address_2" => $order_data['shipping']['address_2'],
        "order_shipping_city" => $order_data['shipping']['city'],
        "order_shipping_state" => $order_data['shipping']['state'],
        "order_shipping_postcode" => $order_data['shipping']['postcode'],
        "order_shipping_country" => $order_data['shipping']['country'],

        "order_shipping_houseno" => get_post_meta( $order->id, '_shipping_houseno', true ),

        "edit_url" => $post_url
    );
}

function getAllOrders(){

    if (getUrlParameterValue("status") === "NO PARAMETER"){
        $status = array('wc-pending' , 'wc-processing' , 'wc-on-hold');
    }
    else{
        $status = explode("///", getUrlParameterValue("status"));
    }

    if (getUrlParameterValue("amount") === "NO PARAMETER"){
        $ppp = '50';
    }
    else{
        $ppp = getUrlParameterValue("amount");
    }



    $args = array(
        'post_type' => 'shop_order',
        'post_status'=> $status,
        'posts_per_page' => $ppp
    );
    $my_query = new WP_Query($args);
    $orders = $my_query->posts;

    $args2 = array(
        'post_type' => 'shop_order',
        'post_status'=> array('wc-pending' , 'wc-processing' , 'wc-on-hold'),
        'posts_per_page' => 999
    );
    $my_query2 = new WP_Query($args2);
    $orders2 = $my_query2->posts;



    $order_datas = array();

    foreach ($orders as $order) {
        $order_array = getSingleOrder($order->ID);
        array_push($order_datas , $order_array);
    }
    foreach ($orders2 as $order) {
        $order_array = getSingleOrder($order->ID);
        array_push($order_datas , $order_array);
    }

    return $order_datas;
}

function get_option_bd( $order_id, $option_id ) {

    $order = wc_get_order( $order_id );
    if ( !$order ) {
        return FALSE;
    }
    $order_currency = is_callable( array( $order, 'get_currency' ) ) ? $order->get_currency() : $order->get_order_currency();
    $mt_prefix = $order_currency;

    $line_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );

    $all_epos = array();

    $all_items_in_order = array();
    foreach ( $line_items as $item_id => $item ) {


//        $_product = tc_get_product_from_item( $item, $order );
        $item_meta = function_exists( 'wc_get_order_item_meta' ) ? wc_get_order_item_meta( $item_id, '', FALSE ) : $order->get_item_meta( $item_id );
        $order_taxes = $order->get_taxes();
        $check_box_html = (version_compare( WC()->version, '2.6', '>=' )) ? '' : '<td class="check-column">&nbsp;</td>';


        $d_productid = $item->get_product_id();
        $d_product_name = $item->get_name(); // Get the product name
        $d_image = get_the_post_thumbnail_url($d_productid);
        $d_quantity = $item->get_quantity(); // Get the item quantity
        $extra_options = array();

        $single_item = array(
            "product_id" => $d_productid,
            "product_name" => $d_product_name,
            "product_image_url" => $d_image,
            "product_quantity" => $d_quantity,
            "extra_options" => $extra_options,
        );

        $has_epo = is_array( $item_meta )
            && isset( $item_meta['_tmcartepo_data'] )
            && isset( $item_meta['_tmcartepo_data'][0] )
            && isset( $item_meta['_tm_epo'] );

        $has_fee = is_array( $item_meta )
            && isset( $item_meta['_tmcartfee_data'] )
            && isset( $item_meta['_tmcartfee_data'][0] );

        if ( $has_epo || $has_fee ) {
            $current_product_id = $item['product_id'];
            $original_product_id = floatval( TM_EPO_WPML()->get_original_id( $current_product_id, 'product' ) );
            if ( TM_EPO_WPML()->get_lang() == TM_EPO_WPML()->get_default_lang() && $original_product_id != $current_product_id ) {
                $current_product_id = $original_product_id;
            }
            $wpml_translation_by_id = TM_EPO_WPML()->get_wpml_translation_by_id( $current_product_id );
        }

        if ( $has_epo ) {
            $epos = maybe_unserialize( $item_meta['_tmcartepo_data'][0] );

            if ( $epos && is_array( $epos ) ) {

                foreach ( $epos as $key => $epo ) {
                    if ( $epo && is_array( $epo ) ) {
                        if ( $epo['section'] != $option_id && $option_id !== 'all' ) {
                            continue;
                        }

                        $new_currency = FALSE;
                        if ( isset( $epo['price_per_currency'] ) ) {
                            $_current_currency_prices = $epo['price_per_currency'];
                            if ( $mt_prefix !== ''
                                && $_current_currency_prices !== ''
                                && is_array( $_current_currency_prices )
                                && isset( $_current_currency_prices[ $mt_prefix ] )
                                && $_current_currency_prices[ $mt_prefix ] != ''
                            ) {

                                $new_currency = TRUE;
                                $epo['price'] = $_current_currency_prices[ $mt_prefix ];

                            }
                        }
                        if ( !$new_currency ) {
                            $type = "";
                            if ( isset( $epo['element'] ) && isset( $epo['element']['_'] ) && isset( $epo['element']['_']['price_type'] ) ) {
                                $type = $epo['element']["_"]['price_type'];
                            }
                            $epo['price'] = apply_filters( 'wc_epo_get_current_currency_price', $epo['price'], $type );
                        }

                        if ( !isset( $epo['quantity'] ) ) {
                            $epo['quantity'] = 1;
                        }
                        if ( isset( $wpml_translation_by_id[ $epo['section'] ] ) ) {
                            $epo['name'] = $wpml_translation_by_id[ $epo['section'] ];
                        }
                        // normal (local) mode
                        if ( !isset( $epo['price_per_currency'] ) && taxonomy_exists( $epo['name'] ) ) {
                            $epo['name'] = wc_attribute_label( $epo['name'] );
                        }
                        if ( isset( $wpml_translation_by_id[ "options_" . $epo['section'] ] )
                            && is_array( $wpml_translation_by_id[ "options_" . $epo['section'] ] )
                            && !empty( $epo['multiple'] )
                            && !empty( $epo['key'] )
                        ) {

                            $pos = strrpos( $epo['key'], '_' );

                            if ( $pos !== FALSE ) {

                                $av = array_values( $wpml_translation_by_id[ "options_" . $epo['section'] ] );

                                if ( isset( $av[ substr( $epo['key'], $pos + 1 ) ] ) ) {

                                    $epo['value'] = $av[ substr( $epo['key'], $pos + 1 ) ];

                                }

                            }

                        }
                        $display_value = $epo['value'];
                        if ( is_array( $epo['value'] ) ) {
                            $display_value = array_map( 'html_entity_decode', $display_value, version_compare( phpversion(), '5.4', '<' ) ? ENT_COMPAT : (ENT_COMPAT | ENT_HTML401), 'UTF-8' );
                        } else {
                            $display_value = html_entity_decode( $display_value, version_compare( phpversion(), '5.4', '<' ) ? ENT_COMPAT : (ENT_COMPAT | ENT_HTML401), 'UTF-8' );
                        }

                        if ( TM_EPO()->tm_epo_show_image_replacement == "yes" && !empty( $epo['use_images'] ) && !empty( $epo['images'] ) && $epo['use_images'] == "images" ) {
                            $display_value = '<div class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="' . apply_filters( "tm_image_url", $epo['images'] ) . '" /></div>' . esc_attr( $display_value );
                        }

                        $display_value = apply_filters( 'tm_translate', $display_value );

                        if ( TM_EPO()->tm_epo_show_upload_image_replacement == "yes" && isset( $epo['element'] ) && isset( $epo['element']['type'] ) && $epo['element']['type'] == 'upload' ) {
                            $check = wp_check_filetype( $epo['value'] );
                            if ( !empty( $check['ext'] ) ) {
                                $image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
                                if ( in_array( $check['ext'], $image_exts ) ) {
                                    $display_value = '<a target="_blank" href="' . $display_value . '"><span class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image epo-upload-image" src="' .
                                        apply_filters( "tm_image_url", $epo['value'] ) . '" /></span></a>';
                                }
                            }
                        }

                        if ( !empty( $epo['multiple_values'] ) ) {
                            $display_value_array = explode( $epo['multiple_values'], $display_value );
                            $display_value = "";
                            foreach ( $display_value_array as $d => $dv ) {
                                $display_value .= '<span class="cpf-data-on-cart">' . $dv . '</span>';
                            }
                        }

                        $epo_name = apply_filters( 'tm_translate', $epo['name'] );
                        $epo_value = make_clickable( $display_value );
                        if ( $epo['element']['type']==='textarea' ){
                            $epo_value = trim( $epo_value );

                            $epo_value = str_replace( array( "\r\n", "\r" ), "\n", $epo_value );

                            $epo_value = preg_replace( "/\n\n+/", "\n\n", $epo_value );

                            $epo_value = array_map( 'wc_clean', explode( "\n", $epo_value ) );

                            $epo_value = implode( "\n", $epo_value );

                            $epo_value = wpautop( $epo_value );
                        }
                        $epo_quantity = ($epo['quantity'] * (float) $item_meta['_qty'][0]) . ' <small>(' . $epo['quantity'] . '&times;' . (float) $item_meta['_qty'][0] . ')</small>';
                        $epo_edit_value = TRUE;
                        $edit_buttons = TRUE;
                        $epo_edit_cost = TRUE;
                        $epo_edit_quantity = TRUE;
                        $epo_is_fee = FALSE;
                        $epo['price'] = floatval( $epo['price'] );

                        $all_epos[$item_id][$key] = $epo;
//                        include('views/html-tm-epo-order-item.php');
                    }
                    $extra_options = array(
                        "value" => $epo_value,
                        "quantity" => $item_meta['_qty'][0]
                    );

                    array_push($single_item["extra_options"] , $extra_options);

                }
            }
        }

        if ( $has_fee ) {
            $epos = maybe_unserialize( $item_meta['_tmcartfee_data'][0] );
            if ( isset( $epos[0] ) ) {
                $epos = $epos[0];
            } else {
                $epos = FALSE;
            }

            if ( $epos && is_array( $epos ) && !empty( $epos[0] ) ) {

                foreach ( $epos as $key => $epo ) {
                    if ( $epo && is_array( $epo ) ) {
                        if ( $epo['section'] != $option_id && $option_id !== 'all' ) {
                            continue;
                        }
                        if ( !isset( $epo['quantity'] ) ) {
                            $epo['quantity'] = 1;
                        }
                        if ( isset( $wpml_translation_by_id[ $epo['section'] ] ) ) {
                            $epo['name'] = $wpml_translation_by_id[ $epo['section'] ];
                        }
                        if ( isset( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) && is_array( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) && !empty( $epo['multiple'] ) && !empty( $epo['key'] ) ) {
                            $pos = strrpos( $epo['key'], '_' );
                            if ( $pos !== FALSE ) {
                                $av = array_values( $wpml_translation_by_id[ "options_" . $epo['section'] ] );
                                if ( isset( $av[ substr( $epo['key'], $pos + 1 ) ] ) ) {
                                    $epo['value'] = $av[ substr( $epo['key'], $pos + 1 ) ];
                                    if ( !empty( $epo['use_images'] ) && !empty( $epo['images'] ) && $epo['use_images'] == "images" ) {
                                        $epo['value'] = '<div class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="' . apply_filters( "tm_image_url", $epo['images'] ) . '" /></div>' . $epo['value'];
                                    }
                                }
                            }
                        }

                        $epo_name = apply_filters( 'tm_translate', $epo['name'] );
                        $epo_value = apply_filters( 'tm_translate', $epo['value'] );
                        $epo_quantity = ($epo['quantity'] * (float) $item_meta['_qty'][0]) . ' <small>(' . $epo['quantity'] . '&times;' . (float) $item_meta['_qty'][0] . ')</small>';
                        $epo_edit_value = FALSE;
                        $edit_buttons = FALSE;
                        $epo_edit_cost = FALSE;
                        $epo_edit_quantity = FALSE;
                        $epo_is_fee = TRUE;
                        $epo['price'] = floatval( $epo['price'] );

//                        $all_epos[$item_id][$key] = $epo;
//                        echo "<br><br>" . $epo_value . "<br><br>";
//                        echo $epo_quantity;

//                        echo $epo_quantity;
//
////                        echo $epo['price'];
//                        echo "<br><br>";
//                        echo $epo['price'];
//                        echo "<br><br>";
////                        var_dump($epo_edit_cost) ;


                        $extra_options = array(
                            "value" => $epo_value,
                            "quantity" => $epo['quantity']
                        );

                        array_push($single_item["extra_options"] , $extra_options);
//                        include('views/html-tm-epo-order-item.php');
                    }
                }
            }
        }
//        echo "<br><br><br><br>";
        array_push($all_items_in_order , $single_item);
    }

//    var_dump($all_items_in_order);

    return $all_items_in_order;

}

?>
