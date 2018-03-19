<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use ZfcBase\EventManager\EventProvider;
use Zend\ServiceManager\ServiceManager;

class AnalyticsInsights extends EventProvider implements ServiceManagerAwareInterface {

    private $insightTypes = array(
        0 => "Audience",
        1 => "Acquisition",
        2 => "Behaviour",
        3 => "Goals",
        4 => "Revenue",
    );
    private $insightOptions = array(
        "Audience" => array("Total Sessions" => "[[total_sessions]]",
            "Total Users" => "[[total_users]]",
            "New Users" => "[[new_users]]",
            "Top Device  user%" => "[[top_device_1_user_percent]]",
            "Top Device" => "[[top_device_1]]",
            "2nd Top Device  user %" => "[[top_device_2_user_percent]]",
            "2nd Top Device" => "[[top_device_2]]",
            "3rd Top Device user%" => "[[top_device_3_user_percent]]",
            "3rd Top Device" => "[[top_device_3]]",
            "Top Browser user %" => "[[top_browser_1_user_percent]]",
            "Top Browser" => "[[top_browser_1]]",
            "2nd Top Browser user%" => "[[top_browser_2_user_percent]]",
            "2nd Top Browser" => "[[top_browser_2]]",
            "3rd Top Browser user%" => "[[top_browser_3_user_percent]]",
            "3rd Top Browser" => "[[top_browser_3]]",
        ),
        "Acquisition" => array(
            "Top Channel" => "[[top_channel_1]]",
            "2nd Top Channel" => "[[top_channel_2]]",
            "3rd Top Channel" => "[[top_channel_3]]",
            "Top Channel sessions" => "[[top_channel_session_1]]",
            "Top Social Channel" => "[[top_social_network_1]]",
            "2nd Top Channel" => "[[top_social_network_2]]",
            "3rd Top Channel" => "[[top_social_network_3]]"
        ),
        "Behaviour" => array(
            "Top visited Page" => "[[top_page_1]]",
            "2nd Top visited Page" => "[[top_page_2]]",
            "3nd Top visited Page" => "[[top_page_3]]",
            "4th Top visited Page" => "[[top_page_4]]",
            "5th Top visited Page" => "[[top_page_5]]",
            "Top Landing Page" => "[[top_landing_page_1]]",
            "2nd Top Landing Page" => "[[top_landing_page_2]]",
            "3rd Top Landing Page" => "[[top_landing_page_3]]",
            "4th Top Landing Page" => "[[top_landing_page_4]]",
            "5th Top Landing Page" => "[[top_landing_page_5]]",
        ),
        "Revenue" => array(
            "Total Transaction Rev." => "[[total_transaction_revenue]]",
            "Total Transactions" => "[[total_transactions]]",
            "Avg. Order Value" => "[avg_order_value]",
            "avg Item Quantity" => "[avg_item_quantity]",
            "Top Product" => "[[top_product_1]]",
            "Top Product Sales" => "[[top_product_sales_1]]",
            "Top Product Rev." => "[top_product_revenue_1]",
            "2nd Top Product" => "[[top_product_2]]",
            "2nd Top Product Sales" => "[top_product_sales_2]",
            "2nd Top Product Rev." => "[top_product_revenue_2]",
            "3rd Top Product" => "[[top_product_3]]",
            "3rd Top Product Sales" => "[top_product_sales_3]",
            "3rd Top Product Rev." => "[top_product_revenue_3]",
        ),
        "Goals" => array(
            "Total Goal Completions" => "[[total_goal_completion]]",
            "Goal 1 Title" => "[[goal_1_title]]",
            "Goal 1 Completion" => "[[goal_1_completion]]",
            "Goal 2 Title" => "[[goal_2_title]]",
            "Goal 2 Completion" => "[[goal_2_completion]]",
            "Goal 3 Title" => "[[goal_3_title]]",
            "Goal 3 Completion" => "[[goal_3_completion]]",
        )
    );

