<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewSchedule_Export
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSchedule_PDF extends JViewLegacy
{
	public $document;

	private $cellLineHeight = 3;

	private $dataWidth = 45;

	private $padding = 2;

	private $timeWidth = 11;

	/**
	 * Method to get extra
	 *
	 * @param string $tpl template
	 *
	 * @return  mixed  false on error, otherwise void
	 */
	public function display($tpl = null)
	{
		$libraryInstalled = $this->checkLibraries();

		if (!$libraryInstalled)
		{
			return false;
		}

		$model = $this->getModel();
		$parameters = $model->parameters;
		$grid = $model->grid;
		$lessons = $model->lessons;

		switch ($parameters['paperFormat'])
		{
			case 'A3':
				require_once __DIR__ . '/tmpl/a3.php';
				new THM_OrganizerTemplateSchedulePDFA3($parameters, $grid, $lessons);
				break;

			case 'A4':
			default:
				require_once __DIR__ . '/tmpl/a4.php';
				new THM_OrganizerTemplateSchedulePDFA4($parameters, $grid, $lessons);
				break;

		}
	}

	/**
	 * Imports libraries and sets library variables
	 *
	 * @return  void
	 */
	private function checkLibraries()
	{
		$this->compiler = jimport('tcpdf.tcpdf');

		if (!$this->compiler)
		{
			JError::raiseWarning('COM_THM_ORGANIZER_MESSAGE_FPDF_LIBRARY_NOT_INSTALLED');

			return false;
		}

		return true;
	}
}
