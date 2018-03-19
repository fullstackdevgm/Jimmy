<?php

namespace JimmyBase\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use Braintree\Configuration as BtConfig;
use Braintree\ClientToken as BtToken;
use Braintree\Customer;
use Braintree\Subscription;
use Braintree\Transaction;
use Braintree\TransactionSearch;
use Jimmybase\Entity\BraintreePayment as BtEntity;

class BraintreePayment extends EventProvider implements ServiceManagerAwareInterface
{
   private $user;

   const PACKAGE_UNLIMITED = 13;
   const PACKAGE_PAY_AS_YOU_GO = 14;
   const PACKAGE_TRIAL = 5;
   const PACKAGE_NEW_TRIAL = 15;
   const PACKAGE_14_DAY_TRIAL = 16;
   const PACKAGE_GRG = 11;

   const PLAN_UNLIMITED = "unlimitedReports";
   const PLAN_PAY_AS_YOU_GO = "payPerReport";
   const PLAN_GRG = "grg";

    /**
     * To initalise braintre config.
     */
    public function __construct($config,$env)
    {

      $bt_settings = $this->braintreeSettings = $config[$env];
      BtConfig::environment($env);
      BtConfig::merchantId($bt_settings['merchant_id']);
      BtConfig::publicKey($bt_settings['public_key']);
      BtConfig::privateKey($bt_settings['private_key']);
    }

    /**
     * Generate token for the initalize transaction.
     */
    public function getToken()
    {
         $token =  BtToken::generate();
         return $token;
    }


    /**
     * Create a Customer.
     */
    public function createCustomer($userEntity,$userInfo)
    {
        $userEntity = $this->getLoggedInUser($userEntity);

        $userData = $userInfo->userInfo;

        $firstname = $userData->firstname;
        $lastname = $userData->lastname;
        $address = $userData->streetaddress;
        $region = $userData->state;
        $postalCode = $userData->postalCode;
        $country = $userData->country;
        $userNameArr = explode(" ",$userEntity->getName());
        foreach($userNameArr as $i => $un) {
            if ($i == 0) {
                $userFName = $un;
            } else {
                $userLName .= $un;

            }
        }
        $requestData = array(
                'creditCard' => array(
                    'billingAddress' => array(
                        'countryCodeAlpha2' => $country,
                        'firstName' => $firstname,
                        'lastName' => $lastname,
                        'locality' => $region,
                        'streetAddress' => $address,
                        'postalCode' => $postalCode,
                        ),
                    'options' => array(
                        'verifyCard' => true,
                        ),
                    'cardholderName' => $firstname.' '.$lastname,
                    ),
                'email' => $userEntity->getEmail(),
                'paymentMethodNonce' => $userInfo->nonce,
                'firstName' => $userFName,
                'lastName' => $userLName,
            );

        $result = Customer::create($requestData);
        
        if ($result->success) {
            $customerId = $result->customer->id;
            $subResult = $this->createSubscription($userEntity, $customerId, $result->customer->paymentMethods[0]->token);

            if (!!$subResult) {
                   // Save customer card details to database
                   $this->saveUserCardMeta(
                        $userEntity,
                        $result->customer->creditCards[0]->bin.'XXXXXX'.$result->customer->creditCards[0]->last4,
                        $result->customer->creditCards[0]->expirationMonth,
                        $result->customer->creditCards[0]->expirationYear
                        );

            }



            return $subResult;

        } else {
            foreach ($result->errors->deepAll() as $errors) {             
                $message .= $errors->code. ": ". $errors->message. "\n";
                throw new \Exception($message);
            }
            return true;
        }

    }

    public function getSubscriptionStatus($userEntity) {
      $userEntity = $this->getLoggedInUser($userEntity);
      $mapper = $this->getBraintreePaymentMapper();
      $btEntity = $mapper->findByUser($userEntity->getId());

      $subscription = Subscription::find($btEntity->getSubscriptionId());

      return $subscription;
    }

    public function checkSubscriptionStatus($userEntity) {
      $userEntity = $this->getLoggedInUser($userEntity);
      $mapper = $this->getBraintreePaymentMapper();
      $btEntity = $mapper->findByUser($userEntity->getId());

      $subscription = Subscription::find($btEntity->getSubscriptionId());


      $btEntity->setStatus($subscription->status);

      switch ($subscription->status) {
        case Subscription::PENDING:
        case Subscription::ACTIVE:
          return true;
          break;
        default:
          return false;
          break;
      }

      return false;
    }

