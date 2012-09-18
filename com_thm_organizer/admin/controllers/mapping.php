<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerMapping
 * @description THM_OrganizerControllerMapping component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerMapping for component com_thm_organizer
 *
 * Class provides methods perform actions for mapping
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
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
		$explodedcid = implode(',', $cid);
		echo "controller edit";

		if (count($cid) > 1)
		{
			JRequest::setVar("multipleEdit", "true");
			JRequest::setVar("id", $cid[0]);
			$this->setRedirect(
					JRoute::_('index.php?option=com_thm_organizer&view=mapping&layout=edit&id=' .
							$explodedcid . "&multiple_edit=true", false
					)
			);
		}
		else
		{
			parent::edit();
		}
	}

	/**
	 * Method to perform save2new
	 *
	 * @return  void
	 */
	public function save2new()
	{
		$data[$key] = $recordId;
		$context = "$this->option.edit . $this->context";

		// Clear the record id and data from the session.
		$this->releaseEditId($context, $recordId);
		$app->setUserState($context . '.data', null);

		// Redirect back to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_thm_organizer&view=mapping&layout=edit'));
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
	 * @param   String  $prefix  Url variable  (default: 'THM_curriculumTable')
	 * @param   Array   $config  Url variable  (default: Array)
	 *
	 * @return  Object
	 */
	public function getTable($type = 'mapping', $prefix = 'THM_curriculumTable', $config = array())
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
		$db = & JFactory::getDBO();
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$stud_id = $_SESSION['stud_id'];

		foreach ($cid as $id)
		{
			$query = 'DELETE FROM #__thm_organizer_assets_tree'
			. ' WHERE id = ' . $id . ';';
			$db->setQuery($query);
			$db->query();
		}

		// Get the primary keys and ordering values for the selection.
		$query = $db->getQuery(true);

		// Select the primary key and ordering values from the table.
		$query->select('*');
		$query->from(' #__thm_organizer_assets_tree as assets_tree');
		$query->join('inner', '#__thm_organizer_assets_semesters as assets_semesters ON assets_semesters.assets_tree_id = assets_tree.id');
		$query->join('inner', '#__thm_organizer_semesters_majors as semesters_majors ON assets_semesters.semesters_majors_id = semesters_majors.id');
		$query->where("semesters_majors.major_id =" . $stud_id);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

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
