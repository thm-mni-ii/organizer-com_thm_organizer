<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerMajor
 * @description THM_OrganizerControllerMajor component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controllerform');

JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_thm_organizer' . DS . 'tables');

/**
 * Class THM_OrganizerControllerMajor for component com_thm_organizer
 *
 * Class provides methods perform actions for major
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerMajor extends JControllerForm
{
	/**
	 * Method to perform save
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return  void
	 */
	public function save($key = null, $urlVar = null)
	{
		$retVal = parent::save($key, $urlVar);

		if ($retVal)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=majors', false));
		}
	}

	/**
	 * Method to perform cancel
	 *
	 * @return  void
	 */
	public function cancel()
	{
		$retVal = parent::cancel();
		if ($retVal)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=majors', false));
		}
	}

	/**
	 * Method to set the redirect
	 *
	 * @return  void
	 */
	public function importRedirect()
	{
		$ids = implode(",", JRequest::getVar("cid"));
		$this->setRedirect('index.php?option=com_thm_organizer&view=start&cid=' . $ids, $msg);
	}

	/**
	 * Method to perform delete
	 *
	 * @return  void
	 */
	public function delete()
	{
		$db = & JFactory::getDBO();
		$cid = JRequest::getVar('cid', array(), 'post', 'array');

		foreach ($cid as $id)
		{
			// Delete all related assets
			$query = 'SELECT * FROM #__thm_organizer_semesters_majors'
			. ' WHERE major_id = ' . $id . ';';
			$db->setQuery($query);
			$semesters = $db->loadObjectList();

			foreach ($semesters as $semester)
			{
				$semesterID = $semester->id;

				// Delete all related assets
				$query = 'SELECT * FROM #__thm_organizer_assets_semesters'
				. ' WHERE semesters_majors_id = ' . $semesterID . ';';
				$db->setQuery($query);
				$assets = $db->loadObjectList();

				foreach ($assets as $asset)
				{
					$asset = $asset->assets_tree_id;

					$query = 'DELETE FROM #__thm_organizer_assets_tree'
					. ' WHERE id = ' . $asset . ';';
					$db->setQuery($query);
					$db->query();
				}
			}

			// Delete the major
			$query = 'DELETE FROM #__thm_organizer_majors'
			. ' WHERE id = ' . $id . ';';
			$db->setQuery($query);
			$db->query();
		}
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=majors', false));
	}
}
