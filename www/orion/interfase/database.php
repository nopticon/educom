<?php
/*
<NPT, a web development framework.>
Copyright (C) <2009>  <NPT>

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
if (!defined('IN_APP')) exit;

if (!function_exists('sql_filter')) {
	function sql_filter() {
		global $db;

		return call_user_func_array(array($db, '__prepare'), func_get_args());
	}
}

function sql_insert($table, $insert) {
	global $db;

	$a = $db->sql_insert($table, $insert);
	return sql_error($a);
}

function sql_query($sql) {
	global $db;

	$a = $db->sql_query($sql);

	return sql_error($a);
}

function sql_transaction($status = 'begin') {
	global $db;

	$a = $db->sql_transaction($status);
	return sql_error($a);
}

function sql_field($sql, $field, $def = false) {
	global $db;

	$a = $db->sql_field($sql, $field, $def);
	return sql_error($a);
}

function sql_fieldrow($sql, $result_type = MYSQLI_ASSOC) {
	global $db;

	$a = $db->sql_fieldrow($sql, $result_type);
	return sql_error($a);
}

function sql_rowset($sql, $a = false, $b = false, $global = false, $type = MYSQLI_ASSOC) {
	global $db;

	$a = $db->sql_rowset($sql, $a, $b, $global, $type);

	return sql_error($a);
}

function sql_truncate($table) {
	global $db;

	$a = $db->sql_truncate($table);
	return sql_error($a);
}

function sql_total($table) {
	global $db;

	$a = $db->sql_total($table);
	return sql_error($a);
}

function sql_close() {
	global $db;

	$a = $db->sql_close();
	return sql_error($a);
}

function sql_queries() {
	global $db;

	$a = $db->sql_queries();
	return sql_error($a);
}

function sql_query_nextid($sql) {
	global $db;

	$a = $db->sql_query_nextid($sql);
	return sql_error($a);
}

function sql_nextid() {
	global $db;

	$a = $db->sql_nextid();
	return sql_error($a);
}

function sql_affected($sql) {
	global $db;

	$a = $db->sql_affected($sql);
	return sql_error($a);
}

function sql_affectedrows() {
	global $db;

	$a = $db->sql_affectedrows();
	return sql_error($a);
}

function sql_escape($sql) {
	global $db;

	$a = $db->sql_escape($sql);
	return sql_error($a);
}

function sql_build($cmd, $a, $b = false) {
	global $db;

	$a = $db->sql_build($cmd, $a, $b);
	return sql_error($a);
}

function sql_cache($sql, $sid = '', $private = true) {
	global $db;

	$a = $db->sql_cache($sql, $sid, $private);
	return sql_error($a);
}

function sql_cache_limit(&$arr, $start, $end = 0) {
	global $db;

	$a = $db->sql_cache_limit($arr, $start, $end);
	return sql_error($a);
}

function sql_numrows(&$a) {
	global $db;

	$a = $db->sql_numrows($a);
	return sql_error($a);
}

function sql_history() {
	global $db;

	$a = $db->sql_history();
	return sql_error($a);
}

function sql_error($a) {
	if (is_array($a)) {
		$a = json_decode(json_encode($a));
	}

	if (isset($a->type) && $a->type == 'mysql') {
		return fatal_error('mysql', html_entity_decode($a->message->message, ENT_QUOTES) . "\n\n" . html_entity_decode($a->message->sqltext, ENT_QUOTES));
	}

	return $a;
}