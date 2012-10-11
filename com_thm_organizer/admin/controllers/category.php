<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        category controller
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';
/**
 * Class performing access checks and model function calls for category actions 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class thm_organizersControllerCategory extends JController
{
    /**
     * redirects to the category_edit view for the creation of new categories
     * 
     * @return void
     */
    public function add()
    {
        if (!thm_organizerHelper::isAdmin('category'))
        {
            thm_organizerHelper::noAccess();
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
        if (!thm_organizerHelper::isAdmin('category'))
        {
            thm_organizerHelper::noAccess();
        }
        JRequest::setVar('view', 'category_edit');
        parent::display();
    }

    /**
     * saves changes made to the category and redirects to the category_manager view
     * 
     * @return void
     */
    public function save()
    {
        if (!thm_organizerHelper::isAdmin('category'))
        {
            thm_organizerHelper::noAccess();
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
        if (!thm_organizerHelper::isAdmin('category'))
        {
            thm_organizerHelper::noAccess();
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
        if (!thm_organizerHelper::isAdmin('category'))
        {
            thm_organizerHelper::noAccess();
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
        if (!thm_organizerHelper::isAdmin('category'))
        {
            thm_organizerHelper::noAccess();
        }
        $this->setRedirect('index.php?option=com_thm_organizer&view=category_manager');
    }
}
