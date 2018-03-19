<?php

namespace JimmyBase\Service;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Crypt\Password\Bcrypt;
use ZfcBase\EventManager\EventProvider;
use Zend\Session\Container as SessionContainer;

use JimmyBase\Mapper\UserPaymentsInterface as UserPaymentsMapperInterface;
use JimmyBase\Mapper\UserInterface as UserMapperInterface;
use JimmyBase\Mapper\PackageInterface as PackageMapperInterface;
use JimmyBase\Entity\UserPayments;

class Payment extends EventProvider   implements ServiceManagerAwareInterface
{


	protected $_currency;

	protected $_processor = 'eWay';

	protected $_frequency;

	protected $_period;

    protected $recurringApi;

    protected $directPaymentApi;

    protected $packageMapper;

	protected $directPaymentApiResponse;

	protected $userId;

	protected $recurringApiResponse;

	protected $payment_frequency;

	protected $payment_period;

	
    /**
     * @var UserMapperInterface
     */
    protected $userMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

	/*
	 * Jimmy  Config
	 */
  	private $_config = null;



	public function __construct($config){
		$this->setConfig($config);


		$this->_currency  = $config['payment']['currency'];
		$this->_processor = $config['payment']['processor'];
		$this->_frequency = $config['payment']['frequency'];
		$this->_period    = $config['payment']['period'];

	}




	public function setupCustomerPayment($request){



		if($request->getPost()){
			$customerInfo = $request->getPost()->toArray();

			$user_mapper   			= $this->getUserMapper();
			$user_payments_mapper   = $this->getUserPaymentsMapper();
			$package_mapper			= $this->getPackageMapper();


			$user = $user_mapper->setUserType('agency')
					            ->findByEmail($customerInfo['email']);

			if(!$user){

				if(!$customerInfo['package'])
					throw new \Exception("Package Id not provided");

				$package       = $package_mapper->findById($customerInfo['package']);

				if(!$package)
					throw new \Exception("Package not found");

				$package_price = 0;
			    $package_price = $package->getPrice(); // Eway accepts currencies in cents so multiply by 100 to get the dollar


				$customerInfo['oneoff_amount']  = $package_price;
				$customerInfo['recurr_amount']  = $package_price;

				# Direct Payment  - Charge for the first time to check if the card is valid
				if($this->doDirectPayment($customerInfo)){

					$this->getEventManager()->trigger('userInvoice.success', $this, array('rawUserData'     => $customerInfo,
																				   		  'paymentResponse' => $this->directPaymentApiResponse,
																				   		  'currency'		=> $this->_currency));

					if(!$this->setupRecurringPayment($customerInfo))
						throw new \Exception($this->recurringApiResponse->ErrorDetails);

					if(!$user = $this->saveUser($customerInfo,$this->recurringApiResponse->RebillCustomerID,$this->recurringApiResponse->RebillID))
					    throw new \Exception('User couldnot be created');

					$this->userId = $user->getId();

					if(!$this->saveUserMeta($user,$package->getId(),$this->recurringApiResponse->RebillCustomerID,$this->recurringApiResponse->RebillID))
					    throw new \Exception('User  Info couldnot be saved');

					if(!$this->saveUserPayment($this->directPaymentApiResponse))
					    throw new \Exception("Transaction couldnot be saved");

					$json = array('success'=>true);

				} else {

					if(is_object($this->directPaymentApiResponse))
					    $error = $this->directPaymentApiResponse->comment;
					else
						$error = $this->directPaymentApiResponse;

					$json = array('success'=>false,'message' => $error);
				}

			} else {
				$json = array('success'=>false,'message'=>'User with the email address already exists');
			}

		} else  {
			 $json = array('success' => false,'message'=>'No Data provided');
		}

		$json['directpayment_response'] = $this->directPaymentApiResponse;

		return $json;
	}

