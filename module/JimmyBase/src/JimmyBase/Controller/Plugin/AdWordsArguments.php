<?php

namespace JimmyBase\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdWordsArguments extends AbstractPlugin
{

    private $metricsService  = null;
   
   	private $widgetService  = null;

	private $selector_fields = null;

	private $segment_fields  = null;

	private $report_type 	 = null;


	public function __construct(){

	   # Set the Selector Fields
	   $this->_mapSelectorFields();

	   # Set the Report Type
	   $this->_mapReportType();

	   # Set the Segment Fields
	   $this->_mapSegments();

	}

	public  function prepareParams($widget,$opParams = null){

	  $metrics_service  = $this->getMetricsService();
	  $widget_service  = $this->getWidgetService();


	  $fields = unserialize($widget->getFields());

	  	if($opParams['date_range']) {
               $date_range = $opParams['date_range'];
	  	} else if($fields['date_range']) {
           		$date_range = $fields['date_range'];
        	} 
    	

	    if($fields['compare_dates']){
			if($fields['date_range_compare'] == 'previous_period'){
				$params['date_range_compare'] = $this->_getCompareDateRangeType($metrics_service->getDateRange($date_range));

			} else {
				$params['date_range_compare'] = array('min' => strtotime($fields['date_range_custom_min_compare']),'max' => strtotime($fields['date_range_custom_max_compare']));
			}
		}
             if ($fields['filter']) {
                    $params['filter'] = $fields['filter'];
                 
             };
	    switch($widget->getType()){

	  		case 'table':

	  		 		if($fields['sort_by'])
	    			   $params['sort_by'] = $this->_getSelectorField($metrics_service->getRawData($fields['sort_by']))[1];

					if($fields['show_top'])
	    			   $params['show_top'] = $fields['show_top'];

	    			if($fields['report_type'])
	    				$params['report_type_id']=$fields['report_type'];

				   # Fields for Raw Data
				  if($table_metrics = $fields['raw_data']){

                                    foreach ($table_metrics as  $value) {
                                                  $selector_field	= $this->_getSelectorField($metrics_service->getRawData($value));                                                        
                                                  if (isset($selector_field[1])) {                                                        
                                                      $params['fields_raw_data'][$selector_field[1]] = $selector_field;
                                                  }
                                                  $params['fields'][] = $selector_field[0];
                                                 
                                    }                                       
				  }

				  break;
			case 'kpi':

				# Fields for KPI
				if($kpi_metrics = $fields['kpi']){

					  foreach($kpi_metrics as  $value){

							$kpi_field 			= $metrics_service->getKPI($value);
							$selector_field		= $this->_getSelectorField($kpi_field[1]);


							$params['kpi_fields'][$selector_field[1]]= $selector_field;
							$params['kpi_fields'][$selector_field[1]][0]  = $kpi_field[0];

							//array($kpi_field[0],$selector_field[1]);
							$params['fields'][]		= $selector_field[0];
					  }

				}
				$params['fields'][]    = 'Date';
				$params["kpi_type"]    = $fields["kpi_type"];

				break;
            case 'graph':
				  /* Selector Fields */
				 $params['field']	   = $this->_getSelectorField($metrics_service->getMetrics($fields['metrics_type'],$fields['metrics']));
				 $params['fields'][]   = $params['field'][0];

				 if($fields['compare']) {
					$params['field_compare'] = $this->_getSelectorField($metrics_service->getMetrics($fields['metrics_type_compare'],$fields['metrics_compare']));
					$params['fields'][]      = $params['field_compare'][0];
				 }
				 //Segmenting;
				 $params['fields'][]    = 'Date';
	  		break;
	  	case 'piechart':
	  			

	  			if($fields['sort_by'])
	    			   $params['sort_by'] = $this->_getSelectorField($metrics_service->getRawData($fields['sort_by']))[1];

				if($fields['show_top'])
	    			   $params['show_top'] = $fields['show_top'];

				   # Fields for Raw Data

				

						$value = $fields['raw_data'];

                                    
                                                  $selector_field	= $this->_getSelectorField($metrics_service->getRawData($value));


                                                  if (isset($selector_field[1])) {                                                        
                                                      $params['fields_raw_data'][$selector_field[1]] = $selector_field;
                                                  }
                                                  $params['fields'][] = $selector_field[0];

                                             // 
                                                 
                                                                           
				  
	  	break;
	  }
	  //if($widget->getType()=='kpi' || $widget->getType() == 'table' ) {
	  if($widget->getType()!='notes' ) {
		  foreach($params['fields'] as $field){
				$selector_field = null;
                                    
				# Dependency Fields #
                               
				if($field == 'ClickConversionRate'){
					if(in_array(1,$params['fields'])) {# Dependency = Click
					  continue;
					}

					$selector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(1,1));
					$params['fields'][]   		  = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;

				}
                if($field == 'CostPerConvertedClick'){
					if(in_array(1,$params['fields'])) {# Dependency = Click
					  continue;
					}

					$selector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(2,1));
					$params['fields'][]   		  = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;
                                        
                                        $sector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(2,11));
					$params['fields'][]   		  = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;

				}
                                                               
