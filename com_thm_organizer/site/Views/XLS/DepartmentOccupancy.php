<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\XLS;

use Organizer\Views\BaseView;

/**
 * Class instantiates and renders an XLS File with the department statistics.
 */
class DepartmentOccupancy extends BaseView
{
	use PHPExcelDependent;

	/**
	 * Sets context variables and renders the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel();

		require_once __DIR__ . '/tmpl/document.php';
		$export = new \DepartmentOccupancy_XLS($model);
		$export->render();
		ob_flush();
	}
}