	public function upgradeCustomerFromTrialPackage($data,$user){


		if($data){
			$customerInfo = $data;
			//$customerInfo['package'] = 3;
			$user_mapper   			= $this->getUserMapper();
			$user_payments_mapper   = $this->getUserPaymentsMapper();
			$package_mapper			= $this->getPackageMapper();

			if($user){
				$this->userId = $user->getId();


				if(!$customerInfo['package'])
					throw new \Exception("Package Id not provided");

				$package       = $package_mapper->findById($customerInfo['package']);

				if(!$package)
					throw new \Exception("Package not found");

				$package_price = 0;
			    $package_price = $package->getPrice(); // Eway accepts currencies in cents so multiply by 100 to get the dollar

				$customerInfo['oneoff_amount']  = $package_price;
				$customerInfo['recurr_amount']  = $package_price;
				$customerInfo['email']  	    = $user->getEmail();

				# Direct Payment  - Charge for the first time to check if the card is valid
				if($this->doDirectPayment($customerInfo)){

					$this->getEventManager()->trigger('userInvoice.success', $this, array('user'   	        => $user,
																				   		  'rawUserData'     => $customerInfo,
																				   		  'paymentResponse' => $this->directPaymentApiResponse,
																				   		  'currency'		=> $this->_currency));

					if(!$this->setupRecurringPayment($customerInfo))
						throw new \Exception($this->recurringApiResponse->ErrorDetails);


					if(!$this->saveUserMeta($user,$package->getId(),$this->recurringApiResponse->RebillCustomerID,$this->recurringApiResponse->RebillID))
					    throw new \Exception('User  Info couldnot be saved');

					if(!$this->saveUserPayment($this->directPaymentApiResponse))
					    throw new \Exception("Transaction couldnot be saved");

					$json = array('success'=>true);

				} else {

					if(is_object($this->directPaymentApiResponse))
					    $error = $this->directPaymentApiResponse->comment;
					else
						$error = $this->directPaymentApiResponse;


					$json = array('success'=>false,'message' => $error);
				}

			} else {
				$json = array('success'=>false,'message'=>'User with the given email address does not exist');
			}

		} else  {
			 $json = array('success' => false,'message'=>'No Data provided');
		}

		$json['directpayment_response'] = $this->directPaymentApiResponse;

		return $json;
	}

	public function upgradeCustomerPackage($customerInfo,$user){
		if($customerInfo){

			if($user){
				$this->userId = $user->getId();


				$user_mapper   			= $this->getUserMapper();
				$user_payments_mapper   = $this->getUserPaymentsMapper();
				$package_mapper			= $this->getPackageMapper();

				if(!$customerInfo['package'])
					throw new \Exception("Package Id not provided");

				$package       = $package_mapper->findById($customerInfo['package']);

				if(!$package)
					throw new \Exception("Package not found");


				$customerInfo['email']  		   = $user->getEmail();
				$customerInfo['eway_customer_id']  = $user_mapper->getMeta($user->getId(),'eway_customer_id');
				$customerInfo['eway_rebill_id']    = $user_mapper->getMeta($user->getId(),'eway_rebill_id');
			    $next_payment_date   		       = $user_mapper->getMeta($user->getId(),'next_payment_date');
			    $days_in_period 		 		   = $this->_getTotalDaysInPeriod();

			    $next_payment_timestamp	 = strtotime($next_payment_date);
				$prev_payment_timestamp  = strtotime("-{$days_in_period} days",$next_payment_timestamp);
				$current_timestamp	   	 = strtotime("today");


			    if($next_payment_timestamp >= $current_timestamp){
				   $diff           = $next_payment_timestamp - $current_timestamp;
				   $days_remaining = ceil(abs($next_payment_timestamp - $current_timestamp) / 86400); // days
				   $days_consumed  = ceil(abs($current_timestamp - $prev_payment_timestamp) / 86400); // days


				   // adjust the days consumed
				   if( $days_consumed + $days_remaining > $days_in_period)
					   $days_consumed--;
				   else if( $days_consumed + $days_remaining < $days_in_period)
				       $days_consumed = $days_in_period - ($days_consumed  + $days_remaining);

				   if($days_remaining > $days_in_period)
				 	  $days_remaining = $days_in_period;
				}


				# Current Package
				$current_package_id          = $user_mapper->getMeta($user->getId(),'package');
				$current_package	         = $package_mapper->findById($current_package_id);
				$current_package_price 	     = $current_package->getPrice();

		    	$current_package_price_daily = $this->_getDailyPrice($current_package_price);

				# Package to be Upgraded to
			    $new_package_price       	 = $package->getPrice();
				$new_package_price_daily 	 = $this->_getDailyPrice($new_package_price);

				$used_amt       = number_format($days_consumed  * $current_package_price_daily,2);
			 	$amt_to_be_paid = number_format($days_remaining * $new_package_price_daily,2); // This amount could be 0 sometimes;


				/*
				 * The $amt_to_be_paid can be 0 if  the customer upgrades on the scheduled payment date
				 * Therefore , set the $customerInfo['oneoff_amount'] to be the first month's subscription amount
				 * to make sure that the CC details provided is correct
				 */

				 $customerInfo['inv_desc']		= "JimmyData Difference Amount Payment for Package Upgrade";

				if($amt_to_be_paid == 0){
				   $amt_to_be_paid = $new_package_price;
				   $customerInfo['inv_desc'] = "JimmyData Package Difference price";
				}

				$customerInfo['oneoff_amount']  = $amt_to_be_paid;
				$customerInfo['recurr_amount']  = $new_package_price;

				# Fetch recurring event info from eWay
				$apiResponse    = $this->queryRecurringEvent($user->getId());

				# Direct Payment  -  Charge the oneoff amount to test the CC.
				if($this->doDirectPayment($customerInfo)){

					$this->getEventManager()->trigger('userInvoice.success', $this, array('user'   	     	=> $user,
																				   		  'rawUserData'     => $customerInfo,
																				   		  'paymentResponse' => $this->directPaymentApiResponse,
																				          'currency'		=> $this->_currency));

					if(!$this->setupUpgradedPackage($customerInfo,$apiResponse))
						throw new \Exception($this->recurringApiResponse->ErrorDetails);

					if(!$this->saveUserPackageMeta($user,$package->getId()))
						throw new \Exception('User Package Info couldnot be saved');

					if(!$this->saveUserPayment($this->directPaymentApiResponse))
					    throw new \Exception("Transaction couldnot be saved");

				    $json = array('success' => true);

				} else {

					if(is_object($this->directPaymentApiResponse))
					    $error = $this->directPaymentApiResponse->comment;
					else
						$error = $this->directPaymentApiResponse;

					$json = array('success'=>false,'message' => $error);
				}


			} else {
				$json = array('success'=>false,'message'=>'User with the given email address doesnot exists');
			}

		} else  {
			 $json = array('success' => false,'message'=>'No Data provided');
		}


		$json['directpayment_response'] = $this->directPaymentApiResponse;

		return $json;
	}



