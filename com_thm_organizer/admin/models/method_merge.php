<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMethod_Merge
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/form.php';

/**
 * Loads method entry information to be merged
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMethod_Merge extends THM_OrganizerModelForm
{
	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}
}
