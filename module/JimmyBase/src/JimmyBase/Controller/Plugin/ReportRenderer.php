<?php

namespace JimmyBase\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class ReportRenderer extends AbstractPlugin  implements ServiceManagerAwareInterface
{
	private $serviceManager;

	private $data;
	
	private $dataCompare;
	
	private $units = array();
	
	private $viewModel;
	
	private $viewRenderer;
	
	

	
	public function __construct(){

      //$this->getServiceManager()->get('Application')->getMvcEvent()->attach(self,'renderKPI.process', array($this, 'processKPIData'), 1);

	  $this->units = array('clicks'	=>	array('format'=>'%s',	'decimal'=>0),
	  					   'impressions'		=>	array('format'=>'%s',	'decimal'=>0),
						   'conv1PerClick' 		=> 	array('format'=>'%s',	'decimal'=>0),
						   'ctr' 				=>  array('format'=>'%s%%',	'decimal'=>2) ,
						   'avgCPC' 			=>  array('format'=>'A$%s',	'decimal'=>2),
						   'cost' 				=>  array('format'=>'A$%s',	'decimal'=>2),
						   'costConv1PerClick' 	=> 	array('format'=>'A$%s',	'decimal'=>2),
						   'convRate1PerClick' 	=> 	array('format'=>'%s%%',	'decimal'=>2),
						   'avgPosition'	   	=> 	array('format'=>'%s',	'decimal'=>1)
						  );

	}
	
	
	public function setViewRenderer($viewRenderer){
		$this->viewRenderer = $viewRenderer;
	
		return $this;
	}
	
	
	public function getViewRenderer(){
	
	  return $this->viewRenderer;
	}
	
	
	public function prepareResult($result,$resultCompare=null){
		
		# Normal Result
		if($result)
		   $this->data = $this->__processResult($result);
		 
		# Comparable Result 
		if($resultCompare)
		   $this->dataCompare = $this->__processResult($resultCompare);
		 
		 
		return $this;	
	}
	
	
	private function __processResult($result){

			$xml = simplexml_load_string($result);
			foreach($xml->table->columns->column as $column){
				$columns[] = array('name' =>(string)$column->attributes()->name,'display'=>(string)$column->attributes()->display);
			}
			
			
			foreach($xml->table->row as $row){
				
				foreach($columns as $column){
						 $d = (array)$row->attributes()->{$column['name']};
						 $rowData[$column['name']] = $d[0];
				}
				
				$rows[] = $rowData;
			}
			
			
			$data['columns']  		= $columns;
			$data['rows']  	  		= $rows;	

		return $data;	
	}
	
  
    public function renderGraph($report,$widget,$args,$download=false) {
		
		
		if(!is_array($this->data)) 
		    return false;
			
		 $start_date  = $args['date_range']['min'];
	 	 $end_date    = $args['date_range']['max'];
		 $duration    = ($end_date-$start_date)/86400;
		 $field		  = $args['field'][1];
	     $field_comp  = $args['field_compare'][1];
		 $i 		  = 0;
		 
		 
			if(is_array($args['dependent_fields'])){
				foreach($args['dependent_fields']  as $column){
						$depFields[] = $column[1];
				}
				$depFields  =  array_unique($depFields);
			}
			

			
			$rawDataTotal = array();

			# Segmentation Logic -- Loop Over each day data 
			# Since data are returned and segmented by day
			for($i = 0; $i < (int)$duration; $i++){
				$day = date('Y-m-d',strtotime("+$i day",$start_date));
				$dataExistsForDate = false;
				
				if($this->data['rows']){
					# Loop over each campaign data 
					foreach($this->data['rows'] as $data){
					
						if($day != $data['day'] )
						  continue;
						  
						$dataExistsForDate = true;
						
						# For AvgPosition  (avgPos * impressions)
					    if($field == 'avgPosition'){ 
						 	if($data['impressions'])
								$totals[$field][$day] += $data[$field] * $data['impressions'];
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
							if($field_comp == 'avgPosition'){ 
								if($data['impressions'])
								   $totals[$field_comp][$day] += $data[$field_comp] * $data['impressions'];
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

		

	return array(
								 'class' 			   => 'moreStuff radius5 t1',
								 'args' 			   => $args,
								 'field' 			   => $field,
								 'totals' 	  		   => $totals,
								 'new_date' 		   => $new_date,
								 'field_comp'		   => $field_comp
					  );

	$viewModel = new ViewModel();
	
	
	$template	= 'graph';
	if($download) $template = 'graph-download';
	
	$viewModel->setTemplate($template)
			   ->setVariables(array(
								 'class' 			   => 'moreStuff radius5 t1',
								 'args' 			   => $args,
								 'field' 			   => $field,
								 'totals' 	  		   => $totals,
								 'new_date' 		   => $new_date,
								 'field_comp'		   => $field_comp,
								 'widget'			   => $widget
					  ));
	
	$script = $this->getViewRenderer()
				   ->render($viewModel);
			
	return $script;

		
	}
	

	
	public function renderTable($report,$widget,$args){

			
			if(!is_array($this->data)) 
		   		 return false;
			
			$sort_by_append = null;
			
			
			# First sort the data by the given sort column
			$sortedRawData = $this->_sortData($this->data,$args['sort_by']);	
			

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
					foreach($data as $dataEach){

						foreach($args['extra_fields'] as $column){
							   $rawData[$key][$column[1]] = $dataEach[$column[1]];
						}

						foreach($rawDataFields as $field){ 
							   
						   if($field == 'avgPosition'){
								$rawData[$key]['sumAvgPos'] = $dataEach[$field] * $dataEach['impressions'];
								$rawData[$key][$field]     += $dataEach[$field];
						   } else {
								$rawData[$key][$field]     += $dataEach[$field];
						   }
					   }
					   
					   foreach($depFields as $field)
							   $depData[$key][$field]     += $dataEach[$field];
						
					}
				}
			}
											

			
			if($rawData){			

			foreach($rawData as $key => $data){
					
					foreach($rawDataFields as $column)
							$rawDataTotal[$column] += $data[$column];

					if($data['searchImprShare'])
                                            $totalImpression += $data['impressions']*100/$data['searchImprShare'];
						$searchImpressionShare++;

					
					if($data['sumAvgPos'])
					   $rawDataTotal['sumAvgPos'] += $data['sumAvgPos'];
					 
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
			   $rawDataTotal['avgPosition'] = $rawDataTotal['sumAvgPos'];
				
		    if($rawDataTotal['searchImprShare'])
			   $rawDataTotal['searchImprShare'] = $rawDataTotal['impressions']*100 / $searchImpressionShare;

				
			# Perform Manual Calculations for Certain fields
			# The array is passed as reference
			$this->_applyManualCalculations($rawDataTotal,$depDataTotal,array('ctr','avgCPC','cost','costConv1PerClick','convRate1PerClick','avgPosition'));
			
	
			$viewModel = new ViewModel();
			
			$viewModel->setTemplate('table')
					   ->setVariables(array(
					   					 'class' 			   => 'performTbl radius5',
					   					 'widget'			   => $widget,
										 'field' 			   => $field,
										 'args' 	  		   => $args,
										 'rawData' 	  		   => $rawData,
										 'rawDataTotal' 	   => $rawDataTotal,
										 'field_comp'		   => $field_comp,
										 'units'			   => $this->units
							  ));
			
			$html = $this->getViewRenderer()
						   ->render($viewModel);
					
			return $html;
			
	}
	
	
	public function processKPIData(){
		echo 1;exit;
		   # First sort the data by the given sort column
			$sortedData = $this->_sortData($adwordsData,$args['sort_by']);	
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

			foreach($sortedData as $key => $data){
					
					foreach($data as $dataEach){

						foreach($args['extra_fields'] as $column)
							   $kpiData[$key][$column[1]] = $dataEach[$column[1]];
						
						foreach($kpiDataFields as $field){ 

								
						   if($field == 'avgPosition'){
								$kpiData[$key]['sumAvgPos'] = $dataEach[$field] * $dataEach['impressions'];
								$kpiData[$key][$field]     += $dataEach[$field];
						   } else {
								$kpiData[$key][$field]     += $dataEach[$field];
						   }
					   }
					   
					   foreach($depFields as $field)
					   		   $depData[$key][$field]     += $dataEach[$field];
						
					}
			}
			
			
			
			
			foreach($kpiData as $key => $data){
				
				foreach($kpiDataFields as $column)
						$kpiDataTotal[$column] += $data[$column];
				
				if($data['searchImprShare'])
					$searchImpressionShare++;

				if($data['sumAvgPos'])
				   $kpiDataTotal['sumAvgPos'] += $data['sumAvgPos'];
				 
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
			   $kpiDataTotal['avgPosition'] = $kpiDataTotal['sumAvgPos'];
							
			if($kpiDataTotal['searchImprShare'])
			   $kpiDataTotal['searchImprShare'] = $kpiDataTotal['searchImprShare'] / $searchImpressionShare;

			# Perform Manual Calculations for Certain fields
			# The array is passed as reference
			$this->_applyManualCalculations($kpiDataTotal,$depDataTotal,array('ctr','avgCPC','cost','costConv1PerClick','convRate1PerClick','avgPosition'));

		return $kpiDataTotal;
	}
	
	public function renderKPI($report,$widget,$args){
                   
			$this->getEventManager()->trigger('renderKPI.process', $this, array('data' => $this->data,'args'=>$args));

			
			if($this->dataCompare)
				$kpiDataTotalCompare  = $this->__processKPIData($this->dataCompare,$args);
			
			
			$viewModel = new ViewModel();
			
			
			$viewModel->setTemplate('kpi')
					   ->setVariables(array(
					   					 'class'			   => 'moreStuff radius5 t1',
										 'args' 			   => $args,
										 'kpiDataTotal' 	   => $kpiDataTotal,
										 'kpiDataTotalCompare' => $kpiDataTotalCompare,
										 'widget'			   => $widget,
										 'units'			   => $this->units
							  ));
			
			$kpiHtml = $this->getViewRenderer()
				 			->render($viewModel);
			
						 
			return $kpiHtml;		 
	}
	
	
	public function renderNotes($report,$widget){
		    $fields = unserialize($widget->getFields());
			
			$viewModel = new ViewModel();
			
			
			$viewModel->setTemplate('notes')
					   ->setVariables(array(
					   					 'class'	=> 'notes',
										 'args' 	=> $args,
										 'notes'	=> $fields['notes'],
										 'widget'	=> $widget
							  ));
			
			$notesHtml = $this->getViewRenderer()
				 			  ->render($viewModel);
			
						 
		return $notesHtml;
	
	}

	
	
	private function _applyManualCalculations(&$dataTotal,$depDataTotal,$fields){
		/*echo '<pre>';
		print_r($dataTotal);
		print_r($depDataTotal);
		*/
		if(is_array($depDataTotal) && !empty($depDataTotal))
		@extract($depDataTotal);
		# Extract the array values and create variables out of them
		if(is_array($dataTotal) && !empty($dataTotal))
		@extract($dataTotal);
		
							
		foreach($fields as $field){
			switch($field){
				
					case 'ctr':
							 
                                                        if($impressions > 0) 
                                                                  
									$dataTotal['ctr'] = round(($clicks/$impressions)*100,2);
							    else  
									$dataTotal['ctr'] = '0';
																		
								break;
					case 'avgCPC':
								if($clicks > 0)  
								   $dataTotal['avgCPC'] = round(($cost/1000000)/$clicks,2); 
							    else  
								   $dataTotal['avgCPC'] = '0.00';
								
								break;
					case 'cost':
								if($cost)
								  $dataTotal['cost'] = round($cost/1000000,2);
								else
								  $dataTotal['cost'] = 0;
								  
								 break;
					case 'costConv1PerClick':
								if($conv1PerClick)
								  $dataTotal['costConv1PerClick'] = round(($cost/$conv1PerClick)/1000000,2);
								else
								  $dataTotal['costConv1PerClick'] = '0.00';
									
								break;
					case 'convRate1PerClick':
								
								if($clicks)
								   $dataTotal['convRate1PerClick'] = round(($conv1PerClick/$clicks)*100,2);
								else
								   $dataTotal['convRate1PerClick'] = '0';						
							    
								break;
						
					case 'avgPosition':
								if($impressions)
								   $dataTotal['avgPosition'] = round(($avgPosition/$impressions),1);
								else
								   $dataTotal['avgPosition'] = '0';
					break;			   
			}
			
		}	
		/*	if(in_array($field,array_keys($dataTotal))){
			    echo $manualColumns[$field]['formula'];
				eval($manualColumns[$field]['formula']);
			}*/
						
		
	
		//echo '<pre>';print_r($dataTotal);
				
			
		return $dataTotal;
	}
	
	
	private function _prepareRawData(){
	
	
	}
	
	
	private function _sortData($adwordsRawData,$sort_by){
		
		if(!is_array($adwordsRawData) or empty($sort_by) or empty($adwordsRawData['rows'])) 
			return  false;
			
		foreach($adwordsRawData['rows'] as $data){
				$sortedData[$data[$sort_by]][] = $data;
		}
		
	 	return $sortedData;		
	}
	
	public function setMetricsService($metrics_service){
	
	 $this->metricsService = $metrics_service;
		
	 return $this;	
	}
	
	public function getMetricsService(){
	
	 return $this->metricsService;
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
}
