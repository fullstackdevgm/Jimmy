<?php
namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\View\Model\ViewModel;

use ZfcBase\EventManager\EventProvider;
use JimmyBase\Mapper\WidgetInterface as WidgetMapperInterface;

use BingAds\Reporting\SubmitGenerateReportRequest;
use BingAds\Reporting\CampaignPerformanceReportRequest;
use BingAds\Reporting\KeywordPerformanceReportRequest;
use BingAds\Reporting\AccountPerformanceReportRequest;
use BingAds\Reporting\AdGroupPerformanceReportRequest;
use BingAds\ReportingService\AdPerformanceReportRequest;
use BingAds\Reporting\ReportFormat;
use BingAds\Reporting\ReportAggregation;
use BingAds\Reporting\AccountThroughAdGroupReportScope;
use BingAds\Reporting\AccountThroughAccountGroupReportScope;
use BingAds\Reporting\AccountThroughAdReportScope;
use BingAds\Reporting\AccountThroughKeywordReportScope;
use BingAds\Reporting\AccountThroughCampaignReportScope;
use BingAds\Reporting\AccountThroughAccountReportScope;

use BingAds\Reporting\CampaignReportScope;
use BingAds\Reporting\ReportTime;
use BingAds\Reporting\Date;
use BingAds\Reporting\ReportTimePeriod;
use BingAds\Reporting\CampaignPerformanceReportFilter;
use BingAds\Reporting\KeywordPerformanceReportFilter;
use BingAds\Reporting\DeviceTypeReportFilter;
use BingAds\Reporting\KeywordPerformanceReportColumn;
use BingAds\Reporting\PollGenerateReportRequest;
use BingAds\Reporting\ReportRequestStatusType;
use BingAds\Reporting\KeywordPerformanceReportSort;
use BingAds\Reporting\SortOrder;

class BingAds extends EventProvider implements ServiceManagerAwareInterface
{

	private $data;

	private $args;

	private $dataCompare;

	private $units = array();

	private $report_renderer  = null;

	private $report_params_service = null;

	private $metrics_format_service = null;

	private $request = null;

	private $currency = 'AUD';

	private	$formatFields = array('ctr','clicks','impressions','cost','costConvertedClick','avgCPC','avgPosition','searchImprShare',
                          'ga:percentNewVisits','ga:visitBounceRate','ga:pageviewsPerVisit','ga:entrances','ga:exitRate','ga:pageValue',
                          'ga:transactionsPerVisit','ga:transactionRevenue','ga:avgTimeOnSite');

	/**
     * @var ServiceManager
     */
    protected $serviceManager;


	public function __construct(){


	  $this->units = array('clicks'				=>	array('format'=>'%s',	'decimal'=>0),
	  					   'impressions'		=>	array('format'=>'%s',	'decimal'=>0),
						   'conv1PerClick' 		=> 	array('format'=>'%s',	'decimal'=>0),
						   'ctr' 				=>  array('format'=>'%s%%',	'decimal'=>2) ,
						   'avgCPC' 			=>  array('format'=>'A$%s',	'decimal'=>2),
						   'cost' 				=>  array('format'=>'A$%s',	'decimal'=>2),
						   'costConv1PerClick' 	=> 	array('format'=>'A$%s',	'decimal'=>2),
						   'convRate1PerClick' 	=> 	array('format'=>'%s%%',	'decimal'=>2),
						   'avgPosition'	   	=> 	array('format'=>'%s',	'decimal'=>1)
						  );

	   # Set the Selector Fields
	   $this->_mapSelectorFields();

	   # Set the Report Type
	   $this->_mapReportType();


	}

	public function loadReport($widget,$client_account,$download=false){
                if (!$client_account->getUserTokenId()) {
                    return array("success" =>false, "message" => "migration not done");
                }
		$request   = $this->getRequest();

		$report_id = $widget->getReportId();
		$report    = $this->getServiceManager()->get('jimmybase_reports_service')
				   	   	  ->getMapper()
				   	   	  ->findById($report_id);

		$client    = $this->getServiceManager()->get('jimmybase_client_service')
				   	   	  ->getClientMapper()
				   	   	  ->findById($report->getUserId());

	   	if(method_exists($request, 'getQuery'))
		   $getParams = $request->getQuery()->toArray();

		$args = $this->prepareParams($widget,$getParams);



		$args['channel'] = $client_account->getChannel();

		$this->args      = $args;


		switch($widget->getType())
		{

		   case 'kpi':

				$result  = $this->setClientAccount($client_account)
								->requestReport($args,$widget);

				$args_compare = $args;

				if($args['date_range_compare']){
				  unset($args_compare['date_range']);
				  unset($args_compare['date_range_type']);

				  $result_compare  = $this->setClientAccount($client_account)
				  					      ->requestReport($args_compare,$widget);
				}

		   		$kpiHtml = $this->prepareResult($result,$result_compare)
	            				->renderKPI($report,$widget,$args,$download);

				$return = $kpiHtml;
				break;

		   case 'graph':
				$result  = $this->setClientAccount($client_account)
								->requestReport($args,$widget);

				$graph = $this->prepareResult($result)
	             		  	  ->renderGraph($report,$widget,$args,$download);


				$return = $graph;
		   		break;

		   case 'table':
				$result  = $this->setClientAccount($client_account)
								->requestReport($args,$widget);

				$table 	 = $this->prepareResult($result)
	            		     	->renderTable($report,$widget,$args,$download);
				$return = $table;
				break;

		   case 'notes':

		   		$notesHtml = $this->getReportRenderer()
								  ->setViewRenderer($this->getServiceManager()->get('viewrenderer'))
	            		      	  ->renderNotes($report,$widget);

				$return = array('success'=>true,'html' => $notesHtml);

				break;

		}

		return $return;
	}

