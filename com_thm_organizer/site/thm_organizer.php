<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer;

defined('_JEXEC') or die;

require_once 'autoloader.php';

use Exception;
use Organizer\Helpers\OrganizerHelper;

try {
    OrganizerHelper::setUp();
} catch (Exception $exc) {
    throw $exc;
}
