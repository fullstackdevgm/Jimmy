<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;


use Google\AdWords\Service\AdWords as GoogleAdWords;
use Google\AdWords\Service\Ananlytics as GoogleAnalytics;

use BingAds\Service\BingAds;

use JimmyBase\Entity\ClientAccounts;

class ClientApi implements ServiceManagerAwareInterface
{

	private $api  			 = null;


	private $cache			 = null;


	private $channel 		 = null;


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
     * Get client_id.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->api->getClientId();
    }

	 /**
     * Get channel.
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

	 /**
     * Get channel.
     *
     * @return string
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * Set client_id.
     *
     * @param string $client_id
     * @return ReportsApi
     */
    public function setClientId($client_id)
    {

		$this->api->setClientId($client_id);
        return $this;
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


	public function setApiAccessToken($api_access) {

		switch($this->getChannel()){
			case ClientAccounts::GOOGLE_ADWORDS:

					//$api_access = $api_access);// Needs to be unserialized
		  			$this->getServiceManager()->get('jimmybase_adwords_api_service')->verifyApiAccess($api_access);

					break;
			case ClientAccounts::GOOGLE_ANALYTICS:
					//$api_access = $api_access);// Needs to be unserialized

					$this->getServiceManager()->get('jimmybase_ananlytics_api_service')->getClient()->setAccessToken($api_access);

					break;

			case ClientAccounts::FACEBOOK_ADS:

			break;
			case ClientAccounts::BING_ADS;
                    $this->getServiceManager()->get('BingAdsApi')->setAccessToken($api_access);

			break;
			default:

		}

		return $this;
	}

	/*
	 * key = Cache key.Can be client_id or agency_id
	 */
	public function fetchClientsAccounts($key){
             
		switch($this->getChannel()){

			case ClientAccounts::GOOGLE_ADWORDS:

					$clients 	  = $this->fetchAdwordsClientAccounts($key);
					
                                        /*
					if($clients){
						foreach($clients as $client){
						  $clients_array[$client['customerId']] = $client['name'];
						}
					}
					*/
					return $clients;

					break;
			case ClientAccounts::GOOGLE_ANALYTICS:

                    $clients      = $this->fetchAnalyticsManagmentAccounts($key);
                    if($clients->items){
                        foreach($clients->items as $gaClient){
                            $clients_array[] = array('id'=>$gaClient->id,'name'=>$gaClient->name);
                        }
                    }


                    return $clients_array;

                    break;
            case ClientAccounts::BING_ADS:

                    $clients      = $this->fetchBingClientAccounts($key);

                    if($clients){
                        foreach($clients as $client){
                            $clients_array[] = array('id'=>$client->Id,'name'=>$client->Name);
                        }
                    }


                    return $clients_array;

                    break;
			default:
				return false;
			}

	}


	public function fetchWebProfiles($account_id){

		$key  = $account_id.'-analytics-web-properties';


		if($this->getCache()->hasItem($key))
		   $web_properties = unserialize($this->getCache()->getItem($key));

		$analytics_api_service = $this->getServiceManager()->get('jimmybase_ananlytics_api_service');

		if(!$web_properties){
		   $web_properties  =  $analytics_api_service->getService()
                                            ->management_webproperties
                                            ->listManagementWebproperties($account_id);
		   $this->getCache()->setItem($key,serialize($web_properties));
		}




		if($web_properties){
			foreach ($web_properties->items as $prop) {
				$key_profile = $prop->id.'-analytics-web-profiles';

				$web_profile = null;

				if($this->getCache()->hasItem($key_profile))
				   $web_profile = unserialize($this->getCache()->getItem($key_profile));

				if(!$web_profile){
					$web_profile = $analytics_api_service->getService()->management_profiles->listManagementProfiles($account_id,$prop->id);

					$this->getCache()->setItem($key_profile,serialize($web_profile));
				}

				if(is_array($web_profile->items)){
					foreach ($web_profile->items as $profileItem) {
						$profiles[] = array('name' => $profileItem->name, "web_property_id" =>$prop->id,'profile_id' => $profileItem->id,'currency'=>$profileItem->currency);
					}
				}

			}
		}


	 return $profiles;
	}
        
        public function fetchSegments($account_id){

		$key  = $account_id.'-analytics-segments';
		if($this->getCache()->hasItem($key)) {
		   $segments = unserialize($this->getCache()->getItem($key));
                }
		$analytics_api_service = $this->getServiceManager()
                                              ->get('jimmybase_ananlytics_api_service');

		if(!$segments) {
		   $segments  =  $analytics_api_service->getService()
                                                       ->management_segments
                                                       ->listManagementSegments();
                                                      
		   $this->getCache()->setItem($key,serialize($segments));
		}

		if($segments){
			foreach ($segments->getItems() as $seg) {
				$key_segment = $seg->id.'-analytics-segment';
				$segment = null;
				if($this->getCache()->hasItem($key_segment))
				   $segment = unserialize($this->getCache()->getItem($key_segment));

				if(!$segment){
					$segment = $seg;
					$this->getCache()->setItem($key_segment,serialize($segment));
				}
			}
		}


	 return $segments;
	}

	public function fetchAnalyticsManagmentAccounts($key){
		/*
		$key  = $key.'-analytics-mgmt-accounts';
		if($this->getCache()->hasItem($key)){
		    $cache_clients = unserialize($this->getCache()->getItem($key));

			if(isset($cache_clients))
			   return  $cache_clients;
		}
		*/

		$analytics_api_service = $this->getServiceManager()->get('jimmybase_ananlytics_api_service');

		$clients   	  = $analytics_api_service->getService()->management_accounts->listManagementAccounts();

		$this->getCache()->setItem($key,serialize($clients));

	 return $clients;
	}

   
	public function fetchAdwordsClientAccounts($key) {
        
		/*
                    $key  = $key.'-adwords-clients';

                    if($this->getCache()->hasItem($key)){
                        $cache_clients = unserialize($this->getCache()->getItem($key));

                            if(isset($cache_clients))
                               return  $cache_clients;
                    }
                */

          $current_client = $this->api->getService('CustomerService')-> get();
                      
          $this->setClientId($current_client->customerId);          
          
		  # Get the service, which loads the required classes.
		  $accountService = $this->api->getService('ManagedCustomerService','v201605');
                 
		  # Create selector.
		  $selector = new \Selector();
                  
		 // Specify the fields to retrieve.
  		  $selector->fields = array( 'CustomerId',  'Name');
		  $selector->ordering[] = new \OrderBy('Name', 'ASCENDING');
                   
		  $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);                 
                 // $page = $accountService->get($selector);
                       
                  do {
			# Make the get request.
			$page = $accountService->get($selector);
                        
			# Display results.
			if (isset($page->entries)) {
			  foreach ($page->entries as $client) {                            
				$clients_array[$client->customerId] =                                        
                                        array('name'=> $client->name,
                                              'email'=> $client->login,
                                              'customerId'=> $client->customerId);
                               
			  }
			}
			# Advance the paging index.
			$selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;

		  } while ($page->totalNumEntries > $selector->paging->startIndex);
                
                  // This will add the a client for a non MCC account which fails to find entries from 
                  // manage customer service.
                  if ($clients_array == null && $current_client->canManageClients == false) {
                      $clients_array[$current_client->customerId] = 
                              array('name' => $current_client->companyName,
                                    'email' => null,
                                    'customerId' => $current_client->customerId
                              );
                  } 
		$this->getCache()->setItem($key,serialize($clients_array));
		return $clients_array;
	}


	public function fetchBingClientAccounts($key){
        $bingads_api_service = $this->getServiceManager()->get('BingAdsApi');
        $request = new \BingAds\CustomerManagement\GetAccountsInfoRequest();
        $request->TopN = 100;

        $clients = $bingads_api_service->getService('CustomerManagementService')->GetAccountsInfo($request)->AccountsInfo->AccountInfo;

		$this->getCache()->setItem($key,serialize($clients));

		return $clients;
	}


	public function getCampaigns($type='Active'){
		  # Get the service, which loads the required classes.
		  $campaignService = $this->api->getService('CampaignService');

		  # Create selector.
		  $selector = new \Selector();
		  $selector->fields     = array('Id', 'Name');
		  $selector->ordering[] = new \OrderBy('Name', 'ASCENDING');

		  # Create paging controls.
		  $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

		  if($type     == 'Active')
		     $selector->predicates[] = new \Predicate('Status', 'IN', array('ACTIVE'));
  		  elseif($type == 'Paused' )
		  	 $selector->predicates[] = new \Predicate('Status', 'IN', array('PAUSED'));


		  do {
			# Make the get request.
			$page = $campaignService->get($selector);

			# Display results.
			if (isset($page->entries)) {
			  foreach ($page->entries as $campaign) {
				$campaigns_array[$campaign->id] = $campaign->name;
			  }
			}
			# Advance the paging index.
			$selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
		  } while ($page->totalNumEntries > $selector->paging->startIndex);


		return $campaigns_array;
	}

	public function getClientReport($report,$postParams = null){


	  if(empty($report))
	     return false;



	  $args = $this->_prepareParams($report,$postParams);
	 /* echo '<pre>';print_r($args);
 	exit;*/
	  # Get the service, which loads the required classes.
	  $campaignService = $this->api->getService('CampaignService');


	  # Create selector.
	  $selector 			= new \Selector();

	  $selector->fields     = array_merge(array('Id', 'Name','Date'),$args['fields']);

	  $selector->ordering[] = new \OrderBy('Date', 'ASCENDING');
	  //$selector->ordering[] = new \OrderBy('Clicks', 'DESCENDING');
	 //$selector->predicates[] = new \Predicate('Impressions', 'GREATER_THAN', array(0));

	  if($report->getCampaigns())
	    $selector->predicates[] = new \Predicate('Id', 'IN', @explode(',',$report->getCampaigns()) );


	  # Create paging controls.
	  $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

	  $selector->predicates[] = new \Predicate('Status', 'IN', array('ACTIVE'));

	  # Date Range
	  $dateRange = new \DateRange();
	  $dateRange->min = date('Ymd', $args['date_range']['min']);
	  $dateRange->max = date('Ymd', $args['date_range']['max']);
	  $selector->dateRange = $dateRange;

	  do {
		# Make the get request.
		$page = $campaignService->get($selector);
		# Display results.
		if (isset($page->entries)) {
		  foreach ($page->entries as $campaign) {
			$campaigns_array[$campaign->id][] = $campaign;
		  }
		}
		# Advance the paging index.
		$selector->paging->startIndex += \AdWordsConstants::RECOMMENDED_PAGE_SIZE;
	  } while ($page->totalNumEntries > $selector->paging->startIndex);

	/*echo '<pre>';
	print_r($campaigns_array);
	exit;
		*/
		return array($campaigns_array,$args);
	}


	public function getClientReportAwql($report){

	  if(empty($report))
	     return false;


	  $args = $this->_prepareParams($report);
	  # Get the service, which loads the required classes.
	  $campaignService = $this->api->getService('CampaignService');

	  # Prepare a date range for the last week. Instead you can use 'LAST_7_DAYS'.
	  $dateRange = sprintf('%d,%d',date('Ymd', $args['date_range']['min']), date('Ymd', $args['date_range']['max']));

	  $reportQuery = "SELECT ";
	  $reportQuery.= implode(',',array_merge($args['fields'],array('CampaignId',  'Id','Date')));
	  $reportQuery.= " FROM  ".$args['report_type'];

	  //$reportQuery.= " WHERE Status IN [ACTIVE] ";

	  if($report->getCampaigns())
	    $reportQuery .= " WHERE Id IN [". $report->getCampaigns()."]";

	  $reportQuery.= " during  ".$dateRange;

	  # Set additional options.
	  $options = array('version' => GoogleAdWords::ADWORDS_VERSION);
	  $filePath = dirname(__FILE__) . '/report.csv';

	  $campaign_array =  \ReportUtils::DownloadReportWithAwql($reportQuery, null, $this->api->adwords_user,'CSV', $options);


	  echo '<pre>';
	  print_r($campaign_array);exit;
	  return $campaign_array;
	}


	public function getClientCriteriaReport($args){

	  if(empty($args))
	     return false;


	  //echo '<pre>';print_r($args);exit;

	  # Load the service, so that the required classes are available.
	  $this->api->getAdWordsUser()->LoadService('ReportDefinitionService', GoogleAdWords::ADWORDS_VERSION);

	  # Create selector.
	  $selector = new \Selector();
	  $selector->fields = $args['fields'];

	  # Filter out deleted criteria.
	 // $selector->predicates[] = new \Predicate('Status', 'NOT_IN', array('DELETED'));

	  if(!empty($args['campaigns']))
	    $selector->predicates[] = new \Predicate('CampaignId', 'IN', $args['campaigns']);

	  # Create report definition.
	  $reportDefinition = new \ReportDefinition();
	  if($args['date_range_type']){
	  	$reportDefinition->dateRangeType  = $args['date_range_type'];

		if($args['date_range_type']=='CUSTOM_DATE')
		   $selector->dateRange = array('min' => date('Ymd',$args['date_range']['min']),'max' => date('Ymd',$args['date_range']['max']));

	  } elseif($args['date_range_compare']){
		$reportDefinition->dateRangeType      = 'CUSTOM_DATE';

		if($args['date_range_compare'])
		   $selector->dateRange = array('min' => date('Ymd',$args['date_range_compare']['min']),'max' => date('Ymd',$args['date_range_compare']['max']));
		//$reportDefinition->startDate = $args['date_range_compare']['min'];
		//$reportDefinition->endDate = $args['date_range_compare']['max'];
	  }

	  $reportDefinition->selector       = $selector;
	  $reportDefinition->reportName     = $args['report_type'].' #' . uniqid();

	  $reportDefinition->reportType     = $args['report_type'];
	  $reportDefinition->downloadFormat = 'XML';

	  # Exclude criteria that haven't recieved any impressions over the date range.
	  $reportDefinition->includeZeroImpressions = TRUE;

	  # Set additional options.
	  $options = array('version' => GoogleAdWords::ADWORDS_VERSION, 'returnMoneyInMicros' => TRUE);

	  $campaign_array =  \ReportUtils::DownloadReport($reportDefinition, null, $this->api->getAdWordsUser(),$options);

	  return $campaign_array;
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