# region assets
# there is an unused duplicate entry for the health department
UPDATE `v7ocf_assets` AS a1
SET a1.`lft` = a1.`lft` - 2, a1.`rgt` = a1.`rgt` - 2
WHERE a1.`lft` > (SELECT a2.`lft`
                  FROM (SELECT *
                        FROM `v7ocf_assets`) AS a2
                  WHERE a2.`id` = 102);

DELETE
FROM `v7ocf_assets`
WHERE `id` = 102;

# GES - using the number normally associated with department mni
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.5'
WHERE `name` = 'com_thm_organizer.department.6';

# MNI - using the number normally associated with department wi
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.6'
WHERE `name` = 'com_thm_organizer.department.14';

# MuK
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.21'
WHERE `name` = 'com_thm_organizer.department.8';

# Friedberg - no real number just adding space for any future developments
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.50'
WHERE `name` = 'com_thm_organizer.department.19';

# Gießen - no real number, using the number normally associated with department m
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.51'
WHERE `name` = 'com_thm_organizer.department.12';

# STK - no real number, using the number normally associated with department iem
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.52'
WHERE `name` = 'com_thm_organizer.department.11';

# ZDH - no real number, using the number normally associated with department mnd
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.22'
WHERE `name` = 'com_thm_organizer.department.13';

# IEM
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.11'
WHERE `name` = 'com_thm_organizer.department.15';

# M
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.12'
WHERE `name` = 'com_thm_organizer.department.16';

# MND
UPDATE `v7ocf_assets`
SET `name`= 'com_thm_organizer.department.13'
WHERE `name` = 'com_thm_organizer.department.17';

# WI
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
# fk name missing 's'
ALTER TABLE `v7ocf_thm_organizer_buildings` DROP FOREIGN KEY `building_campusID_fk`;

ALTER TABLE `v7ocf_thm_organizer_buildings`
    ADD CONSTRAINT `buildings_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `v7ocf_thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region campuses
# no explicit index had been set
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

# region plan programs => categories
# index not explicitly set, table name changed, indexed column changed
ALTER TABLE `v7ocf_thm_organizer_plan_programs`
    DROP FOREIGN KEY `plan_programs_programid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_programs_programid_fk`;

RENAME TABLE `v7ocf_thm_organizer_plan_programs` TO `v7ocf_thm_organizer_categories`;

ALTER TABLE `v7ocf_thm_organizer_categories`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`),
    ADD INDEX `programID` (`programID`);

ALTER TABLE `v7ocf_thm_organizer_categories`
    ADD CONSTRAINT `categories_programID_fk` FOREIGN KEY (`programID`) REFERENCES `v7ocf_thm_organizer_programs` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region departments
# primary key was missing?
ALTER TABLE `v7ocf_thm_organizer_departments`
    ADD PRIMARY KEY (`id`),
    ADD COLUMN contactType TINYINT(1) UNSIGNED DEFAULT 0,
    ADD COLUMN contactID INT(11) DEFAULT NULL,
    ADD COLUMN contactEmail VARCHAR(100) DEFAULT NULL,
    ADD INDEX `contactID` (`contactID`);

ALTER TABLE `v7ocf_thm_organizer_departments`
    ADD CONSTRAINT `departments_contactID_fk` FOREIGN KEY (`contactID`) REFERENCES `v7ocf_users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

# change the ids to match the real numbering of the departments if existent
# GES - using the number normally associated with department mni
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 5
WHERE `id` = 6;

# MNI - using the number normally associated with department wi
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 6
WHERE `id` = 14;

# MuK
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 21
WHERE `id` = 8;

# Friedberg - no real number just adding space for any future developments
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 50
WHERE `id` = 19;

# Gießen - no real number, using the number normally associated with department m
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 51
WHERE `id` = 12;

# STK - no real number, using the number normally associated with department iem
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 52
WHERE `id` = 11;

# ZDH - no real number, using the number normally associated with department mnd
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 22
WHERE `id` = 13;

# IEM
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 11
WHERE `id` = 15;

# M
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 12
WHERE `id` = 16;

# MND
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 13
WHERE `id` = 17;

# WI
UPDATE `v7ocf_thm_organizer_departments`
SET `id`= 14
WHERE `id` = 18;

ALTER TABLE `v7ocf_thm_organizer_departments` AUTO_INCREMENT = 53;
# endregion

# region plan subjects => events
# table name changed, index not explicitly set, indexed column changed
ALTER TABLE `v7ocf_thm_organizer_plan_subjects`
    DROP FOREIGN KEY `plan_subjects_fieldid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_subjects_fieldid_fk`,
    DROP INDEX `subjectIndex`;

RENAME TABLE `v7ocf_thm_organizer_plan_subjects` TO `v7ocf_thm_organizer_events`;

# adds language support, adds event declaration / documentation support
# this untis id is only unique in a department context
ALTER TABLE `v7ocf_thm_organizer_events`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL AFTER `id`,
    ADD COLUMN `departmentID` INT(11) UNSIGNED NOT NULL AFTER `untisID`,
    MODIFY `fieldID` INT(11) UNSIGNED DEFAULT NULL AFTER `departmentID`,
    CHANGE `name` `name_de` VARCHAR(100) NOT NULL AFTER `fieldID`,
    ADD COLUMN `name_en` VARCHAR(100) NOT NULL AFTER `name_de`,
    ADD COLUMN `description_de` TEXT ,
    ADD COLUMN `description_en` TEXT,
    ADD COLUMN `preparatory` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN `campusID` INT(11) UNSIGNED DEFAULT NULL,
    ADD COLUMN `deadline` INT(2) UNSIGNED DEFAULT 0
        COMMENT 'The deadline in days for registration before the course starts.',
    ADD COLUMN `fee` INT(3) UNSIGNED DEFAULT 0,
    ADD COLUMN `maxParticipants` INT(4) UNSIGNED DEFAULT 1000,
    ADD COLUMN `registrationType` INT(1) UNSIGNED DEFAULT NULL
        COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.',
    ADD INDEX `campusID` (`campusID`),
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `fieldID` (`fieldID`),
    ADD INDEX `untisID` (`untisID`);

# set default values
UPDATE `v7ocf_thm_organizer_events`
SET `name_en` = `name_de`;

