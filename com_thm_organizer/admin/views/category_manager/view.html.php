<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view category manager
 * @description lists saved event categories and basic information about them
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewcategory_manager extends JView
{
	
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");


        $model = $this->getModel();
        $this->categories = $model->categories;
        if(count($this->categories))$this->setIcons();
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * addToolBar
     *
     * generates buttons for user interaction
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_CAT_TITLE');        
        JToolBarHelper::title( $title, 'mni' );        
        JToolBarHelper::addNew ('category.add');
        JToolBarHelper::editList('category.edit');
        JToolBarHelper::deleteList( JText::_('COM_THM_ORGANIZER_CAT_DELETE_CONFIRM'), 'category.delete');
        if (thm_organizerHelper::isAdmin("category_manager"))
        {
        	JToolBarHelper::divider();
        	JToolBarHelper::preferences('com_thm_organizer');
        }
    }

    /**
     * setIcons
     *
     * sets images used for display of properties
     */
    private function setIcons()
    {
        $this->yes = JHTML::_('image', 'administrator/templates/bluestork/images/admin/tick.png',
                        JText::_( 'COM_THM_ORGANIZER_ALLOWED' ), array( 'class' => 'thm_organizer_sm_icon'));
        $this->no = JHTML::_('image', 'administrator/templates/bluestork/images/admin/publish_x.png',
                       JText::_( 'COM_THM_ORGANIZER_DENIED' ), array( 'class' => 'thm_organizer_sm_icon'));
    }
	
	
}