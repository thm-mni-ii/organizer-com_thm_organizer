ALTER TABLE `#__thm_organizer_buildings` DROP FOREIGN KEY `building_campusID_fk`;

ALTER TABLE `#__thm_organizer_buildings`
    ADD CONSTRAINT `buildings_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_calendar`
    DROP FOREIGN KEY `calendar_lessonid_fk`,
    DROP INDEX `configurationID`;

ALTER TABLE `#__thm_organizer_calendar`
    ADD CONSTRAINT `calendar_lessonID_fk` FOREIGN KEY (`lessonID`)
        REFERENCES `#__thm_organizer_lessons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_calendar_configuration_map`
    DROP FOREIGN KEY `calendar_configuration_map_calendarID_fk`,
    DROP FOREIGN KEY `calendar_configuration_map_configurationID_fk`,
    DROP INDEX `calendar_configuration_map_configurationID_fk`;

ALTER TABLE `#__thm_organizer_calendar_configuration_map`
    ADD INDEX `calendarID` (`calendarID`),
    ADD INDEX `configurationID` (`configurationID`);

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
    DROP FOREIGN KEY `campus_gridID_fk`,
    DROP INDEX `campus_gridID_fk`;

ALTER TABLE `#__thm_organizer_campuses` ADD INDEX `gridID` (`gridID`);

ALTER TABLE `#__thm_organizer_campuses`
    ADD CONSTRAINT `campus_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
    DROP FOREIGN KEY `department_resources_departmentid_fk`,
    DROP FOREIGN KEY `department_resources_programid_fk`,
    DROP FOREIGN KEY `department_resources_teacherid_fk`,
    DROP INDEX `programID`;

ALTER TABLE `#__thm_organizer_department_resources`
    CHANGE `programID` `categoryID`    INT(11) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_department_resources` ADD INDEX `categoryID` (`categoryID`);

ALTER TABLE `#__thm_organizer_department_resources`
    ADD CONSTRAINT `department_resources_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `department_resources_teacherID_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_departments` ADD PRIMARY KEY (`id`);

ALTER TABLE `#__thm_organizer_fields`
    DROP FOREIGN KEY `fields_colorid_fk`,
    DROP INDEX `gpuntisID`;

ALTER TABLE `#__thm_organizer_fields`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_fields` ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `#__thm_organizer_fields`
    ADD CONSTRAINT `fields_colorID_fk` FOREIGN KEY (`colorID`) REFERENCES `#__thm_organizer_colors` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_grids` DROP INDEX `gpuntisID`;

ALTER TABLE `#__thm_organizer_grids`
    MODIFY `defaultGrid` INT(1) NOT NULL DEFAULT '0',
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_grids` ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `#__thm_organizer_lesson_configurations`
    DROP FOREIGN KEY `lesson_configurations_lessonid_fk`,
    DROP INDEX `lessonID`;

ALTER TABLE `#__thm_organizer_lesson_configurations`
    CHANGE `lessonID` `lessonCourseID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_lesson_configurations` ADD INDEX `lessonCourseID` (`lessonCourseID`);

ALTER TABLE `#__thm_organizer_lesson_pools`
    DROP FOREIGN KEY `lesson_pools_poolid_fk`,
    DROP FOREIGN KEY `lesson_pools_subjectid_fk`,
    DROP INDEX `poolID`,
    DROP INDEX `subjectID`;

RENAME TABLE `#__thm_organizer_lesson_pools` TO `#__thm_organizer_lesson_groups`;

ALTER TABLE `#__thm_organizer_lesson_groups`
    CHANGE `poolID` `groupID` INT(11) UNSIGNED NOT NULL,
    CHANGE `subjectID` `lessonCourseID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_lesson_groups`
    ADD INDEX `groupID` (`groupID`),
    ADD INDEX `lessonCourseID` (`lessonCourseID`);

ALTER TABLE `#__thm_organizer_lesson_subjects`
    DROP FOREIGN KEY `lesson_subjects_lessonid_fk`,
    DROP FOREIGN KEY `lesson_subjects_subjectid_fk`,
    DROP INDEX `subjectID`;

RENAME TABLE `#__thm_organizer_lesson_subjects` TO `#__thm_organizer_lesson_courses`;

ALTER TABLE `#__thm_organizer_lesson_courses`
    CHANGE `subjectID` `courseID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_lesson_courses` ADD INDEX `courseID` (`courseID`);

