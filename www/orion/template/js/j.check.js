jQuery.fn.vchecks = function() {
	object = jQuery(this);
	object.addClass('geogoer_vchecks');
	object.find("li:first").addClass('first');
	object.find("li:last").addClass('last');
	
	//removing checkboxes
	object.find("input[type=checkbox]").each(function(){
		$(this).hide();
	});
	
	//adding images true false
	object.find("li.v").each(function() {
		if ($(this).find("input[type=checkbox]").attr('checked')) {
			$(this).addClass('checked');
			$(this).prepend('<div class="check_div"></div>');
		} else {
			$(this).addClass('unchecked');
			$(this).prepend('<div class="check_div"></div>');
		}
	});
	
	//binding onClick function
	object.find("li.v").find('span').click(function(e) {
		e.preventDefault();
		check_li = $(this).parent('li');
		checkbox = $(this).parent('li').find("input[type=checkbox]");
		
		if (checkbox.attr('checked')) {
			checkbox.attr('checked',false);
			check_li.removeClass('checked');
			check_li.addClass('unchecked');
		} else {
			checkbox.attr('checked',true);
			check_li.removeClass('unchecked');
			check_li.addClass('checked');
		}
	});
	
	//mouse over / out
	//simple
	object.find("li:not(:last,:first)").find('span').bind('mouseover', function(e){
		$(this).parent('li').addClass('hover');
	});
	object.find("li:not(:last,:first)").find('span').bind('mouseout', function(e){
		$(this).parent('li').removeClass('hover');
	});
	//first
	object.find("li:first").find('span').bind('mouseover', function(e){
		$(this).parent('li').addClass('first_hover');
	});
	object.find("li:first").find('span').bind('mouseout', function(e){
		$(this).parent('li').removeClass('first_hover');
	});
	//last
	object.find("li:last").find('span').bind('mouseover', function(e){
		$(this).parent('li').addClass('last_hover');
	});
	object.find("li:last").find('span').bind('mouseout', function(e){
		$(this).parent('li').removeClass('last_hover');
	});
}