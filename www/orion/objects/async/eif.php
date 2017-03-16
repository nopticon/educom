<?php

if (!defined('IN_APP')) exit;

$event_id = request_var('event_id', 0);
$image_id = request_var('image_id', 0);

$sql = 'SELECT *
    FROM _events_images
    WHERE event_id = ?
        AND image = ?';
if (!$imaged = sql_fieldrow(sql_filter($sql, $event_id, $image_id))) {
    fatal_error();
}
$image_footer = request_var('image_footer', '', true);

$sql = 'UPDATE _events_images SET image_footer = ?
    WHERE event_id = ?
        AND image = ?';
sql_query(sql_filter($sql, $image_footer, $event_id, $image_id));

echo $image_footer;
exit;
