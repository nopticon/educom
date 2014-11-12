ALTER TABLE _forum_posts
  ADD post_likes INT( 11 ) NOT NULL,
  ADD post_dislikes INT( 11 ) NOT NULL;

ALTER TABLE `_forum_posts`
  CHANGE COLUMN `post_likes` `post_likes` INT(11) NOT NULL DEFAULT '0' ,
  CHANGE COLUMN `post_dislikes` `post_dislikes` INT(11) NOT NULL DEFAULT '0' ;

CREATE TABLE IF NOT EXISTS `_menu` (
  `menu_id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `menu_alias` varchar(100) NOT NULL,
  `menu_name` varchar(100) NOT NULL,
  `menu_icon` varchar(25) NOT NULL,
  `menu_order` mediumint(5) NOT NULL DEFAULT '0',
  `menu_validate` varchar(100) NOT NULL,
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB;

INSERT INTO `_menu` (`menu_id`, `menu_alias`, `menu_name`, `menu_icon`, `menu_order`, `menu_validate`) VALUES
(1, 'today', 'TODAY', 'star', 10, 'member'),
(2, 'artists', 'ARTISTS', 'fire', 20, ''),
(3, 'events', 'EVENTS', 'camera', 30, ''),
(4, 'board', 'BOARD', 'th-list', 40, ''),
(5, 'broadcast', 'BROADCAST', 'volume-up', 50, ''),
(6, 'community', 'COMMUNITY', 'th', 60, ''),
(7, 'acp', 'ACP', 'share', 70, 'acp');

ALTER TABLE `_forum_topics`
  ADD COLUMN `topic_active` TINYINT(1) NULL DEFAULT '0' AFTER `topic_last_post_id`;

UPDATE _forum_topics SET topic_active = 1 WHERE topic_active = 0;

ALTER TABLE `_events` CHANGE COLUMN `text` `text` TEXT NULL;

ALTER TABLE `_members` CHANGE COLUMN `user_posts` `user_posts` INT(11) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `_forums` ADD COLUMN `forum_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `cat_id`;

ALTER TABLE `_forum_posts` ADD COLUMN `post_active` TINYINT(1) NOT NULL DEFAULT '1' AFTER `post_id`;

SET @script := "
  var $database := 'me_republicarock';
  foreach ($table, $schema, $engine: table in :$database)
  {
    ALTER TABLE :$schema.:$table ENGINE=InnoDB;
    ALTER TABLE :$schema.:$table CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
  }
";
CALL common_schema.run(@script);