	public  function prepareParams($widget,$opParams = null){

	  $metrics_service  = $this->getMetricsService();

	  $fields = unserialize($widget->getFields());

	  	if($opParams['date_range']) {
	       $date_range = $opParams['date_range'];
	  	} else {
			if($fields['date_range']){
		   	   $date_range = $fields['date_range'];
		  	}
	    }


	    if($fields['compare_dates']){
				if($fields['date_range_compare'] == 'previous_period'){
				   $params['date_range_compare'] = $this->_getCompareDateRangeType($metrics_service->getDateRange($date_range));
				} else {
				   $params['date_range_compare'] = array('min' => strtotime($fields['date_range_custom_min_compare']),'max' => strtotime($fields['date_range_custom_max_compare']));
				}
		}
	    //var_dump($params);exit;

	    switch($widget->getType()){

	  		case 'table':

	  		 		if($fields['sort_by'])
	    			   $params['sort_by'] = $this->_getSelectorField($metrics_service->getBingAdsKPI($fields['sort_by']))[1];

					if($fields['show_top'])
	    			   $params['show_top'] = $fields['show_top'];

				   # Fields for Raw Data
				  if($table_metrics = $fields['raw_data']){
					  foreach($table_metrics as  $value){
							$selector_field									= $this->_getSelectorField($metrics_service->getBingAdsKPI($value)[1]);

							$params['fields_raw_data'][$selector_field[1]]	= $selector_field;
							$params['fields'][]								= $selector_field[0];


					  }
				  }

				  break;
			case 'kpi':

				# Fields for KPI
				if($kpi_metrics = $fields['kpi']){

					  foreach($kpi_metrics as  $value){

							$kpi_field 			= $metrics_service->getBingAdsKPI($value);
							$selector_field		= $this->_getSelectorField($kpi_field[1]);

							$params['kpi_fields'][$selector_field[1]]= $selector_field;
							$params['kpi_fields'][$selector_field[1]][0]  = $kpi_field[0];

							$params['fields'][]		= $selector_field[0];
					  }

				}

				break;
		case 'graph':
				  /* Selector Fields */
				 $params['field']	   = $this->_getSelectorField($metrics_service->getBingAdsMetrics($fields['metrics_type'],$fields['metrics']));
				 $params['fields'][]   = $params['field'][0];

				 if($fields['compare']) {
					$params['field_compare'] = $this->_getSelectorField($metrics_service->getBingAdsMetrics($fields['metrics_type_compare'],$fields['metrics_compare']));
					$params['fields'][]      = $params['field_compare'][0];
				 }
	  		break;
	  }



	  if(isset($fields['report_type']))
	  	 $params['report_type'] = $this->_getReportType($metrics_service->getReportType($fields['report_type']));
	  else
	     $params['report_type'] ='CAMPAIGN_PERFORMANCE_REPORT';


	  switch($params['report_type']){

	  			case 'CAMPAIGN_PERFORMANCE_REPORT':

						$params['fields'][]     = 'CampaignId';
						$params['fields'][]     = 'CampaignName';
						$params['extra_fields'] = array('CampaignName'=>array('CampaignName','CampaignName','CampaignName'));
 						$params['group_by']		= 'CampaignId';

				break;

				case 'ACCOUNT_PERFORMANCE_REPORT':

						$params['fields'][]   = 	'AccountId';
						$params['fields'][]   = 	'AccountDescriptiveName';

						$params['extra_fields'] =	array( 'accountID' => array('AccountId','accountID','Account Id'),
														   'account' => array('AccountDescriptiveName','account','Account')
														  );
						$params['group_by']		= 'accountID';

				break;
				case 'KEYWORDS_PERFORMANCE_REPORT':
					    $params['fields'][]   = 	'KeywordText';
					    $params['fields'][]   = 	'KeywordMatchType';
					    $params['extra_fields']['keyword']   =  array('KeywordText','keyword','Keyword');
					    $params['extra_fields']['matchType']   =  array('KeywordMatchType','matchType','MatchType');



						$params['fields'][]   = 	'CampaignId';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';

				 		$params['group_by']		= 'keyword';
			    break;
				case 'ADGROUP_PERFORMANCE_REPORT':
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';

						$params['extra_fields']['campaign']  =   array('CampaignName','CampaignName' ,'CampaignName');
						$params['extra_fields']['adGroup']  =   array('AdGroupName','adGroup','Ad Group');

				 		$params['group_by']		   = 'adGroupID';
				break;
				case 'AD_PERFORMANCE_REPORT':
						$params['fields'][]   =     'PromotionLine';
						$params['fields'][]   =     'Description1';
						$params['fields'][]   =     'Description2';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';

						$params['extra_fields']['ad']  =   array('Ad','ad','Ad');
						$params['extra_fields']['descriptionLine1']   =  array('Description1','descriptionLine1','DescriptionLine1');
						$params['extra_fields']['descriptionLine2']   =  array('Description2','descriptionLine2','DescriptionLine2');

						$params['extra_fields']['adGroup']  =   array('AdGroupName','adGroup','Ad Group');

				 		$params['group_by']		= 'adGroupID';
				break;
	  			case  'SEARCH_QUERY_PERFORMANCE_REPORT':
						$params['fields'][]   = 	'Query';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';
						$params['fields'][]   = 	'KeywordTextMatchingQuery';
						$params['fields'][]   = 	'MatchType';

						$params['extra_fields']['CampaignName']   =  array('CampaignName','CampaignName','CampaignName');
						$params['extra_fields']['searchTerm']   =  array('Query','searchTerm','Search term');
						$params['extra_fields']['keyword']   =  array('KeywordTextMatchingQuery','keyword','Keyword');
					    $params['extra_fields']['matchType']   =  array('MatchType','matchType','Match');
						$params['extra_fields']['adGroup']   =  array('AdGroupName','adGroup','Ad Group');

				 		$params['group_by']			= 'searchTerm';

	  			break;
	  }



	  $params['fields'][]   = 	'CurrencyCode';
	  $params['fields']     = array_unique($params['fields']);

	  $params['show_campaign']	  = $fields['show_campaign'];


	  if(is_array($fields['device_type'])){
	  	foreach ($fields['device_type'] as $device_type) {
	  		if($device_option  = $metrics_service->getDeviceOptions($device_type)[1])
	  		   $device_types[] = $device_option;
	  	}
	  	  $params['device_type']	= $device_types;
	  }


 	  $params['date_range']       = $this->_parseDateRange($metrics_service->getDateRange($date_range));
	  $params['date_range_type']  = $this->_getDateRangeType($metrics_service->getDateRange($date_range));

	  if($opParams['date_range'] && $date_range == 14){
	   	 $params['date_range'] = array('min' => strtotime($opParams['min']),'max' => strtotime($opParams['max']));
	  } else if($date_range == 14)
		 $params['date_range'] = array('min' => strtotime($fields['date_range_custom_min']),'max' => strtotime($fields['date_range_custom_max']));

  		$params['date_range_formatted'] = array('min' => date('F j, Y',$params['date_range']['min']),
	   	 	                                  'max' => date('F j, Y',$params['date_range']['max']));

  		if($params['date_range_compare']){
  			$params['date_range_compare_formatted'] = array('min' => date('F j, Y',$params['date_range_compare']['min']),
	   	 	                                  'max' => date('F j, Y',$params['date_range_compare']['max']));


  		}

	  $params['campaigns']   	  = $fields['campaigns'];
	  $params['widget_id']        = $widget->getId();
	  return $params;
	}



	private function _getSelectorField($selector_field_key /* This is the display value */){

		if(is_array($this->selector_fields) && isset($this->selector_fields[$selector_field_key]))
		  return $this->selector_fields[$selector_field_key];

		return false;
	}




