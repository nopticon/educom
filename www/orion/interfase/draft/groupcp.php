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
define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();

$script_name = ( $script_name != '' ) ? $script_name . '/groupcp.php' : 'groupcp.php';
$server_name = trim($config['server_name']);
$server_protocol = ( $config['cookie_secure'] ) ? 'https://' : 'http://';

$server_url = $server_protocol . $server_name . $script_name;

if ( isset($_GET[POST_GROUPS_URL]) || isset($_POST[POST_GROUPS_URL]) )
{
	$group_id = ( isset($_POST[POST_GROUPS_URL]) ) ? intval($_POST[POST_GROUPS_URL]) : intval($_GET[POST_GROUPS_URL]);
}
else
{
	$group_id = '';
}

if ( isset($_POST['mode']) || isset($_GET['mode']) )
{
	$mode = ( isset($_POST['mode']) ) ? $_POST['mode'] : $_GET['mode'];
	$mode = htmlspecialchars($mode);
}
else
{
	$mode = '';
}

$confirm = ( isset($_POST['confirm']) ) ? true : 0;
$cancel = ( isset($_POST['cancel']) ) ? true : 0;

$start = ( isset($_GET['start']) ) ? intval($_GET['start']) : 0;

//
// Default var values
//
$is_moderator = false;

