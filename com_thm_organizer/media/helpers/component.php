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

require_once 'access.php';

/**
 * Class provides generalized functions useful for several component files.
 */
class THM_OrganizerHelperComponent
{
    /**
     * Adds menu parameters to the object (id and route)
     *
     * @param object $object the object to add the parameters to, typically a view
     *
     * @return void modifies $object
     * @throws Exception
     */
    public static function addMenuParameters(&$object)
    {
        $app    = JFactory::getApplication();
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
     * Configure the submenu.
     *
     * @param object &$view the view context calling the function
     *
     * @return void
     * @throws Exception
     */
    public static function addSubmenu(&$view)
    {
        $viewName = $view->get('name');

        // No submenu creation while editing a resource
        if (strpos($viewName, 'edit')) {
            return;
        }

        JHtmlSidebar::addEntry(
            JText::_('COM_THM_ORGANIZER'),
            'index.php?option=com_thm_organizer&amp;view=thm_organizer',
            $viewName == 'thm_organizer'
        );

        if (THM_OrganizerHelperAccess::allowSchedulingAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_SCHEDULING') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $scheduling = [];

            $scheduling[JText::_('COM_THM_ORGANIZER_PLAN_POOL_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_pool_manager',
                'active' => $viewName == 'plan_pool_manager'
            ];
            $scheduling[JText::_('COM_THM_ORGANIZER_PLAN_PROGRAM_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=plan_program_manager',
                'active' => $viewName == 'plan_program_manager'
            ];
            $scheduling[JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_TITLE')]     = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_manager',
                'active' => $viewName == 'schedule_manager'
            ];
            ksort($scheduling);

            // Uploading a schedule should always be the first menu item and will never be the active submenu item.
            $prepend    = [
                JText::_('COM_THM_ORGANIZER_SCHEDULE_UPLOAD') . ' <span class="icon-upload"></span>' => [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=schedule_edit',
                    'active' => false
                ]
            ];
            $scheduling = $prepend + $scheduling;
            foreach ($scheduling as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::allowDocumentAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $documentation = [];

            if (THM_OrganizerHelperAccess::isAdmin()) {
                $documentation[JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE')] = [
                    'url'    => 'index.php?option=com_thm_organizer&amp;view=department_manager',
                    'active' => $viewName == 'department_manager'
                ];
            }
            $documentation[JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=pool_manager',
                'active' => $viewName == 'pool_manager'
            ];
            $documentation[JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=program_manager',
                'active' => $viewName == 'program_manager'
            ];
            $documentation[JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=subject_manager',
                'active' => $viewName == 'subject_manager'
            ];
            ksort($documentation);
            foreach ($documentation as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::allowHRAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_HUMAN_RESOURCES') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);
            JHtmlSidebar::addEntry(
                JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_TITLE'),
                'index.php?option=com_thm_organizer&amp;view=teacher_manager',
                $viewName == 'teacher_manager'
            );
        }

        if (THM_OrganizerHelperAccess::allowFMAccess()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_FACILITY_MANAGEMENT') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $fmEntries = [];

            $fmEntries[JText::_('COM_THM_ORGANIZER_BUILDING_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=building_manager',
                'active' => $viewName == 'building_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_CAMPUS_MANAGER_TITLE')]    = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=campus_manager',
                'active' => $viewName == 'campus_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TITLE')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=monitor_manager',
                'active' => $viewName == 'monitor_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_TITLE')]      = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_manager',
                'active' => $viewName == 'room_manager'
            ];
            $fmEntries[JText::_('COM_THM_ORGANIZER_ROOM_TYPE_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=room_type_manager',
                'active' => $viewName == 'room_type_manager'
            ];
            ksort($fmEntries);
            foreach ($fmEntries as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        if (THM_OrganizerHelperAccess::isAdmin()) {
            $spanText = '<span class="menu-spacer">' . JText::_('COM_THM_ORGANIZER_ADMINISTRATION') . '</span>';
            JHtmlSidebar::addEntry($spanText, '', false);

            $adminEntries = [];

            $adminEntries[JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=color_manager',
                'active' => $viewName == 'color_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=degree_manager',
                'active' => $viewName == 'degree_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_FIELD_MANAGER_TITLE')]  = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=field_manager',
                'active' => $viewName == 'field_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_GRID_MANAGER_TITLE')]   = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=grid_manager',
                'active' => $viewName == 'grid_manager'
            ];
            $adminEntries[JText::_('COM_THM_ORGANIZER_METHOD_MANAGER_TITLE')] = [
                'url'    => 'index.php?option=com_thm_organizer&amp;view=method_manager',
                'active' => $viewName == 'method_manager'
            ];
            ksort($adminEntries);
            foreach ($adminEntries as $key => $value) {
                JHtmlSidebar::addEntry($key, $value['url'], $value['active']);
            }
        }

        $view->sidebar = JHtmlSidebar::render();
    }

