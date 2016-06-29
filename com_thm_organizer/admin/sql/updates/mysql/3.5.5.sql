ALTER TABLE `#__thm_organizer_fields`
  CHANGE `gpuntisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_methods`
  CHANGE `untisID` `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_rooms`
  CHANGE `gpuntisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_rooms`
  ADD UNIQUE `gpuntisID` (`gpuntisID`);

ALTER TABLE `#__thm_organizer_room_types`
  CHANGE `gpuntisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE `#__thm_organizer_room_types`
  DROP INDEX `gpuntisID`,
  ADD UNIQUE `gpuntisID` (`gpuntisID`);

ALTER TABLE `#__thm_organizer_teachers`
  CHANGE `gpuntisID`  `gpuntisID` VARCHAR(60)
CHARACTER SET utf8
COLLATE utf8_bin NULL DEFAULT NULL,
  CHANGE `username`  `username` VARCHAR(150) DEFAULT NULL;

UPDATE `#__thm_organizer_teachers`
SET `gpuntisID` = NULL
WHERE `gpuntisID` = '';

ALTER TABLE `#__thm_organizer_teachers`
  ADD UNIQUE `gpuntisID` (`gpuntisID`);

INSERT INTO `#__thm_organizer_methods` (`gpuntisID`, `abbreviation_de`, `abbreviation_en`, `name_de`, `name_en`)
VALUES
  ('AÜB', 'AÜB', 'PEX', 'Anwesenheitsübung', 'Presence Exercise'),
  ('BKR', 'BKR', 'RFC', 'Brückenkurs', 'Refresher Course'),
  ('KAB', 'KAB', 'CWK', 'Konstruktionsarbeit', 'Construction Work'),
  ('KES', 'KES', 'FRV', 'Klausureinsicht', 'Final Review'),
  ('KLA', 'KLA', 'FIN', 'Klausur', 'Final'),
  ('KTU', 'KTU', 'CTU', 'Konstruktionstutorium', 'Construction Tutorium'),
  ('KÜB', 'KÜB', 'CEX', 'Konstruktionsübung', 'Construction Exercise'),
  ('KVB', 'KVB', 'FPR', 'Klausurvorbereitung', 'Final Preparation'),
  ('LAB', 'LAB', 'LAB', 'Labor', 'Lab Exercise'),
  ('LAB/ÜBG', 'LAB/ÜBG', 'LAB/EXC', 'Labor / Übung', 'Lab Exercise / Exercise'),
  ('LKT', 'LKT', 'LCT', 'Lernkontrolle', 'Learning Control'),
  ('PRK', 'PRK', 'PRC', 'Praktikum', 'Practice'),
  ('PRÜ', 'PRÜ', 'EXM', 'Prüfung', 'Examination'),
  ('RÜB', 'RÜB', 'CEX', 'Rechenübung', 'Computational Exercise'),
  ('SEM', 'SEM', 'SEM', 'Seminar', 'Seminar'),
  ('SEM/PRK', 'SEM/PRK', 'SEM/PRC', 'Seminar/Praktikum', 'Seminar/Practice'),
  ('SMU', 'SMU', 'GDS', 'Seminaristische Unterricht', 'Guided Discussion'),
  ('TUT', 'TUT', 'TUT', 'Tutorium', 'Tutorium'),
  ('ÜBG', 'ÜBG', 'EXC', 'Übung', 'Exercise'),
  ('VKR', 'VKR', 'PCR', 'Vorkurs', 'Introductory Course'),
  ('VRL', 'VRL', 'LCT', 'Vorlesung', 'Lecture'),
  ('VRL/PRK', 'VRL/PRK', 'LCT/PRC', 'Vorlesung/Praktikum', 'Lecture/Practice'),
  ('VRL/PRK/SEM', 'VRL/PRK/SEM', 'LCT/PRC/SEM', 'Vorlesung/Praktikum/Seminar', 'Lecture/Practice/Seminar'),
  ('VRL/PRK/ÜBG', 'VRL/PRK/ÜBG', 'LCT/PRC/EXC', 'Vorlesung/Praktikum/Übung', 'Lecture/Practice/Exercise'),
  ('VRL/SEM', 'VRL/SEM', 'LCT/SEM', 'Vorlesung/Seminar', 'Lecture/Seminar'),
  ('VRL/ÜBG', 'VRL/ÜBG', 'LCT/EXC', 'Vorlesung/Übung', 'Lecture/Exercise'),
  ('WPF', 'WPF', 'ELC', 'Wahlpflicht', 'Elective'),
  ('WPF/PRK', 'WPF/PRK', 'ELC/PRC', 'Wahlpflicht/Praktikum', 'Elective/Practice'),
  ('WPF/VRL', 'WPF/VRL', 'ELC/LCT', 'Wahlpflicht/Vorlesung', 'Elective/Lecture');

INSERT INTO `#__thm_organizer_methods` (`gpuntisID`, `abbreviation_de`)
VALUES
  ('BM', 'BM'),
  ('BSP', 'BSP'),
  ('IVR', 'IVR'),
  ('EXK', 'EXK'),
  ('K', 'K'),
  ('KA', 'KA'),
  ('KE', 'KE'),
  ('KT', 'KT'),
  ('KÜ', 'KÜ'),
  ('KV', 'KV'),
  ('LK', 'LK'),
  ('MP', 'MP'),
  ('O', 'O'),
  ('P', 'P'),
  ('P/Ü', 'P/Ü'),
  ('PRÜ', 'PRÜ'),
  ('RÜ', 'RÜ'),
  ('S', 'S'),
  ('S/P', 'S/P'),
  ('SCH', 'SCH'),
  ('SONST', 'SONST'),
  ('STZ', 'STZ'),
  ('SU', 'SU'),
  ('T', 'T'),
  ('TAG', 'TAG'),
  ('Ü', 'Ü'),
  ('Ü/P', 'Ü/P'),
  ('ÜG', 'ÜG'),
  ('ÜK', 'ÜK'),
  ('V', 'V'),
  ('V/P', 'V/P'),
  ('V/P/S', 'V/P/S'),
  ('V/P/Ü', 'V/P/Ü'),
  ('V/S', 'V/S'),
  ('V/Ü', 'V/Ü'),
  ('V/Ü+P', 'V/Ü+P'),
  ('VB', 'VB'),
  ('VER', 'VER'),
  ('VK', 'VK'),
  ('VRT', 'VRT'),
  ('VSM', 'VSM'),
  ('WP', 'WP'),
  ('WP/P', 'WP/P'),
  ('WP/V', 'WP/V'),
  ('Z', 'Z');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, 'LS_', '');

UPDATE `#__thm_organizer_user_schedules`
SET `data` = REPLACE(`data`, 'LS_', '');

UPDATE `#__thm_organizer_fields`
SET `gpuntisID` = REPLACE(`gpuntisID`, 'DS_', '');