<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResObj_WC_Prod_Tab {

	public function __construct() {
		// Add new tab to product
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_reservation_product_data_tab' ) );
		// add data to the created tab
		add_action( 'woocommerce_product_data_panels', array( $this, 'add_reservation_product_data_fields' ) );
		// Save data from tab
		add_action( 'save_post', array( $this, 'save_reservation_product_data' ) );
	}

	public function add_reservation_product_data_tab( $product_data_tabs ) {

		$product_data_tabs['reservation-object-tab'] = array(
			'label' => 'Reservation',
			'target' => 'reservation_tab',
		);
		return $product_data_tabs;
	}

	public function add_reservation_product_data_fields() {
		global $woocommerce, $post;

		$wp_query = new WP_Query;
		$res_obj_posts = $wp_query->query(array(
			'post_type' => 'res_obj',
			'posts_per_page' => -1
		));
		$obj_id = get_post_meta( $post->ID, '_reservation_object', true );
		?>
		<div id="reservation_tab" class="panel woocommerce_options_panel" style="display: none;">
			<div class="options_group">
				<p class="form-field">
					<label for="_reservation_object">Reservation Object</label>
					<select class="short" id="_reservation_object" name="_reservation_object">
						<option value="0">No object selected</option>
						<?php foreach ( $res_obj_posts as $res_post ): ?>
							<option value="<?php echo $res_post->ID; ?>" <?php echo ($obj_id && $obj_id == $res_post->ID ? 'selected' : ''); ?>>
								<?php echo $res_post->post_title; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
				    <label for="socks_product"><?php _e( 'Socks' ); ?></label>
				    <select class="wc-product-search" style="width: 50%;" id="socks_product" name="socks_product" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
				        <?php
				            $product_id = get_post_meta( $post->ID, '_reservation_object_socks_product', true );
				            if( !empty($product_id) ) {
				            	$product = wc_get_product( $product_id );
								echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
				            }
				        ?>
				    </select> <?php echo wc_help_tip( __( 'Select the product that is responsible for socks.' ) ); ?>
				</p>
			</div>
		</div>
	<?php
	}

	public function save_reservation_product_data( $post_id ) {
		if ( ! isset( $_POST['_reservation_object'] ) )
			return;

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

		if( ! current_user_can( 'edit_post', $post_id ) )
			return;

		update_post_meta( $post_id, '_reservation_object', $_POST['_reservation_object'] );
		update_post_meta( $post_id, '_reservation_object_socks_product', $_POST['socks_product'] );
	}
}

return new ResObj_WC_Prod_Tab();