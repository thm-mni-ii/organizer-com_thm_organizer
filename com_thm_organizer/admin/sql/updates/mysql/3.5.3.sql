ALTER TABLE `#__thm_organizer_schedules`
  MODIFY `schedule` MEDIUMTEXT NOT NULL;

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, 'lessontypes', 'methods');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, 'roomtypes', 'room_types');

UPDATE `#__thm_organizer_schedules`
SET `schedule` = REPLACE(`schedule`, 'DS_', '');

INSERT INTO `#__thm_organizer_room_types` (`gpuntisID`, `name_de`, `name_en`)
VALUES
  ('A1', 'Seminarraum,  1-20 Plätze', 'z'),
  ('A2', 'Seminarraum, 21-33 Plätze', 'z'),
  ('A3', 'Seminarraum, 34-47 Plätze', 'z'),
  ('A4', 'Hörsaal,  48- 69 Plätze', 'z'),
  ('A5', 'Hörsaal,  70- 84 Plätze', 'z'),
  ('A6', 'Hörsaal,  85-105 Plätze', 'z'),
  ('A7', 'Hörsaal, 100-150 Plätze', 'z'),
  ('A8', 'Hörsaal, 150-200 Plätze', 'z'),
  ('A9', 'Hörsaal, 200-300 Plätze', 'z'),
  ('AZ', 'Hörsaal, 300-400 Plätze', 'z'),
  ('B1', 'Fachsaal, Physik-Hörsaal (gr.)', 'z'),
  ('B2', 'Fachsaal, Physik-Hörsaal (kl.)', 'z'),
  ('B3', 'Fachsaal, Chemie', 'z'),
  ('C1', 'Fachsaal, Praktikumsraum mit. spez. Einbauten', 'z'),
  ('C2', 'Fachsaal, Praktikumsraum keine spez. Einbauten', 'z'),
  ('C3', 'Fachsaal, Forschungslabor', 'z'),
  ('C4', 'Fachsaal, Mess-Stand', 'z'),
  ('C5', 'Fachsaal, Nebenlabor', 'z'),
  ('C6', 'Fachsaal, Vorbereitungsraum', 'z'),
  ('C9', 'Fachsaal, Betriebsraum', 'z'),
  ('D1', 'Rechnerraum, allgemein', 'z'),
  ('D2', 'Rechnerraum, fachspez.', 'z'),
  ('D3', 'Rechnerraum, fachspez. (Forschung)', 'z'),
  ('D4', 'Rechnerraum, Peripherie- \/ Geräteraum', 'z'),
  ('D5', 'Rechnerraum, Serverraum', 'z'),
  ('D7', 'Rechnerraum, Schulung', 'z'),
  ('F1', 'Projektraum, allgemein', 'z'),
  ('F2', 'Projektraum, Gruppenarbeitsraum', 'z'),
  ('F3', 'Projektraum, Übungen', 'z'),
  ('F5', 'Projektraum, Studiebüro', 'z'),
  ('F6', 'Projektraum, Sozialraum', 'z'),
  ('H1', 'Sonstige Räume, Archiv', 'z'),
  ('H2', 'Sonstige Räume, Seminar-Nebenraum', 'z'),
  ('H3', 'Sonstige Räume, sonstiges Lager', 'z'),
  ('H4', 'Sonstige Räume, Labor Lager', 'z'),
  ('H5', 'Sonstige Räume, Abstellraum', 'z'),
  ('I1', 'Büro, Büro', 'z'),
  ('I2', 'Büro, Labor-Ingenieur', 'z'),
  ('I3', 'Büro, Werkstatt', 'z'),
  ('I4', 'Büro, Besprechungen', 'z'),
  ('I5', 'Büro, Ergänzungsraum', 'z'),
  ('I6', 'Büro, Videokonferenzen', 'z'),
  ('UNIMA', 'Raum, Uni Marburg', 'z'),
  ('W1', 'Werkstatt, Feinmechanik', 'z'),
  ('W2', 'Werkstatt, Metall', 'z'),
  ('W5', 'Werkstatt, Elektronik', 'z'),
  ('W7', 'Werkstatt, Nebenraum', 'z'),
  ('W9', 'Werkstatt, Lager', 'z'),
  ('X', 'Raum, nicht bekannt', 'z');

UPDATE `#__thm_organizer_fields`
SET `gpuntisID` = REPLACE(`gpuntisID`, 'DS_', '');