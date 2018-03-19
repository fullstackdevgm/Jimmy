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

class Adwords extends EventProvider implements ServiceManagerAwareInterface
{
    private $api;

    private $client_account  = null;

    private $data;

    private $dataCompare;

    private $units = array();

    private $report_renderer  = null;

    private $report_params_service = null;

    private $metrics_format_service = null;

    private $request = null;

    private $currency = 'AUD';

    private $formatFields = array('ctr','clicks','impressions','cost','costConvertedClick','avgCPC','avgPosition','searchImprShare',
                          'ga:percentNewVisits','ga:visitBounceRate','ga:pageviewsPerVisit','ga:entrances','ga:exitRate','ga:pageValue',
                          'ga:transactionsPerVisit','ga:transactionRevenue','ga:avgTimeOnSite', 'valueConv','costConv');

    public function __construct()
    {
        $this->units = array(
            'clicks' =>    array('format'=>'%s',    'decimal'=>0),
            'impressions' => array('format'=>'%s', 'decimal'=>0),
            'conv1PerClick' => array('format'=>'%s',    'decimal'=>0),
            'ctr' =>  array('format'=>'%s%%', 'decimal'=>2) ,
            'avgCPC' =>  array('format'=>'A$%s',    'decimal'=>2),
            'cost' =>  array('format'=>'A$%s',    'decimal'=>2),
            'costConv1PerClick' => array('format'=>'A$%s', 'decimal'=>2),
            'convRate1PerClick' => array('format'=>'%s%%', 'decimal'=>2),
            'avgPosition' => array('format'=>'%s', 'decimal'=>1),
            'valueConv' => array('format'=>'%s%%', 'decimal'=> 2),
            'costConv' => array('format'=>'A$%s', 'decimal'=>2),
                          );
    }

    public function loadReport($widget, $client_account, $download=false)
    {
        
        if (!$client_account->getUserTokenId()) {
            return array('success'=>false,message =>"migration note done");
        }
        $request   = $this->getRequest();

        $report_id = $widget->getReportId();
        $report    = $this->getServiceManager()->get('jimmybase_reports_service')
                          ->getMapper()
                          ->findById($report_id);

        $client    = $this->getServiceManager()->get('jimmybase_client_service')
                          ->getClientMapper()
                          ->findById($report->getUserId());

        if (method_exists($request, 'getQuery')) {
            $getParams = $request->getQuery()->toArray();
        }

        $args      = $this->getReportParamsService()->prepareParams($widget, $getParams);

        $args['channel']     = $client_account->getChannel();

        $filter = $args['filter'];
        switch ($widget->getType()) {

           case 'kpi':


                $result  = $this->getServiceManager()->get('jimmybase_campaign_service')
                                                     ->setClientAccount($client_account)
                                                     ->getClientCriteriaReport($args, $widget->getType());

                $args_compare = $args;


                if ($args['date_range_compare']) {
                    unset($args_compare['date_range']);
                    unset($args_compare['date_range_type']);
                    $result_compare  = $this->getServiceManager()
                                                          ->get('jimmybase_campaign_service')
                                                          ->getClientCriteriaReport($args_compare, $widget->getType());
                }

                $kpiHtml = $this->prepareResult($result, $result_compare, $filter)
                                ->renderKPI($report, $widget, $args, $download);

                $return = $kpiHtml;

                break;

           case 'graph':

                $result    = $this->getServiceManager()
                                                  ->get('jimmybase_campaign_service')
                                                  ->setClientAccount($client_account)
                                                  ->getClientCriteriaReport($args, $widget->getType());


                $graph = $this->getReportRenderer()
                              ->prepareResult($result, null, $filter)
                              ->setViewRenderer($this->getServiceManager()->get('viewrenderer'))
                              ->renderGraph($report, $widget, $args, $download);

                $return = $graph;
                break;

           case 'table':
                $result    = $this->getServiceManager()->get('jimmybase_campaign_service')
                                                                       ->setClientAccount($client_account)
                                                                       ->getClientCriteriaReport($args, $widget->getType());
                $table     = $this->getReportRenderer()
                                  ->prepareResult($result, null, $filter)
                                  ->setViewRenderer($this->getServiceManager()->get('viewrenderer'))
                                  ->renderTable($report, $widget, $args, $download);


                                if (is_array($tables['args']['fields'])) {
                                    $table['args']['fields'] = array_filter($table['args']['fields']);
                                }
                $return = $this->filterData($table);

                break;

           case 'notes':

                $notesHtml = $this->getReportRenderer()
                                  ->setViewRenderer($this->getServiceManager()->get('viewrenderer'))
                                    ->renderNotes($report, $widget);
                $return = array('success'=>true,'html' => $notesHtml);

                break;

            case 'piechart':

                $result    = $this->getServiceManager()->get('jimmybase_campaign_service')
                                                                       ->setClientAccount($client_account)
                                                                       ->getClientCriteriaReport($args, $widget->getType());


                $piechart    = $this->getReportRenderer()
                                   ->prepareResult($result, null, $filter)
                                   ->setViewRenderer($this->getServiceManager()->get('viewrenderer'))
                                   ->renderPie($report, $widget, $args, $download);

                $return = $piechart;
                break;

        }

        return $return;
    }





