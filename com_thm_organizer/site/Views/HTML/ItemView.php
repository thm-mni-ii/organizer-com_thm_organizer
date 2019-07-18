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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;

/**
 * Class loads the resource form into display context. Specific resource determined by extending class.
 */
abstract class ItemView extends BaseHTMLView
{
    public $form = null;

    public $item = null;

    protected $_layout = 'item';

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->addDisclaimer();
        $this->modifyDocument();
        HTML::setMenuTitle('THM_ORGANIZER_SUBJECT', $this->item['name']['value']);
        unset($this->item['name']);

        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    protected function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.framework', true);

        $document = Factory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/item.css');
        $document->addStyleSheet(Uri::root() . 'media/jui/css/bootstrap-extended.css');
    }

    /**
     * Creates a basic output for processed values
     *
     * @param string $attribute the attribute name
     * @param mixed  $data      the data to be displayed array|string
     *
     * @return void outputs HTML
     */
    abstract protected function renderAttribute($attribute, $data);
}