# set explicit department ids
UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 1
WHERE `subjectIndex` LIKE 'BAU_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 2
WHERE `subjectIndex` LIKE '%EI_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 3
WHERE `subjectIndex` LIKE 'ME_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 4
WHERE `subjectIndex` LIKE 'LSE_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 5
WHERE `subjectIndex` LIKE 'GES_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 5
WHERE `subjectIndex` LIKE '%GESUNDHEIT_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 6
WHERE `subjectIndex` LIKE 'MNI_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 7
WHERE `subjectIndex` LIKE 'W_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 21
WHERE `subjectIndex` LIKE 'MUK_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 22
WHERE `subjectIndex` LIKE 'ZDH_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 50
WHERE `subjectIndex` LIKE 'FB_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 51
WHERE `subjectIndex` LIKE 'THM_%';

UPDATE `v7ocf_thm_organizer_events`
SET `departmentID` = 52
WHERE `subjectIndex` LIKE 'STK_%';

# indexing through aggregation changed to multiple columns
ALTER TABLE `v7ocf_thm_organizer_events`
    DROP COLUMN `subjectIndex`,
    ADD CONSTRAINT `entry` UNIQUE (`untisID`, `departmentID`);

UPDATE `v7ocf_thm_organizer_events` AS e
SET `description_de` = (SELECT DISTINCT `description_de`
                        FROM `v7ocf_thm_organizer_subjects` AS s
                                 INNER JOIN `v7ocf_thm_organizer_subject_mappings` AS sm ON sm.`subjectID` = s.`id`
                        WHERE s.`is_prep_course` = 1 AND sm.`plan_subjectID` = e.`id`);

UPDATE `v7ocf_thm_organizer_events` AS e
SET `description_en` = (SELECT DISTINCT `description_en`
                        FROM `v7ocf_thm_organizer_subjects` AS s
                                 INNER JOIN `v7ocf_thm_organizer_subject_mappings` AS sm ON sm.`subjectID` = s.`id`
                        WHERE s.`is_prep_course` = 1 AND sm.`plan_subjectID` = e.`id`);

UPDATE `v7ocf_thm_organizer_events` AS e
SET `preparatory` = IFNULL((SELECT DISTINCT `is_prep_course`
                            FROM `v7ocf_thm_organizer_subjects` AS s
                                     INNER JOIN `v7ocf_thm_organizer_subject_mappings` AS sm ON sm.`subjectID` = s.`id`
                            WHERE sm.`plan_subjectID` = e.`id`), 0);

UPDATE `v7ocf_thm_organizer_events` AS e
SET `campusID` = (SELECT DISTINCT `campusID`
                  FROM `v7ocf_thm_organizer_subjects` AS s
                           INNER JOIN `v7ocf_thm_organizer_subject_mappings` AS sm ON sm.`subjectID` = s.`id`
                  WHERE sm.`plan_subjectID` = e.`id`);

UPDATE `v7ocf_thm_organizer_events` AS e
SET `deadline` = 5;

UPDATE `v7ocf_thm_organizer_events` AS e
SET `fee` = (SELECT MAX(DISTINCT `fee`)
             FROM `v7ocf_thm_organizer_lessons` AS l
                      INNER JOIN `v7ocf_thm_organizer_lesson_subjects` AS ls ON ls.`lessonID` = l.`id`
             WHERE ls.`subjectID` = e.`id`);

UPDATE `v7ocf_thm_organizer_events` AS e
SET `maxParticipants` = (SELECT MAX(DISTINCT `max_participants`)
                         FROM `v7ocf_thm_organizer_subjects` AS s
                                  INNER JOIN `v7ocf_thm_organizer_subject_mappings` AS sm ON sm.`subjectID` = s.`id`
                         WHERE s.`is_prep_course` = 1 AND sm.`plan_subjectID` = e.`id`);

UPDATE `v7ocf_thm_organizer_events` AS e
SET `registrationType` = (SELECT MAX(DISTINCT `registration_type`)
                          FROM `v7ocf_thm_organizer_subjects` AS s
                                   INNER JOIN `v7ocf_thm_organizer_subject_mappings` AS sm ON sm.`subjectID` = s.`id`
                          WHERE s.`is_prep_course` = 1 AND sm.`plan_subjectID` = e.`id`);

ALTER TABLE `v7ocf_thm_organizer_events`
    ADD CONSTRAINT `events_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `v7ocf_thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `events_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `events_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

#region fields
# non-standard fk syntax, indexed column changed
ALTER TABLE `v7ocf_thm_organizer_fields`
    DROP FOREIGN KEY `fields_colorid_fk`,
    DROP INDEX `gpuntisID`;

ALTER TABLE `v7ocf_thm_organizer_fields`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `v7ocf_thm_organizer_fields`
    ADD CONSTRAINT `fields_colorID_fk` FOREIGN KEY (`colorID`) REFERENCES `v7ocf_thm_organizer_colors` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
#endregion

#region grids
# indexed column changed
ALTER TABLE `v7ocf_thm_organizer_grids` DROP INDEX `gpuntisID`;

ALTER TABLE `v7ocf_thm_organizer_grids`
    MODIFY `defaultGrid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);
#endregion

# region plan pools => groups
# index not explicitly set, non-standard fk syntax, table name changed, indexed column changed, table name changed
ALTER TABLE `v7ocf_thm_organizer_plan_pools`
    DROP FOREIGN KEY `plan_pools_fieldid_fk`,
    DROP FOREIGN KEY `plan_pools_gridid_fk`,
    DROP FOREIGN KEY `plan_pools_poolid_fk`,
    DROP FOREIGN KEY `plan_pools_programid_fk`,
    DROP INDEX `dbID`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_pools_gridid_fk`,
    DROP INDEX `programID`;

RENAME TABLE `v7ocf_thm_organizer_plan_pools` TO `v7ocf_thm_organizer_groups`;