    public function prepareResult($result, $resultCompare=null)
    {
        # Normal Result
        if ($result) {
            $this->data = $this->__processResult($result);
        }


        # Comparable Result
        if ($resultCompare) {
            $this->dataCompare = $this->__processResult($resultCompare);
        }


        //var_dump($this->data);
        //exit;
        return $this;
    }


    private function __processResult($result)
    {   
        
       
        $xml = simplexml_load_string($result);

        foreach ($xml->table->columns->column as $column) {
            $columns[] = array('name' =>(string)$column->attributes()->name,'display'=>(string)$column->attributes()->display);
        }


        foreach ($xml->table->row as $row) {
            foreach ($columns as $column) {
                $d = (array)$row->attributes()->{$column['name']};
                $rowData[$column['name']] = $d[0];
            }

            $rows[] = $rowData;
        }


        $data['columns']        = $columns;
        $data['rows']            = $rows;

        return $data;
    }


    public function renderGraph($report, $widget, $args, $download=false)
    {
        if (!is_array($this->data)) {
            return false;
        }

        $start_date  = $args['date_range']['min'];
        $end_date    = $args['date_range']['max'];
        $duration    = date('d', $end_date-$start_date);
        $field          = $args['field'][1];
        $field_comp  = $args['field_compare'][1];
        $i          = 0;


        if (is_array($args['dependent_fields'])) {
            foreach ($args['dependent_fields']  as $column) {
                $depFields[] = $column[1];
            }
            $depFields  =  array_unique($depFields);
        }



        $rawDataTotal = array();

            # Segmentation Logic -- Loop Over each day data
            # Since data are returned and segmented by day
            for ($i = 0; $i < (int)$duration; $i++) {
                $day = date('Y-m-d', strtotime("+$i day", $start_date));
                $dataExistsForDate = false;

                if ($this->data['rows']) {
                    # Loop over each campaign data
                    foreach ($this->data['rows'] as $data) {
                        if ($day != $data['day']) {
                            continue;
                        }

                        $dataExistsForDate = true;

                        # For AvgPosition  (avgPos * impressions)
                        if ($field == 'avgPosition') {
                            if ($data['impressions']) {
                                $totals[$field][$day] += $data[$field] * $data['impressions'];
                            }
                        } else {
                            $totals[$field][$day]     += $data[$field];
                        }


                        # For certain fields values returned
                        # from adwords have to be manually processed
                        if ($depFields) {
                            foreach ($depFields as $depField) {
                                $depTotal[$depField][$i]   += $data[$depField];
                                $depDataTotal[$depField]    = $depTotal[$depField][$i];
                            }
                        }



                        if ($field_comp) {
                            # For AvgPosition  (avgPos * impressions)
                            if ($field_comp == 'avgPosition') {
                                if ($data['impressions']) {
                                    $totals[$field_comp][$day] += $data[$field_comp] * $data['impressions'];
                                }
                            } else {
                                $totals[$field_comp][$day] += $data[$field_comp];
                            }
                        }
                    }
                }

                # For Main Field
                $rawTotals[$field] = $totals[$field][$day] ;
                $this->_applyManualCalculations($rawTotals, $depDataTotal, array($field));
                $totals[$field][$day]  = $rawTotals[$field];

                # For Comparions
                $rawTotals[$field_comp] = $totals[$field_comp][$day] ;
                $this->_applyManualCalculations($rawTotals, $depDataTotal, array($field_comp));
                $totals[$field_comp][$day]  = $rawTotals[$field_comp];

                if (!$dataExistsForDate) {
                    $totals[$field][$day]            +=    null;
                    $totals[$field_comp][$day]    +=    null;
                }
            }

        foreach (array_keys($totals[$field]) as $date) {
            $new_date[] = date('d', strtotime($date));
        }

        $viewModel = new ViewModel();


        $template    = 'graph';
        if ($download) {
            $template = 'graph-download';
        }

        $viewModel->setTemplate($template)
               ->setVariables(array(
                                 'class'               => 'moreStuff radius5 t1',
                                 'args'               => $args,
                                 'field'               => $field,
                                 'totals'               => $totals,
                                 'new_date'           => $new_date,
                                 'field_comp'           => $field_comp,
                                 'widget'               => $widget
                      ));

        $script = $this->getViewRenderer()
                   ->render($viewModel);

        return $script;
    }