if ( isset($_POST['groupstatus']) && $group_id )
{
	if ( !$userdata['session_logged_in'] )
	{
		do_login();
	}

	$result = $db->sql_query("SELECT group_moderator FROM _groups  WHERE group_id = $group_id");

	$row = $db->sql_fetchrow($result);

	if ( $row['group_moderator'] != $userdata['user_id'] && $userdata['user_level'] != USER_ADMIN )
	{
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . s_link('cover') . '">')
		);

		$message = $lang['Not_group_moderator'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?g=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');

		trigger_error($message);
	}

	$result = $db->sql_query("UPDATE _groups SET group_type = " . intval($_POST['group_type']) . " WHERE group_id = $group_id");

	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="3;url=' . "groupcp.php?g=$group_id" . '">')
	);

	$message = $lang['Group_type_updated'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?g=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');

	trigger_error($message);

}
else if ( isset($_POST['joingroup']) && $group_id )
{
	//
	// First, joining a group
	// If the user isn't logged in redirect them to login
	//
	if ( !$userdata['session_logged_in'] )
	{
		do_login();
	}

	$sql = "SELECT ug.user_id, g.group_type, group_count, group_count_max
		FROM _members_group ug, _groups g 
		WHERE g.group_id = $group_id 
			AND ( g.group_type <> " . GROUP_HIDDEN . " OR (g.group_count <= '".$userdata['user_posts']."' AND g.group_count_max > '".$userdata['user_posts']."')) 
			AND ug.group_id = g.group_id";
	$result = $db->sql_query($sql);
	unset($sql);

	if (	$row = $db->sql_fetchrow($result) )
	{
		$is_autogroup_enable = ($row['group_count'] <= $userdata['user_posts'] && $row['group_count_max'] > $userdata['user_posts']) ? true : false;
		
		if ( $row['group_type'] == GROUP_OPEN || $is_autogroup_enable)
		{
			do
			{
				if ( $userdata['user_id'] == $row['user_id'] )
				{
					$template->assign_vars(array(
						'META' => '<meta http-equiv="refresh" content="3;url=' . s_link('cover') . '">')
					);

					$message = $lang['Already_member_group'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?g=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');

					trigger_error($message);
				}
			} while ( $row = $db->sql_fetchrow($result) );
		}
		else
		{
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . s_link('cover') . '">')
			);

			$message = $lang['This_closed_group'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?g=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');

			trigger_error($message);
		}
	}
	else
	{
		trigger_error('No_groups_exist');
	}

	$result = $db->sql_query("INSERT INTO _members_group (group_id, user_id, user_pending) VALUES ($group_id, " . $userdata['user_id'] . ",'".(($is_autogroup_enable)? 0 : 1)."')");

	$result = $db->sql_query("SELECT u.user_email, u.username, u.user_lang, g.group_name FROM _members u, _groups g WHERE u.user_id = g.group_moderator AND g.group_id = '$group_id'");

	$moderator = $db->sql_fetchrow($result);
	
	if (!$is_autogroup_enable)
	{
		$emailer = new emailer();
		$emailer->from($config['board_email']);
		$emailer->replyto($config['board_email']);
		
		$emailer->use_template('group_request', $moderator['user_lang']);
		$emailer->email_address($moderator['user_email']);
		$emailer->set_subject($lang['Group_request']);
		
		$emailer->assign_vars(array(
			'SITENAME' => $config['sitename'], 
			'GROUP_MODERATOR' => $moderator['username'],
			
			'U_GROUPCP' => $server_url . '?' . POST_GROUPS_URL . "=$group_id&validate=true"
		));
		
		$emailer->send();
		$emailer->reset();}
		
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="3;url=' . s_link('cover') . '">'
		));
		
		$message = ($is_autogroup_enable) ? $lang['Group_added'] : $lang['Group_joined'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?g=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');
		
		trigger_error($message);
	}
	else if ( isset($_POST['unsub']) || isset($_POST['unsubpending']) && $group_id )
	{
		//
		// Second, unsubscribing from a group
		// Check for confirmation of unsub.
		//
		if ( $cancel )
		{
			redirect("groupcp.php");
		}
		elseif ( !$userdata['session_logged_in'] )
		{
			do_login();
		}
	
		if ( $confirm )
		{
			$result = $db->sql_query("DELETE FROM _members_group WHERE user_id = " . $userdata['user_id'] . " AND group_id = '$group_id'");
	
			if ( $userdata['user_level'] != USER_ADMIN && $userdata['user_level'] == USER_MOD )
			{
				$result = $db->sql_query("SELECT COUNT(auth_mod) AS is_auth_mod FROM _auth_access aa, _members_group ug WHERE ug.user_id = " . $userdata['user_id'] . " AND aa.group_id = ug.group_id AND aa.auth_mod = 1");
	
				if ( !($row = $db->sql_fetchrow($result)) || $row['is_auth_mod'] == 0 )
				{
					$result = $db->sql_query("UPDATE _members SET user_level = " . USER_NORMAL . " WHERE user_id = " . $userdata['user_id']);
				}
			}
	
			$template->assign_vars(array(
				'META' => '<meta http-equiv="refresh" content="3;url=' . s_link('cover') . '">')
			);
	
			$message = $lang['Unsub_success'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?g=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');
	
			trigger_error($message);
		}
		else
		{
			$unsub_msg = ( isset($_POST['unsub']) ) ? $lang['Confirm_unsub'] : $lang['Confirm_unsub_pending'];
	
			$s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" /><input type="hidden" name="unsub" value="1" />';
			
			$template_vars = array(
				'MESSAGE_TITLE' => $lang['Confirm'],
				'MESSAGE_TEXT' => $unsub_msg,
				'L_YES' => $lang['Yes'],
				'L_NO' => $lang['No'],
				'S_CONFIRM_ACTION' => "groupcp.php",
				'S_HIDDEN_FIELDS' => $s_hidden_fields
			);
			
			page_layout('Group_Control_Panel', 'confirm_body', $template_vars);
		}
}
else if ( $group_id )
{
	//
	// Did the group moderator get here through an email?
	// If so, check to see if they are logged in.
	//
	if ( isset($_GET['validate']) )
	{
		if ( !$userdata['session_logged_in'] )
		{
			do_login();
		}
	}

	$sql = "SELECT g.group_moderator, g.group_type, aa.auth_mod 
		FROM ( _groups g 
		LEFT JOIN _auth_access aa ON aa.group_id = g.group_id )
		WHERE g.group_id = $group_id";
	$result = $db->sql_query($sql);
	unset($sql);

	if ( $group_info = $db->sql_fetchrow($result) )
	{
		$group_moderator = $group_info['group_moderator'];
	
		if ( $group_moderator == $userdata['user_id'] || $userdata['user_level'] == USER_ADMIN )
		{
			$is_moderator = true;
		}
			
		//
		// Handle Additions, removals, approvals and denials
		//
		if ( !empty($_POST['add']) || !empty($_POST['remove']) || isset($_POST['approve']) || isset($_POST['deny']) )
		{
			if ( !$userdata['session_logged_in'] )
			{
				do_login();
			}

			if ( !$is_moderator )
			{
				$template->assign_vars(array(
					'META' => '<meta http-equiv="refresh" content="3;url=' . s_link('cover') . '">')
				);

				$message = $lang['Not_group_moderator'] . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');

				trigger_error($message);
			}

			if ( isset($_POST['add']) )
			{
				$username = ( isset($_POST['username']) ) ? get_username_base($_POST['username']) : '';
				
				$result = $db->sql_query("SELECT user_id, user_email, user_lang, user_level FROM _members WHERE username = '" . str_replace("\'", "''", $username) . "'");

				if ( !($row = $db->sql_fetchrow($result)) )
				{
					$template->assign_vars(array(
						'META' => '<meta http-equiv="refresh" content="3;url=' . "groupcp.php?g=$group_id" . '">')
					);

					$message = $lang['Could_not_add_user'] . "<br /><br />" . sprintf($lang['Click_return_group'], "<a href=\"" . "groupcp.php?g=$group_id" . "\">", "</a>") . "<br /><br />" . sprintf($lang['Click_return_index'], "<a href=\"" . s_link('cover') . "\">", "</a>");

					trigger_error($message);
				}

				if ( $row['user_id'] == GUEST )
				{
					$template->assign_vars(array(
						'META' => '<meta http-equiv="refresh" content="3;url=' . "groupcp.php?g=$group_id" . '">')
					);

					$message = $lang['Could_not_anon_user'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?g=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');

					trigger_error($message);
				}
				
				$result = $db->sql_query("SELECT ug.user_id, u.user_level FROM _members_group ug, _members u WHERE u.user_id = " . $row['user_id'] . " AND ug.user_id = u.user_id AND ug.group_id = '$group_id'");

				if ( !($db->sql_fetchrow($result)) )
				{
					$db->sql_query("INSERT INTO _members_group (user_id, group_id, user_pending) VALUES (" . $row['user_id'] . ", $group_id, 0)");
					
					if ( $row['user_level'] != USER_ADMIN && $row['user_level'] != USER_MOD && $group_info['auth_mod'] )
					{
						$db->sql_query("UPDATE _members SET user_level = " . USER_MOD . " WHERE user_id = " . $row['user_id']);
					}

					//
					// Get the group name
					// Email the user and tell them they're in the group
					//
					$result = $db->sql_query("SELECT group_name FROM _groups WHERE group_id = '$group_id'");

					$group_name_row = $db->sql_fetchrow($result);
					$group_name = $group_name_row['group_name'];

					$emailer = new emailer();

					$emailer->from($config['board_email']);
					$emailer->replyto($config['board_email']);

					$emailer->use_template('group_added', $row['user_lang']);
					$emailer->email_address($row['user_email']);
					$emailer->set_subject($lang['Group_added']);

					$emailer->assign_vars(array(
						'SITENAME' => $config['sitename'], 
						'GROUP_NAME' => $group_name,

						'U_GROUPCP' => $server_url . '?' . POST_GROUPS_URL . "=$group_id")
					);
					$emailer->send();
					$emailer->reset();
				}
				else
				{
					$template->assign_vars(array(
						'META' => '<meta http-equiv="refresh" content="3;url=' . "groupcp.php?g=$group_id" . '">')
					);

					$message = $lang['User_is_member_group'] . '<br /><br />' . sprintf($lang['Click_return_group'], '<a href="' . "groupcp.php?=$group_id" . '">', '</a>') . '<br /><br />' . sprintf($lang['Click_return_index'], '<a href="' . s_link('cover') . '">', '</a>');

					trigger_error($message);
				}
			}
			else 
			{
				if ( ( ( isset($_POST['approve']) || isset($_POST['deny']) ) && isset($_POST['pending_members']) ) || ( isset($_POST['remove']) && isset($_POST['members']) ) )
				{

					$members = ( isset($_POST['approve']) || isset($_POST['deny']) ) ? $_POST['pending_members'] : $_POST['members'];

					$sql_in = '';
					for($i = 0; $i < count($members); $i++)
					{
						$sql_in .= ( ( $sql_in != '' ) ? ', ' : '' ) . intval($members[$i]);
					}

					if ( isset($_POST['approve']) )
					{
						if ( $group_info['auth_mod'] )
						{
							$db->sql_query("UPDATE _members SET user_level = " . USER_MOD . " WHERE user_id IN ($sql_in) AND user_level NOT IN (" . USER_MOD . ", " . USER_ADMIN . ")");
						}

						$sql = "UPDATE _members_group SET user_pending = 0 WHERE user_id IN ($sql_in) AND group_id = $group_id";
						$sql_select = "SELECT user_email FROM _members WHERE user_id IN ($sql_in)"; 
					}
					else if ( isset($_POST['deny']) || isset($_POST['remove']) )
					{
						if ( $group_info['auth_mod'] )
						{
							$sql = "SELECT ug.user_id, ug.group_id 
								FROM _auth_access aa, _members_group ug 
								WHERE ug.user_id IN  ($sql_in) 
									AND aa.group_id = ug.group_id 
									AND aa.auth_mod = 1 
								GROUP BY ug.user_id, ug.group_id 
								ORDER BY ug.user_id, ug.group_id";
							$result = $db->sql_query($sql);
							unset($sql);

							if ( $row = $db->sql_fetchrow($result) )
							{
								$group_check = array();
								$remove_mod_sql = '';

								do
								{
									$group_check[$row['user_id']][] = $row['group_id'];
								}
								while ( $row = $db->sql_fetchrow($result) );

								while( list($user_id, $group_list) = @each($group_check) )
								{
									if ( count($group_list) == 1 )
									{
										$remove_mod_sql .= ( ( $remove_mod_sql != '' ) ? ', ' : '' ) . $user_id;
									}
								}

								if ( $remove_mod_sql != '' )
								{
									$db->sql_query("UPDATE _members SET user_level = " . USER_NORMAL . " WHERE user_id IN ($remove_mod_sql) AND user_level NOT IN (" . USER_ADMIN . ")");
								}
							}
						}

						$sql = "DELETE FROM _members_group WHERE user_id IN ($sql_in) AND group_id = $group_id";
					}

					$db->sql_query($sql);

					//
					// Email users when they are approved
					//
					if ( isset($_POST['approve']) )
					{
						$result = $db->sql_query($sql_select);

						$bcc_list = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$bcc_list[] = $row['user_email'];
						}

						//
						// Get the group name
						//
						$result = $db->sql_query("SELECT group_name FROM _groups WHERE group_id = '$group_id'");

						$group_name_row = $db->sql_fetchrow($result);
						$group_name = $group_name_row['group_name'];

						$emailer = new emailer();

						$emailer->from($config['board_email']);
						$emailer->replyto($config['board_email']);

						for ($i = 0; $i < count($bcc_list); $i++)
						{
							$emailer->bcc($bcc_list[$i]);
						}

						$emailer->use_template('group_approved');
						$emailer->set_subject($lang['Group_approved']);

						$emailer->assign_vars(array(
							'SITENAME' => $config['sitename'], 
							'GROUP_NAME' => $group_name,

							'U_GROUPCP' => $server_url . '?' . POST_GROUPS_URL . "=$group_id")
						);
						$emailer->send();
						$emailer->reset();
					}
				}
			}
		}
		//
		// END approve or deny
		//
	}
	else
	{
		trigger_error('No_groups_exist');
	}

	//
	// Get group details
	//
	$result = $db->sql_query("SELECT * FROM _groups WHERE group_id = '$group_id' AND group_single_user = 0");

	if ( !($group_info = $db->sql_fetchrow($result)) )
	{
		trigger_error('Group_not_exist');
	}

	//
	// Get moderator details for this group
	//
	$result = $db->sql_query("SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email, user_icq, user_aim, user_yim, user_msnm  FROM _members WHERE user_id = " . $group_info['group_moderator']);

	$group_moderator = $db->sql_fetchrow($result); 

	//
	// Get user information for this group
	//
	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, ug.user_pending 
		FROM _members u, _members_group ug
		WHERE ug.group_id = $group_id
			AND u.user_id = ug.user_id
			AND ug.user_pending = 0 
			AND ug.user_id <> " . $group_moderator['user_id'] . " 
		ORDER BY u.username LIMIT $start, ".$config['topics_per_page']; 
	$result = $db->sql_query($sql);
	unset($sql);

	$group_members = $db->sql_fetchrowset($result); 
	// 1 line deleted for Faster groupcp USER_MOD >> $members_count = count($group_members);
	$db->sql_freeresult($result);

	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm
		FROM _members_group ug, _members u
		WHERE ug.group_id = $group_id 
			AND ug.user_pending = 1
			AND u.user_id = ug.user_id
		ORDER BY u.username"; 
	$result = $db->sql_query($sql);
	unset($sql);

	$modgroup_pending_list = $db->sql_fetchrowset($result);

	$result = $db->sql_query("SELECT SUM(user_pending = 0) as members, SUM(user_pending=1) as pending FROM _members_group WHERE group_id = $group_id AND user_id <> " . $group_moderator['user_id']);

	$counting_list = $db->sql_fetchrow($result);
	$members_count = $counting_list['members'];
	$modgroup_pending_count = $counting_list['pending'];
	// End replacement - Faster groupcp USER_MOD
	$db->sql_freeresult($result);

	$is_group_member = 0;
	if ( $members_count )
	{
		for($i = 0; $i < $members_count; $i++)
		{
			if ( $group_members[$i]['user_id'] == $userdata['user_id'] && $userdata['session_logged_in'] )
			{
				$is_group_member = true; 
			}
		}
	}

	$is_group_pending_member = 0;
	$is_autogroup_enable = ($group_info['group_count'] <= $userdata['user_posts'] && $group_info['group_count_max'] > $userdata['user_posts']) ? true : false;

	if ( $modgroup_pending_count )
	{
		for($i = 0; $i < $modgroup_pending_count; $i++)
		{
			if ( $modgroup_pending_list[$i]['user_id'] == $userdata['user_id'] && $userdata['session_logged_in'] )
			{
				$is_group_pending_member = true;
			}
		}
	}

	if ( $userdata['user_level'] == USER_ADMIN )
	{
		$is_moderator = true;
	}

	if ( $userdata['user_id'] == $group_info['group_moderator'] )
	{
		$is_moderator = true;

		$group_details =  $lang['Are_group_moderator'];

		$s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
	}
	else if ( $is_group_member || $is_group_pending_member )
	{
		$template->assign_block_vars('switch_unsubscribe_group_input', array());

		$group_details =  ( $is_group_pending_member ) ? $lang['Pending_this_group'] : $lang['Member_this_group'];

		$s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
	}
	else if ( $userdata['user_id'] == GUEST )
	{
		$group_details =  $lang['Login_to_join'];
		$s_hidden_fields = '';
	}
	else
	{
		if ( $group_info['group_type'] == GROUP_OPEN )
		{
			$template->assign_block_vars('switch_subscribe_group_input', array());

			$group_details =  $lang['This_open_group'];
			$s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
		}
		else if ( $group_info['group_type'] == GROUP_CLOSED )
		{
			if ($is_autogroup_enable) 
			{
				$template->assign_block_vars('switch_subscribe_group_input', array());
				$group_details =  sprintf ($lang['This_closed_group'],$lang['Join_auto']);
				$s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
			} else
			{
				$group_details =  sprintf ($lang['This_closed_group'],$lang['No_more']);
				$s_hidden_fields = '';
			}
		}
		else if ( $group_info['group_type'] == GROUP_HIDDEN )
		{
			if ($is_autogroup_enable) 
			{
				$template->assign_block_vars('switch_subscribe_group_input', array());
				$group_details =  sprintf ($lang['This_hidden_group'],$lang['Join_auto']);
				$s_hidden_fields = '<input type="hidden" name="' . POST_GROUPS_URL . '" value="' . $group_id . '" />';
			} else
			{
				$group_details =  sprintf ($lang['This_closed_group'],$lang['No_add_allowed']);
				$s_hidden_fields = '';
			}
		}
	}
	
	//
	// Load templates
	//
	$template->set_filenames(array(
		'pendinginfo' => 'groupcp_pending_info.htm')
	);

	//
	// Add the moderator
	//
	$username = $group_moderator['username'];
	$user_id = $group_moderator['user_id'];

	generate_user_info($group_moderator, $config['default_dateformat'], $is_moderator, $from, $posts, $joined, $poster_avatar, $profile_img, $profile, $search_img, $search, $pm_img, $pm, $email_img, $email, $www_img, $www, $icq_status_img, $icq_img, $icq, $aim_img, $aim, $msn_img, $msn, $yim_img, $yim);

	$s_hidden_fields .= '';

	$template->assign_vars(array(
		'L_GROUP_INFORMATION' => $lang['Group_Information'],
		'L_GROUP_NAME' => $lang['Group_name'],
		'L_GROUP_DESC' => $lang['Group_description'],
		'L_GROUP_TYPE' => $lang['Group_type'],
		'L_GROUP_MEMBERSHIP' => $lang['Group_membership'],
		'L_SUBSCRIBE' => $lang['Subscribe'],
		'L_UNSUBSCRIBE' => $lang['Unsubscribe'],
		'L_JOIN_GROUP' => $lang['Join_group'], 
		'L_UNSUBSCRIBE_GROUP' => $lang['Unsubscribe'], 
		'L_GROUP_OPEN' => $lang['Group_open'],
		'L_GROUP_CLOSED' => $lang['Group_closed'],
		'L_GROUP_HIDDEN' => $lang['Group_hidden'], 
		'L_UPDATE' => $lang['Update'], 
		'L_GROUP_MODERATOR' => $lang['Group_Moderator'], 
		'L_GROUP_MEMBERS' => $lang['Group_Members'], 
		'L_PENDING_MEMBERS' => $lang['Pending_members'], 
		'L_SELECT_SORT_METHOD' => $lang['Select_sort_method'], 
		'L_PM' => $lang['Private_Message'], 
		'L_EMAIL' => $lang['Email'], 
		'L_POSTS' => $lang['Posts'], 
		'L_WEBSITE' => $lang['Website'],
		'L_FROM' => $lang['Location'],
		'L_ORDER' => $lang['Order'],
		'L_SORT' => $lang['Sort'],
		'L_SUBMIT' => $lang['Sort'],
		'L_AIM' => $lang['AIM'],
		'L_YIM' => $lang['YIM'],
		'L_MSNM' => $lang['MSNM'],
		'L_ICQ' => $lang['ICQ'],
		'L_SELECT' => $lang['Select'],
		'L_REMOVE_SELECTED' => $lang['Remove_selected'],
		'L_ADD_MEMBER' => $lang['Add_member'],
		'L_FIND_USERNAME' => $lang['Find_username'],

		'GROUP_NAME' => $group_info['group_name'],
		'GROUP_DESC' => $group_info['group_description'],
		'GROUP_DETAILS' => $group_details,
		'MOD_ROW_COLOR' => '#' . $themeset['td_color1'],
		'MOD_ROW_CLASS' => $themeset['td_class1'],
		'MOD_USERNAME' => $username,
		'MOD_FROM' => $from,
		'MOD_JOINED' => $joined,
		'MOD_POSTS' => $posts,
		'MOD_AVATAR_IMG' => $poster_avatar,
		'MOD_PROFILE_IMG' => $profile_img, 
		'MOD_PROFILE' => $profile, 
		'MOD_SEARCH_IMG' => $search_img,
		'MOD_SEARCH' => $search,
		'MOD_PM_IMG' => $pm_img,
		'MOD_PM' => $pm,
		'MOD_EMAIL_IMG' => $email_img,
		'MOD_EMAIL' => $email,
		'MOD_WWW_IMG' => $www_img,
		'MOD_WWW' => $www,
		'MOD_ICQ_STATUS_IMG' => $icq_status_img,
		'MOD_ICQ_IMG' => $icq_img, 
		'MOD_ICQ' => $icq, 
		'MOD_AIM_IMG' => $aim_img,
		'MOD_AIM' => $aim,
		'MOD_MSN_IMG' => $msn_img,
		'MOD_MSN' => $msn,
		'MOD_YIM_IMG' => $yim_img,
		'MOD_YIM' => $yim,

		'U_MOD_VIEWPROFILE' => s_link('u', $user_id),
		'U_SEARCH_USER' => "search.php?mode=searchuser", 

		'S_GROUP_OPEN_TYPE' => GROUP_OPEN,
		'S_GROUP_CLOSED_TYPE' => GROUP_CLOSED,
		'S_GROUP_HIDDEN_TYPE' => GROUP_HIDDEN,
		'S_GROUP_OPEN_CHECKED' => ( $group_info['group_type'] == GROUP_OPEN ) ? ' checked="checked"' : '',
		'S_GROUP_CLOSED_CHECKED' => ( $group_info['group_type'] == GROUP_CLOSED ) ? ' checked="checked"' : '',
		'S_GROUP_HIDDEN_CHECKED' => ( $group_info['group_type'] == GROUP_HIDDEN ) ? ' checked="checked"' : '',
		'S_HIDDEN_FIELDS' => $s_hidden_fields, 
		'S_MODE_SELECT' => $select_sort_mode,
		'S_ORDER_SELECT' => $select_sort_order,
		'S_GROUPCP_ACTION' => "groupcp.php?g=$group_id")
	);

	//
	// Dump out the remaining users
	//
	// 1 line deleted >> for($i = $start; $i < min($config['topics_per_page'] + $start, $members_count); $i++)
	// Start replacement - Faster groupcp USER_MOD
	$i = -1;
	while(!empty($group_members[++$i]['username']))
	// End replacement - Faster groupcp USER_MOD
	{
		$username = $group_members[$i]['username'];
		$user_id = $group_members[$i]['user_id'];

		generate_user_info($group_members[$i], $config['default_dateformat'], $is_moderator, $from, $posts, $joined, $poster_avatar, $profile_img, $profile, $search_img, $search, $pm_img, $pm, $email_img, $email, $www_img, $www, $icq_status_img, $icq_img, $icq, $aim_img, $aim, $msn_img, $msn, $yim_img, $yim);

		if ( $group_info['group_type'] != GROUP_HIDDEN || $is_group_member || $is_moderator )
		{
			$row_color = ( !($i % 2) ) ? $themeset['td_color1'] : $themeset['td_color2'];
			$row_class = ( !($i % 2) ) ? $themeset['td_class1'] : $themeset['td_class2'];

			$template->assign_block_vars('member_row', array(
				'ROW_COLOR' => '#' . $row_color,
				'ROW_CLASS' => $row_class,
				'USERNAME' => $username,
				'FROM' => $from,
				'JOINED' => $joined,
				'POSTS' => $posts,
				'USER_ID' => $user_id, 
				'AVATAR_IMG' => $poster_avatar,
				'PROFILE_IMG' => $profile_img, 
				'PROFILE' => $profile, 
				'SEARCH_IMG' => $search_img,
				'SEARCH' => $search,
				'PM_IMG' => $pm_img,
				'PM' => $pm,
				'EMAIL_IMG' => $email_img,
				'EMAIL' => $email,
				'WWW_IMG' => $www_img,
				'WWW' => $www,
				'ICQ_STATUS_IMG' => $icq_status_img,
				'ICQ_IMG' => $icq_img, 
				'ICQ' => $icq, 
				'AIM_IMG' => $aim_img,
				'AIM' => $aim,
				'MSN_IMG' => $msn_img,
				'MSN' => $msn,
				'YIM_IMG' => $yim_img,
				'YIM' => $yim,
				
				'U_VIEWPROFILE' => s_link('u', $user_id))
			);

			if ( $is_moderator )
			{
				$template->assign_block_vars('member_row.switch_mod_option', array());
			}
		}
	}

	if ( !$members_count )
	{
		//
		// No group members
		//
		$template->assign_block_vars('switch_no_members', array());
		$template->assign_vars(array(
			'L_NO_MEMBERS' => $lang['No_group_members'])
		);
	}

	$current_page = ( !$members_count ) ? 1 : ceil( $members_count / $config['topics_per_page'] );

	$template->assign_vars(array(
		'PAGINATION' => generate_pagination("groupcp.php?g=$group_id", $members_count, $config['topics_per_page'], $start),
		'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $config['topics_per_page'] ) + 1 ), $current_page ), 

		'L_GOTO_PAGE' => $lang['Goto_page'])
	);

	if ( $group_info['group_type'] == GROUP_HIDDEN && !$is_group_member && !$is_moderator )
	{
		//
		// No group members
		//
		$template->assign_block_vars('switch_hidden_group', array());
		$template->assign_vars(array(
			'L_HIDDEN_MEMBERS' => $lang['Group_hidden_members'])
		);
	}

	//
	// We've displayed the members who belong to the group, now we 
	// do that pending memebers... 
	//
	if ( $is_moderator )
	{
		//
		// Users pending in ONLY THIS GROUP (which is moderated by this user)
		//
		if ( $modgroup_pending_count )
		{
			for($i = 0; $i < $modgroup_pending_count; $i++)
			{
				$username = $modgroup_pending_list[$i]['username'];
				$user_id = $modgroup_pending_list[$i]['user_id'];

				generate_user_info($modgroup_pending_list[$i], $config['default_dateformat'], $is_moderator, $from, $posts, $joined, $poster_avatar, $profile_img, $profile, $search_img, $search, $pm_img, $pm, $email_img, $email, $www_img, $www, $icq_status_img, $icq_img, $icq, $aim_img, $aim, $msn_img, $msn, $yim_img, $yim);

				$row_color = ( !($i % 2) ) ? $themeset['td_color1'] : $themeset['td_color2'];
				$row_class = ( !($i % 2) ) ? $themeset['td_class1'] : $themeset['td_class2'];

				$user_select = '<input type="checkbox" name="member[]" value="' . $user_id . '">';

				$template->assign_block_vars('pending_members_row', array(
					'ROW_CLASS' => $row_class,
					'ROW_COLOR' => '#' . $row_color, 
					'USERNAME' => $username,
					'FROM' => $from,
					'JOINED' => $joined,
					'POSTS' => $posts,
					'USER_ID' => $user_id, 
					'AVATAR_IMG' => $poster_avatar,
					'PROFILE_IMG' => $profile_img, 
					'PROFILE' => $profile, 
					'SEARCH_IMG' => $search_img,
					'SEARCH' => $search,
					'PM_IMG' => $pm_img,
					'PM' => $pm,
					'EMAIL_IMG' => $email_img,
					'EMAIL' => $email,
					'WWW_IMG' => $www_img,
					'WWW' => $www,
					'ICQ_STATUS_IMG' => $icq_status_img,
					'ICQ_IMG' => $icq_img, 
					'ICQ' => $icq, 
					'AIM_IMG' => $aim_img,
					'AIM' => $aim,
					'MSN_IMG' => $msn_img,
					'MSN' => $msn,
					'YIM_IMG' => $yim_img,
					'YIM' => $yim,
					
					'U_VIEWPROFILE' => s_link('u', $user_id))
				);
			}

			$template->assign_block_vars('switch_pending_members', array() );

			$template->assign_vars(array(
				'L_SELECT' => $lang['Select'],
				'L_APPROVE_SELECTED' => $lang['Approve_selected'],
				'L_DENY_SELECTED' => $lang['Deny_selected'])
			);

			$template->assign_var_from_handle('PENDING_USER_BOX', 'pendinginfo');
		
		}
	}

	if ( $is_moderator )
	{
		$template->assign_block_vars('switch_mod_option', array());
		$template->assign_block_vars('switch_add_member', array());
	}
}
else
{
	//
	// Show the main groupcp.php screen where the user can select a group.
	//
	// Select all group that the user is a member of or where the user has
	// a pending membership.
	//
	$in_group = array();
	
	if ( $userdata['session_logged_in'] ) 
	{
		$sql = "SELECT g.group_id, g.group_name, g.group_type, ug.user_pending 
			FROM _groups g, _members_group ug
			WHERE ug.user_id = " . $userdata['user_id'] . "  
				AND ug.group_id = g.group_id
				AND g.group_single_user <> " . true . "
			ORDER BY g.group_name, ug.user_id";
		$result = $db->sql_query($sql);
		unset($sql);

		if ( $row = $db->sql_fetchrow($result) )
		{
			$in_group = array();
			$s_member_groups_opt = '';
			$s_pending_groups_opt = '';

			do
			{
				$in_group[] = $row['group_id'];
				if ( $row['user_pending'] )
				{
					$s_pending_groups_opt .= '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
				}
				else
				{
					$s_member_groups_opt .= '<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
				}
			}
			while( $row = $db->sql_fetchrow($result) );

			$s_pending_groups = '<select name="' . POST_GROUPS_URL . '">' . $s_pending_groups_opt . "</select>";
			$s_member_groups = '<select name="' . POST_GROUPS_URL . '">' . $s_member_groups_opt . "</select>";
		}
	}

	//
	// Select all other groups i.e. groups that this user is not a member of
	//
	$ignore_group_sql =	( count($in_group) ) ? "AND group_id NOT IN (" . implode(', ', $in_group) . ")" : ''; 
	$sql = "SELECT group_id, group_name, group_type, group_count , group_count_max 
		FROM _groups g 
		WHERE group_single_user <> " . true . " 
			$ignore_group_sql 
		ORDER BY g.group_name";
	$result = $db->query($sql);
	unset($sql);

	$s_group_list_opt = '';
	while( $row = $db->fetchrow($result) )
	{
$is_autogroup_enable = ($row['group_count'] <= $userdata['user_posts'] && $row['group_count_max'] > $userdata['user_posts']) ? true : false;

		if  ( $row['group_type'] != GROUP_HIDDEN || $userdata['user_level'] == USER_ADMIN|| $is_autogroup_enable )
		{
			$s_group_list_opt .='<option value="' . $row['group_id'] . '">' . $row['group_name'] . '</option>';
		}
	}
	$s_group_list = '<select name="' . POST_GROUPS_URL . '">' . $s_group_list_opt . '</select>';

	if ( $s_group_list_opt != '' || $s_pending_groups_opt != '' || $s_member_groups_opt != '' )
	{
		//
		// Load and process templates
		//
		$template->set_filenames(array(
			'body' => 'groupcp_user_body.htm')
		);

		if ( $s_pending_groups_opt != '' || $s_member_groups_opt != '' )
		{
			$template->assign_block_vars('switch_groups_joined', array() );
		}

		if ( $s_member_groups_opt != '' )
		{
			$template->assign_block_vars('switch_groups_joined.switch_groups_member', array() );
		}

		if ( $s_pending_groups_opt != '' )
		{
			$template->assign_block_vars('switch_groups_joined.switch_groups_pending', array() );
		}

		if ( $s_group_list_opt != '' )
		{
			$template->assign_block_vars('switch_groups_remaining', array() );
		}

		$s_hidden_fields = '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />';

		$template->assign_vars(array(
			'L_GROUP_MEMBERSHIP_DETAILS' => $lang['Group_member_details'],
			'L_JOIN_A_GROUP' => $lang['Group_member_join'],
			'L_YOU_BELONG_GROUPS' => $lang['Current_memberships'],
			'L_SELECT_A_GROUP' => $lang['Non_member_groups'],
			'L_PENDING_GROUPS' => $lang['Memberships_pending'],
			'L_SUBSCRIBE' => $lang['Subscribe'],
			'L_UNSUBSCRIBE' => $lang['Unsubscribe'],
			'L_VIEW_INFORMATION' => $lang['View_Information'], 

			'S_USERGROUP_ACTION' => "groupcp.php", 
			'S_HIDDEN_FIELDS' => $s_hidden_fields, 

			'GROUP_LIST_SELECT' => $s_group_list,
			'GROUP_PENDING_SELECT' => $s_pending_groups,
			'GROUP_MEMBER_SELECT' => $s_member_groups)
		);
	}
	else
	{
		trigger_error('No_groups_exist');
	}

}

page_layout('Group_Control_Panel', 'groupcp_info_body');

//
// FUNCTIONS
//
function generate_user_info(&$row, $date_format, $group_mod, &$from, &$posts, &$joined, &$poster_avatar, &$profile_img, &$profile, &$search_img, &$search, &$pm_img, &$pm, &$email_img, &$email, &$www_img, &$www, &$icq_status_img, &$icq_img, &$icq, &$aim_img, &$aim, &$msn_img, &$msn, &$yim_img, &$yim)
{
	global $lang, $images, $config;

	$from = ( !empty($row['user_from']) ) ? $row['user_from'] : '&nbsp;';
	$joined = $user->format_date($row['user_regdate'], $date_format);
	$posts = ( $row['user_posts'] ) ? $row['user_posts'] : 0;

	$poster_avatar = '';
	if ($row['user_avatar_type'] && $row['user_id'] != GUEST)
	{
		switch( $row['user_avatar_type'] )
		{
			case USER_AVATAR_UPLOAD:
				$poster_avatar = ( $config['allow_avatar_upload'] ) ? '<img src="' . $config['avatar_path'] . '/' . $row['user_avatar'] . '" alt="" border="0" />' : '';
				break;
			case USER_AVATAR_REMOTE:
				$poster_avatar = ( $config['allow_avatar_remote'] ) ? '<img src="' . $row['user_avatar'] . '" alt="" border="0" />' : '';
				break;
			case USER_AVATAR_GALLERY:
				$poster_avatar = ( $config['allow_avatar_local'] ) ? '<img src="' . $config['avatar_gallery_path'] . '/' . $row['user_avatar'] . '" alt="" border="0" />' : '';
				break;
		}
	}

	if ( !empty($row['user_viewemail']) || $group_mod )
	{
		$email_uri = '';
		$email = '';
	}
	else
	{
		$email_img = '&nbsp;';
		$email = '&nbsp;';
	}

	$temp_url = s_link('m', $row['username_base']);
	$profile = '<a href="' . $temp_url . '">' . $lang['Read_profile'] . '</a>';

	$temp_url = s_link('my', array('note', $row['username_base']));
	$pm = '<a href="' . $temp_url . '">' . $lang['Send_private_message'] . '</a>';

	$www = ( $row['user_website'] ) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . $lang['Visit_website'] . '</a>' : '';

	if ( !empty($row['user_icq']) )
	{
		$icq_status_img = '<a href="http://wwp.icq.com/' . $row['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&img=5" width="18" height="18" border="0" /></a>';
		$icq =  '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $row['user_icq'] . '">' . $lang['ICQ'] . '</a>';
	}
	else
	{
		$icq_status_img = '';
		$icq_img = '';
		$icq = '';
	}

	$aim = ( $row['user_aim'] ) ? '<a href="aim:goim?screenname=' . $row['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $lang['AIM'] . '</a>' : '';

	$temp_url = s_link('u', $row['username_base']);
	$msn = ( $row['user_msnm'] ) ? '<a href="' . $temp_url . '">' . $lang['MSNM'] . '</a>' : '';

	$yim = ( $row['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg">' . $lang['YIM'] . '</a>' : '';

	return;
}