<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use JHtmlSidebar;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Can;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Views\BaseView;

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 */
abstract class BaseHTMLView extends BaseView
{
	public $disclaimer = '';

	public $submenu = '';

	public $subtitle = '';

	public $supplement = '';

	/**
	 * Adds a legal disclaimer to the view.
	 *
	 * @return void modifies the class property disclaimer
	 */
	protected function addDisclaimer()
	{
		if ($this->clientContext === self::BACKEND)
		{
			return;
		}

		$thisClass = OrganizerHelper::getClass($this);
		if (!in_array($thisClass, ['Curriculum', 'SubjectItem', 'Subjects']))
		{
			return;
		}

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/disclaimer.css');

		$attributes = ['target' => '_blank'];

		$lsfLink = HTML::link(
			'https://studien-sb-service.th-mittelhessen.de/docu/online.html',
			Languages::_('ORGANIZER_DISCLAIMER_LSF_TITLE'),
			$attributes
		);
		$ambLink = HTML::link(
			'http://www.thm.de/amb/pruefungsordnungen',
			Languages::_('ORGANIZER_DISCLAIMER_AMB_TITLE'),
			$attributes
		);
		$poLink  = HTML::link(
			'http://www.thm.de/site/studium/sie-studieren/pruefungsordnung.html',
			Languages::_('ORGANIZER_DISCLAIMER_PO_TITLE'),
			$attributes
		);

		$disclaimer = '<div class="disclaimer">';
		$disclaimer .= '<h4>' . Languages::_('ORGANIZER_DISCLAIMER_LEGAL') . '</h4>';
		$disclaimer .= '<ul>';
		$disclaimer .= '<li>' . sprintf(Languages::_('ORGANIZER_DISCLAIMER_LSF_TEXT'), $lsfLink) . '</li>';
		$disclaimer .= '<li>' . sprintf(Languages::_('ORGANIZER_DISCLAIMER_AMB_TEXT'), $ambLink) . '</li>';
		$disclaimer .= '<li>' . sprintf(Languages::_('ORGANIZER_DISCLAIMER_PO_TEXT'), $poLink) . '</li>';
		$disclaimer .= '</ul>';
		$disclaimer .= '</div>';

		$this->disclaimer = $disclaimer;
	}