    public function renderTable($report, $widget, $args)
    {
        if (!is_array($this->data)) {
            return false;
        }

        $sort_by_append = null;

            # First sort the data by the given sort column
            $sortedRawData = $this->_groupData($this->data, $args['group_by']);


        if ($args['report_type'] == 'KEYWORDS_PERFORMANCE_REPORT') {
            if ($sortedRawData) {
                foreach ($sortedRawData as $key => $sortData) {
                    foreach ($sortData as $sortedData) {
                        $newKey = '';
                        if ($sortedData['matchType']=='Exact') {
                            $newKey   =   '['.$key.']';
                            $keyword    = '['.$sortedData['keyword'].']';
                        } elseif ($sortedData['matchType']=='Phrase') {
                            $newKey   =   '"'.$key.'"';
                            $keyword  = '"'.$sortedData['keyword'].'"';
                        } else {
                            $newKey  = $key;
                            $keyword = $sortedData['keyword'];
                        }


                        $sortedData['keyword']         = $keyword;
                        $sortedRawDataNew[$newKey][] = $sortedData;
                    }
                }
            }
        } elseif ($args['report_type'] == 'AD_PERFORMANCE_REPORT') {
            if ($sortedRawData) {
                foreach ($sortedRawData as $key => $sortData) {
                    foreach ($sortData as $sortedData) {
                        $newKey = '';
                        $newKey = $key.'-'.$sortedData['ad'];
                        $sortedRawDataNew[$newKey][] = $sortedData;
                    }
                }
            }
        }



        if ($sortedRawDataNew) {
            $sortedRawData = $sortedRawDataNew;
        }



        $rawDataFields = array();

        foreach ($args['fields_raw_data']  as $column) {
            $rawDataFields[] = $column[1];
        }

        $depFields = array();

        if (is_array($args['dependent_fields'])) {
            foreach ($args['dependent_fields']  as $column) {
                $depFields[] = $column[1];
            }
        }

        $rawDataFields = array_unique($rawDataFields, $kpiFields);
        $depFields     = array_unique($depFields);

        if ($sortedRawData) {
            foreach ($sortedRawData as $key => $data) {
                foreach ($data as $dataEach) {
                    foreach ($args['extra_fields'] as $column) {
                        $rawData[$key][$column[1]] = $dataEach[$column[1]];
                    }

                    foreach ($rawDataFields as $field) {
                        if ($field == 'avgPosition') {
                            $rawData[$key]['sumAvgPos'] = $dataEach[$field] * $dataEach['impressions'];
                            $rawData[$key][$field]     += $dataEach[$field];
                        } else {
                            $rawData[$key][$field]     += $dataEach[$field];
                        }
                    }

                    foreach ($depFields as $field) {
                        $depData[$key][$field]     += $dataEach[$field];
                    }
                }
            }
        }



        if ($rawData) {
            foreach ($rawData as $key => $data) {
                foreach ($rawDataFields as $column) {
                    $rawDataTotal[$column] += $data[$column];
                }

                if ($data['searchImprShare']) {
                    $searchImpressionShare++;
                }


                if ($data['sumAvgPos']) {
                    $rawDataTotal['sumAvgPos'] += $data['sumAvgPos'];
                }
            }
        }


        if ($depData) {
            foreach ($depData as $key => $data) {
                foreach ($depFields as $column) {
                    $depDataTotal[$column] += $data[$column];
                }
            }
        }



        if ($rawDataTotal['sumAvgPos']) {
            $rawDataTotal['avgPosition'] = $rawDataTotal['sumAvgPos'];
        }

        if ($rawDataTotal['searchImprShare']) {
            $rawDataTotal['searchImprShare'] = $rawDataTotal['searchImprShare'] / $searchImpressionShare;
        }


            # Perform Manual Calculations for Certain fields
            # The array is passed as reference

            $this->_applyManualCalculations($rawDataTotal, $depDataTotal, array('ctr', 'avgCPC', 'cost', 'costConv1PerClick', 'costConvertedClick', 'convRate1PerClick', 'avgPosition'));


        $viewModel = new ViewModel();

        $viewModel->setTemplate('table')
                       ->setVariables(array(
                                         'class'                => 'performTbl radius5',
                                         'widget'                => $widget,
                                         'field'                    => $field,
                                         'args'                => $args,
                                         'rawData'              => $rawData,
                                         'rawDataTotal'      => $rawDataTotal,
                                         'field_comp'            => $field_comp,
                                         'units'                    => $this->units
                              ));

        $html = $this->getViewRenderer()
                           ->render($viewModel);

        return $html;
    }


