
# Migration commands

USE educom;

DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `_commentmeta`;
DROP TABLE IF EXISTS `_comments`;
DROP TABLE IF EXISTS `_mmg_upr`;
DROP TABLE IF EXISTS `_options`;
DROP TABLE IF EXISTS `_podpress_statcounts`;
DROP TABLE IF EXISTS `_podpress_stats`;
DROP TABLE IF EXISTS `_posts`;
DROP TABLE IF EXISTS `_postmeta`;
DROP TABLE IF EXISTS `_site_history`;
DROP TABLE IF EXISTS `_usermeta`;
DROP TABLE IF EXISTS `_users`;
DROP TABLE IF EXISTS `_term_relationships`;
DROP TABLE IF EXISTS `_term_taxonomy`;
DROP TABLE IF EXISTS `_terms`;
DROP TABLE IF EXISTS `_config`;
DROP TABLE IF EXISTS `_twitter`;

TRUNCATE TABLE `alumno`;
TRUNCATE TABLE `alumnos_encargados`;
TRUNCATE TABLE `faltas`;
TRUNCATE TABLE `secciones`;
TRUNCATE TABLE `grado`;
TRUNCATE TABLE `notas`;
TRUNCATE TABLE `ocupacion_alumno`;
TRUNCATE TABLE `reinscripcion`;
TRUNCATE TABLE `examenes`;
TRUNCATE TABLE `catedratico`;
TRUNCATE TABLE `cursos`;

TRUNCATE TABLE `_activities_assigned`;
TRUNCATE TABLE `_activities`;

TRUNCATE TABLE `_art_posts`;
TRUNCATE TABLE `_art_fav`;
TRUNCATE TABLE `_art`;

TRUNCATE TABLE `_artists`;
TRUNCATE TABLE `_artists_access`;
TRUNCATE TABLE `_artists_auth`;
TRUNCATE TABLE `_artists_events`;
TRUNCATE TABLE `_artists_fav`;
TRUNCATE TABLE `_artists_images`;
TRUNCATE TABLE `_artists_log`;
TRUNCATE TABLE `_artists_lyrics`;
TRUNCATE TABLE `_artists_posts`;
TRUNCATE TABLE `_artists_stats`;
TRUNCATE TABLE `_artists_video`;
TRUNCATE TABLE `_artists_viewers`;
TRUNCATE TABLE `_artists_voters`;
TRUNCATE TABLE `_artists_votes`;

TRUNCATE TABLE `_chat_auth`;
TRUNCATE TABLE `_chat_cat`;
TRUNCATE TABLE `_chat_ch`;
TRUNCATE TABLE `_chat_sessions`;

TRUNCATE TABLE `_banlist`;
TRUNCATE TABLE `_crypt_confirm`;
TRUNCATE TABLE `_dc`;
TRUNCATE TABLE `_dl_fav`;
TRUNCATE TABLE `_dl_posts`;
TRUNCATE TABLE `_dl_vote`;
TRUNCATE TABLE `_dl_voters`;
TRUNCATE TABLE `_dl`;
TRUNCATE TABLE `_downloads`;
TRUNCATE TABLE `_email`;

TRUNCATE TABLE `_events`;
TRUNCATE TABLE `_events_colab`;
TRUNCATE TABLE `_events_fav`;
TRUNCATE TABLE `_events_images`;
TRUNCATE TABLE `_events_posts`;

TRUNCATE TABLE `_forum_posts_rev`;
TRUNCATE TABLE `_forum_topics_nopoints`;
TRUNCATE TABLE `_forum_topics_fav`;
TRUNCATE TABLE `_forum_posts`;
TRUNCATE TABLE `_forum_topics`;
TRUNCATE TABLE `_forum_categories`;
TRUNCATE TABLE `_forums`;

TRUNCATE TABLE `_groups`;
TRUNCATE TABLE `_help_faq`;
TRUNCATE TABLE `_help_cat`;
TRUNCATE TABLE `_help_modules`;
TRUNCATE TABLE `_links`;
TRUNCATE TABLE `_partners`;

TRUNCATE TABLE `_members_ban`;
TRUNCATE TABLE `_members_friends`;
TRUNCATE TABLE `_members_group`;
TRUNCATE TABLE `_members_iplog`;
TRUNCATE TABLE `_members_posts`;
TRUNCATE TABLE `_members_ref_assoc`;
TRUNCATE TABLE `_members_ref_invite`;
TRUNCATE TABLE `_members_unread`;
TRUNCATE TABLE `_members_viewers`;
TRUNCATE TABLE `_members`;

TRUNCATE TABLE `_news_posts`;
TRUNCATE TABLE `_news_cat`;
TRUNCATE TABLE `_news`;

TRUNCATE TABLE `_poll_options`;
TRUNCATE TABLE `_poll_results`;
TRUNCATE TABLE `_poll_voters`;

