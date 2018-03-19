<?php
namespace Eway\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use SoapHeader;

class Recurring  extends EwayApi   implements ServiceManagerAwareInterface
{


    /*
	 * Eway Api Endpoint Production
	 */

	const  RECURRING_SOAP_API_ENDPOINT_PRODUCTION = "https://www.eway.com.au/gateway/rebill/manageRebill.asmx";

    /*
	 * Eway Api Endpoint Sandbox
	 */
	const  RECURRING_SOAP_API_ENDPOINT_SANDBOX   = "https://www.eway.com.au/gateway/rebill/test/manageRebill_test.asmx";

    /*
	 * Eway Api NameSpace
	 */
	const  RECURRING_SOAP_API_NAMESPACE		     = "http://www.eway.com.au/gateway/rebill/manageRebill";

    /*
	 * Eway XML API ENDPOINT Live
	 */
	const  RECURRING_XML_API_ENDPOINT_PRODUCTION    = "https://www.eway.com.au/gateway/rebill/upload.aspx";

    /*
	 * Eway XML Api Endpoint Sandbox
	 */
	const  RECURRING_XML_API_ENDPOINT_SANDBOX    = "https://www.eway.com.au/gateway/rebill/test/Upload_test.aspx";





  public function __construct($config){
		$this->__api_endpoint_production  = self::RECURRING_SOAP_API_ENDPOINT_PRODUCTION;
		$this->__api_endpoint_sandbox     = self::RECURRING_SOAP_API_ENDPOINT_SANDBOX;
		$this->__api_namespace		      = self::RECURRING_SOAP_API_NAMESPACE;

		// Invoke the Parent's constructor
		parent::__construct($config);
		parent::__initSoap();

  }


  public function createCustomer($customer_data){


		if(!$customer_data)
		  throw new \Exception('Customer Information not found');

		//try {
			 $requestbody = array(
					'customerTitle' 	=> '',
					'customerFirstName' => $customer_data['firstname'],
					'customerLastName' 	=> $customer_data['lastname'],
					'customerAddress' 	=> $customer_data['address']?$customer_data['address']:'',
					'customerSuburb' 	=> '',
					'customerState' 	=> '',
					'customerCompany' 	=> '',
					'customerPostCode' 	=> '',
					'customerCountry' 	=> $customer_data['country']?$customer_data['country']:'',
					'customerEmail' 	=> $customer_data['email'],
					'customerFax' 		=> '',
					'customerPhone1' 	=> '',
					'customerPhone2' 	=> '',
					'customerRef' 		=> '',
					'customerJobDesc' 	=> '',
					'customerComments' 	=> '',
					'customerURL'	    => '',
				);

				$this->setSoapHeaders();

			    $response = $this->getSoapClient()->CreateRebillCustomer($requestbody);

				return $this->__processResponse($response->CreateRebillCustomerResult);
		//} catch (\Exception $e){

			//return $e->getMessage();
			//echo $e->getMessage();
		//	// Catch Exception and do whatever
		//}

	}


	public function updateCustomer($customer_data){

		if(!$customer_data)
		  throw new \Exception('Customer Information not found');


		try {
			 $requestbody = array(
					'customerTitle' 	=> '',
					'customerFirstName' => $customer_data['firstname'],
					'customerLastName' 	=> $customer_data['lastname'],
					'customerAddress' 	=> $customer_data['address']?$customer_data['address']:'',
					'customerSuburb' 	=> '',
					'customerState' 	=> '',
					'customerCompany' 	=> '',
					'customerPostCode' 	=> '',
					'customerCountry' 	=> $customer_data['country']?$customer_data['country']:'',
					'customerEmail' 	=> $customer_data['email'],
					'customerFax' 		=> '',
					'customerPhone1' 	=> '',
					'customerPhone2' 	=> '',
					'customerRef' 		=> '',
					'customerJobDesc' 	=> '',
					'customerComments' 	=> '',
					'customerURL'	    => '',
				);

			    $this->setSoapHeaders();

			    $response = $this->getSoapClient()->UpdateRebillCustomer($requestbody);

				return  $this->__processResponse($response->UpdateRebillCustomerResult);

		} catch (\Exception $e){
			// Catch Exception and do whatever
		}

  }

