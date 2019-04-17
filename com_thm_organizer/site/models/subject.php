<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';

/**
 * Class which manages stored subject data.
 */
class THM_OrganizerModelSubject extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return \JTable  A \JTable object
     */
    public function getTable($name = 'subjects', $prefix = 'THM_OrganizerTable', $options = [])
    {
        \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');

        return \JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Saves course data to database
     *
     * @return bool true on success, false on error
     * @throws Exception => unauthorized access
     */
    public function save()
    {
        $data     = \OrganizerHelper::getInput()->get('jform', [], 'array');
        $lessonID = \OrganizerHelper::getInput()->getInt('lessonID', 0);

        if (!THM_OrganizerHelperSubjects::allowEdit($data['id'])) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $table   = $this->getTable();
        $success = $table->save($data);

        if (!empty($success) and !empty($lessonID)) {
            THM_OrganizerHelperCourses::refreshWaitList($lessonID);
        }

        return $success;
    }
}
