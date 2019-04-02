<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResObj_Install {

	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		self::create_tables();
	}

	private static function create_tables() {
		global $wpdb;

		$table_name = $wpdb->prefix . "resobj_booking";

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id int(11) NOT NULL AUTO_INCREMENT,
			order_id int(11) NOT NULL,
			book_date date DEFAULT NULL,
			book_time time DEFAULT NULL,
			people_amount int(11) NOT NULL,
			socks_amount int(11) NOT NULL,
			jump_duration int(11) NOT NULL,
			cancelled_order tinyint(1) DEFAULT 0,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
}

return;