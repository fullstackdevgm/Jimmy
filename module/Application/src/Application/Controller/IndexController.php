<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container as SessionContainer;
use Zend\Json\Json;
use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Google\AdWords\Service\AdWords;
use JimmyBase\Service\Notification;
use JimmyBase\Entity\ClientAccounts;
use JimmyBase\Entity\UserCancelLog;
use JimmyBase\Entity\UserCancelLogInterface;

use \DateTime as DateTime;

class IndexController extends AbstractActionController
{
	public  $user;

	private $options;

	private	$redirectUri  = '/authcallback';

	const PACKAGE_TRIAL = 5;
	const PACKAGE_NEW_TRIAL = 15;
	const PACKAGE_14_DAY_TRIAL = 16;


	public function indexAction(){

		 $viewModel =  new ViewModel();
		 $this->layout('layout/layout-login.phtml');

		 $httpHost = $this->getRequest()->getUri()->getHost();

		if(preg_match('/reports.jimmydata.com/',$httpHost) or
                         preg_match('/reports.staging.jimmydata.com/',$httpHost) or 
                         preg_match('/reports.localhost/',$httpHost)) {
			$viewModel->setVariables(array('client_login'=>true));
		}

		if(!$this->zfcUserAuthentication()->hasIdentity()) {
                    $login = $this->forward()->dispatch('ScnSocialAuth-User', array('action' => 'login'));

                    $viewModel->addChild($login, 'login');
                    $config = $this->getServiceLocator()->get('Config');

                    $viewModel->setVariable('logo_config',$config['logo-config']);
                    $this->layout()->setVariable('title',$config['jimmy-config']['title']);
                    $this->layout()->setVariable('logo_config',$config['logo-config']);


                    $referrer   = $this->getRequest()->getQuery('referrer');
                    $hybridAuth = $this->getServiceLocator()->get('HybridAuth');

                    $hybridAuth::storage()->delete("hauth_session.referrer");
                    $hybridAuth::storage()->delete("hauth_session.trial");

                    if($referrer){
                                    $hybridAuth::storage()->set("hauth_session.referrer",$referrer);

                                    $this->layout('layout/layout-referrer.phtml');
                                    $resolver = $this->getEvent()
                                                            ->getApplication()
                                                            ->getServiceManager()
                                                            ->get('Zend\View\Resolver\TemplatePathStack');
                                    $template = "application/index/referrer/".$referrer.".phtml";
                                if ($resolver->resolve($template)) {
                                            $viewModel->setTemplate($template);
                                } else {
                                    $viewModel->setTemplate('application/index/referrer.phtml');
                                }

                            }
                    return $viewModel;
		}

	 	return $this->redirect()->toRoute('dashboard');
	}




    public function authappAction(){

    	$channel = $this->params()->fromRoute('channel', 0);

    	$uri    = $this->getRequest()->getUri();
      
        $base   = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());

		$google_api_config  = $this->getOptions();

		$user         = $this->ZfcUserAuthentication()->getIdentity();

		$session      = new SessionContainer('Client_Auth');


		if($channel)
			$session->offsetSet('channel',$channel);

		// Set the redirect to be true
		$session->offsetSet('auth_redirect',true);
		// Store the referer for redirection after callback
		$session->offsetSet('referer',$this->params('referrer'));

			switch($channel) {
				case ClientAccounts::GOOGLE_ADWORDS:
                                    $campaign_api 	  = $this->getServiceLocator()->get('jimmybase_campaignapi_service');

                                    $oauth2Info       =  $campaign_api->getApiService()
                                                                        ->getAdWordsUser()
                                                                        ->GetOAuth2Info();

                                    $authorizationUrl =  $campaign_api->getApiService()
                                                                    ->getAdWordsUser()
                                                                    ->GetOAuth2Handler()
                                                                    ->GetAuthorizationUrl($oauth2Info,$google_api_config['redirect_uri'],
                                                                                            TRUE,array('prompt' => 'consent'));
// var_dump($authorizationUrl);
// exit();
                                    break;
				case ClientAccounts::GOOGLE_ANALYTICS:

					    $authorizationUrl =      $this->getServiceLocator()->get('jimmybase_ananlytics_api_service')->getClient()->createAuthUrl();

					    break;
				case ClientAccounts::BING_ADS:

					    $authorizationUrl =      $this->getServiceLocator()->get('BingAdsApi')->getAuthorizationUrl();
                                         
					    break;
				default:

			}
                        