    /**
     *
     * @param type $user
     * @param string $nonce
     */
    public function updateCustomer($userEntity, $userInfo)
    {

        $userEntity = $this->getLoggedInUser($userEntity);

        $userData = $userInfo->userInfo;

        $firstname = $userData->firstname;
        $lastname = $userData->lastname;
        $address = $userData->streetaddress;
        $region = $userData->state;
        $postalCode = $userData->postalCode;
        $country = $userData->country;
        $userNameArr = explode(" ",$userEntity->getName());
        foreach($userNameArr as $i => $un) {
            if ($i == 0) {
                $userFName = $un;
            } else {
                $userLName .= $un;

            }
        }
        $requestData = array(
                'creditCard' => array(
                    'billingAddress' => array(
                        'countryCodeAlpha2' => $country,
                        'firstName' => $firstname,
                        'lastName' => $lastname,
                        'locality' => $region,
                        'streetAddress' => $address,
                        'postalCode' => $postalCode,
                        'options' => array(
                            'updateExisting' => true
                            ),
                    ),
                    'options' => array(
                        'verifyCard' => true,
                        'makeDefault' => true,
                    ),
                    'cardholderName' => $firstname.' '.$lastname,
                ),
                'email' => $userEntity->getEmail(),
                'paymentMethodNonce' => $userInfo->nonce,
                'firstName' => $userFName,
                'lastName' => $userLName,

            );

        $mapper = $this->getBraintreePaymentMapper();
        $btEntity = $mapper->findByUser($userEntity->getId());

        $customerId = $btEntity->getCustomerId();
        $subscriptionId = $btEntity->getSubscriptionId();

        $result = Customer::update($customerId, $requestData);     
        
        if($result->success) {
                $paymentToken = $result->customer->creditCards[0]->token;
                $subUpdate = Subscription::update(
                $subscriptionId, [
                    'id' => $subscriptionId,
                    'paymentMethodToken' => $paymentToken,
                    
                ]);
              
           if ($subUpdate->success) {
               $subStatus = $subUpdate->subscription->status;
               $balanceAmount = $subUpdate->subscription->balance;
               $btEntity->setStatus($subStatus);
               if ($subStatus == Subscription::PAST_DUE) {                  
                   $retryResult = Subscription::retryCharge(
                        $subscriptionId,
                        $balanceAmount
                    );
                    if ($retryResult->success) {
                        $submitRetry = Transaction::submitForSettlement(
                            $retryResult->transaction->id
                        );
                        if (!$submitRetry->success) {                          
                            $this->fetchErrors($submitRetry);
                            return false;
                        }
                    } else {                         
                         $this->fetchErrors($retryResult);
                        return false;
                        
                    }
                    $subscription = Subscription::find($subscriptionId);
                    $btEntity->setStatus($subscription->status);
               }               
               $mapper->update($btEntity);
              
          // Save customer card details to database
                $this->saveUserCardMeta(
                    $userEntity,
                    $result->customer->creditCards[0]->bin.'XXXXXX'.$result->customer->creditCards[0]->last4,
                    $result->customer->creditCards[0]->expirationMonth,
                    $result->customer->creditCards[0]->expirationYear
                );
                return true;
           } else {              
                $this->fetchErrors($subUpdate);
                return false;
           }
        } else {
            $this->fetchErrors($result);
            return false;
        }

    }
    
