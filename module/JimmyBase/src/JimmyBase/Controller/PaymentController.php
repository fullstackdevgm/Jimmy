<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use Zend\Json\Json;
use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Google\AdWords\Service\AdWords;
use JimmyBase\Service\Notification;

class PaymentController extends AbstractActionController
{
	public  $user;
	
	private $options;
	
	
	public function indexAction(){
		
		
	}

    
    /**
     * set options
     *
     * @param   $options
     * @return google-api-config
     */
    public function setOptions($options)
    {
		
        $this->options = $options;

        return $this;
    }

    /**
     * get options
     *
     * @return google-api-config
     */
    public function getOptions()
    {
		if(!$this->options){
			$config = $this->getServiceLocator()->get('Config');
			$this->setOptions($config['google-api-config']);
		}
		   
        return $this->options;
    }
	
	
	public function processAction(){
	
		$request    = $this->getRequest();
		$response   = $this->getResponse();
		$hybridAuth = $this->getServiceLocator()->get('HybridAuth');

		try{	
			if($request->getPost('access_token'))
				$hybridAuth::storage()->set('hauth_session.google.token.access_token',$request->getPost('access_token'));
			if($request->getPost('refresh_token'))
				$hybridAuth::storage()->set('hauth_session.google.token.refresh_token',$request->getPost('refresh_token'));
			if($request->getPost('expires_in'))
				$hybridAuth::storage()->set('hauth_session.google.token.expires_in',$request->getPost('expires_in'));
			if($request->getPost('expires_at'))
				$hybridAuth::storage()->set('hauth_session.google.token.expires_at',$request->getPost('expires_at'));
			if($request->getPost('is_logged_in'))
			   $hybridAuth::storage()->set('hauth_session.google.is_logged_in',$request->getPost('is_logged_in'));
			
			 $this->getEventManager()->trigger('userSignup.pre', $this, array('request' => $request));
	
			 $apiResponse = $this->getServiceLocator()->get('jimmybase_payment_service')->setupCustomerPayment($request);

			 if(!$apiResponse['success']){
				 $this->getEventManager()->trigger('userSignup.failure', $this, array('paymentResponse' => $paymentResponse,
			 																          'rawUserData'     => $request->getPost()->toArray() ));
				 throw new \Exception($apiResponse['message']);																	   
			 }

			 try {
				 
				 $adapter = $this->zfcUserAuthentication()->getAuthAdapter();
				 $result  = $adapter->prepareForAuthentication($this->getRequest());
				 
				 if ($result instanceof Response) // Return early if an adapter returned a response
					throw new \Exception( $result );
				  
				 if($this->zfcUserAuthentication()->getAuthService()->authenticate($adapter)) 
					$new_user  = $this->zfcUserAuthentication()->getIdentity();
				
				  $this->getEventManager()->trigger('userSignup.success', $this, array('apiResponse'  => $apiResponse,
																					   'newUser'      => $new_user,
																					   'rawUserData'  => $request->getPost()->toArray() ));
		
			} catch (\Exception $e){
				
				throw new \Exception( $e->getMessage() );
			
			}
			 
			 
			$paymentResponse = array('success'    => true,
									 'message'    => 'You have successfully signed up and authenticated.We will redirect you to the dashboard!',
									 'session_id' => session_id());			
		
		} catch (\Exception $e){
			$paymentResponse = array('success'=>false,'message'=> $e->getMessage());			
		
		}
		
		$response->setContent(Json::encode($paymentResponse));
		return $response;
	}
	
	
	
