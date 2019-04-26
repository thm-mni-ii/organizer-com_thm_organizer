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
     */
    protected function allowEdit()
    {
        return Access::isAdmin();
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
            throw new \Exception(Languages::_('THM_ORGANIZER_401'), 401);
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
        $name = str_replace('_edit', '', $this->get('name'));
        $name .= $name == 'campus' ? 'es' : 's';

        return \JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Method to load the form data.
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
