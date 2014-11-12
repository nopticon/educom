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
define('IN_APP', true);
require_once('./interfase/common.php');
require_once(ROOT . 'objects/board.php');

$user->init();
$user->setup();

$known = w('gmail.com hotmail.com hotmail.es starmedia.com latinmail.com nopticon.com');

$fix = w('yahoo.es.com:yahoo.com hotamil.com:hotmail.com htomail.com:hotmail.com ahoo.es:yahoo.com hotmmail.com:hotmail.com hayoo.es:yahoo.com hotamail.es:hotmail.es hotmail.com.es:hotmail.es hotmail.com.com:hotmail.com gmai.com:gmail.com hotmail.co:hotmail.com yahoo.om:yahoo.com yahoo.co:yahoo.com hotmail.yahoo:hotmail.com hotmail.cim:hotmail.com yahho.com:yahoo.com eotmail.com:hotmail.com googlemail.com:gmail.com yohoo.es:yahoo.com uahoo.com:yahoo.com yahoo.cl:yahoo.com gmaill.com:gmail.com homtmail.com:hotmail.com hotmail.cl:hotmail.com hotmali.com:hotmail.com 18hotmail.com:hotmail.com totmail.com:hotmail.com hotail.com:hotmail.com hotmail.bom:hotmail.com hotmail.org:hotmail.com gmil.com:gmail.com gmal.com:gmail.com hotmile.com:hotmail.com yahoo.e:yahoo.es hotmail.cm:hotmail.com hootmail.com:hotmail.com gyahoo.es:yahoo.es 25hotmail.com:hotmail.com hptmail.com:hotmail.com gamail.com:gmail.com hhotmail.com:hotmail.com hortmail.com:hotmail.com h0tmail.com:hotmail.com hotmai.es:hotmail.es hotmail.cokm:hotmail.com 1005hotmail.com:hotmail.com hotmailmail.com:hotmail.com 1975yahoo.com:yahoo.com hotmail.live:hotmail.com gamil.com:gmail.com homail.es:hotmail.es uahoo.es:yahoo.es yahoo.de:yahoo.com gmial.com:gmail.com gmail.es:gmail.com gmail.c:gmail.com hotmail.com.mx:hotmail.com hotmail.esz:hotmail.es htomauil.com:hotmail.com hotamil.es:hotmail.es yahoo.mx:yahoo.com yahu.com:yahoo.com yaoo.es:yahoo.es hotrmail.com:hotmail.com yaju.es:yahoo.es hotmail.con:hotmail.com 29msn.com:msn.com yahoomail.com:yahoo.com hotmail.net:hotmail.com otmail.com:hotmail.com hotmsil.com:hotmail.com yaho.com.mx:yahoo.com hormail.com:hotmail.com yohoo.com:yahoo.com yaho.es:yahoo.es ahoo.com:yahoo.com hoitmail.com:hotmail.com rockpublik.net:rockrepublik.net gmeil.com:gmail.com yaho.com:yahoo.com hitmail.com:hotmail.com hotmail.c:hotmail.com hoymail.com:hotmail.com yahool.com:yahoo.com hotmai.com:hotmail.com hotmal.com:hotmail.com 541hotmail.com:hotmail.com hotmail.com.ar:hotmail.com hotmeil.com:hotmail.com htmail.com:hotmail.com hotmailo.com:hotmail.com htoamil.com:hotmail.com');

/*$sql = 'SELECT user_id, username, user_email, user_public_email
	FROM _members
	WHERE user_id <> 1
	ORDER BY user_id';
$members = sql_rowset($sql);

foreach ($members as $row) {
	$sql = 'UPDATE _members SET user_email = ?, user_public_email = ?
		WHERE user_id = ?';
	sql_query(sql_filter($sql, strtolower($row['user_email']), strtolower($row['user_public_email']), $row['user_id']));
}*/

// ----------------------------

$sql = 'SELECT user_id, username, user_email, user_public_email
	FROM _members
	WHERE user_id <> 1
	ORDER BY user_id';
$members = sql_rowset($sql);

$rk = w();
$groups = w();
foreach ($members as $row) {
	$part = explode('@', $row['user_email']);
	
	foreach ($fix as $fixrow) {
		$fixpart = explode(':', $fixrow);
		
		if ($part[1] == $fixpart[0]) {
			$_new = $part[0] . '@' . $fixpart[1];
			
			$sql = 'UPDATE _members SET user_email = ?
				WHERE user_id = ?';
			sql_query(sql_filter($sql, $_new, $row['user_id']));
			
			$part[1] = $fixpart[1];
		}
	}
	
	if (!isset($groups[$part[1]])) {
		$groups[$part[1]] = 0;
	}
	
	switch ($part[1]) {
		case 'rockrepublik.net':
		case 'rockrepublik.com':
			$rk[$part[1]][] = $row['username'] . ' '  . $row['user_email'] . ' ' . $row['user_public_email'];
		break;
	}
	
	$groups[$part[1]]++;
}

_pre($rk);
_pre($groups);

?>