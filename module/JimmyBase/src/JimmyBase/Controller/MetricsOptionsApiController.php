<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Validator\InArray;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Cache\StorageFactory;

use JimmyBase\Service\Client as ClientService;
use JimmyBase\Entity\ClientAccounts;

class MetricsOptionsApiController extends AbstractRestfulController
{

  protected $metrics_service;


    public function getList() {

      $channel                 = $this->params('channel');
      $widget_type             = $this->params('widget_type');
     // $metric_type             = $this->params('metric_type')
      $this->metrics_service   = $this->getServiceLocator()->get('jimmybase_metrics_service');

      $metrics['googleadwords']['kpi']    =  $this->metrics_service->getKPIOptionsLabels();
      $metrics['googleadwords']['table']  =  $this->metrics_service->getRawDataOptions();
      $metrics['googleadwords']['graph']  =  $this->metrics_service->getMetricsOptionsAll();
      $metrics['googleadwords']['piechart']  =  $this->metrics_service->getRawDataOptions();

      $metrics['bingads']['kpi']    =  $this->metrics_service->getBingAdsKPIOptionsLabels();
      $metrics['bingads']['table']  =  $this->metrics_service->getBingAdsKPIOptionsLabels();
      $metrics['bingads']['graph']  =  $this->metrics_service->getBingAdsGraphOptionsAll();

      $metrics['googleanalytics']['kpi']    =  $this->metrics_service->getAnalyticsKPIOptionsLabels();
      $metrics['googleanalytics']['table']  =  $this->metrics_service->getAnalyticsTableMetricsOptions();
      $metrics['googleanalytics']['graph']  =  $this->metrics_service->getAnalyticsGraphMetricsOptions();
      $metrics['googleanalytics']['piechart']  =  $this->metrics_service->getAnalyticsPiechartMetricsOptions();

        //var_dump($metrics);
      
      $device_types  = $this->metrics_service->getDeviceTypeOptions();
    $date_ranges   = $this->metrics_service->getDateRangeOptions();
    $month_ranges   = $this->metrics_service->getMonthRangeOptions();
		$week_ranges   = $this->metrics_service->getWeekRangeOptions();
		$network_types = $this->metrics_service->getAdNetworkTypeOptions();

    	            $metricsChannelList = array();

      foreach ($metrics as $channel =>  $metric_types) { // Channel
        foreach ($metric_types as  $type => $metric) { // Type
            $metricTypeList = array();
            foreach ($metric as $key => $value) {

                if(is_array($value)){
                  $metricSubList = array();

                  foreach ($value as $k => $v) {
                    $metricSubList[] = array('id'=>$k,'title'=>$v);
                  }

                  $metricTypeList[] = $metricSubList;
                } else {
            		  $metricTypeList[] = array('id'=>$key,'title'=>$value);
                }
            }
            $meticsTypeList[$type] = $metricTypeList;
        }

        $metricsChannelList[$channel] = $meticsTypeList;
      }

      foreach ($date_ranges as $key => $val)
        $dateRangeList[] = array('id'=>$key,'title'=>$val);

      foreach ($month_ranges as $key => $val)
        $monthRangeList[] = array('id'=>$key,'title'=>$val);

    	foreach ($week_ranges as $key => $val)
    		$weekRangeList[] = array('id'=>$key,'title'=>$val);

    	foreach ($device_types as $key => $val)
    		$deviceTypeList[] = array('id'=>$key,'title'=>$val);

    	foreach ($network_types as $key => $val)
    		$networkTypesList[] = array('id'=>$key,'title'=>$val);



		$metrics_options['metrics']  	    = $metricsChannelList;
    $metrics_options['date_ranges']   = $dateRangeList;
    $metrics_options['month_ranges']   = $monthRangeList;
		$metrics_options['week_ranges']   = $weekRangeList;
		$metrics_options['device_types']  = $deviceTypeList;
		$metrics_options['network_types'] = $networkTypesList;

    //echo '<pre>';print_r($metrics_options);



     return new JsonModel($metrics_options);
    }

}