  public function deleteCustomer($rebill_customer_id){

		 if(!$rebill_customer_id)
		  throw new \Exception('Rebill Customer Id not found');

		try {
				$requestbody = array(
					'RebillCustomerID' => $rebill_customer_id
				);

				$this->setSoapHeaders();

				$response =  $this->getSoapClient()->DeleteRebillCustomer($requestbody);

				return $this->__processResponse($response->DeleteRebillCustomerResult);

		} catch (\Exception $e){
			// Catch Exception and do whatever
		}

   }

   public function queryCustomer($rebill_customer_id){

		 if(!$rebill_customer_id)
		  throw new \Exception('Rebill Customer Id not found');

		try {
				$requestbody = array(
					'RebillCustomerID' => $rebill_customer_id
				);

				$this->setSoapHeaders();

			    $response = $this->getSoapClient()->QueryRebillCustomer($requestbody);

			return $this->__processResponse($response->QueryRebillCustomerResult);

		} catch (\Exception $e){
			// Catch Exception and do whatever
		}

	}

  	public function createEvent($event_data){

		 if(!$event_data)
		  throw new \Exception('Rebill Event data not found');

		//try {
				 $requestbody = array(
					'RebillCustomerID' 	=> $event_data['customer_id'],
					'RebillInvRef' 		=> $event_data['invoice_ref'],
					'RebillInvDes' 		=> $event_data['invoice_desc'],
					'RebillCCName' 		=> $event_data['cc_name'],
					'RebillCCNumber' 	=> $event_data['cc_num'],
					'RebillCCExpMonth' 	=> $event_data['cc_exp_month'],
					'RebillCCExpYear' 	=> $event_data['cc_exp_year'],
					'RebillInitAmt'	 	=> $event_data['init_amt'],
					'RebillInitDate' 	=> $event_data['init_date'],
					'RebillRecurAmt' 	=> $event_data['recurring_amt'],
					'RebillStartDate' 	=> $event_data['start_date'],
					'RebillInterval' 	=> $event_data['interval'],
					'RebillIntervalType' => $event_data['interval_type'],
					'RebillEndDate' 	=> $event_data['end_date']
				);

					$this->setSoapHeaders();

			   		$response = $this->getSoapClient()->CreateRebillEvent($requestbody);

			return $this->__processResponse($response->CreateRebillEventResult);

		//} catch (\Exception $e){
		//	return $e;
			// Catch Exception and do whatever
		//}

	}


	public function updateEvent($event_data){

		 if(!$event_data)
		  throw new \Exception('Rebill Event data not found');

		try {
				 $requestbody = array(
					'RebillCustomerID' 	=> $event_data['customer_id'],
					'RebillID' 			=> $event_data['rebill_id'],
					'RebillInvRef' 		=> $event_data['invoice_ref'],
					'RebillInvDes' 		=> $event_data['invoice_desc'],
					'RebillCCName' 		=> $event_data['cc_name'],
					'RebillCCNumber' 	=> $event_data['cc_num'],
					'RebillCCExpMonth' 	=> $event_data['cc_exp_month'],
					'RebillCCExpYear' 	=> $event_data['cc_exp_year'],
					'RebillInitAmt'	 	=> $event_data['init_amt'],
					'RebillInitDate' 	=> $event_data['init_date'],
					'RebillRecurAmt' 	=> $event_data['recurring_amt'],
					'RebillStartDate' 	=> $event_data['start_date'],
					'RebillInterval' 	=> $event_data['interval'],
					'RebillIntervalType' => $event_data['interval_type'],
					'RebillEndDate' 	=> $event_data['end_date']
				);

				$this->setSoapHeaders();
				$response = $this->getSoapClient()->UpdateRebillEvent($requestbody);
				//print_r($this->getSoapClient());

			return $this->__processResponse($response->UpdateRebillEventResult);

		} catch (\Exception $e){
			echo $e->getMessage();
			// Catch Exception and do whatever
		}

	}