	public function setupRecurringPayment($customerInfo){

	  	if(!$customerInfo)
		    return false;  // If customer info not provided return early

		$this->recurringApiResponse    = $this->getRecurringPaymentApi()->createCustomer($customerInfo); # Create Customer in eWay

		if(!$this->recurringApiResponse->RebillCustomerID)
			return false;

		# Create Rebill Event
		$rebillEventInfo	= array('customer_id'		=> $this->recurringApiResponse->RebillCustomerID,
									'cc_name' 			=> $customerInfo['firstname'].' '.$customerInfo['lastname'],
									'cc_num' 			=> $customerInfo['cc_number'],
									'cc_exp_month' 		=> date('m',strtotime($customerInfo['cc_exp_month'])),
									'cc_exp_year' 		=> $customerInfo['cc_exp_year'],
									'invoice_ref' 		=> '',
									'invoice_desc' 		=> 'JimmyData Subscription Payment',
									'init_amt' 			=> 0,
									'init_date' 		=> date('d/m/Y'),
									'recurring_amt' 	=> $customerInfo['recurr_amount']*100,
									'start_date' 		=> $this->_getSubscriptionStartDate(),
									'interval' 			=> $this->_period,
									'interval_type' 	=> $this->_frequency,
									'end_date' 		 	=> $this->_getSubscriptionEndDate($customerInfo['cc_exp_year'],$customerInfo['cc_exp_month'])
						);
		 # Create A Recurring Event for the Customer
		 $this->recurringApiResponse = $this->getRecurringPaymentApi()->createEvent($rebillEventInfo);

		if(!$this->recurringApiResponse->RebillID){
			# Delete the Customer if rebillEvent couldnot be created
			$deleteCustomerResult = $this->getRecurringPaymentApi()->deleteCustomer($this->recurringApiResponse->RebillCustomerID);
			return false;
		}

		return true;
	}


