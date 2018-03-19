<?php

namespace Application\Controller;

use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Crypt\Password\Bcrypt;
use ZfcUser\Controller\UserController as ZfcUserController;
use Zend\Session\Container as SessionContainer;
use Hybrid_Auth;

use Application\Service\User as UserService;

class UserController extends ZfcUserController
{

    const ROUTE_LOGIN        = 'client-login';


    const CONTROLLER_NAME    = 'zfcuser';

    public function indexAction()
    {


	   	if (!$this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toRoute(self::ROUTE_LOGIN);
        }

        return new ViewModel();
    }



	 public function uploadThumbAction(){
 		$request 	= $this->getRequest();
		$service 	= $this->getUserService();
		$user       = $request->getPost()->toArray();
		$response   = $this->getResponse();

		if($request->isPost()){
			if($service->uploadThumb($user,$_FILES['file'])){
				$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
			} else  {
				$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
			}
		} else {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
		}

		return $response;
    }

     public function uploadLogoAction(){
 		$request 	= $this->getRequest();
		$service 	= $this->getUserService();
		$user       = $request->getPost()->toArray();
		$response   = $this->getResponse();
                  
		if($request->isPost()){
			if($service->uploadLogo($user,$_FILES['file'])){
				$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
			} else  {
				$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
			}
		} else {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
		}

		return $response;
    }

    public function removeThumbAction() {
    	$request = $this->getRequest();
    	$service = $this->getUserService();
    	$user = json_decode($request->getContent(), true);
    	$response = $this->getResponse();

    	if($request->isPost()) {
    		if($service->removeThumb($user)) {
    			$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
    		} else {
    			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
    		}
    	} else {
    		$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
    	}

    	return $response;
    }

    public function removeLogoAction() {
    	$request = $this->getRequest();
    	$service = $this->getUserService();
    	$user = json_decode($request->getContent(), true);
    	$response = $this->getResponse();

    	if($request->isPost()) {
    		if($service->removeLogo($user)) {
    			$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
    		} else {
    			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
    		}
    	} else {
    		$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
    	}

    	return $response;
    }

	public function paymentsAction(){
		$request   = $this->getRequest();
		$response  = $this->getResponse();


		$this->getServiceLocator()->get('viewhelpermanager')->get('HeadScript')
															->appendFile( $basePath.'/js/jQuery/jquery-1.9.1.min.js')
															->offsetSetFile(200, $basePath.'/js/jQuery/jquery-ui.min.js')
															->offsetSetFile(204,$basePath.'/js/application.js')
															->appendFile($basePath.'/js/jquery.dataTables.js');


		$service = $this->getUserService();

		$payments_mapper = $this->getServiceLocator()->get('jimmybase_userpayments_mapper');
		$current_user  = $this->zfcUserAuthentication()->getIdentity();

		$payments =$payments_mapper->fetchAllByUserId($current_user->getId());
		return  new ViewModel(array('payments' => $payments));
	}



	public function changepassAction(){
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$viewmodel = new ViewModel();

		$service = $this->getUserService();

		$user = $service->getUserMapper()->findById($request->getPost('user_id'));

		if($request->isPost()){
			$userInfo   = array( 'id'=>$user->getId(), 'password' => $request->getPost('password'));
		    $user   = $service->changePassword($userInfo);
		    if($userInfo){
					$json = array('success'=>true,'message'=>'Password changed!');
				} else {
					$json = array('success'=>false,'message'=>'Sorry password couldn\'t be changed!');
				}

			return new JsonModel($json);

		}
	}



	public function saveTitleAction(){
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$user_id   =  $this->params('user_id');

		$service = $this->getUserService();

		$user = $service->getUserMapper()->findById($user_id);

		if($request->isPost()){
			$user->setName($request->getPost('name'));
		    $user  = $service->saveTitle($user);
		    if($user){
				$json = array('success'=>true,'message'=>'User Name updated!');
			} else {
				$json = array('success'=>false,'message'=>'A problem occurred whiel updating user name!');
			}
		} else {
			$json = array('success'=>false,'message'=>'Invalid Request');
		}

		return new JsonModel($json);
	}



