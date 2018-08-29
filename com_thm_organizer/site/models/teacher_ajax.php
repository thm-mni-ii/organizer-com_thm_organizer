<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Class which retrieves dynamic teacher information.
 */
class THM_OrganizerModelTeacher_Ajax extends JModelLegacy
{
    /**
     * Gets the pool options as a string
     *
     * @param bool $short whether or not the options should use abbreviated names
     *
     * @return string the concatenated plan pool options
     */
    public function getPlanOptions($short = false)
    {
        $planOptions = THM_OrganizerHelperTeachers::getPlanTeachers($short);

        return json_encode($planOptions);
    }

    /**
     * Retrieves teacher entries from the database
     *
     * @return string  the teachers who hold courses for the selected program and pool
     * @throws Exception
     */
    public function teachersByProgramOrPool()
    {
        $input     = JFactory::getApplication()->input;
        $programID = $input->getString('programID');
        $poolID    = $input->getString('poolID');

        if (!empty($poolID) and $poolID != '-1' and $poolID != 'null') {
            $resourceType = 'pool';
            $resourceID   = $poolID;
        } else {
            $resourceType = 'program';
            $resourceID   = $programID;
        }

        $boundarySet = THM_OrganizerHelperMapping::getBoundaries($resourceType, $resourceID);

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT t.id, t.forename, t.surname")->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.teacherID = t.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = st.subjectID');
        if (!empty($boundarySet)) {
            $where   = '';
            $initial = true;
            foreach ($boundarySet as $boundaries) {
                $where   .= $initial ?
                    "((m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')"
                    : " OR (m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')";
                $initial = false;
            }

            $query->where($where . ')');
        }

        $query->order('t.surname');
        $dbo->setQuery($query);

        try {
            $teachers = $dbo->loadObjectList();
        } catch (RuntimeException $exc) {
            JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

            return '[]';
        }

        if (empty($teachers)) {
            return '';
        }

        foreach ($teachers as $key => $value) {
            $teachers[$key]->name = empty($value->forename) ?
                $value->surname : $value->surname . ', ' . $value->forename;
        }

        return json_encode($teachers);
    }
}