    /**
     * Attempts to delete entries from a standard table
     *
     * @param string $table the table name
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    public static function delete($table)
    {
        $cids         = JFactory::getApplication()->input->get('cid', [], '[]');
        $formattedIDs = "'" . implode("', '", $cids) . "'";

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->delete("#__thm_organizer_$table");
        $query->where("id IN ( $formattedIDs )");
        $dbo->setQuery($query);
        try {
            $dbo->execute();
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * Gets a div with a given background color and text with a dynamically calculated text color
     *
     * @param string $text    the text to be displayed
     * @param string $bgColor hexadecimal color code
     *
     * @return string  the html output string
     */
    public static function getColorField($text, $bgColor)
    {
        $textColor = self::getTextColor($bgColor);
        $style     = 'color: ' . $textColor . '; background-color: ' . $bgColor . '; text-align:center';

        return '<div class="color-preview" style="' . $style . '">' . $text . '</div>';
    }

    /**
     * Builds a the base url for redirection
     *
     * @return string the root url to redirect to
     * @throws Exception
     */
    public static function getRedirectBase()
    {
        $app    = JFactory::getApplication();
        $url    = JUri::base();
        $menuID = $app->input->getInt('Itemid');

        if (!empty($menuID)) {
            $url .= $app->getMenu()->getItem($menuID)->route . '?';
        } else {
            $url .= '?option=com_thm_organizer&';
        }

        if (!empty($app->input->getString('languageTag'))) {
            $url .= '&languageTag=' . THM_OrganizerHelperLanguage::getShortTag();
        }

        return $url;
    }

    /**
     * Gets an appropriate value for text color
     *
     * @param string $bgColor the background color associated with the field
     *
     * @return string  the hexadecimal value for an appropriate text color
     */
    public static function getTextColor($bgColor)
    {
        $color              = substr($bgColor, 1);
        $params             = JComponentHelper::getParams('com_thm_organizer');
        $red                = hexdec(substr($color, 0, 2));
        $green              = hexdec(substr($color, 2, 2));
        $blue               = hexdec(substr($color, 4, 2));
        $relativeBrightness = ($red * 299) + ($green * 587) + ($blue * 114);
        $brightness         = $relativeBrightness / 1000;
        if ($brightness >= 128) {
            return $params->get('darkTextColor', '#4a5c66');
        } else {
            return $params->get('lightTextColor', '#eeeeee');
        }
    }

    /**
     * Gets an array of dynamically translated default options.
     *
     * @param object $field   the field object.
     * @param object $element the field's xml signature. passed separately to get around its protected status.
     *
     * @return array the default options.
     * @throws Exception
     */
    public static function getTranslatedOptions($field, $element)
    {
        require_once 'language.php';
        $lang    = THM_OrganizerHelperLanguage::getLanguage();
        $options = [];

        foreach ($element->xpath('option') as $option) {

            $value = (string)$option['value'];
            $text  = trim((string)$option) != '' ? trim((string)$option) : $value;

            $disabled = (string)$option['disabled'];
            $disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
            $disabled = $disabled || ($field->readonly && $value != $field->value);

            $checked = (string)$option['checked'];
            $checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

            $selected = (string)$option['selected'];
            $selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

            $tmp = [
                'value'    => $value,
                'text'     => $lang->_($text),
                'disable'  => $disabled,
                'class'    => (string)$option['class'],
                'selected' => ($checked || $selected),
                'checked'  => ($checked || $selected),
            ];

            $options[] = $tmp;
        }

        return $options;
    }