ALTER TABLE `v7ocf_thm_organizer_groups`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    CHANGE `programID` `categoryID` INT(11) UNSIGNED NOT NULL,
    ADD CONSTRAINT `entry` UNIQUE (`untisID`, `categoryID`),
    ADD INDEX `categoryID` (`categoryID`),
    ADD INDEX `gridID` (`gridID`),
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `v7ocf_thm_organizer_groups`
    ADD CONSTRAINT `groups_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `v7ocf_thm_organizer_categories` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `v7ocf_thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_poolID_fk` FOREIGN KEY (`poolID`) REFERENCES `v7ocf_thm_organizer_pools` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

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
# non-standard fk syntax
ALTER TABLE `v7ocf_thm_organizer_monitors` DROP FOREIGN KEY `monitors_roomid_fk`;

ALTER TABLE `v7ocf_thm_organizer_monitors`
    MODIFY `useDefaults` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE `v7ocf_thm_organizer_monitors`
    ADD CONSTRAINT `monitors_roomID_fk` FOREIGN KEY (`roomID`) REFERENCES `v7ocf_thm_organizer_rooms` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
#endregion

#region participants
# no index explicitly set, non-standard fk syntax
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

# region teachers => persons
# avoid conflicts by dropping fks in advance
ALTER TABLE `v7ocf_thm_organizer_department_resources` DROP FOREIGN KEY `department_resources_teacherid_fk`;
ALTER TABLE `v7ocf_thm_organizer_lesson_teachers` DROP FOREIGN KEY `lesson_teachers_teacherid_fk`;
ALTER TABLE `v7ocf_thm_organizer_subject_teachers` DROP FOREIGN KEY `subject_teachers_teacherid_fk`;

# non-standard fk syntax, indexed column changed, table name changed
ALTER TABLE `v7ocf_thm_organizer_teachers`
    DROP FOREIGN KEY `teachers_fieldid_fk`,
    DROP INDEX `gpuntisID`;

RENAME TABLE `v7ocf_thm_organizer_teachers` TO `v7ocf_thm_organizer_persons`;

ALTER TABLE `v7ocf_thm_organizer_persons`
    MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `v7ocf_thm_organizer_persons`
    ADD CONSTRAINT `persons_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region department resources (fk: persons)
# non-standard fk syntax, referenced table names have changed
ALTER TABLE `v7ocf_thm_organizer_department_resources`
    DROP FOREIGN KEY `department_resources_departmentid_fk`,
    DROP FOREIGN KEY `department_resources_programid_fk`,
    DROP INDEX `programID`,
    DROP INDEX `teacherID`;

ALTER TABLE `v7ocf_thm_organizer_department_resources`
    CHANGE `programID` `categoryID` INT(11) UNSIGNED DEFAULT NULL,
    CHANGE `teacherID` `personID` INT(11) DEFAULT NULL,
    ADD INDEX `categoryID` (`categoryID`),
    ADD INDEX `personID` (`personID`);