    private function __processKPIData($adwordsData, $args, $date_range)
    {
        $start_date  = $date_range['min'];
        $end_date    = $date_range['max'];
            //$duration    =($end_date-$start_date)/86400;
            $date1       = new \DateTime(date('Y-m-d', $start_date));
        $date2       = new \DateTime(date('Y-m-d', $end_date));
        $duration    = $date2->diff($date1)->format("%a");

        $i = 0;

        $kpiDataFields = array();

        $kpiFields = array();
        if (is_array($args['kpi_fields'])) {
            foreach ($args['kpi_fields']  as $column) {
                $kpiFields[] = $column[1];
            }
        }

        $depFields = array();

        if (is_array($args['dependent_fields'])) {
            foreach ($args['dependent_fields']  as $column) {
                $depFields[] = $column[1];
            }
        }


        $kpiDataFields = array_unique($kpiFields);
        $depFields     = array_unique($depFields);

        $searchImpressionShare = 0;
        $depData = array();
        for ($i = 0; $i <= (int)$duration; $i++) {
            $day = date('Y-m-d', strtotime("-$i day", $end_date));
            $dataExistsForDate     = false;
                    
                    # Loop over each campaign data
                    foreach ($adwordsData['rows'] as $data) {
                        if ($day != $data['day']) {
                            continue;
                        }


                        $this->currency = $data['currency'];

                        $dataExistsForDate = true;

                        foreach ($kpiDataFields as $field) {
                            
                            if ($field == 'avgPosition') {
                                if ($i <= 13) { // Check for the last 14 days data only
                                    $kpiDataSegmented[$day]['sumAvgPos']+= $data[$field] * $data['impressions'];
                                    $kpiDataSegmented[$day][$field]     += $data[$field];
                                }

                                $kpiDataTotal['sumAvgPos']  += $data[$field] * $data['impressions'];
                            } else {
                                // Check for the last 14 days data only
                                if ($i<=13) {
                                    $kpiDataSegmented[$day][$field]     += $data[$field];
                                }
                            }

                            if ($field=='searchImprShare' && $data['searchImprShare']=='--') {
                                continue;
                            }
                            $kpiDataTotal[$field] += $data[$field];
                        }


                        if ($data['searchImprShare']!='--') {
                            $searchImpressionShare++;
                            if ($i<=13) {
                                $kpiDataSegmented[$day]['campaignCount']++;
                            }
                        }



                        foreach ($depFields as $field) {
                            $depData[$day][$field]     += $data[$field];
                        }
                        if ($data['searchImprShare']) {
                            $AllImpr += ($depData[$day]['impressions']/ $data['searchImprShare']);
                        }
                        # For certain fields values returned
                        # from adwords have to be manually processed
//						if($depFields){
//
//							foreach($depFields as $depField){
//
//							    $depTotal[$i][$depField]   += $data[$depField];
//								$depDataTotal[$depField]    = $depTotal[$i][$depField];
//							}
//						}
                    }

            if ($i<=13 && $kpiDataSegmented[$day]['campaignCount']) {
                $kpiDataSegmented[$day]['searchImprShare'] = $kpiDataSegmented[$day]['searchImprShare'] / $kpiDataSegmented[$day]['campaignCount'];
            }
            
        }

        $kpiDataSegmented = array_reverse($kpiDataSegmented);



        foreach ($kpiDataSegmented as $key => $data2) {
            if ($data2['currency']) {
                $kpiDataSegmented[$key]['currency'] = $data2['currency'];
            }

            if ($data2['sumAvgPos']) {
                $data2['avgPosition'] += $data2['sumAvgPos'];
            }


            $this->_applyManualCalculations($data2, $depData[$key], array('ctr', 'avgCPC', 'cost','costConv', 'costConvertedClick', 'clickConversionRate', 'avgPosition', 'totalConvValue'));

            $kpiDataSegmented[$key]  = $data2;

            foreach ($kpiDataFields as $column) {
                $kpiDataSegementedTotal[$column][] = array($data2[$column]);
            }
        }

            #Calculate the totals of the dependent fields
            if ($depData) {
                foreach ($depData as $key => $data) {
                    foreach ($depFields as $column) {
                        $depDataTotal[$column] += $data[$column];
                    }
                }
            }

        if ($kpiDataTotal['sumAvgPos']) {
            $kpiDataTotal['avgPosition'] = $kpiDataTotal['sumAvgPos'];
        }

        if ($kpiDataTotal['searchImprShare']) {
            $kpiDataTotal['searchImprShare'] = $depDataTotal['impressions']  / $AllImpr;
        }
        
        
            # Perform Manual Calculations for Certain fields
            # The array is passed as reference
            $this->_applyManualCalculations($kpiDataTotal, $depDataTotal, array('ctr', 'avgCPC', 'cost',
                            'costConvertedClick', 'clickConversionRate', 'avgPosition', 'totalConvValue', 'valueConv','costConv'));



        foreach ($kpiDataTotal as $key => $value) {
            if ($args['kpi_fields'][$key]) {
                $newValue     = $this->getMetricsFormatService()->formatNumber($key, $value, $this->currency);

                if (is_numeric($newValue) && floor($newValue) != $newValue) {
                    $newValue = number_format($newValue, 2);
                } elseif (is_numeric($newValue)) {
                    $newValue = number_format($newValue);
                }
                $kpiDataTotalNew[] = array(
                                                                            'value' => (string)$newValue,'rawValue'=>$value,
                                                                            'caption'=>$args['kpi_fields'][$key][0],
                                                                            'key'=>$args['kpi_fields'][$key][1],
                                                                            'icon'=>$args['kpi_fields'][$key][3]
                                                                          );
            }
        }


        return array($kpiDataTotalNew,$kpiDataSegementedTotal);
    }


