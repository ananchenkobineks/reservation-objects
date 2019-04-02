jQuery(function($){

	/*** Create Order ***/

	var post_id = 0,
		selectedDate,
		dataTime,
		validForm = true;

	$( "#select-prod" ).on('change', function() {
		post_id = $("option:selected", this).val();

		if( post_id != 0 ) {
			init_date_picker();
		} else {
			$( "#resobj-container" ).html('');
		}
	});

	$( document ).on( "click", "#resobj-container .book", function() {
		available_data = JSON.parse(available_data);
		dataTime = $(this).data('time');

		$.each( available_data.times, function( key, value ) {
			if( value.time != dataTime ) {
				delete available_data.times[ key ];
			} else {
				return false;
			}
		});

		function isEmpty(value) {
		  return value.length != 0;
		}
		available_data.times = available_data.times.filter(isEmpty);

		var data = {
			action: 'resobj_admin_preorder_data',
			postID: post_id,
			selected_time: dataTime,
			available_data: available_data
		};
		$.post( ajaxurl, data, function(response) {
			$( "#resobj-container" ).html(response);
		});
	});

	$( "#resobj-order" ).on( "submit", function() {

		booking_validation();

		if( validForm ) {
			var append_html = '<input type="hidden" name="start_time" value="'+dataTime+'">'+
							'<input type="hidden" name="start_date" value="'+selectedDate+'">';
			$(this).append(append_html);
		} else {
			return false;
		}
	});

	$( document ).on( "change change keyup mouseup", "#product_variation_id, #people_per_ticket", function() {
		booking_validation();
	});

	function init_date_picker() {
		$( "#resobj-container" ).html( '<div id="booking-datepicker"></div>' );
		$( "#booking-datepicker" ).datepicker({
			minDate: new Date(),
			onSelect: function(bookingDate, inst){
				var theDate = new Date(Date.parse($(this).datepicker('getDate')));
					selectedDate = $.datepicker.formatDate('yy-mm-d', theDate);

				ajax_book_time();
			}
		});
	}

	function ajax_book_time() {
		var data = {
			action: 'resobj_admin_available_time',
			postID: post_id,
			selectedDate: selectedDate
		};
		$.post( ajaxurl, data, function(response) {
			$( "#resobj-container" ).html(response);
		});
	}

	function booking_validation() {

		var number_of_people = parseInt($('#people_per_ticket').val()),
			iteration = parseInt($('#product_variation_id option:selected').data('iteration')),
			max_people = parseInt(available_data.bookable['people-per-ticket']),
			message = '';

		for( var i=0; i <= iteration; i++ ) {

			var available_seats = parseInt(available_data.times[ i ].available_seats);
			if( number_of_people > available_seats ) {
				message = '<b>no seats available</b>';
				validForm = false;
				break;
			} else if( number_of_people > max_people ) {
				$("#resobj-order .form-message").html('<b>no seats available</b>');
				message = '<b>maximum of available seats per ticket: '+ max_people +'</b>';
				validForm = false;
				break;
			} else {
				validForm = true;
			}
		}
		$("#resobj-order .form-message").html(message);
	}


	/*** Reservation List ***/

	$( "#res_obj-datepicker" ).datepicker();
});