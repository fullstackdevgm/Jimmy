<?php
namespace Google\Api\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;


$depth = '/../../';


define('API_PATH', getcwd().'/module/Google/src/');// For Adwords Classes
define('BASE_PATH', dirname(__DIR__).'/');
define('LIB_PATH',  BASE_PATH.'Ads/AdWords/Lib');

// Probably Not Used Anymore
define('SRC_PATH',  dirname(__DIR__).'/');
define('UTIL_PATH', BASE_PATH.'Ads/Common/Util');
//
define('ADWORDS_UTIL_PATH', BASE_PATH.'Ads/AdWords/Util');

define('ADWORDS_VERSION', 'v201605');

//
ini_set('include_path', implode(array(ini_get('include_path'), PATH_SEPARATOR, BASE_PATH)));
ini_set('include_path', implode(array(ini_get('include_path'), PATH_SEPARATOR, API_PATH)));

//echo API_PATH;
// Include the AdWordsUser
require_once LIB_PATH . '/AdWordsUser.php';


class AdWords  implements ServiceManagerAwareInterface
{

	const ADWORDS_VERSION = 'v201605';


	/**
     * @var int
     */
    protected $client_id;

	/**
     * @var AdWordsUser
     */
    public $adwords_user;



	public function __construct($config){

		if(!$config)
		   throw new \Exception("Google api config not found");


		$this->adwords_user = new \AdWordsUser();
		$this->setAdwordsConfig($config);


		$this->adwords_user->LogAll();

	}

	/**
	 * Retrieve adwords user instance
	 *
	 * @return AdWordsUser $adwords_user
	 */
	public function getAdWordsUser(){

		return $this->adwords_user;
	}

    /**
     * Set AdWordsUser instance
     *
     * @param AdWordsUser $adwords_user
     * @return GoogleAdwords
     */
	public function setAdWordsUser(AdWordsUser $adwords_user){

		$this->adwords_user = $adwords_user;

	 return $this;
	}


	protected function setAdwordsConfig($config){
		//echo '<pre>';print_r($config);
		//$this->adwords_user->SetEmail($config['email']);
		//$this->adwords_user->SetPassword($config['password']);
		$this->adwords_user->SetUserAgent($config['user_agent']);
		$this->adwords_user->SetClientLibraryUserAgent($config['user_agent']);
		$this->adwords_user->SetClientCustomerId($config['client_id']);
		$this->adwords_user->SetDeveloperToken($config['developer_token']);
	    $this->adwords_user->SetOAuth2Info($config['oauth2_info']);

	}


	public function getService($service_name){

		return $this->adwords_user->GetService($service_name, self::ADWORDS_VERSION);

	}

	// For oauth2
	public function verifyApiAccess($oauth2Info){

		 if(is_array($oauth2Info)){
			//try{

				$oauth2InfoNew = $this->adwords_user->GetOAuth2Handler()->GetOrRefreshAccessToken($oauth2Info);

				$diff = array_diff($oauth2InfoNew, $oauth2Info);


				$this->adwords_user->SetOAuth2Info($oauth2InfoNew);

				if(!empty($diff))
					return array(true,$oauth2InfoNew);
				else
					return array(false,$oauth2Info);

				return $oauth2Info;
			//} catch (Exception $e) {
			//	echo $e->getMessage();
			//}
	    } else {
		    return false;
		}


	}


	 /**
     * Get client_id.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set client_id.
     *
     * @param string $client_id
     * @return ReportsApi
     */
    public function setClientId($client_id)
    {
        $this->client_id   =  $client_id;
		$this->adwords_user->SetClientCustomerId($client_id);

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