ALTER TABLE `#__thm_organizer_lesson_courses`
    ADD CONSTRAINT `lesson_courses_lessonID_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_configurations`
    ADD CONSTRAINT `lesson_configurations_lessonCourseID_fk` FOREIGN KEY (`lessonCourseID`)
        REFERENCES `#__thm_organizer_lesson_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_groups`
    ADD CONSTRAINT `lesson_groups_lessonCourseID_fk` FOREIGN KEY (`lessonCourseID`) REFERENCES `#__thm_organizer_lesson_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_teachers`
    DROP FOREIGN KEY `lesson_teachers_subjectid_fk`,
    DROP FOREIGN KEY `lesson_teachers_teacherid_fk`,
    DROP INDEX `subjectID`;

ALTER TABLE `#__thm_organizer_lesson_teachers`
    CHANGE `subjectID` `lessonCourseID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_lesson_teachers` ADD INDEX `lessonCourseID` (`lessonCourseID`);

ALTER TABLE `#__thm_organizer_lesson_teachers`
    ADD CONSTRAINT `lesson_teachers_lessonCourseID_fk` FOREIGN KEY (`lessonCourseID`) REFERENCES `#__thm_organizer_lesson_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lesson_teachers_teacherID_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lessons`
    DROP FOREIGN KEY `lessons_campusID_fk`,
    DROP FOREIGN KEY `lessons_departmentid_fk`,
    DROP FOREIGN KEY `lessons_methodid_fk`,
    DROP FOREIGN KEY `lessons_planningperiodid_fk`,
    DROP INDEX `planID`,
    DROP INDEX `lessons_campusID_fk`,
    DROP INDEX `lessons_departmentid_fk`,
    DROP INDEX `lessons_planningperiodid_fk`;

ALTER TABLE `#__thm_organizer_lessons`
    CHANGE `gpuntisID` `untisID` INT(11) UNSIGNED NOT NULL,
    CHANGE `planningPeriodID` `termID` INT(11) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_lessons`
    ADD CONSTRAINT `planID` UNIQUE (`untisID`, `departmentID`, `termID`),
    ADD INDEX `campusID` (`campusID`),
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `termID` (`termID`);

ALTER TABLE `#__thm_organizer_lessons`
    ADD CONSTRAINT `lessons_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lessons_methodID_fk` FOREIGN KEY (`methodID`) REFERENCES `#__thm_organizer_methods` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `lessons_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
    DROP FOREIGN KEY `mappings_parentid_fk`,
    DROP FOREIGN KEY `mappings_poolid_fk`,
    DROP FOREIGN KEY `mappings_programid_fk`,
    DROP FOREIGN KEY `mappings_subjectid_fk`;

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

ALTER TABLE `#__thm_organizer_methods`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_methods` ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `#__thm_organizer_monitors` DROP FOREIGN KEY `monitors_roomid_fk`;

ALTER TABLE `#__thm_organizer_monitors`
    ADD CONSTRAINT `monitors_roomID_fk` FOREIGN KEY (`roomID`) REFERENCES `#__thm_organizer_rooms` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_participants`
    DROP FOREIGN KEY `participants_programid_fk`,
    DROP FOREIGN KEY `participants_userid_fk`,
    DROP INDEX `participants_programid_fk`;

ALTER TABLE `#__thm_organizer_participants`
    ADD INDEX `programID` (`programID`);

ALTER TABLE `#__thm_organizer_participants`
    ADD CONSTRAINT `participants_programID_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `participants_userID_fk` FOREIGN KEY (`id`) REFERENCES `#__users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

RENAME TABLE `#__thm_organizer_planning_periods` TO `#__thm_organizer_terms`;

ALTER TABLE `#__thm_organizer_terms` DROP INDEX `pp_long`;

ALTER TABLE `#__thm_organizer_terms` ADD UNIQUE INDEX `term` (`name`, `startDate`, `endDate`);

ALTER TABLE `#__thm_organizer_lessons`
    ADD CONSTRAINT `lessons_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pool_publishing`
    DROP FOREIGN KEY `plan_pool_publishing_planpoolid_fk`,
    DROP FOREIGN KEY `plan_pool_publishing_planningperiodid_fk`,
    DROP INDEX `entry`,
    DROP INDEX `plan_pool_publishing_planningperiodid_fk`;

RENAME TABLE `#__thm_organizer_plan_pool_publishing` TO `#__thm_organizer_group_publishing`;