	public function deleteEvent($customer_id, $rebill_id){

		 if(!$customer_id)
		  throw new \Exception('Rebill Customer Id not found');

		 if(!$rebill_id)
		  throw new \Exception('Rebill Event Id not found');

		try {
				$requestbody = array(
					'RebillCustomerID' 	=> $customer_id,
					'RebillID' 			=> $rebill_id,
				);

				$this->setSoapHeaders();

				$response = $this->getSoapClient()->DeleteRebillEvent($requestbody);

			return $this->__processResponse($response->DeleteRebillEventResult);

		} catch (\Exception $e){
			// Catch Exception and do whatever
		}

	}


	 public function queryEvent($customer_id, $rebill_id){

		 if(!$customer_id)
		  throw new \Exception('Rebill Customer Id not found');

		 if(!$rebill_id)
		  throw new \Exception('Rebill Event Id not found');

		try {
				$requestbody = array(
					'RebillCustomerID' 	=> $customer_id,
					'RebillID' 			=> $rebill_id,
				);

				$this->setSoapHeaders();

			    $response = $this->getSoapClient()->QueryRebillEvent($requestbody);

			return $this->__processResponse($response->QueryRebillEventResult);
		} catch (\Exception $e){
			// Catch Exception and do whatever
		}

	}

 	public function queryTransaction($trans_data){

		if(!$trans_data)
		  throw new \Exception('Transaction Information not found');


		try {	$date =  date('Y-m-d',strtotime('2013-11-26'));
				 $requestbody = array(
					'RebillCustomerID' 	=> $trans_data['customer_id'],
					'RebillID' 			=> $trans_data['rebill_id'],
					'startDate' 		=> $trans_data['start_date'],
					'endDate'			=> $trans_data['end_date'],
					'status' 			=> $trans_data['status']
				);
				$this->setSoapHeaders();

			    $response = $this->getSoapClient()->QueryTransactions($requestbody);


			    if($response->QueryTransactionsResult->rebillTransaction){

			    	if(count($response->QueryTransactionsResult->rebillTransaction) > 1){
				    	foreach($response->QueryTransactionsResult->rebillTransaction as $transaction)	{
					       	$transaction[] 	= (object)array('status' 		=> $transaction->Status,
					       					 				'amount' 		=> $transaction->Amount/100,
					       					 				'type'	  		=> $transaction->Type,
					       					 				'date'	   		=> $transaction->TransactionDate,
					       					 				'trans_id'		=> $transaction->TransactionNumber,
					       					 				'comment'		=> $transaction->TransactionError);

				 	   }
				 	   return $transactions;
			 		} else {
			 			$transaction 	= (object)array(	'status' 		=> $response->QueryTransactionsResult->rebillTransaction->Status,
					       					 				'amount' 		=> $response->QueryTransactionsResult->rebillTransaction->Amount/100,
					       					 				'type'	  		=> $response->QueryTransactionsResult->rebillTransaction->Type,
					       					 				'date'	   		=> $response->QueryTransactionsResult->rebillTransaction->TransactionDate,
					       					 				'trans_id'		=> $response->QueryTransactionsResult->rebillTransaction->TransactionNumber,
					       					 				'comment'		=> $response->QueryTransactionsResult->rebillTransaction->TransactionError);

			 		}

			 		return $transaction;

				} else {
					if(is_object($response))
						throw new \Exception("No transactions found");
					else
						throw new \Exception($response);

				}

		} catch (\Exception $e){
			echo $e->getMessage();
		}

	}

	public function queryNextTransaction($customer_id, $rebill_id){

		if(!$customer_id)
		  throw new \Exception('Customer Id not found');

		if(!$rebill_id)
		  throw new \Exception('Rebill Id not found');


		try {
				 $requestbody = array(
					'RebillCustomerID' 	=> $customer_id,
					'RebillID' 			=> $rebill_id,
				);

				$this->setSoapHeaders();

			    $response = $this->getSoapClient()->QueryNextTransaction($requestbody);

			return $this->__processResponse($response->QueryNextTransactionResult);

		} catch (\Exception $e){
			// Catch Exception and do whatever
		}

	}

