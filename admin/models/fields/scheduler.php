<?php
// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * HelloWorld Form Field class for the HelloWorld component
 */
class JFormFieldScheduler extends JFormFieldList
{
        /**
         * The field type.
         *
         * @var         string
         */
        protected $type = 'Scheduler';

        /**
         * Method to get a list of options for a list input.
         *
         * @return      array           An array of JHtml options.
         */
        protected function getOptions()
        {
                $db = JFactory::getDBO();
                $query = $db->getQuery(true);
                $query->select('sid,CONCAT(orgunit, "-", semester, " (", author, ")") as semester');
                $query->from('#__giessen_scheduler_semester');
                $db->setQuery((string)$query);
                $semesters = $db->loadObjectList();
                $options = array();
                if ($semesters)
                {
                        foreach($semesters as $semester)
                        {
                                $options[] = JHtml::_('select.option', $semester->sid, $semester->semester);
                        }
                }
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}
