CREATE TABLE IF NOT EXISTS `#__thm_organizer_buildings` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `campusID`     INT(11) UNSIGNED          DEFAULT NULL,
  `name`         VARCHAR(60)      NOT NULL,
  `location`     VARCHAR(20)      NOT NULL,
  `address`      VARCHAR(255)     NOT NULL,
  `propertyType` INT(1) UNSIGNED  NOT NULL DEFAULT '0'
  COMMENT '0 - new/unknown | 1 - owned | 2 - rented/leased',
  PRIMARY KEY (`id`),
  KEY `campusID` (`campusID`),
  UNIQUE KEY `prefix` (`campusID`, `name`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_rooms`
  ADD COLUMN `buildingID` INT(11) UNSIGNED DEFAULT NULL;

ALTER TABLE `#__thm_organizer_buildings`
  ADD CONSTRAINT `building_campusID_fk` FOREIGN KEY (`campusID`) REFERENCES `#__thm_organizer_campuses` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

ALTER TABLE `#__thm_organizer_rooms`
  ADD CONSTRAINT `room_buildingID_fk` FOREIGN KEY (`buildingID`) REFERENCES `#__thm_organizer_buildings` (`id`)
  ON DELETE SET NULL
  ON UPDATE CASCADE;

INSERT INTO `#__thm_organizer_buildings` (`id`, `campusID`, `name`, `location`, `address`, `propertyType`)
VALUES
  ('1', '2', 'A10', '50.587015,08.683445', 'Wiesenstraße 14', '1'),
  ('2', '2', 'A11', '50.586982,08.682800', 'Wiesenstraße 12', '1'),
  ('3', '2', 'A12', '50.586774,08.682604', 'Wiesenstraße 8', '1'),
  ('4', '2', 'A13', '50.586607,08.681724', 'Platz der Deutschen Einheit 2', '2'),
  ('5', '2', 'A14', '50.586311,08.682234', 'Moltkestraße 3', '1'),
  ('6', '2', 'A15', '50.586631,08.683586', 'Wiesenstraße 16', '1'),
  ('7', '2', 'A16', '50.586527,08.682859', 'Wiesenstraße 10', '1'),
  ('8', '2', 'A20', '50.587130,08.681875', 'Platz der Deutschen Einheit 1', '1'),
  ('9', '2', 'A21', '50.587288,08.682814', 'Wiesenstraße 11', '1'),
  ('10', '2', 'A22', '50.587027,08.682146', 'Wiesenstraße 9', '1'),
  ('11', '2', 'A30', '50.586731,08.680252', 'Senckenbergstraße ??', '1'),
  ('12', '3', 'B10', '50.585658,08.680864', 'Ostanlage 39', '1'),
  ('13', '3', 'B11', '50.585340,08.680502', 'Ostanlage 39', '1'),
  ('14', '3', 'B12', '50.585088,08.680346', 'Ostanlage 39', '2'),
  ('15', '3', 'B13', '50.584719,08.679920', 'Ostanlage 45', '2'),
  ('16', '3', 'B14', '50.584319,08.679405', 'Ostanlage 45', '2'),
  ('17', '3', 'B20', '50.582969,08.679350', 'Ludwigsplatz 13', '2'),
  ('18', '3', 'B21', '50.583490,08.680913', 'Ludwigsplatz 4', '2'),
  ('19', '4', 'C10', '50.586304,08.684439', 'Ringallee 5', '1'),
  ('20', '4', 'C13', '50.585413,08.683996', 'Eichgärtenallee 1', '1'),
  ('21', '4', 'C14', '50.585684,08.684685', 'Eichgärtenallee 3', '1'),
  ('22', '4', 'C15', '50.586074,08.685138', 'Eichgärtenallee 7', '2'),
  ('23', '4', 'C16', '50.585841,08.685058', 'Eichgärtenallee 5', '1'),
  ('24', '4', 'C20', '50.585945,08.683771', 'Moltkestraße 11', '1'),
  ('25', '4', 'C21', '50.585707,08.683460', 'Moltkestraße 11a', '1'),
  ('26', '4', 'C50', '50.585080,08.685363', 'Eichgärtenallee 6', '1'),
  ('27', '5', 'D10', '50.589731,08.681680', 'Gutfleischstraße 3-5', '1'),
  ('28', '5', 'D11', '50.589994,08.681522', 'Gutfleischstraße 3-5', '1'),
  ('29', '5', 'D12', '50.590232,08.681334', 'Gutfleischstraße 3-5', '1'),
  ('30', '5', 'D13', '50.590452,08.681173', 'Gutfleischstraße 3-5', '1'),
  ('31', '5', 'D14', '50.590701,08.680647', 'Gutfleischstraße 3-5', '1'),
  ('32', '5', 'D15', '50.590081,08.680617', 'Gutfleischstraße 3-5', '1'),
  ('33', '5', 'D16', '50.589684,08.680936', 'Gutfleischstraße 3-5', '1'),
  ('34', '6', 'E10', '50.583028,08.676480', 'Südanlage 6', '1'),
  ('35', '6', 'E11', '50.582878,08.676813', 'Bismarckstraße 2', '1'),
  ('36', '6', 'E12', '50.582771,08.676671', 'Bismarckstraße 2a', '1'),
  ('37', '6', 'E13', '50.582471,08.677111', 'Bismarckstraße 4', '1'),
  ('38', '6', 'E14', '50.582704,08.676481', 'Bismarckstraße ??', '1'),
  ('39', '6', 'E15', '50.582685,08.677570', 'Bismarckstraße 5', '2'),
  ('40', '7', 'G10', '50.588787,08.671787', 'Nordanlage 19', '2'),
  ('41', '8', 'I10', '50.586293,08.672977', 'Löbershof 10', '2'),
  ('42', '8', 'I11', '50.587484,08.674075', 'Kirchenplatz 7a', '2'),
  ('43', '10', 'A1', '50.330460,08.759413', 'Wilhelm-Leuschner-Straße 13', '1'),
  ('44', '10', 'A2', '50.330051,08.759335', 'Wilhelm-Leuschner-Straße 13', '1'),
  ('45', '10', 'A3', '50.330260,08.758815', 'Wilhelm-Leuschner-Straße 13', '1'),
  ('46', '10', 'A4', '50.330630,08.758812', 'Wilhelm-Leuschner-Straße 13', '1'),
  ('47', '10', 'A5', '50.329598,08.758318', 'Wilhelm-Leuschner-Straße 13', '1'),
  ('48', '10', 'A6', '50.330007,08.758117', 'Wilhelm-Leuschner-Straße 13', '1'),
  ('49', '10', 'A7', '50.330353,08.757902', 'Wilhelm-Leuschner-Straße 13', '1'),
  ('50', '10', 'A8', '50.330146,08.757323', 'Kettlerstraße ??', '1'),
  ('51', '11', 'B1', '50.331376,08.759607', 'Wilhelm-Leuschner-Straße 10', '1'),
  ('52', '11', 'B2', '50.331681,08.760318', 'Wilhelm-Leuschner-Straße 10', '1'),
  ('53', '12', 'C1', '50.327918,08.756230', 'Am Dachspfad ??', '1'),
  ('54', '12', 'C2', '50.328291,08.756241', 'Karlsbader Straße', '1'),
  ('55', '12', 'C3', '50.328130,08.756783', 'Tepler Straße ??', '1'),
  ('56', '13', 'D1', '', 'Raiffeisenstraße 6', '2'),
  ('57', '13', 'D2', '', 'Raiffeisenstraße ??', '2'),
  ('58', '15', 'A1', '50.551343,08.521479', 'Steinbühlstraße 6', '2'),
  ('59', '15', 'A15', '50.549755,08.522740', 'Spillburgstraße 6', '2'),
  ('60', '16', 'B19', '50.550478,08.529382', 'Schanzenfeldstraße 14', '2'),
  ('61', '24', 'Hessenhalle I', '50.587348,08.661112', 'An der Hessenhalle 11', '2'),
  ('62', '24', 'Hessenhalle II', '50.587058,08.660932', 'An der Hessenhalle 11', '2'),
  ('63', '24', 'Hessenhalle III', '50.586820,08.660953', 'An der Hessenhalle 11', '2'),
  ('64', '24', 'Hessenhalle IV', '50.586551,08.660932', 'An der Hessenhalle 11', '2'),
  ('65', '24', 'Hessenhalle V', '50.586238,08.660878', 'An der Hessenhalle 11', '2'),
  ('66', '24', 'Hessenhalle VI', '50.587250,08.659955', 'An der Hessenhalle 11', '2'),
  ('67', '24', 'Hessenhalle VII', '50.586959,08.659974', 'An der Hessenhalle 11', '2');

UPDATE `#__thm_organizer_rooms` SET `buildingID` = 43 WHERE `gpUntisID` LIKE 'A1.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 1 WHERE `gpUntisID` LIKE 'A10.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 3 WHERE `gpUntisID` LIKE 'A12.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 6 WHERE `gpUntisID` LIKE 'A15.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 44 WHERE `gpUntisID` LIKE 'A2.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 8 WHERE `gpUntisID` LIKE 'A20.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 9 WHERE `gpUntisID` LIKE 'A21.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 45 WHERE `gpUntisID` LIKE 'A3.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 50 WHERE `gpUntisID` LIKE 'A8.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 12 WHERE `gpUntisID` LIKE 'B10.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 15 WHERE `gpUntisID` LIKE 'B13.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 16 WHERE `gpUntisID` LIKE 'B14.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 17 WHERE `gpUntisID` LIKE 'B20.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 18 WHERE `gpUntisID` LIKE 'B21.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 19 WHERE `gpUntisID` LIKE 'C10.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 20 WHERE `gpUntisID` LIKE 'C13.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 21 WHERE `gpUntisID` LIKE 'C14.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 22 WHERE `gpUntisID` LIKE 'C15.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 25 WHERE `gpUntisID` LIKE 'C21.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 26 WHERE `gpUntisID` LIKE 'C50.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 27 WHERE `gpUntisID` LIKE 'D10.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 28 WHERE `gpUntisID` LIKE 'D11.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 29 WHERE `gpUntisID` LIKE 'D12.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 34 WHERE `gpUntisID` LIKE 'E10.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 35 WHERE `gpUntisID` LIKE 'E11.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 36 WHERE `gpUntisID` LIKE 'E12.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 37 WHERE `gpUntisID` LIKE 'E13.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 39 WHERE `gpUntisID` LIKE 'E15.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 40 WHERE `gpUntisID` LIKE 'G10.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 61 WHERE `gpUntisID` = 'HH1';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 63 WHERE `gpUntisID` = 'HH3';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 64 WHERE `gpUntisID` = 'HH4';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 66 WHERE `gpUntisID` = 'HH6';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 67 WHERE `gpUntisID` = 'HH7';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 41 WHERE `gpUntisID` LIKE 'I10.%';
UPDATE `#__thm_organizer_rooms` SET `buildingID` = 42 WHERE `gpUntisID` LIKE 'I11.%';