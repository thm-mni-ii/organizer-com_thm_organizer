<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        category edit model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('thm_core.edit.model');

/**
 * Class retrieving category item information
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelCategory_Edit extends THM_CoreModelEdit
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * Method to get a table object, load it if necessary. Can't be generalized because of irregular english plural
     * spelling. :(
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  JTable object
     */
    public function getTable($name = 'categories', $prefix = 'thm_organizerTable', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }
}
