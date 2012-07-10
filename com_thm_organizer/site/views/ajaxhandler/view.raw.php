<?php
/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   thm_organizerViewAjaxHandler
 *@description thm_organizerViewAjaxHandler file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @package  Joomla.site
 * @since    1.5
 */

class THM_OrganizerViewAjaxHandler extends JView
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

		$task = JRequest::getCmd('scheduletask');

		$output = $model->executeTask($task);

		if (count($output) == 1)
		{
			$this->response($output["data"]);
		}
		else
		{
			$this->response($output["success"], $output["data"]);
		}
	}

	/**
	 * Method to send a response to the client
	 *
	 * @param   Object  $mix  The information to send can be a array, string or boolean
	 * @param   Array   $arr  Additional information to send
	 *
	 * @return void
	 */
	public function response($mix, $arr = array())
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
			echo json_encode($arr);
		}
		else
		{
			echo $arr;
		}
	}
}
