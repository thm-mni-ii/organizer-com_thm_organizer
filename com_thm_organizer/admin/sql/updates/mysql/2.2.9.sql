ALTER TABLE  `#__thm_organizer_subjects`
ADD `sws` INT( 2 ) UNSIGNED NOT NULL ,
ADD `aids_en` TEXT NOT NULL ,
ADD `aids_de` TEXT NOT NULL ,
ADD `evaluation_en` TEXT NOT NULL ,
ADD `evaluation_de` TEXT NOT NULL ,
ADD `expertise` INT( 1 ) UNSIGNED NOT NULL ,
ADD `self_competence` INT( 1 ) UNSIGNED NOT NULL ,
ADD `method_competence` INT( 1 ) UNSIGNED NOT NULL ,
ADD `social_competence` INT( 1 ) UNSIGNED NOT NULL ;
