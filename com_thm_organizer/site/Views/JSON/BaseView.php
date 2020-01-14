<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\JSON;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Named;
use Organizer\Helpers\OrganizerHelper;

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 */
abstract class BaseView extends CMSObject
{
	use Named;

	/**
	 * The base path of the view
	 *
	 * @var    string
	 */
	protected $_basePath = null;

	/**
	 * The base path of the site itself
	 *
	 * @var string
	 */
	private $baseurl;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 */
	public function __construct($config = array())
	{
		// Set the view name
		if (empty($this->name))
		{
			$this->getName();
		}

		// Set a base path for use by the view
		if (array_key_exists('base_path', $config))
		{
			$this->_basePath = $config['base_path'];
		}
		else
		{
			$this->_basePath = JPATH_COMPONENT;
		}

		$this->baseurl = Uri::base(true);
	}

	/**
	 * Display the view output
	 */
	abstract public function display();
}
