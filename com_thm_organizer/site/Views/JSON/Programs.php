<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\JSON;

use Organizer\Helpers\Input;
use Organizer\Helpers\Programs as ProgramsHelper;

/**
 * Class answers dynamic (degree) program related queries
 */
class Programs extends BaseView
{
	/**
	 * loads model data into view context
	 *
	 * @return void
	 */
	public function display()
	{
		$function = Input::getTask();
		if (method_exists('Organizer\\Helpers\\Programs', $function))
		{
			echo json_encode(ProgramsHelper::$function(), JSON_UNESCAPED_UNICODE);
		}
		else
		{
			echo false;
		}
	}
}
