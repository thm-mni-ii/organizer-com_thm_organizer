<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerMajor
 * @description THM_OrganizerControllerMajor component admin controller
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');
JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_thm_organizer' . DS . 'tables');

/**
 * Class THM_OrganizerControllerMajor for component com_thm_organizer
 * Class provides methods perform actions for major
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
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
		$dbo = JFactory::getDBO();
		$majorQuery = $dbo->getQuery(true);
		$majorQuery->select('*');
		$majorQuery->from('#__thm_organizer_semesters_majors');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		foreach ($cid as $id)
		{
			$majorQuery->clear('where');
			$majorQuery->where("major_id = '$id'");
			$dbo->setQuery((string) $majorQuery);
			$semesters = $dbo->loadObjectList();

			$semesterQuery = $dbo->getQuery(true);
			$semesterQuery->select('*');
			$semesterQuery->from('#__thm_organizer_assets_semesters');
			foreach ($semesters as $semester)
			{
				$semesterQuery->clear('where');
				$semesterQuery->where("major_id = '$semester->id'");
				$dbo->setQuery((string) $semesterQuery);
				$assets = $dbo->loadObjectList();

				$deleteTreeQuery = $dbo->getQuery(true);
				$deleteTreeQuery->delete('__thm_organizer_assets_tree');
				foreach ($assets as $asset)
				{
					$deleteTreeQuery->clear('where');
					$deleteTreeQuery->where("id = '$asset->assets_tree_id'");
					$dbo->setQuery((string) $deleteTreeQuery);
					$dbo->query();
				}
			}

			$deleteMajorQuery = $dbo->getQuery(true);
			$deleteMajorQuery->delete('#__thm_organizer_majors');
			$deleteMajorQuery->where("id = '$id'");
			$dbo->setQuery((string) $deleteMajorQuery);
			$dbo->query();
		}
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=majors', false));
	}
}
