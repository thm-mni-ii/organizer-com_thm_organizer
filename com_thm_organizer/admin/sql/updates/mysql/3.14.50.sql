# large scale restructuring of schedule related tables

# region events (here because of renaming and data migration from subjects)
ALTER TABLE `v7ocf_thm_organizer_plan_subjects`
    DROP FOREIGN KEY `plan_subjects_fieldid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_subjects_fieldid_fk`,
    DROP INDEX `subjectIndex`;

RENAME TABLE `v7ocf_thm_organizer_plan_subjects` TO `v7ocf_thm_organizer_events`;

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

UPDATE `v7ocf_thm_organizer_events`
SET `name_en` = `name_de`;

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

UPDATE `v7ocf_thm_organizer_events`
SET `registrationType` = 1
WHERE `maxParticipants` IS NOT NULL;

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

# region event coordinators (fk: events, persons)
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

# region subjects (here because of previous data migration to events)
ALTER TABLE `v7ocf_thm_organizer_subjects`
    DROP FOREIGN KEY `subject_campusID_fk`,
    DROP FOREIGN KEY `subjects_departmentid_fk`,
    DROP FOREIGN KEY `subjects_fieldid_fk`,
    DROP FOREIGN KEY `subjects_frequencyid_fk`,
    DROP INDEX `subject_campusID_fk`,
    DROP INDEX `subjects_departmentid_fk`;

DELETE
FROM `v7ocf_thm_organizer_subjects`
WHERE `lsfID` IS NULL;

UPDATE `v7ocf_thm_organizer_subjects`
SET `expertise` = NULL
WHERE `expertise` > 3;

UPDATE `v7ocf_thm_organizer_subjects`
SET `method_competence` = NULL
WHERE `method_competence` > 3;

UPDATE `v7ocf_thm_organizer_subjects`
SET `self_competence` = NULL
WHERE `self_competence` > 3;

UPDATE `v7ocf_thm_organizer_subjects`
SET `social_competence` = NULL
WHERE `social_competence` > 3;

ALTER TABLE `v7ocf_thm_organizer_subjects`
    DROP COLUMN `hisID`,
    CHANGE `externalID` `code` VARCHAR(45) DEFAULT '',
    CHANGE `short_name_de` `shortName_de` VARCHAR(45) NOT NULL DEFAULT '',
    CHANGE `short_name_en` `shortName_en` VARCHAR(45) NOT NULL DEFAULT '',
    CHANGE `preliminary_work_de` `preliminaryWork_de` TEXT,
    CHANGE `preliminary_work_en` `preliminaryWork_en` TEXT,
    MODIFY `expertise` TINYINT(1) UNSIGNED DEFAULT NULL,
    CHANGE `self_competence` `selfCompetence` TINYINT(1) UNSIGNED DEFAULT NULL,
    CHANGE `method_competence` `methodCompetence` TINYINT(1) UNSIGNED DEFAULT NULL,
    CHANGE `social_competence` `socialCompetence` TINYINT(1) UNSIGNED DEFAULT NULL,
    CHANGE `recommended_prerequisites_de` `recommendedPrerequisites_de` TEXT,
    CHANGE `recommended_prerequisites_en` `recommendedPrerequisites_en` TEXT,
    CHANGE `used_for_de` `usedFor_de` TEXT,
    CHANGE `used_for_en` `usedFor_en` TEXT,
    CHANGE `bonus_points_de` `bonusPoints_de` TEXT,
    CHANGE `bonus_points_en` `bonusPoints_en` TEXT,
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

# region subject events (fk: events, subjects)
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

# region subject persons (fk: persons, subjects)
ALTER TABLE `v7ocf_thm_organizer_subject_teachers`
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`id`),
    DROP INDEX `id`,
    DROP FOREIGN KEY `subject_teachers_subjectid_fk`,
    DROP INDEX `teacherID`;

RENAME TABLE `v7ocf_thm_organizer_subject_teachers` TO `v7ocf_thm_organizer_subject_persons`;

ALTER TABLE `v7ocf_thm_organizer_subject_persons`
    CHANGE `teacherID` `personID` INT(11) NOT NULL,
    CHANGE `teacherResp` `role` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
        COMMENT 'The person\'s role for the given subject. Roles are not mutually exclusive. Possible values: 1 - coordinator, 2 - teacher.',
    ADD UNIQUE INDEX `entry` (`personID`, `subjectID`, `role`),
    ADD INDEX `personID` (`personID`);