ALTER TABLE `v7ocf_thm_organizer_department_resources`
    ADD CONSTRAINT `department_resources_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `v7ocf_thm_organizer_categories` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `department_resources_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `department_resources_personID_fk` FOREIGN KEY (`personID`) REFERENCES `v7ocf_thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region event coordinators (fk: persons)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_event_coordinators` (
    `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `eventID`  INT(11) UNSIGNED NOT NULL,
    `personID` INT(11)          NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `entry` UNIQUE (`eventID`, `personID`),
    INDEX `eventID` (`eventID`),
    INDEX `personID` (`personID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `v7ocf_thm_organizer_event_coordinators` (`eventID`, `personID`)
SELECT DISTINCT `plan_subjectID`, `teacherID`
FROM `v7ocf_thm_organizer_subject_teachers` AS st
         INNER JOIN `v7ocf_thm_organizer_subject_mappings` AS sm ON sm.`subjectID` = st.`subjectID`
WHERE `teacherResp` = 1;

ALTER TABLE `v7ocf_thm_organizer_event_coordinators`
    ADD CONSTRAINT `event_coordinators_eventID_fk` FOREIGN KEY (`eventID`) REFERENCES `v7ocf_thm_organizer_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `event_coordinators_personID_fk` FOREIGN KEY (`personID`) REFERENCES `v7ocf_thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region pools
# no index explicitly set, non-standard fk syntax, lsfID should be unique
ALTER TABLE `v7ocf_thm_organizer_pools`
    DROP FOREIGN KEY `pools_departmentid_fk`,
    DROP FOREIGN KEY `pools_fieldid_fk`,
    DROP INDEX `lsfID`,
    DROP INDEX `pools_departmentid_fk`;

# 0 is not a valid value and causes the unique constraint to fail
UPDATE `v7ocf_thm_organizer_pools`
SET `lsfID` = NULL
WHERE `lsfID` = 0;

# unused columns
ALTER TABLE `v7ocf_thm_organizer_pools`
    DROP COLUMN `display_type`,
    DROP COLUMN `enable_desc`,
    ADD INDEX `departmentID` (`departmentID`),
    ADD UNIQUE INDEX `lsfID` (`lsfID`);

ALTER TABLE `v7ocf_thm_organizer_pools`
    ADD CONSTRAINT `pools_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `pools_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

#region prerequisites
# no index explicitly set, non-standard fk syntax
ALTER TABLE `v7ocf_thm_organizer_prerequisites`
    DROP FOREIGN KEY `prerequisites_prerequisites_fk`,
    DROP FOREIGN KEY `prerequisites_subjectid_fk`,
    DROP INDEX `entry`,
    DROP INDEX `prerequisites_prerequisites_fk`;

# non-standard fk column name syntax
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

#region programs
# no index explicitly set, non-standard fk syntax
ALTER TABLE `v7ocf_thm_organizer_programs`
    DROP FOREIGN KEY `programs_degreeid_fk`,
    DROP FOREIGN KEY `programs_departmentid_fk`,
    DROP FOREIGN KEY `programs_fieldid_fk`,
    DROP FOREIGN KEY `programs_frequencyid_fk`,
    DROP INDEX `programs_departmentid_fk`,
    DROP INDEX `programs_frequencyid_fk`;

# deprecated programs will context-dependent not be displayed
ALTER TABLE `v7ocf_thm_organizer_programs`
    ADD COLUMN `deprecated` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `frequencyID` (`frequencyID`);

ALTER TABLE `v7ocf_thm_organizer_programs`
    ADD CONSTRAINT `programs_degreeID_fk` FOREIGN KEY (`degreeID`) REFERENCES `v7ocf_thm_organizer_degrees` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `programs_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `programs_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `programs_frequencyID_fk` FOREIGN KEY (`frequencyID`) REFERENCES `v7ocf_thm_organizer_frequencies` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
#endregion

# region room types => roomtypes
# indexed column changed
ALTER TABLE `v7ocf_thm_organizer_room_types` DROP INDEX `gpuntisID`;

RENAME TABLE `v7ocf_thm_organizer_room_types` TO `v7ocf_thm_organizer_roomtypes`;

# rooms referencing non-public room types will, context dependent, not be displayed
ALTER TABLE `v7ocf_thm_organizer_roomtypes`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD COLUMN `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

UPDATE `v7ocf_thm_organizer_roomtypes`
SET public = 0
WHERE `untisID` = 'BR';
# endregion

# region rooms
# no index explicitly set, non-standard fk syntax, indexed column changed
ALTER TABLE `v7ocf_thm_organizer_rooms`
    DROP FOREIGN KEY `room_buildingID_fk`,
    DROP FOREIGN KEY `rooms_typeid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `room_buildingID_fk`,
    DROP INDEX `typeID`;

# longname is no longer used, non-standard fk column name
ALTER TABLE `v7ocf_thm_organizer_rooms`
    DROP COLUMN `longname`,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    CHANGE `typeID` `roomtypeID` INT(11) UNSIGNED DEFAULT NULL,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`),
    ADD INDEX `buildingID` (`buildingID`),
    ADD INDEX `roomtypeID` (`roomtypeID`);

ALTER TABLE `v7ocf_thm_organizer_rooms`
    ADD CONSTRAINT `rooms_buildingID_fk` FOREIGN KEY (`buildingID`) REFERENCES `v7ocf_thm_organizer_buildings` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `rooms_roomtypeID_fk` FOREIGN KEY (`roomtypeID`) REFERENCES `v7ocf_thm_organizer_roomtypes` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region subject mappings => subject events
# no index explicitly set, non-standard fk syntax
ALTER TABLE `v7ocf_thm_organizer_subject_mappings`
    DROP FOREIGN KEY `subject_mappings_plan_subjectID_fk`,
    DROP FOREIGN KEY `subject_mappings_subjectID_fk`,
    DROP INDEX `entry`,
    DROP INDEX `subject_mappings_plan_subjectID_fk`;

RENAME TABLE `v7ocf_thm_organizer_subject_mappings` TO `v7ocf_thm_organizer_subject_events`;

ALTER TABLE `v7ocf_thm_organizer_subject_events`
    CHANGE `plan_subjectID` `eventID` INT(11) UNSIGNED NOT NULL,
    ADD CONSTRAINT `entry` UNIQUE (`subjectID`, `eventID`),
    ADD INDEX `subjectID` (`subjectID`),
    ADD INDEX `eventID` (`eventID`);

ALTER TABLE `v7ocf_thm_organizer_subject_events`
    ADD CONSTRAINT `subject_events_eventID_fk` FOREIGN KEY (`eventID`) REFERENCES `v7ocf_thm_organizer_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_events_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `v7ocf_thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region subject teachers => subject persons
# change primary key to auto-increment column, non-standard fk syntax, referenced table name changed, table name changed
ALTER TABLE `v7ocf_thm_organizer_subject_teachers`
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    DROP INDEX `id`,
    DROP FOREIGN KEY `subject_teachers_subjectid_fk`,
    DROP INDEX `teacherID`;

RENAME TABLE `v7ocf_thm_organizer_subject_teachers` TO `v7ocf_thm_organizer_subject_persons`;

ALTER TABLE `v7ocf_thm_organizer_subject_persons`
    CHANGE `teacherID` `personID` INT(11) NOT NULL,
    CHANGE `teacherResp` `responsibility` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
        COMMENT 'The person\'s responsibility for the given subject. Responsibilities are not mutually exclusive. Possible values: 1 - coordinates, 2 - teaches.',
    ADD UNIQUE INDEX `entry` (`personID`, `subjectID`, `responsibility`),
    ADD INDEX `personID` (`personID`);

ALTER TABLE `v7ocf_thm_organizer_subject_persons`
    ADD CONSTRAINT `subject_persons_personID_fk` FOREIGN KEY (`personID`) REFERENCES `v7ocf_thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_persons_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `v7ocf_thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region subjects
# index not explicitly set, non-standard fk syntax
ALTER TABLE `v7ocf_thm_organizer_subjects`
    DROP FOREIGN KEY `subject_campusID_fk`,
    DROP FOREIGN KEY `subjects_departmentid_fk`,
    DROP FOREIGN KEY `subjects_fieldid_fk`,
    DROP FOREIGN KEY `subjects_frequencyid_fk`,
    DROP INDEX `subject_campusID_fk`,
    DROP INDEX `subjects_departmentid_fk`;

# prep course information has already been moved to events
DELETE
FROM `v7ocf_thm_organizer_subjects`
WHERE `is_prep_course` = 1;

# remove course relevant columns
ALTER TABLE `v7ocf_thm_organizer_subjects`
    DROP COLUMN `campusID`,
    DROP COLUMN `is_prep_course`,
    DROP COLUMN `max_participants`,
    DROP COLUMN `registration_type`,
    ADD INDEX `departmentID` (`departmentID`);

ALTER TABLE `v7ocf_thm_organizer_subjects`
    ADD CONSTRAINT `subjects_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subjects_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subjects_frequencyID_fk` FOREIGN KEY (`frequencyID`) REFERENCES `v7ocf_thm_organizer_frequencies` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region planning periods => terms
ALTER TABLE `v7ocf_thm_organizer_planning_periods` DROP INDEX `pp_long`;

RENAME TABLE `v7ocf_thm_organizer_planning_periods` TO `v7ocf_thm_organizer_terms`;

ALTER TABLE `v7ocf_thm_organizer_terms` ADD UNIQUE INDEX `entry` (`name`, `startDate`, `endDate`);
# endregion

# region plan pool publishing => group publishing (fk: terms)
# index not explicitly set, non-standard fk syntax, table name changed
ALTER TABLE `v7ocf_thm_organizer_plan_pool_publishing`
    DROP FOREIGN KEY `plan_pool_publishing_planningperiodid_fk`,
    DROP FOREIGN KEY `plan_pool_publishing_planpoolid_fk`,
    DROP INDEX `entry`,
    DROP INDEX `plan_pool_publishing_planningperiodid_fk`;

RENAME TABLE `v7ocf_thm_organizer_plan_pool_publishing` TO `v7ocf_thm_organizer_group_publishing`;

ALTER TABLE `v7ocf_thm_organizer_group_publishing`
    CHANGE `planPoolID` `groupID` INT(11) UNSIGNED NOT NULL,
    CHANGE `planningPeriodID` `termID` INT(11) UNSIGNED DEFAULT NULL,
    MODIFY `published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    ADD CONSTRAINT `entry` UNIQUE (`groupID`, `termID`),
    ADD INDEX `groupID` (`groupID`),
    ADD INDEX `termID` (`termID`);

ALTER TABLE `v7ocf_thm_organizer_group_publishing`
    ADD CONSTRAINT `group_publishing_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `v7ocf_thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `group_publishing_termID_fk` FOREIGN KEY (`termID`) REFERENCES `v7ocf_thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

#region schedules (fk: terms)
# non-standard fk syntax, referenced table name changed
ALTER TABLE `v7ocf_thm_organizer_schedules`
    DROP FOREIGN KEY `schedules_departmentid_fk`,
    DROP FOREIGN KEY `schedules_planningperiodid_fk`,
    DROP FOREIGN KEY `schedules_userid_fk`,
    DROP INDEX `schedules_departmentid_fk`,
    DROP INDEX `schedules_planningperiodid_fk`;

# column migrated will be dropped in the migration process
ALTER TABLE `v7ocf_thm_organizer_schedules`
    MODIFY `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    MODIFY `departmentID` INT(11) UNSIGNED NOT NULL,
    CHANGE `planningPeriodID` `termID` INT(11) UNSIGNED NOT NULL,
    ADD COLUMN `migrated` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `termID` (`termID`);

ALTER TABLE `v7ocf_thm_organizer_schedules`
    ADD CONSTRAINT `schedules_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `schedules_termID_fk` FOREIGN KEY (`termID`) REFERENCES `v7ocf_thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `schedules_userID_fk` FOREIGN KEY (`userID`) REFERENCES `v7ocf_users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

UPDATE `v7ocf_thm_organizer_schedules`
SET `schedule` = replace(`schedule`, 'planningPeriodID', 'termID');
#endregion

# region runs
CREATE TABLE `v7ocf_thm_organizer_runs` (
    `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_de` VARCHAR(50)      NOT NULL,
    `name_en` VARCHAR(50)      NOT NULL,
    `termID`  INT(11) UNSIGNED NOT NULL,
    `period`  TEXT             NOT NULL
        COMMENT 'Period contains the start date and end date of lessons which are saved in JSON string.',
    PRIMARY KEY (`id`),
    INDEX `termID` (`termID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `v7ocf_thm_organizer_runs`
    ADD CONSTRAINT `runs_termID_fk` FOREIGN KEY (`termID`) REFERENCES `v7ocf_thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

INSERT INTO `v7ocf_thm_organizer_runs` (`id`, `name_de`, `name_en`, `termID`, `period`)
VALUES (1, 'Sommersemester', 'Summer Semester', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-04-06\",\"endDate\":\"2020-04-09\"},\"2\":{\"startDate\":\"2020-04-14\",\"endDate\":\"2020-04-17\"},\"3\":{\"startDate\":\"2020-04-20\",\"endDate\":\"2020-04-24\"},\"4\":{\"startDate\":\"2020-04-27\",\"endDate\":\"2020-04-30\"},\"5\":{\"startDate\":\"2020-05-04\",\"endDate\":\"2020-05-08\"},\"6\":{\"startDate\":\"2020-05-11\",\"endDate\":\"2020-05-15\"},\"7\":{\"startDate\":\"2020-05-18\",\"endDate\":\"2020-05-20\"},\"8\":{\"startDate\":\"2020-05-25\",\"endDate\":\"2020-05-29\"},\"9\":{\"startDate\":\"2020-06-08\",\"endDate\":\"2020-06-10\"},\"10\":{\"startDate\":\"2020-06-15\",\"endDate\":\"2020-06-19\"},\"11\":{\"startDate\":\"2020-06-22\",\"endDate\":\"2020-06-26\"},\"12\":{\"startDate\":\"2020-06-29\",\"endDate\":\"2020-07-03\"},\"13\":{\"startDate\":\"2020-07-06\",\"endDate\":\"2020-07-10\"}}}'),
       (2, 'Blockveranstaltungen 1', 'Block Event 1', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-07-27\",\"endDate\":\"2020-08-01\"},\"2\":{\"startDate\":\"2020-08-03\",\"endDate\":\"2020-08-08\"}}}'),
       (3, 'Blockveranstaltungen 2', 'Block Event 2', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-08-10\",\"endDate\":\"2020-08-15\"},\"2\":{\"startDate\":\"2020-08-17\",\"endDate\":\"2020-08-22\"}}}'),
       (4, 'Blockveranstaltungen 3', 'Block Event 3', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-08-24\",\"endDate\":\"2020-08-29\"},\"2\":{\"startDate\":\"2020-08-31\",\"endDate\":\"2020-09-05\"}}}'),
       (5, 'Blockveranstaltungen 4', 'Block Event 4', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-09-07\",\"endDate\":\"2020-09-12\"}}}'),
       (6, 'Einführungswoche', 'Introduction Week', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-03-30\",\"endDate\":\"2020-04-03\"}}}'),
       (7, 'Klausurwoche 1', 'Examination Week 1', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-07-13\",\"endDate\":\"2020-07-18\"}}}'),
       (8, 'Klausurwoche 2', 'Examination Week 2', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-07-20\",\"endDate\":\"2020-07-25\"}}}'),
       (9, 'Klausurwoche 3', 'Examination Week 3', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-09-14\",\"endDate\":\"2020-09-19\"}}}'),
       (10, 'Klausurwoche 4', 'Examination Week 4', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-09-21\",\"endDate\":\"2020-09-26\"}}}'),
       (11, 'Projektwoche', 'Project Week', 11, '{\"dates\":{\"1\":{\"startDate\":\"2020-06-02\",\"endDate\":\"2020-06-06\"},\"2\":{\"startDate\":\"2020-06-12\",\"endDate\":\"2020-06-12\"}}}'),
       (12, 'Sommersemester', 'Summer Semester', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-04-12\",\"endDate\":\"2021-04-16\"},\"2\":{\"startDate\":\"2021-04-19\",\"endDate\":\"2021-04-23\"},\"3\":{\"startDate\":\"2021-04-26\",\"endDate\":\"2021-04-30\"},\"4\":{\"startDate\":\"2021-05-03\",\"endDate\":\"2021-05-07\"},\"5\":{\"startDate\":\"2021-05-10\",\"endDate\":\"2021-05-12\"},\"6\":{\"startDate\":\"2021-05-17\",\"endDate\":\"2021-05-21\"},\"7\":{\"startDate\":\"2021-05-25\",\"endDate\":\"2021-05-28\"},\"8\":{\"startDate\":\"2021-06-07\",\"endDate\":\"2021-06-11\"},\"9\":{\"startDate\":\"2021-06-14\",\"endDate\":\"2021-06-18\"},\"10\":{\"startDate\":\"2021-06-21\",\"endDate\":\"2021-06-25\"},\"11\":{\"startDate\":\"2021-06-28\",\"endDate\":\"2021-07-02\"},\"12\":{\"startDate\":\"2021-07-05\",\"endDate\":\"2021-07-09\"},\"13\":{\"startDate\":\"2021-07-12\",\"endDate\":\"2021-07-16\"}}}'),
       (13, 'Blockveranstaltungen 1', 'Block Event 1', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-08-02\",\"endDate\":\"2021-08-07\"},\"2\":{\"startDate\":\"2021-08-09\",\"endDate\":\"2021-08-14\"}}}'),
       (14, 'Blockveranstaltungen 2', 'Block Event 2', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-08-16\",\"endDate\":\"2021-08-21\"},\"2\":{\"startDate\":\"2021-08-23\",\"endDate\":\"2021-08-28\"}}}'),
       (15, 'Blockveranstaltungen 3', 'Block Event 3', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-08-30\",\"endDate\":\"2021-09-04\"},\"2\":{\"startDate\":\"2021-09-06\",\"endDate\":\"2021-09-11\"}}}'),
       (16, 'Blockveranstaltungen 4', 'Block Event 4', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-09-13\",\"endDate\":\"2021-09-18\"}}}'),
       (17, 'Klausurwoche 1', 'Examination Week 1', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-07-19\",\"endDate\":\"2021-07-24\"}}}'),
       (18, 'Klausurwoche 2', 'Examination Week 2', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-07-26\",\"endDate\":\"2021-07-31\"}}}'),
       (19, 'Klausurwoche 3', 'Examination Week 3', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-09-20\",\"endDate\":\"2021-09-25\"}}}'),
       (20, 'Klausurwoche 4', 'Examination Week 4', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-09-27\",\"endDate\":\"2021-09-30\"}}}'),
       (21, 'Projektwoche', 'Project Week', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-05-31\",\"endDate\":\"2021-06-02\"},\"2\":{\"startDate\":\"2021-06-04\",\"endDate\":\"2021-06-04\"}}}'),
       (22, 'Einführungswoche', 'Introduction Week', 12, '{\"dates\":{\"1\":{\"startDate\":\"2021-04-06\",\"endDate\":\"2021-04-09\"}}}'),
       (23, 'Wintersemester', 'Winter Semester', 10, '{\"dates\":{\"1\":{\"startDate\":\"2019-10-07\",\"endDate\":\"2019-10-11\"},\"2\":{\"startDate\":\"2019-10-14\",\"endDate\":\"2019-10-18\"},\"3\":{\"startDate\":\"2019-10-21\",\"endDate\":\"2019-10-25\"},\"4\":{\"startDate\":\"2019-10-28\",\"endDate\":\"2019-11-01\"},\"5\":{\"startDate\":\"2019-11-04\",\"endDate\":\"2019-11-08\"},\"6\":{\"startDate\":\"2019-11-11\",\"endDate\":\"2019-11-15\"},\"7\":{\"startDate\":\"2019-11-18\",\"endDate\":\"2019-11-22\"},\"8\":{\"startDate\":\"2019-11-25\",\"endDate\":\"2019-11-29\"},\"9\":{\"startDate\":\"2019-12-02\",\"endDate\":\"2019-12-06\"},\"10\":{\"startDate\":\"2019-12-09\",\"endDate\":\"2019-12-13\"},\"11\":{\"startDate\":\"2019-12-16\",\"endDate\":\"2019-12-20\"},\"12\":{\"startDate\":\"2020-01-13\",\"endDate\":\"2020-01-17\"},\"13\":{\"startDate\":\"2020-01-20\",\"endDate\":\"2020-01-24\"}}}'),
       (24, 'Blockveranstaltungen 1', 'Block Event 1', 10, '{\"dates\":{\"1\":{\"startDate\":\"2020-02-10\",\"endDate\":\"2020-02-15\"},\"2\":{\"startDate\":\"2020-02-17\",\"endDate\":\"2020-02-22\"}}}'),
       (25, 'Blockveranstaltungen 2', 'Block Event 2', 10, '{\"dates\":{\"1\":{\"startDate\":\"2020-02-24\",\"endDate\":\"2020-02-29\"},\"2\":{\"startDate\":\"2020-03-02\",\"endDate\":\"2020-03-07\"}}}'),
       (26, 'Blockveranstaltungen 3', 'Block Event 3', 10, '{\"dates\":{\"1\":{\"startDate\":\"2020-03-09\",\"endDate\":\"2020-03-14\"},\"2\":{\"startDate\":\"2020-03-16\",\"endDate\":\"2020-03-21\"}}}'),
       (27, 'Einführungswoche', 'Introduction Week', 10, '{\"dates\":{\"1\":{\"startDate\":\"2019-09-30\",\"endDate\":\"2019-10-02\"},\"2\":{\"startDate\":\"2019-10-04\",\"endDate\":\"2019-10-04\"}}}'),
       (28, 'Klausurwoche 1', 'Examination Week 1', 10, '{\"dates\":{\"1\":{\"startDate\":\"2020-01-27\",\"endDate\":\"2020-02-01\"}}}'),
       (29, 'Klausurwoche 2', 'Examination Week 2', 10, '{\"dates\":{\"1\":{\"startDate\":\"2020-02-03\",\"endDate\":\"2020-02-08\"}}}'),
       (30, 'Klausurwoche 3', 'Examination Week 3', 10, '{\"dates\":{\"1\":{\"startDate\":\"2020-03-23\",\"endDate\":\"2020-03-28\"}}}'),
       (31, 'Projektwoche', 'Project Week', 10, '{\"dates\":{\"1\":{\"startDate\":\"2020-01-06\",\"endDate\":\"2020-01-10\"}}}');
# endregion

#region lessons => units
# table name changed, index not explicitly set, non-standard fk syntax, referenced table name changed
ALTER TABLE `v7ocf_thm_organizer_lessons`
    DROP FOREIGN KEY `lessons_campusID_fk`,
    DROP FOREIGN KEY `lessons_departmentid_fk`,
    DROP FOREIGN KEY `lessons_methodid_fk`,
    DROP FOREIGN KEY `lessons_planningperiodid_fk`,
    DROP INDEX `planID`,
    DROP INDEX `lessons_departmentid_fk`,
    DROP INDEX `lessons_planningperiodid_fk`;

RENAME TABLE `v7ocf_thm_organizer_lessons` TO `v7ocf_thm_organizer_units`;

# columns referencing campuses and methods will be dropped after migration
# columns with course information will be dropped after migration
# this untis id is a non-unique integer value
ALTER TABLE `v7ocf_thm_organizer_units`
    CHANGE `gpuntisID` `untisID` INT(11) UNSIGNED NOT NULL,
    MODIFY `departmentID` INT(11) UNSIGNED DEFAULT NULL AFTER `untisID`,
    CHANGE `planningPeriodID` `termID` INT(11) UNSIGNED DEFAULT NULL AFTER `departmentID`,
    MODIFY `comment` VARCHAR(200) DEFAULT NULL AFTER `termID`,
    ADD CONSTRAINT `entry` UNIQUE (`departmentID`, `termID`, `untisID`),
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `termID` (`termID`),
    ADD INDEX `untisID` (`untisID`);

# columns referencing campuses and methods will be dropped after migration
ALTER TABLE `v7ocf_thm_organizer_units`
    ADD CONSTRAINT `untis_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_termID_fk` FOREIGN KEY (`termID`) REFERENCES `v7ocf_thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

# region instances (data from units)
# configuration will be dropped after data migration
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instances` (
    `id`            INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `blockID`       INT(11) UNSIGNED NOT NULL,
    `eventID`       INT(11) UNSIGNED NOT NULL,
    `methodID`      INT(11) UNSIGNED          DEFAULT NULL,
    `unitID`        INT(11) UNSIGNED NOT NULL,
    `delta`         VARCHAR(10)      NOT NULL DEFAULT '',
    `modified`      TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `configuration` TEXT             NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `entry` UNIQUE (`eventID`, `blockID`, `unitID`),
    INDEX `blockID` (`blockID`),
    INDEX `eventID` (`eventID`),
    INDEX `methodID` (`methodID`),
    INDEX `unitID` (`unitID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `v7ocf_thm_organizer_instances`(`eventID`, `blockID`, `unitID`, `methodID`, `delta`, `modified`, `configuration`)
SELECT ls.`subjectID` AS eventID,
       b.`id`         AS blockID,
       u.`id`         AS unitID,
       u.`methodID`,
       c.`delta`,
       c.`modified`,
       lc.`configuration`
FROM `v7ocf_thm_organizer_lesson_subjects` AS ls
         INNER JOIN `v7ocf_thm_organizer_units` AS u ON u.`id` = ls.`lessonID`
         INNER JOIN `v7ocf_thm_organizer_calendar` AS c ON c.`lessonID` = ls.`lessonID`
         INNER JOIN `v7ocf_thm_organizer_blocks` AS b
                    ON b.`date` = c.`schedule_date` AND b.`startTime` = c.`startTime` AND b.`endTime` = c.`endTime`
         INNER JOIN `v7ocf_thm_organizer_calendar_configuration_map` AS ccm ON ccm.`calendarID` = c.`id`
         INNER JOIN `v7ocf_thm_organizer_lesson_configurations` AS lc
                    ON lc.`lessonID` = ls.`id` AND lc.`id` = ccm.`configurationID`
GROUP BY eventID, blockID, unitID;

ALTER TABLE `v7ocf_thm_organizer_instances`
    ADD CONSTRAINT `instances_blockID_fk` FOREIGN KEY (`blockID`) REFERENCES `v7ocf_thm_organizer_blocks` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instances_eventID_fk` FOREIGN KEY (`eventID`) REFERENCES `v7ocf_thm_organizer_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instances_methodID_fk` FOREIGN KEY (`methodID`) REFERENCES `v7ocf_thm_organizer_methods` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instances_unitID_fk` FOREIGN KEY (`unitID`) REFERENCES `v7ocf_thm_organizer_units` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

# data has been migrated
ALTER TABLE `v7ocf_thm_organizer_units`
    DROP INDEX `methodID`,
    DROP COLUMN `methodID`;
#endregion

# region courses (data from units & association 2nd grade to instances)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_courses` (
    `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campusID`         INT(11) UNSIGNED          DEFAULT NULL,
    `eventID`          INT(11) UNSIGNED NOT NULL,
    `termID`           INT(11) UNSIGNED NOT NULL,
    `groups`           VARCHAR(100)     NOT NULL DEFAULT '',
    `deadline`         INT(2) UNSIGNED           DEFAULT 0
        COMMENT 'The deadline in days for registration before the course starts.',
    `fee`              INT(3) UNSIGNED           DEFAULT 0,
    `maxParticipants`  INT(4) UNSIGNED           DEFAULT 1000,
    `registrationType` INT(1) UNSIGNED           DEFAULT NULL
        COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.',
    PRIMARY KEY (`id`),
    INDEX `campusID` (`campusID`),
    INDEX `eventID` (`eventID`),
    INDEX `termID` (`termID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

# create initial courses from the existing instances
INSERT INTO `v7ocf_thm_organizer_courses`(`campusID`, `eventID`, `termID`, `deadline`, `fee`, `maxParticipants`, `registrationType`)
SELECT u.`campusID`, i.`eventID`, u.`termID`, u.`deadline`, u.`fee`, u.`max_participants`, u.`registration_type`
FROM `v7ocf_thm_organizer_units` AS u
         INNER JOIN `v7ocf_thm_organizer_instances` AS i ON i.`unitID` = u.`id`
GROUP BY i.`eventID`, u.`termID`;

ALTER TABLE `v7ocf_thm_organizer_courses`
    ADD CONSTRAINT `courses_eventID_fk` FOREIGN KEY (`eventID`) REFERENCES `v7ocf_thm_organizer_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `courses_termID_fk` FOREIGN KEY (`termID`) REFERENCES `v7ocf_thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

# data has been migrated
ALTER TABLE `v7ocf_thm_organizer_units`
    DROP INDEX `lessons_campusID_fk`,
    DROP COLUMN `campusID`,
    DROP COLUMN `deadline`,
    DROP COLUMN `fee`,
    DROP COLUMN `max_participants`,
    DROP COLUMN `registration_type`;
# endregion

# region course instances
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_course_instances` (
    `id`         INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `courseID`   INT(11) UNSIGNED NOT NULL,
    `instanceID` INT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `entry` UNIQUE (`courseID`, `instanceID`),
    INDEX `courseID` (`courseID`),
    INDEX `instanceID` (`instanceID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

# map courses to instances
INSERT INTO `v7ocf_thm_organizer_course_instances`(`courseID`, `instanceID`)
SELECT c.`id`, i.`id`
FROM `v7ocf_thm_organizer_instances` AS i
         INNER JOIN `v7ocf_thm_organizer_units` AS u ON u.`id` = i.`unitID`
         INNER JOIN `v7ocf_thm_organizer_courses` AS c
                    ON c.`eventID` = i.`eventID` AND c.`termID` = u.`termID`
GROUP BY i.`id`, c.`id`;

ALTER TABLE `v7ocf_thm_organizer_course_instances`
    ADD CONSTRAINT `course_instances_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `v7ocf_thm_organizer_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `course_instances_instanceID_fk` FOREIGN KEY (`instanceID`) REFERENCES `v7ocf_thm_organizer_instances` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region course participants
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_course_participants` (
    `id`              INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `courseID`        INT(11) UNSIGNED NOT NULL,
    `participantID`   INT(11)          NOT NULL,
    `participantDate` DATETIME        DEFAULT NULL COMMENT 'The last date of participant action.',
    `status`          INT(1) UNSIGNED DEFAULT 0
        COMMENT 'The participant''s course status. Possible values: 0 - pending, 1 - registered',
    `statusDate`      DATETIME        DEFAULT NULL COMMENT 'The last date of status action.',
    PRIMARY KEY (`id`),
    INDEX `courseID` (`courseID`),
    INDEX `participantID` (`participantID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `v7ocf_thm_organizer_course_participants`
    ADD CONSTRAINT `course_participants_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `v7ocf_thm_organizer_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `course_participants_participantID_fk` FOREIGN KEY (`participantID`) REFERENCES `v7ocf_thm_organizer_participants` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region instance participants
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instance_participants` (
    `id`            INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `instanceID`    INT(20) UNSIGNED NOT NULL,
    `participantID` INT(11)          NOT NULL,
    `delta`         VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The association''s delta status. Possible values: empty, new, removed.',
    `modified`      TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `instanceID` (`instanceID`),
    INDEX `participantID` (`participantID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `v7ocf_thm_organizer_instance_participants`
    ADD CONSTRAINT `instance_participants_instanceID_fk` FOREIGN KEY (`instanceID`) REFERENCES `v7ocf_thm_organizer_instances` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_participants_participantID_fk` FOREIGN KEY (`participantID`) REFERENCES `v7ocf_thm_organizer_participants` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region instance persons
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instance_persons` (
    `id`             INT(20) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `instanceID`     INT(20) UNSIGNED    NOT NULL,
    `personID`       INT(11)             NOT NULL,
    `responsibility` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Responsibilities are not mutually exclusive. Possible values: 1 - Teacher, 2 - Tutor, 3 - Supervisor, 4 - Speaker, 5 - Moderator.',
    `delta`          VARCHAR(10)         NOT NULL DEFAULT ''
        COMMENT 'The association''s delta status. Possible values: empty, new, removed.',
    `modified`       TIMESTAMP                    DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `instanceID` (`instanceID`),
    INDEX `personID` (`personID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `v7ocf_thm_organizer_instance_persons`(`instanceID`, `personID`, `delta`, `modified`)
SELECT DISTINCT i.`id`, lt.`teacherID`, lt.`delta`, lt.`modified`
FROM `v7ocf_thm_organizer_lesson_teachers` AS lt
         INNER JOIN `v7ocf_thm_organizer_lesson_subjects` AS ls ON ls.`id` = lt.`subjectID`
         INNER JOIN `v7ocf_thm_organizer_instances` AS i ON i.`eventID` = ls.`subjectID` AND i.`unitID` = ls.`lessonID`
GROUP BY i.`id`, lt.`teacherID`;

ALTER TABLE `v7ocf_thm_organizer_instance_persons`
    ADD CONSTRAINT `instance_persons_instanceID_fk` FOREIGN KEY (`instanceID`) REFERENCES `v7ocf_thm_organizer_instances` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_persons_personID_fk` FOREIGN KEY (`personID`) REFERENCES `v7ocf_thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

#region person groups
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_person_groups` (
    `id`       INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `groupID`  INT(11) UNSIGNED NOT NULL,
    `personID` INT(20) UNSIGNED NOT NULL
        COMMENT 'The instance to person association id.',
    `delta`    VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The association''s delta status. Possible values: empty, new, removed.',
    `modified` TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `groupID` (`groupID`),
    INDEX `personID` (`personID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;


INSERT INTO `v7ocf_thm_organizer_person_groups`(`groupID`, `personID`, `delta`, `modified`)
SELECT DISTINCT lp.`poolID`, ip.`id`, lp.`delta`, lp.`modified`
FROM `v7ocf_thm_organizer_lesson_pools` AS lp
         INNER JOIN `v7ocf_thm_organizer_lesson_subjects` AS ls ON ls.`id` = lp.`subjectID`
         INNER JOIN `v7ocf_thm_organizer_instances` AS i ON i.`eventID` = ls.`subjectID` AND i.`unitID` = ls.`lessonID`
         INNER JOIN `v7ocf_thm_organizer_instance_persons` AS ip ON ip.`instanceID` = i.`id`
GROUP BY i.`id`, lp.`poolID`;

#169 - 10s
ALTER TABLE `v7ocf_thm_organizer_person_groups`
    ADD CONSTRAINT `person_groups_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `v7ocf_thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `person_groups_personID_fk` FOREIGN KEY (`personID`) REFERENCES `v7ocf_thm_organizer_instance_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

#region person rooms
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_person_rooms` (
    `id`       INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `personID` INT(20) UNSIGNED NOT NULL COMMENT 'The instance to person association id.',
    `roomID`   INT(11) UNSIGNED NOT NULL,
    `delta`    VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The association''s delta status. Possible values: empty, new, removed.',
    `modified` TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `personID` (`personID`),
    INDEX `roomID` (`roomID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `v7ocf_thm_organizer_person_rooms`
    ADD CONSTRAINT `person_rooms_personID_fk` FOREIGN KEY (`personID`) REFERENCES `v7ocf_thm_organizer_instance_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `person_rooms_roomID_fk` FOREIGN KEY (`roomID`) REFERENCES `v7ocf_thm_organizer_rooms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

DROP TABLE `v7ocf_thm_organizer_lesson_pools`;

DROP TABLE `v7ocf_thm_organizer_lesson_teachers`;

# participant courses coupon?

# preflight - set site to offline

# - migrate data in schedules - check
# - migrate data in user_lessons
# - migrate data in lesson_configurations
# - drop calendar, calendar_configuration_map, lesson_configuration, lesson_subjects & user_lessons

# enable site