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

twitter();

function twitter() {
	header('Content-type: text/html; charset=utf-8');
	
	require_once(ROOT . 'interfase/twitter.php');
	
	$a = decode_ht('.htsa');
	
	foreach ($a as $i => $row) {
		$a[$i] = _decode($row);
	}
	
	$twitter = new Twitter($a[0], $a[1], $a[2], $a[3]);
	$channel = $twitter->load(Twitter::ME, 10);
	
	foreach ($channel->status as $status) {
		$in_reply = (int) $status->in_reply_to_user_id; 
		
		if ($in_reply) {
			continue;
		}
		
		$sql = 'SELECT tw_status
			FROM _twitter
			WHERE tw_status = ?';
		
		
		// Mon Aug 22 03:31:16 +0000 2011
		$created_at = $status->created_at;
		$format = 'D M d H:i:s P Y';
		
		$at = date_parse_from_format($format, $created_at);
		
		/*
		 * int gmmktime ([ int $hour = gmdate("H") [, int $minute = gmdate("i") [, int $second = gmdate("s") [, i
		 * nt $month = gmdate("n") [, int $day = gmdate("j") [, int $year = gmdate("Y") [, int $is_dst = -1 ]]]]]]] )
		 * */
		
		$created_date = gmmktime($at['hour'], $at['minute'], $at['second'], $at['month'], $at['day'], $at['year']);
		$message = htmlentities(Twitter::clickable($status->text), ENT_NOQUOTES, 'UTF-8');
		$message = str_replace(array('&lt;', '&gt;'), array('<', '>'), $message);
		
		$sql_insert = array(
			'status' => (string) $status->id,
			'time' => $created_date,
			'message' => $message,
			'name' => (string) $status->user->screen_name,
			'followers' => (int) $status->user->followers_count,
			'friends' => (int) $status->user->friends_count
		);
		
		echo '<pre>';
		print_r($sql_insert);
		echo '</pre>';
		//exit;
		
		// id created_at text
		
		//echo $status->created_at . '<br /><br />';
		//echo  . '<br /><br />';
	}
	
	/*
	 * <li><a href="http://twitter.com/<?php echo $status->user->screen_name ?>"><img src="<?php echo htmlspecialchars($status->user->profile_image_url) ?>">
			<?php echo htmlspecialchars($status->user->name) ?></a>:
			<?php echo Twitter::clickable($status->text) ?>
			<small>at <?php echo date("j.n.Y H:i", strtotime($status->created_at)) ?></small>
		</li>
	 * */
}