	public function setupTokenPayment($data, $user){

		$customerInfo = $data;

		$user_service = $this->getServiceManager()->get('jimmybase_user_service');

	  	if(!$customerInfo)
		    throw new \Exception("Customer info not provided.");

		$this->tokenApiResponse = $this->getTokenPaymentApi()->createCustomer($customerInfo); # Create Customer Token in eWay
		// Check if customer was returned
		if($this->tokenApiResponse['success']) {
			$data = $this->tokenApiResponse['data'];
			// Save customer card meta in database
			$this->saveUserCardMeta(
				$user,
				$data->Customer->CardDetails->Number,
				$data->Customer->CardDetails->ExpiryMonth,
				$data->Customer->CardDetails->ExpiryYear+2000
				);
			// Save customer token id in database		
			$this->saveUserTokenIDMeta(
				$user,
				$data->Customer->TokenCustomerID
				);
			// Update user's package
			$this->updateCustomerPackage(
				$user
				);
			// Set next payment date
			$this->setNextPaymentDate(
				$user
				);
		}
		
		return $this->tokenApiResponse;
	}

	public function updateCustomerPackage($user) {
		$user_service = $this->getServiceManager()->get('jimmybase_user_service');
		$package = $user_service->getPackage($user);
		// Check if user is in 'Trial Package'
		if($package->getId()==5) {
			// Change Package to 'Pay As You Go'
			$user->setKey('package');
			$user->setValue(14);
			$user_service->saveMeta($user);
		}
	}

	public function processTokenPayment($user) {

		$payment_service = $this->getServiceManager()->get('jimmybase_payment_service');
		$user_service = $this->getServiceManager()->get('jimmybase_user_service');

		$user_mapper = $this->getServiceManager()->get('jimmybase_user_mapper');
		$package_mapper = $this->getServiceManager()->get('jimmybase_package_mapper');
		$reports_mapper = $this->getServiceManager()->get('jimmybase_reports_mapper');

		if(!$user)
			throw new \Exception("Customer info not provided.");
		//Find token ID
		$tokenID = $user_mapper->getMeta($user->getId(), 'eway_token_id');

		// Calculate amount to be paid.

		// Identify user's package
		$package = $user_service->getPackage($user);

		$amount = 0;

		// Check if user is on pay as you go package
		if($package->getId()==14) {
			// If true calculate amount from the number of paid reports and package price
			$paid_reports_count = $reports_mapper->getPaidCount($user->getId());
			$amount = $package->getPrice() * $paid_reports_count;
		} else {
			// else amount is the price of the package
			$amount = $package->getPrice();
		}
		
		$current_pending_amount = $this->getPendingAmount($user);
		$total_amount = $current_pending_amount + $amount;
		$total_amount*=100;

		if(!$total_amount) {
			$this->setNextPaymentDate($user);
			throw new \Exception('No charges to be billed yet.');
		}

		if($total_amount < 100) {
			// Save the pending amount to database
			$this->setPendingAmount($user, $total_amount);
			// Update the billing date.
			$this->setNextPaymentDate($user);
			throw new \Exception('Amount too low to process. The amount will be billed in the next billing date.');
		}

		$this->tokenApiResponse = $this->getTokenPaymentApi()->processPayment($user, $tokenID, $total_amount);
		$data = $this->tokenApiResponse['data'];
		// If transaction was completed
		if($this->tokenApiResponse['success']) {
			// Setuo an invoice
			$invoice = new UserPayments();

			$invoice->setUserId($user->getId());
			$invoice->setAmount($data->Payment->TotalAmount/100);
			$invoice->setStatus($data->TransactionStatus?'Success':'Failed');
			$invoice->setTransId($data->TransactionID);
			$invoice->setCurrency($data->Payment->CurrencyCode);
			$invoice->setProcessor('eWay');
			$invoice->setComments($data->ResponseCode.','.$data->ResponseMessage);
			$invoice->setDate(date('Y-m-d H:i:s'));

			// Save invoice to database
			$this->getUserPaymentsMapper()->insert($invoice);

			// Set next payment date
			$this->setNextPaymentDate(
				$user
				);

			// Clear any pending amount.
			if($pendingAmount>0)
				$this->setPendingAmount($user, 0);

			// Add invoice ID for future references
			$this->tokenApiResponse['invoice'] = $invoice->getId();

		}

		return $this->tokenApiResponse;

	}


