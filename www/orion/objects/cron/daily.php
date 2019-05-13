<?php

if (!defined('IN_APP')) exit;

$d = getdate();
$start_1 = mktime(0, 0, 0, $d['mon'], ($d['mday'] - 7), $d['year']);
$start_2 = mktime(0, 0, 0, $d['mon'], ($d['mday'] - 14), $d['year']);

//
// Optimize
set_config('site_disable', 1);

$sql = 'SHOW TABLES';
$result = sql_rowset($sql, false, false, false, MYSQL_NUM);

foreach ($result as $row) {
    $tables[] = $row[0];
}

$sql = 'OPTIMIZE TABLE ' . implode(', ', $tables);
sql_query($sql);

set_config('site_disable', 0);

_pre('Done.', true);
