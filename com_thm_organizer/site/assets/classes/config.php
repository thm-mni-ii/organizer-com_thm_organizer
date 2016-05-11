<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        MySchedConfig
 * @description MySchedConfig file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

// Definiert Basepath
define('B', dirname(__FILE__));

/**
 * Class MySchedConfig for component com_thm_organizer
 *
 * Class provides information about the database and estudy
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class MySchedConfig
{
    /**
     * The default folder when downloading schedules
     */
    public $pdf_downloadFolder = '';

    /**
     * Whether or not HTTPS is required
     */
    public $requireHTTPS = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Hier werden die PDF Dateien gespeichert (Muss nicht im Webroot liegen!)
        $this->pdf_downloadFolder = JFactory::getApplication()->get('tmp_path') . DIRECTORY_SEPARATOR;
    }
}
