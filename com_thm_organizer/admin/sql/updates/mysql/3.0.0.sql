ALTER TABLE `#__thm_organizer_virtual_schedules`
DROP FOREIGN KEY `#__thm_organizer_virtual_schedules_ibfk_1`;

ALTER TABLE `#__thm_organizer_virtual_schedules_elements`
DROP FOREIGN KEY `#__thm_organizer_virtual_schedules_elements_ibfk_1`;

ALTER TABLE `#__thm_organizer_fields`
DROP FOREIGN KEY `#__thm_organizer_fields_ibfk_1`;

ALTER TABLE `#__thm_organizer_programs`
DROP FOREIGN KEY `#__thm_organizer_programs_ibfk_1`;

ALTER TABLE `#__thm_organizer_programs`
DROP FOREIGN KEY `#__thm_organizer_programs_ibfk_2`;

ALTER TABLE `#__thm_organizer_pools`
DROP FOREIGN KEY `#__thm_organizer_pools_ibfk_1`;

ALTER TABLE `#__thm_organizer_subjects`
DROP FOREIGN KEY `frequencyID_fk`;

ALTER TABLE `#__thm_organizer_subjects`
DROP FOREIGN KEY `fieldID_fk`;

ALTER TABLE `#__thm_organizer_prerequisites`
DROP FOREIGN KEY `#__thm_organizer_prerequisites_ibfk_1`;

ALTER TABLE `#__thm_organizer_prerequisites`
DROP FOREIGN KEY `#__thm_organizer_prerequisites_ibfk_2`;

ALTER TABLE `#__thm_organizer_mappings`
DROP FOREIGN KEY `#__thm_organizer_mappings_ibfk_1`;

ALTER TABLE `#__thm_organizer_mappings`
DROP FOREIGN KEY `#__thm_organizer_mappings_ibfk_2`;

ALTER TABLE `#__thm_organizer_mappings`
DROP FOREIGN KEY `#__thm_organizer_mappings_ibfk_3`;

ALTER TABLE `#__thm_organizer_mappings`
DROP FOREIGN KEY `#__thm_organizer_mappings_ibfk_4`;

ALTER TABLE `#__thm_organizer_teachers`
DROP FOREIGN KEY `#__thm_organizer_teachers_ibfk_1`;

ALTER TABLE `#__thm_organizer_subject_teachers`
DROP FOREIGN KEY `#__thm_organizer_subject_teachers_ibfk_1`;

ALTER TABLE `#__thm_organizer_subject_teachers`
DROP FOREIGN KEY `#__thm_organizer_subject_teachers_ibfk_2`;

ALTER TABLE `#__thm_organizer_subject_teachers`
DROP FOREIGN KEY `#__thm_organizer_subject_teachers_ibfk_3`;

ALTER TABLE `#__thm_organizer_rooms`
DROP FOREIGN KEY `#__thm_organizer_rooms_ibfk_1`;

ALTER TABLE `#__thm_organizer_monitors`
DROP FOREIGN KEY `#__thm_organizer_monitors_ibfk_1`;

ALTER TABLE `#__thm_organizer_events`
DROP FOREIGN KEY `#__thm_organizer_events_ibfk_1`;

ALTER TABLE `#__thm_organizer_event_exclude_dates`
DROP FOREIGN KEY `#__thm_organizer_event_exclude_dates_ibfk_1`;

ALTER TABLE `#__thm_organizer_event_teachers`
DROP FOREIGN KEY `#__thm_organizer_event_teachers_ibfk_1`;

ALTER TABLE `#__thm_organizer_event_teachers`
DROP FOREIGN KEY `#__thm_organizer_event_teachers_ibfk_2`;

ALTER TABLE `#__thm_organizer_event_rooms`
DROP FOREIGN KEY `#__thm_organizer_event_rooms_ibfk_1`;

ALTER TABLE `#__thm_organizer_event_rooms`
DROP FOREIGN KEY `#__thm_organizer_event_rooms_ibfk_2`;

ALTER TABLE `#__thm_organizer_event_groups`
DROP FOREIGN KEY `#__thm_organizer_event_groups_ibfk_1`;

ALTER TABLE `#__thm_organizer_users` DROP PRIMARY KEY;

ALTER TABLE `#__thm_organizer_virtual_schedules` DROP INDEX `semestername`;

ALTER TABLE `#__thm_organizer_virtual_schedules_elements` DROP INDEX `vid`;

ALTER TABLE `#__thm_organizer_fields`  DROP INDEX `colorID`;

ALTER TABLE `#__thm_organizer_programs`  DROP INDEX `degreeID`;

ALTER TABLE `#__thm_organizer_programs`  DROP INDEX `fieldID`;

ALTER TABLE `#__thm_organizer_pools`  DROP INDEX `fieldID`;

ALTER TABLE `#__thm_organizer_subjects`  DROP INDEX `frequencyID_fk`;

ALTER TABLE `#__thm_organizer_subjects`  DROP INDEX `fieldID_fk`;

ALTER TABLE `#__thm_organizer_prerequisites`  DROP INDEX `prerequisite`;

ALTER TABLE `#__thm_organizer_mappings` DROP INDEX `parentID`;

ALTER TABLE `#__thm_organizer_mappings` DROP INDEX `programID`;

ALTER TABLE `#__thm_organizer_mappings` DROP INDEX `poolID`;