		return $this->redirect()->toUrl($authorizationUrl);
                
	}

    public function wizardAuthAppAction() {

    	$channel = $this->params()->fromRoute('channel', 0);

    	$uri    = $this->getRequest()->getUri();
        $base   = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());

		$google_api_config  = $this->getOptions();

		$user         = $this->ZfcUserAuthentication()->getIdentity();

		$session      = new SessionContainer('Client_Auth');


		if($channel)
			$session->offsetSet('channel',$channel);

		// Set the redirect to be true
		$session->offsetSet('wizard_auth_redirect',true);

		// Store the referer for redirection after callback
		$session->offsetSet('referer',$this->url()->fromRoute('client/add'));


			switch($channel){
				case ClientAccounts::GOOGLE_ADWORDS:
						$campaign_api 	  = $this->getServiceLocator()->get('jimmybase_campaignapi_service');

						$oauth2Info       =  $campaign_api->getApiService()
													      ->getAdWordsUser()
													      ->GetOAuth2Info();

						$authorizationUrl =  $campaign_api->getApiService()
														  ->getAdWordsUser()
														  ->GetOAuth2Handler()
														  ->GetAuthorizationUrl($oauth2Info,$google_api_config['redirect_uri'], TRUE);

						break;
				case ClientAccounts::GOOGLE_ANALYTICS:

					    $authorizationUrl =      $this->getServiceLocator()->get('jimmybase_ananlytics_api_service')->getClient()->createAuthUrl();

					    break;
				default:

			}
		return $this->redirect()->toUrl($authorizationUrl);
	}



	public function reauthappAction() {

    	$client_account_id = $this->params()->fromRoute('client_account_id');
		$client_accounts_mapper = $this->getServiceLocator()
                                               ->get('jimmybase_clientaccounts_mapper');

		$client_account = $client_accounts_mapper->findById($client_account_id);
		$api_info = $client_account->getApiAuthInfo();

		if($api_info){
			$this->revoke(unserialize($api_info));

			$client_account->setApiAuthInfo(null);
                        $client_accounts_mapper->update($client_account);
		}


    	$uri    = $this->getRequest()->getUri();
        $base   = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());

		$google_api_config  = $this->getOptions();

		$user         = $this->ZfcUserAuthentication()->getIdentity();

		$session      = new SessionContainer('Client_Auth');

		if($client_account->getChannel())
			$session->offsetSet('channel',$client_account->getChannel());

		// Set the redirect to be true
		$session->offsetSet('re_auth_redirect',true);
		$session->offsetSet('client_account_id',$client_account_id);

		$session->offsetSet('referer',$_SERVER['HTTP_REFERER']);

		// Store the referer for redirection after callback

			switch($client_account->getChannel()){
				case ClientAccounts::GOOGLE_ADWORDS:
						$adwords_service  = $this->getServiceLocator()->get('jimmybase_adwords_service');

						$oauth2Info       =  $adwords_service->getApiService()
                                                                                    ->getAdWordsUser()
                                                                                    ->GetOAuth2Info();

						$authorizationUrl =  $adwords_service->getApiService()
                                                                                    ->getAdWordsUser()
                                                                                    ->GetOAuth2Handler()
                                                                                    ->GetAuthorizationUrl($oauth2Info,$google_api_config['redirect_uri'], TRUE,array('approval_prompt' => 'force'));

						break;
				case ClientAccounts::GOOGLE_ANALYTICS:

					    $authorizationUrl =      $this->getServiceLocator()->get('jimmybase_ananlytics_api_service')->getClient()->createAuthUrl();

					    break;


				default:

			}
                    
		return $this->redirect()->toUrl($authorizationUrl);
	}
