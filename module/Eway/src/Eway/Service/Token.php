<?php
namespace Eway\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use SoapHeader;

class Token  extends EwayApi   implements ServiceManagerAwareInterface
{
	
	
    /*
	 * Eway Api Endpoint Production 
	 */
    	
	const  TOKEN_SOAP_API_ENDPOINT_PRODUCTION = "https://www.eway.com.au/gateway/rebill/manageRebill.asmx";
   
    /*
	 * Eway Api Endpoint Sandbox 
	 */
	const  TOKEN_SOAP_API_ENDPOINT_SANDBOX   = "https://www.eway.com.au/gateway/rebill/test/manageRebill_test.asmx";
	
    /*
	 * Eway Api NameSpace 
	 */
	const  TOKEN_SOAP_API_NAMESPACE		     = "https://www.eway.com.au/gateway/managedpayment";
	
   
  
  public function __construct($config){
		$this->__api_endpoint_production  = self::TOKEN_SOAP_API_ENDPOINT_PRODUCTION;
		$this->__api_endpoint_sandbox     = self::TOKEN_SOAP_API_ENDPOINT_SANDBOX;
		$this->__api_namespace		      = self::TOKEN_SOAP_API_NAMESPACE;
		
		// Invoke the Parent's constructor
		parent::__construct($config);
		parent::__initSoap();

  }
	
 
  public function createCustomer($customer_data){		

		try {

			 $requestbody = array(
				'Title' 		=> 'Mr.',
				'FirstName' 	=> $customer_data['firstname'],
				'LastName' 		=> $customer_data['lastname'],
				'Country' 		=> $customer_data['billing']['country'],
				'CardDetails'	=> array(
					'Name'			=> $customer_data['firstname'].' '. $customer_data['lastname'],
					'Number'	    => $customer_data['cc_number'],
					'ExpiryMonth'	=> $customer_data['cc_exp_month'],
					'ExpiryYear'	=> $customer_data['cc_exp_year'],
					'CVN'			=> $customer_data['cc_ccv'],
					),
				);
		    $client = \Eway\Rapid::createClient(
				"60CF3CDttBwvlWa+w7SbiaF02ZHrmhwSPETbPUFc7bRdwlnC1PQ82awcTnv3dT0R4kEJN4",
				"kP8ttSLy",
				"Sandbox"
			);

			$response = $client->createCustomer(\Eway\Rapid\Enum\ApiMethod::DIRECT, $requestbody);

			return $this->__processRapidResponse($response);
		} catch (\Exception $e){
			//return $e->getMessage();
			$response = array(
				'success' => false,
				'message' => $e->getMessage()
				);
			return $response;
			// Catch Exception and do whatever
		}
					
	}

	public function processPayment($customer_data, $tokenID, $amount=0) {

		try {

			$transaction = array(
				'Customer' => array(
					'TokenCustomerID' => $tokenID,
					),
				'Payment' => array(
					'TotalAmount' => $amount,
					),
				'TransactionType' => \Eway\Rapid\Enum\TransactionType::RECURRING,
				);

			$client = \Eway\Rapid::createClient(
					"60CF3CDttBwvlWa+w7SbiaF02ZHrmhwSPETbPUFc7bRdwlnC1PQ82awcTnv3dT0R4kEJN4",
					"kP8ttSLy",
					"Sandbox"
				);

			$response = $client->createTransaction(\Eway\Rapid\Enum\ApiMethod::DIRECT, $transaction);

			return $this->__processRapidResponse($response);

		} catch(\Exception $e) {
			$response = array(
				'success' => false,
				'message' => $e->getMessage()
				);
			return $response;
		}

	}
  
