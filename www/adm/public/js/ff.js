$(function() {
	$('input:text, textarea').first().focus();

	$('select').select2({style: 'btn-primary', menuStyle: 'dropdown-inverse'});
	$(':radio').radiocheck();

	$('#grado').change(function() {
		$.ajax({
			type: "POST",
			url: '/adm/api/section.php',
			data: 'grado=' + $(this).val(),
			success: function(msg) {
				$('#seccion').html(msg);
			}
		});
	});

	$('#inputgrado').change(function() {
		$.ajax({
			type: "POST",
			url: '/adm/api/section.php',
			data: 'grado=' + $(this).val(),
			success: function(msg) {
				$('#inputseccion').html(msg);
			}
		});
	});

	$('.input-tags').textext({
		plugins : 'autocomplete filter tags ajax',
		ajax : {
			url : '/adm/api/students.php',
			dataType : 'json'
		}
	}).bind('isTagAllowed', function(e, data) {
		var formData = $(e.target).textext()[0].tags()._formData,
			list = eval(formData);

		// Duplicate checking
		if (formData.length && list.indexOf(data.tag) >= 0) {
			var message = [ 'El estudiante', data.tag, 'ya esta incluido.' ].join(' ');
			alert(message);

			data.result = false;
		}
	});
});