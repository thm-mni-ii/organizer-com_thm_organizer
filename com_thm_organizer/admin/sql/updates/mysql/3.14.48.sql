# new tables and tables with self contained changes

# region assets
UPDATE `v7ocf_assets` AS a1
SET a1.`lft` = a1.`lft` - 2, a1.`rgt` = a1.`rgt` - 2
WHERE a1.`lft` > (SELECT a2.`lft`
                  FROM (SELECT *
                        FROM `v7ocf_assets`) AS a2
                  WHERE a2.`id` = 102);

DELETE
FROM `v7ocf_assets`
WHERE `id` = 102;

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.5'
WHERE `name` = 'com_thm_organizer.department.6';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.6'
WHERE `name` = 'com_thm_organizer.department.14';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.21'
WHERE `name` = 'com_thm_organizer.department.8';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.50'
WHERE `name` = 'com_thm_organizer.department.19';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.51'
WHERE `name` = 'com_thm_organizer.department.12';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.52'
WHERE `name` = 'com_thm_organizer.department.11';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.22'
WHERE `name` = 'com_thm_organizer.department.13';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.11'
WHERE `name` = 'com_thm_organizer.department.15';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.12'
WHERE `name` = 'com_thm_organizer.department.16';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.13'
WHERE `name` = 'com_thm_organizer.department.17';

UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.14'
WHERE `name` = 'com_thm_organizer.department.18';
# endregion

# region blocks
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_blocks` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `date`      DATE DEFAULT NULL,
    `startTime` TIME DEFAULT NULL,
    `endTime`   TIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `date` (`date`),
    INDEX `startTime` (`startTime`),
    INDEX `endTime` (`endTime`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `v7ocf_thm_organizer_blocks` (`date`, `startTime`, `endTime`)
SELECT DISTINCT `schedule_date`, `startTime`, `endTime`
FROM `v7ocf_thm_organizer_calendar`;
# endregion

# region buildings
ALTER TABLE `v7ocf_thm_organizer_buildings` DROP FOREIGN KEY `building_campusID_fk`;

ALTER TABLE `v7ocf_thm_organizer_buildings`
    ADD CONSTRAINT `buildings_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `v7ocf_thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region campuses
ALTER TABLE `v7ocf_thm_organizer_campuses`
    DROP FOREIGN KEY `campus_gridID_fk`,
    DROP INDEX `campus_gridID_fk`;

ALTER TABLE `v7ocf_thm_organizer_campuses`
    MODIFY `isCity` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    ADD INDEX `gridID` (`gridID`);

ALTER TABLE `v7ocf_thm_organizer_campuses`
    ADD CONSTRAINT `campuses_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `v7ocf_thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
#endregion

# region colors
ALTER TABLE `v7ocf_thm_organizer_colors`
    MODIFY `name_en` VARCHAR(60) NOT NULL AFTER `name_de`;
# endregion

# region departments
ALTER TABLE `v7ocf_thm_organizer_departments`
    DROP INDEX `short_name`,
    DROP INDEX `name`,
    DROP INDEX `short_name_en`;

ALTER TABLE `v7ocf_thm_organizer_departments`
    CHANGE `short_name_de` `shortName_de` VARCHAR(50) NOT NULL,
    CHANGE `short_name_en` `shortName_en` VARCHAR(50) NOT NULL AFTER `shortName_de`,
    ADD PRIMARY KEY (`id`),
    ADD COLUMN contactType TINYINT(1) UNSIGNED DEFAULT 0,
    ADD COLUMN contactID INT(11) DEFAULT NULL,
    ADD COLUMN contactEmail VARCHAR(100) DEFAULT NULL,
    ADD INDEX `contactID` (`contactID`),
    ADD CONSTRAINT `shortName_de` UNIQUE (`shortName_de`),
    ADD CONSTRAINT `shortName_en` UNIQUE (`shortName_en`),
    ADD CONSTRAINT `name_de` UNIQUE (`name_de`);

ALTER TABLE `v7ocf_thm_organizer_departments`
    ADD CONSTRAINT `departments_contactID_fk` FOREIGN KEY (`contactID`) REFERENCES `v7ocf_users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 5
