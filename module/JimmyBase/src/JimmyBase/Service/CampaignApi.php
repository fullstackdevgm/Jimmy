<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;


use Google\Api\Service\AdWords as GoogleAdWords;

class CampaignApi implements ServiceManagerAwareInterface
{

	private $api  			 = null;


	private $cache			 = null;

	private $client_account  = null;


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
     * Get client_id.
     *
     * @return string
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
     * Set client_id.
     *
     * @param string $client_id
     * @return ReportsApi
     */
    public function setClientAccount($client_account)
    {    	
        $this->client_account = $client_account;
        $userTokenMapper = $this->getUserTokenMapper();       
        $tokenObj = $userTokenMapper->findById($client_account->getUserTokenId());
        $this->getApiService()->setClientId($client_account->getAccountId());
        $this->setAccessToken($client_account,$tokenObj->getToken());
        return $this;        
    }

    public function setAccessToken($client_account,$api_auth_info){
          
        if($api_auth_info){
                $api_access = unserialize($api_auth_info);

                if(list($new,$api_auth_info) = $this->getApiService()->verifyApiAccess($api_access)){

                        if($new){
                                 $userTokenMapper = $this->getUserTokenMapper();
                                 $tokenObj = $userTokenMapper->findById($client_account->getUserTokenId());						
                                 $tokenObj->setToken(serialize($api_auth_info));
                                 $userTokenMapper->update($tokenObj);
                                //$client_account->setApiAuthInfo(serialize($api_auth_info));
                                //$client_account = $this->getServiceManager()->get('jimmybase_clientaccounts_mapper')
                                //					   ->update($client_account);

                        }
                }

        }

        return $this;
         
	}


	public function setApiAccessToken($client_id){
		$clientMapper = $this->getServiceManager()->get('jimmybase_client_mapper');
		$userMapper   = $this->getServiceManager()->get('jimmybase_user_mapper');
		$client       = $clientMapper->findByAdwordsClientId($client_id);


		if($client){
				if($client->getParent()) {
					$api_access = $userMapper->getMeta($client->getParent(),'api_access');

					if($api_access){
					   $api_access = unserialize($api_access);
					   $this->getApiService()->verifyApiAccess($api_access);
					}
				}
		}

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

	echo '<pre>';
	//print_r($campaigns_array);
	exit;

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


	public function getClientCriteriaReport($args,$type){
	  if(empty($args))
	     return false;

	  # Load the service, so that the required classes are available.
	  $this->api->getAdWordsUser()
               ->LoadService('ReportDefinitionService', GoogleAdWords::ADWORDS_VERSION);

	  # Create selector.
	  $selector = new \Selector();
          
	  if($args['report_type'] == 'ACCOUNT_PERFORMANCE_REPORT' && $type == 'table' && in_array($args['report_type_id'], array(7, 8)) )
	  {
	  	unset($args['campaigns']);
	  }
	
	  else {
	  	$args['fields'][] = 'AdNetworkType1';
		}
	  $selector->fields = $args['fields'];
	  # Filter out deleted criteria.
	 // $selector->predicates[] = new \Predicate('Status', 'NOT_IN', array('DELETED'));
	  if(!empty($args['campaigns'])){
		  
	  	if($args['campaigns']!='all')
                    $selector->predicates[] = new \Predicate('CampaignId', 'IN', $args['campaigns']);
               
	  }
	  if(!empty($args['network_type'])){
	    $selector->predicates[] = new \Predicate('AdNetworkType1', 'IN', $args['network_type']);
		}
  	  if($args['device_type']){
	    $selector->predicates[] = new \Predicate('Device', 'IN', $args['device_type']);
	  }

	  # Create report definition.
	  $reportDefinition = new \ReportDefinition();

	  # Report additional options
	  $options = array();

	  if($args['date_range_type']){
	  	$reportDefinition->dateRangeType  = $args['date_range_type'];

		if($args['date_range_type']=='CUSTOM_DATE' || $args['report_type_id']==7 ||$args['report_type_id']==8 ){
		   $selector->dateRange = array('min' => date('Ymd',$args['date_range']['min']),
                                                'max' => date('Ymd',$args['date_range']['max']));
		   $reportDefinition->dateRangeType = 'CUSTOM_DATE';
		}


	  } 
	  elseif($args['date_range_compare']) {
		$reportDefinition->dateRangeType      = 'CUSTOM_DATE';

		if($args['date_range_compare'])
		   $selector->dateRange = array('min' => date('Ymd',$args['date_range_compare']['min']),
                                                              'max' => date('Ymd',$args['date_range_compare']['max']));
		  //$reportDefinition->startDate = $args['date_range_compare']['min'];
		  //$reportDefinition->endDate = $args['date_range_compare']['max'];
	  }

	  $reportDefinition->selector       = $selector;
	  $reportDefinition->reportName     = $args['report_type'].' #' . uniqid(); // Creating Unique key for report
	  $reportDefinition->reportType     = $args['report_type'];
	  $reportDefinition->downloadFormat = 'XML';
    //   var_dump($reportDefinition);
	  
	  # Exclude criteria that haven't recieved any impressions over the date range.
	  $options['includeZeroImpressions'] = false;

	 if($args['report_type'] == 'CAMPAIGN_PERFORMANCE_REPORT' && $type == 'table'){
		$options['includeZeroImpressions'] = true;
	}
	if($args['report_type'] == 'ACCOUNT_PERFORMANCE_REPORT' && $type == 'table' && in_array($args['report_type_id'], array(7, 8)) ) {
	//s	$reportDefinition->includeZeroImpressions = false;
	}

	 if($args['report_type'] == 'ADGROUP_PERFORMANCE_REPORT')
 	   $selector->predicates[] = new \Predicate('AdGroupStatus', 'NOT_IN', array('REMOVED'));

	  # Set additional options.
	  $options['version'] = GoogleAdWords::ADWORDS_VERSION;

          $reportUtils = new \ReportUtils();

	  $campaign_array =  $reportUtils->DownloadReport( $reportDefinition, null,
                                                           $this->api->getAdWordsUser(),
                                                           $options
                                                   ); 
 												            
// var_dump("iam in getclientcriteriareport");
// die;
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
            $this->setCache($this->getServiceLocator()->get('cache'));
        }

      return $this->cache;
    }

	public function setCache($cache){
        $this->cache = $cache;
    }
}
?>