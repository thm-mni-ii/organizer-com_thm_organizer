<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewCurriculum_Ajax
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class loading persistent data into the view context 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewCurriculum_Ajax extends JView
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
        $function = JRequest::getString('task');
        $this->$function();
    }

    /**
     * Retrieves a degree program's curriculum
     * 
     * @return void
     */
    private function getCurriculum()
    {
        $program = JRequest::getString('id');
        $languageTag = JRequest::getString('lang');
        if (empty($program))
        {
            echo '';
        }
        else
        {
            $model = $this->getModel();
            echo $model->getCurriculum($program, $languageTag);
        }
    }
}