    /**
     * Throw all errors.
     * @param Array $result
     * @throws \Exception
     */
    public function fetchErrors($result) {
        foreach ($result->errors->deepAll() as $errors) {
                $message .= $errors->code. ": ". $errors->message. "\n";
                throw new \Exception($message);
         }
    }
    /**
     * Create a Subscription for a customer.
     *
     * @param type $customerToken
     * @return type
     */
    public function createSubscription($user, $customerId, $customerToken)
    {

        $user = $this->getLoggedInUser($user);

        $userPackage = $this->getServiceManager()
                                    ->get('jimmybase_user_mapper')
                                    ->getMeta($user->getId(), 'package');
        $requestData = array();
        $planId = "";
         // update subscription with number of reports as soon as user subscribes
        $reports_mapper = $this->getServiceManager()->get('jimmybase_reports_mapper');
        $templates = $reports_mapper->findByAgency($user->getId());

        if ($templates->count() <= 0) {
            throw new \Exception("You have to create Atleast one report");
        }

        $nextPaymentDate = $this->getServiceManager()->get('jimmybase_user_mapper')
                            ->getMeta($user->getId(), 'next_payment_date');
        $today = new \DateTime();
        $nextDate = new \DateTime($nextPaymentDate);
        
        if ($nextDate->diff($today)->format('%R%a days') > 0) {
             $newDate = new \DateTime('tomorrow');
             $nextPaymentDate = $newDate->format('Y/m/d');
        }
       
        
        
        if(is_null($nextPaymentDate)) {
          $newDate = new \DateTime('tomorrow');
          $nextPaymentDate = $newDate->format('Y/m/d');
        }
     
        $restOfPackages = array(
                                  self::PACKAGE_TRIAL,
                                  self::PACKAGE_NEW_TRIAL, 
                                  self::PACKAGE_14_DAY_TRIAL,
                                  self::PACKAGE_PAY_AS_YOU_GO
                                  
                                );
   
        if(in_array($userPackage, $restOfPackages)) {

          $requestData = array(
            'paymentMethodToken' => $customerToken,
            'planId' => self::PLAN_PAY_AS_YOU_GO,
            'addOns' => array(
              'add' => array(
                array(
                  'inheritedFromId' => 'report',
                  'quantity' => $templates->count()
                  )
                ),
              ),
            );
        

        } else if($userPackage==self::PACKAGE_UNLIMITED) {            
             $requestData = array(
            'paymentMethodToken' => $customerToken,
            'planId' => self::PLAN_UNLIMITED,
            'firstBillingDate' => new \DateTime(date("Y-m-d", strtotime($nextPaymentDate)))
            );
        } else if ($userPackage == self::PACKAGE_GRG) {
            $requestData = array(
            'paymentMethodToken' => $customerToken,
            'planId' => self::PLAN_GRG,
            'firstBillingDate' => new \DateTime(date("Y-m-d", strtotime($nextPaymentDate)))
            );
        }

 
       
        $subResult = Subscription::create($requestData);
      
        if ($subResult->success) {           
            $mapper = $this->getBraintreePaymentMapper();
            $btEntity = new BtEntity();
            $btEntity->setUserId($user->getId());
            $btEntity->setCustomerId($customerId);
            $btEntity->setSubscriptionId($subResult->subscription->id);
            $btEntity->setStatus($subResult->subscription->status);
            $mapper->insert($btEntity);
           
            $this->setNextPaymentDate($user, $subResult->subscription->nextBillingDate->format('Y-m-d'));
            if(in_array($userPackage, array(
                                      self::PACKAGE_TRIAL,
                                      self::PACKAGE_NEW_TRIAL,                                 
                                      self::PACKAGE_PAY_AS_YOU_GO,
                                      self::PACKAGE_14_DAY_TRIAL)
                                    )) {
                $this->saveUserPackageMeta(
                  $user,
                  self::PACKAGE_PAY_AS_YOU_GO
                );
            } else if($userPackage==self::PACKAGE_UNLIMITED) {
                $this->saveUserPackageMeta(
                    $user,
                    self::PACKAGE_UNLIMITED
                );
            } else if ($userPackage == self::PACKAGE_GRG) {
                $this->saveUserPackageMeta(
                    $user,
                    self::PACKAGE_GRG
                );
            }


            return true;

        } else {
            throw new \Exception($subResult->message);
        }
    }

    /**
     * Update a subscription. Add or remove reports.
     *
     * @param string $subscriptionId
     */
    public function updateSubscription ($user, $quantity = null)
    {
        $user = $this->getLoggedInUser($user);

        $mapper = $this->getBraintreePaymentMapper();
        $btEntity = $mapper->findByUser($user->getId());
        
        if(!$btEntity) {
          throw new \Exception('No Braintree Subscription');
        }
        $subscriptionId = $btEntity->getSubscriptionId();
        $subscription = Subscription::find($subscriptionId);
        if ($subscription->planId == self::PLAN_PAY_AS_YOU_GO) {
            if ($quantity == 0) {
                $result = Subscription::update(
                            $subscriptionId, [
                                'addOns' => [
                                    'remove' => 'report'
                                ]
                            ]
                          );
            } else if(count($subscription->addOns) == 0) {
                $result = Subscription::update(
                            $subscriptionId, [
                                'addOns' => [
                                    'add' => [
                                        [
                                            'inheritedFromId' => 'report',
                                            'quantity' => $quantity
                                        ]
                                    ]
                                ]
                            ]
                           );
            } else {
                $result = Subscription::update(
                            $subscriptionId,  [
                                'addOns' => [
                                    'update' => [
                                        [
                                            'existingId' => 'report',
                                            'quantity' => $quantity
                                        ]
                                    ]
                                ]
                            ]
                          );
            }
            if ($result->success) {
                return true;
            } else {
                throw new \Exception($result->message);
            }
        } else if($subscription->planId == self::PLAN_UNLIMITED) {
            // Subscription update for unlimited reports
            return true;
        } else if($subscription->planId == self::PLAN_GRG) {
            // Subscription update for unlimited reports
            return true;
        } else {
            // check for user's package
            throw new \Exception('Invalid Package');
        }

    }

