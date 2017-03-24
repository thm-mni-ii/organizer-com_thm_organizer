<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewSchedule_Export
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSchedule_Export extends JViewLegacy
{
	public $document;

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

		$model      = $this->getModel();
		$parameters = $model->parameters;
		$grid       = empty($model->grid)? null : $model->grid;
		$lessons    = $model->lessons;

		$fileName = $parameters['documentFormat'] . '_' . $parameters['displayFormat'] . '_' . $parameters['pdfWeekFormat'];
		require_once __DIR__ . "/tmpl/$fileName.php";
		new THM_OrganizerTemplateSchedule_Export_PDF($parameters, $lessons, $grid);
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
			JError::raiseWarning('COM_THM_ORGANIZER_MESSAGE_TCPDF_LIBRARY_NOT_INSTALLED');

			return false;
		}

		return true;
	}
}
