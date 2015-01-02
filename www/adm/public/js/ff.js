
$(function() {
	$("input:text, textarea").first().focus();

	$("select").selectpicker({style: 'btn-primary', menuStyle: 'dropdown-inverse'});

	$(':radio').radio();
});

function validarinscripcion() {
	if (!confirm("Seguro que desea guardar el nuevo alumno...")) {
		return false;
	}

	MM_validateForm('nombre','','R','apellido','','R','direccion','','R','telefono1','','R','edad','','R','email','','NisEmail','padre','','R','madre','','R','encargado','','R','telefono2','','R');
	return document.MM_returnValue;
}

function validarreinscripcion2() {
	if (!confirm("Seguro que Desea Re-Inscribir al Alumno?")) {
		return false;
	}

	MM_validateForm('encargado','','R','telefonos','','R');
	return document.MM_returnValue;
}

function validarfalta1() {
	if (!confirm("Deseas ver las faltas del alumno?")) {
		return false;
	}

	MM_validateForm('carne1','','R');
	return document.MM_returnValue;
}

function buscar(url) {
	ventana = open(url,"ventana","scrollbars=yes,status=no,toolbar=no,directories=no,menubar=no,resizable=yes,width=350,height=425");
	return false;
}