ALTER TABLE `v7ocf_thm_organizer_subject_persons`
    ADD CONSTRAINT `subject_persons_personID_fk` FOREIGN KEY (`personID`) REFERENCES `v7ocf_thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_persons_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `v7ocf_thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region courses (fk: campuses, terms)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_courses` (
    `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campusID`         INT(11) UNSIGNED          DEFAULT NULL,
    `termID`           INT(11) UNSIGNED NOT NULL,
    `groups`           VARCHAR(100)     NOT NULL DEFAULT '',
    `name_de`          VARCHAR(100)              DEFAULT NULL,
    `name_en`          VARCHAR(100)              DEFAULT NULL,
    `description_de`   TEXT,
    `description_en`   TEXT,
    `deadline`         INT(2) UNSIGNED           DEFAULT 0
        COMMENT 'The deadline in days for registration before the course starts.',
    `fee`              INT(3) UNSIGNED           DEFAULT 0,
    `maxParticipants`  INT(4) UNSIGNED           DEFAULT 1000,
    `registrationType` INT(1) UNSIGNED           DEFAULT NULL
        COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.',
    `unitID`           INT(11) UNSIGNED          DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `campusID` (`campusID`),
    INDEX `termID` (`termID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `v7ocf_thm_organizer_courses`
    ADD CONSTRAINT `courses_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `v7ocf_thm_organizer_campuses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `courses_termID_fk` FOREIGN KEY (`termID`) REFERENCES `v7ocf_thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
# endregion

# region course participants (fk: courses, participants)
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

#region units (fk: courses)
ALTER TABLE `v7ocf_thm_organizer_lessons`
    DROP FOREIGN KEY `lessons_campusID_fk`,
    DROP FOREIGN KEY `lessons_departmentid_fk`,
    DROP FOREIGN KEY `lessons_methodid_fk`,
    DROP FOREIGN KEY `lessons_planningperiodid_fk`,
    DROP INDEX `planID`,
    DROP INDEX `lessons_departmentid_fk`,
    DROP INDEX `lessons_planningperiodid_fk`;

RENAME TABLE `v7ocf_thm_organizer_lessons` TO `v7ocf_thm_organizer_units`;

ALTER TABLE `v7ocf_thm_organizer_units`
    ADD `courseID` INT(11) UNSIGNED DEFAULT NULL AFTER `id`,
    MODIFY `departmentID` INT(11) UNSIGNED DEFAULT NULL AFTER `courseID`,
    CHANGE `planningPeriodID` `termID` INT(11) UNSIGNED DEFAULT NULL AFTER `departmentID`,
    CHANGE `gpuntisID` `untisID` INT(11) UNSIGNED NOT NULL AFTER `termID`,
    ADD `gridID` INT(11) UNSIGNED DEFAULT NULL AFTER `untisID`,
    ADD `runID` INT(11) UNSIGNED DEFAULT NULL AFTER `gridID`,
    ADD `startDate` DATE DEFAULT NULL AFTER `runID`,
    ADD `endDate` DATE DEFAULT NULL AFTER `startDate`,
    MODIFY `comment` VARCHAR(200) DEFAULT NULL AFTER `termID`,
    ADD CONSTRAINT `entry` UNIQUE (`departmentID`, `termID`, `untisID`),
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `gridID` (`gridID`),
    ADD INDEX `runID` (`runID`),
    ADD INDEX `termID` (`termID`),
    ADD INDEX `untisID` (`untisID`);

INSERT INTO `v7ocf_thm_organizer_courses`(`campusID`, `termID`, `deadline`, `fee`, `maxParticipants`, `unitID`)
SELECT u.`campusID`, u.`termID`, u.`deadline`, u.`fee`, u.`max_participants`, u.`id`
FROM `v7ocf_thm_organizer_units` AS u
WHERE `max_participants` IS NOT NULL;

UPDATE `v7ocf_thm_organizer_courses`
SET `registrationType` = 1
WHERE `maxParticipants` IS NOT NULL;

UPDATE `v7ocf_thm_organizer_units` AS u
    INNER JOIN `v7ocf_thm_organizer_courses` AS c ON c.`unitID` = u.`id`
SET u.`courseID` = c.`id`;

ALTER TABLE `v7ocf_thm_organizer_courses` DROP COLUMN `unitID`;

ALTER TABLE `v7ocf_thm_organizer_units`
    DROP INDEX `lessons_campusID_fk`,
    DROP COLUMN `campusID`,
    DROP COLUMN `deadline`,
    DROP COLUMN `fee`,
    DROP COLUMN `max_participants`,
    DROP COLUMN `registration_type`;

UPDATE `v7ocf_thm_organizer_units` AS u
    INNER JOIN `v7ocf_thm_organizer_lesson_subjects` AS ls ON ls.`lessonID` = u.`id`
    INNER JOIN `v7ocf_thm_organizer_lesson_pools` AS lp ON lp.`subjectID` = ls.`id`
    INNER JOIN `v7ocf_thm_organizer_groups` AS g ON g.`id` = lp.`poolID`
SET u.`gridID` = g.`gridID`;

ALTER TABLE `v7ocf_thm_organizer_units`
    ADD CONSTRAINT `units_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `v7ocf_thm_organizer_courses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `v7ocf_thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `v7ocf_thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_runID_fk` FOREIGN KEY (`runID`) REFERENCES `v7ocf_thm_organizer_runs` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_termID_fk` FOREIGN KEY (`termID`) REFERENCES `v7ocf_thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