	private function _mapSelectorFields(){

		$this->selector_fields = array(  'Clicks' 							=> 	array('Clicks','Clicks','Clicks','icon-bar-chart-o'),
										 'Impressions' 						=> 	array('Impressions','Impressions','Impr.','icon-eye'),
										 'Ctr' 								=> 	array('Ctr','Ctr','CTR','icon-random'),
										 'AverageCpc' 						=> 	array('AverageCpc','AverageCpc','Avg. CPC','icon-bookmark-o'),
										 'Spend' 							=> 	array('Spend','Spend','Spend','icon-dollar'),
										 'AveragePosition' 						=> 	array('AveragePosition','AveragePosition','Avg. Pos.','icon-sort-numeric-asc'),
										 'Conversions' 						=> 	array('Conversions','Conversions','Conv.','icon-check'),
										 'ConversionRate' 					=> 	array('ConversionRate','ConversionRate','Conv. Rate'),
										 'CostPerConversion' 				=> 	array('CostPerConversion','CostPerConversion','CostPerConversion'),
										 'Keyword'						    => 	array('Keyword','Keyword','Keyword'),
										 'AdGroupName' 						=> 	array('AdGroupName','AdGroupName','AdGroupName'),
										 'CampaignName'						=>	array('CampaignName','CampaignName','CampaignName'),
									 );

	}

	private function _getDateRangeType($date_range){

	  switch($date_range){
		  	case 'Today': // Today
				 return 'TODAY';
			break;
			case 'Yesterday':
				 return 'YESTERDAY';
			break;

			case 'This week (Sun - Today)':
				 return 'THIS_WEEK_SUN_TODAY';
			break;

			case 'This week (Mon - Today)':
				 return 'THIS_WEEK_MON_TODAY';
			break;

			case 'Last 7 days':
				 return 'LAST_7_DAYS';
			break;

			case 'Last week (Sun - Sat)':
				 return 'LAST_WEEK_SUN_SAT';
			break;

			case 'Last week (Mon - Sun)':
				 return 'THIS_WEEK_MON_SUN';
			break;

			case 'Last business week (Mon - Fri)':
				 return 'LAST_BUSINESS_WEEK';
			break;

			case 'Last 14 days':
				 return 'LAST_14_DAYS';

			break;
			case 'Custom':
				 return 'CUSTOM_DATE';

			break;
			case 'This month':
				 return 'THIS_MONTH';
			break;

			case 'Last 30 days':
				  return 'LAST_30_DAYS';
			break;

			case 'Last month':
				  return 'LAST_MONTH';
			break;

			case 'All time':
				return 'ALL_TIME';
			break;
	  }

	}



	private function _getCompareDateRangeType($date_range,$custom=null){

	  switch($date_range){
		  	case 'Today': // Today
					return array('min' => strtotime('-1 day'), 'max' => strtotime('-1 day') );
			break;
			case 'Yesterday':
					return array('min' => strtotime('-2 day'), 'max' => strtotime('-2 day') );
			break;

			case 'This week (Sun - Today)':
				  $min =  strtotime('last sun - 1 week');
				  $max =  strtotime('today - 1 week');
				  return array('min' => $min, 'max' => $max );
			break;

			case 'This week (Mon - Today)':
				  $min =  strtotime('last mon -1 week');
				  $max =  strtotime('today -1 week');
				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last 7 days':
				  $max      =  strtotime('yesterday - 7 day');
				  $min      =  strtotime('-6 day',$max);

				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last week (Sun - Sat)':
				  $max =  strtotime('last sat - 1 week');
				  $min =  strtotime('-6 day',$max);
				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last week (Mon - Sat)':
				  $max   =  strtotime('last sat - 1 week');
				  $min   =  strtotime('-5 day',$max);
				  return array('min' => $min, 'max' => $max );
			break;
			case 'Last business week (Mon - Fri)':
				  $max   =  strtotime('last fri - 1 week');
				  $min   =  strtotime('-4 day',$max);
				  return array('min' => $min, 'max' => $max );
			break;
			case 'Last 14 days':
				  $max      =  strtotime('yesterday - 14 day');
				  $min      =  strtotime('-13 day',$max);

				  return array('min' => $min, 'max' => $max );

			break;
			case 'This month':
				  $max   =  strtotime('last day of last month');
				  $min   =  strtotime('first day of last month');
				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last 30 days':
				  $max   =  strtotime('yesterday - 30 day');
				  $min   =  strtotime('-29 day',$max);
				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last month':
				  $max   =  strtotime('last day of last month - 1 month');
				  $min   =  strtotime('first day of last month - 1 month');

				  return array('min' => $min, 'max' => $max );
			break;

			case 'All time':
				 /*
				  $max   =  strtotime('last  day or last month');
				  $min     =  strtotime('first day of last month');*/
				return null;
			break;

			case 'Custom':
				  $max   =  strtotime("{$custom['max']}");
				  $min   =  strtotime("{$custom['min']}");
				  $duration = $max - $min;

				  $dur_days = floor($duration/(60*60*24))	;
				  $dur_days++;
				  $max   = strtotime("{$custom['max']} - {$dur_days} day");
				  $dur_days--;
				  $min   = strtotime("- {$dur_days} days",$max);

				  return array('min' => $min, 'max' => $max );
			break;
	  }

	}
	private function _parseDateRange($date_range){

	  switch($date_range){
		  	case 'Today': // Today
					return array('min' => strtotime('today'), 'max' => strtotime('today') );
			break;
			case 'Yesterday':
					return array('min' => strtotime('-1 day'), 'max' => strtotime('-1 day') );
			break;

			case 'This week (Sun - Today)':
				  $min =  strtotime('last sun');
				  $max   =  strtotime('today');
				  return array('min' => $min, 'max' => $max );
			break;

			case 'This week (Mon - Today)':
				  $min =  strtotime('last mon');
				  $max =  strtotime('today');
				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last 7 days':
				  $max      =  strtotime('yesterday');
				  $min      =  strtotime('-6 day',$max);

				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last week (Sun - Sat)':
				  $saturday =  strtotime('last sat');
				  $sunday   =  strtotime('-6 day',$saturday);
				  return array('min' => $sunday, 'max' => $saturday );
			break;

			case 'Last week (Mon - Sun)':
				  $monday   =  strtotime('last week');
				  $sunday   =  strtotime('last sun');
				  return array('min' => $monday, 'max' => $sunday );
			break;
			case 'Last business week (Mon - Fri)':
				  $monday   =  strtotime('last week');
				  $sunday   =  strtotime('last fri');
				  return array('min' => $monday, 'max' => $sunday );
			break;
			case 'Last 14 days':
				  $max      =  strtotime('yesterday');
				  $min      =  strtotime('-13 day',$max);
				  return array('min' => $min, 'max' => $max );
			break;
			case 'Custom':
				  $monday   =  strtotime('last week');
				  $sunday   =  strtotime('today');
				  return array('min' => $monday, 'max' => $sunday );
			break;
			case 'This month':
				  $max   =  strtotime('today');
				  $min   =  strtotime('first day of this month');
				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last 30 days':
				  $max   =  strtotime('yesterday');
				  $min   =  strtotime('-29 day',$max);
				  return array('min' => $min, 'max' => $max );
			break;

			case 'Last month':
				  $max   =  strtotime('last day of last month');
				  $min   =  strtotime('first day of last month');

				  return array('min' => $min, 'max' => $max );
			break;

			case 'All time':
				 /*
				  $max   =  strtotime('last  day or last month');
				  $min     =  strtotime('first day of last month');*/
				return null;
			break;
			default:
			   return null;

	  }

	}