    public function renderKPI($report, $widget, $args, $download)
    {
        $args['report_type_id'] = 0;

        // var_dump($args);
            if ($this->data) {
                list($kpiDataTotal, $kpiDataSegmented) = $this->__processKPIData($this->data, $args, $args['date_range']);
            }


        if ($this->dataCompare) {
            list($kpiDataTotalCompare, $kpiDataSegmentedCompare) = $this->__processKPIData($this->dataCompare, $args, $args['date_range_compare']);
            foreach ($kpiDataSegmented as $key => $value) {
                foreach ($value as $k => $v) {
                    $kpiDataSegmented[$key][$k][] = $kpiDataSegmentedCompare[$key][$k][0];
                }
            }
        }


        $kpiVars =  array(
                                 'class' => 'moreStuff radius5 t1',
                                 'args' => $args,
                                 'currency' => $this->currency,
                                 'kpiDataTotal' => $kpiDataTotal,
                                 'kpiDataSegmented' => $kpiDataSegmented,
                                 'kpiDataTotalCompare' => $kpiDataTotalCompare,
                                 'units' => $this->units
                              );


        if (!$download) {
            return $kpiVars;
        }


        $viewModel = new ViewModel();


        $viewModel->setTemplate('kpi')
                       ->setVariables(array(
                                         'class'               => 'moreStuff radius5 t1',
                                         'args'               => $args,
                                         'kpiDataTotal'       => $kpiDataTotal,
                                         'kpiDataTotalCompare' => $kpiDataTotalCompare,
                                         'kpiDataSegmented'    => $kpiDataSegmented,
                                         'widget'               => $widget,
                                         'units'               => $this->units
                              ));

        $kpiHtml = $this->getViewRenderer()
                            ->render($viewModel);

     
        return $kpiHtml;
    }


    private function __processKPIData2($adwordsData, $args)
    {

           # First sort the data by the given sort column
            $sortedData    = $this->_groupData($adwordsData, $args['group_by']);
        $sortedDataDay = $this->_groupData($adwordsData, 'day');


        $start_date  = $args['date_range']['min'];
        $end_date    = $args['date_range']['max'];
        $duration    =($end_date-$start_date)/86400;
        $i = 0;

        $kpiDataFields = array();

        $kpiFields = array();
        if (is_array($args['kpi_fields'])) {
            foreach ($args['kpi_fields']  as $column) {
                $kpiFields[] = $column[1];
            }
        }

        $depFields = array();

        if (is_array($args['dependent_fields'])) {
            foreach ($args['dependent_fields']  as $column) {
                $depFields[] = $column[1];
            }
        }

        $kpiDataFields = array_unique($kpiFields);
        $depFields     = array_unique($depFields);

        foreach ($sortedDataDay as $key => $data) {
            foreach ($data as $dataEach) {
                for ($i = 0; $i <= (int)$duration; $i++) {
                    $day = date('Y-m-d', strtotime("+$i day", $start_date));
                    $dataExistsForDate = false;
                            # Loop over each campaign data
                            foreach ($dataEach as $segmentData) {

                                //if($day != $segmentData['day'] )
                                 // continue;

                                //print_r($segmentData);
                            }
                }
                        /*
                        foreach($args['extra_fields'] as $column){
                               $kpiData[$key][$column[1]] = $dataEach[$column[1]];
                        }

                        foreach($kpiDataFields as $field){


                           if($field == 'avgPosition'){
                                $kpiData[$key]['sumAvgPos']+= $dataEach[$field] * $dataEach['impressions'];
                                $kpiData[$key][$field]     += $dataEach[$field];
                           } else {
                                $kpiData[$key][$field]     += $dataEach[$field];
                           }

                       }

                       foreach($depFields as $field)
                                  $depData[$key][$field]     += $dataEach[$field];

                        */
                       if ($dataEach['currency']) {
                           $kpiData[$key]['currency'] = $dataEach['currency'];
                       }
            }
        }

            //echo '<pre>';
          //	print_r($kpiData);
              exit;



        foreach ($kpiData as $key => $data) {
            foreach ($kpiDataFields as $column) {
                $kpiDataTotal[$column] += $data[$column];
            }

            if ($data['currency']) {
                $kpiDataTotal['currency'] = $data['currency'];
            }


            if ($data['searchImprShare']) {
                $searchImpressionShare++;
            }

            if ($data['sumAvgPos']) {
                $kpiDataTotal['sumAvgPos'] += $data['sumAvgPos'];
            }
        }

            #Calculate the totals of the dependent fields
            if ($depData) {
                foreach ($depData as $key => $data) {
                    foreach ($depFields as $column) {
                        $depDataTotal[$column] += $data[$column];
                    }
                }
            }


        if ($kpiDataTotal['sumAvgPos']) {
            $kpiDataTotal['avgPosition'] = $kpiDataTotal['sumAvgPos'];
        }

        if ($kpiDataTotal['searchImprShare']) {
            $kpiDataTotal['searchImprShare'] = $kpiDataTotal['searchImprShare'] / $searchImpressionShare;
        }



            # Perform Manual Calculations for Certain fields
            # The array is passed as reference
            $this->_applyManualCalculations($kpiDataTotal, $depDataTotal, array('ctr', 'avgCPC', 'cost', 'costConvertedClick', 'clickConversionRate', 'avgPosition'));

        foreach ($kpiDataTotal as $key => $value) {
            if ($args['kpi_fields'][$key]) {
                $newValue     = $this->getMetricsFormatService()->formatNumber($key, $value);

                $kpiDataTotalNew[] = array('value' => string($newValue),'rawValue'=>$value,'caption'=>$args['kpi_fields'][$key][0],'key'=>$args['kpi_fields'][$key][1],'icon'=>$args['kpi_fields'][$key][3]);
            }
        }

        $this->currency = $kpiDataTotal['currency'];

            //$totals = $this->processKPIGraph($adwordsData,$args);

        return $kpiDataTotalNew;
    }


