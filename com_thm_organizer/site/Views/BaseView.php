<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views;

use Exception;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Named;
use Organizer\Helpers\OrganizerHelper;

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 */
abstract class BaseView extends HtmlView
{
	use Named;

	const BACKEND = true, FRONTEND = false;

	public $clientContext;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->clientContext = OrganizerHelper::getApplication()->isClient('administrator');
	}

	/**
	 * Sets the layout name to use
	 *
	 * @param   string  $layout  The layout name or a string in format <template>:<layout file>
	 *
	 * @return  string  Previous value.
	 *
	 * @throws Exception
	 */
	public function setLayout($layout)
	{
		// I have no idea what this does but don't want to break it.
		$joomlaValid = strpos($layout, ':') === false;

		// This was explicitly set
		$nonStandard = $layout !== 'default';
		if ($joomlaValid and $nonStandard)
		{
			$this->_layout = $layout;
		}
		else
		{
			// Default is not an option anymore.
			$replace = $this->_layout === 'default';
			if ($replace)
			{
				$layoutName = strtolower($this->getName());
				$exists     = false;
				foreach ($this->_path['template'] as $path)
				{
					$exists = file_exists("$path$layoutName.php");
					if ($exists)
					{
						break;
					}
				}
				if (!$exists)
				{
					throw new Exception(
						sprintf(Languages::_('ORGANIZER_LAYOUT_NOT_FOUND'), $layoutName),
						500
					);
				}
				$this->_layout = strtolower($this->getName());
			}
		}

		return $this->_layout;
	}

	/**
	 * Method to add a model to the view.
	 *
	 * @param   BaseDatabaseModel  $model    The model to add to the view.
	 * @param   boolean            $default  Is this the default model?
	 *
	 * @return  BaseDatabaseModel  The added model.
	 */
	public function setModel($model, $default = false)
	{
		$name                 = strtolower($this->getName());
		$this->_models[$name] = $model;

		if ($default)
		{
			$this->_defaultModel = $name;
		}

		return $model;
	}
}
