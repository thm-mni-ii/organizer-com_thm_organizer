<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher_Edit
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/edit.php';

/**
 * Class THM_OrganizerModelTeacher for component com_thm_organizer
 *
 * Class provides methods to deal with teacher
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelTeacher_Edit extends THM_OrganizerModelEdit
{
	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
	}
}
