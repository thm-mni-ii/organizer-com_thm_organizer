TRUNCATE TABLE `#__thm_organizer_prerequisites`;

ALTER TABLE `#__thm_organizer_prerequisites`
ADD `id` int( 11 )  UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY `id` ( `id` ),
ADD UNIQUE KEY `entry` (`subjectID`,`prerequisite`);