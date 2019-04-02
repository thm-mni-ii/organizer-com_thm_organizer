<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class instantiates a \JTable Object associated with the user_lessons table.
 */
class THM_OrganizerTableUser_Lessons extends \Joomla\CMS\Table\Table
{
    /**
     * fields get encoded by binding, when values are arrays
     *
     * @var array
     */
    protected $_jsonEncode = ['configuration'];

    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_user_lessons', 'id', $dbo);
    }
}
