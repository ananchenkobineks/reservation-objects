<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ResObj_Post_Type {

	public function __construct() {
		// Register post type
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		// Register meta-boxes
		add_action( 'add_meta_boxes', array( $this, 'data_meta_boxes' ) );
		// Save Attributes
		add_action( 'save_post', array( $this, 'save_post_attributes' ) );
	}

	public function register_custom_post_type() {
        $args = array(
            'labels'				=> array(
	            'name'                => 'Reservation Objects',
	            'singular_name'       => 'Reservation Object',
	            'all_items'           => 'All Objects',
	            'add_new'			  => 'Add Object',
	            'add_new_item'		  => 'Add new Object',
	            'edit'                => 'Edit',
				'edit_item'           => 'Edit Object',
				'new_item'            => 'New Object',
				'view_item'           => 'View Object',
				'search_items'        => 'Search Objects',
				'not_found'           => 'No objects found.',
				'not_found_in_trash'  => 'No objects found in Trash.',
				'menu_name'           => 'Reservation Objects',
	        ),
	        'public'              	=> false,
	        'publicly_queryable'  	=> false,
            'rewrite'				=> false,
            'supports'            	=> array('title'),
            'menu_position'       	=> 5,
            'menu_icon'           	=> 'dashicons-clipboard',
            'show_ui'             	=> true,
            'show_in_menu'        	=> true,
            'show_in_admin_bar'   	=> 'show_in_menu',
            'show_in_nav_menus'   	=> false,
            'hierarchical'        	=> false,
            'description'         	=> '',
            'exclude_from_search' 	=> true,
            'has_archive'         	=> false,
            'query_var'           	=> false
        );

        register_post_type( 'res_obj', $args );
	}

	public function data_meta_boxes() {

		add_meta_box( 'res_obj_attributes',
			'Attributes of the object',
			array(
				$this, 'res_obj_attributes'
			),
			'res_obj', 'normal', 'high'
		);
	}

	public function res_obj_attributes( $post, $meta ) {
		$bookable = get_post_meta( $post->ID, 'res_obj_bookable', true );
		?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th>
						<label>Bookable From (start time)</label>
					</th>
					<td>
						<select name="bookable[from-hh]">
							<?php for( $i = 1; $i <= 24; $i++ ): $hours = $this->time_value( $i ); ?>
								<option value="<?php echo $hours; ?>" <?php echo ( $bookable && $bookable['from-hh'] == $hours ? 'selected' : ''); ?>>
									<?php echo $hours; ?>
								</option>
							<?php endfor; ?>
						</select>
						<select name="bookable[from-mm]">
							<?php for( $i = 0; $i <= 3; $i++ ): $minutes = $this->time_value( $i*15 ); ?>
								<option value="<?php echo $minutes; ?>" <?php echo ( $bookable && $bookable['from-mm'] == $minutes ? 'selected' : ''); ?>>
									<?php echo $minutes; ?>
								</option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label>Bookable Till (end time)</label>
					</th>
					<td>
						<select name="bookable[till-hh]">
							<?php for( $i = 1; $i <= 24; $i++ ): $hours = $this->time_value( $i ); ?>
								<option value="<?php echo $hours; ?>" <?php echo ( $bookable && $bookable['till-hh'] == $hours ? 'selected' : ''); ?>>
									<?php echo $hours; ?>
								</option>
							<?php endfor; ?>
						</select>
						<select name="bookable[till-mm]">
							<?php for( $i = 0; $i <= 3; $i++ ): $minutes = $this->time_value( $i*15 ); ?>
								<option value="<?php echo $minutes; ?>" <?php echo ( $bookable && $bookable['till-mm'] == $minutes ? 'selected' : ''); ?>>
									<?php echo $minutes; ?>
								</option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label>Amount of people as the same time on this location</label>
					</th>
					<td>
						<input type="number" name="bookable[people]" value="<?php echo $bookable ? $bookable['people'] : 100; ?>">
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label>Bookable Periods</label>
					</th>
					<td>
						<select name="bookable[period-hh]">
							<?php for( $i = 0; $i <= 24; $i++ ): $hours = $this->time_value( $i ); ?>
								<option value="<?php echo $hours; ?>" <?php echo ( $bookable && $bookable['period-hh'] == $hours ? 'selected' : ''); ?>>
									<?php echo $hours; ?>
								</option>
							<?php endfor; ?>
						</select>
						<select name="bookable[period-mm]">
							<?php for( $i = 0; $i <= 3; $i++ ): $minutes = $this->time_value( $i*15 ); ?>
								<option value="<?php echo $minutes; ?>" <?php echo ( $bookable && $bookable['period-mm'] == $minutes ? 'selected' : ''); ?>>
									<?php echo $minutes; ?>
								</option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th>
						<label>Maximum number of people per ticket</label>
					</th>
					<td>
						<input type="number" name="bookable[people-per-ticket]" value="<?php echo $bookable ? $bookable['people-per-ticket'] : 20; ?>">
					</td>
				</tr>
			</tbody>
		</table>
	<?php
	}

	public function save_post_attributes( $post_id ) {
		if ( ! isset( $_POST['bookable'] ) )
			return;

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

		if( ! current_user_can( 'edit_post', $post_id ) )
			return;

		update_post_meta( $post_id, 'res_obj_bookable', $_POST['bookable'] );
	}

	private function time_value( $val ) {
		return str_pad($val, 2, '0', STR_PAD_LEFT);
	}
}

return new ResObj_Post_Type();