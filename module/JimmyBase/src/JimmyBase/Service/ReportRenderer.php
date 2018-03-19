<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;
use ZfcBase\EventManager\EventProvider;

class ReportRenderer extends EventProvider  implements ServiceManagerAwareInterface
{
    private $serviceManager;

    private $data;

    private $dataCompare;

    private $units = array();

    private $viewModel;

    private $viewRenderer;

    private $metrics_format_service = null;

    private $currency = 'AUD';

    private $formatFields = array('ctr', 'clicks', 'impressions', 'cost',
                                      'costConvertedClick', 'convRate', 'avgCPC','costConv', 
                                     'avgPosition', 'searchImprShare', 'ga:percentNewVisits',
                                     'searchLostISRank','searchLostISBudget',
                                      'ga:visitBounceRate', 'ga:pageviewsPerVisit', 'ga:entrances',
                                      'ga:exitRate', 'ga:pageValue', 'ga:transactionsPerVisit',
                                        'ga:transactionRevenue', 'ga:avgTimeOnSite', );

    public function __construct()
    {
        $this->units = array('clicks' => array('format' => '%s',    'decimal' => 0),
                           'impressions' => array('format' => '%s',    'decimal' => 0),
                           'conv1PerClick' => array('format' => '%s',    'decimal' => 0),
                           'ctr' => array('format' => '%s%%',    'decimal' => 2),
                           'avgCPC' => array('format' => 'A$%s',    'decimal' => 2),
                           'cost' => array('format' => 'A$%s',    'decimal' => 2),
                           'costConv1PerClick' => array('format' => 'A$%s',    'decimal' => 2),
                           'convRate1PerClick' => array('format' => '%s%%',    'decimal' => 2),
                           'avgPosition' => array('format' => '%s',    'decimal' => 1),
                          );
    }

    public function setViewRenderer($viewRenderer)
    {   
        
        $this->viewRenderer = $viewRenderer;

        return $this;
    }

    public function getViewRenderer()
    {
        return $this->viewRenderer;
    }

    public function prepareResult($result, $resultCompare = null, $filter = null)
    {

        # Normal Result
        if ($result) {
            $this->data = $this->__processResult($result, $filter);
        }

        # Comparable Result
        if ($resultCompare) {
            $this->dataCompare = $this->__processResult($resultCompare, $filter);
        }

        return $this;
    }

    private function __processResult($result, $filter = null)
    {
        $xml = simplexml_load_string($result);
        foreach ($xml->table->columns->column as $column) {
            $columns[] = array('name' => (string) $column->attributes()->name, 'display' => (string) $column->attributes()->display);
        }
        if ($filter) {
            foreach ($xml->table->row as $row) {
                $rowMatched = true;
                foreach ($columns as $column) {
                    foreach ($filter as $i => $f) {
                        if ($i == $column['name']) {
                            if (!preg_match('/'.$f.'/i', $row->attributes()->{$column['name']}[0])) {
                                $rowMatched = false;
                            }
                        }
                    }
                    $d = (array) $row->attributes()->{$column['name']};
                    $rowData[$column['name']] = $d[0];
                }
                if ($rowMatched) {
                    $rows[] = $rowData;
                }
            }
        } else {
            foreach ($xml->table->row as $row) {
                foreach ($columns as $column) {
                    $d = (array) $row->attributes()->{$column['name']};
                    $rowData[$column['name']] = $d[0];
                }

                $rows[] = $rowData;
            }
        }
        $data['columns'] = $columns;
        $data['rows'] = $rows;               
        return $data;
       
       
    }

