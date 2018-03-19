<?php
/*
 * Wrapper for the CampaignApi
 *
 */
namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Crypt\Key\Derivation\Pbkdf2;
use Zend\Math\Rand;
use Zend\Crypt\BlockCipher;
use ZfcBase\EventManager\EventProvider;

class Campaign extends EventProvider implements ServiceManagerAwareInterface
{

	protected $cache;

	protected $campaignapi_service;


	public function __construct(){
	}

	/*
     * Get client_id.
     *
     * @return string
     */
    public function getClientAccount()
    {
        return $this->getCampaignApi()->getClientAccount();
    }


    /*
     * Set client_id.
     *
     * @param string $client_id
     * @return ReportsApi
     */
    public function setClientAccount($client_account)
    {
		$this->getCampaignApi()->setClientAccount($client_account);
        return $this;
    }


	public function getCampaigns($type = 'Active'){

		# Set the cache key for the client
		$key  = $this->getClientAccount()->getAccountId().'-'.strtolower($type);

		if($this->getCache()->hasItem($key)){
		   $cache_campaigns = unserialize($this->getCache()->getItem($key));
		}


		if(isset($cache_campaigns['campaigns'][$type])){
			$campaigns_array = $cache_campaigns['campaigns'][$type];
		} else {
	    	$campaigns_array = $this->getCampaignApi()->getCampaigns($type);

			$cache_campaigns['campaigns'][$type] = $campaigns_array;
			$this->getCache()->setItem($key,serialize($cache_campaigns));
		}


		return $campaigns_array;
	}

	public function getClientReport($report,$postParams = null){

	}

	public function getClientCriteriaReport($args,$type){
		$client_id  = $this->getClientAccount()
                                    ->getAccountId(); // Google Adwords Id.
		$widget_id  = $args['widget_id']; //Report Id.
               
		$hash       = md5(serialize($args)); // Serialize the object and hash it to get a key
                $cache_key  = $client_id .'-'. $widget_id . '-'.$hash;
	/*      # If cache exists - Cache causing error
		if($this->getCache()->hasItem($cache_key)){
		    $result = unserialize($this->getCache()->getItem($cache_key));
                 
		} else {
			# Fetch from Google Adwords
                     
	 		$result = $this->getCampaignApi()->getClientCriteriaReport($args,$type);                        
			$this->getCache()->setItem($cache_key,serialize($result));
		} 
      */  
        $result = $this->getCampaignApi()->getClientCriteriaReport($args,$type);                        
            $this->getCache()->setItem($cache_key,serialize($result));
		return $result;
            
      
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
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }


	public function getCampaignApi()
    {
       if (!$this->campaignapi_service) {
            $this->setCampaignApi($this->getServiceManager()->get('jimmybase_campaignapi_service'));
       }

       return $this->campaignapi_service;
    }

	public function setCampaignApi($campaignapi_service)
    {
        $this->campaignapi_service = $campaignapi_service;
    }


	public function getCache(){
		if (!$this->cache) {
			$this->setCache($this->getServiceManager()->get('cache'));
		}

      return $this->cache;
    }

	public function setCache($cache){
        $this->cache = $cache;
    }
}
?>