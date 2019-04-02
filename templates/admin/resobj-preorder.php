<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<form action="" method="post" id="preorder-jump">
	<h3>Jump duration</h3>
	<select name="product_variation_id" id="product_variation_id">
	<?php
		$i = 0; foreach( $available_variations as $variation ) {
			$time = current($variation['attributes']);
			echo "<option value='{$variation['variation_id']}' data-price='{$variation['display_price']}' data-iteration='{$i}'>{$time} Minutes</option>";
			$i++;
		}
	?>
	</select>
	<h3>Number of people</h3>
	<input type="number" name="product_quantity" id="people_per_ticket" value="1" min="1" max="<?php echo $current_time['people_per_ticket']; ?>">
	<h3>Number of pairs of socks</h3>
	<input type="number" name="socks_quantity" id="" value="0" min="0">
	<input type="hidden" name="resobj_add_to_cart" value="true">
	<p></p>
	<input type="submit" value="Create Order">
	<div class="form-message"></div>
</form>