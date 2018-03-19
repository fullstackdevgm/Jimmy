<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;
use ZfcBase\EventManager\EventProvider;

class Analytics extends EventProvider implements ServiceManagerAwareInterface
{
    private $ga_metrics = null;

    private $request = null;

    private $data;

    private $dataCompare;

    private $metrics_format_service = null;

    private $units = array();

    private $formatFields = array('ctr', 'clicks', 'impressions', 'cost', 'costConvertedClick', 'avgCPC', 'avgPosition', 'searchImprShare',
                          'ga:percentNewVisits', 'ga:goalConversionRateAll', 'ga:visitBounceRate', 'ga:pageviewsPerVisit', 'ga:entrances', 'ga:exitRate', 'ga:pageValue',
                          'ga:transactionsPerVisit', 'ga:transactionRevenue', 'ga:transactionsPerSession', 'ga:avgTimeOnSite', 'ga:revenuePerTransaction',);

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function __construct()
    {
        $this->mapGaMetrics();
    }

    public function loadReport($widget, $client_account, $download = false)
    {
        if (!$client_account->getUserTokenId()) {
            return array('success' => false, 'message' => 'Migration not done');
        }

        $request = $this->getRequest();
        $analyticsInsightsService = $this->getServiceManager()
                                         ->get('jimmybase_analytics_insights_service');
        
        $report_id = $widget->getReportId();
        $report = $this->getServiceManager()->get('jimmybase_reports_service')
                          ->getMapper()
                          ->findById($report_id);
        $client = $this->getServiceManager()->get('jimmybase_client_service')
                          ->getClientMapper()
                          ->findById($report->getUserId());
        
        

        # If date filter is used
        if (method_exists($request, 'getQuery')) {
            $getParams = $request->getQuery()->toArray();
        }

        $args = $this->prepareParams($widget, $getParams);

        $args['channel'] = $client_account->getChannel();

        $data_ga = $this->getServiceManager()
                                ->get('jimmybase_dataga_api_service')
                    ->setClientAccount($client_account)
                ->setProfileId($args['profile_id']);
        if ($widget->getInsight()) {
             $insights = $analyticsInsightsService
                                ->getInsight($args, $widget, $args['id'], $data_ga);
             
                     
        } else {
            $insights = null;
        }
        
        switch ($widget->getType()) {
            

           case 'kpi':
               
                $result = $data_ga->fetchData($args, $widget);
                
                if ($args['date_range_compare']) {
                    $args_compare = $args;
                    $args_compare['date_range'] = $args['date_range_compare'];
                    $result_compare = $data_ga->setProfileId($args['id'])
                                 ->fetchData($args_compare, $widget);
                }

                $kpiHtml = $this->prepareKpiResult($result, $args, $result_compare)
                                   ->renderKpi($report, $widget, $args, $download, $insights);

                $return = $kpiHtml;

                break;

           case 'graph':

                $result = $data_ga->setProfileId($args['id'])
                                                  ->fetchData($args, $widget);

                $graph = $this->prepareGraphResult($result, $args)
                                              ->renderGraph($report, $widget, $args, $download, $insights);

                return $graph;

                $return = $graph;

                break;

           case 'table':
             
                $result = $data_ga->setProfileId($args['id'])
                                                  ->fetchData($args, $widget);
               
                $metrics_service = $this->getServiceManager()->get('jimmybase_metrics_service');
                $fields = unserialize($widget->getFields());
                $metric_type = $metrics_service->getAnalyticsTableMetricType($fields['metrics_type']);
                if(in_array($metric_type, array(
                  'Month on Month',
                  'Week on Week',
                  'Geo'
                ))) {
                  $segment_keyword = '';
                  if($metric_type=='Month on Month') {
                    $segment_keyword = 'ga:month';
                  }
                  if($metric_type=='Week on Week') {
                    $segment_keyword = 'ga:week';
                  }
                  
                  $html = $this->prepareTableResult($result, $args)
                               ->renderTable($report, $widget, $args, $download, true, $segment_keyword,$insights);
                } else if($metric_type == 'Channel Acquisitions') {
                    $segment_keyword = 'ga:channelGrouping';
                    $html = $this->prepareTableResult($result, $args)
                                               ->renderTable($report, $widget, $args, $download, true, $segment_keyword, $insights);

                } else {
                  $html = $this->prepareTableResult($result, $args)
                               ->renderTable($report, $widget, $args, $download,false, '', $insights);

                }

                $return = $html;

                break;
           case 'piechart':
                $result = $data_ga->setProfileId($args['id'])
                                                  ->fetchData($args, $widget);

                $html = $this->prepareTableResult($result, $args)
                                             ->renderPie($report, $widget, $args, $download, $insights);

                $return = $html;

                break;

           case 'notes':

                $notesHtml = $this->getReportRenderer()
                                  ->setViewRenderer($this->getServiceManager()->get('viewrenderer'))
                                    ->renderNotes($report, $widget);

                $return = $notesHtml;

                break;

        }
        if(!$download) {
            $return['insights'] = addslashes($insights);     
        }
       
        return $return;

    }

