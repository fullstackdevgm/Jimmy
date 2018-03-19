<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventInterface;
use Braintree\Subscription;
use Zend\Session\Container as SessionContainer;

use JimmyBase\Entity\ClientAccounts;

class DashboardApiController extends AbstractRestfulController
{


    public function getList()
    {

		$current_user = $this->zfcUserAuthentication()->getIdentity();
		$user_mapper  = $this->getServiceLocator()->get('jimmybase_user_mapper');
		$widget_mapper= $this->getServiceLocator()->get('jimmybase_widget_mapper');
		$user_payments_mapper = $this->getServiceLocator()->get('jimmybase_userpayments_mapper');

		$bt_mapper = $this->getServiceLocator()->get('jimmybase_braintree_payment_mapper');

		if(!$this->zfcUserAuthentication()->getIdentity()){
	 			return new JsonModel(array('loggedout'=>1));
		}

		$user 		  = $user_mapper->findById($this->ZfcUserAuthentication()->getIdentity()->getId());

		$credit_card_array = [];
		$parent_user_array = [];
		$next_payment_date = '';

		if($user_mapper->getMeta($current_user->getId(), 'credit_card_number')) {
			$credit_card_array = array(
										'number' => $user_mapper->getMeta($current_user->getId(), 'credit_card_number'),
										'expiration'	=> array(
											'month' => $user_mapper->getMeta($current_user->getId(), 'credit_card_expiration_month'),
											'year' => $user_mapper->getMeta($current_user->getId(), 'credit_card_expiration_year'),
											)
									);
			$bt_subscription = $bt_mapper->findByUser($current_user->getId());
			if($bt_subscription) {
				$credit_card_array['subscription'] = array(
					'gateway' => 'braintree',
					'id' => $bt_subscription->getSubscriptionId(),
					'customer_id' => $bt_subscription->getCustomerId(),
					);
				switch ($bt_subscription->getStatus()) {
					case Subscription::ACTIVE:
					case Subscription::PENDING:
						$credit_card_array['subscription']['status'] = $bt_subscription->getStatus();
						break;
					default:
						$credit_card_array['subscription']['status'] = "0";
						break;
				}
			}
			$next_payment_date = date("c",strtotime($user_mapper->getMeta($current_user->getId(), 'next_payment_date')));
		}

		$current_user_array = array('id'    => $current_user->getId(),
			                        'name'  => $current_user->getName(),
			                        'email' => $current_user->getEmail(),
			                        'type'  => $current_user->getType(),
			                        'logo'  => $user_mapper->getMeta($current_user->getId(),'logo'),
			                        'thumb' => $user_mapper->getMeta($current_user->getId(), 'thumb'),
			                        'created'=>$current_user->getCreated(),
			                        'invoices'=> $user_payments_mapper->fetchAllByUserId($this->ZfcUserAuthentication()->getIdentity()->getId()),
			                        );
		if($credit_card_array) {
			                        $current_user_array['credit_card'] = $credit_card_array;
			                        $current_user_array['next_payment_date'] = $next_payment_date;
		}
		
		if($current_user->getType()=='coworker') {
			// get parent user
			$parent_user_id = $this->getServiceLocator()
                                     ->get('jimmybase_user_mapper')
                                     ->getMeta($current_user->getId(),'parent');
            $parent = $this->getServiceLocator()
                 					 ->get('jimmybase_user_mapper')
                 					 ->findById($parent_user_id);	
			// setup parent user array
            $parent_user_array = array('id'    => $parent->getId(),
			                        'created'=>$parent->getCreated(),
			                        );
			$parent_credit_card_array = [];
			if($user_mapper->getMeta($parent->getId(), 'credit_card_number')) {
				// get parent user creditcard
				$parent_credit_card_array = array(
					'number' => $user_mapper->getMeta($parent->getId(), 'credit_card_number'),
					'expiration'	=> array(
						'month' => $user_mapper->getMeta($parent->getId(), 'credit_card_expiration_month'),
						'year' => $user_mapper->getMeta($parent->getId(), 'credit_card_expiration_year'),
						)
				);	
				// get parent user subscription
				$bt_subscription = $bt_mapper->findByUser($parent->getId());
				if($bt_subscription) {
					$parent_credit_card_array['subscription'] = array(
						'gateway' => 'braintree',
						'id' => $bt_subscription->getSubscriptionId(),
						'customer_id' => $bt_subscription->getCustomerId(),
						);
					switch ($bt_subscription->getStatus()) {
						case Subscription::ACTIVE:
						case Subscription::PENDING:
							$credit_card_array['subscription']['status'] = $bt_subscription->getStatus();
							break;
						default:
							$credit_card_array['subscription']['status'] = "0";
							break;
					}
				}
			}
			if($parent_credit_card_array) {
				$parent_user_array['credit_card'] = $parent_credit_card_array;
			}

		}

        if(in_array($current_user->getType(),array('agency','coworker')) ) {

				$current_user_id = $current_user->getId();
                                $user_created =new \DateTime($current_user->getCreated());
                               
                               

            	if($current_user->getType()=='coworker'){
					$current_user_id  = $user_mapper->getMeta($current_user_id,'parent');
					$general_info['parent'] = $parent_user_array;
                                        $user_created = new \DateTime($parent->getCreated());                                        
                                        
            	}
                $userState =  $user_mapper->findById($current_user_id)->getState();
                switch ($userState) {
                    case $current_user::ACTIVE :
                    {
                        $general_info['account_state'] = "active";
                        break;
                    }
                    case $current_user::INACTIVE :
                    {
                        $general_info['account_state'] = "inactive";
                        break;
                    }
                    case $current_user::CANCELLED :
                    {
                        $general_info['account_state'] = "cancelled";
                        break;
                    }
                        
                }
                 
                $general_info['current_user']['created_timestamp'] = $user_created->getTimestamp();
                

				$clientService    = $this->getServiceLocator()->get('jimmybase_client_service');
				$clients 	      = $clientService->getClientMapper()->fetchAllByAgency($current_user_id);
				$package_mapper   = $this->getServiceLocator()->get('jimmybase_package_mapper');

				$current_package  = $user_mapper->getMeta($current_user_id,'package');
				$templates_used   = 0;

				if($current_package){
				   $package       = $package_mapper->findByIdToArray($current_package);
				}


				$templates      = $this->getServiceLocator()->get('jimmybase_reports_mapper')->findByAgency($current_user_id);
				$templates_used = $templates->count();

                $u_packages      = $this->getServiceLocator()->get('jimmybase_package_mapper')->fetchUnlimited();
                                
                                $today = new \DateTime();                                
                                $date_diff = $user_created->diff($today)->format('%a');                               
                                $days_left = 14 - $date_diff;
                             
                                if ($days_left < 0) {
                                    $days_left = 0;
                                }
                                $general_info['days_left'] = $days_left;
  				$general_info['current_user']   = $current_user_array;
                               
                $general_info['package']        = $package;
                // TODO Find out why is unlimited_package used in some cases and fix its use with unlimited_packages.
                $general_info['unlimited_package']      = $u_packages[0];
                $general_info['unlimited_packages']      = $u_packages;
  				$general_info['total_clients']  = $clients->count();

  				$general_info['templates_used'] = $templates_used;

  				if($user_mapper->getMeta($current_user->getId(), 'credit_card_numner'))
  					$general_info['credit_card'] = $credit_card_array;

  				$config 		 = $this->getServiceLocator()->get('Config');
				$jimmy_settings  = $config['default-user-config'];



  				$general_info['settings']		  = @unserialize($user_mapper->getMeta($current_user_id,'_settings'));
  				$general_info['logo_config']	  = $config['logo-config'];


				foreach ($jimmy_settings as $key => $settings) {
					if(!in_array($key, array_keys($general_info['settings'])))
						$general_info['settings'][$key] = $settings;

				}


		} else if($current_user->getType()=='user'){//or $current_user->getType()=='admin'

  			$general_info['current_user']   = $current_user_array;
		}

	 return new JsonModel($general_info);
    }

}
