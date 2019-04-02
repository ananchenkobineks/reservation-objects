<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResObj_WC_Checkout {

	public function __construct() {

		// Add meta to order
		add_action( 'woocommerce_add_order_item_meta', array($this, 'add_order_meta'), 10, 2 );

		add_filter( 'woocommerce_breadcrumb_defaults', array($this, 'implement_steps'), 1000 );
	}

	public function add_order_meta( $item_id, $values ) {
		global $wpdb;

		if( isset($values['res_obj']) ) {
			$res_obj = $values['res_obj'];
			$order_id = wc_get_order_id_by_order_item_id( $item_id );
			update_post_meta( $order_id, 'res_obj', $res_obj );

			ResObj_Helper::insert_obj_to_db( $order_id, $res_obj );
		}
	}

	public function implement_steps( $defaults ) {

		$selected_date = "";
		$selected_time = "";
		$is_checkout = is_checkout();
		$is_product = (is_product() && is_single() ? true : false);
		$preselect_steps = false;
		$show_steps = false;

		if( $is_checkout || $is_product ) {
			$get_cart = WC()->cart->get_cart();
			foreach( $get_cart as $cart ) {
				if( !empty($cart['res_obj']) ) {
					if( $is_checkout || ($is_product && $cart['product_id'] == get_the_ID()) ) {
						$selected_date = $cart['res_obj']['start_date'];
						$selected_time = $cart['res_obj']['start_time'];
						$preselect_steps = true;
						$product_url = get_permalink( $cart['product_id'] );
						break;
					}
				}
			}
		}

		if( !$preselect_steps && $is_product ) {
			$res_obj = get_post_meta( get_the_ID(), '_reservation_object', true );
			if( $res_obj ) {
				$show_steps = true;
			}
		}

		if( $show_steps || $preselect_steps ) {

			$steps_html = '<div class="resobj-steps"><ul>';
			$steps_html .= '<li'. ($show_steps ? ' class="active"' : '') .' data-step="selectDate">'.__('Choose Date').'<span>'.$selected_date.'</span></li>';
			$steps_html .= '<li data-step="selectTime">'.__('Choose Time').'<span>'.$selected_time.'</span></li>';

			if( $is_checkout ) {
				$steps_html .= '<li data-step="preOrder"><a href="'.$product_url.'">'.__('Jumpers').'</a></li>';
			} else {
				$steps_html .= '<li'. ($preselect_steps ? ' class="active"' : '') .' data-step="preOrder">'.__('Jumpers').'</li>';
			}

			$steps_html .= '<li'. ($is_checkout ? ' class="active"' : '') .'>'.__('Checkout').'</li>';
			$steps_html .= '</ul></div>';

			$steps_html .= '<style type="text/css">
				.resobj-steps {
					text-align: center;
				}
				.resobj-steps ul {
					list-style: none;
					margin-left: 0;
					margin-bottom: 45px;
					overflow: hidden;
					display: inline-block;
				}
				.resobj-steps li { float: left; }
				.resobj-steps ul li+li:before {
					padding-left: 10px;
				    color: #ccc;
				    content: "\232A\00a0";
				    font-size: 12px;
				}
				.resobj-steps li.active {
					color: #f15d22;
				}
				.resobj-steps li span {
					display: block;
					font-size: 12px;
					min-height: 20px;
				}
				.resobj-steps ul li+li span {
					padding-left: 24px;
				}
			</style>';

			$defaults['wrap_after'] .= $steps_html;
		}

		return $defaults;
	}
}

return new ResObj_WC_Checkout();