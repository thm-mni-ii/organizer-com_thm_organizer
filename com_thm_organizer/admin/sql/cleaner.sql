DELETE FROM `#__thm_organizer_plan_pools` WHERE programID IS NULL;

DELETE FROM `#__thm_organizer_lesson_subjects` WHERE id NOT IN (SELECT DISTINCT subjectID FROM`#__thm_organizer_lesson_pools`);

DELETE FROM `#__thm_organizer_lesson_subjects` WHERE id NOT IN (SELECT DISTINCT subjectID FROM`#__thm_organizer_lesson_teachers`);

DELETE FROM `#__thm_organizer_lesson_subjects` WHERE id NOT IN (SELECT DISTINCT lessonID FROM`#__thm_organizer_lesson_configurations`);

DELETE FROM `#__thm_organizer_lessons` WHERE id NOT IN (SELECT DISTINCT lessonID FROM`#__thm_organizer_lesson_subjects`);
