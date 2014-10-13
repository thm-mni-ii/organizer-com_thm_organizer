<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldFields
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class loads a list of fields for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldRoomKeys extends JFormFieldList
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'roomkeys';

    /**
     * Method to get the field options for category
     * Use the extension attribute in a form to specify the.specific extension for
     * which categories should be displayed.
     * Use the show_root attribute to specify whether to show the global category root in the list.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        echo "<pre>" . print_r($this, true) . "</pre>";

        $query->select('DISTINCT r.id AS value, r.name AS text');
        $query->from('#__thm_organizer_rooms AS r');
        $query->order('r.name ASC');
        $dbo->setQuery((string) $query);

        try
        {
            $rooms = $dbo->loadAssocList();
            $roomOptions = array();
            foreach ($rooms as $room)
            {
                $roomOptions[] = JHtml::_('select.option', $room['value'], $room['text']);
            }
            return array_merge(parent::getOptions(), $roomOptions);
        }
        catch (Exception $exc)
        {
            return parent::getOptions();
        }
    }

}
