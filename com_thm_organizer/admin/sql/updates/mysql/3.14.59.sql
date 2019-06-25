UPDATE `#__menu`
SET `params` = replace(`params`, 'programIDs', 'categoryIDs'),
    `params` = replace(`params`, 'poolIDs', 'groupIDs'),
    `params` = replace(`params`, 'showPrograms', 'showCategories')
WHERE `link` = 'index.php?option=com_thm_organizer&view=schedule_grid';