ALTER TABLE `#__thm_organizer_rooms`
DROP `capacity`,
DROP `manager`,
ADD `campus` varchar(128) NOT NULL DEFAULT '' AFTER `manager`,
ADD `building` varchar(64) NOT NULL DEFAULT '', AFTER `campus`,
DROP INDEX `manager`;

ALTER TABLE `#__thm_organizer_classes`
DROP `teacherID`,
ADD `manager` varchar(50) DEFAULT '' AFTER `alias`,
DROP INDEX `teacherID`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_types` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_room_types` (`id`, `roomtype`) VALUES
('1', 'COM_THM_ORGANIZER_RM_LECTURE'),
('2', 'COM_THM_ORGANIZER_RM_SEMINAR'),
('3', 'COM_THM_ORGANIZER_RM_LAB'),
('4', 'COM_THM_ORGANIZER_RM_PCLAB'),
('5', 'COM_THM_ORGANIZER_RM_SBJHALL'),
('6', 'COM_THM_ORGANIZER_RM_SBJROOM'),
('7', 'COM_THM_ORGANIZER_RM_COMPUTER'),
('8', 'COM_THM_ORGANIZER_RM_PROJECT'),
('9', 'COM_THM_ORGANIZER_RM_OTHER'),
('10', 'COM_THM_ORGANIZER_RM_OFFICE'),
('11', 'COM_THM_ORGANIZER_RM_SHOP'),
('12', 'COM_THM_ORGANIZER_RM_ROOM');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_room_details` (`id`, `details`) VALUES
('1', ''),
('2', 'COM_THM_ORGANIZER_RM_1TO20'),
('3', 'COM_THM_ORGANIZER_RM_21TO33'),
('4', 'COM_THM_ORGANIZER_RM_34TO47'),
('5', 'COM_THM_ORGANIZER_RM_48TO69'),
('6', 'COM_THM_ORGANIZER_RM_70TO84'),
('7', 'COM_THM_ORGANIZER_RM_85TO105'),
('8', 'COM_THM_ORGANIZER_RM_106TO130'),
('9', 'COM_THM_ORGANIZER_RM_131TO185'),
('10', 'COM_THM_ORGANIZER_RM_186TO270'),
('11', 'COM_THM_ORGANIZER_RM_271TO400'),
('12', 'COM_THM_ORGANIZER_RM_PHYS_SMALL'),
('13', 'COM_THM_ORGANIZER_RM_PHYS_LARGE'),
('14', 'COM_THM_ORGANIZER_RM_CHEM'),
('15', 'COM_THM_ORGANIZER_RM_LAB'),
('16', 'COM_THM_ORGANIZER_RM_NOSPEC'),
('17', 'COM_THM_ORGANIZER_RM_RESEARCH_LAB'),
('18', 'COM_THM_ORGANIZER_RM_MEASURE'),
('19', 'COM_THM_ORGANIZER_RM_ANNEXLAB'),
('20', 'COM_THM_ORGANIZER_RM_PREP'),
('21', 'COM_THM_ORGANIZER_RM_FUNCTION'),
('22', 'COM_THM_ORGANIZER_RM_GENERAL'),
('23', 'COM_THM_ORGANIZER_RM_SPECIAL'),
('24', 'COM_THM_ORGANIZER_RM_RESEARCH'),
('25', 'COM_THM_ORGANIZER_RM_EQUIPMENT'),
('26', 'COM_THM_ORGANIZER_RM_SERVER'),
('27', 'COM_THM_ORGANIZER_RM_TRAINING'),
('28', 'COM_THM_ORGANIZER_RM_SECURE'),
('29', 'COM_THM_ORGANIZER_RM_UNSECURE'),
('30', 'COM_THM_ORGANIZER_RM_EXERCISE'),
('31', 'COM_THM_ORGANIZER_RM_STUDY_OFFICE'),
('32', 'COM_THM_ORGANIZER_RM_SOCIAL'),
('33', 'COM_THM_ORGANIZER_RM_ARCHIVE'),
('34', 'COM_THM_ORGANIZER_RM_ANNEXSEMINAR'),
('35', 'COM_THM_ORGANIZER_RM_STORAGE_OTHER'),
('36', 'COM_THM_ORGANIZER_RM_STORAGE_LAB'),
('37', 'COM_THM_ORGANIZER_RM_STORAGE'),
('38', 'COM_THM_ORGANIZER_RM_OFFICE'),
('39', 'COM_THM_ORGANIZER_RM_ENIGINEER_LAB'),
('40', 'COM_THM_ORGANIZER_RM_WORKSHOP'),
('41', 'COM_THM_ORGANIZER_RM_CONFERENCE'),
('42', 'COM_THM_ORGANIZER_RM_SUPPLEMENT'),
('43', 'COM_THM_ORGANIZER_RM_VIDEO_CONFERENCE'),
('44', 'COM_THM_ORGANIZER_RM_PRECISION_MECHANICS'),
('45', 'COM_THM_ORGANIZER_RM_METAL'),
('46', 'COM_THM_ORGANIZER_RM_ELECTRO'),
('47', 'COM_THM_ORGANIZER_RM_ANNEX'),
('48', 'COM_THM_ORGANIZER_RM_WAREHOUSE'),
('49', 'COM_THM_ORGANIZER_RM_UNKNOWN'),
('50', 'COM_THM_ORGANIZER_RM_PHYS_CHEM');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_room_descriptions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gpuntisID` varchar(50) NOT NULL DEFAULT '',
  `externalKey` varchar(50) NOT NULL DEFAULT '',
  `typeID` int(11) unsigned NOT NULL DEFAULT '1',
  `descID` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY (`typeID`),
  KEY (`descID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_room_descriptions` (`id`, `gpuntisID`, `externalKey`, `typeID`, `descID`) VALUES
(1, 'DS_Hörsaal', 'Hörsaal', '1', '1'),
(2, 'DS_Seminar', 'Seminar', '2', '1'),
(3, 'DS_Labor', 'Labor', '3', '1'),
(4, 'DS_PCLab', 'PCLab', '4', '1'),
(5, 'DS_A1', 'A1', '2', '2'),
(6, 'DS_A2', 'A2', '2', '3'),
(7, 'DS_A3', 'A3', '1', '4'),
(8, 'DS_A4', 'A4', '1', '5'),
(9, 'DS_A5', 'A5', '1', '6'),
(10, 'DS_A6', 'A6', '1', '7'),
(11, 'DS_A7', 'A7', '1', '8'),
(12, 'DS_A8', 'A8', '1', '9'),
(13, 'DS_A9', 'A9', '1', '10'),
(14, 'DS_AZ', 'AZ', '1', '11'),
(15, 'DS_B1', 'B1', '5', '12'),
(16, 'DS_B2', 'B2', '5', '13'),
(17, 'DS_B3', 'B3', '5', '14'),
(18, 'DS_C1', 'C1', '6', '15'),
(19, 'DS_C2', 'C2', '6', '16'),
(20, 'DS_C3', 'C3', '6', '17'),
(21, 'DS_C4', 'C4', '6', '18'),
(22, 'DS_C5', 'C5', '6', '19'),
(23, 'DS_C6', 'C6', '6', '20'),
(24, 'DS_C9', 'C9', '6', '21'),
(25, 'DS_D1', 'D1', '7', '22'),
(26, 'DS_D2', 'D2', '7', '23'),
(27, 'DS_D3', 'D3', '7', '24'),
(28, 'DS_D4', 'D4', '7', '25'),
(29, 'DS_D5', 'D5', '7', '26'),
(30, 'DS_D7', 'D7', '7', '27'),
(31, 'DS_F1', 'F1', '8', '28'),
(32, 'DS_F2', 'F2', '8', '29'),
(33, 'DS_F3', 'F3', '8', '30'),
(34, 'DS_F5', 'F5', '8', '31'),
(35, 'DS_F6', 'F6', '8', '32'),
(36, 'DS_H1', 'H1', '9', '33'),
(37, 'DS_H2', 'H2', '9', '34'),
(38, 'DS_H3', 'H3', '9', '35'),
(39, 'DS_H4', 'H4', '9', '36'),
(40, 'DS_H5', 'H5', '9', '37'),
(41, 'DS_I1', 'I1', '10', '38'),
(42, 'DS_I2', 'I2', '10', '39'),
(43, 'DS_I3', 'I3', '10', '40'),
(44, 'DS_I4', 'I4', '10', '41'),
(45, 'DS_I5', 'I5', '10', '42'),
(46, 'DS_I6', 'I6', '10', '43'),
(47, 'DS_W1', 'W1', '11', '44'),
(48, 'DS_W2', 'W2', '11', '45'),
(49, 'DS_W5', 'W5', '11', '46'),
(50, 'DS_W7', 'W7', '11', '47'),
(51, 'DS_W9', 'W9', '11', '48'),
(52, 'DS_X', 'X', '12', '49'),
(53, 'DS_B', 'B', '5', '50');


CREATE TABLE IF NOT EXISTS `#__thm_organizer_institutions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_institutions` (`id`, `name`) VALUES
(1, 'THM'),
(2, 'JLUG'),
(3, 'PUM');


CREATE TABLE IF NOT EXISTS `#__thm_organizer_campuses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_campuses` (`id`, `name`) VALUES
(1, 'Giessen'),
(2, 'Friedberg'),
(3, 'Wetzlar'),
(4, 'Marburg');

CREATE TABLE IF NOT EXISTS `#__thm_organizer_buildings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_buildings` (`id`, `name`) VALUES
(1, ''),
(2, 'A'),
(3, 'C'),
(4, 'F'),
(5, 'G'),
(6, 'I'),
(7, 'V');