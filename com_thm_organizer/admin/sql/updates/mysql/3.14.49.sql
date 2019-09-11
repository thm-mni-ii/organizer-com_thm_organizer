# structural changes to referenced / referencing tables

# region categories
ALTER TABLE `v7ocf_thm_organizer_plan_programs`
    DROP FOREIGN KEY `plan_programs_programid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_programs_programid_fk`;

RENAME TABLE `v7ocf_thm_organizer_plan_programs` TO `v7ocf_thm_organizer_categories`;

ALTER TABLE `v7ocf_thm_organizer_categories`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD COLUMN `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);
# endregion

#region programs (fk: categories)
ALTER TABLE `v7ocf_thm_organizer_programs`
    DROP FOREIGN KEY `programs_degreeid_fk`,
    DROP FOREIGN KEY `programs_departmentid_fk`,
    DROP FOREIGN KEY `programs_fieldid_fk`,
    DROP FOREIGN KEY `programs_frequencyid_fk`,
    DROP INDEX `lsfData`,
    DROP INDEX `programs_departmentid_fk`,
    DROP INDEX `programs_frequencyid_fk`;

ALTER TABLE `v7ocf_thm_organizer_programs`
    ADD COLUMN `categoryID` INT(11) UNSIGNED DEFAULT NULL AFTER `asset_id`,
    MODIFY `degreeID` INT(11) UNSIGNED DEFAULT NULL AFTER `categoryID`,
    MODIFY `fieldID` INT(11) UNSIGNED DEFAULT NULL AFTER `departmentID`,
    MODIFY `frequencyID` INT(1) UNSIGNED DEFAULT NULL AFTER `fieldID`,
    MODIFY `code` VARCHAR(20) DEFAULT '' AFTER `frequencyID`,
    MODIFY `version` YEAR(4) DEFAULT NULL AFTER `code`,
    ADD COLUMN `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    ADD CONSTRAINT `entry` UNIQUE (`code`, `degreeID`, `version`),
    ADD INDEX `categoryID` (`categoryID`),
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `frequencyID` (`frequencyID`);

UPDATE `v7ocf_thm_organizer_programs` AS p
    INNER JOIN `v7ocf_thm_organizer_categories` AS c ON c.`programID` = p.`id`
SET p.`categoryID` = c.`id`;

ALTER TABLE `v7ocf_thm_organizer_programs`
    ADD CONSTRAINT `programs_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `v7ocf_thm_organizer_categories` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
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

ALTER TABLE `v7ocf_thm_organizer_categories` DROP COLUMN `programID`;
#endregion

# region groups
ALTER TABLE `v7ocf_thm_organizer_plan_pools`
    DROP FOREIGN KEY `plan_pools_fieldid_fk`,
    DROP FOREIGN KEY `plan_pools_gridid_fk`,
    DROP FOREIGN KEY `plan_pools_poolid_fk`,
    DROP FOREIGN KEY `plan_pools_programid_fk`,
    DROP INDEX `dbID`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_pools_gridid_fk`,
    DROP INDEX `poolID`,
    DROP INDEX `programID`;

RENAME TABLE `v7ocf_thm_organizer_plan_pools` TO `v7ocf_thm_organizer_groups`;

