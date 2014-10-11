<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.model');
require_once 'mapping.php';
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/referrer.php';

/**
 * Creates form data for a subject pool from information in the database.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Edit extends THM_CoreModelEdit
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
        $poolIDs = $input->get('cid',  null, 'array');
        $poolID = (empty($poolIDs))? $input->getInt('id', 0) : $poolIDs[0];
        $this->getChildren($poolID);
        THM_OrganizerHelperReferrer::setReferrer('pool');
        return $this->getItem($poolID);
    }

    /**
     * Retrieves the programs existent children and loads them into the object
     * variable
     *
     * @param   int  $poolID  the id of the program
     *
     * @return  void
     */
    private function getChildren($poolID)
    {
        $mappingModel = new THM_OrganizerModelMapping;
        $children = $mappingModel->getChildren($poolID, 'pool', false);
        THM_OrganizerHelperMapping::setChildren($this, $children);
    }
}
