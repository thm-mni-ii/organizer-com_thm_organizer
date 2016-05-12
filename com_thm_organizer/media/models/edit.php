<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelEdit
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class loads item form data to edit an entry.
 *
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerModelEdit extends JModelAdmin
{
    /**
     * Method to get the form
     *
     * @param   Array    $data      Data         (default: Array)
     * @param   Boolean  $loadData  Load data  (default: true)
     *
     * @return  mixed  JForm object on success, False on error.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        $name = $this->get('name');
        $form = $this->loadForm("com_thm_organizer.$name", $name, array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }

        return $form;
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @throws  exception  if the user is not authorized to access the view
     */
    public function getItem($pk = null)
    {
        $path = JPATH_ROOT . "/media/com_thm_organizer/helpers/componentHelper.php";
        require_once $path;

        THM_OrganizerHelperComponent::addActions($this);
        $item = parent::getItem($pk);
        $allowEdit = THM_OrganizerHelperComponent::allowEdit($this, $item->id);
        if ($allowEdit)
        {
            return $item;
        }
        throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function getTable($name = '', $prefix = 'Table', $options = array())
    {
        /**
         * Joomla makes the mistake of handling front end and backend differently for include paths. Here we add the
         * possible frontend and media locations for logical consistency.
         */
        $component = $this->get('option');
        JTable::addIncludePath(JPATH_ROOT . "/media/$component/tables");
        JTable::addIncludePath(JPATH_ROOT . "/components/$component/tables");

        $type = str_replace('_edit', '', $this->get('name')) . 's';
        $prefix = str_replace('com_', '', $component) . 'Table';
        return JTable::getInstance($type, $prefix, $options);
    }

    /**
     * Method to load the form data
     *
     * @return  Object
     */
    protected function loadFormData()
    {
        $input = JFactory::getApplication()->input;
        $name = $this->get('name');
        $resource = str_replace('_edit', '', $name);
        $task = $input->getCmd('task', "$resource.add");
        $resourceID = $input->getInt('id', 0);

        // Edit can only be explicitly called from the list view or implicitly with an id over a URL
        $edit = (($task == "$resource.edit")  OR $resourceID > 0);
        if ($edit)
        {
            if (!empty($resourceID))
            {
                return $this->getItem($resourceID);
            }

            $resourceIDs = $input->get('cid',  null, 'array');
            return $this->getItem($resourceIDs[0]);
        }
        return $this->getItem(0);
    }
}
