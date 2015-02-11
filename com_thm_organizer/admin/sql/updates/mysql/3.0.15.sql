DROP TABLE IF EXISTS `#__thm_organizer_users`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
  `id` INT ( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT ( 11 ) NOT NULL,
  `short_name` VARCHAR ( 50 ) NOT NULL,
  `name` VARCHAR ( 255 ) NOT NULL,
  KEY ( `id` ),
  UNIQUE ( `short_name` ),
  UNIQUE ( `name` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;