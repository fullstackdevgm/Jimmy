<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;



class SettingsController extends AbstractActionController
{
    public function indexAction(){
			$basePath = $this->getRequest()->getBaseUrl();
			
			$this->getServiceLocator()->get('viewhelpermanager')->get('HeadScript')
    															->appendFile($basePath.'/js/jQuery/jquery-1.9.1.min.js')
								   								->appendFile($basePath.'/js/application.js');
	
		
		
		  $viewModel = new ViewModel();
		
	

        
        return $viewModel;
	} 
	
	public function updateAccountAction(){
			
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$viewmodel = new ViewModel();
     	//print_r($request->getPost('id'));		

		$admin_user_mapper = $this->getServiceLocator()->get('admin_user_mapper');
		$client_service    = $this->getServiceLocator()->get('admin_client_service');
		
		$admin_user = $admin_user_mapper->findById($request->getPost('id'));

	
		if($request->isPost()){			
			$clientInfo   = $request->getPost()->toArray();
			$userInfo     = array( 'id' 					=> $request->getPost('id'), 
								   'email' 					=> $request->getPost('email'), 
								  // 'display_name'  			=> $request->getPost('display_name'),
								   'name'   				=> $request->getPost('name'),
								   'state'					=> $admin_user->getState(),
								   'type'					=> $admin_user->getType(),
								   'password'				=> $admin_user->getPassword()
								  );
			 				  
		    $client   = $client_service->save($userInfo);
		}

		if($client){
		   $viewmodel->setTerminal(true)
					 ->setTemplate('admin-account')
					 ->setVariables(array(
							  'client' => $admin_user_mapper->findById($request->getPost('id'))
					  ));
					  
  	       $htmlOutput = $this->getServiceLocator()
                     		  ->get('viewrenderer')
                              ->render($viewmodel);
							  
			$response->setContent(\Zend\Json\Json::encode(array('success'=>true,'html'=>$htmlOutput)));
		} else {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
		}
		
	    return $response;

		return array(
					'registerForm' => $this->getClientRegisterForm(),
					'redirect'     => $redirect,
					'client'	   => $clientDetails
		);	
	   
	}	
	
	public function changePwdAction(){
		$request   = $this->getRequest();
		$response  = $this->getResponse();
		$viewmodel = new ViewModel();
	 	
		$admin_user_mapper = $this->getServiceLocator()->get('admin_user_mapper');
		$client_service    = $this->getServiceLocator()->get('admin_client_service');
		
		$admin_user = $admin_user_mapper->findById($request->getPost('id'));
		
		if($request->isPost()){			
			$clientInfo   = $request->getPost()->toArray();
			$userInfo     = array( 'id'=>$admin_user->getId(), 'password' => $request->getPost('password'));
			 				  
		    $client   = $client_service->changePassword($userInfo);
		}

		if($client){
			$response->setContent(\Zend\Json\Json::encode(array('success'=>true)));
		} else {
			$response->setContent(\Zend\Json\Json::encode(array('success'=>false)));
		}
		
	    return $response;

	}
	
	
	public function clearCacheAction(){
		
     
		 if($this->getCache()->clearByNamespace('jimmy')){
		 	$this->flashMessenger()->setNamespace('success')->addMessage('Cache has been cleared!');
		 } else {
			$this->flashMessenger()->setNamespace('error')->addMessage('Cache could not be cleared!');
		 }
	 	
		return $this->redirect()->toRoute('admin/settings');
	}
	
	public function getCache(){
		if (!$this->cache) {
			$this->setCache($this->getServiceLocator()->get('cache'));
		}
		
      return $this->cache;
    }
	
	public function setCache($cache){
        $this->cache = $cache;
    }
}