ALTER TABLE `v7ocf_thm_organizer_groups`
    CHANGE `full_name` `fullName` VARCHAR(100) NOT NULL,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    DROP COLUMN `poolID`,
    CHANGE `programID` `categoryID` INT(11) UNSIGNED NOT NULL,
    ADD COLUMN `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
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
        ON UPDATE CASCADE;
# endregion

# region pools (fk: groups)
ALTER TABLE `v7ocf_thm_organizer_pools`
    DROP FOREIGN KEY `pools_departmentid_fk`,
    DROP FOREIGN KEY `pools_fieldid_fk`,
    DROP INDEX `externalID`,
    DROP INDEX `lsfID`,
    DROP INDEX `pools_departmentid_fk`;

UPDATE `v7ocf_thm_organizer_pools`
SET `lsfID` = NULL
WHERE `lsfID` = 0;

ALTER TABLE `v7ocf_thm_organizer_pools`
    MODIFY `fieldID` INT(11) UNSIGNED DEFAULT NULL AFTER `departmentID`,
    ADD COLUMN `groupID` INT(11) UNSIGNED DEFAULT NULL AFTER `fieldID`,
    DROP COLUMN `hisID`,
    CHANGE `externalID` `code` VARCHAR(45) DEFAULT '',
    MODIFY `abbreviation_de` VARCHAR(45) DEFAULT '' AFTER `code`,
    MODIFY `abbreviation_en` VARCHAR(45) DEFAULT '' AFTER `abbreviation_de`,
    CHANGE `short_name_de` `shortName_de` VARCHAR(45) DEFAULT '' AFTER `abbreviation_en`,
    CHANGE `short_name_en` `shortName_en` VARCHAR(45) DEFAULT '' AFTER `shortName_de`,
    MODIFY `name_de` VARCHAR(255) DEFAULT NULL AFTER `shortName_en`,
    MODIFY `name_en` VARCHAR(255) DEFAULT NULL AFTER `name_de`,
    DROP COLUMN `display_type`,
    DROP COLUMN `enable_desc`,
    ADD INDEX `code` (`code`),
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `groupID` (`groupID`),
    ADD UNIQUE INDEX `lsfID` (`lsfID`);

ALTER TABLE `v7ocf_thm_organizer_pools`
    ADD CONSTRAINT `pools_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `pools_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `pools_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `v7ocf_thm_organizer_groups` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region persons
ALTER TABLE `v7ocf_thm_organizer_department_resources` DROP FOREIGN KEY `department_resources_teacherid_fk`;
ALTER TABLE `v7ocf_thm_organizer_lesson_teachers` DROP FOREIGN KEY `lesson_teachers_teacherid_fk`;
ALTER TABLE `v7ocf_thm_organizer_subject_teachers` DROP FOREIGN KEY `subject_teachers_teacherid_fk`;

ALTER TABLE `v7ocf_thm_organizer_teachers`
    DROP FOREIGN KEY `teachers_fieldid_fk`,
    DROP INDEX `gpuntisID`;

RENAME TABLE `v7ocf_thm_organizer_teachers` TO `v7ocf_thm_organizer_persons`;

ALTER TABLE `v7ocf_thm_organizer_persons`
    MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    ADD COLUMN `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `v7ocf_thm_organizer_persons`
    ADD CONSTRAINT `persons_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `v7ocf_thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;
# endregion

# region department resources (fk: persons)
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

# region roomtypes
ALTER TABLE `v7ocf_thm_organizer_room_types` DROP INDEX `gpuntisID`;

RENAME TABLE `v7ocf_thm_organizer_room_types` TO `v7ocf_thm_organizer_roomtypes`;

ALTER TABLE `v7ocf_thm_organizer_roomtypes`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    CHANGE `min_capacity` `minCapacity` INT(4) UNSIGNED DEFAULT NULL,
    CHANGE `max_capacity` `maxCapacity` INT(4) UNSIGNED DEFAULT NULL,
    ADD COLUMN `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

UPDATE `v7ocf_thm_organizer_roomtypes`
SET public = 0
WHERE `untisID` = 'BR';
# endregion

# region rooms (fk: roomtypes)
ALTER TABLE `v7ocf_thm_organizer_rooms`
    DROP FOREIGN KEY `room_buildingID_fk`,
    DROP FOREIGN KEY `rooms_typeid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `room_buildingID_fk`,
    DROP INDEX `typeID`;

ALTER TABLE `v7ocf_thm_organizer_rooms`
    DROP COLUMN `longname`,
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    CHANGE `typeID` `roomtypeID` INT(11) UNSIGNED DEFAULT NULL,
    ADD COLUMN `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
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

