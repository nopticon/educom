<!-- INCLUDE js/j.area.js -->
<!-- INCLUDE js/j.periodic.js -->
<!-- INCLUDE js/j.textarea.js -->
<!-- INCLUDE js/j.url.js -->

var decodeEntities = (function() {
  // this prevents any overhead from creating the object each time
  var element = document.createElement('div');

  function decodeHTMLEntities (str) {
    if(str && typeof str === 'string') {
      // strip script/html tags
      str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
      str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
      element.innerHTML = str;
      str = element.textContent;
      element.textContent = '';
    }

    return str;
  }

  return decodeHTMLEntities;
})();

jQuery.decodeEntities = decodeEntities;

function save_thumb() {
	var x1 = $('#x1').val();
	var y1 = $('#y1').val();
	var x2 = $('#x2').val();
	var y2 = $('#y2').val();
	var w = $('#w').val();
	var h = $('#h').val();

	if (x1 == '' || y1 == '' || x2 == '' || y2 == '' || w == '' || h == '') {
		alert("Select area first");
		return false;
	}

	return true;
}

function strpos(text, search) {
	return text.indexOf(search);
}

$(function() {
	'use strict';

	var doctitle = document.title;
	var docurl = window.location.href;
	var window_size = $(window).width();
	
	$('select').select2();

	$('.input-group.date').datepicker({
		autoclose: true,
		todayHighlight: true,
		language: 'es'
	});

	$('.input-tags').select2({
		locale: 'es',
		// placeholder: 'Buscar alumnos',
		minimumInputLength: 1,
		ajax: { 
			url: '/adm/api/students.php',
			dataType: 'json',
			data: function(params) {
				return {
					q: params.term,
					page: params.page,
				};
			},
			processResults: function(data) {
				return {results: data};
			}
		}
	});

	$('textarea').autoResize({
		onReize: function() {
			$(this).css({opacity: 0.8});
		},
		animateCallback: function() {
			$(this).css({opacity: 1});
		},
		limit: 250
	});

	$('#grado').on('change', function() {
		$.ajax({
			type: 'POST',
			url: '/adm/api/section.php',
			data: 'grado=' + $(this).val(),
			success: function(msg) {
				$('#seccion').html(msg);
			}
		});
	});

	$('#inputgrado').on('change', function() {
		$.ajax({
			type: 'POST',
			url: '/adm/api/section.php',
			data: 'grado=' + $(this).val(),
			success: function(msg) {
				$('#inputseccion').html(msg);
			}
		});
	});

	if ($('#inputactivity_schedule').length) {
		$('#inputactivity_group').on('change', function() {
			$.ajax({
				type: 'POST',
				url: '/adm/api/teacher_schedule.php',
				data: 'group=' + $(this).val(),
				success: function(json) {
					// inputactivity_schedule

					$('#inputactivity_schedule').empty().append($('<option>', {
						value: '0',
						text : 'Seleccione el curso'
					}));

					$.each(json, function (i, item) {
						$('#inputactivity_schedule').append($('<option>', {
							value: item.id_curso,
							text : $.decodeEntities(item.nombre_curso)
						}));
					});

					return;
				}
			});
		});
	}

	//
	// Ajax: Account login
	//
	$('#account_login').on('submit', function(event) {
		event.preventDefault();

		$.ajax({
			type: "POST",
			url: $(this).attr('action'),
			data: $(this).serialize() + '&login=1&_ghost=1',
			success: function(msg) {
				switch (msg) {
					case '401':
						alert('Uno de los datos no es correcto. Por favor vuelva a intentar.');
						return;
					default:
						if (strpos(msg, 'Location: ') !== false) {
							window.location = msg.replace('Location: ', '');
							return;
						}
				}

				return;
			}
		});

		return false;
	});

	$('.expand').on('click', function(event) {
		event.preventDefault();

		var id = $(this).attr('id');

		position = $(this).position();
		$('#expand_' + id).css('top', position.top + $(this).height() + 9);
		$('#expand_' + id).css('left', position.left + 1);

		$('#expand_' + id).slideToggle('medium');
		return false;
	});

	$('.pub').on('click', function(event) {
		event.preventDefault();
		$.scrollTo('.publish');
	});

	$('.ask_remove').on('click', function() {
		if (confirm('Confirme si desea eliminar esta publicacion')) {
			return true;
		}

		return false;
	});

	// if ($.url.segment() > 0) {
	// 	$.url.segment(0)
	// }
});

var _ = {
	call: function(action, el, rate, decode) {
		$.PeriodicalUpdater('/async/' + action + '/', {
			method: 'post',
			data: {ajax: '1'},
			minTimeout: ((rate - 1) * 1000),
			maxTimeout: (rate * 1000),
			success: function(data) {
				if (el) {
					response = (decode) ? unescape(data) : data;
					$('#' + el).html(response);
				}
			}
		});
	}
}