	private function _getReportType($report_type_key ){

		if(is_array($this->report_type) && isset($this->report_type[$report_type_key]))
		  return $this->report_type[$report_type_key];

		return false;
	}

	private function _mapReportType(){

		$this->report_type = array(  'Campaign' 	=> 'CAMPAIGN_PERFORMANCE_REPORT',
									 'Account'  	=> 'ACCOUNT_PERFORMANCE_REPORT',
									 'Ad Group' 	=> 'ADGROUP_PERFORMANCE_REPORT',
									 'Ad'       	=> 'AD_PERFORMANCE_REPORT',
									 'Keyword'  	=> 'KEYWORDS_PERFORMANCE_REPORT'
								   );
	}

	private function _getReportTypeRequestMeta($report_type){

		$this->report_type_request = array(
			                                 'CAMPAIGN_PERFORMANCE_REPORT' 	=>
			                                 	array('request'=>"CampaignPerformanceReportRequest",
			                                 		  'scope'=>'AccountThroughCampaignReportScope',
			                                 		  'filter'=>'CampaignPerformanceReportFilter',
			                                 		  'column'=>'CampaignPerformanceReportColumns'),
											 'ACCOUNT_PERFORMANCE_REPORT'  	=>
											 	array('request'=>'AccountPerformanceReportRequest','scope'=>'AccountThroughAccountReportScope','filter'=>'AccountPerformanceReportFilter'),
											 'ADGROUP_PERFORMANCE_REPORT' 	=>
											 	array('request'=>'AdGroupPerformanceReportRequest','scope'=>'AccountThroughAdGroupReportScope','filter'=>'AdGroupPerformanceReportFilter'),
											 'AD_PERFORMANCE_REPORT'       	=>
											 	array('request'=>'AdPerformanceReportRequest','scope'=>'AccountThroughAdReportScope','filter'=>'AdPerformanceReportFilter'),
											 'KEYWORDS_PERFORMANCE_REPORT'  =>
											 	array('request'=>'KeywordPerformanceReportRequest','scope'=>'AccountThroughKeywordReportScope','filter'=>'KeywordPerformanceReportFilter')
								   		);

		return $this->report_type_request[$report_type];
	}


	private function _getReportTypeRequestInstance($report_type){

		switch ($report_type) {
			case 'CAMPAIGN_PERFORMANCE_REPORT':
					return new CampaignPerformanceReportRequest();
				break;
			case 'ACCOUNT_PERFORMANCE_REPORT':
					return new AccountPerformanceReportRequest();
				break;
			case 'ADGROUP_PERFORMANCE_REPORT':
					return new AdGroupPerformanceReportRequest();
				break;
			case 'AD_PERFORMANCE_REPORT':
					return new AdPerformanceReportRequest();
				break;
			case 'KEYWORDS_PERFORMANCE_REPORT':
					return new KeywordPerformanceReportRequest();
				break;
			default:
				return false;
				break;
		}

	}

private function _getReportTypeScopeInstance($report_type){

		switch ($report_type) {
			case 'CAMPAIGN_PERFORMANCE_REPORT':
					return new AccountThroughCampaignReportScope();
				break;
			case 'ACCOUNT_PERFORMANCE_REPORT':
					return new AccountThroughAccountReportScope();
				break;
			case 'ADGROUP_PERFORMANCE_REPORT':
					return new AccountThroughAdGroupReportScope();
				break;
			case 'AD_PERFORMANCE_REPORT':
					return new AccountThroughAdReportScope();
				break;
			case 'KEYWORDS_PERFORMANCE_REPORT':
					return new AccountThroughKeywordReportScope();
				break;
			default:
				return false;
				break;
		}

	}


