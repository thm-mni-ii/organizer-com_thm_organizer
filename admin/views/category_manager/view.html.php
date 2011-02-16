<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the  Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage  Giessen Scheduler
 */
class  thm_organizersViewcategory_manager extends JView
{   
    protected $items;
    protected $pagination;

    function display($tpl = null)
    {
        $this->items		= $this->get('Items');
        $this->pagination	= $this->get('Pagination');
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar()
    {


//        JToolBarHelper::title( JText::_( 'THM - Organizer: Category Manager' ), 'generic.png' );
//        JToolBarHelper::addNewX();
//        JToolBarHelper::editListX();
//        JToolBarHelper::deleteList();
//
//        //Create Submenu
//        $model = $this->getModel();
//        foreach($model->data->links as $link)
//        {
//            JSubMenuHelper::addEntry( JText::_( $link['name'] ), 'index.php?'.$link['link']);
//        }
		// Initialise variables.
		$categoryId	= $this->state->get('filter.category_id');
		$component	= $this->state->get('filter.component');
		$section	= $this->state->get('filter.section');
		$canDo		= null;


 		// Load the category helper.
		require_once JPATH_COMPONENT.'/helpers/categories.php';

		// Get the results for each action.
		$canDo = CategoriesHelper::getActions($component, $categoryId);

		// If a component categories title string is present, let's use it.
		if ($lang->hasKey($component_title_key = strtoupper($component.($section?"_$section":'')).'_CATEGORIES_TITLE')) {
			$title = JText::_($component_title_key);
		}
		// Else if the component section string exits, let's use it
		elseif ($lang->hasKey($component_section_key = strtoupper($component.($section?"_$section":'')))) {
			$title = JText::sprintf( 'COM_CATEGORIES_CATEGORIES_TITLE', $this->escape(JText::_($component_section_key)));
		}
		// Else use the base title
		else {
			$title = JText::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
		}

		// Load specific css component
		JHtml::_('stylesheet',$component.'/administrator/categories.css', array(), true);

		// Prepare the toolbar.
		JToolBarHelper::title($title, 'categories '.substr($component,4).($section?"-$section":'').'-categories');

		if ($canDo->get('core.create')) {
			 JToolBarHelper::custom('category.add', 'new.png', 'new_f2.png', 'JTOOLBAR_NEW', false);
		}

		if ($canDo->get('core.edit' ) || $canDo->get('core.edit.own')) {
			JToolBarHelper::custom('category.edit', 'edit.png', 'edit_f2.png', 'JTOOLBAR_EDIT', true);
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::custom('categories.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('categories.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('categories.archive','JTOOLBAR_ARCHIVE');
		}

		if (JFactory::getUser()->authorise('core.admin')) {
			JToolBarHelper::custom('categories.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete', $component)) {
			JToolBarHelper::deleteList('', 'categories.delete','JTOOLBAR_EMPTY_TRASH');
		}
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('categories.trash','JTOOLBAR_TRASH');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
			JToolBarHelper::preferences($component);
			JToolBarHelper::divider();
		}

		// Compute the ref_key if it does exist in the component
		if (!$lang->hasKey($ref_key = strtoupper($component.($section?"_$section":'')).'_CATEGORIES_HELP_KEY')) {
			$ref_key = 'JHELP_COMPONENTS_'.strtoupper(substr($component,4).($section?"_$section":'')).'_CATEGORIES';
		}

		// Get help for the categories view for the component by
		// -remotely searching in a language defined dedicated URL: *component*_HELP_URL
		// -locally  searching in a component help file if helpURL param exists in the component and is set to ''
		// -remotely searching in a component URL if helpURL param exists in the component and is NOT set to ''
		if ($lang->hasKey($lang_help_url = strtoupper($component).'_HELP_URL')) {
			$debug = $lang->setDebug(false);
			$url = JText::_($lang_help_url);
			$lang->setDebug($debug);
		}
		else {
			$url = null;
		}
		JToolBarHelper::help($ref_key, JComponentHelper::getParams( $component )->exists('helpURL'), $url);
	}
}
	