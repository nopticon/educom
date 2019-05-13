<?php

if (!defined('IN_APP')) exit;

// require_once(ROOT . 'objects/community.php');

$user->setup();

$comm = new community();
$comm->recent_members();
$current_time = time();

//
// Online
//
$sql = 'SELECT u.user_id, u.username, u.username_base, u.user_type, u.user_hideuser, s.session_ip
    FROM _members u, _sessions s
    WHERE s.session_time >= ??
        AND u.user_id = s.session_user_id
    ORDER BY u.username ASC, s.session_ip ASC';
$comm->online(sql_filter($sql, ($current_time - (5 * 60))), 'online', 'MEMBERS_ONLINE');

//
// Today Online
//
$minutes = date('is');
$timetoday = ($current_time - (60 * intval($minutes[0].$minutes[1])) - intval($minutes[2].$minutes[3])) - (3600 * $user->format_date($current_time, 'H'));

$sql = 'SELECT user_id, username, username_base, user_hideuser, user_type
    FROM _members
    WHERE user_type <> ?
        AND user_lastvisit >= ?
        AND user_lastvisit < ?
    ORDER BY username';
$comm->online(sql_filter($sql, USER_INACTIVE, $timetoday, ($timetoday + 86399)), 'online', 'MEMBERS_TODAY', 'MEMBERS_VISIBLE');

$template->set_filenames(array(
    'body' => 'community.online.htm')
);
$template->pparse('body');
