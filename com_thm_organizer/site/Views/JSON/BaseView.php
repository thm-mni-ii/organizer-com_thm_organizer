<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Organizer\Views\JSON;

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
     * @param array $config A named configuration array for object construction.
     */
    public function __construct($config = array())
    {
        // Set the view name
        if (empty($this->_name)) {
            if (array_key_exists('name', $config)) {
                $this->_name = $config['name'];
            } else {
                $this->_name = $this->getName();
            }
        }

        // Set a base path for use by the view
        if (array_key_exists('base_path', $config)) {
            $this->_basePath = $config['base_path'];
        } else {
            $this->_basePath = JPATH_COMPONENT;
        }

        $this->baseurl = Uri::base(true);
    }

    /**
     * Display the view output
     */
    abstract public function display();

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