	public function requestReport($args,$widget){

		// Check if the report exists in the cache
		$hash       = md5(serialize($args)); // Serialize the object and hash it to get a key
	    $cache_key  = $widget->getId() . '-'.$hash;


	     # If cache exists
		if($this->getCache()->hasItem($cache_key)){
		    return  unserialize($this->getCache()->getItem($cache_key));
		}

		try{

					$proxy = $this->getServiceManager()->get('BingAdsApi')->getProxy('ReportingService');

					$bingads_api_service = $this->getServiceManager()->get('BingAdsApi');

					$request_meta = $this->_getReportTypeRequestMeta($args['report_type']);


			    	$report = $this->_getReportTypeRequestInstance($args['report_type']);

				    $report->Format = ReportFormat::Xml;
				    $report->ReportName = 'My Campaign Performance Report';
				    $report->ReturnOnlyCompleteData = false;
				    $report->Aggregation = ReportAggregation::Daily;


				    $report->Scope = $this->_getReportTypeScopeInstance($args['report_type']);
				    $report->Scope->AccountIds = null;
				    $report->Scope->AdGroups = null;
				    $report->Scope->Campaigns = array ();


				    if(is_array($args['campaigns'])){
				    	foreach ($args['campaigns'] as $key => $CampaignId) {
				    		$campaignReportScope = new CampaignReportScope();
						    $campaignReportScope->CampaignId = $CampaignId;
						    $campaignReportScope->AccountId  = $bingads_api_service->getAccountId();
						    $report->Scope->Campaigns[] 	 = $campaignReportScope;
				    	}
					}


			        $report->Time = new ReportTime();

    				if($args['date_range']){

    					$month_start =  date('m',$args['date_range']['min']);
    					$day_start   =  date('d',$args['date_range']['min']);
			        	$year_start  =  date('Y',$args['date_range']['min']);

			        	$month_end   =  date('m',$args['date_range']['max']);
    					$day_end     =  date('d',$args['date_range']['max']);
			        	$year_end    =  date('Y',$args['date_range']['max']);

					} elseif($args['date_range_compare']){

						$month_start =  date('m',$args['date_range_compare']['min']);
    					$day_start   =  date('d',$args['date_range_compare']['min']);
			        	$year_start  =  date('Y',$args['date_range_compare']['min']);

			        	$month_end   =  date('m',$args['date_range_compare']['max']);
    					$day_end     =  date('d',$args['date_range_compare']['max']);
			        	$year_end    =  date('Y',$args['date_range_compare']['max']);

					}

    				$report->Time->CustomDateRangeStart 	   = new Date();
			        $report->Time->CustomDateRangeStart->Month = $month_start;
			        $report->Time->CustomDateRangeStart->Day   = $day_start;
			        $report->Time->CustomDateRangeStart->Year  = $year_start;

			   	    $report->Time->CustomDateRangeEnd 		   = new Date();
			        $report->Time->CustomDateRangeEnd->Month   = $month_end;
			        $report->Time->CustomDateRangeEnd->Day     = $day_end;
			        $report->Time->CustomDateRangeEnd->Year    = $year_end;


				    $report->Columns   = $args['fields'];
				    $report->Columns[] =  KeywordPerformanceReportColumn::TimePeriod;



				    $encodedReport = new \SoapVar($report, SOAP_ENC_OBJECT, $request_meta['request'], $proxy->GetNamespace());

					$request = new SubmitGenerateReportRequest();
				    $request->ReportRequest = $encodedReport;
				    $reportRequestId =  $proxy->GetService()->SubmitGenerateReport($request)->ReportRequestId;


				    $waitTime = 5 * 1;
				    $reportRequestStatus = null;

				    // This sample polls every 30 seconds up to 5 minutes.
				    // In production you may poll the status every 1 to 2 minutes for up to one hour.
				    // If the call succeeds, stop polling. If the call or
				    // download fails, the call throws a fault.

				    for ($i = 0; $i < 10; $i++)
				    {
				    	sleep($waitTime);

				    	// PollGenerateReport helper method calls the corresponding Bing Ads service operation
				    	// to get the report request status.
						$request = new PollGenerateReportRequest();
						$request->ReportRequestId = $reportRequestId;
						$reportRequestStatus =  $proxy->GetService()->PollGenerateReport($request)->ReportRequestStatus;

				    	if ($reportRequestStatus->Status == ReportRequestStatusType::Success ||
				    		$reportRequestStatus->Status == ReportRequestStatusType::Error)
				    	{
				    		break;
				    	}
				    }

				    if ($reportRequestStatus != null)
				    {
				    	if ($reportRequestStatus->Status == ReportRequestStatusType::Success)
				    	{
				    		$reportDownloadUrl = $reportRequestStatus->ReportDownloadUrl;
				    		$fileName 		   = md5(serialize($args));
				    		$reportFileName    = "./data/tmp-reports/".$fileName.".zip";

				    		if(copy($reportDownloadUrl,$reportFileName)){
				    			$zip = zip_open($reportFileName);
								if ($zip) {
									while ($zip_entry = zip_read($zip)) {

										if (zip_entry_open($zip, $zip_entry, "r")) {
											$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
											zip_entry_close($zip_entry);
										}
									}
								zip_close($zip);
								}
				    		}

				    		$this->getCache()->setItem($cache_key,serialize($buf));

				    		unlink($reportFileName);

				    		return $buf;
				    	}
				    	else if ($reportRequestStatus->Status == ReportRequestStatusType::Error)
				    		printf("The request failed. Try requesting the report " ."later.\nIf the request continues to fail, contact support.\n");
				    	else  // Pending
				    		printf("The request is taking longer than expected.\n " ."Save the report ID (%s) and try again later.\n",$reportRequestId);

				    }


		} catch(SoapFault $e){
		} catch (Exception $e) {
		}

	}



	public function prepareResult($result,$resultCompare=null){
		# Normal Result
		if($result)
		   $this->data = $this->__processResult($result);


		# Comparable Result
		if($resultCompare)
		   $this->dataCompare = $this->__processResult($resultCompare);


		//var_dump($this->data);
		//exit;
		return $this;
	}


	private function __processResult($result){

			$xml = simplexml_load_string($result);
			$columnClass = $this->_getReportTypeRequestMeta($this->args['report_type'])['column'];

			foreach($xml->{$columnClass}->Column as $column){
				$columns[] = (string)$column->attributes()->name;
			}


			foreach($xml->Table->Row as $row){
				foreach($columns as $column){
					//print_r($row->{$column});
						 $rowData[$column] = (string)$row->{$column}->attributes()['value'];
				}

				$rows[] = $rowData;
			}


			$data['columns']  		= $columns;
			$data['rows']  	  		= $rows;


		return $data;
	}



	private function __processKPIData($adwordsData,$args,$date_range){

		  	$start_date  = $date_range['min'];
	 	  	$end_date    = $date_range['max'];
		  //	$duration    =($end_date-$start_date)/86400;
		  	$date1 	     = new \DateTime(date('Y-m-d',$start_date));
		    $date2 	     = new \DateTime(date('Y-m-d',$end_date));
		    $duration    = $date2->diff($date1)->format("%a");
	      	$i 		     = 0;

			$kpiDataFields = array();

			$kpiFields = array();
			if(is_array($args['kpi_fields'])){
				foreach($args['kpi_fields']  as $column){
						$kpiFields[] = $column[1];
				}
			}

			$depFields = array();

			if(is_array($args['dependent_fields'])){
				foreach($args['dependent_fields']  as $column){
						$depFields[] = $column[1];
				}
			}



			$kpiDataFields = array_unique($kpiFields);
			$depFields     = array_unique($depFields);



			for($i = 0; $i <= (int)$duration; $i++){

				$day = date('n/j/Y',strtotime("-$i day",$end_date));
				$dataExistsForDate     = false;

					# Loop over each campaign data
					foreach($adwordsData['rows'] as $data){

						if($day != $data['GregorianDate'] )
						  continue;

						$dataExistsForDate = true;
						foreach($kpiDataFields as $field){

							if($field=='CurrencyCode')
							   $this->currency = $data[$field];


							if($field == 'AveragePosition'){

								if($i <= 13){ // Check for the last 14 days data only
									$kpiDataSegmented[$day]['sumAvgPos']+= $data[$field] * $data['Impressions'];
									$kpiDataSegmented[$day][$field]     += $data[$field];
								}

								$kpiDataTotal['sumAvgPos'] 			   += $data[$field] * $data['Impressions'];

						    } else {
						    	// Check for the last 14 days data only
						   		if($i<=13) $kpiDataSegmented[$day][$field]     += $data[$field];

							}

							$kpiDataTotal[$field] += $data[$field];
					   }


					    foreach($depFields as $field)
					   		   $depData[$day][$field]     += $data[$field];


						# For certain fields values returned
						# from adwords have to be manually processed
						if($depFields){
							foreach($depFields as $depField){
							    $depTotal[$depField][$i]   += $data[$depField];
								$depDataTotal[$depField]    = $depTotal[$depField][$i];
							}
						}

					}

			}


			$kpiDataSegmented = array_reverse($kpiDataSegmented);




			// Data for the sparklines
			foreach($kpiDataSegmented as $key => $data){
				if($data['CurrencyCode']){
				  $kpiDataSegmented[$key]['CurrencyCode'] = $data['CurrencyCode'];
				}

				if($data['sumAvgPos'])
				   $data['AveragePosition'] += $data['sumAvgPos'];

				$this->_applyManualCalculations($data,$depData[$key],array('Ctr','AverageCpc','Conversions','ConversionRate','AveragePosition'));


				foreach($kpiDataFields as $column){
					    // Format the value
						$newValue     = $this->getMetricsFormatService()->formatNumberWithoutSprintf($column,$data[$column]);
						$kpiDataSegementedTotal[$column][] = array($newValue);
				}

			}


			#Calculate the totals of the dependent fields
			if($depData){
				foreach($depData as $key => $data){
					foreach($depFields as $column){
							 $depDataTotal[$column] += $data[$column];
					}
				}
			}


			if($kpiDataTotal['sumAvgPos'])
			   $kpiDataTotal['AveragePosition'] = $kpiDataTotal['sumAvgPos'];


			# Perform Manual Calculations for Certain fields
			# The array is passed as reference
			$this->_applyManualCalculations($kpiDataTotal,$depDataTotal,array('Ctr','AverageCpc','Conversions','ConversionRate','AveragePosition'));


			foreach ($kpiDataTotal as $key => $value) {
					if($args['kpi_fields'][$key]){
						$newValue     = $this->getMetricsFormatService()->formatNumber($key,$value,$this->currency);
						$kpiDataTotalNew[] = array('value' => $newValue,'rawValue'=>$value,'caption'=>$args['kpi_fields'][$key][0],'key'=>$args['kpi_fields'][$key][1],'icon'=>$args['kpi_fields'][$key][3]);
					}
			}



		return array($kpiDataTotalNew,$kpiDataSegementedTotal);
	}

