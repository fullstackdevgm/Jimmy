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
	 * SOAP Client
	 */
  	private $__soap_client = null;
	 
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
	
	
	
	public function __construct($config){

		if(!$config)
		   throw new \Exception("Eway Api configuration couldnot be found");
		
		// Set the configurations
		$this->setConfig($config);
		
		
		// Invoke the init method
		$this->__init();
		
		
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

	protected function __init(){
		
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
	
	
	protected function __processResponse($response){
		
		if(!$response)
		  throw new \Exception("No Response");

		 if($response->Result=='Success') 
		    return $response;
		 else if($response->Result=='Fail')
		   	return $response; //throw new \Exception($response->ErrorDetails);
		   	
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
