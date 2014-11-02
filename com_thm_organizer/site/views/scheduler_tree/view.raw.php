<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewScheduler_Tree
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('extjs4.extjs4');
jimport('joomla.application.component.view');

/**
 * HTML View class for the THM Organizer Component
 *
 * @category  Joomla.Component.Site
 * @package   thm_organizer
 */

class THM_OrganizerViewScheduler_Tree extends JViewLegacy
{
    /**
     * Method to get extra
     *
     * @param   String  $tpl  template
     *
     * @return void
     *
     * @see JView::display()
     */
    public function display($tpl = null)
    {
        echo $this->model->load();

        parent::display($tpl);
    }
}
