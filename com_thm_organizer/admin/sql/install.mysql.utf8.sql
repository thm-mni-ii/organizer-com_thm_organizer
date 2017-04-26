SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_calendar` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_date` DATE                      DEFAULT NULL,
  `startTime`     TIME                      DEFAULT NULL,
  `endTime`       TIME                      DEFAULT NULL,
  `lessonID`      INT(11) UNSIGNED NOT NULL,
  `delta`         VARCHAR(10)      NOT NULL DEFAULT '',
  `modified`      TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_calendar_configuration_map` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calendarID`      INT(11) UNSIGNED NOT NULL,
  `configurationID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`calendarID`, `configurationID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_colors` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_de` VARCHAR(60)      NOT NULL,
  `color`   VARCHAR(7)       NOT NULL,
  `name_en` VARCHAR(60)      NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_degrees` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(255)     NOT NULL,
  `abbreviation` VARCHAR(45)      NOT NULL DEFAULT '',
  `code`         VARCHAR(10)               DEFAULT '',
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`      INT(11)          NOT NULL,
  `short_name_de` VARCHAR(50)      NOT NULL,
  `name_de`       VARCHAR(150)     NOT NULL,
  `short_name_en` VARCHAR(50)      NOT NULL,
  `name_en`       VARCHAR(150)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_name` (`short_name_de`),
  UNIQUE KEY `name` (`name_de`),
  UNIQUE KEY `short_name_en` (`short_name_en`),
  UNIQUE KEY `name_en` (`name_en`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `roomID` (`roomID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_fields` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR(60)
              CHARACTER SET utf8
              COLLATE utf8_bin          DEFAULT NULL,
  `field_de`  VARCHAR(60)      NOT NULL DEFAULT '',
  `colorID`   INT(11) UNSIGNED          DEFAULT NULL,
  `field_en`  VARCHAR(100)     NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`),
  KEY `colorID` (`colorID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_frequencies` (
  `id`           INT(1) UNSIGNED NOT NULL,
  `frequency_de` VARCHAR(45)     NOT NULL,
  `frequency_en` VARCHAR(45)     NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_grids` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name_de`     VARCHAR(255)              DEFAULT NULL,
  `name_en`     VARCHAR(255)              DEFAULT NULL,
  `grid`        TEXT             NOT NULL
  COMMENT 'A grid object modeled by a JSON string, containing the respective start and end times of the grid blocks.',
  `defaultGrid` INT(1)           NOT NULL DEFAULT '0'
  COMMENT 'True if the grid is displayed by default.',
  `gpuntisID`   VARCHAR(60)      NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
  `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID`         INT(11) UNSIGNED NOT NULL,
  `methodID`          INT(3) UNSIGNED           DEFAULT NULL
  COMMENT 'The method of instruction for this lesson unit.',
  `delta`             VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  `registration_type` INT(1) UNSIGNED           DEFAULT '0'
  COMMENT 'The method of registration for the lesson. Possible values: 0 - FIFO, 1 - Manual.',
  `max_participants`  INT(4) UNSIGNED           DEFAULT NULL
  COMMENT 'The maximum number of participants. NULL is without limit.',
  `comment`           VARCHAR(200)              DEFAULT NULL,
  `departmentID`      INT(11) UNSIGNED          DEFAULT NULL,
  `planningPeriodID`  INT(11) UNSIGNED          DEFAULT NULL,
  `modified`          TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `planID` (`gpuntisID`, `departmentID`, `planningPeriodID`),
  KEY `methodID` (`methodID`),
  KEY `lessons_departmentid_fk` (`departmentID`),
  KEY `lessons_planningperiodid_fk` (`planningPeriodID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_configurations` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID`      INT(11) UNSIGNED NOT NULL,
  `configuration` TEXT             NOT NULL
  COMMENT 'A configuration of teachers and rooms for a lesson, inclusive of their delta status.',
  `modified`      TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_pools` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `poolID`    INT(11) UNSIGNED NOT NULL,
  `delta`     VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  `modified`  TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `poolID` (`poolID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_subjects` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lessonID`  INT(11) UNSIGNED NOT NULL,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `delta`     VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  `modified`  TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `lessonID` (`lessonID`),
  KEY `subjectID` (`subjectID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID` INT(11) UNSIGNED NOT NULL,
  `teacherID` INT(11) UNSIGNED NOT NULL,
  `delta`     VARCHAR(10)      NOT NULL DEFAULT ''
  COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
  `modified`  TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `subjectID` (`subjectID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `roomID` (`roomID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_planning_periods` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`      VARCHAR(10)      NOT NULL,
  `startDate` DATE                      DEFAULT NULL,
  `endDate`   DATE                      DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pp_long` (`name`, `startDate`, `endDate`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_pool_publishing` (
  `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `planPoolID`       INT(11) UNSIGNED NOT NULL,
  `planningPeriodID` INT(11) UNSIGNED NOT NULL,
  `published`        TINYINT(1)       NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`planPoolID`, `planningPeriodID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `fieldID` (`fieldID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_programs` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gpuntisID` VARCHAR(60)
              CHARACTER SET utf8
              COLLATE utf8_bin NOT NULL,
  `programID` INT(11) UNSIGNED          DEFAULT NULL,
  `name`      VARCHAR(100)     NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gpuntisID` (`gpuntisID`),
  KEY `plan_programs_programid_fk` (`programID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `gpuntisID` (`gpuntisID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `pools_departmentid_fk` (`departmentID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID`    INT(11) UNSIGNED NOT NULL,
  `prerequisite` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subjectID`, `prerequisite`),
  KEY `prerequisites_prerequisites_fk` (`prerequisite`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `programs_frequencyid_fk` (`frequencyID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `typeID` (`typeID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `untisID` VARCHAR(1)       NOT NULL
  COMMENT 'The Untis internal ID',
  `name_de` VARCHAR(255)              DEFAULT NULL,
  `name_en` VARCHAR(255)              DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_features_map` (
  `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roomID`    INT(11) UNSIGNED NOT NULL,
  `featureID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `roomID` (`roomID`),
  KEY `featureID` (`featureID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
  `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id`         INT(11)          NOT NULL DEFAULT '0',
  `departmentID`     INT(11) UNSIGNED          DEFAULT NULL,
  `departmentname`   VARCHAR(50)      NOT NULL,
  `semestername`     VARCHAR(50)      NOT NULL,
  `creationDate`     DATE                      DEFAULT NULL,
  `creationTime`     TIME                      DEFAULT NULL,
  `schedule`         MEDIUMTEXT       NOT NULL,
  `active`           TINYINT(1)       NOT NULL DEFAULT '0',
  `startDate`        DATE                      DEFAULT NULL,
  `endDate`          DATE                      DEFAULT NULL,
  `planningPeriodID` INT(11) UNSIGNED          DEFAULT NULL,
  `newSchedule`      MEDIUMTEXT       NOT NULL,
  PRIMARY KEY (`id`),
  KEY `semestername` (`semestername`),
  KEY `schedules_departmentid_fk` (`departmentID`),
  KEY `schedules_planningperiodid_fk` (`planningPeriodID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  `description_de`               TEXT             NOT NULL DEFAULT '',
  `description_en`               TEXT             NOT NULL DEFAULT '',
  `objective_de`                 TEXT             NOT NULL DEFAULT '',
  `objective_en`                 TEXT             NOT NULL DEFAULT '',
  `content_de`                   TEXT             NOT NULL DEFAULT '',
  `content_en`                   TEXT             NOT NULL DEFAULT '',
  `prerequisites_de`             TEXT             NOT NULL DEFAULT '',
  `prerequisites_en`             TEXT             NOT NULL DEFAULT '',
  `preliminary_work_de`          TEXT             NOT NULL DEFAULT '',
  `preliminary_work_en`          TEXT             NOT NULL DEFAULT '',
  `instructionLanguage`          VARCHAR(2)       NOT NULL DEFAULT 'D',
  `literature`                   TEXT             NOT NULL DEFAULT '',
  `creditpoints`                 INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `expenditure`                  INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `present`                      INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `independent`                  INT(4) UNSIGNED  NOT NULL DEFAULT '0',
  `proof_de`                     TEXT             NOT NULL DEFAULT '',
  `proof_en`                     TEXT             NOT NULL DEFAULT '',
  `frequencyID`                  INT(1) UNSIGNED           DEFAULT NULL,
  `method_de`                    TEXT             NOT NULL DEFAULT '',
  `method_en`                    TEXT             NOT NULL DEFAULT '',
  `fieldID`                      INT(11) UNSIGNED          DEFAULT NULL,
  `sws`                          INT(2) UNSIGNED  NOT NULL DEFAULT '0',
  `aids_de`                      TEXT             NOT NULL DEFAULT '',
  `aids_en`                      TEXT             NOT NULL DEFAULT '',
  `evaluation_de`                TEXT             NOT NULL DEFAULT '',
  `evaluation_en`                TEXT             NOT NULL DEFAULT '',
  `expertise`                    INT(1) UNSIGNED           DEFAULT NULL,
  `self_competence`              INT(1) UNSIGNED           DEFAULT NULL,
  `method_competence`            INT(1) UNSIGNED           DEFAULT NULL,
  `social_competence`            INT(1) UNSIGNED           DEFAULT NULL,
  `recommended_prerequisites_de` TEXT             NOT NULL DEFAULT '',
  `recommended_prerequisites_en` TEXT             NOT NULL DEFAULT '',
  `used_for_de`                  TEXT             NOT NULL DEFAULT '',
  `used_for_en`                  TEXT             NOT NULL DEFAULT '',
  `duration`                     INT(2) UNSIGNED           DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `frequencyID` (`frequencyID`),
  KEY `fieldID` (`fieldID`),
  KEY `subjects_departmentid_fk` (`departmentID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_mappings` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID`      INT(11) UNSIGNED NOT NULL,
  `plan_subjectID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`subjectID`, `plan_subjectID`),
  KEY `subject_mappings_plan_subjectID_fk` (`plan_subjectID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_teachers` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `subjectID`   INT(11) UNSIGNED NOT NULL,
  `teacherID`   INT(11) UNSIGNED NOT NULL,
  `teacherResp` INT(11) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`subjectID`, `teacherID`, `teacherResp`),
  UNIQUE KEY `id` (`id`),
  KEY `subjectID` (`subjectID`),
  KEY `teacherID` (`teacherID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `fieldID` (`fieldID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

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
  KEY `userID` (`userID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_schedules` (
  `username` VARCHAR(100)     NOT NULL,
  `created`  INT(11) UNSIGNED NOT NULL,
  `data`     MEDIUMBLOB       NOT NULL,
  PRIMARY KEY (`username`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_calendar`
  ADD CONSTRAINT `calendar_lessonid_fk` FOREIGN KEY (`lessonID`)
REFERENCES `#__thm_organizer_lessons` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_calendar_configuration_map`
  ADD CONSTRAINT `calendar_configuration_map_calendarID_fk` FOREIGN KEY (`calendarID`)
REFERENCES `#__thm_organizer_calendar` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `calendar_configuration_map_configurationID_fk` FOREIGN KEY (`configurationID`)
REFERENCES `#__thm_organizer_lesson_configurations` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
  ADD CONSTRAINT `department_resources_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `department_resources_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `department_resources_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_plan_programs` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `department_resources_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `department_resources_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `department_resources_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_fields`
  ADD CONSTRAINT `fields_colorid_fk` FOREIGN KEY (`colorID`) REFERENCES `#__thm_organizer_colors` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lessons`
  ADD CONSTRAINT `lessons_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `lessons_planningperiodid_fk` FOREIGN KEY (`planningPeriodID`) REFERENCES `#__thm_organizer_planning_periods` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `lessons_methodid_fk` FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_configurations`
  ADD CONSTRAINT `lesson_configurations_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lesson_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_pools`
  ADD CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `lesson_pools_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_lesson_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_subjects`
  ADD CONSTRAINT `lesson_subjects_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `lesson_subjects_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_teachers`
  ADD CONSTRAINT `lesson_teachers_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_lesson_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `lesson_teachers_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
  ADD CONSTRAINT `mappings_parentid_fk` FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_mappings` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `mappings_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `mappings_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `mappings_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_monitors`
  ADD CONSTRAINT `monitors_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pool_publishing`
  ADD CONSTRAINT `plan_pool_publishing_planpoolid_fk` FOREIGN KEY (`planPoolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `plan_pool_publishing_planningperiodid_fk` FOREIGN KEY (`planningPeriodID`)
REFERENCES `#__thm_organizer_planning_periods` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pools`
  ADD CONSTRAINT `plan_pools_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `plan_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `plan_pools_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_plan_programs` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_programs`
  ADD CONSTRAINT `plan_programs_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_subjects`
  ADD CONSTRAINT `plan_subjects_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
  ADD CONSTRAINT `pools_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `pools_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
  ADD CONSTRAINT `prerequisites_prerequisites_fk` FOREIGN KEY (`prerequisite`) REFERENCES `#__thm_organizer_mappings` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `prerequisites_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_mappings` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
  ADD CONSTRAINT `programs_degreeid_fk` FOREIGN KEY (`degreeID`) REFERENCES `#__thm_organizer_degrees` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `programs_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `programs_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `programs_frequencyid_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_rooms`
  ADD CONSTRAINT `rooms_typeid_fk` FOREIGN KEY (`typeID`) REFERENCES `#__thm_organizer_room_types` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_room_features_map`
  ADD CONSTRAINT `room_features_map_featureid_fk` FOREIGN KEY (`featureID`) REFERENCES `#__thm_organizer_room_features` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `room_features_map_roomid_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_schedules`
  ADD CONSTRAINT `schedules_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `schedules_planningperiodid_fk` FOREIGN KEY (`planningPeriodID`) REFERENCES `#__thm_organizer_planning_periods` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
  ADD CONSTRAINT `subjects_departmentid_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `subjects_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE,
  ADD CONSTRAINT `subjects_frequencyid_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_mappings`
  ADD CONSTRAINT `subject_mappings_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `subject_mappings_plan_subjectID_fk` FOREIGN KEY (`plan_subjectID`) REFERENCES `#__thm_organizer_plan_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
  ADD CONSTRAINT `subject_teachers_subjectid_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `subject_teachers_teacherid_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_teachers`
  ADD CONSTRAINT `teachers_fieldid_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_user_lessons`
  ADD CONSTRAINT `user_lessons_lessonid_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `user_lessons_userid_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

INSERT INTO `#__thm_organizer_colors` (`name_de`, `color`, `name_en`) VALUES
  ('THM Hintergrundgruen', '#cce3a7', 'THM Hintergrundgruen'),
  ('THM Hintergrundgrau', '#b7bec2', 'THM Hintergrundgrau'),
  ('THM Hintergrundrot', '#e199ad', 'THM Hintergrundrot'),
  ('THM Hintergrundgelb', '#fde499', 'THM Hintergrundgelb'),
  ('THM Hintergrundcyan', '#99e1f1', 'THM Hintergrundcyan'),
  ('THM Hintergrundblau', '#99b4d0', 'THM Hintergrundblau'),
  ('THM hellgruen', '#9bd641', 'THM hellgruen'),
  ('THM hellgrau', '#6b7e88', 'THM hellgrau'),
  ('THM hellrot', '#d32154', 'THM hellrot'),
  ('THM hellgelb', '#ffca30', 'THM hellgelb'),
  ('THM hellcyan', '#1dd1f9', 'THM hellcyan'),
  ('THM hellblau', '#2568ae', 'THM hellblau'),
  ('THM gruen', '#80ba24', 'THM gruen'),
  ('THM rot', '#b30033', 'THM rot'),
  ('THM gelb', '#fbbb00', 'THM gelb'),
  ('THM cyanm', '#00b5dd', 'THM cyanm'),
  ('THM mittelgruen', '#71a126', 'THM mittelgruen'),
  ('THM mittelgrau', '#44535b', 'THM mittelgrau'),
  ('THM mittelrot', '#990831', 'THM mittelrot'),
  ('THM mittelgelb', '#d7a30b', 'THM mittelgelb'),
  ('THM mittelcyan', '#099cbd', 'THM mittelcyan'),
  ('THM mittelblau', '#063d76', 'THM mittelblau'),
  ('THM dunkelgruen', '#638929', 'THM dunkelgruen'),
  ('THM dunkelgrau', '#3d494f', 'THM dunkelgrau'),
  ('THM dunkelrot', '#810e2f', 'THM dunkelrot');

INSERT INTO `#__thm_organizer_degrees` (`name`, `abbreviation`, `code`) VALUES
  ('Bachelor of Engineering', 'B.Eng.', 'BE'),
  ('Bachelor of Science', 'B.Sc.', 'BS'),
  ('Bachelor of Arts', 'B.A.', 'BA'),
  ('Master of Engineering', 'M.Eng.', 'ME'),
  ('Master of Science', 'M.Sc.', 'MS'),
  ('Master of Arts', 'M.A.', 'MA'),
  ('Master of Business Administration and Engineering', 'M.B.A.', 'MB');

INSERT INTO `#__thm_organizer_fields` (`gpuntisID`, `field_de`, `colorID`, `field_en`) VALUES
  ('BAU', 'Bauwesen', 9, 'Bauwesen'),
  ('BI.BAU', 'Bauinformatik', NULL, 'Bauinformatik'),
  ('BPBSK.BAU', 'Bauphysik & Baustoffkunde', NULL, 'Bauphysik & Baustoffkunde'),
  ('DIV', 'Diverse', 8, 'Diverse'),
  ('INF', 'Informatik', 2, 'Informatik'),
  ('ING', 'Ingenieurwesen', 11, 'Ingenieurwesen'),
  ('ING-INF', 'Ingenieur-Informatik', 5, 'Ingenieur-Informatik'),
  ('REC', 'Recht', NULL, 'Recht'),
  ('SPR', 'Sprachen', NULL, 'Sprachen'),
  ('MAT', 'Mathematik', 7, 'Mathematik'),
  ('MAT-INF', 'Theoretische Informatik', 1, 'Theoretische Informatik'),
  ('MAT-NAT', 'Mathematik / Naturwissenschaften', NULL, 'Mathematik / Naturwissenschaften'),
  ('MED', 'Medizin', 15, 'Medizin'),
  ('MED-INF', 'Medizinische Informatik', 4, 'Medizinische Informatik'),
  ('MED-ING', 'Medizinisches Ingenieurwesen', 13, 'Medizinisches Ingenieurwesen'),
  ('MED-NAT', 'Medizinische Naturwissenschaft', NULL, 'Medizinische Naturwissenschaft'),
  ('NAT', 'Naturwissenschaft', 12, 'Naturwissenschaft'),
  ('NAT-INF', 'Naturwissenschaft / Informatik', 6, 'Naturwissenschaft / Informatik'),
  ('NAT-ING', 'Naturwissenschaften / Ingenieurwesen', 22, 'Naturwissenschaften / Ingenieurwesen'),
  ('SOZ', 'Sozialwissenschaften', 20, 'Sozialwissenschaften'),
  ('WIR', 'Wirtschaft', 21, 'Wirtschaft'),
  ('WIR-INF', 'Wirtschaftsinformatik', 1, 'Wirtschaftsinformatik'),
  ('WIR-ING', 'Wirtschaftliches Ingenieurwesen', NULL, 'Wirtschaftliches Ingenieurwesen'),
  ('WIR-JUR', 'Wirtschaftsrecht', 1, 'Wirtschaftsrecht'),
  ('WIR-MAT', 'Wirtschaftsmathematik', NULL, 'Wirtschaftsmathematik'),
  ('WIR-SOZ', 'WIrtschaftssoziologie', NULL, 'WIrtschaftssoziologie'),
  ('WIR-MED', 'Wirschaftliches Gesundheitswesen', 1, 'Wirschaftliches Gesundheitswesen'),
  ('MAT-ING', 'Mathematik / Ingenieurwesen', NULL, 'Mathematik / Ingenieurwesen'),
  ('MED-SOZ', 'Medizinische Soziologie', NULL, 'Medizinische Soziologie'),
  ('WIR-CNT', 'Controlling', 1, 'Controlling'),
  ('WIR-FDL', 'Finanzdienstleistungen', 1, 'Finanzdienstleistungen'),
  ('WIR-IMN', 'Internationales Management', 1, 'Internationales Management'),
  ('WIR-MKT', 'Marketing', 1, 'Marketing'),
  ('WIR-MST', 'Mittelstand', 1, 'Mittelstand'),
  ('WIR-PSW', 'Personalwesen', 1, 'Personalwesen'),
  ('WIR-SBWP', 'Steuerberatung & Wirtschaftsprüfung', 1, 'Steuerberatung & Wirtschaftsprüfung'),
  ('PHY', 'Physik', NULL, 'Physik'),
  ('TEC', 'Eventtechnik', NULL, 'Eventtechnik'),
  ('TECHKOMM', 'Technische Kommunikation', NULL, 'Technische Kommunikation'),
  ('SOZ-NAT', 'Sozialwissenschaften / Naturwissenschaft', NULL, 'Sozialwissenschaften / Naturwissenschaft'),
  ('BWL.EMT', 'Betriebswirtschaft', NULL, 'Betriebswirtschaft'),
  ('BAUM', 'Baumaßnahmen', NULL, 'Baumaßnahmen'),
  ('FM', 'Facility Management', NULL, 'Facility Management'),
  ('IO', 'International Office', NULL, 'International Office'),
  ('HSP', 'Hochschulsport', NULL, 'Hochschulsport'),
  ('THMO', 'THM Orchester', NULL, 'THM Orchester'),
  ('DS', 'Datenschutz', NULL, 'Datenschutz'),
  ('MED-MAN', 'Medizinisches Management', NULL, 'Medizinisches Management'),
  ('PFLEGE', 'Pflegewissenschaften', NULL, 'Pflegewissenschaften'),
  ('ARC.BAU', 'Architektur', NULL, 'Architektur'),
  ('BB.BAU', 'Bauen im Bestand', NULL, 'Bauen im Bestand'),
  ('BK.BAU', 'Baukonstruktion', NULL, 'Baukonstruktion'),
  ('BMPS.BAU', 'Baumanagement & Projektsteuerung', NULL, 'Baumanagement & Projektsteuerung'),
  ('BST.BAU', 'Baustatik', NULL, 'Baustatik'),
  ('EWK.BAU', 'Entwerfen & Konstruieren', NULL, 'Entwerfen & Konstruieren'),
  ('GTVM.BAU', 'Geotechnik & Vermessung', NULL, 'Geotechnik & Vermessung'),
  ('SBST.BAU', 'Städtebau & Stadttheorie', NULL, 'Städtebau & Stadttheorie'),
  ('VKT.BAU', 'Verkehrstechnik', NULL, 'Verkehrstechnik'),
  ('SiWaWi.BAU', 'Siedlungswasserwirtschaft', NULL, 'Siedlungswasserwirtschaft'),
  ('MED-JUR', 'Medizin / Jura', NULL, 'Medizin / Jura'),
  ('IWW', 'Interne Wissenschaftliche Weiterbildung', NULL, 'Interne Wissenschaftliche Weiterbildung'),
  ('SOZ-INF', 'Sozialwissenschaften / Informatik', NULL, 'Sozialwissenschaften / Informatik'),
  ('WIR-NAT', 'Wirtschaft / Naturwissenschaften', NULL, 'Wirtschaft / Naturwissenschaften'),
  ('MSK', 'Musik', NULL, 'Music'),
  ('AGKM', 'AG Kommunikation und Marketing', NULL, 'Communication and Marketing Team'),
  ('ZEuUS', 'Zentrum für Energie- und Umweltsystemtechnik', NULL, 'Center for Energy and Environmental Technology');

INSERT INTO `#__thm_organizer_frequencies` (`id`, `frequency_de`, `frequency_en`) VALUES
  (0, 'Nach Termin', 'By Appointment'),
  (1, 'Nur im Sommersemester', 'Only Spring/Summer Term'),
  (2, 'Nur im Wintersemester', 'Only Fall/Winter Term'),
  (3, 'Jedes Semester', 'Semesterly'),
  (4, 'Nach Bedarf', 'As Needed'),
  (5, 'Einmal im Jahr', 'Yearly');

INSERT INTO `#__thm_organizer_grids` (`name_de`, `name_en`, `grid`, `defaultGrid`, `gpuntisID`) VALUES
  ('Haupt-Zeitraster', 'Haupt-Zeitraster',
   '{"periods":{"1":{"startTime":"0800","endTime":"0930"},"2":{"startTime":"0950","endTime":"1120"},"3":{"startTime":"1130","endTime":"1300"},"4":{"startTime":"1400","endTime":"1530"},"5":{"startTime":"1545","endTime":"1715"},"6":{"startTime":"1730","endTime":"1900"}},"startDay":1,"endDay":6}',
   1, 'Haupt-Zeitraster'),
  ('Klausurwochen', 'Klausurwochen',
   '{"periods":{"1":{"startTime":"0800","endTime":"0959"},"2":{"startTime":"1000","endTime":"1159"},"3":{"startTime":"1200","endTime":"1359"},"4":{"startTime":"1400","endTime":"1559"},"5":{"startTime":"1600","endTime":"1759"},"6":{"startTime":"1800","endTime":"1959"}},"startDay":1,"endDay":6}',
   0, 'Klausurwochen'),
  ('keine', 'none', '{"startDay":1,"endDay":6}', 0, 'keine');

INSERT INTO `#__thm_organizer_methods` (`gpuntisID`, `abbreviation_de`, `abbreviation_en`, `name_de`, `name_en`) VALUES
  ('AÜB', 'AÜB', 'PEX', 'Anwesenheitsübung', 'Presence Exercise'),
  ('BKR', 'BKR', 'RFC', 'Brückenkurs', 'Refresher Course'),
  ('KAB', 'KAB', 'CWK', 'Konstruktionsarbeit', 'Construction Work'),
  ('KES', 'KES', 'FRV', 'Klausureinsicht', 'Final Review'),
  ('KLA', 'KLA', 'FIN', 'Klausur', 'Final'),
  ('KTU', 'KTU', 'CTU', 'Konstruktionstutorium', 'Construction Tutorium'),
  ('KÜB', 'KÜB', 'CEX', 'Konstruktionsübung', 'Construction Exercise'),
  ('KVB', 'KVB', 'FPR', 'Klausurvorbereitung', 'Final Preparation'),
  ('LAB', 'LAB', 'LAB', 'Labor', 'Lab Exercise'),
  ('LAB/ÜBG', 'LAB/ÜBG', 'LAB/EXC', 'Labor / Übung', 'Lab Exercise / Exercise'),
  ('LKT', 'LKT', 'LCT', 'Lernkontrolle', 'Learning Control'),
  ('PRK', 'PRK', 'PRC', 'Praktikum', 'Practice'),
  ('PRÜ', 'PRÜ', 'EXM', 'Prüfung', 'Examination'),
  ('RÜB', 'RÜB', 'CEX', 'Rechenübung', 'Computational Exercise'),
  ('SEM', 'SEM', 'SEM', 'Seminar', 'Seminar'),
  ('SEM/PRK', 'SEM/PRK', 'SEM/PRC', 'Seminar/Praktikum', 'Seminar/Practice'),
  ('SMU', 'SMU', 'GDS', 'Seminaristische Unterricht', 'Guided Discussion'),
  ('TUT', 'TUT', 'TUT', 'Tutorium', 'Tutorium'),
  ('ÜBG', 'ÜBG', 'EXC', 'Übung', 'Exercise'),
  ('VKR', 'VKR', 'PCR', 'Vorkurs', 'Introductory Course'),
  ('VRL', 'VRL', 'LCT', 'Vorlesung', 'Lecture'),
  ('VRL/PRK', 'VRL/PRK', 'LCT/PRC', 'Vorlesung/Praktikum', 'Lecture/Practice'),
  ('VRL/PRK/SEM', 'VRL/PRK/SEM', 'LCT/PRC/SEM', 'Vorlesung/Praktikum/Seminar', 'Lecture/Practice/Seminar'),
  ('VRL/PRK/ÜBG', 'VRL/PRK/ÜBG', 'LCT/PRC/EXC', 'Vorlesung/Praktikum/Übung', 'Lecture/Practice/Exercise'),
  ('VRL/SEM', 'VRL/SEM', 'LCT/SEM', 'Vorlesung/Seminar', 'Lecture/Seminar'),
  ('VRL/ÜBG', 'VRL/ÜBG', 'LCT/EXC', 'Vorlesung/Übung', 'Lecture/Exercise'),
  ('WPF', 'WPF', 'ELC', 'Wahlpflicht', 'Elective'),
  ('WPF/PRK', 'WPF/PRK', 'ELC/PRC', 'Wahlpflicht/Praktikum', 'Elective/Practice'),
  ('WPF/VRL', 'WPF/VRL', 'ELC/LCT', 'Wahlpflicht/Vorlesung', 'Elective/Lecture'),
  ('PRK/ÜBG', 'PRK/ÜBG', 'PRC/EXC', 'Praktikum / Übung', 'Practice / Exercise'),
  ('ÜBG/PRK', 'ÜBG/PRK', 'EXC/PRC', 'Übung / Praktikum', 'Exercise / Practise');

INSERT INTO `#__thm_organizer_room_types` (`gpuntisID`, `name_de`, `name_en`, `description_de`, `description_en`, `min_capacity`, `max_capacity`)
VALUES
  ('SR.K', 'Seminarraum, Klein', 'Seminar Room, Small', '', '', NULL, 19),
  ('SR.M', 'Seminarraum, Mittel', 'Seminar Room, Medium', '', '', 20, 59),
  ('HS.K', 'Hörsaal, Klein', 'Lecture Hall, Small', '', '', NULL, 69),
  ('HS.M', 'Hörsaal, mittel', 'Lecture Hall, Medium', '', '', 70, 89),
  ('HS.G', 'Hörsaal, Groß', 'Lecture Hall, Large', '', '', 90, 199),
  ('HS.A', 'Hörsaal, Auditorium', 'Lecture Hall, Auditorium', '', '', 200, NULL),
  ('FS.P', 'Fachsaal, Physik', 'Physics Hall', '', '', NULL, NULL),
  ('FS.C', 'Fachsaal, Chemie', 'Chemistry Hall', '', '', NULL, NULL),
  ('LAR', 'Labor', 'Laboratory', '', '', NULL, NULL),
  ('PCL', 'PC Labor', 'PC Laboratory', '', '', NULL, NULL),
  ('GAR', 'Gruppenarbeitsraum', 'Groupwork Room', '', '', NULL, NULL),
  ('BR', 'Büro', 'Office', '', '', NULL, NULL),
  ('PUM', 'Philipps-Universität Marburg', 'Philipps University Marburg', '', '', NULL, NULL),
  ('XRM', 'Raumtyp unbekannt', 'Unknown Room Type', '', '', NULL, NULL),
  ('FUAS', 'Frankfurt UAS', 'Frankfurt UAS', '', '', NULL, NULL),
  ('SR.G', 'Seminarraum, Groß', 'Seminar Room, Large', '', '', 60, NULL),
  ('EXT', 'externer Raum', 'External Room', '', '', NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
