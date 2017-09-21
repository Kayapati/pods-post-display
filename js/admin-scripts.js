(function($) {
  "use strict";
  $(function() {

  	  $('.choose_image_sizes').change(function(){
         var $options_val = $(this).find('option:selected').val();
         $('.wp_default_img_columns').parent().parent().hide();
         $('#taxonomy_gallery_width').parent().parent().hide();
        if( $options_val == 'wp_image_sizes' ){
       		$('.wp_default_img_columns').parent().parent().show();
        }else{
        	$('#taxonomy_gallery_width').parent().parent().show();
        }
      }).change();
  	
  });
})(jQuery);