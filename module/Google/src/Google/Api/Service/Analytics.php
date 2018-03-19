<?php
namespace Google\Api\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;


$depth = '/../../';


define('API_PATH', getcwd().'/module/Google/src/');// For Adwords Classes
define('BASE_PATH', dirname(__DIR__).'/'); 
define('LIB_PATH',  BASE_PATH.'Ads/AdWords/Lib');

define('ADWORDS_VERSION', 'v201302');

//
ini_set('include_path', implode(array(ini_get('include_path'), PATH_SEPARATOR, BASE_PATH)));
ini_set('include_path', implode(array(ini_get('include_path'), PATH_SEPARATOR, API_PATH)));


require_once BASE_PATH . '/Analytics/Google_Client.php';
require_once BASE_PATH . '/Analytics/contrib/Google_AnalyticsService.php';

class Analytics  implements ServiceManagerAwareInterface
{
	
	const APP_NAME = 'Jimmy - Google Analytics Reporting Application';
	
	const ANALYTICS_SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';
	
	//const ANALYTICS_VERSION = 'v';
	protected $client = null;

	protected $service = null;

	protected $account_id = null;
		
	public function __construct($config){
		
	    $this->setClient(new \Google_Client() );
	    $this->setAnalyticsConfig($config);
	    $this->setService(new \Google_AnalyticsService($this->getClient()));
	    
	}

	public function setClient(\Google_Client $client){

		$this->client = $client;
	 return $this;
	}

	public function getClient(){
	   return $this->client;
	}
	
	
	public function setAnalyticsConfig($config){
		
	    $client = $this->getClient();

		$client->setClientId($config['client_id']);
		$client->setClientSecret($config['client_secret']);
		$client->setRedirectUri($config['redirect_uri']);
		$client->setApplicationName(self::APP_NAME);
		$client->setScopes( array(self::ANALYTICS_SCOPE) );
		$client->setUseObjects(true);

	}
	
	public function setService(\Google_AnalyticsService $service){

		$this->service = $service;

	 return $this;
	}


	public function getService(){
	
		return $this->service;
	}
	
	// For oauth2
	public function verifyApiAccess($accessToken){
	     	
		 if($accessToken){
			$this->getClient()->setAccessToken($accessToken);
	    } else {
		    return false;
		}
	
	    
	}
	

	 /**
     * Get client_id.
     *
     * @return string
     */
    public function getAccountId()
    {   
        return $this->account_id;
    }

    /**
     * Set client_id.
     *
     * @param string $client_id
     * @return ReportsApi
     */
    public function setAccountId($account_id)
    {
        $this->account_id   =  $account_id;

        return $this;
    }
	    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return GoogleAdwords
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}
