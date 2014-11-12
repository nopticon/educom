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

$user->init();
$user->setup();

$forum_id = 22;

$sql = 'SELECT *
	FROM _forum_topics
	WHERE forum_id = ?';
$a_topics = sql_rowset(sql_filter($sql, $forum_id), 'topic_id');

$topics = array(
	3454 => $a_topics[3454],
	3468 => $a_topics[3468],
	3455 => $a_topics[3455],
	3456 => $a_topics[3456],
	
	3479 => $a_topics[3479],
	3480 => $a_topics[3480],
	3481 => $a_topics[3481],
	3482 => $a_topics[3482]
);

if (time() >= 1197093599) {
	_style('expired');
} else {
	//
	// Get data for all polls
	//
	foreach ($topics as $topic_id => $row) {
		$topic_url = s_link('topic', $topic_id);
		
		$sql = 'SELECT vd.vote_id, vd.vote_text, vd.vote_start, vd.vote_length, vr.vote_option_id, vr.vote_option_text, vr.vote_result
			FROM _poll_options vd, _poll_results vr
			WHERE vd.topic_id = ?
				AND vr.vote_id = vd.vote_id
			ORDER BY vr.vote_option_order, vr.vote_option_id ASC';
		if ($vote_info = sql_rowset(sql_filter($sql, $topic_id))) {
			$vote_options = count($vote_info);
			
			$sql = 'SELECT vote_id
				FROM _poll_voters
				WHERE vote_id = ?
					AND vote_user_id = ?';
			$user_voted = sql_field(sql_filter($sql, $vote_info[0]['vote_id'], $user->data['user_id']), 'vote_id', 0);
	
			_style('poll', array(
				'POLL_TITLE' => $vote_info[0]['vote_text'])
			);
			
			if ($user_voted) {
				_style('poll.results');
			} else {
				_style('poll.options', array(
					'S_VOTE_ACTION' => $topic_url)
				);
	
				for ($i = 0; $i < $vote_options; $i++) {
					_style('poll.options.item', array(
						'POLL_OPTION_ID' => $vote_info[$i]['vote_option_id'],
						'POLL_OPTION_CAPTION' => $vote_info[$i]['vote_option_text'],
						'POLL_OPTION_LINK' => s_link('a', get_username_base($vote_info[$i]['vote_option_text'])))
					);
				}
			}
		}
	}
}

$template_vars = array(
	'S_TOPIC_ACTION' => $topic_url . (($start) ? 's' . $start . '/' : ''),
	'U_VIEW_FORUM' => s_link('forum', $forum_id)
);
page_layout('AWARDS', 'awards_voting', $template_vars);