<?php
	if ( ! defined( 'ABSPATH' ) ) { exit; }
	$max_seats = $available_data['max_seats'];
?>
<table>
	<tbody>
		<?php foreach( $available_data['times'] as $data ): ?>
			<tr>
				<td><?php echo $data['time']; ?></td>
				<td><?php echo $data['available_text']; ?></td>
				<td>
					<button type="button" class="book" data-time="<?php echo $data['time']; ?>" <?php echo ($data['disabled'] ? 'disabled' : ''); ?>><?php _e('Book'); ?></button>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<script type="text/javascript">
	var available_data = '<?php echo json_encode($available_data); ?>';
</script>