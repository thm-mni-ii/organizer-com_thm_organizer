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

/**
 * Class loads item form data to edit an entry.
 */
class THM_OrganizerModelEdit extends \Joomla\CMS\MVC\Model\AdminModel
{
    protected $deptResource;

    public $item = null;

    /**
     * Provides a strict access check which can be overwritten by extending classes.
     *
     * @return bool  true if the user can access the view, otherwise false
     * @throws Exception
     */
    protected function allowEdit()
    {
        return THM_OrganizerHelperAccess::isAdmin();
    }

    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return mixed  JForm object on success, False on error.
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
     * @throws Exception
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
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
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
     * @return JTable  A JTable object
     */
    public function getTable($name = '')
    {
        /**
         * Joomla makes the mistake of handling front end and backend differently for include paths. Here we add the
         * possible frontend and media locations for logical consistency.
         */
        JTable::addIncludePath(JPATH_ROOT . "/media/com_thm_organizer/tables");
        JTable::addIncludePath(JPATH_ROOT . "/components/com_thm_organizer/tables");

        $name = str_replace('_edit', '', $this->get('name'));
        $name .= $name == 'campus' ? 'es' : 's';

        return JTable::getInstance($name, 'THM_OrganizerTable');
    }

    /**
     * Method to load the form data
     *
     * @return object
     * @throws Exception
     */
    protected function loadFormData()
    {
        $input       = JFactory::getApplication()->input;
        $resourceIDs = $input->get('cid', [], 'array');
        $resourceID  = empty($resourceIDs) ? $input->getInt('id', 0) : $resourceIDs[0];

        return $this->getItem($resourceID);
    }
}
