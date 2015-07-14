<?php

class NewsController extends BaseController {
	public function start($module, $args) {
		global $user;

		$user->init();
		$user->setup();

		$news = new news();
		$news->run();

		page_layout($news->get_title('NEWS'), $news->get_template('news'));
	}
}