    public function processKPIGraph($adwordsData, $args)
    {
        $start_date  = $args['date_range']['min'];
        $end_date    = $args['date_range']['max'];
        $duration    =($end_date-$start_date)/86400;


        $field       = $args['field'][1];
        $field_comp  = $args['field_compare'][1];
        $i           = 0;

        if (!$duration) {
            $duration = 1;
        }

        if (is_array($args['dependent_fields'])) {
            foreach ($args['dependent_fields']  as $column) {
                $depFields[] = $column[1];
            }
            $depFields  =  array_unique($depFields);
        }

        $rawDataTotal = array();


        $kpiDataFields = array();

        $kpiFields = array();
        if (is_array($args['kpi_fields'])) {
            foreach ($args['kpi_fields']  as $column) {
                $kpiFields[] = $column[1];
            }
        }

        $depFields = array();

        if (is_array($args['dependent_fields'])) {
            foreach ($args['dependent_fields']  as $column) {
                $depFields[] = $column[1];
            }
        }





        $kpiDataFields = array_unique($kpiFields);
        $depFields     = array_unique($depFields);

            # Segmentation Logic -- Loop Over each day data
            # Since data are returned and segmented by day
            for ($i = 0; $i <= (int)$duration; $i++) {
                $day = date('Y-m-d', strtotime("+$i day", $start_date));
                $dataExistsForDate = false;

                if ($adwordsData['rows']) {
                    # Loop over each campaign data
                    foreach ($adwordsData['rows'] as $data) {
                        if ($day != $data['day']) {
                            continue;
                        }

                        $dataExistsForDate = true;

                        foreach ($kpiDataFields as $field) {
                            if ($field == 'avgPosition') {
                                $totals[$key]['sumAvgPos']+= $dataEach[$field] * $dataEach['impressions'];
                                $totals[$key][$field]     += $dataEach[$field];
                            } else {
                                $totals[$key][$field]     += $dataEach[$field];
                            }
                        }

                        foreach ($depFields as $field) {
                            $totals[$key][$field]     += $dataEach[$field];
                        }


                        if ($dataEach['currency']) {
                            $totals[$key]['currency'] = $dataEach['currency'];
                        }

                        # For certain fields values returned
                        # from adwords have to be manually processed
                        if ($depFields) {
                            foreach ($depFields as $depField) {
                                $depTotal[$depField][$i]   += $data[$depField];
                                $depDataTotal[$depField]    = $depTotal[$depField][$i];
                            }
                        }



                        if ($field_comp) {
                            # For AvgPosition  (avgPos * impressions)
                            if ($field_comp == 'avgPosition') {
                                if ($data['impressions']) {
                                    $totals[$field_comp][$day] += $data[$field_comp] * $data['impressions'];
                                }
                            } else {
                                $totals[$field_comp][$day] += $data[$field_comp];
                            }
                        }
                    }
                }

                # For Main Field
                $rawTotals[$field] = $totals[$field][$day] ;
                $this->_applyManualCalculations($rawTotals, $depDataTotal, array($field));
                $totals[$field][$day]  = $rawTotals[$field];

                # For Comparions
                $rawTotals[$field_comp] = $totals[$field_comp][$day] ;
                $this->_applyManualCalculations($rawTotals, $depDataTotal, array($field_comp));
                $totals[$field_comp][$day]  = $rawTotals[$field_comp];

                if (!$dataExistsForDate) {
                    $totals[$field][$day]            +=    null;
                    $totals[$field_comp][$day]    +=    null;
                }
            }


        foreach (array_keys($totals[$field]) as $date) {
            $new_date[] = date('d', strtotime($date));
        }


        $formatFields = array('ctr','clicks','impressions','cost','costConvertedClick','convRate','searchImprShare');


        foreach ($totals[$field] as $key => $val) {
            if (in_array($field, $formatFields)) {
                $val     = $this->getMetricsFormatService()->calculateMetrics($field, $val, $currency);
            } else {
                $val     = $this->getMetricsFormatService()->formatNumber($field, $val, $currency);
            }

            $newVal  = array('x' => $key,'y' => $val);


            if ($totals[$field_comp]) {
                if (in_array($field_comp, $this->formatFields)) {
                    $newVal['z']     = $this->getMetricsFormatService()->calculateMetrics($field_comp, $totals[$field_comp][$key], $currency);
                } else {
                    $newVal['z']     = $this->getMetricsFormatService()->formatNumber($field, $totals[$field_comp][$key], $currency);
                }
            }

            $newTotal[] = $newVal;
        }

        return $totals;
    }


