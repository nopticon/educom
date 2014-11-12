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
class __conv extends common
{
	var $_no = true;
	var $methods = array();
	
	function home()
	{
		global $db, $nucleo;
		
		error_reporting(0);
		$v = $this->control->__(array('v' => array('default' => 0)));
		if (!$v['v']) {
			$sql = 'SELECT id
				FROM _dl
				WHERE ud = 1
					AND dl_mp3 = 0
				ORDER BY id
				LIMIT 1';
			$v['v'] = $this->_field($sql, 'id');
		}
		
		$sql = 'SELECT d.*, a.name
			FROM _dl d, _artists a
			WHERE d.id = ' . (int) $v['v'] . '
				AND d.ub = a.ub';
		if ($songd = $this->_fieldrow($sql)) {
			$spaths = '/data/artists/' . $songd['ub'] . '/media/';
			$spath = '/var/www/vhosts/rockrepublik.net/httpdocs' . $spaths;
			$songid = $songd['id'];
			$fwma = $spath . $songid . '.wma';
			$fmp3 = $spath . $songid . '.mp3';
			
			if (@file_exists('.' . $spaths . $songid . '.wma') && !@file_exists('.' . $spaths . $songid . '.mp3') && !$songd['dl_mp3']) {
				exec('ffmpeg -i ' . $fwma . ' -vn -ar 44100 -ac 2 -ab 64kb -f mp3 ' . $fmp3);
				
				// MP3 tags
				$tag_format = 'UTF-8';
				
				include_once(SROOT . 'core/getid3/getid3.php');
				$getID3 = new getID3;
				$getID3->setOption(array('encoding' => $tag_format));
				getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__, true);
				
				$tagwriter = new getid3_writetags;
				$tagwriter->filename = getid3_lib::SafeStripSlashes($fmp3);
				$tagwriter->tagformats = array('id3v1');
				$tagwriter->overwrite_tags = true;
				$tagwriter->tag_encoding = $tag_format;
				$tagwriter->remove_other_tags = true;
				$tag_comment = 'Visita www.rockrepublik.net';
				
				$songd['album'] = (!empty($songd['album'])) ? $songd['album'] : 'Single';
				$songd['genre'] = (!empty($songd['genre'])) ? $songd['genre'] : 'Rock';
				
				$songd_f = array('title', 'name', 'album', 'genre');
				foreach ($songd_f as $songd_r)
				{
					$songd[$songd_r] = getid3_lib::SafeStripSlashes(utf8_encode(html_entity_decode($songd[$songd_r])));
				}
				
				$tagwriter->tag_data = array(
					'title' => array($songd['title']),
					'artist' => array($songd['name']),
					'album' => array($songd['album']),
					'year' => array(getid3_lib::SafeStripSlashes($songd['year'])),
					'genre' => array($songd['genre']),
					'comment' => array(getid3_lib::SafeStripSlashes($tag_comment)),
					'tracknumber' => array('')
				);
				$tagwriter->WriteTags();
				
				$sql = 'UPDATE _dl SET dl_mp3 = 1
					WHERE id = ' . (int) $songd['id'];
				$db->sql_query($sql);
				
				$fp = @fopen('./conv.txt', 'a+');
				fwrite($fp, $fmp3 . "\n");
				fclose($fp);
			}
			
			if (!@file_exists('.' . $spaths . $songid . '.wma'))
			{
				$sql = 'UPDATE _dl SET dl_mp3 = 2
					WHERE id = ' . (int) $songd['id'];
				$db->sql_query($sql);
			}
		}
		
		$sql = 'SELECT id
			FROM _dl
			WHERE ud = 1
				AND dl_mp3 = 0
			ORDER BY id
			LIMIT 1';
		if ($v_next = $this->_field($sql, 'id', 0)) {
			sleep(1);
			$nucleo->redirect($nucleo->link('conv', array('v' => $v_next)));
		} else {
			die('no_next');
		}
		
		return $this->e('.');
	}
}