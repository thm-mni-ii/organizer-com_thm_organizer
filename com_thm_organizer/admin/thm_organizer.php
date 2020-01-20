<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Admin;

require_once JPATH_COMPONENT_SITE . '/autoloader.php';

use Exception;
use Joomla\CMS\Factory;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

if (!Factory::getUser()->authorise('core.manage', 'com_thm_organizer'))
{
	throw new Exception(Languages::_('ORGANIZER_403'), 403);
}

OrganizerHelper::setUp();