	public function ToXML($recurring_data){
		$config = $this->getConfig();

		$xmlRebill = new \DomDocument('1.0');

		$nodeRoot = $xmlRebill->CreateElement('RebillUpload');
		$nodeRoot = $xmlRebill->appendChild($nodeRoot);

		$nodeNewRebill = $xmlRebill->createElement('NewRebill');
		$nodeNewRebill = $nodeRoot->appendChild($nodeNewRebill);

		$nodeCustomer = $xmlRebill->createElement('eWayCustomerID');
		$nodeCustomer = $nodeNewRebill->appendChild($nodeCustomer);

		$value = $xmlRebill->createTextNode($config['customer_id']);
		$value = $nodeCustomer->appendChild($value);


        //Customer
		$nodeCustomer = $xmlRebill->createElement('Customer');
		$nodeCustomer = $nodeNewRebill->appendChild($nodeCustomer);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerRef');
	 	$nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerRef']);
                $value = $nodeCustomerDetails->appendChild($value);

	 	$nodeCustomerDetails = $xmlRebill->createElement('CustomerTitle');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($this->m_CustomerTitle);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerFirstName');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerFirstName']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerLastName');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerLastName']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerCompany');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerCompany']);
                $value = $nodeCustomerDetails->appendChild($value);


		$nodeCustomerDetails = $xmlRebill->createElement('CustomerJobDesc');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerJobDesc']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerEmail');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerEmail']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerAddress');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerAddress']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerSuburb');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($this->m_CustomerSuburb);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerState');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerState']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerPostCode');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerPostCode']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerCountry');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerCountry']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerPhone1');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerPhone1']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerPhone2');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerPhone2']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerFax');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerFax']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerURL');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerURL']);
                $value = $nodeCustomerDetails->appendChild($value);

		$nodeCustomerDetails = $xmlRebill->createElement('CustomerComments');
                $nodeCustomerDetails = $nodeCustomer->appendChild($nodeCustomerDetails);

                $value = $xmlRebill->createTextNode($recurring_data['CustomerComments']);
                $value = $nodeCustomerDetails->appendChild($value);


        //Rebill Events

		$nodeRebillEvent = $xmlRebill->createElement('RebillEvent');
		$nodeRebillEvent = $nodeNewRebill->appendChild($nodeRebillEvent);

		$nodeRebillDetails = $xmlRebill->createElement('RebillInvRef');
		$nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

		$value = $xmlRebill->createTextNode($recurring_data['RebillInvRef']);
		$value = $nodeRebillDetails->AppendChild($value);


		$nodeRebillDetails = $xmlRebill->createElement('RebillInvDesc');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillInvDesc']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillCCName');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillCCName']);
                $value = $nodeRebillDetails->AppendChild($value);


		$nodeRebillDetails = $xmlRebill->createElement('RebillCCNumber');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillCCNumber']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillCCExpMonth');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillCCExpMonth']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillCCExpYear');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillCCExpYear']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillInitAmt');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillInitAmt']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillInitDate');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillInitDate']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillRecurAmt');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillRecurAmt']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillStartDate');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillStartDate']);
                $value = $nodeRebillDetails->AppendChild($value);

		$nodeRebillDetails = $xmlRebill->createElement('RebillInterval');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillInterval']);
                $value = $nodeRebillDetails->AppendChild($value);

                $nodeRebillDetails = $xmlRebill->createElement('RebillIntervalType');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillIntervalType']);
                $value = $nodeRebillDetails->AppendChild($value);

                $nodeRebillDetails = $xmlRebill->createElement('RebillEndDate');
                $nodeRebillDetails = $nodeRebillEvent->appendChild($nodeRebillDetails);

                $value = $xmlRebill->createTextNode($recurring_data['RebillEndDate']);
                $value = $nodeRebillDetails->AppendChild($value);

		$InnerXml = $xmlRebill->saveXML();

		return $InnerXml;

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
