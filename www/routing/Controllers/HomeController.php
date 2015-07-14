<?php

class HomeController extends BaseController {
	public function start() {
		global $user;

		$user->init();
		$user->setup();

		$home = new home();
		$home->news();

		page_layout('HOME', 'home');
	}
}