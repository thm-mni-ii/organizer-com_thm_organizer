<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerMapping
 * @description THM_OrganizerControllerMapping component admin controller
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerMapping for component com_thm_organizer
 * Class provides methods perform actions for mapping
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerMapping extends JControllerForm
{
	/**
	 * Method to perform copy
	 *
	 * @return  void
	 */
	public function copy()
	{
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$model = $this->getModel('mapping');
		$stud_id = $_SESSION['stud_id'];

		// Copy the asset on the same level
		$model->copy($cid);

		$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
	}

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
		$task = $this->getTask();
		$stud_id = $_SESSION['stud_id'];
		$post = JRequest::getVar('jform', array(), 'post', 'array');
		$ids = explode(",", $post['id']);

		if ($task == 'save')
		{
			$model = $this->getModel('mapping');

			foreach ($ids as $id)
			{
				$post['id'] = $id;
				JRequest::setVar("id", $id);
				$retVal = $model->save($post);
			}

			if ($retVal)
			{
				$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
			}
		}
		elseif ($task == 'save2new')
		{
			parent::save($key, $urlVar);
			$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mapping&layout=edit", false));
		}
	}

	/**
	 * Method to perform edit
	 *
	 * @return  void
	 */
	public function edit()
	{
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		echo "controller edit";

		if (count($cid) > 1)
		{
			JRequest::setVar("multipleEdit", "true");
			JRequest::setVar("id", $cid[0]);
			$url = 'index.php?option=com_thm_organizer&view=mapping&layout=edit&id=$cids&multiple_edit=true';
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			parent::edit();
		}
	}

	/**
	 * Method to perform cancel
	 *
	 * @return  void
	 */
	public function cancel()
	{
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$retVal = parent::cancel();
		if ($retVal)
		{
			$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
		}
	}

	/**
	 * Method to get table
	 *
	 * @param   String  $type    Key		   (default: 'mapping')
	 * @param   String  $prefix  Url variable  (default: 'THM_OrganizerTable')
	 * @param   Array   $config  Url variable  (default: Array)
	 *
	 * @return  Object
	 */
	public function getTable($type = 'mapping', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to perform delete
	 *
	 * @return  void
	 */
	public function delete()
	{
		$stud_id = $_SESSION['stud_id'];
		$dbo = JFactory::getDBO();
		$deleteQuery = $dbo->getQuery(true);
		$deleteQuery->delete('#__thm_organizer_assets_tree');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		foreach ($cid as $id)
		{
			$deleteQuery->clear('where');
			$deleteQuery->where("id = '$id'");
			$dbo->setQuery((string) $deleteQuery);
			$dbo->query();
		}

		// Get the primary keys and ordering values for the selection.
		$reorderQuery = $dbo->getQuery(true);
		$reorderQuery->select('*');
		$reorderQuery->from('#__thm_organizer_assets_tree AS at');
		$reorderQuery->join('#__thm_organizer_assets_semesters AS asem ON asem.assets_tree_id = at.id');
		$reorderQuery->join('#__thm_organizer_semesters_majors AS sm ON asem.semesters_majors_id = sm.id');
		$reorderQuery->where("semesters_majors.major_id =" . $stud_id);
		$dbo->setQuery((string) $reorderQuery);
		$rows = $dbo->loadObjectList();

		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_thm_organizer' . DS . 'tables');
		$table = self::getTable();

		foreach ($rows as $row)
		{
			$table->reorder("parent_id=" . $row->asset);
		}

		$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
	}

	/**
	 * Method to publish selected asset tree items
	 *
	 * @return  void
	 */
	public function publish()
	{
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$model = $this->getModel('mapping');
		$model->publish();
		$this->setRedirect("index.php?option=com_thm_organizer&view=mappings&id=$stud_id");
	}

	/**
	 * Method to unpublish selected asset tree items
	 *
	 * @return  void
	 */
	public function unpublish()
	{
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$model = $this->getModel('mapping');
		$model->unpublish();
		$this->setRedirect("index.php?option=com_thm_organizer&view=mappings&id=$stud_id");
	}
}
