$(function() {
	$('input:text, textarea').first().focus();

	$('select').select2({style: 'btn-primary', menuStyle: 'dropdown-inverse'});
	$(':radio').radiocheck();

	$('#grado').change(function() {
		$.ajax({
			type: "POST",
			url: '../api/section.php',
			data: "grado=" + $(this).val(),
			success: function(msg) {
				$('#seccion').html(msg);
			}
		});
	});

	$('#inputgrado').change(function() {
		$.ajax({
			type: "POST",
			url: '../api/section.php',
			data: "grado=" + $(this).val(),
			success: function(msg) {
				$('#inputseccion').html(msg);
			}
		});
	});
});