    public function renderNotes($report, $widget)
    {
        $fields = unserialize($widget->getFields());

        $viewModel = new ViewModel();


        $viewModel->setTemplate('notes')
                       ->setVariables(array(
                                         'class'    => 'notes',
                                         'args'    => $args,
                                         'notes'    => $fields['notes'],
                                         'widget'    => $widget
                              ));

        $notesHtml = $this->getViewRenderer()
                              ->render($viewModel);


        return $notesHtml;
    }



    private function _applyManualCalculations(&$dataTotal, $depDataTotal, $fields)
    {
        /*echo '<pre>';
        print_r($dataTotal);
        print_r($depDataTotal);
        */
        if (is_array($depDataTotal) && !empty($depDataTotal)) {
            @extract($depDataTotal);
        }
        # Extract the array values and create variables out of them
        if (is_array($dataTotal) && !empty($dataTotal)) {
            @extract($dataTotal);
        }
    //	echo $avgPosition;
    //	echo $impressions
           
        foreach ($fields as $field) {
            switch ($field) {
                    case 'ctr':
                                if ($impressions > 0) {
                                    //   var_dump($clicks,$impressions);

                                    $dataTotal['ctr'] = round(($clicks/$impressions)*100, 2);
                                } else {
                                    $dataTotal['ctr'] = '0';
                                }

                                break;
                    case 'avgCPC':
                                if ($clicks > 0) {
                                    $dataTotal['avgCPC'] = round(($cost/1000000)/$clicks, 2);
                                } else {
                                    $dataTotal['avgCPC'] = '0.00';
                                }

                                break;
                    case 'cost':
                                if ($cost) {
                                    $dataTotal['cost'] = round($cost/1000000, 2);
                                } else {
                                    $dataTotal['cost'] = 0;
                                }

                                 break;
                    case 'costConvertedClick':
                                if ($convertedClicks) {
                                    $dataTotal['costConvertedClick'] = round(($cost/$convertedClicks)/1000000, 2);
                                } else {
                                    $dataTotal['costConvertedClick'] = '0.00';
                                }

                                break;
                    case 'clickConversionRate':

                                if ($clicks) {
                                    $dataTotal['clickConversionRate'] = round(($convertedClicks/$clicks)*100, 2);
                                } else {
                                    $dataTotal['clickConversionRate'] = '0';
                                }

                                break;

                    case 'avgPosition':
                                if ($impressions) {
                                    $dataTotal['avgPosition'] = round(($avgPosition/$impressions), 1);
                                } else {
                                    $dataTotal['avgPosition'] = '0';
                                }
                    break;
                    
                     case 'costConv':
                  
                            
                                if ($costConv) {
                                   //var_dump($dataTotal['conversions']);
                              $dataTotal['costConv'] = round(($cost/$dataTotal['conversions'])/1000000, 2);
                                  
                                } else {
                                  
                                    $dataTotal['costConv'] = '0';
                              }
                    break;

                    case 'totalConvValue':
                                if ($convertedClicks) {
                                    $dataTotal['totalConvValue'] = $totalConvValue;
                                                                           //round(($totalConvValue/$convertedClicks),1);
                                } else {
                                    $dataTotal['totalConvValue'] = '0';
                                }
                    break;
                                        case 'valueConv' :
                                                               if ($valueConv) {
                                                                   $dataTotal['valueConv'] = round($totalConvValue/$conversions, 2);
                                                                           //round(($totalConvValue/$convertedClicks),1);
                                                               } else {
                                                                   $dataTotal['valueConv'] = '0';
                                                               }
            }
        }
        /*	if(in_array($field,array_keys($dataTotal))){
                echo $manualColumns[$field]['formula'];
                eval($manualColumns[$field]['formula']);
            }*/



        //echo '<pre>';print_r($dataTotal);


        return $dataTotal;
    }


    private function _prepareRawData()
    {
    }


