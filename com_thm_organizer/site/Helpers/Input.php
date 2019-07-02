<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Class provides generalized functions useful for several component files.
 */
class Input
{
    /**
     * Adds menu parameters to the object (id and route)
     *
     * @param object $object the object to add the parameters to, typically a view
     *
     * @return void modifies $object
     */
    public static function addMenuParameters(&$object)
    {
        $app    = self::getApplication();
        $menuID = $app->input->getInt('Itemid');

        if (!empty($menuID)) {
            $menuItem = $app->getMenu()->getItem($menuID);
            $menu     = ['id' => $menuID, 'route' => self::getRedirectBase()];

            $query = explode('?', $menuItem->link)[1];
            parse_str($query, $parameters);

            if (empty($parameters['option']) or $parameters['option'] != 'com_thm_organizer') {
                $menu['view'] = '';
            } elseif (!empty($parameters['view'])) {
                $menu['view'] = $parameters['view'];
            }

            $object->menu = $menu;
        }
    }

    /**
     * Returns the application's input object.
     *
     * @param string $resource the name of the resource upon which the ids being sought reference
     *
     * @return array the filter ids
     */
    public static function getFilterIDs($resource)
    {
        $input         = self::getInput();
        $pluralIndex   = "{$resource}IDs";
        $singularIndex = "{$resource}ID";

        $requestIDs = $input->get($pluralIndex, [], 'array');
        $requestIDs = ArrayHelper::toInteger($requestIDs);

        if (!empty($requestIDs)) {
            return $requestIDs;
        }

        $requestID = $input->getInt($singularIndex);

        if (!empty($requestID)) {
            return [$requestID];
        }

        // Forms
        $formData = self::getForm();
        $relevant = (!empty($formData) and (isset($formData[$pluralIndex]) or isset($formData[$singularIndex])));
        if ($relevant) {
            if (isset($formData[$pluralIndex])) {
                return self::resolveListIDs($formData[$pluralIndex]);
            }

            return [(int)$formData[$singularIndex]];
        }

        $filters  = $input->get('filter', [], 'array');
        $relevant = (!empty($filters) and (isset($filters[$pluralIndex]) or isset($filters[$singularIndex])));
        if ($relevant) {
            if (isset($filters[$pluralIndex])) {
                return self::resolveListIDs($filters[$pluralIndex]);
            }

            return [(int)$filters[$singularIndex]];
        }

        $listFilters = $input->get('list', [], 'array');
        $relevant    = (!empty($listFilters) and (isset($listFilters[$pluralIndex]) or isset($listFilters[$singularIndex])));
        if ($relevant) {
            if (isset($listFilters[$pluralIndex])) {
                return self::resolveListIDs($listFilters[$pluralIndex]);
            }

            return [(int)$listFilters[$singularIndex]];
        }

        $params  = self::getParams();
        $listIDs = $params->get($pluralIndex);
        if (count($listIDs)) {
            return $listIDs;
        }

        $itemID = $params->get($singularIndex, null);
        if ($itemID !== null) {
            return [(int)$itemID];
        }

        return [];
    }

    /**
     * Retrieves the specified parameter.
     *
     * @param string $property Name of the property to get.
     * @param mixed  $default  Default value to return if variable does not exist.
     *
     * @return string
     */
    public static function getCMD($property, $default = '')
    {
        return self::getInput()->getCmd($property, $default);
    }

    /**
     * Retrieves the request form.
     *
     * @return array with the request data if available
     */
    public static function getForm()
    {
        return self::getInput()->get('jform', [], 'array');
    }

    /**
     * Retrieves the id parameter.
     *
     * @return int
     */
    public static function getID()
    {
        return self::getInput()->getInt('task');
    }

    /**
     * Retrieves the specified parameter.
     *
     * @param string $property Name of the property to get.
     * @param mixed  $default  Default value to return if variable does not exist.
     *
     * @return int
     */
    public static function getInt($property, $default = 0)
    {
        return self::getInput()->getInt($property, $default);
    }

    /**
     * Retrieves the id of the requested menu item / menu item configuration.
     *
     * @return int
     */
    public static function getItemid()
    {
        $app     = OrganizerHelper::getApplication();
        $default = (empty($app->getMenu()) or empty($app->getMenu()->getActive())) ?
            0 : $app->getMenu()->getActive()->id;

        return self::getInput()->getInt('Itemid', $default);
    }

    /**
     * Returns the application's input object.
     *
     * @return \JInput
     */
    public static function getInput()
    {
        return OrganizerHelper::getApplication()->input;
    }

    /**
     * Consolidates the application, component and menu parameters to a single registry with one call.
     *
     * @return Registry
     */
    public static function getParams()
    {
        $app = self::getApplication();

        return method_exists($app, 'getParams') ? $app->getParams() : ComponentHelper::getParams('com_thm_organizer');
    }

    /**
     * Builds a the base url for redirection
     *
     * @return string the root url to redirect to
     */
    public static function getRedirectBase()
    {
        $url    = Uri::base();
        $input  = self::getInput();
        $menuID = $input->getInt('Itemid');

        if (!empty($menuID)) {
            $url .= self::getApplication()->getMenu()->getItem($menuID)->route . '?';
        } else {
            $url .= '?option=com_thm_organizer&';
        }

        if (!empty($input->getString('languageTag'))) {
            $url .= '&languageTag=' . Languages::getTag();
        }

        return $url;
    }

    /**
     * Returns the application's input object.
     *
     * @return array the selected ids
     */
    public static function getSelectedIDs()
    {
        $input = self::getInput();

        // List Views
        $selectedIDs = $input->get('cid', [], 'array');
        $selectedIDs = ArrayHelper::toInteger($selectedIDs);

        if (!empty($selectedIDs)) {
            return $selectedIDs;
        }

        // Forms
        $formData = self::getForm();
        if (!empty($formData)) {
            // Merge Views
            if (isset($formData['ids'])) {
                $selectedIDs = self::resolveListIDs($formData['ids']);
                if (!empty($selectedIDs)) {
                    asort($selectedIDs);

                    return $selectedIDs;
                }
            }

            // Edit Views
            if (isset($formData['id'])) {
                return [(int)$formData['id']];
            }
        }

        // Default: explicit GET/POST parameter
        $selectedID = $input->getInt('id', 0);

        return empty($selectedID) ? [] : [$selectedID];
    }

    /**
     * Retrieves the task parameter.
     *
     * @return string
     */
    public static function getTask()
    {
        return self::getInput()->getCmd('task');
    }

    /**
     * Retrieves the view parameter.
     *
     * @return string
     */
    public static function getView()
    {
        return self::getInput()->getCmd('view');
    }

    /**
     * Resolves a comma separated list of id values to an array of id values
     *
     * @param string $list the list to be resolved
     *
     * @return array
     */
    public static function resolveListIDs($list)
    {
        $idValues         = explode(',', $list);
        $cleanedIDValues  = ArrayHelper::toInteger($idValues);
        $filteredIDValues = array_filter($cleanedIDValues);

        return $filteredIDValues;
    }
}
