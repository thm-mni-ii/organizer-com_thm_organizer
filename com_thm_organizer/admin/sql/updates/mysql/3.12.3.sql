ALTER TABLE `#__thm_organizer_room_features_map`
  DROP FOREIGN KEY `room_features_map_featureid_fk`,
  DROP FOREIGN KEY `room_features_map_roomid_fk`;

DROP TABLE `#__thm_organizer_room_features_map`;

DROP TABLE `#__thm_organizer_room_features`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_campuses` (
  `id`       INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentID` INT(11) UNSIGNED          DEFAULT NULL,
  `name_de`  VARCHAR(60)      NOT NULL,
  `name_en`  VARCHAR(60)      NOT NULL,
  `isCity`   TINYINT(1)       NOT NULL DEFAULT '0',
  `location` VARCHAR(20)      NOT NULL,
  `address`  VARCHAR(255)     NOT NULL,
  `city`     VARCHAR(60)      NOT NULL,
  `zipCode`  VARCHAR(60)      NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`),
  UNIQUE KEY `germanName` (`parentID`, `name_de`),
  UNIQUE KEY `englishName` (`parentID`, `name_en`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_campuses`
  ADD CONSTRAINT `campus_parentID_fk` FOREIGN KEY (`parentID`) REFERENCES `#__thm_organizer_campuses` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

INSERT INTO `#__thm_organizer_campuses` (`id`, `parentID`, `name_de`, `name_en`, `isCity`, `location`, `address`, `city`, `zipCode`)
VALUES
  ('1', NULL, 'THM Campus Gießen', 'THM Giessen Campus', '1', '50.586934,08.682115', 'Wiesenstraße 14', 'Gießen',
   '35390'),
  ('2', '1', 'Campus A', 'A Campus', '0', '50.586934,08.682115', '', '', ''),
  ('3', '1', 'Campus B', 'B Campus', '0', '50.585584,08.680636', '', '', ''),
  ('4', '1', 'Campus C', 'C Campus', '0', '50.585578,08.684504', '', '', ''),
  ('5', '1', 'Campus D', 'D Campus', '0', '50.590017,08.681038', '', '', ''),
  ('6', '1', 'Campus E', 'E Campus', '0', '50.582724,08.676923', '', '', ''),
  ('7', '1', 'Campus G', 'G Campus', '0', '50.588787,08.671787', '', '', ''),
  ('8', '1', 'Campus I', 'I Campus', '0', '50.586382,08.672964', '', '', ''),
  ('9', NULL, 'THM Campus Friedberg', 'THM Friedberg Campus', '1', '50.330208,08.758748', 'Wilhelm-Leuschner-Straße 13', 'Friedberg', '61169'),
  ('10', '9', 'Campus A', 'A Campus', '0', '50.330208,08.758748', '', '', ''),
  ('11', '9', 'Campus B', 'B Campus', '0', '50.331463,08.759896', '', '', ''),
  ('12', '9', 'Campus C', 'C Campus', '0', '50.328116,08.756382', '', '', ''),
  ('13', '9', 'Campus D', 'D Campus', '0', '', '', '', ''),
  ('14', NULL, 'THM Campus Wetzlar', 'THM Wetzlar Campus', '1', '50.551330,08.521448', 'Charlotte-Bamberg-Straße 3', 'Wetzlar', '35578'),
  ('15', '14', 'Campus A', 'A Campus', '0', '50.551330,08.521448', '', '', ''),
  ('16', '14', 'Campus B', 'B Campus', '0', '50.550693,08.529417', '', '', ''),
  ('17', NULL, 'THM Campus Bad Wildungen', 'THM Bad Wildungen Campus', '1', '51.117264,09.123137', 'Eichlerstraße 25', 'Bad Wildungen', '34537'),
  ('18', NULL, 'THM Campus Bad Hersfeld', 'THM Bad Hersfeld Campus', '1', '50.870981,09.708601', 'Benno-Schilde-Platz 3', 'Bad Hersfeld', '36251'),
  ('19', NULL, 'THM Campus Biedenkopf', 'THM Biedenkopf Campus', '1', '50.916110,08.516289', 'Hainstraße 103', 'Biedenkopf', '35216'),
  ('20', NULL, 'THM Campus Frankenberg', 'THM Frankenberg Campus', '1', '51.056835,08.791999', 'Bahnhofstraße  8a', 'Frankenberg', '35066'),
  ('21', NULL, 'THM Campus Bad Vilbel', 'THM Bad Vilbel Campus', '1', '50.181754,08.729184', 'Huizener Straße 60', 'Bad Vilbel', '61118'),
  ('22', NULL, 'THM Campus Limburg', 'THM Limburg Campus', '1', '50.384112,08.061014', 'Bahnhofsplatz 1a', 'Limburg',
   '65549'),
  ('24', NULL, 'Messe Gießen', 'Giessen Expo', '1', '50.586773,08.660667', 'An der Hessenhalle 11', 'Gießen', '35398');


ALTER TABLE `#__thm_organizer_subjects`
  ADD COLUMN `campusID` INT(11) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_subjects`
  ADD CONSTRAINT `subject_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