    private function _groupData($adwordsRawData, $group_by)
    {
        if (!is_array($adwordsRawData) or empty($group_by) or empty($adwordsRawData['rows'])) {
            return  false;
        }

        foreach ($adwordsRawData['rows'] as $data) {
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
        return $this->getServiceManager()->get('jimmybase_adwords_api_service');
    }

    /**
     * Get User Token Mapper.
     */
    public function getUserTokenMapper()
    {
        return $this->getServiceManager()->get('jimmybase_usertoken_mapper');
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
        $this->setAccessToken($client_account, $tokenObj->getToken());

        return $this;
    }

    public function setAccessToken($client_account, $api_auth_info)
    {
        if ($api_auth_info) {
            $api_access = unserialize($api_auth_info);

            if (list($new, $api_auth_info) = $this->getApiService()->verifyApiAccess($api_access)) {
                if ($new) {
                    $userTokenMapper = $this->getUserTokenMapper();
                    $tokenObj = $userTokenMapper->findById($client_account->getUserTokenId());

                    $tokenObj->setToken(serialize($api_auth_info));
                    $userTokenMapper->update($tokenObj);

                                        //$client_account = $this->getServiceManager()->get('jimmybase_clientaccounts_mapper')
                    //					   ->update($client_account);
                }
            }
        }

        return $this;
    }



    public function getClientCriteriaReport($args, $type)
    {
        if (empty($args)) {
            return false;
        }

      # Load the service, so that the required classes are available.
      $this->api->getAdWordsUser()->LoadService('ReportDefinitionService', GoogleAdWords::ADWORDS_VERSION);

      # Create selector.
      $selector = new \Selector();
        $args['fields'][] = 'AdNetworkType1';

        $selector->fields = $args['fields'];

      # Filter out deleted criteria.
     // $selector->predicates[] = new \Predicate('Status', 'NOT_IN', array('DELETED'));
      if (!empty($args['campaigns'])) {
          if ($args['campaigns']!='all') {
              $selector->predicates[] = new \Predicate('CampaignId', 'IN', $args['campaigns']);
          }
      }

        if (!empty($args['network_type'])) {
            $selector->predicates[] = new \Predicate('AdNetworkType1', 'IN', $args['network_type']);
        }


        if ($args['device_type']) {
            $selector->predicates[] = new \Predicate('Device', 'IN', $args['device_type']);
        }


      # Create report definition.
      $reportDefinition = new \ReportDefinition();
        if ($args['date_range_type']) {
            $reportDefinition->dateRangeType  = $args['date_range_type'];

            if ($args['date_range_type']=='CUSTOM_DATE') {
                $selector->dateRange = array('min' => date('Ymd', $args['date_range']['min']),'max' => date('Ymd', $args['date_range']['max']));
            }
        } elseif ($args['date_range_compare']) {
            $reportDefinition->dateRangeType      = 'CUSTOM_DATE';

            if ($args['date_range_compare']) {
                $selector->dateRange = array('min' => date('Ymd', $args['date_range_compare']['min']),'max' => date('Ymd', $args['date_range_compare']['max']));
            }
          //$reportDefinition->startDate = $args['date_range_compare']['min'];
          //$reportDefinition->endDate = $args['date_range_compare']['max'];
        }



        $reportDefinition->selector       = $selector;
        $reportDefinition->reportName     = $args['report_type'].' #' . uniqid();

        $reportDefinition->reportType     = $args['report_type'];
        $reportDefinition->downloadFormat = 'XML';

      # Exclude criteria that haven't recieved any impressions over the date range.
      $reportDefinition->includeZeroImpressions = false;

        if ($args['report_type'] == 'CAMPAIGN_PERFORMANCE_REPORT' && $type == 'table') {
            $reportDefinition->includeZeroImpressions = true;
        }

        if ($args['report_type'] == 'ADGROUP_PERFORMANCE_REPORT') {
            $selector->predicates[] = new \Predicate('AdGroupStatus', 'NOT_IN', array('DELETED'));
        }

      # Set additional options.
      $options = array('version' => GoogleAdWords::ADWORDS_VERSION);

        $campaign_array =  \ReportUtils::DownloadReport($reportDefinition, null, $this->api->getAdWordsUser(), $options);
       

        return $campaign_array;
    }


    public function getCampaigns($type='Active')
    {
        # Get the service, which loads the required classes.
          $campaignService = $this->getApiService()->getService('CampaignService');

          # Create selector.
          $selector = new \Selector();
        $selector->fields     = array('Id', 'Name');
        $selector->ordering[] = new \OrderBy('Name', 'ASCENDING');

          # Create paging controls.
          $selector->paging = new \Paging(0, \AdWordsConstants::RECOMMENDED_PAGE_SIZE);

        if ($type     == 'Active') {
            $selector->predicates[] = new \Predicate('Status', 'IN', array('ACTIVE'));
        } elseif ($type == 'Paused') {
            $selector->predicates[] = new \Predicate('Status', 'IN', array('PAUSED'));
        }


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


    public function getReportRenderer()
    {
        return $this->getServiceManager()->get('jimmybase_reportrenderer_service');
    }




    public function getReportParamsService()
    {
        if (!$this->report_params_service) {
            $this->setReportParamsService();
        }

        return $this->report_params_service;
    }

    public function setReportParamsService($report_params_service)
    {
        $this->report_params_service = $report_params_service;

        return $this;
    }


    public function getMetricsFormatService()
    {
        return $this->getServiceManager()->get('jimmybase_metricsformat_service');
    }



    public function getRequest()
    {
        if (!$this->request) {
            $this->setRequest();
        }

        return $this->request;
    }


    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }



    public function getViewRenderer()
    {
        return $this->getServiceManager()->get('viewrenderer');
    }



    /**
     * @var ServiceManager
     */
    protected $serviceManager;


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

    /*
     *function to filter the text and remve unwanted chars.
     */

    public function filterData($data)
    {
        foreach ($data['rawData'] as $i=>$r) {
            foreach ($r as $j=> $item) {
                if ($j == "ad") {
                    $data['rawData'][$i][$j] = preg_replace('/^.*(\s\-\s)/', '', $item);
                }
            }
        }

        return $data;
    }
}