WHERE `id` = 6;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 6
WHERE `id` = 14;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 21
WHERE `id` = 8;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 50
WHERE `id` = 19;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 51
WHERE `id` = 12;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 52
WHERE `id` = 11;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 22
WHERE `id` = 13;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 11
WHERE `id` = 15;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 12
WHERE `id` = 16;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 13
WHERE `id` = 17;

UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 14
WHERE `id` = 18;

ALTER TABLE `v7ocf_thm_organizer_departments` AUTO_INCREMENT = 53;
# endregion

# region extensions
UPDATE `v7ocf_extensions`
SET `params` = replace(`params`, '_refresh', 'Refresh')
WHERE `name` = 'THM_ORGANIZER';
# endregion

# region fields
ALTER TABLE `v7ocf_thm_organizer_fields`
    DROP FOREIGN KEY `fields_colorid_fk`,
    DROP INDEX `gpuntisID`;

ALTER TABLE `v7ocf_thm_organizer_fields`
    CHANGE `field_de` `name_de` VARCHAR(60) NOT NULL AFTER `colorID`,
    CHANGE `field_en` `name_en` VARCHAR(60) NOT NULL AFTER `name_de`,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`),
    ADD CONSTRAINT `name_de` UNIQUE (`name_de`),
    ADD CONSTRAINT `name_en` UNIQUE (`name_en`);

ALTER TABLE `v7ocf_thm_organizer_fields`
    ADD CONSTRAINT `fields_colorID_fk` FOREIGN KEY (`colorID`) REFERENCES `v7ocf_thm_organizer_colors` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region frequencies
ALTER TABLE `v7ocf_thm_organizer_frequencies`
    CHANGE `frequency_de` `name_de` VARCHAR(45) NOT NULL,
    CHANGE `frequency_en` `name_en` VARCHAR(45) NOT NULL,
    ADD CONSTRAINT `name_de` UNIQUE (`name_de`),
    ADD CONSTRAINT `name_en` UNIQUE (`name_en`);
# endregion

#region grids
ALTER TABLE `v7ocf_thm_organizer_grids` DROP INDEX `gpuntisID`;

ALTER TABLE `v7ocf_thm_organizer_grids`
    MODIFY `defaultGrid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);
#endregion

