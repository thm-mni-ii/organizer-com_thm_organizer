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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/subjects.php';

use OrganizerHelper;

/**
 * Class loads a form for editing data.
 */
class THM_OrganizerModelSubject_Edit extends \Joomla\CMS\MVC\Model\AdminModel
{
    protected $deptResource;

    public $item = null;

    /**
     * Checks for user authorization to access the view
     *
     * @param int $subjectID the id of the subject for which authorization is to be checked
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit($subjectID = null)
    {
        return THM_OrganizerHelperSubjects::allowEdit($subjectID);
    }

    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return mixed  \JForm object on success, False on error.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = true)
    {
        $name = $this->get('name');
        $form = $this->loadForm("com_thm_organizer.$name", $name, ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {

            return false;
        }

        return $form;
    }

    /**
     * Method to get a single record.
     *
     * @param integer $pk The id of the primary key.
     *
     * @return mixed    Object on success, false on failure.
     * @throws Exception => unauthorized access
     */
    public function getItem($pk = null)
    {
        // Prevents duplicate execution from getForm and getItem
        if (isset($this->item->id) and ($this->item->id === $pk or $pk === null)) {
            return $this->item;
        }

        $this->item = parent::getItem($pk);
        $allowEdit  = $this->allowEdit();

        if (!$allowEdit) {
            throw new \Exception(\JText::_('THM_ORGANIZER_401'), 401);
        }

        return $this->item;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return \JTable  A \JTable object
     */
    public function getTable($name = '', $prefix = 'THM_OrganizerTable', $options = [])
    {
        \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');

        return \JTable::getInstance('subjects', $prefix, $options);
    }

    /**
     * Method to load the form data
     *
     * @return object
     * @throws Exception => unauthorized access
     */
    protected function loadFormData()
    {
        $input       = OrganizerHelper::getInput();
        $resourceIDs = $input->get('cid', [], 'array');
        $resourceID  = empty($resourceIDs) ? $input->getInt('id', 0) : $resourceIDs[0];

        return $this->getItem($resourceID);
    }
}
