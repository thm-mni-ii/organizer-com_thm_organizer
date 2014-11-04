ALTER TABLE `#__thm_organizer_subjects`
CHANGE `expertise` `expertise` INT(1) UNSIGNED NULL DEFAULT NULL,
CHANGE `self_competence` `self_competence` INT(1) UNSIGNED NULL DEFAULT NULL,
CHANGE `method_competence` `method_competence` INT(1) UNSIGNED NULL DEFAULT NULL,
CHANGE `social_competence` `social_competence` INT(1) UNSIGNED NULL DEFAULT NULL;