	public function renderKPI($report,$widget,$args,$download){


			if($this->data)
				list($kpiDataTotal,$kpiDataSegmented) 		  = $this->__processKPIData($this->data,$args,$args['date_range']);


			if($this->dataCompare){
				list($kpiDataTotalCompare ,$kpiDataSegmentedCompare) = $this->__processKPIData($this->dataCompare,$args,$args['date_range_compare']);
				foreach ($kpiDataSegmented as $key => $value) {
					foreach ($value as $k => $v) {
						$kpiDataSegmented[$key][$k][] = $kpiDataSegmentedCompare[$key][$k][0];
					}

				}
			}



			$kpiVars =  array(
			   					 'class'			   => 'moreStuff radius5 t1',
								 'args' 			   => $args,
								 'currency'			   => $this->currency,
								 'kpiDataTotal' 	   => $kpiDataTotal,
								 'kpiDataSegmented'    => $kpiDataSegmented,
								 'kpiDataTotalCompare' => $kpiDataTotalCompare,
								 'units'			       => $this->units
							  );


			if(!$download) return $kpiVars;


			$viewModel = new ViewModel();



			$viewModel->setTemplate('kpi')
					   ->setVariables(array(
					   					 'class'			   => 'moreStuff radius5 t1',
										 'args' 			   => $args,
										 'currency'		       => $this->currency,
										 'kpiDataTotal' 	   => $kpiDataTotal,
										 'kpiDataTotalCompare' => $kpiDataTotalCompare,
										 'kpiDataSegmented'    => $kpiDataSegmented,
										 'widget'			   => $widget,
										 'units'			   => $this->units
							  ));

			$kpiHtml = $this->getViewRenderer()
				 			->render($viewModel);

			return $kpiHtml;
	}


	public function renderTable($report,$widget,$args,$download=false){

			if(!is_array($this->data))
		   		 return false;


			if($args['sort_by']){
				$this->data['rows'] = $this->_sortData($this->data['rows'],$args['sort_by']);
			}

			if($args['show_top']){
				array_splice($this->data['rows'], $args['show_top']);
			}

			# First sort the data by the given sort column
			$sortedRawData = $this->_groupData($this->data,$args['group_by']);


			if($args['report_type'] == 'KEYWORDS_PERFORMANCE_REPORT'){
				if($sortedRawData){
					foreach($sortedRawData as $key => $sortData){
						foreach($sortData as $sortedData){

								$newKey = '';
							if($sortedData['matchType']=='Exact'){
								$newKey   =   '['.$key.']';
								$keyword    = '['.$sortedData['keyword'].']';
							} else if($sortedData['matchType']=='Phrase'){
								$newKey   =   '"'.$key.'"';
								$keyword  = '"'.$sortedData['keyword'].'"';
							}  else {
							    $newKey  = $key;
								$keyword = $sortedData['keyword'];
							}

								$sortedData['keyword']		 = $keyword;
								$sortedRawDataNew[$newKey][] = $sortedData;
						}
					}
				}
			} else if($args['report_type'] == 'AD_PERFORMANCE_REPORT'){
				if($sortedRawData){
					foreach($sortedRawData as $key => $sortData){

						foreach($sortData as $sortedData){
								$newKey = '';
								$newKey = $key.'-'.$sortedData['ad'];
								$sortedRawDataNew[$newKey][] = $sortedData;
						}
					}
				}
			}

			if($sortedRawDataNew)
				$sortedRawData = $sortedRawDataNew;


			$rawDataFields = array();

			foreach($args['fields_raw_data']  as $column){
					$rawDataFields[] = $column[1];
			}

			$depFields = array();

			if(is_array($args['dependent_fields'])){
				foreach($args['dependent_fields']  as $column){
						$depFields[] = $column[1];
				}
			}

			$rawDataFields = array_unique($rawDataFields,$kpiFields);
			$depFields     = array_unique($depFields);

			if($sortedRawData){
				foreach($sortedRawData as $key => $data){
					foreach($data as $k => $dataEach){
						foreach($args['extra_fields'] as $column){
							   $rawData[$key][$column[1]] = $dataEach[$column[1]];
						}

						foreach($rawDataFields as $field){

						   if($field == 'AveragePosition'){
								$rawData[$key]['sumAvgPos'] += $dataEach[$field] * $dataEach['Impressions'];
								$rawData[$key]['AveragePosition']    += $dataEach[$field];
						   } else {
								$rawData[$key][$field]      += $dataEach[$field];
						   }
					   }

					   foreach($depFields as $field)
							   $depData[$key][$field]     += $dataEach[$field];

						if($dataEach['CurrencyCode']){
					   	   $rawData[$key]['currency'] = $dataEach['CurrencyCode'];
					    }
					}

					if($rawData[$key]['AveragePosition']){
						$rawData[$key]['AveragePosition']  = $rawData[$key]['AveragePosition']/($k+1);
					}

				}
			}

			if($rawData){
				foreach($rawData as $key => $data){

					foreach($rawDataFields as $column)
							$rawDataTotal[$column] += $data[$column];

					if($data['sumAvgPos'])
					   $rawDataTotal['sumAvgPos'] += $data['sumAvgPos'];

					if($data['currency']){
				 	   $rawDataTotal['currency'] = $data['currency'];
					}
				}
			}

			if($depData){
				foreach($depData as $key => $data){
					foreach($depFields as $column){
							 $depDataTotal[$column] += $data[$column];
					}
				}
			}


			if($rawDataTotal['sumAvgPos'])
			   $rawDataTotal['AveragePosition'] = $rawDataTotal['sumAvgPos'];


			# Perform Manual Calculations for Certain Total Fields
			# The array is passed as reference
			$this->_applyManualCalculations($rawDataTotal,$depDataTotal,array('Ctr','AverageCpc','Conversions','ConversionRate','AveragePosition'));

			if($args['sort_by']){
				$rawData = $this->_sortData($rawData,$args['sort_by']);
			}

			unset($rawDataTotal['sumAvgPos']);

			$currency = $rawDataTotal['currency'];

			//if(!$download)
			unset($rawDataTotal['currency']);


			foreach ($rawDataTotal as $key => $value) {

					if(in_array($key,$formatFieldsTotal))
						$value     = $this->getMetricsFormatService()->calculateMetrics($key,$value,$currency);
					else{
						$value     = $this->getMetricsFormatService()->formatNumber($key,$value,$currency);
					}

					if($args['fields_raw_data'][$key])
						$fld = $args['fields_raw_data'][$key];
					else if($args['extra_fields'][$key])
						$fld = $args['extra_fields'][$key];

					$dataTotalNew[$fld[1]] = array('value' => $value,'caption'=>$fld[2],'key'=>$fld[1]);
		    }


		    $rawDataTotalNew = $dataTotalNew;

		    foreach ($rawData as $key => $values) {
		    			$dataNew   = array();
						unset($values['sumAvgPos']);

						if(!$download)
							unset($values['currency']);


						// Do some manual calculations
						$this->_applyManualCalculations($values,$depData[$key],array('Ctr','AverageCpc','Conversions','ConversionRate'));

						foreach ($values as $k => $value) {

							if($k=='CampaignName')
								$dataNew['CampaignName'] = $value;
							else {
								if(in_array($k,array_diff($this->formatFields,array('AveragePosition'))))
									$value     = $this->getMetricsFormatService()->calculateMetrics($k,$value,$currency);
								else
									$value     = $this->getMetricsFormatService()->formatNumber($k,$value,$currency);

								if($args['fields_raw_data'][$k])
									$fld = $args['fields_raw_data'][$k];
								else if($args['extra_fields'][$k])
									$fld = $args['extra_fields'][$k];
								else
									continue;

								if(is_numeric( $value ) && floor( $value ) != $value)
									$dataNew[$fld[1]] = (float)$value;
								elseif(is_numeric($value))
									$dataNew[$fld[1]] = (int)$value;
								else
									$dataNew[$fld[1]] = $value;

							}
						}

				$rawDataNew[] = $dataNew;
		    }

			//echo '<pre>';
		   // print_r($rawDataTotalNew);

			$tableVars =  array(
								 'field' 			   => $field,
								 'args' 	  		   => $args,
								 'rawData' 	  		   => $rawDataNew,
								 'rawDataTotal' 	   => $rawDataTotalNew,
								 'units'			   => $this->units,
								 'channel'			   => $channel
							  );

			if(!$download) return $tableVars;

			$viewModel = new ViewModel();

		 	$template = 'table';

		    if($download) $template = 'table-download';
//exit;


			$viewModel->setTemplate($template)
						   ->setVariables(array(
										 'currency'		       => $this->currency,
					   					 'widget'			   => $widget,
										 'field' 			   => $field,
										 'args' 	  		   => $args,
										 'rawData' 	  		   => $rawDataNew,
										 'rawDataTotal' 	   => $rawDataTotalNew,
										 'units'			   => $this->units,
										 'channel'			   => $channel
							  ));

			$html = $this->getViewRenderer()
						   ->render($viewModel);

			return $html;

	}





