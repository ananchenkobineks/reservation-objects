<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResObj_Helper {

	public static function insert_obj_to_db( $order_id, $res_obj ) {
		global $wpdb;

		$table_name = $wpdb->prefix . "resobj_booking";
		$wpdb->insert(
			$table_name,
			array(
				'order_id' 		=> $order_id,
				'book_date' 	=> $res_obj['start_date'],
				'book_time' 	=> $res_obj['start_time'],
				'people_amount' => $res_obj['people_amount'],
				'socks_amount'  => $res_obj['socks_amount'],
				'jump_duration' => $res_obj['jump_duration'],
			),
			array('%d','%s','%s','%d','%d')
		);
	}

	public static function get_available_time( $post_id, $book_date ) {
		global $wpdb;

		$available_data = array();

		$reserv_object_id = get_post_meta( $post_id, '_reservation_object', true );
		$bookable = get_post_meta( $reserv_object_id, 'res_obj_bookable', true );

		$period = (intval($bookable['period-hh']) * 60 * 60) + (intval($bookable['period-mm']) * 60);
		$from_time = strtotime("{$bookable['from-hh']}:{$bookable['from-mm']}");
		$till_time = strtotime("{$bookable['till-hh']}:{$bookable['till-mm']}") + $period;

		$table_name = $wpdb->prefix . "resobj_booking";
		$booking_data = $wpdb->get_results( "SELECT * FROM $table_name WHERE book_date = '$book_date' ORDER BY book_time ASC" );

		$default_duration = $bookable['period-hh'] * 60 + $bookable['period-mm'];

		$available_time = array();
		foreach( $booking_data as $data ) {
			$book_time = $data->book_time;
			$steps = $data->jump_duration / $default_duration;

			$book_time = date( 'H:i', strtotime($book_time) );

			if( isset($available_time[ $book_time ]) ) {
				$old_people_amount = $available_time[ $book_time ];
				$available_time[ $book_time ] = $old_people_amount + $data->people_amount;
			} else {
				$available_time[ $book_time ] = $data->people_amount;
			}

			if( $steps >= 2 ) {
				for( $i = 2; $i <= $steps; $i++ ) {

					$next_time = strtotime($book_time) + $default_duration * 60;
					$next_time = date( 'H:i', $next_time );

					if( isset($available_time[ $next_time ]) ) {
						$old_people_amount = $available_time[ $next_time ];
						$available_time[ $next_time ] = $old_people_amount + $data->people_amount;
					} else {
						$available_time[ $next_time ] = $data->people_amount;
					}
					$book_time = $next_time;
				}
			}
		}

		$time_data = array(
			'bookable' => $bookable
		);
		for ( $i = $from_time; $i <= $till_time; $i = $i + $period ) {
			$time = date( 'H:i', $i );
			$disabled = false;

			if( $bookable['people'] >= 10 ) {
				$available_text = __('more than 10 seats available', 'woocommerce-reservation-objects');
			} else {
				$available_text = sprintf(__('only %d available', 'woocommerce-reservation-objects'), $bookable['people']);
			}

			if( isset($available_time[ $time ]) ) {
				$seats = $available_time[ $time ];

				$seats_left = $bookable['people'] - $seats;

				if( $seats_left <= 10 && $seats_left >= 1 ) {
					$available_text = sprintf(__('only %d available', 'woocommerce-reservation-objects'), $seats_left);
				} elseif( !$seats_left ) {
					$available_text = __('no available', 'woocommerce-reservation-objects');
					$disabled = true;
				}
			} else {
				$seats_left = $bookable['people'];
			}

			$time_data['times'][] = array(
				'time' => $time,
				'available_text' => $available_text,
				'available_seats' => (int)$seats_left,
				'people_per_ticket' => $bookable['people-per-ticket'],
				'disabled' => $disabled
			);
		}

		return $time_data;
	}
}