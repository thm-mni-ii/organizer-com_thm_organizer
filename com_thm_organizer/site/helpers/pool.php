<?php
/**
 * @category    Joomla <extension type>
 * @package     THM_<extension family>
 * @subpackage  <extension name>.<admin/site>
 * @name        <class name>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

class THM_OrganizerHelperPool
{
    public static function getCrPText(&$pool)
    {
        $minCrPExists = !empty($pool->minCrP);
        $maxCrPExists = !empty($pool->maxCrP);
        if ($maxCrPExists)
        {
            if ($minCrPExists)
            {
                if ($pool->minCrP == $pool->maxCrP)
                {
                    return "$pool->maxCrP CrP";
                }
                return "$pool->minCrP - $pool->maxCrP CrP";
            }
            return "max. $pool->maxCrP CrP";
        }
        if ($minCrPExists)
        {
            return "min. $pool->minCrP CrP";
        }
        return '';
    }
}