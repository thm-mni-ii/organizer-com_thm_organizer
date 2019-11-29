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

use Exception;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

/**
 * Class loads a filtered set of resources into the display context. Specific resource determined by extending class.
 */
abstract class TableView extends BaseHTMLView
{
	protected $_layout = 'table';

	public $activeFilters = null;

	private $columnCount = 0;

	public $filterForm = null;

	public $headers = null;

	public $pagination = null;

	public $rows = null;

	/**
	 * @var Registry
	 */
	public $state = null;

	/**
	 * Concrete classes are supposed to use this method to add a toolbar.
	 *
	 * @return void  sets context variables
	 */
	abstract protected function addToolBar();

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return true;
	}

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
			throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
		}

		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->setIndividualAttributes();
		$this->setHeaders();
		$this->setRows($this->get('Items'));
		$this->pagination = $this->get('Pagination');

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
	 * Adds styles and scripts to the document
	 *
	 * @return void  modifies the document
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		$document = Factory::getDocument();
		$document->addScript(Uri::root() . 'components/com_thm_organizer/js/postloader.js');
	}

	private function getHeaderCellOutput($cell, $dataClass, $colSpan = 0)
	{
		$attributes = "class=\"$dataClass\"";
		$attributes .= $colSpan ? " colspan=\"$colSpan\"" : '';
		$text       = !empty($cell['text']) ? $cell['text'] : '';

		return "<th $attributes>" . $text . '</th>';
	}

	/**
	 * Processes an individual list item resolving it to an array of table data values.
	 *
	 * @param   object  $resource  the resource whose information is displayed in the row
	 *
	 * @return array an array of property columns with their values
	 */
	abstract protected function getRow($resource);

	/**
	 * Creates a label with tooltip for the resource row.
	 *
	 * @param   object  $resource  the resource to be displayed in the row
	 *
	 * @return array  the label inclusive tooltip to be displayed
	 */
	abstract protected function getRowLabel($resource);

	/**
	 * Creates a table cell.
	 *
	 * @param   array  $data  the data used to structure the cell
	 *
	 * @return array structured cell information
	 */
	abstract protected function getCell($data);

	public function renderHeaders()
	{
		$levelOne = '';
		$levelTwo = '';

		foreach ($this->headers as $header)
		{
			$colspan   = 1;
			$dataClass = $header['text'] ? 'data-column' : 'resource-column';

			if (isset($header['columns']))
			{
				if ($header['columns'])
				{
					$colspan           = count($header['columns']);
					$this->columnCount += $colspan;
					foreach ($header['columns'] as $column)
					{
						$levelTwo .= $this->getHeaderCellOutput($column, $dataClass);
					}
				}
				else
				{
					$levelTwo .= $this->getHeaderCellOutput([], $dataClass);
				}

			}
			elseif ($header['text'])
			{
				$this->columnCount++;
			}

			$levelOne .= $this->getHeaderCellOutput($header, $dataClass, $colspan);
		}

		$columnClass = "columns-$this->columnCount";
		echo "<tr class=\"$columnClass\">$levelOne</tr>";

		if ($levelTwo)
		{
			echo "<tr class=\"level-2 $columnClass\">$levelTwo</tr>";
		}

		return;
	}

	public function renderRows()
	{
		$columnClass = "class=\"columns-$this->columnCount\"";

		foreach ($this->rows as $row)
		{
			echo "<tr $columnClass>";
			foreach ($row as $cell)
			{
				if (isset($cell['label']))
				{
					echo "<th class=\"resource-column\">{$cell['label']}</th>";
				}
				elseif (isset($cell['text']))
				{
					echo "<td class=\"data-column\">{$cell['text']}</td>";
				}
			}
			echo "</tr>";
		}
	}

	/**
	 * Sets the table header information
	 *
	 * @return void sets the headers property
	 */
	abstract protected function setHeaders();

	/**
	 * Sets class properties of inheriting views necessary for individualized table definitions.
	 *
	 * @return void sets properties of inheriting classes
	 */
	abstract protected function setInheritingProperties();

	/**
	 * Processes the resources for display in rows.
	 *
	 * @param   array  $resources  the resources to be displayed
	 *
	 * @return void processes the class rows property
	 */
	protected function setRows($resources)
	{
		$rows = [];

		foreach ($resources as $resource)
		{
			$rows[$resource->id] = $this->getRow($resource);
		}

		$this->rows = $rows;
	}
}
