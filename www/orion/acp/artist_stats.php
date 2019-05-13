<?php

if (!defined('IN_APP')) exit;

class __artist_stats extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $comments;

		$this->_artist();

		$sql = 'SELECT *, SUM(members + guests) AS total
			FROM _artists_stats
			WHERE ub = ?
			GROUP BY date
			ORDER BY date DESC';
		$stats = sql_rowset(sql_filter($sql, $this->object->ub), 'date');

		$years_sum = $years_temp = $years = w();

		foreach ($stats as $date => $void) {
			$year = substr($date, 0, 4);

			if (!isset($years_temp[$year])) {
				$years[] = $year;
				$years_temp[$year] = true;
			}

			if (!isset($years_sum[$year])) {
				$years_sum[$year] = 0;
			}

			$years_sum[$year] += $void->total;
		}
		unset($years_temp);

		if (count($years)) {
			rsort($years);
		} else {
			$years[] = date('Y');
		}

		$total_graph = 0;
		foreach ($years as $year) {
			_style('year', array(
				'YEAR' => $year)
			);

			if (!isset($years_sum[$year])) {
				$years_sum[$year] = 0;
			}

			for ($i = 1; $i < 13; $i++) {
				$month = (($i < 10) ? '0' : '') . $i;
				$monthdata = (isset($stats[$year . $month])) ? $stats[$year . $month] : w();
				$monthdata['total'] = isset($monthdata['total']) ? $monthdata['total'] : 0;
				$monthdata['percent'] = ($years_sum[$year] > 0) ? $monthdata['total'] / $years_sum[$year] : 0;
				$monthdata['members'] = isset($monthdata['members']) ? $monthdata['members'] : 0;
				$monthdata['guests'] = isset($monthdata['guests']) ? $monthdata['guests'] : 0;
				$monthdata['unix'] = gmmktime(0, 0, 0, $i, 1, $year) - $user->timezone - $user->dst;
				$total_graph += $monthdata['total'];

				_style('year.month', array(
					'NAME' => $user->format_date($monthdata['unix'], 'F'),
					'TOTAL' => $monthdata['total'],
					'MEMBERS' => $monthdata['members'],
					'GUESTS' => $monthdata['guests'],
					'PERCENT' => sprintf("%.1d", ($monthdata['percent'] * 100)))
				);
			}
		}

		v_style(array(
			'BEFORE_VIEWS' => number_format($this->object['views']),
			'SHOW_VIEWS_LEGEND' => ($this->object['views'] > $total_graph))
		);

		return;
	}
}
