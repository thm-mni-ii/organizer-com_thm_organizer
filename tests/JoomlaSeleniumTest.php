<?php
class JoomlaSeleniumTest extends PHPUnit_Extensions_SeleniumTestCase
{
    const DEFAULT_JOOMLA_URL        = 'http://localhost/joomla/';
    const DEFAULT_USER_NAME         = 'admin';
    const DEFAULT_USER_PASSWORD     = '';
    const DEFAULT_USER_LANGUAGE        = 'en-GB';
    
    protected $joomlaURL;
    
    public function __construct()
    {
        $this->joomlaURL = $this->findJoomlaURLInArgs();
        parent::__construct();
    }
    
    private function findJoomlaURLInArgs()
    {
        $needle    = "--joomla_url=";
        $arg     = array_values(
            array_filter(
                $_SERVER['argv'],
                function ($i) use ($needle) {
                    return strpos($i, $needle) !== false;
                }
            )
        );
        if (empty($arg)) return self::DEFAULT_JOOMLA_URL;
    
        $url = substr($arg[0], strlen($needle));
        return $url{(strlen($url) - 1)} === "/" ? $url : $url . "/";
    }
    
    protected function setUp()
    {
        $this->setBrowser("*chrome");
        $this->setBrowserUrl($this->joomlaURL);
        parent::setUp();
    }
    
    protected function tearDown()
    {
        parent::tearDown();
    }

    protected function refreshPage()
    {
        $this->refresh();
        $this->waitForPageToLoad("30000");
    }
    
    protected function performBackendLogout()
    {
        $this->click("link=Log out");
        $this->waitForPageToLoad("30000");
    }
    
    protected function performBackendLogin($username = NULL, $password = NULL, $language = NULL) 
    {
        $username = $username ?: self::DEFAULT_USER_NAME;
        $password = $password ?: self::DEFAULT_USER_PASSWORD;
        $language = $language ?: self::DEFAULT_USER_LANGUAGE;
        
        $this->open($this->joomlaURL . "administrator/index.php");
        $this->waitForPageToLoad("30000");
        
        $this->assertElementPresent("id=mod-login-username");
        $this->type("id=mod-login-username", $username);
        
        $this->assertElementPresent("id=mod-login-password");
        $this->type("id=mod-login-password", $password);
        
        $this->assertElementPresent("id=lang");
        $this->select("id=lang", "value=$language");
        
        $this->click("link=Log in");
        $this->waitForPageToLoad("30000");
    }
}