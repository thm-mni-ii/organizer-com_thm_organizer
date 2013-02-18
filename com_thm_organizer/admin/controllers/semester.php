<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        www.mni.thm.de
 **/
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerColor for component com_thm_organizer
 *
 * Class provides methods perform actions for semeter
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerControllerSemester extends JControllerForm
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
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=semesters', false));
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
			$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=semesters', false));
		}
	}

	/**
	 * Method to perform delete
	 *
	 * @return  void
	 */
	public function delete()
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->delete('#__thm_organizer_semesters');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		foreach ($cid as $id)
		{
			$query->clear('where');
			$query->where("id = '$id'");
			$dbo->setQuery((string) $query);
			$dbo->query();
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=semesters', false));
	}
}