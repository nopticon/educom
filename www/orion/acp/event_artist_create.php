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

class __event_artist_create extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('mod');
	}
	
	/*
	Show form listing all event available to this artist.
	*/
	public function _home() {
		global $config, $user, $cache;

		$this->_artist();

		if ($this->create()) {
			return;
		}

		$ini_year = mktime(0, 0, 0, 1, 1);

		$sql = 'SELECT *
			FROM _events
			WHERE date > ??
			ORDER BY date DESC';
		$events = sql_rowset(sql_filter($sql, $ini_year));

		$last_month = '';
		foreach ($events as $i => $row) {
			if (!$i) _style('events');

			$row_month = ucfirst($user->format_date($row->date, 'F \'y'));

			if ($last_month != $row_month) {
				$last_month = $row_month;

				_style('events.month', array(
					'NAME' => $row_month)
				);
			}

			_style('events.month.row', array(
				'ID' => $row->id,
				'TITLE' => $row->title,
				'DATE' => $user->format_date($row->date))
			);
		}

		return;
	}

	/*
	Assign an event to selected artist.
	*/
	private function create() {
		$v = _request(array('event' => 0));
		
		if (_empty($v)) {
			return;
		}
		
		$sql = 'SELECT id, event_alias
			FROM _events
			WHERE id = ?';
		if (!$event = sql_fieldrow(sql_filter($sql, $v->event))) {
			return;
		}

		$sql = 'SELECT ub
			FROM _artists_events
			WHERE a_artist = ?
				AND a_event = ?';
		if (sql_field(sql_filter($sql, $this->object->ub, $v->event))) {
			return;
		}

		$sql_insert = array(
			'a_artist' => $this->object->ub,
			'a_event' => $event->id
		);
		sql_insert('artists_events', $sql_insert);
		
		return redirect(s_link('events', $event->event_alias));
	}
}