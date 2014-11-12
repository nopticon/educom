<?php
/*
<Orion, a web development framework for RK.>
Copyright (C) <2011>  <Orion>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
	define('IN_APP', true);
	define('ROOT', './');
	
	require_once(ROOT . 'interfase/common.php');
	
	$module = request_var('module', '');
	
	if (!empty($module) && preg_match('#^([a-z\_]+)$#i', $module)) {
		$module_path = ROOT . 'objects/async/' . $module . '.php';
		
		if (@file_exists($module_path)) {
			$user->init(false);
			$user->setup();
			
			@require_once($module_path);
			return;
		}
	}
}

$file_content = @file('./template/exceptions/missing.htm');

$matches = array(
	'<!--#echo var="HTTP_HOST" -->' => $_SERVER['HTTP_HOST'],
	'<!--#echo var="REQUEST_URI" -->' => $_SERVER['REQUEST_URI']
);

$orig = $repl = array();
foreach ($matches as $row_k => $row_v) {
	$orig[] = $row_k;
	$repl[] = $row_v;
}

echo str_replace($orig, $repl, implode('', $file_content));
exit;