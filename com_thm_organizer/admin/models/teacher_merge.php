<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Loads teacher entry information to be merged
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelTeacher_Merge extends JModel
{
    /**
     * Array holding teacher entry information
     *
     * @var array
     */
    public $dteacherInformation = null;

    /**
     * Pulls a list of teacher data from the database
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('t.id, t.gpuntisID, surname, forename, username, fieldID, field, title');
        $query->from('#__thm_organizer_teachers AS t');
        $query->leftJoin('#__thm_organizer_fields AS f ON t.fieldID = f.id');

        $cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";
        $query->where("t.id IN ( $cids )");

        $query->order('t.id ASC');

        $dbo->setQuery((string) $query);
        $this->teacherInformation = $dbo->loadAssocList();
    }
}
