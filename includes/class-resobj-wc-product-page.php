<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResObj_WC_Prod_Page {

	public function __construct() {
		add_action( 'the_post', array( $this, 'page_hooks' ) );

		add_action( 'wp_ajax_resobj_available_time', array( $this, 'resobj_available_time_callback' ) );
		add_action( 'wp_ajax_nopriv_resobj_available_time', array( $this, 'resobj_available_time_callback' ) );

		add_action( 'wp_ajax_resobj_preorder_data', array( $this, 'resobj_preorder_data_callback' ) );
		add_action( 'wp_ajax_nopriv_resobj_preorder_data', array( $this, 'resobj_preorder_data_callback' ) );

		add_action( 'template_redirect', array( $this, 'add_resobj_product_to_cart' ) );
	}

	public function page_hooks() {

		$res_obj = get_post_meta( get_the_ID(), '_reservation_object', true );
		if( $res_obj ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

			add_action( 'woocommerce_single_product_summary', array( $this, 'booking_procedure' ), 30 );
		}
	}

	public function booking_procedure() {

		$preselect = false;

		$get_cart = WC()->cart->get_cart();
        foreach( $get_cart as $cart ) {
            if( !empty($cart['res_obj']) && get_the_ID() == $cart['product_id'] ) {
                $preselect = $cart['res_obj'];
                break;
            }
        }

        if( $preselect ) {
        	$available_data = ResObj_Helper::get_available_time( get_the_ID(), $cart['res_obj']['start_date'] );
        	require_once( RES_OBJ_TEMPLATE . 'resobj-booking-preorder.php' );
        } else {
        	require_once( RES_OBJ_TEMPLATE . 'resobj-booking-select-date.php' );
        }
	}

	public function resobj_available_time_callback() {

		$available_data = ResObj_Helper::get_available_time( $_POST['postID'], $_POST['selectedDate'] );
		include RES_OBJ_TEMPLATE.'resobj-booking-select-time.php';
		exit;
	}

	public function resobj_preorder_data_callback() {

		include RES_OBJ_TEMPLATE.'resobj-booking-preorder.php';
		exit;	
	}

	public function add_resobj_product_to_cart() {

		if( !empty($_POST) && isset($_POST['resobj_add_to_cart']) && is_product() ) {
			
			$product_id = get_the_ID();
			$reserv_object_id = get_post_meta( $product_id, '_reservation_object', true );

			if( $reserv_object_id ) {
				$bookable = get_post_meta( $reserv_object_id, 'res_obj_bookable', true );

				WC()->cart->empty_cart();

				$variation = wc_get_product($_POST['product_variation_id']);
				$cart_item_data = array(
					'res_obj' => array(
						'start_time' => $_POST['start_time'],
						'start_date' => $_POST['start_date'],
						'people_amount' => $_POST['product_quantity'],
						'socks_amount' => $_POST['socks_quantity'],
						'jump_duration' => current($variation->attributes)
					)
				);
				WC()->cart->add_to_cart( $product_id, $_POST['product_quantity'], $_POST['product_variation_id'], array(), $cart_item_data );

				if( $_POST['socks_quantity'] ) {
					$socks_product_id = get_post_meta( $product_id, '_reservation_object_socks_product', true );

					WC()->cart->add_to_cart( $socks_product_id, $_POST['socks_quantity'] );
				}

				$checkout_url = WC()->cart->get_checkout_url();
				wp_redirect( $checkout_url );
				exit;
			}
		}
	}

}

return new ResObj_WC_Prod_Page();