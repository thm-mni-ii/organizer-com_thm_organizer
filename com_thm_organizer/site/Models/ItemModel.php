<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\FormModel;
use Organizer\Helpers\Access;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Named;

/**
 * Class loads non-item-specific form data.
 */
class ItemModel extends BaseModel
{
    use Named;

    protected $option = 'com_thm_organizer';

    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->setContext();
    }

    /**
     * Provides a strict access check which can be overwritten by extending classes.
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowView()
    {
        return Access::isAdmin();
    }
}
