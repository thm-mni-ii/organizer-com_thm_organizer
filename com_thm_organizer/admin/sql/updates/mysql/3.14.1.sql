UPDATE `#__assets` AS a1
INNER JOIN `#__assets` AS a2 ON a1.`lft` > a2.`lft` AND a1.`rgt` < a2.`rgt`
SET a1.`parent_id` = a2.`id`
WHERE a1.`parent_id` = 0
  AND a2.`name` LIKE 'com_thm_organizer_department.%';

ALTER TABLE `#__thm_organizer_department_resources`
  DROP FOREIGN KEY `department_resources_poolid_fk`,
  DROP FOREIGN KEY `department_resources_subjectid_fk`,
  DROP FOREIGN KEY `department_resources_roomid_fk`;

DELETE
FROM `#__thm_organizer_department_resources`
WHERE `poolID` IS NOT NULL
   OR `subjectID` IS NOT NULL
   OR `roomID` IS NOT NULL;

ALTER TABLE `#__thm_organizer_department_resources`
  DROP COLUMN `poolID`,
  DROP COLUMN `subjectID`,
  DROP COLUMN `roomID`;

ALTER TABLE `#__thm_organizer_schedules`
  DROP COLUMN `departmentname`,
  DROP COLUMN `endDate`,
  DROP COLUMN `semestername`,
  DROP COLUMN `schedule`,
  DROP COLUMN `startDate`;

ALTER TABLE `#__thm_organizer_schedules`
  CHANGE COLUMN `newSchedule` `schedule` MEDIUMTEXT NOT NULL;