ALTER TABLE `#__thm_organizer_group_publishing`
    CHANGE `planPoolID` `groupID` INT(11) UNSIGNED NOT NULL,
    CHANGE `planningPeriodID` `termID` INT(11) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_group_publishing`
    ADD CONSTRAINT `entry` UNIQUE (`groupID`, `termID`),
    ADD INDEX `groupID` (`groupID`),
    ADD INDEX `termID` (`termID`);

ALTER TABLE `#__thm_organizer_group_publishing`
    ADD CONSTRAINT `group_publishing_termID_fk` FOREIGN KEY (`termID`) REFERENCES `#__thm_organizer_terms` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_pools`
    DROP FOREIGN KEY `plan_pools_fieldid_fk`,
    DROP FOREIGN KEY `plan_pools_gridid_fk`,
    DROP FOREIGN KEY `plan_pools_poolid_fk`,
    DROP FOREIGN KEY `plan_pools_programid_fk`,
    DROP INDEX `dbID`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `poolID`,
    DROP INDEX `programID`,
    DROP INDEX `plan_pools_gridid_fk`;

RENAME TABLE `#__thm_organizer_plan_pools` TO `#__thm_organizer_groups`;

ALTER TABLE `#__thm_organizer_groups`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
    DROP COLUMN `poolID`,
    CHANGE `programID` `categoryID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_groups`
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`),
    ADD INDEX `categoryID` (`categoryID`),
    ADD INDEX `gridID` (`gridID`);

ALTER TABLE `#__thm_organizer_groups`
    ADD CONSTRAINT `groups_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `groups_gridID_fk` FOREIGN KEY (`gridID`) REFERENCES `#__thm_organizer_grids` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_group_publishing`
    ADD CONSTRAINT `group_publishing_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `#__thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_groups`
    ADD CONSTRAINT `lesson_groups_groupID_fk` FOREIGN KEY (`groupID`) REFERENCES `#__thm_organizer_groups` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_programs`
    DROP FOREIGN KEY `plan_programs_programid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_programs_programid_fk`;

RENAME TABLE `#__thm_organizer_plan_programs` TO `#__thm_organizer_categories`;

ALTER TABLE `#__thm_organizer_categories`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_categories`
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`),
    ADD INDEX `programID` (`programID`);

ALTER TABLE `#__thm_organizer_categories`
    ADD CONSTRAINT `categories_programID_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
    ADD CONSTRAINT `department_resources_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `#__thm_organizer_categories` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_groups`
    ADD CONSTRAINT `groups_categoryID_fk` FOREIGN KEY (`categoryID`) REFERENCES `#__thm_organizer_categories` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_plan_subjects`
    DROP FOREIGN KEY `plan_subjects_fieldid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `plan_subjects_fieldid_fk`;

RENAME TABLE `#__thm_organizer_plan_subjects` TO `#__thm_organizer_courses`;

ALTER TABLE `#__thm_organizer_courses`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_courses`
    ADD CONSTRAINT `courses_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_lesson_courses`
    ADD CONSTRAINT `lesson_courses_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `#__thm_organizer_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
    DROP FOREIGN KEY `pools_departmentid_fk`,
    DROP FOREIGN KEY `pools_fieldid_fk`,
    DROP INDEX `pools_departmentid_fk`;

ALTER TABLE `#__thm_organizer_pools` ADD INDEX `departmentID` (`departmentID`);

ALTER TABLE `#__thm_organizer_pools`
    ADD CONSTRAINT `pools_departmentID_fk` FOREIGN KEY (`departmentID`) REFERENCES `#__thm_organizer_departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `pools_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
    DROP FOREIGN KEY `prerequisites_prerequisites_fk`,
    DROP FOREIGN KEY `prerequisites_subjectid_fk`,
    DROP INDEX `entry`,
    DROP INDEX `prerequisites_prerequisites_fk`;

ALTER TABLE `#__thm_organizer_prerequisites`
    CHANGE `prerequisite` `prerequisiteID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_prerequisites`
    ADD CONSTRAINT `entry` UNIQUE (`prerequisiteID`, `subjectID`),
    ADD INDEX `prerequisiteID` (`prerequisiteID`),
    ADD INDEX `subjectID` (`subjectID`);

ALTER TABLE `#__thm_organizer_prerequisites`
    ADD CONSTRAINT `prerequisites_prerequisiteID_fk` FOREIGN KEY (`prerequisiteID`) REFERENCES `#__thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `prerequisites_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_mappings` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
    DROP FOREIGN KEY `programs_degreeid_fk`,
    DROP FOREIGN KEY `programs_departmentid_fk`,
    DROP FOREIGN KEY `programs_fieldid_fk`,
    DROP FOREIGN KEY `programs_frequencyid_fk`,
    DROP INDEX `programs_departmentid_fk`,
    DROP INDEX `programs_frequencyid_fk`;

ALTER TABLE `#__thm_organizer_programs`
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `frequencyID` (`frequencyID`);

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

ALTER TABLE `#__thm_organizer_room_types` DROP INDEX `gpuntisID`;