    /**
     * Cancel an subscription.
     * @param string $subscriptionId
     */
    public function cancelSubscription ($user)
    {
        $user = $this->getLoggedInUser($user);

        $mapper = $this->getBraintreePaymentMapper();
        $btEntity = $mapper->findByUser($user->getId());
        $result = Subscription::cancel($btEntity->getSubscriptionId());
      
        if ($result->success) {
           $btEntity->setStatus($result->subscription->status);
           $mapper->update($btEntity);
           return true;
        } else {            
           throw new \Exception($result->message);
          
        }
    }


     /**
     * getTemplateMapper
     *
     * @return templateMapperInterface
     */
    public function getBraintreePaymentMapper()
    {
        return $this->getServiceManager()->get('jimmybase_braintree_payment_mapper');

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
            throw new \Exception('User not found. Please log in again.');

        $user = $this->getLoggedInUser($user);

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
            throw new \Exception($e->getMessage());

        }
    }

    public function saveUserPackageMeta($user,$packageId){

            if(!$user)
                throw new \Exception('User not found. Please log in again.');

        try{
            $user_service = $this->getServiceManager()->get('jimmybase_user_service');

            # Save the eway package in the user meta
            $user->setKey('package');
            $user->setValue($packageId);
            $user_service->saveMeta($user);

            return true;

        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function setNextPaymentDate($user, $date) {
        $user_service = $this->getServiceManager()->get('jimmybase_user_service');

        $user->setKey('next_payment_date');
        $user->setValue($date);
        $user_service->saveMeta($user);

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

   public function updateCustomerPackage($user) {
        $user_service = $this->getServiceManager()->get('jimmybase_user_service');
        $package = $user_service->getPackage($user);
        // Check if user is in 'Trial Package'
        if(in_array($package->getId(), array(self::PACKAGE_TRIAL, self::PACKAGE_NEW_TRIAL))) {
            // Change Package to 'Pay As You Go'
            $user->setKey('package');
            $user->setValue(self::PACKAGE_PAY_AS_YOU_GO);
            $user_service->saveMeta($user);
        }
    }

    public function getInvoice($user) {

      $user = $this->getLoggedInUser($user);

      $mapper = $this->getBraintreePaymentMapper();
      $btEntity = $mapper->findByUser($user->getId());
      $results = Transaction::search([
        TransactionSearch::customerId()->is($btEntity->getCustomerId())
        ]);
      $invoices = array();
      foreach ($results as $result) {
        $invoice = array(
          'date' => $result->createdAt,
          'transaction_id' => $result->id,
          'status' => $result->status,
          'amount' => $result->amount,
          );
        array_push($invoices, $invoice);
      }
      return $invoices;
    }

    /**
     * To get an Invoice by ID.
     * @return array of Invoice Information
     */
    public function getInvoiceById($transaction_id) {
      $invoice = array();
      $results = Transaction::search([
        TransactionSearch::id()->is($transaction_id),
        ]);
      return $results;
    }

    /**
     * To get the currentLoggedInuser;
     *
     * @return Entity\User
     *
     */
    public function getLoggedInUser($userEntity)
    {
      $currentUserId = $userEntity->getId();
         if ($userEntity->getType()=='coworker') {
               $currentUserId = $this->getServiceManager()
                                       ->get('jimmybase_user_mapper')
                                       ->getMeta($currentUserId,'parent');
          }
     $user = $this->getServiceManager()
                   ->get('jimmybase_user_mapper')
                   ->findById($currentUserId);
     return $user;
    }

}