    public function getInsight($args, $widget, $profileId, $dataGa) {
        $insightData = array();
        $metricsInUse = explode(",", $args["metrics"]);


        if (in_array("Audience", $args['insights'])) {
            $insightData = array_merge($insightData, $this->getAudienceInsight($args, $widget, $profileId, $dataGa));
        }
        if (in_array("Acquisition", $args['insights'])) {
            $insightData = array_merge($insightData, $this->getAcquisitionInsight($args, $widget, $profileId, $dataGa));
        }
        if (in_array("Behaviour", $args['insights'])) {
            $insightData = array_merge($insightData, $this->getBehaviourInsight($args, $widget, $profileId, $dataGa));
        }

        if (in_array("Revenue", $args['insights'])) {
            $insightData = array_merge($insightData, $this->getRevenueInsight($args, $widget, $profileId, $dataGa));
        }

        if (in_array("Goals", $args['insights'])) {
            $insightData = array_merge($insightData, $this->getGoalInsight($args, $widget, $profileId, $dataGa));
        }
        $patterns = array();
        $replacements = array();
        $insightText = $widget->getInsight();

        foreach ($insightData as $index => $val) {
            $patterns[] = "/\[\[$index\]\]/";
            $replacements[] = $val;
        }
        $insightText = preg_replace($patterns, $replacements, $insightText);

        return $insightText;
    }

    /**
     * Generates the audience Insight Data.
     * @param array $args
     * @param array $widget
     * @param int $profileId
     * @param array $dataGa
     * @return array
     */
    public function getAudienceInsight($args, $widget, $profileId, $dataGa) {

        $args['metrics'] = "ga:sessions, ga:users,ga:newUsers";
        $args['optParams']['dimensions'] = "ga:deviceCategory, ga:browser";
        $args['optParams']['sort'] = null;
        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $totals = $result->totalsForAllResults;
        $deviceGroup = array();
        $browserGroup = array();
        foreach ($result->rows as $i => $r) {
            $deviceGroup[$r[0]]+=intval($r[3]);
            $browserGroup[$r[1]]+=intval($r[3]);
        }
        $i = 0;

        foreach ($deviceGroup as $device => $dg) {
            $userPercent = round(intval($dg) * 100 / $totals['ga:users'], 2);
            $insightData["top_device_" . ($i + 1)] = $device;
            $insightData["top_device_" . ($i + 1) . "_user_percent"] = $userPercent;
            $i++;
        }

        $j = 0;
        arsort($browserGroup);
        array_splice($browserGroup, 3);
        foreach ($browserGroup as $browser => $bg) {
            $userPercent = round(intval($bg) * 100 / $totals['ga:users'], 2);
            $insightData["top_browser_" . ($j + 1)] = $browser;
            $insightData["top_browser_" . ($j + 1) . "_user_percent"] = $userPercent;
            $j++;
        }

        $retUsers = intval($totals['ga:users']) - intval($totals['ga:newUsers']);
        $insightData["total_sessions"] = $totals['ga:sessions'];
        $insightData["total_users"] = $totals['ga:users'];
        $insightData["new_users"] = $totals['ga:newUsers'];
        $insightData["returning_users"] = $retUsers;
        return $insightData;
    }

    public function getAcquisitionInsight($args, $widget, $profileId, $dataGa) {
        $args['metrics'] = "ga:sessions";
        $insightData = array();
        $args['optParams']['dimensions'] = "ga:channelGrouping";
        $args['optParams']['sort'] = "-ga:sessions";
        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $count = 0;
        foreach ($result->rows as $i => $r) {
            $count++;
            $insightData["top_channel_" . $count] = $r[0];
            $insightData["top_channel_session_" . $count] = $r[1];
        }

        //get the social network grouping.
        // have to query seperately as it cant be queried with channelGrouping"

        $args['optParams']['dimensions'] = "ga:socialNetwork";
        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $count = 0;
        foreach ($result->rows as $i => $r) {
            if ($r[0] !== "(not set)") {
                $count++;
                $insightData["top_social_network_" . $count] = $r[0];
                $insightData["top_social_network_session_" . $count] = $r[1];
            }
        }

        return $insightData;
    }

