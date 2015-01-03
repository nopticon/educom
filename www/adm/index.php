<?php

require_once('./conexion.php');

if (!$user->is('member')) {
	do_login();
}

page_layout('HOME', 'home', false, false);