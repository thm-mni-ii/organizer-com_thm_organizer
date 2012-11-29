ALTER TABLE  `jos_thm_organizer_monitors`
ADD  `schedule_refresh` INT( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of seconds before the schedule refreshes' AFTER  `interval` ,
ADD  `content_refresh` INT( 3 ) UNSIGNED NOT NULL DEFAULT  '60' COMMENT  'the amount of time in seconds before the content refreshes' AFTER  `schedule_refresh`,
DROP `interval`