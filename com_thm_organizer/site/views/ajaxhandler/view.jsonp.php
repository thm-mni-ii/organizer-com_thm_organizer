<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		thm_organizerViewAjaxHandler
 * @description thm_organizerViewAjaxHandler file from com_thm_organizer
 * @author      Dominik Bassing, <dominik.bassing@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @category  Joomla.Component.Site
 * @package   thm_organizer
 */

class THM_OrganizerViewAjaxHandler extends JViewLegacy
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
        $model = $this->getModel();

        $task = JRJFactory::getApplication()->input->getCmd('scheduletask');
        $callback = JFactory::getApplication()->input->getCmd('callback');

        $output = $model->executeTask($task);

        if (count($output) == 1)
        {
            $this->response($callback, $output["data"]);
        }
        else
        {
            $this->response($callback, $output["success"], $output["data"]);
        }
    }

    /**
     * Method to send a response to the client for JSONP
     *
     * @param   Object  $mix  The information to send can be a array, string or boolean
     * @param   Array   $arr  Additional information to send
     *
     * @return void
     */
    public function response($callback, $mix, $arr = array())
    {
        if (is_bool($mix))
        {
            if (is_array($arr))
            {
                $arr['size'] = count($arr);
                $arr['success'] = $mix;
                $arr['sid'] = session_id();
            }
        }
        elseif (is_array($mix))
        {
            $arr = $mix;
            $arr['size'] = count($arr);
            $arr['sid'] = session_id();
        }
        elseif (is_string($mix))
            $arr = $mix;
        else
        {
            // TODO
        }

        if (is_array($arr))
        {
            echo $callback . '(' . json_encode($arr) . ')';
        }
        else
        {
            echo $callback . '(' . $arr . ')';
        }
    }
}