    public function renderGraph($report, $widget, $args, $download = false)
    {   
        
        if (!is_array($this->data)) {
            return false;
        }
    
        $start_date = $args['date_range']['min'];
        $end_date = $args['date_range']['max'];
         // $duration    =($end_date-$start_date)/86400;
          $field = $args['field'][1];
        $field_comp = $args['field_compare'][1];
        $i = 0;
        $date1 = new \DateTime(date('Y-m-d', $start_date));
        $date2 = new \DateTime(date('Y-m-d', $end_date));
        $duration = $date2->diff($date1)->format('%a');

     
        if (!$duration) {
            $duration = 1;
        }

        if (is_array($args['dependent_fields'])) {
            foreach ($args['dependent_fields']  as $column) {
                $depFields[] = $column[1];
            }
            $depFields = array_unique($depFields);
        }

        $rawDataTotal = array();

            # Segmentation Logic -- Loop Over each day data
            # Since data are returned and segmented by day
            for ($i = 0; $i <= (int) $duration; ++$i) {
                $day = date('Y-m-d', strtotime("+$i day", $start_date));
                $dataExistsForDate = false;
                
                if ($this->data['rows']) {
                    # Loop over each campaign data
                    
                                    foreach ($this->data['rows'] as $data) {
                                       
                                        
                                        if ($day != $data['day']) {
                                            continue;
                                        }                                     
                                        //echo '<pre>';print_r($data);

                                        $this->currency = $data['currency'];

                                        $dataExistsForDate = true;
                                        
                                        $rowForEachDay[$day] += 1;
                                       
                                       
                                        # For AvgPosition  (avgPos * impressions)
                                        if ($field == 'avgPosition') {
                                            if ($data['impressions']) {
                                                $totals[$field][$day] += $data[$field] * $data['impressions'];
                                            }
                                        } else {
                                            $totals[$field][$day] += ($data[$field]);
                                        }
                                      
                                        # For certain fields values returned
                                        # from adwords have to be manually processed

                                        if ($depFields) {
                                            foreach ($depFields as $depField) {
                                                $depTotal[$depField][$i]   += $data[$depField];
                                                $depDataTotal[$depField] = $depTotal[$depField][$i];
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
                $rawTotals[$field] = $totals[$field][$day];                
                $this->_applyManualCalculations($rawTotals, $depDataTotal, array($field));
                $totals[$field][$day] = $rawTotals[$field];
                # For Comparions
                $rawTotals[$field_comp] = $totals[$field_comp][$day];
                $this->_applyManualCalculations($rawTotals, $depDataTotal, array($field_comp));
                $totals[$field_comp][$day] = $rawTotals[$field_comp];

                if (!$dataExistsForDate) {
                    $totals[$field][$day] = 0;
                    $totals[$field_comp][$day] = 0;
                }
            }
            
             if ($field == "searchImprShare" || $field == "searchLostISBudget" || $field == "searchLostISRank" || $field == "searchExactMatchIS") {
                                        foreach ($totals[$field] as $dayIndex => $rowItem) {
                                            $totals[$field][$dayIndex] = $totals[$field][$dayIndex]/$rowForEachDay[$dayIndex];
                                            
                                        }
                                    }
            if ($field_comp == "searchImprShare" || $field_comp == "searchLostISBudget" || $field_comp == "searchLostISRank" || $field_comp == "searchExactMatchIS") {
                 foreach ($totals[$field_comp] as $dayIndex => $rowItem) {
                    $totals[$field_comp][$dayIndex] = $totals[$field_comp][$dayIndex]/$rowForEachDay[$dayIndex];
                 }
            }

        foreach (array_keys($totals[$field]) as $date) {
            $new_date[] = date('d', strtotime($date));
        }

        $formatFields = array('ctr', 'clicks', 'impressions',
                                              'cost', 'costConvertedClick',
                                              'convRate', 'searchImprShare', );

        foreach ($totals[$field] as $key => $val) {
            if (in_array($field, $formatFields)) {
                $val = $this->getMetricsFormatService()
                            ->calculateMetrics($field, $val, $this->currency); 
            } else {
                $val = $this->getMetricsFormatService()
                            ->formatNumber($field, $val, $this->currency);
            }

            $newVal = array('x' => $key, 'y' => $val);

            if ($totals[$field_comp]) {
                if (in_array($field_comp, $formatFields)) {
                    $newVal['z'] = $this->getMetricsFormatService()
                                                                    ->calculateMetrics($field_comp, $totals[$field_comp][$key], $this->currency);
                } else {
                    $newVal['z'] = $this->getMetricsFormatService()
                                                                    ->formatNumber($field, $totals[$field_comp][$key], $this->currency);
                }
            }
            $newTotal[] = $newVal;
        }
        
        $graphVars = array(
                                'class' => 'moreStuff radius5 t1',
                                'args' => $args,
                                'field' => $field,
                                'totals' => $newTotal,
                                'new_date' => $new_date,
                                'field_comp' => $field_comp,
                            );

        if (!$download) {
            return $graphVars;
        }

        $viewModel = new ViewModel();

        $template = 'graph';

        if ($download) {
            $template = 'graph-download';
        }

        foreach ($totals[$field] as $key => $val) {
            $newVal = array('x' => $key, 'y' => $val);

            if ($totals[$field_comp]) {
                $newVal['z'] = $totals[$field_comp][$key];
            }

            $newTotalForDownload[] = $newVal;
        }

        $viewModel->setTemplate($template)
               ->setVariables(array(
                                            'class' => 'moreStuff radius5 t1',
                                            'args' => $args,
                                            'field' => $field,
                                            'totals' => $newTotalForDownload,
                                            'new_date' => $new_date,
                                            'field_comp' => $field_comp,
                                            'widget' => $widget,
                      ));

        $script = $this->getViewRenderer()
                   ->render($viewModel);

        return $script;
    }

    public function renderTable($report, $widget, $args, $download = false)
    {
        if (!is_array($this->data)) {
            return false;
        }

        
        if ($args['sort_by']) {
            $this->data['rows'] = $this->_sortData($this->data['rows'],$args['sort_by']);
        }
        if ($args['show_top']) {
            //for some reason the Adcopy table is showing one row less when show top selected"
                            if ($args['report_type'] == 'AD_PERFORMANCE_REPORT') {
                                $args['show_top'] += 1;
                            }
            array_splice($this->data['rows'], $args['show_top']);
        }
        $newsortedRawData =  array();
        if($args['report_type_id']==7 || $args['report_type_id']==8){
            $sortedRawData = $this->data['rows'];
            foreach ($sortedRawData as $value) {
               // unset ($value['campaign']);
               // unset ($value['campaignID']);
                if($args['report_type_id']==7 && !isset($args['sort_by']))
                {
                    $value['monthOfYear'] =  $value['monthOfYear'] . ' '. $value['year'];

                }elseif ($args['report_type_id']==8 && !isset($args['sort_by']))
                {
                    
                    //$value['week'] =  'Week ending on '.date('jS F  Y',strtotime($value['week'].' +6 days'));

                    unset ($value['month']);
                }
                unset ($value['year']);
                $newsortedRawData[]=$value;
            }
            $sortedRawData = $newsortedRawData;
        }else {
            # First sort the data by the given sort column
            $sortedRawData = $this->_groupData($this->data, $args['group_by']);
        }


//                        if($args['report_type'] != 'CAMPAIGN_PERFORMANCE_REPORT'
//                                                    && !isset($args['show_campaign'])) {
//                                 if($sortedRawData) {
//                                     foreach ($sortedRawData as $key =>$sortData) {
//                                         var_dump($key);
//                                     }
//                                 }
//                        }
            if ($args['report_type'] == 'KEYWORDS_PERFORMANCE_REPORT') {
                if ($sortedRawData) {
                    foreach ($sortedRawData as $key => $sortData) {
                        foreach ($sortData as $sortedData) {
                            $newKey = '';
                            if ($sortedData['matchType'] == 'Exact') {
                                $newKey = '['.$key.']';
                                $keyword = '['.$sortedData['keyword'].']';
                            } elseif ($sortedData['matchType'] == 'Phrase') {
                                $newKey = '"'.$key.'"';
                                $keyword = '"'.$sortedData['keyword'].'"';
                            } else {
                                $newKey = $key;
                                $keyword = $sortedData['keyword'];
                            }

                            $sortedData['keyword'] = $keyword;
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
        $depFields = array_unique($depFields);

        if ($sortedRawData) {
            $keyCount = array();                          
                foreach ($sortedRawData as $key => $data) {
                    if($args['report_type_id']!=7 && $args['report_type_id']!=8){
                    foreach ($data as $k => $dataEach) {
                        foreach ($args['extra_fields'] as $column) {
                            $rawData[$key][$column[1]] = $dataEach[$column[1]];
                        }
                        foreach ($rawDataFields as $field) {
                            if ($field == 'avgPosition') {
                                $rawData[$key]['sumAvgPos'] += $dataEach[$field] * $dataEach['impressions'];
                                $rawData[$key]['avgPosition']    += $dataEach[$field];
                            } else {
                                 if($args['report_type_id']!=7 && $args['report_type_id']!=8){
                                    $keyCount[$key] += 1;
                                    $rawData[$key][$field]  += round(preg_replace('/,/', '', $dataEach[$field]), 2);
                                 } else {
                                    $rawData[$key][$field]  += $dataEach[$field];

                                 }
                            }
                        }
                        
                       foreach ($depFields as $field) {
                           $depData[$key][$field]     += $dataEach[$field];
                       }
                        if ($dataEach['currency']) {
                            $rawData[$key]['currency'] = $dataEach['currency'];
                        }
                    }
                } else {
                     $dataEach =$data;
                        foreach ($args['extra_fields'] as $column) {
                            $rawData[$key][$column[1]] = $dataEach[$column[1]];
                        }

                        foreach ($rawDataFields as $field) {
                            if ($field == 'avgPosition') {
                                $rawData[$key]['sumAvgPos'] += $dataEach[$field] * $dataEach['impressions'];
                                $rawData[$key]['avgPosition']    += $dataEach[$field];
                            } else {
                                 if($args['report_type_id']!=7 && $args['report_type_id']!=8){
                                    $keyCount[$key] += 1;
                                    $rawData[$key][$field]  += round(preg_replace('/,/', '', $dataEach[$field]), 2);
                                 } else {
                                    $rawData[$key][$field]  += $dataEach[$field];

                                 }
                            }
                        }
                        
                       foreach ($depFields as $field) {
                           $depData[$key][$field]     += $dataEach[$field];
                       }
                        if ($dataEach['currency']) {
                            $rawData[$key]['currency'] = $dataEach['currency'];
                        }
                   

                }
                    if ($rawData[$key]['avgPosition']) {
                        //$rawData[$key]['avgPosition']  = $rawData[$key]['avgPosition']/($k+1);
                        //unset($rawData[$key]['avgPos']);
                    }
                      
                }
        }
        
        if ($rawData) {
            $AllImpr = 0;
            foreach ($rawData as $key => $data) {
                $rowCount++;
                foreach ($rawDataFields as $column) {                    
                    $rawDataTotal[$column] += $data[$column];
                }               
                                        //Manual calculation for each data row for ctr and cost/converted click.
                                           
                                        if ($data['ctr']) {
                                            $rawData[$key]['ctr'] = ($depData[$key]['clicks'] / $depData[$key]['impressions']) * 100;
                                        }
                                       

//                                        if ($data['costConvertedClick']) {
//
//                                            $rawData[$key]['costConvertedClick'] = $depData[$key]['cost']/ $depData[$key]['clicks'];
//                                        }

                if ($data['searchImprShare']) {
                    $AllImpr += ($depData[$key]['impressions']  / $data['searchImprShare']);
                }

                if ($data['sumAvgPos']) {
                    $rawDataTotal['sumAvgPos'] += $data['sumAvgPos'];
                }

                if ($data['currency']) {
                    $rawDataTotal['currency'] = $data['currency'];
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
        
        if ($rawDataTotal['convRate']) {          
                $rawDataTotal['convRate'] = ($depDataTotal['conversions']/$depDataTotal['clicks'])*100;
        }

        if ($rawDataTotal['sumAvgPos']) {
            $rawDataTotal['avgPosition'] = $rawDataTotal['sumAvgPos'];
        }

        if ($rawDataTotal['searchImprShare']) {
                $rawDataTotal['searchImprShare'] = $depDataTotal['impressions'] / $AllImpr;
            
        }
        if ($rawDataTotal['searchLostISBudget']) {
                $rawDataTotal['searchLostISBudget'] = $rawDataTotal['searchLostISBudget'] /$rowCount;
            
        }
        if ($rawDataTotal['searchLostISRank']) {
                $rawDataTotal['searchLostISRank'] = $rawDataTotal['searchLostISRank'] / $rowCount;
            
        }
        if ($rawDataTotal['searchExactMatchIS']) {
                $rawDataTotal['searchExactMatchIS'] = $rawDataTotal['searchExactMatchIS'] / $rowCount;            
        }
        
        if ($rawDataTotal) {
            # Perform Manual Calculations for Certain fields
            # The array is passed as reference
                     //   var_dump($rawDataTotal);
            $this->_applyManualCalculations($rawDataTotal, $depDataTotal,
                                            array('ctr', 'cost', 'costConvertedClick', 'avgCPC',
                                                  'clickConversionRate', 'costConvertedClick',));
             
        }
        if (!!isset($args['sort_by'])) {
            $rawData = $this->_sortData($rawData, $args['sort_by']);
        } else if($args['report_type_id']==7) { // Sorting Date descending.
            usort($rawData, function($a1, $a2) {
                               $value1month = strtotime($a1['monthOfYear']);
                               $value2month = strtotime($a2['monthOfYear']);
                               if ($value1month == $value2month) {return 0; } 
                               else {
                               return  $value1month < $value2month?1:-1;
                                }
                            });
        } else if($args['report_type_id']==8) {
            usort($rawData, function($a1, $a2) {
                               $value1week = strtotime($a1['week']);
                               $value2week = strtotime($a2['week']);
                               if ($value1week == $value2week) {return 0; } 
                               else {
                               return  $value1week < $value2week?1:-1;
                                }
                            });
            foreach ($rawData as $key => $value) {
                $rawData[$key]['week'] =  'Week ending on '.date('jS F  Y',strtotime($value['week'].' +6 days'));
            }
        }
        
        unset($rawDataTotal['sumAvgPos']);

        $currency = $rawDataTotal['currency'];

        //	if(!$download)
        unset($rawDataTotal['currency']);
        foreach ($rawDataTotal as $key => $value) {
            if (in_array($key, $this->formatFields)) {
                $value = $this->getMetricsFormatService()->calculateMetrics($key, $value, $currency);
                
            } else {
                $value = $this->getMetricsFormatService()->formatNumber($key, $value, $currency);
                 
            }
            
               
            if (is_numeric($value) && floor($value) != $value) {
                $value = number_format($value, 2);                
            } elseif (is_numeric($value)) {
                $value = number_format($value);
               
            }
            

            if ($args['fields_raw_data'][$key]) {
                $fld = $args['fields_raw_data'][$key];
            } elseif ($args['extra_fields'][$key]) {
                $fld = $args['extra_fields'][$key];
            }
            $dataTotalNew[$fld[1]] = array('value' => $value, 'caption' => $fld[2], 'key' => $fld[1]);
        }

        $rawDataTotalNew = $dataTotalNew;
       
        foreach ($rawData as $key => $values) {
           
            $dataNew = array();
            unset($values['sumAvgPos']);

            if (!$download) {
                unset($values['currency']);
            }

            foreach ($values as $k => $value) {
                if ($k == 'campaign') {
                    $dataNew['campaign'] = $value;
                } else {
                    if (in_array($k, array_diff($this->formatFields, array('avgPosition')))) {
                        $value = $this->getMetricsFormatService()->calculateMetrics($k, $value, $currency);
                    } else {
                        $value = $this->getMetricsFormatService()->formatNumber($k, $value, $currency);
                    }
                    if ($args['fields_raw_data'][$k]) {
                        $fld = $args['fields_raw_data'][$k];
                    } elseif ($args['extra_fields'][$k]) {
                        $fld = $args['extra_fields'][$k];
                    } else {
                        continue;
                    }

                    if (is_numeric($value) && floor($value) != $value) {
                        $dataNew[$fld[1]] = number_format((float) $value, 2);
                    } elseif (is_numeric($value)) {
                        $dataNew[$fld[1]] = number_format((int) $value);
                    } else {
                        $dataNew[$fld[1]] = $value;
                    }
                }
            }
            $rawDataNew[] = $dataNew;
        }
        $tableVars = array(
                                            'field' => $field,
                                            'args' => $args,
                                            'rawData' => $rawDataNew,
                                            'rawDataTotal' => $rawDataTotalNew,
                                            'field_comp' => $field_comp,
                                            'units' => $this->units,
                                            'channel' => $channel,
                                        );
        if (!$download) {
            return $tableVars;
        }

        $viewModel = new ViewModel();

        $template = 'table';

        if ($download) {
            $template = 'table-download';
        }

        $viewModel->setTemplate($template)
                           ->setVariables(array(
                                         'class' => 'performTbl radius5',
                                         'widget' => $widget,
                                         'field' => $field,
                                         'args' => $args,
                                         'rawData' => $rawDataNew,
                                         'rawDataTotal' => $rawDataTotalNew,
                                         'field_comp' => $field_comp,
                                         'units' => $this->units,
                                         'channel' => $channel,
                              ));

        $html = $this->getViewRenderer()
                           ->render($viewModel);

        return $html;
    }

    public function renderPie($report, $widget, $args, $download = false)
    {
        if (!is_array($this->data)) {
            return false;
        }

        $this->data['rows'] = $this->_sortData($this->data['rows'],
                                       key($args['fields_raw_data']));

    //    $args['ctitle'] = key($args['extra_fields']).' by '.key($args['fields_raw_data']); // The Heading of the Piechart.

        $args['ctitle'] = (array_values($args['extra_fields'])[0][1]).' by '.(array_values($args['fields_raw_data'])[0][0]);

        if (!$args['show_top']) {
            $args['show_top'] = 15;
        }
        array_splice($this->data['rows'], $args['show_top']);

       # First sort the data by the given sort column
       $sortedRawData = $this->_groupData($this->data, $args['group_by']);
    //                        if($args['report_type'] != 'CAMPAIGN_PERFORMANCE_REPORT'
    //                                                    && !isset($args['show_campaign'])) {
    //                                 if($sortedRawData) {
    //                                     foreach ($sortedRawData as $key =>$sortData) {
    //                                         var_dump($key);
    //                                     }
    //                                 }
    //                        }
       if ($args['report_type'] == 'KEYWORDS_PERFORMANCE_REPORT') {
           if ($sortedRawData) {
               foreach ($sortedRawData as $key => $sortData) {
                   foreach ($sortData as $sortedData) {
                       $newKey = '';
                       if ($sortedData['matchType'] == 'Exact') {
                           $newKey = '['.$key.']';
                           $keyword = '['.$sortedData['keyword'].']';
                       } elseif ($sortedData['matchType'] == 'Phrase') {
                           $newKey = '"'.$key.'"';
                           $keyword = '"'.$sortedData['keyword'].'"';
                       } else {
                           $newKey = $key;
                           $keyword = $sortedData['keyword'];
                       }

                       $sortedData['keyword'] = $keyword;
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
        $depFields = array_unique($depFields);

        if ($sortedRawData) {
            $keyCount = array();
                           //problem is hre
               foreach ($sortedRawData as $key => $data) {
                   foreach ($data as $k => $dataEach) {
                       foreach ($args['extra_fields'] as $column) {
                           $rawData[$key][$column[1]] = $dataEach[$column[1]];
                       }
//
                       foreach ($rawDataFields as $field) {
                           if ($field == 'avgPosition') {
                               $rawData[$key]['sumAvgPos'] += $dataEach[$field] * $dataEach['impressions'];
                               $rawData[$key]['avgPosition']    += $dataEach[$field];
                           } else {
                               $keyCount[$key] += 1;
                               $rawData[$key][$field]  += round(preg_replace('/,/', '', $dataEach[$field]), 2);
                           }
                       }
//
                      foreach ($depFields as $field) {
                          $depData[$key][$field]     += $dataEach[$field];
                      }
                       if ($dataEach['currency']) {
                           $rawData[$key]['currency'] = $dataEach['currency'];
                       }
                   }

                   if ($rawData[$key]['avgPosition']) {
                       //$rawData[$key]['avgPosition']  = $rawData[$key]['avgPosition']/($k+1);
                       //unset($rawData[$key]['avgPos']);
                   }
               }
        }

        if ($depData) {
            //    var_dump($depData);
               foreach ($depData as $key => $data) {
                   foreach ($depFields as $column) {
                       $depDataTotal[$column] += $data[$column];
                   }
               }
        }
        if ($args['sort_by']) {
            $rawData = $this->_sortData($rawData, $args['sort_by']);
        }

        $rawDataTotalNew = $dataTotalNew;
        $colorArray = array('#1660A1', '#F1B0C4', '#E14B78', '#C03A45',
                                       '#E0423F', '#FD9F2E', '#F7C00B', '#D4DE57',
                                     '#47C86B', '#1F84DC', );
        $i = 0;
        foreach ($rawData as $key => $values) {
            $dataNew = array();
            unset($values['sumAvgPos']);

            if (!$download) {
                unset($values['currency']);
            }
            $adskip = false;
            $campskip = false;
            $keyskip = false;
            foreach ($values as $k => $value) {
                if ($k == 'searchTerm') {
                    $dataNew['key'] = $values['keyword'].' \ '.$values['searchTerm'];
                    $campskip = true;
                    $adskip = true;
                    $keyskip = true;
                } elseif ($k == 'ad') {
                    $dataNew['key'] = $values['ad'].' \ '.$values['adGroup'];
                    $adskip = true;
                } elseif ($k == 'campaign' && $campskip == false) {
                    $dataNew['key'] = $values['campaign'];
                } elseif ($k == 'adGroup' && $adskip == false) {
                    $dataNew['key'] = $values['campaign'].' \ '.$values['adGroup'];
                } elseif ($k == 'keyword' && $keyskip == false) {
                    $dataNew['key'] = $values['keyword'];
                } else {
                    if (in_array($k, array_diff($this->formatFields, array('avgPosition')))) {
                        $value = $this->getMetricsFormatService()->calculateMetrics($k, $value, $currency);
                    } else {
                        $value = $this->getMetricsFormatService()->formatNumber($k, $value, $currency);
                    }
                    //Filter the tail string - Improve with better login in MetricsFormat Service
                    if (strpos($value, 'span>')) {
                        $valtemp = explode(' <', $value, 2);
                        $value = $valtemp[0];
                    } elseif (strpos($value, '%')) {
                        $value = substr($value, 0, -1);
                    }
                    $value = str_replace(',', '', $value);

                    if ($args['fields_raw_data'][$k]) {
                        $fld = $args['fields_raw_data'][$k];
                    } elseif ($args['extra_fields'][$k]) {
                        $fld = $args['extra_fields'][$k];
                    } else {
                        continue;
                    }

                    if (is_numeric($value) && floor($value) != $value) {
                        $dataNew['y'] = (float) $value;
                    } elseif (is_numeric($value)) {
                        $dataNew['y'] = (int) $value;
                    } else {
                        $dataNew['y'] = floatval($value);
                    }
                }
            }
            $dataNew['color'] = $colorArray[$i];
            ++$i;
            if ($i > 9) { //Reset color to 0 value of array
                    $i = 0;
            }
            $rawDataNew[] = $dataNew;
        }
        $tableVars = array(
                           'field' => $field,
                           'args' => $args,
                           'rawData' => $rawDataNew,
                           'units' => $this->units,

                           );

        if (!$download) {
            return $tableVars;
        }

        $viewModel = new ViewModel();

        $template = 'piechart';

        if ($download) {
            $template = 'piechart-download';
        }

        $viewModel->setTemplate($template)
                          ->setVariables(array(
                                        'class' => 'performTbl radius5',
                                        'widget' => $widget,
                                        'field' => $field,
                                        'args' => $args,
                                        'rawData' => $rawDataNew,
                                        'rawDataTotal' => $rawDataTotalNew,
                                        'units' => $this->units,
                             ));

        $html = $this->getViewRenderer()
                          ->render($viewModel);

        return $html;
    }

    public function renderPieChart($report, $widget, $args, $download = false)
    {
        if (!is_array($this->data)) {
            return false;
        }

        $field = $args['field'][1];
        $dtitle = $args['fields'][1];
        $field_comp = $args['field_compare'][1];
        $i = 0;

        $this->data['rows'] = $this->_sortData($this->data['rows'], $field);
        array_splice($this->data['rows'], 10);

        $colorArray = array('#1660A1', '#F1B0C4', '#E14B78', '#C03A45',
                                        '#E0423F', '#FD9F2E', '#F7C00B', '#D4DE57',
                                      '#47C86B', '#1F84DC', );
        $pieData = array();

               //  print_r($this->data );

                  foreach ($this->data['rows'] as $i => $row) {

                      //print_r($row[1]);
                      $dates = date_create($row['day']);

                      $pieData[] = array(
                                        'key' => date_format($dates, 'M, d').' : '.$row[$field].' '.$field,
                                         'y' => $row[$field],
                                         'color' => $colorArray[$i],
                                        );
                  }

        $graphVars = array(
                                    'args' => $args,
                                    'field' => $field,
                                    'totals' => $pieData,
                                );

            //Redner PDF is handled by piechart-download.phtml

        if (!$download) {
            return $graphVars;
        }

        $viewModel = new ViewModel();

        $template = 'graph';

        if ($download) {
            $template = 'graph-download';
        }

        foreach ($totals[$field] as $key => $val) {
            $newVal = array('x' => $key, 'y' => $val);

            if ($totals[$field_comp]) {
                $newVal['z'] = $totals[$field_comp][$key];
            }

            $newTotalForDownload[] = $newVal;
        }

        $viewModel->setTemplate($template)
                   ->setVariables(array(
                                                'class' => 'moreStuff radius5 t1',
                                                'args' => $args,
                                                'field' => $field,
                                                'totals' => $newTotalForDownload,
                                                'new_date' => $new_date,
                                                'field_comp' => $field_comp,
                                                'widget' => $widget,
                          ));

        $script = $this->getViewRenderer()
                       ->render($viewModel);

        return $script;
    }

    public function processKPIData()
    {
        $params = $e->getParams();
        $adwordsData = $params['data'];
        $args = $params['args'];

           # First sort the data by the given sort column
            $sortedData = $this->_groupData($adwordsData, $args['group_by']);

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
        $depFields = array_unique($depFields);
        foreach ($sortedData as $key => $data) {
            foreach ($data as $dataEach) {
                foreach ($args['extra_fields'] as $column) {
                    $kpiData[$key][$column[1]] = $dataEach[$column[1]];
                }

                foreach ($kpiDataFields as $field) {
                    if ($field == 'avgPosition') {
                        $kpiData[$key]['sumAvgPos'] = $dataEach[$field] * $dataEach['impressions'];
                        $kpiData[$key][$field]     += $dataEach[$field];
                    } else {
                        $kpiData[$key][$field]     += $dataEach[$field];
                    }
                }

                foreach ($depFields as $field) {
                    $depData[$key][$field]     += $dataEach[$field];
                }
            }
        }

        foreach ($kpiData as $key => $data) {
            foreach ($kpiDataFields as $column) {
                $kpiDataTotal[$column] += $data[$column];
            }

            if ($data['searchImprShare']) {
                ++$searchImpressionShare;
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
            $this->_applyManualCalculations($rawDataTotal, $depDataTotal, array('ctr', 'avgCPC', 'cost', 'costConvertedClick', 'clickConversionRate', 'avgPosition', 'totalConvValue'));

        return $kpiDataTotal;
    }

    public function renderKPI($report, $widget, $args, $channel)
    {
      
                       //var_dump($widget);
        $this->getEventManager()->trigger(__FUNCTION__.'.process', $this, array('data' => $this->data, 'args' => $args));

        if ($this->dataCompare) {
            $kpiDataTotalCompare = $this->__processKPIData($this->dataCompare, $args);
        }

        $viewModel = new ViewModel();

        $viewModel->setTemplate('kpi')
                                    ->setVariables(array(
                                                     'class' => 'moreStuff radius5 t1',
                                                     'args' => $args,
                                                     'kpiDataTotal' => $kpiDataTotal,
                                                     'kpiDataTotalCompare' => $kpiDataTotalCompare,
                                                     'widget' => $widget,
                                                     'units' => $this->units,
                                                   ));

        $kpiHtml = $this->getViewRenderer()
                            ->render($viewModel);

        return $kpiHtml;
    }

    public function renderNotes($report, $widget, $download = false)
    {
        $fields = unserialize($widget->getFields());

        $notesVar = array(
                                         'args' => $args,
                                         'notes' => $fields['notes'],
                                         'widget' => $widget,
                              );

        if (!$download) {
            return $notesVar;
        }

        $viewModel = new ViewModel();

        $viewModel->setTemplate('notes')
                       ->setVariables($notesVar);

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

        foreach (@array_keys($dataTotal) as $field) {
            switch ($field) {

                    case 'ctr':

                                if ($impressions > 0) {
                                    $dataTotal['ctr'] = round(($clicks / $impressions) * 100, 2);
                                } else {
                                    $dataTotal['ctr'] = '0';
                                }

                                break;
                    case 'avgCPC':
                                if ($clicks > 0) {
                                    $dataTotal['avgCPC'] = round($cost / $clicks, 2);
                                } else {
                                    $dataTotal['avgCPC'] = '0.00';
                                }
                                    
                                break;
//                    case 'cost':
//                                if ($cost) {
//                                    $dataTotal['cost'] = round($cost / 1000000, 2);
//                                } else {
//                                    $dataTotal['cost'] = 0;
//                                }
//
//                                 break;
                    case 'costConvertedClick':
                                if ($convertedClicks) {
                                    $dataTotal['costConvertedClick'] = $cost / $convertedClicks ;
                                } else {
                                    $dataTotal['costConvertedClick'] = '0.00';
                                }

                                break;
                    case 'costConv':
                                if ($costConv) {
                                    
                                    $dataTotal['costConv'] = round($cost/$conversions); 
                                } else {
                                    $dataTotal['costConv'] = '0.00';
                                }

                                break;
                    case 'clickConversionRate':

                                if ($clicks) {
                                    $dataTotal['clickConversionRate'] = round(($convertedClicks / $clicks) * 100, 2);
                                } else {
                                    $dataTotal['clickConversionRate'] = '0';
                                }

                                break;

                    case 'avgPosition':
                                if ($impressions) {
                                    $dataTotal['avgPosition'] = round(($avgPosition / $impressions), 1);
                                } else {
                                    $dataTotal['avgPosition'] = '0';
                                }
                                break;
                    case 'totalConvValue':
                                if ($convertedClicks) {
                                    $dataTotal['totalConvValue'] = $totalConvValue;
                                } else {
                                    $dataTotal['totalConvValue'] = '0';
                                }
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

    private function _prepareRawData()
    {
    }

    private function _sortData($data, $sort_by)
    {
        if (!is_array($data) or empty($sort_by)) {
            return  false;
        }

        usort($data, function ($a, $b) use ($sort_by) {
            if ($a[$sort_by] == $b[$sort_by]) {
                return 0;
            }

            return ($a[$sort_by] < $b[$sort_by]) ? 1 : -1;

        });

        return $data;
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

    public function getMetricsFormatService()
    {
        return $this->getServiceManager()->get('jimmybase_metricsformat_service');
    }

    public function setMetricsService($metrics_service)
    {
        $this->metricsService = $metrics_service;

        return $this;
    }

    public function getMetricsService()
    {
        return $this->metricsService;
    }

    /**
     * Retrieve service manager instance.
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance.
     *
     * @param ServiceManager $serviceManager
     *
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
