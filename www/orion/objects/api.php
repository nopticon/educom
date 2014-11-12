<?php

class service {
	protected static $arg = array();
	protected static $config;			// Config for authenticated application
	protected static $users = array();	// Array of account id's

	/**
	Receive the list of arguments of every method in class.
	@return 	true
	*/
	public function __combine($method, $arg, $assign = false) {
		if ($assign !== false) {
			self::$arg[$method] = w($arg);
			return true;
		}

		$response = w();

		if (!isset(self::$arg[$method])) {
			return $response;
		}

		return array_combine(self::$arg[$method], $arg);
	}

	/**
	Method for removing private information returned by SSO system
	@return:	(object)
	*/
	protected function __rm_sso_keys(& $object) {
		$keys = 'userpassword identitydetails_name identitydetails_type identitydetails_realm sunidentitymsisdnnumber iplanet_am_user_password_reset_force_reset manager birthday gender';
		$keys .= ' iplanet_am_user_password_reset_question_answer universalid iplanet_am_user_success_url iplanet_am_user_auth_config iplanet_am_user_password_resetoptions';
		$keys .= ' iplanet_am_user_failure_url iplanet_am_user_account_life changepassword postaladdress employeenumber preferredlocale inetuserstatus telephonenumber telephonenumber2 telephonenumber3';

		foreach (w($keys) as $row) {
			if (isset($object->$row)) {
				unset($object->$row);
			}
		}

		return;
	}

	/**
	Encode password for storage and comparison
	*/
	protected function password_encode($password) {
		return sha1(md5($password));
	}

	/**
	Receive XML (APPID, APPSECRET, LANG) and the name of method to check if this application and method has permission for execution.
	@return:	0 or error message
	*/
	protected function __app_authenticate($config, $method) {
		$response = false;

		if (!is_array($config)) {
			$config = xml2array($config);

			if (isset($config['DATA'])) {
				$config = $config['DATA'];
			} else {
				$response = true;
			}
		}

		if ($response === false) {
			self::$config = (object) array_lower($config);
			unset($config);

			$sql = 'SELECT *
				FROM applications
				WHERE app_identify = ?
					AND app_secret = ?';
			if (!$app = sql_fieldrow(sql_filter($sql, self::$config->appid, self::$config->appsecret))) {
				$response = true;
			}

			if ($response === false) {
				$a = (object) $app;
				unset($app);

				self::$config->app = $a->app_id;

				$a->app_start = strtotime($a->app_start);
				$a->app_end = strtotime($a->app_end);

				if ((!empty($a->app_start) && $a->app_start >= time()) || (!empty($a->app_end) && time() >= $a->app_end)) {
					$response = true;
				}

				if ($response === false && !empty($a->app_ip_range)) {
					$detected_ip = htmlspecialchars(get_real_ip());
					$ip_part = explode(',', $a->app_ip_range);

					foreach ($ip_part as $row) {
						if (!ip_in_range($detected_ip, $row)) {
							$response = true;
							break;
						}
					}
				}

				if ($response === false) {
					$sql = 'SELECT method_id
						FROM methods
						INNER JOIN methods_app_rel ON rel_method = method_id
						WHERE rel_app = ?
							AND method_name = ?';
					if (!$method = sql_field(sql_filter($sql, $a->app_id, $method), 'method_id', 0)) {
						$response = true;
					}
				}
			}
		}

		return ($response === true) ? self::lang('AUTHENTICATION_FAILED') : false;
	}

	/**
	Test method
	*/
	public function test() {
		return xml('Connection successful.');
	}

	/**
	Method for creating companies.
	@return:	(xml) 1 or 0
	*/
	public function company_create() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$alias = alias($name);
		$response = false;

		$sql = 'SELECT company_id
			FROM companies
			WHERE company_alias = ?';
		if (!sql_field(sql_filter($sql, $alias), 'company_id', 0)) {
			$sql_insert = array(
				'company_alias' => $alias,
				'company_name' => $name
			);
			$sql = sql_build('INSERT', $sql_insert, 'companies');
			sql_query($sql);

			$response = true;
		}

