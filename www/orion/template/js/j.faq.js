(function($) {
 
   $.fn.faq = function(settings) {

     this.each(function() {	
		o = $(this).attr('id');
		list = $('#' + o );
		
		$('#' + o + ' li p.answer').hide();
		
		$('#' + o + ' li h3 a').bind("click", function(){
			var item = $(this).parents().children("p.answer");
			$('#' + o + ' li p.answer').slideUp('fast');
			item.slideToggle("fast");
			// alert($(this).parents().children("p.answer").html());
		});		  
     });
 
     return this;
 
   };
 
 })(jQuery);