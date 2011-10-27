RENAME TABLE `#__thm_organizer_plantype` TO  `#__thm_organizer_plantypes`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_display_behaviours` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `behaviour` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE`jos_thm_organizer_monitors`
ADD `display` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
ADD `interval` INT(1) UNSIGNED NOT NULL DEFAULT'0' COMMENT 'the time interval in minutes between context switches',
ADD `content` VARCHAR(256) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed'
ADD`content_path` VARCHAR( 256) DEFAULT NULL COMMENT'the path to the content to be displayed',
CHANGE `monitorID` `monitorID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'references id of rooms table',
ADD INDEX (`display`);

INSERT IGNORE INTO `#__thm_organizer_display_behaviours` (`id`, `behaviour`) VALUES
(1, 'COM_THM_ORGANIZER_MON_SCHEDULE'),
(2, 'COM_THM_ORGANIZER_MON_MIXED'),
(3, 'COM_THM_ORGANIZER_MON_CONTENT');