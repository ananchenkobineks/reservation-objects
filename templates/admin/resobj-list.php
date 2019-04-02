<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

$reservation_list_table = new ResObj_Reservation_List_Table();
$reservation_list_table->prepare_items();

?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php _e('Reservation List'); ?></h1>
	<form method="get">
		<input type="hidden" name="post_type" value="res_obj">
		<input type="hidden" name="page" value="reservation_list">
	    <?php 
			$reservation_list_table->search_box('Search', 'search');
			$reservation_list_table->display();
		?>
  	</form>
</div>