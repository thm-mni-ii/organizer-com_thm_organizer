<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model resource
 * @description abstract class for resources
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
jimport('joomla.application.component.model');
abstract class thm_organizersModelresource extends JModel
{
    /**
     * validate
     *
     * calls the type appropriate validate function for the selected object
     *
     * @param string $type the document ecapsulating the resource
     * @param mixed $object the resouce object to be validated
     * @param array $data a model of the data within the resource object
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper contains optional external resource data as needed
     */
    public function validate($type, &$object, &$data, &$errors, &$warnings, &$helper = null)
    {
        if($type == 'xml')$this->validateXML ($object, $data, $errors, $warnings, $helper);
    }

    /**
     * validateXML
     *
     * an abstract function that must be implemented by inheriting classes
     *
     * @param SimpleXMLNode $parent a node containing of resource nodes
     * @param array $data
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper contains optional external resource data as needed
     */
    protected function validateXML(&$parent, &$data, &$errors, &$warnings, &$helper = null){}

    /**
     * validateXMLChild
     *
     * an abstract function that must be implemented by inheriting classes
     *
     * @param SimpleXMLNode $child a resource node
     * @param array $data models the data contained in $element
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper contains optional external resource data as needed
     */
    protected function validateXMLChild(&$child, &$data, &$errors, &$warnings, &$helper = null){}

    /**
     * processData
     *
     * iterates over resource nodes, saves/updates resource data
     *
     * @param SimpleXMLNode $parent
     * @param array $data models the data contained in $element
     * @param int $semesterID the id of the relevant planning period
     * @param array $helper contains optional external resource data as needed
     */
    public function processData(&$parent, &$data, $semesterID = 0, &$helper = null){}

    /**
     * processNode
     *
     * saves/updates resource data
     *
     * @param SimpleXMLNode $child
     * @param array $data models the data contained in $element
     * @param int $semesterID the id of the relevant planning period
     * @param array $helper contains optional external resource data as needed
     */
    protected function processNode(&$child, &$data, $semesterID = 0, &$helper = null){}
}
