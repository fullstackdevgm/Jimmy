<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

use GoogleAdwords\Adwords\Lib\AdWordsUser;
use GoogleAdwords\Adwords\v201209\Selector;
use GoogleAdwords\Adwords\v201209\Paging;
use GoogleAdwords\Adwords\v201209\OrderBy;
use GoogleAdwords\Adwords\Lib\AdWordsConstants;


class ReportsApi
{
	
	/**
     * @var int
     */
    protected $client_id;
	
	/**
	 * @var array
	 */
	protected  $csv_report_data;
	
	
	public function __construct(){
	
	
	}
	
	
	public function init($report){
		
		/*if($report){
		
			$this->setClientId($report->getClientId());
			
				$filePath = 'module/Admin/report1.csv';
			
				$row = 1;
				$lines =file($filePath);

				foreach($lines as $key => $data)
				{
					if($key == 0){
						$keys   = explode(',',$data);
					} else{ 
						
					    $arr   = explode(',',$data);
						
						foreach($arr as $k => $v){
							$new_arr[trim(strtolower(str_replace(' ','_',$keys[$k])))] = trim($v);
						}
						
						$report_data[] = $new_arr;
					}
				}
				
			if(is_array($report_data))
			   $this->csv_report_data = $report_data;
			
		}*/
	}
	
	
	 /**
     * Get csv_report_data.
     *
     * @return array
     */
	public function getCsvReportData(){

		return $this->csv_report_data;
	}
	
	/**
     * Set csv_report_data.
     *
     * @param string $csv_report_data
     * @return ReportsApi
     */
	public function setCsvReportData($csv_report_data){
		
		$this->csv_report_data = $csv_report_data;
		return $this;
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
        $this->client_id = (int)$client_id;
        return $this;
    }
	
	
	public function getAllCampaigns(){
		
		  $user = new AdWordsUser();

		  // Log every SOAP XML request and response.
		  $user->LogAll();
		  
		  // Get the service, which loads the required classes.
		  $campaignService = $user->GetService('CampaignService', ADWORDS_VERSION);
		
		  // Create selector.
		  $selector = new Selector();
		  $selector->fields = array('Id', 'Name');
		  $selector->ordering[] = new OrderBy('Name', 'ASCENDING');
		
		  // Create paging controls.
		  $selector->paging = new Paging(0, AdWordsConstants::RECOMMENDED_PAGE_SIZE);
		
		  do {
			// Make the get request.
			$page = $campaignService->get($selector);
		
			// Display results.
			if (isset($page->entries)) {
			  foreach ($page->entries as $campaign) {
				$campaigns_array[] = $campaign;				
			  }
			}
			// Advance the paging index.
			$selector->paging->startIndex += AdWordsConstants::RECOMMENDED_PAGE_SIZE;
		  } while ($page->totalNumEntries > $selector->paging->startIndex);
	
		return $campaigns_array;
	}	 	
}
?>