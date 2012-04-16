ALTER TABLE `#__thm_organizer_rooms` DROP `capacity`;
ALTER TABLE `#__thm_organizer_rooms` DROP INDEX `manager`;
ALTER TABLE `#__thm_organizer_rooms` DROP `manager`;
ALTER TABLE `#__thm_organizer_rooms` DROP `floor`;

RENAME TABLE `#__thm_organizer_application_settings` TO  `#__thm_organizer_settings` ;

ALTER TABLE `#__thm_organizer_monitors`
ADD `display` INT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'the display behaviour of the monitor',
ADD `interval` INT(1) UNSIGNED NOT NULL DEFAULT'1' COMMENT 'the time interval in minutes between context switches',
ADD `content` VARCHAR(256) DEFAULT NULL COMMENT 'the filename of the resource to the optional resource to be displayed',
ADD`content_meta` TEXT DEFAULT NULL COMMENT'a json string containing optional file extension specific parameters',
CHANGE `roomID` `roomID` INT(11) UNSIGNED NOT NULL COMMENT 'references id of rooms table',
ADD INDEX (`display`);

TRUNCATE TABLE #__thm_organizer_virtual_schedules;
TRUNCATE TABLE #__thm_organizer_virtual_schedules_elements;

ALTER TABLE #__thm_organizer_virtual_schedules CHANGE vid id int(11) NOT NULL;
ALTER TABLE #__thm_organizer_virtual_schedules ADD PRIMARY KEY (id);
ALTER TABLE #__thm_organizer_virtual_schedules AUTO_INCREMENT=1;
ALTER TABLE #__thm_organizer_virtual_schedules CHANGE vname name varchar(50);
ALTER TABLE #__thm_organizer_virtual_schedules CHANGE vtype type varchar(50);
ALTER TABLE #__thm_organizer_virtual_schedules CHANGE vresponsible responsible varchar(50);
ALTER TABLE #__thm_organizer_virtual_schedules DROP unittype;
ALTER TABLE #__thm_organizer_virtual_schedules CHANGE department departmentID int(11);
ALTER TABLE #__thm_organizer_virtual_schedules CHANGE sid semesterID int(11);

ALTER TABLE #__thm_organizer_virtual_schedules_elements CHANGE vid virtualID int(11);
ALTER TABLE #__thm_organizer_virtual_schedules_elements CHANGE eid elementID int(11);
ALTER TABLE #__thm_organizer_virtual_schedules_elements CHANGE sid semesterID int(11);