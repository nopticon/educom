<!-- INCLUDE js/j.periodic.js -->
<!-- INCLUDE js/j.url.js -->
<!-- INCLUDE js/j.textarea.js -->
<!-- INCLUDE js/j.search.js -->
<!-- INCLUDE js/j.slider.js -->
<!-- INCLUDE js/j.area.js -->

function popup(url, name, width, height) {
	var win = window.open(url, name, 'toolbar = 0, scrollbars = 1, location = 0, statusbar = 0, menubar = 0, resizable = 1, width=' + width + ', height=' + height);
	return false;
}

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

	var xka = true;
	var $d;
	var doctitle = document.title;
	var docurl = window.location.href;
	var window_size = $(window).width();
	var filesList = []

	// $('input:text, textarea').first().focus();
	$('select').select2();

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
						alert('authentication failed!');
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

	$('ul[id^="expand_"]').hide().addClass('flying');

	$('.expand').on('click', function(event) {
		event.preventDefault();

		var id = $(this).attr('id');

		position = $(this).position();
		$('#expand_' + id).css('top', position.top + $(this).height() + 9);
		$('#expand_' + id).css('left', position.left + 1);

		$('#expand_' + id).slideToggle('medium');
		return false;
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
		// $.url.segment(0)
	// }

	if (xka) {
		// Keep alive
		// $.PeriodicalUpdater('/async/ka/', {
		// 	method: 'post',
		// 	data: {ajax: '1'},
		// 	minTimeout: 10000,
		// 	maxTimeout: 15000
		// });
	}

	function getNumberOfFiles() {
		return $('#files').find('div').size();
	}

	function fileuploadadd(e, data) {
		filesList.push(data.files[0])

     	data.context = $('<div/>').appendTo('#files');
	    $.each(data.files, function (index, file) {
            var node = $('<p/>').append($('<span/>').text(file.name));
            node.appendTo(data.context);
        });

     //    var form = $(this).closest('form');
    	// form.on('submit', function(event) {
    	// 	event.preventDefault();

    	// 	// alert(form.serializeArray());

    	// 	// data.submit();

    	// 	return false;
    	// });
    }

    function fileuploadadd_single(e, data) {
        if (getNumberOfFiles() > 0) {
        	return false;
        }

        fileuploadadd(e, data);
    }

    function fileuploadprocessalways(e, data) {
    	try {
    		var index = data.index,
	            file = data.files[index],
	            node = $(data.context.children()[index]);

	    	if (file.preview) {
	            node.prepend('<br>').prepend(file.preview);
	        }
	        if (file.error) {
	            node.append('<br>').append($('<span class="text-danger"/>').text(file.error));
	        }
	        // if (index + 1 === data.files.length && typeof data.context != 'undefined') {
	        //     data.context.find('button').text('Upload').prop('disabled', !!data.files.error);
	        // }
    	} catch(e) {}
    }

    function fileuploadprogressall(e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css('width', progress + '%');
    }

    function fileuploaddone(e, data) {
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url);
                $(data.context.children()[index])
                    .wrap(link);
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }

    function fileuploadfail(e, data) {
        $.each(data.files, function (index, file) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }

	try {
		var url = window.location;

        $('.fileupload_single button[type=submit]').on('click', function(event) {
        	event.preventDefault();

	        $('.fileupload_single').fileupload('send', {files:filesList});

	        return false;
	    });

	    $('.fileupload button[type=submit]').on('click', function(event) {
        	event.preventDefault();

	        $('.fileupload').fileupload('send', {files:filesList});

	        return false;
	    });

        $('.fileupload_single').fileupload({
	        url: url,
	        dataType: 'json',
	        autoUpload: false,

	        singleFileUploads: true,
		    limitMultiFileUploads: undefined,
		    maxNumberOfFiles: 2,

	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|mp3)$/i,
	        maxFileSize: 20000000, // 20 MB
	        disableImageResize: /Android(?!.*Chrome)|Opera/
	            .test(window.navigator.userAgent),
	        previewMaxWidth: 100,
	        previewMaxHeight: 100,
	        previewCrop: true
	    })
	    .on('fileuploadadd', fileuploadadd_single)
	    .on('fileuploadprocessalways', fileuploadprocessalways)
	    .on('fileuploadprogressall', fileuploadprogressall)
	    .on('fileuploaddone', fileuploaddone)
	    .on('fileuploadfail', fileuploadfail);

	    $('.fileupload').fileupload({
	        url: url,
	        dataType: 'json',
	        autoUpload: false,

	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|mp3)$/i,
	        maxFileSize: 20000000, // 20 MB
	        disableImageResize: /Android(?!.*Chrome)|Opera/
	            .test(window.navigator.userAgent),
	        previewMaxWidth: 100,
	        previewMaxHeight: 100,
	        previewCrop: true
	    })
	    .on('fileuploadadd', fileuploadadd)
	    .on('fileuploadprocessalways', fileuploadprocessalways)
	    .on('fileuploadprogressall', fileuploadprogressall)
	    .on('fileuploaddone', fileuploaddone)
	    .on('fileuploadfail', fileuploadfail)
	    .prop('disabled', !$.support.fileInput);
	} catch(e) { }

	// $('div[id^="hse_"]').each(function() {
	// 	$d = $('#se_' + this.id.substr(4)).empty();
	// 	$('ins:first', this).appendTo($d).addClass('rows5_top_2');
	// });
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