    public function getBehaviourInsight($args, $widget, $profileId, $dataGa) {

        $args['metrics'] = "ga:pageviews";
        $insightData = array();
        $args['optParams']['dimensions'] = "ga:hostname, ga:pagePath";
        $args['optParams']['sort'] = "-ga:pageviews";
        $args['optParams']['max-results'] = 5;
        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $count = 0;
        foreach ($result->rows as $i => $r) {
            $count++;
            $insightData["top_page_" . $count] = $r[0] . $r[1];
            $insightData["top_pageviews_" . $count] = $r[2];
        }
        //to get the landing page path we need to change the dimension 
        $args['optParams']['dimensions'] = "ga:hostname, ga:landingPagePath";
        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $count = 0;
        foreach ($result->rows as $i => $r) {
            $count++;
            $insightData["top_landing_page_" . $count] = $r[0] . $r[1];
            $insightData["top_landing_pageviews_" . $count] = $r[2];
        }
        return $insightData;
    }

    public function getRevenueInsight($args, $widget, $profileId, $dataGa) {
        $insightData = array();
        $args['metrics'] = "ga:transactionRevenue, "
                . "ga:transactions,"
                . "ga:revenuePerTransaction,"
                . "ga:itemsPerPurchase";


        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $totals = $result->totalsForAllResults;

        $insightData["total_transaction_revenue"] = round($totals['ga:transactionRevenue'], 2);
        $insightData["total_transactions"] = round($totals['ga:transactions'], 2);
        $insightData["avg_order_value"] = round($totals['ga:revenuePerTransaction'], 2);
        $insightData["avg_item_quantity"] = round($totals['ga:itemsPerPurchase'], 2);

        $args['metrics'] = "ga:itemRevenue,ga:itemQuantity";
        $args['optParams']['dimensions'] = "ga:productName";
        $args['optParams']['sort'] = "-ga:itemRevenue";
        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $args['optParams']['max-results'] = 5;

        $rows = $result->rows;
        $count = 0;
        foreach ($rows as $i => $r) {
            $count++;
            $insightData['top_product_' . $count] = $r[0];
            $insightData['top_product_revenue_' . $count] = $r[1];
            $insightData['top_product_sales_' . $count] = $r[2];
        }


        return $insightData;
    }

    public function getGoalInsight($args, $widget, $profileId, $dataGa) {
        $fields = unserialize($widget->getFields());
        $insightData = array();
        $widgetType = $widget->getType();
        if ($widgetType == "kpi" || $widgetType == "table" || $widgetType == "piechart") {
            $goals = $fields['goals'];
        } else if ($widgetType == "graph") {
            $goals = array($fields["goals"]);
        }
        //If mp goals are selected.
        if (empty($goals)) {
            return null;
        }
        $analyticsService = $this->getServiceManager()->get('jimmybase_analytics_service');
        $goalTitles = array();
        $goalMetrics = array();
        foreach ($goals as $g) {
            $goalTitles[] = $analyticsService->getGoal($g, $widget);
            $goalMetrics[] = 'ga:goal' . $g . 'Completions';
        }
        $args['metrics'] = implode(",", $goalMetrics);
        $result = $dataGa->setProfileId($profileId)
                ->fetchData($args, $widget);
        $totals = $result->totalsForAllResults;
        $i = 1;

        foreach ($totals as $t) {
            $insightData['total_goal_completion'] += $t;
            $insightData['goal_' . $i . '_completion'] = $t;
            $insightData['goal_' . $i . '_title'] = $goalTitles[$i - 1];
            $i++;
        }

        return $insightData;
    }

