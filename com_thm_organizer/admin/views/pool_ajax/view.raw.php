<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewAjax_Handler
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('joomla.application.plugin.helper');
jimport('jquery.jquery');
/**
 * Class loading persistent data into the view context 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewPool_Ajax extends JView
{
    /**
     * loads model data into view context
     * 
     * @param   string  $tpl  the name of the template to be used
     * 
     * @return void
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $function = JRequest::getString('task');
        $model = $this->$function();
    }

    /**
     * Retrieves a list of pools by degree and creates select options
     */
    private function poolDegreeOptions()
    {
        $requestedPrograms = JRequest::getString('programID');
        if (empty($requestedPrograms))
        {
            echo '';
        }
        else
        {
            $model = $this->getModel();
            $parentIDs = $model->getParentIDs();
            $programPools = $model->getProgramPools();
            echo JHTML::_('select.options', $programPools, 'id', 'name', $parentIDs);
        }
    }
}
