<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;


use Google\Api\Service\AdWords as GoogleAdWords;

class GoalsApi implements ServiceManagerAwareInterface
{

	private $api  			 = null;

	private $cache			 = null;

	private $client_account  = null;

	private $profile_id 	 = null;

	private $session;


	public function __construct(){


	}

	 /**
     * Get api.
     *
     * @return string
     */
    public function getApiService()
    {
        return $this->api;
    }

    /**
     * Set api.
     *
     * @param string $client_id
     * @return ReportsApi
     */
    public function setApiService($service)
    {
		$this->api = $service;
        return $this;
    }

	 /**
     * Get client_account.
     *
     */
    public function getClientAccount()
    {
        return $this->client_account;
    }
    
     /**
     * Get User Token Mapper.
     */
    public function getUserTokenMapper()
    {
        return $this->getServiceManager()->get('jimmybase_usertoken_mapper');
        
    }


    /**
     * Set client_account.
     *
     * @param string $client_account
     */
    public function setClientAccount($client_account)
    {
       
            $this->client_account = $client_account;
            $userTokenMapper = $this->getUserTokenMapper();
            $tokenObj = $userTokenMapper->findById($client_account->getUserTokenId());
            $this->setAccessToken($tokenObj->getToken());
            return $this;
       
    }
    
    /**
     * Get client_account.
     *
     */
    public function getProfileId()
    {
        return $this->profile_id;
    }


    /**
     * Set client_account.
     *
     * @param string $client_account
     */
    public function setProfileId($profile_id)
    {
    	$this->profile_id = $profile_id;
        return $this;
    }


    public function setAccessToken($api_auth_info){


            if($api_auth_info)
                    $this->getApiService()->verifyApiAccess(unserialize($api_auth_info));


            return $this;
    }


    public function getAll(){

              # Get the service, which loads the required classes.
              $campaignService = $this->api->getService('CampaignService');

              # Create selector.
              $selector = new \Selector();
              $selector->fields =    array('Id', 'Name', 'Impressions', 'Clicks', 'Cost', 'Ctr');

              $selector->ordering[] = new \OrderBy('Name', 'ASCENDING');

              # Create paging controls.
              $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

              $selector->predicates[] = new \Predicate('Status', 'IN', array('ACTIVE'));

             //$selector->predicates[] = new \Predicate('Status', 'NOT_IN', array('PAUSED'));


              # Date Range
              $dateRange = new \DateRange();
              $dateRange->min = date('Ymd', strtotime('-2 week'));
              $dateRange->max = date('Ymd', strtotime('-1 day'));
              $selector->dateRange = $dateRange;

              do {
                    # Make the get request.
                    $page = $campaignService->get($selector);

                    # Display results.
                    if (isset($page->entries)) {
                      foreach ($page->entries as $campaign) {
                            $campaigns_array[] = $campaign;
                      }
                    }
                    # Advance the paging index.
                    $selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
              } while ($page->totalNumEntries > $selector->paging->startIndex);


            return $campaigns_array;
    }



	public function getGoals(){


		//try{
		 	list($web_property_id, $profile_id) = explode(":",$this->getProfileId());

			# Set the cache key for the client
			$key  = $this->getClientAccount()->getAccountId().'-'.$profile_id;

			if($this->getCache()->hasItem($key)){
			   $cache_goals = unserialize($this->getCache()->getItem($key));
			}

			if(isset($cache_goals['goals'])){
				$goalsArray = $cache_goals['goals'];
			} else {


			  	$goals = $this->getApiService()->getService()
			  								   ->management_goals
		                		   			   ->listManagementGoals($this->getClientAccount()->getAccountId(), $web_property_id,$profile_id);
				if($goals->items){
		         	foreach ($goals->items as $item) {
		         		$goalsArray[$item->id] = $item->name;
		         	}

					$cache_goals['goals'] = $goalsArray;
					$this->getCache()->setItem($key,serialize($cache_goals));
				}
			}


	  //  } catch(\Exception $e){
	    //	return false;
	   // }
       return $goalsArray;
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