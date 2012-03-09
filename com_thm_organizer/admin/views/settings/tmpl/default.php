<?php defined('_JEXEC') or die('Restricted access');?>
<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="col100">
        <fieldset class="adminform">
            <legend><?php echo JText::_( "COM_THM_ORGANIZER_RIA_MENU_LABEL_GENERAL" ); ?></legend>
            <table class="admintable">
                <tr>
                    <td class="key">
                        <label for="scheduler_downFolder"><?php echo JText::_( "COM_THM_ORGANIZER_RIA_LABEL_DOWN_FOLDER" ); ?></label>
                    </td>
                    <td>
                        <input class="text_area" type="text" name="scheduler_downFolder" id="scheduler_downFolder" size="100" maxlength="100"
                                        value="<?php if($this->settings != false) echo $this->settings[0]->downFolder; ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="key">
                        <label for="scheduler_vacationcat"><?php echo JText::_( "COM_THM_ORGANIZER_RIA_LABEL_VACATION_CATEGORY" ); ?></label>
                    </td>
                    <td>
                        <?php echo $this->categories;?><br/>
                    </td>
                </tr>
            </table>
        </fieldset>
        <fieldset class="adminform">
                <legend><?php echo JText::_( 'eStudy' ); ?></legend>
                <table class="admintable">
                    <tr>
                        <td class="key">
                            <label for="scheduler_eStudyPath"><?php echo JText::_( "COM_THM_ORGANIZER_RIA_LABEL_PATH" ); ?></label>
                        </td>
                        <td>
                            <input class="text_area" type="text" name="scheduler_eStudyPath" id="scheduler_eStudyPath" size="100" maxlength="100"
                                            value="<?php if($this->settings != false) echo $this->settings[0]->eStudyPath; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <label for="scheduler_eStudywsapiPath"><?php echo JText::_( "COM_THM_ORGANIZER_RIA_LABEL_WSAPI_PATH" ); ?></label>
                        </td>
                        <td>
                            <input class="text_area" type="text" name="scheduler_eStudywsapiPath" id="scheduler_eStudywsapiPath" size="100" maxlength="100"
                                            value="<?php if($this->settings != false) echo $this->settings[0]->eStudywsapiPath; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <label for="scheduler_eStudyCreateCoursePath"><?php echo JText::_( "COM_THM_ORGANIZER_RIA_LABEL_CREATE_COURSE_PATH" ); ?></label>
                        </td>
                        <td>
                            <input class="text_area" type="text" name="scheduler_eStudyCreateCoursePath" id="scheduler_eStudyCreateCoursePath" size="100" maxlength="100"
                                            value="<?php if($this->settings != false) echo $this->settings[0]->eStudyCreateCoursePath; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="key">
                            <label for="scheduler_eStudySoapSchema"><?php echo JText::_( "COM_THM_ORGANIZER_RIA_LABEL_SOAP_SCHEMA" ); ?></label>
                        </td>
                        <td>
                            <input class="text_area" type="text" name="scheduler_eStudySoapSchema" id="scheduler_eStudySoapSchema" size="100" maxlength="100"
                                            value="<?php if($this->settings != false) echo $this->settings[0]->eStudySoapSchema; ?>" />
                        </td>
                    </tr>
                </table>
        </fieldset>
    </div>
    <input type="hidden" name="option" value="com_thm_organizer" />
    <input type="hidden" name="task" value="" />
</form>
