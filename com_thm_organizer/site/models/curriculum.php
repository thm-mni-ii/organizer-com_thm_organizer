<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelIndex
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class creates a model
 *
 * @category    Joomla.Component.Site
 * @package     thm_urriculum
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelCurriculum extends JModel
{
    /**
     * Gets the name of the program selected
     * 
     * @return  string  the name of the program
     */
    public function getProgramName($programID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("CONCAT(p.subject, ' (', d.abbreviation, ' ', p.version, ')')");
        $query->from('#__thm_organizer_programs AS p')->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->where("p.id = '$programID'");
        $dbo->setQuery((string) $query);
        $programName = $dbo->loadResult();
        return empty($programName)? '' : $programName;
    }
}