# region holidays
CREATE TABLE `v7ocf_thm_organizer_holidays` (
    `id`        INT(11)             NOT NULL AUTO_INCREMENT,
    `name_de`   VARCHAR(50)         NOT NULL,
    `name_en`   VARCHAR(50)         NOT NULL,
    `startDate` DATE                NOT NULL,
    `endDate`   DATE                NOT NULL,
    `type`      TINYINT(1) UNSIGNED NOT NULL DEFAULT 3
        COMMENT 'The impact of the holiday on the planning process. Possible values: 1 - Automatic, 2 - Manual, 3 - Unplannable',
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `v7ocf_thm_organizer_holidays` (`id`, `name_de`, `name_en`, `startDate`, `endDate`, `type`)
VALUES (1, 'Christi Himmelfahrt', 'Ascension Day', '2019-05-30', '2019-05-30', 3),
       (2, 'Weihnachten', 'Christmas Day ', '2019-12-25', '2019-12-26', 3),
       (3, 'Tag der Deutschen Einheit', 'Day of German Unity', '2019-10-03', '2019-10-03', 3),
       (4, 'Ostermontag', 'Easter Monday', '2019-04-22', '2019-04-22', 3),
       (5, 'Karfreitag', 'Good Friday', '2019-04-19', '2019-04-19', 3),
       (6, 'Tag der Arbeit', 'May Day', '2019-05-01', '2019-05-01', 3),
       (7, 'Neujahrstag', 'New Year\'s Day', '2019-01-01', '2019-01-01', 3),
       (8, 'Pfingstmontag', 'Whit Monday', '2019-06-10', '2019-06-10', 3),
       (9, 'Fronleichnam', 'Corpus Christi', '2019-06-20', '2019-06-20', 3),
       (10, 'Neujahrstag', 'New Year\'s Day', '2020-01-01', '2020-01-01', 3),
       (11, 'Karfreitag', 'Good Friday', '2020-04-10', '2020-04-10', 3),
       (12, 'Ostermontag', 'Easter Monday', '2020-04-13', '2020-04-13', 3),
       (13, 'Tag der Arbeit', 'May Day', '2020-05-01', '2020-05-01', 3),
       (14, 'Christi Himmelfahrt', 'Ascension Day', '2020-05-21', '2020-05-21', 3),
       (15, 'Pfingstmontag', 'Whit Monday', '2020-06-01', '2020-06-01', 3),
       (16, 'Fronleichnam', 'Corpus Christi', '2020-06-11', '2020-06-11', 3),
       (17, 'Tag der Deutschen Einheit', 'Day of German Unity', '2020-10-03', '2020-10-03', 3),
       (18, 'Weihnachten', 'Christmas Day', '2020-12-25', '2020-12-27', 3),
       (19, 'Neujahrstag', 'New Year\'s Day', '2021-01-01', '2021-01-01', 3),
       (20, 'Karfreitag', 'Good Friday', '2021-04-02', '2021-04-02', 3),
       (21, 'Ostermontag', 'Easter Monday', '2021-04-05', '2021-04-05', 3),
       (22, 'Tag der Arbeit', 'May Day', '2021-05-01', '2021-05-01', 3),
       (23, 'Christi Himmelfahrt', 'Ascension Day', '2021-05-13', '2021-05-13', 3),
       (24, 'Pfingstmontag', 'Whit Monday', '2021-05-24', '2021-05-24', 3),
       (25, 'Fronleichnam', 'Corpus Christi', '2021-06-03', '2021-06-03', 3),
       (26, 'Weihnachten', 'Christmas Day', '2021-12-25', '2021-12-26', 3),
       (27, 'Tag der Deutschen Einheit', 'Day of German Unity', '2021-10-03', '2021-10-03', 3),
       (28, 'Tag der Deutschen Einheit', 'Day of German Unity', '2022-10-03', '2022-10-03', 3),
       (29, 'Neujahrstag', 'New Year\'s Day', '2022-01-01', '2022-01-01', 3),
       (30, 'Karfreitag', 'Good Friday', '2022-04-15', '2022-04-15', 3),
       (31, 'Ostermontag', 'Easter Monday', '2022-04-18', '2022-04-18', 3),
       (32, 'Tag der Arbeit', 'May Day', '2022-05-01', '2022-05-01', 3),
       (33, 'Christi Himmelfahrt', 'Ascension Day', '2022-05-26', '2022-05-26', 3),
       (34, 'Pfingstmontag', 'Whit Monday', '2022-06-06', '2022-06-06', 3),
       (35, 'Fronleichnam', 'Corpus Christi', '2022-06-16', '2022-06-16', 3),
       (36, 'Weihnachten', 'Christmas Day', '2022-12-25', '2022-12-26', 3),
       (37, 'Neujahrstag', 'New Year\'s Day', '2023-01-01', '2023-01-01', 3),
       (38, 'Karfreitag', 'Good Friday', '2023-04-07', '2023-04-07', 3),
       (39, 'Ostermontag', 'Easter Monday', '2023-04-10', '2023-04-10', 3),
       (40, 'Tag der Arbeit', 'May Day', '2023-05-01', '2023-05-01', 3),
       (41, 'Christi Himmelfahrt', 'Ascension Day', '2023-05-18', '2023-05-18', 3),
       (42, 'Pfingstmontag', 'Whit Monday', '2023-05-29', '2023-05-29', 3),
       (43, 'Fronleichnam', 'Corpus Christi', '2023-06-08', '2023-06-08', 3),
       (44, 'Tag der Deutschen Einheit', 'Day of German Unity', '2023-10-03', '2023-10-03', 3),
       (45, 'Weihnachten', 'Christmas Day', '2023-12-25', '2023-12-26', 3),
       (46, 'Neujahrstag', 'New Year\'s Day', '2024-01-01', '2024-01-01', 3),
       (47, 'Karfreitag', 'Good Friday', '2024-03-29', '2024-03-29', 3),
       (48, 'Ostermontag', 'Easter Monday', '2024-04-01', '2024-04-01', 3),
       (49, 'Tag der Arbeit', 'May Day', '2024-05-01', '2024-05-01', 3),
       (50, 'Christi Himmelfahrt', 'Ascension Day', '2024-05-09', '2024-05-09', 3),
       (51, 'Pfingstmontag', 'Whit Monday', '2024-05-20', '2024-05-20', 3),
       (52, 'Fronleichnam', 'Corpus Christi', '2024-05-30', '2024-05-30', 3),
       (53, 'Tag der Deutschen Einheit', 'Day of German Unity', '2024-10-03', '2024-10-03', 3),
       (54, 'Weihnachten', 'Christmas Day', '2024-12-25', '2024-12-26', 3);
# endregion

# region mappings
ALTER TABLE `v7ocf_thm_organizer_mappings`
    DROP FOREIGN KEY `mappings_parentid_fk`,
    DROP FOREIGN KEY `mappings_poolid_fk`,
    DROP FOREIGN KEY `mappings_programid_fk`,
    DROP FOREIGN KEY `mappings_subjectid_fk`;

ALTER TABLE `v7ocf_thm_organizer_mappings`
    ADD CONSTRAINT `mappings_parentID_fk` FOREIGN KEY (`parentID`) REFERENCES `v7ocf_thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `mappings_poolID_fk` FOREIGN KEY (`poolID`) REFERENCES `v7ocf_thm_organizer_pools` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `mappings_programID_fk` FOREIGN KEY (`programID`) REFERENCES `v7ocf_thm_organizer_programs` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `mappings_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `v7ocf_thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region changes to saved menu parameters => menu
UPDATE `v7ocf_menu`
SET `link`   = 'index.php?option=com_thm_organizer&view=courses',
    `params` = replace(`params`, '}', ',"onlyPrepCourses":"1"}')
WHERE `link` = 'index.php?option=com_thm_organizer&view=course_list';

UPDATE `v7ocf_menu`
SET `link`   = 'index.php?option=com_thm_organizer&view=schedule_item',
    `params` = replace(`params`, 'programIDs', 'categoryIDs'),
    `params` = replace(`params`, 'poolIDs', 'groupIDs'),
    `params` = replace(`params`, 'showPrograms', 'showCategories')
WHERE `link` = 'index.php?option=com_thm_organizer&view=schedule';

UPDATE `v7ocf_menu`
SET `link` = 'index.php?option=com_thm_organizer&view=subjects'
WHERE `link` = 'index.php?option=com_thm_organizer&view=subject_list';

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, 'showTeachers', 'showPersons');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, 'deltaDays', 'delta');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"6"', '"departmentID":"5"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"14"', '"departmentID":"6"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"8"', '"departmentID":"21"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"19"', '"departmentID":"50"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"12"', '"departmentID":"51"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"11"', '"departmentID":"52"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"13"', '"departmentID":"22"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"15"', '"departmentID":"11"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"16"', '"departmentID":"12"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"17"', '"departmentID":"13"');

UPDATE `v7ocf_menu`
SET `params` = replace(`params`, '"departmentID":"18"', '"departmentID":"14"');
# endregion

#region methods
ALTER TABLE `v7ocf_thm_organizer_methods`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);
#endregion

#region monitors
ALTER TABLE `v7ocf_thm_organizer_monitors` DROP FOREIGN KEY `monitors_roomid_fk`;

ALTER TABLE `v7ocf_thm_organizer_monitors`
    MODIFY `useDefaults` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    CHANGE `schedule_refresh` `scheduleRefresh` INT(3) UNSIGNED NOT NULL DEFAULT 60
        COMMENT 'the amount of seconds before the schedule refreshes',
    CHANGE `content_refresh` `contentRefresh` INT(3) UNSIGNED NOT NULL DEFAULT 60
        COMMENT 'the amount of time in seconds before the content refreshes';

ALTER TABLE `v7ocf_thm_organizer_monitors`
    ADD CONSTRAINT `monitors_roomID_fk` FOREIGN KEY (`roomID`) REFERENCES `v7ocf_thm_organizer_rooms` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
#endregion

#region participants
ALTER TABLE `v7ocf_thm_organizer_participants`
    DROP FOREIGN KEY `participants_programid_fk`,
    DROP FOREIGN KEY `participants_userid_fk`,
    DROP INDEX `participants_programid_fk`;

ALTER TABLE `v7ocf_thm_organizer_participants`
    CHANGE `zip_code` `zipCode` INT(11) NOT NULL DEFAULT 0,
    MODIFY `programID` INT(11) UNSIGNED DEFAULT NULL,
    ADD COLUMN `notify` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    ADD INDEX `programID` (`programID`);

ALTER TABLE `v7ocf_thm_organizer_participants`
    ADD CONSTRAINT `participants_programID_fk` FOREIGN KEY (`programID`) REFERENCES `v7ocf_thm_organizer_programs` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `participants_userID_fk` FOREIGN KEY (`id`) REFERENCES `v7ocf_users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

#region prerequisites
ALTER TABLE `v7ocf_thm_organizer_prerequisites`
    DROP FOREIGN KEY `prerequisites_prerequisites_fk`,
    DROP FOREIGN KEY `prerequisites_subjectid_fk`,
    DROP INDEX `entry`,
    DROP INDEX `prerequisites_prerequisites_fk`;

ALTER TABLE `v7ocf_thm_organizer_prerequisites`
    CHANGE `prerequisite` `prerequisiteID` INT(11) UNSIGNED NOT NULL,
    ADD CONSTRAINT `entry` UNIQUE (`prerequisiteID`, `subjectID`),
    ADD INDEX `prerequisiteID` (`prerequisiteID`),
    ADD INDEX `subjectID` (`subjectID`);

ALTER TABLE `v7ocf_thm_organizer_prerequisites`
    ADD CONSTRAINT `prerequisites_prerequisiteID_fk` FOREIGN KEY (`prerequisiteID`) REFERENCES `v7ocf_thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `prerequisites_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `v7ocf_thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

# region roles
CREATE TABLE `v7ocf_thm_organizer_roles` (
    `id`              TINYINT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
    `abbreviation_de` VARCHAR(10)         NOT NULL,
    `abbreviation_en` VARCHAR(10)         NOT NULL,
    `name_de`         VARCHAR(50)         NOT NULL,
    `name_en`         VARCHAR(50)         NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `name_de` (`name_de`),
    UNIQUE INDEX `name_en` (`name_en`),
    UNIQUE INDEX `abbreviation_de` (`abbreviation_de`),
    UNIQUE INDEX `abbreviation_en` (`abbreviation_en`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `v7ocf_thm_organizer_roles` (`id`, `name_de`, `name_en`, `abbreviation_de`, `abbreviation_en`)
VALUES (1, 'Dozent', 'Teacher', 'DOZ', 'TCH'),
       (2, 'Tutor', 'Tutor', 'TUT', 'TUT'),
       (3, 'Aufsicht', 'Supervisor', 'AFS', 'SPR'),
       (4, 'Referent', 'Speaker', 'REF', 'SPK');

# endregion