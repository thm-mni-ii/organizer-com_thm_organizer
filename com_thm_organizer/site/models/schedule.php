<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        schedule model
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class THM_OrganizerModelSchedule for loading the chosen schedule from the database
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule extends JModelLegacy
{
	/**
	 * time grids for displaying the schedules
	 *
	 * @var array
	 */
	public $grids;

	/**
	 * name of active department
	 *
	 * @var array
	 */
	public $departmentName;

	/**
	 * THM_OrganizerModelSchedule constructor.
	 *
	 * @param   array $config options
	 *
	 * @throws Exception
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);
		$this->grids          = $this->getGrids();
		$this->departmentName = $this->getDepartmentName();
	}

	/**
	 * getter for the default time grid out of database
	 *
	 * @return false|string
	 *
	 * @throws Exception
	 */
	public function getDepartmentName()
	{
		$languageTag        = THM_OrganizerHelperLanguage::getShortTag();
		$this->params       = JFactory::getApplication()->getParams();
		$this->departmentID = $this->params->get('departmentID', 0);

		$query = $this->_db->getQuery(true);
		$query
			->select("name_$languageTag as name")
			->from('#__thm_organizer_departments')
			->where("id = $this->departmentID");

		$this->_db->setQuery((string) $query);

		try
		{
			$result = $this->_db->loadResult();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Getter method for all grids in database
	 *
	 * @return mixed  array | empty in case of errors or no results
	 *
	 * @throws RuntimeException
	 */
	public function getGrids()
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();
		$query       = $this->_db->getQuery(true);
		$query->select("name_$languageTag AS name, grid, defaultGrid")
			->from('#__thm_organizer_grids');
		$this->_db->setQuery((string) $query);

		try
		{
			$grids = $this->_db->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return '[]';
		}

		if (empty($grids))
		{
			return '[]';
		}

		return $grids;
	}

	/**
	 * gets the first default grid from all grid objects in database
	 *
	 * @return object JSON grid
	 */
	public function getDefaultGrid()
	{
		$defaultGrids = array_filter(
			$this->grids,
			function ($var)
			{
				return $var->defaultGrid;
			}
		);

		if (empty($defaultGrids))
		{
			return $this->getGridFallback();
		}
		else
		{
			return $defaultGrids[0];
		}
	}

	/**
	 * example and fallback of a default time grid
	 *
	 * @return object (json)
	 */
	private function getGridFallback()
	{
		$fallback = '{
				"periods": {
				    "1":{
				        "startTime":"0800",
			            "endTime":"0930"
			        },
			        "2": {
				        "startTime":"0950",
			            "endTime":"1120"},
			        "3": {
				        "startTime":"1130",
			            "endTime":"1300"
			        },
			        "4": {
				        "startTime":"1400",
			            "endTime":"1530"},
			        "5": {
				        "startTime":"1545",
			            "endTime":"1715"},
			        "6": {
				        "startTime":"1730",
			            "endTime":"1900"
			        }
			    },
			    "startDay":1,
			    "endDay":6
			}';

		return json_decode($fallback);
	}

	/**
	 * an example of schedules and their json data
	 *
	 * @return object
	 */
	public function getTestSchedule()
	{
		return json_decode(
			'{
                "name": "BWL",
                "pool": "Finanzen",
                "id": "bwl-finanzen",
                "days": {
                    "1": {
                        "1": [
                            {
                                "name": "BWL-er Kram",
                                "room": "A.4.4.4",
                                "teacher": "BWLer",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                        ],
                        "3": [
                        ],
                        "4": [
                        ],
                        "5": [
                        ],
                        "6": [
                            {
                                "name": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "room": "B1.2.3",
                                "teacher": "Zuckerberg",
                                "time": "1800",
                                "module": "CS123"
                            }
                        ]
                    },
                    "2": {
                        "1": [
                            {
                                "name": "Irgendwas mit Geld",
                                "name_en": "Something with money",
                                "room": "A.6.6.6",
                                "teacher": "Schmidt",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name": "Zaster",
                                "name_en": "Zaster",
                                "room": "A.7.7.7",
                                "teacher": "Müller",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name": "Die kleinste Geige der Welt",
                                "name_en": "Die kleinste Geige der Welt",
                                "room": "A.1.1.1",
                                "teacher": "Schneider",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                            {
                                "name": "Moneten",
                                "name_en": "Moneten",
                                "room": "A.1.1.1",
                                "teacher": "Schneider",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "5": [
                        ],
                        "6": [
                        ]
                    },
                    "3": { 
                        "1": [
                        ],
                        "2": [
                        ],
                        "3": [
                        ],
                        "4": [
                        ],
                        "5": [
                        ],
                        "6": [
                        ]
                    },
                    "4": {
                        "1": [
                            {
                                "name": "BWL-er Kram",
                                "name_en": "BWL-er Kram",
                                "room": "A.4.4.4",
                                "teacher": "BWLer",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name": "Irgendwas mit Geld",
                                "name_en": "Irgendwas mit Geld",
                                "room": "A.6.6.6",
                                "teacher": "Schmidt",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name": "Projektmanagement und -qualität",
                                "name_en": "Projektmanagement und -qualität",
                                "room": "A.7.7.7",
                                "teacher": "Müller",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                            {
                                "name": "Wie bleibe ich reich?",
                                "name_en": "Wie bleibe ich reich?",
                                "room": "A.1.1.1",
                                "teacher": "Schneider",
                                "module": "CS123"
                            }
                        ],
                        "5": [
                            {
                                "name": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "name_en": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "room": "B1.2.3",
                                "teacher": "Zuckerberg",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "6": [
                            {
                                "name": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "name_en": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "room": "B1.2.3",
                                "teacher": "Zuckerberg",
                                "module": "CS123"
                            }
                        ]
                    },
                    "5": {
                        "1": [
                        ],
                        "2": [
                        ],
                        "3": [
                        ],
                        "4": [
                        ],
                        "5": [
                        ],
                        "6": [
                        ]
                    },
                    "6": {
                        "1": [
                        ],
                        "2": [
                        ],
                        "3": [
                        ],
                        "4": [
                        ],
                        "5": [
                        ],
                        "6": [
                        ]
                    }
                }
            }'
		);
	}
}
