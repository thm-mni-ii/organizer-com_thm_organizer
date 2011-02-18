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
                $query->select('id,CONCAT(organization, "-", semesterDesc, " (", manager, ")") as semester');
                $query->from('#__thm_organizer_semesters');
                $db->setQuery((string)$query);
                $semesters = $db->loadObjectList();
                $options = array();
                if ($semesters)
                {
                        foreach($semesters as $semester)
                        {
                                $options[] = JHtml::_('select.option', $semester->id, $semester->semester);
                        }
                }
                $options = array_merge(parent::getOptions(), $options);
                return $options;
        }
}