    public function renderGraph($report,$widget,$args,$download=false) {


		if(!is_array($this->data))
		    return false;

		  $start_date  = $args['date_range']['min'];
	 	  $end_date    = $args['date_range']['max'];
		  //$duration    =($end_date-$start_date)/86400;
		  $date1 	     = new \DateTime(date('Y-m-d',$start_date));
	      $date2 	   = new \DateTime(date('Y-m-d',$end_date));
	      $duration    = $date2->diff($date1)->format("%a");
		  $field	   = $args['field'][1];
	      $field_comp  = $args['field_compare'][1];
		  $i 		   = 0;

		  if(!$duration)
		  	$duration = 1;

			if(is_array($args['dependent_fields'])){
				foreach($args['dependent_fields']  as $column){
						$depFields[] = $column[1];
				}
				$depFields  =  array_unique($depFields);
			}

		$rawDataTotal = array();



			# Segmentation Logic -- Loop Over each day data
			# Since data are returned and segmented by day
			for($i = 0; $i <= (int)$duration; $i++){
				$day = date('n/j/Y',strtotime("+$i day",$start_date));

				$dataExistsForDate = false;

				if($this->data['rows']){
					# Loop over each campaign data
					foreach($this->data['rows'] as $data){

						if($day != $data['GregorianDate'] )
						  continue;

						$dataExistsForDate = true;

						# For AvgPosition  (avgPos * impressions)
					    if($field == 'AveragePosition'){
						 	if($data['Impressions'])
								$totals[$field][$day] += $data[$field] * $data['Impressions'];
					    } else {
						 	$totals[$field][$day]     += $data[$field];
					    }


						# For certain fields values returned
						# from adwords have to be manually processed
						if($depFields){
							foreach($depFields as $depField){
							    $depTotal[$depField][$i]   += $data[$depField];
								$depDataTotal[$depField]    = $depTotal[$depField][$i];
							}
						}



						if($field_comp){
							# For AvgPosition  (avgPos * impressions)
							if($field_comp == 'AveragePosition'){
								if($data['Impressions'])
								   $totals[$field_comp][$day] += $data[$field_comp] * $data['Impressions'];
							} else {
						   		   $totals[$field_comp][$day] += $data[$field_comp];
							}

						}
					}
				}

				# For Main Field
				$rawTotals[$field] = $totals[$field][$day] ;
				$this->_applyManualCalculations($rawTotals,$depDataTotal,array($field));
				$totals[$field][$day]  = $rawTotals[$field];

				# For Comparions
				$rawTotals[$field_comp] = $totals[$field_comp][$day] ;
				$this->_applyManualCalculations($rawTotals,$depDataTotal,array($field_comp));
				$totals[$field_comp][$day]  = $rawTotals[$field_comp];

				if(!$dataExistsForDate){
				  $totals[$field][$day]			+=	null;
				  $totals[$field_comp][$day]	+=	null;
				}
			}

			foreach(array_keys($totals[$field]) as $date){
				$new_date[] = date('d',strtotime($date));
			}


			$formatFields = array('Ctr','Clicks','Impressions','Spend','Conversions','ConversionRate');


			foreach ($totals[$field] as $key => $val) {
				$day = date('Y-m-d',strtotime($key));

				if(in_array($field,$formatFields))
					$val     = $this->getMetricsFormatService()->calculateMetrics($field,$val,$currency);
				else
					$val     = $this->getMetricsFormatService()->formatNumber($field,$val,$currency);

				$newVal  = array('x' => $day,'y' => $val);


				if($totals[$field_comp]){
					if(in_array($field_comp,$this->formatFields))
						$newVal['z']     = $this->getMetricsFormatService()->calculateMetrics($field_comp,$totals[$field_comp][$key],$currency);
					else
						$newVal['z']     = $this->getMetricsFormatService()->formatNumber($field,$totals[$field_comp][$key],$currency);

				}

				$newTotal[] = $newVal;
			}



		$graphVars =  array(
									 'args' 			   => $args,
									 'field' 			   => $field,
									 'totals' 	  		   => $newTotal,
									 'new_date' 		   => $new_date,
									 'field_comp'		   => $field_comp,
									 'currency'		       => $this->currency,

						  );

		if(!$download)
		   return $graphVars;

		$viewModel = new ViewModel();


		$template	= 'graph';

		if($download) $template = 'graph-download';


		foreach ($totals[$field] as $key => $val) {
			$newVal  = array('x' => $key,'y' => $val);

			if($totals[$field_comp]){
				$newVal['z']  = $totals[$field_comp][$key];
			}

			$newTotalForDownload[] = $newVal;
		}

		$viewModel->setTemplate($template)
				   ->setVariables(array(
									'currency'		       => $this->currency,
									 'args' 			   => $args,
									 'field' 			   => $field,
									 'totals' 	  		   => $newTotalForDownload,
									 'new_date' 		   => $new_date,
									 'field_comp'		   => $field_comp,
									 'widget'			   => $widget
						  ));

		$script = $this->getViewRenderer()
					   ->render($viewModel);

		return $script;
	}