	public function upgradeAction() {
		$request   	    = $this->getRequest();
		$response  	    = $this->getResponse();
		
 		$package_mapper = $this->getServiceLocator()->get('jimmybase_package_mapper');
		$user_service   = $this->getServiceLocator()->get('jimmybase_user_service');
		
		
		$current_user   = $this->ZfcUserAuthentication()->getIdentity();
		
		if(!$request->isPost()){
		
				$this->getServiceLocator()->get('viewhelpermanager')->get('HeadScript')
																	->appendFile( $basePath.'/js/jQuery/jquery-1.9.1.min.js')
																	->appendFile( $basePath.'/js/bootstrap.min.js')
																	->appendFile( $basePath.'/js/jquery.psteps.js')
																	->appendFile( $basePath.'/js/jquery.cookie.js')
																	->appendFile( $basePath.'/js/application.js');
																	
				$this->getServiceLocator()->get('viewhelpermanager')->get('HeadLink')
																	->appendStylesheet('/css/bootstrap.min.css')
																	->appendStylesheet('/css/bootstrap-responsive.min.css')
																	->appendStylesheet('/css/font-awesome/css/font-awesome.min.css')
																	->appendStylesheet('/css/style.css');
					
					
		
				$packageId		= $this->params('package_id');
			
				$package_id     = $this->getServiceLocator()->get('jimmybase_user_mapper')->getMeta($current_user->getId(),'package');
				$package        = $package_mapper->findById($package_id);
				$packages 		= $package_mapper->fetchAllUpgradeable($package->getTemplatesAllowed());
				//var_dump($packages);
							
				if(!$user_service->getPackage($current_user) or $user_service->hasTrial($current_user))
					return new ViewModel(array('packages' => $packages,'fromTrial' => true,'signup'=>true,'user' => $current_user));
				else {
					return new ViewModel(array('packages' => $packages,'fromTrial' => false));
				}
			
		} else {
			
			try{	
				 $this->getEventManager()->trigger('userUpgrade.pre', $this, array('request' => $request));
				
				 $user_package =  $user_service->getPackage($current_user);
				
				
				 if($user_service->hasTrial($current_user)){ // Upgrading from Trial Package
					$apiResponse = $this->getServiceLocator()->get('jimmybase_payment_service')->upgradeCustomerFromTrialPackage($request,$current_user);
				 } else { // Upgrading from Paid Package
					$apiResponse  = $this->getServiceLocator()->get('jimmybase_payment_service')->upgradeCustomerPackage($request,$current_user);
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
			
			$response->setContent(Json::encode($paymentResponse));
			return $response;
		
		}
	}
	
	
	
	
	public function queryAction(){
		
		
		 //$date = date('Y-m-d',strtotime('2013-11-26'));
		 
		 echo "\n";
		 echo date('Y-m-d h:i:s')." Recurring Check Script Start\n";
		 
		 $date = date('Y-m-d');
		 $user_mapper = $this->getServiceLocator()->get('jimmybase_user_mapper');
		 $users_to_be_billed  	= $user_mapper->findToBeInvoicedToday($date);
		
		 if(!$users_to_be_billed)
		 	return false;
		 
		 $payment_service = $this->getServiceLocator()->get('jimmybase_payment_service');
		 echo "Total Users:".count($users_to_be_billed)."\n";	
		 foreach($users_to_be_billed as $user){
		 	if($user_mapper->getMeta($user->getId(), 'eway_customer_id')!=null) {
		 		$payment_service->queryRecurringPayment($user,$date,$date);
		 	} else if($user_mapper->getMeta($user->getId(), 'eway_token_id')!=null) {
		 		$payment_service->processTokenPayment($user);
		 	}
		 }
		 echo date('Y-m-d h:i:s')." Recurring Check Script End\n";
		 echo "================================================\n";

		
		exit;
	}
	
	public function getccinfoAction(){
		$request       = $this->getRequest();
		$response      = $this->getResponse();
		$current_user  = $this->ZfcUserAuthentication()->getIdentity();
	    $user_service  = $this->getServiceLocator()->get('jimmybase_user_service');
		
	
		
		$viewModel = new ViewModel();
		
		
		if(!$user_service->getPackage($current_user) or  $user_service->hasTrial($current_user)){
			
			$viewModel->setVariable('user' , $current_user)
					  ->setTemplate("application/index/signup.phtml");
		
		} else  {

			$apiResponse   = $this->getServiceLocator()->get('jimmybase_payment_service')->queryRecurringEvent($current_user->getId());
			
			if($apiResponse->Result=='Success'){
				$json = array('success' => true,'ccinfo'=>$apiResponse);
			} else {
				$json = array('success' => false,'messahe' => 'Couldnot retrieve your card details');
			}
		
			
			
			$viewModel->setVariable( 'ccinfo' , $apiResponse)
					  ->setTemplate("application/index/cc.phtml");
		}
		
		$htmlOutput = $this->getServiceLocator() ->get('viewrenderer')
									   		     ->render($viewModel);
												 
		$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'html'=>$htmlOutput)));
		return $response;
	}



}