<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Pools;
use Organizer\Helpers\Programs;

/**
 * Class loads persistent information a filtered set of subjects into the display context.
 */
class Subjects extends ListView
{
	const COORDINATES = 1, TEACHES = 2;

	private $documentAccess = false;

	private $params = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->params = Input::getParams();
	}

	/**
	 * Sets Joomla view title and action buttons
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$resourceName = '';
		if ($this->clientContext == self::FRONTEND)
		{
			if ($programID = Input::getInt('programID'))
			{
				$resourceName = Programs::getName($programID);
			}
			if ($poolID = $this->state->get('calledPoolID', 0))
			{
				$poolName     = Pools::getName($poolID);
				$resourceName .= empty($resourceName) ? $poolName : ", $poolName";
			}
		}

		HTML::setMenuTitle('THM_ORGANIZER_SUBJECTS', $resourceName, 'book');
		$toolbar = Toolbar::getInstance();
		if ($this->documentAccess)
		{
			$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'subject.add', false);
			$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'subject.edit', true);
			$toolbar->appendButton(
				'Standard',
				'upload',
				Languages::_('THM_ORGANIZER_IMPORT_LSF'),
				'subject.import',
				true
			);
			$toolbar->appendButton(
				'Confirm',
				Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
				'delete',
				Languages::_('THM_ORGANIZER_DELETE'),
				'subject.delete',
				true
			);
		}
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		$this->documentAccess = Access::allowDocumentAccess();

		return $this->clientContext === self::BACKEND ? $this->documentAccess : true;
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$direction = $this->state->get('list.direction');
		$ordering  = $this->state->get('list.ordering');
		$headers   = [];

		if ($this->clientContext === self::BACKEND OR $this->documentAccess)
		{
			$headers['checkbox'] = '';
		}

		$headers['name']         = HTML::sort('NAME', 'name', $direction, $ordering);
		$headers['code']         = HTML::sort('MODULE_CODE', 'code', $direction, $ordering);
		$headers['persons']      = Languages::_('THM_ORGANIZER_TEACHERS');
		$headers['creditpoints'] = Languages::_('THM_ORGANIZER_CREDIT_POINTS');

		return $headers;
	}

	/**
	 * Retrieves the person texts and formats them according to their roles for the subject being iterated
	 *
	 * @param   object  $subject  the subject being iterated
	 *
	 * @return string
	 */
	private function getPersonDisplay($subject)
	{
		$names = [];
		foreach ($subject->persons as $personID => $person)
		{
			$name = $this->getPersonText($person);

			$roles = [];
			if (isset($person['role'][self::COORDINATES]))
			{
				$roles[] = Languages::_('THM_ORGANIZER_COORDINATOR_ABBR');
			}
			if (isset($person['role'][self::TEACHES]))
			{
				$roles[] = Languages::_('THM_ORGANIZER_TEACHER_ABBR');
			}

			$name    .= ' (' . implode(', ', $roles) . ')';
			$names[] = $name;
		}

		return implode('<br>', $names);
	}

	/**
	 * Generates the person text (surname(, forename)?( title)?) for the given person
	 *
	 * @param   array  $person  the subject person
	 *
	 * @return string
	 */
	public function getPersonText($person)
	{
		$showTitle = (bool) $this->params->get('showTitle');

		$text = $person['surname'];

		if (!empty($person['forename']))
		{
			$text .= ", {$person['forename']}";
		}

		if ($showTitle and !empty($person['title']))
		{
			$text .= " {$person['title']}";
		}

		return $text;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$backend         = $this->clientContext === self::BACKEND;
		$editLink        = 'index.php?option=com_thm_organizer&view=subject_edit&id=';
		$index           = 0;
		$itemLink        = 'index.php?option=com_thm_organizer&view=subject_item&id=';
		$structuredItems = [];

		foreach ($this->items as $subject)
		{
			$access   = Access::allowSubjectAccess($subject->id);
			$checkbox = $access ? HTML::_('grid.id', $index, $subject->id) : '';
			$thisLink = ($backend and $access) ? $editLink . $subject->id : $itemLink . $subject->id;

			$structuredItems[$index] = [];

			if ($backend OR $this->documentAccess)
			{
				$structuredItems[$index]['checkbox'] = $checkbox;
			}

			$structuredItems[$index]['name']         = HTML::_('link', $thisLink, $subject->name);
			$structuredItems[$index]['code']         = HTML::_('link', $thisLink, $subject->code);
			$structuredItems[$index]['persons']      = $this->getPersonDisplay($subject);
			$structuredItems[$index]['creditpoints'] = empty($subject->creditpoints) ? '' : $subject->creditpoints;

			$index++;
		}

		$this->items = $structuredItems;
	}
}