ALTER TABLE `#__thm_organizer_room_types`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_room_types` ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `#__thm_organizer_rooms`
    DROP FOREIGN KEY `room_buildingID_fk`,
    DROP FOREIGN KEY `rooms_typeid_fk`,
    DROP INDEX `gpuntisID`,
    DROP INDEX `room_buildingID_fk`;

ALTER TABLE `#__thm_organizer_rooms`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_rooms`
    ADD CONSTRAINT `untisID` UNIQUE (`untisID`),
    ADD INDEX `buildingID` (`buildingID`);

ALTER TABLE `#__thm_organizer_rooms`
    ADD CONSTRAINT `rooms_buildingID_fk` FOREIGN KEY (`buildingID`) REFERENCES `#__thm_organizer_buildings` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    ADD CONSTRAINT `rooms_typeID_fk` FOREIGN KEY (`typeID`) REFERENCES `#__thm_organizer_room_types` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_schedules`
    DROP FOREIGN KEY `schedules_departmentid_fk`,
    DROP FOREIGN KEY `schedules_planningperiodid_fk`,
    DROP FOREIGN KEY `schedules_userid_fk`,
    DROP INDEX `schedules_departmentid_fk`,
    DROP INDEX `schedules_planningperiodid_fk`;

ALTER TABLE `#__thm_organizer_schedules`
    CHANGE `planningPeriodID` `termID` INT(11) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_schedules`
    ADD INDEX `departmentID` (`departmentID`),
    ADD INDEX `termID` (`termID`);

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
    DROP FOREIGN KEY `subject_mappings_subjectID_fk`,
    DROP FOREIGN KEY `subject_mappings_plan_subjectID_fk`,
    DROP INDEX `entry`,
    DROP INDEX `subject_mappings_plan_subjectID_fk`;

ALTER TABLE `#__thm_organizer_subject_mappings`
    CHANGE `plan_subjectID` `courseID` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `#__thm_organizer_subject_mappings`
    ADD CONSTRAINT `entry` UNIQUE (`subjectID`, `courseID`),
    ADD INDEX `subjectID` (`subjectID`),
    ADD INDEX `courseID` (`courseID`);

ALTER TABLE `#__thm_organizer_subject_mappings`
    ADD CONSTRAINT `subject_mappings_courseID_fk` FOREIGN KEY (`courseID`) REFERENCES `#__thm_organizer_courses` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_mappings_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
    DROP FOREIGN KEY `subject_teachers_subjectid_fk`,
    DROP FOREIGN KEY `subject_teachers_teacherid_fk`;

ALTER TABLE `#__thm_organizer_subject_teachers`
    ADD CONSTRAINT `subject_teachers_subjectID_fk` FOREIGN KEY (`subjectID`) REFERENCES `#__thm_organizer_subjects` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `subject_teachers_teacherID_fk` FOREIGN KEY (`teacherID`) REFERENCES `#__thm_organizer_teachers` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
    DROP FOREIGN KEY `subject_campusID_fk`,
    DROP FOREIGN KEY `subjects_departmentid_fk`,
    DROP FOREIGN KEY `subjects_fieldid_fk`,
    DROP FOREIGN KEY `subjects_frequencyid_fk`,
    DROP INDEX `subject_campusID_fk`,
    DROP INDEX `subjects_departmentid_fk`;

ALTER TABLE `#__thm_organizer_subjects`
    ADD INDEX `campusID` (`campusID`),
    ADD INDEX `departmentID` (`departmentID`);

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
    DROP FOREIGN KEY `teachers_fieldid_fk`,
    DROP INDEX `gpuntisID`;

ALTER TABLE `#__thm_organizer_teachers`
    CHANGE `gpuntisID` `untisID` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;

ALTER TABLE `#__thm_organizer_teachers` ADD CONSTRAINT `untisID` UNIQUE (`untisID`);

ALTER TABLE `#__thm_organizer_teachers`
    ADD CONSTRAINT `teachers_fieldID_fk` FOREIGN KEY (`fieldID`) REFERENCES `#__thm_organizer_fields` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_user_lessons`
    DROP FOREIGN KEY `user_lessons_lessonid_fk`,
    DROP FOREIGN KEY `user_lessons_userid_fk`;

ALTER TABLE `#__thm_organizer_user_lessons`
    ADD CONSTRAINT `user_lessons_lessonID_fk` FOREIGN KEY (`lessonID`) REFERENCES `#__thm_organizer_lessons` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    ADD CONSTRAINT `user_lessons_userID_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE;

UPDATE `#__thm_organizer_schedules`
SET `schedule` = replace(`schedule`, 'planningPeriodID', 'termID');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = replace(`schedule`, 'subjectID', 'courseID');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = replace(`schedule`, 'subjects', 'courses');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = replace(`schedule`, 'pools', 'groups');