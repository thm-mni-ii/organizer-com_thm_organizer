<?php
/**
 * @package     [Joomla.Site | Joomla.Administrator]
 * @subpackage  [extension type]_thm_[extension name]
 * @name        [joomla type]
 * @description [description of the file and/or its purpose]
 * @author      [first name] [last name] [Email]
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
// no direct access
defined('_JEXEC') or die;
jimport( 'joomla.application.component.table' );
class thm_organizerTabledeltas extends JTable
{
    /**
     * @param JDatabase	A database connector object
     */
    public function __construct(&$dbo){ parent::__construct('#__thm_organizer_deltas', 'id', $dbo); }

}