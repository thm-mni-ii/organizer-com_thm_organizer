ALTER TABLE `#__thm_organizer_subject_teachers`
DROP FOREIGN KEY `subject_teachers_responsibility_fk`;

ALTER TABLE `#__thm_organizer_event_groups`
ADD PRIMARY KEY (`eventID`, `groupID`);

ALTER TABLE `#__thm_organizer_event_rooms`
ADD PRIMARY KEY (`eventID`, `roomID`);

ALTER TABLE `#__thm_organizer_event_teachers`
ADD PRIMARY KEY (`eventID`, `teacherID`);

ALTER TABLE `#__thm_organizer_events`
CHANGE `recurrence_INTerval` `recurrence_interval`  INT ( 2 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_events`
CHANGE `starttime` `starttime`  time NOT NULL DEFAULT '00:00:00';

ALTER TABLE `#__thm_organizer_events`
CHANGE `endtime` `endtime`  time NOT NULL DEFAULT '00:00:00';

ALTER TABLE `#__thm_organizer_events`
CHANGE `startdate` `startdate` date NOT NULL;

ALTER TABLE `#__thm_organizer_monitors`
ADD UNIQUE KEY  `ip` (`ip`);

ALTER TABLE `#__thm_organizer_monitors`
CHANGE `content` `content` VARCHAR ( 256 ) NOT NULL DEFAULT '' COMMENT 'the filename of the resource to the optional resource to be displayed';

ALTER TABLE `#__thm_organizer_rooms`
CHANGE `name` `name` VARCHAR ( 10 ) NOT NULL;

ALTER TABLE `#__thm_organizer_rooms`
CHANGE `gpuntisID` `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_room_types`
DROP INDEX `gpuntisID`;

ALTER TABLE `#__thm_organizer_subject_teachers`
CHANGE `teacherResp` `teacherResp` INT(11) UNSIGNED NOT NULL DEFAULT 1;

DROP TABLE `#__thm_organizer_teacher_responsibilities`;

ALTER TABLE `#__thm_organizer_teachers`
CHANGE `title` `title` varchar ( 45 ) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_teachers`
CHANGE `forename` `forename` varchar ( 255 ) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_teachers`
CHANGE `gpuntisID` `gpuntisID` VARCHAR ( 50 ) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `evaluation_de` `evaluation_de` TEXT NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `evaluation_en` `evaluation_en` TEXT NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `aids_de` `aids_de` TEXT NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `aids_en` `aids_en` TEXT NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `method_de` `method_de` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `method_en` `method_en` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `proof_de` `proof_de` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `proof_en` `proof_en` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `literature` `literature` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `instructionLanguage` `instructionLanguage` varchar(2)  NOT NULL DEFAULT 'D';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `preliminary_work_de` `preliminary_work_de` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `preliminary_work_en` `preliminary_work_en` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `prerequisites_de` `prerequisites_de` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `prerequisites_en` `prerequisites_en` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `content_de` `content_de` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `content_en` `content_en` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `objective_de` `objective_de` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `objective_en` `objective_en` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `description_de` `description_de` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `description_en` `description_en` text NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `name_de` `name_de` varchar(255) NOT NULL;

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `name_en` `name_en` varchar(255) NOT NULL;

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `short_name_de` `short_name_de` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `short_name_en` `short_name_en` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `abbreviation_de` `abbreviation_de` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `abbreviation_en` `abbreviation_en` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `externalID` `externalID` varchar(45) NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `sws` `sws` INT( 2 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `independent` `independent` INT( 2 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `present` `present` INT( 2 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `expenditure` `expenditure` INT( 2 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_subjects`
CHANGE `creditpoints` `creditpoints` INT( 2 ) UNSIGNED NOT NULL DEFAULT '0';

ALTER TABLE `#__thm_organizer_frequencies`
CHANGE `frequency_de` `frequency_de` varchar (45) NOT NULL;

ALTER TABLE `#__thm_organizer_frequencies`
CHANGE `frequency_en` `frequency_en` varchar (45) NOT NULL;

ALTER TABLE `#__thm_organizer_pools`
CHANGE `distance` `distance` INT(2) UNSIGNED DEFAULT 10;

ALTER TABLE `#__thm_organizer_pools`
CHANGE `maxCrP` `maxCrP` INT(3) UNSIGNED DEFAULT 0;

ALTER TABLE `#__thm_organizer_pools`
CHANGE `minCrP` `minCrP` INT(3) UNSIGNED DEFAULT 0;

ALTER TABLE `#__thm_organizer_pools`
CHANGE `description_de` `description_de` text DEFAULT NULL;

ALTER TABLE `#__thm_organizer_pools`
CHANGE `description_en` `description_en` text DEFAULT NULL;

ALTER TABLE `#__thm_organizer_pools`
CHANGE `abbreviation_de` `abbreviation_de` varchar(45) DEFAULT '';

ALTER TABLE `#__thm_organizer_pools`
CHANGE `abbreviation_en` `abbreviation_en` varchar(45) DEFAULT '';

ALTER TABLE `#__thm_organizer_pools`
CHANGE `short_name_de` `short_name_de` varchar(45) DEFAULT '';

ALTER TABLE `#__thm_organizer_pools`
CHANGE `short_name_en` `short_name_en` varchar(45) DEFAULT '';

ALTER TABLE `#__thm_organizer_pools`
CHANGE `externalID` `externalID` varchar(45) DEFAULT '';

ALTER TABLE `#__thm_organizer_programs`
ADD UNIQUE KEY `lsfData` (`version`, `lsfFieldID`, `degreeID`);

ALTER TABLE `#__thm_organizer_programs`
CHANGE `lsfFieldID` `lsfFieldID` varchar(20) DEFAULT '';

ALTER TABLE `#__thm_organizer_degrees`
CHANGE `lsfDegree` `lsfDegree` varchar ( 10 ) DEFAULT '';

ALTER TABLE `#__thm_organizer_fields`
DROP INDEX `gpuntisID`;

ALTER TABLE `#__thm_organizer_fields`
ADD KEY `gpuntisID` (`gpuntisID`);

ALTER TABLE `#__thm_organizer_schedules`
ADD `term_startdate` date DEFAULT NULL;

ALTER TABLE `#__thm_organizer_schedules`
ADD `term_enddate` date DEFAULT NULL;

ALTER TABLE `#__thm_organizer_schedules`
CHANGE `description` `description` TEXT NOT NULL DEFAULT '';

ALTER TABLE `#__thm_organizer_teachers`
CHANGE `surname` `surname` VARCHAR ( 255 ) NOT NULL;



