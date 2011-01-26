<?php defined('_JEXEC') or die('Restricted access');?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>
		<table class="admintable">
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="ecname">Name</label>
				</td>
				<td>
					<input class="text_area" type="text" name="ecname" id="ecname" size="25" maxlength="100" 
							value="<?php echo $this->category->ecname;?>" />
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="ecdescription"><?php echo JText::_('DESCRIPTION');?></label>
				</td>
				<td>
					<textarea name='ecdescription' rows='5' cols='48' id='ecdescription'><?php echo $this->category->ecdescription;?></textarea>
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="access"><?php echo JText::_('ACCESS LEVEL');?></label>
				</td>
				<td>
					<?php echo $this->usergroups;?><br>
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="globalp"><?php echo JText::_('GLOBAL');?></label>
				</td>
				<td>
					<input type="radio" name="globalp" value="1"
						<?php if($this->category->globalp) echo 'checked="checked"';?>
					>
						<?php echo JText::_('YES');?><br>
					<input type="radio" name="globalp" value="0"
						<?php if(!$this->category->globalp) echo 'checked="checked"';?>
					>
						<?php echo JText::_('NO');?><br>
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="reservingp"><?php echo JText::_('Reservierend');?></label>
				</td>
				<td>
					<input type="radio" name="reservingp" value="1"
						<?php if($this->category->reservingp) echo 'checked="checked"';?>
					>
						<?php echo JText::_('YES');?><br>
					<input type="radio" name="reservingp" value="0"
						<?php if(!$this->category->reservingp) echo 'checked="checked"';?>
					>
						<?php echo JText::_('NO');?><br>
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="ecimage"><?php echo JText::_('Bild Hochladen');?></label>
				</td>
				<td>
					<input name="ecimage" type="file" id="ecimage" size="25" />
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="ecimage"><?php echo JText::_('Bild Ausw&auml;hlen');?></label>
				</td>
				<td>
					<?php echo $this->imagelist;?>
				</td>
			</tr>
			<tr>
				<td style="width: 20px;" align="right" class="key">
					<label for="ecimage"><?php echo JText::_('preview');?></label>
				</td>
				<td>
					<script language="javascript" type="text/javascript">
					if (document.forms[0].image.options.value!=''){
						jsimg='../images/thm_organizer/categories/' + getSelectedValue( 'adminForm', 'image' );
					} else {
						jsimg='../images/M_images/blank.png';
					}
					document.write('<img src=' + jsimg + ' name="imagelib" width="80" height="80" border="2" alt="Preview" />');
					</script>
					<br /><br />
				</td>
			</tr>
		</table>
	</fieldset>
</div>
<div class="clr"></div>
<input type="hidden" name="option" value="com_thm_organizer" />
<input type="hidden" name="id" value="<?php echo $this->category->ecid; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="category" />
</form>
