<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        category controller
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
//jimport('joomla.application.component.controller');
//jimport('joomla.application.component.controllerform');
jimport('joomla.application.component.controlleradmin');

/**
 * Class performing access checks and model function calls for category actions
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerCategory extends JControllerLegacy
{
    /**
     * redirects to the category_edit view for the creation of new categories
     *
     * @return void
     */
    public function add()
    {
    	
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        JRequest::setVar('view', 'category_edit');
        JRequest::setVar('categoryID', '0');
        parent::display();
    }

    /**
     * redirects to the category_edit view for the editing of existing categories
     *
     * @return void
     */
    public function edit()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect("index.php?option=com_thm_organizer&view=category_edit");
    }

    /**
     * saves changes made to the category and redirects to the category_manager view
     *
     * @return void
     */
    public function apply()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('category');
        $success = $model->save();
        if ($success)
        {
            $msg = JText::_('COM_THM_ORGANIZER_CAT_SAVE_SUCCESS');
            $this->setRedirect("index.php?option=com_thm_organizer&view=category_edit&id=$success", $msg);
        }
        else
        {
            $msg = JText::_('COM_THM_ORGANIZER_CAT_SAVE_FAIL');
            $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager&id=0', $msg, 'error');
        }
    }

    /**
     * saves changes made to the category and redirects to the category_manager view
     *
     * @return void
     */
    public function save()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('category');
        $success = $model->save();
        if ($success)
        {
            $msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager', $msg, 'error');
        }
    }

    /**
     * saves changes made to the category and redirects to the category edit view
     *
     * @return void
     */
    public function save2new()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('category');
        $result = $model->save();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=category_edit', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=category_edit', $msg, 'error');
        }
    }

    /**
     * deletes the selected category and redirects to the category manager
     *
     * @return void
     */
    public function delete()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $model = $this->getModel('category');
        $result = $model->delete();
        if ($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_CAT_DELETE_SUCCESS");
            $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_CAT_DELETE_FAIL");
            $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager', $msg, 'error');
        }
    }

    /**
     * redirects to the category manager view without making any persistent changes
     *
     * @return void
     */
    public function cancel()
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager');
    }
}
