<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('WP_List_Table') ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ResObj_Reservation_List_Table extends WP_List_Table {

	private $row_border;

	public function no_items() {
		_e( 'Reservations not found.' );
  	}

  	public function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'order_id':
			case 'user':
			case 'date':
			case 'start_time':
			case 'end_time':
			case 'people':
			case 'socks':
				return $item[ $column_name ];
		default:
			return print_r( $item, true );
		}
	}

	public function get_sortable_columns() {
        $sortable_columns = [
        	'order_id'	=> [ 'order_id', false ]
        ];
        return $sortable_columns;
    }

	public function get_columns(){
		$columns = array(
			'order_id' 		=> __('Order ID'),
			'user'    		=> __('User'),
			'date'    		=> __('Date'),
			'start_time'	=> __('Start time'),
			'end_time'		=> __('End time'),
			'people'		=> __('People'),
			'socks'			=> __('Socks'),
		);
		return $columns;
	}

	public function usort_reorder( $a, $b ) {
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'date';
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'desc';
		$result = strcmp( $a[$orderby], $b[$orderby] );
		return ( $order === 'asc' ) ? $result : -$result;
	}

	public function prepare_items() {
		global $wpdb;

		$columns = $this->get_columns();
		$hidden = array();
        $sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		if( !empty($_GET['date']) ) {
			$date = date( 'Y-m-d', strtotime($_GET['date']) );
		}
		if( !empty($_GET['time-hh']) && $_GET['time-hh'] != '-1' ) {
			$hh = $_GET['time-hh'];
			$min = ( !empty($_GET['time-min']) && $_GET['time-min'] != '-1' ? $_GET['time-min'] : '00' );
			$time = $hh.":".$min.':00';
		}

		$sql_where = "";
		if( isset($time) ) {
			if( !isset($date) ) {
				$date = current_time( 'Y-m-d' );
			}
			$sql_where = "WHERE book_time = '$time' AND book_date = '$date'";
		} elseif( isset($date) ) {
			$sql_where = "WHERE book_date = '$date'";
		}

		$table_name = $wpdb->prefix . "resobj_booking";
		$reservations = $wpdb->get_results("SELECT * FROM $table_name $sql_where ORDER BY 'book_date'");

		$user_array = array();
		if( !empty($_GET['s']) ) {
			$search = $_GET['s'];
	        $reservations = array_filter($reservations, function($e) use ($search) {

	        	$order = wc_get_order( $e->order_id );
	    		$user_id = $order->get_user_id();

	    		if( $user_id ) {
	    			$user_data = get_userdata( $user_id );
	    			$user_name = ucfirst($user_data->first_name).' '.ucfirst($user_data->last_name);
	    			$user_array[ $e->order_id ] = '<a href="'.get_edit_user_link( $user_id ).'"><strong>'.$user_name.'</strong></a>';
	    		} else {

	    			$order_billing = $order->get_data()['billing'];
	    			
	    			if( !empty($order_billing['first_name']) || !empty($order_billing['last_name']) ) {
	    				$user_name = ucfirst($order_billing['first_name']).' '.ucfirst($order_billing['last_name']);
	    			} else {
	    				$user_name = __('Guest');
	    			}
	    			$user_array[ $e->order_id ] = $user_name;
	    		}

	        	if ( strpos($e->order_id, $search) !== false || stripos($user_name, $search) !== false ) {
				    return true;
				}
			});
	    }

		$data = array();
		foreach( $reservations as $info ) {

			if( empty($user_array) ) {
				$order = wc_get_order( $info->order_id );
	    		$user_id = $order->get_user_id();
	    		if( $user_id ) {
	    			$user_data = get_userdata( $user_id );
	    			$user = '<a href="'.get_edit_user_link( $user_id ).'"><strong>'.ucfirst($user_data->first_name).' '.ucfirst($user_data->last_name).'</strong></a>';
	    		} else {

	    			$order_billing = $order->get_data()['billing'];

	    			if( !empty($order_billing['first_name']) || !empty($order_billing['last_name']) ) {
	    				$user = ucfirst($order_billing['first_name']).' '.ucfirst($order_billing['last_name']);
	    			} else {
	    				$user = __('Guest');
	    			}
	    		}
			} else {
				$user = $user_array[ $info->order_id ];
			}

			$data[] = [
				'order_id' 		=> '<a href="'.get_edit_post_link( $info->order_id ).'"><strong>'.$info->order_id.'</strong></a>',
				'user' 			=> $user,
				'date' 			=> $info->book_date.' <span style="display:none;">'.date( 'H:i', strtotime($info->book_time) ).'</span>',
				'start_time' 	=> date( 'H:i', strtotime($info->book_time) ),
				'end_time'		=> date( 'H:i', strtotime($info->book_time) + ($info->jump_duration * 60) ),
				'people' 		=> $info->people_amount,
				'socks'  		=> $info->socks_amount,
			];
		}
		usort( $data, array( $this, 'usort_reorder' ) );

		$per_page_option = get_current_screen()->get_option('per_page');
		$per_page = get_user_meta( get_current_user_id(), $per_page_option['option'], true ) ?: $per_page_option['default'];

		$current_page = $this->get_pagenum();
		$total_items = count( $data );
		$data = array_slice( $data, (($current_page - 1) * $per_page), $per_page );

		$this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

		$this->items = $data;
	}

	public function single_row( $item ) {

		$class = '';
		if( !empty( $this->row_border ) && $this->row_border != $item['date'] ) {
			$class = ' class="separate"';
		}
		$this->row_border = $item['date'];

		echo '<tr'.$class.'>';
		$this->single_row_columns( $item );
		echo "</tr>";
    }

	public function extra_tablenav( $which ) {
		if ( $which == "top" ): ?>

			<?php
				$cur_hh = ( !empty($_GET['time-hh']) ? $_GET['time-hh'] : '-1' );
				$cur_min = ( !empty($_GET['time-min']) ? $_GET['time-min'] : '-1' );
			?>

			<div class="alignleft actions">
				<input type="text" id="res_obj-datepicker" autocomplete="off" name="date" placeholder="Select Date" value="<?php echo ( !empty($_GET['date']) ? $_GET['date'] : '' ) ; ?>">

				<select name="time-hh">
					<option value="-1"><?php _e('Hours'); ?></option>
					<?php for( $i = 6; $i <= 23; $i++ ): ?>
						<?php $hours = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
						<option value="<?php echo $hours; ?>" <?php echo ( $cur_hh == $hours ? 'selected' : '' ); ?>>
							<?php echo $hours; ?>
						</option>
					<?php endfor; ?>
				</select>
				<select name="time-min">
					<option value="-1"><?php _e('Minutes'); ?></option>
					<?php for( $i = 0; $i <= 3; $i++ ): $minutes =  str_pad($i*15, 2, '0', STR_PAD_LEFT); ?>
						<option value="<?php echo $minutes; ?>" <?php echo ( $cur_min == $minutes ? 'selected' : '' ); ?>>
							<?php echo $minutes; ?>
						</option>
					<?php endfor; ?>
				</select>

				<input type="submit" name="filter_action" class="button" value="Filter">
			</div>
	    <?php endif;
	}
}