<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class DataAbstraction
{
    private $dbo;
    private $user;
    private $mainframe;

    public function __construct( )
    {
        $this->mainframe = JFactory::getApplication( );
        $this->mainframe->initialise();
        $this->dbo =& JFactory::getDBO();
        $this->user =& JFactory::getUser();
    }

    public function getUserName( )
    {
        return $this->user->username;
    }

    public function getUserSessionID( )
    {
    	return session_id();
    }

    public function getUserRoles( )
    {
        return $this->user->groups;
    }

    public function getUserID( )
    {
        return $this->user->id;
    }

    public function query( $sqlstatement, $arr = false )
    {
        $this->dbo->setQuery( $sqlstatement );
        if ( strpos( strtolower( $sqlstatement ), "select" ) === 0) {
            if ( $arr == false ) {
                $data = $this->dbo->loadObjectList();
            } else {
                $data = $this->dbo->loadResultArray();
            }
        } else {
            $this->dbo->query();
            $data = true;
        }
        if ( $this->dbo->getErrorNum() ) {
            $data = false;
        }

        return $data;
    }

    public function getDBO( )
    {
        return $this->dbo;
    }

    public function getRequest( $var )
    {
        return JREQUEST::getVar( $var );
    }

    public function getSettings( )
    {
        $settings = $this->query( "SELECT * FROM #__thm_organizer_application_settings WHERE id=1" );
        if($settings)
        	$settings = $settings[ 0 ];
        else
        	return (object) array("eStudyPath"=>"", "eStudywsapiPath"=>"","eStudyCreateCoursePath"=>"","eStudySoapSchema"=>"", "downFolder"=>"","vacationcat"=>"");
        return $settings;
    }

    public function isComponentavailable( $com )
    {
		$query	= $this->dbo->getQuery(true);
		$query->select('extension_id AS "id", element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where('`type` = '.$this->dbo->quote('component'));
		$query->where('`element` = '.$this->dbo->quote($com));
		$this->dbo->setQuery($query);
    	if ($error = $this->dbo->getErrorMsg())
    		return false;

		$result = $this->dbo->loadObject();

		if($result === null)
			return false;

    	return true;
    }

    public function getSemID()
	{
		$session = & JFactory::getSession();
		return $session->get('scheduler_semID');
	}

	public function getDoc()
	{
		$doc =& JFactory::getDocument();
		return $doc;
	}


}
?>
