<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class Metrics
{
	private  $metrics_type	  = array();
	private  $metrics_options = array();
	private  $date_range	  = array();
	private  $month_range	  = array();
	private  $week_range	  = array();
	private  $report_type	  = array();
	private  $segment	  = array();
	private  $metrics_format  = array();
	private  $raw_data        = array();
	private  $kpi	          = array();
	private  $analyticsKpi	  = array();
	private  $analytics_graph_metric_type = array();
	private  $analytics_table_metric_type = array();
	private  $analytics_traffic_metrics	  = array();
	private  $ad_network_options = array();
	private  $ad_network_types	 = array();
	private  $device_types 		 = array();
	private  $device_options	 = array();
	private  $bingads_kpi	     = array();
	private  $bingads_graph		 = array();



	public function __construct(){

		# Set the Report Type
		$this->setReportType();

		# Set the Report Type
		$this->setAnalyticsGraphMetricType();

		# Set the Report Type
		$this->setAnalyticsTableMetricType();

		# Set the Date Range
		$this->setDateRange();

		# Set the Month Range
		$this->setMonthRange();

		# Set the Week Range
		$this->setWeekRange();

		# Set the Metrics  Type
		$this->setMetricsType();

		# Set the Metrics
		$this->setMetricsOption();

		# Set the segments
		$this->setSegments();

		# Set the Metrics Format
		$this->setMetricsFormat();

		# Set Raw Data Options
		$this->setRawDataOptions();

		# Set Raw Data Options
		$this->setKPIOptions();

		# Set Raw Data Options
		$this->setAnalyticsKpiOptions();

		# Set Analytics Graph Metrics Options
		$this->setAnalyticsGraphMetricsOptions();

		# Set Analytics Table Metrics Options
		$this->setAnalyticsTableMetricsOptions();
                
                # Set Analytics Table Metrics Options
		$this->setAnalyticsPiechartMetricsOptions();
                
		# Set Adwords Network Type
		$this->setAdNetworkTypes();

		# Set Adwords Network Type Options
		$this->setAdNetworkOptions();

		# Set Device Types
		$this->setDeviceTypes();

		# Set Device Type Options
		$this->setDeviceOptions();

		# Set Raw Data Options
		$this->setBingAdsKPIOptions();

		# Set Bing Ads Graph Data Options
		$this->setBingAdsGraphOptions();

	}


	protected function setReportType(){

		$this->report_type =  array(
								 1 	 => 'Campaign',
								// 2	 => 'Account',
								 3   => 'Ad Group',
								 4   => 'Ad',
								 5   => 'Keyword',
								 6   => 'Search Query',
								 7   => 'Month on Month',
								 8   => 'Week on Week',
								 9	 => 'Channel Acquisitions'
							 );
	}

	public function getReportType($report_type_id){

		if(in_array($report_type_id,array_keys($this->report_type)))
	   		 return $this->report_type[$report_type_id];
	 	else
	    	 return false;

	}


	public function getReportTypeOptions(){

	 return $this->report_type;

	}


	protected function setAdNetworkTypes(){

		$this->ad_network_types =  array(
                                                    1   => 'All',
                                                    2	 => 'Search',
                                                    3   => 'Display'

                                                );
	}

	public function getAdNetworkTypes($network_type_id){

		if(in_array($network_type_id,array_keys($this->ad_network_type)))
	   		 return $this->ad_network_types[$network_type_id];
	 	else
	    	 return false;

	}


	public function getAdNetworkTypeOptions(){

	 return $this->ad_network_types;

	}

	public function setAdNetworkOptions(){
		$this->ad_network_options = array(  1 => null, //All. Leave it null
                                                    2 => array('Search','SEARCH'),
                                                    3 => array('Display','CONTENT'));
	}

	public function getAdNetworkOptions($network_type_option_id){

		if(in_array($network_type_option_id,array_keys($this->ad_network_options)))
	   		 return $this->ad_network_options[$network_type_option_id];
	 	else
	    	 return false;

	}


	protected function setDeviceTypes(){

		$this->device_types =  array(
                                                1   => 'All',
                                                2   => 'Mobile',
                                                3   => 'Tablet',
                                                4   => 'Computer',
                                                5   => 'Other'

                                            );
	}

	public function getDeviceType($device_type_id){

		if(in_array($device_type_id,array_keys($this->device_types)))
	   		 return $this->device_types[$device_type_id];
	 	else
	    	 return false;

	}


	public function getDeviceTypeOptions(){

	 return $this->device_types;

	}


	public function setDeviceOptions(){
		$this->device_options = array(
                                                1 => null, //All. Leave it null
                                                2 => array('Mobile','HIGH_END_MOBILE'),
                                                3 => array('Tablet','TABLET'),
                                                4 => array('Computers','DESKTOP'),
                                                5 => array('Other','UNKNOWN')
                                             );
	}

	public function getDeviceOptions($device_type_option_id) {

		if(in_array($device_type_option_id,array_keys($this->device_options))){
	   		 return $this->device_options[$device_type_option_id];
	 	} else
	    	 return false;

	}

	protected function setMetricsType(){

		$this->metrics_type =  array(
                                                1 => 'Performance',
                                                2 => 'Conversions',
                                                //3 => 'Attributes',
                                                4 => 'Competitive'
                                            );
	}

	public function getMetricsType($metrics_type_id){

		if(in_array($metrics_type_id,array_keys($this->metrics_type))) {
	   		 return $this->metrics_type[$metrics_type_id];
                } else
	    	 return false;

	}


	public function getMetricsTypeOptions(){

	 return $this->metrics_type;

	}



	protected function setMetricsOption(){
 		$this->metrics_options = array(
				1 => array(	//Performance
                                                    1 => 'Clicks',
                                                    2 => 'Impressions',
                                                    3 => 'CTR',
                                                    4 => 'Avg. CPC',
                                                    5 => 'Cost',
                                                    6 => 'Avg. Pos.',
                                                    7 => 'Cost / conv. (many-per-click)',
                                            ),
				2 =>  array(	// Conversions
                                                    1 => 'Converted Clicks',
                                                    2 => 'Cost / Conv. (1-per-click)',
                                                    3 => 'Click conversion rate',
                                                    4 => 'View-through Conv.',
                                                    5 => 'Conv. (many-per-click)',
                                                    6 => 'Cost / conv. (many-per-click)',
                                                    7 => 'Conv. Rate (many-per-click)',
                                                    8 => 'Conversion Value',
                                                    9 => 'Value / conv.click',
                                                    10 =>'Value / conv. (many-per-click)',
                                                    11 =>'Cost',
                                            ),
				/*3 => array(	// Attributes
									 1 => 'Labels'
					 			),*/
				4 => array(	// Competitive
                                                    1 => 'Search Impr. Share',
                                                    2 => 'Search Exact Match IS',
                                                    3 => 'Search Lost IS (rank)',
                                                    4 => 'Search Lost IS (budget)',
                                                   // 5 => 'Display Impr. share',
                                                   // 6 => 'Display Lost IS (rank)',
                                                   // 7 => 'Display Lost IS(budget)',
                                                   // 8 => 'Impr. share',
                                                   // 9 => 'Conv. value / cost',
                                                   // 10 => 'Exact  Match IS',
                                                   // 11 => 'Lost IS (budget)',
                                                    5 => 'Relative CTR',
                                                    6 => 'Impressions'
					 ),



		);


	}


	public function getMetricsOptions($metrics_type){

	 if(in_array($metrics_type,array_keys($this->metrics_options)))
	    return $this->metrics_options[$metrics_type];
	 else
	    return false;

	}

	public function getMetrics($metrics_type,$metrics_option){

		if(in_array($metrics_type,array_keys($this->metrics_options))){

			if(in_array($metrics_option,array_keys($this->metrics_options[$metrics_type])))
				return $this->metrics_options[$metrics_type][$metrics_option];
			else
				return false;

		} else
	    	return false;

	}


	public function getMetricsOptionsAll(){
	    return $this->metrics_options;
	}

	protected function setDateRange(){

			$this->date_range = array(
                                                    1  => 'Today',
                                                    2  => 'Yesterday',
                                                    3  => 'This week (Sun - Today)',
                                                    4  => 'This week (Mon - Today)',
                                                    5  => 'Last 7 days',
                                                    6  => 'Last week (Sun - Sat)',
                                                    7  => 'Last week (Mon - Sun)',
                                                    8  => 'Last business week (Mon - Fri)',
                                                    9  => 'Last 14 days',
                                                    10 => 'This month',
                                                    11 => 'Last 30 days',
                                                    12 => 'Last month',
                                                    13 => 'All time',
                                                    14 => 'Custom'
                                                );
	}

	public function getDateRange($option_id){

		if(in_array($option_id,array_keys($this->date_range)))
	   		 return $this->date_range[$option_id];
	 	else
	    	 return false;

	}

	public function getDateRangeOptions(){

	 	return $this->date_range;

	}
	protected function setMonthRange(){

			$this->month_range = array(
                                                    3  => 'Last 3 Months',
                                                    6  => 'Last 6 Months',
                                                    12  => 'Last 12 Months',
                                               //     14 => 'Custom',
                                                );
	}

	public function getMonthRange($option_id){

		if(in_array($option_id,array_keys($this->month_range)))
	   		 return $this->month_range[$option_id];
	 	else
	    	 return false;

	}

	public function getMonthRangeOptions(){

	 	return $this->month_range;

	}
	protected function setWeekRange(){

			$this->week_range = array(
                                                    4  => 'Last 4 Weeks',
                                                    8  => 'Last 8 Weeks',
                                                    12  => 'Last 12 Weeks',
                                           //         14 => 'Custom',
                                                );
	}

	public function getWeekRange($option_id){

		if(in_array($option_id,array_keys($this->week_range)))
	   		 return $this->week_range[$option_id];
	 	else
	    	 return false;

	}

	public function getWeekRangeOptions(){

	 	return $this->week_range;

	}

	public function setSegments(){

		$this->segments = array(''=>'Select',
                                                    1 => 'Time (Day)',
                                                    2 => 'Time (Week)',
                                                    3 => 'Time (Month)',
                                                    4 => 'Time (Quarter)',
                                                    5 => 'Time (Year)',
                                                    6 => 'Time (Day of the Week)',
                                                    7 => 'Time (Hour of day)',
                                                    8 => 'Conversions (Conversion Action Name)',
                                                    9 =>  'Conversions (Conversion Tracking Purpose)',
                                                    10 => 'Network',
                                                    11 => 'Network with Search Partner',
                                                    12 => 'Click Type',
                                                    13 => 'Device',
                                                    14 => 'Experiment',
                                                    15 => 'Top vs. Other',
                                                    16 => '+1 Annotations'
                                        );


	}




	public function getSegment($segment_id){

	 if(in_array($segment_id,array_keys($this->segments)))
	    return $this->segments[$segment_id];
	 else
	    return false;

	}

	public function getSegmentOptions(){

	 	return $this->segments;

	}

	protected function setMetricsFormat(){

            $this->metrics_format = array(
                                            1 => 'Line',
                                            2 => 'Bar',
                                            3 => 'Pie'
                                         );
	}

	public function getMetricsFormat($metrics_format_id){

	 if(in_array($metrics_format_id,array_keys($this->metrics_format)))
	    return $this->metrics_format[$metrics_format_id];
	 else
	    return false;

	}


	public function getMetricsFormatOptions(){

	 	return $this->metrics_format;

	}

	protected function setRawDataOptions(){

            $this->raw_data   =	array(
                                            1  => 'Clicks',
                                            2  => 'Impressions',
                                            3  => 'CTR',
                                            4  => 'Avg. CPC',
                                            5  => 'Cost',
                                            6  => 'Avg. Pos.',
                                            7  => 'Cost / Converted Click',
                                            8  => 'Converted Clicks',
                                            9  => 'Conversion Value',
                                            10 => 'Click conversion rate',
                                            11 => 'Search Impr. Share',
                                            12 => 'Phone Calls',
                                            13 => 'Phone Impressions',
                                            14 => 'Search Exact Match IS',
                                            15 => 'Search Lost IS (rank)',
                                            16 => 'Search Lost IS (budget)',
                                            17 => 'PTR',
                                            18 => 'Phone Cost',
                                            19 => 'Avg. CPP',
                                            20 => 'Conv. (many-per-click)',
                                            21 => 'Cost / conv. (many-per-click)',
                                            22 => 'Conv. Rate (many-per-click)',
                                            23 => 'Value / conv. (many-per-click)',
                                            24 => 'View-through Conv.',
                                            25 => 'Conv. (many-per-click)'
                                        );

	}


	public function getRawDataOptions(){

		return $this->raw_data;
	}

	public function getRawData($raw_data_id){

		if(in_array($raw_data_id,array_keys($this->raw_data)))
	   		 return $this->raw_data[$raw_data_id];
	 	else
	    	 return false;

	}


	protected function setKPIOptions(){

		$this->kpi   =	array(		 // key => array('Label','Value')
                                        1  => array('Clicks','Clicks'),
                                        2  => array('CTR','CTR'),
                                        3  => array('Converted Clicks','Converted Clicks'),
                                        4  => array('Cost/Converted Click','Cost / Converted Click'),
                                        5  => array('Click Conversion Rate','Click conversion rate'),
                                        6  => array('Impressions','Impressions'),
                                        7  => array('Avg. Pos.','Avg. Pos.'),
                                        8  => array('Avg. CPC','Avg. CPC'),
                                        9  => array('Cost','Cost'),
                                        10 => array('Search Impr. Share','Search Impr. Share'),
                                        11  => array('Conversion Value','Conversion Value'),
                                        12 => array('Conversions', 'Conv. (many-per-click)'),
                                        13 => array('Value / conv', 'Value / conv. (many-per-click)'),
                                        14 => array('View-through Conv.','View-through Conv.'),
                                   //     15 => array('Conversion Rate','Conv. Rate (many-per-click)'),
                                         15 => array('Cost Per Conversion', 'Cost / conv. (many-per-click)'),

                                    );

	}

	public function getKPIOptions(){

		return $this->kpi;
	}



	public function getKPIOptionsLabels(){

		foreach($this->kpi as $key => $kpi){
			$kpiArray[$key] = $kpi[0];
		}

	 return $kpiArray;
	}

	public function getKPI($kpi_id){

		if(in_array($kpi_id,array_keys($this->kpi)))
	   		 return $this->kpi[$kpi_id];
	 	else
	    	 return false;

	}

	public function setAnalyticsKpiOptions(){
            $this->analyticsKpi   =	array(
                                                1  => 'Sessions',
                                                2  => 'Users',
                                                3  => 'Pageviews',
                                                4  => 'Pages/Visit',
                                                5  => 'Avg Duration',
                                                6  => 'Bounce Rate',
                                                7  => '% New Sessions',
                                                8  => 'Goal Completions',
                                                9  => 'Goal Conversion Rate',
                                                10 => 'Revenue',                                                
                                                11 => 'Transactions',
                                                12 => 'Average Order Value',
                                                13 => 'Ecommerce Conversion Rate'
                                            );
	}

	public function getAnalyticsKPIOptionsLabels(){


	 return $this->analyticsKpi;
	}

	public function getAnalyticsKPI($kpi_id){

		if(in_array($kpi_id,array_keys($this->analyticsKpi)))
	   		 return $this->analyticsKpi[$kpi_id];
	 	else
	    	 return false;

	}

	protected function setAnalyticsGraphMetricType(){

		$this->analytics_graph_metric_type =  array(
                                                            1   => 'Traffic',
                                                            2   => 'Goals',
                                                            3   => 'Eccomerce'
                                                        );
	}

	public function getAnalyticsGraphMetricType($metric_type_id){

		if(in_array($metric_type_id,array_keys($this->analytics_graph_metric_type)))
	   		 return $this->analytics_graph_metric_type[$metric_type_id];
	 	else
	    	 return false;

	}


	public function getAnalyticsGraphMetricTypeOptions(){

	 return $this->analytics_graph_metric_type;

	}

	protected function setAnalyticsGraphMetricsOptions(){

		$this->analytics_graph_metrics =  array(
                                                        1 => array(	 // Traffic
                                                                1  => 'Sessions',
                                                                2  => 'Users',
                                                                3  => 'Pageviews',
                                                                4  => 'Pages/Visit',
                                                                5  => 'Avg. Duration',
                                                                6  => 'Bounce Rate',
                                                                7  => '% New Sessions',
                                                                8  => 'Revenue'
                                                        ),
                                                        2 => array( // Goals
                                                                1 => 'Goal Completions',
                                                                2 => 'Goal Value',
                                                                3 => 'Goal Conversion Rate',
                                                                4 => 'Goal Total Abandonment'
                                                        ),
                                                        3 => array(// Ecommerce
                                                                1 => 'Conversion Rate',
                                                                2 => 'Transactions',
                                                                3 => 'Qty',
                                                                4 => 'Revenue',
                                                                5 => 'Avg. Order Value',
                                                                6 => 'Unique Purchase'
                                                        )
                                                    );
	}

	public function getAnalyticsGraphMetricsOptions($graph_metric_type_id=null){

		if($graph_metric_type_id){
			if(in_array($graph_metric_type_id,array_keys($this->analytics_graph_metrics)))
		   		 return $this->analytics_graph_metrics[$graph_metric_type_id];
		 	else
		    	 return false;
		} else {
			return $this->analytics_graph_metrics;
		}
	}


	public function getAnalyticsGraphMetrics($graph_metric_type_id,$metric_option){

		if(in_array($graph_metric_type_id,array_keys($this->analytics_graph_metrics))){
			if(in_array($metric_option,array_keys($this->analytics_graph_metrics[$graph_metric_type_id]))){
				return $this->analytics_graph_metrics[$graph_metric_type_id][$metric_option];
			}
			else
				return false;

		} else
	    	return false;

	}



	protected function setAnalyticsTableMetricType(){

		$this->analytics_table_metric_type =  array(
                                                            1   => 'Source Medium',
                                                            2   => 'Geo',
                                                            3   => 'Site Content',
                                                            4   => 'E-Commerce',
                                                            5   => 'Campaign',
                                                            7   => 'Month on Month',
                                                            8   => 'Week on Week',
                                                            9	=> 'Channel Acquisitions',
                                                            );
	}

	public function getAnalyticsTableMetricType($metric_type_id){

		if(in_array($metric_type_id,array_keys($this->analytics_table_metric_type)))
	   		 return $this->analytics_table_metric_type[$metric_type_id];
	 	else
	    	 return false;

	}


	public function getAnalyticsTableMetricTypeOptions(){

	 return $this->analytics_table_metric_type;
	}



	protected function setAnalyticsTableMetricsOptions(){

		$source_medium_geo = array (
                                            1  => 'Sessions',
                                            2  => '% New Sessions',
                                            3  => 'New Visits',
                                            4  => 'Bounce Rate',
                                            5  => 'Pages/Visit',
                                            6  => 'Avg. Duration',
                                            7  => 'Transactions',
                                            8  => 'Revenue',
                                            9  => 'Ecommerce Conversion Rate',
                                            10  => 'Goal Completions',
                                            11  => 'Goal Conversion Rate',
                                            12  => 'Goal Total Abandonment'
                                        );
                $ecommerce =  array (
                                        1 => 'Product Category',
                                        2 => 'Product SKU',
                                        3 => 'Quantity',
                                        4 => 'Unique Purchases',
                                        5 => 'Product Revenue',
                                        6 => 'Average Price',
                                        7 => 'Average QTY',
                                        8 => 'Goal Total Abandonment'

                                    );

		$this->analytics_table_metrics = array(

                                                        1 => $source_medium_geo,
                                                        2 => $source_medium_geo,
                                                        3 => array ( //Site Content.
                                                                1 => 'Pageviews',
                                                                2 => 'Unique Page Views',
                                                                3 => 'Avg. Time on Page',
                                                                4 => 'Entrances',
                                                                5 => 'Bounce Rate',
                                                                6 => '% Exit',
                                                                7 => 'Page Value',
                                                                8 => 'Goal Completions',
                                                                9 => 'Goal Conversion Rate',
                                                                10 => 'Goal Total Abandonment'
                                                            ),
                                                        4 => $ecommerce,
                                                        5 => $source_medium_geo,
                                                        7 => array(//Month on Month
                                                                    1  => 'Sessions',
					                                                2  => 'Users',
					                                                3  => 'Pageviews',
					                                                4  => 'Pages/Visit',
					                                                5  => 'Avg Duration',
					                                                6  => 'Bounce Rate',
					                                                7  => '% New Sessions',
					                                                8  => 'Goal Completions',
					                                                9  => 'Goal Conversion Rate',
					                                                10 => 'Revenue'
                                                        	),
                                                        8 => array(//Week on Week
                                                                    1  => 'Sessions',
					                                                2  => 'Users',
					                                                3  => 'Pageviews',
					                                                4  => 'Pages/Visit',
					                                                5  => 'Avg Duration',
					                                                6  => 'Bounce Rate',
					                                                7  => '% New Sessions',
					                                                8  => 'Goal Completions',
					                                                9  => 'Goal Conversion Rate',
					                                                10 => 'Revenue'
                                                        	),
                                                        9 => $source_medium_geo,
                                                    );
	}
        
        
        protected function setAnalyticsPiechartMetricsOptions(){

		$source_medium_geo = array (
                                            1  => 'Sessions',
                                            2  => '% New Sessions',
                                            3  => 'New Visits',
                                            4  => 'Bounce Rate',
                                            5  => 'Pages/Visit',
                                            6  => 'Avg. Duration',
                                            7  => 'Transactions',
                                            8  => 'Revenue',
                                            9  => 'Ecommerce Conversion Rate',
                                            10  => 'Goal Completions',
                                            11  => 'Goal Conversion Rate',
                                            12  => 'Goal Total Abandonment'
                                        );
                $ecommerce =  array (                                     
                                        1 => 'Quantity',
                                        2 => 'Unique Purchases',
                                        3 => 'Product Revenue',
                                        4 => 'Average Price',
                                        5 => 'Average QTY',
                                        6 => 'Goal Total Abandonment'

                                    );

		$this->analytics_piechart_metrics = array(

                                                        1 => $source_medium_geo,
                                                        2 => $source_medium_geo,
                                                        3 => array ( //Site Content.
                                                                1 => 'Pageviews',
                                                                2 => 'Unique Page Views',
                                                                3 => 'Avg. Time on Page',
                                                                4 => 'Entrances',
                                                                5 => 'Bounce Rate',
                                                                6 => '% Exit',
                                                                7 => 'Page Value',
                                                                8 => 'Goal Completions',
                                                                9 => 'Goal Conversion Rate',
                                                                10 => 'Goal Total Abandonment'
                                                            ),
                                                        4 => $ecommerce,
                                                        5 =>$source_medium_geo,
                                                        7 =>array(//Month on Month
                                                                                        1  => 'Sessions',
					                                                2  => 'Users',
					                                                3  => 'Pageviews',
					                                                4  => 'Pages/Visit',
					                                                5  => 'Avg Duration',
					                                                6  => 'Bounce Rate',
					                                                7  => '% New Sessions',
					                                                8  => 'Goal Completions',
					                                                9  => 'Goal Conversion Rate',
					                                                10 => 'Revenue'
                                                        	),
                                                        8 =>array(//Week on Week
                                                                                        1  => 'Sessions',
					                                                2  => 'Users',
					                                                3  => 'Pageviews',
					                                                4  => 'Pages/Visit',
					                                                5  => 'Avg Duration',
					                                                6  => 'Bounce Rate',
					                                                7  => '% New Sessions',
					                                                8  => 'Goal Completions',
					                                                9  => 'Goal Conversion Rate',
					                                                10 => 'Revenue'
                                                        	),
                                                    );
	}

	public function getAnalyticsTableMetricsOptions($table_metric_type_id=null){

		if($table_metric_type_id){
			if(in_array($table_metric_type_id,array_keys($this->analytics_table_metrics)))
		   		 return $this->analytics_table_metrics[$table_metric_type_id];
		 	else
		    	 return false;
		} else  {

			return $this->analytics_table_metrics;
		}

	}
        
        public function getAnalyticsPiechartMetricsOptions($piechart_metric_type_id=null){

		if($piechart_metric_type_id){
			if(in_array($piechart_metric_type_id,array_keys($this->analytics_piechart_metrics)))
		   		 return $this->analytics_piechart_metrics[$piechart_metric_type_id];
		 	else
		    	 return false;
		} else  {

			return $this->analytics_piechart_metrics;
		}

	}


	public function getAnalyticsTableMetrics($table_metric_type_id,$metric_option){

		if(in_array($table_metric_type_id,array_keys($this->analytics_table_metrics))){

			if(in_array($metric_option,array_keys($this->analytics_table_metrics[$table_metric_type_id])))
				return $this->analytics_table_metrics[$table_metric_type_id][$metric_option];
			else
				return false;

		} else
	    	return false;

	}
        
        public function getAnalyticsPiechartMetrics($piechart_metric_type_id,$metric_option){

		if(in_array($piechart_metric_type_id,array_keys($this->analytics_piechart_metrics))){

			if(in_array($metric_option,array_keys($this->analytics_piechart_metrics[$piechart_metric_type_id])))
				return $this->analytics_piechart_metrics[$piechart_metric_type_id][$metric_option];
			else
				return false;

		} else
	    	return false;

	}

	protected function setBingAdsKPIOptions(){

		$this->bingads_kpi   =	array(		 // key => array('Label','Value')
									 1  => array('Impressions','Impressions'),
									 2  => array('Clicks ','Clicks'),
									 3  => array('Ctr','Ctr'),
									 4  => array('Avg. Cpc','AverageCpc'),
									 5  => array('Spend','Spend'),
									 6  => array('Average Position','AveragePosition'),
									 7  => array('Conversions','Conversions'),
									 8  => array('Conversion Rate','ConversionRate'),
									 9  => array('CostPerConversion','CostPerConversion')

								);

	}

	public function getBingAdsKPIOptions(){

		return $this->bingads_kpi;
	}



	public function getBingAdsKPIOptionsLabels(){

		foreach($this->bingads_kpi as $key => $kpi){
			$kpiArray[$key] = $kpi[0];
		}

	 return $kpiArray;
	}

	public function getBingAdsKPI($kpi_id){

		if(in_array($kpi_id,array_keys($this->bingads_kpi)))
	   		 return $this->bingads_kpi[$kpi_id];
	 	else
	    	 return false;

	}

	protected function setBingAdsGraphOptions(){

		$this->bingads_graph   =	array(		 // key => array('Label','Value')
								1 => array(
                                                                            1  => 'Impressions',
                                                                            2  => 'Clicks',
                                                                            3  => 'Ctr',
                                                                            4  => 'AverageCpc',
                                                                            5  => 'Spend',
                                                                            6  => 'Average Position',
                                                                        ),
								2 => array(

                                                                            1  => 'Conversions',
                                                                            2  => 'Conversion Rate',
                                                                            3  => 'CostPerConversion'
                                                                        )

								);

	}



	public function getBingAdsMetricsOptions($metrics_type){

	 if(in_array($metrics_type,array_keys($this->bingads_graph)))
	    return $this->bingads_graph[$metrics_type];
	 else
	    return false;

	}

	public function getBingAdsMetrics($metrics_type,$metrics_option){

		if(in_array($metrics_type,array_keys($this->bingads_graph))){

			if(in_array($metrics_option,array_keys($this->bingads_graph[$metrics_type])))
				return $this->bingads_graph[$metrics_type][$metrics_option];
			else
				return false;

		} else
	    	return false;

	}


	public function getBingAdsGraphOptionsAll(){

	 return $this->bingads_graph;
	}


}
?>