	/**
	 * Adds the component menu to the view.
	 *
	 * @return void
	 */
	protected function addMenu()
	{
		if ($this->clientContext == self::FRONTEND)
		{
			return;
		}

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/sidebar.css');

		$viewName = strtolower($this->get('name'));

		JHtmlSidebar::addEntry(
			Languages::_('ORGANIZER'),
			'index.php?option=com_thm_organizer&amp;view=organizer',
			$viewName == 'organizer'
		);

		if (Can::scheduleTheseDepartments())
		{
			$spanText = '<span class="menu-spacer">' . Languages::_('ORGANIZER_SCHEDULING') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);

			$scheduling = [];

			$scheduling[Languages::_('ORGANIZER_GROUPS')]     = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=groups',
				'active' => $viewName == 'groups'
			];
			$scheduling[Languages::_('ORGANIZER_CATEGORIES')] = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=categories',
				'active' => $viewName == 'categories'
			];
			$scheduling[Languages::_('ORGANIZER_SCHEDULES')]  = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=schedules',
				'active' => $viewName == 'schedules'
			];
			$scheduling[Languages::_('ORGANIZER_EVENTS')]     = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=events',
				'active' => $viewName == 'events'
			];
			ksort($scheduling);

			// Uploading a schedule should always be the first menu item and will never be the active submenu item.
			$prepend    = [
				Languages::_('ORGANIZER_SCHEDULE_UPLOAD') . ' <span class="icon-upload"></span>' => [
					'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_edit',
					'active' => false
				]
			];
			$scheduling = $prepend + $scheduling;
			foreach ($scheduling as $key => $value)
			{
				JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
			}
		}

		if (Can::documentTheseDepartments())
		{
			$spanText = '<span class="menu-spacer">' . Languages::_('ORGANIZER_DOCUMENTATION') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);

			$documentation = [];

			$documentation[Languages::_('ORGANIZER_POOLS')]    = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=pools',
				'active' => $viewName == 'pools'
			];
			$documentation[Languages::_('ORGANIZER_PROGRAMS')] = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=programs',
				'active' => $viewName == 'programs'
			];
			$documentation[Languages::_('ORGANIZER_SUBJECTS')] = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=subjects',
				'active' => $viewName == 'subjects'
			];
			ksort($documentation);
			foreach ($documentation as $key => $value)
			{
				JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
			}
		}

		if (Can::manage('courses'))
		{
			$spanText = '<span class="menu-spacer">' . Languages::_('ORGANIZER_EVENT_MANAGEMENT') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);

			$courseItems                                         = [];
			$courseItems[Languages::_('ORGANIZER_COURSES')]      = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=courses',
				'active' => $viewName == 'courses'
			];
			$courseItems[Languages::_('ORGANIZER_PARTICIPANTS')] = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=participants',
				'active' => $viewName == 'participants'
			];
			$courseItems[Languages::_('ORGANIZER_UNITS')]        = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=units',
				'active' => $viewName == 'units'
			];
			ksort($courseItems);

			foreach ($courseItems as $key => $value)
			{
				JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
			}
		}

		if (Can::manage('persons'))
		{
			$spanText = '<span class="menu-spacer">' . Languages::_('ORGANIZER_HUMAN_RESOURCES') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);
			JHtmlSidebar::addEntry(
				Languages::_('ORGANIZER_PERSONS'),
				'index.php?option=com_thm_organizer&amp;view=persons',
				$viewName == 'persons'
			);
		}

		if (Can::manage('facilities'))
		{
			$spanText = '<span class="menu-spacer">' . Languages::_('ORGANIZER_FACILITY_MANAGEMENT') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);

			$fmEntries = [];

			$fmEntries[Languages::_('ORGANIZER_BUILDINGS')] = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=buildings',
				'active' => $viewName == 'buildings'
			];
			$fmEntries[Languages::_('ORGANIZER_CAMPUSES')]  = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=campuses',
				'active' => $viewName == 'campuses'
			];
			$fmEntries[Languages::_('ORGANIZER_MONITORS')]  = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=monitors',
				'active' => $viewName == 'monitors'
			];
			$fmEntries[Languages::_('ORGANIZER_ROOMS')]     = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=rooms',
				'active' => $viewName == 'rooms'
			];
			$fmEntries[Languages::_('ORGANIZER_ROOMTYPES')] = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=roomtypes',
				'active' => $viewName == 'roomtypes'
			];
			ksort($fmEntries);
			foreach ($fmEntries as $key => $value)
			{
				JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
			}
		}

		if (Can::administrate())
		{
			$spanText = '<span class="menu-spacer">' . Languages::_('ORGANIZER_ADMINISTRATION') . '</span>';
			JHtmlSidebar::addEntry($spanText, '', false);

			$adminEntries = [];

			$adminEntries[Languages::_('ORGANIZER_DEPARTMENTS')] = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=departments',
				'active' => $viewName == 'departments'
			];
			$adminEntries[Languages::_('ORGANIZER_COLORS')]      = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=colors',
				'active' => $viewName == 'colors'
			];
			$adminEntries[Languages::_('ORGANIZER_DEGREES')]     = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=degrees',
				'active' => $viewName == 'degrees'
			];
			$adminEntries[Languages::_('ORGANIZER_FIELDS')]      = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=fields',
				'active' => $viewName == 'fields'
			];
			$adminEntries[Languages::_('ORGANIZER_GRIDS')]       = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=grids',
				'active' => $viewName == 'grids'
			];
			$adminEntries[Languages::_('ORGANIZER_HOLIDAYS')]    = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=holidays',
				'active' => $viewName == 'holidays'
			];
			$adminEntries[Languages::_('ORGANIZER_METHODS')]     = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=methods',
				'active' => $viewName == 'methods'
			];
			$adminEntries[Languages::_('ORGANIZER_RUNS')]        = [
				'url'    => 'index.php?option=com_thm_organizer&amp;view=runs',
				'active' => $viewName == 'runs'
			];
			ksort($adminEntries);
			foreach ($adminEntries as $key => $value)
			{
				JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
			}
		}

		$this->submenu = JHtmlSidebar::render();
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		$document = Factory::getDocument();
		$document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/global.css');
		$document->addStyleSheet(Uri::root() . 'media/jui/css/bootstrap-extended.css');
		$document->setCharset('utf-8');

		HTML::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'right'));
	}
}
