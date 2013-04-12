<?php 
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view start default
 * @description THM_Curriculum component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<head>
<script type="text/javascript">

window.addEvent('domready', function(){

		var ids = "<?php echo JRequest::getVar("cid");?>";
		console.debug(ids);
	
	    new Request.HTML({

	      url: 'index.php?option=com_thm_organizer&tmpl=component&task=assets.import&cid='+ids,

	      onRequest: function(){
	          var ajaxLoader  = new Element('img', {src: '<?php echo JURI::base();?>/components/com_thm_organizer/assets/images/ajax-loader.gif'});
	          $('result').empty();
	          ajaxLoader.empty().inject($('result')); 
  
	      },

	      onComplete: function(response){
	    		
	      },
	      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript){

	    	  console.debug(responseHTML);
	    		
				if(responseHTML == "") {
					 var ajaxLoader  = new Element('img', {src: '<?php echo JURI::base();?>/components/com_thm_organizer/assets/images/tick.png'});
			          $('result').empty();
			          ajaxLoader.empty().inject($('result')); 
				} else {
					var ajaxLoader  = new Element('img', {src: '<?php echo JURI::base();?>/components/com_thm_organizer/assets/images/error.png'});
					var text  = new Element('div', {id: 'myFirstElement'});
					text.set('html', 'The selected major has no valid LSF parameters');
					
			        $('result').empty();
			        ajaxLoader.empty().inject($('result')); 
			        text.inject($('result'));      
				} 


		      }
	    }).send();


	});

</script>
</head>



<div align="center">
	<div id="result"></div>
</div>
