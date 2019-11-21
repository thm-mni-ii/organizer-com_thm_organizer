SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `#__thm_organizer_blocks` (
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_buildings` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campusID`     INT(11) UNSIGNED          DEFAULT NULL,
    `name`         VARCHAR(60)      NOT NULL,
    `location`     VARCHAR(20)      NOT NULL,
    `address`      VARCHAR(255)     NOT NULL,
    `propertyType` INT(1) UNSIGNED  NOT NULL DEFAULT 0
        COMMENT '0 - new/unknown | 1 - owned | 2 - rented/leased',
    PRIMARY KEY (`id`),
    INDEX `campusID` (`campusID`),
    UNIQUE INDEX `prefix` (`campusID`, `name`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_campuses` (
    `id`       INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `parentID` INT(11) UNSIGNED             DEFAULT NULL,
    `name_de`  VARCHAR(60)         NOT NULL,
    `name_en`  VARCHAR(60)         NOT NULL,
    `isCity`   TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `location` VARCHAR(20)         NOT NULL,
    `address`  VARCHAR(255)        NOT NULL,
    `city`     VARCHAR(60)         NOT NULL,
    `zipCode`  VARCHAR(60)         NOT NULL,
    `gridID`   INT(11) UNSIGNED             DEFAULT NULL,
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
    `id`      INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `untisID` VARCHAR(60)                  DEFAULT NULL,
    `name`    VARCHAR(100)        NOT NULL,
    `active`  TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_colors` (
    `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_de` VARCHAR(60)      NOT NULL,
    `name_en` VARCHAR(60)      NOT NULL,
    `color`   VARCHAR(7)       NOT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_course_participants` (
    `id`              INT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `courseID`        INT(11) UNSIGNED NOT NULL,
    `participantID`   INT(11)          NOT NULL,
    `participantDate` DATETIME            DEFAULT NULL COMMENT 'The last date of participant action.',
    `status`          TINYINT(1) UNSIGNED DEFAULT 0 COMMENT 'Possible values: 0 - pending, 1 - registered',
    `statusDate`      DATETIME            DEFAULT NULL COMMENT 'The last date of status action.',
    `paid`            TINYINT(1) UNSIGNED DEFAULT 0 COMMENT 'Possible values: 0 - unpaid, 1 - paid',
    PRIMARY KEY (`id`),
    INDEX `courseID` (`courseID`),
    INDEX `participantID` (`participantID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_courses` (
    `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campusID`         INT(11) UNSIGNED NOT NULL,
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
    PRIMARY KEY (`id`),
    INDEX `campusID` (`campusID`),
    INDEX `termID` (`termID`)
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
    `personID`     INT(11)          DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `categoryID` (`categoryID`),
    INDEX `departmentID` (`departmentID`),
    INDEX `personID` (`personID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_departments` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id`     INT(11)          NOT NULL,
    `shortName_de` VARCHAR(50)      NOT NULL,
    `shortName_en` VARCHAR(50)      NOT NULL,
    `name_de`      VARCHAR(150)     NOT NULL,
    `name_en`      VARCHAR(150)     NOT NULL,
    `contactType`  TINYINT(1) UNSIGNED DEFAULT 0,
    `contactID`    INT(11)             DEFAULT NULL,
    `contactEmail` VARCHAR(100)        DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `shortName_de` (`shortName_de`),
    UNIQUE INDEX `shortName_en` (`shortName_en`),
    UNIQUE INDEX `name_de` (`name_de`),
    UNIQUE INDEX `name_en` (`name_en`),
    INDEX `contactID` (`contactID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_event_coordinators` (
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_events` (
    `id`               INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `untisID`          VARCHAR(60)                  DEFAULT NULL,
    `departmentID`     INT(11) UNSIGNED    NOT NULL,
    `fieldID`          INT(11) UNSIGNED             DEFAULT NULL,
    `name_de`          VARCHAR(100)        NOT NULL,
    `name_en`          VARCHAR(100)        NOT NULL,
    `subjectNo`        VARCHAR(45)         NOT NULL DEFAULT '',
    `description_de`   TEXT,
    `description_en`   TEXT,
    `preparatory`      TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `campusID`         INT(11) UNSIGNED             DEFAULT NULL,
    `deadline`         INT(2) UNSIGNED              DEFAULT 0
        COMMENT 'The deadline in days for registration before the course starts.',
    `fee`              INT(3) UNSIGNED              DEFAULT 0,
    `maxParticipants`  INT(4) UNSIGNED              DEFAULT 1000,
    `registrationType` INT(1) UNSIGNED              DEFAULT NULL
        COMMENT 'The method of registration for the lesson. Possible values: NULL - None, 0 - FIFO, 1 - Manual.',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`untisID`, `departmentID`),
    INDEX `campusID` (`campusID`),
    INDEX `departmentID` (`departmentID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_fields` (
    `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `untisID` VARCHAR(60)      DEFAULT NULL,
    `colorID` INT(11) UNSIGNED DEFAULT NULL,
    `name_de` VARCHAR(60)      NOT NULL,
    `name_en` VARCHAR(60)      NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `colorID` (`colorID`),
    UNIQUE INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_frequencies` (
    `id`      INT(1) UNSIGNED NOT NULL,
    `name_de` VARCHAR(45)     NOT NULL,
    `name_en` VARCHAR(45)     NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `name_de` (`name_de`),
    UNIQUE `name_en` (`name_en`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_grids` (
    `id`          INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `name_de`     VARCHAR(255)                 DEFAULT NULL,
    `name_en`     VARCHAR(255)                 DEFAULT NULL,
    `grid`        TEXT                NOT NULL
        COMMENT 'A grid object modeled by a JSON string, containing the respective start and end times of the grid blocks.',
    `defaultGrid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `untisID`     VARCHAR(60)                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_group_publishing` (
    `id`        INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `groupID`   INT(11) UNSIGNED    NOT NULL,
    `termID`    INT(11) UNSIGNED    NOT NULL,
    `published` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`groupID`, `termID`),
    INDEX `groupID` (`groupID`),
    INDEX `termID` (`termID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_groups` (
    `id`         INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `untisID`    VARCHAR(60)                  DEFAULT NULL,
    `categoryID` INT(11) UNSIGNED             DEFAULT NULL,
    `fieldID`    INT(11) UNSIGNED             DEFAULT NULL,
    `gridID`     INT(11) UNSIGNED             DEFAULT 1,
    `name`       VARCHAR(100)        NOT NULL,
    `fullName`   VARCHAR(100)        NOT NULL
        COMMENT 'The fully qualified name of the pool including the degree program to which it is associated.',
    `active`     TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`untisID`, `categoryID`),
    INDEX `categoryID` (`categoryID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `gridID` (`gridID`),
    UNIQUE `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_holidays` (
    `id`        INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_instance_groups` (
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_instance_participants` (
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_instance_persons` (
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_instance_rooms` (
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_instances` (
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
    INDEX `poolID` (`poolID`),
    INDEX `programID` (`programID`),
    INDEX `subjectID` (`subjectID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_methods` (
    `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `untisID`         VARCHAR(60)  DEFAULT NULL,
    `abbreviation_de` VARCHAR(45)  DEFAULT '',
    `abbreviation_en` VARCHAR(45)  DEFAULT '',
    `name_de`         VARCHAR(255) DEFAULT NULL,
    `name_en`         VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_monitors` (
    `id`              INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `roomID`          INT(11) UNSIGNED             DEFAULT NULL,
    `ip`              VARCHAR(15)         NOT NULL,
    `useDefaults`     TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `display`         INT(1) UNSIGNED     NOT NULL DEFAULT 1
        COMMENT 'the display behaviour of the monitor',
    `scheduleRefresh` INT(3) UNSIGNED     NOT NULL DEFAULT 60
        COMMENT 'the amount of seconds before the schedule refreshes',
    `contentRefresh`  INT(3) UNSIGNED     NOT NULL DEFAULT 60
        COMMENT 'the amount of time in seconds before the content refreshes',
    `interval`        INT(1) UNSIGNED     NOT NULL DEFAULT 1
        COMMENT 'the time interval in minutes between context switches',
    `content`         VARCHAR(256)                 DEFAULT ''
        COMMENT 'the filename of the resource to the optional resource to be displayed',
    PRIMARY KEY (`id`),
    INDEX `roomID` (`roomID`),
    UNIQUE INDEX `ip` (`ip`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_participants` (
    `id`        INT(11)             NOT NULL,
    `forename`  VARCHAR(255)        NOT NULL DEFAULT '',
    `surname`   VARCHAR(255)        NOT NULL DEFAULT '',
    `city`      VARCHAR(60)         NOT NULL DEFAULT '',
    `address`   VARCHAR(60)         NOT NULL DEFAULT '',
    `zipCode`   INT(11)             NOT NULL DEFAULT 0,
    `programID` INT(11) UNSIGNED             DEFAULT NULL,
    `notify`    TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `programID` (`programID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_persons` (
    `id`       INT(11)             NOT NULL AUTO_INCREMENT,
    `untisID`  VARCHAR(60)                  DEFAULT NULL,
    `surname`  VARCHAR(255)        NOT NULL,
    `forename` VARCHAR(255)        NOT NULL DEFAULT '',
    `username` VARCHAR(150)                 DEFAULT NULL,
    `fieldID`  INT(11) UNSIGNED             DEFAULT NULL,
    `title`    VARCHAR(45)         NOT NULL DEFAULT '',
    `active`   TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`),
    INDEX `username` (`username`),
    INDEX `fieldID` (`fieldID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_pools` (
    `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_id`        INT(11)          NOT NULL DEFAULT 0,
    `departmentID`    INT(11) UNSIGNED          DEFAULT NULL,
    `fieldID`         INT(11) UNSIGNED          DEFAULT NULL,
    `groupID`         INT(11) UNSIGNED          DEFAULT NULL,
    `lsfID`           INT(11) UNSIGNED          DEFAULT NULL,
    `abbreviation_de` VARCHAR(45)               DEFAULT '',
    `abbreviation_en` VARCHAR(45)               DEFAULT '',
    `shortName_de`    VARCHAR(45)               DEFAULT '',
    `shortName_en`    VARCHAR(45)               DEFAULT '',
    `name_de`         VARCHAR(255)              DEFAULT NULL,
    `name_en`         VARCHAR(255)              DEFAULT NULL,
    `description_de`  TEXT,
    `description_en`  TEXT,
    `minCrP`          INT(3) UNSIGNED           DEFAULT 0,
    `maxCrP`          INT(3) UNSIGNED           DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `departmentID` (`departmentID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `groupID` (`groupID`),
    UNIQUE `lsfID` (`lsfID`)
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
    `id`             INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `asset_id`       INT(11)             NOT NULL DEFAULT 0,
    `categoryID`     INT(11) UNSIGNED             DEFAULT NULL,
    `degreeID`       INT(11) UNSIGNED             DEFAULT NULL,
    `departmentID`   INT(11) UNSIGNED             DEFAULT NULL,
    `fieldID`        INT(11) UNSIGNED             DEFAULT NULL,
    `frequencyID`    INT(1) UNSIGNED              DEFAULT NULL,
    `code`           VARCHAR(20)                  DEFAULT '',
    `version`        YEAR(4)                      DEFAULT NULL,
    `name_de`        VARCHAR(60)         NOT NULL,
    `name_en`        VARCHAR(60)         NOT NULL,
    `description_de` TEXT,
    `description_en` TEXT,
    `active`         TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`code`, `degreeID`, `version`),
    INDEX `categoryID` (`categoryID`),
    INDEX `degreeID` (`degreeID`),
    INDEX `departmentID` (`departmentID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `frequencyID` (`frequencyID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `#__thm_organizer_roles` (
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

CREATE TABLE IF NOT EXISTS `#__thm_organizer_rooms` (
    `id`         INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `buildingID` INT(11) UNSIGNED             DEFAULT NULL,
    `untisID`    VARCHAR(60)                  DEFAULT NULL,
    `name`       VARCHAR(10)         NOT NULL,
    `roomtypeID` INT(11) UNSIGNED             DEFAULT NULL,
    `capacity`   INT(4) UNSIGNED              DEFAULT NULL,
    `active`     TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`),
    INDEX `buildingID` (`buildingID`),
    INDEX `roomtypeID` (`roomtypeID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_roomtypes` (
    `id`             INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `untisID`        VARCHAR(60)                  DEFAULT NULL,
    `name_de`        VARCHAR(50)         NOT NULL,
    `name_en`        VARCHAR(50)         NOT NULL,
    `description_de` TEXT                NOT NULL,
    `description_en` TEXT                NOT NULL,
    `minCapacity`    INT(4) UNSIGNED              DEFAULT NULL,
    `maxCapacity`    INT(4) UNSIGNED              DEFAULT NULL,
    `public`         TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE `#__thm_organizer_runs` (
    `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name_de` VARCHAR(50)      NOT NULL,
    `name_en` VARCHAR(50)      NOT NULL,
    `termID`  INT(11) UNSIGNED NOT NULL,
    `run`     TEXT             NOT NULL
        COMMENT 'run contains the start date and end date of lessons which are saved in JSON string.',
    PRIMARY KEY (`id`),
    INDEX `termID` (`termID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_schedules` (
    `id`           INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `asset_id`     INT(11)             NOT NULL DEFAULT 0,
    `departmentID` INT(11) UNSIGNED    NOT NULL,
    `termID`       INT(11) UNSIGNED    NOT NULL,
    `userID`       INT(11)                      DEFAULT NULL,
    `creationDate` DATE                         DEFAULT NULL,
    `creationTime` TIME                         DEFAULT NULL,
    `schedule`     MEDIUMTEXT          NOT NULL,
    `active`       TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `departmentID` (`departmentID`),
    INDEX `termID` (`termID`),
    INDEX `userID` (`userID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_events` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `eventID`   INT(11) UNSIGNED NOT NULL,
    `subjectID` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`eventID`, `subjectID`),
    INDEX `eventID` (`eventID`),
    INDEX `subjectID` (`subjectID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subject_persons` (
    `id`        INT(11) UNSIGNED    NOT NULL AUTO_INCREMENT,
    `subjectID` INT(11) UNSIGNED    NOT NULL,
    `personID`  INT(11)             NOT NULL,
    `role`      TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
        COMMENT 'The person''s role for the given subject. Roles are not mutually exclusive. Possible values: 1 - coordinates, 2 - teaches.',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`personID`, `subjectID`, `role`),
    INDEX `subjectID` (`subjectID`),
    INDEX `personID` (`personID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_subjects` (
    `id`                          INT(11) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `asset_id`                    INT(11)               NOT NULL DEFAULT 0,
    `departmentID`                INT(11) UNSIGNED               DEFAULT NULL,
    `lsfID`                       INT(11) UNSIGNED               DEFAULT NULL,
    `code`                        VARCHAR(45)           NOT NULL DEFAULT '',
    `abbreviation_de`             VARCHAR(45)           NOT NULL DEFAULT '',
    `abbreviation_en`             VARCHAR(45)           NOT NULL DEFAULT '',
    `shortName_de`                VARCHAR(45)           NOT NULL DEFAULT '',
    `shortName_en`                VARCHAR(45)           NOT NULL DEFAULT '',
    `name_de`                     VARCHAR(255)          NOT NULL,
    `name_en`                     VARCHAR(255)          NOT NULL,
    `description_de`              TEXT                  NOT NULL,
    `description_en`              TEXT                  NOT NULL,
    `objective_de`                TEXT                  NOT NULL,
    `objective_en`                TEXT                  NOT NULL,
    `content_de`                  TEXT                  NOT NULL,
    `content_en`                  TEXT                  NOT NULL,
    `prerequisites_de`            TEXT                  NOT NULL,
    `prerequisites_en`            TEXT                  NOT NULL,
    `preliminaryWork_de`          TEXT,
    `preliminaryWork_en`          TEXT,
    `instructionLanguage`         VARCHAR(2)            NOT NULL DEFAULT 'D',
    `literature`                  TEXT                  NOT NULL,
    `creditpoints`                DOUBLE(4, 1) UNSIGNED NOT NULL DEFAULT 0,
    `expenditure`                 INT(4) UNSIGNED       NOT NULL DEFAULT 0,
    `present`                     INT(4) UNSIGNED       NOT NULL DEFAULT 0,
    `independent`                 INT(4) UNSIGNED       NOT NULL DEFAULT 0,
    `proof_de`                    TEXT                  NOT NULL,
    `proof_en`                    TEXT                  NOT NULL,
    `frequencyID`                 INT(1) UNSIGNED                DEFAULT NULL,
    `method_de`                   TEXT,
    `method_en`                   TEXT,
    `fieldID`                     INT(11) UNSIGNED               DEFAULT NULL,
    `sws`                         INT(2) UNSIGNED       NOT NULL DEFAULT 0,
    `aids_de`                     TEXT,
    `aids_en`                     TEXT,
    `evaluation_de`               TEXT,
    `evaluation_en`               TEXT,
    `expertise`                   TINYINT(1) UNSIGNED            DEFAULT NULL,
    `selfCompetence`              TINYINT(1) UNSIGNED            DEFAULT NULL,
    `methodCompetence`            TINYINT(1) UNSIGNED            DEFAULT NULL,
    `socialCompetence`            TINYINT(1) UNSIGNED            DEFAULT NULL,
    `recommendedPrerequisites_de` TEXT,
    `recommendedPrerequisites_en` TEXT,
    `usedFor_de`                  TEXT,
    `usedFor_en`                  TEXT,
    `duration`                    INT(2) UNSIGNED                DEFAULT 1,
    `bonusPoints_de`              TEXT,
    `bonusPoints_en`              TEXT,
    PRIMARY KEY (`id`),
    INDEX `departmentID` (`departmentID`),
    INDEX `fieldID` (`fieldID`),
    INDEX `frequencyID` (`frequencyID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_terms` (
    `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`        VARCHAR(10)      NOT NULL,
    `name_de`     VARCHAR(100) DEFAULT '',
    `name_en`     VARCHAR(100) DEFAULT '',
    `fullName_de` VARCHAR(255) DEFAULT '',
    `fullName_en` VARCHAR(255) DEFAULT '',
    `startDate`   DATE         DEFAULT NULL,
    `endDate`     DATE         DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`code`, `startDate`, `endDate`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_units` (
    `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `courseID`     INT(11) UNSIGNED          DEFAULT NULL,
    `departmentID` INT(11) UNSIGNED          DEFAULT NULL,
    `termID`       INT(11) UNSIGNED          DEFAULT NULL,
    `untisID`      INT(11) UNSIGNED NOT NULL,
    `gridID`       INT(11) UNSIGNED          DEFAULT NULL,
    `runID`        INT(11) UNSIGNED          DEFAULT NULL,
    `endDate`      DATE                      DEFAULT NULL,
    `startDate`    DATE                      DEFAULT NULL,
    `comment`      VARCHAR(200)              DEFAULT NULL,
    `delta`        VARCHAR(10)      NOT NULL DEFAULT '' COMMENT 'The unit''s delta status. Possible values: empty, new, removed.',
    `modified`     TIMESTAMP                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `entry` (`departmentID`, `termID`, `untisID`),
    INDEX `departmentID` (`departmentID`),
    INDEX `termID` (`termID`),
    INDEX `untisID` (`untisID`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_buildings`
    ADD CONSTRAINT `buildings_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_campuses`
    ADD CONSTRAINT `campus_parentID_fk` FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `campuses_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_course_participants`
    ADD CONSTRAINT `course_participants_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `#__thm_organizer_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `course_participants_participantID_fk` FOREIGN KEY (`participantID`) REFERENCES `#__thm_organizer_participants` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_courses`
    ADD CONSTRAINT `courses_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `courses_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
    ADD CONSTRAINT `department_resources_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `#__thm_organizer_categories` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `department_resources_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `department_resources_personID_fk` FOREIGN KEY (`personID`) REFERENCES `#__thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_departments`
    ADD CONSTRAINT `departments_contactID_fk` FOREIGN KEY (`contactID`) REFERENCES `#__users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_coordinators`
    ADD CONSTRAINT `event_coordinators_eventID_fk` FOREIGN KEY (`eventID`) REFERENCES `#__thm_organizer_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `event_coordinators_personID_fk` FOREIGN KEY (`personID`) REFERENCES `#__thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_events`
    ADD CONSTRAINT `events_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `events_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `events_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
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
    ADD CONSTRAINT `groups_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_instance_participants`
    ADD CONSTRAINT `instance_participants_instanceID_fk` FOREIGN KEY (`instanceID`) REFERENCES `#__thm_organizer_instances` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_participants_participantID_fk` FOREIGN KEY (`participantID`) REFERENCES `#__thm_organizer_participants` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_instance_groups`
    ADD CONSTRAINT `instance_groups_assocID_fk` FOREIGN KEY (`assocID`) REFERENCES `#__thm_organizer_instance_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_groups_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `#__thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_instance_persons`
    ADD CONSTRAINT `instance_persons_instanceID_fk` FOREIGN KEY (`instanceID`) REFERENCES `#__thm_organizer_instances` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_persons_personID_fk` FOREIGN KEY (`personID`) REFERENCES `#__thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_persons_roleID_fk` FOREIGN KEY (`roleID`) REFERENCES `#__thm_organizer_roles` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_instance_rooms`
    ADD CONSTRAINT `instance_rooms_assocID_fk` FOREIGN KEY (`assocID`) REFERENCES `#__thm_organizer_instance_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instance_rooms_roomID_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_instances`
    ADD CONSTRAINT `instances_blockID_fk` FOREIGN KEY (`blockID`) REFERENCES `#__thm_organizer_blocks` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instances_eventID_fk` FOREIGN KEY (`eventID`) REFERENCES `#__thm_organizer_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instances_methodID_fk` FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `instances_unitID_fk` FOREIGN KEY (`unitID`) REFERENCES `#__thm_organizer_units` (`id`)
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

ALTER TABLE `#__thm_organizer_persons`
    ADD CONSTRAINT `persons_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
    ADD CONSTRAINT `pools_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `pools_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `pools_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `#__thm_organizer_groups` (`id`)
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
    ADD CONSTRAINT `programs_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `#__thm_organizer_categories` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
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
    ADD CONSTRAINT `rooms_roomtypeID_fk` FOREIGN KEY (`roomtypeID`) REFERENCES `#__thm_organizer_roomtypes` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_runs`
    ADD CONSTRAINT `runs_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_schedules`
    ADD CONSTRAINT `schedules_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `schedules_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `schedules_userID_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_events`
    ADD CONSTRAINT `subject_events_eventID_fk` FOREIGN KEY (`eventID`) REFERENCES `#__thm_organizer_events` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_events_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_persons`
    ADD CONSTRAINT `subject_persons_personID_fk` FOREIGN KEY (`personID`) REFERENCES `#__thm_organizer_persons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_persons_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
    ADD CONSTRAINT `subjects_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subjects_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subjects_frequencyID_fk` FOREIGN KEY (`frequencyID`) REFERENCES `#__thm_organizer_frequencies` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_units`
    ADD CONSTRAINT `units_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `#__thm_organizer_courses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_runID_fk` FOREIGN KEY (`runID`) REFERENCES `#__thm_organizer_runs` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `units_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;