<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewScheduler
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewScheduler extends JViewLegacy
{
    /**
     * Method to get extra
     *
     * @param   String  $tpl  template
     *
     * @return void
     *
     * @see JViewLegacy::display()
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');
        
        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        $activeItemLanguage = $active->language;
        
        /* Set your tag */
        $tag = $activeItemLanguage;
        /* Set your extension (component or module) */
        $extension = "com_thm_organizer";
        /* Get the Joomla core language object */
        $language = JFactory::getLanguage();
        /* Set the base directory for the language */
        $base_dir = JPATH_SITE;
        /* Load the language */
        
        if ($tag === "en-GB")
        {
            $language->load($extension, $base_dir, $tag, true);
            $this->languageTag = "en";
        }
        else
        {
            $this->languageTag = "de";
        }
        
        $libraryInstalled = jimport('extjs4.extjs4');
        if (!$libraryInstalled)
        {
            return JError::raiseWarning(404, JText::_("COM_THM_ORGANIZER_EXTJS4_LIBRARY_NOT_INSTALLED"));
        }

        // Check wether the FPDF library is installed
        $this->FPDFInstalled = jimport('fpdf.fpdf');

        // Check wether the iCalcreator library is installed
        $this->iCalcreatorInstalled = jimport('iCalcreator.iCalcreator');

        // Check wether the PHPExcel library is installed
        $this->PHPExcelInstalled = jimport('PHPExcel.PHPExcel');

        $doc = JFactory::getDocument();
        $doc->addStyleSheet($this->baseurl . '/libraries/extjs4/css/ext-all-gray.css');
        $doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");

        $schedulerModel = $this->getModel();
        $eventModel = JModelLegacy::getInstance('event_manager', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));
        $ajaxModel = JModelLegacy::getInstance('Ajaxhandler', 'thm_organizerModel', array('ignore_request' => false));

        $menuparams = JFactory::getApplication()->getParams();
        $this->canWriteEvents = $eventModel->canWrite;
        $this->jsid = $schedulerModel->getSessionID();

        $this->searchModuleID = JRequest::getString('moduleID');

        $displayModuleNumber = (boolean) $menuparams->get("displayModuleNumber", true);
        $this->displayModuleNumber = $displayModuleNumber;
        
        $deltaDisplayDays = (int) $menuparams->get("deltaDisplayDays", 14);
        if (is_int($deltaDisplayDays))
        {
            $this->deltaDisplayDays = $deltaDisplayDays;
        }
        else
        {
            $this->deltaDisplayDays = 14;
        }
        
        $site = new JSite;
        $menu = $site->getMenu();
        $menuid = JRequest::getInt("menuID", 0);

        if ($menuid === 0 && !is_null($menu->getActive()))
        {
            $menuid = $menu->getActive()->id;
        }

        $this->joomlaItemid = $menuid;

        $schedulerFromMenu = null;
        $schedule = null;

        if ($menuid != 0 || !is_null($menu->getActive()))
        {
            $schedulerFromMenu = true;
        }
        elseif (JRequest::getString('scheduleID'))
        {
            $schedulerFromMenu = false;
        }
        else
        {
            $schedulerFromMenu = null;
        }

        $this->schedulerFromMenu = $schedulerFromMenu;

        if ($schedulerFromMenu === true) // Called via menu item
        {
            try
            {
                $publicDefaultIDArray = (array) json_decode($menuparams->get("publicDefaultID"));
            }
            catch (Exception $e)
            {
                $publicDefaultIDArray = array();
            }

            $schedule = $schedulerModel->getActiveSchedule($menuparams->get("departmentSemesterSelection"));
        }
        elseif ($schedulerFromMenu === false) // Called via link
        {
            $requestSchedulerID = JRequest::getInt("scheduleID", null);
            if (isset($requestSchedulerID))
            {
                $schedule = $schedulerModel->getActiveScheduleByID($requestSchedulerID);
            }
            else
            {
                return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_NO_ACTIVE_SCHEDULE'));
            }

        }
        else
        {
            return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_NO_ACTIVE_SCHEDULE'));
        }

        if (is_object($schedule) AND is_string($schedule->schedule))
        {
            $scheduleData = json_decode($schedule->schedule);

            // To save memory unset schedule
            unset($schedule->schedule);
            
            if ($scheduleData != null)
            {
                $periods = $scheduleData->periods;
                unset($scheduleData->periods);
                $degrees = $scheduleData->degrees;
                unset($scheduleData->degrees);
                $rooms = $scheduleData->rooms;
                unset($scheduleData->rooms);
                $roomTypes = $scheduleData->roomtypes;
                unset($scheduleData->roomtypes);
                $subjects = $scheduleData->subjects;
                unset($scheduleData->subjects);
                $teachers = $scheduleData->teachers;
                unset($scheduleData->teachers);
                if (isset($scheduleData->pools))
                {
                    $modules = $scheduleData->pools;
                }
                else
                {
                    $modules = $scheduleData->modules;
                }
                unset($scheduleData->modules);
                $calendar = $scheduleData->calendar;
                unset($scheduleData->calendar);
                $scheduleLessons = $scheduleData->lessons;
                unset($scheduleData->lessons);
                $scheduleFields = $scheduleData->fields;
                unset($scheduleData->fields);

                if (!is_object($periods) OR !is_object($degrees) OR !is_object($rooms)
                 OR !is_object($roomTypes) OR !is_object($subjects) OR !is_object($teachers)
                 OR !is_object($modules) OR !is_object($calendar) OR !is_object($scheduleLessons)
                 OR !is_object($scheduleFields))
                {
                    return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_IMPORTANT_DATA_MISSING'));
                }
            }
            else
            {
                // Cant decode json
                return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_FLAWED'));
            }
        }
        else
        {
            return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_NO_ACTIVE_SCHEDULE'));
        }

        $this->semesterID = $schedule->id;
        $this->semAuthor = "";

        $this->departmentAndSemester = $schedule->departmentname . ";" . $schedule->semestername . ";" .
                                        $schedule->startdate . ";" . $schedule->enddate;

        $this->semesterName = $this->departmentAndSemester;

        $schedulearr = array();

        $periods->length = count((array) $periods);

        $this->requestTeacherGPUntisIDs = JRequest::getVar('teacherID', array(), 'default', 'array');
        $this->requestRoomGPUntisIDs = JRequest::getVar('roomID', array(), 'default', 'array');
        $this->requestPoolGPUntisIDs = JRequest::getVar('poolID', array(), 'default', 'array');
        $this->requestSubjectGPUntisIDs = JRequest::getVar('subjectID', array(), 'default', 'array');

        foreach ($periods as $period)
        {
            if (isset($period->starttime) AND is_string($period->starttime))
            {
                $period->starttime = wordwrap($period->starttime, 2, ':', 1);
            }
            if (isset($period->endtime) AND is_string($period->endtime))
            {
                $period->endtime = wordwrap($period->endtime, 2, ':', 1);
            }
        }

