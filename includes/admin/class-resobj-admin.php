<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResObj_Admin {

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'register_reserv_obj_submenu_page' ) );

		add_action( 'wp_ajax_resobj_admin_available_time', array( $this, 'resobj_admin_available_time_callback' ) );
		add_action( 'wp_ajax_resobj_admin_preorder_data', array( $this, 'resobj_admin_preorder_data_callback' ) );

		add_action( 'admin_post_resobj_order_process', array( $this, 'resobj_order_process' ) );

		add_filter( 'set-screen-option', function( $status, $option, $value ){
			return ( $option == 'reservations_per_page' ) ? (int) $value : $status;
		}, 10, 3 );
	}

	public function register_reserv_obj_submenu_page() {

		add_submenu_page(
			'edit.php?post_type=res_obj',
			'Create Order',
			'Create Order',
			'manage_options', 'create-order', array( $this, 'create_res_order' )
		);

		$reservation_list_hook = add_submenu_page(
			'edit.php?post_type=res_obj',
			'Reservation List',
			'Reservation List',
			'manage_options', 'reservation_list', array( $this, 'reservation_list' )
		);
		add_action( "load-$reservation_list_hook", function() {
			add_screen_option( 'per_page', array(
				'label' => __('Number of items per page:'),
				'default' => 20,
				'option' => 'reservations_per_page'
			) );
		} );
	}

	public function create_res_order() {

		include RES_OBJ_TEMPLATE.'admin/resobj-create-order.php';
	}

	public function resobj_admin_available_time_callback() {

		$available_data = ResObj_Helper::get_available_time( $_POST['postID'], $_POST['selectedDate'] );
		include RES_OBJ_TEMPLATE.'admin/resobj-select-time.php';
		exit;
	}

	public function resobj_admin_preorder_data_callback() {

		$product = get_product($_POST['postID']);
		$available_variations = $product->get_available_variations();
		$current_time = $_POST['available_data']['times'][0];

		include RES_OBJ_TEMPLATE.'admin/resobj-preorder.php';
		exit;
	}

	public function resobj_order_process() {

		$data = $_REQUEST;

		if( wp_verify_nonce( $data['_wpnonce'], 'create_order' ) ) {

			$booking_product = new WC_Product_Variable( $data['res-prod'] );
			$booking_product_variations = $booking_product->get_available_variations();

			$order_product_variations = array();

			foreach( $booking_product_variations as $variation ) {
			    if ( $variation['variation_id'] == $data['product_variation_id'] ) {
			        $order_product_variations['variation'] = $variation['attributes'];
			    }
			}
			$order_product = new WC_Product_Variation( $data['product_variation_id'] );

			$order = wc_create_order();
		    $order->add_product($order_product, $data['product_quantity'], $order_product_variations);
		    if( $data['socks_quantity'] ) {
				$socks_product_id = get_post_meta( $data['res-prod'], '_reservation_object_socks_product', true );
				$order->add_product( get_product($socks_product_id), $data['socks_quantity'] );
			}
		    $order->calculate_totals();

		    $res_obj = array(
				'start_time' => $data['start_time'],
				'start_date' => $data['start_date'],
				'people_amount' => $data['product_quantity'],
				'socks_amount' => $data['socks_quantity'],
				'jump_duration' => current($order_product_variations['variation'])
			);

		    update_post_meta( $order->ID, 'res_obj', $res_obj );
		    ResObj_Helper::insert_obj_to_db( $order->ID, $res_obj );

		    wp_safe_redirect( "/wp-admin/post.php?post={$order->ID}&action=edit" );
		    exit;
		}
	}

	public function reservation_list() {

		if( ! empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg(array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI'])) );
			exit;
		}
		if( !isset($_GET['date']) ) {
			wp_redirect( add_query_arg(array('date' => current_time( 'm/d/Y' )), stripslashes($_SERVER['REQUEST_URI'])) );
			exit;
		}

		include RES_OBJ_TEMPLATE.'admin/resobj-list.php';
	}
}

return new ResObj_Admin();