//        
//        public function reauthappBulkAction() 
//        {
//
//                $client_account_id = $this->params()->fromRoute('client_account_id');
//		$client_accounts_mapper = $this->getServiceLocator()
//                                               ->get('jimmybase_clientaccounts_mapper');
//                $client_mapper = $this->getServiceLocator()
//                                      ->get('jimmybase_client_mapper');
//		
//                $client_account = $client_accounts_mapper->findById($client_account_id);
//                $clientId = $client_account->getclientId();
//                $parent = $client_mapper->findById($clientId);
//                $clients = $client_mapper->findByParent($parent->getParent());
//                
//                foreach ($clients as $c) {
//                   
//                
//                    $client_account = $client_accounts_mapper->findByClientId($c->getId()); 
//                    if ($client_account) {
//                        $api_info = $client_account->getApiAuthInfo();
//            
//                        if($api_info) {
//                                $this->revoke(unserialize($api_info));
//
//                                $client_account->setApiAuthInfo(null);
//                            $client_accounts_mapper->update($client_account);
//                        }
//
//                        $uri    = $this->getRequest()->getUri();
//                        $base   = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
//
//                        $google_api_config  = $this->getOptions();
//    
//                        switch($client_account->getChannel()){
//                                case ClientAccounts::GOOGLE_ADWORDS:
//                                    $adwords_service  = $this->getServiceLocator()->get('jimmybase_adwords_service');
//
//                                    $oauth2Info       =  $adwords_service->getApiService()
//                                                                         ->getAdWordsUser()
//                                                                         ->GetOAuth2Info();
//
//                                    $authorizationUrl =  $adwords_service->getApiService()
//                                                                         ->getAdWordsUser()
//                                                                         ->GetOAuth2Handler()
//                                                                         ->GetAuthorizationUrl($oauth2Info,
//                                                                                               $google_api_config['redirect_uri'],
//                                                                                               TRUE,
//                                                                                               array('approval_prompt' => 'force'));
//                                    var_dump($authorizationUrl);
//                                                break;
//                                case ClientAccounts::GOOGLE_ANALYTICS:
//
//                                    $authorizationUrl = $this->getServiceLocator()
//                                                             ->get('jimmybase_ananlytics_api_service')
//                                                             ->getClient()->createAuthUrl();
//
//                                            break;
//
//
//                                default:
//
//                        }
//                       
//                    }
//                    
//                }
//                die;
//                 return $this->redirect()->toUrl($authorizationUrl);
//
//		
//	}
//        
	public function authcallbackAction(){
		$uri    = $this->getRequest()->getUri();
		$base   = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
		$google_api_config  = $this->getOptions();
		$user        = $this->ZfcUserAuthentication()->getIdentity();
		$api_service = $this->getServiceLocator()->get('jimmybase_adwords_service');

		$session = new SessionContainer('Client_Auth');

		$channel = $session->offsetGet('channel');

		$re_auth_redirect = $session->offsetGet('re_auth_redirect');
		$config = $this->getServiceLocator()->get('Config');

	    if ($this->ZfcUserAuthentication()->getIdentity()){

                $current_user_id = $this->ZfcUserAuthentication()
                                        ->getIdentity()
                                        ->getId();

                if ($this->ZfcUserAuthentication()->getIdentity()->getType()=='coworker') {
                    $current_user_id = $this->getServiceLocator()                
                                           ->get('jimmybase_user_mapper')
                                           ->getMeta($current_user_id,'parent');
		}
            }
		$viewModel = new ViewModel();
        try{
			switch($channel) {

				case ClientAccounts::GOOGLE_ADWORDS:

					if($this->params()->fromQuery('error'))
						throw new \Exception($this->params()->fromQuery('error'));

					$code =  $this->params()->fromQuery('code');

					$oauth2Info = $api_service->getApiService()->getAdWordsUser()->GetOAuth2Info();

					// Get the access token using the authorization code. Ensure you use the same
					// Redirect URL used when requesting authorization.
					$access_token = (array)$api_service->getApiService()
						->getAdWordsUser()
						->GetOAuth2Handler()
						->GetAccessToken($oauth2Info,$code, $google_api_config['redirect_uri']);
					//print_r($access_token);
					$session->offsetSet('access_token',$access_token);

					if($channel && !$re_auth_redirect) {
						if($access_token){

							$clients_array = $this->getServiceLocator()
								->get('jimmybase_clientapi_service')
								->setChannel($channel)
								->setApiAccessToken($access_token)
								->fetchClientsAccounts($current_user_id);
							//print_r($clients_array);
							foreach ($clients_array as $key => $value) {
								$newClientsArray[] = array('id' => $value['customerId'],'name' => $value['name'],'email' => $value['email']);
							}
							//print_r($newClientsArray);
							$session->offsetSet('client_accounts', $newClientsArray);

						}
					}


				break;
				case ClientAccounts::GOOGLE_ANALYTICS:
					if($this->params()->fromQuery('error'))
						throw new \Exception($this->params()->fromQuery('error'));

					$code =  $this->params()->fromQuery('code');

					$access_token = $this->getServiceLocator()->get('jimmybase_ananlytics_api_service')->getClient()->authenticate($code);
					$session->offsetSet('access_token',$access_token);

					if($channel && !$re_auth_redirect){
						if($access_token){
							$clients_array = $this->getServiceLocator()
								->get('jimmybase_clientapi_service')
								->setChannel($channel)
								->setApiAccessToken($access_token)
								->fetchClientsAccounts($current_user_id);

							$session->offsetSet('client_accounts',$clients_array);

						}
					}

				break;
				case ClientAccounts::BING_ADS:
					$code  =  $this->params()->fromQuery('code');

					// Work around for the bing login as bing login is not currently supporting multiple redirect_uri
					if($session->offsetGet('binglogin')){
						$session->offsetSet('binglogin',null);

						# get hybridauth base url
						if (empty(\Hybrid_Auth::$config["base_url"])) {
							// the base url wasn't provide, so we must use the current
							// url (which makes sense actually)
							$url  = empty($_SERVER['HTTPS']) ? 'http' : 'https';
							$url .= '://' . $_SERVER['HTTP_HOST'];

							$HYBRID_AUTH_URL_BASE = $url;
						} else {
							$HYBRID_AUTH_URL_BASE = \Hybrid_Auth::$config["base_url"];
						}
						$HYBRID_AUTH_URL_BASE = $HYBRID_AUTH_URL_BASE . "/scn-social-auth/hauth/done/live?code=".$code;

						return $this->redirect()->toUrl($HYBRID_AUTH_URL_BASE);
					}

					$access_token_arr = $this->getServiceLocator()->get('BingAdsApi')->authenticate($code);

					$session->offsetSet('access_token',$access_token_arr);

					if($channel && !$re_auth_redirect){

						if($access_token_arr){
							//$access_token = $access_token_arr['access_token'];

							$clients_array = $this->getServiceLocator()
							->get('jimmybase_clientapi_service')
							->setChannel($channel)
							->setApiAccessToken($access_token_arr)
							->fetchClientsAccounts($current_user_id);

							$session->offsetSet('client_accounts',$clients_array);

						}
					}
				break;

				default:
					throw new \Exception("Invalid Channel Used", 1);
			}

			$this->layout('layout/layout-authcallback');
			$viewModel->setTemplate('application/index/authcallback.phtml');
                            

			//var_dump($_SESSION);
			if($re_auth_redirect && ($client_account_id = $session->offsetGet('client_account_id'))){

				$client_accounts_mapper = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper');
				$userTokenMapper = $this->getServiceLocator()->get('jimmybase_usertoken_mapper');
                                    
                                    
                $client_account = $client_accounts_mapper->findById($client_account_id);
				//Update the source Token 
                $userTokenObj = $userTokenMapper->findById($client_account->getUserTokenId());
                
                $userTokenObj->setToken(serialize($access_token));
                
                //$client_account->setApiAuthInfo(serialize($access_token));                                       
				$userTokenMapper->update($userTokenObj);
				$session->offsetSet('client_account_id',null);
				$viewModel->setTemplate('application/index/re-authcallback.phtml');
				$viewModel->setVariable('reauth-done',true);

			}

			$session->offsetSet('re_auth_redirect',false);

		} catch(\OAuth2Exception $e){
			print_r($e->getMessage());
			$json = json_decode($e->getMessage());
			$viewModel->setVariable('error',true);
			$viewModel->setVariable('error_msg',"Sorry, a problem occurred while retrieving the client accounts!");

			if($json->error=='invalid_grant'){
				$this->layout('layout/layout-authcallback');
				$viewModel->setVariable('error',true);
				$viewModel->setTemplate('application/index/authcallback.phtml');
			}

		} catch(\SoapFault $e){
		print_r($e->getMessage());
				$viewModel->setVariable('error',true);
				$viewModel->setVariable('error_msg',"Sorry, a problem occurred while retrieving the client accounts!");

			if(strpos($e->getMessage(), 'NOT_ADS_USER') !== FALSE){
				$this->layout('layout/layout-authcallback');
				$viewModel->setVariable('error',true);
				$viewModel->setVariable('error_msg',"Not a valid Google Adwords Account!");
				$viewModel->setTemplate('application/index/authcallback.phtml');
			}
		} catch(\Exception $e){
				print_r($e->getMessage());
				$viewModel->setVariable('error',true);
				$viewModel->setVariable('error_msg',"Sorry, a problem occurred while retrieving the client accounts!");

			if($e->getMessage() == 'access_denied'){
				$this->layout('layout/layout-authcallback');
				$viewModel->setVariable('error',true);
				$viewModel->setVariable('error_msg',"You have denied access to your account!");
				$viewModel->setTemplate('application/index/authcallback.phtml');
			} else if(strpos($e->getMessage(), "User does not have any Google Analytics account") !== FALSE ){
				$this->layout('layout/layout-authcallback');
				$viewModel->setVariable('error',true);
				$viewModel->setVariable('error_msg',"Not a valid Google Analytics Account!");
				$viewModel->setTemplate('application/index/authcallback.phtml');
			}
		}
	    	$referer = $session->offsetGet('referer');
        	$session->offsetSet('referer',null);
                   

		return $viewModel;

	}

	public function revoke($api_access){

	    $url  = "https://accounts.google.com/o/oauth2/revoke?token={$api_access['access_token']}";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,$url);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$data = curl_exec($curl);
		curl_close($curl);

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



	public function upgradeAction(){
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

	public function saveccAction(){
		$request   	    = $this->getRequest();
		$response  	    = $this->getResponse();


 		// $package_mapper = $this->getServiceLocator()->get('jimmybase_package_mapper');
		$user_service   = $this->getServiceLocator()->get('jimmybase_user_service');
		$user_mapper 	= $this->getServiceLocator()->get('jimmybase_user_mapper');
		$payment_service = $this->getServiceLocator()->get('jimmybase_payment_service');

		$current_user   = $this->ZfcUserAuthentication()->getIdentity();

		if(!$this->ZfcUserAuthentication()->hasIdentity()) {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false,'message'=>'User not authenticated.')));
			return $response;
		}

		if($request->isPost()) {


			try{
				 $this->getEventManager()->trigger('savecc.pre', $this, array('request' => $request));
				 $customerInfo = Json::decode($request->getContent(), true);

			 	 $apiResponse = $payment_service->setupTokenPayment($customerInfo, $current_user);

				 if(!$apiResponse['success']) {
				 	$saveCCResponse = array(
				 		'success' => false,
				 		'message' => $apiResponse['message']
				 	);
				 	$this->getEventManager()->trigger('savecc.failure', $this, $saveCCResponse);
				 } else {
				 	$saveCCResponse = array(
				 		'success' => true,
				 		'credit_card_number' => $user_mapper->getMeta($current_user->getId(), 'credit_card_number'),
			 			'credit_card_expiration_month' => $user_mapper->getMeta($current_user->getId(), 'credit_card_expiration_month'),
			 			'credit_card_expiration_year' => $user_mapper->getMeta($current_user->getId(), 'credit_card_expiration_year'),
				 	);
				 	// Check if next billing date is already present
					if(!$user_mapper->getMeta($current_user->getId(), 'next_payment_date')) {
						// if not set billing date
						$payment_service->setNextPaymentDate($current_user);
			 			$saveCCResponse['next_payment_date'] = $user_mapper->getMeta($current_user->getId(), 'next_payment_date');
					} else {
						// else check next billing date difference
						// with today
						$difference = 0;
						$nextPaymentDate = new DateTime($user_mapper->getMeta($current_user->getId(), 'next_payment_date'));
						$dateDifference = $nextPaymentDate->diff(new DateTime());
						$difference = $dateDifference->days;
						// Make a transaction of the pending amount if its difference <0
						if($difference<0) {
							$report_service = $this->getServiceManager()->get('jimmybase_reports_service');
							$reportsCount = $report_service->getCount($current_user->getId());
							// Ignoring free reports
							$reportsCount-=5;
							// Calculating amount AUD 5 per report
							$amount = $reportsCount*5;
							$paymentResponse = $payment_service->processPayment(
								$current_user,
								$user_mapper->getMeta($current_user->getId(), 'eway_token_id'),
								$amount
								);
							if($paymentResponse['data']->TransactionStatus) {
								$saveCCResponse['next_payment_date'] = $user_service->getMeta($current_user->getId(), 'next_payment_date');
							}
						}
						
					}
				 	if($user_mapper->getMeta($current_user->getId(), 'next_payment_date')) {
				 		$saveCCResponse['next_payment_date'] = $user_mapper->getMeta($current_user->getId(), 'next_payment_date');
				 	}
				 	$this->getEventManager()->trigger('savecc.success', $this, $saveCCResponse);
				 }

			} catch (\Exception $e){
				$saveCCResponse = array('success'=>false,'message'=> $e->getMessage());

			}

			$response->setContent(Json::encode($saveCCResponse));
			return $response;

		}
	}

	public function makePaymentAction() {
		$request 	= $this->getRequest();
		$response 	= $this->getResponse();

		$payment_service = $this->getServiceLocator()->get('jimmybase_payment_service');
		$user_service = $this->getServiceLocator()->get('jimmybase_user_service');

		$user_mapper = $this->getServiceLocator()->get('jimmybase_user_mapper');
		$package_mapper = $this->getServiceLocator()->get('jimmybase_package_mapper');
		$reports_mapper = $this->getServiceLocator()->get('jimmybase_reports_mapper');

		try {
			
			if(!$this->ZfcUserAuthentication()->hasIdentity())
				throw new \Exception('User not autenticated.');

			$current_user = $this->ZfcUserAuthentication()->getIdentity();

			if(!$user_mapper->getMeta($current_user->getId(), 'eway_token_id'))
				throw new \Exception("No credit card stored.");

			
				
			$apiResponse = $payment_service->processTokenPayment(
				$current_user
				);

			if(!$apiResponse['success']) {
				$paymentResponse = array(
					'success' => false,
					'message' => $apiResponse['message']
					);
			} else {
				// $data = $paymentResponse['data'];
				$user_payment_mapper = $this->getServiceLocator()->get('jimmybase_userpayments_mapper');
				$invoice = $user_payment_mapper->findById($apiResponse['invoice']);
				// print_r($invoice); die;
				$paymentResponse = array(
					'success' => true,
					'transaction' => array(
						'id' => $invoice->getTransId(),
						'amount' => $invoice->getAmount(),
						'status' => $invoice->getStatus(),
						'currency' => $invoice->getCurrency(),
						'date' => $invoice->getDate(),
						),
					);
			}

		} catch (\Exception $e) {
			$paymentResponse = array('success' => false, 'message' => $e->getMessage());
		}

		$response->setContent(Json::encode($paymentResponse));
		return $response;
	}

	public function queryAction(){
		 $request   = $this->getRequest();
		 $response  = $this->getResponse();

		//$eway_recurring_api = $this->getServiceLocator()->get('eway_recurring_api');
		//$package_mapper 	= $this->getServiceLocator()->get('jimmybase_package_mapper');

		 $payment_service = $this->getServiceLocator()->get('jimmybase_payment_service');
		 $payment_service->queryRecurringPayment(113);
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

	public function trialAction(){

	 	$viewModel =  new ViewModel();
	 	$this->layout('layout/layout-trial.phtml');


	    if(!$this->zfcUserAuthentication()->getIdentity()){

			$hybridAuth = $this->getServiceLocator()->get('HybridAuth');
			$hybridAuth::storage()->set('hauth_session.trial',true);

			return $viewModel;
		 }

	 	return $this->redirect()->toRoute('dashboard');
	}


	public function quoteAction(){

		$request  = $this->getRequest();
		$response = $this->getResponse();

		if($request->isPost()){

			$support      = $request->getPost()->toArray();
 			$current_user = $this->ZfcUserAuthentication()->getIdentity();

			if(!$request->getPost('no_reports_required'))
				return $response->setContent(\Zend\Json\Json::encode(array('success'=>false,'message'=>'Please specify the number of reports required!')));

			try{

				$viewModel  = new ViewModel();

				$viewModel->setVariable('support' , $support)
					  	  ->setVariable('user' , $current_user)
					  	  ->setTemplate('jimmy-base/emails/get_a_quote.phtml');

				$htmlOutput = $this->getServiceLocator()->get('viewrenderer')
									   		     	    ->render($viewModel);

				$subject = "Custom Quote Request";

				// make a header as html
				$html = new MimePart($htmlOutput);
				$html->type = "text/html";

				$body = new MimeMessage();
				$body->setParts(array($html,));

				// instance mail
				$mail = new Message();
				$mail->setBody($body); // will generate our code html from template.phtml
				$mail->setFrom($current_user->getEmail(),$current_user->getName());
				$mail->setTo('sagar@webmarketers.com.au');
				$mail->setSubject($subject);

				$transport = new Mail\Transport\Sendmail();
			    $transport->send($mail);
				//if(!)
				  // throw new \Exception("A problem occurred while sending the email");

				$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'message'=>'Your  request has been sent. One of our agent will get in touch with you!')));

			} catch(\Exception $e) {
				$response->setContent(\Zend\Json\Json::encode(array('success'=>false,'message'=>'Your request couldnot be sent at this time. Please try again later!')));
			}
			return $response;

		}

	}

	public function supportAction($data){
		$request  = $this->getRequest();
		$response = $this->getResponse();

		if($request->isPost()){

			$support      = $request->getPost()->toArray();
 			$current_user = $this->ZfcUserAuthentication()->getIdentity();


			try{
				$viewModel  = new ViewModel();

				$viewModel->setVariable('support' , $support)
					  	  ->setVariable('user' , $current_user)
					  	  ->setTemplate('jimmy-base/emails/support.phtml');

				$htmlOutput = $this->getServiceLocator()->get('viewrenderer')
									   		     	    ->render($viewModel);


				$subject = "Support Request";

				// make a header as html
				$html = new MimePart($htmlOutput);
				$html->type = "text/html";

				$body = new MimeMessage();
				$body->setParts(array($html,));

				// instance mail
				$mail = new Message();
				$mail->setBody($body); // will generate our code html from template.phtml
				$mail->setFrom($current_user->getEmail(),$current_user->getName());
				$mail->setTo(array('jimmydata.help@gmail.com','william@jimmydata.com','sagar@webmarketers.com.au','sagar@jimmydata.com'));
				$mail->setSubject($subject);

				$transport = new Mail\Transport\Sendmail();
			    $transport->send($mail);

				$json = array('success'=>true,'message'=>'Your support request has been sent. One of our agent will get in touch with you!');

			} catch(\Exception $e) {
				$json = array('success'=>false,'message'=>'Your support request couldnot be sent at this time. Please try again later');
			}
			return new JsonModel($json);

		}
	}

	public function cancelAction(){

 		 $request   = $this->getRequest();
		 $response  = $this->getResponse();

		 $current_user    = $this->ZfcUserAuthentication()->getIdentity();
		 $payment_service = $this->getServiceLocator()->get('jimmybase_payment_service');
		 $bt_payment_service = $this->getServiceLocator()->get('jimmybase_bt_payment_service');

		 $user_mapper   = $this->getServiceLocator()->get('jimmybase_user_mapper');
		 $user_cancel_log_mapper = $this->getServiceLocator()->get('jimmybase_usercancellog_mapper');
		 $bt_payment_mapper = $this->getServiceLocator()->get('jimmybase_braintree_payment_mapper');


		 try{
	 		$bt_customer      = $bt_payment_mapper->findByUser($current_user->getId());
	 		$customerId       = $user_mapper->getMeta($current_user->getId(),'eway_customer_id');
			$subscriptionId   = $user_mapper->getMeta($current_user->getId(),'eway_rebill_id');

			// check if user is not in paid
			 if(!in_array($user_mapper->getMeta($current_user->getId(), 'package'),
                                 array(self::PACKAGE_TRIAL, self::PACKAGE_NEW_TRIAL, self::PACKAGE_14_DAY_TRIAL))) {
			 	// user is paid
			 	if ($bt_customer) {
                                    // if user is a Braintree customer
                                    // cancel the subcription
                                    $bt_payment_service->cancelSubscription($current_user);

                                } else {

                                    if($customerId && !$payment_service->cancelRecurringCustomer($current_user,$customerId,$subscriptionId)){
                                      // if user has eway customer id and cannot cancel the account
                                      throw new \Exception("Error Cancelling the account");
                                    } else {
                                            // if user does not have eway customer id or account was cancelled
                                            $current_user->setKey('eway_customer_id');
                                            $this->getServiceLocator()->get('jimmybase_user_service')->removeMeta($current_user);
                                            $current_user->setKey('eway_rebill_id');
                                            $this->getServiceLocator()->get('jimmybase_user_service')->removeMeta($current_user);

                                            $current_user->setKey('next_payment_date');
                                            $this->getServiceLocator()->get('jimmybase_user_service')->removeMeta($current_user);
                                    }
                                }
                         }
			// Delete user's schedules.
			$user_mapper->deleteSchedules($current_user->getId());

	  		$userCancelLog = new UserCancelLog($current_user);
	  		$now = date('Y-m-d h:m:s');
	  		$userCancelLog->setDeleted($now);
	  		$userCancelLog->setId(null);
	  	
	  		$user_cancel_log_mapper->insert($userCancelLog);	  		
                        $current_user->setState($current_user::CANCELLED);
                        $user_mapper->update($current_user);
	  		//$user_mapper->delete($current_user->getId());

	  		//$user_mapper->delete($user_id,"user_meta.user_id = ". $user_id, 'user_meta');

	  		//$user_mapper->delete($user_id,"user_provider.user_id = ". $user_id, 'user_provider');
			

  		 	$this->getEventManager()->trigger('accountCancel.success', $this, array('user'  => $current_user));


			$json = array('success' => true,'message'=> "Your account has been cancelled.");

		} catch (\Exception $e){
			$json = array('success' => false,'message'=> $e->getMessage());
		}

		return new JsonModel($json);
	}

	public function upgradeSuccessAction(){		
		$current_user = $this->zfcUserAuthentication()->getIdentity();
                $viewModel    = new ViewModel();
		$user_mapper  = $this->getServiceLocator()->get('jimmybase_user_mapper');
		$widget_mapper= $this->getServiceLocator()->get('jimmybase_widget_mapper');
		$settings = @unserialize($user_mapper->getMeta($current_user_id,'_settings'));
		$viewModel->setTemplate('application/index/upgrade-successful.phtml');

	    $this->layout()->setVariable('settings',$settings);
	    $this->layout()->setVariable('logo', $user_mapper->getMeta($current_user->getId(),'logo'));
	    $this->layout()->setVariable('title',$config['jimmy-config']['title']);
	    return $viewModel;
	}
}