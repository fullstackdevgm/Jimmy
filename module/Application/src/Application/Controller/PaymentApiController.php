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
use Zend\Session\Container as SessionContainer;

use JimmyBase\Entity\ClientAccounts;

class PaymentApiController extends AbstractRestfulController
{	

 	

    public function create($data){

    	$request   	    = $this->getRequest();
		$response  	    = $this->getResponse();
		
 		$package_mapper = $this->getServiceLocator()->get('jimmybase_package_mapper');
		$user_service   = $this->getServiceLocator()->get('jimmybase_user_service');
		
		
		$current_user   = $this->ZfcUserAuthentication()->getIdentity();
		

		if($data){

			try{	
				 $this->getEventManager()->trigger('userUpgrade.pre', $this, array('request' => $request));
				
				 $user_package =  $user_service->getPackage($current_user);

				 if($user_service->hasTrial($current_user)){ // Upgrading from Trial Package
					$apiResponse = $this->getServiceLocator()->get('jimmybase_payment_service')->upgradeCustomerFromTrialPackage($data,$current_user);
				 } else { // Upgrading from Paid Package
					$apiResponse  = $this->getServiceLocator()->get('jimmybase_payment_service')->upgradeCustomerPackage($data,$current_user);
				 }
				 
				 
				 if(!$apiResponse['success']){
					 $this->getEventManager()->trigger('userUpgrade.failure', $this, array('paymentResponse' => $paymentResponse,
																						   'rawUserData'     => $request->getPost()->toArray() ));
					 throw new \Exception($apiResponse['message']);																	   
				 }
				 
			    $this->getEventManager()->trigger('userUpgrade.success', $this, array('apiResponse'  => $apiResponse,
																					  'user'         => $current_user,
																				      'rawUserData'  => $request->getPost()->toArray() ));
				 
				$paymentResponse = array('success'    => true,
										 'message'    => 'Your package has been upgraded'
										 );			
			
			} catch (\Exception $e){
				$paymentResponse = array('success'=>false,'message'=> $e->getMessage());			
			}
			
		
		}

		return new JsonModel($paymentResponse);
    }
	
}
