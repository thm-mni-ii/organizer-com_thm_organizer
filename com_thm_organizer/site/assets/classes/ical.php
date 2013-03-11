<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		ICALBauer
 * @description ICALBauer file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once dirname(__FILE__) . "/abstrakterBauer.php";

jimport('iCalcreator.iCalcreator');

/**
 * Class ICALBauer for component com_thm_organizer
 *
 * Class provides methods to create a schedule in ical format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class ICALBauer extends abstrakterBauer
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 */
	private $_JDA = null;

	/**
	 * Config
	 *
	 * @var    Object
	 */
	private $_cfg = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   Object           $cfg  A object which has configurations including
	 */
	public function __construct($JDA, $cfg)
	{
		$this->_JDA = $JDA;
		$this->_cfg = $cfg;
	}

	/**
	 * Method to create a ical schedule
	 *
	 * @param   Object  $arr       The event object
	 * @param   String  $username  The current logged in username
	 * @param   String  $title     The schedule title
	 *
	 * @return Array An array with information about the status of the creation
	 */
	public function erstelleStundenplan($arr, $username, $title)
	{
		$semesterstart = $arr[count($arr) - 1]->sdate;
		$semesterend   = $arr[count($arr) - 1]->edate;

		unset($arr[count($arr) - 1]);

		if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $username != "")
		{
			$title = $username . " - " . $title;
		}

		$vCalendar = new vcalendar;
		$vCalendar->setConfig('unique_id', "MySched");
		$vCalendar->setConfig("lang", "de");
		$vCalendar->setProperty("x-wr-calname", $title);
		$vCalendar->setProperty("X-WR-CALDESC", "Calendar Description");
		$vCalendar->setProperty("X-WR-TIMEZONE", "Europe/Berlin");
		$vCalendar->setProperty("PRODID", "-//212.201.14.161//NONSGML iCalcreator 2.6//");
		$vCalendar->setProperty("VERSION", "2.0");
		$vCalendar->setProperty("METHOD", "PUBLISH");

		$vTimeZone1 = new vtimezone;
		$vTimeZone1->setProperty("TZID", "Europe/Berlin");

		$vTimeZone2 = new vtimezone('standard');
		$vTimeZone2->setProperty("DTSTART", 1601, 1, 1, 0, 0, 0);
		$vTimeZone2->setProperty("TZNAME", "Standard Time");

		$vTimeZone1->setComponent($vTimeZone2);
		$vCalendar->setComponent($vTimeZone1);

		$query = 'SELECT startdate, enddate, starttime, endtime ';
		$query .= 'FROM #__thm_organizer_events ';
		$query .= "WHERE categoryid = '{$this->_cfg['vacation_id']}' ";
		$res   = $this->_JDA->query($query);

		if (is_array($res))
		{
			if (count($res) > 1)
			{
				foreach ($res as $holi)
				{
					if ($holi->enddate == "0000-00-00" || $holi->enddate == null)
					{
						$holi->enddate = $holi->startdate;
					}
				}

				$compare_dates = function($thingOne, $thingTwo)
				{
					if ($thingOne->startdate == $thingTwo->startdate)
					{
						return 0;
					}
					return ($thingOne->startdate > $thingTwo->startdate) ? +1 : - 1;
				};

				usort($res, $compare_dates);

				$todelete = array();

				for ($i = 0; $i < count($res); $i++)
				{
					if ($res[$i]->startdate == $res[$i]->enddate)
					{
						for ($y = 0; $y < $i; $y++)
						{
							if ($res[$y]->startdate <= $res[$i]->startdate && $res[$y]->enddate >= $res[$i]->startdate)
							{
								$todelete[] = $i;
								break;
							}
						}
					}
				}

				foreach ($todelete as $td)
				{
					unset($res[$td]);
				}

				$res = array_values($res);

				$stop  = false;
				$num = null;
				while ($stop === false)
				{
					$stop = true;
					for ($i = 0; $i < count($res) - 1; $i++)
					{
						if ($res[$i]->enddate >= $res[$i + 1]->startdate)
						{
							$res[$i]->enddate = $res[$i + 1]->enddate;
							$stop = false;
							$num = $i + 1;
							break;
						}
					}
					if ($stop === false)
					{
						unset($res[$num]);
						$res = array_values($res);
					}
				}

				if ($res[0]->startdate <= $semesterstart)
				{
					$semesterstart = $res[0]->enddate;
					unset($res[0]);
					$res = array_values($res);
				}

				if ($res[count($res) - 1]->enddate >= $semesterend)
				{
					$semesterend = $res[count($res) - 1]->startdate;
					unset($res[count($res) - 1]);
					$res = array_values($res);
				}

				if (count($res) > 0)
				{
					for ($i = 0; $i <= count($res); $i++)
					{
						if ($i == 0)
						{
							$vCalendar = $this->setEvent($vCalendar, $arr, $semesterstart, $res[$i]->startdate);
						}
						elseif ($i == count($res))
						{
							$vCalendar = $this->setEvent($vCalendar, $arr, date("Y-m-d", strtotime("+1 day", strtotime($res[$i - 1]->enddate))), $semesterend);
						}
						else
						{
							$vCalendar = $this->setEvent($vCalendar, $arr, date("Y-m-d", strtotime("+1 day", strtotime($res[$i - 1]->enddate))), $res[$i]->startdate);
						}
					}
				}
				else
				{
					$vCalendar = $this->setEvent($vCalendar, $arr, $semesterstart, $semesterend, $res);
				}
			}
			else
			{
				$vCalendar = $this->setEvent($vCalendar, $arr, $semesterstart, $semesterend, $res);
			}
		}
		else
		{
			$vCalendar = $this->setEvent($vCalendar, $arr, $semesterstart, $semesterend, $res);
		}

		$vCalendar->saveCalendar($this->_cfg['pdf_downloadFolder'], $title . '.ics');
		$resparr['url'] = "false";
		return array("success" => true,"data" => $resparr);
	}

	/**
	 * Method to set an event
	 *
	 * @param   Object  $event          An event
	 * @param   Array   $arr            The event array
	 * @param   String  $semesterstart  The semester start date
	 * @param   String  $semesterend    The semester end date
	 * @param   Array   $vacations      The vacation array
	 *
	 * @return An array which has the result including
	 */
	private function setEvent($event, $arr, $semesterstart, $semesterend, $vacations)
	{
		$semesterend = date("Y-m-d", strtotime($semesterend));
		$endarr = explode("-", $semesterend);

		if (is_array($arr))
		{
			foreach ($arr as $event)
			{
				if (isset($event->dow) && isset($event->block))
				{
					$semesterstart = date("Y-m-d", strtotime($semesterstart));
					$tempdate = $semesterstart;

					while (date("N", strtotime($tempdate)) != 1)
					{
						$tempdate = date("Y-m-d", strtotime("-1 day", strtotime($tempdate)));
					}

					$tempdate = date("Y-m-d", strtotime("+" . (((int) $event->dow) - 1) . " day", strtotime($tempdate)));

					while ($tempdate < $semesterstart)
					{
						$tempdate = date("Y-m-d", strtotime("next monday", strtotime($tempdate)));
						$tempdate = date("Y-m-d", strtotime("+" . (((int) $event->dow) - 1) . " day", strtotime($tempdate)));
					}

					if ($tempdate > $semesterend)
					{
						return $event;
					}

					$beginarr = explode("-", $tempdate);

					$times     = $this->blocktotime($event->block);
					$begintime = explode(":", $times[0]);
					$endtime   = explode(":", $times[1]);

					$startdate  = array(
							"year" => $beginarr[0],
							"month" => $beginarr[1],
							"day" => $beginarr[2],
							"hour" => $begintime[0],
							"min" => $begintime[1],
							"sec" => 0
					);
					$enddate    = array(
							"year" => $beginarr[0],
							"month" => $beginarr[1],
							"day" => $beginarr[2],
							"hour" => $endtime[0],
							"min" => $endtime[1],
							"sec" => 0
					);
					$endarrdate = array(
							"year" => $endarr[0],
							"month" => $endarr[1],
							"day" => $endarr[2],
							"hour" => 0,
							"min" => 0,
							"sec" => 0
					);

					$e = new vevent;

					$dozarr  = explode(" ", $event->doz);
					$doztemp = "";

					foreach ($dozarr as $dozitem)
					{
						$res = $this->getResource($dozitem, "doz");

						if (count($res) == 0)
						{
							$res[0]->oname = $dozitem;
						}
						if ($doztemp == "")
						{
							$doztemp .= "" . $res[0]->oname;
						}
						else
						{
							$doztemp .= ", " . $res[0]->oname;
						}
					}

					$roomarr  = explode(" ", $event->room);
					$roomtemp = "";
					foreach ($roomarr as $roomitem)
					{
						$res = $this->getResource($roomitem, "room");
						if (count($res) == 0)
						{
							$res[0]->oname = $roomitem;
						}
						if ($roomtemp == "")
						{
							$roomtemp .= "" . $res[0]->oname;
						}
						else
						{
							$roomtemp .= ", " . $res[0]->oname;
						}
					}

					$desc = "$event->name bei $doztemp im $roomtemp \n";
					$desc .= "{$this->nummerzutag($event->dow)} $event->block Block\n";
					$desc .= "Modulnummer: $event->moduleID \n";

					$e->setProperty("ORGANIZER", $doztemp);
					$e->setProperty("DTSTART", $startdate);
					$e->setProperty("DTEND", $enddate);
					$e->setProperty("RRULE", array(
							"FREQ" => "WEEKLY",
							"UNTIL" => $endarrdate,
							"BYDAY" => array(
									"DAY" => $this->daynumtoday($event->dow)
							),
							"WKST" => "MO"
					)
					);
					$e->setProperty("LOCATION", $roomtemp);
					$e->setProperty("TRANSP", "OPAQUE");
					$e->setProperty("SEQUENCE", "0");
					$e->setProperty("SUMMARY", $event->name . " bei " . $doztemp . " im " . $roomtemp);
					$e->setProperty("PRIORITY", "5");
					$e->setProperty("DESCRIPTION", $desc);

					// Doesnt work in Thunderbird and Outlook 2003
					foreach ($vacations as $vacationValue)
					{
						$vacationStart = DateTime::createFromFormat("Y-m-d", $vacationValue->startdate);
						$vacationEnd = DateTime::createFromFormat("Y-m-d", $vacationValue->enddate);
						$interval = $vacationStart->diff($vacationEnd);
						$diffDays = (int) $interval->format('%d');
						
						while ($diffDays != 0)
						{
							$e->setProperty("EXDATE", array(
									array(
											"year" => $vacationStart->format('Y'),
											"month" => $vacationStart->format('m'),
											"day" => $vacationStart->format('d'),
											"hour" => $begintime[0],
											"min" => $begintime[1],
											"sec" => 0
									)
							), array(
									'TZID' => 'Europe/Berlin'
							)
							);
							$vacationStart->add(new DateInterval('P1D'));
							$interval = $vacationStart->diff($vacationEnd);
							$diffDays = (int) $interval->format('%d');
						}
						$e->setProperty("EXDATE", array(
								array(
										"year" => $vacationEnd->format('Y'),
										"month" => $vacationEnd->format('m'),
										"day" => $vacationEnd->format('d'),
										"hour" => $begintime[0],
										"min" => $begintime[1],
										"sec" => 0
								)
						), array(
								'TZID' => 'Europe/Berlin'
						)
						);
					}

					$event->setComponent($e);
				}
				else
				{

				}
			}
		}
		else
		{

		}
		return $event;
	}

	/**
	 * Method to check the username and password
	 *
	 * @param   Integer  $block  The block
	 *
	 * @return Array An array which includes the block time
	 */
	private function blocktotime($block)
	{
		// Immer eine Stunden weniger wegen tz=Europe/Berlin (+0100)
		$times = array(
			 1 => array(
			 		0 => "8:00",
			 		1 => "9:30"
			 ),
				2 => array(
						0 => "9:50",
						1 => "11:20"
				),
				3 => array(
						0 => "11:30",
						1 => "13:00"
				),
				4 => array(
						0 => "14:00",
						1 => "15:30"
				),
				5 => array(
						0 => "15:45",
						1 => "17:15"
				),
				6 => array(
						0 => "17:30",
						1 => "19:00"
				)
		);
		return $times[$block];
	}

	/**
	 * Method to check the username and password
	 *
	 * @param   Integer  $daynum  The daynumber
	 *
	 * @return String A shortened dayname
	 */
	private function daynumtoday($daynum)
	{
		$days = array(
				1 => "MO",
				2 => "TU",
				3 => "WE",
				4 => "TH",
				5 => "FR"
		);
		return $days[$daynum];
	}

	/**
	 * Method to transform a number to a day
	 *
	 * @param   Integer  $daynum  A day number
	 *
	 * @return String A dayname
	 */
	private function nummerzutag($daynum)
	{
		$days = array(
			 1 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_MONDAY"),
				2 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_TUESDAY"),
				3 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_WEDNESDAY"),
				4 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_THURSDAY"),
				5 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_FRIDAY")
		);
		return $days[$daynum];
	}

	/**
	 * Method to get a name of a resource
	 *
	 * @param   String  $resourcename  A resource name
	 * @param   String  $type          A type (doz, room, class)
	 *
	 * @return Array An array with the resource names
	 */
	private function getResource($resourcename, $type)
	{
		switch ($type)
		{
			case 'doz':
				$table = '#__thm_organizer_teachers';
				break;
			case 'room':
				$table = '#__thm_organizer_rooms';
				break;
			case 'class':
				$table = '#__thm_organizer_classes';
				break;				
		}

		$query = "SELECT name as oname ";
		$query .= "FROM $table";
		$query .= "WHERE gpuntisID ='$resourcename'";
		$hits  = $this->_JDA->query($query);

		return $hits;
	}
}