		return xml($response);
	}

	/**
	Method for company modification.
	@return:	(xml) 1 or 0
	*/
	public function company_modify() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$alias = alias($name);
		$response = false;

		$sql = 'SELECT company_id
			FROM companies
			WHERE company_alias = ?';
		if ($company_id = sql_field(sql_filter($sql, $alias), 'company_id', 0)) {
			$sql = 'UPDATE companies SET company_alias = ?, company_name = ?
				WHERE company_id = ?';
			sql_query(sql_filter($sql, alias($replace), $replace, $company_id));

			$response = true;
		}

		return xml($response);
	}

	/**
	Get list of all applications for a company.
	@return:	(xml) list or 0
	*/
	public function app_list() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$sql = 'SELECT a.app_name, a.app_identify
			FROM companies c, applications a
			WHERE c.company_name = ?
				AND c.company_id = a.app_company
			ORDER BY a.app_name';
		$response = sql_rowset(sql_filter($sql, $company));

		return xml($response);
	}

	/**
	Method for creating application for a company.
	@return:	(xml) 1 or 0
	*/
	public function app_create() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;
		$company = alias($company);

		$sql = 'SELECT company_id
			FROM companies
			WHERE company_alias = ?';
		if ($company_id = sql_field(sql_filter($sql, $company), 'company_id', 0)) {
			$sql = 'SELECT app_id
				FROM applications
				WHERE app_name = ?';
			if (!sql_field(sql_filter($sql, $name), 'app_id', 0)) {
				$app_identify = token(20);
				$app_secret = token(40);

				$response = array(
					'app_identify' => $app_identify,
					'app_secret' => $app_secret
				);

				$sql_insert = array(
					'app_company' => $company_id,
					'app_name' => alias($name),
					'app_identify' => $app_identify,
					'app_secret' => $app_secret,
					'app_creation' => datetime(),
					'app_start' => $start,
					'app_end' => $end,
					'app_ip_range' => $ip
				);
				$sql = sql_build('INSERT', $sql_insert, 'applications');
				sql_query($sql);
			}
		}

		return xml($response);
	}

	/**
	Method for modify an application.
	@return:	1 or 0
	*/
	public function app_modify() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT app_id
			FROM companies
			WHERE company_name = ?';
		if ($company_id = sql_field(sql_filter($sql, $company), 'company_id', 0)) {
			$sql = 'SELECT app_id
				FROM applications
				WHERE app_name = ?';
			if ($app_id = sql_field(sql_filter($sql, $name), 'app_id', 0)) {
				$sql_update = array(
					'app_name' => $name,
					'app_start' => $start,
					'app_end' => $end,
					'app_ip_range' => $ip
				);
				$sql = 'UPDATE applications SET ??
					WHERE app_id = ?';
				sql_query(sql_filter($sql, sql_build('UPDATE', $sql_update), $app_id));

				$response = true;
			}
		}

		return xml($response);
	}

	/**
	Generate new credentials for an application.
	@return:	(xml) new credentials or 0
	*/
	public function app_secret() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT company_id
			FROM companies
			WHERE company_name = ?';
		if (sql_field(sql_filter($sql, $company), 'company_id', 0)) {
			$sql = 'SELECT app_id
				FROM applications
				WHERE app_name = ?';
			if ($app_id = sql_field(sql_filter($sql, $name), 'app_id', 0)) {
				$app_identify = token(20);
				$app_secret = token(40);

				$response = array(
					'app_identify' => $app_identify,
					'app_secret' => $app_secret
				);
				$sql = 'UPDATE applications SET ??
					WHERE app_id = ?';
				sql_query(sql_filter($sql, sql_build('UPDATE', $response), $app_id));
			}
		}

		return xml($response);
	}

	/**
	Associate new methods with applications to has permission for access it.
	@return:	(xml) 1 or 0
	*/
	public function app_method() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		if (!empty($application)) {
			$sql = 'SELECT app_id
				FROM applications
				WHERE app_name = ?';
			$application = sql_field(sql_filter($sql, $application), 'app_id', 0);
		} else {
			$application = self::$config->app;
		}

		if (!empty($application)) {
			$sql = 'SELECT method_id
				FROM methods
				WHERE method_name = ?';
			if ($method_id = sql_field(sql_filter($sql, $method), 'method_id', 0)) {
				$sql = 'SELECT rel_id
					FROM methods_app_rel
					WHERE rel_app = ?
						AND rel_method = ?';
				if (!sql_field(sql_filter($sql, $application, $method_id), 'rel_id', 0)) {
					$sql_insert = array(
						'rel_app' => $application,
						'rel_method' => $method_id,
						'rel_read' => '',
						'rel_insert' => '',
						'rel_update' => '',
						'rel_delete' => ''
					);
					$sql = sql_build('INSERT', $sql_insert, 'methods_app_rel');
					sql_query($sql);

					$response = true;
				}
			}
		}

		return xml($response);
	}

	/**
	Validate if authenticated application has permission to access a method.
	@return:	(xml) 1 or 0
	*/
	public function app_method_validate() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT r.rel_id
			FROM methods m, methods_app_rel r
			WHERE m.method_name = ?
				AND r.rel_app = ?
				AND r.rel_method = m.method_id';
		if (sql_field(sql_filter($sql, $method, self::$config->app), 'rel_id', 0)) {
			$response = true;
		}

		return xml($response);
	}

	/**
	Loop over all class methods, check if exists on database and insert if needed.
	@return:	(xml) insert count
	*/
	public function system_methods() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$skip = '__combine __rm_sso_keys __app_authenticate lang test getUserid getAttribute getAttrid getValueid manage_field getDataid manage_property field_now_value find_value password_encode getAttributes find_duplicate';
		$response = get_class_methods(__CLASS__);

		foreach ($response as $i => $row) {
			if (strpos($skip, $row) !== false) unset($response[$i]);
		}

		$sql = 'SELECT method_name
			FROM methods
			ORDER BY method_name';
		$methods = sql_rowset($sql, 'method_name', 'method_name');

		$i = 0;
		foreach ($response as $row) {
			if (isset($methods[$row])) continue;

			$sql_insert = array(
				'method_name' => $row
			);
			$sql = sql_build('INSERT', $sql_insert, 'methods');
			sql_query($sql);
			$i++;
		}

		return xml($i);
	}

	/**
	Receive email and password for user authentication.
	@return:	(xml) user token or 0
	*/
	public function user_authenticate() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$email = strtolower($email);

		$ws = new libws('sso');
		$sso_response = $ws->__sso_read($email);

		$response = false;

		if (isset($sso_response->user_id)) {
			$sql = 'SELECT status_alias, status_id
				FROM users_status
				ORDER BY status_id';
			$user_status = (object) sql_rowset($sql, 'status_alias', 'status_id');

			$sql = 'SELECT *
				FROM users
				WHERE email = ?';
			if (!$userdata = sql_fieldrow(sql_filter($sql, $email, 0))) {
				$sql_insert = array(
					'email' => $email,
					'block' => 0,
					'blocked_time' => '',
					'try_count' => 0,
					'token_sso' => '',
					'token_block' => '',
					'user_status' => $user_status->A
				);
				$sql = sql_build('INSERT', $sql_insert, 'users');
				$insert_res = sql_query($sql);

				$sql = 'SELECT *
					FROM users
					WHERE email = ?';
				$userdata = sql_fieldrow(sql_filter($sql, $email, 0));
			}

			$userdata = (object) $userdata;

			if (!$userdata->block && $userdata->user_status == $user_status->A) {
				$password = self::password_encode($password);

				$sso_response = $ws->authenticate(array(
					'username' => $email,
					'password' => $password)
				);

				if (isset($sso_response->token_id)) {
					$sql = 'UPDATE users SET try_count = 0
						WHERE user_id = ?';
					$update_res = sql_query(sql_filter($sql, $userdata->user_id));

					$response = array(
						'token' => $sso_response->token_id
					);
				} else {
					if ($userdata->try_count >= 5) {
						$response = array('email_token' => token());

						$sql = 'UPDATE users SET block = ?, token_block = ?, blocked_time = NOW(), user_status = ?
							WHERE user_id = ?';
						$update_res = sql_query(sql_filter($sql, 1, $response['email_token'], $user_status->B, $userdata->user_id));
					} else {
						$sql = 'UPDATE users SET try_count = try_count + 1
							WHERE user_id = ?';
						$insert_res = sql_query(sql_filter($sql, $userdata->user_id));

						$response = false;
					}
				}
			}
		}

		return xml($response);
	}

	/**
	Method to create users on SSO system and database.
	@return:	(xml) user info or 0
	*/
	public function user_create() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$email = strtolower($email);

		if (!preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is', $email)) {
			return xml(false);
		}

		$response = false;
		$ws = new libws('sso');

		/**
		Generate activation token.
		*/
		$activation_token = token(20);
		$activation = encode($email . ':' . $activation_token);

		/**
		Check if password string is empty and copy activation token.
		*/
		if (empty($password)) {
			$password = $activation_token;
		}

		if (empty($email)) {
			$response = self::lang('EMAIL_REQUIRED');
		}

		if ($response === false && (strlen($password) < 8)) {
			$response = self::lang('PASSWORD_COMPLEX');
		}

		if ($response === false) {
			$password = self::password_encode($password);

			$response = $ws->__sso_create($email, $password, $firstname, $lastname);

			if (isset($response->user_id)) {
				self::__rm_sso_keys($response);

				$sql = 'SELECT user_id
					FROM users
					WHERE email = ?';
				$result_users = sql_field(sql_filter($sql, $email), 'user_id', 0);

				if (!$result_users) {
					$response->activation = $activation;

					$sql = 'SELECT status_id
						FROM users_status
						WHERE status_alias = ?';
					$user_status = sql_field(sql_filter($sql, 'I'), 'status_id', 0);

					$sql_insert = array(
						'email' => $email,
						'block' => 0,
						'blocked_time' => '',
						'try_count' => 0,
						'token_sso' => '',
						'token_block' => $activation,
						'user_status' => $user_status
					);
					$sql = sql_build('INSERT', $sql_insert, 'users');
					$result_insert = sql_affected($sql);

					if (!$result_insert) {
						//$response = self::lang('NO_INSERT_VERIFICATION');
					} 
				} 
			}
		}

		return xml($response);
	}

	/**
	Method to verify token for account activation
	If password was given in user_create, account will be activated in this method;
	else, method returns (password = 1) waiting for activation in user_activate_confirm
	@return:	(xml) 1 or (password = 1)
	*/
	public function user_activate_token() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = new stdClass;

		if (empty($token)) {
			return xml(self::lang('TOKEN_INVALID'));
		}
		
		$token_part = explode(':', decode($token));
		$email = array_key($token_part, 0);
		$password = array_key($token_part, 1);

		$sql = 'SELECT status_alias, status_id
			FROM users_status
			ORDER BY status_id';
		$user_status = (object) sql_rowset($sql, 'status_alias', 'status_id');

		$sql = 'SELECT user_id, token_block, user_status
			FROM users
			WHERE email = ?';
		if (!$result = sql_fieldrow(sql_filter($sql, $email))) {
			$response = self::lang('TOKEN_INVALID');
		}

		if (is_object($response)) {
			if (!empty($token) && ($result['user_status'] == $user_status->I) && ($token === $result['token_block'])) {
				$ws = new libws('sso');
				$sso_response = $ws->__sso_read($email);

				if (isset($sso_response->userpassword)) {
					if ($sso_response->userpassword === $password) {
						$response->password = true;
					} else {
						$sql = 'UPDATE users SET token_block = ?, user_status = ?
							WHERE user_id = ?';
						sql_query(sql_filter($sql, '', $user_status->A, $result['user_id']));

						$response->activate = true;
					}
				}
			} else {
				$response = self::lang('TOKEN_INVALID');
			}
		}

		if (is_object($response) && !isset($response->activate) && !isset($response->password)) {
			$response = false;
		}

		return xml($response);
	}

	/**
	Method to activate a recently created user account with new password and confirmation
	@return:	(xml) 1 or 0
	*/
	public function user_activate_confirm() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		if (empty($token)) {
			$response = self::lang('TOKEN_INVALID');
		}

		if ($response === false) {
			$token_part = explode(':', decode($token));
			$email = array_key($token_part, 0);
			$password_old = array_key($token_part, 1);

			$sql = 'SELECT user_id, token_block, user_status
				FROM users
				WHERE email = ?';
			if (!$result = sql_fieldrow(sql_filter($sql, $email))) {
				$response = self::lang('TOKEN_INVALID');
			}
		}

		if ($response === false) {
			$sql = 'SELECT status_alias, status_id
				FROM users_status
				ORDER BY status_id';
			$user_status = (object) sql_rowset($sql, 'status_alias', 'status_id');

			if ((!empty($token) && $token !== $result['token_block']) || ($result['user_status'] != $user_status->I)) {
				$response = self::lang('TOKEN_INVALID');
			}
		}

		if ($response === false && (empty($password_new) || empty($password_confirm))) {
			$response = self::lang('PASSWORDS_EMPTY');
		}

		if ($response === false && (strlen($password_new) < 8)) {
			$response = self::lang('PASSWORD_COMPLEX');
		}

		if ($response === false) {
			$ws = new libws('sso');
			$userdata = $ws->__sso_read($email);

			if ($password_old !== $userdata->userpassword) {
				$response = self::lang('PASSWORDS_NOT_MATCH');
			}
			unset($userdata);
		}

		if ($response === false) {
			if ($password_new === $password_confirm) {
				$response = $ws->__sso_update($email, 'userpassword', $password_new);

				$sql = 'UPDATE users SET token_block = ?, user_status = ?
					WHERE user_id = ?';
				sql_query(sql_filter($sql, '', $user_status->A, $result['user_id']));
			} else {
				$response = self::lang('PASSWORDS_NOT_CONFIRM');
			}
		}

		return xml($response);
	}

	/**
	Read all information about a user.
	@return:	(xml) list of properties and values
	*/
	public function user_read() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sso');

		$response = $ws->__sso_read($email);
		self::__rm_sso_keys($response);

		return xml($response);
	}

	/**
	Read all information about the logged user.
	@return:	(xml) list of properties and values
	*/
	public function user_attributes() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sso');

		$response = $ws->__sso_attributes($token);
		self::__rm_sso_keys($response);

		return xml($response);
	}

	/**
	Logout a user from system.
	@return:	(xml)
	*/
	public function user_logout() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		if (!empty($token)) {
			$ws = new libws('sso');

			$response = $ws->logout(array(
				'subjectid' => $token)
			);

			$response = (isset($response->exception_name)) ? false : true;
		}

		if ($response === false) {
			$response = self::lang('LOGOUT_ERROR');
		}

		return xml($response);
	}

	/**
	Check if the user has a valid token.
	@return:	(xml)
	*/
	public function user_token() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sso');

		$response = $ws->isTokenValid(array(
			'tokenid' => $token)
		);

		return xml($response);
	}

	/**
	Update information about a user.
	@return:	(xml)
	*/
	public function user_update() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?';
		if (!sql_field(sql_filter($sql, $email), 'user_id', 0)) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($response === false) {
			$ws = new libws('sso');
			$response = $ws->__sso_update($email, $name, $value);
		}

		return xml($response);
	}

	/**
	Update a user status to deleted.
	@return:	(xml)
	*/
	public function user_delete() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT status_id
			FROM users_status
			WHERE status_alias = ?';
		$status_deleted = sql_field(sql_filter($sql, 'E'), 'status_id', 0);

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?
				AND user_status <> ?';
		if (!$user_id = sql_field(sql_filter($sql, $email, $status_deleted), 'user_id', 0)) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($response === false) {
			$sql = 'UPDATE users SET user_status = ?
				WHERE user_id = ?';
			sql_query(sql_filter($sql, $status_deleted, $user_id));
		}

		return xml($response);
	}

	/**
	Method to update user password, old password is required.
	@return 	(xml)
	*/
	public function user_password() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?';
		if (!sql_field(sql_filter($sql, $email), 'user_id', 0)) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($response === false && (empty($password_old) || empty($password_new) || empty($password_confirm))) {
			$response = self::lang('PASSWORDS_EMPTY');
		}

		if ($response === false && (strlen($password_new) < 8)) {
			$response = self::lang('PASSWORD_COMPLEX');
		}

		if ($response === false) {
			$ws = new libws('sso');
			$userdata = $ws->__sso_read($email);

			if ($password_old !== $userdata->userpassword) {
				$response = self::lang('PASSWORDS_NOT_MATCH');
			}
			unset($userdata);

			if ($password_old === $password_new) {
				$response = self::lang('PASSWORDS_SAME');
			}
		}

		if ($response === false) {
			if ($password_new === $password_confirm) {
				$response = $ws->__sso_update($email, 'userpassword', $password_new);
			} else {
				$response = self::lang('PASSWORDS_NOT_CONFIRM');
			}
		}

		return xml($response);
	}

	/**
	Request a token to send email, then with this token, ask for a new password.
	@return 	(xml)
	*/
	public function user_password_reset() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?';
		if (!$user_id = sql_field(sql_filter($sql, $email), 'user_id', 0)) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($response === false) {
			$token = token();

			$sql = 'UPDATE users SET token_block = ?
				WHERE user_id = ?';
			sql_query(sql_filter($sql, $token, $user_id));

			$response = array(
				'token' => $token
			);
		}

		return xml($response);
	}

	/**
	Validate if given token for password recovery is valid.
	@return 	(xml)
	*/
	public function user_password_token() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT *
			FROM users
			WHERE email = ?';
		if (!$result = sql_fieldrow(sql_filter($sql, $email))) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($response === false) {
			if (!empty($token) && ($token === $result['token_block'])) {
				$response = true;
			} else {
				$response = self::lang('TOKEN_INVALID');
			}
		}

		return xml($response);
	}

	/**
	Change the user password receiving token from email and the new password.
	@return 	(xml)
	*/
	public function user_password_confirm() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		$sql = 'SELECT *
			FROM users
			WHERE email = ?';
		if (!$result = sql_fieldrow(sql_filter($sql, $email))) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($response === false && (empty($token) || ($token !== $result['token_block']))) {
			$response = self::lang('TOKEN_INVALID');
		}

		if ($response === false && (empty($password_new) || empty($password_confirm))) {
			$response = self::lang('PASSWORDS_EMPTY');
		}

		if ($response === false && (strlen($password_new) < 8)) {
			$response = self::lang('PASSWORD_COMPLEX');
		}

		if ($response === false) {
			if ($password_new === $password_confirm) {
				$ws = new libws('sso');

				$response = $ws->__sso_update($email, 'userpassword', $password_new);

				if (is_bool($response) && $response) {
					$sql = 'SELECT status_alias, status_id
						FROM users_status
						ORDER BY status_id';
					$user_status = (object) sql_rowset($sql, 'status_alias', 'status_id');

					$sql = 'UPDATE users SET block = ?, try_count = ?, token_block = ?, user_status = ?
						WHERE user_id = ?';
					sql_query(sql_filter($sql, 0, 0, '', $user_status->A, $result['user_id']));
				}
			} else {
				$response = self::lang('PASSWORDS_NOT_CONFIRM');
			}
		}

		return xml($response);
	}

	/**
	Initialize user email change process
	@return:	(xml) token
	*/
	public function user_change() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		if (empty($email_old) || empty($email_new)) {
			$response = self::lang('EMAIL_REQUIRED');
		}

		if ($response === false) {
			$sql = 'SELECT *
				FROM users
				WHERE email = ?';
			if (!$result = sql_fieldrow(sql_filter($sql, $email_old))) {
				$response = self::lang('NO_USER_EXIST');
			}
		}

		if ($response === false) {
			$sql = 'SELECT *
				FROM users
				WHERE email = ?';
			if ($result_new = sql_fieldrow(sql_filter($sql, $email_new))) {
				$response = self::lang('USER_EXISTS');
			}
		}

		if ($response === false) {
			/**
			Generate activation token.
			*/
			$activation_token = token(20);
			$activation = encode($email_old . ':' . $activation_token);

			$field_update = $email_new . ':' . $activation;

			$sql = 'UPDATE users SET email_change = ?
				WHERE user_id = ?';
			sql_query(sql_filter($sql, $field_update, $result['user_id']));

			$response = array(
				'token' => $activation
			);
		}

		return xml($response);
	}

	/**
	Confirm user email change
	@return:	(xml)
	*/
	public function user_change_confirm() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;

		if (empty($token)) {
			return xml(self::lang('TOKEN_INVALID'));
		}
		
		$token_part = explode(':', decode($token));
		$email = array_key($token_part, 0);
		$token_confirm = array_key($token_part, 1);

		$sql = 'SELECT status_alias, status_id
			FROM users_status
			ORDER BY status_id';
		$user_status = (object) sql_rowset($sql, 'status_alias', 'status_id');

		$sql = 'SELECT user_id, token_block, email_change, user_status
			FROM users
			WHERE email = ?';
		if (!$result = sql_fieldrow(sql_filter($sql, $email))) {
			$response = self::lang('TOKEN_INVALID');
		}

		if ($response === false) {
			$store_part = explode(':', $result['email_change']);
			$email_new = $store_part[0];

			if ($token !== $store_part[1]) {
				$response = self::lang('TOKEN_INVALID');
			}
		}

		if ($response === false) {
			$sql = 'SELECT *
				FROM users
				WHERE email = ?';
			if ($result_new = sql_fieldrow(sql_filter($sql, $email_new))) {
				$response = self::lang('USER_EXISTS');
			}
			unset($result_new);
		}

		if ($response === false) {
			$ws = new libws('sso');

			$sso_field = $ws->__sso_update($email, 'mail', $email_new);

			if (is_bool($sso_field) && $sso_field) {
				$sso_field = $ws->__sso_update($email, 'user_id', $email_new);
			}

			if (is_bool($sso_field) && $sso_field) {
				$sql = 'UPDATE users SET email = ?, email_change = ?
					WHERE user_id = ?';
				sql_query(sql_filter($sql, $email_new, '', $result['user_id']));

				$sql_insert = array(
					'change_uid' => $result['user_id'],
					'change_old' => $email,
					'change_new' => $email_new,
					'change_time' => time(),
					'change_ip' => ''
				);
				$sql = sql_build('INSERT', $sql_insert, 'users_change');
				$result_change = sql_affected($sql);

				$response = true;
			}
		}

		return xml($response);
	}

	/**
	Search for a user criteria.
	@return:	(xml) user properties and values
	*/
	public function user_search() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sso');
		$response = $ws->__sso_search($criteria);

		return xml($response);
	}

	/**
	Method for checking if number is Claro.
	@return:	(xml)
	*/
	public function user_claro() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}
		$ws = new libws('sms');
 		$response = $ws->__claro_is($country, $phone);

 		return xml($response);
	}

	/**
	Method for sending a message as SMS to Claro numbers.
	@return:	(xml)
	*/
	public function user_sms() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sms'); 
		$response = $ws->__claro_sms($country, $phone, $message);

		return xml($response);
	}

	/**
	Method to get all information about a Claro phone.
	@return 	(xml)
	*/
	public function user_phone() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sms:ws');

		$response = $ws->obtieneInfoCuenta(array(
			'codigoPais' => $country,
			'telefono' => $phone)
		);

		$response = json_decode(json_encode($response), true);

		if (isset($response['obtieneInfoCuentaResult'])) {
			$response = $response['obtieneInfoCuentaResult'];
		}

		return xml($response);
	}

	/**
	*/
	public function user_phone_package_list() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sms:ws');

		$response = $ws->obtienePaquetesOfertados(array(
			'codigoPais' => $country,
			'telefono' => $phone)
		);

		$response = json_decode(json_encode($response), true);

		if (isset($response['obtienePaquetesOfertadosResult']['Paquete'])) {
			$response = $response['obtienePaquetesOfertadosResult']['Paquete'];
		}

		return xml($response);
	}

	/**
	*/
	public function user_phone_package_activate() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sms:ws');

		$response = $ws->activaPaquete(array(
			'codigoPais' => $country,
			'telefono' => $phone,
			'codigoPaquete' => $package)
		);

		$response = json_decode(json_encode($response), true);

		if (isset($response['activaPaqueteResult'])) {
			$response = $response['activaPaqueteResult'];
		}

		return xml($response);
	}

	/**
	*/
	public function user_phone_voucher_activate() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sms:ws');

		$response = $ws->recargaVoucher(array(
			'codigoPais' => $country,
			'telefono' => $phone,
			'voucher' => $voucher
		));

		$response = json_decode(json_encode($response), true);

		if (isset($response['recargaVoucherResult'])) {
			$response = $response['recargaVoucherResult'];
		}

		return xml($response);
	}

	/**
	Associate a phone number to an account, with external validation
	@return:	(xml) 1 or error message
	*/
	public function user_number_create_direct() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$response = false;
		$user_id = 0;
		$app_id  = self::$config->app;

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?';		
		if (!$user_id = sql_field(sql_filter($sql, $email), 'user_id', 0)) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($field_id = self::find_value('phone', $phone, $user_id, 0)) {
			$response = self::lang('PHONE_CONFIRMED');
		}

		if (($response === false && self::find_duplicate('phone', $phone, $user_id, 1))) {
			$response = self::lang('PHONE_CONFIRMED');
		}

		if ($response === false) {
			$result = self::manage_field('phone', $phone, $app_id, $user_id);

			$field_id = self::find_value('phone', $phone, $user_id, 0);
			$value_id = self::getValueid($field_id[0]['field_id'], $user_id, $phone);

			$result = self::manage_property($value_id, 'confirmation', 1, $app_id, $user_id, null, 1);
							
			if ($result) {
				$result = self::manage_property($value_id, 'status', 1, $app_id, $user_id);

				$result_message = (!$result) ? 'NO_PHONE_CONFIRM' : 'PHONE_CONFIRM';
				$response = self::lang($result_message);
			}
		}

		return xml($response);
	}

	/**
	Associate a phone number to an account.
	@return:	(xml) 1 or error message
	*/
	public function user_number_create() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$ws = new libws('sms');

		$response = false;
		$user_id = 0;
		$verification_code = random_number();
		$app_id  = self::$config->app;

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?';		
		if (!$result_users = sql_field(sql_filter($sql, $email), 'user_id', 0)) {
			$response = self::lang('NO_USER_EXIST');
		}

		if ($response === false) {
			if ($result_phone = self::find_value('phone', $phone, $result_users, 1)) {
				$status_id = self::getAttrid('status');
				$confirm_id = self::getAttrid('confirmation');

				foreach ($result_phone as $row) {
					if (($row['data_attr'] != $status_id) || ($row['data_content'] != 0)) {
						continue;
					}

					if ($ws->__claro_sms($country, $phone, 'Codigo de verificacion: ' . $verification_code)) {
						$response = self::manage_field('phone', $phone, $app_id, $result_users);

						if (count($response) == 0) {
							$field_id = self::find_value('phone', $phone, $result_users, 1, $confirm_id);	

							$value_id = self::getValueid($field_id[0]['field_id'], $result_users, $phone);
							$response = self::manage_property($value_id, 'confirmation', $field_id[0]['data_content'], $app_id, $result_users, $verification_code, 1);

							$result_message = (!$response) ? 'NO_UPDATE_VERIFICATION' : 'UPDATE_VERIFICATION';
							$response = self::lang($result_message);
						}
					} else {
						$response = self::lang('NO_SEND_VERIFICATION');
					}
				}
			} else {
				$response_is = $ws->__claro_is($country, $phone, true);

				if ($response_is == 'POST' && $response = self::find_duplicate('phone', $phone, $result_users, 1)) {
					$response = self::lang('PHONE_CONFIRMED');
				}

				if ($response === false) {
					switch ($response_is) {
						case 'POST':
						case 'PREP':
						case 'HIBR':
						case 'ESPE':
							if ($ws->__claro_sms($country, $phone, 'Codigo de verificacion: ' . $verification_code)) {						
								$response = self::manage_field('phone', $phone, $app_id, $result_users);
									
								if (count($response) == 0) {
									$field_id = self::find_value('phone', $phone, $result_users, 0);
										
									$value_id = self::getValueid($field_id[0]['field_id'], $result_users, $phone);
									$response = self::manage_property($value_id, 'confirmation', $verification_code, $app_id, $result_users, null, 1);
										
									if (!$response) {
										$response = self::lang('NO_UPDATE_VERIFICATION');	
									} else {
										$response = self::manage_property($value_id, 'status', 0, $app_id, $result_users);

										$result_message = (!$response) ? 'NO_UPDATE_VERIFICATION' : 'UPDATE_VERIFICATION';
										$response = self::lang($result_message);
									}
								}
							} else {
								$response = self::lang('NO_SEND_VERIFICATION');
							}
							break;
						default:
							$response = self::lang('NO_CLARO_NUMBER');
							break;
					}
				}
			}
		}

		return xml($response);
	}

	/**
	Add a phone number to an account, with SMS confirmation code.
	@return:	(xml) 1 or error message
	*/
	public function user_number_confirm() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if (!is_numeric($email)) {
			if ($a = self::__app_authenticate($config, __FUNCTION__)) {
				return xml($a);
			}

			$sql = 'SELECT user_id
				FROM users
				WHERE email = ?';
			$email = sql_field(sql_filter($sql, $email), 'user_id', 0);
		}

		$result = array();
		$user_id = 0;
		$app_id  = self::$config->app;

		if ($email > 0) {
			$resul_phone = self::find_value('phone', $phone, $email, 1);
			
			if ($resul_phone) {
				$status_id = self::getAttrid('status');
				$confirm_id = self::getAttrid('confirmation');

				for ($i = 0, $end = count($resul_phone); $i < $end; $i++) {
					if ($resul_phone[$i]['data_attr'] == $status_id) {
						if ($resul_phone[$i]['data_content'] == 0) {
							$field_id = self::find_value('phone', $phone, $email, 1, $confirm_id);

							if ($field_id[0]['data_content'] > 1) {
								if ($verification == $field_id[0]['data_content']) {
									$value_id = self::getValueid($field_id[0]['field_id'], $email, $phone);
									$result = self::manage_property($value_id, 'confirmation', $field_id[0]['data_content'], $app_id, $email, 1, 1);

									if (!$result) {
										$result = self::lang('NO_PHONE_CONFIRM');
									} else {
										$result = self::manage_property($value_id, 'status', 0, $app_id, $email, 1, 1);

										$result_message = (!$result) ? 'NO_PHONE_CONFIRM' : 'PHONE_CONFIRM';
										$result = self::lang($result_message);
									}
								} else {
									$result = self::lang('NO_CORRECT_VERIFICATION');
								}
							} else {
								$result = self::lang('NO_CORRECT_VERIFICATION');
							}
						} else {
							$result = self::lang('PHONE_CONFIRMED');
						}
					}
				}
			} else {
				$result = self::lang('NO_PHONE_SET');
			}
		} else {
			$result = self::lang('NO_USER_EXIST');
		}	

		return xml($result);
	}	

	/**
	Remove a phone number from account.
	@return:	(xml) 1 or error message
	*/
	public function user_number_delete() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = array();
		$user_id = 0;
		$app_id  = self::$config->app;

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?';
		if ($result_users = sql_field(sql_filter($sql, $email), 'user_id', 0)) {
			if ($resul_phone = self::find_value('phone', $phone, $result_users, 1)) {
				$status_id = self:: getAttrid('status');

				for ($i = 0, $end = count($resul_phone); $i < $end; $i++) {
					if ($resul_phone[$i]['data_attr'] == $status_id) {
						if ($resul_phone[$i]['data_content'] == 1) {
							$field_id = self::find_value('phone', $phone, $result_users, 0);
							$value_id = self::getValueid($field_id[0]['field_id'], $result_users, $phone);
							$result = self::manage_property($value_id, 'status', 1, $app_id, $result_users, 0);

							$result_message = (!$result) ? 'NO_PHONE_REMOVE' : 'PHONE_REMOVE';
							$result = self::lang($result_message);
						} else {
							 $result = self::lang('NO_PHONE_SET');
						}
					}
				}
			} else {
				$result = self::lang('NO_PHONE_SET');
			}
		} else {
			$result = self::lang('NO_USER_EXIST');
		}

		return xml($result);
	}

	/**
	Get list of phone numbers assigned to an account.
	@return:	(xml) number list or error message
	*/
	public function user_number_list() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = array();
		$user_id = 0;
		$j = 0;

		$sql = 'SELECT user_id
			FROM users
			WHERE email = ?';
		if ($result_users = sql_field(sql_filter($sql, $email), 'user_id', 0)) {
			$attributes = self::getAttributes($result_users);

			if ($result_phone = self::find_value('phone', '', $result_users)) {
				$status_id = self::getAttrid('status');

				foreach ($result_phone as $phone) {
					$field_id = self::find_value('phone', $phone['value_data'], $result_users, 1, $status_id);

					if (!isset($field_id[0]['data_content']) || $field_id[0]['data_content'] == 1) {
						$result[$j] = array(
							'number' => $phone['value_data']
						);

						if (isset($attributes[$phone['value_data']])) {
							$k = 0;

							foreach ($attributes[$phone['value_data']] as $row) {
								$result[$j]['attributes_' . $k] = array(
									'name' => $row['attr_name'],
									'value' => $row['data_content']
								);

								$k++;
							}
						} else {
							$result[$j]['attributes'] = w();
						}

						$j++;
					}
				}
			} else {
				$result = self::lang('NO_PHONE_RESULT');				
			}
		} else {
			$result = self::lang('NO_USER_EXIST');
		}

		return xml($result, 'phones');
	}

	/**
	Update fields and it's properties for an account, receiving a XML string.
	@return:	(xml)
	*/
	public function user_number_attributes() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = false;
		$app_id  = self::$config->app;
		$user_id = self::getUserid($email);

		if ($app_id < 1) {
			$result = self::lang('NOT_APP_EXIST');
		}

		if ($result === false && $user_id < 1) {
			$result = self::lang('NO_USER_EXIST');
		}

		if ($result === false) {
			$data = xml2array($data);
			$field_name = 'phone';

			if (count($data) > 0 && isset($data['ATTRIBUTES']['ATTRIBUTE'])) {
				if (!isset($data['ATTRIBUTES']['ATTRIBUTE'][0])) {
					$data['ATTRIBUTES']['ATTRIBUTE'] = array($data['ATTRIBUTES']['ATTRIBUTE']);
				}

				foreach ($data['ATTRIBUTES']['ATTRIBUTE'] as $field) {
					$field_id = self::find_value($field_name, $field['VALUE'], $user_id, 0);

					if (!$field_id) {
						continue;
					}

					if (isset($field['PROPERTIES'])) {
						if (!isset($field['PROPERTIES']['PROPERTY'][0])) {
							$field['PROPERTIES']['PROPERTY'] = array($field['PROPERTIES']['PROPERTY']);
						}

						foreach ($field['PROPERTIES']['PROPERTY'] as $property) {
							if (!isset($property['NAME']) || !isset($property['VALUE'])) {
								continue;
							}

							$property_valuenew = (isset($property['VALUENEW'])) ? $property['VALUENEW'] : null;

							$field_id = self::find_value($field_name, $field['VALUE'], $user_id, 0);
							$value_id = self::getValueid($field_id[0]['field_id'], $user_id, $field['VALUE']);
							
							if (self::manage_property($value_id, $property['NAME'], $property['VALUE'], $app_id, $user_id, $property_valuenew)) {
								$result = self::lang('DATA_SAVE');
							}
						}
					}
				}
			}
		}

		return xml($result);
	}

	/**
	Block an account by an administrator.
	@return:	(xml) 1 or error message
	*/
	public function user_block_admin() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = array();
		$user_id = 0;

		$sql = 'SELECT user_id, banned_admin
			FROM users
			WHERE email = ?';
		if ($result_users = sql_rowset(sql_filter($sql, $email))) {
			if ($result_users[0]['banned_admin'] == 0) {
				$sql = 'UPDATE users SET banned_admin = ?, block = ?, blocked_time = now(), user_status = ?
					WHERE email = ?';
				$result_update = sql_affected(sql_filter($sql, 1, 1, 3, $email));

				$result_message = (!$result_update) ? 'USER_BAN_FAIL' : 'USER_IS_BAN';
				$result = self::lang($result_message);
			} else {
				$result = self::lang('USER_IS_BANNED');
			}
		} else {
			$result = self::lang('NO_USER_EXIST');
		}

		return xml($result);
	}

	/**
	Unblock an account by an administrator.
	@return:	(xml) 1 or error message
	*/
	public function user_unblock_admin() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = array();
		$user_id = 0;

		$sql = 'SELECT user_id, banned_admin
			FROM users
			WHERE email = ?';
		if ($result_users = sql_rowset(sql_filter($sql, $email))) {
			if ($result_users[0]['banned_admin'] == 1) {
				$sql = 'UPDATE users SET banned_admin = ?, block = ?, blocked_time = ? , user_status = ?,  banned_admin = ?	
					WHERE email = ?';
				$result_update = sql_affected(sql_filter($sql, 0, 0, 0, 1, 0, $email));

				$result_message = (!$result_update) ? 'USER_UNBAN_FAIL' : 'USER_IS_UNBANED';
				$result = self::lang($result_message);
			} else {
				$result = self::lang('USER_NOT_UNBANNED');
			}
		} else {
			$result = self::lang('NO_USER_EXIST');
		}

		return xml($result);
	}

	/**
	Unblock an account with email verification token.
	@return:	(xml) 1 or error message
	*/
	public function user_unblock() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = false;
		$user_id = 0;

		$sql = 'SELECT user_id, block, token_block
			FROM users
			WHERE email = ?';
		if ($result_users = sql_rowset(sql_filter($sql, $email))) {
			if ($result_users[0]['block'] == 1) {
				if ($result_users[0]['token_block'] == $token){
					$sql = 'UPDATE users SET block = ?, token_block = ?, try_count = ?, blocked_time = ?, user_status = ?, banned_admin = ?
						WHERE email = ?';
					$result_update = sql_affected(sql_filter($sql, 0, '', 0, 0, 1, 0, $email));

					$result_message = (!$result_update) ? 'USER_UNBAN_FAIL' : 'USER_IS_UNBANED';
					$result = self::lang($result_message);
				} else {
					$result = self::lang('INVALID_TOKEN');
				}
			} else {
				$result = self::lang('USER_NOT_UNBANNED');
			}
		} else {
			$result = self::lang('NO_USER_EXIST');
		}

		return xml($result);
	}

	/**
	Generate a new unblock token for sending email one more time.
	@return:	(xml) email token or error message
	*/
	public function user_get_token() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = false;
		$user_id = 0;
		
		$sql = 'SELECT user_id, block, token_block 
			FROM users
			WHERE email = ?';
		if ($result_users = sql_fieldrow(sql_filter($sql, $email))) {
			if ($result_users['block'] == 1) {
				$verification_code = token();

				$sql = 'UPDATE users SET token_block = ? 
					WHERE email = ?';
				$result_update = sql_affected(sql_filter($sql, $verification_code, $email));

				if (!$result_update) {
					$result = self::lang('NO_NEW_TOKEN');
				} else {
					$result['token'] = $verification_code;
				}
			} else {
				$result = self::lang('USER_NOT_UNBANNED');
			}
		} else {
			$result = self::lang('NO_USER_EXIST');
		}

		return xml($result);	
	}

	/**
	Get all attributes for an account, if application is the first time calling, returns all data and if not, returns data created by this app; if field is PHONE returns only when status = 1
	@return:	(xml) account data or error message
	*/
	public function user_data_list() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}
				
		$result = array();
		$app_id  = self::$config->app;
		$user_id = self::getUserid($email);

		if ($app_id > 0) {
			if ($user_id > 0) {
				$status_id = self::getAttrid('status');

				$sql = 'SELECT ef.field_name, ev.value_data
					FROM extra_fields ef, extra_apps ap, extra_values ev
					WHERE ef.field_id = ap.apps_fields
						AND ef.field_id = ev.value_field
						AND ap.apps_app = ?
						AND ev.value_user_id = ?';
				if ($result_fields = sql_rowset(sql_filter($sql, $app_id, $user_id))) {
					for ($i = 0, $end = count($result_fields); $i < $end; $i++) {
						$field_id = self::find_value($result_fields[$i]['field_name'], $result_fields[$i]['value_data'], $user_id, 1, $status_id);

						if (!isset($field_id[0]['data_content']) || $field_id[0]['data_content'] == 1) {		
							$result[] = array('NAME' => $result_fields[$i]['field_name'], 'VALUE' => $result_fields[$i]['value_data']); 	
						}
					}
				} else {
					$sql = 'SELECT ef.field_name, ev.value_data
						FROM extra_fields ef, extra_values ev
						WHERE ef.field_id = ev.value_field
							AND ev.value_user_id = ?';
					$result_fields = sql_rowset(sql_filter($sql, $user_id));

					for ($i = 0, $end = count($result_fields); $i < $end; $i++) {
						$field_id = self::find_value($result_fields[$i]['field_name'], $result_fields[$i]['value_data'], $user_id, 1, $status_id);

						if (!isset($field_id[0]['data_content']) || $field_id[0]['data_content'] == 1) {		
							$result[] = array('NAME' => $result_fields[$i]['field_name'], 'VALUE' => $result_fields[$i]['value_data']); 	
						}
					}
				}

				$result = (count($result)) ? $result : self::lang('NO_FIELDS_EXIST');
			} else {
				$result = self::lang('NO_USER_EXIST');
			}
		} else {
			$result = self::lang('NOT_APP_EXIST');
		}
		
		return xml($result, 'FIELD');	
	}		

	/**
	Update fields and it's properties for an account, receiving a XML string.
	@return:	(xml)
	*/
	public function user_data() {
		$_e = self::__combine(__FUNCTION__, func_get_args());
		extract($_e);

		if ($a = self::__app_authenticate($config, __FUNCTION__)) {
			return xml($a);
		}

		$result = array();
		$app_id  = self::$config->app;
		$user_id = self::getUserid($email);

		if ($app_id < 1) {
			$result = self::lang('NOT_APP_EXIST');
		}

		if ($user_id < 1) {
			$result = self::lang('NO_USER_EXIST');
		}

		if (!count($result)) {
			$data = xml2array($data);

			if (count($data) > 0 && isset($data['FIELDS']['FIELD'])) {
				foreach ($data['FIELDS']['FIELD'] as $field) {
					$field_valuenew = null;
					if (isset($field['VALUENEW'])) {
						$field_valuenew = $field['VALUENEW'];
					}

					$result = self::manage_field($field['NAME'], $field['VALUE'], $app_id, $user_id, $field_valuenew);

					if (!isset($result['error_code'])) {
						if (isset($field['PROPERTIES'])) {
							foreach ($field['PROPERTIES'] as $property) {
								$property_valuenew = (isset($property['VALUENEW'])) ? $property['VALUENEW'] : null; 

								$field_id = self::find_value($field['NAME'], $field['VALUE'], $user_id, 0);
								$value_id = self::getValueid($field_id[0]['field_id'], $user_id, $field['VALUE']);
								
								if (self::manage_property($value_id, $property['NAME'], $property['VALUE'], $app_id, $user_id, $property_valuenew)) {
									$result = self::lang('DATA_SAVE');
								}
							}
						} else {
							$result = self::lang('DATA_SAVE');
						}	
					}
				}
			}
		}

		return xml($result);
	}

	/**
	Generate a lang string and code from the catalog in database.
	@return:	(xml) true or 
	*/
	private function lang($error_message = '') {
		$language = isset(self::$config->lang) ? self::$config->lang : 'es';

		$result = array(
			'error_code' => 0,
			'error_message' => $error_message
		);

		$sql = 'SELECT lv.value_id error_code, lv.value_data error_message, key_iserror error
			FROM lang_values lv, lang_keys lk, lang l
			WHERE lk.key_id = lv.value_key
				AND lv.value_lang = l.lang_id
				AND lk.key_name = ?
				AND l.lang_alias = ?';
		if ($lang = sql_fieldrow(sql_filter($sql, $error_message, $language))) {
			if ($lang['error'] == 1) {
				$result = array(
					'error_code' => $lang['error_code'],
					'error_message' => $lang['error_message']
				);
			} else {
				$result = true;
			}
		}

		return $result; 
	}

	/**
	Get an account id from the given email.
	@return:	(int) user_id
	*/
	private function getUserid($email) {
		if (!isset(self::$users[$email])) {
			$sql = 'SELECT user_id
				FROM users
				WHERE email = ?';
			self::$users[$email] = sql_field(sql_filter($sql, $email), 'user_id', 0);
		}
		
		return self::$users[$email];
	}

	/**
	Get property value id for value of field.
	@return:	(int)
	*/
	private function getAttribute($data_content = '', $data_attr = 0, $data_value_id = 0) {
		$sql = 'SELECT data_id
			FROM extra_attr_data
			WHERE data_content = ?
				AND data_attr = ?
				AND data_value_id = ?';
		return 	sql_field(sql_filter($sql, $data_content, $data_attr, $data_value_id), 'data_id', 0);
	}

	/**
	Get all attributes for a value, except confirmation and status.
	@return:	(array)
	*/
	private function getAttributes($user_id) {
		$sql = 'SELECT ev.value_data, ea.attr_name, ead.data_content
			FROM extra_values ev, extra_attr ea, extra_attr_data ead, extra_fields ef
			WHERE ev.value_user_id = ?
				AND ea.attr_name NOT IN (?, ?)
				AND ev.value_field = ef.field_id
				AND ea.attr_id = ead.data_attr
				AND ead.data_value_id = ev.value_id
			ORDER BY ea.attr_name, ead.data_content';
		return 	sql_rowset(sql_filter($sql, $user_id, 'status', 'confirmation'), 'value_data', false, true);
	}

	/**
	Get id of property of given name.
	@return:	(int)
	*/
	private function getAttrid($attr_name = '') {
		$sql = 'SELECT attr_id
			FROM extra_attr
			WHERE attr_name = ?';
		return sql_field(sql_filter($sql, $attr_name), 'attr_id', 0);
	}

	/**
	Get value id of a field.
	@return:	(int)
	*/
	private function getValueid($field_id = 0, $user_id = 0, $field_value = '') {
		$sql = 'SELECT value_id
			FROM extra_values
			WHERE value_field = ?
				AND value_user_id = ?
				AND value_data = ?';
		return sql_field(sql_filter($sql, $field_id, $user_id, $field_value), 'value_id', null);
	}
	
	/**
	Create fields, Insert, update value for a field and create relations between applications and fields.
	@return:	(string) errors
	*/
	private function manage_field($field_name = '', $field_value = '', $app_id = 0, $user_id = 0, $field_valuenew = null) {
		$result = array();

		$sql = 'SELECT *
			FROM extra_fields
			WHERE field_name = ?';
		if ($result_field = sql_fieldrow(sql_filter($sql, $field_name))) {
			$sql = 'SELECT apps_fields
				FROM extra_apps
				WHERE apps_fields = ?
					AND apps_app = ?';
			if (!$result_field_app = sql_field(sql_filter($sql, $result_field['field_id'], $app_id), 'apps_fields', 0)) {
				$sql_insert = array(
					'apps_fields' => $result_field['field_id'],
					'apps_app' => $app_id
				);
				$sql = sql_build('INSERT', $sql_insert, 'extra_apps');
				$result_insert = sql_affected($sql);
			}

			if (is_array($field_value) && !$field_value && isset($field_valuenew) && $field_valuenew) {
				$field_value = $field_valuenew;
			}

			if (is_array($field_value)) {
				return $field_value;
			}

			$sql = 'SELECT value_data
				FROM extra_values
				WHERE value_field = ?
					AND value_user_id = ?
					AND value_data = ?';
			if (!$result_field_value = sql_field(sql_filter($sql, $result_field['field_id'], $user_id, $field_value), 'value_data', false)) {
				$sql_insert = array(
					'value_field' => $result_field['field_id'],
					'value_user_id' => $user_id,
					'value_data' => $field_value
				);
				$sql = sql_build('INSERT', $sql_insert, 'extra_values');
				if (!sql_affected($sql)) {
					return self::lang('NOT_FIELD_SAVE');
				}

				$result = array(1);
			} else if (isset($field_valuenew)) {
				$sql =' UPDATE extra_values SET value_data = ?
					WHERE value_user_id = ?
						AND value_field = ?
						AND value_data = ?';
				if (!sql_affected(sql_filter($sql, $field_valuenew, $user_id, $result_field['field_id'], $field_value))) {
					//return self::lang('NOT_FIELD_SAVE');
				}
			}
		} else {
			$sql_insert = array(
				'field_name' => $field_name
			);
			$sql = sql_build('INSERT', $sql_insert, 'extra_fields');
			if (!sql_affected($sql)) {
				return self::lang('NOT_FIELD_SAVE');
			}

			$sql = 'SELECT field_id
				FROM extra_fields
				WHERE field_name = ?';
			if (!$result_field = sql_field(sql_filter($sql, $field_name), 'field_id', 0)) {
				return self::lang('NOT_FIELD_EXIST');
			}

			$sql_insert = array(
				'apps_fields' => $result_field,
				'apps_app' => $app_id
			);
			$sql = sql_build('INSERT', $sql_insert, 'extra_apps');
			if (!sql_affected($sql)) {
				return self::lang('NOT_FIELD_SAVE');
			}

			if (is_array($field_value) && !$field_value && isset($field_valuenew) && $field_valuenew) {
				$field_value = $field_valuenew;
			}

			$sql_insert = array(
				'value_field' => $result_field,
				'value_user_id' => $user_id,
				'value_data' => $field_value
			);
			$sql = sql_build('INSERT', $sql_insert, 'extra_values');
			if (!sql_affected($sql)) {
				return self::lang('NOT_FIELD_SAVE');
			}

			$result = array(1);
		}

		return $result;
	}	
	
	/**
	Get id of a value related to field.
	@return:	(int)
	*/
	private function getDataid($field_id = 0, $user_id = 0, $field_value = '') {
		$sql = 'SELECT value_id
			FROM extra_values
			WHERE value_field = ?
				AND value_user_id = ?
				AND value_data = ?';
		return sql_field(sql_filter($sql, $field_id, $user_id, $field_value), 'value_id', null);
	}
	
	/**
	Create an attribute name and inserts and updates values for an attribute.
	@return:	(string)
	*/
	private function manage_property($value_id = 0, $property_name = '', $property_value = '', $app_id = 0, $user_id = 0, $property_valuenew = null, $confirmation = 0) {
		$result = false;

		$sql = 'SELECT *
			FROM extra_attr
			WHERE attr_name = ?';
		if ($result_property = sql_fieldrow(sql_filter($sql, $property_name))) {
			$attr = self::getAttribute($property_value, $result_property['attr_id'], $value_id);

			if ($property_name == 'confirmation' && $confirmation == 0) {
				$field_value = self::field_now_value($value_id);
				$result = self::user_number_confirm(null, $user_id, $field_value, $property_value);
			} else if ($attr == 0) {
				$sql_insert = array(
					'data_content' => $property_value,
					'data_attr' => 	$result_property['attr_id'],
					'data_value_id' => $value_id
				);
				$sql = sql_build('INSERT', $sql_insert, 'extra_attr_data');

				if (sql_affected($sql)) {
					$result = true;
				}
			} else if (isset($property_valuenew)) {
				$sql =' UPDATE extra_attr_data SET data_content = ?
					WHERE data_content = ?
						AND data_attr = ?
						AND data_value_id = ?';
				if (sql_affected(sql_filter($sql, $property_valuenew, $property_value, $result_property['attr_id'], $value_id))) {
					$result = true;
				}
			}
		} else {
			$sql_insert = array(
				'attr_name' => $property_name				
			);
			$sql = sql_build('INSERT', $sql_insert, 'extra_attr');
			if (sql_affected($sql)) {
				$sql = 'SELECT attr_id
					FROM extra_attr
					WHERE attr_name = ?';
				$result_field_app = sql_field(sql_filter($sql, $property_name), 'attr_id', 0);

				$sql_insert = array(
					'data_content' => $property_value,
					'data_attr' => 	$result_field_app,
					'data_value_id' => $value_id			
				);
				$sql = sql_build('INSERT', $sql_insert, 'extra_attr_data');
				if (sql_affected($sql)) {
					$result = true;
				}
			}
		}

		return $result;		
	}

	/**
	Get value data of an value id
	@return:	(xml)
	*/
	private function field_now_value($value_id = 0) {
		$sql = 'SELECT value_data
			FROM extra_values
			WHERE value_id = ?';
		return sql_field(sql_filter($sql, $value_id), 'value_data', null);
	}

	/**
	Get the value data of field, and it's related attributes
	@return:	(array) result list
	*/
	private function find_value($field_name = '', $field_value = '', $user_id = 0, $property = 0, $property_attr = 0) {
		$sql = 'SELECT ef.field_id, ev.value_data ' . ($property == 1 ? ', ead.data_id, ead.data_content, ead.data_attr' : '') . '
			FROM extra_fields ef, extra_values ev ' . ($property == 1 ? ' LEFT JOIN extra_attr_data ead ON ev.value_id = ead.data_value_id ' . ($property_attr > 0 ? ' AND ead.data_attr = ' . $property_attr : '') : '') . '
			WHERE ef.field_id = ev.value_field
			AND ef.field_name = ?
			' . (!empty($field_value) ? ' AND ev.value_data = ? ' : '') . ' 
			AND ev.value_user_id = ?';
		$response = (!empty($field_value)) ? sql_filter($sql, $field_name, $field_value, $user_id) : sql_filter($sql, $field_name, $user_id);

		return sql_rowset($response);
	}

	/**
	Find duplicate values in another users.
	@return:	(bool)
	*/
	private function find_duplicate($field_name = '', $field_value = '', $user_id = 0, $property = 0, $property_attr = 0) {
		$sql = 'SELECT ef.field_id, ev.value_data ' . ($property == 1 ? ', ead.data_id, ead.data_content, ead.data_attr' : '') . '
			FROM extra_fields ef, extra_values ev ' . ($property == 1 ? ' LEFT JOIN extra_attr_data ead ON ev.value_id = ead.data_value_id ' . ($property_attr > 0 ? ' AND ead.data_attr = ' . $property_attr : '') : '') . '
			WHERE ef.field_id = ev.value_field
			AND ef.field_name = ?
			' . (!empty($field_value) ? ' AND ev.value_data = ? ' : '') . ' 
			AND ev.value_user_id <> ?';
		$response = (!empty($field_value)) ? sql_filter($sql, $field_name, $field_value, $user_id) : sql_filter($sql, $field_name, $user_id);

		return sql_rowset($response);
	}
}