<?php

define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$sql = 'SELECT *
	FROM _partners
	ORDER BY partner_order';
$partners = sql_rowset($sql);

foreach ($partners as $i => $row) {
	if (!$i) _style('partners');

	_style('partners.row', array(
		'NAME' => $row->partner_name,
		'IMAGE' => $row->partner_image,
		'URL' => $config->assets_url . '/style/sites/' . $row->partner_url)
	);
}

page_layout('PARTNERS', 'partners', false, false);
