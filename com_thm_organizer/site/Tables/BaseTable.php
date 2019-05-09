<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

defined('_JEXEC') or die;

use InvalidArgumentException;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\OrganizerHelper;
use RuntimeException;
use UnexpectedValueException;

/**
 * Abstract class for use by resource tables whose access rules are to be stored in the Joomla assets table.
 */
abstract class BaseTable extends Table
{
    /**
     * Method to load a row from the database by primary key and bind the fields to the Table instance properties.
     *
     * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
     *                           If not set the instance property value is used.
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful, otherwise false
     */
    public function load($keys = null, $reset = true)
    {
        try {
            return parent::load($keys, $reset);
        } catch (InvalidArgumentException $exception) {
            OrganizerHelper::message($exception->getMessage(), 'error');

            return false;
        } catch (RuntimeException $exception) {
            OrganizerHelper::message($exception->getMessage(), 'error');

            return false;
        } catch (UnexpectedValueException $exception) {
            OrganizerHelper::message($exception->getMessage(), 'error');

            return false;
        }
    }
}
