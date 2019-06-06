<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Organizer\Views;

defined('_JEXEC') or die;

use Exception;
use JHtmlSidebar;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\View\HtmlView;
use Organizer\Helpers\Access;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 */
abstract class BaseView extends HtmlView
{
    /**
     * Method to get the view name
     *
     * The model name by default parsed using the classname, or it can be set
     * by passing a $config['name'] in the class constructor
     *
     * @return  string  The name of the model
     */
    public function getName()
    {
        if (empty($this->_name)) {
            $this->_name = OrganizerHelper::getClass($this);
        }

        return $this->_name;
    }

    /**
     * Sets the layout name to use
     *
     * @param string $layout The layout name or a string in format <template>:<layout file>
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
        if ($joomlaValid and $nonStandard) {
            $this->_layout = $layout;
        } else {
            // Default is not an option anymore.
            $replace = $this->_layout === 'default';
            if ($replace) {
                $layoutName = strtolower($this->getName());
                $exists     = false;
                foreach ($this->_path['template'] as $path) {
                    $exists = file_exists("$path$layoutName.php");
                    if ($exists) {
                        break;
                    }
                }
                if (!$exists) {
                    throw new Exception(Languages::sprintf('THM_ORGANIZER_LAYOUT_NOT_FOUND', $layoutName),
                        500);
                }
                $this->_layout = strtolower($this->getName());
            }
        }

        return $this->_layout;
    }

    /**
     * Method to add a model to the view.
     *
     * @param BaseDatabaseModel $model   The model to add to the view.
     * @param boolean           $default Is this the default model?
     *
     * @return  BaseDatabaseModel  The added model.
     */
    public function setModel($model, $default = false)
    {
        $name                 = strtolower($this->getName());
        $this->_models[$name] = $model;

        if ($default) {
            $this->_defaultModel = $name;
        }

        return $model;
    }
}
