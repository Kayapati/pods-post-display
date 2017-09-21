(function($) {
  "use strict";
  $(function() {

    // CPT Post Grid Slider
    $('.cpt-post-content-wrapper .owl-carousel').each(function(){
      var $column = $(this).data('columns');
      var $responsive_columns2 = ( ($column == '3' ) || ( $column == '4'  ) || ( $column == '5' )  || ( $column == '6'  ) ) ? '2' : $column;
      var $responsive_columns3 = ( ($column == '3' ) || ( $column == '4'  ) || ( $column == '5' )  || ( $column == '6'  ) ) ? '3' : $column;
      $(this).owlCarousel({
        loop:true,
        margin:10,
        nav:false,
        items:$column,
        responsive:{
            0:{
                items:1
            },
            500:{
                items:$responsive_columns2,
                 loop:true,
            },
            768:{
              items:$responsive_columns3,
               loop:true,
            },
            1000:{
            }
        }
    })
  });
  	/**
     * Masonry Gallry images
     */
	$(window).load(function(){
		$(function (){
			$( '.cpt-post-content-wrapper:not(.shortlist-page-wrapper) > ul.masonry, .taxonomy-content-wrapper > ul, .kaya-post-content-wrapper:not(.shortlist-page-wrapper) > ul, .single-page-meta-content-wrapper .gallery, .ajax-search-results-page > ul' ).masonry()
		});
	});
  // Advanced widget on Change
  $('.advanced_search_box_wrrapper').each(function(){
    var $this = $(this);
    $(this).find('select.form_options_data').on('change', function(){
        var $search_opt_val = $(this).find('option:selected').val();
         $this.find('.advancedsearch_form_wrapper').hide();
        $this.find('#'+$search_opt_val).show();
    }).change();
  });

  // Advanced Search

  $('.advanced_search_wrapper').each(function(){
    var $this = $(this); 
     $(this).find('#pods_cpt_data').change(function(){
        $('.advanced_search_forms').hide();
        var pod_cpt_name = $(this).find('option:selected').val();
        $this.find('.advanced_search_forms.pods_'+ pod_cpt_name +'_fields').show();
     }).change();
  })


 //$('#pods_cpt_data').each(function(){

  $('.advanced_search_wrapper #pods_cpt_data').each(function(){
    $(this).on('change', function(){
        //alert($(this).val());
        $(this).next('#pods_cpt_name').val($(this).val());
    }).change();
  });

$('.advanced_search_wrapper').each(function(){
   var $this = $(this);
     var ajax_search = $this.data('ajax');
    if( ajax_search == 'on' ){
    $(this).find('.advanced_search_forms .search_data_submit').on('click', function(e){
       e.preventDefault();         
    var url = $(this).attr('href'); //Grab the URL destination as a string
    var paged = $(this).text(); //Split the string at the occurance of &paged=
      ajax_search_data($this);
      return false;
    });
  }
});

  $('.search-content-wrapper .page-numbers a.page-numbers ').live('click', function(e){
        e.preventDefault(); 
        var $this_id =   $('.search-content-wrapper').data('div_id');
        var div_id = 'div#'+$this_id;
        var $this = $('div#'+$this_id);
       
         var ajax_search = $this.data('ajax');
         if( ajax_search == 'on' ){    
        var url = $(this).attr('href'); //Grab the URL destination as a string
        var paged = $(this).text(); //Split the string at the occurance of &paged=
        ajax_search_data($this, paged);
     }
  });

function ajax_search_data($this, paged){
 // $('.advanced_search_wrapper').each(function(){
    var $div_id =   $this.attr('id');
    var paged_val = paged;
    var ajax_search = $this.data('ajax');
    var append_search_class = $this.data('class') ? ' .'+$this.data('class') : '.mid-content';
    if( ajax_search == 'on' ){
      if($this.find('.advanced_search_forms').length > 1){
        var pods_cpt_name = $this.find('#pods_cpt_name').val();
      }else{
        var pods_cpt_name = $this.find('#pods_cpt_name').data('cpt_name');
      }
      var search_data = $('.searchbox-wrapper.pods_'+pods_cpt_name+'_fields_info').serialize();
      $('#kaya-mid-content-wrapper #mid-content .mid-content, .main-pages-slider-wrapper , '+ append_search_class).stop(true, true).animate({
                        'opacity': '0.2'
                    }, 300);
       $.ajax({
            type: "POST",
            url: kaya_ajax_url.ajaxurl,
            data: {
                action : 'ajx_search_query',
                advance_search: 'advance_search ',
                search_data: search_data,
                 paged: paged_val,
                 div_id : $div_id,
            },
            success: function(data) {
              $('.main-pages-slider-wrapper').remove();
               $(append_search_class ).stop(true, true).animate({
                        'opacity': '1'
                    }, 300).html(data);

               $('.talent-info').each(function(){
                  $('a.item_button.add').hide();
                  $(this).hover(function(){
                    $(this).find('a.item_button.add').show(250);
                  }, function(){
                    $(this).find('a.item_button.add').hide(150);
                  });
                });
               //
               var action = $('a.item_button, .cpt_posts_add_remove a.action');
              shortlist.getItemTotal();
              shortlist.itemActions( action );
            }
        });


     // return false;
 // });
  }
}

//});
  	// End Jquery
  });
})(jQuery);

// Model Short list Data added to ajax call back
var shortlist = (function($) {
  // define object literal container for parameters and methods
  var method = {};
   method.getItemTotal = function() {
    var counter = $('.shortlist-count'),
        clearAll = $('.shortlist-clear a');

    $.ajax({
      type: 'POST',
      url: kaya_ajax_url.ajaxurl,
       data : {
        action : 'kaya_pods_cpt_shortlist_items_count',
      },
      success: function(data) {
        counter.text('('+data+')');
      },
      error: function() {
        log('error with getItemTotal function');
      }
    });
  };
  /* Add & remove actions for individual items
   *
   * ajax method is wrapped inside itemActions() function
   *
   * method has 'button' parameter so jQuery object
   * can be passed in and run via $(button).on('click'...)
  -------------------------------------------------------- */

  method.itemActions = function(button) {
    $(button).on('click', function(e) {
        var target    = $(this),
          item      = target.closest('.item'),
          itemID    = item.attr('id'),
          itemAction= target.data('action');
        $.ajax({
          type: 'POST',
          url: kaya_ajax_url.ajaxurl,
          data : {
        action : 'kaya_pods_cpt_shortlist_items_remove_add',
        item_action : itemAction,
        item_id : itemID
      },
          success: function(data) {
            method.getItemTotal();
            log(itemAction + ' item ' + itemID);
            return false;
          },
          error: function(data) {
            log('error with itemActions function');
        }
      });

    if (itemAction === 'remove') {
      item.removeClass('item_selected');
    } else {
      item.addClass('item_selected');
    }
      e.preventDefault();
    });

  }; // end fn
  /* make methods accessible
  -------------------------- */
  return method;

}(jQuery)); // end of shortlist constructor

(function($){
  // select items, excluding those on shortlist page
   var action = $('a.item_button, .cpt_posts_add_remove a.action');
  shortlist.getItemTotal();
  shortlist.itemActions( action );

});