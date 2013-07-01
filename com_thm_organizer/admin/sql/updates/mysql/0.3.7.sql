
RENAME TABLE `#__thm_organizer_degree_programs` TO `#__thm_organizer_programs`;

ALTER TABLE `#__thm_organizer_pools`
ADD  KEY `lsfID` ( `lsfID` ),
ADD  KEY `externalID` ( `externalID` );