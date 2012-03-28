<?php
  // no direct access

  defined('_JEXEC') or die('Restricted access');

  jimport('joomla.application.component.view');

  /**
   * HTML View class for the Giessen Scheduler Component
   *
   * @package    Giessen Scheduler
   */

  class thm_organizerViewAjaxHandler extends JView
  {
      function display($tpl = null)
      {
          $model = $this->getModel();

		  $task = JRequest::getCmd( 'scheduletask' );

		  $output = $model->executeTask( $task );

          if(count($output) == 1)
          	$this->response($output["data"]);
          else
          	$this->response($output["success"], $output["data"]);
      }



      function response($mix, $arr = array())
      {
      	  if (is_bool($mix))
      	  {
      	  	 if(is_array($arr))
      	  	 {
      	  		$arr['size'] = count($arr);
                $arr['success'] = $mix;
                $arr['sid'] = session_id();
      	  	 }
      	  }
      	  else
      	  	if(is_array($mix))
      	  	{
      	  		$arr = $mix;
      	  		$arr['size'] = count($arr);
                $arr['sid'] = session_id();
      	  	}
      	  	else
      	  		if(is_string($mix))
      	  			$arr = $mix;
      	  		else
      	  		{
      	  			//TODO
      	  		}

		  if(is_array($arr))
          	echo json_encode($arr);
          else
          	echo $arr;
      }
  }
?>