# region instances (fk: events, units)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instances` (
    `id`       INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `blockID`  INT(11) UNSIGNED NOT NULL,
    `eventID`  INT(11) UNSIGNED NOT NULL,
    `methodID` INT(11) UNSIGNED          DEFAULT NULL,
    `unitID`   INT(11) UNSIGNED NOT NULL,
    `delta`    VARCHAR(10)      NOT NULL DEFAULT '',
    `modified` TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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

INSERT INTO `v7ocf_thm_organizer_instances`(`eventID`, `blockID`, `unitID`, `methodID`, `delta`, `modified`)
SELECT ls.`subjectID` AS eventID,
       b.`id`         AS blockID,
       u.`id`         AS unitID,
       u.`methodID`,
       c.`delta`,
       c.`modified`
FROM `v7ocf_thm_organizer_lesson_subjects` AS ls
         INNER JOIN `v7ocf_thm_organizer_units` AS u ON u.`id` = ls.`lessonID`
         INNER JOIN `v7ocf_thm_organizer_calendar` AS c ON c.`lessonID` = ls.`lessonID`
         INNER JOIN `v7ocf_thm_organizer_blocks` AS b
                    ON b.`date` = c.`schedule_date` AND b.`startTime` = c.`startTime` AND b.`endTime` = c.`endTime`
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

ALTER TABLE `v7ocf_thm_organizer_units`
    DROP INDEX `methodID`,
    DROP COLUMN `methodID`;
#endregion

# region instance participants (fk: instances, participants)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instance_participants` (
    `id`            INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `instanceID`    INT(20) UNSIGNED NOT NULL,
    `participantID` INT(11)          NOT NULL,
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

# region instance persons (fk: instances, persons)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instance_persons` (
    `id`         INT(20) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `instanceID` INT(20) UNSIGNED    NOT NULL,
    `personID`   INT(11)             NOT NULL,
    `roleID`     TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
    `delta`      VARCHAR(10)         NOT NULL DEFAULT ''
        COMMENT 'The association''s delta status. Possible values: empty, new, removed.',
    `modified`   TIMESTAMP                    DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `entry` UNIQUE (`instanceID`, `personID`),
    INDEX `instanceID` (`instanceID`),
    INDEX `personID` (`personID`),
    INDEX `roleID` (`roleID`)
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
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_persons_roleID_fk` FOREIGN KEY (`roleID`) REFERENCES `v7ocf_thm_organizer_roles` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

#region instance groups (fk: groups, instance persons)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instance_groups` (
    `id`       INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `assocID`  INT(20) UNSIGNED NOT NULL COMMENT 'The instance to person association id.',
    `groupID`  INT(11) UNSIGNED NOT NULL,
    `delta`    VARCHAR(10)      NOT NULL DEFAULT '' COMMENT 'The association''s delta status. Possible values: empty, new, removed.',
    `modified` TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `assocID` (`assocID`),
    INDEX `groupID` (`groupID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

INSERT INTO `v7ocf_thm_organizer_instance_groups`(`assocID`, `groupID`, `delta`, `modified`)
SELECT DISTINCT ip.`id`, lp.`poolID`, lp.`delta`, lp.`modified`
FROM `v7ocf_thm_organizer_lesson_pools` AS lp
         INNER JOIN `v7ocf_thm_organizer_lesson_subjects` AS ls ON ls.`id` = lp.`subjectID`
         INNER JOIN `v7ocf_thm_organizer_instances` AS i ON i.`eventID` = ls.`subjectID` AND i.`unitID` = ls.`lessonID`
         INNER JOIN `v7ocf_thm_organizer_instance_persons` AS ip ON ip.`instanceID` = i.`id`
GROUP BY ip.`id`, lp.`poolID`;

ALTER TABLE `v7ocf_thm_organizer_instance_groups`
    ADD CONSTRAINT `instance_groups_assocID_fk` FOREIGN KEY (`assocID`) REFERENCES `v7ocf_thm_organizer_instance_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_groups_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `v7ocf_thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

#region instance rooms (fk: instance persons, rooms)
CREATE TABLE IF NOT EXISTS `v7ocf_thm_organizer_instance_rooms` (
    `id`       INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `assocID`  INT(20) UNSIGNED NOT NULL COMMENT 'The instance to person association id.',
    `roomID`   INT(11) UNSIGNED NOT NULL,
    `delta`    VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The association''s delta status. Possible values: empty, new, removed.',
    `modified` TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `assocID` (`assocID`),
    INDEX `roomID` (`roomID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `v7ocf_thm_organizer_instance_rooms`
    ADD CONSTRAINT `instance_rooms_assocID_fk` FOREIGN KEY (`assocID`) REFERENCES `v7ocf_thm_organizer_instance_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_rooms_roomID_fk` FOREIGN KEY (`roomID`) REFERENCES `v7ocf_thm_organizer_rooms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;
#endregion

DROP TABLE `v7ocf_thm_organizer_lesson_pools`;

DROP TABLE `v7ocf_thm_organizer_lesson_teachers`;