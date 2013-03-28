<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelColor
 * @description THM_OrganizerModelColor component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelColor for component com_thm_organizer
 * Class provides methods to deal with color
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelColor extends JModel
{
	/**
	 * Removes color entries from the database
	 * 
	 * @return  boolean true on success, otherwise false
	 */
	public function delete()
	{
		$cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";
		$query = $this->_db->getQuery(true);
		$query->delete('#__thm_organizer_colors');
		$query->where("id IN ( $cids )");
		$this->_db->setQuery($query);
		try
		{
			$this->_db->query();
			return true;
		}
		catch (Exception $exception)
		{
			return false;
		}
	}
}
