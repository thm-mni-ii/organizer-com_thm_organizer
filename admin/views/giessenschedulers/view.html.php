<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage Giessen Scheduler
 */
class thm_organizersViewthm_organizers extends JView
{
    function display($tpl = null)
    {
        //Load pane behavior
        jimport('joomla.html.pane');

        //initialise variables
        $document	= & JFactory::getDocument();
        $pane   	= & JPane::getInstance('sliders');
        $user 		= & JFactory::getUser();

        //build toolbar
        JToolBarHelper::title( JText::_( 'Giessen Scheduler - Main Menu' ), 'home.png' );

        //Create Submenu
	$model = $this->getModel();
        foreach($model->data->links as $link)
        {
            JSubMenuHelper::addEntry( JText::_( $link['name'] ), 'index.php?'.$link['link']);
        }

        //assign vars to the template
        $this->assignRef('pane'			, $pane);
        $this->assignRef('user'			, $user);
        
        $logo = JHTML::_('image', 'components/com_thm_organizer/assets/images/logo.png', 'logo' );
        $this->assignRef('logo', $logo);

	parent::display($tpl);
    }

    /**
    * Creates the buttons view
    *
    * @param string $link targeturl
    * @param string $image path to image
    * @param string $text image description
    * @param boolean $modal 1 for loading in modal
    */
    function quickiconButton( $link, $image, $text )
    {
        //initialise variables
        $lang = & JFactory::getLanguage();
?>
        <div style="float: left;">
            <div class="icon">
                <a href="<?php echo $link; ?>">
<?php echo JHTML::_('image', 'components/com_thm_organizer/assets/images/'.$image, $text ); ?>
                    <span><?php echo $text; ?></span>
                </a>
            </div>
        </div>
<?php
    }
}
?>