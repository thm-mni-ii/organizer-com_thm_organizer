ALTER TABLE `#__thm_organizer_room_types`
  CHANGE `type` `name_de` VARCHAR(50) NOT NULL,
  ADD `name_en` VARCHAR(50) NOT NULL,
  ADD `description_de` TEXT NOT NULL DEFAULT '',
  ADD `description_en` TEXT NOT NULL DEFAULT '',
  ADD `min_capacity` INT(4) UNSIGNED DEFAULT NULL,
  ADD `max_capacity` INT(4) UNSIGNED DEFAULT NULL,
  ADD CONSTRAINT UNIQUE (`gpuntisID`);

INSERT INTO `#__thm_organizer_room_types` (`gpuntisID`, `name_de`, `name_en`, `description_de`, `description_en`, `min_capacity`, max_capacity)
VALUES
  ('BR', 'Büro', 'Office', 'Ein Raum, der vorwiegend als Büro verwendet wird.',
   'A room that is primarily used as an office.', NULL, NULL),
  ('EXT', 'externer Raum', 'External Room', 'Ein Raum, der nicht direkt von der Schule verwaltet wird.', 'A room that is under external management.', NULL, NULL),
  ('FS.C', 'Fachsaal, Chemie', 'Chemistry Hall', 'Ein Chemie Vorlesungssaal mit entsprechende Ausrüstung.', 'A chemistry lecture hall with fitting equipment.', NULL, NULL),
  ('FS.P', 'Fachsaal, Physik', 'Physics Hall', 'Ein Physik Vorlesungssaal mit entsprechende Ausrüstung.', 'A physics lecture hall with fitting equipment.', NULL, NULL),
  ('GAR', 'Gruppenarbeitsraum', 'Groupwork Room', 'Ein Kleingruppenarbeitsraum, typischerweise ohne besondere Ausstattung.', 'A room for work in small groups, typically with no special equipment.', NULL, NULL),
  ('HS.A', 'Hörsaal, Auditorium', 'Lecture Hall, Auditorium', 'Hörsaal mit 200 Sitzplätze oder mehr. Hörsäle haben aufsteigender Böden und typischerweise feste Bestühlung.', 'Lecture hall with 200 or more seats. Lecture halls have inclined floors and typically fixed seating', '200', NULL),
  ('HS.G', 'Hörsaal, groß', 'Lecture Hall, Large', 'Hörsaal mit 90 bis einschließlich 199 Sitzplätze. Hörsäle haben aufsteigender Böden und typischerweise feste Bestühlung.', 'Lecture hall with 90 to 199 seats. Lecture halls have inclined floors and typically fixed seating', '90', '199'),
  ('HS.K', 'Hörsaal, klein', 'Lecture Hall, Small', 'Hörsaal mit bis zu 69 Sitzplätze. Hörsäle haben aufsteigender Böden und typischerweise feste Bestühlung.', 'Lecture hall with up to 69 seats. Lecture halls have inclined floors and typically fixed seating', NULL, '69'),
  ('HS.M', 'Hörsaal, mittel', 'Lecture Hall, Medium', 'Hörsaal mit 70 bis einschließlich 89 Sitzplätze. Hörsäle haben aufsteigender Böden und typischerweise feste Bestühlung.', 'Lecture hall with 70 to 89 seats. Lecture halls have inclined floors and typically fixed seating', '70', '89'),
  ('LAR', 'Labor', 'Laboratory', '', '', NULL, NULL),
  ('PCL', 'PC Labor', 'PC Laboratory', '', '', NULL, NULL),
  ('SR.G', 'Seminarraum, groß', 'Seminar Room, Large',
   'Seminarraum mit 60 Sitzplätze oder mehr. Seminarräume haben ebenen Böden und typischerweise lose Bestühlung',
   'Seminar room with 60 or more seats. Seminar rooms are flat and typically have loose seating.', '60', NULL),
  ('SR.K', 'Seminarraum, klein', 'Seminar Room, Small',
   'Seminarraum mit bis zu 19 Sitzplätze. Seminarräume haben ebenen Böden und typischerweise lose Bestühlung',
   'Seminar room with up to 19 seats. Seminar rooms are flat and typically have loose seating.', NULL, '19'),
  ('SR.M', 'Seminarraum, mittel', 'Seminar Room, Medium',
   'Seminarraum mit 20 bis einschließlich 59 Sitzplätze. Seminarräume haben ebenen Böden und typischerweise lose Bestühlung',
   'Seminar room with 20 to 59 seats. Seminar rooms are flat and typically have loose seating.', '20', '59'),
  ('XRM', 'Raumtyp unbekannt', 'Unknown Room Type', 'Die Kategorizierung des Raums wurde nicht ermittelt.',
   'The room\'s categorization was not determined.', NULL, NULL);