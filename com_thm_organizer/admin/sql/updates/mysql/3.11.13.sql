ALTER TABLE `#__thm_organizer_subjects`
  ADD COLUMN `is_prep_course`   INT(1)  UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN `max_participants` INT(11) UNSIGNED          DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_user_data` (
  `id`           INT(11)          NOT NULL AUTO_INCREMENT,
  `userID`       INT(11)          NOT NULL,
  `forename`     VARCHAR(60)      NOT NULL DEFAULT '',
  `surname`      VARCHAR(60)      NOT NULL DEFAULT '',
  `city`         VARCHAR(60)      NOT NULL DEFAULT '',
  `address`      VARCHAR(60)      NOT NULL DEFAULT '',
  `zip_code`     INT(11)          NOT NULL DEFAULT 0,
  `programID`    INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_user_data`
  ADD CONSTRAINT `user_data_userid_fk` FOREIGN KEY (`userID`) REFERENCES `#__users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `user_data_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

INSERT INTO `#__thm_organizer_subjects` (`departmentID`, `is_prep_course`, `max_participants`, `name_de`, `short_name_de`, `abbreviation_de`, `name_en`, `short_name_en`, `abbreviation_en`, `description_de`, `objective_de`) VALUES
  (12, 1, 200, 'Brückenkurs Programmieren', 'BRK Programmieren', 'BRKPR','Preparatory Course Programming', 'PC Programming', 'PCPR',
    '<p><span>Der Kurs bietet eine Einführung in das Programmieren in&nbsp;Java und richtet sich an Studierende, die über keine oder&nbsp;sehr geringe Erfahrungen im Programmieren verfügen&nbsp;und sich in einem Studiengang eingeschrieben haben, in dem Programmierkenntnisse von Bedeutung sind.&nbsp;Wir führen Sie in die Bedienung eines Rechners ein und&nbsp;zeigen, wie einfache Programme erstellt und zur Ausführung&nbsp;gebracht werden. Schwerpunkte sind eine erste&nbsp;Einführung in das für das Programmieren wichtige algorithmische Denken und die Vermittlung praktischer Fertigkeiten&nbsp;im Umgang mit einem Computer. Im Rahmen&nbsp;des Kurses kann das Erlernte an Rechnern der Hochschule&nbsp;ausprobiert werden.</span></p>',
    '<h4>Testaufgaben aus der Programmierung:</h4> <ol> <li>Sind Sie sicher, dass dieses Programm für alle eingegebenen Temperaturwerte die richtige Jahreszeiten ausgibt?<br/>EINGABE Temperatur<br/>&nbsp; &nbsp; &nbsp; &nbsp; WENN Temperatur &gt; 20 DANN<br/>&nbsp; &nbsp; &nbsp; &nbsp; AUSGABE „Sommer“<br/>SONST<br/>&nbsp; &nbsp; &nbsp; &nbsp; WENN Temperatur &gt; 5 DANN<br/>&nbsp; &nbsp; &nbsp; &nbsp; AUSGABE „Herbst/Frühling“<br/>SONST<br/>&nbsp; &nbsp; &nbsp; &nbsp; AUSGABE „<span>Herbst/Frühling</span>“<br/>Wenn Sie sich nicht sicher sind, sollten Sie unseren Brückenkurs in Erwägung ziehen!</li> <li>Sie wissen sicher, wie die Zahl PI berechnet wird:<br/>PI/4 = 1 - 1/3 + 1/5 – 1/7 + 1/9 – 1/11 + 1/13 …<br/>Wenn Sie die Berechnung der Zahl PI nach diesem Verfahren programmieren können, brauchen Sie diesen Brückenkurs nicht.</li> <li>Nehmen wir an, a und b seien Ganzzahlen, c eine Fließkommazahl: a = 10 | b = 4 | c = a/b<br/>Wenn Sie der Meinung c = 2,5 sind, sollten Sie unseren Brückenkurs besuchen.</li> </ol>'),
  (12, 1, 200, 'Brückenkurs Chemie', 'BRK Chemie', 'BRKCH','Preparatory Course Chemistry', 'PC Chemistry', 'PCCH',
    '<p><span>Grundlegende Themengebiete der allgemeinen, anorganischen&nbsp;und organischen Chemie für technisch-naturwissenschaftliche Studiengänge werden bearbeitet und anhand&nbsp;von Übungsaufgaben gefestigt.</span></p>',
    '<h4>Testaufgaben aus der Chemie</h4> <p>(Hilfsmittel: Periodensystem der Elemente)</p> <ol> <li>1a. Wie groß ist die Stoffmengenkonzentration in Mol pro Liter, wenn 49 g H<span>3</span>PO<span>4</span>&nbsp;mit Wasser zu 1 L Lösung aufgefüllt werden.<br/>1b. Wie viel Gramm Natriumfluorid enthalten 250 ml einer Lösung der Konzentration 0,5 mol/L?</li> <li>Wie viele Elektronen besitzt das Natriumion im Natriumchlorid?</li> <li>Geben Sie die Formeln folgender Stoffe an:<br/>a. Schwefelsäure b. Kaliumbromid<br/>c. Calciumcarbonat d. Magnesiumchlorid<br/>e. Aluminiumsulfat</li> <li>&nbsp;Was entsteht beim Zusammengießen von Salzsäure und Natronlauge?</li> <li>5a. Welcher Stoff bildet sich beim Verbrennen von Schwefel an der Luft?<br/>5b. Wie ändert sich dabei die Oxidationszahl des Schwefels?</li> </ol> <h4>Lösungen</h4> <ol> <li>a. 0,5 mol/L<br />b. 5,25 g</li> <li>10</li> <li>a. H<span>2</span>SO<span />b. KBr<br/>c. CaCO<span>3</span><br/>d. MgCl<span>2</span><br/>e. Al<span>2</span>(SO<span>4</span>)<span >3</span></li> <li>Natriumchlorid und Wasser</li> <li>a. Schwefeldioxid<br />b. von 0 auf +4</li> </ol>'),
  (12, 1, 200, 'Brückenkurs Physik', 'BRK Physik', 'BRKPH','Preparatory Course Physics', 'PC Physics', 'PCPH',
    '<p><span>Das für alle technisch-naturwissenschaftlichen Studiengänge erforderliche physikalische Grundwissen wird wiederholt und gegebenenfalls neu erarbeitet. Dabei werden exemplarisch physikalische Lösungsstrategien entwickelt und mit Hilfe von Übungsaufgaben konkretisiert, überprüft und vertieft.</span></p>',
    '<h4>Testaufgaben aus der Physik</h4> <ol><strong>Falsch oder richtig?</strong><br/><br/>a. 1,5 m<span>2</span>&nbsp;+ 25 dm<span>2</span>&nbsp;+ 1000 cm<span>2</span>&nbsp;+ 150000 mm<span>2</span>&nbsp;= 2000 dm<span>2</span><br/>b. Ein Messzylinder zur Bestimmung der Regenmenge enthält 25 mm Niederschlag. Dies entspricht der Regenmenge von 25 Liter/m<span>2</span>.<br/>c. Eine Masse von m = 50 kg verursacht am Standort Gießen (g = 9,81 m/s<span>2</span>) die Gewichtskraft F<span>G</span>&nbsp;= 250 N.<br/>d. Die im SI-Maßsystem festgelegten sieben Basiseinheiten sind: kg, m, s, N, A, K, Ω<br/>e. Die Winkelgeschwindigkeit einer mit 3000 min<span>-1</span>&nbsp;rotierenden Scheibe beträgt ω = 314 s<span >-1</span>.<br/>f. Ein PKW wird in 5 s von 0 auf 140 km/h beschleunigt. Die mittlere Beschleunigung beträgt dann a = 28 m/s<span>2</span></li> <li><strong>Lösen Sie folgende Aufgaben:</strong><br /><br/>(g = 9,81 m/s<span >2</span>&nbsp;, Reibungskräfte vernachlässigen)<br/>a. Mit welcher Geschwindigkeit trifft ein Stein auf dem Erdboden auf, der aus einer Höhe h = 50 m fallen gelassen wird?<br/>b. Zur Bestimmung der Brunnentiefe lässt ein Beobachter einen Stein in den Brunnen fallen und sieht ihn nach t = 1,43 s auf die Wasseroberfläche aufschlagen. Wie tief liegt die Wasseroberfläche?<br/>c. Um wie viel Uhr nach Mitternacht bilden der kleine und der große Uhrzeiger erstmalig genau einen rechten Winkel?<br/>d. Die Erde, idealisiert als Kugel betrachtet, hat einen Radius R = 6370 km. Sie dreht sich in 24 h einmal um ihre Achse. Wie groß ist dann die Umfangsgeschwindigkeit in Gießen (50,6° nördlicher Breite)?</li> </ol> <h4>Lösungen</h4> <ol> <li>a. 200 dm<span>2</span><br/>b. r<br/>c. F<span>G</span>&nbsp;= 490,5 N<br/>d. kg, m, s, A, K, mol, cd<br/>e. r<br/>f. 7,8 m/s<span>2</span></li> <li>a. 31,3 m/s<br/>b. 10 m<br/>c. 00:16:21,82 h<br/>d. 294 m/s</li> </ol>'),
  (12, 1, 200, 'Brückenkurs Englisch', 'BRK Englisch', 'BRKEN','Preparatory Course English', 'PC English', 'PCEN',
    '',
    ''),
  (12, 1, 200, 'Brückenkurs Mathematik', 'BRK Mathematik', 'BRKM','Preparatory Course Mathematics', 'PC Mathematics', 'PCM',
    '<p>Es wird das mathematische Grundlagenwissen wiederholt,&nbsp;das von Studienbeginn an in allen technischen, mathematisch-naturwissenschaftlichen und wirtschaftswissenschaftlichen&nbsp;Studiengängen benötigt wird.</p> <p>Themen: Zahlen, Größen - Klammern, Brüche - Proportionalität,&nbsp;Prozentrechnung - Gleichungen, Potenzen, Wurzeln - Logarithmen - Trigonometrie.</p> <p>Beispiele werden besprochen&nbsp;und vertiefende Übungsaufgaben durchgearbeitet.</p>',
    ''),
  (12, 1, 200, 'Brückenkurs Buchführung', 'BRK Buchführung', 'BRKBF','Preparatory Course Accounting', 'PC Accounting', 'PCA',
    '',
    '');

INSERT INTO `#__thm_organizer_subject_mappings` (`subjectID`, `plan_subjectID`) VALUES
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKPR'), 5),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKPR'), 98),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKPR'), 734),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKCH'), 2),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKCH'), 95),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKCH'), 731),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKPH'), 4),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKPH'), 97),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKPH'), 733),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKEN'), 377),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKEN'), 1862),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKEN'), 1877),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKM'), 3),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKM'), 96),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKM'), 375),
  ((SELECT `id` FROM `#__thm_organizer_subjects` WHERE abbreviation_de = 'BRKBF'), 1276);