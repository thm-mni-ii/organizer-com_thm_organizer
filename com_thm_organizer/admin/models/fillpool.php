<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelFillpool
 * @description THM_OrganizerModelFillpool component admin model
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/module.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/lsfapi.php';
require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'models/mapping.php';

/**
 * Class THM_OrganizerModelFillpool for component com_thm_organizer
 *
 * Class provides methods to deal with fill pool
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelFillpool extends JModelAdmin
{
	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  			(default: 'colors')
	 * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
	 * @param   Array   $config  Configuration  (default: 'Array')
	 *
	 * @return  JTable object
	 */
	public function getTable($type = 'colors', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the form
	 *
	 * @param   Array    $data      Data  	   (default: Array)
	 * @param   Boolean  $loadData  Load data  (default: true)
	 *
	 * @return  A Form object
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.fillpool', 'fillpool', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Method to load the form data
	 *
	 * @return  Object
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.fillpool.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * Method to get the LSF configuration
	 *
	 * @param   Integer  $configId  Configuration id
	 *
	 * @return  mixed  Configration
	 */
	private function getLsfConfiguration($configId)
	{
		$this->db = JFactory::getDBO();
				
		$query = $this->db->getQuery(true);
		$query->select('*');
		$query->from("#__thm_organizer_majors");
		$query->where("id = $configId");
		
		$this->db->setQuery($query);
		$rows = $this->db->loadObjectList();
		return $rows;
	}

	/**
	 * Method to get the form
	 *
	 * @param   Integer  $parent  Parent id
	 *
	 * @return  Array
	 */
	private function getSemesters($parent)
	{
		$db = JFactory::getDBO();

		// Get the current selected major-id
		$majorId = $_SESSION['stud_id'];

		// Get the semester-ids from the database
		$query = $db->getQuery(true);
		$query->select("#__thm_organizer_semesters_majors.id as id");
		$query->from('#__thm_organizer_assets_tree');
		$query->join('inner', '#__thm_organizer_assets_semesters ' .
				'ON #__thm_organizer_assets_tree.id = #__thm_organizer_assets_semesters.assets_tree_id');
		$query->join('inner', '#__thm_organizer_semesters_majors ' .
				'ON #__thm_organizer_assets_semesters.semesters_majors_id = #__thm_organizer_semesters_majors.id');
		$query->where("asset = $parent");
		$query->where("major_id = $majorId");

		$db->setQuery($query);
		return $db->loadResultArray();
	}

	/**
	 * Method to overwrite the save method
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  Boolean
	 */
	public function save($data)
	{
		$globParams = JComponentHelper::getParams('com_thm_organizer');
		$db = JFactory::getDBO();
		$config = self::getLsfConfiguration($data["soap_query"]);
		$model = new THM_OrganizerModelMapping;
		$parent = $data['parent_id'];

		$semesters = self::getSemesters($parent);

		// Doing a soap request on curriculum, based on the current component configuration
		$client = new LSFClient($globParams->get('webserviceUri'), $globParams->get('webserviceUsername'), $globParams->get('webservicePassword'));
		$modulesXML = $client->getModules($config[0]->lsf_object, $config[0]->lsf_study_path, "", $config[0]->lsf_degree, $config[0]->po);

		// Check whether there is a soap response (xml format)
		if (isset($modulesXML))
		{
			// Iterate over the entire over each course-group of the returned xml structure
			foreach ($modulesXML->gruppe as $gruppe)
			{
				if ($gruppe->titelde == $data['lsf_group'])
				{
					// Iterate over each found course
					foreach ($gruppe->modulliste->modul as $modul)
					{
						$id = $modul->modulid;
						$this->db = JFactory::getDBO();
						
						$query = $this->db->getQuery(true);
						$query->select('*');
						$query->from("#__thm_organizer_assets");
						$query->where("lsf_course_id = $id");
						
						$this->db->setQuery($query);
						$rows = $this->db->loadObjectList();

						$arr = array();
						$arr['asset'] = $rows[0]->id;
						$arr['parent_id'] = $parent;
						$arr['proportion_crp'] = 0;
						$arr['color_id'] = $data['color_id'];

						JRequest::setVar('semesters', $semesters);

						$model->save($arr);
					}
				}
			}
		}
		return true;
	}
}