	public function setupUpgradedPackage($customerInfo,$rebillInfo){

	  	if(!$customerInfo or !$rebillInfo)
		    return false;  // If customer info not provided return early

		$rebillEventInfo	= array('customer_id'		=> $customerInfo['eway_customer_id'],
									'rebill_id'			=> $customerInfo['eway_rebill_id'],
									'cc_name' 			=> $customerInfo['firstname'].' '.$customerInfo['lastname'],
									'cc_num' 			=> $customerInfo['cc_number'],
									'cc_exp_month' 		=> date('m',strtotime($customerInfo['cc_exp_month'])),
									'cc_exp_year' 		=> $customerInfo['cc_exp_year'],
									'invoice_ref' 		=> '',
									'invoice_desc' 		=> 'JimmyData Subscription Payment',
									'init_amt' 			=> $rebillInfo->RebillInitAmt,
									'init_date' 		=> $rebillInfo->RebillInitDate,
									'recurring_amt' 	=> $customerInfo['recurr_amount']*100,
									'start_date' 		=> $this->_getSubscriptionStartDate(),
									'interval' 			=> $this->_period,
									'interval_type' 	=> $this->_frequency,
									'end_date' 		 	=> $this->_getSubscriptionEndDate($customerInfo['cc_exp_year'],$customerInfo['cc_exp_month'])
						);

		# Create A Recurring Event for the Customer
		$this->recurringApiResponse = $this->getRecurringPaymentApi()->updateEvent($rebillEventInfo);

		return true;
	}


	public function updatePaymentDetails($rebillInfo){
		if(!$rebillInfo)
		    return false;  // If customer info not provided return early

		$rebillEventInfo	= array('customer_id'		=> $rebillInfo['eway_customer_id'],
									'rebill_id'			=> $rebillInfo['eway_rebill_id'],
									'cc_name' 			=> $customerInfo['firstname'].' '.$customerInfo['lastname'],
									'cc_num' 			=> $customerInfo['cc_number'],
									'cc_exp_month' 		=> date('m',strtotime($customerInfo['cc_exp_month'])),
									'cc_exp_year' 		=> $customerInfo['cc_exp_year'],
									'invoice_ref' 		=> '',
									'invoice_desc' 		=> 'JimmyData Subscription Payment',
									'init_amt' 			=> $rebillInfo->RebillInitAmt,
									'init_date' 		=> $rebillInfo->RebillInitDate,
									'recurring_amt' 	=> $customerInfo['recurr_amount'],
									'start_date' 		=> date('d/m/Y',strtotime('+1 month')),
									'interval' 			=> $this->_period,
									'interval_type' 	=> $this->_frequency,
									'end_date' 		 	=> date('d/m/Y',strtotime(date('d').'-'.$customerInfo['cc_exp_month'].'-'.$customerInfo['cc_exp_year']))
						);
		# Create A Recurring Event for the Customer
		$this->recurringApiResponse = $this->getRecurringPaymentApi()->updateEvent($rebillEventInfo);

		return true;
	}

	public function doDirectPayment($customerInfo){

		$directpayment_api = $this->getDirectPaymentApi();
		if(!$customerInfo)
		    return false;


		$directPaymentInfo 	= array('TotalAmount'		    		=> $customerInfo['oneoff_amount']*100,
									'CustomerFirstName'		    	=> $customerInfo['firstname'],
									'CustomerLastName'		    	=> $customerInfo['lastname'],
									'CustomerEmail'		    		=> $customerInfo['email'],
									'CustomerAddress'		    	=> null,
									'CustomerPostcode'		    	=> '',
									'CardHoldersName'				=> $customerInfo['firstname'].' '.$customerInfo['lastname'],
									'CardNumber' 					=> $customerInfo['cc_number'],
									'CardExpiryMonth' 				=> $customerInfo['cc_exp_month'],
									'CardExpiryYear' 				=> $customerInfo['cc_exp_year'],
									'TrxnNumber' 					=> '',
									'CustomerInvoiceRef' 			=> '',
									'CustomerInvoiceDescription' 	=> $customerInfo['inv_desc'],
									'Option1' 						=> '',
									'Option2' 						=> '',
									'Option3' 						=> '',
									'CVN'							=> $customerInfo['cc_ccv'],
									'CustomerIPAddress'				=> $directpayment_api->getVisitorIP(),
									'CustomerBillingCountry'		=> ''
							);



		$this->directPaymentApiResponse = $directpayment_api->doPayment($directPaymentInfo,$directpayment_api::REAL_TIME_CVN);

		if($this->directPaymentApiResponse->status == 'True')
			return true;
		else
			return false;

	}


