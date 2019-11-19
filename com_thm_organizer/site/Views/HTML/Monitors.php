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

/**
 * Class loads persistent information a filtered set of monitors into the display context.
 */
class Monitors extends ListView
{
	const DAILY = 1, MIXED = 2, CONTENT = 3, EVENT_LIST = 4;

	public $displayBehaviour = [];

	protected $rowStructure = [
		'checkbox'    => '',
		'name'        => 'link',
		'ip'          => 'link',
		'useDefaults' => 'value',
		'display'     => 'link',
		'content'     => 'link'
	];

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 */
	public function __construct($config = array())
	{
		$this->displayBehaviour[self::DAILY]      = Languages::_('THM_ORGANIZER_DAILY_PLAN');
		$this->displayBehaviour[self::MIXED]      = Languages::_('THM_ORGANIZER_MIXED_PLAN');
		$this->displayBehaviour[self::CONTENT]    = Languages::_('THM_ORGANIZER_CONTENT_DISPLAY');
		$this->displayBehaviour[self::EVENT_LIST] = Languages::_('THM_ORGANIZER_EVENT_LIST');

		parent::__construct($config);
	}

	/**
	 * Creates joomla toolbar elements
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_MONITORS'), 'screen');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'monitor.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'monitor.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'monitor.delete',
			true
		);
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return Access::allowFMAccess();
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [];

		$headers['checkbox']    = HTML::_('grid.checkall');
		$headers['name']        = HTML::sort('ROOM', 'r.name', $direction, $ordering);
		$headers['ip']          = HTML::sort('IP', 'm.ip', $direction, $ordering);
		$headers['useDefaults'] = HTML::sort('DEFAULT_SETTINGS', 'm.useDefaults', $direction, $ordering);
		$headers['display']     = Languages::_('THM_ORGANIZER_DISPLAY_BEHAVIOUR');
		$headers['content']     = HTML::sort('DISPLAY_CONTENT', 'm.content', $direction, $ordering);

		return $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$index           = 0;
		$structuredItems = [];

		$params       = Input::getParams();
		$displayParam = $params->get('display');
		$contentParam = $params->get('content');

		foreach ($this->items as $item)
		{
			$tip               = Languages::_('THM_ORGANIZER_TOGGLE_COMPONENT_SETTINGS');
			$item->useDefaults = $this->getToggle($item->id, $item->useDefaults, 'monitor', $tip);

			if (empty($item->useDefaults))
			{
				$item->display = $this->displayBehaviour[$item->display];
			}
			else
			{
				$item->display = $this->displayBehaviour[$displayParam];
				$item->content = $contentParam;
			}

			$structuredItems[$index] = $this->structureItem($index, $item, $item->link);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
