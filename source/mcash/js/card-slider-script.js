function createSlick(){  

	$(".slider").not('.slick-initialized').slick({
		centerMode: true,
	    autoplay: false,
	    dots: true,
      initialSlide: 1,
  		slidesToShow: 3,
	    responsive: [{ 
	        breakpoint: 768,
	        settings: {
	            dots: false,
	            arrows: false,
	            infinite: false,
	            slidesToShow: 1,
	            slidesToScroll: 1
	        } 
	    }]
	});	

}


(function($) {
	
  $(document).ready(function () {
    createSlick();
    //Will not throw error, even if called multiple times.
    $(window).on( 'resize', createSlick );


    // Render
    $('body').on('click', '.desc a', function () {
      $(this).addClass('selected');
      const weight = $(this).data('productWeight');
      const product = $(this).data('productCode');
      const quantity = $('#quantity').val();
      const total = weight * quantity;
      const initial = $('#gold-balance').text();
  
      $('#total-conversion').text((total).toFixed(3));
      $('#balance-conversion').text((initial - total).toFixed(3));
      $('#product').val(product);
      $('#weight').val(weight);
  
      $(this).parent().find('a').not(this).removeClass('selected');
    });
    
    $('body').on('click', '.plus-minus .plus', function () {
      var current_val = $(this).siblings('.plus-minus-value').val();
      current_val++;
  
      grabweight = $('#weight').val()
      const weight = Number(grabweight);
      const initial = $('#gold-balance').text();
      const total = weight * current_val;
  
      $('#total-conversion').text((total).toFixed(3));
      $('#balance-conversion').text((initial - total).toFixed(3));
  
      $(this).siblings('.plus-minus-value').val(current_val);
    });
    $('body').on('click', '.plus-minus .minus', function () {
      var current_val = $(this).siblings('.plus-minus-value').val();
      current_val--;
      if (current_val < 1) {
        current_val = 1;
      }
  
      grabweight = $('#weight').val()
      const weight = Number(grabweight);
      const initial = $('#gold-balance').text();
      const total = weight * current_val;
  
      $('#total-conversion').text((total).toFixed(3));
      $('#balance-conversion').text((initial - total).toFixed(3));
      $(this).siblings('.plus-minus-value').val(current_val);
    });
  
    $('body').on('keyup', '.plus-minus-value', function () {
      var current_val = $(this).val();
      current_val--;
      if (current_val < 1) {
        current_val = 1;
      }
  
      grabweight = $('#weight').val()
      const weight = Number(grabweight);
      const initial = $('#gold-balance').text();
      const total = weight * current_val;
  
      $('#total-conversion').text((total).toFixed(3));
      $('#balance-conversion').text((initial - total).toFixed(3));
      $(this).val(current_val);
    });

      // Swap
      // $('.slider').on('beforeChange', function(event, slick, currentSlide, nextSlide){
      //   alert(nextSlide);
      //   console.log(nextSlide);
      // });
      
      $('.slider').on('afterChange', function(event, slick, currentSlide){
        slides = $(slick.$slides.get(currentSlide));
        const product = slides[0].getElementsByClassName("btn")[0].dataset.productCode;
        const weight = slides[0].getElementsByClassName("btn")[0].dataset.productWeight;

        const quantity = $('#quantity').val();
        const total = weight * quantity;
        const initial = $('#gold-balance').text();
    
        $('#total-conversion').text((total).toFixed(3));
        $('#balance-conversion').text((initial - total).toFixed(3));
        $('#product').val(product);
        $('#weight').val(weight);
        //https://github.com/kenwheeler/slick/issues/411
      });
      
      // $('.slider').on('swipe', function(event, slick, direction){
      //   alert(direction);
      //   console.log(direction);
      //   // left
      // });
      //End Swap
	});

})(jQuery);