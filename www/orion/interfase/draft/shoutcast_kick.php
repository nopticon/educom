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
if (!isset($config['kick_script'])) {
	define('IN_APP', true);
	require_once('./interfase/common.php');
	
	$user->init(true, true);
	$user->setup();
	
	redirect(s_link('forum djs'));
}

$scl = array(
	'host' => 'statistics.streamsolutions.co.uk',
	'host_port' => '80',
	'var' => 'server_status',
	'value' => 'Server is currently down.',
	'data' => array(),
	
	'ip' => '216.66.69.4',
	'port' => '9594',
	'passwd' => 'h1dLm0aC3'
);

$stats_get_line = 'GET http://' . $scl['host'] . '/shoutcast/text/index.php?';
$stats_get_line.= 'server=' . $scl['ip'] . '&port=' . $scl['port'] . '&variable=' . $scl['var'] . " HTTP/1.0\r\n";
$stats_get_line.= "User-Agent: StreamSolutions  (Mozilla Compatible)\r\n\r\n";

// Open Connection
$fp = fsockopen($scl['host'] , $scl['host_port'], &$errno, &$errstr, 30);
if ($fp) {
	fputs($fp, $stats_get_line);
	
	$scl['data'][$scl['var']] = '';
	while (!feof($fp)) {
		$scl['data'][$scl['var']] .= fgets($fp, 1000);
	}
	fclose($fp);
	
	$lines = split("\n", $scl['data'][$scl['var']]);
	if ($lines[8] != $scl['value']) {
		$fp = @fsockopen($scl['ip'] , $scl['port'], &$errno, &$errstr, 30);
		if ($fp) {
			$kick_request = 'GET /admin.cgi?pass=' . $scl['passwd'] . '&mode=kicksrc' . " HTTP/1.0\r\n";
			$kick_request.= "User-Agent: StreamSolutions  (Mozilla Compatible)\r\n\r\n";
			$data = '';
			
			fwrite($fp, $kick_request);
			while (!feof($fp)) {
				$data .= fgets($fp, 1000);
			}
			fclose($fp);
			
			$lines = split("\n", trim($data));
			if (!isset($lines[4])) {
				$lines[4] = '';
			}
			
			// If successful
			if (strstr($lines[4], 'redirect')) {
				$insert = array(
					'log_uid' => $user->data['user_id'],
					'log_time' => time()
				);
				sql_insert('radio_dj_log', $insert);
			}
		}
	}
}

_pre('Disconnected.');