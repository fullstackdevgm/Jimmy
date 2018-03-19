<?php
namespace Eway\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container as SessionContainer;
use Zend\XmlRpc\Client as XmlRpcClient;
use Zend\Soap\Client as SoapClient;
use SoapHeader;

class EwayApi  implements ServiceManagerAwareInterface
{	

	/*
	 * Service Manager
	 */
    private $serviceManager;

    /*
     * RapidApi Client
     */
    protected $__rapid_api_client;
  
	/*
	 * SOAP Client
	 */
  	protected $__soap_client = null;
	 
	/*
	 * SOAP Errors
	 */
  	private $__soap_errors = null;
	 
	/*
	 * Eway Api Endpoint Production 
	 */
    	
	protected $__api_endpoint_production =  null;
   
    /*
	 * Eway Api Endpoint Sandbox 
	 */
	protected $__api_endpoint_sandbox  = null;
	
	/*
	 * Eway  Config
	 */
  	private $__config = null;
	
	/**
	 * Response messages sent by eWay
	 * when a transaction is success
	 **/
	private $eway_success_transaction_messages = array(
		'A2000',
		'A2008',
		'A2010',
		'A2011',
		'A2016',
		);

	
	public function __construct($config){

		if(!$config)
		   throw new \Exception("Eway Api configuration couldnot be found");
		
		// Set the configurations
		$this->setConfig($config);
	}


	/*
	 * Sets the configurations
	 */
	public function setConfig($config){
		
		if($config)
			$this->__config = $config;
	
		return $this;
	}

	
	/*
	 * Retrieves the configurations
	 */
	public function getConfig(){
		
		if($this->__config)
			return $this->__config;
			
		return false;
	}
	
	
	/*
	 * Initializes and creates the Soap Client
	 */

	protected function __initSoap(){
		
		$config       = $this->getConfig();
	    $api_endpoint = $config['test_mode']?$this->__api_endpoint_sandbox:$this->__api_endpoint_production;
		
		$options = array('soap_version'   => SOAP_1_1,'encoding' => 'ISO-8859-1');
			
		try{
			$soap_client = new SoapClient($api_endpoint.'?wsdl',$options);
			
			$this->setSoapClient($soap_client);

		} catch (\Exception $e){
			//Catch errors here
		}	
	
		return $this;
	}
	
	
	/*
	 * Set Soap Headers for authentication
	 */
	public function setSoapHeaders(){
			$config  =  $this->getConfig();
			$headers = array(   'eWAYCustomerID'=> $config['customer_id'],
								'Username'		=> $config['username'],
								'Password'		=> $config['password'],
							 );
							 
			
 		    $header = new SoapHeader($this->__api_namespace,'eWAYHeader',$headers);
			$this->getSoapClient()->addSoapInputHeader($header);	
	}
	
	 /**
     * Set the SoapClient instance
     *
     * @return EwaySoapApi
     */
	public function setSoapClient($soap_client){
		
		if($soap_client)
			$this->__soap_client = $soap_client;
			
		return $this;
	}
	
	 /**
     * Retrieve the SoapClient instance
     *
     * @return SoapClient
     */
	public function getSoapClient(){
		
		return $this->__soap_client;
	}

	/**
	* Creates the Rapid Api Client
	*
	* @return RapidApi Client
	*/
	public function setRapidApiClient() {
		$config = $this->getConfig();
		$api_endpoint = "";
		if($config['test_mode']) {
			$api_endpoint = \Eway\Rapid\Client::MODE_SANDBOX;
		} else {
			$api_endpoint = \Eway\Rapid\Client::MODE_PRODUCTION;
		}
		$this->__rapid_api_client = \Eway\Rapid::createClient(
			"60CF3CDttBwvlWa+w7SbiaF02ZHrmhwSPETbPUFc7bRdwlnC1PQ82awcTnv3dT0R4kEJN4",
			"kP8ttSLy",
			\Eway\Rapid\Client::MODE_SANDBOX
			);
		return $this->__rapid_api_client;
	}


	/**
	* Retrieves the Rapid Api Client
	*
	* @return RapidApi Client
	*/
	public function getRapidApiClient() {
		if(!is_null($this->__rapid_api_client)) {
			return $this->__rapid_api_client;
		} else {
			// echo "set";
			return $this->setRapidApiClient();
		}
	}
	
	
	
	protected function __make_curl_request($xmlRequest){
		
		$config = $this->getConfig();
		
	    $api_endpoint = $config['test_mode']?$this->__api_endpoint_sandbox:$this->__api_endpoint_production;
		
		$ch = curl_init($api_endpoint);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $xmlResponse = curl_exec($ch);
		//echo curl_error($ch);

		
		if(curl_errno( $ch ) == CURLE_OK){
        	return $xmlResponse;
		} else 
		    return curl_error($ch);
	}
	
	
	//Parse XML response from eway and place them into an array
	function __processXMLResponse($xmlResponse){
		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser,  $xmlResponse, $xmlData, $index);
        $responseFields = array();
        foreach($xmlData as $data)
	    	if($data["level"] == 2)
        		$responseFields[$data["tag"]] = $data["value"];
        return $responseFields;
	}
	
	protected function __processResponse($response){
		
		
		
		if(!$response)
		  throw new \Exception("No Response");

		 if($response->Result=='Success') {
		    return $response;
		 } else if($response->Result=='Fail')
		   	return $response; //throw new \Exception($response->ErrorDetails);
		 else
		   return $response;  	
		return false;
	}
	
	protected function __processRapidResponse($response) {
		if(!$response) {
			throw new \Exception("No Response");
		}

		/**
		 * Checks success response messages array
		 * to see if the transaction was success
		 **/
		if($response->ResponseMessage) {
			// Response is success
			$processedResponse = array(
				'success' => true,
				'data' => $response
				);
			// Check if the transaction was successfully completed.
			if(in_array($response->ResponseMessage, $this->eway_success_transaction_messages)) {
				$processedResponse['transaction_success'] = true;
			} else {
				$processedResponse['transaction_success'] = false;
			}
			return $processedResponse;
		} else {
			if($response->Errors)
				throw new \Exception("Error processing transaction.");
		}
		return false;
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
     * @return GoogleAdwords
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
	
}