                                //value conversions !!!
                                if ($field == "ValuePerConversionManyPerClick") {
                                   
                                    if(in_array(1,$params['fields'])) {# Dependency = totalConvValue , conversions
					  continue;
					}
					$selector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(2,8));
					$params['fields'][]   		  = $selector_field[0];
					$params['dependent_fields'][]     = $selector_field;
                                        $selector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(2,5));
					$params['fields'][]   		  = $selector_field[0];
					$params['dependent_fields'][]     = $selector_field;
				
                                       
                                }
				if($field == 'ConversionValue'){
					if(in_array(1,$params['fields'])) {# Dependency = Conversions
					  continue;
					}
					$selector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(2,1));
					$params['fields'][]   		  = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;

				}
                                
                if ($field == 'ConversionRate') {
                    if(in_array(1,$params['fields'])) {# Dependency = Conversions
					  continue;                      
					}
                                            //Clicks
                                            $selector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(1,1));
                                            $params['fields'][]   		  = $selector_field[0];
                                            $params['dependent_fields'][] = $selector_field;
                                            
                                            	// Conversions
                                            $selector_field               = $this->_getSelectorField($metrics_service->getMetrics(2,5));
                                            $params['fields'][]           = $selector_field[0];
                                            $params['dependent_fields'][] = $selector_field;
                                         
                                }
                                if($field == 'SearchImpressionShare'){
					if(in_array(1,$params['fields'])) {# Dependency = Conversions
					  continue;
					}
                                            $selector_field       		  = $this->_getSelectorField($metrics_service->getMetrics(4,6));
                                            $params['fields'][]   		  = $selector_field[0];
                                            $params['dependent_fields'][] = $selector_field;
                                            
                                            	// Impressions
                                            $selector_field               = $this->_getSelectorField($metrics_service->getMetrics(1,2));
                                            $params['fields'][]           = $selector_field[0];
                                            $params['dependent_fields'][] = $selector_field;
                                         
				}


				if($field == 'AveragePosition' or $field =='Ctr'){  # Dependency = Impression,Clicks
					if(in_array(2,$params['fields']))
					  continue;
					// Impressions
					$selector_field               = $this->_getSelectorField($metrics_service->getMetrics(1,2));
					$params['fields'][]           = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;

					// Clicks
					$selector_field               = $this->_getSelectorField($metrics_service->getMetrics(1,1));
					$params['fields'][]           = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;
				}

				if($field == 'AverageCpc' or $field == 'CostPerConvertedClick'){ # Dependency = Cost,Clicks
					if(in_array(5,$params['fields']))
					  continue;

					//Cost
					$selector_field 	          = $this->_getSelectorField($metrics_service->getMetrics(1,5));
					$params['fields'][]           = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;

					//Clicks
					$selector_field 	          = $this->_getSelectorField($metrics_service->getMetrics(1,1));
					$params['fields'][]   	      = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;

					//Conv1perclick
					$selector_field 	          = $this->_getSelectorField($metrics_service->getMetrics(2,1));
					$params['fields'][]   	      = $selector_field[0];
					$params['dependent_fields'][] = $selector_field;

				}
                                
				### ^^^^Dependency Fields^^^^ #####
			}
	  }
	  if(isset($fields['report_type'])){
	  	if($fields['report_type'] ==7 || $fields['report_type'] ==8 ) {
	  		$params['report_type'] ='ACCOUNT_PERFORMANCE_REPORT';		
	  	}else if($fields['report_type'] ==9 ) {
	  		$params['report_type'] ='AD_PERFORMANCE_REPORT';
			 		
	  	}
		   else {
	  	  $params['report_type'] = $this->_getReportType($metrics_service->getReportType($fields['report_type']));
	  	}

	  } else {
	     $params['report_type'] ='CAMPAIGN_PERFORMANCE_REPORT';
	  }

	  switch($params['report_type']){

	  			case 'CAMPAIGN_PERFORMANCE_REPORT':
	  					$newfields_raw_data= array();
						$params['fields'][]     = 'CampaignId';
						$params['fields'][]     = 'CampaignName';
	 					$params['group_by']		= 'campaignID';
	 					

	 					$params['extra_fields'] = array('campaign'=>array('CampaignName','campaign','Campaign'));
	 					
				break;

				case 'ACCOUNT_PERFORMANCE_REPORT':

					//	$params['fields'][]   = 	'AccountId';

						if($params['report_type_id']==7){
	 						$params['fields'][] = 'MonthOfYear';
	 						$params['fields'][] = 'Year';
	 						$params['extra_fields']['monthOfYear'] = array('Month','monthOfYear','Month');
	 				/*		$newfields_raw_data['month'] = array('Month','monthOfYear','Month');
	 						foreach ($params['fields_raw_data'] as $key => $field) {
	 							$newfields_raw_data [$key]=$field;
	 						}
	 						$params['fields_raw_data'] = $newfields_raw_data;
	 				*/
	 					}
	 					elseif($params['report_type_id']==8){	
	 						$params['fields'][] = 'Week';
	 						$params['fields'][] = 'Month';
	 						$params['fields'][] = 'Year';

	 						$params['extra_fields']['week'] = array('Week','week','Week');
	 					/*	$newfields_raw_data['week'] = array('Week','Week','Week');
	 						foreach ($params['fields_raw_data'] as $key => $field) {
	 							$newfields_raw_data [$key]=$field;
	 						}
	 						$params['fields_raw_data'] = $newfields_raw_data;
						*/
	 					} else {
	 						$params['fields'][]   = 	'AccountDescriptiveName';
	 						$params['extra_fields'] =	array( 'accountID' => array('AccountId','accountID','Account Id'),
														   'account' => array('AccountDescriptiveName','account','Account')
														  );
							$params['group_by']		= 'accountID';
	 					}

				break;
				case 'KEYWORDS_PERFORMANCE_REPORT':
					    $params['fields'][]   = 	'Criteria';
					   // $params['fields'][]   = 	'KeywordMatchType';
					    $params['extra_fields']['keyword']   =  array('Criteria','keyword','Keyword');
					   // $params['extra_fields']['matchType']   =  array('KeywordMatchType','matchType','MatchType');



						$params['fields'][]   = 	'CampaignId';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';
						//$params['fields'][]   = 	'AdGroupStatus';
						//$params['extra_fields'][]   =  array('CampaignId','campaignID');
						//$params['extra_fields'][]   =  array('CampaignName','campaign','Campaign');
						//$params['extra_fields'][]  =   array('AdGroupId','adGroupID');
						//$params['extra_fields'][]  =   array('AdGroupName','adGroup');
						//$params['extra_fields'][]  =   array('AdGroupStatus','adGroupState');
				 		$params['group_by']		= 'keyword';
			    break;
				case 'ADGROUP_PERFORMANCE_REPORT':
						//$params['fields'][]   = 	'CampaignId';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';
						//$params['fields'][]   = 	'AdGroupStatus';
						//$params['extra_fields'][]  =   array('CampaignId','campaignID');
						$params['extra_fields']['campaign']  =   array('CampaignName','campaign' ,'Campaign');
						//$params['extra_fields'][]  =   array('AdGroupId','adGroupID');
						$params['extra_fields']['adGroup']  =   array('AdGroupName','adGroup','Ad Group');
						//$params['extra_fields'][]  =   array('AdGroupStatus','adGroupState');
				 		$params['group_by']		   = 'adGroupID';
				break;
				case 'AD_PERFORMANCE_REPORT':
			   		
					   if($params['report_type_id']==9){

				   		$params['fields'][]   =     'Headline';
						// $params['fields'][]   =     'Description1';
						// $params['fields'][]   =     'Description2';
						//$params['fields'][]   = 	'CampaignId';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';
						//$params['fields'][]   = 	'AdGroupStatus';

						$params['extra_fields']['ad']  =   array('Ad','ad','Ad');
						//$params['extra_fields'][]  =   array('Campaign','campaign','Campaign');
						// $params['extra_fields']['descriptionLine1']   =  array('Description1','descriptionLine1','DescriptionLine1');
						// $params['extra_fields']['descriptionLine2']   =  array('Description2','descriptionLine2','DescriptionLine2');

						$params['extra_fields']['adGroup']  =   array('AdGroupName','adGroup','Ad Group');
						//$params['extra_fields'][]  =   array('Ad Group','adGroup','Ad Group');
						//$params['extra_fields'][]  =   array('Status','adGroupState');

				 		$params['group_by']		= 'adGroupID';

			           }else{
						$params['fields'][]   =     'Headline';
						$params['fields'][]   =     'Description1';
						$params['fields'][]   =     'Description2';
						//$params['fields'][]   = 	'CampaignId';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';
						//$params['fields'][]   = 	'AdGroupStatus';

						$params['extra_fields']['ad']  =   array('Ad','ad','Ad');
						//$params['extra_fields'][]  =   array('Campaign','campaign','Campaign');
						$params['extra_fields']['descriptionLine1']   =  array('Description1','descriptionLine1','DescriptionLine1');
						$params['extra_fields']['descriptionLine2']   =  array('Description2','descriptionLine2','DescriptionLine2');

						$params['extra_fields']['adGroup']  =   array('AdGroupName','adGroup','Ad Group');
						//$params['extra_fields'][]  =   array('Ad Group','adGroup','Ad Group');
						//$params['extra_fields'][]  =   array('Status','adGroupState');

				 		$params['group_by']		= 'adGroupID';
				
			           }	
				break;
	  			case  'SEARCH_QUERY_PERFORMANCE_REPORT':
						$params['fields'][]   = 	'Query';
						$params['fields'][]   = 	'CampaignName';
						$params['fields'][]   = 	'AdGroupId';
						$params['fields'][]   = 	'AdGroupName';
						$params['fields'][]   = 	'KeywordTextMatchingQuery';
						$params['fields'][]   = 	'MatchType';

						$params['extra_fields']['campaign']   =  array('CampaignName','campaign','Campaign');
						$params['extra_fields']['searchTerm']   =  array('Query','searchTerm','Search term');
						$params['extra_fields']['keyword']   =  array('KeywordTextMatchingQuery','keyword','Keyword');
                        $params['extra_fields']['matchType']   =  array('MatchType','matchType','Match');
						$params['extra_fields']['adGroup']   =  array('AdGroupName','adGroup','Ad Group');
				 		$params['group_by']			= 'searchTerm';
	  			break;
	  }
	  $params['fields'][]   = 	'AccountCurrencyCode';
	  $params['fields']     = array_unique($params['fields']);
          

	  $params['network_type']	  = $metrics_service->getAdNetworkOptions($fields['network_type'])[1];
	  $params['show_campaign']	  = $fields['show_campaign'];

	  if(is_array($fields['device_type'])){
	  	foreach ($fields['device_type'] as $device_type) {
	  		if($device_option  = $metrics_service->getDeviceOptions($device_type)[1])
	  		   $device_types[] = $device_option;
	  	}
	  	  $params['device_type']	= $device_types;
	  }

	  if ($fields['report_type']==7){ 
	  		//Month on Month Report - ID 7
		  	if($fields['period']!=14){
		  		$params['date_range'] = $widget_service->_parseWidgetDateRange('Month',$fields['period'],$fields['show_current_period'],'googleadwords');
		  		$params['date_range_type']  = $this->_getDateRangeType('Custom');
		  	} else {
		  		$params['date_range'] = array('min' => strtotime($fields['date_range_custom_min']),'max' => strtotime($fields['date_range_custom_max']));
		  		$params['date_range_type']  = $this->_getDateRangeType('Custom');
		  	}
	  	
	  } elseif  ($fields['report_type']==8) {
	  		//Week on Week Report - ID 8
		  	if($fields['period']!=14){
		  		$params['date_range']       = $widget_service->_parseWidgetDateRange('Week',$fields['period'],$fields['show_current_period'],'googleadwords');
		  		$params['date_range_type']  = $this->_getDateRangeType('Custom');
		  	} else {
		  		$params['date_range'] = array('min' => strtotime($fields['date_range_custom_min']),'max' => strtotime($fields['date_range_custom_max']));
		  		$params['date_range_type']  = $this->_getDateRangeType('Custom');
		  	}
	  }else{
		  	//All Other Reports
	 	  	$params['date_range']       = $this->_parseDateRange($metrics_service->getDateRange($date_range));
		  	$params['date_range_type']  = $this->_getDateRangeType($metrics_service->getDateRange($date_range));
		  	if($date_range == 14){
			 $params['date_range'] = array('min' => strtotime($fields['date_range_custom_min']),'max' => strtotime($fields['date_range_custom_max']));
		  	}
	  }

	  if($opParams['date_range'] && $date_range == 14){
	   	 $params['date_range'] = array('min' => strtotime($opParams['min']),'max' => strtotime($opParams['max']));
	  } 

  		$params['date_range_formatted'] = array('min' => date('F j, Y',$params['date_range']['min']),
	   	 	                                  'max' => date('F j, Y',$params['date_range']['max']));

  		if($params['date_range_compare']){
  			$params['date_range_compare_formatted'] = array('min' => date('F j, Y',$params['date_range_compare']['min']),
	   	 	                                  'max' => date('F j, Y',$params['date_range_compare']['max']));

  		}

	  $params['campaigns']   	  = $fields['campaigns'];
	  $params['widget_id']        = $widget->getId();

	//print_r($params); die;
	  return $params;
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

			case 'Last week (Mon - Sun)': // Adwords Reporting API doesnot provide a date range type for this
				 return 'CUSTOM_DATE';
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
	private function _getMonthRangeType($date_range){

	  switch($date_range){
		  	case 'Last 3 Months': // Today
				 return 'LAST_3_MONTHS';
			break;
			case 'Last 6 Months':
				 return 'LAST_6_MONTHS';
			break;

			case 'Last 12 Months':
				 return 'LAST_12_MONTHS';
			break;
			default:
			   return null;

			
	  }

	}
	private function _getWeekRangeType($date_range){

	  switch($date_range){
		  	case 'Last 4 Weeks': // Today
				 return 'LAST_4_WEEKS';
			break;
			case 'Last 8 Weeks':
				 return 'LAST_8_WEEKS';
			break;

			case 'Last 12 Weeks':
				 return 'LAST_12_WEEKS';
			break;
			default:
			   return null;

			
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
			case 'Last week (Mon - Sun)':
				  $max   =  strtotime('last sun - 1 week');
				  $min   =  strtotime('-6 day',$max);
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
				  $max   =  strtotime('today');
				  $min     =  strtotime('1.1.2000');
				return array('min' => $min, 'max' => $max );
				
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
				  $max   =  strtotime('today');
				  $min     =  strtotime('1.1.2000');
				return array('min' => $min, 'max' => $max );
				
			break;
			default:
			   return null;

	  }

	}
	
	private function _getSelectorField($selector_field_key /* This is the display value */){

		if(is_array($this->selector_fields) && isset($this->selector_fields[$selector_field_key]))
		  return $this->selector_fields[$selector_field_key];

		return false;
	}




	private function _mapSelectorFields(){

            $this->selector_fields =  array(        
                    'Clicks'                           =>  array('Clicks','clicks','Clicks','icon-bar-chart-o'),
                    'Impressions'                      =>  array('Impressions','impressions','Impr.','icon-eye'),
                    'CTR'                              =>  array('Ctr','ctr','CTR','icon-random'),
                    'Avg. CPC'                         =>  array('AverageCpc','avgCPC','Avg. CPC','icon-bookmark-o'),
                    'Cost'                             =>  array('Cost','cost','Cost','icon-dollar'),
                    'Avg. Pos.'                        =>  array('AveragePosition','avgPosition','Avg. Pos.','icon-sort-numeric-asc'),
                    'First Page CPC'                   =>  array('FirstPageCpc','firstPageCPC','FPage CPC'),
                    'Converted Clicks'                 =>  array('ConvertedClicks','convertedClicks','Converted Clicks','icon-check'),
                    'Cost / Converted Click'           =>  array('CostPerConvertedClick','costConvertedClick','Cost / Converted Click','icon-check-circle'),
                    'Click conversion rate'            =>  array('ClickConversionRate','clickConversionRate','Click Conversion Rate'),
                    'Conversion Value'                 =>  array('ConversionValue','totalConvValue','Conv. Value'),
                    'View-through Conv.'               =>  array('ViewThroughConversions','viewThroughConv','View-Through Conv.'),
                    'Conv. (many-per-click)'           =>  array('Conversions','conversions','Conversions'),
                    'Cost / conv. (many-per-click)'    =>  array('CostPerConversion','costConv','Cost/Conv.'),
                    'Conv. Rate (many-per-click)'      =>  array('ConversionRate','convRate','Conv. Rate',' icon-check-square'),
                   // 'Total conv. value'              =>  array('TotalConvValue','totalConvValue','T. Conv. Value'),
                    'Value / conv.click         '      =>  array('ValuePerConvertedClick','valueConvertedClick','Value / conv. click'),
                    'Value / conv. (many-per-click)'   =>  array('ValuePerConversion','valueConv','Value / conv'),
                    'Search Impr. Share'               =>  array('SearchImpressionShare','searchImprShare','Search Impr. Share','icon-info'),
                    'Search Exact Match IS'            =>  array('SearchExactMatchImpressionShare','searchExactMatchIS','Search Exact Match IS'),
                    'Search Lost IS (rank)'            =>  array('SearchRankLostImpressionShare','searchLostISRank','Search Lost IS (rank)'),
/*not in adgroup keyboard*/'Search Lost IS (budget)'          =>  array('SearchBudgetLostImpressionShare','searchLostISBudget','Search Lost IS (budget)'),
/*not in keyword */        'Relative CTR'                     =>  array('RelativeCtr','relativeCTR','R CTR'),
/*not in keyword */        'Phone Calls'                      =>  array('NumOfflineInteractions','phoneCalls', 'Phone Calls'),
/*not in keyword */        'Phone Impressions'                =>  array("NumOfflineImpressions", 'phoneImpressions',"Phone Impressions"),
/*not in keyword */        'PTR'                              =>  array("OfflineInteractionRate", 'ptr', "PTR"),
/*not in keyword */        'Phone Cost'                       =>  array('OfflineInteractionCost', 'Phone Cost', 'phoneCost'),
/*not in keyword */        'Avg. CPP'                         =>  array('AvgCostForOfflineInteraction','Avg. CPP','avgCPP')
                );
                                                                                                                                                                                
/*
		$this->selector_fields = array(  'Clicks' 							=> 	array('Clicks','clicks','Clicks'),
										 'Impressions' 						=> 	array('Impressions','impressions','Impr.'),
										 'CTR' 								=> 	array('Ctr','ctr','CTR'),
										 'Avg. CPC' 						=> 	array('AverageCpc','avgCPC','Avg. CPC'),
										 'Cost' 							=> 	array('Cost','cost','Cost'),
										 'Avg. Pos.' 						=> 	array('AveragePosition','avgPosition','Avg. Pos.'),
										 'First Page CPC' 					=> 	array('FirstPageCpc','firstPageCPC','FPage CPC'),
										 'Conv.(1-per-click)' 				=> 	array('Conversions','conv1PerClick','Conv.'),
										 'Cost / Conv. (1-per-click)' 		=> 	array('CostPerConversion','costConv1PerClick','CPA'),
										 'Conv. Rate (1-per-click)' 		=> 	array('ConversionRate','convRate1PerClick','CPA(%)'),
										 'View-through Conv.'				=> 	array('ViewThroughConversions','viewThroughConv','View-Through Conv.'),
										 'Conv. (many-per-click)' 			=> 	array('ConversionsManyPerClick','convManyPerClick'),
										 'Cost / conv. (many-per-click)'	=>	array('CostPerConversionManyPerClick','costConvManyPerClick','Conv. (many-per-click)'),
										 'Conv. Rate (many-per-click)'		=>	array('ConversionRateManyPerClick','convRateManyPerClick','Conv. Rate'),
										 'Total conv. value' 				=> 	array('TotalConvValue','totalConvValue','T. Conv. Value'),
										 'Value / conv. (1-per-click)' 		=>	array('ValuePerConv','valueConv1PerClick','Value / conv.'),
										 'Value / conv. (many-per-click)'	=>	array('ValuePerConvManyPerClick','valueConvManyPerClick','Value / conv.'),
										 'Search Impr. Share'				=>	array('SearchImpressionShare','searchImprShare','Search Impr. Share'),
										 'Search Exact Match IS'			=>	array('SearchExactMatchImpressionShare','searchExactMatchIS','Search Exact Match IS'),
										 'Search Lost IS (rank)'			=>	array('SearchRankLostImpressionShare','searchLostISRank','Search Lost IS (rank)'),
										 'Search Lost IS (budget)'			=>	array('SearchBudgetLostImpressionShare','searchLostISBudget','Search Lost IS (budget)'),
										 'Relative CTR'						=>	array('RelativeCtr','relativeCTR','R CTR'),

									 );
		*/

	}


	private function _getSegment($segment_field_key){

		if(is_array($this->segment_fields) && isset($this->segment_fields[$segment_field_key]))
		  return $this->segment_fields[$segment_field_key];

		return false;
	}


	private function _mapSegments(){


		$this->segment_fields = array(  'Time (Day)'   => 'date',
										'Time (Week)'  => 'week',
										'Time (Month)' => 'month',
										/*'Time (Quarter)',
										'Time (Year)',
										'Time (Day of the Week)',
										'Time (Hour of day)',
										'Conversions (Conversion Action Name)',
										'Conversions (Conversion Tracking Purpose)',
										'Network',
										'Network with Search Partner',
										'Click Type',
										'Device',
										'Experiment',
										'Top vs. Other',
										'+1 Annotations'*/
									 );


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
									 'Keyword'  	=> 'KEYWORDS_PERFORMANCE_REPORT',
									 'Search Query' => 'SEARCH_QUERY_PERFORMANCE_REPORT'
								   );
	}



	public function setMetricsService($metrics_service){

	 $this->metricsService = $metrics_service;

	 return $this;
	}

	public function getMetricsService(){

	 return $this->metricsService;
	}

	public function setWidgetService($widget_service){

	 $this->widgetService = $widget_service;

	 return $this;
	}

	public function getWidgetService(){

	 return $this->widgetService;
	}


}