	public function queryRecurringPayment($user,$date_from,$date_to){

		$user_payments_mapper   = $this->getUserPaymentsMapper();
		$package_mapper			= $this->getPackageMapper();
		$user_mapper   			= $this->getUserMapper();



		$customerId        = $user_mapper->getMeta($user->getId(),'eway_customer_id');
		$subscription_id   = $user_mapper->getMeta($user->getId(),'eway_rebill_id');


		$recurringPaymentApi = $this->getRecurringPaymentApi();

		$trans_data = array('customer_id'	=>	$customerId,
								'rebill_id'		=>  $subscription_id,
								'start_date'	=>  $date_from,
								'end_date'		=>  $date_to
							);

		$transaction = $recurringPaymentApi->queryTransaction($trans_data);


		if(!$transaction)
			return false;

			$user_service = $this->getServiceManager()->get('jimmybase_user_service');

		if($transaction->status == 'Successful'){

			$this->getEventManager()->trigger('userInvoice.success', $this, array(  'user'     		   => $user,
																	   				'paymentResponse'  => $transaction,
																	   	    		'currency'		   => $this->_currency ));


			# Save the next query date package in the user meta
			$user->setKey('next_payment_date');
			$user->setValue($this->_getNextPaymentDate());

			$user_service->saveMeta($user);

		} else if(in_array($transaction->status,array('Future','Initial'))){

			# Save the next query date package in the user meta
			$user->setKey('next_payment_date');
			$user->setValue(date('Y-m-d',strtotime($transaction->date)));
			$user_service->saveMeta($user);

			return true;
		} else if($transaction->status == 'Failed'){
			$this->getEventManager()->trigger('userInvoice.failure', $this, array( 'user'     	    => $user,
																	   		 	   'paymentResponse' => $transaction,
																	   	     	   'currency'	    => $this->_currency ));

			# Change  the Status to -1
			# Status Code  0  = Inactive
			# Status Code  1  = Payment Failed
			$user->setState(2);
			$user_mapper->update($user);

			# Delete the next payment date
			$user->setKey('next_payment_date');
			$user->setValue($this->_getNextPaymentDate());

			$user_service->saveMeta($user);

		} else
		  return false;



		$user_payment = new UserPayments();

		$user_payment->setUserId($user->getId());
		$user_payment->setAmount($transaction->amount?$transaction->amount:0);
		$user_payment->setStatus($transaction->status == 'Successful'?'Success':$transaction->status);	# Status Code  True = Success , Successfull = Success
		$user_payment->setCurrency($this->_currency);
		$user_payment->setDate(date('Y-m-d h:i:s'));
		$user_payment->setProcessor($this->_processor);
		$user_payment->setTransId($transaction->trans_id?$transaction->trans_id:0);
		$user_payment->setComments($transaction->comment?$transaction->comment:'');

		return $this->getUserPaymentsMapper()->insert($user_payment);

	}


	public function queryRecurringEvent($user_id){
		$user_mapper   			= $this->getUserMapper();
		$user_payments_mapper   = $this->getUserPaymentsMapper();
		$package_mapper			= $this->getPackageMapper();


		$user 	       			= $user_mapper->setUserType('agency')
									 		  ->findById($user_id);

		$customerId        = $user_mapper->getMeta($user->getId(),'eway_customer_id');
		$subscription_id   = $user_mapper->getMeta($user->getId(),'eway_rebill_id');


		$recurringPaymentApi = $this->getRecurringPaymentApi();

		$this->recurringApiResponse = $recurringPaymentApi->queryEvent($customerId,$subscription_id);


		return $this->recurringApiResponse;
	}


	public function cancelRecurringCustomer($user,$customerId,$subscriptionId){
		$user_mapper   			= $this->getUserMapper();
		$package_mapper			= $this->getPackageMapper();


		$recurringPaymentApi = $this->getRecurringPaymentApi();


		$this->recurringApiResponse = $recurringPaymentApi->deleteEvent($customerId,$subscriptionId);

		if($this->recurringApiResponse->Result=='Success'){
		   $this->recurringApiResponse = $recurringPaymentApi->deleteCustomer($customerId);

			if($this->recurringApiResponse->Result=='Success')
				return true;
		}

		return false;
	}

