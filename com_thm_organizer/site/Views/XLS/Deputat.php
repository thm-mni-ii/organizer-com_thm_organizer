<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\XLS;

use Organizer\Views\BaseView;

/**
 * Class creates a XLS file for the display of the filtered schedule information.
 */
class Deputat extends BaseView
{
	use PHPExcelDependent;

	/**
	 * Sets context variables and renders the view.
	 *
	 * @param   string  $tpl  template
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$templateNameParameter = 'thm';
		$fileName              = 'deputat_' . $templateNameParameter;
		require_once __DIR__ . "/tmpl/$fileName.php";
		$model  = $this->getModel();
		$export = new \THM_OrganizerTemplateDeputat($model);
		$export->render();
		ob_flush();
	}
}