	public function updateCustomer($customer_data){
			
		if(!$customer_data) 
		  throw new \Exception('Customer Information not found');	
		
		if(!$customer_data['customer_id']) 
		  throw new \Exception('Customer Id not found');	

		try {	
			 $requestbody = array(
			 		'managedCustomerID'	=> $customer_data['customer_id'],
					'Title' 			=> '',
					'FirstName' 		=> $customer_data['firstname'],
					'LastName' 			=> $customer_data['lastname'],
					'Address' 			=> $customer_data['address'],
					'Suburb' 			=> '',
					'State' 			=> $customer_data['state'],
					'Company' 			=> '',
					'PostCode' 			=> $customer_data['zip'],
					'Country' 			=> $customer_data['country'],
					'Email' 			=> $customer_data['email'],
					'Fax' 				=> '',
					'Phone' 			=> '',
					'Mobile' 			=> '',
					'CustomerRef' 		=> '',
					'JobDesc' 			=> '',
					'Comments' 			=> '',
					'URL'	    		=> '',
					'CCNameOnCard'		=> $customer_data['firstname'].' '. $customer_data['lastname'],
					'CCNumber'	    	=> $customer_data['cc_num'],
					'CCExpiryMonth'		=> $customer_data['cc_exp_month'],
					'CCExpiryYear'		=> $customer_data['cc_exp_year'],
				);

				$this->setSoapHeaders();
		
			    $response = $this->getSoapClient()->UpdateCustomer($requestbody);

				return $this->__processResponse($response->UpdateCustomerResponse);
		} catch (\Exception $e){
			//return $e->getMessage();
			echo $e->getMessage();
			// Catch Exception and do whatever
		}
					
					
  }
  
  
   public function queryCustomer($customer_id){
			
		if(!$customer_id) 
		  throw new \Exception('Customer Id not found');	
		  
		try {	
				$requestbody = array(
					'managedCustomerID' => $customer_id
				);

				$this->setSoapHeaders();

			    $response = $this->getSoapClient()->QueryCustomer($requestbody);
				
			return $this->__processResponse($response->QueryCustomerResponse);
				
		} catch (\Exception $e){
			// Catch Exception and do whatever
		}
					
	}
		
	public function processPaymentWithBeagle($customer_data){
			
		if(!$customer_data) 
		  throw new \Exception('Customer data not found');	
		  
		if(!$customer_id) 
		  throw new \Exception('Customer Id not found');	
		  
		try {	
				   	 

					$requestbody = array(
						'managedCustomerID' 	=> $customer_data['customer_id'],
						'amount' 				=> $customer_data['amount'],
						'invoiceReference' 		=> $customer_data['inv_ref'],
						'invoiceDescription' 	=> $customer_data['inv_desc'],
						'cvn' 					=> $customer_data['cvn'],
						'ipAddress' 			=> $customer_data['ip_address'],
						'billingCountry' 		=> $customer_data['billing_country'],
					);
					
					$this->setSoapHeaders();
	
			   		$response = $this->getSoapClient()->ProcessPaymentWithBeagle($requestbody);

			return $this->__processResponse($response->ProcessPaymentWithBeagleResponse);
				
		} catch (\Exception $e){
			return $e;
			// Catch Exception and do whatever
		}
					
	}
	
  	public function processPaymentWithCVN($customer_data){
			
		if(!$customer_data) 
		  throw new \Exception('Customer data not found');	
		
		if(!$customer_id) 
		  throw new \Exception('Customer Id not found');	
		

		try {	
				   	 

					$requestbody = array(
						'managedCustomerID' 	=> $customer_data['customer_id'],
						'amount' 				=> $customer_data['amount'],
						'invoiceReference' 		=> $customer_data['inv_ref'],
						'invoiceDescription' 	=> $customer_data['inv_desc'],
						'cvn' 					=> $customer_data['cvn'],
					);
					
					$this->setSoapHeaders();
	
			   		$response = $this->getSoapClient()->ProcessPaymentWithCVN($requestbody);

			return $this->__processResponse($response->ProcessPaymentWithCVNResponse);
				
		} catch (\Exception $e){
			return $e;
			// Catch Exception and do whatever
		}
					
	}

	public function queryPayment($customer_id){
			
		if(!$customer_id) 
		  throw new \Exception('Customer Id not found');	
		  
		try {	
				$requestbody = array(
					'managedCustomerID' => $customer_id
				);

				$this->setSoapHeaders();

			    $response = $this->getSoapClient()->QueryPayment($requestbody);
				
			return $this->__processResponse($response->QueryPaymentResponse);
				
		} catch (\Exception $e){
			// Catch Exception and do whatever
		}
					
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
