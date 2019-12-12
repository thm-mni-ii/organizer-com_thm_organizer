<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\JSON;

use Organizer\Models\SubjectItem as SubjectItemModel;

/**
 * Class loads the subject into the display context.
 */
class SubjectItem extends BaseView
{
	/**
	 * loads model data into view context
	 *
	 * @return void
	 */
	public function display()
	{
		$model = new SubjectItemModel;
		echo json_encode($model->get('Item'), JSON_UNESCAPED_UNICODE);
	}
}
