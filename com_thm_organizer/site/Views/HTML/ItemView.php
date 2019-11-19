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

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;

/**
 * Class loads the resource form into display context. Specific resource determined by extending class.
 */
abstract class ItemView extends BaseHTMLView
{
	protected $_layout = 'item';

	public $form = null;

	public $item = null;

	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->item = $this->get('Item');
		$this->addDisclaimer();
		if (method_exists($this, 'setSubtitle'))
		{
			$this->setSubtitle();
		}
		if (method_exists($this, 'addSupplement'))
		{
			$this->addSupplement();
		}
		$this->modifyDocument();
		$defaultConstant = 'THM_ORGANIZER_' . strtoupper(str_replace('Item', '', $this->getName()));
		$itemName        = is_array($this->item['name']) ? $this->item['name']['value'] : $this->item['name'];
		HTML::setMenuTitle($defaultConstant, $itemName);
		unset($this->item['name']);

		// This has to be after the title has been set so that it isn't prematurely removed.
		$this->filterAttributes();
		parent::display($tpl);
	}

	/**
	 * Filters out invalid and true empty values. (0 is allowed.)
	 *
	 * @return void modifies the item
	 */
	protected function filterAttributes()
	{
		foreach ($this->item as $key => $attribute)
		{
			// Invalid for HTML Output
			if (!is_array($attribute)
				or !array_key_exists('value', $attribute)
				or !array_key_exists('label', $attribute)
				or $attribute['value'] === null
				or $attribute['value'] === ''
			)
			{
				unset($this->item[$key]);
			}
		}
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		HTML::_('bootstrap.framework');

		$document = Factory::getDocument();
		$document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/item.css');
		$document->addStyleSheet(Uri::root() . 'media/jui/css/bootstrap-extended.css');
	}

	/**
	 * Recursively outputs an array of items as a list.
	 *
	 * @param   array  $items  the items to be displayed.
	 *
	 * @return void outputs the items as an html list
	 */
	public function renderListValue($items, $url, $urlAttribs)
	{
		echo '<ul>';
		foreach ($items as $index => $item)
		{
			echo '<li>';
			if (is_array($item))
			{
				echo $index;
				$this->renderListValue($item, $url, $urlAttribs);
			}
			else
			{
				echo empty($url) ? $item : HTML::link(Route::_($url . $index), $item, $urlAttribs);
			}
			echo '</li>';
		}
		echo '</ul>';
	}
}