    private function prepareParams($widget, $opParams = null)
    {
        $metrics_service = $this->getServiceManager()->get('jimmybase_metrics_service');
        $widget_service = $this->getServiceManager()->get('jimmybase_widget_service');

        $fields = unserialize($widget->getFields());

        $metric_type = $metrics_service->getAnalyticsTableMetricType($fields['metrics_type']);

        if(in_array($metric_type, array(
          'Month on Month',
          'Week on Week'
        ))) {
          $segment_keyword = '';
          switch ($metric_type) {
            case 'Month on Month':
              $segment_keyword = 'Month';
              break;
            case 'Week on Week':
              $segment_keyword = 'Week';
              break;
          }
          if($fields['period']!= 14) {
            $opParams['date_range'] = 14;
            $dates = $widget_service->_parseWidgetDateRange($segment_keyword, $fields['period'],$fields['show_current_period'],'googleanalytics');
            $opParams['min'] = date('Y-m-d', $dates['min']);
            $opParams['max'] = date('Y-m-d', $dates['max']);
          }
        }

        if ($fields['profile_id']) {
            list($web_property_id, $profile_id) = explode(':', $fields['profile_id']);

            $args['id'] = 'ga:'.$profile_id;
        }
        if ($fields['segment']) {
            $args['segment'] = 'gaid::'.$fields['segment'];
        }
        if ($fields['filter']) {
            $args['filter'] = $fields['filter'];
        }
        $args['currency'] = $fields['currency'];
        
        //set args for insight.
        if ($fields['insight']) {
            $args['insight'] = $fields['insight'];
        }
        if ($opParams) {
            if ($opParams['date_range'] == 14) {
                $args['date_range'] = array('start' => $opParams['min'], 'end' => $opParams['max']);
                $date_range = array('min' => strtotime($opParams['min']), 'max' => strtotime($opParams['max']));
            } else {
                $date_range = $this->_parseDateRange($metrics_service->getDateRange($opParams['date_range']));
                $args['date_range'] = array('start' => date('Y-m-d', $date_range['min']), 'end' => date('Y-m-d', $date_range['max']));
            }
        } else {
            if ($fields['date_range'] == 14) {
                $args['date_range'] = array('start' => $fields['date_range_custom_min'], 'end' => $fields['date_range_custom_max']);
                $date_range = array('min' => strtotime($fields['date_range_custom_min']), 'max' => strtotime($fields['date_range_custom_max']));
            } else {
                $date_range = $this->_parseDateRange($metrics_service->getDateRange($fields['date_range']));
                $args['date_range'] = array('start' => date('Y-m-d', $date_range['min']), 'end' => date('Y-m-d', $date_range['max']));
            }

            if ($fields['compare_dates']) {
                if ($fields['date_range_compare'] == 'previous_period') {
                    $date_range_compare = $this->_getCompareDateRangeType($metrics_service->getDateRange($fields['date_range']));
                    $args['date_range_compare'] = array('start' => date('Y-m-d', $date_range_compare['min']), 'end' => date('Y-m-d', $date_range_compare['max']));
                } else {
                    $args['date_range_compare'] = array('start' => date('Y-m-d', strtotime($fields['date_range_custom_min_compare'])), 'end' => date('Y-m-d', strtotime($fields['date_range_custom_max_compare'])));
                    $date_range_compare = array('min' => strtotime($fields['date_range_custom_min_compare']), 'max' => strtotime($fields['date_range_custom_max_compare']));
                }

                $args['date_range_compare_formatted'] = array('min' => date('F j, Y', $date_range_compare['min']),
                                                                'max' => date('F j, Y', $date_range_compare['max']), );
            }
        }

        $args['date_range_formatted'] = array('min' => date('F j, Y', $date_range['min']),
                                                   'max' => date('F j, Y', $date_range['max']), );


        switch ($widget->getType()) {

            case 'kpi':
                if (is_array($fields['kpi'])) {
                    foreach ($fields['kpi'] as $metric_id) {
                        $metric = $this->_getGaMetric($metrics_service->getAnalyticsKPI($metric_id));

                        if ($metric) {
                            if ($metric[1] == 'ga:goalCompletionsAll') {
                                foreach ($fields['goals'] as   $goal) {
                                    $m = 'ga:goal'.$goal.'Completions';
                                    $goal_title = $this->getGoal($goal, $widget);
                                    $args['kpi_fields'][$m] = array($metric[0], $m, 'sub_caption' => $goal_title);
                                    $args['metrics'][] = $m;
                                }
                            } elseif ($metric[1] == 'ga:goalConversionRateAll') {
                                foreach ($fields['goals'] as   $goal) {
                                    $m = 'ga:goal'.$goal.'ConversionRate';
                                    $goal_title = $this->getGoal($goal, $widget);
                                    $args['kpi_fields'][$m] = array($metric[0], $m, 'sub_caption' => $goal_title);
                                    $args['metrics'][] = $m;
                                }
                            } else {
                                $args['kpi_fields'][$metric[1]] = $metric;
                                $args['metrics'][] = $metric[1];
                            }
                        }
                    }
                    $args['kpi_type'] = $fields['kpi_type'];
                   
                }

                $args['optParams'] = array('dimensions' => 'ga:date', 'sort' => 'ga:date');

                break;
            case 'graph':
                if ($fields['metrics']) {
                    $metric = $this->_getGaMetric($metrics_service->getAnalyticsGraphMetrics($fields['metrics_type'], $fields['metrics']));

                    if ($metric) {
                        if ($metric[1] == 'ga:goalCompletionsAll' && $fields['goals']) {
                            $m = 'ga:goal'.$fields['goals'].'Completions';
                            $goal_title = $this->getGoal($fields['goals'], $widget);
                            $args['field'] = array($metric[0], $m, $goal_title);
                            $args['metrics'][] = $m;
                        } elseif ($metric[1] == 'ga:goalConversionRateAll' && $fields['goals']) {
                            $m = 'ga:goal'.$fields['goals'].'ConversionRate';
                            $goal_title = $this->getGoal($fields['goals'], $widget);
                            $args['field'][$m] = array($metric[0], $m, $goal_title);
                            $args['metrics'][] = $m;
                        } else {
                            $args['field'] = $metric;
                            $args['metrics'][] = $metric[1];
                        }
                    }
                }

                if ($fields['compare']) {
                    $metric = $this->_getGaMetric($metrics_service->getAnalyticsGraphMetrics($fields['metrics_type_compare'], $fields['metrics_compare']));

                    if ($metric) {
                        if ($metric[1] == 'ga:goalCompletionsAll' && $fields['goals_compare']) {
                            $m = 'ga:goal'.$fields['goals_compare'].'Completions';
                            $goal_title = $this->getGoal($fields['goals_compare'], $widget);
                            $args['field_compare'] = array($metric[0], $m, $goal_title);
                            $args['metrics'][] = $m;
                        } elseif ($metric[1] == 'ga:goalConversionRateAll' && $fields['goals_compare']) {
                            $m = 'ga:goal'.$fields['goals_compare'].'ConversionRate';
                            $goal_title = $this->getGoal($fields['goals_compare'], $widget);
                            $args['field_compare'][$m] = array($metric[0], $m, $goal_title);
                            $args['metrics'][] = $m;
                        } else {
                            $args['field_compare'] = $metric;
                            $args['metrics'][] = $metric[1];
                        }
                    }
                }

                $args['optParams'] = array('dimensions' => 'ga:date', 'sort' => 'ga:date');
                break;
            case 'table':

                if (is_array($fields['raw_data'])) {
                    foreach ($fields['raw_data'] as $metric_id) {
                        $metric = $this->_getGaMetric($metrics_service->getAnalyticsTableMetrics($fields['metrics_type'], $metric_id));

                        if ($metric) {
                            if ($metric[1] == 'ga:goalCompletionsAll') {
                                foreach ($fields['goals'] as   $goal) {
                                    $m = 'ga:goal'.$goal.'Completions';
                                    $goal_title = $this->getGoal($goal, $widget);
                                    $args['fields_raw_data'][$m] = array($metric[0], $m, 'G. Compl.', null, $goal_title);
                                    $args['metrics'][] = $m;
                                }
                            } elseif ($metric[1] == 'ga:goalConversionRateAll') {
                                foreach ($fields['goals'] as   $goal) {
                                    $m = 'ga:goal'.$goal.'ConversionRate';
                                    $goal_title = $this->getGoal($goal, $widget);
                                    $args['fields_raw_data'][$m] = array($metric[0], $m, 'G. Conv. R.', null, $goal_title);
                                    $args['metrics'][] = $m;
                                }
                            }
//

                                                else {
                                                    $args['fields_raw_data'][$metric[1]] = $metric;
                                                    $args['metrics'][] = $metric[1];
                                                }
                        }
                    }
                }

                $metric_type = $metrics_service->getAnalyticsTableMetricType($fields['metrics_type']);
                                if ($metric_type == 'E-Commerce') {
                                    $dimensions = 'ga:productName,';
                                    $metric = $this->_getGaMetric('Product');
                                    $args['extra_fields']['ga:productName'] = $metric;
                                    foreach ($args['metrics'] as $key => $m) {
                                        if ($m == 'ga:productSku' || $m == 'ga:productCategory') {
                                            $dimensions .= $m.',';
                                            $metric = $this->_getGaMetric($args['fields_raw_data'][$m][0]);
                                            $args['extra_fields'][$m] = $metric;
                                            unset($args['fields_raw_data'][$m]);
                                            unset($args['metrics'][$key]);
                                        }
                                    }
                                    //$args['fields_raw_data'] = array_filter($args['fields_raw_data']);

                                    $dimensions = rtrim($dimensions, ',');
                                    $args['optParams'] = array(
                                                                'dimensions' => $dimensions,
                                                                'sort' => $args['metrics'][0],
                                                              );

                                } else if ($metric_type == 'Campaign') {
                                    $dimensions = 'ga:campaign';
                                    $metric = $this->_getGaMetric($metric_type);
                                    $args['extra_fields']['ga:campaign'] = $metric;
                                    $args['optParams'] = array(
                                                                'dimensions' => $dimensions,
                                                                'sort' => $args['metrics'][0],
                                                              );
                                } else if($metric_type == 'Month on Month') {
                                    $dimensions = 'ga:month, ga:year';
                                    $metric1 = $this->_getGaMetric('Month');
                                    $metric2 = $this->_getGaMetric('Year');
                                    $args['extra_fields'][$metric1[1]] = $metric1;
                                    $args['extra_fields'][$metric2[1]] = $metric2;
                                    $args['optParams'] = array('dimensions' => $dimensions, 'sort' => $args['metrics'][0]);
                                } else if($metric_type == 'Week on Week') {
                                  $dimensions = 'ga:week, ga:year';
                                  $metric1 = $this->_getGaMetric('Week');
                                  $metric2 = $this->_getGaMetric('Year');
                                  $args['extra_fields'][$metric1[1]] = $metric1;
                                  $args['extra_fields'][$metric2[1]] = $metric2;
                                  $args['optParams'] = array('dimensions' => $dimensions, 'sort' => $args['metrics'][0]);
                                } else if($metric_type == 'Channel Acquisitions') {
                                    $dimensions = 'ga:channelGrouping';
                                    $metric = $this->_getGaMetric($metric_type);
                                    $args['extra_fields']['ga:channelGrouping'] = $metric;
                                    $args['optParams'] = array('dimensions' => $dimensions, 'sort' => $args['metrics'][0]);
                                } else {
                                    $metric = $this->_getGaMetric($metric_type);
                                    $args['extra_fields'][$metric[1]] = $metric;
                                    $args['optParams'] = array('dimensions' => $metric[1], 'sort' => $args['metrics'][0]);
                                }
                if ($fields['sort_by']) {
                    $args['sort_by'] = $this->_getGaMetric($metrics_service->getAnalyticsTableMetrics($fields['metrics_type'], $fields['sort_by']))[1];
                    $args['optParams']['sort'] = '-'.$args['sort_by'];
                    $args['sort_by'] = str_replace(':', '', $args['sort_by']);
                } else {
                    if(in_array($metric_type, array(
                        'Month on Month',
                        'Week on Week'
                        ))) {
                    // sorts in reverse chronological order if no sort is set up
                    $args['optParams']['sort'] = '-ga:year,-ga:'.strtolower($segment_keyword);
                    $args['sortby'] = "-ga".strtolower($segment_keyword);
                    }
                }

                if ($fields['show_top']) {
                    $args['show_top'] = $fields['show_top'];
                    $args['optParams']['start-index'] = 1;
                    $args['optParams']['max-results'] = $args['show_top'];
                }

                break;
            case 'piechart':

                $metric_id = $fields['raw_data'];

                                        $metric = $this->_getGaMetric($metrics_service->getAnalyticsPiechartMetrics($fields['metrics_type'], $metric_id));

                                        if ($metric) {
                                            if ($metric[1] == 'ga:goalCompletionsAll') {
                                                foreach ($fields['goals'] as   $goal) {
                                                    $m = 'ga:goal'.$goal.'Completions';
                                                    $goal_title = $this->getGoal($goal, $widget);
                                                    $args['fields_raw_data'][$m] = array($metric[0], $m, 'G. Compl.', null, $goal_title);
                                                    $args['metrics'][] = $m;
                                                }
                                            } elseif ($metric[1] == 'ga:goalConversionRateAll') {
                                                foreach ($fields['goals'] as   $goal) {
                                                    $m = 'ga:goal'.$goal.'ConversionRate';
                                                    $goal_title = $this->getGoal($goal, $widget);
                                                    $args['fields_raw_data'][$m] = array($metric[0], $m, 'G. Conv. R.', null, $goal_title);
                                                    $args['metrics'][] = $m;
                                                }
                                            } else {
                                                $args['fields_raw_data'][$metric[1]] = $metric;
                                                $args['metrics'][] = $metric[1];
                                            }
                                        }

                $metric_type = $metrics_service->getAnalyticsTableMetricType($fields['metrics_type']);
                                if ($metric_type == 'E-Commerce') {
                                    $dimensions = 'ga:productName,';
                                    $metric = $this->_getGaMetric('Product');
                                    $args['extra_fields']['ga:productName'] = $metric;
                                    foreach ($args['metrics'] as $key => $m) {
                                        if ($m == 'ga:productSku' || $m == 'ga:productCategory') {
                                            $dimensions .= $m.',';
                                            $metric = $this->_getGaMetric($args['fields_raw_data'][$m][0]);
                                            $args['extra_fields'][$m] = $metric;
                                            unset($args['fields_raw_data'][$m]);
                                            unset($args['metrics'][$key]);
                                        }
                                    }
                                    //$args['fields_raw_data'] = array_filter($args['fields_raw_data']);

                                    $dimensions = rtrim($dimensions, ',');
                                    $args['optParams'] = array(
                                                                'dimensions' => $dimensions,
                                                                'sort' => $args['metrics'][0],
                                                              );
                                } else if ($metric_type == 'Campaign') {
                                    $dimensions = 'ga:campaign';
                                    $metric = $this->_getGaMetric($metric_type);
                                    $args['extra_fields']['ga:campaign'] = $metric;
                                    $args['optParams'] = array(
                                                                'dimensions' => $dimensions,
                                                                'sort' => $args['metrics'][0],
                                                              );
                                } else {
                                    $metric = $this->_getGaMetric($metric_type);
                                    $args['extra_fields'][$metric[1]] = $metric;
                                    $args['optParams'] = array('dimensions' => $metric[1], 'sort' => $args['metrics'][0]);
                                }
                          $fields['sort_by'] = $fields['raw_data'];
                if ($fields['sort_by']) {
                    $args['sort_by'] = $this->_getGaMetric($metrics_service->getAnalyticsPiechartMetrics($fields['metrics_type'], $fields['sort_by']))[1];
                    $args['optParams']['sort'] = '-'.$args['sort_by'];
                    $args['sort_by'] = str_replace(':', '', $args['sort_by']);
                }

                if ($fields['show_top']) {
                    $args['show_top'] = $fields['show_top'];
                    $args['optParams']['start-index'] = 1;
                    $args['optParams']['max-results'] = $args['show_top'];
                } else {
                    $fields['show_top'] = 15;
                }
                break;
        }

//		    	$args['optParams']['']     = "ga:currencyCode";

        //echo '<pre>';print_r($args);
        if ($fields['insights']) {
             $args['insights'] = $fields['insights'];
        }

        if (is_array($args['metrics'])) {
            $args['metrics'] = implode(',', $args['metrics']);
        }
        //$args['metrics'].= ',ga:sourceMedium';
      return $args;
    }

