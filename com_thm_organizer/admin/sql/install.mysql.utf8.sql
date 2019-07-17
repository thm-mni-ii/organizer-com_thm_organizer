SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_buildings` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campusID`     INT(11) UNSIGNED          DEFAULT NULL,
    `name`         VARCHAR(60)      NOT NULL,
    `location`     VARCHAR(20)      NOT NULL,
    `address`      VARCHAR(255)     NOT NULL,
    `propertyType` INT(1) UNSIGNED  NOT NULL DEFAULT '0'
        COMMENT '0 - new/unknown | 1 - owned | 2 - rented/leased',
    PRIMARY KEY (`id`),
    INDEX `campusID` (`campusID`),
    UNIQUE INDEX `prefix` (`campusID`, `name`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_calendar` (
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `schedule_date` DATE                      DEFAULT NULL,
    `startTime`     TIME                      DEFAULT NULL,
    `endTime`       TIME                      DEFAULT NULL,
    `lessonID`      INT(11) UNSIGNED NOT NULL,
    `delta`         VARCHAR(10)      NOT NULL DEFAULT '',
    `modified`      TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `lessonID` (`lessonID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_calendar_configuration_map` (
    `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `calendarID`      INT(11) UNSIGNED NOT NULL,
    `configurationID` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `calendarID` (`calendarID`),
    INDEX `configurationID` (`configurationID`),
    UNIQUE INDEX `entry` (`calendarID`, `configurationID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_campuses` (
    `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `parentID` INT(11) UNSIGNED          DEFAULT NULL,
    `name_de`  VARCHAR(60)      NOT NULL,
    `name_en`  VARCHAR(60)      NOT NULL,
    `isCity`   TINYINT(1)       NOT NULL DEFAULT '0',
    `location` VARCHAR(20)      NOT NULL,
    `address`  VARCHAR(255)     NOT NULL,
    `city`     VARCHAR(60)      NOT NULL,
    `zipCode`  VARCHAR(60)      NOT NULL,
    `gridID`   INT(11) UNSIGNED          DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `gridID` (`gridID`),
    INDEX `parentID` (`parentID`),
    UNIQUE INDEX `englishName` (`parentID`, `name_en`),
    UNIQUE INDEX `germanName` (`parentID`, `name_de`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_categories` (
    `id`        INT(11) UNSIGNED                                NOT NULL AUTO_INCREMENT,
    `untisID`   VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    `programID` INT(11) UNSIGNED DEFAULT NULL,
    `name`      VARCHAR(100)                                    NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `programID` (`programID`),
    UNIQUE INDEX `untisID` (`untisID`)
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_courses` (
    `id`           INT(11) UNSIGNED                                NOT NULL AUTO_INCREMENT,
    `untisID`      VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    `fieldID`      INT(11) UNSIGNED                                         DEFAULT NULL,
    `subjectNo`    VARCHAR(45)                                     NOT NULL DEFAULT '',
    `name`         VARCHAR(100)                                    NOT NULL,
    `subjectIndex` VARCHAR(70)                                     NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `plan_subjects_fieldid_fk` (`fieldID`),
    INDEX `untisID` (`untisID`),
    UNIQUE INDEX `subjectIndex` (`subjectIndex`)
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_department_resources` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `departmentID` INT(11) UNSIGNED NOT NULL,
    `categoryID`   INT(11) UNSIGNED DEFAULT NULL,
    `teacherID`    INT(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `categoryID` (`categoryID`),
    INDEX `departmentID` (`departmentID`),
    INDEX `teacherID` (`teacherID`)
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
    `contact_type`  TINYINT(1)       NOT NULL,
    `contactID`     INT(11) DEFAULT NULL,
    `contact_email` VARCHAR(100)     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `short_name` (`short_name_de`),
    UNIQUE INDEX `name` (`name_de`),
    UNIQUE INDEX `short_name_en` (`short_name_en`),
    UNIQUE INDEX `name_en` (`name_en`),
    INDEX `contactID` (`contactID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_fields` (
    `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `untisID`  VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    `field_de` VARCHAR(60)      NOT NULL                       DEFAULT '',
    `colorID`  INT(11) UNSIGNED                                DEFAULT NULL,
    `field_en` VARCHAR(100)     NOT NULL                       DEFAULT '',
    PRIMARY KEY (`id`),
    INDEX `colorID` (`colorID`),
    UNIQUE INDEX `untisID` (`untisID`)
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
    `defaultGrid` INT(1)           NOT NULL DEFAULT '0',
    `untisID`     VARCHAR(60)      NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_holidays` (
    `id`          INT(11)     UNSIGNED  NOT NULL     AUTO_INCREMENT,
    `name_de`     VARCHAR(50)           NOT NULL,
    `name_en`     VARCHAR(50)           NOT NULL,
    `startDate`   DATE                  NOT NULL,
    `endDate`     DATE                  NOT NULL,
    `type`        TINYINT(1)            NOT NULL
        COMMENT 'Type of Holiday in deciding the Planning Schedule. Possible values: 1 - Automatic, 2 - Manual, 3 - Unplannable.',
     PRIMARY KEY (`id`)
)
    ENGINE  =  InnoDB
    DEFAULT CHARSET  =  utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_group_publishing` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `groupID`   INT(11) UNSIGNED NOT NULL,
    `termID`    INT(11) UNSIGNED NOT NULL,
    `published` TINYINT(1)       NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    INDEX `groupID` (`groupID`),
    INDEX `termID` (`termID`),
    UNIQUE INDEX `entry` (`groupID`, `termID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_groups` (
    `id`         INT(11) UNSIGNED                                NOT NULL AUTO_INCREMENT,
    `untisID`    VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    `categoryID` INT(11) UNSIGNED DEFAULT NULL,
    `fieldID`    INT(11) UNSIGNED DEFAULT NULL,
    `gridID`     INT(11) UNSIGNED DEFAULT 1,
    `name`       VARCHAR(100)                                    NOT NULL,
    `full_name`  VARCHAR(100)                                    NOT NULL
        COMMENT 'The fully qualified name of the pool including the degree program to which it is associated.',
    PRIMARY KEY (`id`),
    INDEX `categoryID` (`categoryID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `gridID` (`gridID`),
    UNIQUE INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_configurations` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `lessonCourseID` INT(11) UNSIGNED NOT NULL,
    `configuration`  TEXT             NOT NULL
        COMMENT 'A configuration of teachers and rooms for a lesson, inclusive of their delta status.',
    `modified`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `lessonCourseID` (`lessonCourseID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_courses` (
    `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `lessonID` INT(11) UNSIGNED NOT NULL,
    `courseID` INT(11) UNSIGNED NOT NULL,
    `delta`    VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
    `modified` TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `lessonID` (`lessonID`),
    INDEX `courseID` (`courseID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_groups` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `lessonCourseID` INT(11) UNSIGNED NOT NULL,
    `groupID`        INT(11) UNSIGNED NOT NULL,
    `delta`          VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
    `modified`       TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `lessonCourseID` (`lessonCourseID`),
    INDEX `groupID` (`groupID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lesson_teachers` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `lessonCourseID` INT(11) UNSIGNED NOT NULL,
    `teacherID`      INT(11) UNSIGNED NOT NULL,
    `delta`          VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
    `modified`       TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `lessonCourseID` (`lessonCourseID`),
    INDEX `teacherID` (`teacherID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_lessons` (
    `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `untisID`           INT(11) UNSIGNED NOT NULL,
    `methodID`          INT(3) UNSIGNED           DEFAULT NULL
        COMMENT 'The method of instruction for this lesson unit.',
    `delta`             VARCHAR(10)      NOT NULL DEFAULT ''
        COMMENT 'The lesson''s delta status. Possible values: empty, new, removed.',
    `registration_type` INT(1) UNSIGNED           DEFAULT NULL
        COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.',
    `max_participants`  INT(4) UNSIGNED           DEFAULT NULL
        COMMENT 'The maximum number of participants. NULL is without limit.',
    `comment`           VARCHAR(200)              DEFAULT NULL,
    `departmentID`      INT(11) UNSIGNED          DEFAULT NULL,
    `termID`            INT(11) UNSIGNED          DEFAULT NULL,
    `campusID`          INT(11) UNSIGNED          DEFAULT NULL,
    `modified`          TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    `deadline`          INT(2) UNSIGNED           DEFAULT NULL
        COMMENT 'The deadline in days for registration before the course starts.',
    `fee`               INT(3) UNSIGNED           DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `departmentID` (`departmentID`),
    INDEX `methodID` (`methodID`),
    INDEX `termID` (`termID`),
    UNIQUE INDEX `planID` (`untisID`, `departmentID`, `termID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_mappings` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `programID` INT(11) UNSIGNED DEFAULT NULL,
    `parentID`  INT(11) UNSIGNED DEFAULT NULL,
    `poolID`    INT(11) UNSIGNED DEFAULT NULL,
    `subjectID` INT(11) UNSIGNED DEFAULT NULL,
    `lft`       INT(11) UNSIGNED DEFAULT NULL,
    `rgt`       INT(11) UNSIGNED DEFAULT NULL,
    `level`     INT(11) UNSIGNED DEFAULT NULL,
    `ordering`  INT(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `parentID` (`parentID`),
    INDEX `programID` (`programID`),
    INDEX `poolID` (`poolID`),
    INDEX `subjectID` (`subjectID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_methods` (
    `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `untisID`         VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    `abbreviation_de` VARCHAR(45)                                     DEFAULT '',
    `abbreviation_en` VARCHAR(45)                                     DEFAULT '',
    `name_de`         VARCHAR(255)                                    DEFAULT NULL,
    `name_en`         VARCHAR(255)                                    DEFAULT NULL,
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
    INDEX `roomID` (`roomID`),
    UNIQUE INDEX `ip` (`ip`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_participants` (
    `id`        INT(11)          NOT NULL,
    `forename`  VARCHAR(255)     NOT NULL DEFAULT '',
    `surname`   VARCHAR(255)     NOT NULL DEFAULT '',
    `city`      VARCHAR(60)      NOT NULL DEFAULT '',
    `address`   VARCHAR(60)      NOT NULL DEFAULT '',
    `zip_code`  INT(11)          NOT NULL DEFAULT 0,
    `programID` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `programID` (`programID`)
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
    PRIMARY KEY (`id`),
    INDEX `departmentID` (`departmentID`),
    INDEX `externalID` (`externalID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `lsfID` (`lsfID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_prerequisites` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `subjectID`      INT(11) UNSIGNED NOT NULL,
    `prerequisiteID` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`prerequisiteID`, `subjectID`),
    INDEX `prerequisiteID` (`prerequisiteID`),
    INDEX `subjectID` (`subjectID`)
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
    UNIQUE INDEX `lsfData` (`version`, `code`, `degreeID`),
    INDEX `degreeID` (`degreeID`),
    INDEX `departmentID` (`departmentID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `frequencyID` (`frequencyID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_types` (
    `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `untisID`        VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    `name_de`        VARCHAR(50)      NOT NULL,
    `name_en`        VARCHAR(50)      NOT NULL,
    `description_de` TEXT             NOT NULL,
    `description_en` TEXT             NOT NULL,
    `min_capacity`   INT(4) UNSIGNED                                 DEFAULT NULL,
    `max_capacity`   INT(4) UNSIGNED                                 DEFAULT NULL,
    `public`         TINYINT(1)       NOT NULL                       DEFAULT '1',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
    `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `buildingID` INT(11) UNSIGNED                                DEFAULT NULL,
    `untisID`    VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    `name`       VARCHAR(10)      NOT NULL,
    `typeID`     INT(11) UNSIGNED                                DEFAULT NULL,
    `capacity`   INT(4) UNSIGNED                                 DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`),
    INDEX `buildingID` (`buildingID`),
    INDEX `typeID` (`typeID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id`     INT(11)          NOT NULL DEFAULT '0',
    `departmentID` INT(11) UNSIGNED          DEFAULT NULL,
    `userID`       INT(11)                   DEFAULT NULL,
    `creationDate` DATE                      DEFAULT NULL,
    `creationTime` TIME                      DEFAULT NULL,
    `schedule`     MEDIUMTEXT       NOT NULL,
    `active`       TINYINT(1)       NOT NULL DEFAULT '0',
    `termID`       INT(11) UNSIGNED          DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `departmentID` (`departmentID`),
    INDEX `termID` (`termID`),
    INDEX `userID` (`userID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_mappings` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `subjectID` INT(11) UNSIGNED NOT NULL,
    `courseID`  INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`courseID`, `subjectID`),
    INDEX `courseID` (`courseID`),
    INDEX `subjectID` (`subjectID`)
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
    UNIQUE INDEX `id` (`id`),
    INDEX `subjectID` (`subjectID`),
    INDEX `teacherID` (`teacherID`)
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
    `method_de`                    TEXT,
    `method_en`                    TEXT,
    `fieldID`                      INT(11) UNSIGNED          DEFAULT NULL,
    `sws`                          INT(2) UNSIGNED  NOT NULL DEFAULT '0',
    `aids_de`                      TEXT,
    `aids_en`                      TEXT,
    `evaluation_de`                TEXT,
    `evaluation_en`                TEXT,
    `expertise`                    INT(1) UNSIGNED           DEFAULT NULL,
    `self_competence`              INT(1) UNSIGNED           DEFAULT NULL,
    `method_competence`            INT(1) UNSIGNED           DEFAULT NULL,
    `social_competence`            INT(1) UNSIGNED           DEFAULT NULL,
    `recommended_prerequisites_de` TEXT,
    `recommended_prerequisites_en` TEXT,
    `used_for_de`                  TEXT,
    `used_for_en`                  TEXT             NOT NULL,
    `duration`                     INT(2) UNSIGNED           DEFAULT 1,
    `is_prep_course`               INT(1) UNSIGNED  NOT NULL DEFAULT 1,
    `max_participants`             INT(4) UNSIGNED           DEFAULT NULL,
    `campusID`                     INT(11) UNSIGNED          DEFAULT NULL,
    `registration_type`            INT(1) UNSIGNED           DEFAULT NULL
        COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.',
    `bonus_points_de`              TEXT             NOT NULL,
    `bonus_points_en`              TEXT             NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `campusID` (`campusID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `frequencyID` (`frequencyID`),
    INDEX `departmentID` (`departmentID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_teachers` (
    `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `untisID`  VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    `surname`  VARCHAR(255)     NOT NULL,
    `forename` VARCHAR(255)     NOT NULL                       DEFAULT '',
    `username` VARCHAR(150)                                    DEFAULT NULL,
    `fieldID`  INT(11) UNSIGNED                                DEFAULT NULL,
    `title`    VARCHAR(45)      NOT NULL                       DEFAULT '',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`),
    INDEX `username` (`username`),
    INDEX `fieldID` (`fieldID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_terms` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`      VARCHAR(10)      NOT NULL,
    `startDate` DATE DEFAULT NULL,
    `endDate`   DATE DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `term` (`name`, `startDate`, `endDate`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_lessons` (
    `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `lessonID`      INT(11) UNSIGNED NOT NULL,
    `userID`        INT(11)          NOT NULL,
    `status`        INT(1) UNSIGNED  DEFAULT '0'
        COMMENT 'The user''s registration status. Possible values: 0 - pending, 1 - registered',
    `user_date`     DATETIME         DEFAULT NULL
        COMMENT 'The last date of user action.',
    `status_date`   DATETIME         DEFAULT NULL
        COMMENT 'The last date of status action.',
    `configuration` TEXT             NOT NULL
        COMMENT 'A configuration of the lessons visited should the added lessons be a subset of those offered.',
    `campusID`      INT(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `lessonID` (`lessonID`),
    INDEX `userID` (`userID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_buildings`
    ADD CONSTRAINT `buildings_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_calendar`
    ADD CONSTRAINT `calendar_lessonID_fk` FOREIGN KEY (`lessonID`)
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

ALTER TABLE `#__thm_organizer_campuses`
    ADD CONSTRAINT `campus_parentID_fk` FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `campus_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_categories`
    ADD CONSTRAINT `categories_programID_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_courses`
    ADD CONSTRAINT `courses_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
    ADD CONSTRAINT `department_resources_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `#__thm_organizer_categories` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `department_resources_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `department_resources_teacherID_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_departments`
    ADD CONSTRAINT `departments_contactID_fk` FOREIGN KEY (`contactID`) REFERENCES `#__users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_fields`
    ADD CONSTRAINT `fields_colorID_fk` FOREIGN KEY (`colorID`) REFERENCES `#__thm_organizer_colors` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_group_publishing`
    ADD CONSTRAINT `group_publishing_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `#__thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `group_publishing_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_groups`
    ADD CONSTRAINT `groups_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `#__thm_organizer_categories` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_configurations`
    ADD CONSTRAINT `lesson_configurations_lessonCourseID_fk` FOREIGN KEY (`lessonCourseID`)
        REFERENCES `#__thm_organizer_lesson_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_courses`
    ADD CONSTRAINT `lesson_courses_lessonID_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lesson_courses_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `#__thm_organizer_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_groups`
    ADD CONSTRAINT `lesson_groups_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `#__thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lesson_groups_lessonCourseID_fk` FOREIGN KEY (`lessonCourseID`) REFERENCES `#__thm_organizer_lesson_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_teachers`
    ADD CONSTRAINT `lesson_teachers_lessonCourseID_fk` FOREIGN KEY (`lessonCourseID`) REFERENCES `#__thm_organizer_lesson_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lesson_teachers_teacherID_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lessons`
    ADD CONSTRAINT `lessons_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lessons_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lessons_methodID_fk` FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lessons_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
    ADD CONSTRAINT `mappings_parentID_fk` FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `mappings_poolID_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_pools` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `mappings_programID_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `mappings_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_monitors`
    ADD CONSTRAINT `monitors_roomID_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_participants`
    ADD CONSTRAINT `participants_programID_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `participants_userID_fk` FOREIGN KEY (`id`) REFERENCES `#__users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
    ADD CONSTRAINT `pools_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `pools_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
    ADD CONSTRAINT `prerequisites_prerequisiteID_fk` FOREIGN KEY (`prerequisiteID`) REFERENCES `#__thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `prerequisites_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
    ADD CONSTRAINT `programs_degreeID_fk` FOREIGN KEY (`degreeID`) REFERENCES `#__thm_organizer_degrees` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `programs_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `programs_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `programs_frequencyID_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_rooms`
    ADD CONSTRAINT `rooms_buildingID_fk` FOREIGN KEY (`buildingID`) REFERENCES `#__thm_organizer_buildings` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `rooms_typeID_fk` FOREIGN KEY (`typeID`) REFERENCES `#__thm_organizer_room_types` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_schedules`
    ADD CONSTRAINT `schedules_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `schedules_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `schedules_userID_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_mappings`
    ADD CONSTRAINT `subject_mappings_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `#__thm_organizer_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_mappings_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
    ADD CONSTRAINT `subject_teachers_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_teachers_teacherID_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
    ADD CONSTRAINT `subjects_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subjects_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subjects_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subjects_frequencyID_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_teachers`
    ADD CONSTRAINT `teachers_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_user_lessons`
    ADD CONSTRAINT `user_lessons_lessonID_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `user_lessons_userID_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;