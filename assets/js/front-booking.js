jQuery(function($){

	var selectedDate = "",
		dataTime = "";
		validForm = true;

	init_date_picker();

	$( document ).on( "click", "#resobj-container .book", function() {

		dataTime = $(this).data('time');
		available_data = filtering_data_for_validation( available_data, dataTime );

		var data = {
			action: 'resobj_preorder_data',
			postID: ajaxObj.post_id,
			selected_time: dataTime,
			available_data: available_data
		};
		$.post( ajaxObj.url, data, function(response) {
			$("#resobj-container").html(response);
			change_active_step( 'preOrder' );
		});
	});

	$( document ).on( "submit", "#preorder-jump", function() {

		booking_validation();

		if( validForm ) {
			var append_html = '<input type="hidden" name="start_time" value="'+dataTime+'">'+
						  '<input type="hidden" name="start_date" value="'+selectedDate+'">';
			$(this).append(append_html);
		} else {
			return false;
		}
	});

	$( document ).on( "change keyup mouseup", "#product_variation_id, #people_per_ticket", function() {
		booking_validation();
	});

	$( document ).on('click', '#prev-step', function(){

		var prevStep = $(this).data('step');
		if( prevStep == 'selectDate' ) {
			$("#resobj-container").html('<div id="booking-datepicker"></div>');
			init_date_picker();
			change_active_step( 'selectDate', true );
		} else if( prevStep == 'selectTime' ) {
			ajax_book_time( true );
		}
	});

	if( ajaxObj.preselect ) {

		selectedDate = ajaxObj.preselect.start_date;
		dataTime = ajaxObj.preselect.start_time;
		available_data = filtering_data_for_validation( available_data, dataTime );
	}

	function init_date_picker() {
		$( "#booking-datepicker" ).datepicker({
			minDate: new Date(),
			onSelect: function(bookingDate, inst){
				var theDate = new Date(Date.parse($(this).datepicker('getDate')));
				var dateFormatted = $.datepicker.formatDate('yy-mm-d', theDate);

				selectedDate = dateFormatted;
				ajax_book_time();
			}
		});
	}

	function ajax_book_time( prev = false ) {
		var data = {
			action: 'resobj_available_time',
			postID: ajaxObj.post_id,
			selectedDate: selectedDate
		};
		$.post( ajaxObj.url, data, function(response) {
			$("#resobj-container").html(response);
			change_active_step( 'selectTime', prev );
		});
	}

	function filtering_data_for_validation( available_data, dataTime ) {

		available_data = JSON.parse(available_data);

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

		return available_data;
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
				$("#preorder-jump .form-message").html('<b>no seats available</b>');
				message = '<b>maximum of available seats per ticket: '+ max_people +'</b>';
				validForm = false;
				break;
			} else {
				validForm = true;
			}
		}
		$("#preorder-jump .form-message").html(message);
	}

	function change_active_step( step, prev = false ) {

		$('.resobj-steps li').removeClass('active');
		$('.resobj-steps li[data-step="'+step+'"]').addClass('active');

		if( prev ) {
			$('.resobj-steps li[data-step="'+step+'"] span').html("");
		} else {
			if( step == "selectTime" ) {
				$('.resobj-steps li[data-step="selectDate"] span').html(selectedDate);
			} else if( step == "preOrder" ) {
				$('.resobj-steps li[data-step="selectTime"] span').html(dataTime);
			}
		}
	}
});