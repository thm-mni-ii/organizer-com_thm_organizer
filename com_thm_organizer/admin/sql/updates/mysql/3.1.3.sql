ALTER TABLE `#__thm_organizer_subjects`
CHANGE `preliminary_work_de` `preliminary_work_de` text NOT NULL DEFAULT '',
CHANGE `preliminary_work_en` `preliminary_work_en` text NOT NULL DEFAULT '',
CHANGE `proof_de` `proof_de` text NOT NULL DEFAULT '',
CHANGE `proof_en` `proof_en` text NOT NULL DEFAULT '',
CHANGE `method_de` `method_de` text NOT NULL DEFAULT '',
CHANGE `method_en` `method_en` text NOT NULL DEFAULT '',
ADD `recommended_prerequisites_de` text NOT NULL DEFAULT '',
ADD `recommended_prerequisites_en` text NOT NULL DEFAULT '';

