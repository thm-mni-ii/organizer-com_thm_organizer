
-- for manually removing unwanted lessons for a given ccm id
DELETE FROM `#__thm_organizer_lessons`
WHERE id IN (
  SELECT DISTINCT c.lessonID
  FROM `#__thm_organizer_calendar` as c
    INNER JOIN `#__thm_organizer_calendar_configurations_map` as ccm on ccm.calendarID = c.id
  WHERE ccm.id IN ('')
);