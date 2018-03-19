<?php
namespace Eway\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use SoapHeader;

class DirectPayment  extends EwayApi implements ServiceManagerAwareInterface
{

    /*
	 * Eway Api Endpoint Production
	 */

	const  DIRECTPAYMENT_LIVE_REAL_TIME_API_ENDPOINT_PRODUCTION         = "https://www.eway.com.au/gateway/xmlpayment.asp";
	const  DIRECTPAYMENT_LIVE_REAL_TIME_API_ENDPOINT_SANDBOX            = "https://www.eway.com.au/gateway/xmltest/testpage.asp";

	const  DIRECTPAYMENT_LIVE_REAL_TIME_CVN_API_ENDPOINT_PRODUCTION     = "https://www.eway.com.au/gateway_cvn/xmlpayment.asp";
	const  DIRECTPAYMENT_LIVE_REAL_TIME_CVN_API_ENDPOINT_SANDBOX        = "https://www.eway.com.au/gateway_cvn/xmltest/testpage.asp";

	const  DIRECTPAYMENT_LIVE_GEO_IP_ANTI_FRAUD_API_ENDPOINT_PRODUCTION = "https://www.eway.com.au/gateway_beagle/xmlbeagle.asp";
	const  DIRECTPAYMENT_LIVE_GEO_IP_ANTI_FRAUD_API_ENDPOINT_SANDBOX    = "https://www.eway.com.au/gateway_beagle/test/xmlbeagle_test.asp";

	const  REAL_TIME = 'REAL-TIME';
	const  REAL_TIME_CVN = 'REAL-TIME-CVN';
	const  GEO_IP_ANTI_FRAUD = 'GEO-IP-ANTI-FRAUD';

    private $__trans_data = array();

    private $__curl_prefs = array();

	public function __construct($config){

		// Invoke the Parent's constructor
		parent::__construct($config);

	}


    //Class Constructor
	private function __setApiEndpoint($method =  self::REAL_TIME) {

	    switch($method){

		    case self::REAL_TIME:
		    			$this->__api_endpoint_production = self::DIRECTPAYMENT_LIVE_REAL_TIME_API_ENDPOINT_PRODUCTION;
	    				$this->__api_endpoint_sandbox    = self::DIRECTPAYMENT_LIVE_REAL_TIME_API_ENDPOINT_SANDBOX;
	    		break;
	    	 case self::REAL_TIME_CVN:
		    			$this->__api_endpoint_production = self::DIRECTPAYMENT_LIVE_REAL_TIME_CVN_API_ENDPOINT_PRODUCTION;
	    				$this->__api_endpoint_sandbox    = self::DIRECTPAYMENT_LIVE_REAL_TIME_CVN_API_ENDPOINT_SANDBOX;
	    		break;
	    	case self::GEO_IP_ANTI_FRAUD:
			    	    $this->__api_endpoint_production = self::DIRECTPAYMENT_LIVE_GEO_IP_ANTI_FRAUD_API_ENDPOINT_PRODUCTION;
	    				$this->__api_endpoint_sandbox    = self::DIRECTPAYMENT_LIVE_GEO_IP_ANTI_FRAUD_API_ENDPOINT_SANDBOX;
	    		break;
    	}
	}


	public function doPayment($trans_data,$method = 'REAL_TIME') {

		$config = $this->getConfig();
		
		// enabling sandbox mode for testing

		$this->__setApiEndpoint($method);

		if(!is_array($trans_data))
		  return false;

		$xmlRequest = "<ewaygateway><ewayCustomerID>" . $config['customer_id'] . "</ewayCustomerID>";

		foreach($trans_data as $key => $value)
				$xmlRequest .="<eway" . $key.">".htmlentities(trim($value))."</eway" . $key.">";

		$xmlRequest .= "</ewaygateway>";

		$xmlResponse = $this->__make_curl_request($xmlRequest);

		$response  	 =  $this->__processXMLResponse($xmlResponse);

		$response  	 = (object)array(	'status' 		=> $response['EWAYTRXNSTATUS'],
	   					 				'amount' 		=> $response['EWAYRETURNAMOUNT'] / 100, // amount is returned in cents
	   					 				'reference'	  	=> $response['EWAYTRXNREFERENCE'],
	   					 				'trans_option1'	=> $response['EWAYTRXNOPTION1'],
	   					 				'trans_option2'	=> $response['EWAYTRXNOPTION2'],
	   					 				'trans_option3'	=> $response['EWAYTRXNOPTION3'],
	   					 				'auth_code'	  	=> $response['EWAYAUTHCODE'],
	   					 				'trans_id'		=> $response['EWAYTRXNNUMBER'],
	   					 				'comment'		=> $response['EWAYTRXNERROR']);

		return $response;
	}


	//Set Transaction Data
	private function __setTransactionData($trans_data) {
		if(!is_array($trans_data))
		  return false;

		  foreach($trans_data as $key => $value)
				   $this->__trans_data["eway" . $key] = htmlentities(trim($value));

	}



	//obtain visitor IP even if is under a proxy
	public function getVisitorIP(){
		$ip    = $_SERVER["REMOTE_ADDR"];
		$proxy = $_SERVER["HTTP_X_FORWARDED_FOR"];

		if(preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$proxy))
		       $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];

		//return '127.0.0.1';
		return $ip;
	}
}
?>