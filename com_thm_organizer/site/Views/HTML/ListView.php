<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Exception;
use Organizer\Helpers\HTML;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

/**
 * Class loads a filtered set of resources into the display context. Specific resource determined by extending class.
 */
abstract class ListView extends BaseHTMLView
{
    protected $_layout = 'list';

    public $filterForm = null;

    public $headers = null;

    public $items = null;

    public $pagination = null;

    public $state = null;

    /**
     * Concrete classes are supposed to use this method to add a toolbar.
     *
     * @return void  sets context variables
     */
    abstract protected function addToolBar();

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the use may access the view, otherwise false
     */
    abstract protected function allowAccess();

    /**
     * Method to create a list output
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        if (!$this->allowAccess()) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $this->state = $this->get('State');

        $this->filterForm = $this->get('FilterForm');
        $this->headers    = $this->getHeaders();
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        $this->addToolBar();
        $this->addMenu();
        $this->modifyDocument();
        $this->preProcessItems();

        parent::display($tpl);
    }

    /**
     * Generates a string containing attribute information for an HTML element to be output
     *
     * @param mixed &$element the element being processed
     *
     * @return string the HTML attribute output for the item
     */
    public function getAttributesOutput(&$element)
    {
        $output = '';
        if (!is_array($element)) {
            return $output;
        }

        $relevant = (!empty($element['attributes']) and is_array($element['attributes']));
        if ($relevant) {
            foreach ($element['attributes'] as $attribute => $attributeValue) {
                $output .= $attribute . '="' . $attributeValue . '" ';
            }
        }
        unset($element['attributes']);

        return $output;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    abstract protected function getHeaders();

    /**
     * Generates a toggle for the attribute in question
     *
     * @param int    $id        the id of the database entry
     * @param bool   $value     the value currently set for the attribute (saves asking it later)
     * @param string $resource  the name of the data management controller
     * @param string $tip       the tooltip
     * @param string $attribute the resource attribute to be changed (useful if multiple entries can be toggled)
     *
     * @return string  a HTML string
     */
    protected function getToggle($id, $value, $resource, $tip, $attribute = null)
    {
        $iconClass = empty($value) ? 'unpublish' : 'publish';
        $icon      = '<i class="icon-' . $iconClass . '"></i>';

        $attributes          = [];
        $attributes['title'] = $tip;
        $attributes['class'] = 'btn btn-micro hasTooltip';
        $attributes['class'] .= empty($value) ? ' inactive' : '';

        $url = "index.php?option=com_thm_organizer&id=$id&value=$value";
        $url  .= "&task=$resource.toggle";
        $url  .= empty($attribute) ? '' : "&attribute=$attribute";
        $link = HTML::_('link', $url, $icon, $attributes);

        return '<div class="button-grp">' . $link . '</div>';
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/organizer.css');

        HTML::_('bootstrap.tooltip');
        HTML::_('searchtools.form', '#adminForm', []);
    }

    /**
     * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
     *
     * @return void processes the class items property
     */
    abstract protected function preProcessItems();
}
