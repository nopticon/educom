<?php

if (!defined('IN_APP')) exit;

$max_email = 10;
@set_time_limit(120);

$emailer = new emailer();

$sql = "SELECT m.*
    FROM _members m
    LEFT JOIN _banlist b ON m.user_id = b.ban_userid
    WHERE user_type NOT IN (??)
        AND m.user_birthday LIKE '%??'
        AND m.user_birthday_last < ?
        AND b.ban_userid IS NULL
    ORDER BY m.username
    LIMIT ??";
$result = sql_rowset(sql_filter($sql, USER_INACTIVE, date('md'), date('Y'), $max_email));

$done = array();
$usernames = array();

foreach ($result as $row) {
    $emailer->from('notify');
    $emailer->use_template('user_birthday');
    $emailer->email_address($row->user_email);

    if (!empty($row->user_public_email) && $row->user_email != $row->user_public_email) {
        $emailer->cc($row->user_public_email);
    }

    $emailer->assign_vars(array(
        'USERNAME' => $row->username)
    );
    $emailer->send();
    $emailer->reset();

    $done[] = $row->user_id;
    $usernames[] = $row->username;
}

if (count($done)) {
    $sql = 'UPDATE _members SET user_birthday_last = ?
        WHERE user_id IN (??)';
    sql_query(sql_filter($sql, date('Y'), implode(',', $done)));
}

_pre('Done. @ ' . implode(', ', $usernames), true);
