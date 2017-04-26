CREATE TABLE IF NOT EXISTS `#__thm_organizer_plan_pool_publishing` (
  `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `planPoolID`       INT(11) UNSIGNED NOT NULL,
  `planningPeriodID` INT(11) UNSIGNED NOT NULL,
  `published`        TINYINT(1)       NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry` (`planPoolID`, `planningPeriodID`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

ALTER TABLE `#__thm_organizer_plan_pool_publishing`
  ADD CONSTRAINT `plan_pool_publishing_planpoolid_fk` FOREIGN KEY (`planPoolID`) REFERENCES `#__thm_organizer_plan_pools` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE,
  ADD CONSTRAINT `plan_pool_publishing_planningperiodid_fk` FOREIGN KEY (`planningPeriodID`)
REFERENCES `#__thm_organizer_planning_periods` (`id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;