    /**
     * Change the users password
     */
    public function resetpassAction()
    {
    	 $request   = $this->getRequest();
		 $response  = $this->getResponse();

		 $email = $this->getRequest()->getPost('email');
		 $code  = $this->params('code');
		 $user_service = $this->getUserService();



		if($email = $this->getRequest()->getPost('email')){
			$user         = $user_service->getUserMapper()->findByEmail($email);

			if($user){

				if($user->getType()=='user'){

					 $time_plus_24_hours = date('y-m-d h:i:s',strtotime('+24 hour'));
					 $bcrypt = new Bcrypt;
	        	     $bcrypt->setCost(14);
				 	 $verification_code = md5($time_plus_24_hours.'--'.$email);
					 $user->setKey('resetpass_ver_code');
					 $user->setValue($verification_code);

					 if($user_service->saveMeta($user)){
					 	$this->getEventManager()->trigger('passwordResetLink', $this, array('resetpass_ver_code' => $verification_code,'user' => $user));
						$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'message'=>'The password reset link has been sent in your email.')));
						return $response;
					 } else {
						$response->setContent(\Zend\Json\Json::encode(array('success'=>false,'message'=>'Sorry!There was a problem in sending the password reset link.')));
						return $response;
					}

				} else {
					$response->setContent(\Zend\Json\Json::encode(array('success'=>false,'message'=>'You are not a valid client.')));
					return $response;
				}
			}


			$viewModel = new ViewModel();
			$viewModel->setTerminal(true);

		} elseif($code = $this->params('code')){
			$user  = $user_service->getUserMapper()->findByMeta($code);

			if($user){
				$password = $this->App()->randomPassword();

				if($user_service->resetPassword($user,$password)){
					$user_service->removeMeta($user);

				    $this->flashMessenger()->setNamespace('success')->addMessage('Password has been changed and sent in your email.');

				    $config =  $this->getServiceLocator()->get('Config');
	   				$jimmy_settings  = $config['jimmy-config'];

	   				 return $this->redirect()->toUrl($this->url()->fromRoute('user/login'));

				}

			} else {
			  	$this->flashMessenger()->setNamespace('error')->addMessage('Invalid Verification Link.');
			}

		}
		return $this->redirect()->toUrl($this->url()->fromRoute('user/login'));
    }



	public function scnloginAction()
    {
         
        $zfcUserLogin = $this->forward()->dispatch('zfcuser', array('action' => 'login'));
        if (!$zfcUserLogin instanceof ModelInterface) {
            return $zfcUserLogin;
        }
        $viewModel = new ViewModel();
        $viewModel->addChild($zfcUserLogin, 'zfcUserLogin');
        $viewModel->setVariable('options', $this->getOptions());

        $redirect = false;
        if ($this->getServiceLocator()->get('zfcuser_module_options')->getUseRedirectParameterIfPresent() && $this->getRequest()->getQuery()->get('redirect')) {
            $redirect = $this->getRequest()->getQuery()->get('redirect');
        }
        $viewModel->setVariable('redirect', $redirect);

        return $viewModel;
    }

    public function providerLoginAction()
    {
        $provider   = $this->getEvent()->getRouteMatch()->getParam('provider');


        $options    = $this->getServiceLocator()->get('ScnSocialAuth-ModuleOptions');


        if (!in_array($provider, $options->getEnabledProviders())) {
            return $this->notFoundAction();
        }

        $hybridAuth = $this->getServiceLocator()->get('HybridAuth');

        if($provider=='live')
           $hybridAuth::storage()->set('login.live',true);

        $query = array();
        if ($this->getServiceLocator()->get('zfcuser_module_options')->getUseRedirectParameterIfPresent() && $this->getRequest()->getQuery()->get('redirect')) {
            $query = array('redirect' => $this->getRequest()->getQuery()->get('redirect'));
        }
        
        $redirectUrl = $this->url()->fromRoute('scn-social-auth-user/authenticate/provider', array('provider' => $provider), array('query' => $query));
        
        $adapter = $hybridAuth->authenticate(
            $provider,
            array(
                'hauth_return_to' => $redirectUrl,
            )
        );

        return $this->redirect()->toUrl($redirectUrl);

    }


    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if (!$request->isPost()) {
			$viewModel = new ViewModel();
		 	$this->layout('layout/layout-login.phtml');

		 	$config = $this->getServiceLocator()->get('Config');

         	$viewModel->setVariable('logo_config',$config['logo-config']);

			$viewModel->setTemplate('application/index/index.phtml');
            $viewModel->setVariables( array(
				'client_login'  	 => true,
                'loginForm' 		 => $form,
                'redirect'  		 => $redirect,
                'enableRegistration' => $this->getOptions()->getEnableRegistration(),
            ));
			return $viewModel;
  		}


        $form->setData(array('identity'=>trim($request->getPost('identity')),'credential'=> $request->getPost('credential')));

        if (!$form->isValid()) {

            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);
            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_LOGIN).($redirect ? '?redirect='.$redirect : ''));
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
	}

	public function logoutAction()
    {


    	$current_user = $this->zfcUserAuthentication()->getIdentity();



        Hybrid_Auth::logoutAllProviders();

        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthAdapter()->logoutAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }

		$redirect = "home";
		if($current_user && $current_user->getType()=='user')
		   $redirect = 'client-login';

        return $this->redirect()->toRoute($redirect);

    }

	public function searchAction(){
		$user_mapper      = $this->getServiceLocator()->get('jimmybase_user_mapper');
		$q                = $this->params()->fromQuery('query');

        $users = $user_mapper->search($q);

        foreach ($users as $key => $value) {
        	$newUsers[] = $value->getEmail();
        }

        return new JsonModel($newUsers);
	}

	/**
	 * Getters/setters for DI stuff
	 */
    public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = $this->getServiceLocator()->get('jimmybase_user_service');
        }

        return $this->userService;
    }

    public function setUserService($userService)
    {
        $this->userService = $userService;
        return $this;
    }
}