    private function _parseDateRange($date_range)
    {
        switch ($date_range) {
            case 'Today': // Today
                    return array('min' => strtotime('today'), 'max' => strtotime('today'));
            break;
            case 'Yesterday':
                    return array('min' => strtotime('-1 day'), 'max' => strtotime('-1 day'));
            break;

            case 'This week (Sun - Today)':
                  $min = strtotime('last sun');
                  $max = strtotime('today');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'This week (Mon - Today)':
                  $min = strtotime('last mon');
                  $max = strtotime('today');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last 7 days':
                  $max = strtotime('yesterday');
                  $min = strtotime('-6 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last week (Sun - Sat)':
                  $saturday = strtotime('last sat');
                  $sunday = strtotime('-6 day', $saturday);

                  return array('min' => $sunday, 'max' => $saturday);
            break;

            case 'Last week (Mon - Sun)':

                  $monday = strtotime('last week');
                  $sunday = strtotime('last sun');

                  return array('min' => $monday, 'max' => $sunday);
            break;
            case 'Last business week (Mon - Fri)':
                  $monday = strtotime('last week');
                  $sunday = strtotime('last fri');

                  return array('min' => $monday, 'max' => $sunday);
            break;
            case 'Last 14 days':
                  $max = strtotime('yesterday');
                  $min = strtotime('-13 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;
            case 'Custom':
                  $monday = strtotime('last week');
                  $sunday = strtotime('today');

                  return array('min' => $monday, 'max' => $sunday);
            break;
            case 'This month':
                  $max = strtotime('today');
                  $min = strtotime('first day of this month');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last 30 days':
                  $max = strtotime('yesterday');
                  $min = strtotime('-29 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last month':
                  $max = strtotime('last day of last month');
                  $min = strtotime('first day of last month');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'All time':

                  $max = strtotime('today');
                  $min = strtotime('1.1.2005');

                return array('min' => $min, 'max' => $max);
            break;
            default:
               return;

      }
    }

    private function _getCompareDateRangeType($date_range, $custom = null)
    {
        switch ($date_range) {
            case 'Today': // Today
                    return array('min' => strtotime('-1 day'), 'max' => strtotime('-1 day'));
            break;
            case 'Yesterday':
                    return array('min' => strtotime('-2 day'), 'max' => strtotime('-2 day'));
            break;

            case 'This week (Sun - Today)':
                  $min = strtotime('last sun - 1 week');
                  $max = strtotime('today - 1 week');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'This week (Mon - Today)':
                  $min = strtotime('last mon -1 week');
                  $max = strtotime('today -1 week');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last 7 days':
                  $max = strtotime('yesterday - 7 day');
                  $min = strtotime('-6 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last week (Sun - Sat)':
                  $max = strtotime('last sat - 1 week');
                  $min = strtotime('-6 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last week (Mon - Sat)':
                  $max = strtotime('last sat - 1 week');
                  $min = strtotime('-5 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;

                        case 'Last week (Mon - Sun)':
                  $max = strtotime('last sun - 1 week');
                  $min = strtotime('-6 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last business week (Mon - Fri)':
                  $max = strtotime('last fri - 1 week');
                  $min = strtotime('-4 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;
            case 'Last 14 days':
                  $max = strtotime('yesterday - 14 day');
                  $min = strtotime('-13 day', $max);

                  return array('min' => $min, 'max' => $max);

            break;
            case 'This month':
                  $max = strtotime('last day of last month');
                  $min = strtotime('first day of last month');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last 30 days':
                  $max = strtotime('yesterday - 30 day');
                  $min = strtotime('-29 day', $max);

                  return array('min' => $min, 'max' => $max);
            break;

            case 'Last month':
                  $max = strtotime('last day of last month - 1 month');
                  $min = strtotime('first day of last month - 1 month');

                  return array('min' => $min, 'max' => $max);
            break;

            case 'All time':
                  $max = strtotime('today');
                  $min = strtotime('1.1.2005');

                return array('min' => $min, 'max' => $max);

            break;

            case 'Custom':
                  $max = strtotime("{$custom['max']}");
                  $min = strtotime("{$custom['min']}");
                  $duration = $max - $min;

                  $dur_days = floor($duration / (60 * 60 * 24));
                  ++$dur_days;
                  $max = strtotime("{$custom['max']} - {$dur_days} day");
                  --$dur_days;
                  $min = strtotime("- {$dur_days} days", $max);

                  return array('min' => $min, 'max' => $max);
            break;
      }
    }

    private function mapGaMetrics()
    {
        $this->ga_metrics = array(
                        'Visits' => array('Visits', 'ga:visits', 'Visits', 'icon-bar-chart-o'),
                        'Sessions' => array('Sessions', 'ga:sessions', 'Sessions', 'icon-bar-chart-o'),
                        'Users' => array('Users', 'ga:users', 'Users', 'icon-male'),
                        'Pageviews' => array('Pageviews', 'ga:pageviews', 'Pageviews', 'icon-eye'),
                        'Pages/Visit' => array('Pages/Visit', 'ga:pageviewsPerVisit', 'Pages/Visits', 'icon-code-fork'),
                        'Avg. Duration' => array('Avg. Duration', 'ga:avgTimeOnSite', 'Avg. Duration', 'icon-click'),
                        'Bounce Rate' => array('Bounce Rate', 'ga:visitBounceRate', 'Bounce Rate', 'icon-external-link'),
                        '% New Sessions' => array('% New Sessions', 'ga:percentNewSessions', '% New Sessions', 'icon-sing-in'),
                        'New Visits' => array('New Visits', 'ga:newVisits', 'New Visits'),
                        'Goal Completions' => array('Goal Completions', 'ga:goalCompletionsAll', 'Goal Completions', 'icon-check'),
                        'Goal Conversion Rate' => array('Goal Conversion Rate', 'ga:goalConversionRateAll', 'Goal Conv. Rate', 'icon-check-sqaure'),
                        'Goal Value' => array('Goal Value', 'ga:goalValueAll', 'Goal Value'),
                        'Goal Total Abandonment' => array('Goal Total Abandonment', 'ga:goalAbandonsAll', 'Goal Total Abandonment'),
                        'Conversion Rate' => array('Conversion Rate', 'ga:transactionsPerVisit', 'Conv. Rate'),
                        'Ecommerce Conversion Rate' => array('Ecom. Conv. Rate', 'ga:transactionsPerSession', 'Ecom. Conv. Rate'),
                        'Transactions' => array('Transactions', 'ga:transactions', 'Trans.'),
                        'Qty' => array('Qty', 'ga:itemQuantity', 'Qty'),
                        'Revenue' => array('Revenue', 'ga:transactionRevenue', 'Revenue', 'icon-dollar'),
                        'Avg. Order Value' => array('Avg. Order Value', 'ga:revenuePerTransaction', 'Avg. Order Value'),
                        'Unique Purchase' => array('Unique Purchase', 'ga:uniquePurchases', 'Unique Purchase'),
                        '% Exit' => array('% Exit', 'ga:exitRate', '% Exit'),
                        'Unique Pageviews' => array('Unique Pageviews', 'ga:uniquePageviews', 'Unique Pageviews'),
                        'Avg. Time on Page' => array('Avg. Time on Page', 'ga:avgTimeOnPage', 'Avg. Time on Page'),
                        'Entrances' => array('Entrances', 'ga:entrances', 'Entrances'),
                        'Page Value' => array('Page Value', 'ga:pageValue', 'Page Value'),
                        'Source Medium' => array('Source Medium', 'ga:sourceMedium', 'Source Medium'),
                        'Geo' => array('Country/Territory', 'ga:country', 'Country'),
                        'Site Content' => array('Site Content', 'ga:pagePath', 'Page'),
                        'Product' => array('Product', 'ga:productName', 'Product'),
                        'Campaign' => array('Campaign', 'ga:campaign', 'Campaign'),
                        'Product SKU' => array('Product SKU', 'ga:productSku', 'Product SKU'),
                        'Transactions' => array('Transactions', 'ga:transactions', 'Transactions'),
                        'Average Order Value' => array('Average Order Value', 'ga:revenuePerTransaction', 'Average Order Value'),                        
                        'Product Category' => array('Product Category', 'ga:productCategory', 'Product Category'),
                        'Quantity' => array('Qyantity', 'ga:itemQuantity', 'Quantity'),
                        'Unique Purchases' => array('Unique Purchases', 'ga:uniquePurchases', 'Unique Purchases'),
                        'Product Revenue' => array('Product Revenue', 'ga:itemRevenue', 'Product Revenue'),
                        'Average Price' => array('Average Price', 'ga:revenuePerItem', 'Average Price'), // no direct val need calculation.
                        'Average QTY' => array('Average QTY', 'ga:itemsPerPurchase', 'Average QTY'), // no direct val need calculation.
                        'Month' => array('Month', 'ga:month', 'Month'),
                        'Year' => array('Year', 'ga:year', 'Year'),
                        'Week' => array('Week', 'ga:week', 'Week'),
                        'Channel Acquisitions' => array('Channel Grouping', 'ga:channelGrouping', 'Channel Grouping'),
                        'Cost Per Conversion' => array('Cost Per Conversion', 'ga:costPerConversion', 'Cost Per Conversion'),

        );
    }

    private function _getGaMetric($metric_name /* This is the display value */)
    {
        if (is_array($this->ga_metrics) && isset($this->ga_metrics[$metric_name])) {
            return $this->ga_metrics[$metric_name];
        }

        return false;
    }

    public function prepareKpiResult($data, $args, $dataCompare = null)
    {
        $dataArray = array();
        $dataArrayCompare = array();

        if ($data) {
            $start_date = strtotime($args['date_range']['start']);
            $end_date = strtotime($args['date_range']['end']);
            $date1 = new \DateTime(date('Y-m-d', $start_date));
            $date2 = new \DateTime(date('Y-m-d', $end_date));
            $duration = $date2->diff($date1)->format('%a');
            //$duration    =($end_date-$start_date)/86400;

            if ($args['kpi_fields']) {
                foreach ($args['kpi_fields'] as $key => $field) {
                    $dataArray[$field[1]] = $data->totalsForAllResults[$field[1]];
                }
            }

            $dataReversed = array_reverse($data->rows);
            // For Sparklines in KPI
            for ($i = 0; $i <= $duration;++$i) {
                $day = date('Ymd', strtotime("-$i day", $end_date));

                if ($dataReversed[$i][0] == $day && $i <= 13) {
                    # Loop over each campaign data

                    if ($args['kpi_fields']) {
                        $j = 1;
                        foreach ($args['kpi_fields'] as $key => $field) {
                            $dataArraySegmented[$field[1]][] = array($dataReversed[$i][$j++]);
                        }
                    }
                }
            }

            if ($args['fields']) {
                foreach ($args['fields'] as $key => $field) {
                    $dataArray[$field[1]] = $data->totalsForAllResults[$field[1]];
                }
            }
        }

        if ($dataCompare) {
            if ($args['kpi_fields']) {
                foreach ($args['kpi_fields'] as $key => $field) {
                    $dataArrayCompare[$field[1]] = $dataCompare->totalsForAllResults[$field[1]];
                }
            }
            $start_date = strtotime($args['date_range_compare']['start']);
            $end_date = strtotime($args['date_range_compare']['end']);
            //$duration    =($end_date-$start_date)/86400;
            $date1 = new \DateTime(date('Y-m-d', $start_date));
            $date2 = new \DateTime(date('Y-m-d', $end_date));
            $duration = $date2->diff($date1)->format('%a');
            $dataReversed = array_reverse($dataCompare->rows);

            // For Sparklines in KPI
            for ($i = 0; $i <= $duration;++$i) {
                $day = date('Ymd', strtotime("-$i day", $end_date));
                $dataExistsForDate = false;

                if ($dataReversed[$i][0] == $day && $i <= 13) {
                    # Loop over each campaign data
                    $dataExistsForDate = true;
                    if ($args['kpi_fields']) {
                        $j = 1;
                        foreach ($args['kpi_fields'] as $key => $field) {
                            echo $k;
                            $dataArraySegmented[$field[1]][$i][] = $dataReversed[$i][$j++];
                        }
                    }
                }
            }

            if ($args['fields']) {
                foreach ($args['fields'] as $key => $field) {
                    $dataArrayCompare[$field[1]] = $data->totalsForAllResults[$field[1]];
                }
            }
        }

        foreach ($args['kpi_fields'] as $key => $field) {
            $dataArraySegmented[$field[1]] = array_reverse($dataArraySegmented[$field[1]]);
        }

        $this->data = $dataArray;

        $this->dataArraySegmented = $dataArraySegmented;

        $this->dataCompare = $dataArrayCompare;

        return $this;
    }

    public function prepareGraphResult($data, $args, $dataCompare = null)
    {
        $dataArray = array();
        $dataArrayCompare = array();

        if ($args['field']) {
            foreach ($data->rows as $key => $val) {
                $dataArray[$args['field'][1]][$val[0]] = $val[1];
            }
        }

        if ($args['field_compare']) {
            foreach ($data->rows as $key => $val) {
                $dataArrayCompare[$args['field_compare'][1]][$val[0]] = $val[2];
            }
        }

        $this->data = $dataArray;

        $this->dataCompare = $dataArrayCompare;

        return $this;
    }

    public function applyFilter($data, $row)
    {
    }

    public function prepareTableResult($data, $args, $dataCompare = null, $isSegmentedByYear = false)
    {
        $dataArray = array();
        $dataArrayCompare = array();
        $filter = $args['filter'];
        //var_dump($data);exit;
        $rawDataFields = array();
        # Check if the data is segmented by year.
        if($isSegmentedByYear) {
          foreach ($args['extra_fields']  as $column) {
            if($column[1]!='Year') {
              $rawDataFields[] = $column[1];
            }
          }
        } else {
          foreach ($args['extra_fields']  as $column) {
            if($column[1]!='Year') {
              $rawDataFields[] = $column[1];
            }
          }
        }

        foreach ($args['fields_raw_data']  as $column) {
            $rawDataFields[] = $column[1];
        }

        if ($data->rows) {
            if ($filter) {
                foreach ($data->rows as $key => $val) {
                    $key = $val[0];
                    $sortedData = array();
                    $match = true;
                    foreach ($rawDataFields as $k => $field) {
                        foreach ($filter as $i => $fl) {
                            if ($i == str_replace(':', '', $field)) {
                                if (!preg_match('/'.$fl.'/i', $val[$k])) {
                                    $match = false;
                                }
                            }
                        }
                        $sortedData[$field] = $val[$k];
                    }
                    if ($match) {
                        $dataArray[$key] = $sortedData;
                    }
                }
            } else {
                foreach ($data->rows as $key => $val) {
                    $key = $val[0];
                    $sortedData = array();

                    foreach ($rawDataFields as $k => $field) {
                        $sortedData[$field] = $val[$k];
                    }

                    $dataArray[$key] = $sortedData;
                }
            }
        }

        $this->data['rows'] = $dataArray;

        $this->data['totals'] = $data->totalsForAllResults;

        return $this;
    }

    private function renderKPI($report, $widget, $args, $download, $insights=null)
    {
        
        $min = new \DateTime($args['date_range']['start']);
        $max = new \DateTime($args['date_range']['end']);

        $currency = $args['currency'];

        $date = array('min' => $min->getTimestamp(), 'max' => $max->getTimestamp());
        $args['date_range'] = $date;

        if ($args['date_range_compare']) {
            $min = new \DateTime($args['date_range_compare']['start']);
            $max = new \DateTime($args['date_range_compare']['end']);

            $date = array('min' => $min->getTimestamp(), 'max' => $max->getTimestamp());
            $args['date_range_compare'] = $date;
        }

        foreach ($this->data as $key => $value) {
            if ($args['kpi_fields'][$key]) {
                $newKey = preg_replace('!\d+!', '', $key);

                $newValue = $this->getMetricsFormatService()->formatNumber($newKey, $value, $currency);

                $caption = $args['kpi_fields'][$key][0];
                $sub_caption = $args['kpi_fields'][$key]['sub_caption'];
                $kpiDataTotalNew[] = array('value' => $newValue, 'rawValue' => $value, 'caption' => $caption, 'sub_caption' => $sub_caption, 'key' => $args['kpi_fields'][$key][1], 'icon' => 'icon-bar-chart-o');
            }
        }

        foreach ($this->dataCompare as $key => $value) {
            if ($args['kpi_fields'][$key]) {
                $newKey = preg_replace('!\d+!', '', $key);

                $newValue = $this->getMetricsFormatService()
                                                                             ->formatNumber($newKey, $value, $currency);
                $caption = $args['kpi_fields'][$key][0];
                $sub_caption = $args['kpi_fields'][$key][2];
                $kpiDataTotalNewCompare[] = array('value' => $newValue, 'rawValue' => $value, 'caption' => $caption, 'sub_caption' => $sub_caption, 'key' => $args['kpi_fields'][$key][1], 'icon' => 'icon-bar-chart-o');
            }
        }

        $kpiVars = array(
                             'class' => 'moreStuff radius5 t1',
                             'args' => $args,
                             'kpiDataSegmented' => $this->dataArraySegmented,
                             'kpiDataTotal' => $kpiDataTotalNew,
                             'kpiDataTotalCompare' => $kpiDataTotalNewCompare,
                             'widget' => $widget,
                             'units' => $this->units,
                              );
        if (!$download) {
            return $kpiVars;
        }

        $viewModel = new ViewModel();

        $viewModel->setTemplate('kpi')
                      ->setVariables(array(
                                         'class' => 'moreStuff radius5 t1',
                                         'args' => $args,
                                         'kpiDataTotal' => $kpiDataTotalNew,
                                         'kpiDataTotalCompare' => $kpiDataTotalNewCompare,
                                         'kpiDataSegmented' => $this->dataArraySegmented,
                                         'widget' => $widget,
                                         'insights' => $insights,
                                         'units' => $this->units,
                              ));

        $kpiHtml = $this->getViewRenderer()
                            ->render($viewModel);

        return $kpiHtml;
    }

    public function renderGraph($report, $widget, $args, $download = false, $insights=null)
    {
        if (!is_array($this->data)) {
            return false;
        }

        $min = new \DateTime($args['date_range']['start']);
        $max = new \DateTime($args['date_range']['end']);

        $date = array('min' => $min->getTimestamp(), 'max' => $max->getTimestamp());
        $args['date_range'] = $date;
        //echo '<pre>';print_r($args);
        $currency = $args['currency'];

        $start_date = $args['date_range']['min'];
        $end_date = $args['date_range']['max'];
       // $duration    = date('d',$end_date-$start_date);
          $date1 = new \DateTime(date('Y-m-d', $start_date));
        $date2 = new \DateTime(date('Y-m-d', $end_date));
        $duration = $date2->diff($date1)->format('%a');

        $field = $args['field'][1];
        $field_comp = $args['field_compare'][1];
        $i = 0;

        $rawDataTotal = array();

            # Segmentation Logic -- Loop Over each day data
            # Since data are returned and segmented by day
            for ($i = 0; $i < (int) $duration; ++$i) {
                $day = date('Ymd', strtotime("+$i day", $start_date));
                $dataExistsForDate = false;

                if (isset($this->data[$field][$day])) {
                    # Loop over each campaign data

                    $dataExistsForDate = true;

                    $totals[$args['field'][1]][date('Y-m-d', strtotime($day))] = $this->data[$field][$day];

                    if ($field_comp) {
                        $totals[$args['field_compare'][1]][date('Y-m-d', strtotime($day))] = $this->dataCompare[$field_comp][$day];
                    }
                }
            }

        foreach (array_keys($totals[$args['field'][0]]) as $date) {
            $new_date[] = date('d', strtotime($date));
        }

        foreach ($totals[$field] as $key => $val) {
            if (in_array($field, $this->formatFields)) {
                $val = $this->getMetricsFormatService()->calculateMetrics($field, $val, $currency);
            } else {
                $val = $this->getMetricsFormatService()->formatNumber($field, $val, $currency);
            }

            $newVal = array('x' => $key, 'y' => $val);

            if ($totals[$field_comp]) {
                if (in_array($field_comp, $this->formatFields)) {
                    $newVal['z'] = $this->getMetricsFormatService()
                                                                        ->calculateMetrics($field_comp, $totals[$field_comp][$key], $currency);
                } else {
                    $newVal['z'] = $this->getMetricsFormatService()
                                                                        ->formatNumber($field, $totals[$field_comp][$key], $currency);
                }
            }

            $newTotal[] = $newVal;
        }                     

        $graphVars = array(
                                 'class' => 'moreStuff radius5 t1',
                                 'args' => $args,
                                 'field' => $args['field'][0],
                                 'totals' => $newTotal,
                                 'new_date' => $new_date,
                                 'field_comp' => $args['field_compare'][0],
                                 'insights' => $insights,
                                 'widget' => $widget,
                      );

        if (!$download) {
            return $graphVars;
        }

        $viewModel = new ViewModel();

        $template = 'graph';
        if ($download) {
            $template = 'graph-download';
        }

        $viewModel->setTemplate($template)
               ->setVariables($graphVars);

        $script = $this->getViewRenderer()
                   ->render($viewModel);

        return $script;
    }

    public function renderTable($report, $widget, $args, $download, 
                                $isSegmentedByYear=false, $segment_keyword='',$insights = null 
                                )
    {
        if (!is_array($this->data['rows'])) {
            return false;
        }

        $sortedRawData = $this->data['rows'];
        $currency = $args['currency'];
       

        $rawDataFields = array();

        foreach ($args['extra_fields']  as $column) {
            $rawDataFields[] = $column[1];
        }
        foreach ($args['fields_raw_data']  as $column) {
            $rawDataFields[] = $column[1];
        }

        if ($sortedRawData) {
            foreach ($sortedRawData as $key => $data) {
                foreach ($rawDataFields as $field) {
                    if (isset($data[$field])) {
                        $rawData[$key][$field] = $data[$field];
                    }
                }
            }
        
        }

        $min = new \DateTime($args['date_range']['start']);
        $max = new \DateTime($args['date_range']['end']);

        $date = array('min' => $min->getTimestamp(), 'max' => $max->getTimestamp());

        $args['date_range'] = $date;

        $rawDataTotal = $this->data['totals'];

        foreach ($rawDataTotal as $key => $value) {
            if (in_array($key, $formatFieldsTotal)) {
                $value = $this->getMetricsFormatService()->calculateMetrics($key, $value, $currency);
            } else {
                $value = $this->getMetricsFormatService()->formatNumber($key, $value, $currency);
            }

            $caption = $args['fields_raw_data'][$key][2];
            $sub_caption = $args['fields_raw_data'][$key][3];
            $dataTotalNew[str_replace(':', '', $args['fields_raw_data'][$key][1])] = array('value' => $value, 'caption' => $caption, 'sub_caption' => $sub_caption, 'key' => $args['fields_raw_data'][$key][1]);
        }

        $rawDataTotalNew = $dataTotalNew;

        $newRawData = array();
        if($isSegmentedByYear) {
          if($segment_keyword=='ga:month') {
            foreach ($rawData as $value) {
              $dateObj   = \DateTime::createFromFormat('!m', $value['ga:month']);
              $monthText = $dateObj->format('F');
              $value['ga:month'] = $monthText.' '.$value['ga:year'];
              unset($value['ga:year']);
              $newRawData[] = $value;
              $rawData = $newRawData;
            }
          } else if($segment_keyword=='ga:week') {
            foreach ($rawData as $value) {
              $week_date = new \DateTime();
              $week_date->setISODate($value['ga:year'], $value['ga:week']-1);
              $week_date->add(new \DateInterval('P5D')); // Because for google week begins on sunday and for PHP it begins on Monday ):
              $value['ga:week'] = 'Week ending on '.$week_date->format('jS F Y');
              unset($value['ga:year']);
              $newRawData[] = $value;
            }
            $rawData = $newRawData;
          }
        }

        foreach ($rawData as $key => $values) {
            $dataNew = array();

            unset($values['sumAvgPos']);

            if (!$download) {
                unset($values['currency']);
            }

            if($isSegmentedByYear) {
              unset($args['extra_fields']['ga:year']);
            }

            foreach ($values as $k => $value) {
                if (in_array($k, $this->formatFields)) {
                    $value = $this->getMetricsFormatService()->calculateMetrics($k, $value, $currency);
                } else {
                    $value = $this->getMetricsFormatService()->formatNumber($k, $value, $currency);
                }

                if ($args['fields_raw_data'][$k]) {
                    $caption = $args['fields_raw_data'][$k][2];
                    $keyName = $args['fields_raw_data'][$k][1];
                } elseif ($args['extra_fields'][$k]) {
                    $caption = $args['extra_fields'][$k][2];
                    $keyName = $args['extra_fields'][$k][1];
                }

//                if (is_numeric($value) && floor($value) != $value) {
//                    $dataNew[$fld[1]] = (float) $value;
//                } elseif (is_numeric($value)) {
//                    $dataNew[$fld[1]] = (int) $value;
//                } else {
//                    $dataNew[$fld[1]] = $value;
//                }
                $dataNew[str_replace(':', '', $keyName)] = $value;
            }
            $rawDataNew[] = $dataNew;
        }
        

        foreach ($args['extra_fields'] as $key => $value) {
            $value[1] = str_replace(':', '', $value[1]);
            $args['extra_fields'][$key] = $value;
        }

        foreach ($args['fields_raw_data'] as $key => $value) {
            $value[1] = str_replace(':', '', $value[1]);
            $args['fields_raw_data'][$key] = $value;
        }

        if($segment_keyword == 'ga:month') {
            $args['report_type_id'] = 7;
        } else if($segment_keyword == 'ga:week') {
            $args['report_type_id'] = 8;
        }
       
        $tableVars = array(
                                            'args' => $args,
                                            'rawData' => $rawDataNew,
                                            'rawDataTotal' => $rawDataTotalNew,
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
                       ->setVariables(
                                    array(
                                         'widget' => $widget,
                                         'field' => $field,
                                         'args' => $args,
                                         'rawData' => $rawDataNew,
                                         'rawDataTotal' => $rawDataTotalNew,
                                         'insights' => $insights,
                                         'units' => $this->units,
                                    )
                               );

        $html = $this->getViewRenderer()
                           ->render($viewModel);

        return $html;
    }

    public function renderPie($report, $widget, $args, $download, $insights = null)
    {
        if (!is_array($this->data['rows'])) {
            return false;
        }
        if (!$args['show_top']) {
            $args['show_top'] = 15;
        }
        array_splice($this->data['rows'], $args['show_top']);

        $sortedRawData = $this->data['rows'];
        $currency = $args['currency'];

        $args['ctitle'] = (array_values($args['extra_fields'])[0][0]).' by '.(array_values($args['fields_raw_data'])[0][0]);

        $rawDataFields = array();

        foreach ($args['extra_fields']  as $column) {
            $rawDataFields[] = $column[1];
        }
        foreach ($args['fields_raw_data']  as $column) {
            $rawDataFields[] = $column[1];
        }

        if ($sortedRawData) {
            foreach ($sortedRawData as $key => $data) {
                foreach ($rawDataFields as $field) {
                    if (isset($data[$field])) {
                        $rawData[$key][$field] = $data[$field];
                    }
                }
            }
        }

        $min = new \DateTime($args['date_range']['start']);
        $max = new \DateTime($args['date_range']['end']);

        $date = array('min' => $min->getTimestamp(), 'max' => $max->getTimestamp());

        $args['date_range'] = $date;

        $rawDataTotal = $this->data['totals'];

        foreach ($rawDataTotal as $key => $value) {
            if (in_array($key, $formatFieldsTotal)) {
                $value = $this->getMetricsFormatService()->calculateMetrics($key, $value, $currency);
            } else {
                $value = $this->getMetricsFormatService()->formatNumber($key, $value, $currency);
            }

            $caption = $args['fields_raw_data'][$key][2];
            $sub_caption = $args['fields_raw_data'][$key][3];
            $dataTotalNew[str_replace(':', '', $args['fields_raw_data'][$key][1])] = array('value' => $value, 'caption' => $caption, 'sub_caption' => $sub_caption, 'key' => $args['fields_raw_data'][$key][1]);
        }

        $rawDataTotalNew = $dataTotalNew;
        $colorArray = array('#1660A1', '#F1B0C4', '#E14B78', '#C03A45',
                                        '#E0423F', '#FD9F2E', '#F7C00B', '#D4DE57',
                                      '#47C86B', '#c21fdd', );

        $ci = 0;
        foreach ($rawData as $key => $values) {
            $dataNew = array();

            unset($values['sumAvgPos']);

            if (!$download) {
                unset($values['currency']);
            }

            foreach ($values as $k => $value) {
                if (in_array($k, $this->formatFields)) {
                    $value = $this->getMetricsFormatService()->calculateMetrics($k, $value, $currency);
                } else {
                    $value = $this->getMetricsFormatService()->formatNumber($k, $value, $currency);
                }
                if (strpos($value, 'span>')) {
                    $valtemp = explode(' <', $value, 2);
                    $value = $valtemp[0];
                } elseif (strpos($value, '%')) {
                    $value = substr($value, 0, -1);
                }
                $value = str_replace(',', '', $value);

                if ($args['fields_raw_data'][$k]) {
                    $caption = $args['fields_raw_data'][$k][2];
                    $keyName = $args['fields_raw_data'][$k][1];
                } elseif ($args['extra_fields'][$k]) {
                    $caption = $args['extra_fields'][$k][2];
                    $keyName = $args['extra_fields'][$k][1];
                }

                if (is_numeric($value) && floor($value) != $value) {
                    $dataNew['y'] = (float) $value;
                } elseif (is_numeric($value)) {
                    $dataNew['y'] = (int) $value;
                } else {
                    $dataNew['key'] = $value;
                }

                            //$dataNew[str_replace(":","",$keyName)] = $value;
            }
            ++$ci;
            if ($ci > 9) {
                $ci = 0;
            }
            $dataNew['color'] = $colorArray[$ci];
            $rawDataNew[] = $dataNew;
        }

        foreach ($args['extra_fields'] as $key => $value) {
            $value[1] = str_replace(':', '', $value[1]);
            $args['extra_fields'][$key] = $value;
        }

        foreach ($args['fields_raw_data'] as $key => $value) {
            $value[1] = str_replace(':', '', $value[1]);
            $args['fields_raw_data'][$key] = $value;
        }

        $pieVars = array(
                                            'args' => $args,
                                            'rawData' => $rawDataNew,
                                            'rawDataTotal' => $rawDataTotalNew,
                                        );

        if (!$download) {
            return $pieVars;
        }

        $viewModel = new ViewModel();

        $template = 'piechart';

        if ($download) {
            $template = 'piechart-download';
        }

        $viewModel->setTemplate($template)
                       ->setVariables(array(
                                         'widget' => $widget,
                                         'field' => $field,
                                         'args' => $args,
                                         'rawData' => $rawDataNew,
                                         'rawDataTotal' => $rawDataTotalNew,
                                         'insights' => $insights,
                                         'units' => $this->units,
                              ));

        $html = $this->getViewRenderer()
                           ->render($viewModel);

        return $html;
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

    public function getGoal($goal_id, $widget)
    {
        if (!$goal_id) {
            return false;
        }

        $goals = unserialize($widget->getFields())['goals_list'];
        foreach ($goals as $key => $value) {
            if ($goal_id == $value['id']) {
                return $value['title'];
            }
        }

        return false;
    }

    public function getReportRenderer()
    {
        if (!$this->report_renderer) {
            $this->setReportRenderer();
        }

        return $this->report_renderer;
    }

    public function setReportRenderer($report_renderer)
    {
        $this->report_renderer = $report_renderer;

        //$this->report_renderer = $this->getServiceManager()->get();
        return $this;
    }

    public function getMetricsFormatService()
    {
        return $this->getServiceManager()->get('jimmybase_metricsformat_service');
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
