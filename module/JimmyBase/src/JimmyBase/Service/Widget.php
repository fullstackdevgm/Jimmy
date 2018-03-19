<?php

namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;


use ZfcBase\EventManager\EventProvider;
use JimmyBase\Mapper\WidgetInterface as WidgettMapperInterface;
use JimmyBase\Entity\ClientAccounts;

class Widget extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var UserMapperInterface
     */
    protected $widgetMapper;


    /**
     * @var Form
     */
    protected $widgetForm;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;


    public function save(array $data)
    {
    	if($data['id']){
    		$widget = $this->getMapper()->findById($data['id']);
    	} else {
    		$widget = new  \JimmyBase\Entity\Widget();
        }

		if($data['type']!='notes') {

				$widget->setClientAccountId($data['client_account_id']);
                      
				if ($data['channel']  ==  'googleadwords') {
					$fields['campaigns']    = $data['campaigns'];
					$fields['device_type']  = $data['device_type'];
					$fields['network_type'] = $data['network_type'];
                                        
				} else if($data['channel'] =='googleanalytics') {
					$fields['profile_id']  = $data['profile_id'];
					$fields['goals']	   = $data['goals'];
                                        $fields['goals_list']  = $data['goals_list'];
                                        $fields['currency']    = $data['currency'];
                                        $fields['segment']     = $data['segment'];

					if(count($data['metrics'])+count($data['goals'])>10) {
						throw new \Exception("You can select upto only 10 metrics. Selecting each goal is counted as individual metrics");
					}
				} else if($data['channel']=='bingads'){
                                    $fields['campaigns']    = $data['campaigns'];
        }
        if ($data['channel']  ==  'googleadwords') {
          if(!in_array($data['report_type'], array(7,8))){
						$fields['date_range']   = $data['date_range'];
						if($fields['date_range']==14) {
							$fields['date_range_custom_min'] = $data['date_range_custom_min'];
							$fields['date_range_custom_max'] = $data['date_range_custom_max'];
						}
          } else if($data['report_type']==7){ //ID 7 is Month on Month
						$fields['period']   = $data['month_range'];
						$fields['date_range'] = 14; // Just set as custom date and finding dates from that
						if($fields['period']==14) {
							$fields['date_range_custom_min'] = $data['date_range_custom_min'];
							$fields['date_range_custom_max'] = $data['date_range_custom_max'];
						}
          } elseif ($data['report_type']==8){ //ID 8 is Week on Week
						$fields['period']   = $data['week_range'];
						$fields['date_range'] = 14; // Just set as custom date and finding dates from that
						if($fields['period']==14) {
							$fields['date_range_custom_min'] = $data['date_range_custom_min'];
							$fields['date_range_custom_max'] = $data['date_range_custom_max'];
						}
					}
				} else if($data['channel'] =='googleanalytics') {
					if(!in_array($data['report_type'], array(7,8))){
						$fields['date_range']   = $data['date_range'];
						if($fields['date_range']==14) {
							$fields['date_range_custom_min'] = $data['date_range_custom_min'];
							$fields['date_range_custom_max'] = $data['date_range_custom_max'];
						}
	                }elseif($data['metrics_type']==7){ //ID 7 is Month on Month
						$fields['period']   = $data['month_range'];
						$fields['date_range'] = 14; // Just set as custom date and finding dates from that
						if($fields['period']==14) {
							$fields['date_range_custom_min'] = $data['date_range_custom_min'];
							$fields['date_range_custom_max'] = $data['date_range_custom_max'];
						}
					} elseif ($data['metrics_type']==8){ //ID 8 is Week on Week
						$fields['period']   = $data['week_range'];
						$fields['date_range'] = 14; // Just set as custom date and finding dates from that
						if($fields['period']==14) {
							$fields['date_range_custom_min'] = $data['date_range_custom_min'];
							$fields['date_range_custom_max'] = $data['date_range_custom_max'];
						}
					}
				}


                if ($data['filter']) {
                    $fields['filter'] = $data["filter"];
                }


				if($data['metrics_type'])
					$fields['metrics_type'] = $data['metrics_type'];

				if($data['report_type'])
					$fields['report_type'] = $data['report_type'];



				switch ($data['type']) {
					case 'kpi':
                        $fields['kpi']      = $data['metrics'];

                        foreach ($fields['kpi'] as $key => $value) {
                            if(!isset($data['kpi_type'][$key]))
                                $fields['kpi_type'][$key] = 1;
                            else
                                $fields['kpi_type'][$key] = $data['kpi_type'][$key];
                        }


						$fields['compare_dates']        = $data['compare_dates'];

						if($data['compare_dates']) {
							$fields['date_range_compare']   = $data['date_range_compare'];
							if($data['date_range_compare']=='custom'){
								$fields['date_range_custom_min_compare'] = $data['date_range_custom_min_compare'];
								$fields['date_range_custom_max_compare'] = $data['date_range_custom_max_compare'];
							}
						}

						break;

					case 'graph':
						$fields['metrics'] 		= $data['metrics'];
						$fields['graph_type'] 	= $data['graph_type'];

						if($data['compare'] && $data['metrics_compare']){
							$fields['compare']				= $data['compare'];
							$fields['metrics_compare'] 		= $data['metrics_compare'];
							$fields['metrics_type_compare'] = $data['metrics_type_compare'];
						}

						if($data['goals_compare'])
						   $fields['goals_compare'] = $data['goals_compare'];



						break;
					case 'table':
						$fields['raw_data']      		=  $data['metrics'];
						$fields['show_top']      		=  $data['show_top'];
						$fields['sort_by']       		=  $data['sort_by'];
						if ($data['channel']  ==  'googleadwords') {
							if(!in_array($data['report_type'], array(7,8))){
								$fields['show_campaign'] 		=  $data['show_campaign'];
							}elseif($data['report_type']==7){ //ID 7 is Month on Month
								$fields['show_current_period'] 	=  $data['show_current_period'];
							} elseif ($data['report_type']==8){ //ID 8 is Week on Week
								$fields['show_current_period'] 	=  $data['show_current_period'];
							}
						}else if($data['channel'] =='googleanalytics') {
							if($data['metrics_type']==7){ //ID 7 is Month on Month
								$fields['show_current_period'] 	=  $data['show_current_period'];
							} elseif ($data['metrics_type']==8){ //ID 8 is Week on Week
								$fields['show_current_period'] 	=  $data['show_current_period'];
							}
						}

						break;
					case 'piechart':
						$fields['raw_data']      =  $data['metrics'];
						$fields['show_campaign'] =  $data['show_campaign'];
						$fields['show_top']      =  $data['show_top'];
						$fields['sort_by']       =  $data['sort_by'];
					break;
				}
		} else {
			$fields['notes'] = $data['notes'];
			$widget->setClientAccountId(null);
		}

                if ($data['insights']) { 
                    if (in_array("Goals", $data['insights']) && empty($data['goals'])) {
                        throw new \Exception($message = "Please select goals to use the goals Insight");
                    }
                    $fields['insights'] = $data['insights'];
                    
                    //When creating the widget. The insights are empty.
                    if (!$data['id']) {  
                        $insightItems = $data['insights'];
                                               
                    } else { 
                        //When updating the widget, the widget may have already existing Insights.
                           $fieldsTemp = unserialize($widget->getFields());
                           $insightItems = array_diff($data['insights'], $fieldsTemp['insights']);   
                           $insightBlurb = $widget->getInsight();
                        
                    }
                   
                    foreach ($insightItems as $insightItem) {
                            $insightBlurb .= $this->getAnalyticsInsightService()
                                                  ->getDefaultInsightblurb($insightItem);
                        } 
                    $widget->setInsight($insightBlurb);
                }     
                        

		$data['fields'] = $fields;
               

		$widget->setReportId($data['report_id']);
		$widget->setTitle($data['title']);
		$widget->setType($data['type']);
		$widget->setStatus(1);
		$widget->setFields(serialize($data['fields']));
		$widget->setComments($data['comments']);
		$now = date('Y-m-d h:i:s');

		if(!$widget->getId()) {
		    $widget->setOrder(1);
                    $widget->setCreated($now);
                    $widget->setUpdated($now);
		} else {
			//$widget->setOrder(null);
			//$widget->setCreated(null);
			$widget->setUpdated($now);
		}

		//$widget->setFields(serialize($data['fields']));

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('widget' => $widget, 'form' => $form));

		if(!$widget->getId())
        	$this->getMapper()->insert($widget);
		else
		    $this->getMapper()->update($widget);

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('widget' => $widget, 'form' => $form));

        return $widget;
    }
    
    
    
    


    public function wizardSave($widget_data)
    {
    	extract($widget_data);

		$client_accounts_mapper = $this->getServiceManager()->get('jimmybase_clientaccounts_mapper');

		$client_account = $client_accounts_mapper->findById($client_account_id);
	    $source 		= $client_account->getChannel();

        $widget  = new \JimmyBase\Entity\Widget();
		$data = array();

		$widget->setTitle(ucfirst($widget_type));

		if($widget_type!='notes')
			$widget->setClientAccountId($client_account_id);

		$widget->setReportId($report_id);
		$widget->setComments("Comments Here");
		$widget->setType($widget_type);


		$channel = array();


		$channel['fields_kpi']	 		 = array();

		$channel['fields_date_range']    = 11;

		if($source==ClientAccounts::GOOGLE_ADWORDS){
			$channel['fields_campaigns']	 = 'all';
			$channel['fields_network_type']  = 1;
			$channel['fields_device_type'][] = 1;
		} else if($source==ClientAccounts::GOOGLE_ANALYTICS){
		   $channel['fields_profile_id']	 = $web_property_id;
		}


		switch($widget_type){

			case 'kpi':

				if($source==ClientAccounts::GOOGLE_ADWORDS){

					if($type == 'performance'){
					  $channel['fields_kpi']  = array(1,2,7,8,9);   // Clicks, Ctr, Avg. Pos, Avg. CPC and Cost
					} else if($type == 'conversion'){
					  $channel['fields_kpi']  = array(1,4,5,6,9,10); // Clicks, CPA, Conv. Rate, Impressions, Search Imp Share, Cost
					}

				} else if($source==ClientAccounts::GOOGLE_ANALYTICS) {

					if($type=='traffic'){
					  $channel['fields_kpi'] = array(1,2,3,4,5,6,7);   // Visits, Users, Pageviews, Pages/Views, Avg. Duration, Bounce Rate , % New Sessions
					} else if($type == 'source'){
					  $channel['fields_kpi'] = array(1,2,3,4,5,6,7); // Visits, Users, Pageviews, Pages/Views, Avg. Duration, Bounce Rate , % New Sessions, Transactions, Ecom Conv. Rate
					}
				}


			break;
			case 'graph':

				if($source==ClientAccounts::GOOGLE_ADWORDS){

					if($type=='performance'){
					   $channel['fields_metrics_type']  = 1;   // Performance
					   $channel['fields_metrics']  = 1 ;      //  Clicks
					} else if($type == 'conversion'){
 					   $channel['fields_metrics_type']  = 2 ;  // Performance
					   $channel['fields_metrics']  = 1 ;      //  Clicks
					}

				} else if($source==ClientAccounts::GOOGLE_ANALYTICS){
				   	$channel['fields_metrics_type']  = 1;   // Source
					$channel['fields_metrics']  = 1 ;      //  Visits
				}


			break;
			case 'table':

				if($source==ClientAccounts::GOOGLE_ADWORDS){
					  $channel['fields_report_type']  = 1;   // Performance
					if($type=='performance'){
					   $channel['fields_raw_data']  =  array(1,3,6,4,5);  ;  // Clicks, Ctr, Avg. Pos, Avg. CPC and Cost

                                        } else if($type == 'conversion'){
					   $channel['fields_raw_data']  =  array(1,2,10,11,5);  ;  //Clicks, Impressions, Conv. Rate, Search Imp Share, Cost
					}

				} else if($source==ClientAccounts::GOOGLE_ANALYTICS){

					if($type=='traffic'){
					  $channel['fields_metrics_type']	= 1;
					  $channel['fields_raw_data']       = array(1,2,5,6,4);   //  Visits, % New Sessions, Pages/Views, Avg. Duration, Bounce Rate
					} else if($type == 'source'){
					  $channel['fields_metrics_type']	= 1;
					  $channel['fields_raw_data']       = array(1,2,5,6,4,7,9); // Visits, % New Sessions,  Pages/Views, Avg. Duration, Bounce Rate ,  Transactions, Ecom Conv. Rate
					}
				}

			break;
			case 'piechart':

				if($source==ClientAccounts::GOOGLE_ADWORDS){
					  $channel['fields_report_type']  = 1;   // Performance
					if($type=='performance'){
					   $channel['fields_raw_data']  =  array(1,3,6,4,5);  ;  // Clicks, Ctr, Avg. Pos, Avg. CPC and Cost

                                        } else if($type == 'conversion'){
					   $channel['fields_raw_data']  =  array(1,2,10,11,5);  ;  //Clicks, Impressions, Conv. Rate, Search Imp Share, Cost
					}

				} else if($source==ClientAccounts::GOOGLE_ANALYTICS){

					if($type=='traffic'){
					  $channel['fields_metrics_type']	= 1;
					  $channel['fields_raw_data']       = array(1,2,5,6,4);   //  Visits, % New Sessions, Pages/Views, Avg. Duration, Bounce Rate
					} else if($type == 'source'){
					  $channel['fields_metrics_type']	= 1;
					  $channel['fields_raw_data']       = array(1,2,5,6,4,7,9); // Visits, % New Sessions,  Pages/Views, Avg. Duration, Bounce Rate ,  Transactions, Ecom Conv. Rate
					}
				}

			break;
			case 'notes':
				$data['notes'] = 'Dummy Data';

			break;

		}

		if($widget_type!='notes')
			$data['channel'] = $channel;



		if($widget_type!='notes'){
			if($data['channel']){
			  foreach($data['channel'] as $key => $el){
				 	list($prefix,$name)  = explode('fields_',$key);
					if($name)
					   $fields[$name]  = $el;
			  }
			}
		} else {
			$fields['notes'] = $data['notes'];
		}



		$data['fields'] = $fields;

        unset($data['channel']);

		$widget->setStatus(1);

		$now = date('Y-m-d h:i:s');

		if($data['type']=='notes'){
			$widget->setClientAccountId(null);
		}

		if(!$widget->getId()){
		    $widget->setOrder(1);
        	$widget->setCreated($now);
			$widget->setUpdated($now);
		} else {
			$widget->setOrder(null);
			$widget->setCreated(null);
			$widget->setUpdated($now);
		}

		$widget->setFields(serialize($data['fields']));

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('widget' => $widget, 'form' => $form));


		if(!$widget->getId())
        	$this->getMapper()->insert($widget);
		else
		    $this->getMapper()->update($widget);

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('widget' => $widget, 'form' => $form));

        return $widget;
    }




	public function copy($widget)
    {


		$widget->setId(null);
		$widget->setStatus(1);
		$now = date('Y-m-d h:i:s');

		$widget->setCreated($now);
		$widget->setUpdated($now);



        $this->getEventManager()->trigger(__FUNCTION__, $this, array('widget' => $widget));

		if(!$widget->getId())
        	$this->getMapper()->insert($widget);
		else
		    $this->getMapper()->update($widget);

        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('widget' => $widget));

        return $widget;

    }


	public function sortUpdate($report_id, $widget_order)
    {
        $widget  = new \JimmyBase\Entity\Widget();
		$widgets  = $this->getMapper()->findByReportId($report_id);

		$flipped_widget_order = array_flip($widget_order);
		if($widgets){
			foreach($widgets as $widget){
				$now = date('Y-m-d h:i:s');
				$widget->setOrder($flipped_widget_order[$widget->getId()]);
				$widget->setUpdated($now);
				$widget->setStatus(1);
				$this->getMapper()
					 ->update($widget);
			}
		}


        return true;
    }


    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getMapper()
    {
        if (null === $this->widgetMapper) {
            $this->widgetMapper = $this->getServiceManager()->get('jimmybase_widget_mapper');
        }
        return $this->widgetMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setMapper(ReportMapperInterface $widgetMapper)
    {
        $this->widgetMapper = $widgetMapper;
        return $this;
    }



    public function getForm()
    {
        if (!$this->widgetForm) {
              $this->setForm($this->getServiceManager()->get('jimmybase_widget_form'));
			// $this->setForm($this->getServiceManager()->get('FormElementManager')->get('JimmyBase\Form\Widget'));
        }
        return $this->widgetForm;
    }

    public function setForm(Form $widgetForm)
    {
        $this->widgetForm = $widgetForm;
    }
    
    public function getAnalyticsInsightService() {
        return $this->getServiceManager()->get('jimmybase_analytics_insights_service');
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
    public function _parseWidgetDateRange($periodFlag,$period,$includeCurrentDay,$channel){
    	$min = nulll;
    	$max = null;
    	switch ($periodFlag) {
    		case 'Month':
    				$min   =  strtotime ('-'.$period.'months', strtotime('first day of this month'));
    				if($includeCurrentDay)
    				{
    				$max   = strtotime ( 'today' );
    				} else {
    				$max   = strtotime ( 'last day of last month' );
    				}
    			break;
    		case 'Week':
    			$stringmin="";
    			$stringmax="";
    			if($channel=='googleadwords') {
    				$stringmin=strtotime('last week sunday');
    				$stringmin = strtotime($stringmin.' -6 days');
    				$stringmin = $tringmin .' - ' . ($period).' week';
    				$stringmax='last week sunday';
    			} else if ($channel=="googleanalytics")
    			{
    				$stringmin=strtotime('last week saturday');
    				$stringmin = strtotime($stringmin.' -6 days');
    				$stringmin = $tringmin .' - ' . ($period).' week';
    				$stringmax='last week saturday';

    			}
    				$min   =  strtotime ($stringmin);
    				if($includeCurrentDay)
    				{
    				$max   = strtotime ( 'today' );
    				} else {
    				$max   = strtotime ( $stringmax );
    				}
    			break;
    	}
    	return array('min'=>$min,'max'=>$max);
	 }
}
