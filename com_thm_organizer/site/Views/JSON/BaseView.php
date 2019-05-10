<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Organizer\Views\JSON;

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\OrganizerHelper;

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 */
abstract class BaseView extends CMSObject
{
    /**
     * The base path of the view
     *
     * @var    string
     */
    protected $_basePath = null;

    /**
     * The name of the view
     *
     * @var    array
     */
    protected $_name = null;

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
     *                          name: the name (optional) of the view (defaults to the view class name suffix).
     *                          charset: the character set to use for display
     *                          escape: the name (optional) of the function to use for escaping strings
     *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
     *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
     *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
     *                          layout: the layout (optional) to use to display the view
     */
    public function __construct($config = array())
    {
        // Set the view name
        if (empty($this->_name))
        {
            if (array_key_exists('name', $config))
            {
                $this->_name = $config['name'];
            }
            else
            {
                $this->_name = $this->getName();
            }
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
    public abstract function display();

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
}
