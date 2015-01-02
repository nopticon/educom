<?php

require_once('../conexion.php');

encabezado('Reportes del Sistema');

?>

<ul class="options">
	<li><a href="./alumnos/listado_alumno.php"><img src="/public/images/iconos/59.ico" width="20" valign="middle" /> Listado de Alumnos</a></li>
	<li><a href="./asistencia/listado_alumno.php"><img src="/public/images/iconos/152.ico" width="20" valign="middle" /> Control Asistencia de Alumnos</a></li>
	<li><a href="./promedios/"><img src="/public/images/iconos/227.ico" width="20" valign="middle" /> Promedios de Alumnos</a></li>
	<li><a href="./calificaciones.php"><img src="/public/images/iconos/30.ico" width="20" valign="middle" /> Tarjeta de Calificaciones</a></li>
	<li><a href="./catedraticos/listado_catedratico.php"><img src="/public/images/iconos/209.ico" width="20" valign="middle" /> Catedr&aacute;ticos con Cursos</a></li>
	<li><a href="./certificaciones.php"><img src="/public/images/iconos/144.ico" width="20" valign="middle" /> Certificaciones Anuales</a></li>
	<li><a href="./fgenerales.php"><img src="/public/images/iconos/buddy-signon.ico" width="20" valign="middle" /> Cuadros Generales de Calificaciones</a></li>
	<li><a href="./carta_editar.php" target="_blank"><img src="/public/images/iconos/22.ico" width="20" valign="middle" /> Carta para Edici&oacute;n de Calificaci&oacute;n</a></li>
</ul>

<?php pie(); ?>