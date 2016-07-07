ALTER TABLE `#__thm_organizer_fields`
  DROP INDEX `gpuntisID`,
  ADD UNIQUE `gpuntisID` (`gpuntisID`);

ALTER TABLE `#__thm_organizer_plan_rooms`
  DROP FOREIGN KEY `plan_rooms_roomid_fk`,
  DROP FOREIGN KEY `plan_rooms_typeid_fk`;

ALTER TABLE `#__thm_organizer_plan_teachers`
  DROP FOREIGN KEY `plan_teachers_teacherid_fk`,
  DROP FOREIGN KEY `plan_teachers_fieldid_fk`;

ALTER TABLE `#__thm_organizer_lesson_teachers`
  DROP FOREIGN KEY `lesson_teachers_teacherid_fk`;

ALTER TABLE `#__thm_organizer_department_resources`
  DROP FOREIGN KEY `department_resources_teacherid_fk`,
  DROP FOREIGN KEY `department_resources_roomid_fk`;

DROP TABLE `#__thm_organizer_plan_rooms`;

DROP TABLE `#__thm_organizer_plan_teachers`;

ALTER TABLE `#__thm_organizer_lesson_teachers`
  ADD CONSTRAINT `lesson_teachers_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_teachers` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
  ADD CONSTRAINT `department_resources_teacherid_fk` FOREIGN KEY (`teacherID`)
REFERENCES `#__thm_organizer_teachers` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_department_resources`
  ADD CONSTRAINT `department_resources_roomid_fk` FOREIGN KEY (`roomID`)
REFERENCES `#__thm_organizer_rooms` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;