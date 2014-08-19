CREATE TABLE IF NOT EXISTS `#__thm_organizer_users` (
  `id` INT ( 11 ) UNSIGNED NOT NULL,
  `program_manager` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  `planner` TINYINT ( 1 ) NOT NULL DEFAULT '0',
  PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;