ALTER TABLE `#__thm_organizer_mappings` DROP INDEX `subjectID`;

ALTER TABLE `#__thm_organizer_teachers` DROP INDEX `fieldID`;

ALTER TABLE `#__thm_organizer_subject_teachers` DROP INDEX `teacherID`;

ALTER TABLE `#__thm_organizer_subject_teachers` DROP INDEX `teacherResp`;

ALTER TABLE `#__thm_organizer_rooms` DROP INDEX `typeID`;

ALTER TABLE `#__thm_organizer_monitors` DROP INDEX `roomID`;

ALTER TABLE `#__thm_organizer_categories` DROP INDEX `contentCatID`;

ALTER TABLE `#__thm_organizer_events` DROP PRIMARY KEY;

ALTER TABLE `#__thm_organizer_events` DROP INDEX `categoryID`;

ALTER TABLE `#__thm_organizer_event_exclude_dates` DROP INDEX `eventID`;

ALTER TABLE `#__thm_organizer_event_teachers` DROP INDEX `eventID`;

ALTER TABLE `#__thm_organizer_event_teachers` DROP INDEX `teacherID`;

ALTER TABLE `#__thm_organizer_event_rooms` DROP INDEX `eventID`;

ALTER TABLE `#__thm_organizer_event_rooms` DROP INDEX `roomID`;

ALTER TABLE `#__thm_organizer_event_groups` DROP INDEX `eventID`;

ALTER TABLE `#__thm_organizer_event_groups` DROP INDEX `groupID`;

ALTER TABLE `#__thm_organizer_users` CHANGE `id` `userID` INT(11) NOT NULL;

ALTER TABLE `#__thm_organizer_monitors` CHANGE `roomID` `roomID` INT(11) UNSIGNED NULL;

ALTER TABLE `#__thm_organizer_users`
ADD CONSTRAINT `users_userid_fk` FOREIGN KEY (`userID`)
REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_virtual_schedules`
ADD CONSTRAINT `virtual_schedules_semestername_fk` FOREIGN KEY (`semestername`)
REFERENCES `#__thm_organizer_schedules` (`semestername`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_virtual_schedules_elements`
ADD CONSTRAINT `virtual_schedules_elements_vid_fk` FOREIGN KEY (`vid`)
REFERENCES `#__thm_organizer_virtual_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_fields`
ADD CONSTRAINT `fields_colorid_fk` FOREIGN KEY (`colorID`)
REFERENCES `#__thm_organizer_colors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
ADD CONSTRAINT `programs_degreeid_fk` FOREIGN KEY (`degreeID`)
REFERENCES `#__thm_organizer_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_programs`
ADD CONSTRAINT `programs_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_pools`
ADD CONSTRAINT `pools_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `subjects_frequencyid_fk` FOREIGN KEY (`frequencyID`)
REFERENCES `#__thm_organizer_frequencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subjects`
ADD CONSTRAINT `subjects_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
ADD CONSTRAINT `prerequisites_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_prerequisites`
ADD CONSTRAINT `prerequisites_prerequisites_fk` FOREIGN KEY (`prerequisite`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_parentid_fk` FOREIGN KEY (`parentID`)
REFERENCES `#__thm_organizer_mappings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_programid_fk` FOREIGN KEY (`programID`)
REFERENCES `#__thm_organizer_programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_poolid_fk` FOREIGN KEY (`poolID`)
REFERENCES `#__thm_organizer_pools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_mappings`
ADD CONSTRAINT `mappings_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_teachers`
ADD CONSTRAINT `teachers_fieldid_fk` FOREIGN KEY (`fieldID`)
REFERENCES `#__thm_organizer_fields` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
ADD CONSTRAINT `subject_teachers_subjectid_fk` FOREIGN KEY (`subjectID`)
REFERENCES `#__thm_organizer_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
ADD CONSTRAINT `subject_teachers_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_subject_teachers`
ADD CONSTRAINT `subject_teachers_responsibility_fk` FOREIGN KEY (`teacherResp`)
REFERENCES `#__thm_organizer_teacher_responsibilities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_rooms`
ADD CONSTRAINT `rooms_typeid_fk` FOREIGN KEY (`typeID`)
REFERENCES `#__thm_organizer_room_types` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_monitors`
ADD CONSTRAINT `monitors_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_categories`
ADD CONSTRAINT `categories_categoryid_fk` FOREIGN KEY (`contentCatID`)
REFERENCES `#__categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_events`
ADD CONSTRAINT `events_contentid_fk` FOREIGN KEY (`id`)
REFERENCES `#__content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_events`
ADD CONSTRAINT `events_categoryid_fk` FOREIGN KEY (`categoryID`)
REFERENCES `#__thm_organizer_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_exclude_dates`
ADD CONSTRAINT `event_exclude_dates_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_teachers`
ADD CONSTRAINT `event_teachers_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_teachers`
ADD CONSTRAINT `event_teachers_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_rooms`
ADD CONSTRAINT `event_rooms_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_rooms`
ADD CONSTRAINT `event_rooms_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_groups`
ADD CONSTRAINT `event_groups_eventid_fk` FOREIGN KEY (`eventID`)
REFERENCES `#__thm_organizer_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_event_groups`
ADD CONSTRAINT `event_groups_groupid_fk` FOREIGN KEY (`groupID`)
REFERENCES `#__usergroups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
