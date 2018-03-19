<?php
namespace JimmyBase\Service;

use Zend\View\Helper\AbstractHelper;

class MetricsFormat extends AbstractHelper
{

    private $units    = array(    'clicks'                =>    array('format'=>'%s',    'decimal'=>0,'formula'=> null),

                                'impressions'            =>    array('format'=>'%s',    'decimal'=>0,'formula'=> null),
                                'convertedClicks'        =>    array('format'=>'%s',    'decimal'=>0,'formula'=> null),
                                'ctr'                    =>  array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'avgCPC'                =>  array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> '%s/1000000'),
                                'costConv'              => array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> '%s/1000000'),
                                'cost'                    =>  array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> '%s/1000000'),
                                'costConvertedClick'    =>    array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> '%s/1000000'),
                                'clickConversionRate'    =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'convRate'                =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'avgPosition'            =>    array('format'=>'%s',    'decimal'=>1,'formula'=> null),
                                'searchImprShare'        =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'searchLostISBudget'        =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                 'searchLostISRank'        =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'searchExactMatchIS'        =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:visitBounceRate'    =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:pageviewsPerVisit'    =>    array('format'=>'%s',    'decimal'=>2,'formula'=> null),
                                'ga:percentNewVisits'    =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:avgTimeOnSite'        =>    array('format'=>'%s',    'decimal'=>2,'formula'=> null),
                                'ga:entrances'            =>    array('format'=>'%s',    'decimal'=>0,'formula'=> null),
                                'ga:exitRate'            =>    array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:pageValue'            =>    array('format'=>'%s',    'decimal'=>2,'formula'=> null),
                                'ga:transactionsPerVisit' => array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:transactionsPerSession' => array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:transactionRevenue'   => array('format'=>'%s  <span style="font-size:10px">%s</span>',    'decimal'=>2,'formula'=> null),
                                'ga:goalConversionRate'  => array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:revenuePerItem' => array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> '%s/1000000'),
                                'ga:itemRevenue' => array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> '%s/1000000'),
                                'ga:itemsPerPurchase' => array('format'=>'%s',    'decimal'=>2,'formula'=> null),
                                'ga:percentNewSessions' => array('format'=>'%s%',    'decimal'=>2,'formula'=> null),
                                'ga:avgTimeOnPage' => array('format'=>'%s',    'decimal'=>2,'formula'=> null),
                                'ga:costPerConversion' => array('format'=>'%s', 'decimal'=>2, 'formula' => null),
                                //'ga:goalCompletions'    =>  array('format'=>'%s',	'decimal'=>0,'formula'=> null),
                                'ga:goalConversionRate' =>  array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),
                                'ga:revenuePerTransaction' => array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> null),
                                'Ctr'                    =>  array('format'=>'%s%%',    'decimal'=>2,'formula'=> null),//Bing
                                'AverageCpc'            =>  array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> null),//Bing
                                'AveragePosition'        =>    array('format'=>'%s','decimal'=>2,'formula'=> null),//Bing
                                'Spend'                =>  array('format'=>'%s <span style="font-size:10px">%s</span>','decimal'=>2,'formula'=> null),
                                
                          );
    private $currency = array('AUD'=>'A$','USD'=>'$','$'=>'$');

    /*
     *  Decimal number key
     */
    const DECIMAL = 'decimal';

    /*
     *  Format key
     */
    const FORMAT = 'format';

    /*
     *  Formula
     */
    const FORMULA = 'formula';


    /**
     * __invoke
     *
     * @access public
     * @return String
     */

     public function calculateMetrics($metrics_name, $value, $currency = null)
    {
        $val = $value;
        if ($currency) {
            $currency = $this->translateCurrency($currency);
        }
        # If the key doesnot exist in array return the passed value
        if (!in_array($metrics_name, array_keys($this->units))) {
            return $val;
        }
       
        # Format Formula
        if ($formula = $this->getMetricsFormula($metrics_name)) {
            eval('$evVal ='. sprintf($formula, $value, $currency). ";");
            $val =  round($evVal, $this->getMetricsDecimal($metrics_name));
        }
         
        $val =  number_format($val, $this->getMetricsDecimal($metrics_name), '.', '');
        
        $ret =  $this->sprintf($metrics_name, $val, $currency);
        
        return $ret;
    }


    public function translateCurrency($currency)
    {
        if ($translatedCurrency = $this->currency[$currency]) {
            return $translatedCurrency;
        } else {
            return $currency;
        }
    }

    public function formatNumber($metrics_name, $value, $currency=null)
    {
        if ($currency) {
            $currency = $this->translateCurrency($currency);
        }

        if ($decimal = $this->getMetricsDecimal($metrics_name)) {
            $value   = number_format($value, $decimal);
        }

        return $this->sprintf($metrics_name, $value, $currency);
    }


    public function formatNumberWithoutSprintf($metrics_name, $value, $currency=null)
    {
        if ($decimal = $this->getMetricsDecimal($metrics_name)) {
            return  number_format($value, $decimal);
        }
        return $value;
    }


    public function formatTime($metrics_name, $value)
    {
        $val =   gmdate("H:i:s", $value);
        return $this->sprintf($metrics_name, $val);
    }


    public function formatText($field, &$val, $formatField)
    {
        $method = '_'.$field.'Format';

        if (method_exists($this, $method)) {
            return $this->$method($field, $val, $formatField);
        } else {
            return $val[$field];
        }
    }


    private function _adFormat($ad, &$val, $formatField)
    {
        $text =   sprintf('<span class="ad-title">%s</span><p class="ad-desc"><small>%s</br>%s</small></p>', $val[$ad], $val['descriptionLine1'], $val['descriptionLine2']);

        if ($formatField['removeDepFields']) {
            unset($val['descriptionLine1']);
            unset($val['descriptionLine2']);
        }

        return $text;
    }



    public function sprintf($format_key, $value, $currency=null)
    {
        if ($currency) {
            $currency = $this->translateCurrency($currency);
        }

        if ($format = $this->getMetricsFormat($format_key)) {
            //if($currency)
             return sprintf($format, $value, $currency);
            //else
            // return sprintf($format,$value);
        } else {
            return $value;
        }
    }




    public function getMetricsFormat($metrics_name)
    {
        if ($this->units[$metrics_name][self::FORMAT]) {
            return $this->units[$metrics_name][self::FORMAT];
        }
    }

    public function getMetricsDecimal($metrics_name)
    {
        if (preg_match('/ga:goal\dConversionRate/', $metrics_name)) {
            $metrics_name = 'ga:goalConversionRate';
        }
        if ($this->units[$metrics_name][self::DECIMAL]) {
            return $this->units[$metrics_name][self::DECIMAL];
        }
    }

    public function getMetricsFormula($metrics_name)
    {
        if ($this->units[$metrics_name][self::FORMULA]) {
            return $this->units[$metrics_name][self::FORMULA];
        }
    }
}