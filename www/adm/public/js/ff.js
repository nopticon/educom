$(function() {
	$('input:text, textarea').first().focus();

	$('select').select2({style: 'btn-primary', menuStyle: 'dropdown-inverse'});
	$(':radio').radiocheck();
});