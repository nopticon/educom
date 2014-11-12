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
if (!defined('IN_APP')) exit;

require_once(ROOT . 'interfase/artists.php');

$artists = new _artists();
$artists->get_data();

$a_ary = array();
for ($i = 0; $i < 4; $i++) {
	$_a = array_rand($artists->adata);
	if (!$artists->adata[$_a]['images'] || isset($a_ary[$_a])) {
		$i--;
		continue;
	}
	$a_ary[$_a] = $artists->adata[$_a];
}

if (sizeof($a_ary))
{
	$sql = 'SELECT *
		FROM _artists_images
		WHERE ub IN (??)
		ORDER BY RAND()';
	$result = sql_rowset(sql_filter($sql, implode(',', array_keys($a_ary))));
	
	$random_images = array();
	foreach ($result as $row) {
		if (!isset($random_images[$row['ub']])) {
			$random_images[$row['ub']] = $row['image'];
		}
	}
	
	$return_string = '<table width="100%" class="t-collapse"><tr>';
	
	$i = 0;
	foreach ($a_ary as $ub => $data) {
		$url = s_link('a', $data['subdomain']);
		$return_string .= '<td class="' . (($i % 2) ? 'dark-color' : '') . ' pad6"><a href="' . $url . '">' . $data['name'] . '</a><br/ ><small>' . (($data['local']) ? 'Guatemala' : $data['location']) . '</small><br /><div class="sep2-top"><a href="' . $url . '"><img class="box" src="/data/artists/' . $ub . '/thumbnails/' . $random_images[$ub] . '.jpg" title="' . $data['genre'] . '" /></a></div></td>';
		$i++;
	}
	
	$return_string .= '</tr></table>';
	echo rawurlencode($return_string);
}

?>