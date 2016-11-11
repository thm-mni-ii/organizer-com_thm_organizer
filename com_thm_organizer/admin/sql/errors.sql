Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('14873','400','')
Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('14878','312','')
Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('14879','341','')

Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('15949','411','')
Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('15950','400','')
Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('16299','312','')
Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
  CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('16300','341','')

Cannot add or update a child row: a foreign key constraint fails (`joomla3_organizer`.`#__thm_organizer_lesson_pools`,
CONSTRAINT `lesson_pools_poolid_fk` FOREIGN KEY (`poolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`) ON DELETE CASCADE ON UPDAT)
SQL=INSERT INTO `#__thm_organizer_lesson_pools` (`subjectID`,`poolID`,`delta`) VALUES ('18806','411','')

UPDATE v7ocf_thm_organizer_schedules SET newSchedule = NULL WHERE departementID = 4;
DELETE FROM v7ocf_thm_organizer_lessons WHERE departmentID = 4;