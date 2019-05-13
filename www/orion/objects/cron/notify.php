<?php

if (!defined('IN_APP')) exit;

$d = getdate();
$start_1 = mktime(0, 0, 0, $d['mon'], ($d['mday'] - 7), $d['year']);
$start_2 = mktime(0, 0, 0, $d['mon'], ($d['mday'] - 14), $d['year']);

$sql = 'DELETE FROM _members_unread
    WHERE element = ?
        AND datetime < ??
        AND item NOT IN (
            SELECT topic_id
            FROM _forum_topics
            WHERE topic_announce = 1
        )';
sql_query(sql_filter($sql, UH_T, $start_1));

$sql = 'DELETE FROM _members_unread
    WHERE element = ?
        AND datetime < ??';
sql_query(sql_filter($sql, UH_N, $start_2));

_pre('Done.', true);
