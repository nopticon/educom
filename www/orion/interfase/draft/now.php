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
$scl = array(
	'host' => 'statistics.streamsolutions.co.uk',
	'host_port' => '80',
	
	'ip' => '216.66.69.4',
	'port' => '9594',
	'value' => 'Server is currently down.',
	'down' => 'La radio no est&aacute; disponible en este momento.',
	'data' => array()
);
$errno = 0;
$errstr = '';

$stats_get_line = 'GET http://' . $scl['host'] . '/shoutcast/text/index.php?';
$stats_get_line.= 'server=' . $scl['ip'] . '&port=' . $scl['port'] . " HTTP/1.0\r\n";
$stats_get_line.= "User-Agent: StreamSolutions  (Mozilla Compatible)\r\n\r\n";

// Open Connection
$fp = fsockopen($scl['host'] , $scl['host_port'], $errno, $errstr, 30);
if (!$fp) {
	_pre($scl['down'], true);
}

$data = '';
fwrite($fp, $stats_get_line);
while (!feof($fp)) {
	$data .= fgets($fp, 1000);
}
fclose($fp);

$lines = array_slice(split("\n", trim($data)), 8);
foreach ($lines as $line) {
	$e = explode('<SSTAG>', $line);
	$scl['data'][$e[0]] = $e[1];
}

if ($scl['data']['server_status'] == $scl['value']) {
	_pre($scl['down'], true);
}

// Parse song
$song = array_map('trim', explode('-', $scl['data']['current_song']));
$song[1] = array_pop($song);

if (!empty($scl['data']['stream_title']) && $scl['data']['stream_title'] != $config['sitename']) {
	echo '<div class="live">Al Aire</div><div class="livetitle"><span>' . $scl['data']['stream_title'] . '</span></div>';
}

foreach ($song as $row) {
	echo '<div>' . $row . '</div>';
}