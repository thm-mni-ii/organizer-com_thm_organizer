UPDATE `#__thm_organizer_grids`
SET grid = '{"periods":{"1":{"startTime":"0800","endTime":"0959"},"2":{"startTime":"1000","endTime":"1159"},"3":{"startTime":"1200","endTime":"1359"},"4":{"startTime":"1400","endTime":"1559"},"5":{"startTime":"1600","endTime":"1759"},"6":{"startTime":"1800","endTime":"1959"}},"startDay":1,"endDay":6}'
WHERE id = '2';

UPDATE `#__thm_organizer_calendar`
SET endTime = '09:59:00'
WHERE endTime = '10:00:00';

UPDATE `#__thm_organizer_schedules`
SET newSchedule = REPLACE(newSchedule,'-1000','-0959');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`,'-1000','-0959');

UPDATE `#__thm_organizer_calendar`
SET endTime = '11:59:00'
WHERE endTime = '12:00:00';

UPDATE `#__thm_organizer_schedules`
SET newSchedule = REPLACE(newSchedule,'-1200','-1159');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`,'-1200','-1159');

UPDATE `#__thm_organizer_calendar`
SET endTime = '13:59:00'
WHERE endTime = '14:00:00';

UPDATE `#__thm_organizer_schedules`
SET newSchedule = REPLACE(newSchedule,'-1400','-1359');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`,'-1400','-1359');

UPDATE `#__thm_organizer_calendar`
SET endTime = '15:59:00'
WHERE endTime = '16:00:00';

UPDATE `#__thm_organizer_schedules`
SET newSchedule = REPLACE(newSchedule,'-1600','-1559');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`,'-1600','-1559');

UPDATE `#__thm_organizer_calendar`
SET endTime = '17:59:00'
WHERE endTime = '18:00:00';

UPDATE `#__thm_organizer_schedules`
SET newSchedule = REPLACE(newSchedule,'-1800','-1759');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`,'-1800','-1759');

UPDATE `#__thm_organizer_calendar`
SET endTime = '19:59:00'
WHERE endTime = '20:00:00';

UPDATE `#__thm_organizer_schedules`
SET newSchedule = REPLACE(newSchedule,'-2000','-1959');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`,'-2000','-1959');