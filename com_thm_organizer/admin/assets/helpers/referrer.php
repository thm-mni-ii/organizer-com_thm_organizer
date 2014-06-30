<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerHelperController
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class handles referrer handling over the session
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerHelperReferrer
{
    /**
     * Gets the referrer field value for pools and subjects
     *
     * @param   string  $type  the type of resource being processed
     *
     * @return  string  the url referring to the pool or subject edit view
     */
    public static function getReferrer($type)
    {
        $referrer = JFactory::getSession()->get("{$type}.referrer", '');
        JFactory::getSession()->set("{$type}.referrer", '');
        return $referrer;
    }

    /**
     * Sets the referrer field value of the item given if the reference is valid in context
     *
     * @param   string  $type  the type of resource being processed
     *
     * @return  void
     */
    public static function setReferrer($type)
    {
        $validReferrer = self::validateReferrer($type);
        if ($validReferrer)
        {
            $httpReferrer = JFactory::getApplication()->input->server->getString('HTTP_REFERER', '');
            JFactory::getSession()->set("{$type}.referrer", $httpReferrer);
        }
    }

    /**
     * Checks whether the reference to the referrer is usable in the current context
     *
     * @param   string  $type  the type of resource being processed
     *
     * @return  boolean  true if the parameters are the same, otherwise false
     */
    private static function validateReferrer($type)
    {
        $app = JFactory::getApplication();
        $server = JFactory::getApplication()->input->server;
        $currentQuery = $server->getString('QUERY_STRING', '');
        if (empty($currentQuery))
        {
            return true;
        }

        $currentParams = array();
        parse_str($currentQuery, $currentParams);
        if (empty($currentParams) OR empty($currentParams['view']))
        {
            return true;
        }

        $httpReferrer = $server->getString('HTTP_REFERER', '');
        if (empty($httpReferrer))
        {
            return true;
        }

        $referrerParams = array();
        parse_str(parse_url($httpReferrer, PHP_URL_QUERY), $referrerParams);
        if (empty($referrerParams) OR empty($referrerParams['view']))
        {
            return true;
        }

        return self::validateView($type, $currentParams['view'], $referrerParams['view']);
    }

    /**
     * Checks if the current view is usable as the referrer. Allows for customized validation.
     *
     * @param   string  $type           the type of resource being processed
     * @param   string  $currentView    the current view
     * @param   string  $referringView  the view which directed to this one
     *
     * @return  bool  true if the view context is valid as referrer, otherwise false
     */
    private static function validateView($type, $currentView, $referringView)
    {
        if ($type == 'pool' AND is_int(strpos($referringView, 'subject')))
        {
            return false;
        }
        return $currentView != $referringView;
    }
}