    /**
     * TODO: Including this (someday) to the Joomla Core!
     * Checks if the device is a smartphone, based on the 'Mobile Detect' library
     *
     * @return boolean
     */
    public static function isSmartphone()
    {
        $mobileCheckPath = JPATH_ROOT . '/components/com_jce/editor/libraries/classes/mobile.php';

        if (file_exists($mobileCheckPath)) {
            if (!class_exists('Wf_Mobile_Detect')) {
                // Load mobile detect class
                require_once $mobileCheckPath;
            }

            $checker = new Wf_Mobile_Detect;
            $isPhone = ($checker->isMobile() and !$checker->isTablet());

            if ($isPhone) {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates a select box
     *
     * @param mixed  $entries        a set of keys and values
     * @param string $name           the name of the element
     * @param mixed  $attributes     optional attributes: object, array, or string in the form key => value(,)+
     * @param mixed  $selected       optional selected items
     * @param array  $defaultOptions default options key => value
     *
     * @return string  the html output for the select box
     */
    public static function selectBox($entries, $name, $attributes = null, $selected = null, $defaultOptions = null)
    {
        $options = [];

        $defaultValid = (!empty($defaultOptions) and is_array($defaultOptions));
        if ($defaultValid) {
            foreach ($defaultOptions as $key => $value) {
                $options[] = JHtml::_('select.option', $key, $value);
            }
        }

        $entriesValid = (is_array($entries) or is_object($entries));
        if ($entriesValid) {
            foreach ($entries as $key => $value) {
                $textValid = (is_string($value) or is_numeric($value));
                if (!$textValid) {
                    continue;
                }

                $options[] = JHtml::_('select.option', $key, $value);
            }
        }

        $attribsInvalid = (empty($attributes)
            or (!is_object($attributes) and !is_array($attributes) and !is_string($attributes)));
        if ($attribsInvalid) {
            $attributes = [];
        } elseif (is_object($attributes)) {
            $attributes = (array)$attributes;
        } elseif (is_string($attributes)) {
            $validString = preg_match("/^((\'[\w]+\'|\"[\w]+\") => (\'[\w]+\'|\"[\w]+\")[,]?)+$/", $attributes);
            if ($validString) {
                $singleAttribs = explode(',', $attributes);
                $attributes    = [];
                array_walk($singleAttribs, 'parseAttribute', $attributes);

                /**
                 * Parses the attribute array entries into text/value pairs for use as options
                 *
                 * @param string $attribute  the attribute being iterated
                 * @param int    $key        the array key from the array being iterated (unused)
                 * @param array  $attributes the array where parsed attributes are stored
                 *
                 * @return void modifies the $attributes array
                 *
                 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
                 */
                function parseAttribute($attribute, $key, &$attributes)
                {
                    list($property, $value) = explode(' => ', $attribute);
                    $attributes[$property] = $value;
                }
            } else {
                $attributes = [];
            }
        }

        if (empty($attributes['class'])) {
            $attributes['class'] = 'organizer-select-box';
        } elseif (strpos('organizer-select-box', $attributes['class']) === false) {
            $attributes['class'] .= ' organizer-select-box';
        }

        $isMultiple = (!empty($attributes['multiple']) and $attributes['multiple'] == 'multiple');
        $multiple   = $isMultiple ? '[]' : '';

        $name = "jform[$name]$multiple";

        return JHtml::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $selected);
    }

    /**
     * Loads required files, calls the appropriate controller.
     *
     * @param boolean $isAdmin whether the file is being called from the backend
     *
     * @return void
     * @throws Exception
     */
    public static function setUp($isAdmin = true)
    {
        if ($isAdmin) {
            $basePath = JPATH_COMPONENT_ADMINISTRATOR;
        } else {
            $basePath = JPATH_COMPONENT_SITE;
            require_once 'date.php';
        }

        $handler = explode(".", JFactory::getApplication()->input->getCmd('task', ''));
        if (count($handler) == 2) {
            $task = $handler[1];
        } else {
            $task = $handler[0];
        }

        require_once $basePath . '/controller.php';

        $controllerObj = new THM_OrganizerController;
        $controllerObj->execute($task);
        $controllerObj->redirect();
    }
}
