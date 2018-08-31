UPDATE `#__assets` AS a1
INNER JOIN `#__assets` AS a2 ON a1.lft > a2.lft AND a1.rgt < a2.rgt
SET a1.parent_id = a2.id
WHERE a1.parent_id = 0 AND a2.name LIKE 'com_thm_organizer_department.%';