<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

/**
 * Class standardizes the getName function across classes.
 */
trait Named
{
    /**
     * The name of the object
     */
    protected $name = null;

    /**
     * Method to get the object name
     *
     * The model name by default parsed using the classname, or it can be set
     * by passing a $config['name'] in the class constructor
     *
     * @return  string  The name of the model
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = OrganizerHelper::getClass($this);
        }

        return $this->name;
    }
}