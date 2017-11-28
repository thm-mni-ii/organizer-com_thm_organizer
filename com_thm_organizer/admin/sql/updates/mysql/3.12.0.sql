DROP TABLE IF EXISTS `#__thm_organizer_user_data`;

CREATE TABLE IF NOT EXISTS `#__thm_organizer_participants` (
  `id`        INT(11)          NOT NULL,
  `forename`  VARCHAR(255)     NOT NULL DEFAULT '',
  `surname`   VARCHAR(255)     NOT NULL DEFAULT '',
  `city`      VARCHAR(60)      NOT NULL DEFAULT '',
  `address`   VARCHAR(60)      NOT NULL DEFAULT '',
  `zip_code`  INT(11)          NOT NULL DEFAULT 0,
  `programID` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_participants`
  ADD CONSTRAINT `participants_userid_fk` FOREIGN KEY (`id`) REFERENCES `#__users` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `participants_programid_fk` FOREIGN KEY (`programID`) REFERENCES `#__thm_organizer_programs` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;