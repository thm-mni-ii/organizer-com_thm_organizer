<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        create/edit appointment/event view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');

/**
 * Loads model data into context and sets variables used for html output
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class Thm_OrganizerViewEvent_Edit extends JView
{
    /**
     * loads model data into context and sets variables used for html output
     *
     * @param   string  $tpl  the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.framework', true);
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_edit.js'));

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $model = $this->getModel();
        $this->event = $model->event;
        $this->rooms = $model->rooms;
        $this->teachers = $model->teachers;
        $this->groups = $model->groups;
        $this->categories = $model->categories;

        if (!count($this->categories))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        $this->listLink = $model->listLink;
        $this->eventLink = $model->eventLink;

        $blockchecked = $dailychecked = '';
        switch ($this->event['recurrence_type'])
        {
            case 0:
                $dailychecked = 'checked';
                break;
            case 1:
                $blockchecked = 'checked';
                break;
        }
        $this->blockchecked = $blockchecked;
        $this->dailychecked = $dailychecked;

        $title = ($this->event['id'] == 0)?
                JText::_('COM_THM_ORGANIZER_EE_TITLE_NEW') : JText::_('COM_THM_ORGANIZER_EE_TITLE_EDIT');
        $document->setTitle($title);

        $this->createHTMLElements();
        parent::display($tpl);
    }

    /**
     * Creates HTML elements from saved data
     *
     * @return void
     */
    private function createHTMLElements()
    {
        $this->createResourceElement('rooms', JText::_('COM_THM_ORGANIZER_NO_ROOMS'));
        $this->createResourceElement('teachers', JText::_('COM_THM_ORGANIZER_NO_TEACHERS'));
        $this->createResourceElement('groups', JText::_('COM_THM_ORGANIZER_NO_GROUPS'));
        $this->processCategories();
        $this->createActionLink('save');
        $this->createActionLink('reset');
        $this->createActionLink('cancel');
    }

    /**
     * creates the selection boxes for resources
     *
     * @param   string  $name       the name of the resource
     *
     * @param   string  $emptyText  the text for the selection of no resources
     *
     * @return void
     */
    private function createResourceElement($name, $emptyText)
    {
        $dummyResources = array();
        $dummyResources[] = array('id' => '-1', 'name' => $emptyText);
        $resources = array_merge($dummyResources, $this->$name);
        $attributes = array('id' => $name,
                            'class' => 'inputbox',
                            'size' => '4',
                            'multiple' => 'multiple'
            );
        $selectname = $name . 'select';
        if (isset($this->event[$name]))
        {
            $selectbox = JHTML::_('select.genericlist',
                                          $resources,
                                          $name . '[]',
                                          $attributes,
                                          'id',
                                          'name',
                                          $this->event[$name]
                                         );
        }
        else
        {
            $selectbox = JHTML::_('select.genericlist',
                                          $resources,
                                          $name . '[]',
                                          $attributes,
                                          'id',
                                          'name'
                                         );
        }
        $this->$selectname = $selectbox;
    }

    /**
     * processes the categories adding a dummy to eliminate having a default
     * category, and creates the javascript output for each category
     *
     * @return void
     */
    private function processCategories()
    {
        $attributes = array();
        $attributes['id'] = 'category';
        $attributes['class'] = 'inputbox validate-category';
        $attributes['onChange'] = 'changeCategoryInformation()';
        $attributes['required'] = 'true';
        $this->categoryselect = JHTML::_('select.genericlist',
                                            $this->categories,
                                            'category',
                                            $attributes,
                                            'id',
                                            'title',
                                            $this->event['categoryID']
                                        );
        foreach ($this->categories as $k => $category)
        {
            $javascript = 'categories[' . $category['id'] . '] = new Array( "';
            $javascript .= $category['description'] . '", "';
            $javascript .= $category['display'] . '",  "';
            $javascript .= $category['contentCat'] . '", "';
            $javascript .= $category['contentCatDesc'] . '", "';
            $javascript .= $category['access'];
            $javascript .= '" );';
            $this->categories[$k]['javascript'] = $javascript;
        }
    }

    /**
     * creates links similar to the joomla backend action buttons
     *
     * @param   string  $action  the name of the action
     *
     * @return  void
     */
    private function createActionLink($action)
    {
        $linkname = $action . 'link';
        $image = JHTML::_('image',
                              "components/com_thm_organizer/assets/images/$action.png",
                              JText::_(ucfirst($action)),
                              array( 'class' => 'thm_organizer_ee_image_button',
                                     'onclick' => "return submitbutton('" . $action . "event');")
                             );
        $this->$linkname = "<a href='#' onclick='Joomla.submitbutton('$action')'>" . $image . "</a>";
    }
}
