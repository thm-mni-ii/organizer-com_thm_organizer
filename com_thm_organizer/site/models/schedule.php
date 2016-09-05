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
	 * getter for the default time grid out of database
	 *
	 * @return  object|false
	 *
	 * @throws  RuntimeException
	 */
	public function getDepartments()
	{
		$languageTag = explode('-', JFactory::getLanguage()->getTag())[0];
		$dbo         = JFactory::getDbo();
		$query       = $dbo->getQuery(true);
		$query
			->select("id, name_$languageTag as name")
			->from('#__thm_organizer_departments');
		$dbo->setQuery((string) $query);

		try
		{
			$result = $dbo->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * getter for the default time grid out of database
	 *
	 * @return  object|false
	 *
	 * @throws  RuntimeException
	 */
	public function getDefaultGrid()
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query
			->select('grid')
			->from('#__thm_organizer_grids')
			->where("'default'='true'"); // Apostrophes needed, otherwise 'default' gets handled as keyword
		$dbo->setQuery((string) $query);

		try
		{
			$result = $dbo->loadObject();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

			return false;
		}

		if (!$result)
		{
			return $this->getTimeFallback();
		}

		return json_decode($result->grid);
	}

	/**
	 * example and default fallback of a time grid loaded from database
	 *
	 * @return array
	 */
	private function getTimeFallback()
	{
		$fallback = '{
				"periods": {
				    "1":{
				        "start_time":"0800",
			            "end_time":"0930"
			        },
			        "2": {
				        "start_time":"0950",
			            "end_time":"1120"},
			        "3": {
				        "start_time":"1130",
			            "end_time":"1300"
			        },
			        "4": {
				        "start_time":"1400",
			            "end_time":"1530"},
			        "5": {
				        "start_time":"1545",
			            "end_time":"1715"},
			        "6": {
				        "start_time":"1730",
			            "end_time":"1900"
			        }
			    },
			    "start_day":1,
			    "end_day":6
			}';

		return json_decode($fallback);
	}

	/**
	 * an example of schedules and their json data
	 *
	 * @return array
	 */
	public function getSchedules()
	{
		$schedules = array();

		$schedules[] = json_decode(
			'{
                "name": "BWL",
                "pool": "Finanzen",
                "id": "bwl-finanzen",
                "days": {
                    "1": {
                        "1": [
                            {
                                "name_de": "BWL-er Kram",
                                "name_en": "BWL-er stuff",
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
                                "name_de": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "name_en": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
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
                                "name_de": "Irgendwas mit Geld",
                                "name_en": "Something with money",
                                "room": "A.6.6.6",
                                "teacher": "Schmidt",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name_de": "Zaster",
                                "name_en": "Zaster",
                                "room": "A.7.7.7",
                                "teacher": "Müller",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Die kleinste Geige der Welt",
                                "name_en": "Die kleinste Geige der Welt",
                                "room": "A.1.1.1",
                                "teacher": "Schneider",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                            {
                                "name_de": "Moneten",
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
                                "name_de": "BWL-er Kram",
                                "name_en": "BWL-er Kram",
                                "room": "A.4.4.4",
                                "teacher": "BWLer",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name_de": "Irgendwas mit Geld",
                                "name_en": "Irgendwas mit Geld",
                                "room": "A.6.6.6",
                                "teacher": "Schmidt",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Projektmanagement und -qualität",
                                "name_en": "Projektmanagement und -qualität",
                                "room": "A.7.7.7",
                                "teacher": "Müller",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                            {
                                "name_de": "Wie bleibe ich reich?",
                                "name_en": "Wie bleibe ich reich?",
                                "room": "A.1.1.1",
                                "teacher": "Schneider",
                                "module": "CS123"
                            }
                        ],
                        "5": [
                            {
                                "name_de": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "name_en": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
                                "room": "B1.2.3",
                                "teacher": "Zuckerberg",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "6": [
                            {
                                "name_de": "Angebot-Nachfrage-Modell in Kohärenz zum Social-Media-Hype",
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
            }');

		$schedules[] = json_decode(
			'{
                "name": "Social",
                "pool": "Social Skills",
                "id": "informatik-social",
                "days": {
                    "1": {
                        "1": [
                            {
                                "name_de": "Soziales und der Bezug zur Wissenschaft",
                                "name_en": "Soziales und der Bezug zur Wissenschaft",
                                "room": "A12.06.17",
                                "teacher": "Henrich",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name_de": "Hauptsache Credit-Points",
                                "name_en": "Hauptsache Credit-Points",
                                "room": "A20.02.300",
                                "teacher": "Fischer",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Armut bekämpfen - gut bewerben",
                                "name_en": "Armut bekämpfen - gut bewerben",
                                "room": "A.3.3.3",
                                "teacher": "Weber",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Problem Frames",
                                "name_en": "Problem Frames",
                                "room": "A2.2.12",
                                "teacher": "Koch",
                                "module": "CS123"
                            }
                        ],
                        "6": [
                        ]
                    },
                    "2" :{
                        "1": [
                            {
                                "name_de": "Soziales und der Bezug zur Wissenschaft",
                                "name_en": "Soziales und der Bezug zur Wissenschaft",
                                "room": "A12.06.17",
                                "teacher": "Henrich",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name_de": "Hauptsache Credit-Points",
                                "name_en": "Hauptsache Credit-Points",
                                "room": "A20.02.300",
                                "teacher": "Fischer",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Armut bekämpfen - gut bewerben",
                                "name_en": "Armut bekämpfen - gut bewerben",
                                "room": "A.3.3.3",
                                "teacher": "Weber",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Problem Frames",
                                "name_en": "Problem Frames",
                                "room": "A2.2.12",
                                "teacher": "Koch",
                                "module": "CS123"
                            }
                        ],
                        "6": [
                        ]
                    },
                    "3": {
                        "1": [
                            {
                                "name_de": "Soziales und der Bezug zur Wissenschaft",
                                "name_en": "Soziales und der Bezug zur Wissenschaft",
                                "room": "A12.06.17",
                                "teacher": "Henrich",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name_de": "Hauptsache Credit-Points",
                                "name_en": "Hauptsache Credit-Points",
                                "room": "A20.02.300",
                                "teacher": "Fischer",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Armut bekämpfen - gut bewerben",
                                "name_en": "Armut bekämpfen - gut bewerben",
                                "room": "A.3.3.3",
                                "teacher": "Weber",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Problem Frames",
                                "name_en": "Problem Frames",
                                "room": "A2.2.12",
                                "teacher": "Koch",
                                "module": "CS123"
                            }
                        ],
                        "6": [
                        ]
                    },
                    "4": {
                        "1": [
                            {
                                "name_de": "Soziales und der Bezug zur Wissenschaft",
                                "name_en": "Soziales und der Bezug zur Wissenschaft",
                                "room": "A12.06.17",
                                "teacher": "Henrich",
                                "time": "1200",
                                "module": "CS123"
                            }
                        ],
                        "2": [
                            {
                                "name_de": "Hauptsache Credit-Points",
                                "name_en": "Hauptsache Credit-Points",
                                "room": "A20.02.300",
                                "teacher": "Fischer",
                                "module": "CS123"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Armut bekämpfen - gut bewerben",
                                "name_en": "Armut bekämpfen - gut bewerben",
                                "room": "A.3.3.3",
                                "teacher": "Weber",
                                "module": "CS123"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Problem Frames",
                                "name_en": "Problem Frames",
                                "room": "A2.2.12",
                                "teacher": "Koch",
                                "module": "CS123"
                            }
                        ],
                        "6": [
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
            }');

		$schedules[] = json_decode(
			'{
                "name": "Informatik",
                "pool": "1. Semester",
                "id": "informatik-1",
                "days": {
                    "1": {
                        "1": [
                        ],
                        "2": [
                            {
                                "name_de": "Grundlagen der Informatik",
                                "name_en": "Grundlagen der Informatik",
                                "module": "CS1014",
                                "room": "A20.1.36",
                                "teacher": "Priefer"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "module": "MN1007",
                                "room": "A20.1.36",
                                "teacher": "Metz"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Grundlagen der Informatik",
                                "name_en": "Grundlagen der Informatik",
                                "module": "MN1014",
                                "room": "A12.3.04",
                                "teacher": "Gerlach"
                            },
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "module": "MN1007",
                                "room": "A20.1.36",
                                "teacher": "Metz"
                            }
                        ],
                        "6": [
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "module": "CS123",
                                "room": "A20.1.07",
                                "time": "1200",
                                "teacher": "Metz"
                            }
                        ]
                    },
                    "2": {
                        "1": [
                        ],
                        "2": [
                            {
                                "name_de": "Grundlagen der Informatik",
                                "name_en": "Grundlagen der Informatik",
                                "module": "CS1014",
                                "room": "A20.1.36",
                                "teacher": "Priefer"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "module": "MN1007",
                                "room": "A20.1.36",
                                "time": "1200",
                                "teacher": "Metz"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Grundlagen der Informatik",
                                "name_en": "Grundlagen der Informatik",
                                "module": "MN1014"
                            },
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "module": "MN1007",
                                "room": "A20.1.36",
                                "teacher": "Metz"
                            }
                        ],
                        "6": [
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "room": "A20.1.07",
                                "teacher": "Metz"
                            }
                        ]
                    },
                    "3": {
                        "1": [
                        ],
                        "2": [
                            {
                                "module": "CS1014",
                                "room": "A20.1.36",
                                "teacher": "Priefer"
                            }
                        ],
                        "3": [
                            {
                                "module": "MN1007",
                                "room": "A20.1.36",
                                "teacher": "Metz"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Grundlagen der Informatik",
                                "name_en": "Grundlagen der Informatik",
                                "module": "MN1014",
                                "room": "A12.3.04",
                                "teacher": "Priefer"
                            }
                        ],
                        "6": [
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "room": "A20.1.07",
                                "teacher": "Metz"
                            }
                        ]
                    },
                    "4": {
                        "1": [
                        ],
                        "2": [
                            {
                                "name_de": "Grundlagen der Informatik",
                                "name_en": "Grundlagen der Informatik",
                                "module": "CS1014",
                                "room": "A20.1.36",
                                "teacher": "Priefer"
                            }
                        ],
                        "3": [
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "room": "A20.1.36",
                                "time": "1200",
                                "teacher": "Metz"
                            }
                        ],
                        "4": [
                        ],
                        "5": [
                            {
                                "name_de": "Grundlagen der Informatik",
                                "name_en": "Grundlagen der Informatik",
                                "module": "MN1014",
                                "room": "A12.3.04",
                                "teacher": "Priefer"
                            },
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "module": "MN1007",
                                "room": "A20.1.36"
                            }
                        ],
                        "6": [
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "room": "A20.1.07",
                                "teacher": "Metz"
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
                            {
                                "name_de": "Diskrete Mathematik",
                                "name_en": "Diskrete Mathematik",
                                "teacher": "Metz",
                                "module": "CS123"
                            }
                        ]
                    }
                }
            }');

		return $schedules;
	}
}
