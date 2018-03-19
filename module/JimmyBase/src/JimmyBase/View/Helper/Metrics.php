<?php

namespace JimmyBase\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Metrics extends AbstractHelper
{

	private $units	= array(	'clicks'			=>	array('format'=>'%s',	'decimal'=>0,'formula'=> null),
	  					   		'impressions'		=>	array('format'=>'%s',	'decimal'=>0,'formula'=> null),
						   		'convertedClicks' 	=> 	array('format'=>'%s',	'decimal'=>0,'formula'=> null),
						  		'ctr' 				=>  array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
						   		'avgCPC' 			=>  array('format'=>'%s <small class="curr">%s</small>', 'decimal'=>2,'formula'=> '%s/1000000'),
						   		'cost' 				=>  array('format'=>'%s <small class="curr">%s</small>', 'decimal'=>2,'formula'=> '%s/1000000'),
						   		'costConvertedClick' => 	array('format'=>'%s <small class="curr">%s</small>', 'decimal'=>2,'formula'=> '%s/1000000'),
						  		'clickConversionRate' => 	array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
						   		'convRate'			=> 	array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
								'avgPosition'	   	=> 	array('format'=>'%s',	'decimal'=>1,'formula'=> null),
						   		'searchImprShare'	=> 	array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
						   		'ga:visitBounceRate'	=> 	array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
						   		'ga:pageviewsPerVisit'	=> 	array('format'=>'%s',	'decimal'=>2,'formula'=> null),
						   		'ga:percentNewVisits'	=> 	array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
						   		'ga:avgTimeOnSite'		=> 	array('format'=>'%s',	'decimal'=>2,'formula'=> null),
						   		'ga:entrances'			=> 	array('format'=>'%s',	'decimal'=>0,'formula'=> null),
						   		'ga:exitRate'			=> 	array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
						   		'ga:pageValue'			=> 	array('format'=>'%s',	'decimal'=>2,'formula'=> null),
						   		'ga:transactionsPerVisit'=> array('format'=>'%s%%',	'decimal'=>2,'formula'=> null),
						   		'ga:transactionRevenue'=> array('format'=>'$%s',	'decimal'=>2,'formula'=> null)                                                                
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
    public function __invoke()
    {

		return $this;

	}

	public function calculateMetrics($metrics_name,$value,$currency){
	 	if($currency)
	 		$currency = $this->translateCurrency($currency);

		# If the key doesnot exist in array return the passed value
		if(!in_array($metrics_name,array_keys($this->units)))
	 		return $value;

		# Format Formula
		if($formula = $this->getMetricsFormula($metrics_name))
		    eval("\$value =  round(".sprintf($formula,$value,$currency).",\$this->getMetricsDecimal(\$metrics_name));");

		$val =  number_format($value,$this->getMetricsDecimal($metrics_name));
		return $this->sprintf($metrics_name,$val,$currency);
	}


	public function formatNumber($metrics_name,$value,$currency=null){
		if($currency)
	 		$currency = $this->translateCurrency($currency);

		$val =  number_format($value,$this->getMetricsDecimal($metrics_name));
		 return $this->sprintf($metrics_name,$val,$currency);

	}

	public function formatTime($metrics_name,$value){

		$val =   gmdate("H:i:s", $value);
		return $this->sprintf($metrics_name,$val);
	}

	public function translateCurrency($currency){

		if($translatedCurrency = $this->currency[$currency]){
			return $translatedCurrency;
		} else
			return $currency;

	}


	public function formatText($field,&$val,$formatField){
		$method = '_'.$field.'Format';

	  if(method_exists($this,$method))
		 return $this->$method($field,$val,$formatField);
	  else
		 return $val[$field];

	}


	private function _adFormat($ad,&$val,$formatField){
		$text =   sprintf('<span class="ad-title">%s</span><p class="ad-desc"><small>%s</br>%s</small></p>',$val[$ad],$val['descriptionLine1'],$val['descriptionLine2']);

		if($formatField['removeDepFields']){
			unset($val['descriptionLine1']);
			unset($val['descriptionLine2']);
		}

		return $text;
	}



	public function sprintf($format_key,$value,$currency){

			if($format = $this->getMetricsFormat($format_key) ){
				if($currency)
				 return sprintf($format,$value,$currency);
				else
				 return sprintf($format,$value);
			}
			else
			    return $value;
	}




	private function getMetricsFormat($metrics_name){

		if($this->units[$metrics_name][self::FORMAT])
		   return $this->units[$metrics_name][self::FORMAT];

	}

    private function getMetricsDecimal($metrics_name){

		if($this->units[$metrics_name][self::DECIMAL])
		   return $this->units[$metrics_name][self::DECIMAL];

	}

	private function getMetricsFormula($metrics_name){

		if($this->units[$metrics_name][self::FORMULA])
		   return $this->units[$metrics_name][self::FORMULA];

	}


	public function calcComparedMetrics($value,$compareValue){
 		$value  =  str_replace(array(",","%"),"",$value);
        $compareValue = str_replace(array(",","%"),"",$compareValue);

         $val = round((($value-$compareValue)/$compareValue)*100);

         if(!is_numeric($val)){
           $val ='n/a <i class="glyph-icon icon-long-arrow-up font-gray-dark" style="visibility:hidden"></i>';
         } else if($val=='Infinity'){
           $val ='n/a <i class="glyph-icon icon-long-arrow-up font-gray-dark" style="visibility:hidden"></i>';
         } else if($val>=0){
           $val.='% <i class="glyph-icon icon-long-arrow-up font-gray-dark"></i>';
         } else {
           $val =  str_replace('-',"",$val);
           $val.='% <i class="glyph-icon icon-long-arrow-down font-gray-dark"></i>';
         }

        return $val;

	}

	public function calcComparedMetricsCaption($value,$compareValue,$metric){
 		$value  =  str_replace(array(",","%"),"",$value);
        $compareValue = str_replace(array(",","%"),"",$compareValue);

         $val = round((($value-$compareValue)/$compareValue)*100);

         if(!is_numeric($val)){
           $text ='n/a';
         } else if($val=='Infinity'){
           $text ='n/a';
         } else if($val>=0){
           $text.='Increase in '.$metric." by " . $val . "%";
         } else {
           $text.='Decrease in '.$metric." by " . abs($val) . "%";
         }


        return $text;

	}


}
