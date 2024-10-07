(function($) {
	
	var $event = $.event,
		$special,
		resizeTimeout;
	
	$special = $event.special.debouncedresize = {
		setup: function() {
			$( this ).on( "resize", $special.handler );
		},
		teardown: function() {
			$( this ).off( "resize", $special.handler );
		},
		handler: function( event, execAsap ) {
			// Save the context
			var context = this,
				args = arguments,
				dispatch = function() {
					// set correct event type
					event.type = "debouncedresize";
					$event.dispatch.apply( context, args );
				};
	
			if ( resizeTimeout ) {
				clearTimeout( resizeTimeout );
			}
	
			execAsap ?
				dispatch() :
				resizeTimeout = setTimeout( dispatch, $special.threshold );
		},
		threshold: 150
	};
	
})(jQuery);
	
$.fn.equalHeight = function() {
	var maxHeight = 0;
	return this.each(function(index, box) {
		var boxHeight = $(box).outerHeight();
		maxHeight = Math.max(maxHeight, boxHeight);
	}).height(maxHeight);
};
$.fn.equalHeightResized = function() {
	var maxHeight = 0;
	return this.each(function(index, box) {
		var boxHeight = $(box).height();
		maxHeight = Math.max(maxHeight, boxHeight);
	}).height(maxHeight);
};

$(document).ready(function() {
	
	// Init
	$('input[id="tncbox"]').click(function(){
		if($(this).prop("checked") == true){
			$('#btnpayfpx').show();
			$('#btnpaywallet').show();
		}
		else if($(this).prop("checked") == false){
			$('#btnpayfpx').hide();
			$('#btnpaywallet').hide();
		}
	});
	// End Init

	// Check and display error message
	$('#goldbalanceinsufficienterror').hide();
	$("body").on('DOMSubtreeModified', "#balance-conversion", function() {
		// If balance is negative
		if(parseFloat($('#balance-conversion').text()) < parseFloat($('#minbalance').val())){
			//alert($('#balance-conversion').text());
			$('#goldbalanceinsufficienterror').show();
		}else{
			$('#goldbalanceinsufficienterror').hide();
		}
	});
	// End
	
	// $('body').on('click', '.select-weight-row a', function() {
	// 	$(this).addClass('selected');
	// 	const weight = $(this).data('productWeight');
	// 	const product = $(this).data('productCode');
	// 	const quantity = $('#quantity').val();
	// 	const total = weight * quantity;
	// 	const initial = $('#gold-balance').text();

	// 	$('#total-conversion').text((total).toFixed(3));
	// 	$('#balance-conversion').text((initial - total).toFixed(3));
	// 	$('#product').val(product);
	// 	$('#weight').val(weight);

	// 	$(this).parent().find('a').not(this).removeClass('selected');
	// });
	
	// $('body').on('click', '.plus-minus .plus', function() {
	// 	var current_val = $(this).siblings('.plus-minus-value').val();
	// 	current_val++;

	// 	const weight = $('.select-weight-row .selected').data('productWeight');
	// 	const initial = $('#gold-balance').text();
	// 	const total = weight * current_val;

	// 	$('#total-conversion').text((total).toFixed(3));
	// 	$('#balance-conversion').text((initial - total).toFixed(3));

	// 	$(this).siblings('.plus-minus-value').val(current_val);
	// });
	// $('body').on('click', '.plus-minus .minus', function() {
	// 	var current_val = $(this).siblings('.plus-minus-value').val();
	// 	current_val--;
	// 	if(current_val < 1) {
	// 		current_val = 1;
	// 	}

	// 	const weight = $('.select-weight-row .selected').data('productWeight');
	// 	const initial = $('#gold-balance').text();
	// 	const total = weight * current_val;

	// 	$('#total-conversion').text((total).toFixed(3));
	// 	$('#balance-conversion').text((initial - total).toFixed(3));
	// 	$(this).siblings('.plus-minus-value').val(current_val);
	// });

	// $(document).ready(function() {
	//   $('select:not(.swal2-select)').niceSelect();
	// });
	
	// $('body').on('keyup', '.plus-minus-value', function () {
	// 	var current_val = $(this).val();
	// 	current_val--;
	// 	if (current_val < 1) {
	// 		current_val = 1;
	// 	}

	// 	const weight = $('.select-weight-row .selected').data('productWeight');
	// 	const initial = $('#gold-balance').text();
	// 	const total = weight * current_val;

	// 	$('#total-conversion').text((total).toFixed(3));
	// 	$('#balance-conversion').text((initial - total).toFixed(3));
	// 	$(this).val(current_val);
	// });

	$('body').on('click', '.box-listing-tabs a', function() {
		var current_tab = $(this).attr('rel');
		$(this).addClass('active');
		$(this).parent().find('a').not(this).removeClass('active');
		
		if ($('.box-listing-tab-content[rel=' + current_tab + ']').length) {
			$('.box-listing-tab-content[rel=' + current_tab + ']').addClass('active');
		}
		$('.box-listing-tab-content').not('.box-listing-tab-content[rel=' + current_tab + ']').removeClass('active');
		
	});

	
	$('body').on('click', '.box-listing-row.transaction, .box-listing-row.conversion', function () {
		const transaction = $(this).data('transaction');
		const type = $(this).data('type');

		if (type == 'spot') {
			$("#detail-form").attr('action', 'spot-detail.php');
		} else if (type == 'conversion') {
			$("#detail-form").attr('action', 'convert-receipt.php');
		}

		$("#payload").val(transaction);
		$("#type").val(type);
		$("#detail-form").submit();
	});

	$('body').on('keyup', '.verify-box input', function (e) {
		var key = e.which,
		t = $(e.target),
		sib = t.next('input');

		if (key != 8 && (key < 48 || key > 57)) {
			e.preventDefault();			
			return false;
		}

		if (key === 8) {
			return true;
		}

		if (!sib || !sib.length) {
			sib = body.find('input').eq(0);
		}
		sib.select().focus();
	});
	

	$('body').on('keydown', '.verify-box input', function (e) {
		var key = e.which;

		if (key === 8 || (key >= 48 && key <= 57)) {
			return true;
		}

		e.preventDefault();
		return false;
	});
	
	
	$('body').on('change', '.form-row.tnc input[name="tnc"]', function () {
		const checked = this.checked;
		
		if (checked) {
			$('#submit-form').attr('disabled', false);
		} else {
			$('#submit-form').attr('disabled', true);
		}
	});
});

function validateConversion() {
	const balance = $('#balance-conversion').text();
	if (balance < 0) {
		return false;
	}
}

function validatePriceAlert(form) {
	if (0 < $(form).find('input[name=price]').val()) {
		return true;
	}

	return false;
}

function str_pad_left(string,pad,length) {
    return (new Array(length+1).join(pad)+string).slice(-length);
}