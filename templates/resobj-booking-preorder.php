<?php
	if ( ! defined( 'ABSPATH' ) ) { exit; }

	if( $preselect ) {
		$product = get_product();
		$max_per_ticket = $available_data['bookable']['people-per-ticket'];
	} else {
		$product = get_product($_POST['postID']);
		$max_per_ticket = $_POST['available_data']['bookable']['people-per-ticket'];
	}
	$available_variations = $product->get_available_variations();
?>

<?php if( $preselect ): ?>
<div id="resobj-container">
<?php endif; ?>

	<div class="steps">
		<button type="button" id="prev-step" data-step="selectTime">< <?php _e('Go to previous step'); ?></button>
	</div>
	<form action="" method="post" id="preorder-jump">
		<h3>Jump duration</h3>
		<select name="product_variation_id" id="product_variation_id">
		<?php
			$i = 0; foreach( $available_variations as $variation ) {
				$time = current($variation['attributes']);
				echo "<option value='{$variation['variation_id']}' data-price='{$variation['display_price']}' data-iteration='{$i}' ". ($time == $preselect['jump_duration'] ? 'selected' : '').">{$time} ". __('Minutes','woocommerce-reservation-objects') ."</option>";
				$i++;
			}
		?>
		</select>
		<h3>Number of people</h3>
		<input type="number" name="product_quantity" id="people_per_ticket" value="<?php echo ( $preselect ? $preselect['people_amount'] : 1 ); ?>" min="1" max="<?php echo $max_per_ticket; ?>">
		<h3>Number of pairs of socks</h3>
		<input type="number" name="socks_quantity" id="" value="<?php echo ( $preselect ? $preselect['socks_amount'] : 0 ); ?>" min="0">
		<input type="hidden" name="resobj_add_to_cart" value="true">
		<p></p>
		<input type="submit" value="Submit">
		<div class="form-message"></div>
	</form>

<?php if( $preselect ): ?>
	<script type="text/javascript">var available_data = '<?php echo json_encode($available_data); ?>';</script>
</div>
<?php endif; ?>