//         $schedulearr['CurriculumColors'] = $schedulerModel->getCurriculumModuleColors();
        $schedulearr['CurriculumColors'] = array();

        $schedulearr["Grid.load"] = $periods;

        $schedulearr["Calendar"] = $calendar;

        $schedulearr["Events.load"] = $ajaxModel->executeTask("Events.load");

        $schedulearr["UserSchedule.load"] = array();

        if ($menuparams->get("loadLessonsOnStartUp") === null)
        {
            $this->loadLessonsOnStartUp = true;
        }
        else
        {
            $this->loadLessonsOnStartUp = (bool) $menuparams->get("loadLessonsOnStartUp");
        }

        if ($this->loadLessonsOnStartUp == true)
        {
            $lessons = array();

            foreach ($calendar as $dateKey => $dateValue)
            {
                if (is_object($dateValue))
                {
                    foreach ($dateValue as $blockKey => $blockValue)
                    {
                        foreach ($blockValue as $lessonKey => $lessonValue)
                        {
                            $currentDate = new DateTime($dateKey);
                            $dow = strtolower($currentDate->format("l"));

                            $lessonID = $lessonKey . $blockKey . $dow;

                            if (!array_key_exists($lessonID, $lessons))
                            {
                                $lessons[$lessonID] = clone $scheduleLessons->{$lessonKey};
                                $lessons[$lessonID]->lessonKey = $lessonKey;
                                $lessons[$lessonID]->block = $blockKey;
                                $lessons[$lessonID]->dow = $dow;
                                if (isset($lessons[$lessonID]->pools))
                                {
                                    $lessons[$lessonID]->modules = $lessons[$lessonID]->pools;
                                }
                            }

                            if (!isset($lessons[$lessonID]->calendar))
                            {
                                $lessons[$lessonID]->calendar = array();
                            }

                            $lessons[$lessonID]->calendar[$dateKey][$blockKey]["lessonData"] = clone $lessonValue;
                        }
                    }
                }
            }

            $schedulearr["Lessons"] = $lessons;
        }

        $schedulearr["UserSchedule.load"]["respChanges"] = $ajaxModel->executeTask("UserSchedule.load", array("username" => "respChanges"));

        $schedulearr["ScheduleDescription.load"] = new stdClass;
        $schedulearr["ScheduleDescription.load"]->data = $schedule;

        $this->startup = rawurlencode(json_encode($schedulearr));

        parent::display($tpl);
    }
}
