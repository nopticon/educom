DELETE FROM _config WHERE config_name = 'avatar_path';
DELETE FROM _config WHERE config_name = 'avatar_gallery_path';
DELETE FROM _config WHERE config_name = 'prune_enable';
DELETE FROM _config WHERE config_name = 'gzip_compress';
DELETE FROM _config WHERE config_name = 'xs_version';
DELETE FROM _config WHERE config_name = 'max_topics';
DELETE FROM _config WHERE config_name = 'max_posts';
DELETE FROM _config WHERE config_name = 's_version';
DELETE FROM _config WHERE config_name = 'default_avatar_set';
DELETE FROM _config WHERE config_name = 'default_avatar_users_url';
DELETE FROM _config WHERE config_name = 'default_avatar_guests_url';
DELETE FROM _config WHERE config_name = 'xs_ftp_host';
DELETE FROM _config WHERE config_name = 'xs_ftp_login';
DELETE FROM _config WHERE config_name = 'xs_ftp_path';
DELETE FROM _config WHERE config_name = 'xs_downloads_count';
DELETE FROM _config WHERE config_name = 'xs_downloads_default';
DELETE FROM _config WHERE config_name = 'xs_downloads_0';
DELETE FROM _config WHERE config_name = 'xs_downloads_title_0';
DELETE FROM _config WHERE config_name = 'xs_shownav';
DELETE FROM _config WHERE config_name = 'num_topics';
DELETE FROM _config WHERE config_name = 'num_posts';
DELETE FROM _config WHERE config_name = 'twitter_rk_account';
DELETE FROM _config WHERE config_name = 'twitter_rk_key';
DELETE FROM _config WHERE config_name = 'config_id';
DELETE FROM _config WHERE config_name = 'smtp_delivery';
DELETE FROM _config WHERE config_name = 'smtp_host';
DELETE FROM _config WHERE config_name = 'smtp_username';
DELETE FROM _config WHERE config_name = 'smtp_password';
DELETE FROM _config WHERE config_name = 'board_email_form';
DELETE FROM _config WHERE config_name = 'smilies_path';
DELETE FROM _config WHERE config_name = 'script_path';
DELETE FROM _config WHERE config_name = 'board_email_sig';
DELETE FROM _config WHERE config_name = 'main_dl';
DELETE FROM _config WHERE config_name = 'dl_rate';
DELETE FROM _config WHERE config_name = 'check_www';
DELETE FROM _config WHERE config_name = 'login_reset_time';
DELETE FROM _config WHERE config_name = 'enable_confirm';
DELETE FROM _config WHERE config_name = 'board_startdate';
DELETE FROM _config WHERE config_name = 'xs_check_switches';
DELETE FROM _config WHERE config_name = 'xs_def_template';
DELETE FROM _config WHERE config_name = 'xs_auto_recompile';
DELETE FROM _config WHERE config_name = 'xs_auto_compile';
DELETE FROM _config WHERE config_name = 'server_port';
DELETE FROM _config WHERE config_name = 'xs_warn_includes';
DELETE FROM _config WHERE config_name = 'shoutcast_host';
DELETE FROM _config WHERE config_name = 'shoutcast_port';
DELETE FROM _config WHERE config_name = 'shoutcast_code';
DELETE FROM _config WHERE config_name = 'mailserver_url';
DELETE FROM _config WHERE config_name = 'mailserver_port_url';
DELETE FROM _config WHERE config_name = 'mailserver_news_login';
DELETE FROM _config WHERE config_name = 'mailserver_news_pass';
DELETE FROM _config WHERE config_name = 'sc_stats_host';
DELETE FROM _config WHERE config_name = 'sc_stats_port';
DELETE FROM _config WHERE config_name = 'sc_stats_ip';
DELETE FROM _config WHERE config_name = 'sc_stats_ipport';
DELETE FROM _config WHERE config_name = 'sc_stats_down';
DELETE FROM _config WHERE config_name = 'sc_stats_key';
DELETE FROM _config WHERE config_name = 'max_login_attempts';

INSERT INTO _config (config_name, config_value) VALUES ('assets_url', '//assets.rockrepublik.net/');
INSERT INTO _config (config_name, config_value) VALUES ('assets_path', 'assets/');

ALTER TABLE _artists_voters ADD voter_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _artists_votes ADD vote_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _art_fav ADD fav_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _chat_auth ADD PRIMARY KEY(ch_id);
ALTER TABLE _dl_fav ADD fav_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _dl_vote ADD vote_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _dl_voters ADD voter_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _events_fav ADD fav_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _events_images ADD image_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _members_iplog ADD log_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _members_ref_invite ADD invite_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _members_unread ADD unread_id INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _members_viewers ADD viewers_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE _team_members ADD members_id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

CREATE TABLE IF NOT EXISTS _partners (
	partner_id MEDIUMINT(5) NOT NULL AUTO_INCREMENT,
	partner_name VARCHAR(100) NOT NULL DEFAULT '',
	partner_url VARCHAR(100) NOT NULL DEFAULT '',
	partner_image VARCHAR(50) NOT NULL DEFAULT '',
	partner_order SMALLINT(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (partner_id)
) ENGINE=InnoDB;

INSERT INTO _partners (partner_name, partner_url, partner_image, partner_order) VALUES
	('18-50 TV', 'http://www.18-50.tv/', '18-50.jpg', 1),
	('Black Moon Shows', 'http://www.blackmoonshows.com/', 'blackmoonshows.jpg', 2),
	('Craneo Metal de Costa Rica', 'http://www.craneometal.com/', 'craneometal.jpg', 3),
	('Equipos Profesionales, S.A.', 'http://www.equiposprofesionales.com/music/', 'epsa.jpg', 4),
	('Innovation Network Technologies', 'http://www.innett.com/', 'innett.jpg', 5),
	('Metal cr&iacute;tico, El Salvador', 'http://www.metalcritico.com/', 'metalcritico.jpg', 6),
	('Mundo Xpedition', 'http://www.mundoxpedition.com/', 'xpedition.jpg', 7),
	('Nopticon Networks', 'http://www.nopticon.com/', 'nopticon.jpg', 8),
	('Oxigeno Radio', 'http://www.oxigenoradio.net/', 'oxigenoradio.jpg', 9),
	('Radio Reacktor', 'http://www.reacktor.com/', 'reacktor.jpg', 10),
	('Radio Rock 9-80', 'http://www.rock9-80.com/', 'rock9-80.jpg', 11),
	('Rock Stage Rumania', 'http://www.rockstage.ro/', 'rockstage.jpg', 12),
	('Sangre Chapina', 'http://www.sangrechapina.com/', 'sangrechapina.jpg', 13),
	('The Metal Room', 'http://www.elcuartodelmetal.co.cc/', 'themetalroom.jpg', 14);