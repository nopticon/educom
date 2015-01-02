<?php

require_once('conexion.php');

unset($_SESSION['userlog']);
session_destroy();

redirect('login.php');