	private function _applyManualCalculations(&$dataTotal,$depDataTotal,$fields){

		if(is_array($depDataTotal) && !empty($depDataTotal))
		@extract($depDataTotal);

		# Extract the array values and create variables out of them
		if(is_array($dataTotal) && !empty($dataTotal))
		@extract($dataTotal);


		foreach($fields as $field){
			switch($field){

					case 'Ctr':
								if($Impressions > 0)
									$dataTotal['Ctr'] = ($Clicks/$Impressions)*100;
							    else
									$dataTotal['Ctr'] = '0';

								break;
					case 'AverageCpc':
								if($Clicks > 0)
								   $dataTotal['AverageCpc'] = $Spend/$Clicks;
							    else
								   $dataTotal['AverageCpc'] = '0.00';

								break;
					case 'Conversions':
								if($Conversions)
								  $dataTotal['Conversions'] = ($Spend/$Conversions)/1000000;
								else
								  $dataTotal['Conversions'] = '0.00';

								break;
					case 'ConversionRate':

								if($Clicks)
								   $dataTotal['ConversionRate'] = ($Conversions/$Clicks)*100;
								else
								   $dataTotal['ConversionRate'] = '0';

								break;

					case 'AveragePosition':
								if($Impressions)
								   $dataTotal['AveragePosition'] = ($AveragePosition/$Impressions);
								else
								   $dataTotal['AveragePosition'] = '0';
					break;
			}

		}

		//echo '<pre>';print_r($dataTotal);


		return $dataTotal;
	}



	private function _sortData($data,$sort_by){

		if(!is_array($data) or empty($sort_by))
			return  false;

		usort($data,function($a,$b)  use ($sort_by){
			if ($a[$sort_by] == $b[$sort_by]) {
		        return 0;
		    }
		    return ($a[$sort_by] < $b[$sort_by]) ? 1 : -1;

		});


	 	return $data;
	}


	private function _groupData($adwordsRawData,$group_by){

		if(!is_array($adwordsRawData) or empty($group_by) or empty($adwordsRawData['rows']))
			return  false;

		foreach($adwordsRawData['rows'] as $data){
				$sortedData[$data[$group_by]][] = $data;
		}

	 	return $sortedData;
	}



	 /**
     * Get api.
     *
     * @return string
     */
    public function getApiService()
    {
        return $this->getServiceManager()->get('BingAdsApi');
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
                    $this->getApiService()->setAccountId($client_account->getAccountId());
                    $this->setAccessToken($tokenObj->getToken(),$client_account);

            return $this;
        
    }

    public function setAccessToken($api_auth_info,$client_account = null){
           
		if($api_auth_info){

			if($client_account){

			    if(list($new,$api_auth_info) = $this->getApiService()->verifyApiAccess(unserialize($api_auth_info))){

					if($new){
                                                $userTokenMapper = $this->getUserTokenMapper();
                                                $tokenObj = $userTokenMapper->findById($client_account->getUserTokenId());						
                                                $tokenObj->setToken(serialize($api_auth_info));
                                                $userTokenMapper->update($tokenObj);
					//	$client_account = $this->getServiceManager()->get('jimmybase_clientaccounts_mapper')
					//						   ->update($client_account);
					}

				}
			}

		    $this->getApiService()->setAccessToken($api_auth_info);

		}


		return $this;
          
	}


	public function getCampaigns(){
		$bingads_api_service = $this->getServiceManager()->get('BingAdsApi');

        # Set the cache key for the client
		$key  = $this->getClientAccount()->getAccountId();

		if($this->getCache()->hasItem($key)){
		   $cache_campaigns = unserialize($this->getCache()->getItem($key));
		}


		if(isset($cache_campaigns['campaigns'])){
			$campaigns_array = $cache_campaigns['campaigns'];
		} else {

			$request = new \BingAds\CampaignManagement\GetCampaignsByAccountIdRequest();
	        $request->AccountId = $this->getApiService()->getAccountId();

	        try{
	        	$campaigns_array = $bingads_api_service->getService('CampaignManagementService')->GetCampaignsByAccountId($request)->Campaigns->Campaign;
	        } catch(\Exception $e){

	        	return array('success'=>false,'message'=>$e->getMessage());
	        }

			$cache_campaigns['campaigns'] = $campaigns_array;
			$this->getCache()->setItem($key,serialize($cache_campaigns));
		}



		return $campaigns_array;
	}


	public function getReportRenderer(){

		return $this->getServiceManager()->get('jimmybase_reportrenderer_service');
	}




	public function getReportParamsService(){

		if(!$this->report_params_service)
			$this->setReportParamsService();

		return $this->report_params_service;
	}

	public function setReportParamsService($report_params_service){

		$this->report_params_service = $report_params_service;

		return $this;
	}


	public function getMetricsFormatService(){

		return $this->getServiceManager()->get('jimmybase_metricsformat_service');

	}



	public function getRequest(){

		if(!$this->request)
			$this->setRequest();

		return $this->request;
	}


	public function setRequest($request){

		$this->request = $request;

		return $this;
	}



	public function getViewRenderer(){

	  return $this->getServiceManager()->get('viewrenderer');
	}

	public function setMetricsService($metrics_service){

	 $this->metricsService = $metrics_service;

	 return $this;
	}

	public function getMetricsService(){

	 return $this->getServiceManager()->get('jimmybase_metrics_service');

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
