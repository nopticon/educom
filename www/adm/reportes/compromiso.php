<?php

require_once("../conexion.php");

$id_alumno = $_REQUEST['id_alumno'];
$id_grado = isset($_REQUEST['id_grado']) ? $_REQUEST['id_grado'] : 0;

if ($id_grado) {
	$sql = 'SELECT *
		FROM alumno a, reinscripcion r, grado g
		WHERE r.id_alumno = ?
			AND r.id_grado = ?
			AND r.id_grado = g.id_grado
			AND r.id_alumno = a.id_alumno';
	$row = $db->sql_fieldrow(sql_filter($sql, $id_alumno, $id_grado));

	$encargado = 'encargado_reinscripcion';
} else {
	$sql = 'SELECT *
		FROM alumno
		WHERE id_alumno = ?';

	$sql = 'SELECT *
		FROM alumno a, reinscripcion r, grado g
		WHERE r.id_alumno = ?
			AND r.id_grado = g.id_grado
			AND r.id_alumno = a.id_alumno
		ORDER BY g.id_grado DESC';

	$row = $db->sql_fieldrow(sql_filter($sql, $id_alumno));

	$encargado = 'encargado';
}

encabezado_simple('Compromiso de responsabilidad de estudios');

?>

<table width="100%">
	<tr>
		<td width="23">&nbsp;</td>
		<td width="729">
			<table width="754">
				<tr>
					<td width="189" align="center"><img src="/public/images/logo.jpg" width="110" height="117" /></td>
					<td width="555" valign="top" align="center">
						<br />MINISTERIO DE EDUCACION
						<br />ESCUELA NORMAL RURAL No.5 &quot;Prof. JULIO E. ROSADO PINELO&quot;
						<br />NIVEL DE EDUCACION MEDIA
					</td>
				</tr>
			</table>
		</td>
		<td width="18">&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<p align="center"><strong>COMPROMISO DE RESPONSABILIDAD DE ESTUDIOS</strong></p>
			<p align="justify">Para estudiantes menores de edad o mayores de edad bajo la tutela de los padres o encargados. El que debe ser complemtado y firmado por las personas que se indican, PREVIO a la inscripci&oacute;n del alumno.</p>
			<p align="justify">En el municipio de Flores del departamento de Pet&eacute;n, el dia__ de _____ del a&ntilde;o___ ante el infranscrito Director de la Escuela Normal Rural No. 5 &quot;Profesor Julio Edmundo Rosado Pinelo&quot;,
			se presenta el (la) se&ntilde;or (a): <strong><?php echo $row->$encargado; ?></strong> mayor de edad, con C&eacute;dula de Vecindad N&uacute;mero de Orden <strong><?php echo $row->orden; ?></strong> y Registro <strong><?php echo $row->registro; ?></strong> extendida
			en <strong><?php echo $row->extendida; ?></strong> con profesi&oacute;n u oficio <strong><?php echo $row->profesion; ?></strong>, laborando actualmente en <strong><?php echo $row->labora; ?></strong> con direcci&oacute;n en <strong><?php echo $row->direccion_labora; ?></strong>,
			tel&eacute;fono: <strong><?php echo $row->telefono2; ?></strong>, manifiesta que es: <strong><?php echo $row->emergencia; ?></strong> del alumno (a): <strong><?php echo $row->nombre_alumno . ' ' . $row->apellido; ?></strong> de
			<strong><?php echo $row->edad; ?></strong> de edad, cursante del <strong><?php echo $row->nombre; ?></strong> con direccion en <strong><?php echo $row->direccion; ?></strong> tel&eacute;fono <strong><?php echo $row->telefono1; ?></strong>, quien por este medio o acto suscriben
			El presente COMPROMISO DE RESPONSABILIDAD DE ESTUDIO de acuerdo a las Cl&aacute;usulas que se se&ntilde;alan en los siguientes puntos: </p>
			<p>PRIMERO: Que el alumno (a): <strong><?php echo $row->nombre_alumno . ' ' . $row->apellido; ?></strong>
			se compromete a cumplir con las obligaciones que le impone el Articulo 34 de la Ley de Educaci&oacute;n Nacional, Decreto
			No. 12-91 del Congreso de la Rep&uacute;blica, con el Reglamento Interno del Centro Educativo y con lo siguiente:<br />
			</p>

			<ol type="a">
				<li>Cumplir con todas las obligaciones inherentes a su calidad de alumno y las que sean establecidas por las autoridades educativas, y espec&iacute;ficamente por el Director del Plantel, Personal T&eacute;cnico-Administrativo y Catedr&aacute;ticos;
				<li>Respectar a las autoridades T&eacute;cnico-Administrativas, a los Docentes y Estudiantes del Plantel;
				<li>Observar buena conducta en todos sus actos, tanto dentro como fuera del Plantel;
				<li>Asistir puntualmente y diariamente a sus clases. Si por causa justificada no pudiera hacerlo, deber&aacute; presentar excusa escrita a la Direcci&oacute;n del Plantel, firmada por el Padre, Madre o Encargado;
				<li>Abstenerse de participar en su per&iacute;odo de estudio en el Plantel, en actividades no autorizadas por la Direccion del establecimiento;
				<li>Colaborar por mantener en buenas condiciones el edificio, sus instalaciones, mobiliario y equipo del plantel;
				<li>Asistir con presentaci&oacute;n personal adecuada en cuanto a higiene corporal y de vestuario;
				<li>Pagar &iacute;ntegramente, el valor de los libros, equipo, &uacute;tiles, mobiliario e instalaciones de cuya p&eacute;rdida, deterioro o destrucci&oacute;n resulte individual o grupalmente responsable;
				<li>Rendir el respeto que se merece nuestro s&iacute;mbolos patrios, participar en actos y eventos de car&aacute;cter c&iacute;vico que se programen por el Ministerio de Educaci&oacute;n o por el Plantel Educativo.
			</ol>

			<p>&nbsp;</p>
			<p align="justify">SEGUNDO: El se&ntilde;or (a): <strong><?php echo $row->$encargado; ?></strong> se compromete y responsabiliza por lo siguiente:</p>
      		<p align="justify">
				<ol type="a">
					<li>Velar porque su hijo (a): <strong><?php echo $row->nombre_alumno . ' ' . $row->apellido; ?></strong> cumpla con todo lo consignado en el punto anterior de este compromiso;</li>
					<li>Responder directa y personalmente de lo prescrito en los literales f) y h) del punto anterior;</li>
					<li>Presentarse a la Direcci&oacute;n del Plantel y &oacute;rgano de su administraci&oacute;n cuando su presencia sea requerida; y</li>
					<li>Cuidar porque el alumno (a) cumpla con todas las medidas disciplinarias que disponga la Direcc&oacute;n del Plantel.</li>
				</ol>
			<p>&nbsp;</p>
			<p align="justify">TERCERO: Para garantizar la buena disciplina del establecimiento, as&iacute; para sancionar las faltas en que se incurra el alumno (a), la Direcci&oacute;n del Plantel podr&aacute; hacer uso de las sanciones siguientes:</p>
			<p align="justify">
				<ol type="1">
					<li>Amonestaci&oacute;n verbal</li>
					<li>Amonestaci&oacute;n escrita</li>
					<li>Suspenci&oacute;n por sus estudios por un per&iacute;odo no mayor de un mes; y</li>
					<li>Cancelaci&oacute;n de la Matr&iacute;cula. En este caso, el estudiante sancionado que se encuentre en disfrute de Becas y/o Bolsa de Estudios, perder&aacute; autom&aacute;ticamente dichos beneficios.</li>
				</ol>
			</p>
			<p align="justify">
				Estas sanciones ser&aacute;n consideradas y aplicadas seg&uacute;n la gravedad de la falta o reincidencia, y se notificar&aacute; al Padre, Madre, Tutor o Encargado del alumno.
			</p>
			<p>&nbsp;</p>
			<p align="justify">CUATRO: Cuando el alumno (a) sea mayor de edad y cometa actos que sean constituidos de faltas y delitos, dentro o fuera del establecimiento educativo, ser&aacute; procesado conforme las leyes del pa&iacute;s.</p>
			<p>&nbsp;</p>
			<p align="justify">QUINTO: Los suscritos, plenamente conscientes del contenido, alcance y efectos legales del presente compromiso de responsabilidad de estudio, lo firman de conformidad, juntamente con el Director del Plantel.</p>
		</td>
	</tr>
</table>

</body>
</html>