	public function saveUser($customerInfo,$rebillCustomerId,$rebillEventId){
			// Create User Here
			$user_service = $this->getServiceManager()->get('jimmybase_user_service');

			$userInfo     = array(
								   'name'					=> $customerInfo['firstname'].' '.$customerInfo['lastname'],
								   'email' 					=> $customerInfo['email'],
								   'password' 				=> '',
								   'state'					=> 1,
								   'type'					=> 'agency',
								 );


			$user    = $user_service->save($userInfo);

		return $user;
	}



	public function saveUserMeta($user,$packageId,$rebillCustomerId,$rebillEventId){

			if(!$user)
			    return false;

		try{
			$user_service = $this->getServiceManager()->get('jimmybase_user_service');
			# Save the eway customer id in the user meta table
			$user->setKey('eway_customer_id');
			$user->setValue($rebillCustomerId);
			$user_service->saveMeta($user);

			# Save the eway customer rebill id in the user meta
			$user->setKey('eway_rebill_id');
			$user->setValue($rebillEventId);
			$user_service->saveMeta($user);

			# Save the eway package in the user meta
			$user->setKey('package');
			$user->setValue($packageId);
			$user_service->saveMeta($user);

			# Save the next query date package in the user meta
			$user->setKey('next_payment_date');
			$user->setValue($this->_getNextPaymentDate());
			$user_service->saveMeta($user);

			return true;

		} catch(\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * Saves the credit card information to Meta Table
	 * @param $user Current user
	 * @param $ccNumber Masked credit card number
	 * @param $expMonth Month of cards expiry in MM format
	 * @param $expYear Year of cards expiry in YYYY format
	 * @return Whether success or not
	 **/
	public function saveUserCardMeta($user, $ccNumber, $expMonth, $expYear) {
		if(!$user)
			return false;

		try {
			$user_service = $this->getServiceManager()->get('jimmybase_user_service');
			// Saving credit card number
			$user->setKey('credit_card_number');
			$user->setValue($ccNumber);
			$user_service->saveMeta($user);

			// Saving expiry month in the user meta table
			$user->setKey('credit_card_expiration_month');
			$user->setValue($expMonth);
			$user_service->saveMeta($user);


			// Saving expiry year in the user meta table
			$user->setKey('credit_card_expiration_year');
			$user->setValue($expYear);
			$user_service->saveMeta($user);

			return true;

		} catch(\Exception $e) {
			throw new Exception($e->getMessage());
			
		}
	}

	public function saveUserTokenIDMeta($user, $token) {
		if(!$user)
			return false;

		try {
			$user_service = $this->getServiceManager()->get('jimmybase_user_service');

			// Save user payment token information
			$user->setKey('eway_token_id');
			$user->setValue($token);
			$user_service->saveMeta($user);

			return true;
		} catch (\Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public function saveUserPackageMeta($user,$packageId){

			if(!$user)
			    return false;

		try{
			$user_service = $this->getServiceManager()->get('jimmybase_user_service');

			# Save the eway package in the user meta
			$user->setKey('package');
			$user->setValue($packageId);
			$user_service->saveMeta($user);

			# Save the next query date package in the user meta
			$user->setKey('next_payment_date');

			$user->setValue($this->_getNextPaymentDate());
			$user_service->saveMeta($user);

			return true;

		} catch(\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function saveUserPayment($transaction){

		if(!$transaction) return false;


		$user_payment = new UserPayments();

		$user_payment->setUserId($this->userId);
		$user_payment->setAmount($transaction->amount?$transaction->amount:0);
		$user_payment->setStatus($transaction->status == 'True'?'Success':'Failed');
		$user_payment->setCurrency($this->_currency);
		$user_payment->setDate(date('Y-m-d h:i:s'));
		$user_payment->setProcessor($this->_processor);
		$user_payment->setTransId($transaction->trans_id?$transaction->trans_id:0);
		$user_payment->setComments($transaction->comment?$transaction->comment:0);

		return $this->getUserPaymentsMapper()->insert($user_payment);
	}





    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceManager()->get('jimmybase_user_mapper');
        }
        return $this->userMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setUserMapper(UserMapperInterface $userMapper)
    {
        $this->userMapper = $userMapper;
        return $this;
    }

    /**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getUserPaymentsMapper()
    {
        if (null === $this->userpaymentsMapper) {
            $this->userpaymentsMapper = $this->getServiceManager()->get('jimmybase_userpayments_mapper');
        }
        return $this->userpaymentsMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setUserPaymentsMapper(UserPaymentsMapperInterface $userpaymentsMapper)
    {
        $this->userpaymentsMapper = $userpaymentsMapper;
        return $this;
    }


	/**
     * getUserMapper
     *
     * @return UserMapperInterface
     */
    public function getPackageMapper()
    {
        if (null === $this->packageMapper) {
            $this->packageMapper = $this->getServiceManager()->get('jimmybase_package_mapper');
        }
        return $this->packageMapper;
    }

    /**
     * setUserMapper
     *
     * @param UserMapperInterface $userMapper
     * @return User
     */
    public function setPackageMapper(PackageMapperInterface $packageMapper)
    {
        $this->packageMapper = $packageMapper;
        return $this;
    }



    public function getRecurringPaymentApi(){

          return $this->getServiceManager()->get('eway_recurring_api');
    }



    public function getDirectPaymentApi(){

          return $this->getServiceManager()->get('eway_directpayment_api');
    }



    public function getTokenPaymentApi(){

    	return $this->getServiceManager()->get('eway_token_payment_api');
    }



    protected function _getNextPaymentDate(){

        switch($this->_frequency){
				case 1:
					 $frequency = 'days';
					break;
				case 2:
					 $frequency = 'weeks';
					 break;
				case 3:
					 $frequency = 'month';
					 break;
				case 4:
					 $frequency = 'years';
					 break;

		 }

	 return date('Y-m-d',strtotime("+{$this->_period} {$frequency}"));

   }


   protected function _getSubscriptionStartDate(){

        switch($this->_frequency){
				case 1:
					 $frequency = 'days';
					break;
				case 2:
					 $frequency = 'weeks';
					 break;
				case 3:
					 $frequency = 'month';
					 break;
				case 4:
					 $frequency = 'years';
					 break;
		 }

	 return date('d/m/Y',strtotime("+{$this->_period} {$frequency}"));

   }


   protected function _getDailyPrice($price){

   		$days = $this->_getTotalDaysInPeriod();

        switch($this->_frequency){
			case 1: // day
				return $price  / $days;
			case 2: // week
				return $price  / $days;
			case 3: // month
				return $price  / $days;
			case 4: // year
				return $price  / $days;
		}

   }


   protected function _getTotalDaysInPeriod(){

   	 	switch($this->_frequency){
			case 1: // day
				return  $this->_period;
			case 2: // week
				return $this->_period*7;
			case 3: // month
				return $this->_period*30;
			case 4: // year
				return $this->_period*365;
		 }

   }


   protected function _getSubscriptionEndDate($cc_exp_year,$cc_exp_month){

		return date('d/m/Y',strtotime(date('d').'-'.$cc_exp_month.'-'.$cc_exp_year));
		//return date('d/m/Y',strtotime('+7 days'));
   }



	/*
	 * Sets the configurations
	 */
	public function setConfig($config){

		if($config)
			$this->_config = $config;

		return $this;
	}


	/*
	 * Retrieves the configurations
	 */
	public function getConfig(){

		if($this->_config)
			return $this->_config;

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
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    public function setNextPaymentDate($user) {
    	$user_service = $this->getServiceManager()->get('jimmybase_user_service');
    	$user->setKey('next_payment_date');
    	$user->setValue(date('Y-m-d', strtotime('+30 days')));
    	$user_service->saveMeta($user);
    }

   	public function setPendingAmount($user, $amount) {
   		$user_service = $this->getServiceManager()->get('jimmybase_user_service');
   		$user->setKey('pending_amount');
   		$user->setValue($aount);
   		$user_service->saveMeta($user);
   	}

   	public function getPendingAmount($user) {
   		try {
	   		$user_mapper = $this->getServiceManager()->get('jimmybase_user_mapper');
	   		$pending_amount = $user_mapper->getMeta($user->getId(), 'pending_amount');
	   		if(!$pending_amount || $pending_amount == null)
	   			$pending_amount = 0;
	   		return $pending_amount;			
   		} catch (\Exception $e) {
   			return 0;
   		}
   	}
}
