UPDATE `#__menu`
SET `link` = 'index.php?option=com_thm_organizer&view=course_manager',
    `params` = replace(`params`, '}', ',"onlyPrepCourses":"1"}')
WHERE `link` = 'index.php?option=com_thm_organizer&view=course_list';
