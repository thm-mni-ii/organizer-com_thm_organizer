<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        description manager view
 * @description provides a list of descriptions
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @author      Markus Bader markusDOTbaderATmniDOTthmDOTde
 * @author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewdescription_manager extends JView
{
    protected $pagination;
    protected $state;
    protected $subsubbar;

    function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.multiselect');

        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->descriptions = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        
        // for sorting
        $state = $this->state;
        $this->orderby   = $state->get('filter_order');
        $this->direction = $state->get('filter_order_Dir');
        
        $this->addToolBar();
        if(count($this->descriptions))$this->addLinks();

        parent::display($tpl);
    }

    /**
     * addLinks
     *
     * creates links to the edit view for the particular schedule
     */
    private function addLinks()
    {
        $editURL = 'index.php?option=com_thm_organizer&view=description_edit&descriptionID=';
        foreach($this->descriptions as $key => $description)
            $this->descriptions[$key]->url = $editURL.$description->id;
    }

    /**
     * addToolBar
     *
     * creates a joomla administrative tool bar
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_DSM_TITLE');
        JToolBarHelper::title($title);
        JToolBarHelper::addNew('description.add');
        JToolBarHelper::editList('description.edit');
        JToolBarHelper::deleteList(JText::_( 'COM_THM_ORGANIZER_DSM_DELETE_CONFIRM'),'description.delete');

    }
}