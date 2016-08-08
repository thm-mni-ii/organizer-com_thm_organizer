SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_calendar` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_date`   DATE                      DEFAULT NULL,
  `start_time`      TIME                      DEFAULT NULL,
  `end_time`        TIME                      DEFAULT NULL,
  `configurationID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `configurationID` (`configurationID`),
  CONSTRAINT `calendar_configurationid_fk` FOREIGN KEY (`configurationID`) REFERENCES `#__thm_organizer_lesson_configurations` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_colors` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_de` VARCHAR(60)      NOT NULL,
  `color`   VARCHAR(7)       NOT NULL,
  `name_en` VARCHAR(60)      NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_degrees` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255)     NOT NULL,
  `abbreviation` VARCHAR(45)      NOT NULL DEFAULT '',
  `code`         VARCHAR(10)               DEFAULT '',
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_department_resources` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `departmentID` INT(11) UNSIGNED NOT NULL,
  `programID`    INT(11) UNSIGNED          DEFAULT NULL,
  `poolID`       INT(11) UNSIGNED          DEFAULT NULL,
  `subjectID`    INT(11) UNSIGNED          DEFAULT NULL,
  `teacherID`    INT(11) UNSIGNED          DEFAULT NULL,
  `roomID`       INT(11) UNSIGNED          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departmentID` (`departmentID`),
  KEY `programID` (`programID`),
  KEY `poolID` (`poolID`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`),
  KEY `roomID` (`roomID`),
  CONSTRAINT `department_resources_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `department_resources_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `department_resources_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_plan_programs` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `department_resources_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `department_resources_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `department_resources_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`      INT(11)          NOT NULL,
  `short_name_de` VARCHAR(50)      NOT NULL,
  `name_de`       VARCHAR(255)     NOT NULL,
  `short_name_en` VARCHAR(50)      NOT NULL,
  `name_en`       VARCHAR(255)     NOT NULL,
  `plan_key`      VARCHAR(10)      NOT NULL,
  UNIQUE KEY `short_name` (`short_name_de`),
  UNIQUE KEY `name` (`name_de`),
  UNIQUE KEY `short_name_en` (`short_name_en`),
  UNIQUE KEY `name_en` (`name_en`),
  KEY `id` (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_fields` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR(60)
              CHARACTER SET utf8
              COLLATE utf8_bin          DEFAULT NULL,
  `field_de`  VARCHAR(60)      NOT NULL DEFAULT '',
  `colorID`   INT(11) UNSIGNED          DEFAULT NULL,
  `field_en`  VARCHAR(60)      NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`),
  KEY `colorID` (`colorID`),
  CONSTRAINT `fields_colorid_fk` FOREIGN KEY (`colorID`) REFERENCES `#__thm_organizer_colors` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_frequencies` (
  `id`           INT(1) UNSIGNED NOT NULL,
  `frequency_de` VARCHAR(45)     NOT NULL,
  `frequency_en` VARCHAR(45)     NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_grids` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_de`   VARCHAR(255)              DEFAULT NULL,
  `name_en`   VARCHAR(255)              DEFAULT NULL,
  `grid`      TEXT             NOT NULL
  COMMENT 'A grid object modeled by a JSON string, containing the respective start and end times of the grid blocks.',
  `default`   INT(1)           NOT NULL DEFAULT '0'
  COMMENT 'True if the grid is displayed by default.',
  `gpuntisID` VARCHAR(60)      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_configurations` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID`      INT(11) UNSIGNED NOT NULL,
  `configuration` TEXT             NOT NULL
  COMMENT 'A configuration of teachers and rooms for a lesson, inclusive of their delta status.',
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`),
  CONSTRAINT `lesson_configurations_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_pools` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `poolID`    INT(11) UNSIGNED NOT NULL,
  `delta`     VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `poolID` (`poolID`),
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `lesson_pools_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_lesson_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_subjects` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID`  INT(11) UNSIGNED NOT NULL,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `delta`     VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`),
  KEY `subjectID` (`subjectID`),
  CONSTRAINT `lesson_subjects_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `lesson_subjects_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `teacherID` INT(11) UNSIGNED NOT NULL,
  `delta`     VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`),
  CONSTRAINT `lesson_teachers_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_lesson_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `lesson_teachers_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
  `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID`         INT(11) UNSIGNED NOT NULL,
  `plan_name`         VARCHAR(8)       NOT NULL
  COMMENT 'A nomenclature for the source plan in the form XX-PP-YY, where XX is the organization key, PP the planning period and YY the short form for the year.',
  `methodID`          INT(3) UNSIGNED           DEFAULT NULL
  COMMENT 'The method of instruction for this lesson unit.',
  `delta`             VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  `registration_type` INT(1) UNSIGNED           DEFAULT '0'
  COMMENT 'The method of registration for the lesson. Possible values: 0 - FIFO, 1 - Manual.',
  `max_participants`  INT(4) UNSIGNED           DEFAULT NULL
  COMMENT 'The maximum number of participants. NULL is without limit.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `planID` (`gpuntisID`, `plan_name`),
  KEY `plan_name` (`plan_name`),
  KEY `methodID` (`methodID`),
  CONSTRAINT `lessons_methodid_fk` FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_mappings` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `programID` INT(11) UNSIGNED          DEFAULT NULL,
  `parentID`  INT(11) UNSIGNED          DEFAULT NULL,
  `poolID`    INT(11) UNSIGNED          DEFAULT NULL,
  `subjectID` INT(11) UNSIGNED          DEFAULT NULL,
  `lft`       INT(11) UNSIGNED          DEFAULT NULL,
  `rgt`       INT(11) UNSIGNED          DEFAULT NULL,
  `level`     INT(11) UNSIGNED          DEFAULT NULL,
  `ordering`  INT(11) UNSIGNED          DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`),
  KEY `programID` (`programID`),
  KEY `poolID` (`poolID`),
  KEY `subjectID` (`subjectID`),
  CONSTRAINT `mappings_parentid_fk` FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_mappings` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `mappings_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `mappings_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `mappings_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_methods` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID`       VARCHAR(60)
                    CHARACTER SET utf8
                    COLLATE utf8_bin          DEFAULT NULL,
  `abbreviation_de` VARCHAR(45)               DEFAULT '',
  `abbreviation_en` VARCHAR(45)               DEFAULT '',
  `name_de`         VARCHAR(255)              DEFAULT NULL,
  `name_en`         VARCHAR(255)              DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
  `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomID`           INT(11) UNSIGNED          DEFAULT NULL,
  `ip`               VARCHAR(15)      NOT NULL,
  `useDefaults`      TINYINT(1)       NOT NULL DEFAULT '0',
  `display`          INT(1) UNSIGNED  NOT NULL DEFAULT '1'
  COMMENT 'the display behaviour of the monitor',
  `schedule_refresh` INT(3) UNSIGNED  NOT NULL DEFAULT '60'
  COMMENT 'the amount of seconds before the schedule refreshes',
  `content_refresh`  INT(3) UNSIGNED  NOT NULL DEFAULT '60'
  COMMENT 'the amount of time in seconds before the content refreshes',
  `interval`         INT(1) UNSIGNED  NOT NULL DEFAULT '1'
  COMMENT 'the time interval in minutes between context switches',
  `content`          VARCHAR(256)              DEFAULT ''
  COMMENT 'the filename of the resource to the optional resource to be displayed',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`),
  KEY `roomID` (`roomID`),
  CONSTRAINT `monitors_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_pools` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR(60)
              CHARACTER SET utf8
              COLLATE utf8_bin NOT NULL,
  `poolID`    INT(11) UNSIGNED          DEFAULT NULL,
  `programID` INT(11) UNSIGNED          DEFAULT NULL,
  `fieldID`   INT(11) UNSIGNED          DEFAULT NULL,
  `name`      VARCHAR(100)     NOT NULL,
  `full_name` VARCHAR(100)     NOT NULL
  COMMENT 'The fully qualified name of the pool including the degree program to which it is associated.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`),
  UNIQUE KEY `dbID` (`gpuntisID`, `programID`),
  KEY `poolID` (`poolID`),
  KEY `programID` (`programID`),
  KEY `fieldID` (`fieldID`),
  CONSTRAINT `plan_pools_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `plan_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `plan_pools_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_plan_programs` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_programs` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR(60)
              CHARACTER SET utf8
              COLLATE utf8_bin NOT NULL,
  `programID` INT(11) UNSIGNED          DEFAULT NULL,
  `name`      VARCHAR(100)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`),
  KEY `plan_programs_programid_fk` (`programID`),
  CONSTRAINT `plan_programs_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_subjects` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID`    VARCHAR(60)
                 CHARACTER SET utf8
                 COLLATE utf8_bin NOT NULL,
  `fieldID`      INT(11) UNSIGNED          DEFAULT NULL,
  `subjectNo`    VARCHAR(45)      NOT NULL DEFAULT '',
  `name`         VARCHAR(100)     NOT NULL,
  `subjectIndex` VARCHAR(70)      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjectIndex` (`subjectIndex`),
  KEY `plan_subjects_fieldid_fk` (`fieldID`),
  KEY `gpuntisID` (`gpuntisID`),
  CONSTRAINT `plan_subjects_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_pools` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`        INT(11)          NOT NULL DEFAULT '0',
  `departmentID`    INT(11) UNSIGNED          DEFAULT NULL,
  `lsfID`           INT(11) UNSIGNED          DEFAULT NULL,
  `hisID`           INT(11) UNSIGNED          DEFAULT NULL,
  `externalID`      VARCHAR(45)               DEFAULT '',
  `description_de`  TEXT,
  `description_en`  TEXT,
  `abbreviation_de` VARCHAR(45)               DEFAULT '',
  `abbreviation_en` VARCHAR(45)               DEFAULT '',
  `short_name_de`   VARCHAR(45)               DEFAULT '',
  `short_name_en`   VARCHAR(45)               DEFAULT '',
  `name_de`         VARCHAR(255)              DEFAULT NULL,
  `name_en`         VARCHAR(255)              DEFAULT NULL,
  `minCrP`          INT(3) UNSIGNED           DEFAULT '0',
  `maxCrP`          INT(3) UNSIGNED           DEFAULT '0',
  `fieldID`         INT(11) UNSIGNED          DEFAULT NULL,
  `distance`        INT(2) UNSIGNED           DEFAULT '10',
  `display_type`    TINYINT(1)                DEFAULT '1',
  `enable_desc`     TINYINT(1)                DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `lsfID` (`lsfID`),
  KEY `externalID` (`externalID`),
  KEY `fieldID` (`fieldID`),
  KEY `pools_departmentid_fk` (`departmentID`),
  CONSTRAINT `pools_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `pools_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID`    INT(11) UNSIGNED NOT NULL,
  `prerequisite` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subjectID`, `prerequisite`),
  KEY `prerequisites_prerequisites_fk` (`prerequisite`),
  CONSTRAINT `prerequisites_prerequisites_fk` FOREIGN KEY (`prerequisite`) REFERENCES `#__thm_organizer_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `prerequisites_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_programs` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`       INT(11)          NOT NULL DEFAULT '0',
  `departmentID`   INT(11) UNSIGNED          DEFAULT NULL,
  `name_de`        VARCHAR(60)      NOT NULL,
  `name_en`        VARCHAR(60)      NOT NULL,
  `version`        YEAR(4)                   DEFAULT NULL,
  `code`           VARCHAR(20)               DEFAULT '',
  `degreeID`       INT(11) UNSIGNED          DEFAULT NULL,
  `fieldID`        INT(11) UNSIGNED          DEFAULT NULL,
  `description_de` TEXT,
  `description_en` TEXT,
  `frequencyID`    INT(1) UNSIGNED           DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lsfData` (`version`, `code`, `degreeID`),
  KEY `degreeID` (`degreeID`),
  KEY `fieldID` (`fieldID`),
  KEY `programs_departmentid_fk` (`departmentID`),
  KEY `programs_frequencyid_fk` (`frequencyID`),
  CONSTRAINT `programs_degreeid_fk` FOREIGN KEY (`degreeID`) REFERENCES `#__thm_organizer_degrees` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `programs_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `programs_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `programs_frequencyid_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR(1)       NOT NULL
  COMMENT 'The Untis internal ID',
  `name_de` VARCHAR(255)              DEFAULT NULL,
  `name_en` VARCHAR(255)              DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features_map` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomID`    INT(11) UNSIGNED NOT NULL,
  `featureID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `roomID` (`roomID`),
  KEY `featureID` (`featureID`),
  CONSTRAINT `room_features_map_featureid_fk` FOREIGN KEY (`featureID`) REFERENCES `#__thm_organizer_room_features` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `room_features_map_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_types` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID`      VARCHAR(60)
                   CHARACTER SET utf8
                   COLLATE utf8_bin          DEFAULT NULL,
  `name_de`        VARCHAR(50)      NOT NULL,
  `name_en`        VARCHAR(50)      NOT NULL,
  `description_de` TEXT             NOT NULL,
  `description_en` TEXT             NOT NULL,
  `min_capacity`   INT(4) UNSIGNED           DEFAULT NULL,
  `max_capacity`   INT(4) UNSIGNED           DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR(60)
              CHARACTER SET utf8
              COLLATE utf8_bin          DEFAULT NULL,
  `name`      VARCHAR(10)      NOT NULL,
  `longname`  VARCHAR(50)      NOT NULL DEFAULT '',
  `typeID`    INT(11) UNSIGNED          DEFAULT NULL,
  `capacity`  INT(4) UNSIGNED           DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`),
  KEY `typeID` (`typeID`),
  CONSTRAINT `rooms_typeid_fk` FOREIGN KEY (`typeID`) REFERENCES `#__thm_organizer_room_types` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`       INT(11)          NOT NULL DEFAULT '0',
  `departmentID`   INT(11) UNSIGNED          DEFAULT NULL,
  `departmentname` VARCHAR(50)      NOT NULL,
  `semestername`   VARCHAR(50)      NOT NULL,
  `creationdate`   DATE                      DEFAULT NULL,
  `creationtime`   TIME                      DEFAULT NULL,
  `description`    TEXT             NOT NULL,
  `schedule`       MEDIUMTEXT       NOT NULL,
  `active`         TINYINT(1)       NOT NULL DEFAULT '0',
  `startdate`      DATE                      DEFAULT NULL,
  `enddate`        DATE                      DEFAULT NULL,
  `term_startdate` DATE                      DEFAULT NULL,
  `term_enddate`   DATE                      DEFAULT NULL,
  `plan_name`      VARCHAR(50)      NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `semestername` (`semestername`),
  KEY `schedules_departmentid_fk` (`departmentID`),
  KEY `plan_name` (`plan_name`),
  CONSTRAINT `schedules_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_teachers` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID`   INT(11) UNSIGNED NOT NULL,
  `teacherID`   INT(11) UNSIGNED NOT NULL,
  `teacherResp` INT(11) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`subjectID`, `teacherID`, `teacherResp`),
  UNIQUE KEY `id` (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`),
  CONSTRAINT `subject_teachers_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `subject_teachers_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects` (
  `id`                           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`                     INT(11)          NOT NULL DEFAULT '0',
  `departmentID`                 INT(11) UNSIGNED          DEFAULT NULL,
  `lsfID`                        INT(11) UNSIGNED          DEFAULT NULL,
  `hisID`                        INT(11) UNSIGNED          DEFAULT NULL,
  `externalID`                   VARCHAR(45)      NOT NULL DEFAULT '',
  `abbreviation_de`              VARCHAR(45)      NOT NULL DEFAULT '',
  `abbreviation_en`              VARCHAR(45)      NOT NULL DEFAULT '',
  `short_name_de`                VARCHAR(45)      NOT NULL DEFAULT '',
  `short_name_en`                VARCHAR(45)      NOT NULL DEFAULT '',
  `name_de`                      VARCHAR(255)     NOT NULL,
  `name_en`                      VARCHAR(255)     NOT NULL,
  `description_de`               TEXT             NOT NULL,
  `description_en`               TEXT             NOT NULL,
  `objective_de`                 TEXT             NOT NULL,
  `objective_en`                 TEXT             NOT NULL,
  `content_de`                   TEXT             NOT NULL,
  `content_en`                   TEXT             NOT NULL,
  `prerequisites_de`             TEXT             NOT NULL,
  `prerequisites_en`             TEXT             NOT NULL,
  `preliminary_work_de`          TEXT             NOT NULL,
  `preliminary_work_en`          TEXT             NOT NULL,
  `instructionLanguage`          VARCHAR(2)       NOT NULL DEFAULT 'D',
  `literature`                   TEXT             NOT NULL,
  `creditpoints`                 INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `expenditure`                  INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `present`                      INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `independent`                  INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `proof_de`                     TEXT             NOT NULL,
  `proof_en`                     TEXT             NOT NULL,
  `frequencyID`                  INT(1) UNSIGNED           DEFAULT NULL,
  `method_de`                    TEXT             NOT NULL,
  `method_en`                    TEXT             NOT NULL,
  `fieldID`                      INT(11) UNSIGNED          DEFAULT NULL,
  `sws`                          INT(2) UNSIGNED  NOT NULL DEFAULT '0',
  `aids_de`                      TEXT             NOT NULL,
  `aids_en`                      TEXT             NOT NULL,
  `evaluation_de`                TEXT             NOT NULL,
  `evaluation_en`                TEXT             NOT NULL,
  `expertise`                    INT(1) UNSIGNED           DEFAULT NULL,
  `self_competence`              INT(1) UNSIGNED           DEFAULT NULL,
  `method_competence`            INT(1) UNSIGNED           DEFAULT NULL,
  `social_competence`            INT(1) UNSIGNED           DEFAULT NULL,
  `recommended_prerequisites_de` TEXT             NOT NULL,
  `recommended_prerequisites_en` TEXT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `frequencyID` (`frequencyID`),
  KEY `fieldID` (`fieldID`),
  KEY `subjects_departmentid_fk` (`departmentID`),
  CONSTRAINT `subjects_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `subjects_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `subjects_frequencyid_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR(60)
              CHARACTER SET utf8
              COLLATE utf8_bin          DEFAULT NULL,
  `surname`   VARCHAR(255)     NOT NULL,
  `forename`  VARCHAR(255)     NOT NULL DEFAULT '',
  `username`  VARCHAR(150)              DEFAULT NULL,
  `fieldID`   INT(11) UNSIGNED          DEFAULT NULL,
  `title`     VARCHAR(45)      NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`),
  KEY `username` (`username`),
  KEY `fieldID` (`fieldID`),
  CONSTRAINT `teachers_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_lessons` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID`      INT(11) UNSIGNED NOT NULL,
  `userID`        INT(11)          NOT NULL,
  `status`        INT(1) UNSIGNED           DEFAULT '0'
  COMMENT 'The user''s registration status. Possible values: 0 - pending, 1 - registered, 2 - denied.',
  `user_date`     DATETIME                  DEFAULT NULL
  COMMENT 'The last date of user action.',
  `status_date`   DATETIME                  DEFAULT NULL
  COMMENT 'The last date of status action.',
  `order`         INT(4) UNSIGNED           DEFAULT '0'
  COMMENT 'The order for automatic user registration actions.',
  `configuration` TEXT             NOT NULL
  COMMENT 'A configuration of the lessons visited should the added lessons be a subset of those offered.',
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`),
  KEY `userID` (`userID`),
  CONSTRAINT `user_lessons_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `user_lessons_userid_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules` (
  `username` VARCHAR(100)     NOT NULL,
  `created`  INT(11) UNSIGNED NOT NULL,
  `data`     MEDIUMBLOB       NOT NULL,
  PRIMARY KEY (`username`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

SET FOREIGN_KEY_CHECKS = 1;