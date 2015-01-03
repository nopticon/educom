<?php

require_once("./conexion.php");

if (isset($_SESSION['userlog'])) {
	header('location: index.php');
}

if (isset($_REQUEST['submit'])) {
	$username = $_REQUEST['username'];
	$key = $_REQUEST['key'];

	$sql = 'SELECT *
		FROM usuarios
		WHERE usuario = ? AND password = ?';
	if ($row = $db->sql_fieldrow($db->__prepare($sql, $username, $key))) {
		$_SESSION['userlog'] = $row->usuario;
		$_SESSION['nombre'] = $row->nombre;

		header('Location: index.php');
		exit;
	}
}

encabezado('Administracion Escolar', '', 'login');

?>

<div class="small-box">
	<form class="form-horizontal" action="login.php" method="post" role="form">
	  <div class="form-group">
	    <label for="inputUsername" class="col-lg-2 control-label">Usuario</label>
	    <div class="col-lg-10">
	      <input type="text" class="form-control" id="inputUsername" name="username" placeholder="Usuario">
	    </div>
	  </div>
	  <div class="form-group">
	    <label for="inputPassword" class="col-lg-2 control-label">Password</label>
	    <div class="col-lg-10">
	      <input type="password" class="form-control" id="inputPassword" name="key" placeholder="Password">
	    </div>
	  </div>
	  <div class="form-group">
	    <div class="col-lg-offset-2 col-lg-10">
	      <button type="submit" name="submit" class="btn btn-danger">Continuar</button>
	    </div>
	  </div>
	</form>
</div>

<?php pie(); ?>