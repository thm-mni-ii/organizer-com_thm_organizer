<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use THM_OrganizerHelperHTML as HTML;

/**
 * Class loads a filtered set of subjects into the display context.
 */
class THM_OrganizerViewSubject_List extends \Joomla\CMS\MVC\View\HtmlView
{
    public $disclaimer;

    public $displayName;

    public $groupBy = 'list';

    public $languageLinks;

    public $params;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->params = OrganizerHelper::getParams();

        $this->fixGroupBy();

        $this->state = $this->get('State');

        if (empty($this->state->get('programID'))) {
            $this->params->set('showByPool', false);
            $this->params->set('showByTeacher', false);
        }

        $this->items         = $this->get('items');
        $this->languageLinks = new \JLayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');

        $model             = $this->getModel();
        $this->fields      = $model->fields;
        $this->teachers    = $model->teachers;
        $this->pools       = $model->pools;
        $this->displayName = $model->displayName;

        $this->disclaimer     = new \JLayoutFile('disclaimer', JPATH_ROOT . '/components/com_thm_organizer/Layouts');

        parent::display($tpl);
    }

    /**
     * Fixes the group by parameter if the tab is turned off, defining a chain of precedence.
     *
     * @return void
     */
    private function fixGroupBy()
    {
        $defaultDisplayOrder = [0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4];
        $defaultTab          = $this->params->get('groupBy', 0);

        if (!$this->params->get('showByName')) {
            unset($defaultDisplayOrder[0]);
        }

        if (!$this->params->get('showByModuleNumber')) {
            unset($defaultDisplayOrder[1]);
        }

        if (!$this->params->get('showByPool')) {
            unset($defaultDisplayOrder[2]);
        }

        if (!$this->params->get('showByTeacher')) {
            unset($defaultDisplayOrder[3]);
        }

        if (!$this->params->get('showByField')) {
            unset($defaultDisplayOrder[4]);
        }

        $usedTab = in_array($defaultTab, $defaultDisplayOrder) ? $defaultTab : array_shift($defaultDisplayOrder);
        $this->params->set('groupBy', $usedTab);
    }

    /**
     * Generates the creditpoint text for the given pool
     *
     * @param array $pool the pool whose credit point information is required
     *
     * @return string the credit point text to display
     */
    public function getCreditPointText($pool)
    {
        if (empty($pool['minCrP']) and empty($pool['maxCrP'])) {
            return '';
        } elseif (empty($pool['minCrP'])) {
            return sprintf(Languages::_('THM_ORGANIZER_CRP_UPTO'), $pool['maxCrP']);
        } elseif (empty($pool['maxCrP']) or $pool['minCrP'] == $pool['maxCrP']) {
            return "{$pool['minCrP']} CrP";
        } else {
            return sprintf(Languages::_('THM_ORGANIZER_CRP_BETWEEN'), $pool['minCrP'], $pool['maxCrP']);
        }
    }

    /**
     * Renders subject information
     *
     * @param mixed  &$item       object if the the item to be displayed is a subject otherwise array
     * @param string  $type       the type of group/sort
     * @param string  $resourceID the id of the resource in the item row
     *
     * @return string  the HTML for the item to be displayed
     */
    public function getItemRow(&$item, $type = '', $resourceID = '')
    {
        $attribs = ['target' => '_blank'];

        if ($type != 'pool') {
            $href      = $item->subjectLink;
            $name      = $item->name;
            $subjectNo = empty($item->externalID) ? '' : $item->externalID;
            $crp       = empty($item->creditpoints) ? '' : $item->creditpoints . ' CrP';
        } else {
            $href      = "#pool{$item['id']}";
            $name      = $item['name'] . ' <span class="icon-forward-2"></span>';
            $subjectNo = '';
            $crp       = empty($this->params->get('inlinePoolCrP', 1)) ? '' : $this->getCreditPointText($item);
        }

        $displayItem = '<tr>';

        if ($type != 'number' and !empty($subjectNo)) {
            $text = "$name ($subjectNo)";
        } elseif (empty($subjectNo)) {
            $text = $name;
        } else {
            $text = "$subjectNo - $name";
        }

        $displayItem .= '<td class="subject-name">' . HTML::link($href, $text, $attribs) . '</td>';

        if (empty($this->state->get('programID'))) {
            $initial     = true;
            $displayItem .= '<td class="subject-program">';

            foreach ($item->programs as $programID => $programName) {
                if (!$initial) {
                    $displayItem .= '<br>';
                }
                $href        = "?option=com_thm_organizer&view=subject_list&programIDs=$programID";
                $displayItem .= HTML::link($href, $programName, $attribs);
            }

            $displayItem .= '</td>';
        } else {
            $template = '<td class="subject-teacher">XXXX</td>';
            if ($type == 'teacher') {
                $subjectTeacher = $this->getResponsibleDisplay($item, $resourceID);
            } elseif ($type != 'pool') {
                $subjectTeacher = $this->getTeacherDisplay($item);
            } else {
                $subjectTeacher = '';
            }
            $displayItem .= str_replace('XXXX', $subjectTeacher, $template);
        }

        if (empty($crp)) {
            $displayItem .= '<td class="subject-crp"></td>';
        } else {
            $displayItem .= '<td class="subject-crp">' . $crp . '</td>';
        }

        $displayItem .= '</tr>';

        return $displayItem;
    }

    /**
     * Retrieves the teacher responsibility texts
     *
     * @param object $subject   the subject being iterated
     * @param int    $teacherID the key of the teacher being iterated
     *
     * @return string
     */
    public function getResponsibleDisplay($subject, $teacherID)
    {
        $responsibilities = [];

        $isResponsible = (isset($subject->teachers[1]) and array_key_exists($teacherID, $subject->teachers[1]));
        $isTeacher     = (isset($subject->teachers[2]) and array_key_exists($teacherID, $subject->teachers[2]));

        switch ($this->params->get('teacherResp', 0)) {
            case 1:
            case 2:
                break;

            default:
                if ($isResponsible) {
                    $responsibilities[1] = Languages::_('THM_ORGANIZER_RESPONSIBLE');
                }

                if ($isTeacher) {
                    $responsibilities[2] = Languages::_('THM_ORGANIZER_TEACHER');
                }

                break;
        }

        return implode(' & ', $responsibilities);
    }

    /**
     * Retrieves the teacher texts and formats them according to their responisibilites for the subject being iterated
     *
     * @param object $subject the subject being iterated
     *
     * @return string
     */
    public function getTeacherDisplay($subject)
    {
        $displayTeachers = [];
        $responsibility  = $this->params->get('teacherResp', 0);

        if (isset($subject->teachers[1]) and $responsibility != 2) {
            foreach ($subject->teachers[1] as $responsibleID) {
                $name                           = $this->getTeacherText($responsibleID);
                $displayTeachers[$name]         = [];
                $displayTeachers[$name]['id']   = $responsibleID;
                $displayTeachers[$name]['resp'] = '';

                if ($responsibility == 0) {
                    $displayTeachers[$name]['resp'] = Languages::_('THM_ORGANIZER_RESPONSIBLE_ABBR');
                }
            }
        }

        if (isset($subject->teachers[2]) and $responsibility != 1) {
            foreach ($subject->teachers[2] as $teacherID) {
                $name = $this->getTeacherText($teacherID);

                if (empty($displayTeachers[$name])) {
                    $displayTeachers[$name]         = [];
                    $displayTeachers[$name]['id']   = $teacherID;
                    $displayTeachers[$name]['resp'] = '';
                }

                if ($responsibility == 0) {
                    $displayTeachers[$name]['resp'] .= empty($displayTeachers[$name]['resp']) ?
                        Languages::_('THM_ORGANIZER_TEACHER_ABBR') : ', ' . Languages::_('THM_ORGANIZER_TEACHER_ABBR');
                }
            }
        }

        ksort($displayTeachers);
        $return = '';

        foreach ($displayTeachers as $name => $data) {
            if (!empty($return)) {
                $return .= '<br>';
            }

            $return .= $name;

            if ($responsibility == 0) {
                $return .= ' (' . $data['resp'] . ')';
            }
        }

        return $return;
    }

    /**
     * Generates the teacher text for the given teacher key
     *
     * @param int $teacherKey the index where
     *
     * @return string
     */
    public function getTeacherText($teacherKey)
    {
        $showTitle = $this->params->get('showTitle', 0);

        if (empty($this->teachers[$teacherKey])) {
            return '';
        }

        $teacher     = $this->teachers[$teacherKey];
        $teacherText = $teacher['surname'];

        if (!empty($teacher['forename'])) {
            $teacherText .= ", {$teacher['forename']}";
        }

        if ($showTitle and !empty($teacher['title'])) {
            $teacherText .= " {$teacher['title']}";
        }

        return $teacherText;
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.framework');
        HTML::_('bootstrap.tooltip');
        HTML::_('jquery.ui');

        $document = \JFactory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/subject_list.css');
    }
}
