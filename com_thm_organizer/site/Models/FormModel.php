<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\FormModel as ParentModel;
use Organizer\Helpers\Access;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads non-item-specific form data.
 */
class FormModel extends ParentModel
{
    /**
     * @var string provides a unique form context so that data does not overlap between extensions and views
     */
    protected $context = '';

    protected $deptResource;

    protected $option = 'com_thm_organizer';

    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->option  = 'com_thm_organizer';
        $this->context = strtolower($this->option . '.' . $this->getName());
    }

    /**
     * Provides a strict access check which can be overwritten by extending classes.
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit()
    {
        return Access::isAdmin();
    }

    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return mixed Form object on success, False on error.
     * @throws Exception => unauthorized access
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = false)
    {
        $allowEdit = $this->allowEdit();
        if (!$allowEdit) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $name = $this->get('name');
        $form = $this->loadForm("com_thm_organizer.$name", $name, ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the view name
     *
     * The model name by default parsed using the classname, or it can be set
     * by passing a $config['name'] in the class constructor
     *
     * @return  string  The name of the model
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = OrganizerHelper::getClass($this);
        }

        return $this->name;
    }

    /**
     * Method to get a form object.
     *
     * @param string  $name    The name of the form.
     * @param string  $source  The form source. Can be XML string if file flag is set to false.
     * @param array   $options Optional array of options for the form creation.
     * @param boolean $clear   Optional argument to force load a new form.
     * @param string  $xpath   An optional xpath to search for the fields.
     *
     * @return  Form|boolean  \JForm object on success, false on error.
     */
    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
        Form::addFormPath(JPATH_COMPONENT_SITE . '/Forms');
        Form::addFieldPath(JPATH_COMPONENT_SITE . '/Fields');

        return parent::loadForm($name, $source, $options, $clear, $xpath);
    }
}
