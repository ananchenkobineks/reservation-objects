<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

$wp_query = new WP_Query(
	array(
		'post_type' => 'product',
		'posts_per_page' => -1,
	    'meta_key' => '_reservation_object',
	    'meta_value' => 0,
	    'meta_compare' => 'NOT IN'
	)
);

$url = wp_nonce_url( admin_url( 'admin-post.php' ), 'create_order' );
$query = build_query( array( 'action' => 'resobj_order_process' ) );
$action_url = "$url&$query";
?>
<div class="wrap">
	<h2><?php _e('Create Order'); ?></h2>
	<form action="<?php echo $action_url; ?>" method="post" id="resobj-order">
		<table class="form-table">
			<tbody>
				<tr class="select-product">
					<th><?php _e('Select Product'); ?></th>
					<td>
						<select name="res-prod" id="select-prod">
							<option value="0"><?php _e('No product selected'); ?></option>
							<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
								<option value="<?php the_ID(); ?>"><?php the_title(); ?></option>
							<?php endwhile; ?>
						</select>
					</td>
					<td id="resobj-container"></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>