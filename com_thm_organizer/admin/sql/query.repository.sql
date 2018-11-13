-- for manually removing unwanted lessons for a given ccm id
DELETE
FROM `#__thm_organizer_lessons`
WHERE id IN (SELECT DISTINCT c.lessonID
             FROM `#__thm_organizer_calendar` AS c
                    INNER JOIN `#__thm_organizer_calendar_configuration_map` AS ccm ON ccm.calendarID = c.id
             WHERE ccm.id IN (''));