# region terms
ALTER TABLE `v7ocf_thm_organizer_planning_periods` DROP INDEX `pp_long`;

RENAME TABLE `v7ocf_thm_organizer_planning_periods` TO `v7ocf_thm_organizer_terms`;

ALTER TABLE `v7ocf_thm_organizer_terms` ADD UNIQUE INDEX `entry` (`name`, `startDate`, `endDate`);
# endregion

# region group publishing (fk: groups, terms)
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

# region runs (fk: terms)
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
       (12, 'Sommersemester', 'Summer Semester', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-04-12\",\"endDate\":\"2021-04-16\"},\"2\":{\"startDate\":\"2021-04-19\",\"endDate\":\"2021-04-23\"},\"3\":{\"startDate\":\"2021-04-26\",\"endDate\":\"2021-04-30\"},\"4\":{\"startDate\":\"2021-05-03\",\"endDate\":\"2021-05-07\"},\"5\":{\"startDate\":\"2021-05-10\",\"endDate\":\"2021-05-12\"},\"6\":{\"startDate\":\"2021-05-17\",\"endDate\":\"2021-05-21\"},\"7\":{\"startDate\":\"2021-05-25\",\"endDate\":\"2021-05-28\"},\"8\":{\"startDate\":\"2021-06-07\",\"endDate\":\"2021-06-11\"},\"9\":{\"startDate\":\"2021-06-14\",\"endDate\":\"2021-06-18\"},\"10\":{\"startDate\":\"2021-06-21\",\"endDate\":\"2021-06-25\"},\"11\":{\"startDate\":\"2021-06-28\",\"endDate\":\"2021-07-02\"},\"12\":{\"startDate\":\"2021-07-05\",\"endDate\":\"2021-07-09\"},\"13\":{\"startDate\":\"2021-07-12\",\"endDate\":\"2021-07-16\"}}}'),
       (13, 'Blockveranstaltungen 1', 'Block Event 1', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-08-02\",\"endDate\":\"2021-08-07\"},\"2\":{\"startDate\":\"2021-08-09\",\"endDate\":\"2021-08-14\"}}}'),
       (14, 'Blockveranstaltungen 2', 'Block Event 2', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-08-16\",\"endDate\":\"2021-08-21\"},\"2\":{\"startDate\":\"2021-08-23\",\"endDate\":\"2021-08-28\"}}}'),
       (15, 'Blockveranstaltungen 3', 'Block Event 3', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-08-30\",\"endDate\":\"2021-09-04\"},\"2\":{\"startDate\":\"2021-09-06\",\"endDate\":\"2021-09-11\"}}}'),
       (16, 'Blockveranstaltungen 4', 'Block Event 4', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-09-13\",\"endDate\":\"2021-09-18\"}}}'),
       (17, 'Klausurwoche 1', 'Examination Week 1', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-07-19\",\"endDate\":\"2021-07-24\"}}}'),
       (18, 'Klausurwoche 2', 'Examination Week 2', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-07-26\",\"endDate\":\"2021-07-31\"}}}'),
       (19, 'Klausurwoche 3', 'Examination Week 3', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-09-20\",\"endDate\":\"2021-09-25\"}}}'),
       (20, 'Klausurwoche 4', 'Examination Week 4', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-09-27\",\"endDate\":\"2021-09-30\"}}}'),
       (21, 'Projektwoche', 'Project Week', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-05-31\",\"endDate\":\"2021-06-02\"},\"2\":{\"startDate\":\"2021-06-04\",\"endDate\":\"2021-06-04\"}}}'),
       (22, 'Einführungswoche', 'Introduction Week', 13, '{\"dates\":{\"1\":{\"startDate\":\"2021-04-06\",\"endDate\":\"2021-04-09\"}}}'),
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