    /**
     * 
     * @param string $insightName
     * @return string
     */
    public function getDefaultInsightBlurb($insightName) {
        switch ($insightName) {
            case "Audience" :
                $blurb = "<h3>Audience</h3>"
                        . "<p>There were [[total_sessions]] sessions from a total of "
                        . "[[total_users]] users. </p>"
                        . "<p> [[new_users]] "
                        . "of these users were new users "
                        . "while [[returning_users]] were returning.</p>"
                        . "[[top_device_1_user_percent]]% of users came from [[top_device_1]], "
                        . "[[top_device_2_user_percent]]%  from [[top_device_2]] "
                        . " with the other [[top_device_3_user_percent]]% of site traffic coming from [[top_device_3]].</p> "
                        . "<p>Browser preference is skewed towards [[top_browser_1]] with [[top_browser_1_user_percent]]%, following by "
                        . "[[top_browser_2]] with [[top_browser_2_user_percent]]% and then [[top_browser_3]] with [[top_browser_3_user_percent]]%.</p>"
                ;
                break;
            case "Acquisition":
                $blurb = "<h3>Acquisition</h3>
                           <p>[[top_channel_session_1]] sessions came from [[top_channel_1]] which was the
                           highest acquisition channel. This was followed by [[top_channel_2]] and [[top_channel_3]]
                           .</p>
                           <p>[[top_social_network_1]] was the social channel that drove the most traffic during the
                           period, followed by [[top_social_network_2]] and [[top_social_network_3]]</p>
                           ";
                break;
            case "Goals" :
                $blurb = "<h3>Goals</h3>"
                        . "Total completions during the period was [[total_goal_completion]]. The breakdown of which
                       is as follows:<br/>
                      <ul>
                       <li>[[goal_1_title]]: [[goal_1_completion]] completions</li>
                       <li>[[goal_2_title]]: [[goal_2_completion]] completions</li>
                       <li>[[goal_3_title]]: [[goal_3_completion]] completions</li>
                       </ul>";
                break;

            case "Behaviour":
                $blurb = "<h3>Behaviour</h3>"
                        . "<p>The highest visited page was [[top_page_1]].</p>
                            <br/>
                            <p>The Top 5 visited pages were:</p>
                            <br/>
                            <ul>
                                <li>[[top_page_1]]</li>
                                <li>[[top_page_2]]</li>
                                <li>[[top_page_3]]</li>
                                <li>[[top_page_4]]</li>
                                <li>[[top_page_5]]</li>
                            </ul>
                            <br/>
                            <p> From a landing page point of view these were
                            The Top 5 pages:</p><br/>
                            <ul>
                                <li>[[top_landing_page_1]]</li>
                                <li>[[top_landing_page_2]]</li>
                                <li>[[top_landing_page_3]]</li>
                                <li>[[top_landing_page_4]]</li>
                                <li>[[top_landing_page_5]]</li>
                            </ul>
                          ";
                break;
            case "Revenue" :
                $blurb = "<h3>Revenue</h3>
                            <p>Total revenue during the period totalled [[total_transaction_revenue]] from
                            [[total_transactions]] transactions. The average order 
                            value was [[avg_order_value]] with the average order quantity being 
                            [[avg_item_quantity]].</p>
                            <br/>                            
                            <p>
                                [[top_product_1]] was the Top selling product with 
                                [[top_product_sales_1]] sales and a total 
                                of [[top_product_revenue_1]] revenue. This was followed by 
                                [[top_product_2]] with [[top_product_sales_2]] sales and 
                                [[top_product_revenue_2]] revenue and then [[top_product_3]]
                                with [[top_product_sales_3]] sales and [[top_product_revenue_3]] revenue.
                            </p>"
                        . "";
                break;
        }

        return $blurb;
    }

    /**
     * Find all the analytics Insignt types.
     * 
     * @return array()
     */
    public function getInsightType() {
        return $this->insightTypes;
    }

    /**
     * Find all the analytics Insignt items.
     * 
     * @return array()
     */
    public function getInsightOptions($selectedTypes) {
        $selectedList = array();
        foreach ($this->insightOptions as $i => $item) {
            if (in_array($i, $selectedTypes)) {
                $selectedList[$i] = $item;
            }
        }
        return $selectedList;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager() {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        return $this;
    }

}
