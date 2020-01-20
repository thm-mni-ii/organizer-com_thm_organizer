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

use Exception;
use Joomla\Registry\Registry;
use Organizer\Helpers\HTML;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

/**
 * Class loads a filtered set of resources into the display context. Specific resource determined by extending class.
 */
abstract class ListView extends BaseHTMLView
{
	protected $_layout = 'list';

	public $activeFilters = null;

	public $batch = [];

	public $filterForm = null;

	/**
	 * The header information to display indexed by the referenced attribute.
	 * @var array
	 */
	public $headers = [];

	public $items = null;

	public $pagination = null;

	protected $rowStructure = [];

	/**
	 * @var Registry
	 */
	public $state = null;

	/**
	 * Adds a toolbar and title to the view.
	 *
	 * @return void  sets context variables
	 */
	abstract protected function addToolBar();

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	abstract protected function allowAccess();

	/**
	 * Method to create a list output
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		if (!$this->allowAccess())
		{
			throw new Exception(Languages::_('ORGANIZER_401'), 401);
		}

		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->setHeaders();
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		if ($this->items)
		{
			$this->structureItems();
		}

		$this->addDisclaimer();
		if (method_exists($this, 'setSubtitle'))
		{
			$this->setSubtitle();
		}
		if (method_exists($this, 'addSupplement'))
		{
			$this->addSupplement();
		}

		$this->addToolBar();
		$this->addMenu();
		$this->modifyDocument();

		parent::display($tpl);
	}

	/**
	 * Generates a toggle for an attribute of an association
	 *
	 * @param   string  $controller    the name of the controller which executes the task
	 * @param   string  $columnOne     the name of the first identifying column
	 * @param   int     $valueOne      the value of the first identifying column
	 * @param   string  $columnTwo     the name of the second identifying column
	 * @param   int     $valueTwo      the value of the second identifying column
	 * @param   bool    $currentValue  the value currently set for the attribute (saves asking it later)
	 * @param   string  $tip           the tooltip
	 * @param   string  $attribute     the resource attribute to be changed (useful if multiple entries can be toggled)
	 *
	 * @return string  a HTML string
	 */
	protected function getAssocToggle(
		$controller,
		$columnOne,
		$valueOne,
		$columnTwo,
		$valueTwo,
		$currentValue,
		$tip,
		$attribute = null
	) {
		$url = Uri::base() . "?option=com_thm_organizer&task=$controller.toggle";
		$url .= "&$columnOne=$valueOne&$columnTwo=$valueTwo";
		$url .= $attribute ? "&attribute=$attribute" : '';

		$iconClass = empty($currentValue) ? 'checkbox-unchecked' : 'checkbox-checked';
		$icon      = '<span class="icon-' . $iconClass . '"></span>';

		$attributes = ['title' => $tip, 'class' => 'hasTooltip'];

		return HTML::_('link', $url, $icon, $attributes);
	}

	/**
	 * Generates a string containing attribute information for an HTML element to be output
	 *
	 * @param   mixed &$element  the element being processed
	 *
	 * @return string the HTML attribute output for the item
	 */
	public function getAttributesOutput(&$element)
	{
		$output = '';
		if (!is_array($element))
		{
			return $output;
		}

		$relevant = (!empty($element['attributes']) and is_array($element['attributes']));
		if ($relevant)
		{
			foreach ($element['attributes'] as $attribute => $attributeValue)
			{
				$output .= $attribute . '="' . $attributeValue . '" ';
			}
		}
		unset($element['attributes']);

		return $output;
	}

	/**
	 * Generates a toggle for a binary resource attribute
	 *
	 * @param   string  $controller    the name of the data management controller
	 * @param   int     $resourceID    the id of the resource
	 * @param   bool    $currentValue  the value currently set for the attribute (saves asking it later)
	 * @param   string  $tip           the tooltip
	 * @param   string  $attribute     the resource attribute to be changed (useful if multiple entries can be toggled)
	 *
	 * @return string  a HTML string
	 */
	protected function getToggle($controller, $resourceID, $currentValue, $tip, $attribute = null)
	{
		$url = Uri::base() . "?option=com_thm_organizer&task=$controller.toggle&id=$resourceID";
		$url .= $attribute ? "&attribute=$attribute" : '';

		$iconClass = empty($currentValue) ? 'checkbox-unchecked' : 'checkbox-checked';
		$icon      = '<span class="icon-' . $iconClass . '"></span>';

		$attributes = ['title' => $tip, 'class' => 'hasTooltip'];

		return HTML::_('link', $url, $icon, $attributes);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/list.css');
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	abstract protected function setHeaders();

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$index           = 0;
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$structuredItems[$index] = $this->structureItem($index, $item, $item->link);
			$index++;
		}

		$this->items = $structuredItems;
	}

	/**
	 * Processes an individual list item resolving it to an array of table data values.
	 *
	 * @param   mixed   $index  the row index, typically an int value, but can also be string
	 * @param   object  $item   the item to be displayed in a table row
	 * @param   string  $link   the link to the individual resource
	 *
	 * @return array an array of property columns with their values
	 */
	protected function structureItem($index, $item, $link = '')
	{
		$processedItem = [];

		foreach ($this->rowStructure as $property => $propertyType)
		{
			if ($property === 'checkbox')
			{
				$processedItem['checkbox'] = HTML::_('grid.id', $index, $item->id);
				continue;
			}

			if (!property_exists($item, $property))
			{
				continue;
			}

			// Individual code will be added to index later
			if ($propertyType === '')
			{
				$processedItem[$property] = $propertyType;
				continue;
			}

			if ($propertyType === 'link')
			{
				$processedItem[$property] = HTML::_('link', $link, $item->$property);
				continue;
			}

			if ($propertyType === 'value')
			{
				$processedItem[$property] = $item->$property;
				continue;
			}
		}

		return $processedItem;
	}
}