TRUNCATE TABLE `_radio`;
TRUNCATE TABLE `_radio_dj`;
TRUNCATE TABLE `_radio_dj_log`;
TRUNCATE TABLE `_radio_stats_vf`;
TRUNCATE TABLE `_ranks`;
TRUNCATE TABLE `_ref`;
-- TRUNCATE TABLE `_sessions`;

TRUNCATE TABLE `_today_objects`;
TRUNCATE TABLE `_today_type`;

TRUNCATE TABLE `_team_members`;
TRUNCATE TABLE `_team`;

INSERT INTO `_forum_categories` (cat_title, cat_order) VALUES ('Colegio San Gabriel', '10');

INSERT INTO `_members` (user_type, user_active, username, username_base, user_password, user_password_old, user_regip, user_session_time, user_lastpage, user_lastvisit, user_regdate, user_auth_control, user_level, user_posts, user_points, user_dl_favs, user_a_favs, userpage_posts, user_color, user_block_points, user_timezone, user_dst, user_lang, user_dateformat, user_country, user_emailtime, user_hideuser, user_notify, user_notify_pm, user_rank, user_rank_text, user_avatar, user_avatar_type, user_email, user_public_email, user_icq, user_website, user_location, user_sig, user_aim, user_yim, user_msnm, user_lastfm, user_twitter_account, user_twitter_key, user_occ, user_interests, user_os, user_fav_genres, user_fav_artists, user_upw, user_actkey, user_newpasswd, user_lastlogon, user_totaltime, user_totallogon, user_totalpages, user_gender, user_birthday, user_birthday_last, user_login_tries, user_last_login_try, user_send_mass, user_mark_items, user_topic_order, user_return_unread, user_email_dc, user_refop, user_refby)
VALUES(2, 0, 'Invitado', '', '', '', '0', 1143590870, '-4', 1143588924, 1079384862, 0, 0, 791, 13, 0, 0, 0, '4D5358', 0, -6.00, 0, '', 'd M Y H:i', 90, NULL, 0, 0, 1, NULL, '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 1143590870, 33573216, 133295, 263444, 0, '0', 0, 0, 0, 0, 0, 0, 1, 1, 0, '');

INSERT INTO `_members` (user_type, user_active, username, username_base, user_password, user_password_old, user_regip, user_session_time, user_lastpage, user_lastvisit, user_regdate, user_auth_control, user_level, user_posts, user_points, user_dl_favs, user_a_favs, userpage_posts, user_color, user_block_points, user_timezone, user_dst, user_lang, user_dateformat, user_country, user_emailtime, user_hideuser, user_notify, user_notify_pm, user_rank, user_rank_text, user_avatar, user_avatar_type, user_email, user_public_email, user_icq, user_website, user_location, user_sig, user_aim, user_yim, user_msnm, user_lastfm, user_twitter_account, user_twitter_key, user_occ, user_interests, user_os, user_fav_genres, user_fav_artists, user_upw, user_actkey, user_newpasswd, user_lastlogon, user_totaltime, user_totallogon, user_totalpages, user_gender, user_birthday, user_birthday_last, user_login_tries, user_last_login_try, user_send_mass, user_mark_items, user_topic_order, user_return_unread, user_email_dc, user_refop, user_refby)
VALUES(3, 1, 'Guillermo', 'guillermo', 'c1fc640b69e0974f376143fece8dc15119eea82f244787f5e1cf3190baa7ad2b7acedb3eb6629535332da96688277ed713741d5a89880063ea7f41eab2e1ff75', '93a12bf8df3cfe7900851d838fa02be46daf9d72', '0', 1143435543, '', 1423472875, 1079384862, 1, 1, 4561, 442, 2, 0, 1604, 'E9C502', 0, -6.00, 0, 'spanish', 'd M Y H:i', 90, 1082044885, 1, 0, 0, 3689, '', '', 0, 'info@nopticon.com', 'info@nopticon.com', '', '', '', '', '', '', '', 'Psychopsia', '', '', '', '', '', '', '', '8i8o11ag4otatkan', '', '', 1143425618, 3715880, 1048, 49031, 1, '19850503', 2013, 0, 0, 1, 0, 0, 1, 1, 0, '');

INSERT INTO `examenes` (examen, observacion, status, fecha_ingreso) VALUES ('Primer Bimestre', '', 'Alta', '2015-02-23');
INSERT INTO `examenes` (examen, observacion, status, fecha_ingreso) VALUES ('Segundo Bimestre', '', 'Alta', '2015-02-23');
INSERT INTO `examenes` (examen, observacion, status, fecha_ingreso) VALUES ('Tercer Bimestre', '', 'Alta', '2015-02-23');
INSERT INTO `examenes` (examen, observacion, status, fecha_ingreso) VALUES ('Cuarto Bimestre', '', 'Alta', '2015-02-23');
INSERT INTO `examenes` (examen, observacion, status, fecha_ingreso) VALUES ('Primera Recuperaci&oacute;n', '', 'Alta', '2015-02-23');
INSERT INTO `examenes` (examen, observacion, status, fecha_ingreso) VALUES ('Segunda Recuperaci&oacute;n', '', 'Alta', '2015-02-23');

