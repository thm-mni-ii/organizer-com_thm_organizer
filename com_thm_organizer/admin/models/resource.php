<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        abstract class for resource business logic and database abstraction
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('JPATH_PLATFORM') or die;
jimport('joomla.application.component.model');
/**
 * Abstract class defining functions to be used by all resource models
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
abstract class thm_organizersModelresource extends JModel
{
    /**
     * an abstract function for validating a set of a singular resource type
     *
     * @param   SimpleXMLNode  &$parent    a node containing of resource nodes
     * @param   array          &$data      models the data contained in the document
     * @param   array          &$errors    contains strings explaining critical data inconsistancies
     * @param   array          &$warnings  contains strings explaining minor data inconsistancies
     * @param   array          &$helper    contains optional external resource data as needed
     * 
     * @return void
     */
    protected abstract function validate(&$parent, &$data, &$errors, &$warnings = null, &$helper = null);

    /**
     * an abstract function for validating an individual resource
     *
     * @param   SimpleXMLNode  &$child     a resource node
     * @param   array          &$data      models the data contained in the document
     * @param   array          &$errors    contains strings explaining critical data inconsistancies
     * @param   array          &$warnings  contains strings explaining minor data inconsistancies
     * @param   array          &$helper    contains optional external resource data as needed
     * 
     * @return void
     */
    protected abstract function validateChild(&$child, &$data, &$errors, &$warnings = null, &$helper = null);
}
