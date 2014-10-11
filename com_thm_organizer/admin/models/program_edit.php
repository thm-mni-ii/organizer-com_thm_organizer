<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMajor
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.model');
require_once 'mapping.php';
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

/**
 * Class THM_OrganizerModelMajor for component com_thm_organizer
 *
 * Class provides methods to deal with major
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelProgram_Edit extends THM_CoreModelEdit
{
    public $children = null;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Method to load the form data
     *
     * @return  Object
     */
    protected function loadFormData()
    {
        $input = JFactory::getApplication()->input;
        $programIDs = $input->get('cid',  null, 'array');
        $programID = (empty($poolIDs))? $input->getInt('id', 0) : $programIDs[0];
        $this->getChildren($programID);
        return $this->getItem($programID);
    }

    /**
     * Retrieves the programs existent children and loads them into the object
     * variable
     *
     * @param   int  $programID  the id of the program
     *
     * @return  void
     */
    private function getChildren($programID)
    {
        $mappingModel = new THM_OrganizerModelMapping;
        $children = $mappingModel->getChildren($programID, 'program', false);
        THM_OrganizerHelperMapping::setChildren($this, $children);
    }

}
